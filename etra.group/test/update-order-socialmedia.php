<?php


include('../sm-db.php');


// order session
$q = mysql_query("SELECT * FROM order_session where `brand` = 'sv' AND socialmedia IS NULL ORDER BY id DESC LIMIT 100");

while ($info = mysql_fetch_array($q)) {

    echo "UPDATE order_session SET socialmedia = 'ig' WHERE id = ". $info['id']." LIMIT 1<hr>";
    $res = mysql_query("UPDATE order_session SET socialmedia = 'ig' WHERE id = ". $info['id'] ." LIMIT 1");

}


// orders
$q = mysql_query("SELECT * FROM orders where `brand` = 'sv' AND socialmedia IS NULL ORDER BY id DESC LIMIT 100");
while ($info = mysql_fetch_array($q)) {

    echo "UPDATE orders SET socialmedia = 'ig' WHERE id = ". $info['id']." LIMIT 1<hr>";
    $res = mysql_query("UPDATE orders SET socialmedia = 'ig' WHERE id = ". $info['id'] ." LIMIT 1");

}

if(mysql_num_rows($q) == 0){die;}
echo '<meta http-equiv="refresh" content="1">';

?>