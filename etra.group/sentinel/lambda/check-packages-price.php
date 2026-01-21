<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 
echo '<pre>';
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/lambda/core-queue.php';

use Aws\CloudWatch\CloudWatchClient;

class Api
{
    public function setApiKey($value)
    {
        $this->api_key = $value;
    }
    public function setApiUrl($value)
    {
        $this->api_url = $value;
    }

    public function services()
    { // get services
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'services',
        )));
    }

    private function connect($post)
    {
        $_post = array();
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                $_post[] = $name . '=' . urlencode($value);
            }
        }

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (is_array($post)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
            $result = false;
        }
        curl_close($ch);
        return $result;
    }
}
$api = new Api();

$api->setApiKey($fulfillment_api_key);
$api->setApiUrl($fulfillment_url);


$packages = $api->services();
$objectivePercent = 20;
// for($i = 0; $i < count($packages); $i++){
//     $packg[$i]['services'] = $packages[$i]->service;
//     $packg[$i]['rates'] = $packages[$i]->rate;
// }

$packgMap = [];
foreach ($packages as $package) {
    $packgMap[$package->service] = $package->rate;
}


// $serviceIds = array_column($inputArray, 'services');
// print_r($packg);die;
// Initialize CloudWatch client
$cloudWatchClient = new CloudWatchClient([
    'region' => 'us-east-2',
    'version' => 'latest',
    'credentials' => [
        'key'    => $cloudwatchkey,
        'secret' => $cloudwatchpassword,
    ],
]);

$query = "SELECT * from packages";
$query_run = mysql_query($query);

$sumTransaction = 0;
$multiCurl = [];
$results = [];
$mh = curl_multi_init();

while ($data = mysql_fetch_array($query_run)) {

    $id = $data['id'];
    $jap = $data['jap1'];
    $amount = $data['amount'];
    $type = $data['type'];
    $price = $data['price'];
    $socialmedia = $data['socialmedia'];
    $premium = $data['premium'];

    if($premium == 1){
        $premium = 'premium';
    }else{
        $premium = 'normal';
    }

    $rate = isset($packgMap[$jap]) ? $packgMap[$jap] : null;

    $packageDetail = $socialmedia.'-' .$amount . '-' . $type . '-(Package('. $premium .')-id:-' . $id . ')';
    $packagePrice = ($rate / 1000) * $amount;

    if(empty($price)){
        // echo 'Price is 0';
        continue;
    }
    $percentPackage = ($packagePrice / $price) * 100;
  
    if ($rate === null) {
        // echo '<br==============>NA<br>';
        try {
            // Send custom revenue metric data to CloudWatch USD
            $cloudWatchClient->putMetricData([
                'Namespace' => 'Sentinel/Lambda',
                'MetricData' => [
                    [
                        'MetricName' => 'Packages',
                        'Dimensions' => [
                            [
                                'Name' => 'NotAvailableService',
                                'Value' => $packageDetail . '-service-not-available-function'
                            ],
                        ],
                        'Unit' => 'None', // Or 'Currency' if appropriate
                        'Value' => 1, // Use the calculated revenue value
                    ],
                ],
            ]);

        } catch (Exception $e) {
            error_log('Error sending metric data: ' . $e->getMessage());
        }

        continue; // Skip if no matching service found

    }

    // echo $percentPackage . ' ' . $objectivePercent . '<br>';

    if ($percentPackage > $objectivePercent) {

        // echo $percentPackage. '<br>';
        try {

            $cloudWatchClient->putMetricData([
                'Namespace' => 'Sentinel/Lambda',
                'MetricData' => [
                    [
                        'MetricName' => 'Packages',
                        'Dimensions' => [
                            [
                                'Name' => 'PackagesMargin',
                                'Value' => $packageDetail . '-pass-objective-margin-function'
                            ],
                        ],
                        'Unit' => 'None', // Or 'Currency' if appropriate
                        'Value' => $percentPackage, // Use the calculated revenue value
                    ],
                ],
            ]);

        } catch (Exception $e) {
            error_log('Error sending metric data: ' . $e->getMessage());
        }
    }else{
        try {

            $cloudWatchClient->putMetricData([
                'Namespace' => 'Sentinel/Lambda',
                'MetricData' => [
                    [
                        'MetricName' => 'Packages',
                        'Dimensions' => [
                            [
                                'Name' => 'PackagesMargin',
                                'Value' => $packageDetail . '-failed-objective-margin-function'
                            ],
                        ],
                        'Unit' => 'None', // Or 'Currency' if appropriate
                        'Value' => $percentPackage, // Use the calculated revenue value
                    ],
                ],
            ]);

        } catch (Exception $e) {
            error_log('Error sending metric data: ' . $e->getMessage());
        }
    }
    // echo "db price: $price = " . $amount . ' '. $type.'(Service id: '. $jap .') =' . ' ' . (($rate/1000) * $amount) .'<br>===='; 

}
