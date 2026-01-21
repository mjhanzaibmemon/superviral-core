<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../aws-sdk/aws-autoloader.php';

use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;

// Get the input from the Lambda event payload
$input = json_decode(file_get_contents('php://input'), true);


$post_type = $input['type'];
$file = $input['file'];

global $sqsClient;
$sqsClient = new SqsClient([
    'region'  => 'us-east-2',  // Your AWS region
    'version' => 'latest',
    'credentials' => [
        'key'    => getenv('amazonLambdaKey'),
        'secret' => getenv('amazonLambdapassword'),
    ],
]);

$resp = call_curl($file);

// Output the response as JSON
echo $resp;
die;


function call_curl($url)
{
    global $sqsClient;
    
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    curl_setopt($curl, CURLOPT_TIMEOUT, 8);

    // curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');

    // curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 

    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

    curl_setopt($curl, CURLOPT_ENCODING, '');

    // curl_setopt($curl, CURLOPT_PROXY, $rotatingips[$randnum]);


    $get = curl_exec($curl);

    curl_close($curl);
    
    /*
    if (!mb_detect_encoding($get, 'UTF-8', true)) {
        $get = base64_encode($get); 
       // echo 'yes binary data ';
    }*/

    //echo 'res[p]:' . $get;

   return $get;
}
