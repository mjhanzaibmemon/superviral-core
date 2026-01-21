<?php

include('db.php');


$json = json_decode(file_get_contents("php://input"),true);

///////////////

sendCloudwatchData('Tikoid', 'apple-pay-payment-attempt', 'OrderPayment', 'apple-pay-payment-attempt', 1);

$id = addslashes($_GET['id']);

if(empty($id))die('Error: 3843');

$q = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '$id' AND `brand` = 'to' ORDER BY `id` DESC LIMIT 1");
if(mysql_num_rows($q)==0)die('Error: 3920');

$info = mysql_fetch_array($q);


// if(!empty($_GET['userid'])){

//     $userid = addslashes($_GET['userid']);
//     $userq = mysql_query("SELECT * FROM `accounts` WHERE `email_hash` = '$userid' LIMIT 1");
//     if(mysql_num_rows($userq)==1){

    
//         $loggedin=true;
//         $userinfo = mysql_fetch_array($userq);

//     }

// }
////////////


$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND `brand` = 'to' LIMIT 1"));

if(!empty($info['upsell'])){

$upsellprice = explode('###',$info['upsell']);

$upsellamount = $upsellprice[0];
$upsellprice = $upsellprice[1];

$finalprice = $packageinfo['price'] + $upsellprice;
$packageinfo['amount'] = $packageinfo['amount'] + $upsellamount;

}else{

$finalprice = $packageinfo['price'];

}



/*//UPSELL AUTO LIKES
if(!empty($info['upsell_autolikes'])){

$upsellpriceautolikes = explode('###',$info['upsell_autolikes']);

$upsellpriceal = $upsellpriceautolikes[1];

$finalprice = $finalprice + $upsellpriceal;

}*/


$packagetitle = $packageinfo['amount'].' '.ucwords($packageinfo['type']);


$priceamount = $finalprice;

///////////////


$token = base64_encode(json_encode($_POST['token']['paymentData']));

if(empty($token))die('Error: 48220');

$year = date("Y"); 
$now = date('omdHis',time());
$now = substr($now, 4);
$now = $year.$now;


function request_hash($param,$company_hashcode){

    if(in_array($param['transaction']['transaction_type'],array('AUTH_ONLY','AUTH_CAPTURE','CREDIT','BENEFICIARY_NEW'))){
        
        

        $str=$param['timestamp'].$param['transaction']['transaction_type'].$param['company_id'].$param['transaction']['merchant_order_id'];


        }elseif(
            in_array($param['transaction_type'],array('CAPTURE','VOID',
            'REFUND','SUBSCRIPTION_MANAGE','ACCOUNT_UPDATER','PAY_OUT'))){
            $str=$param['timestamp'].$param['transaction_type'].$param['company_id'].
            $param['original_transaction_id'];
        }

        return hash('sha256',$str.$company_hashcode);
    }
      


    


$paydata =  array(
      
    "timestamp" => $now,
    "company_id" => $tikoidacquiredaccountid,
    "company_pass" => $tikoidacquiredcompanypass,

    "transaction" => array(
          
        "merchant_order_id" => $info['order_session'].'-'.time(),
        "transaction_type" => 'AUTH_CAPTURE',
        "amount" => $priceamount,
        "currency_code_iso3" => 'USD',

    ),
          

    "payment" => array(
          
        "method" => 'apple_pay',
        "token" => $token,
    ),
      
);

$request_hash = request_hash($paydata,$tikoidacquiredsecretpasscode);

$paydata['request_hash'] = $request_hash;




$url = "https://gateway.acquired.com/api.php";    
$content = json_encode($paydata);

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Content-type: application/json"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);


curl_close($curl);

$response = json_decode($json_response, true);


if($response['response_code']=='1'){

sendCloudwatchData('Tikoid', 'apple-pay-payment-made', 'OrderPayment', 'apple-pay-payment-made-function', 1);

//FULFILL ORDER

$paymentId = $response['transaction_id'];


////$priceamount//already set


$lastfour = '0';
$info['payment_billingname_crdi'] = '';

$code = '31c223b5500453655b63bf1521eb268487da3';
$applepayprocess='12313';
$dontredirectwebhook = 1;



include('pi/cardinitywebhook.php');




}


$liveresponse = array('code' => $response['response_code'],'message' => $response['response_message']);

$liveresponse = json_encode($liveresponse);

echo $liveresponse;


?>