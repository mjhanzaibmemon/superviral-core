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
$posts = $_POST['myInputs'];
$amount = addslashes($_POST['amount']);

$getBrand = addslashes($_GET['brand']);
if(!empty($getBrand)) $brand = $getBrand;

if((!empty($orderid))&&(!empty($posts))&&(!empty($amount))){

			$emailtrue='asdas4dsdf';

			$added = time();

			$ordersession = md5('neworder'.$added.$id);

			$qfetch = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid' AND brand = '$brand' LIMIT 1");
			$orderinfo = mysql_fetch_array($qfetch);

			include($_SERVER['DOCUMENT_ROOT'].'/crons/orderfulfillraw.php');

			$domain = getBrandSelectedDomain($brand);
			$source = getBrandSelectedSource($brand);
			$brandName = getBrandSelectedName($brand);

			foreach($posts as $post){
			if(empty($post))continue;
			$postsrefined[] = $post;
			}

			$totalposts = count($posts);
			unset($post);

			$multiamount = $amount / $totalposts;
			$multiamount = round($multiamount);

			foreach($postsrefined as $post){


			$post = trim($post);

			$postraw = str_replace('https://www.'. $source .'/p/','',$post);
			$postraw = str_replace('/','',$postraw);
			$postraw = trim($postraw);

			$order1 = $api->order(array('service' => $freelikesorderid, 'link' => $post, 'quantity' => $multiamount));

			$fulfillids .= $order1->order;
			$fulfillids .= ' ';

			$chooseposts .= $postraw.' ';

			echo $postraw.'<br>'.$post.'<br>'.$multiamount.'<hr>';

			}

			if(empty($orderid))die('Contact Rabban with this error: Missing Fulfill ID for Likes');


			$insertq = mysql_query("INSERT INTO `orders` SET 
					`packagetype`= 'likes', 
					`account_id`= '{$orderinfo['account_id']}',
					`order_session`= '{$ordersession}',
					`added` = '$added', 
					`lastrefilled` = '$added', 
					`amount`= '$amount', 
					`emailaddress`= '{$orderinfo['emailaddress']}', 
					`igusername`= '{$orderinfo['igusername']}', 
					`ipaddress`= '{$orderinfo['ipaddress']}',
					`price`= '0.00',
					`payment_id` = '{$orderinfo['payment_id']}',
					`fulfill_id`= '{$fulfillids}',
					`chooseposts` = '$chooseposts',
                     brand = '$brand'");


			//EMAILER NEEDS TO COME IN HERE
$thefreeservice = $amount.' free Likes';
$service = $amount.' High Quality Likes';
$ctahref = 'https://'. $domain .'/track-my-order/'.$ordersession;
$igusername = $username;
$to = $orderinfo['emailaddress'];
$subject = 'Free '. $brandName .' Likes Notification';
include($_SERVER['DOCUMENT_ROOT'] . '/admin/api/emailfree.php');

$insertid = mysql_insert_id();

if($insertq)$success = '<div class="emailsuccess">A new order '.$insertid.' has been placed for '.$amount.' free likes to: '.$igusername.'</div>';



}

$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$id' AND brand = '$brand' LIMIT 1");
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
$tpl = str_replace('{orderinfo}',$orderinfo,$tpl);
$tpl = str_replace('{brand}',$infoa['brand'],$tpl);


output($tpl, $options);
