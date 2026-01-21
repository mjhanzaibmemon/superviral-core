<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// 


require_once '../sm-db.php';



$q2 = mysql_query("SELECT * 
FROM `users` 
WHERE `donemonthlysearchusername` = '0'
AND `source`= 'order' 
ORDER BY `id` DESC 
LIMIT 35;
");

$num_rows = mysql_num_rows($q2);

if ($num_rows==0) {

    $q2 = mysql_query("SELECT * 
    FROM `users` 
    WHERE `donemonthlysearchusername` = '0'
    AND `source`= 'cart' 
    ORDER BY `id` DESC 
    LIMIT 30;
    ");
    
    $num_rows = mysql_num_rows($q2);

}

echo 'Total left:' . mysql_num_rows($q2) . '<hr>';

if ($num_rows==0) {
    die('no more users to search for');
}
 

while($info = mysql_fetch_array($q2)){


        $brand = $info['brand'];

        if ($info['source'] == 'order') {

            $searchordersq = mysql_query("SELECT `igusername`,`emailaddress`,`brand` FROM `orders` WHERE `brand` = '$brand' AND MATCH(`emailaddress`) AGAINST('\"{$info['emailaddress']}\"' IN NATURAL LANGUAGE MODE) ORDER BY `id` DESC LIMIT 1");

            $searchorders = mysql_fetch_array($searchordersq);
        }


        if ($info['source'] == 'cart') {


            $searchordersq = mysql_query("SELECT `igusername`,`emailaddress`,`brand` FROM `order_session` WHERE `brand` = '$brand' AND MATCH(`emailaddress`) AGAINST('\"{$info['emailaddress']}\"' IN NATURAL LANGUAGE MODE) ORDER BY `id` DESC LIMIT 1");

            $searchorders = mysql_fetch_array($searchordersq);
        }



        if ((!empty($searchorders['igusername']))&&(strlen($searchorders['igusername']) < 44) ) {

            $igusernameqinsert = "`monthlyfreeusername` = '{$searchorders['igusername']}',";
        }

        $updatequery = mysql_query("UPDATE `users` SET $igusernameqinsert `donemonthlysearchusername` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");

  
        echo $info['id'].' - '.$info['emailaddress'].': '.$info['source'].' - '.$searchorders['igusername'].'<br>';

        if(!$updatequery){
            

            die("Query failed: UPDATE `users` SET $igusernameqinsert `donemonthlysearchusername` = '1' WHERE `id` = '{$info['id']}' LIMIT 1'");
        
        }


        unset($brand);
        unset($searchorders );
        unset($searchordersq);
        unset($igusernameqinsert);

}

echo '<meta http-equiv="refresh" content="0">';

?>
