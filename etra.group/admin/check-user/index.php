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
$orderid = addslashes(trim($_POST['orderid']));
$post_type = addslashes(trim($_POST['post_type']));

$submitreport = addslashes($_POST['submitreport']);
$reportmessage = addslashes($_POST['reportmessage']);
$reportorderid = addslashes($_POST['reportorderid']);
$reportemailaddress = addslashes($_POST['reportemailaddress']);
$deletereport = addslashes($_POST['deletereport']);
$deletereportid = addslashes($_POST['deletereportid']);
$selectedTable = addslashes($_POST['table']);

if (empty($orderid)) $orderid = addslashes(trim($_GET['orderid']));
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


if(isset($_POST['updateFulfill']) && $_POST['updateFulfill'] == 'Update Fulfill Id'){
	
	$ordrId = addslashes($_POST['orderid']);
	$fulfillIdUpdate = addslashes($_POST['fulfillIdUpdate']);
	$q = mysql_query("UPDATE orders SET fulfill_id = '$fulfillIdUpdate' WHERE id = '$ordrId'");
}
$btnUserActive = 'btnActive';
if (!empty($orderid)) {

		$q = mysql_query("SELECT * FROM `orders` 
		WHERE 
			`id` = '$orderid'
			ORDER BY `id` DESC LIMIT 10");
		$field = '<input type="hidden" name="orderid" value="' . $orderid . '">';
		
		$btnUserActive = '';
		$btnOrderActive = 'btnActive';
		$post_type = 'orderid';
}

if (!empty($user)) {


	if (filter_var($user, FILTER_VALIDATE_EMAIL)) {
		$post_type = 'email';
	}

	$query = "SELECT * FROM `orders` WHERE ";
	$btnUserActive = '';
	

	switch($post_type){

		case 'username':
			$query .= " (brand ='$brand' AND `igusername` LIKE '%$user%') ";
			$btnUserActive = 'btnActive';
		break;
		case 'orderid':
			$query .= " (`id` = '$user') ";
			$btnOrderActive = 'btnActive';
		break;
		case 'email':
			$query .= " (brand ='$brand' AND `emailaddress` LIKE '%$user%') ";
			$btnEmailActive = 'btnActive';
		break;
		case 'lastfour':
			$query .= " (brand ='$brand' AND `lastfour` LIKE '%$user%') ";
			$btnFourActive = 'btnActive';
		break;
		case 'paymentid':
			$query .= " `payment_id` LIKE '%$user%' ";
			$btnPaymentidActive = 'btnActive';
		break;
		default:
			$query .= " (brand ='$brand' AND `igusername` LIKE '%$user%') ";
			$btnUserActive = 'btnActive';

		break;
	}

	
	
	$query .= " ORDER BY `id` DESC";

	$q = mysql_query($query);
	$field = '<input type="hidden" name="user" value="' . $user . '">';



}



if ($q) {



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
			$orderstatus = '<font color="green">Completed: ' . date('l jS \of F Y H:i:s ', $info['fulfilled']) . '</font>';
			$arstatus = 'completed';
		}


		$adminnotifsq = mysql_query("SELECT * FROM `admin_notifications` WHERE `orderid` = '{$info['id']}' OR `emailaddress` = '{$info['emailaddress']}'  AND brand ='$brand' LIMIT 12");
		while ($adminnotifinfo = mysql_fetch_array($adminnotifsq)) {

			if ($adminnotifinfo['done'] == '0') {
				$ifdelete = '😊 Waiting to be checked<br><input type="submit" name="deletereport" value="Delete Report">';
				$adminnotifcolor = 'background-color: #fff579;';
			} else {
				$ifdelete = '<br>✅ <span style="font-size: 12px;
    		font-style: italic;">Checked by Admin, ' . ago($adminnotifinfo['response']) . ', ' . date("d/m/Y H:i:s", $adminnotifinfo['response']) . ')</span>';
				$adminnotifcolor = 'background-color: #cfff9b;';
			}

			if (!empty($adminnotifinfo['directions'])) $adminresponse = '<hr><div>Admin Directions:<br>' . $adminnotifinfo['directions'] . '</div>';

			$reportnotifs .= '<div class="adminnotif" style="' . $adminnotifcolor . '">' . $adminnotifinfo['message'] . '<br><span style="font-size: 12px;
    		font-style: italic;"> (reported by ' . ucfirst($adminnotifinfo['admin_name']) . ' -  ' . ago($adminnotifinfo['added']) . ', ' . date("d/m/Y H:i:s", $adminnotifinfo['added']) . ')</span>

    				' . $adminresponse . '

					<form action="/admin/check-user/#order' . $info['id'] . '" method="POST">' . $fields . '
					<input type="hidden" name="deletereportid" value="' . $adminnotifinfo['id'] . '">
					' . $ifdelete . '
					</form></div>';

			unset($ifdelete);
			unset($adminresponse);
			unset($adminnotifinfo);
			unset($adminnotifinfo['directions']);
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

		///// AUTO RESPONSES
		$brandName = getBrandSelectedName($brand);
		$keyword = getSocialMediaSource($info['socialmedia']);
		
		$autoresponses1 = '<div class="foo" >
			<textarea class="language-less">
			First and foremost, thank you very much for choosing '. $brandName .' for upgrading your profile – it means a lot to us! In regards to the order number associated with your email address, I’ve been able to locate a specific order.

			I can see that your order is for ' . $info['amount'] . ' ' . $info['packagetype'] . ' and is ' . $arstatus . '.' . $artime . ' Bear in mind at '. $brandName .', from fulfilling thousands of orders since 2012, we’ve gained a tremendous amount of experience. This has allowed us to develop the safest methods for delivery so that you can maintain as many ' . $info['packagetype'] . ' as possible - ensuring maximum customer satisfaction.

			If your order has not started yet at all, then our systems are still determining the right delivery method and the right type of ' . $info['packagetype'] . ' based on your Instagram profile’s metrics.

			From what our previous customers have experienced, please stay away from service providers that claim to provide instant deliveries without caring about their customer’s Instagram account. These illicit companies can usually lead to shadow bans from Instagram or even a ban.

			Rest assured, your order is being processed and delivered as we speak!

			</textarea>
			<button class="btn btn3 report copy-button">Copy Order Status</button>
			</div>';

		$autoresponses2 = '<div class="foo" >
			<textarea class="language-less">
			Thank you for contacting '. $brandName .' Customer Care. First and foremost, I would like to apologise for the experience you’ve had on '. $brandName .' and can only imagine the inconvenience you may have experienced.

			Please bear in mind that _________ (reason why it’s not Superviral’s fault – to protect our brand), that being said we’ve reported this on to our technical team to look into so that this issue doesn’t happen again.

			I have had a word with our management team, they’ve looked at your case and want to apologise deeply for the issue this has caused. My management team together with our refunds team can provide you with either:
			
			- A refund of the amount you paid. 
			
			- Or alternatively, we can provide you with upto ' . $arrefundsen . ' and give you a 20% partial refund and also you can keep the current ' . $info['packagetype'] . ' as a goodwill gesture. ' . $arextendsentence . '
			
			At '. $brandName .', the customer always comes first and we hope to resolve this issue ASAP.
			
			Let me know how you would like to commence?
			
			</textarea>
			<button class="btn btn3 report copy-button">Copy Refund - Offer It</button>
			</div>';

		$autoresponses3 = '<div class="foo" >
			<textarea class="language-less">
			Thank you for getting back to us. We are extremely sorry about the issue you have experienced once again. 
			
			We have refunded the full amount of £' . $price . ' to the card ending with ' . $info['lastfour'] . ' back to your account, and as a goodwill gesture, you can keep the ' . $info['packagetype'] . ' you received from '. $brandName .'. Please allow 5-10 days for the refund to appear on your bank statement.
			
			Thank you for using '. $brandName .' and once again we do apologise for the inconvenience this may have caused you. We look forward to serving you again in the future.
			</textarea>
			<button class="btn btn3 report copy-button">Copy Refund - 1 Delivered</button>
			</div>';

		if ($info['packagetype'] == 'followers') {

			$autoresponses4 = '<div class="foo" >
			<textarea class="language-less">
			Thank you for contacting '. $brandName .' Customer Care. I am extremely sorry to hear that you have experienced this issue. We\'ve looked into your order and it seems it was delivered. 
					
			Also to add to that, your order is for Instagram followers. At '. $brandName .', we provide you with '. $brandName .' Refill – where our system automatically scans your Instagram account for any followers/likes (that you’ve ordered) that has been lost. The system will then automatically reimburse your account with followers and likes to ensure you get what you ordered.
					
			Also in some cases, '. $brandName .' may send you an email/text notification, and you may see that your order is complete but you haven’t received your order. For example, you’ve ordered 1000 followers and receive an email notification saying “your order is complete”. But you check your Instagram account and it has increased by only 700 followers.
					
			This happens when Instagram removes your followers in between the time we’ve told you that the order is complete and the time you’ve checked your account. But not to worry, with our '. $brandName .' Refill – we’ll keep refilling your account’s followers/likes according to what you’ve ordered within 30-days of your order.
					
			If this issue still persists, please do not hesitate to contact us again.
					
			Kind regards,
					
			James Harris</textarea>
			<button class="btn btn3 report copy-button">Copy Has Received Order</button>
			</div>';
		} else {
			$autoresponses4 = '<div class="foo" >
			<textarea class="language-less">
			Thank you for contacting '. $brandName .' Customer Care. I am extremely sorry to hear that you have experienced this issue. We\'ve looked into your order and it seems it was delivered. If this issue still persists, please do not hesitate to contact us again.
					
			Kind regards,
					
			James Harris</textarea>
			<button class="btn btn3 report copy-button">Copy Has Received Order</button>
			</div>';
		}





		/////



		$fulfills = explode(' ', trim($info['fulfill_id']));

		foreach ($fulfills as $fulfillorder) {

			if (empty($fulfillorder)) continue;
			$thisorderstatus .= '<a target="_BLANK" rel="noopener noreferrer" href="' . $fulfillmentsite . '/orders?search=' . $fulfillorder . '">' . $fulfillorder . '</a><br>';

			
		}

		$thisorderstatus1 = '<form method="POST" action="/admin/check-user/?#order'.$info['id'].'">
		<input type="hidden" name="orderid" value="'. $info['id'] .'">
		<input type="hidden" name="company" value="'. $brand .'">
		<input autocomplete="off" name="fulfillIdUpdate" style= "margin-top: 10px;" value="' . $info['fulfill_id'] . '" class="input" placeholder="Fulfill Id"><br>
		<input type="submit" style= "margin-top: 10px !important;" onclick="return confirm(\'Are you sure you want to change Supplier ID?\');" name="updateFulfill" class="btn btn3 report" value="Update Fulfill Id">
		</form>';

		if ($info['refund'] == '1') {
			$refundcolor = 'orange';
			$refunded = ' <font color="orange">(refund in progress)</font>';
		} else {
			$refundcolor = 'grey';
		}
		if ($info['refund'] == '2') $refunded = ' <font color="red">(refunded)</font>';


		if (($_SESSION['first_name'] == 'rabban') || ($_SESSION['first_name'] == 'hassan') || ($_SESSION['first_name'] == 'anuj') || ($_SESSION['first_name'] == 'mac')) {

			$offerfreefollowers = '<tr class="grey"><td>Offer Free Followers: </td><td><a target="_BLANK" href="/admin/free-followers/?id=' . $info['id'] . '&brand='. $brand .'" class="btn btn3 report">Offer Followers</a>
			<a target="_BLANK" href="/admin/free-likes/?id=' . $info['id'] . '&brand='. $brand .'" class="btn btn3 report">Offer Likes</a> <a target="_BLANK" href="/admin/auto-likes/?id=' . $info['id'] . '&brand='. $brand .'" class="btn btn3 report">Offer AL</a></td></tr>';

			$supplierfulfillid = '<tr class="grey"><td>Supplier Fulfill ID: </td><td>' . $thisorderstatus . $thisorderstatus1 .'</td></tr>';
			$supplierfulfillidtd = $thisorderstatus;

			if ($_SESSION['first_name'] == 'rabban') {


				if (strpos($info['payment_id'], 'pi_') !== false) $paymenttr = '<tr class="grey"><td>Pid: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://dashboard.stripe.com/payments/' . $info['payment_id'] . '">' . $info['payment_id'] . '</a></td></tr>';


				if (strpos($info['payment_id'], '-') !== false) $paymenttr = '<tr class="grey"><td>Pid: </td><td><a target="_BLANK" rel="noopener noreferrer" href="https://my.cardinity.com/payment/show/' . $info['payment_id'] . '">' . $info['payment_id'] . '</a></td></tr>';
			}

			if ($info['disputed'] == '1') {
				$refundavail = 'A chargeback is has been created for this payment. Can\'t refund this';
			} else {
				$refundavail = '<form action="/admin/api/refundorder.php?" method="POST">
						<input type="hidden" name="id" value="' . $info['id'] . '">
						<input type="hidden" name="ordersession" value="' . $info['order_session'] . '">
						<input type="hidden" name="company" value="' . $brand . '">
						<input type="input" class="input rectifyinput" name="amount" placeholder="\'percentage\' or \'full\'" value="' . $info['refundamount'] . '">%
						<input type="submit" onclick="return confirm(\'Are you sure you want to issue the refund?\');" class="btn btn3 report copy-button" style="width:150px;" value="Issue Refund"></form>';
			}

			$refundbtn = '<tr class="' . $refundcolor . '"><td>Offer Refund: </td><td>' . $refundavail . '</a></td></tr>';

			if ($info['price'] == '0.00') $refundbtn = '';
		}

		if (($info['packagetype'] == 'followers') || ($info['packagetype'] == 'freefollowers')) {
			$rectify = '<form action="/admin/api/ordermake-changeusername.php?update=nodefect" method="POST">
						<input type="hidden" name="id" value="' . $info['id'] . '">
						<input type="hidden" name="brand" value="' . $info['brand'] . '">
						<input type="hidden" name="ordersession" value="' . $info['order_session'] . '">
						<input type="hidden" name="company" value="' . $brand . '">
						<input type="input" class="input rectifyinput" name="igusername" placeholder="kevinhart" value="' . $info['igusername'] . '">
						<input type="submit" onclick="return confirm(\'Are you sure you want to change the username?\');" class="btn btn3 report copy-button" style="width:150px;" value="Make Order"></form>';


			$rectify = '<tr class="grey tr-form"><td>Fix This Order By Username: </td><td>' . $rectify . '</td></tr>';
		}



		if ($info['packagetype'] == 'followers') $lastrefilled = '<tr><td>♻️ Last refiled on: </td><td>' . date('l jS \of F Y H:i:s ', $info['lastrefilled']) . '</td></tr>';

		if (!empty($info['chooseposts'])) {
			$chooseposts = '<tr><td>👉 Posts for ' . $info['packagetype'] . ': </td><td>' . $info['chooseposts'] . '</td></tr>';
		}

		$summaryresults .= '<tr>
				<td style="width: 186px;"><img src="/admin/assets/icons/' . $keyword . '-icon.svg" style ="margin-right:5px;width: 15px;"><a href="#order' . $info['id'] . '">#' . $info['id'] . ' - ' . $packagetype . '</a></td>
				<td>' . date('l jS \of F Y H:i:s ', $info['added']) . '</td>
				<td>' . $orderstatus . '</td>
				<td>' . $supplierfulfillidtd . '</td>
				</tr>';


		if ($info['refundtime'] !== '0') $refunddate = '<tr><td>Refund issued on : </td><td>' . date('l jS \of F Y H:i:s ', $info['refundtime']) . '</td></tr>';

		$domain = getBrandSelectedDomain($brand);
		$brandSource = getBrandSelectedSource($brand);
		$UserName  =$info['igusername'];
		if($brand == 'tp' || $brand == 'to') $sourceURL = "https://$brandSource/@$UserName/";
		else $sourceURL = "https://$brandSource/$UserName/";

		$commentSection = "";	
		if($info['packagetype'] == 'comments')	{

			$commentSession = "	SELECT oc.* FROM order_comments oc 
			INNER JOIN order_session os 
			ON oc.order_session_id = os.id
			WHERE os.order_session = '". $info['order_session'] ."'";
			$runSessionQry = mysql_query($commentSession);

			$commentsList = "";

			$ic = 1;
			while($commentsData = mysql_fetch_array($runSessionQry)){
				$commentsList .= "<p>$ic. ". $commentsData['comment'] ." - ". strtoupper($commentsData['tags']) ."</p>";
				$ic++;
			}

			$commentSection = '<tr><td>📦 Comments: </td><td>
								<section>
									<article>
									  <details>
										<summary><u>Click to see comments selected</u></summary>
										  '. $commentsList .'
									  </details>
									</article>
									
    							</section></td></tr>';	

		}
		
		$orderFailed = '';
		if($info['orderfailed'] == 1){
			$orderFailed = '<img src="/admin/assets/icons/Private-Account-Icon.svg" style ="margin-left:5px;width: 15px;">';
		}
		$results .= '


			<div class="box23">

			<table id="order' . $info['id'] . '" class="perorder">


				<tr><td>Order #' . $info['id'] . '</td><td></td></tr>
				<tr><td>🌍 Country</td><td>'. $info['country'] .'</td></tr>
				<tr><td>🏢 Company:</td><td style=""><img src="/admin/assets/icons/' . $brandName . '.svg"></td></tr>
				<tr><td>#️⃣ Payment ID: </td><td>' . $info['payment_id'] .'</td></tr>
				<tr><td>#️⃣ Order ID: </td><td>' . $info['id'] . '<img src="/admin/assets/icons/' . $keyword . '-icon.svg" style ="margin-left:5px;width: 15px;"></td></tr>
				<tr><td>#️⃣ Account ID: </td><td><span id="spanAccountId'. $info['id'] .'">' . $info['account_id'] . '</span><a href="javascript:void(0);" class="modal-button btn3 modalBtn" id="editAccountId'. $info['id'] .'" onclick="openModal(this.id);" title="edit" style="display: inline-block;width: 25px;height: 25px;border: solid 1px #ddd;border-radius: 7px;margin-left: 17px;background: #f5f5f5;"> <svg style="width:25px;height: 18px;transform: translateY(2px);fill: black;" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 50 50">
				<path d="M 43.125 2 C 41.878906 2 40.636719 2.488281 39.6875 3.4375 L 38.875 4.25 L 45.75 11.125 C 45.746094 11.128906 46.5625 10.3125 46.5625 10.3125 C 48.464844 8.410156 48.460938 5.335938 46.5625 3.4375 C 45.609375 2.488281 44.371094 2 43.125 2 Z M 37.34375 6.03125 C 37.117188 6.0625 36.90625 6.175781 36.75 6.34375 L 4.3125 38.8125 C 4.183594 38.929688 4.085938 39.082031 4.03125 39.25 L 2.03125 46.75 C 1.941406 47.09375 2.042969 47.457031 2.292969 47.707031 C 2.542969 47.957031 2.90625 48.058594 3.25 47.96875 L 10.75 45.96875 C 10.917969 45.914063 11.070313 45.816406 11.1875 45.6875 L 43.65625 13.25 C 44.054688 12.863281 44.058594 12.226563 43.671875 11.828125 C 43.285156 11.429688 42.648438 11.425781 42.25 11.8125 L 9.96875 44.09375 L 5.90625 40.03125 L 38.1875 7.75 C 38.488281 7.460938 38.578125 7.011719 38.410156 6.628906 C 38.242188 6.246094 37.855469 6.007813 37.4375 6.03125 C 37.40625 6.03125 37.375 6.03125 37.34375 6.03125 Z"></path>
				</svg> </a></td></tr>
				<tr><td>📧 Email address: </td><td><a href="/admin/check-user/?user=' . $info['emailaddress'] . '">' . $info['emailaddress'] . '</a></td></tr>
				<tr><td>📦 Package: </td><td>' . $packagetype . '</td></tr>
				'. $commentSection .'
				<tr><td>💸 Amount Paid: </td><td>£' . $price . $refunded . '</td></tr>
				<tr><td>🧍 Username: </td><td><a target="_BLANK" rel="noopener noreferrer" href="'. $sourceURL .'">' . $info['igusername'] . '</a>'. $orderFailed .'</td></tr>
				' . $chooseposts . '
				<tr><td>📞 Contact number: </td><td>' . $info['contactnumber'] . '</td></tr>
				<tr><td>⌚ Order made on: </td><td>' . date('l jS \of F Y H:i:s ', $info['added']) . '</td></tr>
				' . $lastrefilled . '
				<tr><td>🚚 Order status: </td><td>' . $orderstatus . '</td></tr>
				' . $trackinginformation . '
				<tr><td>Tracking page: </td><td><a target="_BLANK" href="https://' . $domain . '/track-my-order/' . $info['order_session'] . '">' . $info['order_session'] . '</a></td></tr>
				<tr><td>❤️😍 Refund offers: </td><td>' . $refundoffers . '</td></tr>
				<tr><td>📝 Report: </td><td>' . $reportnotifs . '
				<form action="/admin/check-user/#order' . $info['id'] . '" method="POST">' . $submittedfield . $field . '
				<input type="hidden" name="reportorderid" value="' . $info['id'] . '">
				<input type="hidden" name="reportemailaddress" value="' . $info['emailaddress'] . '">
				<input type="hidden" name="company" value="' . $brand . '">
				<textarea class="reportmessage" name="reportmessage"></textarea>
				<div style="display:inline-block;width:100%;"><input type="submit" name="submitreport" class="btn btn3 report" value="Send Report">
				</div>
				</form>
				</td></tr>
				<tr class="grey"><td>Automated responses: </td><td><div>' . $autoresponses1 . $autoresponses2 . $autoresponses3 . $autoresponses4 . '</div></td></tr>
				<tr class="grey"><td>IP Address: </td><td>' . $info['ipaddress'] . '</td></tr>
				' . $supplierfulfillid . '
				<tr class="tr-form">
					<td>Resend Order To Supplier: </td>
					<td>
					<form method="POST" action="/admin/check-user/?type=defect#order'.$info['id'].'">
                	<input type="hidden" name="type" value="defect">
                	<input type="hidden" name="user" value="'. $user .'">
                	<input type="hidden" name="orderid" value="'. $info['id'] .'">
                	<input type="hidden" name="submitreport" value="'. $submitreport .'">
                	<input type="hidden" name="reportmessage" value="'. $reportmessage .'">
                	<input type="hidden" name="reportemailaddress" value="'. $reportemailaddress .'">
                	<input type="hidden" name="reportorderid" value="'. $reportorderid .'">
                	<input type="hidden" name="deletereportid" value="'. $deletereportid .'">
                	<input type="hidden" name="deletereport" value="'. $deletereport .'">
                	<input type="hidden" name="company" value="'. $brand .'">
                	<input autocomplete="off" name="search" value="' . $info['id'] . '" class="input" placeholder="Search by Order ID">
					<input type="submit" name="searchOrder" class="btn btn3 report" value="Resend Order">
            		</form> <br><br><br>
					'.($_POST['search'] == $info['id'] ? '{articles}' : '').'
					</td>
				</tr>
				<tr class="tr-form">
					<td>Supplier Error: </td>
					<td style="color:red;">
					'.  $info['supplier_errors'] .'
					</td>
				</tr>
				' . $paymenttr . '
				<tr><td>Last Four: </td><td>' . $info['lastfour'] . '</td></tr>
				' . $rectify . '
				' . $offerfreefollowers . '
				' . $refundbtn . '
				' . $refunddate.'
				
			';
				
				
			$results .= '</table></div>';

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
		unset($fulfillorder);
	}

	//if(!empty($results))$results = ''.$results.'';
	// echo $results;die;
}

