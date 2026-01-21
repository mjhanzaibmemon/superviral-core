<?php
set_time_limit ( 0 ); //0 = unlimited
error_reporting(E_ERROR | E_PARSE);
require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/Mail-master/vendor/autoload.php';
require_once __DIR__ . '/../sm-db.php';

require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';  
require_once('Encoding.php');

$s3 = new S3($amazons3key, $amazons3password);

use \ForceUTF8\Encoding;

// superviral code
    Eden::DECORATOR;

    $imap = eden('mail')->imap(
        'imap.gmail.com',
        $svemailPipingEmail,
        $svemailPipingToken,
        993,
        true);
    $mailboxes = $imap->getMailboxes();
    $imap->setActiveMailbox('INBOX')->getActiveMailbox();

    // $emails = $imap->getEmails(0, 10000);
    $date = date("d-M-Y", strtotime("-1 days")); //since last day
    // echo $date;
    $emails = $imap->search(array("SINCE $date"), 0, 100); // 1k limit per day 
    // $emails = $imap->search(array('FROM "anuj@etra.group"'), 0, 30);

    echo '<pre>';
    $emailsKeys = array_keys($emails);
    // print_r($emails);die;

    $count = count($emailsKeys);

    // echo $count;die;

    // $email = $imap->getUniqueEmails(166, true);

    // $MultipleEmails = $imap->getUniqueEmails(array(166, 165), true);
    // print_r($MultipleEmails);

    // $cleaner_input = strip_tags($email['body']['text/html']);

    // echo $email['body']['text/html'] ;

    // $email = $imap->getUniqueEmails(256, true);
    // echo $email = $email['body']['text/plain']; die;
    // print_r($email);die;



    /////////////////////////// Start inserting data to DB /////////////////////////////////////////////////



    for ($i = 0; $i < $count; $i++) {
    // echo $emailsKeys[$i];
    $keys = $emailsKeys[$i];
    // echo $keys;
    $uid = $emails[$keys]["uid"];
    $subject = addslashes($emails[$keys]["subject"]);
    $to = trim($emails[$keys]["to"][0]["email"]);
    $from = trim($emails[$keys]["from"]["email"]);
    $attachmentFLag = $emails[$keys]["attachment"];
    $seenFlag = $emails[$keys]["flags"][0];
    $cc = $emails[$keys]["cc"][0]["email"];
    $bcc = $emails[$keys]["bcc"][0]["email"];
    $emailDate = $emails[$keys]["date"];
    $dateAdded = time();
    $filePath = "";
    $emailBody = $imap->getUniqueEmails($uid, true);
    $email = $emailBody['body']['text/plain'];

   // echo $from.' - '.$to.'<hr>';

    if(empty($email))$email = $emailBody['body']['text/html'];
    // $emailHtml = $emailBody['body']['text/html'];
    $email = addslashes($email);
    $email = Encoding::toUTF8($email);
    // $email = str_replace('"','\"',$email);
    $existQuery = mysql_query("select count(1) as cnt from email_queue where emailUid=$uid and `source` = 'gmail' AND `brand` = 'sv'");
    $resArr = mysql_fetch_array($existQuery);
    $existCount = $resArr['cnt'];

    if(($to=='no-reply@superviral.io')&&($from!=='complaints@email-abuse.amazonses.com'))$markdoneq = " `markDone` = '1', ";

    // check email spam
    $emailSpam = '1';

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `users` WHERE `emailaddress` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `email` FROM `accounts` WHERE `email` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }

    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `order_session` WHERE `emailaddress` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }


    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `orders` WHERE `emailaddress` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }


    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `to` FROM `email_support_replies` WHERE `to` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }

    if ($existCount == 0) {      
        
/*
        echo $uid.'<br>';
        echo $subject.'<br>';
        echo $from.'<br>';
        echo $to.'<br>';

*/


        $res = mysql_query("INSERT INTO `email_queue`
                                                            SET
                                                            `emailUid`          = $uid,
                                                            `subject`           = '$subject',
                                                            `seenFlag`          = '$seenFlag',
                                                            `email`             = '$email',
                                                            `from`              = '$from',
                                                            `to`                = '$to',
                                                            `cc`                = '$cc',
                                                            `bcc`               = '$bcc',
                                                            `source`            = 'gmail',
                                                            `attachmentFlag`    = '$attachmentFLag',
                                                            `emailSpam`         = '$emailSpam',
                                                            `emailDate`         = '$emailDate',
                                                            `brand`             = 'sv',
                                                            $markdoneq
                                                            `dateAdded`         = '$dateAdded'"
        );

        sendCloudwatchData('EtraGroupCrons', 'support-crons', 'CronSyncEmails', 'support-crons-email-success-function', 1);

        if($res)echo 'Inserted!<br>';

        $lastId = mysql_insert_id();

        if($attachmentFLag){

            foreach ($emailBody['attachment'] as $attachment){
    
                $attachmentType = array_key_first($attachment); 
                $attachmentData = $attachment[$attachmentType]; 
        
                switch($attachmentType){
                    case 'application/msword':
                        $ext = 'doc';
                    break;
                    case 'application/text':
                        $ext = 'txt';
                    break;
                    case 'image/jpeg':
                    case 'image/jpg':
                        $ext = 'jpg';
                    break;
                    case 'image/png':
                        $ext = 'png';
                    break;
                    default:
                        $ext = 'jpg';
                    break;
        
                }
                $fileName = md5(time(). $uid) ;
                
                $putobject = S3::putObject($attachmentData, 'cdn.superviral.io', 'media/'. $fileName.'.'. $ext , S3::ACL_PUBLIC_READ);
                if($putobject){
        			sendCloudwatchData('EtraGroupCrons', 's3-image-upload-success', 'CronSyncEmails', 's3-image-upload-success-function', 1);

                }else{
        			sendCloudwatchData('EtraGroupCrons', 's3-image-upload-failure', 'CronSyncEmails', 's3-image-upload-failure-function', 1);

                }
                $filePath = 'https://cdn.superviral.io/media/'. $fileName.'.'. $ext;
    
                $res = mysql_query("INSERT INTO `email_queue_attachments`
                                                                        SET
                                                                        `email_queue_id`    = $lastId,
                                                                        `attachmentFilePath`= '$filePath', `brand` = 'sv'" );
    
            }
         
    
        }
    }
    
    unset($markdoneq);

    }
    echo 'SV done';

    $imap->disconnect();

