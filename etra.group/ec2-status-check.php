<?php

//DISABLE AWS EVENT BRIDGE SCHEDULER BEFORE EDITING THIS PAGE, OR IF THIS PAGE FAILS, IT WILL RESTART THE SERVER

include('sm-db.php');


$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '1824854' LIMIT 1");

$info = mysql_fetch_array($q);

echo $info['id'];

?>
