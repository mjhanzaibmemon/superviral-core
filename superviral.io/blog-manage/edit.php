<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));
session_start();

require_once  '../db.php';
include_once 'common.php';
require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

if(empty($_SESSION['id'])){
    header("Location: /blog-manage/login.php");
    exit;
}

// $tpl = file_get_contents('editarticle.html');
$tpl = file_get_contents('edit.html');


$id = addslashes($_GET['id']);
$superadminApprove = addslashes($_GET['approve']);
$articleType = addslashes($_GET['articletype']);

if ($id == 'new') {

    $q = mysql_query("INSERT INTO `articles` SET shortdesc='', `article` = '',`published`='1', brand='".addslashes($brand)."', `added_by`='superviral', `article_type`='private'");

    $newid = mysql_insert_id();

    if ($q) {
        header('Location: edit.php?id=' . $newid . '&message=3');
    } else {
        die('Error creating a new row QUERY');
    }

    die;
}

if ($superadminApprove != "" && $superadminApprove != NULL) {


    $q = mysql_query("UPDATE `articles` SET `superadmin_approve` = '".addslashes($superadminApprove)."' WHERE id=$id AND brand = '$brand'");

    $newid = mysql_insert_id();

    if ($q) {
        header('Location: /blog-manage/');
    } else {
        die('Error creating a new row QUERY');
    }

    die;
}


if ($articleType != "" && $articleType != NULL) {


    $q = mysql_query("UPDATE `articles` SET `article_type` = '".addslashes($articleType)."' WHERE id=$id AND brand = '$brand'");

    $newid = mysql_insert_id();

    if ($q) {
        header('Location: /blog-manage/');
    } else {
        die('Error creating a new row QUERY');
    }

    die;
}

if(empty($id)) $id = addslashes($_POST['id']); 
$q = mysql_query("SELECT * FROM `articles` WHERE `id` = '$id' LIMIT 1");
if (mysql_num_rows($q) == '0') {
    exit('DOES NOT EXIST');
}

$info = mysql_fetch_array($q);


$scheduledDate = date('Y-m-d', $info['written']);

if ($scheduledDate == "01-01-1970") {
    $scheduledDate =  date('Y-m-d', time());
}

$scheduledLater = 0;
if($info['written'] > time()){
    $scheduledLater = 1;
}

if ($info['superadmin_approve'] == '1' && $scheduledLater == 0) {

    $live = '<div class="status box23" style="    background: #82fd82;">LIVE</div>';
    $showurl = '	<div class="label labelcontact">URL:</div>
	<input class="input inputcontact" autocomplete="off" name="url" value="' . $info['url'] . '" style="background:#eee">';

    if(!empty($_POST['url']))
    $urlquery = "`url` = '{$_POST['url']}', ";
} else if($info['superadmin_approve'] == '1' && $scheduledLater == 1){

    $live = '<div class="status box23" style="width: 85px;background:Orange">'. $scheduledDate .'</div>';
   
}else{
    $live = '<div class="status box23" style="    background: #ccc;
    ">DRAFT</div>';
}

if ($_GET['message'] == '1') {
    $message = '<div class="emailsuccess box23">Article successfully saved.</div>';
}
if ($_GET['message'] == '2') {
    $message = '<div class="emailsuccess box23">Article published and gone live.</div>';
}
if ($_GET['message'] == '3') {
    $message = '<div class="emailsuccess box23">Created a new article.</div>';
}
if ($_GET['message'] == '4') {
    $message = '<div class="emailsuccess box23">Uploaded Successfully.</div>';
}
if ($_GET['message'] == '5') {
    $message = '<div class="emailsuccess box23">Removed Successfully.</div>';
}

if ($_POST['delete'] == 'Delete') {
    
    $id = $_POST['id'] ? $_POST['id'] : $_GET['id'];

    $q = mysql_query("DELETE FROM `articles` WHERE `id` = '$id' LIMIT 1");

    if ($q) {
        header('Location: /blog-manage/?&message=1');
        die;
    }
}

if (isset($_GET['aiid']) && !empty($_GET['aiid'])) {

    $aiid = addslashes($_GET['aiid']);
    $q = mysql_query("DELETE FROM `articles_imgs` WHERE `id` = '$aiid' LIMIT 1");

    if ($q) {
        header('Location: edit.php?id=' . $id . '&message=5');
        die;
    }
}


