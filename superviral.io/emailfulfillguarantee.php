<?php

if(!$info)exit('No details found');


$sender = 'no-reply@superviral.io';
$senderName = 'Superviral';
$usernameSmtp = $sesusernamev2;
$passwordSmtp = $sespasswordv2;
$host = 'email-smtp.us-east-2.amazonaws.com';
$port = 587;

$recipient = $info['emailaddress'];
$subject = '✨ Your Guarantee for All Orders';

if($webhook==1){$bodyHtml = file_get_contents('../emailtemplate/orderguarantee.html');}else{$bodyHtml = file_get_contents('emailtemplate/orderguarantee.html');}

$bodyHtml = str_replace('{username}', $username, $bodyHtml);
$bodyHtml = str_replace('{payment}', $payment, $bodyHtml);
$bodyHtml = str_replace('{service}', $service, $bodyHtml);
$bodyHtml = str_replace('{subject}', $subject, $bodyHtml);

$socialmedia = $info['socialmedia'];

if($notenglish==true){


    $result = $translate->translate($subject, [
        'source' => 'en', 
        'target' => $locas[$loc]['sdb'],
        'format' => 'html'
    ]);

    $subject = $result['text'];


    $result = $translate->translate($bodyHtml, [
        'source' => 'en', 
        'target' => $locas[$loc]['sdb'],
        'format' => 'html'
    ]);

    $bodyHtml = $result['text'];


}

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
    //echo "Email guarantee sent!" , PHP_EOL;
    $didemailsend = 'Email Sent!';
} catch (phpmailerException $e) {
   // echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
   $didemailsend = 'Email NOT Sent!';
} catch (Exception $e) {
    //echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
    $didemailsend = 'Email NOT Sent!';
}

/*
if($didemailsend == "Email Sent!"){

    $Common = new Common(); //

    $Common->email_stat_insert('Order Complete', $recipient, addslashes($bodyText), 'sv');
}
    */
//echo $bodyHtml;

?>