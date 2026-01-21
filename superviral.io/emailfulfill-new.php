<?php

if(!$info)exit('No details found');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/Exception.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/PHPMailer.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/SMTP.php';



$sender = 'no-reply@superviral.io';
$senderName = 'Superviral';
$usernameSmtp = $sesusernamev2;
$passwordSmtp = $sespasswordv2;
$host = 'email-smtp.us-east-2.amazonaws.com';
$port = 587;

/////////////////////////////////////////////////// NEW ONE

$packageinfoq22 = mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `id` = '{$info['packageid']}' LIMIT 1");
$packageinfo23 = mysql_fetch_array($packageinfoq22);

if(!empty($info['upsell'])){

$upsellpriceformat = sprintf('%.2f', $upsellprice / 100);

$ups = '<tr><td>'.$info['igusername'].'</td><td>Additional '.$upsellamount.' '.$packageinfo23['type'].'</td><td>{currencysign}'.($upsellpriceformat * 1).'{currencyend}</td></tr>';}

if (!empty($info['upsell_all'])) {

    $upsellallpriceformat = sprintf('%.2f', $upsellprice1 / 100);
    
    $ups .= '<tr><td>' . $info['igusername'] . '</td><td>Additional '. $upsellamount1 .' Followers</td><td>{currencysign}' . ($upsellallpriceformat * 1) . '{currencyend}</td></tr>';}
    

if(!empty($info['upsell_autolikes'])){//WE'RE GETTING DATA HERE FROM order3-autolikes.php

$upsell_autolikesdb = explode('###',$info['upsell_autolikes']);

$upsellpriceformatal = $upsell_autolikesdb[4];

$ups .= '<tr><td>'.$info['igusername'].'</td><td>Automatic Instagram Likes ('.$info['igusername'].')</td><td>&pound;'.($upsellpriceformatal * 1).'</td></tr>';}


$mainorderinfoq = mysql_query("SELECT * FROM `orders` WHERE `brand`='sv' AND `order_session` = '{$info['order_session']}' ORDER BY `id` DESC LIMIT 1");
$mainorderinfo = mysql_fetch_array($mainorderinfoq);

$ordernum = $mainorderinfo['id'];

$username = $info['igusername'];
$payment  = $packageinfo23['price'];
$service = $packageinfo['amount'].' Instagram '.ucwords($packageinfo23['type']);


/////////////////////////////////////////////

$recipient = $info['emailaddress'];
$subject = 'Order confirmation: #'.$ordernum;

//STRIPE

$loc2 = $loc;
if(empty($loc2))$loc2=$info['country'];
if(!empty($loc2))$loc2 = $loc2.'.';
if($loc2=='ww.')$loc2 = '';

$ctahref = 'https://'.$loc2.'superviral.io/track-my-order/'.$mainorderinfo['order_session'].'/'.$ordernum;

if($webhook==1){$bodyHtml = file_get_contents('../emailtemplate/ordercon.html');}else{$bodyHtml = file_get_contents('emailtemplate/ordercon.html');}

if(!empty($mainorderinfo['payment_billingname']))$billingname = 'Cardholder name: '.$mainorderinfo['payment_billingname'].'<br><br>';

$bodyHtml = str_replace('{ordernum}', $ordernum, $bodyHtml);
$bodyHtml = str_replace('{ups}', $ups, $bodyHtml);
$bodyHtml = str_replace('{username}', $username, $bodyHtml);
$bodyHtml = str_replace('{payment}', $payment, $bodyHtml);
$bodyHtml = str_replace('{service}', $service, $bodyHtml);
$bodyHtml = str_replace('{subject}', $subject, $bodyHtml);
$bodyHtml = str_replace('{ctahref}', $ctahref, $bodyHtml);
$bodyHtml = str_replace('{billingname}', $billingname, $bodyHtml);
$bodyHtml = str_replace('{currentyear}', date("Y"), $bodyHtml);
$bodyHtml = str_replace('{currencysign}', $locas[$loc]['currencysign'], $bodyHtml);
$bodyHtml = str_replace('{currencyend}', $locas[$loc]['currencyend'], $bodyHtml);

$bodyText =  strip_tags($bodyHtml);

$mail = new PHPMailer(true);

try {
    // Specify the SMTP settings.
    $mail->isSMTP();
    $mail->setFrom($sender, $senderName);
    $mail->Username   = $usernameSmtp;
    $mail->Password   = $passwordSmtp;
    $mail->Host       = $host;
    $mail->Port       = $port;
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = 'tls';
    $mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configurationSet);
    $mail->CharSet = 'UTF-8';

    // Specify the message recipients.
    $mail->addAddress($recipient);
    // You can also add CC, BCC, and additional To recipients here.

    // Specify the content of the message.
    $mail->isHTML(true);
    $mail->Subject    = $subject;
    $mail->Body       = $bodyHtml;
    $mail->AltBody    = $bodyText;
    $mail->Send();
    echo "Email sent!" , PHP_EOL;
    $didemailsend = 'Email Sent!';
} catch (phpmailerException $e) {
    echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
    $didemailsend = 'Email NOT Sent!';
} catch (Exception $e) {
    echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
    $didemailsend = 'Email NOT Sent!';
}


echo 'Payment shown on email: '.$payment."\n";

?>