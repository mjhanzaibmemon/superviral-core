<?php


include('adminheader.php');

$thisstaffmember = addslashes($_SESSION['admin_user']);
$date_of_absence = date('d-m-Y',time());

$checkstaffq = mysql_query("SELECT * FROM `staff_absence` WHERE `staff` = '$thisstaffmember' AND `date_of_absence` = '$date_of_absence' LIMIT 1");


if(mysql_num_rows($checkstaffq)==1){

	$dontdosupportcss = '

	body{background:#fbb!important;}

	.box23{display:none!important;}

	';

	$dontdosupportdiv = '<div style="    width: 100%;
    text-align: center;
    padding-top: 15px;
    font-size: 28px;">Unfortunately, the time limit to start support was missed today,<br>
    please resume support on your next working day.</div>';

}





date_default_timezone_set('Europe/London');

function ago($time)
{$periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");
   $now = time();
       $difference     = $now - $time;
       $tense         = 'ago';
   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }
   $difference = round($difference);
   if($difference != 1) {
       $periods[$j].= "s";
   }   return "$difference $periods[$j] ago";}

$user = addslashes(trim($_POST['user']));
$orderid = addslashes(trim($_POST['orderid']));
$submitreport = addslashes($_POST['submitreport']);
$reportmessage = addslashes($_POST['reportmessage']);
$reportorderid = addslashes($_POST['reportorderid']); 
$reportemailaddress = addslashes($_POST['reportemailaddress']); 
$deletereport = addslashes($_POST['deletereport']);
$deletereportid = addslashes($_POST['deletereportid']);

if(empty($orderid))$orderid = addslashes(trim($_GET['orderid']));
if(empty($user))$user = addslashes(trim($_GET['user']));

if(!empty($deletereport)){mysql_query("DELETE FROM `admin_notifications` WHERE `id` = '$deletereportid' LIMIT 1");}

