<?
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 

require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/sm-db.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/aws-sdk/aws-autoloader.php';

use Aws\Lambda\LambdaClient;
use Aws\Exception\AwsException;


global $awsConfig;
// AWS Configuration
$awsConfig = [
    'version' => 'latest',
    'region'  => 'us-east-2', // Set your region
    'credentials' => [
        'key'    => $amazons3key,
        'secret' => $amazons3password,
    ],
];


function connectToLambda($functionName, $data)
{
    // Create a Lambda client
    global $awsConfig;
    $lambdaClient = new LambdaClient($awsConfig);

    try {
        // Construct an API Gateway-like event payload
        $eventPayload = [
            'httpMethod' => 'POST', // Changed to POST to send data
            'headers' => [],
            'queryStringParameters' => null,
            'pathParameters' => null,
            'body' => json_encode($data), // Encode the data as JSON
            'isBase64Encoded' => false,
        ];

        // Invoke the Lambda function
        $result = $lambdaClient->invoke([
            'FunctionName' => $functionName,
            'InvocationType' => 'RequestResponse', // Synchronous invocation
            'LogType' => 'Tail',
            'Payload' => json_encode($eventPayload),
        ]);

        // Decode the result payload
        $payload = json_decode($result['Payload']->getContents(), true);


        return $payload;
    } catch (AwsException $e) {
        return "Error: " . $e->getAwsErrorMessage() . "\n";
    }
}
