<?php


require_once '../sm-db.php';


$now1 = time() - 10800; //$now1 and $now2 are the protected ones
$now2 = time() + 4000; //into the future just in case the database time and server UNIX timestamps are not in sync

$q = mysql_query("DELETE FROM `order_session` WHERE 
	`added` NOT BETWEEN '$now1' AND '$now2' AND 
	`igusername` = '' AND 
	`emailaddress` = '' AND 
	`account_id` = '0' AND 
	`upsell` = '' AND
	`upsell_autolikes` = '' AND 
	`choose_comments` = '' AND 
	`chooseposts` = '' 
	ORDER BY `order_session`.`id` DESC LIMIT 10000");




?>