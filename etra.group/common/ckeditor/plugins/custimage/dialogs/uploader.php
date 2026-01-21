<?php

include('../../../../../db.php');

$id = addslashes($_GET['id']);
$pid = addslashes($_GET['pid']);
$tempfile = $_FILES['Filedata']['tmp_name'];


if (!is_uploaded_file($_FILES['Filedata']['tmp_name']))die;

if (!class_exists('S3'))require_once('S31.php');

//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', $amazons3key);
if (!defined('awsSecretKey')) define('awsSecretKey', $amazons3password);

$s3 = new S3(awsAccessKey, awsSecretKey);



$size = getimagesize($tempfile);


if ($_FILES['Filedata']['error'] !== UPLOAD_ERR_OK) {
   $errors[] = "Upload failed with error code " . $_FILES['file']['error'];
}

if ($size === FALSE) {
   $errors[] = "Unable to determine image type of uploaded file. Please try again.";
}


if ($size[2] !== IMAGETYPE_JPEG) {
    $errors[] = "Not a jpeg type image. Please try again.";
}



if(768 < $size[0]){
include('resizer.php');

$ratio = $size[0] / $size[1];
$targetHeight = round(768 / $ratio);



resize_image($tempfile, $tempfile, 768, $targetHeight);
$size[0] = 768;$size[1] = $targetHeight;
}





if(!empty($errors)){

echo '###';
foreach($errors as $error){echo $error.'~~~';}}

else{

mysql_query("INSERT INTO `articles_imgs` SET `pid` = '$pid',`encrypt` = '$id',`width`='{$size[0]}',`height`='$size[1]',`added` = UNIX_TIMESTAMP()");
$filename = $pid.'-'.mysql_insert_id().'.jpg';

	if ($s3->putObjectFile($tempfile , "svstorage", $filename, S3::ACL_PUBLIC_READ)){

	echo 'https://svstorage.s3.amazonaws.com/'.$filename;

	}else{echo "There seems to be a problem. Please contact support for further assistance.~~~'";

	mysql_query("DELETE FROM `articles_imgs` WHERE `pid` = '$pid',`encrypt` = '$id' LIMIT 1");

}

}


unlink($tempfile);

?>