if($selectedTable == 'order_session'){

	$results = "";
	$summaryresults = "";
	if (!empty($user)) {
		
			$query = "SELECT * FROM `order_session` WHERE ";
			$btnUserActive = '';

		
			switch($post_type){
			
				case 'username':
					$query .= " (brand ='$brand' AND `igusername` LIKE '%$user%') ";
					$btnUserActive = 'btnActive';
				break;
				case 'orderid':
					$query .= " (`id` = '$user') ";
					$btnOrderActive = 'btnActive';
				break;
				case 'email':
					$query .= " (brand ='$brand' AND `emailaddress` LIKE '%$user%') ";
					$btnEmailActive = 'btnActive';
				break;
				default:
					$query .= " (brand ='$brand' AND `igusername` LIKE '%$user%') ";
					$btnUserActive = 'btnActive';
				
				break;
			}
		


			$query .= " ORDER BY `id` DESC";
		
			$q = mysql_query($query);
			$field = '<input type="hidden" name="user" value="' . $user . '">';
		
		
		
		}

	if ($q) {



		while ($info = mysql_fetch_array($q)) {

			$packgQ = "SELECT * from packages where id = '{$info['packageid']}' limit 1";
			$packData = mysql_fetch_array(mysql_query($packgQ));
			
			$info['packagetype'] = $packData['type'];
	
			$packagetype = $info['amount'] . ' ' . $info['packagetype'];
	
			if(empty($info['price'])) $info['price'] = 0;
			$price = sprintf('%.2f', $info['price'] / 100);
	
	
			///// AUTO RESPONSES
			$brandName = getBrandSelectedName($brand);
			$keyword = getSocialMediaSource($info['socialmedia']);
			$thisorderstatus1 = '<form method="POST" action="/admin/check-user/?#order'.$info['id'].'">
			<input type="hidden" name="orderid" value="'. $info['id'] .'">
			<input type="hidden" name="company" value="'. $brand .'">
			<input autocomplete="off" name="fulfillIdUpdate" style= "margin-top: 10px;" value="' . $info['fulfill_id'] . '" class="input" placeholder="Fulfill Id"><br>
			<input type="submit" style= "margin-top: 10px !important;" onclick="return confirm(\'Are you sure you want to change Supplier ID?\');" name="updateFulfill" class="btn btn3 report" value="Update Fulfill Id">
			</form>';
	
			$offerfreefollowers = "";

			if (strpos($info['chooseposts'], '###') !== false) {

				$chooseposts = explode('~~~', $info['chooseposts']);

				foreach($chooseposts as $posts1){

				if(empty($posts1))continue;

				$posts2 = explode('###', $posts1);

				$multiamountposts++;

				$exp_posts .= $posts2[0].' ';
				}

			} 
	
	
			if (!empty($info['chooseposts'])) {
				$chooseposts = '<tr><td>👉 Posts for ' . $info['packagetype'] . ': </td><td>' . $exp_posts . '</td></tr>';
			}
	
			$summaryresults .= '<tr>
					<td style="width: 186px;">'. $info['order_session'] .'</td>
					<td>' . date('l jS \of F Y H:i:s ', $info['added']) . '</td>
					<td><img src="/admin/assets/icons/' . $keyword . '-icon.svg" style ="margin-right:5px;width: 15px;">' . $info['igusername'] . '</td>
					</tr>';
	
	
			$domain = getBrandSelectedDomain($brand);
			$brandSource = getBrandSelectedSource($brand);
			$UserName  =$info['igusername'];
			if($brand == 'tp' || $brand == 'to') $sourceURL = "https://$brandSource/@$UserName/";
			else $sourceURL = "https://$brandSource/$UserName/";
	
			$commentSection = "";	
			if($info['packagetype'] == 'comments')	{
	
				$commentSession = "	SELECT oc.* FROM order_comments oc 
				INNER JOIN order_session os 
				ON oc.order_session_id = os.id
				WHERE os.order_session = '". $info['order_session'] ."'";
				$runSessionQry = mysql_query($commentSession);
	
				$commentsList = "";
	
				$ic = 1;
				while($commentsData = mysql_fetch_array($runSessionQry)){
					$commentsList .= "<p>$ic. ". $commentsData['comment'] ." - ". strtoupper($commentsData['tags']) ."</p>";
					$ic++;
				}
	
				$commentSection = '<tr><td>📦 Comments: </td><td>
									<section>
										<article>
										  <details>
											<summary><u>Click to see comments selected</u></summary>
											  '. $commentsList .'
										  </details>
										</article>
										
									</section></td></tr>';	
	
			}
			
			$results .= '
	
	
				<div class="box23">
	
				<table id="order' . $info['id'] . '" class="perorder">
	
	
					<tr><td>Order Session#' . $info['id'] . '</td><td></td></tr>
					<tr><td>🌍 Country</td><td>'. $info['country'] .'</td></tr>
					<tr><td>🏢 Company:</td><td style=""><img src="/admin/assets/icons/' . $brandName . '.svg"></td></tr>
	
					<tr><td>#️⃣ Order Session ID: </td><td>' . $info['id'] . '<img src="/admin/assets/icons/' . $keyword . '-icon.svg" style ="margin-left:5px;width: 15px;"></td></tr>
					<tr><td>#️⃣ Account ID: </td><td><span id="spanAccountId'. $info['id'] .'">' . $info['account_id'] . '</span></td></tr>
					<tr><td>📧 Email address: </td><td><a href="/admin/check-user/?user=' . $info['emailaddress'] . '">' . $info['emailaddress'] . '</a></td></tr>
					<tr><td>📦 Package: </td><td>' . $packagetype . '</td></tr>
					'. $commentSection .'
					<tr><td>🧍 Username: </td><td><a target="_BLANK" rel="noopener noreferrer" href="'. $sourceURL .'">' . $info['igusername'] . '</a></td></tr>
					' . $chooseposts . '
					<tr><td>⌚ Order made on: </td><td>' . date('l jS \of F Y H:i:s ', $info['added']) . '</td></tr>
					<tr class="grey"><td>IP Address: </td><td>' . $info['ipaddress'] . '</td></tr>
					' . $offerfreefollowers . '
				';
					
					
				$results .= '</table></div>';
	
			unset($chooseposts);
		}
	
		//if(!empty($results))$results = ''.$results.'';
		// echo $results;die;
	}

}

