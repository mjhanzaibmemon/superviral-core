<?php

include('adminheader.php');

$dbName = $tikoidDB;
mysql_select_db($dbName , $conn);

$id = addslashes($_GET['id']);

$orderid = addslashes($_POST['orderid']);
$order_session = addslashes($_POST['order_session']);

$alorderid = addslashes($_POST['alorderid']);
$alautolikesid = addslashes($_POST['alautolikesid']);

$customamount = addslashes($_POST['percentage']);



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






if(!empty($orderid)){

		$getrefundinfoq = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid' AND `order_session` = '$order_session' LIMIT 1");
		$refundinfo = mysql_fetch_array($getrefundinfoq);

			

		$countryloc = $refundinfo['country'];

		$amount = $locas[$countryloc]['currencysign'].$customamount;

		$recipient = $refundinfo['emailaddress'];

		$lastfour = $refundinfo['lastfour'];
		$ordernum = $refundinfo['id'];
		$service = $refundinfo['amount'].' Instagram '.ucwords($refundinfo['packagetype']);
		$payment  = $refundinfo['price'];




			if(is_numeric($refundinfo['payment_id'])){

					echo 'PAYMENT: '.$payment.' - '.$customamount.'<hr>';

					$now2 = date('omdHis',time());
					$now2 = substr($now2, 4);
					$now2 = date("Y").$now2;

					$refundonacquired =  array(
					      
					    "timestamp" => $now2,
					    "company_id" => $acquiredaccountid,
					    "company_pass" => $acquiredcompanypass,

				        "transaction" => array(
					        "transaction_type" => 'REFUND',
					        "original_transaction_id" => $refundinfo['payment_id'],
					        "amount" => $customamount,
					    ),
		    

					);

					$request_hash = hash('sha256',$now2.'REFUND'.$acquiredaccountid.$refundinfo['payment_id'].$acquiredsecretpasscode);

					$refundonacquired['request_hash'] = $request_hash;


					$url = "https://gateway.acquired.com/api.php";    
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
					echo '</pre>';*/

				

			}

	$updateq = mysql_query("UPDATE `orders` SET `refund` = '2',`order_response` = '$refundtrackingmsg',`refundtime` = '$now' WHERE `id` = '$orderid' AND `order_session` = '$order_session' LIMIT 1");


			if(preg_match("/[a-z]/i", $refundinfo['payment_id'])){



					 $cardinityamt = floatval($customamount);

					$method = new Refund\Create(
					   $refundinfo['payment_id'],
					     $cardinityamt,
					    'my description'
					);
				
					$refund = $client->call($method);




			}



		//include('../emailrefund.php');

		$now = time();

		$refundreason = ' - requested by customer';
		if($refundinfo['refundamount']=='fraud'){$refundreason = ' - due to irregular card payment activity';}

		$refundprice = sprintf('%.2f', $info['price'] / 100);
		$refundtrackingmsg = $refundinfo['order_response'].'~~~'.$now.'###A refund for £'.$customamount.' issued to the card ending with '.$refundinfo['lastfour'].$refundreason.'###0.2';

		$updateq = mysql_query("UPDATE `orders` SET `refund` = '2',`order_response` = '$refundtrackingmsg',`refundtime` = '$now' WHERE `id` = '$orderid' AND `order_session` = '$order_session' LIMIT 1");

		$refundreason = '';

		if($updateq)$success = '<div class="emailsuccess">A refund of $'.$customamount.' has been issued to Order #'.$orderid.$refundreason.'</div>';

		if(!empty($tpmsg))$tpmsg = '<div style="padding:10px;">'.$tpmsg.'</div>';


}//END OF IF SOMETHING SUBMITTED



///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////





///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////


$q = mysql_query("SELECT * FROM `orders` WHERE `refund` = '1' AND `disputed` = '0' LIMIT 1");

//$q = mysql_query("SELECT * FROM `orders` WHERE `refund` = '1' AND `disputed` = '0' AND `payment_id` LIKE '%-%' LIMIT 1");


if(mysql_num_rows($q)=='1'){


$infoa = mysql_fetch_array($q);



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

				<tr>

					<td>ℹ Order ID</td>
					<td><input type="hidden" autocomplete="off" name="id" value="'.$infoa['id'].'" class="input">'.$orderinfo.'</td>

				</tr>


				<tr>

					<td>Reason/amount</td>
					<td>'.$infoa['refundamount'].'</td>

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
					<input style="float:left;width:190px;" type="submit" onclick="return confirm(\'Are you sure you\'ve refunded this?\');" name="submit" class="btn color3" value="Complete Refund"></td>

				</tr>

			</table>';



}



///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////









if(mysql_num_rows($q)=='0')$showorno = 'display:none;';








?>
<!DOCTYPE html>
<head>
<title>Finalise TikTok Refunds</title>
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

		<h1 style="text-align:center;margin-top:35px;">Finalise Tikoid Refunds 💳</h1>

		<div class="box23">

			<?=$success?>

			<?=$tpmsg?>

			<form method="POST" style="<?=$showorno?>">

				<?=$result?>

			</form>
		</div>





	</body>
</html>