<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

$id = addslashes($_GET['id']);

$orderid = addslashes($_POST['orderid']);
$order_session = addslashes($_POST['order_session']);

$alorderid = addslashes($_POST['alorderid']);
$alautolikesid = addslashes($_POST['alautolikesid']);

$customamount = addslashes($_POST['percentage']);
$undoRefund = addslashes($_POST['undoRefund']);

// use Google\Cloud\Translate\V2\TranslateClient;

// require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

// $translate = new TranslateClient(['key' => $googletranslatekey]);



require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/cardinity-php-master/vendor/autoload.php';

/* Start to develop here. Best regards https://php-download.com/ */

use Cardinity\Client;
use Cardinity\Method\Payment;
use Cardinity\Exception;
use Cardinity\Method\ResultObject;
use Cardinity\Method\Refund;

$client = Client::create([
    'consumerKey' => $cardinitykey,
    'consumerSecret' => $cardinitysecret,
]);

if(!empty($undoRefund) && !empty($orderid)){

	// undo refund
	$updateq = mysql_query("UPDATE `orders` SET `refund` = '0', refundamount = '0' WHERE `id` = '$orderid' LIMIT 1");
	// if($updateq)$success = '<div class="emailsuccess">Undo refund</div>';

	// if(!empty($success))$tpmsg = '<div style="padding:10px;">'.$tpmsg.'</div>';
}

if(!empty($undoRefund) && !empty($alorderid)){

	// undo refund
	$updateq = mysql_query("UPDATE `automatic_likes_billing` SET `refunded` = '0', amount = '0' WHERE `id` = '$alorderid' LIMIT 1");


	// if($updateq)$success = '<div class="emailsuccess">Undo refund</div>';

	// if(!empty($success))$tpmsg = '<div style="padding:10px;">'.$tpmsg.'</div>';
}






if(!empty($orderid) && empty($undoRefund) && empty($alorderid)){


		$getrefundinfoq = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid' AND `order_session` = '$order_session' LIMIT 1");
		$refundinfo = mysql_fetch_array($getrefundinfoq);

		$brandName = getBrandSelectedName($refundinfo['brand']);
		$brand = $refundinfo['brand'];
		$countryloc = $refundinfo['country'];

		$amount = $locas[$countryloc]['currencysign'].$customamount;

		$recipient = $refundinfo['emailaddress'];


		//die('Recipient: '.$recipient);
		 


		$lastfour = $refundinfo['lastfour'];

		if($lastfour=='0')$lastfour = '**** (ApplePay)';

		$ordernum = $refundinfo['id'];
		$service = $refundinfo['amount'].' Instagram '.ucwords($refundinfo['packagetype']);
		$payment  = $refundinfo['price'];

		$orderDay = $refundinfo['added'];
		$curTime = date('Y-m-d H:i:s', time());
		$endTime = date('Y-m-d H:i:s', strtotime("tomorrow", $orderDay) - 1); // currentdate end time


			if(is_numeric($refundinfo['payment_id'])){

					//echo 'PAYMENT: '.$payment.' - '.$customamount.'<hr>';

					$now2 = date('omdHis',time());
					$now2 = substr($now2, 4);
					$now2 = date("Y").$now2;

					$refundonacquired =  array(
					      
					    "timestamp" => $now2,
					    "company_id" => $acquiredaccountid,
					    "company_pass" => $acquiredcompanypass,

				        "transaction" => array(
					        // "transaction_type" => 'REFUND',
					        "original_transaction_id" => $refundinfo['payment_id'],
					        // "amount" => $customamount,
					    ),
		    

					);

					if($endTime > $curTime){
						$transactType = "VOID";
						$refundonacquired['transaction']["transaction_type"] = $transactType;
					}else{
						$transactType = "REFUND";
						$refundonacquired['transaction']["transaction_type"] = $transactType;
						$refundonacquired['transaction']["amount"] = $customamount;
					}

					$request_hash = hash('sha256',$now2.$transactType.$acquiredaccountid.$refundinfo['payment_id'].$acquiredsecretpasscode);

					$refundonacquired['request_hash'] = $request_hash;


					$url = "https://gateway.acquired.com/api.php";    
					//$url = "https://qaapi.acquired.com/api.php";
					$refundonacquired = json_encode($refundonacquired);

					$curl = curl_init($url);
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_HTTPHEADER,
					        array("Content-type: application/json"));
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $refundonacquired);

					$json_response = curl_exec($curl);

					$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);


					curl_close($curl);

					$response = json_decode($json_response, true);
			
					/*
					echo '<pre>';
					print_r($response);
					echo '</pre>';
					*/

				

			}


			if(preg_match("/[a-z]/i", $refundinfo['payment_id'])){


					try{

					$cardinityamt = floatval($customamount);

					$method = new Refund\Create(
						$refundinfo['payment_id'],
						$cardinityamt,
					    'my description'
					);
				
					$refund = $client->call($method);

					}catch (\Throwable $e) {
						// catches all ClientExceptions
					} catch (RequestException $e) {
						// catches all RequestExceptions
					}
			}



			if($brand == 'sv' || $brand == 'fb')
			require (dirname($_SERVER["DOCUMENT_ROOT"]).'/superviral.io/emailrefund.php');
	
			if($brand == 'to')
			require (dirname($_SERVER["DOCUMENT_ROOT"]).'/tikoid.com/emailrefund.php');

