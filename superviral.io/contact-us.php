<?php

// start time
$start_time = microtime(true);

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=0;
include('header.php');


// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/Exception.php';
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/PHPMailer.php';
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/src/SMTP.php';






if(!empty($_POST['submit'])) {

$failed=0;

//SECRET KEY
$fetchsecretq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND `country` = '{$locas[$loc]['sdb']}' AND `page` = 'contact-us' AND `name` = 'gcapse' LIMIT 1");
$fetchsecretkey = mysql_fetch_array($fetchsecretq);

$secret = $recaptchasecret;

$data = array('secret' => $secret,'response' => $_POST['g-recaptcha-response']);

$verify = curl_init();
curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
curl_setopt($verify, CURLOPT_POST, true);
curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($verify);

$response = json_decode($response, true);
  
    if($response["success"] === true){
        // actions if successful


    }else{
      $emailsuccess = '<div class="emailsuccess emailfailed">{errorrobot}</div>';
      $failed=1;
      sendCloudwatchData('Superviral', 'Failure', 'Contactus', 'contact-us-failure-function', 1);
    }



////////////////////// EMAILER

if($failed==0){
       
    $first_name = addslashes($_POST['name']); // required
    $orderid    = addslashes($_POST['orderid']); // required
    $replyto    = trim(addslashes($_POST['emailaddress'])); // required
    $subject    = addslashes($_POST['subject']); // required
    $email  = strip_tags(addslashes($_POST['message']));
    // $email = str_replace("'","\'",$emailhtml);
    // $email = str_replace('"','\"',$email); 
    $dateAdded = time();

    $fetchMaxUidContactForm = mysql_query("SELECT max(`emailUid`) as maxId FROM `email_queue` WHERE `brand`='sv' AND `source` = 'contactform'");
    $fetchMaxUid = mysql_fetch_array($fetchMaxUidContactForm);
    $maxUid = $fetchMaxUid["maxId"];

    if($maxUid == "" || $maxUid == null){
        $emailUid = 1;
    }else{
        $emailUid = intval($maxUid) + 1;
    }

////////////////////////EMAIL SPAM


$emailSpam = '0';

/*        // check email spam
    

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `users` WHERE `emailaddress` LIKE '%$replyto%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `email` FROM `accounts` WHERE `email` LIKE '%$replyto%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }

    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `order_session` WHERE `emailaddress` LIKE '%$replyto%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }


    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `orders` WHERE `emailaddress` LIKE '%$replyto%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }


    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `to` FROM `email_support_replies` WHERE `to` LIKE '%$replyto%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }*/



    $insertEmailQueueQuery = mysql_query("INSERT INTO  `email_queue` 
                                                                SET `brand` = 'sv',
                                                                    `subject` = '$subject',
                                                                    `email`   = '$email',
                                                                    `seenFlag`= 'seen',
                                                                    `source`  = 'contactform',
                                                                    `from`    = '$replyto',
                                                                    `to`      = 'support@superviral.io',
                                                                    `emailDate` = '$dateAdded',
                                                                    `dateAdded` = '$dateAdded',
                                                                    `emailUid`  = '$emailUid',
                                                                    `emailSpam` = '$emailSpam'

                                        ");

   if($insertEmailQueueQuery){
        sendCloudwatchData('Superviral', 'Success', 'Contactus', 'contact-us-success-function', 1);
        sendCloudwatchData('Superviral', 'support-contact-us', 'Contactus', 'support-contact-us-success-function', 1);
   }else{
        sendCloudwatchData('Superviral', 'Failure', 'Contactus', 'contact-us-failure-function', 1);
   }                                     


// $sender = 'no-reply@superviral.io';
// $senderName = 'Superviral';
// $usernameSmtp = $sesusername;
// $passwordSmtp = $sespassword;
// $host = 'email-smtp.us-east-1.amazonaws.com';
// $port = 587;


//     $mail = new PHPMailer(true);




//     function clean_string($string) {
//       $bad = array("content-type","bcc:","to:","cc:","href");
//       return str_replace($bad,"",$string);
//     }
     
//     $first_name = $_POST['name']; // required
//     $orderid = $_POST['orderid']; // required
//     $replyto = $_POST['emailaddress']; // required
//     $subject = $_POST['subject']; // required
//     $emailhtml = $_POST['message']; // required
//     $recipient = 'customer-care@superviral.io'; // required
//     $sender = 'no-reply@superviral.io';
//     $senderName = 'Superviral';

//     $email_message .= "Full Name: ".clean_string($first_name)."<br>";
//     $email_message .= "Order ID: ".clean_string($orderid)."<br>";
//     $email_message .= "Email address: ".clean_string($replyto)."<br>";
//     $email_message .= "".clean_string($emailhtml)."<br>";

//     $bodyHtml =  $email_message;
//     $bodyText =  strip_tags($email_message);

//     try {
//         // Specify the SMTP settings.
//         $mail->isSMTP();
//         $mail->setFrom($sender, $senderName);
//         $mail->Username   = $usernameSmtp;
//         $mail->Password   = $passwordSmtp;
//         $mail->Host       = $host;
//         $mail->Port       = $port;
//         $mail->SMTPAuth   = true;
//         $mail->SMTPSecure = 'tls';
//         $mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configurationSet);
//         $mail->CharSet = 'UTF-8';
//         $mail->addReplyTo($replyto, $first_name);

//         // Specify the message recipients.
//         $mail->addAddress($recipient);
//         // You can also add CC, BCC, and additional To recipients here.

//         // Specify the content of the message.
//         $mail->isHTML(true);
//         $mail->Subject    = $subject;
//         $mail->Body       = $bodyHtml;
//         $mail->AltBody    = $bodyText;
//         $mail->Send();
//      //   echo "Email sent!" , PHP_EOL;
        $didemailsend = 'Email Sent!';
        $emailsuccess = '<div class="emailsuccess">{successmsg}</div>';
//     } catch (phpmailerException $e) {
//         //echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
//           $emailsuccess = '<div class="emailsuccess emailfailed">{errormsg}</div>';
//         $didemailsend = 'Email NOT Sent!';
//     } catch (Exception $e) {
//         //echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
//         $emailsuccess = '<div class="emailsuccess emailfailed">{errormsg}</div>';
//         $didemailsend = 'Email NOT Sent!';
//     }

}

///////////////////////



}


$tpl = file_get_contents('contact-us-2.html');

$query = mysql_query("SELECT * FROM notice_msg WHERE brand = 'sv' LIMIT 1");
$data = mysql_fetch_array($query);

if(!empty($data['message'])){

    $noticemsg = ' <div style="    padding: 5px;
    background-color: white;
    border: 1px solid orange;
    font-size: 15px;
    padding: 20px 10px;
    margin-top: 55px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;">'. $data['message'] .'</div>';
}else{
    $noticemsg = '';
}


/*$noticemsg = ' <div style="    padding: 5px;
    background-color: white;
    border: 1px solid orange;
    font-size: 15px;
    padding: 20px 10px;
    margin-top: 55px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;"><b>Auto likes issues from 5th of this month:</b> Customers who have made an order on the 29thth March 2021 may have not received an order confirmation email or had their order fulfilled.<br><br>This is due to us upgrading our systems so that you can have a greater experience at Superviral.<br><br>We can confirm the issue has been resolved and we\'ve sent another email to confirm your order. Also we\'ve begun fulfilling your order. We do apologise in advanced for any inconvenience this may have caused you.</div>';
*/
/*$noticemsg = ' <div style="    padding: 5px;
    background-color: white;
    border: 1px solid orange;
    font-size: 15px;
    padding: 20px 10px;
    margin-top: 55px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;"><b>Auto likes issues from 5th of this month:</b> customers who have automatic likes may experience fulfillment issues due to a technical issue which is affecting a small number of customers.
<br><br>
If you are affected, we highly encourage you to use the Manual Likes tool, in your Superviral account, to issue the likes manually.
<br><br>
The automatic likes issue is due an upgrade being made on our system so that you can have a greater experience with Superviral Automatic Likes. We\'re working at full speed to rectify this issue.
<br><br>
We do apologise in advanced for any inconvenience this may have caused you.</div>';

*/

$tpl = str_replace('{noticemsg}', $noticemsg, $tpl);
$tpl = str_replace('{emailsuccess}', $emailsuccess, $tpl);
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'contact-us') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

// End timer
$end_time = microtime(true);

// Calculate execution time in seconds
$execution_time_sec = $end_time - $start_time;

sendCloudwatchData('Superviral', 'page-load-contactus', 'PageLoadTiming', 'page-load-contactus-function', number_format($execution_time_sec, 2));

echo $tpl;

?>