// end superviral code















// tikoid code
    Eden::DECORATOR;

    $imap = eden('mail')->imap(
        'imap.gmail.com',
        $toemailPipingEmail,
        $toemailPipingToken,
        993,
        true);
    $mailboxes = $imap->getMailboxes();
    $imap->setActiveMailbox('INBOX')->getActiveMailbox();

    // $emails = $imap->getEmails(0, 10000);
    $date = date("d-M-Y", strtotime("-1 days")); //since last day
    // echo $date;
    $emails = $imap->search(array("SINCE $date"), 0, 100); // 1k limit per day 
    // $emails = $imap->search(array('FROM "anuj@etra.group"'), 0, 30);

    echo '<pre>';
    $emailsKeys = array_keys($emails);
    // print_r($emails);die;

    $count = count($emailsKeys);

    // echo $count;die;

    // $email = $imap->getUniqueEmails(166, true);

    // $MultipleEmails = $imap->getUniqueEmails(array(166, 165), true);
    // print_r($MultipleEmails);

    // $cleaner_input = strip_tags($email['body']['text/html']);

    // echo $email['body']['text/html'] ;

    // $email = $imap->getUniqueEmails(256, true);
    // echo $email = $email['body']['text/plain']; die;
    // print_r($email);die;



    /////////////////////////// Start inserting data to DB /////////////////////////////////////////////////



    for ($i = 0; $i < $count; $i++) {
    // echo $emailsKeys[$i];
    $keys = $emailsKeys[$i];
    // echo $keys;
    $uid = $emails[$keys]["uid"];
    $subject = addslashes($emails[$keys]["subject"]);
    $to = trim($emails[$keys]["to"][0]["email"]);
    $from = trim($emails[$keys]["from"]["email"]);
    $attachmentFLag = $emails[$keys]["attachment"];
    $seenFlag = $emails[$keys]["flags"][0];
    $cc = $emails[$keys]["cc"][0]["email"];
    $bcc = $emails[$keys]["bcc"][0]["email"];
    $emailDate = $emails[$keys]["date"];
    $dateAdded = time();
    $filePath = "";
    $emailBody = $imap->getUniqueEmails($uid, true);
    $email = $emailBody['body']['text/plain'];
    if(empty($email))$email = $emailBody['body']['text/html'];
    // $emailHtml = $emailBody['body']['text/html'];
    $email = addslashes($email);
    $email = Encoding::toUTF8($email);
    // $email = str_replace('"','\"',$email);
    $existQuery = mysql_query("select count(1) as cnt from email_queue where emailUid=$uid and `source` = 'gmail' AND `brand` = 'to'");
    $resArr = mysql_fetch_array($existQuery);
    $existCount = $resArr['cnt'];

    if(($to=='no-reply@superviral.io')&&($from!=='complaints@email-abuse.amazonses.com'))$markdoneq = " `markDone` = '1', ";

    // check email spam
    $emailSpam = '1';

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `users` WHERE `emailaddress` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `email` FROM `accounts` WHERE `email` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }

    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `order_session` WHERE `emailaddress` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }


    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `orders` WHERE `emailaddress` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }


    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `to` FROM `email_support_replies` WHERE `to` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }

    if ($existCount == 0) {      

        $res = mysql_query("INSERT INTO `email_queue`
                                                            SET
                                                            `emailUid`          = $uid,
                                                            `subject`           = '$subject',
                                                            `seenFlag`          = '$seenFlag',
                                                            `email`             = '$email',
                                                            `from`              = '$from',
                                                            `to`                = '$to',
                                                            `cc`                = '$cc',
                                                            `bcc`               = '$bcc',
                                                            `source`            = 'gmail',
                                                            `attachmentFlag`    = '$attachmentFLag',
                                                            `emailSpam`         = '$emailSpam',
                                                            `emailDate`         = '$emailDate',
                                                            `brand`             = 'to',
                                                            $markdoneq
                                                            `dateAdded`         = '$dateAdded'"
        );

        $lastId = mysql_insert_id();

        sendCloudwatchData('EtraGroupCrons', 'support-crons', 'CronSyncEmails', 'support-crons-email-success-function', 1);

        if($attachmentFLag){

            foreach ($emailBody['attachment'] as $attachment){

                $attachmentType = array_key_first($attachment); 
                $attachmentData = $attachment[$attachmentType]; 
            
                switch($attachmentType){
                    case 'application/msword':
                        $ext = 'doc';
                    break;
                    case 'application/text':
                        $ext = 'txt';
                    break;
                    case 'image/jpeg':
                    case 'image/jpg':
                        $ext = 'jpg';
                    break;
                    case 'image/png':
                        $ext = 'png';
                    break;
                    default:
                        $ext = 'jpg';
                    break;
                    
                }
                $fileName = md5(time(). $uid) ;

                $putobject = S3::putObject($attachmentData, 'cdn.superviral.io', 'media/'. $fileName.'.'. $ext , S3::ACL_PUBLIC_READ);
                if($putobject){
        			sendCloudwatchData('EtraGroupCrons', 's3-image-upload-success', 'CronSyncEmails', 's3-image-upload-success-function', 1);

                }else{
        			sendCloudwatchData('EtraGroupCrons', 's3-image-upload-failure', 'CronSyncEmails', 's3-image-upload-failure-function', 1);

                }
                $filePath = 'https://cdn.superviral.io/media/'. $fileName.'.'. $ext;

                $res = mysql_query("INSERT INTO `email_queue_attachments`
                                                                        SET
                                                                        `email_queue_id`    = $lastId,
                                                                        `attachmentFilePath`= '$filePath', `brand`= 'to'" );

            }
        

        }
    }

    unset($markdoneq);

    }
    echo 'TO done';

    $imap->disconnect();

