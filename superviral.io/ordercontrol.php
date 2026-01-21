<?php

//make changes also to order3-addpackages.php
$auto_likes = array(
    'likes_per_post' => '50',
    'max_per_day' => '4',
    'price' => '0.00',
    'original_price' => '10.94',
    'save' => '50%'
);



////////////////////////////////////check if logged in



if(isset($_COOKIE['plus_id']) && isset($_COOKIE['plus_token'])) {//Check if cookie already exists and redirect to account home page
    
    $plus_id = $_COOKIE['plus_id'];
    $plus_token = $_COOKIE['plus_token'];

    // get logged in user with plus_id and plus_token cookie values
    $result = mysql_query("SELECT * FROM `accounts` USE INDEX (access_accounts) WHERE `brand`='sv' AND `email_hash` = '$plus_id' AND `token_hash` = '$plus_token' ORDER BY `id` DESC LIMIT 1");
    $userinfo = mysql_fetch_array($result);
    $num_rows33 = mysql_num_rows($result);
    if($num_rows33 == 1){//match found meaning redirect
        $loggedin = true;
    //MYSQL QUERY "UPDATE orders with "
    //REFRESH THE PARENT FRAME SO THAT IT DOESNT COME UP

    }

}

$ordersession = addslashes($_COOKIE['ordersession']);
// from admin test post grabber           
if(!empty($_GET['type']) && $_GET['type'] == 'testPG'){          
          
    $ordersession = addslashes($_GET['ordersession']);
}  

////////////////////////////////////check if logged in with a bypass


if((!empty($_GET['onetimetoken']))&&(empty($ordersession))&&(empty($_COOKIE['plus_id']))&&(empty($_COOKIE['plus_token']))){

$searchonetimetoken = addslashes($_GET['onetimetoken']);

$searchforonetimetokenq = mysql_query("SELECT * FROM `order_session` WHERE `brand`='sv' AND `payment_onetime_token_active` = '1' AND `payment_onetime_token` = '$searchonetimetoken' ORDER BY `id` DESC LIMIT 1");

if(mysql_num_rows($searchforonetimetokenq)==0)die('Error #539392: Order Session Not Found');

$searchonetimetokeninfo = mysql_fetch_array($searchforonetimetokenq);

//immediately update this as non active
mysql_query("UPDATE `order_session` SET `payment_onetime_token_active` = '0' WHERE `id` = '{$searchonetimetokeninfo['id']}' AND `brand`='sv' LIMIT 1");


$_GET['redirectid'] = $searchonetimetokeninfo['order_session'];

    $getuserthroughonetimetokenq = mysql_query("SELECT * FROM `users` WHERE `brand`='sv' AND `id` = '{$searchonetimetokeninfo['account_id']}' ORDER BY `id` DESC LIMIT 1");

    if(mysql_num_rows($getuserthroughonetimetokenq)==0)die('Error #531190: Order Session Not Found');

    $userinfo = mysql_fetch_array($getuserthroughonetimetokenq);

    $loggedin = true;


}


////////////////////////////////////




if((empty($ordersession))&&(!empty($_GET['redirectid']))) {$ordersession = addslashes($_GET['redirectid']);}


if(empty($ordersession)) {


	header('Location: /404?no-order-session');


}else{

$checkifexistsq = mysql_query("SELECT * FROM `order_session` USE INDEX (`idx_order_session_composite`) WHERE `brand`='sv' AND `order_session` = '$ordersession' ORDER BY `id` DESC LIMIT 1");
if(mysql_num_rows($checkifexistsq)=='0'){


	/*if(($_SERVER['REMOTE_ADDR']=='62.30.117.187')||($_SERVER['REMOTE_ADDR']=='172.26.17.119')){
		echo 'Cookie session ID:'.$ordersession.'<br>IP Address:'.$_SERVER['REMOTE_ADDR'];die;}*/


	header('Location: /buy-instagram-followers/');exit('NO ORDER SESSION');
	header('Location: /404');

}

}

$info = mysql_fetch_array($checkifexistsq);

if($loggedin==true){

    mysql_query("UPDATE `order_session` SET `account_id` = '{$userinfo['id']}' WHERE `order_session` = '$ordersession' AND `brand`='sv' ORDER BY `id` DESC LIMIT 1");

}else{


    mysql_query("UPDATE `order_session` SET `account_id` = '0' WHERE `order_session` = '$ordersession' AND `brand`='sv' ORDER BY `id` DESC LIMIT 1");

}




?>