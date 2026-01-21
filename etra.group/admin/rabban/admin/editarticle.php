<?php


include('adminheader.php');

$id = addslashes($_GET['id']);
$superadminApprove = addslashes($_GET['approve']);
$articleType = addslashes($_GET['articletype']);

if($id=='new'){

$q= mysql_query("INSERT INTO `articles` SET `article` = ''");

$newid = mysql_insert_id();

if($q){header('Location: /admin/editarticle.php?id='.$newid.'&message=3');}
else{die('Error creating a new row QUERY');}

die;

}


if($superadminApprove != "" && $superadminApprove != NULL){


	$q= mysql_query("UPDATE `articles` SET `superadmin_approve` = '$superadminApprove' WHERE id=$id");
	
	$newid = mysql_insert_id();
	
	if($q){header('Location: /admin/article.php');}
	else{die('Error creating a new row QUERY');}
	
	die;
	
}


if($articleType != "" && $articleType != NULL){


	$q= mysql_query("UPDATE `articles` SET `article_type` = '$articleType' WHERE id=$id");
	
	$newid = mysql_insert_id();
	
	if($q){header('Location: /admin/article.php');}
	else{die('Error creating a new row QUERY');}
	
	die;
	
}

$q = mysql_query("SELECT * FROM `articles` WHERE `id` = '$id' LIMIT 1");
if(mysql_num_rows($q)=='0'){exit('DOES NOT EXIST');}

$info = mysql_fetch_array($q);

if($info['published']=='1'){

$live = '<div class="status" style="    background: #82fd82;">LIVE</div>';
$showurl = '	<div class="label labelcontact">URL:</div>
	<input class="input inputcontact" autocomplete="off" name="url" value="'.$info['url'].'" style="background:#eee">';

$urlquery = "`url` = '{$_POST['url']}', ";

}else{

$live = '<div class="status" style="    background: #ccc;
   ">DRAFT</div>';

}

if($_GET['message']=='1'){$message = '<div class="emailsuccess">Article successfully saved.</div>';}
if($_GET['message']=='2'){$message = '<div class="emailsuccess">Article published and gone live.</div>';}
if($_GET['message']=='3'){$message = '<div class="emailsuccess">Created a new article.</div>';}

if($_POST['submit']=='Delete'){

$q = mysql_query("DELETE FROM `articles` WHERE `id` = '$id' LIMIT 1");

if($q){header('Location: /admin/article.php?&message=1');die;}


}

