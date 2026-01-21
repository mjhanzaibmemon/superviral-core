<?php


include('adminheader.php');

$dbName = $tikoidDB;
mysql_select_db($dbName , $conn);

$id = addslashes($_GET['id']);

$orderid = addslashes($_POST['id']);
$posts = $_POST['myInputs'];
$amount = addslashes($_POST['amount']);



if((!empty($orderid))&&(!empty($posts))&&(!empty($amount))){

			$emailtrue='asdas4dsdf';

			$added = time();

			$ordersession = md5('neworder'.$added.$id);

			$qfetch = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid' LIMIT 1");
			$orderinfo = mysql_fetch_array($qfetch);

			include('../tiktokorderfulfillraw.php');

			foreach($posts as $post){#
			if(empty($post))continue;
			$postsrefined[] = $post;
			}

			$totalposts = count($posts);
			unset($post);

			$multiamount = $amount / $totalposts;
			$multiamount = round($multiamount);

			foreach($postsrefined as $post){


			$post = trim($post);

			$postraw = str_replace('https://www.tiktok.com/@connorhtown/video/','',$post);
			$postraw = str_replace('/','',$postraw);
			$postraw = trim($postraw);

			$order1 = $api->order(array('service' => $freelikesorderid, 'link' => $post, 'quantity' => $multiamount));

			$fulfillids .= $order1->order;
			$fulfillids .= ' ';

			$chooseposts .= $postraw.' ';

			echo $postraw.'<br>'.$post.'<br>'.$multiamount.'<hr>';

			}

			if(empty($orderid))die('Contact Rabban with this error: Missing Fulfill ID for Likes');


			$insertq = mysql_query("INSERT INTO `orders` SET 
					`packagetype`= 'likes', 
					`account_id`= '{$orderinfo['account_id']}',
					`order_session`= '{$ordersession}',
					`added` = '$added', 
					`lastrefilled` = '$added', 
					`amount`= '$amount', 
					`emailaddress`= '{$orderinfo['emailaddress']}', 
					`igusername`= '{$orderinfo['igusername']}', 
					`ipaddress`= '{$orderinfo['ipaddress']}',
					`price`= '0.00',
					`payment_id` = '{$orderinfo['payment_id']}',
					`fulfill_id`= '{$fulfillids}',
					`chooseposts` = '$chooseposts'");


			//EMAILER NEEDS TO COME IN HERE
$thefreeservice = $amount.' free Instagram Likes';
$service = $amount.' High Quality Likes';
$ctahref = 'https://tikoid.com/track-my-order/'.$ordersession;
$igusername = $username;
$to = $orderinfo['emailaddress'];
$subject = 'Free Instagram Likes Notification';
//include('emailfree.php');

$insertid = mysql_insert_id();

if($insertq)$success = '<div class="emailsuccess">A new order '.$insertid.' has been placed for '.$amount.' free likes to: '.$igusername.'</div>';



}

$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$id' LIMIT 1");
$infoa = mysql_fetch_array($q);

$orderinfo = 'ID: '.$infoa['id'].'<br>';
$orderinfo .= 'Email address: '.$infoa['emailaddress'].'<br>';
$orderinfo .= 'TikTok Username: '.$infoa['igusername'].'<br>';
$orderinfo .= 'Order: '.$infoa['amount'].' '.$infoa['packagetype'].'<br>';
$orderinfo .= 'Price: £'.sprintf('%.2f', $infoa['price'] / 100).'<br>';
$orderinfo .= 'Orded Placed: '.date('l jS \of F Y H:i:s ', $infoa['added']).'<br>';

$orderinfo = '<div style="font-size:14px;font-family:verdana;padding:10px;line-height: 29px;">'.$orderinfo.'</div>';

?>
<!DOCTYPE html>
<head>
<title>Offer Free Likes</title>
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
<script>
var counter = 0;
var limit = 10;
function addInput(divName){
     if (counter == limit)  {
          alert("You have reached the limit of adding " + counter + " inputs");
     }
     else {
          var newdiv = document.createElement('div');
          newdiv.innerHTML = "" + (counter + 1) + ". <br><input autocomplete=\"off\" name=\"myInputs[]\" value=\"\" style=\"margin-bottom:15px;\" class=\"input\" placeholder=\"https://www.tiktok.com/@connorhtown/video/7131260506371050798 \">";
          document.getElementById(divName).appendChild(newdiv);
          counter++;
     }
}
</script>
</head>

	<body>


		<?=$header?>

		<h1 style="text-align:center;margin-top:35px;"><?=$svgtiktokh1?> <?=$id?>: Free Likes</h1>

		<div class="box23">

			<?=$success?>

			<form method="POST">
			<table class="articles">

				<tr>

					<td>Order ID</td>
					<td><input type="hidden" autocomplete="off" name="id" value="<?=$infoa['id']?>" class="input"><?=$orderinfo?></td>

				</tr>


				<tr>

					<td>TikTok Posts</td>
					<td><div id="dynamicInput"></div>

						

						<input type="button" value="Add another post" onClick="addInput('dynamicInput');" style="margin-top:30px;">

					</td>

				</tr>

				<tr>

					<td>Amount</td>
					<td><input autocomplete="off" name="amount" placeholder="0" class="input"><br>minimum amount: 100 per TikTok post</td>

				</tr>



				<tr>

					<td></td>
					<td><input style="float:left;width:190px;" onclick="return confirm('Are you sure you want to offer these free likes?');" type="submit" name="submit" class="btn color3" value="Give Free Likes"></td>

				</tr>

			</table>
			</form>
		</div>





	</body>
</html>