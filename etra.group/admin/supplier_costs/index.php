<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

/* 7 DAY LABEL */
$week_unix = strtotime('-7 days');
$dates = [];
for ($i = 0; $i < 7; $i++) {
    $dates[] = date('d/m/y', strtotime("-$i days"));
}
$one_week = implode('","', $dates);
$one_week = '"'.$one_week.'"';

/* DAILY TOTAL COST */

$tpl = file_get_contents('tpl.html');
$tpl = str_replace("{label_7day}",$one_week,$tpl);

output($tpl, $options);
