<?php
include('header.php');

$data = 0;
if(!empty($_COOKIE['plus_id']) && !empty($_COOKIE['plus_token'])) {

    $plus_id = $_COOKIE['plus_id'];

    $plus_token = $_COOKIE['plus_token'];



    // get logged in user with plus_id and plus_token cookie values

    $result = mysql_query("SELECT * FROM `accounts` WHERE `email_hash` = '$plus_id' AND `token_hash` = '$plus_token' AND `brand` = 'to' LIMIT 1");

    $userinfo = mysql_fetch_array($result);

    $num_rows = mysql_num_rows($result);

    if($num_rows > 0){

        $data = 1;

    }
   

}

echo $data;
?>