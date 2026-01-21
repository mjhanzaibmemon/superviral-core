<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// 

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/aws-sdk/aws-autoloader.php';
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
// $supportSubject = "Superviral-Support Response";
global $s3Client;

$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => 'us-east-2', 
    'credentials' => [
        'key'    => $amazons3key,
        'secret' => $amazons3password,
    ]
]);
// $supportSubject = "Superviral-Support Response";



$type = addslashes($_POST['type']);



switch ($type) {

    case "spamEmails":
        getSpamEmails();
        break;

    case "customerEmails":

        getCustomerEmails();

        break;

    case "replyOnEmail":

        replyOnEmail();

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
    case "updateAccountId":
        
        updateAccountId();

        break;
    case "callCheckIsPrivate":
        
        callCheckIsPrivate();
        break;
    case "downloadChats":
        downloadChats();
        break;       

    case "searchLogs":
        searchLogs();
        break; 

    case "selectAction":
        selectAction();
        break;        

    case "submitReport":
        submitReport();
        break;    

    case "markDone2":
        markDone2();

        break;

    case "blockConversation2":

        blockConversation2();

        break;
        
    case "submitReportOnEmail2":

        submitReportOnEmail2();
    
        break;    
    case "offerFreeFollowers":
        offerFreeFollowers();
        break;    

    case "offerFreeLikes":
        offerFreeLikes();
        break;    
    
    case "updateALStatus":
        updateALStatus();
        break;

    case "ResendFollowers":
        ResendFollowers();
        break;

    case "ResendLikes":
        ResendLikes();
        break;    
}