if($_POST['submit']=='Save'){
	
$_POST['article'] = str_replace('width="undefined"','',$_POST['article']);
$_POST['article'] = str_replace('height="undefined"','',$_POST['article']);


$input_arr = array();
foreach ($_POST as $key => $input_arr) {
$_POST[$key] = addslashes($input_arr);
} 

$authorImage = "";

if(!empty($_FILES['author_image']['name'])) {
	$target_dir = "../imgs/blog/author/";
	$fileName = md5(time());

	if (!file_exists($target_dir)) {
		mkdir($target_dir, 0777, true);
	}

	$imageFileType = strtolower(pathinfo($_FILES["author_image"]["name"], PATHINFO_EXTENSION));
	$target_file = $target_dir . $fileName .'.' . $imageFileType;
	$allowedTypes = ['jpg', 'png', 'jpeg'];

	if (!in_array($imageFileType, $allowedTypes)) {
	    $msg = "File type is not allowed";
		echo $msg;die;
	} // Check if file already exists
	if ($_FILES["author_image"]["size"] > 5000000) {
	    $msg = "Sorry, your file is too large.";
		echo $msg;die;
	} 
	if (move_uploaded_file($_FILES["author_image"]["tmp_name"], $target_file)) {
	    $authorImage = $target_file;
	}
	
}

$q = mysql_query("UPDATE `articles` SET 
	`title` = '{$_POST['title']}',
	`shortdesc` = '{$_POST['shortdesc']}',
	$urlquery
	`summary1` = '{$_POST['summary1']}',
	`summary2` = '{$_POST['summary2']}',
	`summary3` = '{$_POST['summary3']}',
	`mainimg` = '{$_POST['mainimg']}',
	`article` = '{$_POST['article']}', 
	`added_by` = '{$_SESSION['admin_user']}',
	`author` = '{$_POST['author_name']}', 
	`author_description` = '{$_POST['author_description']}', 
	`author_image` = '{$authorImage}',
	`article_type` = 'private' 
	WHERE `id` = '$id' LIMIT 1");

if($q){header('Location: /admin/editarticle.php?id='.$id.'&message=1');die;}


}


if($_POST['submit']=='Publish'){

function create_seo_link($text) {
    $letters = array(
        '–', '—', '\'', '\'', '\'',
        '«', '»', '&', '÷', '>',    '<',  '/'
    );

    $nospace = array(',','"', '"', '"','$','£','|','(',')');

    $text = str_replace($letters, " ", $text);
    $text = str_replace($nospace, "", $text);
    $text = str_replace("&", "and", $text);
    $text = str_replace("?", "", $text);
    $text = strtolower(str_replace(" ", "-", $text));

    return ($text);
}

$url = create_seo_link($_POST['title']);

$written = time();

$q = mysql_query("UPDATE `articles` SET 
	`title` = '{$_POST['title']}', 
	`url` = '$url',
	`written` = '$written',
	`published` = '1' 
	WHERE `id` = '$id' LIMIT 1");

if($q){header('Location: /admin/editarticle.php?id='.$id.'&message=2');die;}

}

?>
<!DOCTYPE html>
<head>
<title>Edit or Add Article</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">

<style type="text/css">

.box23{margin: 66px auto;
    width: 950px;
    background: #fff;
    border-radius: 5px;text-align:left;padding:15px;}

h1{text-align: left;max-width:100%;}

.label{margin-top:35px;}

.container div input, .selectric, .input, .btn {padding: 13px;font-size: 14px;}

.btn{width:100px;text-align:center;}

html{overflow-x: hidden;}

.cke_reset_all{background:#f7f7f7!important;}

.status{ font-weight: bold;
    height: 23px;
    width: 55px;
    padding: 5px;font-size:15px;text-align:center;border-radius:3px;margin-bottom:10px;}

.textareacontact{font-family:'Open Sans';}

.imgcaption{background-color:#ccc;padding:5px;font-weight:600;font-size:15px;}

</style>
<script src="ckeditor/ckeditor.js"></script>
</head>

	<body>


		<?=$header?>

		<div class="box23">

			<a href="/admin/article.php">&laquo; Back</a>

		</div>

		<form method="POST" enctype="multipart/form-data">
		<div class="box23">
	
	<h1>Edit: <?=$info['title']?></h1>
	<?=$live?>
	<?=$message?>
	<hr>
	<input type="hidden" name="id" value="<?=$info['id']?>">
	<div class="label labelcontact">Your title:</div>
	<input class="input inputcontact" autocomplete="off" name="title" value="<?=$info['title']?>">

	<div class="label labelcontact">Description:</div>
	<textarea class="input inputcontact textarea textareacontact" name="shortdesc"><?=$info['shortdesc']?></textarea>

	<?=$showurl?>

	<div class="label labelcontact">Summary 1:</div>
	<input class="input inputcontact" autocomplete="off" name="summary1" value="<?=$info['summary1']?>">

	<div class="label labelcontact">Summary 2:</div>
	<input class="input inputcontact" autocomplete="off" name="summary2" value="<?=$info['summary2']?>">

	<div class="label labelcontact">Summary 3:</div>
	<input class="input inputcontact" autocomplete="off" name="summary3" value="<?=$info['summary3']?>">

	<div class="label labelcontact">Image name:</div>
	<input class="input inputcontact" autocomplete="off" name="mainimg" value="<?=$info['mainimg']?>">

	<div class="label labelcontact">Author name:</div>
	<input class="input inputcontact" autocomplete="off" name="author_name" value="<?=$info['author']?>">
	
	<div class="label labelcontact">Author description:</div>
	<input class="input inputcontact" autocomplete="off" name="author_description" value="<?=$info['author_description']?>">

	<div class="label labelcontact">Author image:</div>
	<input class="input inputcontact" type="file" name="author_image">
	<?php if(!empty($info['author_image'])){ ?>
	<img src="<?=$info['author_image']?>" alt="" style="width: 100px;height: 100px; margin-top: 10px;">
	<?php } ?>	

	<div class="label labelcontact">Article:</div>
	<textarea class="ckeditor input inputcontact textarea textareacontact" cols="80" id="editor1" name="article" rows="10"><?=stripslashes($info['article'])?></textarea>

	<div style="height:330px;position:relative;">

		<input type="submit" class="btn color3" name="submit" value="Save" style="float:left;">
		<?php if($_SESSION['admin_user'] == 'rabban') {  ?>
		<input type="submit" class="btn color3" name="submit" value="Publish" style="float:right;">
		<?php }  ?>
		<input type="submit" class="btn" name="submit" value="Delete" style="left:0;bottom:0px;position:absolute;background:grey;" onclick="return confirm('Are you sure you want to delete this article?');">

	</div>

	</form>



</div>


		<script>

CKEDITOR.on('instanceReady', function(ev) { ev.editor.fire('contentDom'); config.extraAllowedContent = '*(*)';});

    CKEDITOR.replace('editor1', {
          width: '768px',
      contentsCss: "body {font-size: 15px;font-family:'Poppins';letter-spacing: .4px;}"
    });


CKEDITOR.addCss( 'p.imgcaption{background-color:#ccc;padding:5px;font-weight:600;font-size:15px;font-family: arial;}' );
CKEDITOR.extraAllowedContent = '*(*)';

		</script>


	</body>
</html>