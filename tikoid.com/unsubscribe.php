<?php

include('db.php');

if(($_GET['unsub']=='cart')||$_GET['unsub']=='now'){

$ordersession = addslashes($_GET['ordersession']);
$id = addslashes($_GET['id']);

if((empty($ordersession))&&(empty($id))){die('Error: 3426');}

$updatesub = mysql_query("UPDATE `order_session` SET `unsubscribe`= '1' WHERE `order_session` = '$ordersession' AND `brand` = 'to' LIMIT 1");

$fetchupdateq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '$ordersession' AND `brand` = 'to' LIMIT 1");
$fetchinfo = mysql_fetch_array($fetchupdateq);

$searchq = mysql_query("UPDATE `order_session` SET `unsubscribe`= '1' WHERE `emailaddress` = '{$fetchinfo['emailaddress']}' AND `brand` = 'to'");
$searchq = mysql_query("UPDATE `users` SET `unsubscribe`= '1' WHERE `md5` = '{$id}' AND `brand` = 'to' LIMIT 1");

header('Location: https://tikoid.com/?unsub=true');

}

?>