function getSpamEmails(){
    global $brand;
    $Query = mysql_query("SELECT
                                 `from` AS emailId
                                 FROM email_queue
                                     WHERE markDone = '0'
                                 AND `block` = '0'
                                 AND `emailSpam` = '1'
                                 AND `submitReport` = '0'
                                 AND emailDate >= unix_timestamp(CURRENT_DATE - interval 1 month )
                                 AND brand = '$brand'
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
    global $brand;
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

                                AND brand = '$brand'

                                AND emailDate >= unix_timestamp(CURRENT_DATE - interval 2 month );

                        ");



    while ($resArr = mysql_fetch_array($Query)) {

        $dataArr["dataArr"][] = $resArr;

    }



    $Query = mysql_query("SELECT COUNT(1) AS cnt FROM accounts WHERE email = '$email'  AND brand = '$brand'");

    $CountArr = mysql_fetch_array($Query);

    $IsAccountExist = $CountArr['cnt'];



    $Query = mysql_query("SELECT COUNT(1) AS cnt FROM automatic_likes WHERE emailaddress = '$email'  AND brand = '$brand'");

    $CountArr = mysql_fetch_array($Query);

    $IsAutoLikeExist = $CountArr['cnt'];



    $checkArr = array('checkAccountExist' => $IsAccountExist, 'checkAutoLikeExist' => $IsAutoLikeExist);

    $dataArr = array_merge($dataArr, $checkArr);



    $Query = mysql_query("SELECT CONCAT('#','',id,' - ',amount,' ',packagetype) AS `order`, `added` FROM orders WHERE emailaddress = '$email'  AND brand = '$brand'");

    while ($orderArr = mysql_fetch_array($Query)) {

        $dataArr["orders"][] = $orderArr;

    }



    echo json_encode($dataArr);

    die;



}



function replyOnEmail()
{
    global $brand;

    switch($brand){
        case 'sv':
            $supportEmail = 'support@superviral.io';
            $comp = 'superviral.io';
        break;
        case 'to':
            $supportEmail = 'support@tikoid.com';
            $comp = 'tikoid.com';

           break; 
    }
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
                            dateAdded = '$dateAdded',
                            brand = '$brand'
                        ");

    if($brand == 'sv'){$domain = 'superviral.io';}
    if($brand == 'to'){$domain = 'tikoid.com';}


    $jhsignature = '
        <br>
        Head of Customer Care<br>
        <a href="https://'. $comp .'/">'. $comp .'</i></a>
        <br><br>
        <img style="width:275px;height:54px" width="275" height="54" src="https://superviral.io/imgs/jharrissig.png">
        <br><br>
        <i>E: James-harris@'.$domain.'</i><br>
        <i>T: +44 203 856 3786</i><br>
        <i>A: 160 City Road, London, EC1V 2NX, United Kingdom</i>
    ';

    if ($Query) {
        // send email
        require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';

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
    global $brand;
    $emailId = addslashes($_POST['emailId']);



    $Query = mysql_query("UPDATE email_queue SET `markDOne` = '1' WHERE `from` = '$emailId' and brand = '$brand'");



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

function markDone2()

{
    global $brand;
    $emailId = addslashes($_POST['emailId']);
        $Query = mysql_query("UPDATE email_queue SET `markDOne` = '1' WHERE `from` = '$emailId' and brand = '$brand'");




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
    global $brand;
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



    $Query = mysql_query("UPDATE email_queue SET `block` = '$value' WHERE `from` = '$emailId'  AND brand = '$brand'");

    


    if ($Query) {

        if($value == 1){
            $Query = mysql_query("INSERT INTO blocked_conversations SET `email` = '$emailId', `added` = '$now',  brand = '$brand'");
            $response = array('Message' => 'Blocked Conversation Successfully');
        }else{
            $Query = mysql_query("DELETE FROM blocked_conversations WHERE `email` = '$emailId'  AND brand = '$brand'");
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

function blockConversation2()

{
    global $brand;
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



    $Query = mysql_query("UPDATE email_queue SET `block` = '$value' WHERE `from` = '$emailId'  AND brand = '$brand'");

    


    if ($Query) {

        if($value == 1){
            $Query = mysql_query("INSERT INTO blocked_conversations SET `email` = '$emailId', `added` = '$now',  brand = '$brand'");
            $response = array('Message' => 'Blocked Conversation Successfully');
        }else{
            $Query = mysql_query("DELETE FROM blocked_conversations WHERE `email` = '$emailId'  AND brand = '$brand'");
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

global $brand;

    $key = addslashes($_POST['key']);

    $emailId = addslashes($_POST['emailId']);

    $searchType = addslashes($_POST['searchType']);

    $Query = "";

    switch ($searchType) {

        case "order":

            $Query = mysql_query("SELECT CONCAT('#','',id,' - ',amount,' ',packagetype) AS `record`, `added` FROM orders WHERE  brand = '$brand' AND id LIKE '%$key%' OR emailaddress LIKE '%$key%' ");

            break;

        case "user":

            $Query = mysql_query("SELECT CONCAT('#','',email) AS `record`, `added` FROM accounts WHERE `email` LIKE '%$key%'  AND brand = '$brand' ");

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
                                        AND brand = '$brand'
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
    global $brand;
    $orderId = addslashes($_POST['orderId']);

    $report = addslashes($_POST['report']);

    // $report = str_replace("'", "\'", $report);

    // $report = str_replace('"', '\"', $report);

    $emailId = addslashes($_POST['emailId']);

    $emailUid = addslashes($_POST['emailUid']);

    $supportSubject = addslashes($_POST['emailsubject']);

    $dateAdded = time();

    $difficulty = getDifficulty($report);
    $adminName  = $_SESSION['first_name'];
    
    $Query = mysql_query("INSERT INTO admin_notifications

                            SET `emailaddress` = '$emailId',

                                `orderid` = '$orderId',

                                `message` = '$report',

                                `type` = 'emailSupport',

                                `emailUid` = '$emailUid',

                                added = '$dateAdded',

                                directions = '',

                                admin_name ='$adminName',

                                `difficulty` = $difficulty,
                                 brand = '$brand'

                        ");

    if ($Query) {

        $Query = mysql_query("UPDATE email_queue SET `submitReport` = '1' WHERE `from` = '$emailId'");

    }



    if ($Query) {

        $response = array('Message' => 'Report Submitted Successfully');


        ////////////////////////


        // send email
        require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';


        $reply = "Hi there,

Thank you very much for contacting Superviral Customer Care. I've looked into your query and can see that this requires a closer look with my management team. This happens usually when a query requires the assistance of other Superviral departments.

For this reason, I've forwarded your query to our management team so that we can resolve this issue as soon as possible. This usually results in more than one of our team looking at the issue, as you know, at Superviral the customer always comes first.

I'll get back to you within 24-36 hours once I've resolved the issue for you. I understand this is an important issue so hopefully, I can get this resolved even quicker for you.

Kind Regards,
James Harris.";
    
       // $reply = str_replace('\\', '', $reply);
        // emailnow($emailId, 'James Harris', 'customer-care@superviral.io', 'RE: '.$supportSubject, nl2br($reply));




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
                            dateAdded = '$dateAdded',
                             brand = '$brand'
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


    global $brand;
    $notes = addslashes($_POST['notes']);

    // $notes = str_replace("'", "\'", $notes);

    // $notes = str_replace('"', '\"', $notes);

    $emailId = addslashes($_POST['emailId']);

    $dateAdded = time();



    $Query = mysql_query("INSERT INTO email_customer_notes

                            SET `emailId` = '$emailId',

                                `notes` = '$notes',

                                dateAdded = '$dateAdded',
                                 brand = '$brand'

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
    global $brand;
    $emailId = addslashes($_POST['emailId']);



    $Query = mysql_query("SELECT

                               *

                                FROM email_customer_notes

                                WHERE emailId = '$emailId'

                                AND brand = '$brand'

                                AND dateAdded >= unix_timestamp(CURRENT_DATE - interval 1 month )

    ");



    while ($resArr = mysql_fetch_array($Query)) {

        $dataArr[] = $resArr;

    }

    echo json_encode($dataArr);

    die;

}







function getCustomerInfo(){


    global $brand;
    $emailId = addslashes($_POST['emailId']);



    $Query = mysql_query("SELECT `orders`, unsubscribe FROM users WHERE emailaddress ='$emailId'  AND brand = '$brand'");

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

                                 WHERE email ='$emailId'  AND brand = '$brand';

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
    global $brand;
    $emailTo = addslashes($_POST['emailTo']);

    $emailsubject = addslashes($_POST['emailsubject']);

    // $report = str_replace("'", "\'", $report);

    // $report = str_replace('"', '\"', $report);

    $emailMessage = addslashes($_POST['emailMessage']);

    // send email
    require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';

    // $reply = str_replace('\\', '', $reply);
    emailnow($emailTo, 'James Harris', 'customer-care@superviral.io', $emailsubject, nl2br($emailMessage));

    $existQuery = mysql_query("select min(`emailUid`) as cnt from email_queue where `source` = 'composeEmail'  AND brand = '$brand'");
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
                                                            `dateAdded`         = '$dateAdded',  brand = '$brand'"
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

function updateAccountId(){
    $orderId = addslashes($_POST['orderId']);
    $accId = addslashes($_POST['accId']);



    $Query = mysql_query("UPDATE orders SET `account_id` = '$accId' WHERE `id` = '$orderId' LIMIT 1");



    if ($Query) {

        $response = array('Message' => 'Done Successfully');

        echo json_encode($response);

        die;

    } else {

        $response = array('Message' => 'Failed, Please try again');

        echo json_encode($response);

        die;

    }
}


function callCheckIsPrivate(){
    global $superviralsocialscrapekey, $rapidapihost, $rapidapikey;
    $email = addslashes($_POST['email']);
    $query = 'SELECT igusername FROM orders WHERE emailaddress="'.$email.'" AND `added` >= unix_timestamp(CURRENT_DATE - interval 2 month) GROUP BY igusername order by id desc';
    $runQuery = mysql_query($query);
    $i = 0;
    while($info = mysql_fetch_array($runQuery)){

        sendCloudwatchData('EtraGroupAdmin', 'supernova-api-email-support-getprofile', 'EmailSupportHandler', 'supernova-api-email-support-getprofile-function', 1);

        $url = 'https://i.supernova-493.workers.dev/api/v3/userId?username=' . $info['igusername'];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $get = curl_exec($curl);

        if($get == null){
            $url = 'https://flashapi1.p.rapidapi.com/ig/info_username/?user='. $info['igusername'] .'&nocors=false';

            //ATTEMPT TODO IT OUR WAY
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-rapidapi-host: $rapidapihost","x-rapidapi-key: $rapidapikey"));
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
            curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    
            $get = curl_exec($curl);
        }

        $resp = $get;
    
        $resp = json_decode($resp, true);
        $users = $resp['data'];
        if(empty($users)){
            $users = $resp;
        }
        // $userId = $users['user']['pk_id'];
        $isprivate= $users['user']['is_private'];

        if($isprivate){
            $isprivate = "yes";
           
        }else{
            $isprivate = "no";
        }
    
        curl_close($curl);

        $arrayData[$i]['username'] = $info['igusername'];
        $arrayData[$i]['isprivate'] = $isprivate;
        $i++;
        
    }

    echo json_encode($arrayData);
    die;
}

function downloadChats()
{
    
    $limit = addslashes($_POST['limit']);
    $emails = $_POST['emails'];
    
    if(!empty($emails)){
        $emailsArr = json_decode($emails, true);

        $inputValues = array_column($emailsArr, 'inputValue');
        
        $inputValues = array_map(function($email) {
            return "'" . addslashes($email) . "'";
        }, $inputValues);
    
        $emailList = implode(',', $inputValues);
    }
  

    $query = 'SELECT DISTINCT `from` FROM email_queue';

    if (!empty($emailList)) {
        $query .= " WHERE `from` IN ($emailList)";
    }
    if(!empty($limit)){
        $query .= " ORDER BY id desc LIMIT ". $limit;
    }

    $mainQuery = mysql_query($query);

    
    $downloadsDir = '../../gpt-convos'; // Current directory of the script
    if (!is_dir($downloadsDir)) {
        if (!mkdir($downloadsDir, 0775, true)) {
            die("Failed to create downloads directory.");
        }
    }

    $zip = new ZipArchive();
    $zipFileName = "emails_" . time() . ".zip";
    $zipFilePath = $downloadsDir . "/" . $zipFileName;

    // Create the ZIP file
    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die("Failed to create ZIP file.");
    }

    // Loop through each sender
    while ($mainQueryData = mysql_fetch_array($mainQuery)) {
        // Fetch all emails from the current sender
        $responseOnEmailArr = []; // Reset for each email
        $Query = mysql_query("SELECT
                                id,
                                `email` as `reply`,
                                `subject`,
                                `emailDate` as `dateAdded`,
                                `emailUid`,
                                `from`
                            FROM email_queue
                            WHERE `from` = '". $mainQueryData['from'] ."'");

        while ($resArr = mysql_fetch_array($Query)) {
           
            $responseOnEmailArr[] = $resArr; // Add the original email

            // Fetch support responses for the current email
            $QuerySupport = mysql_query("SELECT
                                            id,
                                            `to`,
                                            `emailUid`,
                                            `reply`,
                                            `from`,
                                            `dateAdded`,
                                            hideMessage,
                                            'support' as `type`
                                        FROM email_support_replies
                                        WHERE emailUid = '" . $resArr["emailUid"] . "'");

            while ($resArrSupport = mysql_fetch_array($QuerySupport)) {
                $responseOnEmailArr[] = $resArrSupport; // Add support responses
            }

            // Fetch admin responses for the current email
            $QueryAdmin = mysql_query("SELECT
                                        id,
                                        `message` as initialmsg,
                                        `emailaddress` as `to`,
                                        `emailUid`,
                                        `directions` as reply,
                                        `response` as dateAdded,
                                        hideMessage,
                                        'admin' as `type`
                                    FROM admin_notifications
                                    WHERE emailUid = '" . $resArr["emailUid"] . "'");

            while ($resArrAdmin = mysql_fetch_array($QueryAdmin)) {
                $resArrAdmin['reply'] = $resArrAdmin['initialmsg'] . ' ' . $resArrAdmin['reply'];
                $responseOnEmailArr[] = $resArrAdmin; // Add admin responses
            }
        }
        
        // Sort responses by date
        $time = array_column($responseOnEmailArr, 'dateAdded');
        array_multisort($time, SORT_ASC, $responseOnEmailArr);

        // Prepare content for the text file
        $content = "";
        foreach ($responseOnEmailArr as $response) {
            // Determine email type for display
            if ($response["type"] == "support") {
                $emailnameshow = "Support Team";
            } else if ($response["type"] == "admin") {
                $emailnameshow = "Admin Team";
            } else {
                $emailnameshow = $resArr['from'];
            }

            // Append response details to content
            $content .= "Email ID: " . $emailnameshow . "\n";
            $content .= "Subject: " . $response['subject'] . "\n";
            $content .= "Date Time: " . date('H:i d-m-y', $response['dateAdded']) . "\n";
            $content .= "Reply: " . $response['reply'] . "\n";
            $content .= "---------------------------------------------------------------------------------\n";
        }

        // Define a unique filename for each email based on email ID
        // Sanitize filename to prevent issues with invalid characters
        $safeEmailFrom = preg_replace('/[^A-Za-z0-9\_\-\.]/', '_', $mainQueryData['from']);
        $fileName = "email_" . $safeEmailFrom . ".txt"; // Use sanitized email for uniqueness
        $filePath = $downloadsDir . "/" . $fileName; // Temporary location for the text file

        // Write the content to the file
        if (file_put_contents($filePath, $content) === false) {
            die("Failed to write to file: " . $filePath);
        }

        // Add the text file to the ZIP archive
        $zip->addFile($filePath, $fileName);
    }

    // Close the ZIP file
    $zip->close();

     // Upload the ZIP file to S3
     try {
       global $s3Client;
        $bucketName = 'etra-live-chat-bucket'; // Replace with your S3 bucket name
        $keyName = 'downloads/' . basename($zipFileName); // Define the key where the ZIP file will be stored

        // Upload file to S3
        $result = $s3Client->putObject([
            'Bucket' => $bucketName,
            'Key'    => $keyName,
            'SourceFile' => $zipFilePath,
            // 'ACL'    => 'public-read' // Adjust permissions as needed
        ]);

        // Get the file URL
        $fileUrl = $result['ObjectURL'];

        // Clean up the temporary files
        unlink($zipFilePath);
        array_map('unlink', glob("$downloadsDir/*.txt")); // Remove temporary text files

        $response = array('file' => $fileUrl);
        // echo json_encode($response);
    } catch (AwsException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        die;
    }

    // $response = array('file' => $zipFilePath);

    echo json_encode($response);
    die;
}

function searchLogs() {

    require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';

    global $amazons3key, $amazons3password;
    $s3 = new S3($amazons3key, $amazons3password);
    $bucket = ''; // change later on

    $page = addslashes($_POST['page']);
    $search = addslashes($_POST['search']);
    $dateFrom = isset($_POST['dateFrom']) ? trim($_POST['dateFrom']) : '';
    $dateTo = isset($_POST['dateTo']) ? trim($_POST['dateTo']) : '';

    $hasDateRange = ($dateFrom !== '' && $dateTo !== '');

    $data = [];

    if (!$hasDateRange) {
        // No dates selected 
        $query = "SELECT * FROM lambda_logs WHERE 1=1";
        if ($page) $query .= " AND page = '" . $page . "'";
        if ($search) $query .= " AND log LIKE '%" . $search . "%'";
        $query .= " ORDER BY id DESC LIMIT 500";

        $result = mysql_query($query);
        while ($info = mysql_fetch_array($result)) {
            $data[] = $info;
        }
    } else {
        // Date range selected 
        $dateFromUnix = strtotime($dateFrom);
        $dateToUnix = strtotime($dateTo); // include full day
        $fourteenDaysAgo = strtotime('-14 days');

        if ($dateFromUnix >= $fourteenDaysAgo) {
            // Within 14 days : MySQL
            $query = "SELECT * FROM lambda_logs WHERE added BETWEEN $dateFromUnix AND $dateToUnix";
            if ($page) $query .= " AND page = '" . $page . "'";
            if ($search) $query .= " AND log LIKE '%" . $search . "%'";
            $query .= " ORDER BY id DESC LIMIT 500";

            $result = mysql_query($query);
            while ($info = mysql_fetch_array($result)) {
                $data[] = $info;
            }
        } else {
            // Beyond 14 days : S3
            $prefix = 'etra-live-logs-archive/';
            $objects = $s3->getBucket($bucket, $prefix);
            $results = [];

            foreach ($objects as $object) {
                // Example: logs_2025-04-15_12-00-00.json
                $filename = basename($object['name']);
            
                // Extract date from filename: logs_YYYY-MM-DD
                if (preg_match('/logs_(\d{4}-\d{2}-\d{2})/', $filename, $match)) {
                    $fileDate = strtotime($match[1]);
            
                    if ($fileDate >= $dateFromUnix && $fileDate <= $dateToUnix) {
                        $fileContent = $s3->getObject($bucket, $object['name']);
                        if (!$fileContent || empty($fileContent->body)) continue;
            
                        $logs = json_decode($fileContent->body, true);
                        if (!is_array($logs)) continue;
                        $logs = file_get_contents('/tmp/logs_2025-05-23_10-40-19.json');
                        $logs = json_decode($logs, true);

                        foreach ($logs as $log) {
                            $log['added'] = str_replace('-','',$log['added']);
                            // $log['page'] = str_replace('-','',$log['page']);
                            $log['added'] = trim($log['added']);
                            $log['page'] = trim($log['page']);
                            // echo $log['page'] . '-' . $log['added'] . '-' . $dateFromUnix .'-' . $dateToUnix . '-' . $page .'<br>';
                            if ($log['added'] >= $dateFromUnix && $log['added'] <= $dateToUnix) {
                                if (
                                    ($page && $log['page'] !== $page) ||
                                    ($search && stripos($log['log'], $search) === false)
                                ) {
                                    continue;
                                }
                                $results[] = $log;
                            }
                        }
                    }
                }
            }
            $data = array_slice($results, 0, 500);
        }
    }

    if (!empty($data)) {
        echo json_encode(['info' => $data]);
    } else {
        echo json_encode(['error' => 'No logs found']);
    }

    die;
}


// for email support v2
function selectAction(){

    $actionId = addslashes($_POST['actionId']);
    $ticketId = addslashes($_POST['ticketId']);

    $Query = mysql_query("SELECT * FROM email_queue_actions WHERE id = '$actionId'");
    $ActionsArr = mysql_fetch_array($Query);

    $ActionsArr['email_msg'] = str_replace("'","\'", $ActionsArr['email_msg']);

    if($actionId == 'custom'){
        $Query = mysql_query("UPDATE email_queue SET `custom_tpl` = '', action_done = 1 WHERE `id` = '{$ticketId}' ");

    }else{
        $Query = mysql_query("UPDATE email_queue SET `selected_generated_tpl` = '{$ActionsArr['email_msg']}', action_done = 1 WHERE `id` = '{$ticketId}' ");

    }

    if(is_numeric($actionId))
    $Query = mysql_query("UPDATE email_queue_actions SET selected = 1 WHERE `id` = '{$actionId}' ");
    else
    $Query = mysql_query("UPDATE email_queue_actions SET selected = 1 WHERE LOWER(`action`) = LOWER('{$actionId}') AND email_queue_id = '{$ticketId}' limit 1");


    if ($Query) {

        $response = array('Message' => 'Done');

        echo json_encode($response);

        die;

    } else {

        $response = array('Message' => 'Failed, Please try again');

        echo json_encode($response);

        die;

    }


}

function submitReport()
{
    global $brand;

    switch($brand){
        case 'sv':
            $supportEmail = 'support@superviral.io';
            $comp = 'superviral.io';
        break;
        case 'to':
            $supportEmail = 'support@tikoid.com';
            $comp = 'tikoid.com';

           break; 
    }
    $emailUid = addslashes($_POST['emailUid']);
    $reply = addslashes($_POST['reply']);
    $ticketId = addslashes($_POST['ticketId']);
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
                            dateAdded = '$dateAdded',
                            brand = '$brand'
                        ");

    $Query = mysql_query("UPDATE email_queue SET `custom_tpl` = '$reply',markDone = '1', `selected_generated_tpl` = '$reply' WHERE `id` = '{$ticketId}' ");

    if($brand == 'sv'){$domain = 'superviral.io';}
    if($brand == 'to'){$domain = 'tikoid.com';}


    $jhsignature = '
        <br>
        Head of Customer Care<br>
        <a href="https://'. $comp .'/">'. $comp .'</i></a>
        <br><br>
        <img style="width:275px;height:54px" width="275" height="54" src="https://superviral.io/imgs/jharrissig.png">
        <br><br>
        <i>E: James-harris@'.$domain.'</i><br>
        <i>T: +44 203 856 3786</i><br>
        <i>A: 160 City Road, London, EC1V 2NX, United Kingdom</i>
    ';

    if ($Query) {
        // send email
        require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';

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

function submitReportOnEmail2()

{
    global $brand;
    $orderId = addslashes($_POST['orderId']);

    $report = addslashes($_POST['report']);

    // $report = str_replace("'", "\'", $report);

    // $report = str_replace('"', '\"', $report);

    $emailId = addslashes($_POST['emailId']);

    $emailUid = addslashes($_POST['emailUid']);

    $supportSubject = addslashes($_POST['emailsubject']);

    $dateAdded = time();

    $difficulty = getDifficulty($report);
    $adminName  = $_SESSION['first_name'];
    
    $Query = mysql_query("INSERT INTO admin_notifications

                            SET `emailaddress` = '$emailId',

                                `orderid` = '$orderId',

                                `message` = '$report',

                                `type` = 'emailSupport',

                                `emailUid` = '$emailUid',

                                added = '$dateAdded',

                                directions = '',

                                admin_name ='$adminName',

                                `difficulty` = $difficulty,
                                 brand = '$brand'

                        ");

    if ($Query) {

        $Query = mysql_query("UPDATE email_queue SET `submitReport` = '1' WHERE `from` = '$emailId'");

    }



    if ($Query) {

        $response = array('Message' => 'Report Submitted Successfully');


        ////////////////////////


        // send email
        require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';


        $reply = "Hi there,

        Thank you very much for contacting Superviral Customer Care. I've looked into your query and can see that this requires a closer look with my management team. This happens usually when a query requires the assistance of other Superviral departments.       

        For this reason, I've forwarded your query to our management team so that we can resolve this issue as soon as possible. This usually results in more than one of our team looking at the issue, as you know, at Superviral the customer always comes first.        

        I'll get back to you within 24-36 hours once I've resolved the issue for you. I understand this is an important issue so hopefully, I can get this resolved even quicker for you.       

        Kind Regards,
        James Harris.";
    
       // $reply = str_replace('\\', '', $reply);
        // emailnow($emailId, 'James Harris', 'customer-care@superviral.io', 'RE: '.$supportSubject, nl2br($reply));




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
                            dateAdded = '$dateAdded',
                             brand = '$brand'
                        ");


        echo json_encode($response);

        die;

    } else {

        $response = array('Message' => 'Failed, Please try again');

        echo json_encode($response);

        die;

    }



}


function offerFreeFollowers()
{
    global $freefollowersorderid ,$ttfreefollowersorderid, $fulfillment_api_key, $fulfillment_url ;

    $username = addslashes($_POST['username']);
    $username = str_replace('@', '', $username);    
    $amount = addslashes($_POST['amount']);
    $orderId = addslashes($_POST['orderId']);
    // $socialmedia = addslashes($_POST['socialmedia']);
    $userInfo_qs = parse_str($_POST['userInfo'],$userInfo);
    $brand = addslashes($_POST['brand']);
    $offerSocialMedia = addslashes($_POST['offerSocialMedia']);


    //ANYTHING COMMENTED OUT MEANS THEY TAKE ACTION AND SHOULD BE REMOVED
    if ((!empty($username)) && (!empty($amount))) {

        $emailtrue = 'asdas4dsdf';

        $added = time();
        $randnum = rand(1111111111,9999999999); // for fresh orders with no order id
        $ordersession = md5('neworder' . $added . $randnum);
        $ipaddress = $_SERVER['REMOTE_ADDR'];

        if(!empty($orderId)){
            $ordersession = md5('neworder' . $added . $orderId);
            $qfetch = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderId' LIMIT 1");
            $orderinfo = mysql_fetch_array($qfetch);
            $ipaddress = $orderinfo['ipaddress'];
            $country = $orderinfo['country'];
        }

        include($_SERVER['DOCUMENT_ROOT'] . '/crons/orderfulfillraw.php');

        $product = getBrandSelectedDomain($brand);
        $brandName = getBrandSelectedName($brand);

        switch ($offerSocialMedia) {
            case 'ig':
                $domain = 'instagram.com';
                $serviceId = $freefollowersorderid;
            break;
            case 'tt':
                $domain = 'tiktok.com';
                $username = '@' . $username;
                $serviceId = $ttfreefollowersorderid;
            break;
        }

        $supplierArr  = array('service' => $serviceId, 'link' => 'https://' . $domain . '/' . $username, 'quantity' => $amount);
        // print_r($supplierArr);
        $order1 = $api->order($supplierArr);
        $fulfill_id = $order1->order;

        if (empty($fulfill_id)){
            $response = array('Message' => 'Contact Rabban with this error: Missing Fulfill ID', 'supplier' => $supplierArr);

            echo json_encode($response);
            die;
        }

        // $order_status = $api->status($order1->order);
        // $supplier_cost = $order_status->charge;
        // // insert supplier cost log
        // mysql_query('INSERT INTO supplier_cost SET `type` = "followers", `amount` = "'.$amount.'", `service_id` = "'.$freefollowersorderid.'", `cost` ="'. $supplier_cost .'", `page` = "admin/api/email-support-handler.php", `timestamp` = '.time().', `socialmedia` = "'.$orderinfo['socialmedia'].'", `brand` = "'.$orderinfo['brand'].'"');
        if(empty($userInfo['account_id'])){
            $userInfo['account_id'] = 0;
        }
        
        $username = str_replace('@', '', $username);
        
        $insertq = mysql_query("INSERT INTO `orders` SET 
            `packagetype`= 'followers', 
            `country`= '{$country}', 
            `socialmedia`= '{$offerSocialMedia}', 
            `account_id`= '{$userInfo['account_id']}', 
            `order_session`= '{$ordersession}',
            `added` = '$added', 
            `lastrefilled` = '$added', 
            `amount`= '$amount', 
            `emailaddress`= '{$userInfo['emailaddress']}', 
            `igusername`= '{$username}', 
            `ipaddress`= '{$ipaddress}',
            `price`= '0.00',
            `payment_id` = '{$orderinfo['payment_id']}',
            `fulfill_id`= '{$fulfill_id}',
             brand = '$brand'");
        $insertid = mysql_insert_id();
        //  logs

        $insertq = mysql_query("INSERT INTO `email_offers` SET 
            `type`= 'followers', 
            `amount`= '$amount', 
            `igusername`= '{$username}', 
            `added` = '$added'
            ");

        //EMAILER NEEDS TO COME IN HERE
        $thefreeservice = $amount . ' free Followers';
        $service = $amount . ' High Quality Followers';
        $ctahref = 'https://' . $product . '/track-my-order/' . $ordersession;
        $igusername = $username;
        $to = $orderinfo['emailaddress'];
        $subject = 'Free ' . ucfirst($brandName) . ' Followers Notification';
        include($_SERVER['DOCUMENT_ROOT'] . '/admin/api/emailfree.php');
        
        $orderstatus = 'In progress' ;

        if($userInfo['socialmedia'] == 'ig'){
            $domain = 'https://instagram.com/'; 
            $postUrl = $domain.$igusername.'/';

            $socialMediaLogo = "/admin/assets/icons/Instagram-icon.svg";

        }else if($userInfo['socialmedia'] == 'tt'){
            $domain = 'https://tiktok.com/@';
            $postUrl = $domain.$igusername.'/';

            $socialMediaLogo = "/admin/assets/icons/Tiktok-icon.svg";

        }
        $trackingHref = "https://superviral.io/track-my-order/" . $ordersession;

        $customerOrder = $amount .  ' followers';

        $added = ago($added);

        $data = array('orderStatus' => $orderstatus, 'socialMediaLogo' => $socialMediaLogo, 
                        'postUrl' => $postUrl, 'trackinghref' => $trackingHref, 'customerOrder' => $customerOrder, 'customerOrderId' => $insertid, 'added' => $added);    
        

        if ($insertq){
            $response = array('Message' => 'success', 'insertId' => $insertid, 'dataArr' => $data);

            echo json_encode($response);
            die;
        }
    }else{
        $response = array('Message' => 'Failed, Please fill all values');

        echo json_encode($response);
        die;
    }
}


function offerFreeLikes()
{

    global $freelikesorderid, $ttfreelikesorderid, $freeviewsorderid, $fulfillment_api_key, $fulfillment_url ;

    $posts = $_POST['posts'];
    $amount = addslashes($_POST['amount']);
    $username = addslashes($_POST['username']);
    $username = str_replace('@', '', $username);
    $orderId = addslashes($_POST['orderId']);
    $brand = addslashes($_POST['brand']);
    $userInfo_qs = parse_str($_POST['userInfo'],$userInfo);

    if ((!empty($posts)) && (!empty($amount))) {

        $emailtrue = 'asdas4dsdf';

        $added = time();
        $randnum = rand(1111111111,9999999999); // for fresh orders with no order id
        $ordersession = md5('neworder' . $added . $randnum);
        $ipaddress = $_SERVER['REMOTE_ADDR'];

        if($orderId){
            $ordersession = md5('neworder' . $added . $orderId);
            $qfetch = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderId' LIMIT 1");
            $orderinfo = mysql_fetch_array($qfetch);
            $ipaddress = $orderinfo['ipaddress'];
            $country = $orderinfo['country'];
        }

        include($_SERVER['DOCUMENT_ROOT'] . '/crons/orderfulfillraw.php');

        $product = getBrandSelectedDomain($brand);
        $brandName = getBrandSelectedName($brand);

       
        $postArr = explode(',', $posts);

        foreach ($postArr as $post) {
            if (empty($post)) continue;
            $postsrefined[] = $post;
        }

        // $totalposts = count($postArr);
        unset($post);

        // $multiamount = $amount / $totalposts;
        // $multiamount = round($multiamount);

        foreach ($postsrefined as $post) {

            $post = trim($post);

            if (strpos($post, 'tiktok.com') !== false) {
                $offerSocialMedia = 'tt';
            } elseif (strpos($post, 'instagram.com') !== false) {
                $offerSocialMedia = 'ig';
            } else {
                continue;
            }

            switch ($offerSocialMedia) {
                case 'ig':
                    $domain = 'instagram.com';
                    $serviceId = $freelikesorderid;
                break;
                case 'tt':
                    $domain = 'tiktok.com';
                    $username = '@' . $username;
                    $serviceId = $ttfreelikesorderid;
                break;
            }

              // Extract post identifier
            if ($offerSocialMedia === 'ig') {
                $postraw = str_replace('https://www.instagram.com/p/', '', $post);
                $postraw = str_replace('/', '', $postraw);
            } elseif ($offerSocialMedia === 'tt') {
                // preg_match('/video\/(\d+)/', $post, $matches);
                $postraw = $post; 
            }
            $postraw = trim($postraw);
            
            // likes
            $supplierArr = array('service' => $serviceId, 'link' => $post, 'quantity' => $amount);
            $order1 = $api->order($supplierArr);
            // views
            if($offerSocialMedia == 'ig'){
                $supplierArr = array('service' => $freeviewsorderid, 'link' => $post, 'quantity' => $amount);
                $order1 = $api->order($supplierArr);
            }
          

            $fulfillids .= $order1->order;
            $fulfillids .= ' ';

            $chooseposts .= $postraw . ' ';

            // $order_status = $api->status($order1->order);
            // $supplier_cost = $order_status->charge;
            // insert supplier cost log
            // mysql_query('INSERT INTO supplier_cost SET `type` = "likes", `amount` = "'.$multiamount.'", `service_id` = "'.$freelikesorderid.'", `cost` ="'. $supplier_cost .'", `page` = "admin/api/email-support-handler.php", `timestamp` = '.time().', `socialmedia` = "'.$orderinfo['socialmedia'].'", `brand` = "'.$orderinfo['brand'].'"');
    
        }
       
        if (empty($fulfillids)){
            $response = array('Message' => 'Contact Rabban with this error: Missing Fulfill ID', 'supplier' => $supplierArr);

            echo json_encode($response);
            die;
        } 

        if(empty($userInfo['account_id'])){
            $userInfo['account_id'] = 0;
        }
        $username = str_replace('@', '', $username);
        
        $insertq = mysql_query("INSERT INTO `orders` SET 
            `packagetype`= 'likes', 
            `country`= '{$country}', 
            `socialmedia`= '{$offerSocialMedia}', 
            `account_id`= '{$userInfo['account_id']}',
            `order_session`= '{$ordersession}',
            `added` = '$added', 
            `lastrefilled` = '$added', 
            `amount`= '$amount', 
            `emailaddress`= '{$userInfo['emailaddress']}', 
            `igusername`= '{$username}', 
            `ipaddress`= '{$ipaddress}',
            `price`= '0.00',
            `payment_id` = '{$orderinfo['payment_id']}',
            `fulfill_id`= '{$fulfillids}',
            `chooseposts` = '$chooseposts',
             brand = '$brand'");

        $insertid = mysql_insert_id();
        //  logs

        $insertq = mysql_query("INSERT INTO `email_offers` SET 
        `type`= 'likes', 
        `amount`= '$amount', 
        `igusername`= '{$orderinfo['igusername']}', 
        `post_url` = '$chooseposts',
        `added` = '$added'
        ");

        //EMAILER NEEDS TO COME IN HERE
        $thefreeservice = $amount . ' free Likes';
        $service = $amount . ' High Quality Likes';
        $ctahref = 'https://' . $product . '/track-my-order/' . $ordersession;
        $igusername = $username;
        $to = $orderinfo['emailaddress'];
        $subject = 'Free ' . $brandName . ' Likes Notification';
        include($_SERVER['DOCUMENT_ROOT'] . '/admin/api/emailfree.php');

        $orderstatus = 'In progress' ;

        if($userInfo['socialmedia'] == 'ig'){
            $domain = 'https://instagram.com/'; 
            $postUrl = $domain.$igusername.'/';

            $socialMediaLogo = "/admin/assets/icons/Instagram-icon.svg";

        }else if($userInfo['socialmedia'] == 'tt'){
            $domain = 'https://tiktok.com/';
            $postUrl = $domain.$igusername.'/';

            $socialMediaLogo = "/admin/assets/icons/Tiktok-icon.svg";

        }

        $trackingHref = "https://superviral.io/track-my-order/" . $ordersession;

        $customerOrder = $amount .  ' likes';

        $added = ago($added);

        $data = array('orderStatus' => $orderstatus, 'socialMediaLogo' => $socialMediaLogo, 
                        'postUrl' => $postUrl, 'trackinghref' => $trackingHref, 'customerOrder' => $customerOrder, 'customerOrderId' => $insertid, 'added' => $added);    
        


        if ($insertq){
            $response = array('Message' => 'success', 'insertId' => $insertid, 'fulfills' => $fulfillids, 'dataArr' => $data);

            echo json_encode($response);
            die;
        }
    }else{
        $response = array('Message' => 'Failed, Please fill all values');

        echo json_encode($response);
        die;
    }
}

function updateALStatus(){

    $orderId = addslashes($_POST['orderId']);
    $status = addslashes($_POST['status']);

    ($status == 'Active') ? $status = 0 : $status = 1;

    $q= mysql_query("UPDATE automatic_likes SET `disabled` = '$status' WHERE `id` = '$orderId'");

    if ($q){
        $response = array('Message' => 'success');

        echo json_encode($response);
        die;
    }else{
        $response = array('Message' => 'Failed');

        echo json_encode($response);
        die;
    }

}

function ResendFollowers()
{
    global $freefollowersorderid ,$ttfreefollowersorderid, $fulfillment_api_key, $fulfillment_url ;

    $orderId = addslashes($_POST['orderId']);
   
    $qfetch = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderId' LIMIT 1");
    $orderinfo = mysql_fetch_array($qfetch);
    $brand = $orderinfo['brand'];
    $offerSocialMedia = $orderinfo['socialmedia'];
    $username = $orderinfo['igusername'];
    $username = str_replace('@', '', $username);
    $amount = $orderinfo['amount'];

    include($_SERVER['DOCUMENT_ROOT'] . '/crons/orderfulfillraw.php');
    // $product = getBrandSelectedDomain($brand);
    // $brandName = getBrandSelectedName($brand);
    switch ($offerSocialMedia) {
        case 'ig':
            $domain = 'instagram.com';
            $serviceId = $freefollowersorderid;
        break;
        case 'tt':
            $domain = 'tiktok.com';
            $username = '@' . $username;
            $serviceId = $ttfreefollowersorderid;
        break;
    }
    $supplierArr  = array('service' => $serviceId, 'link' => 'https://' . $domain . '/' . $username, 'quantity' => $amount);
    // print_r($supplierArr);
    $order1 = $api->order($supplierArr);
    $fulfill_id = $order1->order;
    if (empty($fulfill_id)){
        $response = array('Message' => 'Contact Rabban with this error: Missing Fulfill ID', 'supplier' => $supplierArr);
        echo json_encode($response);
        die;
    }
    // //EMAILER NEEDS TO COME IN HERE
    // $thefreeservice = $amount . ' free Followers';
    // $service = $amount . ' High Quality Followers';
    // $ctahref = 'https://' . $product . '/track-my-order/' . $ordersession;
    // $igusername = $username;
    // $to = $orderinfo['emailaddress'];
    // $subject = 'Free ' . ucfirst($brandName) . ' Followers Notification';
    // include($_SERVER['DOCUMENT_ROOT'] . '/admin/api/emailfree.php');
    
    $response = array('Message' => 'success', 'insertId' => $orderId);
    echo json_encode($response);
    die;
}


function ResendLikes()
{

    global $freelikesorderid, $ttfreelikesorderid, $fulfillment_api_key, $fulfillment_url ;

    $orderId = addslashes($_POST['orderId']);
    $qfetch = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderId' LIMIT 1");
    $orderinfo = mysql_fetch_array($qfetch);

    $posts = $orderinfo['chooseposts'];
    $socialmedia = $orderinfo['socialmedia'];
    $amount = $orderinfo['amount'];

    include($_SERVER['DOCUMENT_ROOT'] . '/crons/orderfulfillraw.php');
    // $product = getBrandSelectedDomain($brand);
    // $brandName = getBrandSelectedName($brand);
    
    $postArr = explode(' ', $posts);
    foreach ($postArr as $post) {
        if (empty($post)) continue;
        $postsrefined[] = $post;
    }
    // $totalposts = count($postArr);
    unset($post);
    // $multiamount = $amount / $totalposts;
    // $multiamount = round($multiamount);
    foreach ($postsrefined as $post) {

            $post = trim($post);
           
            switch ($socialmedia) {
                case 'ig':
                    $post = 'https://www.instagram.com/p/' . $post . '/';
                    $serviceId = $freelikesorderid;
                break;
                case 'tt':
                    $username = '@' . $username;
                    $serviceId = $ttfreelikesorderid;
                break;
            }

            $supplierArr = array('service' => $serviceId, 'link' => $post, 'quantity' => $amount);
            $order1 = $api->order($supplierArr);

            $fulfillids .= $order1->order;
            $fulfillids .= ' ';

            // $order_status = $api->status($order1->order);
            // $supplier_cost = $order_status->charge;
            // insert supplier cost log
            // mysql_query('INSERT INTO supplier_cost SET `type` = "likes", `amount` = "'.$multiamount.'", `service_id` = "'.$freelikesorderid.'", `cost` ="'. $supplier_cost .'", `page` = "admin/api/email-support-handler.php", `timestamp` = '.time().', `socialmedia` = "'.$orderinfo['socialmedia'].'", `brand` = "'.$orderinfo['brand'].'"');
    
    }
    
    if (empty($fulfillids)){
        $response = array('Message' => 'Contact Rabban with this error: Missing Fulfill ID', 'supplier' => $supplierArr);
        echo json_encode($response);
        die;
    } 
    // //EMAILER NEEDS TO COME IN HERE
    // $thefreeservice = $amount . ' free Likes';
    // $service = $amount . ' High Quality Likes';
    // $ctahref = 'https://' . $product . '/track-my-order/' . $ordersession;
    // $igusername = $username;
    // $to = $orderinfo['emailaddress'];
    // $subject = 'Free ' . $brandName . ' Likes Notification';
    // include($_SERVER['DOCUMENT_ROOT'] . '/admin/api/emailfree.php');

    $response = array('Message' => 'success', 'insertId' => $orderId);
    echo json_encode($response);
    die;

}