if ($_POST['save'] == 'Save') {

    $_POST['title'] = trim($_POST['title']);
    $_POST['meta_title'] = trim($_POST['meta_title']);

    $url = empty($info['url']) ? $_POST['title']: trim($_POST['url']);
    $url = create_seo_link($url);

    $_POST['article'] = str_replace('width="undefined"', '', $_POST['article']);
    $_POST['article'] = str_replace('height="undefined"', '', $_POST['article']);
    $_POST['article'] = preg_replace('/\s*style\s*=\s*"[^"]*"/i', '', $_POST['article']);

    $input_arr = array();
    foreach ($_POST as $key => $input_arr) {
        $_POST[$key] = addslashes($input_arr);
    }

    $qq = "UPDATE `articles` SET 
	`country` = 'us',
	`title` = '".addslashes($_POST['title'])."',
	`meta_title` = '".addslashes($_POST['meta_title'])."',
    `h1` = '".addslashes($_POST['title'])."',
	`shortdesc` = '".addslashes($_POST['shortdesc'])."',
	`url` = '".addslashes($url)."',
    `published`='1',
    `superadmin_approve`='1',
    `article_type`='public',
	`summary1` = '".addslashes($_POST['summary1'])."',
	`summary2` = '".addslashes($_POST['summary2'])."',
	`summary3` = '".addslashes($_POST['summary3'])."',
	`mainimg` = '".addslashes($_POST['mainimg'])."',
	`article` = '".addslashes($_POST['article'])."', 
	`added_by` = 'superviral',
	`author` = 'The Superviral Team', 
	`author_description` = '".addslashes($_POST['author_description'])."', ";
  
    if (!empty($_POST['date'])) { 
        $written = strtotime($_POST['date']);
        $qq .= " `written` = '$written', "; 
    }
    
    $qq .= " `brand` = 'sv' WHERE `id` = '$id' LIMIT 1";

    $q = mysql_query($qq);

    if ($q) {
        $idsParam = isset($_GET['ids']) ? $_GET['ids'] : '';
        $idList = array_filter(array_map('intval', explode(',', $idsParam)));

        $currentIndex = array_search((int)$id, $idList);

        if ($currentIndex !== false && isset($idList[$currentIndex + 1])) {
            $nextId = $idList[$currentIndex + 1];

            header('Location: edit.php?id=' . $nextId . '&ids=' . implode(',', $idList));
        } else {
            // echo '<script>alert(\'All done successfully.\');</script>';
            header('Location: /blog-manage/');
        }

        // header('Location: edit.php?id=' . $id . '&message=1');
        die;
    }
}

