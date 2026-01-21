<?php

$session = addslashes($_GET['session']);

$db=1;
include('../db.php');

if(mysql_num_rows(mysql_query("SELECT `order_session` FROM `orders` WHERE `order_session` = '$session' LIMIT 1"))=='1')echo '1';


?>