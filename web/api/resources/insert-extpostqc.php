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

$Query_eq = mysql_query('SELECT * FROM ext_socialmedia_posts WHERE `qc`=0 AND `caption` != "" AND `username` IN ("shakeshackuk","chillichicksuk") LIMIT 100');
$i = 1;
echo '<pre>';

while ($row = mysql_fetch_array($Query_eq)) {
    $profile_row = get_profile($row['username']);
    $restaurant_row = get_restaurant($profile_row['restaurant_id']);

    $post_id = $row['id'];
    $post_url = $row['video_url'];
    $media_type = $row['media_type'];
    $caption = addslashes($row['caption']);
    $posted_at = $row['posted_at'];
    $video_length = $row['video_length'];

    mysql_query("
        INSERT INTO 
            `ext_post_qc`
        SET
            `post_id` = {$post_id},
            `post_url` = '{$post_url}',
            `media_type` = '{$media_type}',
            `caption` = '{$caption}',
            `posted_at` = {$posted_at},
            `video_length` = '{$video_length}'
    ");

    mysql_query("UPDATE ext_socialmedia_posts SET qc=1 WHERE `id` = {$post_id}");
}

function get_profile($username){
    $q = mysql_query("SELECT * FROM `instagram_profiles` WHERE `username`='".addslashes($username)."' LIMIT 1");
    return mysql_fetch_array($q);
}

function get_restaurant($id){
    $q = mysql_query("SELECT * FROM `ext_restaurants` WHERE `id`='".$id."' LIMIT 1");
    return mysql_fetch_array($q);
}
