<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');
$options = [];

if (isset($_GET['no_header'])) {
    $options['no_header'] = 1;
}

$snippet = tpl_get('item', $tpl);
$html = '';
for ($i = 0; $i < 10; $i++) {
    $temp = $snippet;
    $temp = str_replace('{title}', 'Title ' . $i, $temp);
    $html .= $temp;
}
$tpl = tpl_replace('item', $html, $tpl);


output($tpl, $options);
