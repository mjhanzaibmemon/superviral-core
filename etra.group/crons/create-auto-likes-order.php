<?php

//THIS IS FOR NEW AUTO LIKES ORDER THAT HAVE BEEN FRESHLY CREATED, BUT NEEDS AN FULFILL ID and occurs every 2 minutes

include('../sm-db.php');

$now = time();

$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `start_fulfill` = '0' AND  `expires` > $now ORDER BY `id` DESC LIMIT 1");

if(mysql_num_rows($q)=='0'){die('No more left');}

include('orderfulfillraw.php');



$info = mysql_fetch_array($q);

	
	$al_expiry = date("d/m/Y", $now);//the next day so the cron job can automate this for the next fulfill id

	$al_username = $info['igusername'];
	$al_username = str_replace('@','',$al_username);

	$al_min = $info['likes_per_post'];
	$al_max = round($info['likes_per_post'] * 1.2);
	$al_max_perday = $info['max_post_per_day'];

	if($info['al_package_id']!=='0'){

		$alpackageidq = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `id` = '{$info['al_package_id']}' LIMIT 1");
		$alpackageidinfo = mysql_fetch_array($alpackageidq);
		$fulfillautolikesorderid = $alpackageidinfo['jap1'];

	}

//
$autolikesorder = $api->order(array(
	'service' => $fulfillautolikesorderid, 
	'username' => $info['igusername'], 
	'min' => $al_min,
	'max' => $al_max,
	'posts' => $al_max_perday,
	'delay' => '5',
	'expiry' => $al_expiry
	));

$al_fullfill_id = $autolikesorder->order;

echo $info['id'].' - '.$al_fullfill_id.'<hr>';

$startoftoday = strtotime("today", $now);

mysql_query("UPDATE `automatic_likes` SET `last_updated` = '$startoftoday', `start_fulfill` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");

$fulfilladded = $now;
$fulfillexpires = strtotime("tomorrow", ($now) - 1);

mysql_query("INSERT INTO `automatic_likes_fulfill` SET 
	`auto_likes_id` = '{$info['id']}',
	`fulfill_id` = '$al_fullfill_id',
	`added` = '$fulfilladded',
	`expires` = '$fulfillexpires'
	");
