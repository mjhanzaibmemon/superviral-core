<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

if (!empty($_POST['submit'])) {

        $now = time();
        $sql = "UPDATE `admin_statistics` SET `metric` = 0, send_sms = 0 WHERE `type` = 'payment_attempts_per_hour' LIMIT 1";
        $res = mysql_query($sql);
}

if (!empty($_POST['recaptcha'])) {

        $now = time();
        $sql = "UPDATE `admin_statistics` SET `metric` = 50, send_sms = 1 WHERE `type` = 'payment_attempts_per_hour' LIMIT 1";
        $res = mysql_query($sql);
}


if (!empty($_POST['reset24HrPA'])) {

        $now = time();
        $sql = "UPDATE `admin_statistics` SET `metric` = 0 WHERE `type` = 'payment_attempts_per_day' LIMIT 1";
        $res = mysql_query($sql);
}


$sql = "SELECT * FROM `admin_statistics` WHERE `type` = 'payment_attempts_per_hour' LIMIT 1";
$res = mysql_query($sql);
$data = mysql_fetch_array($res);
$counterValue = $data['metric'];

$sql1 = "SELECT * FROM `admin_statistics` WHERE `type` = 'payment_attempts_per_day' LIMIT 1";
$res1 = mysql_query($sql1);
$data1 = mysql_fetch_array($res1);
$counterValuePerDay = $data1['metric'];

$tpl = str_replace('{counterValue}',$counterValue,$tpl);
$tpl = str_replace('{counterValuePerDay}',$counterValuePerDay,$tpl);


output($tpl, $options);
