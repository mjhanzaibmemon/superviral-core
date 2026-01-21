<?php
include('../header.php');

$q = mysql_query('SELECT * FROM orders WHERE packagetype="freefollowers" AND socialmedia="ig" AND packageid=189 AND fulfilled="" LIMIT 300');

while ($info = mysql_fetch_array($q)) {
    $id = $info['id'];
    //echo 'UPDATE orders SET fulfilled='.time().' WHERE id='.$id.' LIMIT 1;';
    mysql_query('UPDATE orders SET fulfilled='.time().' WHERE id='.$id.' LIMIT 1');
}

echo '<meta http-equiv="refresh" content="1">';

?>