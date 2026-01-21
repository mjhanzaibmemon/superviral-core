<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
// 

// $host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.superviral.io)
// $subdomain = explode('.', $host)[0]; // Get the first part of the domain
// $initial = $subdomain . '.';
// $subdomain = '/'. $subdomain . '/etra.group';
// if(!empty($initial) && $initial != "superviral.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/aws-sdk/aws-autoloader.php';


global $sesusernamev2;
global $sespasswordv2;
global $cloudwatchkey;
global $cloudwatchpassword;
global $rapidapihost;
global $rapidapikey;

use Aws\Ssm\SsmClient;
use Aws\Exception\AwsException;

function loadEnvFromParameterStore($prefix = '/')
{
    try {
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

        return $loadedVars;

    } catch (AwsException $e) {
        echo "<strong style='color: red;'>AWS Error:</strong> " . $e->getAwsErrorMessage() . "<br>";
        error_log("AWS SSM error: " . $e->getMessage());
        return [];
    } catch (Exception $e) {
        echo "<strong style='color: red;'>General Error:</strong> " . $e->getMessage() . "<br>";
        error_log("General error: " . $e->getMessage());
        return [];
    }
}

// Load environment variables
$envVars = loadEnvFromParameterStore();

// echo "<pre>";

// print_r($envVars);die:
if (empty($envVars)) {
    echo "[⚠️] No parameters found or an error occurred.\n";die;
}

// echo "<h3>Accessing the Loaded Variables</h3><pre>";
// echo "acquire_app_id = $acquire_app_id\n"; 
// echo "cloudwatchkey = $cloudwatchkey\n"; 
// echo "</pre>";
