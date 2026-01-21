<?php

if(!$info)exit('No details found');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/Exception.php';
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/PHPMailer.php';
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/SMTP.php';


$sender = 'orders@tikoid.com';
$senderName = 'Tikoid';
$usernameSmtp = $sesusername;
$passwordSmtp = $sespassword;
$host = 'email-smtp.us-east-1.amazonaws.com';
$port = 587;

if(!empty($info['upsell'])){

$ups = '<tr><td>'.$info['igusername'].'</td><td>Additional '.$upsellprice[0].' '.$packageinfo['type'].'</td><td>&pound;'.$upsellprice.'</td></tr>';}

$mainorderinfoq = mysql_query("SELECT * FROM `orders` WHERE `order_session` = '{$info['order_session']}' AND `brand` = 'to' LIMIT 1");
$mainorderinfo = mysql_fetch_array($mainorderinfoq);

$ordernum = $mainorderinfo['id'];

$username = $info['igusername'];
$payment  = $packageinfo['price'];
$service = $packageinfo['amount'].' TikTok '.ucwords($packageinfo['type']);



$recipient = $info['emailaddress'];
$subject = 'Order confirmation: #'.$ordernum;

$ctahref = 'https://tikoid.com/track-my-order/'.$mainorderinfo['order_session'];

$bodyHtml = file_get_contents('emailtemplate/ordercon.html');
$bodyHtml = str_replace('{ordernum}', $ordernum, $bodyHtml);
$bodyHtml = str_replace('{ups}', $ups, $bodyHtml);
$bodyHtml = str_replace('{username}', $username, $bodyHtml);
$bodyHtml = str_replace('{payment}', $payment, $bodyHtml);
$bodyHtml = str_replace('{service}', $service, $bodyHtml);
$bodyHtml = str_replace('{subject}', $subject, $bodyHtml);
$bodyHtml = str_replace('{ctahref}', $ctahref, $bodyHtml);

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
   // echo "Email sent!" , PHP_EOL;
   sendCloudwatchData('Tikoid', 'email-success', 'EmailOrderConfirmation', 'order-confirmation-success-function', 1);

} catch (phpmailerException $e) {
   // echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
   sendCloudwatchData('Tikoid', 'email-failure', 'EmailOrderConfirmation', 'order-confirmation-failure-function', 1);
} catch (Exception $e) {
    //echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
    sendCloudwatchData('Tikoid', 'email-failure', 'EmailOrderConfirmation', 'order-confirmation-failure-function', 1);
}


?>