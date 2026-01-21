<?php

die;
include('adminheader.php');

$fields = "*";
$q = mysql_query("SELECT {$fields} FROM `orders` WHERE `emailaddress` LIKE '%natalieto91@icloud.com%' ORDER BY `id` DESC");

while($info = mysql_fetch_array($q)){

	foreach($info as $k=>$v){
		$data[$k] = $v;
	}

	/*-------------------------------------------*/



	$showrow['id'] = '#'.$data['id'];
	$showrow['country'] = $data['country'];
	$showrow['emailaddress'] = $data['emailaddress'];
	$showrow['order_session'] = $data['order_session'];
	$showrow['packagetype'] = $data['packagetype'];
	$showrow['amount'] = $data['amount'];
	$showrow['price'] = '£'.sprintf('%.2f', $data['price'] / 100);;
	$showrow['contactnumber'] = $data['contactnumber'];
	$showrow['igusername'] = $data['igusername'];
	$showrow['chooseposts'] = $data['chooseposts'];
	$showrow['added'] = date("d/m/Y H:i:s",$data['added']);
	$showrow['fulfilled'] = date("d/m/Y H:i:s",$data['fulfilled']);
	$showrow['lastrefilled'] = date("d/m/Y H:i:s",$data['lastrefilled']);
	$showrow['lastchecked'] = date("d/m/Y H:i:s",$data['lastchecked']);
	$showrow['payment_id'] = $data['payment_id'];
	$showrow['fulfill_id'] = $data['fulfill_id'];
	$showrow['lastfour'] = $data['lastfour'];

	/*-------------------------------------------*/

	$csv_row = implode(",",$showrow);
	echo str_replace('"','""',$csv_row)."\n";


	unset($showrow);
	unset($data);

}


?>