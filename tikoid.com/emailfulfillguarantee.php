<?php

if(!$info)exit('No details found');


$sender = 'orders@tikoid.com';
$senderName = 'Tikoid';
$usernameSmtp = $sesusername;
$passwordSmtp = $sespassword;
$host = 'email-smtp.us-east-1.amazonaws.com';
$port = 587;

$recipient = $info['emailaddress'];
$subject = 'Tikoid Satisfaction Guarantee since 2012';

$bodyHtml = file_get_contents('emailtemplate/orderguarantee.html');
$bodyHtml = str_replace('{username}', $username, $bodyHtml);
$bodyHtml = str_replace('{payment}', $payment, $bodyHtml);
$bodyHtml = str_replace('{service}', $service, $bodyHtml);
$bodyHtml = str_replace('{subject}', $subject, $bodyHtml);

$bodyText =  strip_tags($bodyHtml);

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
    //echo "Email sent!" , PHP_EOL;
} catch (phpmailerException $e) {
    //echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
} catch (Exception $e) {
    //echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
}


echo $bodyHtml;

?>