if (!empty($_POST['searchOrder'])) {
	class Api
	{
		public function setApiKey($value)
		{
			$this->api_key = $value;
		}
		public function setApiUrl($value)
		{
			$this->api_url = $value;
		}



		public function order($data)
		{ // add order
			$post = array_merge(array('key' => $this->api_key, 'action' => 'add'), $data);
			return json_decode($this->connect($post));
		}

		public function status($order_id)
		{ // get order status
			return json_decode($this->connect(array(
				'key' => $this->api_key,
				'action' => 'status',
				'order' => $order_id
			)));
		}

		public function multiStatus($order_ids)
		{ // get order status
			return json_decode($this->connect(array(
				'key' => $this->api_key,
				'action' => 'status',
				'orders' => implode(",", (array)$order_ids)
			)));
		}

		public function services()
		{ // get services
			return json_decode($this->connect(array(
				'key' => $this->api_key,
				'action' => 'services',
			)));
		}

		public function balance()
		{ // get balance
			return json_decode($this->connect(array(
				'key' => $this->api_key,
				'action' => 'balance',
			)));
		}


		private function connect($post)
		{
			$_post = array();
			if (is_array($post)) {
				foreach ($post as $name => $value) {
					$_post[] = $name . '=' . urlencode($value);
				}
			}

			$ch = curl_init($this->api_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			if (is_array($post)) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
			}
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
			$result = curl_exec($ch);
			if (curl_errno($ch) != 0 && empty($result)) {
				$result = false;
			}
			curl_close($ch);
			return $result;
		}
	}


	$api = new Api();

	$api->setApiKey($fulfillment_api_key);
	$api->setApiUrl($fulfillment_url);


	$reportdone = addslashes($_POST['reportdone']);
	$type = addslashes($_POST['type']);
	$search = trim(addslashes($_POST['search']));
	$mainid = addslashes($_GET['mainid']);

	if (empty($type)) $type = 'cancelled';

	//defect code 1 is detected but needs to be categorised as either 2 or 3 (prior to 4th September 2020)
	//defect code 2 is cancelled
	//defect code 3 is partial
	//defect code 5 is ignore completely


	$theid = $_GET['theid'];

	if (!empty($theid)) {
		$styles = '.first' . $theid . ',.second' . $theid . '{background-color:#e9ffe9;}';
	}

	if ($_GET['message'] == 'email1') $message = '<div class="emailsuccess">Private IG account email: Sent</div>';
	if ($_GET['message'] == 'updatetrue') {
		$message = '<div class="emailsuccess">Order ID #' . $mainid . ' successfully updated with Supplier ID: ' . $theid . '.</div>';
		$search = $mainid;
	}



	if ($type == 'cancelled') $q = mysql_query("SELECT * FROM `orders` WHERE `defect` = '2' AND `refund` = '0' AND brand = '$brand' ORDER BY `id` ASC LIMIT 1");
	if ($type == 'partial') $q = mysql_query("SELECT * FROM `orders` WHERE `defect` = '3' AND `refund` = '0' AND brand = '$brand' ORDER BY `id` ASC LIMIT 1");

	if (!empty($search)) {
		$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$search' AND brand = '$brand' ORDER BY `id` ASC LIMIT 1");
	}




	while ($info = mysql_fetch_array($q)) {

		////////////////// IF SHOW POSTS INSTEAD OF USERNAME


		if (empty($info['chooseposts'])) {
			if ($info['packagetype'] == 'likes' || $info['packagetype'] == 'views') {

				$findchoosepostsq = mysql_query("SELECT * FROM `order_session_paid` WHERE `order_session` = '{$info['order_session']}' AND brand = '$brand' LIMIT 1");

				if (mysql_num_rows($findchoosepostsq) == '0') $findchoosepostsq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$info['order_session']}' AND brand = '$brand' LIMIT 1");


				if (mysql_num_rows($findchoosepostsq) == 1) {
					$findchooseposts = mysql_fetch_array($findchoosepostsq);

					$thechoosepostsfound = $findchooseposts['chooseposts'];


					if (!empty($thechoosepostsfound)) {

						$thechoosepostsfound = explode('~~~', $thechoosepostsfound);

						foreach ($thechoosepostsfound as $posts1) {

							if (empty($posts1)) continue;

							$posts2 = explode('###', $posts1);

							$theupdatequery .= $posts2[0] . ' ';
							$info['chooseposts'] .= $posts2[0] . ' ';
						}
					}

					$theupdatequery1 = ' Update query: "' . $theupdatequery . '" ';
					$chooseposts = ' FOUND: ';
					mysql_query("UPDATE `orders` SET `chooseposts` = '$theupdatequery' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");
				}
			}
		}


		////////////////////

		$info['price'] = sprintf('%.2f', $info['price'] / 100);


		if (!empty($info['chooseposts'])) {

			$thispost = $info['chooseposts'];
			$thispost = explode(' ', $thispost);

			foreach ($thispost as $thisposta) {

				if (empty($thisposta)) continue;

				$posts .= '<a target="_BLANK" rel="noopener noreferrer" href="' . $fulfillmentsite . '/orders?search=' . $thisposta . '">' . $thisposta . '</a><br>';
			}



			$posts = $posts . $chooseposts . $theupdatequery1;

			$show = $posts;
		} else {

			$show = '<a target="_BLANK" rel="noopener noreferrer" href="' . $fulfillmentsite . '/orders?search=' . $info['igusername'] . '">' . $info['igusername'] . '</a>';
		}


		$fulfills = explode(' ', trim($info['fulfill_id']));

		$fulfillcount = count(array_filter($fulfills));
		$balance = $api->multiStatus($fulfills);

		$balance = json_decode(json_encode($balance), True);
		if (isset($balance['error'])) {

		} else {

			foreach ($balance as $key => $order) {


				//    if($order['status']=='Partial')$left = ' - '.($info['amount'] - $order['remains']).'/'.$info['amount'];
				if(isset($order)){ if ($order['status'] == 'Partial') $left = ' - ' . $order['remains'] . '/' . $info['amount']; }

				$thisorderstatus .= '<a target="_BLANK" rel="noopener noreferrer" href="' . $fulfillmentsite . '/orders?search=' . $key . '">' . $key . '</a> - ' . $order['status'] . $left . '<br>';

				unset($left);
			}

		}


		$notesadminq = mysql_query("SELECT * FROM `admin_order_notes` WHERE `orderid` ='{$info['id']}' AND brand = '$brand'");
		if (mysql_num_rows($notesadminq) !== '0') {

			while ($notesinfo = mysql_fetch_array($notesadminq)) {

				$notesadmin .= '<div style="padding:5px;border-bottom:1px dashed grey">' . ago($notesinfo['added']) . ' - ' . $notesinfo['notes'] . '</div>';
			}

			$notesadmin = '<div class="sdiv">' . $notesadmin . '</div>';
		}


		$articles .= '<div class="defectorder">

    <div class="sdiv">
    <b><a target="_BLANK" href="/admin/check-user/?orderid=' . $info['id'] . '#order' . $info['id'] . '">' . $info['id'] . '</a> - ' . $show . '</b><br>£' . $info['price'] . ' - ' . $info['amount'] . ' ' . $info['packagetype'] . '<br>' . date('l jS \of F Y H:i:s ', $info['added']) . '
    </div>

    <div class="sdiv">
					' . $thisorderstatus . '
    </div>

    <div class="sdiv">
                        <form id="makeorder" action="/admin/api/ordermakefordefect.php" method="POST">
                        <input type="hidden" name="update" value="save">
                        <input type="hidden" name="reorder" value="yes">
                        <input type="hidden" name="defectpage" value="defect">
                        <input type="hidden" name="pagefrom" value="' . $type . '">
                        <input type="hidden" name="id" value="' . $info['id'] . '">
                        <input type="hidden" name="ordersession" value="' . $info['order_session'] . '">
                        <input type="submit" style="margin-bottom:10px !important" onclick="return confirm(\'Are you sure you want to create a new order?\');" class="btn color3 btn-primary" style="width:150px;" value="Make Order"></form>
    </div>


    <div class="sdiv">
                    <form method="POST" action="/admin/api/ordersupdate.php" style="display:none;">
                    <input type="hidden" name="pagefrom" value="' . $type . '">
                    <input type="hidden" name="defectpage" value="defect">
                    <input type="hidden" name="update" value="save">
                    <input type="hidden" name="id" value="' . $info['id'] . '">
                    <input class="input" name="orderid" value="' . $info['fulfill_id'] . '">
                    <input type="submit" class="btn color3 btn-primary" value="SAVE">
                    </form>

                    <form method="POST" action="/admin/api/emailissue.php">
                    <input type="hidden" name="defectpage" value="defect">
                    <input type="hidden" name="pagefrom" value="' . $type . '">
                    <input type="hidden" name="id" value="' . $info['id'] . '">
                    <input type="hidden" name="ordersession" value="' . $info['order_session'] . '">
                    <input type="submit" style="margin-bottom:10px !important;width:250px" class="btn color3 btn-primary" value="Send Email for private profile">
                    </form>

    </div>

    <div class="sdiv">
    

                    <form method="POST" action="/admin/api/emailissue2.php">
                    <input type="hidden" name="defectpage" value="defect">
                    <input type="hidden" name="pagefrom" value="' . $type . '">
                    <input type="hidden" name="id" value="' . $info['id'] . '">
                    <input type="hidden" name="ordersession" value="' . $info['order_session'] . '">
                    <input type="submit" style="margin-bottom:10px !important;width:250px" class="btn color3 btn-primary" value="Send Email for non-working page">
                    </form>


    </div>

    ' . $notesadmin . '


                </div>';

		unset($posts);
		unset($thisorderstatus);
	}
}
if($selectedTable != ""){
	$tpl = str_replace('<option>'. $selectedTable, '<option selected>'. $selectedTable, $tpl);

}

