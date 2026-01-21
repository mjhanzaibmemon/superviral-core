<?php
// loadParamStore.php
// Enhanced logging for ECS/Fargate debugging

/**
 * Structured logging function for debugging in CloudWatch
 */
function superviral_log($level, $message, $context = []) {
    $timestamp = date('c');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logLine = "[$timestamp] [$level] [loadParamStore] $message$contextStr";
    error_log($logLine);

    // Also write to a dedicated debug log if in container environment
    if (getenv('ECS_CONTAINER_METADATA_URI') || getenv('ENVIRONMENT') === 'dev') {
        $debugLog = '/var/log/apache2/superviral_debug.log';
        @file_put_contents($debugLog, $logLine . "\n", FILE_APPEND | LOCK_EX);
    }
}

// Avoid "undefined variable" warnings
$awsNotNeeded = $awsNotNeeded ?? false;
$redis = null;
$redisConnected = false;

superviral_log('INFO', 'Starting loadParamStore.php', [
    'REDIS_HOST' => getenv('REDIS_HOST') ?: 'NOT_SET',
    'REDIS_PORT' => getenv('REDIS_PORT') ?: 'NOT_SET',
    'DB_HOST' => getenv('DB_HOST') ?: 'NOT_SET',
    'DB_NAME' => getenv('DB_NAME') ?: 'NOT_SET',
    'ENVIRONMENT' => getenv('ENVIRONMENT') ?: 'NOT_SET'
]);

// 1) Prefer environment variables (EKS / container-friendly)
// If DB env vars are present, use them for database connection
$dbHostEnv = getenv('DB_HOST');
$dbNameEnv = getenv('DB_NAME');
$dbUserEnv = getenv('DB_USER');
$dbPassEnv = getenv('DB_PASS');

if ($dbHostEnv && $dbUserEnv && $dbPassEnv !== false) {
    $dbHost = $dbHostEnv;
    $dbName = $dbNameEnv ?: ($dbName ?? 'etra_superviral');
    $dbUser = $dbUserEnv;
    $dbPass = $dbPassEnv;

    superviral_log('INFO', 'Using environment variables for DB connection', [
        'dbHost' => $dbHost,
        'dbName' => $dbName,
        'dbUser' => $dbUser
    ]);
}

// Optional: debug settings (you can turn display_errors off in prod)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$currentUrl = $requestUri;
$parsedUrl = parse_url($currentUrl, PHP_URL_PATH);
$segments = explode('/', trim($parsedUrl, '/'));
$lastSegment = end($segments);

// Only load AWS SDK if we didn't already decide AWS is not needed
if (!$awsNotNeeded) {
    // Use a relative path based on this file's directory
    $awsAutoloader = __DIR__ . '/common/aws-sdk/aws-autoloader.php';

    if (file_exists($awsAutoloader)) {
        require_once $awsAutoloader;
        superviral_log('INFO', 'AWS SDK autoloader loaded');
    } else {
        // In EKS this will typically not exist; log and continue without fatal error
        superviral_log('WARN', 'AWS SDK autoloader not found, skipping Param Store', [
            'path' => $awsAutoloader
        ]);
        // Mark AWS as not needed and stop here to avoid SSM calls
        $awsNotNeeded = true;
    }
}

// Redis connection with retry and proper error handling
$redisHost = getenv('REDIS_HOST');
$redisPort = getenv('REDIS_PORT') ?: 6379;
$redisRetries = 3;
$redisRetryDelay = 2;

if (empty($redisHost)) {
    superviral_log('ERROR', 'REDIS_HOST environment variable is not set!');
    // Don't die - allow the application to handle this gracefully
    $redisConnected = false;
} else {
    for ($attempt = 1; $attempt <= $redisRetries; $attempt++) {
        try {
            superviral_log('INFO', "Redis connection attempt $attempt/$redisRetries", [
                'host' => $redisHost,
                'port' => $redisPort
            ]);

            $redis = new Redis();
            $connectResult = $redis->connect($redisHost, (int)$redisPort, 5.0); // 5 second timeout

            if ($connectResult) {
                // Test the connection with a PING
                $pingResult = $redis->ping();
                if ($pingResult === true || $pingResult === '+PONG' || $pingResult === 'PONG') {
                    superviral_log('INFO', 'Redis connected successfully', [
                        'host' => $redisHost,
                        'port' => $redisPort,
                        'ping' => $pingResult
                    ]);
                    $redisConnected = true;
                    break;
                } else {
                    superviral_log('WARN', 'Redis connected but PING returned unexpected value', [
                        'ping' => $pingResult
                    ]);
                }
            }
        } catch (RedisException $e) {
            superviral_log('ERROR', "Redis connection failed (attempt $attempt)", [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'host' => $redisHost,
                'port' => $redisPort
            ]);

            if ($attempt < $redisRetries) {
                superviral_log('INFO', "Waiting {$redisRetryDelay}s before retry...");
                sleep($redisRetryDelay);
            }
        } catch (Exception $e) {
            superviral_log('ERROR', "Unexpected error during Redis connection (attempt $attempt)", [
                'error' => $e->getMessage(),
                'type' => get_class($e)
            ]);

            if ($attempt < $redisRetries) {
                sleep($redisRetryDelay);
            }
        }
    }

    if (!$redisConnected) {
        superviral_log('ERROR', 'All Redis connection attempts failed', [
            'host' => $redisHost,
            'port' => $redisPort,
            'attempts' => $redisRetries
        ]);
        // Create a null-safe redis wrapper to prevent fatal errors
        $redis = null;
    }
}

