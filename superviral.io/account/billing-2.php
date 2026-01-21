<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$activelink3 = 'activelink';


include('../db.php');
include('auth.php');
include('header.php');


$now = time();

///////////////////

function encrypt($data, $password){
	$iv = substr(sha1(mt_rand()), 0, 16);
	$password = sha1($password);

	$salt = sha1(mt_rand());
	$saltWithPassword = hash('sha256', $password.$salt);

	$encrypted = openssl_encrypt(
	  "$data", 'aes-256-cbc', "$saltWithPassword", null, $iv
	);
	$msg_encrypted_bundle = "$iv:$salt:$encrypted";
	return $msg_encrypted_bundle;
}


function decrypt($msg_encrypted_bundle, $password){
	$password = sha1($password);

	$components = explode( ':', $msg_encrypted_bundle );
	$iv            = $components[0];
	$salt          = hash('sha256', $password.$components[1]);
	$encrypted_msg = $components[2];

	$decrypted_msg = openssl_decrypt(
	  $encrypted_msg, 'aes-256-cbc', $salt, null, $iv
	);

	if ( $decrypted_msg === false )
		return false;

	$msg = substr( $decrypted_msg, 41 );
	return $decrypted_msg;
}

//////////////////
function secondsToTime($s)
{
    $h = floor($s / 3600);
    $s -= $h * 3600;
    $m = floor($s / 60);
    $s -= $m * 60;
    return $h.':'.sprintf('%02d', $m).':'.sprintf('%02d', $s);
}



function isValidTimeStamp($timestamp)
{
    return ((string) (int) $timestamp === $timestamp) 
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
}

/////////////////// ENABLE AND DISABLE PAYMENTS



if($_GET['billingsave']=='true'){mysql_query("UPDATE `accounts` SET `disablesavepayments` = '0' WHERE `id` = '{$userinfo['id']}' LIMIT 1");
header('Location: /account/settings/billing/');
}


if($_GET['billingsave']=='false'){mysql_query("UPDATE `accounts` SET `disablesavepayments` = '1' WHERE `id` = '{$userinfo['id']}' LIMIT 1");
header('Location: /account/settings/billing/');
}

if($userinfo['disablesavepayments']==0){
$billingsavehref = 'false';
$billingsavecta = 'Disable saving card at checkout';
$billingsavestatus = '<font color="green">Fast checkout - Enabled</font>';}


if($userinfo['disablesavepayments']==1){
$billingsavehref = 'true';
$billingsavecta = 'Enable saving card at checkout';
$billingsavestatus = '<font color="red">Saving card details - Disabled</font>';}


//////////////////// SAVE CARD DETAILS

