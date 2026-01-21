<?php


if($_SERVER['HTTP_X_FORWARDED_FOR']!=='212.159.178.222'){die('Unauthorized access');}

$showoutput = '0';

    function money_convert($from, $to, $amount,$the_key)
{
    $url = "https://v6.exchangerate-api.com/v6/".$the_key."/latest/USD";
    $request = curl_init();
    $timeOut = 0;
    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($request, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36");
    curl_setopt($request, CURLOPT_CONNECTTIMEOUT, $timeOut);
    $response = curl_exec($request);
    curl_close($request);
    $response = json_decode($response);


    return round(($amount * $response->conversion_rates->GBP), 2);
}




// New URL
// https://test-api.acquired.com/v1/login
// https://test-api.acquired.com/app/reconciliation?company_id=604&mid_id=2092&start_date=2024-05-14&end_date=2024-05-15
// prod URL
// https://api.acquired.com/v1/login
// https://api.acquired.com/app/reconciliation?company_id=604&mid_id=2092&start_date=2024-05-14&end_date=2024-05-15


require_once '../sm-db.php';

include dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/messagebird/autoload.php';

$mid = 2567;
$midUS = $locas['us']['mid'];
$midUK = $locas['uk']['mid'];
$companyId = $acquiredaccountid;
$date = date('Ymd', strtotime('-1 days'));
$startDate = date('Y-m-d', strtotime('-1 days')); // yesterday
$endDate = date('Y-m-d'); //today


// =======================================================================================

// step 1: Get Token
$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.acquired.com/v1/login",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    'app_id' => $app_id,
    'app_key' => $app_key,
    "api" => true
  ]),
  CURLOPT_HTTPHEADER => [
    "accept: application/json",
    "content-type: application/json"
  ],
]);

$response = curl_exec($curl);

$data = json_decode($response);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  if($showoutput==1)echo "cURL Error #:" . $err;
} else {
  $accessToken = $data->access_token;
  //if($showoutput==1) echo 'accesstoken:<br>' .$accessToken;
}



// =======================================================================================

// step 2: Access US Data
$curl = curl_init();
// echo "https://test-api.acquired.com/app/reconciliation?company_id=604&mid_id=2092&start_date=$startDate&end_date=$endDate<br>";
curl_setopt_array($curl, [
  //CURLOPT_URL => "https://test-api.acquired.com/app/reconciliation?company_id=$companyId&mid_id=$mid&start_date=$startDate&end_date=$endDate",
  CURLOPT_URL => "https://api.acquired.com/app/reconciliation?company_id=$companyId&mid_id=$midUS&start_date=$startDate&end_date=$endDate",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "accept: application/json",
    "authorization: Bearer $accessToken"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  if($showoutput==1)echo "cURL Error #:" . $err;
} else {
  echo "<pre>";
  //if($showoutput==1) echo $response;
  $data = json_decode($response, true);
  // print_r($data);
  // die;


  if (isset($data['data']['Superviral_' . $midUS . '_' . $date]['transaction_data'])) {
    $transactionDataUS = $data['data']['Superviral_' . $midUS . '_' . $date]['transaction_data'];
    $goodtogo = 1;
  } else {
   if($showoutput==1) echo $data['message'];
    die;
  }
}


// =======================================================================================




// step 2.5: Access UK Data
$curl = curl_init();
// if($showoutput==1)echo "https://test-api.acquired.com/app/reconciliation?company_id=604&mid_id=2092&start_date=$startDate&end_date=$endDate<br>";
curl_setopt_array($curl, [
  //CURLOPT_URL => "https://test-api.acquired.com/app/reconciliation?company_id=$companyId&mid_id=$mid&start_date=$startDate&end_date=$endDate",
  CURLOPT_URL => "https://api.acquired.com/app/reconciliation?company_id=$companyId&mid_id=$midUK&start_date=$startDate&end_date=$endDate",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "accept: application/json",
    "authorization: Bearer $accessToken"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);



if ($err) {
 if($showoutput==1) echo "cURL Error #:" . $err;
} else {
 if($showoutput==1) echo "<pre>";
  // echo $response;
  $data = json_decode($response, true);
 // print_r($data);
 // die;



  if (isset($data['data']['Superviral_' . $midUK . '_' . $date]['transaction_data'])) {
    $transactionDataUK = $data['data']['Superviral_' . $midUK . '_' . $date]['transaction_data'];
    $goodtogo = 1;
  } else {
   if($showoutput==1) echo 'Asd '.$data['message'];
    die;
  }
}


// =======================================================================================

if ($goodtogo == 1) {


  $sumTransactionUS = 0;
  $sumTransactionUK = 0;
  $i =0;

  foreach ($transactionDataUS as $transaction) {


    $sumTransactionUS += $transaction['settled_amount'];
  
    $i++;
  }


/*if($showoutput==1)  echo 'ACtual amount: '.$sumTransactionUS.'<br>';
  if($showoutput==1)echo 'Converted amount: '.money_convert('USD','GBP',$sumTransactionUS,$exchangerate_api_key).'<br>';
*/

if($showoutput==1)echo 'Total US (converted to GBP): '.$sumTransactionUS.'<hr>';



  foreach ($transactionDataUK as $transaction) {

    $sumTransactionUK += $transaction['settled_amount'];

    $i++;

    }


if($showoutput==1)echo 'Total UK: '.$sumTransactionUK.'<hr>';






  $sumTransaction = round(floatval($sumTransactionUS + $sumTransactionUK));

 if($showoutput==1) echo "Result for $startDate :: " . $sumTransaction;




  if ($sumTransaction < $objectiveAmountRev) {

    // echo '<br>sent';die;
    $MessageBird = new \MessageBird\Client($messagebirdclient);
    $Message = new \MessageBird\Objects\Message();
    $Message->originator = +447451272012;
    $Message->recipients = array($rfcontactnumber);

    $Message->body = 'Etra Group Alert: Daily revenue is not achieved £' . round($sumTransaction);

    $MessageBird->messages->create($Message);

    if ($MessageBird) {
     if($showoutput==1) echo 'Text Message Sent to Rabban !<br>';
    }
  }
}
