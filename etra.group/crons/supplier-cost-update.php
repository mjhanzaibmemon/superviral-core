<?php


$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once '../sm-db.php';


$checkQuery = mysql_query(
    "SELECT * 
FROM `supplier_cost` 
WHERE (`cost` + 0 = 0 AND `cost` LIKE '0%') OR `cost` = '' OR `cost` IS NULL
;");



while($data = mysql_fetch_array($checkQuery)){

    $id = $data['id'];
    $type = $data['type'];
    $amount = $data['amount'];
    $page = $data['page'];
    $socialmedia = $data['socialmedia'];
    $brand = $data['brand'];

    echo "<br>";

    echo $id .' '. $type .' '. $amount .' '. $page .' '. $socialmedia .' '. $brand; 

    $getCost = mysql_fetch_array(mysql_query("SELECT * FROM `supplier_cost` WHERE `type` = '$type' AND amount = '$amount' AND `page` = '$page' AND `socialmedia` = '$socialmedia' AND `brand` = '$brand' AND (`cost` + 0) <> 0  ORDER BY `id` DESC LIMIT 1"));

    $newCost = $getCost['cost'];

    if(!empty($newCost)){
        mysql_query("UPDATE `supplier_cost` SET `cost` = '$newCost' WHERE `id` = '$id'");
    }
}

echo '<br>Done';
