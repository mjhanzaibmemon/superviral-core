<?php


include('adminheader.php');

$id = $_GET['id'];

if($id=='new'){

$type = addslashes($_GET['type']);

$findhighq = mysql_query("SELECT * FROM `email_funnels` WHERE `type` = '$type' ORDER BY `{$type}sequence` DESC LIMIT 1");
$findhigh = mysql_fetch_array($findhighq);
$findhighqadded = $findhigh[$type.'sequence'] + 1;
$findhighqadnoadded = $findhigh[$type.'sequence'];

//workout the type
if($findhigh['type']=='cold')$theactualtype = 'freetrial';
if($findhigh['type']=='warm')$theactualtype = 'cart';
if($findhigh['type']=='hot')$theactualtype = 'order';

$q= mysql_query("INSERT INTO `email_funnels` SET `body` = '',`type` = '$type', `{$type}sequence` = '$findhighqadded'");

$newid = mysql_insert_id();

if($q){header('Location: https://superviral.io/admin/editemailfunnels.php?id='.$newid.'&message=3');}
else{die('Error creating a new row QUERY');}

die;

}


$q = mysql_query("SELECT * FROM `email_funnels` WHERE `id` = '$id' LIMIT 1");
if(mysql_num_rows($q)=='0'){exit('DOES NOT EXIST');}

$info = mysql_fetch_array($q);

$optionselect2 = '<option value="Superviral">Superviral</option><option value="James Harris">James Harris</option>';

$optionselect2 = str_replace('"'.$info['name'].'"', '"'.$info['name'].'" selected="selected"', $optionselect2);

$optionselect= '<option value="1800">30 minutes</option>
		<option value="3600">1 hour</option>
		<option value="10800">3 hour</option>
		<option value="21600">6 hour</option>
		<option value="43200">12 hour</option>
		<option value="86400">24 hour/1-day</option>
		<option value="172800">2-day</option>
		<option value="259200">3-day</option>
		<option value="345600">4-day</option>
		<option value="432000">5-day</option>
		<option value="518400">6-day</option>
		<option value="604800">7-day</option>
		<option value="691200">8-day</option>
		<option value="777600">9-day</option>
		<option value="864000">10-day</option>';

$optionselect =str_replace('"'.$info['timeafterunix'].'"', '"'.$info['timeafterunix'].'" selected="selected"', $optionselect);

if($info['published']=='1'){

$live = '<div class="status" style="    background: #82fd82;">LIVE</div>';

$urlquery = "`url` = '{$_POST['url']}', ";

}else{

$live = '<div class="status" style="    background: #ccc;
   ">DRAFT</div>';

}

if($_GET['message']=='1'){$message = '<div class="emailsuccess">Funnel successfully saved.</div>';}
if($_GET['message']=='2'){$message = '<div class="emailsuccess">Funnel published and gone live.</div>';}
if($_GET['message']=='3'){$message = '<div class="emailsuccess">Created a new Funnnel.</div>';}

/*
if($_POST['submit']=='Delete'){

$q = mysql_query("DELETE FROM `articles` WHERE `id` = '$id' LIMIT 1");

if($q){header('Location: https://superviral.io/admin/article.php?&message=1');die;}

}*/

if($_POST['submit']=='Save'){


$_POST['article'] = str_replace('width="undefined"','',$_POST['article']);
$_POST['article'] = str_replace('height="undefined"','',$_POST['article']);


$input_arr = array();
foreach ($_POST as $key => $input_arr) {
$_POST[$key] = addslashes($input_arr);
} 

$q = mysql_query("UPDATE `email_funnels` SET 
	`subject` = '{$_POST['subject']}',
	`timeafterunix` = '{$_POST['sendtime']}',
	`name` = '{$_POST['name']}',
	`body` = '{$_POST['body']}' 
	WHERE `id` = '$id' LIMIT 1");

if($q){header('Location: https://superviral.io/admin/editemailfunnels.php?id='.$id.'&message=1');die;}


}


if($_POST['submit']=='Publish'){

$findhighq = mysql_query("SELECT * FROM `email_funnels` WHERE `id` = '$id' LIMIT 1");
$findhigh = mysql_fetch_array($findhighq);
$qtype = $findhigh['type'];
$findhighqadnoadded = $findhigh[$qtype.'sequence'] - 1;

//workout the type
if($findhigh['type']=='cold')$theactualtype = 'freetrial';
if($findhigh['type']=='warm')$theactualtype = 'cart';
if($findhigh['type']=='hot')$theactualtype = 'order';

echo $findhighqadnoadded.'<br>';
echo $theactualtype.'<br>';

$q= mysql_query("UPDATE `users` SET `locked` = '0' WHERE `source` = '$theactualtype' AND `funnelstate` = '$findhighqadnoadded'");//UNLOCK USERS WITH NEW

$q1 = mysql_query("UPDATE `email_funnels` SET `published` = '1' WHERE `id` = '$id' LIMIT 1");

if($q1){header('Location: https://superviral.io/admin/editemailfunnels.php?id='.$id.'&message=2');die;}

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

.btn{width:100px;text-align:center;margin-right:10px;}

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

			<a href="https://superviral.io/admin/emailfunnels.php">&laquo; Back</a>

		</div>

		<form method="POST">
		<div class="box23">
	
	<h1>Edit: <?=$info['title']?></h1>
	<?=$live?>
	<?=$message?>
	<hr>
	<input type="hidden" name="id" value="<?=$info['id']?>">
	<div class="label labelcontact">subjects:</div>
	<input class="input inputcontact" autocomplete="off" name="subject" value="<?=$info['subject']?>">

	<div class="label labelcontact">from:</div>
	<select class="input inputcontact" name="name">
		<?=$optionselect2?>
	</select>

	<div class="label labelcontact">send time after:</div>
	<select class="input inputcontact" name="sendtime">
		<?=$optionselect?>

	</select>

	<div class="label labelcontact">body:</div>
	<textarea class="ckeditor input inputcontact textarea textareacontact" cols="80" id="editor1" name="body" rows="10"><?=stripslashes($info['body'])?></textarea>

	<div style="height:330px;position:relative;">

		<input type="submit" class="btn color3" name="submit" value="Save" style="float:left;">
		<a href="emailpreview.php?id=<?=$id ?>&sendtestemail=true" target="_BLANK" class="btn color3" style="float:left;width:170px">Send Test Email</a>
		<a href="emailpreview.php?id=<?=$id ?>" target="_BLANK" class="btn color3" style="float:left;width:170px">Preview Email</a>
		<input type="submit" class="btn color3" name="submit" value="Publish" style="float:right;">
		<input type="submit" class="btn" name="submit" value="Delete" style="left:0;bottom:0px;position:absolute;background:grey;display:none;" onclick="return confirm('Are you sure you want to delete this article?');">

	</div>

	</form>



</div>


		<script>

CKEDITOR.on('instanceReady', function(ev) { ev.editor.fire('contentDom'); });

    CKEDITOR.replace('editor1', {
      width: '550px',
      contentsCss: "body {font-size: 15px;font-family:'Poppins';letter-spacing: .4px;}"
    });


CKEDITOR.addCss( 'p.imgcaption{background-color:#ccc;padding:5px;font-weight:600;font-size:15px;font-family: arial;}' );


		</script>


	</body>
</html>