if((!empty($reportmessage))&&(!empty($submitreport))){

$added = time();

$q = mysql_query("INSERT INTO `admin_notifications` SET 
	`orderid` = '$reportorderid', 
	`emailaddress` = '$reportemailaddress', 
	`message` = '$reportmessage', 
	`directions` = '', 
	`admin_name` = '{$_SESSION['admin_user']}', 
	`added` = '$added' 
	");

if(!$q){die('there was an error');}

}


if(!empty($user)){$q = mysql_query("SELECT * FROM `orders` WHERE `emailaddress` LIKE '%$user%' OR `igusername` LIKE '%$user%' OR `payment_id` LIKE '%$user%' ORDER BY `id` DESC");$field = '<input type="hidden" name="user" value="'.$user.'">';}
if(!empty($orderid)){



	$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid' OR `lastfour` LIKE '%$orderid%' OR `chooseposts` LIKE '%$orderid%' ORDER BY `id` DESC LIMIT 10");$field = '<input type="hidden" name="orderid" value="'.$orderid.'">';}

if($q){



while($info = mysql_fetch_array($q)){

			//if(($info['packagetype']=='freefollowers')||($info['packagetype']=='freelikes'))continue;

			$packagetype = $info['amount'].' '.$info['packagetype'];

			$price = sprintf('%.2f', $info['price'] / 100);

			if(!empty($info['order_response'])){

				$i =0;
				$trackinginformation1 = explode('~~~', $info['order_response'].$info['order_response_finish']);
				foreach($trackinginformation1 as $atracking){
					$i++;
					if($i==1)continue;
					$atracking1 = explode('###',$atracking);
					$trackinginformation .= '<div class="trackinginfo"><div class="trackingheader">'.date('l jS \of F Y H:i:s ', $atracking1[0]).'</div>'.$atracking1[1].'('.$atracking1[2].' seconds) </div>';

				}


				$trackinginformation = '<tr><td>🗄️ Tracking information: </td><td>'.$trackinginformation.'</td></tr>';}

			if($info['fulfilled']=='0'){

				$amounto = $info['amount'];

				if ($amounto >= 1 && $amounto <= 150){$approx = '9-10 hours';}
				if ($amounto >= 151 && $amounto <= 250){$approx = '12-13 hours';}
				if ($amounto >= 251 && $amounto <= 380){$approx = '14-15 hours';}
				if ($amounto >= 500 && $amounto <= 999){$approx = '14-15 hours';}
				if ($amounto >= 1000 && $amounto <= 1500){$approx = '24-28 hours';}
				if ($amounto >= 2500 && $amounto <= 3750){$approx = '27-35 hours';}
				if ($amounto >= 5000 && $amounto <= 8000){$approx = '38-48 hours';}

				if(!empty($approx))$approx1 = '(will take around '.$approx.')';

				$orderstatus = '<font color="orange">In progress '.$approx1.'</font>';
				$arstatus = 'in progress';
				$artime = ' Please provide up to '.$approx.' for your order to be delivered. ';}
			else{$orderstatus = '<font color="green">Completed: '.date('l jS \of F Y H:i:s ', $info['fulfilled']).'</font>';
				$arstatus = 'completed';}

			
			$adminnotifsq = mysql_query("SELECT * FROM `admin_notifications` WHERE `orderid` = '{$info['id']}' OR `emailaddress` = '{$info['emailaddress']}' LIMIT 12");	
			while($adminnotifinfo = mysql_fetch_array($adminnotifsq)){

				if($adminnotifinfo['done']=='0'){$ifdelete = '😊 Waiting to be checked<br><input type="submit" name="deletereport" value="Delete Report">';
				$adminnotifcolor = 'background-color: #fff579;';}else{
					$ifdelete = '<br>✅ <span style="font-size: 12px;
    font-style: italic;">Checked by Admin, '.ago($adminnotifinfo['response']).', '.date("d/m/Y H:i:s",$adminnotifinfo['response']).')</span>';$adminnotifcolor = 'background-color: #cfff9b;';
				}

				if(!empty($adminnotifinfo['directions']))$adminresponse = '<hr><div>Admin Directions:<br>'.$adminnotifinfo['directions'].'</div>';

				$reportnotifs .= '<div class="adminnotif" style="'.$adminnotifcolor.'">'.$adminnotifinfo['message'].'<br><span style="font-size: 12px;
    font-style: italic;"> (reported by '.ucfirst($adminnotifinfo['admin_name']).' -  '.ago($adminnotifinfo['added']).', '.date("d/m/Y H:i:s",$adminnotifinfo['added']).')</span>

    			'.$adminresponse.'

				<form action="https://superviral.io/admin/check-user.php#order'.$info['id'].'" method="POST">'.$fields.'
				<input type="hidden" name="deletereportid" value="'.$adminnotifinfo['id'].'">
				'.$ifdelete.'
				</form></div>';

			unset($ifdelete);
			unset($adminresponse);
			unset($adminnotifinfo);
			unset($adminnotifinfo['directions']);

			}

			$findordercostq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = '{$info['packagetype']}' LIMIT 1");
			$findordercostinfo = mysql_fetch_array($findordercostq);

			$saleamount = $price;
			$salecost = $findordercostinfo['perone'] * ($info['amount']);

			$saleamount = round(0.58 * ($saleamount - $salecost),2);
		//	echo $saleamount.' - '.$salecost;;

			if($info['packagetype']=='followers'){
			$searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'likes' LIMIT 1");
			$refundpackageinfo = mysql_fetch_array($searchpackageq);
			$refundoffertype = 'likes';
			$ardestination = 'posts';
			}

			if($info['packagetype']=='likes'){
			$searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'followers' LIMIT 1");
			$refundpackageinfo = mysql_fetch_array($searchpackageq);
			$refundoffertype = 'followers';
			$ardestination = 'profile';}

			if($info['packagetype']=='views'){
			$searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'likes' LIMIT 1");
			$refundpackageinfo = mysql_fetch_array($searchpackageq);
			$refundoffertype = 'likes';
			$ardestination = 'posts';}

			//$refundoffer1 = round($saleamount / $refundpackageinfo['perone']);
			$refundoffer1 = round($info['amount'] * 2.2);
			$refundoffer2 = round($refundoffer1 * $refundpackageinfo['perone'],2);

			//$refundoffers = round($amount).' likes';
			$refundoffers = 'upto '.$refundoffer1.' '.$refundoffertype.' - £'.$refundoffer2;
			$arrefundsen = $refundoffer1.' '.$refundoffertype.' to your '.$ardestination;

			if(($info['packagetype']=='followers')||($info['packagetype']=='views')){$arextendsentence = 'We can also split the '.$refundoffer1.' likes to multiple IG posts.';}

			///// AUTO RESPONSES

			$autoresponses1 = '<div class="foo" >
<textarea class="language-less">
First and foremost, thank you very much for choosing Superviral for upgrading your profile – it means a lot to us! In regards to the order number associated with your email address, I’ve been able to locate a specific order.

I can see that your order is for '.$info['amount'].' '.$info['packagetype'].' and is '.$arstatus.'.'.$artime.' Bear in mind at Superviral, from fulfilling thousands of orders since 2012, we’ve gained a tremendous amount of experience. This has allowed us to develop the safest methods for delivery so that you can maintain as many '.$info['packagetype'].' as possible - ensuring maximum customer satisfaction.

If your order has not started yet at all, then our systems are still determining the right delivery method and the right type of '.$info['packagetype'].' based on your Instagram profile’s metrics.

From what our previous customers have experienced, please stay away from IG service providers that claim to provide instant deliveries without caring about their customer’s Instagram account. These illicit companies can usually lead to shadow bans from Instagram or even a ban.

Rest assured, your order is being processed and delivered as we speak!

</textarea>
<button class="btn btn3 report copy-button">Copy Order Status</button>
</div>';

		$autoresponses2 = '<div class="foo" >
<textarea class="language-less">
Thank you for contacting Superviral Customer Care. First and foremost, I would like to apologise for the experience you’ve had on Superviral and can only imagine the inconvenience you may have experienced.

Please bear in mind that _________ (reason why it’s not Superviral’s fault – to protect our brand), that being said we’ve reported this on to our technical team to look into so that this issue doesn’t happen again.

I have had a word with our management team, they’ve looked at your case and want to apologise deeply for the issue this has caused. My management team together with our refunds team can provide you with either:

- A refund of the amount you paid. 

- Or alternatively, we can provide you with upto '.$arrefundsen.' and give you a 20% partial refund and also you can keep the current '.$info['packagetype'].' as a goodwill gesture. '.$arextendsentence.'

At Superviral, the customer always comes first and we hope to resolve this issue ASAP.

Let me know how you would like to commence?

</textarea>
<button class="btn btn3 report copy-button">Copy Refund - Offer Refund offers</button>
</div>';

	$autoresponses3 = '<div class="foo" >
<textarea class="language-less">
Thank you for getting back to us. We are extremely sorry about the issue you have experienced once again. 

We have refunded the full amount of £'.$price.' to the card ending with '.$info['lastfour'].' back to your account, and as a goodwill gesture, you can keep the '.$info['packagetype'].' you received from Superviral. Please allow 5-10 days for the refund to appear on your bank statement.

Thank you for using Superviral and once again we do apologise for the inconvenience this may have caused you. We look forward to serving you again in the future.
</textarea>
<button class="btn btn3 report copy-button">Copy Refund - 1 Delivered</button>
</div>';

if($info['packagetype']=='followers'){

		$autoresponses4 = '<div class="foo" >
<textarea class="language-less">
Thank you for contacting Superviral Customer Care. I am extremely sorry to hear that you have experienced this issue. We\'ve looked into your order and it seems it was delivered. 

Also to add to that, your order is for Instagram followers. At Superviral, we provide you with Superviral Refill – where our system automatically scans your Instagram account for any followers/likes (that you’ve ordered) that has been lost. The system will then automatically reimburse your account with followers and likes to ensure you get what you ordered.

Also in some cases, Superviral may send you an email/text notification, and you may see that your order is complete but you haven’t received your order. For example, you’ve ordered 1000 followers and receive an email notification saying “your order is complete”. But you check your Instagram account and it has increased by only 700 followers.

This happens when Instagram removes your followers in between the time we’ve told you that the order is complete and the time you’ve checked your account. But not to worry, with our Superviral Refill – we’ll keep refilling your account’s followers/likes according to what you’ve ordered within 30-days of your order.

If this issue still persists, please do not hesitate to contact us again.

Kind regards,

James Harris</textarea>
<button class="btn btn3 report copy-button">Copy Has Received Order</button>
</div>';
}
	else{
	$autoresponses4 = '<div class="foo" >
<textarea class="language-less">
Thank you for contacting Superviral Customer Care. I am extremely sorry to hear that you have experienced this issue. We\'ve looked into your order and it seems it was delivered. If this issue still persists, please do not hesitate to contact us again.

Kind regards,

James Harris</textarea>
<button class="btn btn3 report copy-button">Copy Has Received Order</button>
</div>';}





			/////



$fulfills = explode(' ',trim($info['fulfill_id']));

foreach($fulfills as $fulfillorder){

	if(empty($fulfillorder))continue;

	$thisorderstatus .= '<a target="_BLANK" rel="noopener noreferrer" href="'.$fulfillmentsite.'/orders?search='.$fulfillorder.'">'.$fulfillorder.'</a><br>';

}


if($info['refund']=='1'){$refundcolor = 'orange';$refunded = ' <font color="orange">(refund in progress)</font>';}else{$refundcolor = 'grey';}
if($info['refund']=='2')$refunded = ' <font color="red">(refunded)</font>';


if(($_SESSION['admin_user']=='rabban')||($_SESSION['admin_user']=='admin')){

$offerfreefollowers = '<tr class="grey"><td>Offer Free Followers: </td><td><a target="_BLANK" href="https://superviral.io/admin/offer-free-followers.php?id='.$info['id'].'" class="btn btn3 report">Offer Followers</a>
<a target="_BLANK" href="https://superviral.io/admin/offer-free-likes.php?id='.$info['id'].'" class="btn btn3 report">Offer Likes</a></td></tr>';

$supplierfulfillid = '<tr class="grey"><td>Supplier Fulfill ID: </td><td>'.$thisorderstatus.'</td></tr>';
$supplierfulfillidtd = $thisorderstatus;

if($_SESSION['admin_user']=='rabban'){


	if (strpos($info['payment_id'], 'pi_') !== false)$paymenttr = '<tr class="grey"><td>Pid: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://dashboard.stripe.com/payments/'.$info['payment_id'].'">'.$info['payment_id'].'</a></td></tr>';


		if (strpos($info['payment_id'], '-') !== false)$paymenttr = '<tr class="grey"><td>Pid: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://my.cardinity.com/payment/show/'.$info['payment_id'].'">'.$info['payment_id'].'</a></td></tr>';

		if (is_numeric($info['payment_id']))$paymenttr = '<tr class="grey"><td>Pid: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://hub.acquired.com/#transactions/detail/'.$info['payment_id'].'">'.$info['payment_id'].'</a></td></tr>';

}

if($info['disputed']=='1'){$refundavail = 'A chargeback is has been created for this payment. Can\'t refund this';}
	else
{$refundavail = '<form action="refundorder.php?" method="POST">
						<input type="hidden" name="id" value="'.$info['id'].'">
						<input type="hidden" name="ordersession" value="'.$info['order_session'].'">
						<input type="input" class="input rectifyinput" name="amount" placeholder="\'percentage\' or \'full\'" value="'.$info['refundamount'].'">%
						<input type="submit" onclick="return confirm(\'Are you sure you want to issue the refund?\');" class="btn btn3 report copy-button" style="width:150px;" value="Issue Refund"></form>';}

$refundbtn = '<tr class="'.$refundcolor.'"><td>Offer Refund: </td><td>'.$refundavail.'</a></td></tr>';

if($info['price']=='0.00')$refundbtn = '';

}

if(($info['packagetype']=='followers')||($info['packagetype']=='freefollowers')){
$rectify = '<form action="ordermake-changeusername.php?update=nodefect" method="POST">
						<input type="hidden" name="id" value="'.$info['id'].'">
						<input type="hidden" name="ordersession" value="'.$info['order_session'].'">
						<input type="input" class="input rectifyinput" name="igusername" placeholder="kevinhart" value="'.$info['igusername'].'">
						<input type="submit" onclick="return confirm(\'Are you sure you want to change the username?\');" class="btn btn3 report copy-button" style="width:150px;" value="Make Order"></form>';


$rectify = '<tr class="grey"><td>Fix This Order By Username: </td><td>'.$rectify.'</td></tr>';}



if($info['packagetype']=='followers')$lastrefilled = '<tr><td>♻️ Last refiled on: </td><td>'.date('l jS \of F Y H:i:s ', $info['lastrefilled']).'</td></tr>';

if(!empty($info['chooseposts'])){$chooseposts = '<tr><td>👉 Posts for '.$info['packagetype'].': </td><td>'.$info['chooseposts'].'</td></tr>';}

			$summaryresults .='<tr>
				<td style="width: 186px;"><a href="#order'.$info['id'].'">#'.$info['id'].' - '.$packagetype.'</a></td>
				<td>'.date('l jS \of F Y H:i:s ', $info['added']).'</td>
				<td>'.$orderstatus.'</td>
				<td>'.$supplierfulfillidtd.'</td>
				</tr>';


if($info['refundtime']!=='0')$refunddate = '<tr><td>Refund issued on : </td><td>'.date('l jS \of F Y H:i:s ', $info['refundtime']).'</td></tr>';

			$results .= '


			<div class="box23">

			<table id="order'.$info['id'].'" class="perorder">


				<tr><td>Order #'.$info['id'].'</td><td></td></tr>
				<tr><td>#️⃣ Order ID: </td><td>'.$info['id'].'</td></tr>
				<tr><td>📧 Email address: </td><td><a href="https://superviral.io/admin/check-user.php?user='.$info['emailaddress'].'">'.$info['emailaddress'].'</a></td></tr>
				<tr><td>📦 Package: </td><td>'.$packagetype.'</td></tr>
				<tr><td>💸 Amount Paid: </td><td>£'.$price.$refunded.'</td></tr>
				<tr><td>🧍 IG username: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://instagram.com/'.$info['igusername'].'/">'.$info['igusername'].'</a></td></tr>
				'.$chooseposts.'
				<tr><td>📞 Contact number: </td><td>'.$info['contactnumber'].'</td></tr>
				<tr><td>⌚ Order made on: </td><td>'.date('l jS \of F Y H:i:s ', $info['added']).'</td></tr>
				'.$lastrefilled.'
				<tr><td>🚚 Order status: </td><td>'.$orderstatus.'</td></tr>
				'.$trackinginformation.'
				<tr><td>Tracking page: </td><td><a target="_BLANK" href="https://superviral.io/track-my-order/'.$info['order_session'].'">'.$info['order_session'].'</a></td></tr>
				<tr><td>❤️😍 Refund offers: </td><td>'.$refundoffers.'</td></tr>
				<tr><td>📝 Report: </td><td>'.$reportnotifs.'
				<!--<form action="https://superviral.io/admin/check-user.php#order'.$info['id'].'" method="POST">'.$submittedfield.$field.'
				<input type="hidden" name="reportorderid" value="'.$info['id'].'">
				<input type="hidden" name="reportemailaddress" value="'.$info['emailaddress'].'">
				<textarea class="reportmessage" name="reportmessage"></textarea>
				<div style="display:inline-block;width:100%;"><input type="submit" name="submitreport" class="btn btn3 report" value="Send Report">
				</div>
				</form>-->
				</td></tr>
				<tr class="grey"><td>Automated responses: </td><td><div>'.$autoresponses1.$autoresponses2.$autoresponses3.$autoresponses4.'</div></td></tr>
				<tr class="grey"><td>IP Address: </td><td>'.$info['ipaddress'].'</td></tr>
				'.$supplierfulfillid.'
				'.$paymenttr.'
				<tr><td>Last Four: </td><td>'.$info['lastfour'].'</td></tr>
				'.$rectify.'
				'.$offerfreefollowers.'
				'.$refundbtn.'
				'.$refunddate.'
			</table>


			</div>';

			unset($refunded);
			unset($lastrefilled);
			unset($rectify);
			unset($chooseposts);
			unset($reportnotifs);
			unset($orderstatus);
			unset($trackinginformation);
			unset($refundoffers);
			unset($supplierfulfillid);
			unset($supplierfulfillidtd);
			unset($thisorderstatus);
			unset($refundbtn);
			unset($refundavail);
			unset($refundcolor);
			unset($refunddate);

}

//if(!empty($results))$results = ''.$results.'';

}

if(!empty($summaryresults))$summaryresults = '<div class="box23"><table class="summarytbl"><tr>
	<td>Summary + package</td>
	<td>Order made</td>
	<td>Status</td>
	<td>Supplier Fulfill ID</td>
	</tr>'.$summaryresults.'</table></div>';

if(empty($user))$summaryresults = '';

?>
<!DOCTYPE html>
<head>
<title>Check User/Order</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.2/clipboard.min.js"></script>
<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<style type="text/css">

<?=$dontdosupportcss?>

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
.articles tr td{border-bottom:1px solid #f1f1f1;padding: 19px 10px;vertical-align: top}
.articles tr td:first-child{    font-size: 19px;
    width: 34%;
    vertical-align: middle;}
.articles tr:last-child td{border-bottom: 0;}

.status{ font-weight: bold;
    height: 23px;
    width: 55px;
    padding: 5px;font-size:15px;text-align:center;border-radius:3px;}

    .btn{margin: 0!important;}


.adminmenu{display:inline-block;background-color:white;border-top:1px solid #ccc;width:100%;}
.adminmenu a{float:left;padding:15px;}

.perorder{width:100%;}
.perorder tr:first-child td{background-color:#ccc;font-weight: bold;font-size:20px;}
.perorder tr td:first-child{width:30%;vertical-align: top;}
.perorder tr td{padding:14px 5px;border-bottom:1px solid #e0e0e0;}
.perorder tr.grey td{color:grey;}

.perorder a{text-decoration: underline;color:blue;}

.trackinginfo{border-bottom: 1px dashed #e8e8e8;
    margin-bottom: 2px;
    padding: 11px;
    font-size: 14px;
    color: grey;}
   .trackinginfo .trackingheader{font-weight:bold;}

.report{float: left;
    width: initial;
    margin-right: 10px!important;border:1px solid black!important;color:black!important;text-decoration:none!important;}

 .reportmessage{float: left;
    width: 100%;
    height: 120px;box-sizing:border-box;
    margin: 0px;
    margin-bottom: 20px;
    resize: vertical;padding:10px;font-family:'Open Sans';}

.adminnotif{    font-size: 15px;
    padding: 11px;margin-bottom:10px;}

.language-less{width:1px;height:1px;resize: none;}

.foo{    display: inline-block;
    width: 100%;
    margin-bottom: 18px;}

.rectifyinput{width: 181px;
    float: left;
    margin-top: 0;
    margin-right: 10px;}

.summarytbl{font-size:14px;}
.summarytbl tr:hover{background-color:#e4fbff;}
.summarytbl tr td{    border-bottom: 1px solid #dadada;
    padding: 7px;}

.searchspan{    font-size: 13px;
    color: #4747bf;
    line-height: 22px;
    display: block;}

<?=$styles?>

</style>
<script type="text/javascript">

var copyTextareaBtn = document.getElementById('75019textareacopybtn');

copyTextareaBtn.addEventListener('click', function(event) {
  var copyTextarea = document.getElementById('75019copytextarea');
  copyTextarea.select();

  try {
    var successful = document.execCommand('copy');
    var msg = successful ? 'successful' : 'unsuccessful';
    console.log('Copying text command was ' + msg);
  } catch (err) {
    console.log('Oops, unable to copy');
  }
});

</script>
</head>

	<body>


		<?=$header?>


		<h1 style="text-align:center;margin-top:35px;">Check User 📦</h1>


		<?=$dontdosupportdiv?>

		<div class="box23">



			<form method="POST" action="#">
			<table class="articles">

				<tr>

					<td>👨👩 User:<span class="searchspan">Search by:<br>- IG username<br>- email address</span></td>
					<td><input name="user" class="input" value="<?=$user?>" autocomplete="off"></td>

				</tr>

				<tr>

					<td>📦 Order:<span class="searchspan">Search by:<br>- Order ID<br>- Last Four Card Number e.g. 1234</span></td>
					<td><input name="orderid" class="input" value="<?=$orderid?>" autocomplete="off"></td>

				</tr>

				<tr>

					<td></td>
					<td><input style="float:left;" type="submit" name="submit" class="btn" value="Search"><a href="https://superviral.io/admin/check-user.php" class="btn btn3 report" style="float:right;">Reset Search</a>
					<a href="https://superviral.io/admin/no-order-report.php" class="btn btn4 report" style="float:right;display:none;">Submit No-Order Report</a></td>

				</tr>

			</table>

			</form>


		

		</div>

<?=$summaryresults?>
<?=$results?>

<script>
(function(){

	// Get the elements.
	// - the 'pre' element.

	
	var pre = document.getElementsByClassName('foo');
	

	// Add a copy button in the 'pre' element.
	// which only has the className of 'language-'.
	
	for (var i = 0; i < pre.length; i++) {
		var isLanguage = pre[i].children[0].className.indexOf('language-');
		
		/*
		if ( isLanguage === 0 ) {
			var button           = document.createElement('button');
					button.className = 'copy-button';
					button.textContent = 'Copy';

					pre[i].appendChild(button);
		}*/
	};
	
	// Run Clipboard
	
	var copyCode = new Clipboard('.copy-button', {
		target: function(trigger) {
			return trigger.previousElementSibling;
    }
	});

	// On success:
	// - Change the "Copy" text to "Copied".
	// - Swap it to "Copy" in 2s.
	// - Lead user to the "contenteditable" area with Velocity scroll.
	
	copyCode.on('success', function(event) {
		event.clearSelection();
		event.trigger.textContent = 'Copied';
		window.setTimeout(function() {
			event.trigger.textContent = 'Copy';
		}, 2000);

	});

	// On error (Safari):
	// - Change the  "Press Ctrl+C to copy"
	// - Swap it to "Copy" in 2s.
	
	copyCode.on('error', function(event) { 
		event.trigger.textContent = 'Press "Ctrl + C" to copy';
		window.setTimeout(function() {
			event.trigger.textContent = 'Copy';
		}, 5000);
	});

})();
</script>

	</body>
</html>