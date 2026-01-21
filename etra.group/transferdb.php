<?php

die;

$db1 = 'etra_superviral';
$db2 = 'etra_tikoid';
$table = 'reviews'; 

$limit  = 1;
 
$query = "SELECT * from $db2.$table limit $limit";
$run_first = mysql_query($query);
 
while ($data = mysql_fetch_array($run_first)) {
 
 
    $sql = "INSERT INTO $db1.t$tikable (`id`, `brand`, `country`, `type`, `name`, `location`, `title`, `review`, `timeo`, `approved`)
                SELECT                    
                `id`, `brand`, `country`, `type`, `name`, `location`, `title`, `review`, `timeo`, `approved` FROM $db2.orders WHERE id = " . $data['id'];
 
    $insert = mysql_query($sql);
 
    if ($insert) {
        echo 'delete:'.$data['id'] . '<hr>';
 
        $delQuery = mysql_query("DELETE FROM $db2.$table WHERE id=".$data['id']);
    }
}


?>