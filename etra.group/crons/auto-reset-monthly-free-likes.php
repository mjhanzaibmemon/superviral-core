<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once '../sm-db.php';


$sql = "SELECT * FROM `users` WHERE `monthlyfreelikes` = '1' LIMIT 1";
$res = mysql_query($sql);


if(mysql_num_rows($res) > 0){



                $sql = "UPDATE `users` SET `monthlyfreelikes` = '0' WHERE `monthlyfreelikes` = '1'";
                $res = mysql_query($sql);
                if($res){
                        $msg .= "Successfully reset monthly followers<br>";

                }
                

                
                $sql = "UPDATE `users` SET `donemonthlysearchusername` = '0' WHERE `donemonthlysearchusername` = '1'";
                $res = mysql_query($sql);
                if($res){
                        $msg .= "Successfully reset monthly followers username updates<br>";

                }
    
       
}



echo $msg;

?>
