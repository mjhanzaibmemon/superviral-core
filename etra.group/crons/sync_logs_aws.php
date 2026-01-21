<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/sm-db.php';
require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

global $amazons3key, $amazons3password;

$s3 = new S3($amazons3key, $amazons3password);
$bucket = ''; // change later on

$cutoffStart = strtotime('-6 months');
$cutoffEnd = strtotime('-14 days');

$query = "SELECT * FROM lambda_logs WHERE added BETWEEN $cutoffStart AND $cutoffEnd";
$result = mysql_query($query);

$data = [];

while($row = mysql_fetch_array($result)) {
    $data[] = $row;
}
// echo "<pre>";
// print_r($data);die;
if (!empty($data)) {

    $tmpDir = sys_get_temp_dir(); 
    $filename = 'logs_' . date('Y-m-d_H-i-s') . '.json';
    $filePath = $tmpDir . DIRECTORY_SEPARATOR . $filename;

    if (!is_writable($tmpDir)) {
        echo "Temp directory not writable: $tmpDir";
        die;
    }

    file_put_contents($filePath, json_encode($data));

    if (!file_exists($filePath)) {
        echo "Failed to write file: $filePath";
        die;
    }
    // echo $filePath;
    // $content = file_get_contents($filePath);
    // $dataArray = json_decode($content, true);
    
    // print_r($dataArray);
    // die;
    $upload = $s3->putObjectFile($filePath, $bucket, 'etra-live-logs-archive/' . $filename,  S3::ACL_PUBLIC_READ);

    if ($upload) {

        mysql_query("DELETE FROM lambda_logs WHERE added BETWEEN $cutoffStart AND $cutoffEnd");

        // Delete temp file
        unlink($filePath);
        echo "Moved " . count($data) . " logs to S3 and deleted from MySQL.\n";
    } else {
        echo "Upload to S3 failed.\n";
    }
} else {
    echo "No logs found to move.\n";
}


// delete old logs from S3


$prefix = 'etra-live-logs-archive/';
$objects = $s3->getBucket($bucket, $prefix);
$cutoff = strtotime('-6 months');

foreach ($objects as $object) {
    $filename = basename($object['name']); // logs_2024-11-20_10-22-15.json
    if (preg_match('/logs_(\d{4}-\d{2}-\d{2})_\d{2}-\d{2}-\d{2}\.json/', $filename, $match)) {
        $fileDate = strtotime($match[1]);
        if ($fileDate < $cutoff) {
            $s3->deleteObject($bucket, $object['name']);
            echo "Deleted: " . $object['name'] . "\n";
        }
    }
}
