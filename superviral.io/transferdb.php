<?php

include('db.php');

die;


$table = 'content'; 





$db1 = 'etra_superviral';
$db2 = 'etra_tikoid';


$limit  = 1000;
 
$query = "SELECT * from $db2.$table limit $limit";
$run_first = mysql_query($query);
 
while ($data = mysql_fetch_array($run_first)) {
 
 
    $sql = "INSERT INTO $db1.$table (
        `brand`,`page`, `country`, `name`, `content`
        )
                SELECT                    
               'to', `page`, `country`, `name`, `content`
                 FROM $db2.$table WHERE id = " . $data['id'];

        


    $insert = mysql_query($sql);
 
    if ($insert) {
        echo 'delete:'.$data['id'] . '<hr>';

        //$lastinsertid = mysql_insert_id();

        //echo 'Last Insert ID'.$lastinsertid.'<br>';
 
        //mysql_query("UPDATE $db2.orders SET `account_id` = '$lastinsertid' WHERE `account_id` = '{$data['id']}'");

        $delQuery = mysql_query("DELETE FROM $db2.$table WHERE id=".$data['id']." LIMIT 1");
    }
}


?>