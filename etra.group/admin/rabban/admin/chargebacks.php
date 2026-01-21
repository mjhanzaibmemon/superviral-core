<?php

include('adminheader.php');

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

// /*THE ONLY VARIABLENEEDED*/
$id = addslashes($_POST['id']);
$emailaddress_s = addslashes($_POST['emailaddress_s']);
$stage = addslashes($_POST['stage']);

if(empty($stage))$stage=1;

// STAGE 1 DEFAULT
if($stage==1){

if(!empty($_POST['id'])){

$q = mysql_query("SELECT * FROM `orders` WHERE `id` LIKE '%$id%' OR `emailaddress` LIKE '%$id%' OR `igusername` LIKE '%$id%' OR `payment_id` LIKE '%$id%' OR `payment_id_desc` LIKE '%$id%' ORDER BY `id` DESC");

while($info = mysql_fetch_array($q)){


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


$packagetype = $info['amount'].' '.$info['packagetype'];

if(!empty($info['chooseposts'])){$chooseposts = '<tr><td>👉 Posts for '.$info['packagetype'].': </td><td>'.$info['chooseposts'].'</td></tr>';}
if($info['refund']=='2'){$refunded = '<tr><td>Refunded</td><td>'.date('l jS \of F Y H:i:s ', $info['refundtime']).' ('.$info['refundamount'].')</td></tr>';
$ifrefunded = ' <font color="red">(REFUNDED)</font>';}

if($info['disputed']=='1')$disputed = '<font color="red"> (CHARGEBACK ALREADY CREATED)</font>';

if($info['packagetype']=='followers')$lastrefilled = '<tr><td>♻️ Last refiled on: </td><td>'.date('l jS \of F Y H:i:s ', $info['lastrefilled']).'</td></tr>';

$show .= '<table class="perorder">
<tr><td>'.$info['id'].$disputed .'</td><td></td></tr>
<tr><td>ID<td>'.$info['id'].$ifrefunded.'</td></tr>
<tr><td>Email address<td>'.$info['emailaddress'].'</td></tr>
<tr><td>📦 Package: </td><td>'.$packagetype.'</td></tr>
</tr><td>IG username</td><td>'.$info['igusername'].'</td></tr>
'.$chooseposts.'
<tr><td>⌚ Order made on: </td><td>'.date('l jS \of F Y H:i:s ', $info['added']).'</td></tr>
'.$lastrefilled.'
<tr><td>🚚 Order status: </td><td>'.$orderstatus.'</td></tr>
<tr><td>Payment ID: </td><td>'.$info['payment_id'].'</td></tr>
<tr><td>Payment Short ID: </td><td>'.$info['payment_id_desc'].'</td></tr>
'.$refunded.'
<tr><td>Mark as chargeback?: </td><td><form method="POST">

<input type="hidden" name="stage" value="2">
<input type="hidden" name="id" value="'.$info['id'].'">
<input type="hidden" name="emailaddress_s" value="'.$info['emailaddress'].'">
<input style="float:left;" type="submit" onclick="return confirm(\'Are you sure to mark this as a chargeback?\');" name="submit" class="btn btn3 report" value="Yes - this is a chargeback"></form></td></tr>
</table>



';



unset($disputed);
unset($ifrefunded);
unset($refunded);
unset($packagetype);
unset($chooseposts);
unset($lastrefilled);
unset($orderstatus);

}

}


$show = '<form method="POST" action="">
			<table class="articles">

				<tr>

					<td>Order ID/Payment ID/Payment Short ID: </td>
					<td><input type="hidden" name="stage" value="1" autocomplete="off">
					<input name="id" class="input" value="'.$id.'" autocomplete="off"></td>

				</tr>

				<tr>

					<td></td>
					<td><input style="float:left;" type="submit" name="submit" class="btn color3" value="Search"></td>

				</tr>

			</table></form>

			'.$show.'



			
	';



}


