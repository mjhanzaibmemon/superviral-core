<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);     

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$sql_ago = time() - (6 * 60 * 60);
$three_days_ago = time() - (3 * 24 * 60 * 60);

$q = mysql_query("SELECT * FROM `orders` WHERE `fulfilled` = 0 AND `lambda` = '3' AND `added` <= {$sql_ago} AND `added` >= {$three_days_ago} AND packagetype != 'freelikes' AND packagetype != 'freefollowers' ORDER BY `id` ASC");

while($info = mysql_fetch_array($q)) {
    $package_q = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}'");
    $packageinfo = mysql_fetch_array($package_q);

    $added_ago = dateDiff($info['added']);
    $info['price'] = sprintf('%.2f', $info['price'] / 100);

    $articles .= '<div class="defectorder">
        <div class="sdiv">
            <b><a target="_BLANK" href="/admin/check-user/?orderid='.$info['id'].'#order'.$info['id'].'">'.$info['id'].'</a></b>
            <br>£'.$info['price'].' - '.$info['amount'].' '.$info['packagetype'].' '. $packageinfo['jap1'] .'
            <br>'.$added_ago.'
        </div>
    </div>';

    $service_ids[] = $packageinfo['jap1'];
    $order_ids[] = str_replace(' ','<br>',$info['fulfill_id']);
}   

$service_ids = array_unique($service_ids);
$service_ids = implode('<br>', $service_ids);
$fulfill_ids = implode('<br>', $order_ids);

$tpl = file_get_contents('tpl.html');
$tpl = str_replace('{message}',$message,$tpl);
$tpl = str_replace('{service_ids}',$service_ids,$tpl);
$tpl = str_replace('{fulfill_ids}',$fulfill_ids,$tpl);
$tpl = str_replace('{articles}',$articles,$tpl);
$tpl = str_replace('{autojs}',$autojs,$tpl);

output($tpl, $options);

function dateDiff($date)
{
    $now = date("Y-m-d H:i:s");
    $date = date("Y-m-d H:i:s",$date);
    
    $datetime1 = date_create($date);
    $datetime2 = date_create($now);
    $interval = date_diff($datetime1, $datetime2);

    $isFuture = $datetime1 > $datetime2; // True if date is in the future

    $min = $interval->format('%i');
    $sec = $interval->format('%s');
    $hour = $interval->format('%h');
    $day = $interval->format('%d');
    $mon = $interval->format('%m');
    $year = $interval->format('%y');

    $prefix = $isFuture ? "Next attempt in " : "Added ";
    $suffix = $isFuture ? "" : " ago";

    if ($interval->format('%i%h%d%m%y') == "00000") {
        return $prefix . $sec . " seconds" . $suffix;
    } else if ($interval->format('%h%d%m%y') == "0000") {
        return $prefix . $min . " minutes" . $suffix;
    } else if ($interval->format('%d%m%y') == "000") {
        return $prefix . $hour . " hours" . $suffix;
    } else if ($interval->format('%m%y') == "00") {
        return $prefix . $day . " days" . $suffix;
    } else if ($interval->format('%y') == "0") {
        return $prefix . $mon . " months" . $suffix;
    } else {
        return $prefix . $year . " years" . $suffix;
    }
}
