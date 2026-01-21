<?php

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../common/common.php';
require __DIR__ . '/../func/freelikes.php';

use Aws\Sqs\SqsClient;
use Bref\Context\Context;

global $sqsClient;
$sqsClient = new SqsClient([
    'region'  => 'us-east-2',  // Your AWS region
    'version' => 'latest',
    'credentials' => [
        'key'    => getenv('amazonLambdaKey'),
        'secret' => getenv('amazonLambdapassword'),
    ],
]);


return function (array $event, Context $context) {

    global $email_QueueUrl, $sqsClient;

    // Print the entire event received
    //  echo "Received event: " . json_encode($event) . "\n";
    //  die;
    if (isset($event['Records'])) {
        foreach ($event['Records'] as $record) {


            $jsonString = stripslashes($record['body']);
            $info = json_decode($jsonString, true);
            // print_r($info);die;

            //UPDATE THIS SO THAT ITS DONE
            mysql_query("UPDATE `users` SET `monthlyfreelikes` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");

            //VALIDATE EMAIL

            if (!filter_var($info['emailaddress'], FILTER_VALIDATE_EMAIL)) continue;

            $loc2 = $info['country'];
            $brand = $info['brand'];
            if (empty($loc2)) $loc2 = 'us';

            if (!empty($loc2)) $loc2 = $loc2 . '/';
            if ($loc2 == 'ww/') $loc2 = '';


            if ($info['source'] == 'order') {
                //$searchordersq = mysql_query("SELECT `igusername`,`emailaddress` FROM `orders` WHERE `emailaddress` LIKE '%{$info['emailaddress']}%' ORDER BY `id` DESC LIMIT 1");

                $searchordersq = mysql_query("SELECT `igusername`,`emailaddress`,`brand` FROM `orders` WHERE `brand` = '$brand' AND `emailaddress` = '{$info['emailaddress']}' ORDER BY `id` DESC LIMIT 1");

                $searchorders = mysql_fetch_array($searchordersq);
            }

            if ($info['source'] == 'cart') {


                $searchordersq = mysql_query("SELECT `igusername`,`emailaddress`,`brand` FROM `order_session` WHERE `brand` = '$brand' AND `emailaddress` = '{$info['emailaddress']}' ORDER BY `id` DESC LIMIT 1");

                $searchorders = mysql_fetch_array($searchordersq);
            }


            if (!empty($searchorders['igusername'])) {
                $subject = '@' . ucfirst($searchorders['igusername']) . ': Get 50 Instagram Likes! (One Click)';
                // $subject = '@'.ucfirst($searchorders['igusername']).': Black Friday - 50 Instagram Likes!';
                $igusernameqinsert = "`username` = '{$searchorders['igusername']}',";
            } else {
                $subject = 'Black Friday - Get 50 Free Instagram Likes Now!';
                //$subject = 'Get 50 Free Instagram Likes Now!';
                //$subject = '2022 NEW YEAR! - 50 Free Instagram Likes!';
            }


            $freetrialmd5 = md5($info['emailaddress'] . time());

            $insertq = mysql_query("INSERT INTO `freetrial` SET 
            `brand` = '$brand',
            `md5` = '{$freetrialmd5}',
            `emailaddress`='{$info['emailaddress']}',
            $igusernameqinsert
            `type`='1'
            ");

            ////////////////////////////////////////////////////////////////

            $fetchuserinfo = mysql_fetch_array(mysql_query("SELECT `emailaddress`,`md5`,`brand` FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' LIMIT 1"));
            $md5unsub = $fetchuserinfo['md5'];


            $email = $info['emailaddress'];
            $loc2 = $loc2;
            $subject = $subject;
            $freetrialmd5 = $freetrialmd5;
            $md5unsub = $md5unsub;
            $source = $info['source'];
            $country = $info['country'];

            $tpl = emailTpl($loc2, $freetrialmd5, $md5unsub, $source, $searchorders['igusername'], $brand, $subject, $searchorders['socialmedia'], $info['added']);
            // return json_encode($tpl);

            $email_arr = array(
                'to' => $email,
                'website' => 'Superviral',
                'subject' => '🐙 ' . $subject,
                'body' => $tpl,
                'from' => 'no-reply@superviral.io'
            );

            sendMessageToSqs(json_encode($email_arr), $email_QueueUrl, $sqsClient);
            echo 'Inserted into EMAIL_SQS';

            unset($ctabtn);
            unset($loc2);
            unset($freetrialmd5);
            unset($searchorders['igusername']);
            unset($token);
            unset($tokenExpiry);
            unset($emailHash);
            unset($tokenHash);
            unset($accountId);
            unset($accountquerylogin);
        }
    }
};
