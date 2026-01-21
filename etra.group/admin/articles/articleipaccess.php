<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

include  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('articleipaccess.html');

$ip = addslashes($_POST['ip']);
$name = addslashes($_POST['name']);
$create = addslashes($_POST['submit']);
// insert
if(isset($create) && !empty($create)){
    if((!empty($ip) && !empty($name))){

        $query = "INSERT INTO `articles_ipaddress` SET 
        `ip` = '$ip', `name` = '$name'";

        $q = mysql_query($query);  
        if($q){
            
            $msg ='<div style="color:green;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">Added Successfully!!</div>';

        }else{

            $msg ='<div style="color:red;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">Something went wrong!!</div>';
        }
    }
    else{
        
            $msg ='<div style="color:red;    width: 100%;
            text-align: center;
            padding-top: 15px;
            font-size: 20px;">Inputs can\'t be blank</div>';
    }

}

$id = addslashes($_GET['id']);
if(!empty($_GET['id'])){

    $delete = mysql_query("DELETE FROM `articles_ipaddress` WHERE id = $id");
}

$dataQuery = mysql_query("SELECT * from `articles_ipaddress` order by id desc");
$data = '';
while($val = mysql_fetch_array($dataQuery)){
    $data .= '<tr>';
    $data .= '<td>'. $val['name'] .'</td>';
    $data .= '<td>'. $val['ip'] .'</td>';
    $data .= '<td><a href="?id='. $val['id'] .'"  onclick="return confirm(\'Are you sure you want to delete?\');">Delete</a></td></tr>';
}

$tpl = str_replace('{msg}',$msg, $tpl);
$tpl = str_replace('{data}',$data, $tpl);

output($tpl, $options);
