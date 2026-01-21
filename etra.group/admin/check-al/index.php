<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';


$tpl = file_get_contents('tpl.html');



$thisstaffmember = addslashes($_SESSION['first_name']);
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


$user = addslashes(trim($_POST['user']));
$orderid = addslashes(trim($_POST['orderid']));
$orderid = str_replace('AL','',$orderid);
$type = addslashes(trim($_POST['type']));


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

if(!empty($deletereport)){mysql_query("DELETE FROM `admin_notifications` WHERE `id` = '{$deletereportid}' AND brand = '$brand' LIMIT 1");}

if(!empty($refundid)){mysql_query("UPDATE `automatic_likes_billing` SET `refunded` = '1' WHERE `id` = '{$refundid}' AND brand = '$brand' LIMIT 1");}

if(!empty($undorefundid)){	mysql_query("UPDATE `automatic_likes_billing` SET `refunded` = '0' WHERE `id` = '{$undorefundid}' AND brand = '$brand' LIMIT 1");}


if(!empty($pauseal)){	mysql_query("UPDATE `automatic_likes` SET `disabled` = '1' WHERE `id` = '{$pauseal}' AND brand = '$brand' LIMIT 1");
$orderid = $pauseal;}

if(!empty($resumeal)){	mysql_query("UPDATE `automatic_likes` SET `disabled` = '0' WHERE `id` = '{$resumeal}' AND brand = '$brand' LIMIT 1");
$orderid = $resumeal;}


if(!empty($cancelal)){	mysql_query("UPDATE `automatic_likes` SET `cancelbilling` = '3' WHERE `id` = '{$cancelal}' AND brand = '$brand' LIMIT 1");
$orderid = $cancelal;
}


if(!empty($duplicateal)){	

$expireitnow = time();

mysql_query("UPDATE `automatic_likes` SET `cancelbilling` = '3',`disabled` = '1',`expires` = '$expireitnow' WHERE `id` = '{$duplicateal}' AND brand = '$brand' LIMIT 1");


mysql_query("UPDATE `automatic_likes_billing` SET `refunded` = '1' WHERE `auto_likes_id` = '{$duplicateal}' AND brand = '$brand' ORDER BY `id` DESC LIMIT 1");


$orderid = $duplicateal;
}


if((!empty($changeusernameorderid))&&(!empty($changeigusername))){	

	mysql_query("UPDATE `automatic_likes` SET `igusername` = '$changeigusername' WHERE `id` = '{$changeusernameorderid}' AND brand = '$brand' LIMIT 1");

	$orderid = $changeusernameorderid;

}