if($_POST['submit'] == 'authorImage'){

    $authorImage = "";

    if (!empty($_FILES['author_image']['name'])) {

        $fileName = md5(time()) . '.jpg';

        $author_temp_file = $_FILES["author_image"]["tmp_name"];
        $binary_blob = file_get_contents($author_temp_file);
        $imageFileType = strtolower(pathinfo($_FILES["author_image"]["name"], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'png', 'jpeg'];

        if (!in_array($imageFileType, $allowedTypes)) {
            $msg = "File type is not allowed";
            echo $msg;
            die;
        } // Check if file already exists
        if ($_FILES["author_image"]["size"] > 5000000) {
            $msg = "Sorry, your file is too large.";
            echo $msg;
            die;
        }
        
        $s3 = new S3($amazons3key, $amazons3password);

        if ( S3::putObject($binary_blob, 'cdn.superviral.io', 'media/' . $fileName , S3::ACL_PUBLIC_READ)) {
            $authorImage = 'https://cdn.superviral.io/media/' . $fileName ;
            sendCloudwatchData('EtraGroupAdmin', 's3-image-upload-success', 'EditArticle', 's3-image-upload-success-function', 1);
        }else{
            sendCloudwatchData('EtraGroupAdmin', 's3-image-upload-failure', 'EditArticle', 's3-image-upload-failure-function', 1);
        }

        if(!empty($authorImage)){
            $qq = "UPDATE `articles` SET `author_image` = '{$authorImage}' WHERE `id` = '$id' LIMIT 1 "; 
            $q = mysql_query($qq);
        }
        $img = '<img src="' . $authorImage . '" alt="" style="width: 150px;height: 150px; margin-top: 10px;margin-right:15px;">';
        echo $img;die;
    }
      

}

if($_POST['submit'] == 'thumbImage'){

    $thumbImage = "";

    if (!empty($_FILES['article_tn']['name'])) {

        $fileName = md5(time()) . '.jpg';

        $tn_temp_file = $_FILES["article_tn"]["tmp_name"];
        $binary_blob = file_get_contents($tn_temp_file);
        $imageFileType = strtolower(pathinfo($_FILES["article_tn"]["name"], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'png', 'jpeg'];

        if (!in_array($imageFileType, $allowedTypes)) {
            $msg = "File type is not allowed";
            echo $msg;
            die;
        } // Check if file already exists
        if ($_FILES["article_tn"]["size"] > 5000000) {
            $msg = "Sorry, your file is too large.";
            echo $msg;
            die;
        }
        $s3 = new S3($amazons3key, $amazons3password);

        if ( S3::putObject($binary_blob, 'cdn.superviral.io', 'media/' . $fileName , S3::ACL_PUBLIC_READ)) {
            $thumbImage = 'https://cdn.superviral.io/media/' . $fileName ;
            sendCloudwatchData('EtraGroupAdmin', 's3-image-upload-success', 'EditArticle', 's3-image-upload-success-function', 1);

        }else{
            sendCloudwatchData('EtraGroupAdmin', 's3-image-upload-failure', 'EditArticle', 's3-image-upload-failure-function', 1);

        }
        if(!empty($thumbImage)){
            $qq = "UPDATE `articles` SET `thumb_image` = '{$thumbImage}' WHERE `id` = '$id' LIMIT 1 "; 
            $q = mysql_query($qq);
        }

        $img = '<img src="' . $thumbImage . '" alt="" style="width: 150px;height: 150px; margin-top: 10px;margin-right:15px;">';
        echo $img;die;
    }
    
}

if ($_POST['submit'] == 'Publish') {

    $_POST['title'] = trim($_POST['title']);

    $url = create_seo_link($_POST['title']);

    $written = time();

    if (!empty($_POST['date'])) {
        $written = strtotime($_POST['date']);
    }

    $q = mysql_query("UPDATE `articles` SET 
	`title` = '{$_POST['title']}', 
    `h1` = '{$_POST['title']}',
	`url` = '$url',
	`written` = '$written',
	`published` = '1' 
	WHERE `id` = '$id' AND brand = '$brand' LIMIT 1");

    if ($q) {
        header('Location: edit.php?id=' . $id . '&message=2');
        die;
    }
}

$articleImages = '';
$articleImage = '';
if ($_POST['submit'] == 'Upload') {
 
    if (!empty($_FILES['article_image']['name'])) {

        $chkArticleImageCount = mysql_num_rows(mysql_query('SELECT 1 FROM articles_imgs WHERE pid=' . $id));

        $newCount = intval($chkArticleImageCount) + 1;
        $fileName = md5(time()) .'_' . $id . '_' . $newCount . '.jpg';

        $article_temp_file = $_FILES["article_image"]["tmp_name"];
        $binary_blob = file_get_contents($article_temp_file);
        $imageFileType = strtolower(pathinfo($_FILES["article_image"]["name"], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'png', 'jpeg'];

        if (!in_array($imageFileType, $allowedTypes)) {
            $msg = "File type is not allowed";
            echo $msg;
            die;
        } // Check if file already exists
        if ($_FILES["article_image"]["size"] > 2097152) {
            $msg = "Image Size is too large, allowed 2MB file only";
            echo $msg;
            die;
        }
        $s3 = new S3($amazons3key, $amazons3password);

        if ( S3::putObject($binary_blob, 'cdn.superviral.io', 'media/' . $fileName , S3::ACL_PUBLIC_READ)) {
            $articleImage = 'https://cdn.superviral.io/media/' . $fileName ;
            sendCloudwatchData('EtraGroupAdmin', 's3-image-upload-success', 'EditArticle', 's3-image-upload-success-function', 1);

        }else{
            sendCloudwatchData('EtraGroupAdmin', 's3-image-upload-failure', 'EditArticle', 's3-image-upload-failure-function', 1);

        }
    }
    $time = time();
    $qq = "INSERT INTO articles_imgs SET deleteit = 0, `height` =0, `width` =0, `encrypt` = '',pid = '".addslashes($id)."', added = '".addslashes($time)."', brand = '".addslashes($brand)."', `image` = '$articleImage'";

    $q = mysql_query($qq);

}

$imgQ = mysql_query("SELECT * FROM `articles_imgs` WHERE `pid` = '$id' and `image` IS NOT NULL");

while ($articleImg = mysql_fetch_array($imgQ)) {

    $articleImages .=  '<div class="mainDiv">
                            <div class="info">
                              <a style="color: #FFF; font-size: 14px; text-decoration: none;" onclick="copy(' . $articleImg['id'] . ');return false;">Copy</a>
                              <input id="copyImg' . $articleImg['id'] . '" type="text" style="display:none;" value="' . $articleImg['image'] . '">
                              <a style="color: #FFF; font-size: 14px; text-decoration: none;" href="?id=' . $id . '&aiid=' . $articleImg['id'] . '" onclick="return confirm(\'Are you sure you want to delete?\');">Delete</a>
                            </div>
                            <div class="image">
                            <img src="' . $articleImg['image'] . '" alt="" style="width: 150px;height: 150px;">
                            </div>
                        </div>';
}

if($_POST['submit'] == 'Upload'){
    echo $articleImages;die;
}


$publishBtn = "";
$publishInput = "";
// if ($_SESSION['first_name'] == 'rabban' || $_SESSION['first_name'] == 'mac') {

/*    $publishBtn =  '<input type="submit" class="btn btn-primary color3" name="submit" value="Publish" style="float:right;  margin-top: 15px;">';*/
    $publishInput = ' <div class="label labelcontact">Publish Date:</div>
                    <div style="max-width:200px;"><input class="input inputcontact" type="date" name="date" value="'. $scheduledDate .'"></div>';
// }

if ($info['country'] != '') {
    $tpl = str_replace('<option value="' . $info['country'] . '"', '<option value="' . $info['country'] . '" selected', $tpl);
} else {
    $tpl = str_replace('<option disabled', '<option disabled selected', $tpl);
}

if(isset($_GET['ids'])){
    $saveBtnCss = "display:none";
    $deleteBtnCss = "display:none";
}

$vectorQ = mysql_query("SELECT * FROM `articles_vector_image` WHERE `article_id` = '$id'");
$vectorImages = '';
$i = 0;
while ($vectorImg = mysql_fetch_array($vectorQ)) {

    if ($i == 0) {
        $firstVectorImage = $vectorImg['blob'];
    }
    $vectorImages .=  ' <div class="image-option" style="background:#000;">
                            <img src="' . $vectorImg['blob'] . '" alt="Image">
                        </div>';
    $i++;
}

$tpl = str_replace('{title}', stripslashes($info['title']), $tpl);
$tpl = str_replace('{live}', $live, $tpl);
$tpl = str_replace('{message}', $message, $tpl);
$tpl = str_replace('{id}', $info['id'], $tpl);
$tpl = str_replace('{url}', $info['url'], $tpl);
$tpl = str_replace('{shortdesc}', stripslashes($info['shortdesc']), $tpl);
$tpl = str_replace('{summary1}', stripslashes($info['summary1']), $tpl);
$tpl = str_replace('{summary2}', stripslashes($info['summary2']), $tpl);
$tpl = str_replace('{summary3}', stripslashes($info['summary3']), $tpl);
$tpl = str_replace('{mainimg}', $info['mainimg'], $tpl);
$tpl = str_replace('{author}', stripslashes($info['author']), $tpl);
$tpl = str_replace('{author_description}', stripslashes($info['author_description']), $tpl);
$tpl = str_replace('{article}', stripslashes($info['article']), $tpl);
// $tpl = str_replace('{author_image}',$authorImageDisplay, $tpl);
$tpl = str_replace('{publishBtn}', $publishBtn, $tpl);
$tpl = str_replace('{publishInput}', $publishInput, $tpl);
$tpl = str_replace('{date}', $scheduledDate, $tpl);
$tpl = str_replace('{meta_title}', $info['meta_title'], $tpl);
$tpl = str_replace('{articleImages}', $articleImages, $tpl);
$tpl = str_replace('{saveBtnCss}', $saveBtnCss, $tpl);
$tpl = str_replace('{deleteBtnCss}', $deleteBtnCss, $tpl);
$tpl = str_replace('{timenow}', time(), $tpl);
$tpl = str_replace('{loc}', $info['country'] == 'uk' ? '/uk' :'', $tpl);
$tpl = str_replace('{vectorImages}', $vectorImages, $tpl);
$tpl = str_replace('{firstVectorImage}', $firstVectorImage, $tpl);

if (!empty($info['author_image'])) {
    $img = '<img src="' . $info['author_image'] . '" alt="" style="width: 150px;height: 150px; margin-top: 10px;margin-right:15px;">';
    $tpl = str_replace('{authorImg}', $img, $tpl);
} else {
    $tpl = str_replace('{authorImg}', '', $tpl);
}

if (!empty($info['thumb_image'])) {
    $img = '<img src="' . $info['thumb_image'] . '" alt="" style="width: 150px;height: 150px; margin-top: 10px;margin-right:15px;">';
    $tpl = str_replace('{articleTN}', $img, $tpl);
} else {
    $tpl = str_replace('{articleTN}', '', $tpl);
}

output($tpl, $options);


function create_seo_link($text)
{
    $letters = array(
        '–', '—', '\'', '\'', '\'',
        '«', '»', '&', '÷', '>',    '<',  '/'
    );

    $nospace = array(':', ';', ',', '"', '"', '"', '$', '£', '|', '(', ')',"'",'’');

    $text = str_replace($letters, " ", $text);
    $text = str_replace($nospace, "", $text);
    $text = str_replace("&", "and", $text);
    $text = str_replace("?", "", $text);
    $text = strtolower(str_replace(" ", "-", $text));

    $cleanedInput = cleanUrlString($text);

    return ($cleanedInput);
}

function cleanUrlString($string) {
    // Define a regex pattern that matches only URL-safe characters
    $pattern = '/[^a-zA-Z0-9-_.~]+/';

    $cleanedString = preg_replace($pattern, '', $string);

    return $cleanedString;
}
