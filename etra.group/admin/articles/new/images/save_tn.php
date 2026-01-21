<?php
ob_start();
ob_clean(); 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';
require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php'; // if needed

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['image'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Image data is missing']);
    exit;
}

$imageData = $input['image'];
$article_id = $input['article_id'] ?? 0;

if (strpos($imageData, 'data:image/png;base64,') !== 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid image format']);
    exit;
}

$binary_blob = base64_decode(str_replace('data:image/png;base64,', '', $imageData));
if (!$binary_blob) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to decode image']);
    exit;
}


$fileName = md5(time() . uniqid()) . '.jpg';
$s3 = new S3($amazons3key, $amazons3password);
$bucket = 'cdn.superviral.io';
$articleImage = "https://$bucket/media/$fileName";

if (S3::putObject($binary_blob, $bucket, 'media/' . $fileName , S3::ACL_PUBLIC_READ)) {

    $time = time();
    $qq = "UPDATE articles SET `thumb_image` = '$articleImage' WHERE id = '$article_id' limit 1";
    $q = mysql_query($qq);
    
    ob_end_clean();
    echo json_encode(['success' => true, 'url' => $articleImage]);
    die();
} else {

    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'S3 upload failed']);
    die;
}
