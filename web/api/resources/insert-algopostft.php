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

$q = mysql_query('SELECT * FROM ext_post_qc WHERE `ft_ready`=1 AND ft_insert=0');
$i = 1;
echo '<pre>';

while ($row = mysql_fetch_array($q)) {

    $post_id = $row['post_id'];
    $post_url = $row['post_url'];
    $post_embed = $row['post_embed'];
    $media_type = $row['media_type'];
    $location = $row['location'];
    $latitude = $row['latitude'];
    $longitude = $row['longitude'];
    $keywords = $row['keywords'];
    $posted_at = $row['posted_at'];
    $video_length = $row['video_length'];

    mysql_query("
        INSERT INTO 
            `algo_post_finetune`
        SET
            `post_id` = {$post_id},
            `post_url` = '{$post_url}',
            `post_embed` = '{$post_embed}',
            `location` = '{$location}',
            `latitude` = '{$latitude}',
            `longitude` = '{$longitude}',
            `media_type` = '{$media_type}',
            `keywords` = '{$keywords}',
            `posted_at` = '{$posted_at}',
            `video_length` = '{$video_length}'
    ");

    mysql_query("UPDATE ext_post_qc SET ft_insert=1 WHERE id=".$row['id']);

}
