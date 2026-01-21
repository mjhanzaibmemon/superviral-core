<?php

include('db.php');


$json = json_decode(file_get_contents("php://input"));

$validateurl = $json->ValidationUrl;
$validateurl = 'https://apple-pay-gateway-nc-pod3.apple.com/paymentservices/startSession';




$now = date('omdHis',time());
$now = substr($now, 4);
$now = date("Y").$now;



$regdomain = 'superviral.io';



$requestmerchant_session =  array(
      
    "timestamp" => $now,
    "company_id" => $acquiredaccountid,
    "company_pass" => $acquiredcompanypass,


    "transaction" => array(
          
        "status_request_type" => 'APPLE_SESSION',
        "domain" => $regdomain,
        "display_name" => 'Superviral',
        "validation_url" => $validateurl,
    ),
          

);


$request_hash = hash('sha256',$now.'APPLE_SESSION'.$acquiredaccountid.$acquiredsecretpasscode);

$requestmerchant_session['request_hash'] = $request_hash;



/*echo '<pre>';
print_r($requestmerchant_session);
echo '</pre>';


die;

*/




$url = "https://gateway.acquired.com/api.php/status";    
$requestmerchant_session = json_encode($requestmerchant_session);

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Content-type: application/json"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $requestmerchant_session);

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);


curl_close($curl);

$response = json_decode($json_response, true);

/*echo '<pre>';
print_r($response);
echo '</pre>';
die;
*/
$merchant_session = base64_decode($response['merchant_session']);



echo $merchant_session;

?>