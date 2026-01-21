<?php

require_once '../sm-db.php';


echo "======================================START========================================================<br><br>";

$QueryRun = mysql_query("SELECT * FROM payment_logs WHERE DATE(FROM_UNIXTIME(added)) = CURDATE()");
$count = mysql_num_rows($QueryRun);

echo $count;die;
if ($count >= 200) {

    $UpdateQueryRun = mysql_query("UPDATE admin_statistics 
        SET `metric` = 1
        WHERE `type` = 'payment_attempts_per_day' 
        limit 1");

    if ($UpdateQueryRun) {

        echo 'Metric = 1 Successfully set for Superviral payment_attempts_per_day<br><br>';
    }

}

echo "======================================END========================================================<br><br>";