global $sesusernamev2;
global $sespasswordv2;
global $cloudwatchkey;
global $cloudwatchpassword;
global $rapidapihost;
global $rapidapikey;

use Aws\Ssm\SsmClient;
use Aws\Exception\AwsException;

function loadEnvFromParameterStore($prefix = '/', $useRedis = true, $timeout = 3600) {
    global $redis, $redisConnected;

    superviral_log('INFO', 'loadEnvFromParameterStore called', [
        'prefix' => $prefix,
        'useRedis' => $useRedis,
        'redisConnected' => $redisConnected
    ]);

    // Try to get from Redis cache first (if Redis is available)
    if ($useRedis && $redisConnected && $redis !== null) {
        try {
            $redisKey = "ssm_cache:" . md5($prefix);
            $cachedJson = $redis->get($redisKey);

            if ($cachedJson !== false && $cachedJson !== null) {
                superviral_log('INFO', 'Found cached SSM parameters in Redis');
                $loadedVars = json_decode($cachedJson, true);
                if (is_array($loadedVars)) {
                    foreach ($loadedVars as $name => $value) {
                        putenv("$name=$value");
                        global $$name;
                        $$name = $value;
                    }
                    superviral_log('INFO', 'Loaded parameters from Redis cache', [
                        'count' => count($loadedVars)
                    ]);
                    return $loadedVars;
                }
            }
        } catch (Exception $e) {
            superviral_log('WARN', 'Redis cache read failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    // Check if AWS SDK is available
    if (!class_exists('Aws\Ssm\SsmClient')) {
        superviral_log('WARN', 'AWS SSM Client not available, skipping Parameter Store');
        return [];
    }

    try {
        superviral_log('INFO', 'Fetching parameters from AWS SSM');

        $ssm = new SsmClient([
            'region' => 'us-east-2',
            'version' => 'latest',
        ]);

        $loadedVars = [];
        $nextToken = null;

        do {
            $result = $ssm->getParametersByPath([
                'Path' => $prefix,
                'WithDecryption' => true,
                'Recursive' => true,
                'NextToken' => $nextToken
            ]);

            foreach ($result['Parameters'] as $param) {
                $name = basename($param['Name']);
                $value = $param['Value'];

                putenv("$name=$value");

                global $$name;
                $$name = $value;

                $loadedVars[$name] = $value;
            }

            $nextToken = $result['NextToken'] ?? null;
        } while ($nextToken);

        superviral_log('INFO', 'Loaded parameters from AWS SSM', [
            'count' => count($loadedVars)
        ]);

        // Save to Redis (if available)
        if ($useRedis && $redisConnected && $redis !== null && !empty($loadedVars)) {
            try {
                $redisKey = "ssm_cache:" . md5($prefix);
                $redis->setex($redisKey, $timeout, json_encode($loadedVars));
                superviral_log('INFO', 'Cached SSM parameters in Redis');
            } catch (Exception $e) {
                superviral_log('WARN', 'Failed to cache SSM parameters in Redis', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $loadedVars;

    } catch (AwsException $e) {
        superviral_log('ERROR', 'AWS SSM error', [
            'error' => $e->getAwsErrorMessage(),
            'code' => $e->getAwsErrorCode()
        ]);
        // Don't echo in production - just log
        if (getenv('ENVIRONMENT') === 'dev') {
            error_log("AWS SSM error: " . $e->getMessage());
        }
        return [];
    } catch (Exception $e) {
        superviral_log('ERROR', 'General error loading parameters', [
            'error' => $e->getMessage(),
            'type' => get_class($e)
        ]);
        if (getenv('ENVIRONMENT') === 'dev') {
            error_log("General error: " . $e->getMessage());
        }
        return [];
    }
}

// Only try to load from Parameter Store if AWS SDK is available
$envVars = [];
$loadParamErrorFlag = 0;

if (!$awsNotNeeded && class_exists('Aws\Ssm\SsmClient')) {
    $envVars = loadEnvFromParameterStore('/', $redisConnected, 86400); // 1 day timeout
    if (empty($envVars)) {
        superviral_log('WARN', 'No parameters loaded from SSM Parameter Store');
        $loadParamErrorFlag = 1;
    }
} else {
    superviral_log('INFO', 'Skipping Parameter Store (AWS SDK not available or not needed)');
}

superviral_log('INFO', 'loadParamStore.php completed', [
    'redisConnected' => $redisConnected,
    'parameterCount' => count($envVars),
    'loadParamErrorFlag' => $loadParamErrorFlag
]);
