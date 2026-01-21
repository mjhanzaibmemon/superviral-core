<?php

include('../sm-db.php');


	$q = mysql_query("SELECT * FROM `users` WHERE `country` = '' LIMIT 200");

	$amountleft = mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `country` = ''"));

	echo 'Amount left: '.$amountleft.'<hr>';

	if($amountleft==0)die('All Done');

	while($info = mysql_fetch_array($q)){

		$brand = $info['brand'];

		echo $info['id'];

		$info['emailaddress'] = addslashes($info['emailaddress']);

		$fetchq = mysql_query("SELECT * FROM `orders` WHERE `emailaddress` LIKE '%{$info['emailaddress']}%' AND brand = '$brand' ORDER BY `id` ASC LIMIT 1 ");
		$fetchinfo = mysql_fetch_array($fetchq);

		if(empty($fetchinfo['country']))$fetchinfo['country']= 'us';

		echo ' - '.$fetchinfo['country'];

		mysql_query("UPDATE `users` SET `country` = '{$fetchinfo['country']}' WHERE `id` =  '{$info['id']}' AND brand = '$brand' LIMIT 1");

		echo '<hr>';


	}


echo '<meta http-equiv="refresh" content="2;" />';


?>