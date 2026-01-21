<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');


$query = "SELECT *, COUNT(*) AS ipcount FROM `payment_logs` WHERE `added` >= UNIX_TIMESTAMP(NOW() - INTERVAL 1 DAY) group by ipaddress ORDER BY `id` DESC LIMIT 100";

$q = mysql_query($query);  
$data = ""; 
$count = 0;

if(mysql_num_rows($q) > 0){

    while($info = mysql_fetch_array($q)){
        $count += $info['ipcount'];

        $data .='<tr>
        <td>'. $info['ipaddress'] .'</td>
        <td>'. $info['ipcount'] .'</td>
        <td>'. $info['lastfour'] .'</td>
        <td></td>
        <td>'. date('d/m H:i', $info['added']) .'</td>
        </tr>';
    
    }


}else{

    $data = '<tr style="color:red;    width: 100%;
    text-align: center;
    padding-top: 15px;
    font-size: 20px;"><td colspan = 5>No Records Found! </td></tr>';

}

$tpl = str_replace('{count}',$count,$tpl);
$tpl = str_replace('{data}',$data,$tpl);


output($tpl, $options);
