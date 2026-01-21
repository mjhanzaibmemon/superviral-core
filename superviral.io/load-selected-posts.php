<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

//http://uk.superviral.io/orderselect-test.php?id=6

$loc = addslashes($_GET['loc']);

include('db.php');

$utmcampaign = '&utm_source=mail&utm_medium=email&utm_campaign=postdetection';


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




$id = 22; 

$user_ip = getUserIP();

function setnewcookie($msg,$country,$user_ip){

		global $locas;
		global $loc;

            $id = 22; // by default 250 likes package
			$order_session = md5($_SERVER['REMOTE_ADDR'].time().$id);
			$added = time();

            $shortCodes = addslashes($_GET['shortCodes']);
            $user = addslashes($_GET['user']);
            $email = addslashes($_GET['email']);
            $hash = addslashes($_GET['hash']);
            

            $eachShortCode = explode(',', $shortCodes);
            $strShortCodes = '';
            foreach($eachShortCode as $code){
                $newimgname = md5('superviralrb'.$code);
                $strShortCodes .= "$code###https://cdn.superviral.io/thumbs/$newimgname.jpg~~~";   
            }

            // echo $strShortCodes;die;
			$insert = mysql_query("INSERT INTO `order_session` SET `brand` = 'sv',`country` = '{$country}',`order_session`='$order_session',`packageid` = '$id',`ipaddress` = '{$user_ip}',`igusername`='{$user}',`emailaddress`='{$email}',`done`='0',`added`='$added',`unsubscribe`='0',`abandonedemail`='0',`freefollowers`='0',`chooseposts` = '{$strShortCodes}',`payment_creq_crdi` = ''");
			
			$newcookieid = mysql_insert_id();

			setcookie(
			  "ordersession",
			  $order_session,
			  time() + (10 * 365 * 24 * 60 * 60),
			  "/"
			);

			//header('Location: /order/details/');
			header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1select'].'/?postdetection=true'.$utmcampaign);

}

//CHECK IF ID EXISTS
if(empty($id)){die('NO ID FOUND');header('Location: /404?no-id-found');}

//CHECK IF ID BELONGS TO A PARTICULAR PACKAGE
$checkq = mysql_query("SELECT * FROM `packages` WHERE `id` = '$id' LIMIT 1");
if(mysql_num_rows($checkq)=='0'){die('NO PACKAGE FOUND');header('Location: /404?no-package-found');}



$id = 22; // by default 250 likes package

//IF COOKIE IS SET
if(empty($_COOKIE['ordersession'])) {


		setnewcookie('',$country,$user_ip);

		$_COOKIE['ordersession'] = addslashes($newcookieid);

		echo 'New cookie: '.$_COOKIE['ordersession'];
		//CREATED A NEW AND NOW REDIRECT HEADER
		//header('Location: /order/details/');
        header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1select'].'/?postdetection=true'.$utmcampaign);

}

else

{

		$_COOKIE['ordersession'] = addslashes($_COOKIE['ordersession']);

		$checkq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$_COOKIE['ordersession']}' AND `packageid` = '$id' LIMIT 1");
		if(mysql_num_rows($checkq)=='0'){setnewcookie('',$country,$user_ip);die('ORDER SESSION NOT FOUND');}
		$info = mysql_fetch_array($checkq);

/*		if($_SERVER['REMOTE_ADDR']!==$info['ipaddress']){

			setnewcookie('IP ADDRESS NOT MATCHING NOW REDIRECT',$country);

		}*/

		echo 'Retrieved cookie: '.$_COOKIE['ordersession'];
		//NOW REDIRECT HEADER
		//header('Location: /order/details/');
        header('Location: /'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order1select'].'/?postdetection=true'.$utmcampaign);


}




?>