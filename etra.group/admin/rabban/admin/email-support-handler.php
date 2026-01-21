<?php



// error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . '/../db.php';


// $supportSubject = "Superviral-Support Response";

header('Cache-Control: no-transform'); 


$type = addslashes($_POST['type']);



switch ($type) {

    case "spamEmails":
        getSpamEmails();
        break;

    case "customerEmails":

        getCustomerEmails();

        break;

    case "replyOnEmail":

        replyOnEmail($supportEmail);

        break;

    case "markDone":

        markDone();

        break;

    case "blockConversation":

        blockConversation();

        break;

    case "searchOperations":

        searchOperations();

        break;

    case "submitReportOnEmail":

        submitReportOnEmail();

        break;

    case "submitCustomerNotes":

        submitCustomerNotes();

        break;

    case "getCustomerNotes":

        getCustomerNotes();

        break;

    case "getCustomerInfo":

        getCustomerInfo();

        break;

    case "showMoreEmail":

        showMoreEmail();

        break;   

    case "sendEmail":

        sendEmail();
        
        break;

        case "attachments":

            getAttachments();
    
            break;     
}


function getSpamEmails(){
    $Query = mysql_query("SELECT
                                 `from` AS emailId
                                 FROM email_queue
                                     WHERE markDone = '0'
                                 AND `block` = '0'
                                 AND `emailSpam` = '1'
                                 AND `submitReport` = '0'
                                 AND emailDate >= unix_timestamp(CURRENT_DATE - interval 1 month )
                                 GROUP BY `from`
                                 ORDER BY id DESC
                        ");



    while ($resArr = mysql_fetch_array($Query)) {

        $dataArr[] = $resArr;

    }
    echo json_encode($dataArr);

    die;

}


function getCustomerEmails()

{

    $email = addslashes($_POST['email']);



    $Query = mysql_query("SELECT

                                `emailUid`,

                                `email`,

                                `subject`,

                                `emailDate`

                                FROM email_queue

                                    WHERE markDone = '0'

                                AND `block` = '0'

                             --   AND `submitReport` = '0'

                                AND `from` = '$email'

                                AND emailDate >= unix_timestamp(CURRENT_DATE - interval 2 month );

                        ");



    while ($resArr = mysql_fetch_array($Query)) {

        $dataArr["dataArr"][] = $resArr;

    }



    $Query = mysql_query("SELECT COUNT(1) AS cnt FROM accounts WHERE email = '$email'");

    $CountArr = mysql_fetch_array($Query);

    $IsAccountExist = $CountArr['cnt'];



    $Query = mysql_query("SELECT COUNT(1) AS cnt FROM automatic_likes WHERE emailaddress = '$email'");

    $CountArr = mysql_fetch_array($Query);

    $IsAutoLikeExist = $CountArr['cnt'];



    $checkArr = array('checkAccountExist' => $IsAccountExist, 'checkAutoLikeExist' => $IsAutoLikeExist);

    $dataArr = array_merge($dataArr, $checkArr);



    $Query = mysql_query("SELECT CONCAT('#','',id,' - ',amount,' ',packagetype) AS `order`, `added` FROM orders WHERE emailaddress = '$email'");

    while ($orderArr = mysql_fetch_array($Query)) {

        $dataArr["orders"][] = $orderArr;

    }



    echo json_encode($dataArr);

    die;



}



function replyOnEmail($supportEmail)
{
    $emailUid = addslashes($_POST['emailUid']);
    $reply = addslashes($_POST['reply']);
    // $reply = str_replace("'", "''", $reply);
    // $reply = str_replace('"', '""', $reply);
    
    $emailId = addslashes($_POST['emailId']);
    $supportSubject = 'RE: '.addslashes($_POST['subject']). "";
    $dateAdded = time();
    
    $Query = mysql_query("INSERT INTO email_support_replies
                            SET `from` = '$supportEmail',
                            `to` = '$emailId',
                            `reply` = '$reply',
                            emailUid = '$emailUid',
                            dateAdded = '$dateAdded'
                        ");

    $jhsignature = '
<br>
Head of Customer Care<br>
<a href="https://superviral.io/">Superviral.io</i></a>
<br><br>
<img style="width:275px;height:54px" width="275" height="54" src="https://superviral.io/imgs/jharrissig.png">
<br><br>
<i>E: James-harris@superviral.co.uk</i><br>
<i>T: +44 203 856 3786</i><br>
<i>A: 160 City Road, London, EC1V 2NX, United Kingdom</i>';

    if ($Query) {
        // send email
        include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';
        $reply = str_replace('\\', '', $reply);
        emailnow($emailId, 'James Harris', $supportEmail, $supportSubject, nl2br($reply).$jhsignature);
        $response = array('Message' => 'Reply Sent Successfully');
        echo json_encode($response);
        die;
    } else {
        $response = array('Message' => 'Failed, Please try again');
        echo json_encode($response);
        die;
    }

}







function markDone()

{

    $emailId = addslashes($_POST['emailId']);



    $Query = mysql_query("UPDATE email_queue SET `markDOne` = '1' WHERE `from` = '$emailId'");



    if ($Query) {

        $response = array('Message' => 'Mark Done Successfully');

        echo json_encode($response);

        die;

    } else {

        $response = array('Message' => 'Failed, Please try again');

        echo json_encode($response);

        die;

    }



}



function blockConversation()

{

    $emailId = addslashes($_POST['emailId']);
    $value = addslashes($_POST['value']);
    $now = time();


    if (strpos( $emailId , 'mailer-daemon@googlemail.com') !== false) {

    $response = array('Message' => 'Unfortunately, you cannot block this email - Mark as done instead.');

    echo json_encode($response);

    die;
    }

    if (strpos( $emailId , 'email-abuse') !== false) {

    $response = array('Message' => 'Unfortunately, you cannot block this email - Mark as done instead.');

    echo json_encode($response);

    die;
    }


    if (strpos( $emailId , '@superviral.io') !== false) {

    $response = array('Message' => 'Unfortunately, you cannot block this email - Mark as done instead.');

    echo json_encode($response);

    die;
    }



    $Query = mysql_query("UPDATE email_queue SET `block` = '$value' WHERE `from` = '$emailId'");

    


    if ($Query) {

        if($value == 1){
            $Query = mysql_query("INSERT INTO blocked_conversations SET `email` = '$emailId', `added` = '$now'");
            $response = array('Message' => 'Blocked Conversation Successfully');
        }else{
            $Query = mysql_query("DELETE FROM blocked_conversations WHERE `email` = '$emailId'");
            $response = array('Message' => 'Unblocked Conversation Successfully');
        }


        echo json_encode($response);

        die;

    } else {

        $response = array('Message' => 'Failed, Please try again');

        echo json_encode($response);

        die;

    }



}



function searchOperations()

{



    $key = addslashes($_POST['key']);

    $emailId = addslashes($_POST['emailId']);

    $searchType = addslashes($_POST['searchType']);

    $Query = "";

    switch ($searchType) {

        case "order":

            $Query = mysql_query("SELECT CONCAT('#','',id,' - ',amount,' ',packagetype) AS `record`, `added` FROM orders WHERE id LIKE '%$key%' OR emailaddress LIKE '%$key%' ");

            break;

        case "user":

            $Query = mysql_query("SELECT CONCAT('#','',email) AS `record`, `added` FROM accounts WHERE `email` LIKE '%$key%' ");

            break;

        case "email":



            $Query = mysql_query("SELECT

                                         `emailUid`,

                                         `email`,

                                         `subject`,

                                         `emailDate`

                                        FROM email_queue

                                            WHERE ( `subject` LIKE '%$key%' 

                                            OR `email` LIKE '%$key%' )

                                        AND `from` = '$emailId'    

                                        AND dateAdded >= unix_timestamp(CURRENT_DATE - interval 1 month )

            ");

            break;

    }

    while ($resArr = mysql_fetch_array($Query)) {

        $dataArr[] = $resArr;

    }



    echo json_encode($dataArr);

    die;

}



function submitReportOnEmail()

{

    $orderId = addslashes($_POST['orderId']);

    $report = addslashes($_POST['report']);

    // $report = str_replace("'", "\'", $report);

    // $report = str_replace('"', '\"', $report);

    $emailId = addslashes($_POST['emailId']);

    $emailUid = addslashes($_POST['emailUid']);

    $supportSubject = addslashes($_POST['emailsubject']);

    $dateAdded = time();

    $difficulty = getDifficulty($report);

    $adminName  = $_SESSION['admin_user'];

    $Query = mysql_query("INSERT INTO admin_notifications

                            SET `emailaddress` = '$emailId',

                                `orderid` = '$orderId',

                                `message` = '$report',

                                `type` = 'emailSupport',

                                `emailUid` = '$emailUid',

                                added = '$dateAdded',

                                directions = '',

                                admin_name ='$adminName',

                                `difficulty` = $difficulty

                        ");

    if ($Query) {

        $Query = mysql_query("UPDATE email_queue SET `submitReport` = '1' WHERE `from` = '$emailId'");

    }



    if ($Query) {

        $response = array('Message' => 'Report Submitted Successfully');


        ////////////////////////


        // send email
        include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';


        $reply = "Hi there,

Thank you very much for contacting Superviral Customer Care. I've looked into your query and can see that this requires a closer look with my management team. This happens usually when a query requires the assistance of other Superviral departments.

For this reason, I've forwarded your query to our management team so that we can resolve this issue as soon as possible. This usually results in more than one of our team looking at the issue, as you know, at Superviral the customer always comes first.

I'll get back to you within 24-36 hours once I've resolved the issue for you. I understand this is an important issue so hopefully, I can get this resolved even quicker for you.

Kind Regards,
James Harris.";
    
       // $reply = str_replace('\\', '', $reply);
        emailnow($emailId, 'James Harris', 'customer-care@superviral.io', 'RE: '.$supportSubject, nl2br($reply));




/*
        $response = array('Message' => '1. '.$emailId.'2. '.$supportEmail.'3. '.$supportSubject.'4. '.$reply);

              echo json_encode($response);

        die;*/


    $emailUid = addslashes($_POST['emailUid']);
     $reply = str_replace("'", "''", $reply);
     $reply = str_replace('"', '""', $reply);
    
    $emailId = addslashes($_POST['emailId']);
    $dateAdded = time();
    
    $Query = mysql_query("INSERT INTO email_support_replies
                            SET `from` = '$supportEmail',
                            `to` = '$emailId',
                            `reply` = '$reply',
                            emailUid = '$emailUid',
                            dateAdded = '$dateAdded'
                        ");


        echo json_encode($response);

        die;

    } else {

        $response = array('Message' => 'Failed, Please try again');

        echo json_encode($response);

        die;

    }



}



function submitCustomerNotes()

{



    $notes = addslashes($_POST['notes']);

    // $notes = str_replace("'", "\'", $notes);

    // $notes = str_replace('"', '\"', $notes);

    $emailId = addslashes($_POST['emailId']);

    $dateAdded = time();



    $Query = mysql_query("INSERT INTO email_customer_notes

                            SET `emailId` = '$emailId',

                                `notes` = '$notes',

                                dateAdded = '$dateAdded'

                        ");



    if ($Query) {

        $response = array('Message' => 'Notes Added Successfully');

        echo json_encode($response);

        die;

    } else {

        $response = array('Message' => 'Failed, Please try again');

        echo json_encode($response);

        die;

    }

}



function getCustomerNotes()

{

    //

    $emailId = addslashes($_POST['emailId']);



    $Query = mysql_query("SELECT

                               *

                                FROM email_customer_notes

                                WHERE emailId = '$emailId'

                                AND dateAdded >= unix_timestamp(CURRENT_DATE - interval 1 month )

    ");



    while ($resArr = mysql_fetch_array($Query)) {

        $dataArr[] = $resArr;

    }

    echo json_encode($dataArr);

    die;

}







function getCustomerInfo(){



    $emailId = addslashes($_POST['emailId']);



    $Query = mysql_query("SELECT `orders`, unsubscribe FROM users WHERE emailaddress ='$emailId';");

    $res1Arr = mysql_fetch_array($Query);

    if($res1Arr == null) $res1Arr = [];



    $Query = mysql_query("SELECT added, 

                                 freeautolikes,

                                 freeautolikesnumber, 

                                 lastlogin, 

                                 passwupdated, 

                                 username, 

                                 resetpwtime 

                                 FROM accounts 

                                 WHERE email ='$emailId';

    ");



    $res2Arr = mysql_fetch_array($Query);

    if($res2Arr == null) $res2Arr = [];



    $resArr = array_merge($res1Arr, $res2Arr);



    echo json_encode($resArr);

    die;

}



function showMoreEmail()

{

    $id = addslashes($_POST['id']);

    $val = addslashes($_POST['value']);

    $emailType = addslashes($_POST['emailType']);



    if($emailType == "admin"){

        $table = "admin_notifications";

    }

    if($emailType == "support"){

        $table = "email_support_replies";

    }

    if($emailType == ""){

        $table = "email_queue";

    }



    $Query = mysql_query("UPDATE $table SET `hideMessage` = '$val' WHERE `id` = '$id'");



    if ($Query) {

        $response = array('Message' => 'Successfully done');

        echo json_encode($response);

        die;

    } else {

        $response = array('Message' => 'Failed, Please try again');

        echo json_encode($response);

        die;

    }



}

function getDifficulty($report){

    /*
    
    ###################

    For score 1 (easy):

    Under 7-words in the report contents

    ###################

    For score 2 (medium):

    More than 7-words in the report contents

    ###################

    For score 3 (hard):

    keywords such as:

    blocked
    taken down
    suspend
    
    
    */


    $wordCount = str_word_count($report);


    if($wordCount <= 7 && preg_match('(blocked|taken down|suspend)', $report) === 0){ // for easy

        return 1; 
    }

    if($wordCount > 7 && preg_match('(blocked|taken down|suspend)', $report) === 0){ // for medium

        return 2;
    }

    if(preg_match('(blocked|taken down|suspend)', $report) === 1) { // for hard

        return 3;
    } 


}

function sendEmail()

{

    $emailTo = addslashes($_POST['emailTo']);

    $emailsubject = addslashes($_POST['emailsubject']);

    // $report = str_replace("'", "\'", $report);

    // $report = str_replace('"', '\"', $report);

    $emailMessage = addslashes($_POST['emailMessage']);

    // send email
    include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';

    // $reply = str_replace('\\', '', $reply);
    emailnow($emailTo, 'James Harris', 'customer-care@superviral.io', $emailsubject, nl2br($emailMessage));

    $existQuery = mysql_query("select min(`emailUid`) as cnt from email_queue where `source` = 'composeEmail'");
    $resArr = mysql_fetch_array($existQuery);
    $uid = intval($resArr['cnt']) - 1;
    
    $subject = $emailsubject;
    $to = 'customer-care@superviral.io';
    $from = $emailTo;
    $attachmentFLag = '';
    $seenFlag = 'seen';
    $cc = '';
    $bcc = '';
    $emailDate = time();
    $dateAdded = time();
    $email = $emailMessage;
    $emailSpam = '0';
   

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
                                                            `source`            = 'composeEmail',
                                                            `attachmentFlag`    = '$attachmentFLag',
                                                            `emailSpam`         = '$emailSpam',
                                                            `emailDate`         = '$emailDate',
                                                            `dateAdded`         = '$dateAdded'"
    );


    $response = array('Message' => 'Sent Successfully!');

    echo json_encode($response);

    die;


}

function getAttachments(){


    $emailId = addslashes($_POST['emailId']);



    $Query = mysql_query("SELECT
                               *
                                FROM email_queue_attachments
                                WHERE email_queue_id = '$emailId'
                        ");



    while ($resArr = mysql_fetch_array($Query)) {

        $dataArr[] = $resArr;

    }

    echo json_encode($dataArr);
    die;
}