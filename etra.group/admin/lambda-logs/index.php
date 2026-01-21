<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

// $pageQuery = 'SELECT DISTINCT `page` FROM lambda_logs';
// $resultPage = mysql_query($pageQuery);
$pages = ['autofulfill-lambda', 'refills-lambda', 'autofulfill-free-lambda', 'initiate-tests', 'autolikes-lambda'];
foreach($pages as $page){

    $pages .= '<option>'. $page .'</option>';
}   


$tpl = str_replace('{pages}',$pages, $tpl);

output($tpl, $options);
