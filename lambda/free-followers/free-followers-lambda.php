<?php

require __DIR__ . '/../common/db.php';
require __DIR__ . '/../common/common.php';
require __DIR__ . '/../func/freefollowers.php';

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
            $brand = $info['brand'];

            switch ($brand) {
                case 'sv':
                    $brandName = 'Superviral';
                    $domain = 'superviral';
                    $product = "Instagram";
                    break;
                case 'to':
                    $brandName = 'Tikoid';
                    $domain = 'tikoid';
                    $product = "Tiktok";
                    break;
            }

            //UPDATE THIS SO THAT ITS DONE
            //mysql_query("UPDATE `users` SET `monthlyfreefollowers` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");

            //VALIDATE EMAIL
            if (!filter_var($info['emailaddress'], FILTER_VALIDATE_EMAIL)) {

                mysql_query("UPDATE `users` SET `unsubscribe` = '9' WHERE `id` = '{$info['id']}' LIMIT 1");
            }

            if (!filter_var($info['emailaddress'], FILTER_VALIDATE_EMAIL)) continue;

            $loc2 = $info['country'];

            if (empty($loc2)) $loc2 = 'us';

            if (!empty($loc2)) $loc2 = $loc2 . '/';
            if ($loc2 == 'ww/') $loc2 = '';


            if (!empty($info['monthlyfreeusername'])) {
                $subject = '@' . $info['monthlyfreeusername'] . ': Get 30 '. $product .' Followers! (One Click)';
                //$subject = '@' . ucfirst($info['monthlyfreeusername']) . ': Get 30 '. $product .' Followers! (One Click)';
                 //$subject = '@'.ucfirst($info['monthlyfreeusername']).': Black Friday - Get 30 Free '. $product .' Followers!';
                //$subject = '@' . ucfirst($info['monthlyfreeusername']) . ': Your 30 '. $product .' Followers';
                $igusernameqinsert = "`username` = '{$info['monthlyfreeusername']}',";
            } else {
                //$subject = 'Black Friday - Get 30 Free '. $product .' Followers Now!';
                //$subject = 'Your 30 '. $product .' Followers';
                $subject = 'Get 30 Free '. $product .' Followers Now!';
                //$subject = '2022 NEW YEAR! - 30 Free '. $product .' Followers!';
            }
            

            $freetrialmd5 = md5($info['emailaddress'] . time());

            $insertq = mysql_query("INSERT INTO `freetrial` SET 
            `brand` = '$brand',
            `md5` = '{$freetrialmd5}',
            `tikoidmd5` = '{$freetrialmd5}',
            `emailaddress`='{$info['emailaddress']}',
            $igusernameqinsert
            `type`='1'
            ");

            //if(!$insertq)die('Not inserted free trial');

            $fetchuserinfo = mysql_fetch_array(mysql_query("SELECT `emailaddress`,`md5`,`brand` FROM `users` WHERE `brand` = '$brand' AND `emailaddress` = '{$info['emailaddress']}' LIMIT 1"));
            $md5unsub = $fetchuserinfo['md5'];


            $email = $info['emailaddress'];
            $loc2 = $loc2;
            $freetrialmd5 = $freetrialmd5;
            $md5unsub = $md5unsub;
            $source = $info['source'];
            $country = $info['country'];

            $tpl = emailTpl($loc2, $freetrialmd5, $md5unsub, $source, $searchorders['igusername'], $brand, $subject, $searchorders['socialmedia']);
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