/*echo 'Normal Recipient: '.$recipient.'<hr>';
echo $bodyHtml;*/


		$now = time();

		$refundreason = ' - requested by customer';
		if($refundinfo['refundamount']=='fraud'){$refundreason = ' - due to irregular card payment activity';}

		$refundprice = sprintf('%.2f', $info['price'] / 100);
		$refundtrackingmsg = $refundinfo['order_response'].'~~~'.$now.'###A refund for '.$customamount.' issued to the card ending with '.$refundinfo['lastfour'].$refundreason.'###0.2';

		$updateq = mysql_query("UPDATE `orders` SET `refund` = '2',`order_response` = '$refundtrackingmsg',`refundtime` = '$now' WHERE `id` = '$orderid' AND `order_session` = '$order_session' LIMIT 1");

		$refundreason = '';

		if($updateq)$success = '<div class="emailsuccess">A refund of '.$customamount.' has been issued to Order #'.$orderid.$refundreason.'. Email sent to '.$recipient.'</div>';

		if(!empty($tpmsg))$tpmsg = '<div style="padding:10px;">'.$tpmsg.'</div>';


}//END OF IF SOMETHING SUBMITTED



///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////

if(!empty($alorderid) && empty($undoRefund)){


		$getrefundinfoq = mysql_query("SELECT * FROM `automatic_likes_billing` WHERE `id` = '$alorderid' AND `auto_likes_id` = '$alautolikesid' LIMIT 1");
		$refundinfo = mysql_fetch_array($getrefundinfoq);
		$brand = $refundinfo['brand'];  	

		$getrefundaccountq = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$refundinfo['account_id']}' LIMIT 1");
		$getrefundaccountinfo = mysql_fetch_array($getrefundaccountq);

		$getrefundsubscriptionq = mysql_query("SELECT * FROM `automatic_likes` WHERE `id` = '{$refundinfo['auto_likes_id']}' LIMIT 1");
		$getrefundsubscriptioninfo = mysql_fetch_array($getrefundsubscriptionq);

		$refundinfo['country'] = $getrefundsubscriptioninfo['country'];

		$countryloc = $refundinfo['country'];

		$amount = $locas[$countryloc]['currencysign'].$customamount;

		$recipient = $getrefundaccountinfo['email'];



		try{
			 $cardinityamt = floatval($customamount);

			$method = new Refund\Create(
			   $refundinfo['payment_id'],
			     $cardinityamt,
			    'my description'
			);
		
			$refund = $client->call($method);

		}catch (\Throwable $e) {
			// catches all ClientExceptions
		} catch (RequestException $e) {
			// catches all RequestExceptions
		}
		$recipient = $getrefundaccountinfo['email'];

		$lastfour = $refundinfo['lastfour'];
		if($lastfour=='0')$lastfour = '**** (ApplePay)';
		if(empty($lastfour))$lastfour = '****';

		$ordernum = $refundinfo['auto_likes_id'];
		$service = $refundinfo['likesperpost'].' Instagram Automatic Likes';
		$payment  = $refundinfo['amount'];

		if(empty($brand))$brand = 'sv';

		if($brand == 'sv' || $brand == 'fb')
		require (dirname($_SERVER["DOCUMENT_ROOT"]).'/superviral.io/emailrefund.php');


		$now = time();

		$updateq = mysql_query("UPDATE `automatic_likes_billing` SET `refunded` = '$now' WHERE `id` = '$alorderid' AND `auto_likes_id` = '$alautolikesid'LIMIT 1");


		$refundreason = ' - requested by customer';
		if($refundinfo['refundamount']=='fraud'){$refundreason = ' - due to irregular card payment activity';}


		$refundreason = '';


		if($updateq)$success = '<div class="emailsuccess">A refund of £'.$customamount.' has been issued to Subscription #'.$alorderid.$refundreason.'. Email sent to '.$recipient.'</div>';

		if(!empty($tpmsg))$tpmsg = '<div style="padding:10px;">'.$tpmsg.'</div>';


}//END OF IF SOMETHING SUBMITTED