$btnOActive = "btnActive";

if (!empty($summaryresults))
{ 
	if($selectedTable == "order_session"){

		$summaryresults = '<div class="box23"><table class="summarytbl"><tr>
		<td>Orderssion</td>
		<td>Order made</td>
		<td>Username</td>
		</tr>' . $summaryresults . '</table></div>';

		$btnOSActive = "btnActive";
		$btnOActive = "";

	}else{
		$summaryresults = '<div class="box23"><table class="summarytbl"><tr>
		<td>Summary + package</td>
		<td>Order made</td>
		<td>Status</td>
		<td>Supplier Fulfill ID</td>
		</tr>' . $summaryresults . '</table></div>';
		$btnOActive = "btnActive";
		$btnOSActive = "";
	}
}

$tpl = str_replace('{dontdosupportdiv}', $dontdosupportdiv, $tpl);
$tpl = str_replace('{dontdosupportcss}', $dontdosupportcss, $tpl);
$tpl = str_replace('{user}', $user, $tpl);
$tpl = str_replace('{summaryresults}', $summaryresults, $tpl);
$tpl = str_replace('{results}', $results, $tpl);
$tpl = str_replace('{articles}', $articles, $tpl);
$tpl = str_replace('{btnOActive}', $btnOActive, $tpl);
$tpl = str_replace('{btnOSActive}', $btnOSActive, $tpl);
$tpl = str_replace('{btnUserActive}', $btnUserActive, $tpl);
$tpl = str_replace('{btnOrderActive}', $btnOrderActive, $tpl);
$tpl = str_replace('{btnEmailActive}', $btnEmailActive, $tpl);
$tpl = str_replace('{btnFourActive}', $btnFourActive, $tpl);
$tpl = str_replace('{btnPaymentidActive}', $btnPaymentidActive, $tpl);
$tpl = str_replace('{post_type}', $post_type, $tpl);


output($tpl, $options);
