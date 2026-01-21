<?php


$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/foodie.app/config/config.php';

$Query_eq = mysql_query("
UPDATE ext_post_qc ep
JOIN ext_socialmedia_posts sp ON ep.post_id = sp.id
JOIN instagram_profiles ip ON sp.username = ip.username
JOIN restaurants r ON ip.restaurant_id = r.id
JOIN ext_uber_restaurants eur ON REPLACE(r.phone, ' ', '') = REPLACE(eur.phone, ' ', '')
SET ep.ue_id = eur.id, ep.location = eur.address, ep.latitude = eur.latitude, ep.longitude = eur.longitude
WHERE r.phone <> ''
AND ip.restaurant_id IN (1750, 2447);
");