///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////


// $q = mysql_query("SELECT * FROM `orders` WHERE `refund` = '1' AND `disputed` = '0' LIMIT 1");

//$q = mysql_query("SELECT * FROM `orders` WHERE `refund` = '1' AND `disputed` = '0' AND `payment_id` LIKE '%-%' LIMIT 1");

if (isset($_GET['page'])) {$page = addslashes($_GET['page']);} else {$page = 1;}
if($page < 1)$page = 1;

$no_of_records_per_page = 1;
$offset = ($page-1) * $no_of_records_per_page;

$q = mysql_query("SELECT * FROM `orders` WHERE `refund` = '1' AND `disputed` = '0' LIMIT $offset, $no_of_records_per_page");


$q2 = mysql_query("SELECT * FROM `orders` WHERE `refund` = '1' AND `disputed` = '0'");
$totalleft = mysql_num_rows($q2).' Refunds Remaining';

if(mysql_num_rows($q)=='1'){


	$infoa = mysql_fetch_array($q);

	$keyword = getSocialMediaSource($infoa['socialmedia']);
	$brandName = getBrandSelectedName($infoa['brand']);
	$brand = $refundinfo['brand'];

                if (strpos($infoa['payment_id'], 'pi_') !== false)$payment_idshow = 'Stripe: <a target="_BLANK" rel="noopener noreferrer" href="https://dashboard.stripe.com/payments/'.$infoa['payment_id'].'">'.$infoa['payment_id'].'</a>';

                if (strpos($infoa['payment_id'], '-') !== false)$payment_idshow = 'Cardinity: <a target="_BLANK" rel="noopener noreferrer" href="https://my.cardinity.com/payment/show/'.$infoa['payment_id'].'">'.$infoa['payment_id'].'</a>';         

                if (is_numeric($infoa['payment_id']))$payment_idshow = 'Acquired: <a target="_BLANK" rel="noopener noreferrer" href="https://hub.acquired.com/#transactions/detail/'.$infoa['payment_id'].'">'.$infoa['payment_id'].'</a>';          


		$orderinfo = 'ID: '.$infoa['id'].'<br>';
		$orderinfo .= 'Email address: '.$infoa['emailaddress'].'<br>';
		$orderinfo .= 'IG Username: '.$infoa['igusername'].'<br>';
		$orderinfo .= 'Order: '.$infoa['amount'].' '.$infoa['packagetype'].'<br>';
		$orderinfo .= 'Price: £'.sprintf('%.2f', $infoa['price'] / 100).'<br>';
		$orderinfo .= 'Orded Placed: '.date('l jS \of F Y H:i:s ', $infoa['added']).'<br>';

		$orderinfo = '<div style="font-size:14px;font-family:verdana;padding:10px;line-height: 29px;">'.$orderinfo.'</div>';


		$result = '<table class="articles" style="'.$showorno.'">

                <tr> <td>Company</td> <td> <img src="/admin/assets/icons/'. $brandName .'.svg"> </td> </tr>

				<tr>

					<td>ℹ Order ID <img style="max-width:25px;display:inline-block;" src="/admin/assets/icons/'. $keyword .'-icon.svg" style="float:right;margin-right:10px;"></td>
					<td><input type="hidden" autocomplete="off" name="id" value="'.$infoa['id'].'" class="input">'.$orderinfo.'</td>

				</tr>


				<tr>

					<td>Amount</td>
					<td>'.$infoa['refundamount'].'</td>

				</tr>


				<tr>

					<td>Reason</td>
					<td>'.$infoa['refundreason'].'</td>

				</tr>

				<tr>

					<td>Payment ID</td>
					<td>'.$payment_idshow.'</td>

				</tr>

				<tr>

					<td>Amount: </td>
					<input type="hidden" name="orderid" value="'.$infoa['id'].'">
					<input type="hidden" name="order_session" value="'.$infoa['order_session'].'">
					<td style="position:relative;"><span style="position: absolute;left: 25px;top: 22px;">£</span>

					<input type="text" class="input" name="percentage" value="'.sprintf('%.2f', $infoa['price'] / 100).'" style="    float: left;width: 120px;margin: 0; margin-right: 40px;padding-left:30px;">
                    <input style="float:left;width:190px;" type="submit" onclick="return confirm(\'Are you sure you\'ve refunded this?\');"
                    name="submit"
                   class="btn btn-primary color3" value="Refund Complete" fdprocessedid="vc3xle"></td>
                  
				</tr>
				<tr>
												   
				<td style="position:relative;">
							<input type="hidden" name="order_session" value="'.$infoa['order_session'].'">
							<input type="hidden" name="orderid" value="'.$infoa['id'].'">
                            <input type="submit" onclick="return confirm(\'Are you sure you\'ve to undo refunded?\');"
                            name="undoRefund" class="btn btn3 report nlbtn copy-buttonn " value="Undo Refund"
                            fdprocessedid="a6wljh" style="background-color:#fff">
					</td>
                   
                    <td></td>

			</tr>

				

			</table>';



}
else{
	// if($page != 0){
	// 	header("Location: /admin/complete-refund.php?page=". ($page-1));
	// }
}


