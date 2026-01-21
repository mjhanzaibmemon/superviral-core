<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

    // $q = mysql_query("SELECT os.order_session,os.id,p.type, os.igusername FROM order_session os
    //                                             INNER JOIN packages p ON p.id = os.packageid  
    //                                             WHERE DATE(FROM_UNIXTIME(added)) = CURDATE() 
    //                                             AND os.`socialmedia` = 'ig' 
    //                                             AND p.`type` = 'likes'
    //                                             AND igusername != '' ORDER BY ID DESC LIMIT 4");

    // if(mysql_num_rows($q) > 0){

    //     $i = 0;
    //     while($res = mysql_fetch_array($q)){
    
    //         $border = '1px solid grey';

    //         if(!empty($res['igusername']))
    //         {
    //             $loadIframes .= '<iframe style= "border:'. $border .'" id="iframePostGrabberTester" src="https://test.superviral.io/order/select/?ordersession='. $res['order_session'] .'&type=testPG" width="50%" height="450px" frameborder="0"></iframe>';
    //             $i++;

    //         }
    
    //     }

    // }else{

    //     $loadIframes = "No request made today";
    // }
  

// $tpl = str_replace('{loadIframes}', $loadIframes, $tpl);

output($tpl, $options);
