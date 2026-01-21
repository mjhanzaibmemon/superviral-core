<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

$sql = "SELECT * FROM `users` WHERE `monthlyfreefollowers` = '1' LIMIT 1";
$res = mysql_query($sql);

$msg = "";
$disabled = 'disabled style="opacity:0.5"';
if(mysql_num_rows($res) > 0){

        $disabled = '';
        if (!empty($_POST['submit'])) {

                $now = time();
                $sql = "UPDATE `users` SET `monthlyfreefollowers` = '0' WHERE `monthlyfreefollowers` = '1'";
                $res = mysql_query($sql);
                if($res){
                        $msg = "Successfully reset";
                        $disabled = 'disabled style="opacity:0.5"';
                }
                
        }
       
}



$tpl = str_replace('{msg}',$msg,$tpl);
$tpl = str_replace('{disabled}',$disabled,$tpl);


output($tpl, $options);
