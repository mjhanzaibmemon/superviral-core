<?php

include('../sm-db.php');

$q = mysql_query("DELETE FROM `ig_api_stats` WHERE `added` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 3 MONTH))");



?>
