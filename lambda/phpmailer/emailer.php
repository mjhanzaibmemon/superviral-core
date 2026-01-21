<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

function emailnow($recipient,$senderName,$sender,$subject,$emailhtml){

    $bodyText =  strip_tags($emailhtml);

    $sesusername = getenv('sesusername');
    $sespassword = getenv('sespassword');
    // echo $sesusername. ' aj';
    $usernameSmtp = $sesusername;
    $passwordSmtp = $sespassword;
    $host = 'email-smtp.us-east-1.amazonaws.com';
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
        echo "<br><b style='color:green'>SENT to: ".$recipient."</b><br>" , PHP_EOL;
        sendCloudwatchData('AWSLambda', 'aws-lambda-email-success', 'Emailer', 'aws-lambda-email-success-function', 1);
    } catch (phpmailerException $e) {

        writeCloudWatchLog('Lambda-Emailer', $recipient .' Caught exception: '.  $e->errorMessage());
        sendCloudwatchData('AWSLambda', 'aws-lambda-email-failure', 'Emailer', 'aws-lambda-email-failure-function', 1);
        echo "<br><b style='color:red'>AN ERROR OCCURED</b><br> {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
    } catch (Exception $e) {

        writeCloudWatchLog('Lambda-Emailer', $recipient .' Caught exception: '.  $mail->ErrorInfo);
        sendCloudwatchData('AWSLambda', 'aws-lambda-email-failure', 'Emailer', 'aws-lambda-email-failure-function', 1);
        echo "<br><b style='color:red'>EMAIL NOT SENT</b><br> {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
    }

}

function email_stat_insert($category, $email, $body, $brand){

    $now = time();
    mysql_query("INSERT INTO `email_sent_stats` SET
        `category` = '$category',
        `recipient_email` = '$email',
        `body` = '".urlencode($body)."',
        `brand` = '$brand',
        `sent_date` = '$now'
    ");


}

?>