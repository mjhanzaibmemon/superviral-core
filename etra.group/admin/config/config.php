<?php

//THESE WERE GIVEN FROM ANUJ on TEST SERVER

global $amazons3key, $amazons3password, $superviralsocialscrapekey;

require_once $_SERVER['DOCUMENT_ROOT'] .'/sm-db.php';


//AJ: Dynamic Domain
if($_SERVER['SERVER_NAME'] == "feedbuzz.lcl"){
  $protocol = 'http://'; 
}else{
  $protocol = 'https://'; 
}

$siteDomain = $protocol. $_SERVER['SERVER_NAME'];

$loc = 'test';

$locas = array(
    "test" => array(
             "sdb" => "us",
             "countrycode" => "US",
             "currencysign" => "$",
             "currencyend" => "",
             "currencypp" => "USD",
             "mid" => "2092",
             "contentlanguage" => "en-us",
             "footercopyright" => "© 2012 - ".date("Y")." Feedbuzz. All Rights Reserved.",
             "order" => "order",
             "order1" => "details",
             "order1select" => "select",
             "order2" => "review",
             "order3" => "payment",
             "order3-new" => "payment-new",
             "order3-new-1" => "payment-new-1",
             "order3-processing" => "payment-processing",
             "order4" => "finish",
             "account" => "account",
             "login" => "login",
             "logout" => "logout",
             "signup" => "sign-up",
             "forgotpassword" => "forgot-password",
             "resetpassword" => "reset-password"
         )
  );
