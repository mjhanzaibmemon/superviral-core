<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/Exception.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/PHPMailer.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/SMTP.php';

function emailnow($recipient,$senderName,$sender,$subject,$emailhtml){


$bodyText =  strip_tags($body);

global $sesusernamev2;
global $sespasswordv2;

$usernameSmtp = $sesusernamev2;
$passwordSmtp = $sespasswordv2;

$host = 'email-smtp.us-east-2.amazonaws.com';
$port = 587;

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
    //$mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configurationSet);
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
    //echo "<br><b style='color:green'>SENT to: ".$recipient."</b><br>" , PHP_EOL;


} catch (phpmailerException $e) {
    //echo "<br><b style='color:red'>AN ERROR OCCURED</b><br> {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
    mysql_query("UPDATE `users` SET `unsubscribe` = '1' WHERE `emailaddress` = '$recipient' LIMIT 1");
} catch (Exception $e) {
    //echo "<br><b style='color:red'>EMAIL NOT SENT</b><br> {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
    mysql_query("UPDATE `users` SET `unsubscribe` = '1' WHERE `emailaddress` = '$recipient' LIMIT 1");
}

}

function email_stat_insert($category, $email, $body, $brand){

    $now = time();
    mysql_query("INSERT INTO `email_sent_stats` SET
        `category` = '$category',
        `recipient_email` = '$email',
        `body` = '$body',
        `brand` = '$brand',
        `sent_date` = '$now'
    ");


}

?>
