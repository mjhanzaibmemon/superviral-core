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
$username = addslashes($_POST['username']);
$amount = addslashes($_POST['amount']);

$getBrand = addslashes($_GET['brand']);
if(!empty($getBrand)) $brand = $getBrand;

//ANYTHING COMMENTED OUT MEANS THEY TAKE ACTION AND SHOULD BE REMOVED
if((!empty($orderid))&&(!empty($username))&&(!empty($amount))){

			$emailtrue='asdas4dsdf';

			$added = time();

			$ordersession = md5('neworder'.$added.$id);

			$qfetch = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid' LIMIT 1");
			$orderinfo = mysql_fetch_array($qfetch);

			$brand = $orderinfo['brand'];

			include($_SERVER['DOCUMENT_ROOT'].'/crons/orderfulfillraw.php');

			$domain = getBrandSelectedDomain($brand);
			$source = getBrandSelectedSource($brand);
			$brandName = getBrandSelectedName($brand);

			$order1 = $api->order(array('service' => $freefollowersorderid, 'link' => 'https://'. $source .'/'.$username, 'quantity' => $amount));

			$fulfill_id = $order1->order;

			if(empty($fulfill_id))die('Contact Rabban with this error: Missing Fulfill ID');

			$insertq = mysql_query("INSERT INTO `orders` SET 
					`packagetype`= 'followers', 
					`account_id`= '{$orderinfo['account_id']}', 
					`order_session`= '{$ordersession}',
					`added` = '$added', 
					`lastrefilled` = '$added', 
					`amount`= '$amount', 
					`emailaddress`= '{$orderinfo['emailaddress']}', 
					`igusername`= '{$username}', 
					`ipaddress`= '{$orderinfo['ipaddress']}',
					`price`= '0.00',
					`payment_id` = '{$orderinfo['payment_id']}',
					`fulfill_id`= '{$fulfill_id}',
                     brand = '$brand'");

//EMAILER NEEDS TO COME IN HERE
$thefreeservice = $amount.' free Followers';
$service = $amount.' High Quality Followers';
$ctahref = 'https://'. $domain .'/track-my-order/'.$ordersession;
$igusername = $username;
$to = $orderinfo['emailaddress'];
$subject = 'Free '. ucfirst($brandName) .' Followers Notification';
include($_SERVER['DOCUMENT_ROOT'] . '/admin/api/emailfree.php');

$insertid = mysql_insert_id();

if($insertq)$success = '<div class="success">A new order '.$insertid.' has been placed for '.$amount.' free followers to: '.$igusername.'</div>';

}

$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$id' LIMIT 1");
$infoa = mysql_fetch_array($q);

$orderinfo = 'ID: '.$infoa['id'].'<br>';
$orderinfo .= 'Email address: '.$infoa['emailaddress'].'<br>';
$orderinfo .= 'Username: '.$infoa['igusername'].'<br>';
$orderinfo .= 'Order: '.$infoa['amount'].' '.$infoa['packagetype'].'<br>';
$orderinfo .= 'Price: £'.sprintf('%.2f', $infoa['price'] / 100).'<br>';
$orderinfo .= 'Orded Placed: '.date('l jS \of F Y H:i:s ', $infoa['added']).'<br>';

$orderinfo = '<div style="font-size:14px;font-family:verdana;padding:10px;line-height: 29px;">'.$orderinfo.'</div>';


$tpl = str_replace('{id}',$id,$tpl);
$tpl = str_replace('{success}',$success,$tpl);
$tpl = str_replace('{infoaid}',$infoa['id'],$tpl);
$tpl = str_replace('{infoaun}',$infoa['igusername'],$tpl);
$tpl = str_replace('{brand}',$infoa['brand'],$tpl);
$tpl = str_replace('{orderinfo}',$orderinfo,$tpl);

output($tpl, $options);
