<?php


include('adminheader.php');

$id = addslashes($_GET['id']);

$orderid = addslashes($_POST['id']);
$username = addslashes($_POST['username']);
$amount = addslashes($_POST['amount']);


//ANYTHING COMMENTED OUT MEANS THEY TAKE ACTION AND SHOULD BE REMOVED
if((!empty($orderid))&&(!empty($username))&&(!empty($amount))){

			$emailtrue='asdas4dsdf';

			$added = time();

			$ordersession = md5('neworder'.$added.$id);

			$qfetch = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid' LIMIT 1");
			$orderinfo = mysql_fetch_array($qfetch);

			include('../orderfulfillraw.php');

			$order1 = $api->order(array('service' => $freefollowersorderid, 'link' => 'https://instagram.com/'.$username, 'quantity' => $amount));

			$fulfill_id = $order1->order;

			if(empty($fulfill_id))die('Contact Rabban with this error: Missing Fulfill ID');

			$insertq = mysql_query("INSERT INTO `orders` SET 
					`brand` = 'sv',
					`packagetype`= 'followers', 
					`account_id`= '{$orderinfo['account_id']}', 
					`order_session`= '{$ordersession}',
					`added` = '$added', 
					`lastrefilled` = '$added', 
					`amount`= '$amount', 
					`emailaddress`= '{$orderinfo['emailaddress']}', 
					`igusername`= '{$username}', 
					`ipaddress`= '{$orderinfo['ipaddress']}',
					`price`= '0.00',
					`payment_id` = '{$orderinfo['payment_id']}',
					`fulfill_id`= '{$fulfill_id}'");

//EMAILER NEEDS TO COME IN HERE
$thefreeservice = $amount.' free Instagram Followers';
$service = $amount.' High Quality Followers';
$ctahref = 'https://superviral.io/track-my-order/'.$ordersession;
$igusername = $username;
$to = $orderinfo['emailaddress'];
$subject = 'Free Instagram Followers Notification';
include('emailfree.php');

$insertid = mysql_insert_id();

if($insertq)$success = '<div class="emailsuccess">A new order '.$insertid.' has been placed for '.$amount.' free followers to: '.$igusername.'</div>';

}

$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$id' LIMIT 1");
$infoa = mysql_fetch_array($q);

$orderinfo = 'ID: '.$infoa['id'].'<br>';
$orderinfo .= 'Email address: '.$infoa['emailaddress'].'<br>';
$orderinfo .= 'IG Username: '.$infoa['igusername'].'<br>';
$orderinfo .= 'Order: '.$infoa['amount'].' '.$infoa['packagetype'].'<br>';
$orderinfo .= 'Price: £'.sprintf('%.2f', $infoa['price'] / 100).'<br>';
$orderinfo .= 'Orded Placed: '.date('l jS \of F Y H:i:s ', $infoa['added']).'<br>';

$orderinfo = '<div style="font-size:14px;font-family:verdana;padding:10px;line-height: 29px;">'.$orderinfo.'</div>';

?>
<!DOCTYPE html>
<head>
<title>Offer Free Followers</title>
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

		<h1 style="text-align:center;margin-top:35px;"><?=$id?>: Free Followers</h1>

		<div class="box23">

			<?=$success?>

			<form method="POST">
			<table class="articles">

				<tr>

					<td>Order ID</td>
					<td><input type="hidden" autocomplete="off" name="id" value="<?=$infoa['id']?>" class="input"><?=$orderinfo?></td>

				</tr>

				<tr>

					<td>IG Username</td>
					<td><input autocomplete="off" name="username" value="<?=$infoa['igusername']?>" class="input" placeholder="kevinhart"></td>

				</tr>


				<tr>

					<td>Amount</td>
					<td><input autocomplete="off" name="amount" value="" class="input" placeholder="0"><br>minimum amount: 100</td>

				</tr>





				<tr>

					<td></td>
					<td><input style="float:left;width:190px;" onclick="return confirm('Are you sure you want to offer these free followers?');" type="submit" name="submit" class="btn color3" value="Give Free Followers"></td>

				</tr>

			</table>
			</form>
		</div>





	</body>
</html>