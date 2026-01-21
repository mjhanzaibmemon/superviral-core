<?php



require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/sm-db.php';


mysql_query("UPDATE `admin_statistics` SET `metric` = 0 WHERE `type` = 'lambda_attempts' LIMIT 1");