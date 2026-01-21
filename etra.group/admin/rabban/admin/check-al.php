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
$orderid = str_replace('AL','',$orderid);

$submitreport = addslashes($_POST['submitreport']);
$reportmessage = addslashes($_POST['reportmessage']);
$reportorderid = addslashes($_POST['reportorderid']); 
$reportemailaddress = addslashes($_POST['reportemailaddress']); 
$deletereport = addslashes($_POST['deletereport']);
$deletereportid = addslashes($_POST['deletereportid']);
$refundid = addslashes($_POST['refundid']);
$undorefundid = addslashes($_POST['undorefundid']);
$pauseal = addslashes($_POST['pauseal']);
$resumeal = addslashes($_POST['resumeal']);
$cancelal = addslashes($_POST['cancelal']);
$changeusernameorderid = addslashes($_POST['changeusernameorderid']);
$changeigusername = addslashes($_POST['igusername']);
$duplicateal = addslashes($_POST['duplicateal']);


if(empty($orderid))$orderid = addslashes(trim($_GET['orderid']));
if(empty($user))$user = addslashes(trim($_GET['user']));

if(!empty($deletereport)){mysql_query("DELETE FROM `admin_notifications` WHERE `id` = '{$deletereportid}' LIMIT 1");}

if(!empty($refundid)){mysql_query("UPDATE `automatic_likes_billing` SET `refunded` = '1' WHERE `id` = '{$refundid}' LIMIT 1");}

if(!empty($undorefundid)){	mysql_query("UPDATE `automatic_likes_billing` SET `refunded` = '0' WHERE `id` = '{$undorefundid}' LIMIT 1");}


if(!empty($pauseal)){	mysql_query("UPDATE `automatic_likes` SET `disabled` = '1' WHERE `id` = '{$pauseal}' LIMIT 1");
$orderid = $pauseal;}

if(!empty($resumeal)){	mysql_query("UPDATE `automatic_likes` SET `disabled` = '0' WHERE `id` = '{$resumeal}' LIMIT 1");
$orderid = $resumeal;}


if(!empty($cancelal)){	mysql_query("UPDATE `automatic_likes` SET `cancelbilling` = '3' WHERE `id` = '{$cancelal}' LIMIT 1");
$orderid = $cancelal;
}


if(!empty($duplicateal)){	

$expireitnow = time();

mysql_query("UPDATE `automatic_likes` SET `cancelbilling` = '3',`disabled` = '1',`expires` = '$expireitnow' WHERE `id` = '{$duplicateal}' LIMIT 1");


mysql_query("UPDATE `automatic_likes_billing` SET `refunded` = '1' WHERE `auto_likes_id` = '{$duplicateal}' ORDER BY `id` DESC LIMIT 1");


$orderid = $duplicateal;
}


if((!empty($changeusernameorderid))&&(!empty($changeigusername))){	

	mysql_query("UPDATE `automatic_likes` SET `igusername` = '$changeigusername' WHERE `id` = '{$changeusernameorderid}' LIMIT 1");

	$orderid = $changeusernameorderid;

}




