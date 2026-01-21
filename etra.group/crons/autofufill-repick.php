<?php

include('../sm-db.php');

$now = time();


$q = mysql_query("UPDATE `orders` SET  lambda = '0' WHERE `fulfill_id` = '' AND 
`fulfill_attempt` < '7' AND `next_fulfill_attempt` < $now 
AND `next_fulfill_attempt` != '0' AND `refund` = '0' AND lambda = '3'");

if($q){
    echo "successfully updated lambda 3 to 0";
}
