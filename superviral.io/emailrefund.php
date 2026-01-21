<?php



/*




//IF THERE'S AN ERROR THEN ITS THE TRANSLATION GUARANTEED




*/


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

//$percentage = addslashes($percentage) / 100.00;
/*$payment = $payment * $percentage;
$payment  = sprintf('%.2f', $payment / 100);*/


$subject = 'Refund for Order #'.$ordernum;

$bodyHtml = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]).'/superviral.io/emailtemplate/orderrefund.html');

$bodyHtml = str_replace('{yearend}', date("Y"), $bodyHtml);
$bodyHtml = str_replace('{ordernum}', $ordernum, $bodyHtml);
$bodyHtml = str_replace('{service}', $service, $bodyHtml);
$bodyHtml = str_replace('{amount}', $amount, $bodyHtml);
$bodyHtml = str_replace('{lastfour}', $lastfour, $bodyHtml);
$bodyHtml = str_replace('{subject}', $subject, $bodyHtml);


                   if(($refundinfo['country']!=='ww')&&($refundinfo['country']!=='us')&&($refundinfo['country']!=='uk'))$notenglish2=true;                

                    

                    if($notenglish2==true){

                        //IF THERE'S AN ERROR THEN ITS IN HERE

                        // $thisloc = $refundinfo['country'];

                        //   $result = $translate->translate($bodyHtml, [
                        //       'source' => 'en', 
                        //       'target' => $locas[$thisloc]['sdb'],
                        //       'format' => 'html'
                        //   ]);

                        //   $bodyHtml = $result['text'];



                        //   $result = $translate->translate($subject, [
                        //       'source' => 'en', 
                        //       'target' => $locas[$thisloc]['sdb'],
                        //       'format' => 'html'
                        //   ]);

                        //   $subject = $result['text'];



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
    //echo "Email sent!" , PHP_EOL;
} catch (phpmailerException $e) {
    echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
    $didemailsend = 'Email NOT Sent!';
} catch (Exception $e) {
    echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
    $didemailsend = 'Email NOT Sent!';
}


?>