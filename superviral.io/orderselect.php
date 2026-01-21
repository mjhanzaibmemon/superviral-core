<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

//http://uk.superviral.io/orderselect-test.php?id=6

include('db.php');

// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}


function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}

$country = $locas[$loc]['sdb'];

//SETTING UP ORDER
if(!empty($_GET['setorder'])){

			setcookie(
			  "ordersession",
			  $_GET['setorder'],
			  time() + (10 * 365 * 24 * 60 * 60),
			  "/"
			);

			if($_GET['discounton']!=='no'){
			setcookie(
			  "discount",
			  "on",
			  time() + (10 * 365 * 24 * 60 * 60),
			  "/"
			);}

			sendCloudwatchData('Superviral', 're-order-from-sms', 'ReOrder', 're-order-from-sms-function', 1);

			//header('Location: /order/review/');
			header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/?auch=1');
			

			die;

}

$id = addslashes($_GET['id']);


$user_ip = getUserIP();

//CHECK IF ID BELONGS TO A PARTICULAR PACKAGE
$checkq = mysql_query("SELECT * FROM `packages` USE INDEX (`order-select`) WHERE `brand`='sv' AND `id` = '$id' LIMIT 1");
if(mysql_num_rows($checkq)=='0'){die('NO PACKAGE FOUND');header('Location: /404?no-package-found');}
$pkgInfo = mysql_fetch_array($checkq);
$socialmedia = $pkgInfo['socialmedia'];


function setnewcookie($msg,$country,$user_ip,$loclinkforward, $socialmedia){

		global $locas;
		global $loc;

			$id = addslashes($_GET['id']);
			
			$order_session = md5($_SERVER['REMOTE_ADDR'].time().$id);
			$added = time();





			$insert = mysql_query("INSERT INTO `order_session` SET `brand` = 'sv',`country` = '{$country}',`order_session`='$order_session',`packageid` = '$id',`ipaddress` = '{$user_ip}',`igusername`='',`emailaddress`='',`done`='0',`added`='$added',`unsubscribe`='0',`abandonedemail`='0',`freefollowers`='0',`chooseposts` = '',`payment_creq_crdi` = '', socialmedia = '$socialmedia'");
			
			$newcookieid = mysql_insert_id();

			setcookie(
			  "ordersession",
			  $order_session,
			  time() + (10 * 365 * 24 * 60 * 60),
			  "/"
			);

			//header('Location: /order/details/');
			header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/?auch=1');
			
			
			die;

}

//CHECK IF ID EXISTS
if(empty($id)){die('NO ID FOUND');header('Location: /404?no-id-found');}

$id = addslashes($_GET['id']);

//IF COOKIE IS SET
if(empty($_COOKIE['ordersession'])) {


		setnewcookie('',$country,$user_ip,$loclinkforward, $socialmedia);

		$_COOKIE['ordersession'] = addslashes($newcookieid);

		echo 'New cookie: '.$_COOKIE['ordersession'];
		//CREATED A NEW AND NOW REDIRECT HEADER
		//header('Location: /order/details/');
		header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/?auch=1');
		
		die;

}

else

{

		$_COOKIE['ordersession'] = addslashes($_COOKIE['ordersession']);

		$checkq = mysql_query("SELECT * FROM `order_session` USE INDEX (`idx_order_session_composite`) WHERE `brand`='sv' AND `order_session` = '{$_COOKIE['ordersession']}' AND `packageid` = '$id' LIMIT 1");  
		if(mysql_num_rows($checkq)=='0'){setnewcookie('',$country,$user_ip,$loclinkforward, $socialmedia);die('ORDER SESSION NOT FOUND');}
		$info = mysql_fetch_array($checkq);



		echo 'Retrieved cookie: '.$_COOKIE['ordersession'];
		//NOW REDIRECT HEADER
		//header('Location: /order/details/');
		header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1'].'/?auch=1');
		
		die;


}




?>