if(!empty($_POST['submitcardpayment'])){

$cardholdername = addslashes($_POST['cardholdername']);
$longdigits = addslashes($_POST['longdigits']);
$expirydate = addslashes($_POST['expirydate']);
$expirydate1 = explode('/', $expirydate);
$card_brand = addslashes($_POST['cardbrand']);

if(empty($card_brand))$card_brand = addslashes($_POST['cardbrand1']);

if(empty($cardholdername)){$error1 = '<div class="emailsuccess emailfailed">Cardholder name missing!</div>';}
if(empty($longdigits)){$error2 = '<div class="emailsuccess emailfailed">16-long digits missing!</div>';}
if(empty($expirydate)){$error3 = '<div class="emailsuccess emailfailed">Expiry date</div>';}



if((isset($error1))||(isset($error2))||(isset($error3))||(isset($error0))){



} else{//SUCESS - NO ERRORS so far; let's start saving!

	$lastfour = substr(str_replace(' ','',$longdigits), -4);

	$checkothercardsq = mysql_query("SELECT * FROM `save_payment_details` WHERE `account_id` = '{$userinfo['id']}' AND `brand`='sv' AND `approved` = '1' ");

	if(mysql_num_rows($checkothercardsq)==0)$primarycard = " `primarycard` = '1', ";

	$billingnamehash = encrypt($cardholdername,$billingnamesecretphrase);
	$longdigitshash = encrypt($longdigits,$longdigitsecretphrase);
	$lastfourhash = encrypt($lastfour,$lastfoursecretphrase);
	$exphash = encrypt($expirydate,$expsecretphrase);
	
	$expmonth = trim(str_replace(' ','',$expirydate1[0]));
	$expyear = trim(str_replace(' ','',$expirydate1[1]));
	$expyear = str_replace('20','',$expyear);
	$expyear = str_replace('20','',$expyear);
	$expyear = '20'.$expyear;

	if(iconv_strlen($expmonth)==1)$expmonth = '0'.$expmonth;
	$expirydays = cal_days_in_month(CAL_GREGORIAN, $expmonth, $expyear );
	$expiryunix = mktime(23, 59, 59, $expmonth, $expirydays, $expyear);

	if(!is_numeric($expiryunix)){$expiryunix = 0;
	$error3 = '<div class="emailsuccess emailfailed">Please enter the correct expiry date on your card.</div>';}else{

		if($now > $expiryunix){$error3 = '<div class="emailsuccess emailfailed">Please enter a card that\'s not expired.</div>';}

	}

/*	echo $expiryunix.'<hr>';
	echo $exphash.'<hr>';*/
	
	if((empty($error1))&&(empty($error2))&&(empty($error3))&&(empty($error0))){

			//NOW CHECK IF THE CARD ALREADY EXISTS FOR THIS ACCOUNT
			$insertcardq = mysql_query("INSERT INTO `save_payment_details` SET 


				`brand` = 'sv',
				`account_id` = '{$userinfo['id']}', 
				`card_brand` = '$card_brand', 
				`billingnamehash` = '$billingnamehash', 
				`longdigitshash` = '$longdigitshash', 
				`lastfourhash` = '$lastfourhash', 
				`exphash` = '$exphash', 
				$primarycard
				`expiryunix` = '$expiryunix'
				");


			if($insertcardq){$success1 = '<div class="emailsuccess">Your card was saved successfully!</div>';}else{$error0 = '<div class="emailsuccess emailfailed">Card wasn\'t saved, please contact our support team with error #49310. We apologise for any inconvenience caused.</div>';}


	}

}

if((isset($error1))||(isset($error2))||(isset($error3))||(isset($error0))){//onload with modal showing with error

	$modalonload = '<script type="text/javascript">
	    $(window).on(\'load\', function() {
	        $(\'#ex1\').modal(\'show\');
	    });
	</script>';

}


}






########## DELETE CARD


if(!empty($_GET['deleteid'])){

	$deleteid = addslashes($_GET['deleteid']);

	$fetchdeletecardq = mysql_query("SELECT * FROM `save_payment_details` WHERE `account_id` = '{$userinfo['id']}' AND `id` = '$deleteid'  LIMIT 1");

	$fetechdeletecardinfo = mysql_fetch_array($fetchdeletecardq);

	$deletecardq = mysql_query("DELETE FROM `save_payment_details` WHERE 
			`account_id` = '{$userinfo['id']}' 
		AND `id` = '$deleteid' 
		LIMIT 1");

	if($deletecardq)$success1 = '<div class="emailsuccess" style="margin-bottom:15px;">Your card ending with '.$fetechdeletecardinfo['last_four'].' was deleted successfully!</div>';

}











######### MAKE PRIMARY

if(!empty($_GET['primaryid'])){

	$primaryid = addslashes($_GET['primaryid']);

	$updateallasnotprimaryq = mysql_query("UPDATE `save_payment_details` SET `primarycard` = '0' WHERE `account_id` = '{$userinfo['id']}' AND `brand`='sv'");

	if($updateallasnotprimaryq)$updatenewprimarycardq = mysql_query("UPDATE `save_payment_details` SET `primarycard` = '1' WHERE `account_id` = '{$userinfo['id']}' AND `id` = '$primaryid' LIMIT 1");

	$fetchprimarycardq = mysql_query("SELECT * FROM `save_payment_details` WHERE `account_id` = '{$userinfo['id']}' AND `id` = '$primaryid'  LIMIT 1");

	$fetechprimarycardinfo = mysql_fetch_array($fetchprimarycardq);


	if($updatenewprimarycardq)$success1 = '<div class="emailsuccess" style="margin-bottom:15px;">Your card ending with '.$fetechprimarycardinfo['last_four'].' was updated as the primary card!</div>';

}





######### CARD RESULTS

$findcardsq = mysql_query("SELECT * FROM `save_payment_details` WHERE `account_id` = '{$userinfo['id']}' AND `brand`='sv' ORDER BY `id` DESC");

if(mysql_num_rows($findcardsq)!==0){

	while($cardinfo = mysql_fetch_array($findcardsq)){

		if(!empty($cardinfo['card_brand'])){

			if($cardinfo['card_brand']=='Visa')$imgcardbrand = 'visa';
			if($cardinfo['card_brand']=='Mastercard')$imgcardbrand = 'mastercard';
			if($cardinfo['card_brand']=='American Express')$imgcardbrand = 'amex';
			if($cardinfo['card_brand']=='Maestro')$imgcardbrand = 'maestro';

			if($cardinfo['primarycard']=='0'){$makeprimary = '<a class="btn btn3 savingcardbtn" href="?primaryid='.$cardinfo['ID'].'">make primary</a>';}else{
				$primaryclass='primary';
				$makeprimary = '<div class="primarystatus">PRIMARY</div>';
			}

			$cardbrandset = '<img class="cardbrand" src="/imgs/payment-icons/'.$imgcardbrand.'.svg"> <b>'.$cardinfo['card_brand'].'</b> ';}

			
			$nowplus = time() + 2592000;



			//CHECK IF ITS EXPIRING WITHIN THE NEXT 30-DAYS
			if(($now <= $cardinfo['expiry_unix']) && ($cardinfo['expiry_unix'] <= $nowplus)){


				$datediff = $now - $cardinfo['expiry_unix'];
				$calctime = round($datediff / (60 * 60 * 24));

				$expiredmsg = '<div class="expired expiring">Expiring in '.str_replace('-','',$calctime).' days</div>';}

			if($now > $cardinfo['expiry_unix']){$expiredmsg = '<div class="expired">Expired</div>';$makeprimary = '';}

			 

	$cardresults .= '<div class="savedcards '.$primaryclass.' dshadow">'.$cardbrandset.'**** '.$cardinfo['last_four'].$expiredmsg.$makeprimary.'<a onclick="return confirm(\'Are you sure you want to delete this card?\');" class="deletecard" href="?deleteid='.$cardinfo['ID'].'"><img src="/imgs/x-mark.png"></a>


	</div>';

		unset($cardbrandset);
		unset($makeprimary);
		unset($primaryclass);
		unset($expiredmsg);

		}

} else {

	
	$cardresults = 'Unfortunately, there\'s no card details saved for this account.';


}



######### RESULTS

$findsubcriptonsq = mysql_query("SELECT * FROM `automatic_likes` WHERE `account_id` = '{$userinfo['id']}' AND `brand`='sv' ORDER BY `id` DESC");

if(mysql_num_rows($findsubcriptonsq)!==0){

	while($subsinfo = mysql_fetch_array($findsubcriptonsq)){

		$fetchimgq = mysql_query("SELECT * FROM `ig_dp` WHERE `igusername` LIKE '%{$subsinfo['igusername']}%' ORDER BY `id` DESC LIMIT 1");
		$fetchimg = mysql_fetch_array($fetchimgq);


		$fetchtransactionsq = mysql_query("SELECT * FROM `automatic_likes_billing` WHERE `auto_likes_id` = '{$subsinfo['id']}' AND `brand`='sv'");
		while($fetchtransactions = mysql_fetch_array($fetchtransactionsq)){


			$transactions .= '			<table>

				<tr>

				<td>'.$fetchtransactions['currency'].$fetchtransactions['amount'].'</td>
				<td>'.gmdate("d/m/Y",$fetchtransactions['added']).'</td>
				<td>payment successful</td>



				<tr>

			</table>';


		}


	$subscriptionresults .= '


	<div class="subscriptions dshadow">

		<img class="dp" src="https://cdn.superviral.io/dp/'.$fetchimg['dp'].'.jpg">
		


			
			<div class="substitle subtitlemain"><b>'.$subsinfo['likes_per_post']. ' likes per post</b> <font class="username">  @'.$subsinfo['igusername'].'</font>
			</div>
			<div class="substitle">
				<div class="status statuspaused">Auto Likes Active <img class="spinning" src="/imgs/inprogressgreen.svg"></div>
				<a href="/account/manage-auto-likes.php?id='.$subsinfo['md5'].'" class="btn btn3 savingcardbtn dshadow">change</a>
			</div>
		'.$transactions.'

	</div>';

		unset($cardbrandset);
		unset($makeprimary);
		unset($primaryclass);
		unset($expiredmsg);

		}

} else {

	
	$subscriptionresults = 'Unfortunately, there\'s no subscriptions added.<br><br>';


}









$tpl = file_get_contents('billing-2.html');
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{billingsavestatus}', $billingsavestatus, $tpl);
$tpl = str_replace('{billingsavecta}', $billingsavecta, $tpl);
$tpl = str_replace('{billingsavehref}', $billingsavehref, $tpl);

$tpl = str_replace('{cardholdername}', $cardholdername, $tpl);
$tpl = str_replace('{longdigits}', $longdigits, $tpl);
$tpl = str_replace('{expirydate}', $expirydate, $tpl);
$tpl = str_replace('{cardbrand}', $card_brand, $tpl);
$tpl = str_replace('{cardbrand1}', $card_brand1, $tpl);

$tpl = str_replace('{error0}', $error0, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{error2}', $error2, $tpl);
$tpl = str_replace('{error3}', $error3, $tpl);
$tpl = str_replace('{modalonload}', $modalonload, $tpl);
$tpl = str_replace('{success1}', $success1, $tpl);

$tpl = str_replace('{cardresults}', $cardresults, $tpl);
$tpl = str_replace('{subscriptionresults}', $subscriptionresults, $tpl);




$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

use Google\Cloud\Translate\V2\TranslateClient;

if($notenglish==true){

            // require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

            // $translate = new TranslateClient(['key' => $googletranslatekey]);

            // $result = $translate->translate($tpl, [
            //     'source' => 'en', 
            //     'target' => $locas[$loc]['sdb'],
            //     'format' => 'html'
            // ]);

            // $tpl = $result['text'];

}

echo $tpl;
?>