if((!empty($reportmessage))&&(!empty($submitreport))){

$added = time();

$q = mysql_query("INSERT INTO `admin_notifications` SET 
	`orderid` = 'AL{$reportorderid}', 
	`emailaddress` = '$reportemailaddress', 
	`message` = '$reportmessage', 
	`directions` = '', 
	`admin_name` = '{$_SESSION['first_name']}', 
	`added` = '$added',
     brand = '$brand' 
	");

if(!$q){die('there was an error');}

}



if(!empty($user)){
	
	if($type == "Paid"){
		$q = mysql_query("SELECT * FROM `automatic_likes` WHERE brand = '$brand' AND `emailaddress` LIKE '%$user%' OR `igusername` LIKE '%$user%' OR `payment_id` LIKE '%$user%'  ORDER BY `id` DESC");$field = '<input type="hidden" name="user" value="'.$user.'">';
		$paidTypeSelected = "selected";
	}
	else{
		$q = mysql_query("SELECT * FROM `automatic_likes_free` WHERE `emailaddress` LIKE '%$user%' OR `igusername` LIKE '%$user%' ORDER BY `id` DESC");$field = '<input type="hidden" name="user" value="'.$user.'">';
		$freeTypeSelected = "selected";
	
	}

}
if(!empty($orderid)){

	if($type == "Paid"){
		$q = mysql_query("SELECT * FROM `automatic_likes` WHERE brand = '$brand' AND `id` = '$orderid' OR `lastfour` LIKE '%$orderid%' OR `payment_id` LIKE '%$orderid%' ORDER BY `id` DESC LIMIT 10");
		$field = '<input type="hidden" name="orderid" value="'.$orderid.'">';
		$paidTypeSelected = "selected";
	}else{
		$q = mysql_query("SELECT * FROM `automatic_likes_free` WHERE `id` = '$orderid' ORDER BY `id` DESC LIMIT 10");
		$field = '<input type="hidden" name="orderid" value="'.$orderid.'">';
		$freeTypeSelected = "selected";
	}
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

			


			$adminnotifsq = mysql_query("SELECT * FROM `admin_notifications` WHERE `orderid` = 'AL{$info['id']}' AND brand = '$brand' LIMIT 12");	
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

				<form action="/admin/check-al/#order'.$info['id'].'" method="POST">'.$fields.'
				<input type="hidden" name="deletereportid" value="'.$adminnotifinfo['id'].'">
				'.$ifdelete.'
				</form></div>';

			unset($ifdelete);
			unset($adminresponse);
			unset($adminnotifinfo);
			unset($adminnotifinfo['directions']);

			}








	if(($_SESSION['first_name']=='rabban')||($_SESSION['first_name']=='admin')){

	$supplierfulfillidtd = $thisorderstatus;

	if($_SESSION['first_name']=='rabban'){


		if (strpos($info['payment_id'], 'pi_') !== false)$paymenttr = '<tr class="grey"><td>Pid: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://dashboard.stripe.com/payments/'.$info['payment_id'].'">'.$info['payment_id'].'</a></td></tr>
		<tr class="grey"><td>Pid short: </td><td>'.$info['payment_id_desc'].'</td></tr>';


			if (strpos($info['payment_id'], '-') !== false)$paymenttr = '<tr class="grey"><td>Pid: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://my.cardinity.com/payment/show/'.$info['payment_id'].'">'.$info['payment_id'].'</a></td></tr>
		<tr class="grey"><td>Pid short: </td><td>'.$info['payment_id_desc'].'</td></tr>';

	}



	}






	if($info['disabled']=='0'){

			$changealstatus = '<form action="/admin/check-al/#order'.$info['id'].'" method="POST">
						<input type="hidden" name="pauseal" value="'.$info['id'].'">
						<input type="submit" onclick="return confirm(\'Are you sure you want to PAUSE this automatic likes?\');" class="btn btn3 report copy-button" style="width:180px;" value="Pause Automatic Likes"></form>';

		}
			else
		{


			$changealstatus = '<form action="/admin/check-al/#order'.$info['id'].'" method="POST">
						<input type="hidden" name="resumeal" value="'.$info['id'].'">
						<input type="submit" onclick="return confirm(\'Are you sure you want to RESUME this automatic like?\');" class="btn btn3 report copy-button" style="width:200px;" value="Resume Automatic Likes"></form>';

		}

	if(!empty($changealstatus))$changealstatus = '<tr><td>Change AL Status: </td><td>'.$changealstatus.'</td><td></tr>';



	if(($info['cancelbilling']=='0')&&($info['al_package_id']!=='0')){//CANCEL AL BILLING AND ITS NOT ON FREE TRIAL

			$cancelaltr = '<font color="green">Billing is currently active</font><br><form action="/admin/check-al/#order'.$info['id'].'" method="POST">
						<input type="hidden" name="cancelal" value="'.$info['id'].'">
						<input type="submit" onclick="return confirm(\'Are you sure you want to PERMANENTLY CANCEL this automatic like? This cannot be undone. Only do this when there is a DUPLICATE AL\');" class="btn btn3 report copy-button" style="width:180px;color:red!important;border:1px solid transparent!important; " value="CANCEL Automatic Likes"></form>';

		}

	if(!empty($cancelaltr))$cancelaltr = '<tr><td>Cancel AL Billing Permanently: </td><td>'.$cancelaltr.'</td><td></tr>';



	if(($info['cancelbilling']=='0')){

	$duplicatealtr = '<form action="/admin/check-al/#order'.$info['id'].'" method="POST">
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


		$billingq = mysql_query("SELECT * FROM `automatic_likes_billing` WHERE `auto_likes_id` = '{$info['id']}' AND brand = '$brand' ORDER BY `id` DESC ");

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

					$brandName = getBrandSelectedName($brand);
				$results .= '


				<div class="box23">

				<table id="order'.$info['id'].'" class="perorder">


					<tr class="'.$topcolor.'"><td>AL Subscription #'.$info['id'].'</td><td></td></tr>
					<tr><td>🏢 Company:</td><td style=""><img src="/admin/assets/icons/'. $brandName .'.svg"></td></tr>
					<tr><td>#️⃣ Payment Id:</td><td>'.$info['payment_id'].'</td></tr>
					<tr><td>#️⃣ AL Subscription:</td><td>'.$info['id'].' '.$paidsummary.'</td></tr>
					<tr><td>📧 Email address: </td><td><a href="/admin/check-al/?user='.$info['emailaddress'].'">'.$info['emailaddress'].'</a></td></tr>

					<tr><td>🧍 IG username: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://instagram.com/'.$info['igusername'].'">'.$info['igusername'].'</a></td></tr>
					<tr><td>🧍 AL package: </td><td>'.$info['likes_per_post'].' likes per post</td></tr>
					<tr><td>📞 Verified Contact number: </td><td>'.$info['contactnumber'].'</td></tr>
					<tr><td>⌚ Subscription started on: </td><td>'.date('l jS \of F Y H:i:s ', $info['added']).'</td></tr>
					'.$nextbilledon.'
					<tr><td>🚚 Subscription status: </td><td>'.$orderstatus.'</td></tr>
					'.$paymenttr.'

					<tr><td>📝 Change username: </td><td>
					<form action="/admin/check-al/#orderid='.$info['id'].'" method="POST">
					<input type="hidden" name="changeusernameorderid" value="'.$info['id'].'">
					<input type="input" class="input rectifyinput" name="igusername" placeholder="kevinhart" value="'.$info['igusername'].'">
					<input type="submit" onclick="return confirm(\'Are you sure you want to change the username?\');" class="btn btn3 report copy-button" style="width:150px;" value="Change Username">
					</form>

					</td></tr>

					<tr><td>📝 Report: </td><td>'.$reportnotifs.'
					<form action="/admin/check-al/#order'.$info['id'].'" method="POST">'.$submittedfield.$field.'
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

$tpl = str_replace('{dontdosupportdiv}',$dontdosupportdiv,$tpl);
$tpl = str_replace('{dontdosupportcss}',$dontdosupportcss,$tpl);
$tpl = str_replace('{orderid}',$orderid,$tpl);
$tpl = str_replace('{user}',$user,$tpl);
$tpl = str_replace('{summaryresults}',$summaryresults,$tpl);
$tpl = str_replace('{results}',$results,$tpl);
$tpl = str_replace('{freeTypeSelected}',$freeTypeSelected,$tpl);
$tpl = str_replace('{paidTypeSelected}',$paidTypeSelected,$tpl);


output($tpl, $options);
