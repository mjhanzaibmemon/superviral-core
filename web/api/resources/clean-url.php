<?php

$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';


$checkStmt = mysql_query("SELECT * FROM instagram_profiles");

while ($checkData = mysql_fetch_array($checkStmt)) {

    $url = $checkData['external_url'];
    $domain = getDomainOnly($url);

    $id = $checkData['id'];

    echo "Processing ID: $id - Domain: $domain<br>";

    mysql_query("UPDATE instagram_profiles SET external_url = '$domain' WHERE id = $id");
  
}


function getDomainOnly($url) {
    // Add scheme if missing
    if (!preg_match('#^https?://#', $url)) {
        $url = 'http://' . $url;
    }

    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) return '';

    // Remove leading www.
    $host = preg_replace('/^www\./i', '', $host);

    // Split domain into parts
    $parts = explode('.', $host);

    // Handle domain like: example.co.uk, site.com, etc.
    $count = count($parts);
    if ($count >= 2) {
        $tld = $parts[$count - 1];
        $sld = $parts[$count - 2];

        // Check for 2-level TLDs like co.uk, com.au, etc.
        $common2LevelTLDs = ['co.uk', 'org.uk', 'gov.uk', 'ac.uk', 'com.au', 'co.in'];
        $last2 = $parts[$count - 2] . '.' . $parts[$count - 1];
        if (in_array($last2, $common2LevelTLDs) && $count >= 3) {
            return $parts[$count - 3] . '.' . $last2;
        }

        return $sld . '.' . $tld;
    }

    return $host; // fallback
}
