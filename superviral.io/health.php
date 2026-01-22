<?php
/**
 * Health check endpoint for ALB/ELB
 * Returns 200 OK if the application can connect to required services
 */

// Disable error display for clean JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'checks' => []
];

$allHealthy = true;

// Check 1: Redis connection
$redisHost = getenv('REDIS_HOST');
$redisPort = getenv('REDIS_PORT') ?: 6379;

if (!empty($redisHost)) {
    try {
        $redis = new Redis();
        $connected = @$redis->connect($redisHost, (int)$redisPort, 2.0);
        if ($connected && $redis->ping()) {
            $health['checks']['redis'] = ['status' => 'ok', 'host' => $redisHost];
        } else {
            $health['checks']['redis'] = ['status' => 'failed', 'error' => 'Connection failed'];
            $allHealthy = false;
        }
    } catch (Exception $e) {
        $health['checks']['redis'] = ['status' => 'failed', 'error' => $e->getMessage()];
        $allHealthy = false;
    }
} else {
    $health['checks']['redis'] = ['status' => 'skipped', 'reason' => 'REDIS_HOST not set'];
}

// Check 2: MySQL connection
$dbHost = getenv('DB_HOST');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');
$dbName = getenv('DB_NAME');
$dbPort = getenv('DB_PORT') ?: 3306;

if (!empty($dbHost) && !empty($dbUser)) {
    try {
        $conn = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, (int)$dbPort);
        if ($conn) {
            $health['checks']['mysql'] = [
                'status' => 'ok',
                'host' => $dbHost,
                'database' => $dbName,
                'server' => mysqli_get_server_info($conn)
            ];
            mysqli_close($conn);
        } else {
            $health['checks']['mysql'] = [
                'status' => 'failed',
                'error' => mysqli_connect_error()
            ];
            $allHealthy = false;
        }
    } catch (Exception $e) {
        $health['checks']['mysql'] = ['status' => 'failed', 'error' => $e->getMessage()];
        $allHealthy = false;
    }
} else {
    $health['checks']['mysql'] = ['status' => 'skipped', 'reason' => 'DB credentials not set'];
    $allHealthy = false;
}

// Set overall status
if (!$allHealthy) {
    $health['status'] = 'unhealthy';
    http_response_code(503);
} else {
    http_response_code(200);
}

echo json_encode($health, JSON_PRETTY_PRINT);