// end tikoid code

// feedbuzz code

  /*  Eden::DECORATOR;

    $imap = eden('mail')->imap(
        'imap.gmail.com',
        $emailPipingEmail,
        $emailPipingToken,
        993,
        true);
    $mailboxes = $imap->getMailboxes();
    $imap->setActiveMailbox('INBOX')->getActiveMailbox();

    // $emails = $imap->getEmails(0, 10000);
    $date = date("d-M-Y", strtotime("-1 days")); //since last day
    // echo $date;
    $emails = $imap->search(array("SINCE $date"), 0, 100); // 1k limit per day 
    // $emails = $imap->search(array('FROM "anuj@etra.group"'), 0, 30);

    echo '<pre>';
    $emailsKeys = array_keys($emails);
    // print_r($emails);die;

    $count = count($emailsKeys);

    // echo $count;die;

    // $email = $imap->getUniqueEmails(166, true);

    // $MultipleEmails = $imap->getUniqueEmails(array(166, 165), true);
    // print_r($MultipleEmails);

    // $cleaner_input = strip_tags($email['body']['text/html']);

    // echo $email['body']['text/html'] ;

    // $email = $imap->getUniqueEmails(256, true);
    // echo $email = $email['body']['text/plain']; die;
    // print_r($email);die;



    /////////////////////////// Start inserting data to DB /////////////////////////////////////////////////



    for ($i = 0; $i < $count; $i++) {
    // echo $emailsKeys[$i];
    $keys = $emailsKeys[$i];
    // echo $keys;
    $uid = $emails[$keys]["uid"];
    $subject = addslashes($emails[$keys]["subject"]);
    $to = trim($emails[$keys]["to"][0]["email"]);
    $from = trim($emails[$keys]["from"]["email"]);
    $attachmentFLag = $emails[$keys]["attachment"];
    $seenFlag = $emails[$keys]["flags"][0];
    $cc = $emails[$keys]["cc"][0]["email"];
    $bcc = $emails[$keys]["bcc"][0]["email"];
    $emailDate = $emails[$keys]["date"];
    $dateAdded = time();
    $filePath = "";
    $emailBody = $imap->getUniqueEmails($uid, true);
    $email = $emailBody['body']['text/plain'];
    if(empty($email))$email = $emailBody['body']['text/html'];
    // $emailHtml = $emailBody['body']['text/html'];
    $email = addslashes($email);
    // $email = str_replace('"','\"',$email);
    $existQuery = mysql_query("select count(1) as cnt from email_queue where emailUid=$uid and `source` = 'gmail'");
    $resArr = mysql_fetch_array($existQuery);
    $existCount = $resArr['cnt'];

    if(($to=='no-reply@superviral.io')&&($from!=='complaints@email-abuse.amazonses.com'))$markdoneq = " `markDone` = '1', ";

    // check email spam
    $emailSpam = '1';

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `users` WHERE `emailaddress` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `email` FROM `accounts` WHERE `email` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }

    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `order_session` WHERE `emailaddress` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }


    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `emailaddress` FROM `orders` WHERE `emailaddress` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }


    if($emailSpam==1){

        $emailSpamQuery = mysql_query("SELECT `to` FROM `email_support_replies` WHERE `to` LIKE '%$from%' LIMIT 1");
        if(mysql_num_rows($emailSpamQuery)=='1')$emailSpam='0';

    }

    if ($existCount == 0) {      

        $res = mysql_query("INSERT INTO `email_queue`
                                                            SET
                                                            `emailUid`          = $uid,
                                                            `subject`           = '$subject',
                                                            `seenFlag`          = '$seenFlag',
                                                            `email`             = '$email',
                                                            `from`              = '$from',
                                                            `to`                = '$to',
                                                            `cc`                = '$cc',
                                                            `bcc`               = '$bcc',
                                                            `source`            = 'gmail',
                                                            `attachmentFlag`    = '$attachmentFLag',
                                                            `emailSpam`         = '$emailSpam',
                                                            `emailDate`         = '$emailDate',
                                                            `brand`             = 'fb',
                                                            $markdoneq
                                                            `dateAdded`         = '$dateAdded'"
        );

        $lastId = mysql_insert_id();


        if($attachmentFLag){

            foreach ($emailBody['attachment'] as $attachment){

                $attachmentType = array_key_first($attachment); 
                $attachmentData = $attachment[$attachmentType]; 
            
                switch($attachmentType){
                    case 'application/msword':
                        $ext = 'doc';
                    break;
                    case 'application/text':
                        $ext = 'txt';
                    break;
                    case 'image/jpeg':
                    case 'image/jpg':
                        $ext = 'jpg';
                    break;
                    case 'image/png':
                        $ext = 'png';
                    break;
                    default:
                        $ext = 'jpg';
                    break;
                    
                }
                $fileName = md5(time(). $uid) ;

                $putobject = S3::putObject($attachmentData, 'cdn.superviral.io', 'media/'. $fileName.'.'. $ext , S3::ACL_PUBLIC_READ);
                $filePath = 'https://cdn.superviral.io/media/'. $fileName.'.'. $ext;

                $res = mysql_query("INSERT INTO `email_queue_attachments`
                                                                        SET
                                                                        `email_queue_id`    = $lastId,
                                                                        `attachmentFilePath`= '$filePath', `brand` = 'fb'" );

            }
        

        }
    }

    unset($markdoneq);

    }
    echo 'done';

    $imap->disconnect();

// end feedbuzz code*/