// STAGE 2
if($stage==2){

if(empty($id)){die('No ID');}

// UPDATE MYSQL THAT THIS IS A DISPUTED ORDER AND CAN'T BE REFUNDED

$st2update = mysql_query("UPDATE `orders` SET `disputed` = '1' WHERE `id` = '$id' LIMIT 1");
$st3update = mysql_query("UPDATE `users` SET `fraud` = '1' WHERE `emailaddress` = '$emailaddress_s' LIMIT 1");

$getrefundinfoq = mysql_query("SELECT * FROM `orders` WHERE `id` = '$id' LIMIT 1");
$refundinfo = mysql_fetch_array($getrefundinfoq);

mysql_query("INSERT INTO `blacklist` 
	SET 
	 `emailaddress` =  '{$refundinfo['emailaddress']}', 
	 `igusername` =  '{$refundinfo['igusername']}', 
	 `ipaddress` =  '{$refundinfo['ipaddress']}', 
	 `contactnumber` =  '{$refundinfo['contactnumber']}',
	 `lastfour` =  '{$refundinfo['lastfour']}'
	");


if($st2update)$success = '<div class="emailsuccess">Order #'.$id.' has been marked as a chargeback</div>';

$q2 = mysql_query("SELECT * FROM `orders` WHERE `id` = '$id' LIMIT 1");
if(mysql_num_rows($q2)=='0'){die('No ID found on stage 2');}
$info = mysql_fetch_array($q2);

if($info['refund']=='2'){

	if($info['refundtime']!=='0')$refundtime2 = date('l jS \of F Y H:i:s ', $info['refundtime']);

	$refundrow = '<tr><td>Refund time</td><td><input class="input" name="refundtime" value="'.$refundtime2.'"></td></tr>
	<tr><td>Refund amount</td><td><input class="input" name="refundamount" value="£'.sprintf('%.2f', $info['price'] / 100).'"></td></tr>';}

if($info['chooseposts']){

$chooseposts= explode(' ', $info['chooseposts']);
foreach($chooseposts as $post){

if(empty($post))continue;

$chooseposts2 .= 'https://instagram.com/'.$post.' ';

}


$chooseposts2 = '<tr><td>Posts selected</td><td><input class="input" name="igposts" value="'.$chooseposts2.'"></td><tr>';

}


if(!empty($info['contactnumber']))$contactnumber = '<tr><td>Contact number: </td><td><input class="input" name="contactnumber" value="'.$info['contactnumber'].'"></td></tr>';

if(!empty($info['lastrefilled']))$lastrefilled = '<tr><td>Last refilled: </td><td><input class="input" name="lastrefilled" value="'.date('l jS \of F Y H:i:s ', $info['lastrefilled']).'"></td></tr>';

$now = time();

$fulfills = explode(' ',trim($info['fulfill_id']));

foreach($fulfills as $fulfillorder){

	if(empty($fulfillorder))continue;

	$thisorderstatus .= '<a target="_BLANK" rel="noopener noreferrer" href="'.$fulfillmentsite.'/orders?search='.$fulfillorder.'">'.$fulfillorder.'</a><br>';

}

$show = $success.'<form method="POST" action="">
<table class="perorder">
<tr><td>'.$info['id'].'</td><td>Order Details</td></tr>
<tr><td>Report date: </td><td><input class="input" name="reportdate" value="'.date('l jS \of F Y H:i:s ', $now).'"></td></tr>
<tr><td>Order made: </td><td><input class="input" name="ordermade" value="'.date('l jS \of F Y H:i:s ', $info['added']).'"></td></tr>
<tr><td>Order completed: </td><td><input class="input" name="ordercompleted" value="'.date('l jS \of F Y H:i:s ', $info['fulfilled']).'"></td></tr>
<tr><td>Order last checked: </td><td><input class="input" name="orderlastchecked" value="'.date('l jS \of F Y H:i:s ', $info['lastchecked']).'"></td></tr>
'.$lastrefilled.'
<tr><td>Order ID: </td><td><input class="input" name="orderid" value="'.$info['id'].'"></td></tr>
<tr><td>Email address: </td><td><input class="input" name="emailaddress" value="'.$info['emailaddress'].'"></td></tr>
'.$contactnumber.'
<tr><td>Instagram username: </td><td><input class="input" name="igusername" value="https://instagram.com/'.$info['igusername'].'"></td></tr>
'.$chooseposts2.'
<tr><td>Package: </td><td><input class="input" name="package" value="'.$info['amount'].' '.$info['packagetype'].'"></td></tr>
<tr><td>Order Supplier ID: </td><td>'.$thisorderstatus.'</a></td></tr>
<tr><td>Order Start count: </td><td><input class="input" name="startcount" value=""></td></tr>
<tr><td>Order Status: </td><td><input class="input" name="orderstatus" value="Completed"></td></tr>


</table>

<table class="perorder">
<tr><td>'.$info['id'].'</td><td>Billing Details</td></tr>
<tr><td>Amount paid: </td><td><input class="input" name="price" value="£'.sprintf('%.2f', $info['price'] / 100).'"></td></tr>
<tr><td>Acquirer reference number: </td><td><input class="input" name="arn" value=""></td></tr>
<tr><td>Payment ID: </td><td><input class="input" name="payment_id" value="'.$info['payment_id'].'"></td></tr>
<tr><td>Short ID: </td><td><input class="input" name="payment_id_desc" value="'.$info['payment_id_desc'].'"></td></tr>
<tr><td>Card Last 4-digits: </td><td><input class="input" name="lastfour" value="'.$info['lastfour'].'"></td></tr>
<tr><td>Card brand: </td><td><input class="input" name="cardbrand" value=""></td></tr>
<tr><td>Card Expiry: </td><td><input class="input" name="cardexpiry" value=""></td></tr>
<tr><td>Card Billing name: </td><td><input class="input" name="cardbillingname" value=""></td></tr>
<tr><td>Card Billing postcode: </td><td><input class="input" name="cardpostcode" value=""></td></tr>
'.$refundrow.'
<tr><td>IP Address: </td><td><input class="input" name="ipaddress" value="'.$info['ipaddress'].'"></td></tr>
<tr><td>Personal note: </td><td><textarea class="input" name="personalnote" value=""></textarea></td></tr>
<tr><td></td><td>
<input type="hidden" name="stage" value="3">
<input style="float:left;" type="submit" name="submit" class="btn btn3 report" value="Create chargeback report"></td></tr>
</table>


			</form>';




}


// STAGE 2
if($stage==3){

if(!empty($_POST['personalnote'])){$notes = 'Notes from our payment team: <i>"'.$_POST['personalnote'].'"</i><br><br>';}

if(!empty($_POST['lastrefilled'])){$lastrefilled = '<tr><td>Order last refill</td><td>'.$_POST['lastrefilled'].'</td></tr>';}
if(!empty($_POST['refundtime'])){$refundrow1 = '<tr><td>Refund time</td><td>'.$_POST['refundtime'].'</td></tr>';}
if(!empty($_POST['refundamount'])){$refundrow2 = '<tr><td>Amount refunded</td><td>'.$_POST['refundamount'].'</td></tr>';}
if(!empty($_POST['ipaddress'])){$ipaddressrow = '<tr><td>IP Address</td><td>'.$_POST['ipaddress'].'</td></tr>';}
if(!empty($_POST['igposts'])){$posts = '<tr><td>Order for Instagram Posts</td><td>'.$_POST['igposts'].'</td></tr>';}


$htmlfile = '

<!DOCTYPE html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
.ordertbl td{padding:7px 19px;border-bottom:1px solid #dedede;font-size: 14px;}

</style>
</head>
<body style="padding:0 30px 0 30px;font-family:arial;">

<img style="width:496px;height:72px;margin-bottom:20px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfAAAABICAIAAACGIDAdAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpBQzg2RDAwOUFFRTZFOTExODVGQkRDMEJDMjZGQkMwRCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpCRTA4RkE0MDIyQjExMUVCOEY3MkRGM0RDNDM2RjQ3RiIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpCRTA4RkEzRjIyQjExMUVCOEY3MkRGM0RDNDM2RjQ3RiIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M2IChXaW5kb3dzKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkFFODZEMDA5QUVFNkU5MTE4NUZCREMwQkMyNkZCQzBEIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkFDODZEMDA5QUVFNkU5MTE4NUZCREMwQkMyNkZCQzBEIi8+IDwvcmRmOkRlc2NyaXB0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" alt="" />
<br>
<br>
<span><b>Acquirer Reference Number: #'.str_replace('#','',$_POST['arn']).'</b></span><br>
<br>
<div style="line-height: 24px;">'.$notes.'Here is a detailed report on the payment and order details for this transaction.</div>
<br>
<br>
<table class="ordertbl" style="border:1px solid;width:600px;">
<tr><td style="    background-color: #00870a;color:#fff">Payment and billing details</td><td style="    background-color: #00870a;color:#fff"></td></tr>
<tr><td>Merchant ID</td><td>104960042141</td></tr>
<tr><td>Amount paid</td><td>£'.str_replace('£','',$_POST['price']).'</td></tr>
<tr><td>Chargeback Report Date</td><td>'.$_POST['reportdate'].'</td></tr>
<tr><td>Order made on</td><td>'.$_POST['ordermade'].'</td></tr>
<tr><td>Acquirer Reference Number</td><td>#'.str_replace('#','',$_POST['arn']).'</td></tr>
<tr><td>Short ID</td><td>'.$_POST['payment_id_desc'].'</td></tr>
<tr><td>UUID (TotalProcessing ID)</td><td>'.$_POST['payment_id'].'</td></tr>
<tr><td>Payment Brands</td><td>'.$_POST['cardbrand'].'</td></tr>
<tr><td>Card Billing Name</td><td>'.$_POST['cardbillingname'].'</td></tr>
<tr><td>Card Billing Postcode</td><td>'.$_POST['cardpostcode'].'</td></tr>
<tr><td>Card Expiry</td><td>'.$_POST['cardexpiry'].'</td></tr>
'.$ipaddressrow.'
'.$refundrow1.'
'.$refundrow2.'
</table>

<br>
<br>
<br>
<table class="ordertbl" style="border:1px solid;width:600px;">
<tr><td style="    background-color: #00870a;color:#fff">Order details</td><td style="    background-color: #00870a;color:#fff"></td></tr>
<tr><td>Order ID</td><td>#'.str_replace('#','',$_POST['orderid']).'</td></tr>
<tr><td>Email address</td><td>'.$_POST['emailaddress'].'</td></tr>
<tr><td>Package</td><td>'.$_POST['package'].'</td></tr>
<tr><td>IG username</td><td>'.$_POST['igusername'].'</td></tr>
'.$posts.'
<tr><td>Contact number</td><td>'.$_POST['contactnumber'].' (provided by customer for tracking information)</td></tr>
<tr><td>Order made on</td><td>'.$_POST['ordermade'].'</td></tr>
<tr><td>Order status</td><td>'.$_POST['orderstatus'].'</td></tr>
'.$lastrefilled.'
<tr><td>Order completed</td><td>'.$_POST['ordercompleted'].'</td></tr>
<tr><td>Start count</td><td>'.$_POST['startcount'].'</td></tr>
'.$ipaddressrow.'
</table>
<br>
<br><b>Our notification process:</b>
<ol style="font-size:15px;">
<li>We provide clear instructions on our terms and conditions, that once payment is made, we’ll begin their order.</li>
<li>Once payment is made, we email the customer to the email address that the payment has been made, order has started and we give a summary of what their order is.</li>
<li>Once the order is complete, we’ll email the customer that the order is complete. If the customer has provided us with their contact number, for tracking information, we’ll send an automated text message to confirm the order is complete.</li>
</ol>
<br><br>
<br><b>Our order process:</b>
To start a payment with us, our payment form is located on the following page: https://superviral.io/order/payment/. This is the only page where we take and process payments. This particular page can only be accessed if you\'re placing an order with an order session, so trying to access the page directly will redirect you to the homepage.
<ol style="font-size:15px;">
<li>The customer arrives at https://superviral.io/order/details/ to fill in their email address and Instagram username.</li>
<li>The customer selects "next" once they\'ve filled their details in.</li>
<li>If the customer is ordering for likes or views, they\'re then redirected to https://superviral.io/order/select/. If they\'re not order likes/views, then our system automatically skips this step and go directly to the page https://superviral.io/order/review/.</li>
<li>On this page, the customer reviews their order and can add any bonus packages to increase their growth. If they\'re not happy with any bonus packages they can always remove this. Bonus packages are not applied by default and are optional.</li>
<li>Once they\'ve confirmed their order details, they\'re then redirected to the payments page: https://superviral.io/order/payment/</li>
<li>On this particular page can either pay with ApplePay (if they\'re using an Apple pay enabled device) or pay with debit/credit card.</li>
<li>To pay with a debit/credit card, the customer must provide their Billing name, card number, expiry date, CVV and Billing postcode. In order to make payments, as laid out in our terms and conditions, they must accept our terms and conditions.</li>
<li>As our payments are online-only, the customer does not have to provide their physical signature. However, they must provide all appropriate billing details, listed above, so we can make sure this is a genuine customer and not a fraudulent customer.</li>
<li>Once payment is complete, we\'ll begin the customer\'s order instantly. As laid out in our terms and conditions, the customer is entitled to a full 30-day Refund Guarantee should the customer not be happy with the service. The 30-day satisfaction guarantee starts on the day the customer makes the payment and ends 30-days after the initial payment was made. This is a 30-day Satisfaction Guarantee with no questions asked, refunds are issued instantly within 30-days, should the customer request it.</li>


</ol>
<br><br>
<b>About Superviral.io/ITH Retail Group LTD:</b><br>
<p style="font-size:15px;">Superviral.io/ITH Retail Group LTD take fraudulent matters exceptionally seriously and has developed systems and processes to minimise any fraudulent activity wherever possible.<br>
In order to ensure that the customer is in fact the card holder, we ensure that validation of CVV and address of the payment information, and post payment in respect to confirmation of payments.<br>
Further to this we work with our payment provider Total Processing to ensure that information which is passed to us is validated and any instances where suspicions are identified then orders will be placed on hold and/or refunded immediately. We also block the card from making any future payments to prevent future fraudulent payments.</p>




</body>
</html>
';

// CREATE A NEW HTML FILE AND ASSIGN HTML TO IT, SO IT CAN BE DOWNLOADABLE AFTER


$myfile = fopen("../chargebacks/".str_replace('#','',$_POST['arn']).".html", "w") or die("Unable to open file!");
fwrite($myfile, $htmlfile);
fclose($myfile);

echo '<a id="dl" style="display: none" href="https://superviral.io/chargebacks/'.str_replace('#','',$_POST['arn']).'.html" download>Download</a>
<script>
(function download() {
    document.getElementById(\'dl\').click();
})()
</script>
<div onload="download()"></div>';


$show = 'Email to: chargebacks@acquiring.com<br>
Subject: ACR '.$_POST['arn'].'<br>
Message: '.$_POST['personalnote'];


}



?>
<!DOCTYPE html>
<head>
<title>Chargebacks</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">
<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
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


.emailsuccess{    margin-bottom: 15px;}

<?=$styles?>

</style>
</head>

	<body>


		<?=$header?>



		<h1 style="text-align:center;margin-top:30px;">Chargeback reports</h1>

		<a href="https://superviral.io/admin/chargebacks.php" class="btn btn3 report" style="    margin: 30px auto!important;
    display: block;
    width: 160px;
    float: none;">Back to stage 1</a>

<div class="box23">

<?=$show?>

</div>

<script>

</script>

	</body>
</html>