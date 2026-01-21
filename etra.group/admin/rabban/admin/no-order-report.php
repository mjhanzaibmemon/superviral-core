<?php


include('adminheader.php');

$submit = addslashes($_POST['submit']);
$emailaddress = addslashes($_POST['emailaddress']);
$report = addslashes($_POST['report']);

if((!empty($submit))&&(!empty($emailaddress))&&(!empty($report))){

$reportorderid ='0';

$added = time();

$insertq = mysql_query("INSERT INTO `admin_notifications` SET 
	`orderid` = '$reportorderid', 
	`emailaddress` = '$emailaddress', 
	`message` = '$report', 
	`directions` = '', 
	`admin_name` = '{$_SESSION['admin_user']}', 
	`added` = '$added' 
	");

if($insertq)$success = '<div class="emailsuccess">A report has been successfully made for a no-order report for: '.$emailaddress.'</div>';

}


?>
<!DOCTYPE html>
<head>
<title>No Order Report</title>
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

    .btn{margin: 0!important;}

 .reportmessage{float: left;
    width: 100%;
    height: 350px;box-sizing:border-box;
    margin: 0px;
    margin-bottom: 20px;
    resize: vertical;padding:10px;font-family:'Open Sans';
	border-radius:5px;border: 1px solid #bbb;}

.emailsuccess{    margin-bottom: 15px;}

</style>
<script src="ckeditor/ckeditor.js"></script>
</head>

	<body>


		<?=$header?>

		<h1 style="text-align:center;margin-top:35px">A No-Order Report</h1>

		<div class="box23">

			<?=$success?>

			<form method="POST">
			<table class="articles">

				<tr>

					<td>Details required</td>
					<td>Form</td>

				</tr>

				<tr>

					<td>Email address</td>
					<td><input autocomplete="off" name="emailaddress" value="" class="input" placeholder="borisjohnson@live.co.uk"></td>

				</tr>

				<tr>

					<td>Report</td>
					<td><textarea class="reportmessage" name="report" placeholder="What needs to be reported?"></textarea></td>

				</tr>


				<tr>

					<td></td>
					<td><input style="float:left;width:150px;" type="submit" name="submit" class="btn color3" value="Submit Report"></td>

				</tr>

			</table>
			</form>
		</div>





	</body>
</html>