if((!empty($reportmessage))&&(!empty($submitreport))){

$added = time();

$q = mysql_query("INSERT INTO `admin_notifications` SET 
	`orderid` = 'AL{$reportorderid}', 
	`emailaddress` = '$reportemailaddress', 
	`message` = '$reportmessage', 
	`directions` = '', 
	`admin_name` = '{$_SESSION['admin_user']}', 
	`added` = '$added' 
	");

if(!$q){die('there was an error');}

}




if(!empty($user)){$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `emailaddress` LIKE '%$user%' OR `igusername` LIKE '%$user%' OR `payment_id` LIKE '%$user%'  ORDER BY `id` DESC");$field = '<input type="hidden" name="user" value="'.$user.'">';

}
if(!empty($orderid)){

	$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `id` = '$orderid' OR `lastfour` LIKE '%$orderid%' ORDER BY `id` DESC LIMIT 10");
	$field = '<input type="hidden" name="orderid" value="'.$orderid.'">';
}

if($q){



while($info = mysql_fetch_array($q)){



				if($info['disabled']=='0')
				{


				$orderstatus = '<font color="green">Customer wants AL to continue</font>';
				}


			else

				{


				$orderstatus = '<font color="orange">Customer/Our Team Paused AL</font>';


				}


				if($info['expires'] < time()){$orderstatus .= ' - <font color="red">Expired on '.date("d/m/Y H:i:s",$info['expires']).'</font>';}else{

					if($info['al_package_id']=='0'){
						$orderstatus .= ' - <font color="green">Will expire on '.date("d/m/Y H:i:s",$info['expires']).'</font>';
					}else{


						if($info['cancelbilling']!=='3'){$orderstatus .= ' - <font color="green">until they cancel the billing</font>';}
						else{$orderstatus .= ' - <font color="red">Billing cancelled: will expire on '.date("d/m/Y H:i:s",$info['expires']).'</font>';}



					}

				}

			


			$adminnotifsq = mysql_query("SELECT * FROM `admin_notifications` WHERE `orderid` = 'AL{$info['id']}' LIMIT 12");	
			while($adminnotifinfo = mysql_fetch_array($adminnotifsq))
			{

				if($adminnotifinfo['done']=='0'){$ifdelete = '😊 Waiting to be checked<br><input type="submit" name="deletereport" value="Delete Report">';
				$adminnotifcolor = 'background-color: #fff579;';}else{
					$ifdelete = '<br>✅ <span style="font-size: 12px;
    font-style: italic;">Checked by Admin, '.ago($adminnotifinfo['response']).', '.date("d/m/Y H:i:s",$adminnotifinfo['response']).')</span>';$adminnotifcolor = 'background-color: #cfff9b;';
				}


				if(!empty($adminnotifinfo['directions']))$adminresponse = '<hr><div>Admin Directions:<br>'.$adminnotifinfo['directions'].'</div>';

				$reportnotifs .= '<div class="adminnotif" style="'.$adminnotifcolor.'">'.$adminnotifinfo['message'].'<br><span style="font-size: 12px;
    font-style: italic;"> (reported by '.ucfirst($adminnotifinfo['admin_name']).' -  '.ago($adminnotifinfo['added']).', '.date("d/m/Y H:i:s",$adminnotifinfo['added']).')</span>

    			'.$adminresponse.'

				<form action="https://superviral.io/admin/check-al.php#order'.$info['id'].'" method="POST">'.$fields.'
				<input type="hidden" name="deletereportid" value="'.$adminnotifinfo['id'].'">
				'.$ifdelete.'
				</form></div>';

			unset($ifdelete);
			unset($adminresponse);
			unset($adminnotifinfo);
			unset($adminnotifinfo['directions']);

			}








if(($_SESSION['admin_user']=='rabban')||($_SESSION['admin_user']=='admin')){

$supplierfulfillidtd = $thisorderstatus;

if($_SESSION['admin_user']=='rabban'){


	if (strpos($info['payment_id'], 'pi_') !== false)$paymenttr = '<tr class="grey"><td>Pid: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://dashboard.stripe.com/payments/'.$info['payment_id'].'">'.$info['payment_id'].'</a></td></tr>
	<tr class="grey"><td>Pid short: </td><td>'.$info['payment_id_desc'].'</td></tr>';


		if (strpos($info['payment_id'], '-') !== false)$paymenttr = '<tr class="grey"><td>Pid: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://my.cardinity.com/payment/show/'.$info['payment_id'].'">'.$info['payment_id'].'</a></td></tr>
	<tr class="grey"><td>Pid short: </td><td>'.$info['payment_id_desc'].'</td></tr>';

}



}






if($info['disabled']=='0'){

		$changealstatus = '<form action="https://superviral.io/admin/check-al.php#order'.$info['id'].'" method="POST">
					<input type="hidden" name="pauseal" value="'.$info['id'].'">
					<input type="submit" onclick="return confirm(\'Are you sure you want to PAUSE this automatic likes?\');" class="btn btn3 report copy-button" style="width:180px;" value="Pause Automatic Likes"></form>';

	}
		else
	{


		$changealstatus = '<form action="https://superviral.io/admin/check-al.php#order'.$info['id'].'" method="POST">
					<input type="hidden" name="resumeal" value="'.$info['id'].'">
					<input type="submit" onclick="return confirm(\'Are you sure you want to RESUME this automatic like?\');" class="btn btn3 report copy-button" style="width:200px;" value="Resume Automatic Likes"></form>';

	}

if(!empty($changealstatus))$changealstatus = '<tr><td>Change AL Status: </td><td>'.$changealstatus.'</td><td></tr>';



if(($info['cancelbilling']=='0')&&($info['al_package_id']!=='0')){//CANCEL AL BILLING AND ITS NOT ON FREE TRIAL

		$cancelaltr = '<font color="green">Billing is currently active</font><br><form action="https://superviral.io/admin/check-al.php#order'.$info['id'].'" method="POST">
					<input type="hidden" name="cancelal" value="'.$info['id'].'">
					<input type="submit" onclick="return confirm(\'Are you sure you want to PERMANENTLY CANCEL this automatic like? This cannot be undone. Only do this when there is a DUPLICATE AL\');" class="btn btn3 report copy-button" style="width:180px;color:red!important;border:1px solid transparent!important; " value="CANCEL Automatic Likes"></form>';

	}

if(!empty($cancelaltr))$cancelaltr = '<tr><td>Cancel AL Billing Permanently: </td><td>'.$cancelaltr.'</td><td></tr>';



if(($info['cancelbilling']=='0')){

$duplicatealtr = '<form action="https://superviral.io/admin/check-al.php#order'.$info['id'].'" method="POST">
					<input type="hidden" name="duplicateal" value="'.$info['id'].'">
					<input type="submit" onclick="return confirm(\'Are you sure you want to deactivate this automatic like because of duplicate AL?\');" class="btn btn3 report copy-button" style="width:220px;" value="Deactivate this duplicate AL"></form>';

}

if(!empty($duplicatealtr))$duplicatealtr = '<tr><td>Is this a duplicate AL? All In One deactivation: </td><td>'.$duplicatealtr.'</td><td></tr>';


if($info['al_package_id']!=='0'){ //its paid for

	$paidsummary = '(Paid)';

	$country = $info['country'];

	if(!empty($info['billingfailure'])){

		$billingfailuremsg = ' (billingfailure)';
		$billingfailuretr = '<tr><td>Billing failure: </td><td>'.$billingstatus.'</td></tr>';
	}else{
		$nextbilledon = '<tr><td>Next billed on: </td><td>'.date('l jS \of F Y H:i:s ', $info['expires']).'</td></tr>';
	}


	$billingq = mysql_query("SELECT * FROM `automatic_likes_billing` WHERE `auto_likes_id` = '{$info['id']}' ORDER BY `id` DESC ");

	while($billinginfo = mysql_fetch_array($billingq)){



if($info['refund']=='2')$refunded = ' ';


		if($billinginfo['refunded']=='1')$refunded = ' <font color="orange">(refund in progress)</font>
				<form method="POST" action="?orderid='.$info['id'].'">
					<input type="hidden" name="undorefundid" value="'.$billinginfo['id'].'">
					<input style="float:left;" type="submit" name="submit" class="btn btn3 report" value="Undo">
				</form>';

		if(($billinginfo['refunded']!=='1')&&($billinginfo['refunded']!=='0'))$refunded = ' <font color="red">(refunded '.date('d/m/y ', $billinginfo['refunded']).')</font>';

		if($billinginfo['refunded']=='0')$refunded = '

				<form method="POST" action="?orderid='.$info['id'].'">
					<input type="hidden" name="refundid" value="'.$billinginfo['id'].'">
					<input style="float:left;" type="submit" name="submit" class="btn btn3 report" value="Refund this">
				</form>';

		$billingresults .= '
		<tr>
			<td>'.date('d/m/y ', $billinginfo['added']).'</td>
			<td>'.$billinginfo['amount'].' '.$billinginfo['currency'].'</td>
			<td>'.$billinginfo['likesperpost'].' likes per post</td>
			<td>
				'.$refunded.'
			</td>
		</tr>';

		unset($refunded);

	}


	$paymenttr = '<tr><td>💸 Monthly Payment: </td><td>'.$locas[$country]['currencysign'].$info['price'].' per month '.$billingfailuremsg.$refunded.'</td></tr>
	'.$billingfailuretr.'
	<tr><td>Card Last Four digits: </td><td>'.$info['lastfour'].'</td></tr>
	<tr><td>Billing History: </td>
	<td>
		<table class="billinghistory">
		'.$billingresults.'
		</table>
	</td></tr>
	'.$refundbtn;





}else{

	$paidsummary = '(Free - expires on '.date("d/m/Y H:i:s",$info['expires']).')';

	$paymenttr = '<tr><td>💸 Monthly Payment: </td><td>No monthly payment - free</td></tr>
	<tr><td>🚚 Billing status: </td><td>Free Likes</td></tr>';



}




			$summaryresults .='<tr>
				<td><a href="#order'.$info['id'].'">#'.$info['id'].'</a></td>
				<td>'.date('l jS \of F Y H:i:s ', $info['added']).'</td>
				<td>'.$orderstatus.'</td>
				</tr>';


				if($info['al_package_id']!=='0')$topcolor = 'paid';
				if($info['al_package_id']=='0')$topcolor = 'free';


			$results .= '


			<div class="box23">

			<table id="order'.$info['id'].'" class="perorder">


				<tr class="'.$topcolor.'"><td>AL Subscription #'.$info['id'].'</td><td></td></tr>
				<tr><td>#️⃣ AL Subscription:</td><td>'.$info['id'].' '.$paidsummary.'</td></tr>
				<tr><td>📧 Email address: </td><td><a href="https://superviral.io/admin/check-al.php?user='.$info['emailaddress'].'">'.$info['emailaddress'].'</a></td></tr>
				
				<tr><td>🧍 IG username: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://instagram.com/'.$info['igusername'].'">'.$info['igusername'].'</a></td></tr>
				<tr><td>🧍 AL package: </td><td>'.$info['likes_per_post'].' likes per post</td></tr>
				<tr><td>📞 Verified Contact number: </td><td>'.$info['contactnumber'].'</td></tr>
				<tr><td>⌚ Subscription started on: </td><td>'.date('l jS \of F Y H:i:s ', $info['added']).'</td></tr>
				'.$nextbilledon.'
				<tr><td>🚚 Subscription status: </td><td>'.$orderstatus.'</td></tr>
				'.$paymenttr.'
				
				<tr><td>📝 Change username: </td><td>
				<form action="https://superviral.io/admin/check-al.php#orderid='.$info['id'].'" method="POST">
				<input type="hidden" name="changeusernameorderid" value="'.$info['id'].'">
				<input type="input" class="input rectifyinput" name="igusername" placeholder="kevinhart" value="'.$info['igusername'].'">
				<input type="submit" onclick="return confirm(\'Are you sure you want to change the username?\');" class="btn btn3 report copy-button" style="width:150px;" value="Change Username">
				</form>

				</td></tr>

				<tr><td>📝 Report: </td><td>'.$reportnotifs.'
				<form action="https://superviral.io/admin/check-al.php#order'.$info['id'].'" method="POST">'.$submittedfield.$field.'
				<input type="hidden" name="reportorderid" value="'.$info['id'].'">
				<input type="hidden" name="reportemailaddress" value="'.$info['emailaddress'].'">
				<textarea class="reportmessage" name="reportmessage"></textarea>
				<div style="display:inline-block;width:100%;"><input type="submit" name="submitreport" class="btn btn3 report" value="Send Report">
				</div>
				</form>
				</td></tr>
				'.$offerfreefollowers.'
				'.$changealstatus.'
				'.$cancelaltr.'
				'.$duplicatealtr.'
			</table>


			</div>';

			unset($refunded);
			unset($lastrefilled);
			unset($rectify);
			unset($chooseposts);
			unset($reportnotifs);
			unset($orderstatus);
			unset($supplierfulfillid);
			unset($supplierfulfillidtd);
			unset($thisorderstatus);
			unset($refundbtn);
			unset($refundavail);
			unset($refundcolor);
			unset($billingfailuremsg);
			unset($billingfailuretr);
			unset($nextbilledon);
			unset($billingresults);
			unset($changealstatus);
			unset($cancelaltr);
			unset($duplicatealtr);
			unset($topcolor);

}

//if(!empty($results))$results = ''.$results.'';

}

if(!empty($summaryresults))$summaryresults = '<div class="box23"><table class="summarytbl"><tr>
	<td>AL ID</td>
	<td>Order made</td>
	<td>Status</td>
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

.perorder tr td .billinghistory{width: 100%;border:1px solid blue;}
.perorder tr td .billinghistory tr td{background:#fff;
    font-size: initial;
    font-weight: initial;font-size:12px;
    width: initial;
    vertical-align: inherit;
}


.paid td{background-color:#f9f15b!important;}
.free td{background-color: #ccc!important;}

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

		<h1 style="text-align:center;margin-top:35px;">Check Auto Likes 😄</h1>


		<?=$dontdosupportdiv?>


		<div class="box23">



			<form method="POST" action="#">
			<table class="articles">

				<tr>

					<td>👨👩 User:<span class="searchspan">Search by:<br>- IG username<br>- email address</span></td>
					<td><input name="user" class="input" value="<?=$user?>" autocomplete="off"></td>

				</tr>

				<tr>

					<td>❤️ Auto Likes:<span class="searchspan">Search by:<br>- Order ID<br>- Last Four Card Number e.g. 1234</span></td>
					<td><input name="orderid" class="input" value="<?=$orderid?>" autocomplete="off"></td>

				</tr>

				<tr>

					<td></td>
					<td><input style="float:left;" type="submit" name="submit" class="btn color3" value="Search"><a href="https://superviral.io/admin/check-al.php" class="btn btn3 report" style="float:right;">Reset Search</a>
					</td>

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