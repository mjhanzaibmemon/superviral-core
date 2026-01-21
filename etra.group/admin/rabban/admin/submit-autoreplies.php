<?php


include('adminheader.php');


if(!empty($_POST['submit'])){

	$title = addslashes($_POST['title']);
	$autoreply = addslashes($_POST['autoreply']);
	$time = time();

	$title = utf8_encode($title);

$autoreply = nl2br(htmlentities($autoreply, ENT_QUOTES, 'UTF-8'));
$autoreply = str_replace('’','\'',$autoreply);


	$insertq = mysql_query("INSERT INTO `email_autoreplies`
		SET 

		`title` = '$title', 
		`autoreply` = '$autoreply', 
		`added` = '$time'
		");


	if($insertq)$submitted = '<div class="emailsuccess" style="background-color:#a6ff26;padding:10px;">


Name: '.$title.'<br>
Subject: '.$autoreply.'<br>
Added: '.gmdate("H:i d-m-Y", $time).'<br>


</div>';;




}



?>
<!DOCTYPE html>
<head>
<title>Submit reviews</title>
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

.articles{width:100%;}
.articles tr td{border-right:1px solid #ccc;border-bottom:1px solid #000;padding:10px;vertical-align: top}
.articles tr:first-child td{background:#f1f1f1;font-weight:bold;}

.status{ font-weight: bold;
    height: 23px;
    width: 55px;
    padding: 5px;font-size:15px;text-align:center;border-radius:3px;}

    .btn{margin: 0!important;width:152px;}

    textarea{font-family:'Open Sans';}

</style>
<script src="ckeditor/ckeditor.js"></script>
</head>

	<body>


		<?=$header?>

		<div class="box23">


			<?=$submitted?>

			<form method="POST">


				<input class="input inputcontact" name="title" value="" placeholder="Title">
				<textarea class="input inputcontact" name="autoreply" placeholder="Auto reply"></textarea>

				<input class="btn color3" name="submit" type="submit" value="submit autoreply">

			</form>


		</div>





	</body>
</html>