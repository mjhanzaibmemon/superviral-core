<?php

if($emailtrue!=='1')die('No details found 1');

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

$recipient = $info['emailaddress'];
//$recipient = 'r.faruqui@live.co.uk';//////THIS IS THE ONE
$subject = "Free Instagram Followers ".$orderinfo['id'];

$themd5 = md5($recipient.$orderinfo['id']);

$insertq = mysql_query("INSERT INTO `freetrial` SET `brand`='sv', `md5` = '{$themd5}',`emailaddress`='{$info['emailaddress']}',`type`='1',`ipaddress` = '{$_SERVER['REMOTE_ADDR']}'");
if(!$insertq)die('Not inserted free trial');

$added = time();
//UPDATE USER
$updateuser = mysql_query("INSERT IGNORE INTO `users` SET `brand`='sv', `emailaddress` = '{$info['emailaddress']}', `freetrial` = '1',`source` = 'freetrial',`added` = '{$added}' ");

$updateuser = mysql_query("UPDATE `users` SET `freetrial` = '1' WHERE `emailaddress` = '{$info['emailaddress']}', `brand`='sv'");

$loc2 = $loc;
if(empty($loc2))$loc2=$info['country'];
if(!empty($loc2))$loc2 = $loc2.'.';
if($loc2=='ww.')$loc2 = '';

$ctalink = 'https://superviral.io/'.$loclinkforward.'free-followers/?id='.$themd5;

$emailhtml = file_get_contents('emailtemplate/offerfreefollowers.html');

$emailhtml = str_replace('{subject}', $subject, $emailhtml);
$emailhtml = str_replace('{ctalink}', $ctalink, $emailhtml);

$bodyText =  strip_tags($emailhtml);


$senderName = 'James Harris';


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
    $mail->Body       = $emailhtml;
    $mail->AltBody    = $bodyText;
    $mail->Send();
    //echo "Thank you Email sent to:".$recipient , PHP_EOL;


} catch (phpmailerException $e) {
    //echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
} catch (Exception $e) {
    //echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
}



?>