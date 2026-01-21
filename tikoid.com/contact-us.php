<?php

// start time
$start_time = microtime(true);

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=0;
include('header.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/Exception.php';
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/PHPMailer.php';
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/SMTP.php';















if(!empty($_POST['submit'])) {

$failed=0;

$secret = $tikoidrecaptchasecret;

$data = array('secret' => $secret,'response' => $_POST['g-recaptcha-response']);

$verify = curl_init();
curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
curl_setopt($verify, CURLOPT_POST, true);
curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($verify);

$response = json_decode($response, true);



  
    if($response["success"] == 1){
        // actions if successful


    }else{
      $emailsuccess = '<div class="emailsuccess emailfailed">Please ensure you\'ve proven you\'re not a robot by scrolling down and filling out the reCaptcha box where the blue arrow is!</div>';
        $failed=1;

        sendCloudwatchData('Tikoid', 'Failure', 'Contactus', 'contact-us-failure-function', 1);
    }


//if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222')print_r($response);

////////////////////// EMAILER

if($failed==0){

/*$usernameSmtp = $sesusername;
$passwordSmtp = $sespassword;
$host = 'email-smtp.us-east-1.amazonaws.com';
$port = 587;


    $mail = new PHPMailer(true);




    function clean_string($string) {
      $bad = array("content-type","bcc:","to:","cc:","href");
      return str_replace($bad,"",$string);
    }
     
    $first_name = $_POST['name']; // required
    $orderid = $_POST['orderid']; // required
    $replyto = $_POST['emailaddress']; // required
    $subject = $_POST['subject']; // required
    $emailhtml = $_POST['message']; // required
    $recipient = 'customer-care@tikoid.com'; // required
    $sender = 'no-reply@tikoid.com';
    $senderName = 'Tikoid Customer Care';

    $email_message .= "Full Name: ".clean_string($first_name)."<br>";
    $email_message .= "Order ID: ".clean_string($orderid)."<br>";
    $email_message .= "Email address: ".clean_string($replyto)."<br>";
    $email_message .= "".clean_string($emailhtml)."<br>";

    $bodyHtml =  $email_message;
    $bodyText =  strip_tags($email_message);

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
        $mail->addReplyTo($replyto, $first_name);

        // Specify the message recipients.
        $mail->addAddress($recipient);
        // You can also add CC, BCC, and additional To recipients here.

        // Specify the content of the message.
        $mail->isHTML(true);
        $mail->Subject    = $subject;
        $mail->Body       = $bodyHtml;
        $mail->AltBody    = $bodyText;
        $mail->Send();
     //   echo "Email sent!" , PHP_EOL;
        $didemailsend = 'Email Sent!';

        $emailsuccess = '<div class="emailsuccess">Thank you for contacting our support team. A member of our team will get back to within 1 working day.</div>';
    } catch (phpmailerException $e) {
       //echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
          $emailsuccess = '<div class="emailsuccess emailfailed">111Please ensure you\'ve entered all a correct email address and filled in all boxes.</div>';
        $didemailsend = 'Email NOT Sent!';
    } catch (Exception $e) {
       

        echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
        $emailsuccess = '<div class="emailsuccess emailfailed">Please ensure you\'ve entered all a correct email address and filled in all boxes.</div>';
        $didemailsend = 'Email NOT Sent!';
    }*/

    $first_name = addslashes($_POST['name']); // required
    $orderid    = addslashes($_POST['orderid']); // required
    $replyto    = trim(addslashes($_POST['emailaddress'])); // required
    $subject    = addslashes($_POST['subject']); // required
    $email  = strip_tags(addslashes($_POST['message']));
    // $email = str_replace("'","\'",$emailhtml);
    // $email = str_replace('"','\"',$email); 
    $dateAdded = time();

    $fetchMaxUidContactForm = mysql_query("SELECT max(`emailUid`) as maxId FROM `email_queue` WHERE `brand`='to' AND `source` = 'contactform'");
    $fetchMaxUid = mysql_fetch_array($fetchMaxUidContactForm);
    $maxUid = $fetchMaxUid["maxId"];

    if($maxUid == "" || $maxUid == null){
        $emailUid = 1;
    }else{
        $emailUid = intval($maxUid) + 1;
    }

////////////////////////EMAIL SPAM


$emailSpam = '0';



    $insertEmailQueueQuery = mysql_query("INSERT INTO  `email_queue` 
                                                                SET `brand` = 'to',
                                                                    `subject` = '$subject',
                                                                    `email`   = '$email',
                                                                    `seenFlag`= 'seen',
                                                                    `source`  = 'contactform',
                                                                    `from`    = '$replyto',
                                                                    `to`      = 'support@superviral.io',
                                                                    `emailDate` = '$dateAdded',
                                                                    `dateAdded` = '$dateAdded',
                                                                    `emailUid`  = '$emailUid',
                                                                    `emailSpam` = '$emailSpam'");



    if($insertEmailQueueQuery){
        $emailsuccess = '<div class="emailsuccess">Thank you for contacting our support team. A member of our team will get back to within 1 working day.</div>';
        sendCloudwatchData('Tikoid', 'Success', 'Contactus', 'contact-us-success-function', 1);
        sendCloudwatchData('Tikoid', 'support-contact-us', 'Contactus', 'support-contact-us-success-function', 1);

    }else{
        sendCloudwatchData('Tikoid', 'Failure', 'Contactus', 'contact-us-failure-function', 1);
    }  
}

///////////////////////



}








$tpl = file_get_contents('contact-us-2.html');

$tpl = str_replace('{emailsuccess}', $emailsuccess, $tpl);
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);

// End timer
$end_time = microtime(true);

// Calculate execution time in seconds
$execution_time_sec = $end_time - $start_time;

sendCloudwatchData('Tikoid', 'page-load-contactus', 'PageLoadTiming', 'page-load-contactus-function', number_format($execution_time_sec, 2));

echo $tpl;
?>