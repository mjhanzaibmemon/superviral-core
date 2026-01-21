<?php


include('adminheader.php');

function secondsToTime($seconds) {
	    $dtF = new \DateTime('@0');
	    $dtT = new \DateTime("@$seconds");
	    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

$navigation = '';

$id = addslashes($_GET['id']);
$type = addslashes($_GET['type']);

$_GET['moveid'] = addslashes($_GET['moveid']);
$_GET['move'] = addslashes($_GET['move']);


if(!empty($_GET['move'])){

		$getinfoq = mysql_query("SELECT * FROM `email_funnels` WHERE `id` = '{$_GET['moveid']}' LIMIT 1");
		$getinfonow = mysql_fetch_array($getinfoq);

		$type3 = $getinfonow['type'];

		if($_GET['move']=='down'){
		$sequenceup = $getinfonow[$type3.'sequence'] + 1;
		$sequencedown = $getinfonow[$type3.'sequence'];}

		if($_GET['move']=='up'){
		$sequenceup = $getinfonow[$type3.'sequence'] - 1;
		$sequencedown = $getinfonow[$type3.'sequence'];}		

		$checkifexistq = mysql_query("SELECT * FROM `email_funnels` WHERE `{$getinfonow['type']}sequence` = '{$sequenceup}' LIMIT 1");

		if(mysql_num_rows($checkifexistq)=='1'){

			//SET THE BEFORE ROW LOWER FIRST
			mysql_query("UPDATE `email_funnels` SET  `{$getinfonow['type']}sequence` = '{$sequencedown}' WHERE `{$getinfonow['type']}sequence` = '{$sequenceup}' LIMIT 1");

			//workout the type
			if($type3=='cold')$theactualtype = 'freetrial';
			if($type3=='warm')$theactualtype = 'cart';
			if($type3=='hot')$theactualtype = 'order';

			//
			mysql_query("UPDATE `users` SET `tempmove` = '1',`funnelstate` = '$sequencedown' WHERE `funnelstate` = '$sequenceup' AND `source` = '$theactualtype'");
			mysql_query("UPDATE `users` SET `funnelstate` = '$sequenceup' WHERE `funnelstate` = '$sequencedown' AND `source` = '$theactualtype' AND `tempmove` = '0'");

			mysql_query("UPDATE `users` SET `tempmove` = '0' WHERE `tempmove` = '1'");

			//THEN UPDATE THIS ONE WITH SWAPPED VALUES
			mysql_query("UPDATE `email_funnels` SET  `{$getinfonow['type']}sequence` = '{$sequenceup}' WHERE `id` = '{$getinfonow['id']}' LIMIT 1");

		}

		echo 'Swapped!';
		header('Location: ?type='.$type);
		die;

}




if(empty($type)){header('Location: ?type=cold');}

$q = mysql_query("SELECT * FROM `email_funnels` WHERE `type` = '$type' ORDER BY `{$type}sequence` ASC");

while($info = mysql_fetch_array($q)){

if($info['published']=='1'){

$published = '<span style="color:green">LIVE</span>';

}else{

$published = '<span style="color:red">DRAFT</span>';

}

	$timeafter = secondsToTime($info['timeafterunix']);

	$thistype = $info['type'];

	$funnels .= '<div style="padding: 45px 15px;position:relative;"><img src="watch.png"><span style="left: 100px;
    top: 51px;
    position: absolute;
    font-size: 18px;">After '.$timeafter.' send this email ↓</span></div>
	<table class="funnels"><tr>
					<td>
					<span style="font-size:15px;">'.$info[$thistype.'sequence'].'. - '.ucfirst($info['type']).'</span> - Status: '.$published.'<br>
					<span style="font-size:18px;">'.$info['subject'].'</span><br>
					<div style="border:1px solid #ccc;position:relative;">

					<a class="btn color3" style="width:110px;" href="https://superviral.io/admin/editemailfunnels.php?id='.$info['id'].'">Edit</a>


					<span style="position: absolute;right: 14px;top: 12px;">

						<a href="?move=up&moveid='.$info['id'].'" style="text-decoration:underline;">Move up</a>
						<a href="?move=down&moveid='.$info['id'].'" style="text-decoration:underline;">Move down</a>


					</span></div>
					<span style="color:grey;font-size:14px;">Sent amount: '.$info['sentamount'].', CLV amount: £'.$info['clvamount'].', CLV increase: £'.$info['clvincrease'].'</span><br>
					</td>
				<tr></table>';


}



?>
<!DOCTYPE html>
<head>
<title>Edit or Add Funnel</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">

<style type="text/css">

.box23{margin: 66px auto;
    width: 540px;
    border-radius: 5px;text-align:left;padding:15px;}

h1{text-align: left;max-width:100%;}

.label{margin-top:35px;}

.container div input, .selectric, .input, .btn {padding: 13px;font-size: 14px;}

.btn{width:100px;text-align:center;}

html{overflow-x: hidden;}

.cke_reset_all{background:#f7f7f7!important;}

.funnels{width:100%;background-color:white;margin-bottom:30px;}
.funnels tr td{padding:10px;vertical-align: top}

.status{ font-weight: bold;
    height: 23px;
    width: 55px;
    padding: 5px;font-size:15px;text-align:center;border-radius:3px;}

    .btn{margin: 0!important;}

.thebody{margin: 0;
    font-size: 13px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    line-height: 16px;
    max-height: 50px;
    -webkit-box-orient: vertical;
    color: #4e4e4e;
    -webkit-line-clamp: 2;}

.adminmenu{display:inline-block;background-color:white;border-top:1px solid #ccc;width:100%;}
.adminmenu a{float:left;padding:15px;}

</style>
<script src="ckeditor/ckeditor.js"></script>
</head>

	<body>


		<?=$header?>

		<div class="adminmenu">
			<a href="?type=cold">Cold funnels</a>
			<a href="?type=warm">Warm funnels</a>
			<a href="?type=hot">Hot funnels</a>
		</div>

		<div class="box23">

			<h1><?=ucfirst($type) ?> Email Funnels</h1><br>

			<a class="btn color3" style="width:310px" href="https://superviral.io/admin/editemailfunnels.php?id=new&type=<?=$type?>">Create a new <?=$type?> funnel</a>
			

			

				<?=$funnels?>


		</div>





	</body>
</html>