<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');


$id = addslashes($_GET['id']);

$orderid = addslashes($_POST['id']);
// $posts = $_POST['myInputs'];
// $amount = addslashes($_POST['amount']);
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

	if (filter_var($client, FILTER_VALIDATE_IP)) {
		$ip = $client;
	} elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
		$ip = $forward;
	} else {
		$ip = $remote;
	}

	return $ip;
}

$user_ip = getUserIP();
$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$id' AND brand = '$brand' LIMIT 1");
$infoa = mysql_fetch_array($q);

if (!empty($_POST['submit'])) {

	$now = time();

	$order_session_random = md5($user_ip . time() . 'freeautolikes');

	$insertnewautolikes = mysql_query("INSERT INTO `automatic_likes_session` SET 
    	  `account_id` = '{$infoa['account_id']}',
    	  `country` = '{$locas[$loc]['sdb']}',
    	  `order_session` = '$order_session_random',
    	  `packageid` = '1',
    	  `ipaddress` = '$user_ip',
    	  `added` = '$now',
		  `igusername` = '{$infoa['igusername']}',
    	  `payment_creq_crdi` = ''
    	  ");

	if ($insertnewautolikes) {

		//CHECK IF THIS ACCOUNT IS ELEGIBLE
		$q = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$infoa['account_id']}' AND `freeautolikes` = '1' LIMIT 1 ");
		if (mysql_num_rows($q) == 1) $error = 'Error 402: Already activated for this account, please contact our support team with the error code 402.<style>body{background:#fff;}</style>'; //CLOSE WINDOW

		$q = mysql_query("SELECT * FROM `automatic_likes_free` WHERE 
			  `emailaddress` = '{$infoa['emailaddress']}' OR 
			  `igusername` = '{$infoa['igusername']}'
			  LIMIT 1 ");

		if (mysql_num_rows($q) == 1) $error = 'Error 402: Already activated for this account, please contact our support team with the error code 402.<style>body{background:#fff;}</style>'; //CLOSE WINDOW

		$checkinfo = mysql_fetch_array($q);
		$igusername = $infoa['igusername'];

		$checkexistingq = mysql_query("SELECT * FROM `automatic_likes` WHERE `igusername` LIKE '%$igusername%' LIMIT 1");

		//CHECK ALSO INSTAGRAM LIKES FREE TABLE
		if (mysql_num_rows($checkexistingq) == '1') $checkexistingq = mysql_query("SELECT * FROM `automatic_likes_free` WHERE `igusername` LIKE '%$igusername%' LIMIT 1");

		if ((empty($igusername)) || (mysql_num_rows($checkexistingq) == '1')) {

			if (empty($igusername)) $error .= '<br><div class="emailsuccess emailfailed">Please enter an Instagram username to send the automatic likes to.</div>';

			if (mysql_num_rows($checkexistingq) == '1') $error .= '<br><div class="emailsuccess emailfailed">Please enter an Instagram username that hasn\'t been used before for free auto likes.</div>';
		} else {

			$q = mysql_query("UPDATE `automatic_likes_session` SET `igusername` = '$igusername' WHERE `order_session` = '{$order_session_random}' AND `account_id` = '{$infoa['account_id']}'  LIMIT 1");
		}

		if (empty($error)) {
			// insert
			$al_username = $igusername;
			$al_min = 50;
			$al_max = 55;
			$al_likes_per_post = 50;
			$al_max_perday = 4;
			$al_md5 = md5($now . $al_username . $al_min);

			$al_endexpiry = $now + 1296000;

			//CREATE AUTO LIKES HERE then redirect
			$insertnewautolikes = mysql_query("INSERT INTO `automatic_likes`
			  SET 
			  `account_id` = '{$infoa['account_id']}',
			  `al_package_id` = '0',
			  `country` = '{$locas[$loc]['sdb']}', 
			  `md5` = '$al_md5', 
			  `added` = '$now', 
			  `expires` = '$al_endexpiry', 
			  `last_updated` = '0', 
			  `likes_per_post` = '$al_likes_per_post', 
			  `min_likes_per_post` = '$al_min', 
			  `max_likes_per_post` = '$al_max', 
			  `max_post_per_day` = '$al_max_perday',  
			  `fulfill_id` = '',
			  `start_fulfill` = '0',
			  `price` = '0.00',
			  `igusername` = '$al_username', 
			  `emailaddress` = '{$infoa['emailaddress']}',
			  `contactnumber` = '',
			  `freeautolikes_session` = '$order_session_random',
			  `autolikes_session` = '$order_session_random',
			  brand = '$brand'
			  ");

			$freeautolikesid = mysql_insert_id();

			mysql_query("UPDATE `automatic_likes_session` SET `freeautolikes` = '$freeautolikesid' 

			  WHERE `order_session` = '$order_session_random' AND `account_id` = '{$infoa['account_id']}' LIMIT 1");

			//INSERT INTO AUTOMATIC LIKES FREE SO THAT IT CANT BE USED AGAIN
			mysql_query("INSERT INTO `automatic_likes_free` SET 
			  `igusername` = '$al_username', 
			  `contactnumber` = '', 
			  `emailaddress` = '{$infoa['emailaddress']}', 
			  `ipaddress` = '$user_ip', 
			  `added` = '$now',
			  brand = '$brand'
			  ");

			mysql_query("UPDATE `accounts` SET `freeautolikes` = '1' WHERE `id` = '{$infoa['account_id']}' LIMIT 1");

			$success = "<div style='padding:5px;color:white;background:green;border-radius:5px;'>Successfully Done</div>";
		}
	} else {
		$error = "Error 593921: Please contact support. There seems to be an issue with the auto likes.";
	}
}

$orderinfo = 'ID: ' . $infoa['id'] . '<br>';
$orderinfo .= 'Email address: ' . $infoa['emailaddress'] . '<br>';
$orderinfo .= 'IG Username: ' . $infoa['igusername'] . '<br>';
// $orderinfo .= 'Order: '.$infoa['amount'].' '.$infoa['packagetype'].'<br>';
// $orderinfo .= 'Price: £'.sprintf('%.2f', $infoa['price'] / 100).'<br>';
// $orderinfo .= 'Orded Placed: '.date('l jS \of F Y H:i:s ', $infoa['added']).'<br>';

$orderinfo = '<div style="font-size:14px;font-family:verdana;padding:10px;line-height: 29px;">' . $orderinfo . '</div>';

$tpl = str_replace('{id}', $id, $tpl);
$tpl = str_replace('{success}', $success, $tpl);
$tpl = str_replace('{error}', $error, $tpl);
$tpl = str_replace('{infoaid}', $infoa['id'], $tpl);
$tpl = str_replace('{orderinfo}', $orderinfo, $tpl);


output($tpl, $options);
