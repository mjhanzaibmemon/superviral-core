<?php

if($emailtrue!=='1')die('No details found 1');

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

$recipient = $info['emailaddress'];
//$recipient = 'r.faruqui@live.co.uk';//////THIS IS THE ONE
$subject = "Free TikTok Followers ".$orderinfo['id'];

$themd5 = md5($recipient.$orderinfo['id']);

$insertq = mysql_query("INSERT INTO `freetrial` SET `md5` = '{$themd5}',`emailaddress`='{$info['emailaddress']}',`type`='1',`ipaddress` = '{$_SERVER['REMOTE_ADDR']}', `brand` = 'to'");
if(!$insertq)die('Not inserted free trial');

$added = time();
//UPDATE USER
$updateuser = mysql_query("INSERT IGNORE INTO `users` SET `emailaddress` = '{$info['emailaddress']}', `freetrial` = '1',`source` = 'freetrial',`added` = '{$added}', `brand` = 'to' ");

$updateuser = mysql_query("UPDATE `users` SET `freetrial` = '1' WHERE `emailaddress` = '{$info['emailaddress']}' AND `brand` = 'to' ");

$ctalink = 'https://tikoid.com/free-followers/?id='.$themd5;

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