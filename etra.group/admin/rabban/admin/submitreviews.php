<?php


include('adminheader.php');


/////
$dbName = "tikoid";
mysql_select_db($dbName , $conn);
/////


if(!empty($_POST['submit'])){

/*	$name = trim(addslashes($_POST['name']));
	$subject = trim(addslashes($_POST['subject']));*/
	$reviews = trim(trim(addslashes($_POST['review'])));




$stars = '5';

$reviews = explode("\n", $reviews);

		foreach($reviews as $perreview){


					$time = time();
					$time = rand($time - '604800',$time);



				$review = explode('	', $perreview);

				$name = trim(trim(trim(trim(trim($review[0])))));
				$subject = trim(trim(trim(trim(trim($review[1])))));
				$review = trim(trim(trim(trim(trim($review[2])))));

				if(empty($name))die('Error');
				if(empty($subject))die('Error');
				if(empty($review))die('Error');

					$insertq = mysql_query("INSERT INTO `reviews`
						SET 
						`type` = 'views', 
						`stars` = '$stars', 
						`name` = '$name', 
						`title` = '$subject', 
						`review` = '$review', 
						`timeo` = '$time', 
						`approved` = '1'
						");



					if($insertq){$reviewmessage .= '<div class="emailsuccess" style="margin-bottom:10px;">Your review has been submitted. Thank you!<br><br>

						Name: '.$name.'<br>
						Subject: '.$subject.'<br>
						Name: '.$review.'<br>


						</div>';}else

						{


						$reviewmessage .= '<div class="emailsuccess " style="background-color:red;">Error!</div>';


						}

				unset($reviews);

		}


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


			<?=$reviewmessage?>

			<form method="POST">


				<input class="input inputcontact" name="name" value="" placeholder="name">
				<input class="input inputcontact" name="subject" value="" placeholder="subject">
				<textarea class="input inputcontact" name="review" placeholder="review"></textarea>

				<input class="btn color3" name="submit" type="submit" value="submit review">

			</form>


		</div>





	</body>
</html>