///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////


if(mysql_num_rows($q)=='0'){
	
	// $q = mysql_query("SELECT * FROM `automatic_likes_billing` WHERE `refunded` = '1' LIMIT 1");
	
	if (isset($_GET['page'])) {$page = addslashes($_GET['page']);} else {$page = 1;}
	if($page < 1)$page = 1;

	$no_of_records_per_page = 1;
	$offset = ($page-1) * $no_of_records_per_page;





	$q = mysql_query("SELECT * FROM `automatic_likes_billing` WHERE `refunded` = '1' LIMIT $offset, $no_of_records_per_page");


	$q2 = mysql_query("SELECT * FROM `automatic_likes_billing` WHERE `refunded` = '1'");
	$totalleft = mysql_num_rows($q2).' Refunds Remaining';

//go into AL refund mode



$infoa = mysql_fetch_array($q);

$brandName = getBrandSelectedName($infoa['brand']);
$brand = $refundinfo['brand'];

$accountq = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$infoa['account_id']}' LIMIT 1");
$accountinfo = mysql_fetch_array($accountq);

   
$orderinfo = 'ID: '.$infoa['id'].'<br>';
$orderinfo .= 'Email address: '.$accountinfo['email'].'<br>';
$orderinfo .= 'IG Username: '.$infoa['igusername'].'<br>';
$orderinfo .= 'Price: £'.$infoa['amount'].'<br>';
$orderinfo .= 'Orded Placed: '.date('l jS \of F Y H:i:s ', $infoa['added']).'<br>';

$orderinfo = '<div style="font-size:14px;font-family:verdana;padding:10px;line-height: 29px;">'.$orderinfo.'</div>';


//AL TABLE

$result = '<table class="articles" style="'.$showorno.'">

                <tr> <td>Company</td> <td> <img src="/admin/assets/icons/'. $brandName .'.svg"> </td> </tr>

				<tr>

					<td>ℹ Automatic Likes Billing ID</td>
					<td><input type="hidden" autocomplete="off" name="id" value="'.$infoa['id'].'" class="input">'.$orderinfo.'</td>

				</tr>




				<tr>

					<td>Payment ID</td>
					<td>Cardinity: <a target="_BLANK" rel="noopener noreferrer" href="https://my.cardinity.com/payment/show/'.$infoa['payment_id'].'">'.$infoa['payment_id'].'</a></td>

				</tr>

				<tr>

					<td>Amount: </td>
					<input type="hidden" name="postemailaddress" value="'.$accountinfo['email'].'">
					<input type="hidden" name="alorderid" value="'.$infoa['id'].'">
					<input type="hidden" name="alautolikesid" value="'.$infoa['auto_likes_id'].'">
					<td style="position:relative;"><span style="position: absolute;left: 25px;top: 22px;">£</span>

					<input type="text" class="input" name="percentage" value="'.$infoa['amount'].'" style="    float: left;width: 120px;margin: 0; margin-right: 40px;padding-left:30px;">
					<input style="float:left;width:190px;" type="submit" onclick="return confirm(\'Are you sure you\'ve refunded this?\');" name="submit" class="btn color3" value="Refund Complete"></td>

				</tr>
				<tr>
												   
					<td style="position:relative;">
								<input type="hidden" name="orderid" value="'.$infoa['id'].'">
						<input type="submit" onclick="return confirm(\'Are you sure you\'ve to undo refunded?\');" name="undoRefund" class="btn btn3 report copy-buttonn nlbtn" value="Undo Refund"></td>
					<td></td>

				</tr>

			</table>';




}


if(mysql_num_rows($q)=='0')$showorno = 'display:none;';


$tpl = str_replace('{prevpage}',($page-1),$tpl);
$tpl = str_replace('{nextpage}',($page+1),$tpl);
$tpl = str_replace('{totalleft}',$totalleft,$tpl);
$tpl = str_replace('{success}',$success,$tpl);
$tpl = str_replace('{tpmsg}',$tpmsg,$tpl);
$tpl = str_replace('{showorno}',$showorno,$tpl);
$tpl = str_replace('{result}',$result,$tpl);

output($tpl, $options);
