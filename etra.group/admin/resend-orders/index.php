<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';


$tpl = file_get_contents('tpl.html');



$thisstaffmember = addslashes($_SESSION['first_name']);
$date_of_absence = date('d-m-Y', time());

$checkstaffq = mysql_query("SELECT * FROM `staff_absence` WHERE `staff` = '$thisstaffmember' AND `date_of_absence` = '$date_of_absence' LIMIT 1");


if (mysql_num_rows($checkstaffq) == 1) {

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

$submitreport = addslashes($_POST['submitreport']);
$reportmessage = addslashes($_POST['reportmessage']);
$reportorderid = addslashes($_POST['reportorderid']);
$reportemailaddress = addslashes($_POST['reportemailaddress']);
$deletereport = addslashes($_POST['deletereport']);
$deletereportid = addslashes($_POST['deletereportid']);

if (empty($user)) $user = addslashes(trim($_GET['user']));

if (!empty($deletereport)) {
	mysql_query("DELETE FROM `admin_notifications` WHERE `id` = '$deletereportid' AND brand ='$brand' LIMIT 1");
}

if ((!empty($reportmessage)) && (!empty($submitreport))) {

	$added = time();

	$q = mysql_query("INSERT INTO `admin_notifications` SET 
	`orderid` = '$reportorderid', 
	`emailaddress` = '$reportemailaddress', 
	`message` = '$reportmessage', 
	`directions` = '', 
	`admin_name` = '{$_SESSION['first_name']}', 
	`added` = '$added',
     brand ='$brand' 
	");

	if (!$q) {
		die('there was an error');
	}
}

if (!empty($user)) {
	/*
	$q = mysql_query("SELECT * FROM `orders` WHERE (brand ='$brand' AND `emailaddress` LIKE '%$user%') OR (brand ='$brand' AND `igusername` LIKE '%$user%') OR (brand ='$brand' AND `payment_id` LIKE '%$user%') ORDER BY `id` DESC");
	$field = '<input type="hidden" name="user" value="' . $user . '">';*/


	$q = mysql_query("SELECT * FROM `orders` WHERE 
		(brand ='$brand' AND MATCH(`emailaddress`) AGAINST('\"$user\"' IN NATURAL LANGUAGE MODE) AND refund = 0) 
		OR (brand ='$brand' AND MATCH(`igusername`) AGAINST('\"$user\"' IN NATURAL LANGUAGE MODE) AND refund = 0) 
		OR (brand ='$brand' AND MATCH(`payment_id`) AGAINST('\"$user\"' IN NATURAL LANGUAGE MODE) AND refund = 0) 
		ORDER BY `id` DESC");
	$field = '<input type="hidden" name="user" value="' . $user . '">';
}
if ($q) {


$inProgressCount = 0;
	while ($info = mysql_fetch_array($q)) {

		

		//if(($info['packagetype']=='freefollowers')||($info['packagetype']=='freelikes'))continue;

		$packagetype = $info['amount'] . ' ' . $info['packagetype'];

		$price = sprintf('%.2f', $info['price'] / 100);

		if (!empty($info['order_response'])) {

			$i = 0;
			$trackinginformation1 = explode('~~~', $info['order_response'] . $info['order_response_finish']);
			foreach ($trackinginformation1 as $atracking) {
				$i++;
				if ($i == 1) continue;
				$atracking1 = explode('###', $atracking);
				$trackinginformation .= '<div class="trackinginfo"><div class="trackingheader">' . date('l jS \of F Y H:i:s ', $atracking1[0]) . '</div>' . $atracking1[1] . '(' . $atracking1[2] . ' seconds) </div>';
			}


			$trackinginformation = '<tr><td>🗄️ Tracking information: </td><td>' . $trackinginformation . '</td></tr>';
		}

		if ($info['fulfilled'] == '0') {

			$amounto = $info['amount'];

			if ($amounto >= 1 && $amounto <= 150) {
				$approx = '9-10 hours';
			}
			if ($amounto >= 151 && $amounto <= 250) {
				$approx = '12-13 hours';
			}
			if ($amounto >= 251 && $amounto <= 380) {
				$approx = '14-15 hours';
			}
			if ($amounto >= 500 && $amounto <= 999) {
				$approx = '14-15 hours';
			}
			if ($amounto >= 1000 && $amounto <= 1500) {
				$approx = '24-28 hours';
			}
			if ($amounto >= 2500 && $amounto <= 3750) {
				$approx = '27-35 hours';
			}
			if ($amounto >= 5000 && $amounto <= 8000) {
				$approx = '38-48 hours';
			}

			if (!empty($approx)) $approx1 = '(will take around ' . $approx . ')';

			$orderstatus = '<font color="orange">In progress ' . $approx1 . '</font>';
			$arstatus = 'in progress';
			$artime = ' Please provide up to ' . $approx . ' for your order to be delivered. ';
		} else {
			continue;
			$orderstatus = '<font color="green">Completed: ' . date('l jS \of F Y H:i:s ', $info['fulfilled']) . '</font>';
			$arstatus = 'completed';
		}

		$findordercostq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = '{$info['packagetype']}'  AND brand ='$brand' LIMIT 1");
		$findordercostinfo = mysql_fetch_array($findordercostq);

		$saleamount = $price;
		$salecost = $findordercostinfo['perone'] * ($info['amount']);

		$saleamount = round(0.58 * ($saleamount - $salecost), 2);
		//	echo $saleamount.' - '.$salecost;;

		if ($info['packagetype'] == 'followers') {
			$searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'likes'  AND brand ='$brand' LIMIT 1");
			$refundpackageinfo = mysql_fetch_array($searchpackageq);
			$refundoffertype = 'likes';
			$ardestination = 'posts';
		}

		if ($info['packagetype'] == 'likes') {
			$searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'followers'  AND brand ='$brand' LIMIT 1");
			$refundpackageinfo = mysql_fetch_array($searchpackageq);
			$refundoffertype = 'followers';
			$ardestination = 'profile';
		}

		if ($info['packagetype'] == 'views') {
			$searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'likes'  AND brand ='$brand' LIMIT 1");
			$refundpackageinfo = mysql_fetch_array($searchpackageq);
			$refundoffertype = 'likes';
			$ardestination = 'posts';
		}

		if (!empty($refundpackageinfo['perone'])) {
			$refundoffer1 = round($saleamount / $refundpackageinfo['perone']);
		}

		$refundoffer2 = round($refundoffer1 * $refundpackageinfo['perone'], 2);

		//$refundoffers = round($amount).' likes';
		$refundoffers = 'upto ' . $refundoffer1 . ' ' . $refundoffertype . ' - £' . $refundoffer2;
		$arrefundsen = $refundoffer1 . ' ' . $refundoffertype . ' to your ' . $ardestination;

		if (($info['packagetype'] == 'followers') || ($info['packagetype'] == 'views')) {
			$arextendsentence = 'We can also split the 1000 likes to multiple posts.';
		}

		
		$fulfills = explode(' ', trim($info['fulfill_id']));

		foreach ($fulfills as $fulfillorder) {

			if (empty($fulfillorder)) continue;

			$thisorderstatus .= '<a target="_BLANK" rel="noopener noreferrer" href="' . $fulfillmentsite . '/orders?search=' . $fulfillorder . '">' . $fulfillorder . '</a><br>';
		}


		if ($info['refund'] == '1') {
			$refundcolor = 'orange';
			$refunded = ' <font color="orange">(refund in progress)</font>';
		} else {
			$refundcolor = 'grey';
		}
		if ($info['refund'] == '2') $refunded = ' <font color="red">(refunded)</font>';

		if ($info['packagetype'] == 'followers') $lastrefilled = '<tr><td>♻️ Last refiled on: </td><td>' . date('l jS \of F Y H:i:s ', $info['lastrefilled']) . '</td></tr>';

		if (!empty($info['chooseposts'])) {
			$chooseposts = '<tr><td>👉 Posts for ' . $info['packagetype'] . ': </td><td>' . $info['chooseposts'] . '</td></tr>';
		}

		$supplierfulfillid = '<tr class="grey"><td>Supplier Fulfill ID: </td><td>' . $thisorderstatus . '</td></tr>';
		$supplierfulfillidtd = $thisorderstatus;
		
		$summaryresults .= '<tr>
				<td style="width: 186px;"><a href="#order' . $info['id'] . '">#' . $info['id'] . ' - ' . $packagetype . '</a></td>
				<td>' . date('l jS \of F Y H:i:s ', $info['added']) . '</td>
				<td>' . $orderstatus . '</td>
				<td>' . $supplierfulfillidtd . '</td>
				</tr>';


		if ($info['refundtime'] !== '0') $refunddate = '<tr><td>Refund issued on : </td><td>' . date('l jS \of F Y H:i:s ', $info['refundtime']) . '</td></tr>';

		$domain = getBrandSelectedDomain($brand);
		$brandSource = getBrandSelectedSource($brand);
		$UserName  = $info['igusername'];
		if ($brand == 'tp' || $brand == 'to') $sourceURL = "https://$brandSource/@$UserName/";
		else $sourceURL = "https://$brandSource/$UserName/";

		$commentSection = "";
		if ($info['packagetype'] == 'comments') {

			$commentSession = "	SELECT oc.* FROM order_comments oc 
			INNER JOIN order_session os 
			ON oc.order_session_id = os.id
			WHERE os.order_session = '" . $info['order_session'] . "'";
			$runSessionQry = mysql_query($commentSession);

			$commentsList = "";

			$ic = 1;
			while ($commentsData = mysql_fetch_array($runSessionQry)) {
				$commentsList .= "<p>$ic. " . $commentsData['comment'] . " - " . strtoupper($commentsData['tags']) . "</p>";
				$ic++;
			}

			$commentSection = '<tr><td>📦 Comments: </td><td>
								<section>
									<article>
									  <details>
										<summary><u>Click to see comments selected</u></summary>
										  ' . $commentsList . '
									  </details>
									</article>
									
    							</section></td></tr>';
		}
		$ids[] = $info['id'];
		$inProgressCount++;
		
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

	$ids = implode(',',$ids);

}


$error = addslashes($_GET['err']);
if(!empty($error)){
	$err = "<p style ='color:red'>Something went wrong with order Id : $error . Please try manually</p>";
}

$message = addslashes($_GET['message']);
if(isset($message) && !empty($message)){
	$success = "<p style ='color:green'>All done successfully</p>";
}

if (!empty($summaryresults)) $summaryresults = '<div class="box23"><table class="summarytbl"><tr>
	<td>Summary + package</td>
	<td>Order made</td>
	<td>Status</td>
	<td>Supplier Fulfill ID</td>
	</tr>' . $summaryresults . '</table></div>';

$tpl = str_replace('{dontdosupportdiv}', $dontdosupportdiv, $tpl);
$tpl = str_replace('{dontdosupportcss}', $dontdosupportcss, $tpl);
$tpl = str_replace('{orderid}', $orderid, $tpl);
$tpl = str_replace('{user}', $user, $tpl);
$tpl = str_replace('{summaryresults}', $summaryresults, $tpl);
$tpl = str_replace('{count}', $inProgressCount, $tpl);
$tpl = str_replace('{ids}', $ids, $tpl);
$tpl = str_replace('{error}', $err, $tpl);
$tpl = str_replace('{success}', $success, $tpl);

output($tpl, $options);
