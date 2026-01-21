<?php

require_once '../sm-db.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';


// $domain = "https://superviral.io";
$todaysdate = date("dmY");
$now = time();

echo 'todays date: ' . $todaysdate . '<hr>';

// /////////////////////////////////////// grab all accounts ///////////////////////////////////////
echo "======================================START========================================================<br><br>";

// superviral code
    $dbName = $superViralDB;

	mysql_select_db($dbName , $conn);

    $getemailtemplate = file_get_contents('superviral.io/emailtemplate/emailtemplate.html');

    $QueryRun = mysql_query("SELECT * FROM accounts WHERE viewed_dashboard = 1 AND notification_disabled = 0 AND `dashboard_email_last_sent` != '$todaysdate' LIMIT 140");

    while ($Data = mysql_fetch_array($QueryRun)) {

    echo $Data['id'] . '<br>';

    if ($Data['dashboard_email_last_sent'] != $todaysdate) { // sent once per day



        //////////////////// grab all user in account_username //////////////////////////////////////////
        $accUserQuery = mysql_query("SELECT * FROM account_usernames WHERE `account_id` = '{$Data['id']}' AND active = 1");


        while ($accData = mysql_fetch_array($accUserQuery)) {

            $userName = $accData['username'];
            $accountUserID = $accData['id'];
            echo "======Started for : $userName ====<br><br>";


            // grab stats/////////////////////
            $diffQuery = "SELECT
                            cu.id,
                            cu.followers - cu1.followers AS follower_diff , 
                            cu.avg_post_likes - cu1.avg_post_likes AS avg_post_likes_diff
                            FROM checkusers cu 
                            ,checkusers cu1 
                            WHERE cu.ig_username = '$userName'  AND
                            cu.id = (SELECT id FROM checkusers WHERE ig_username ='$userName' ORDER BY id DESC LIMIT 1) AND
                            cu1.id = (SELECT id FROM checkusers WHERE ig_username ='$userName' ORDER BY id DESC LIMIT 1,1)";

            $diffQueryRun = mysql_query($diffQuery);

            if (mysql_num_rows($diffQueryRun) > 0) {


                $accStats = mysql_fetch_array($diffQueryRun);
                $checkUserId = $accStats['id'];

                if ($accData['last_checkusers_id_done'] == $checkUserId) {
                    echo "======already sent =====<br><br>";
                    continue;
                }

                if ($accStats['follower_diff'] != 0 || $accStats['avg_post_likes_diff'] != 0) {

                    echo 'Difference found!<br>';


                    $subject = '@' . $userName . ': your latest Instagram data is ready';
                    $to = $Data['email'];

                    $token = md5(time() . $to);
                    $tokenExpiry = time() + (3 * 86400); //3 day token expiry
                    $emailHash = $Data['email_hash'];
                    $tokenHash = $Data['token_hash'];
                    $accountId = $Data['id'];


                    $insertTokenQuery = "INSERT INTO auto_login SET
                                                                        `access_token` = '$token',
                                                                        `expiry`  = '$tokenExpiry',    
                                                                        `account_id`        = '$accountId',
                                                                        `email_hash`     = '$emailHash',
                                                                        `token_hash`     = '$tokenHash',
                                                                        `added`     = '$now'";
                    $runTokenQuery = mysql_query($insertTokenQuery);
                    if ($runTokenQuery) echo "======Inserted into auto_login for account: $accountId ====<br><br>";

                    // check country
                    $country = $Data['country'];

                    switch ($country) {
                        case 'us':
                            $dashboardDomain = 'https://superviral.io/us';
                            break;
                        case 'uk':
                            $dashboardDomain = 'https://superviral.io/uk';
                            break;
                        default:
                            $dashboardDomain = 'https://superviral.io';
                            break;
                    }


                    // send email

                    if ($Data['country'] !== 'ww') $loclinkforward = $Data['country'] . '/';
                    if ($loclinkforward == '/') $loclinkforward = '';

                    $emailbody = '
                    <p>As you might remember, your Instagram account data updates every day. It\'s time to check if anything has changed for your Instagram account.</p><br>

                    <p>Checking your Instagram data can help you see if you\'re growing or not.
                    </p><br>

                    <a href="' . $dashboardDomain . '/' . $loclinkforward . 'login/?accessToken=' . $token . '" target="_blank" style="color: #2e00f4;
                    border: 2px solid #2e00f4;display: block;
                    width: 330px;padding: 16px 9px;
                    text-decoration: none;
                    -webkit-border-radius: 45px;
                    -moz-border-radius: 45px;
                    border-radius: 45px;
                    margin: 5px auto;
                    font-weight: 700;
                    text-align:center;">See your new data &raquo;</a>


                    <br>

                    <p style="text-align:center;"><a href="' . $dashboardDomain . '/turn-off-dashboard-notifs.php?id=' . $Data['email_hash'] . '" target="_blank">Edit new data notifications</a></p>';


                    unset($loclinkforward);

                    $tpl = $getemailtemplate;
                    $tpl = str_replace('{body}', $emailbody, $tpl);
                    $tpl = str_replace('{subject}', $subject, $tpl);
                    $tpl = str_replace('<a href="https://superviral.io/unsubscribe.php?unsub=now&id={md5unsub}">Unsubscribe', '', $tpl);

                    $to = addslashes($to);
                    $userName = addslashes($userName);
                    $subject = addslashes($subject);
                    $tpl = addslashes($tpl);

                    $userQueryRun = mysql_query("SELECT *
                                                    FROM users  WHERE emailaddress = '$to' LIMIT 1");
                    $userData = mysql_fetch_array($userQueryRun);

                    $beginOfDay = strtotime("today", time());
                    $userHighConvTime = intval($userData['highestconvtime']) * 3600;
                    $scheduledfor = $beginOfDay + $userHighConvTime;
                    if (time() > $scheduledfor) {
                        $tomorrowTime = strtotime('tomorrow 00:00');
                        $scheduledfor = $tomorrowTime + $userHighConvTime;
                    }

                    $insertMailQuery = "INSERT INTO post_notif_schedule SET
                                                                        `to` = '$to',
                                                                        `scheduled_for`  = '$scheduledfor',    
                                                                        `subject`        = '$subject',
                                                                        `email_body`     = '$tpl',
                                                                        `account_id`     =  '$accountId',
                                                                        `ig_username`    =  '$userName'";


                    $runQuery = mysql_query($insertMailQuery);
                    if ($runQuery) echo "======Inserted into post_notif_schedule====<br><br>";

                    echo "======sent to: " . $Data['email'] . "====<br><br>";
                } else {
                    echo "======No difference is found====<br><br>";
                }

                $Query = "UPDATE accounts SET  `dashboard_email_last_sent` = '$todaysdate' WHERE id =" . $Data['id'];

                $runQuery = mysql_query($Query);
                if ($runQuery) echo "======updated dashboard_email_last_sent to account: " . $Data['id'] . "====<br><br>";

                $Query = "UPDATE account_usernames SET  `last_checkusers_id_done` = '$checkUserId' WHERE id =" . $accountUserID;

                $runQuery = mysql_query($Query);
                if ($runQuery) echo "======updated last_checkusers_id_done to account: " . $Data['id'] . "====<br><br>";
            } else {
                echo "======Data Not found for : " . $userName . "====<br><br>";
                $Query = "UPDATE accounts SET  `dashboard_email_last_sent` = '$todaysdate' WHERE id =" . $Data['id'];
                $runQuery = mysql_query($Query);
                if ($runQuery) echo "======updated last_checkusers_id_done to account: " . $Data['id'] . "====<br><br>";
            }
            echo "===================================================================================<br><br>";
            echo "===================================================================================<br><br>";
        }
    }

    echo '<hr>';
    }

// end superviral code 

// tikoid code
    $dbName = $tikoidDB;

    mysql_select_db($dbName , $conn);

    $getemailtemplate = file_get_contents('tikoid.com/emailtemplate/emailtemplate.html');

    $QueryRun = mysql_query("SELECT * FROM accounts WHERE viewed_dashboard = 1 AND notification_disabled = 0 AND `dashboard_email_last_sent` != '$todaysdate' LIMIT 140");

    while ($Data = mysql_fetch_array($QueryRun)) {

    echo $Data['id'] . '<br>';

    if ($Data['dashboard_email_last_sent'] != $todaysdate) { // sent once per day



        //////////////////// grab all user in account_username //////////////////////////////////////////
        $accUserQuery = mysql_query("SELECT * FROM account_usernames WHERE `account_id` = '{$Data['id']}' AND active = 1");


        while ($accData = mysql_fetch_array($accUserQuery)) {

            $userName = $accData['username'];
            $accountUserID = $accData['id'];
            echo "======Started for : $userName ====<br><br>";


            // grab stats/////////////////////
            $diffQuery = "SELECT
                            cu.id,
                            cu.followers - cu1.followers AS follower_diff , 
                            cu.avg_post_likes - cu1.avg_post_likes AS avg_post_likes_diff
                            FROM checkusers cu 
                            ,checkusers cu1 
                            WHERE cu.ig_username = '$userName'  AND
                            cu.id = (SELECT id FROM checkusers WHERE ig_username ='$userName' ORDER BY id DESC LIMIT 1) AND
                            cu1.id = (SELECT id FROM checkusers WHERE ig_username ='$userName' ORDER BY id DESC LIMIT 1,1)";

            $diffQueryRun = mysql_query($diffQuery);

            if (mysql_num_rows($diffQueryRun) > 0) {


                $accStats = mysql_fetch_array($diffQueryRun);
                $checkUserId = $accStats['id'];

                if ($accData['last_checkusers_id_done'] == $checkUserId) {
                    echo "======already sent =====<br><br>";
                    continue;
                }

                if ($accStats['follower_diff'] != 0 || $accStats['avg_post_likes_diff'] != 0) {

                    echo 'Difference found!<br>';


                    $subject = '@' . $userName . ': your latest Instagram data is ready';
                    $to = $Data['email'];

                    $token = md5(time() . $to);
                    $tokenExpiry = time() + (3 * 86400); //3 day token expiry
                    $emailHash = $Data['email_hash'];
                    $tokenHash = $Data['token_hash'];
                    $accountId = $Data['id'];


                    $insertTokenQuery = "INSERT INTO auto_login SET
                                                                        `access_token` = '$token',
                                                                        `expiry`  = '$tokenExpiry',    
                                                                        `account_id`        = '$accountId',
                                                                        `email_hash`     = '$emailHash',
                                                                        `token_hash`     = '$tokenHash',
                                                                        `added`     = '$now'";
                    $runTokenQuery = mysql_query($insertTokenQuery);
                    if ($runTokenQuery) echo "======Inserted into auto_login for account: $accountId ====<br><br>";

                    // check country
                    $country = $Data['country'];

                    switch ($country) {
                        case 'us':
                            $dashboardDomain = 'https://superviral.io/us';
                            break;
                        case 'uk':
                            $dashboardDomain = 'https://superviral.io/uk';
                            break;
                        default:
                            $dashboardDomain = 'https://superviral.io';
                            break;
                    }


                    // send email

                    if ($Data['country'] !== 'ww') $loclinkforward = $Data['country'] . '/';
                    if ($loclinkforward == '/') $loclinkforward = '';

                    $emailbody = '
                    <p>As you might remember, your Instagram account data updates every day. It\'s time to check if anything has changed for your Instagram account.</p><br>

                    <p>Checking your Instagram data can help you see if you\'re growing or not.
                    </p><br>

                    <a href="' . $dashboardDomain . '/' . $loclinkforward . 'login/?accessToken=' . $token . '" target="_blank" style="color: #2e00f4;
                    border: 2px solid #2e00f4;display: block;
                    width: 330px;padding: 16px 9px;
                    text-decoration: none;
                    -webkit-border-radius: 45px;
                    -moz-border-radius: 45px;
                    border-radius: 45px;
                    margin: 5px auto;
                    font-weight: 700;
                    text-align:center;">See your new data &raquo;</a>


                    <br>

                    <p style="text-align:center;"><a href="' . $dashboardDomain . '/turn-off-dashboard-notifs.php?id=' . $Data['email_hash'] . '" target="_blank">Edit new data notifications</a></p>';


                    unset($loclinkforward);

                    $tpl = $getemailtemplate;
                    $tpl = str_replace('{body}', $emailbody, $tpl);
                    $tpl = str_replace('{subject}', $subject, $tpl);
                    $tpl = str_replace('<a href="https://superviral.io/unsubscribe.php?unsub=now&id={md5unsub}">Unsubscribe', '', $tpl);

                    $to = addslashes($to);
                    $userName = addslashes($userName);
                    $subject = addslashes($subject);
                    $tpl = addslashes($tpl);

                    $userQueryRun = mysql_query("SELECT *
                                                    FROM users  WHERE emailaddress = '$to' LIMIT 1");
                    $userData = mysql_fetch_array($userQueryRun);

                    $beginOfDay = strtotime("today", time());
                    $userHighConvTime = intval($userData['highestconvtime']) * 3600;
                    $scheduledfor = $beginOfDay + $userHighConvTime;
                    if (time() > $scheduledfor) {
                        $tomorrowTime = strtotime('tomorrow 00:00');
                        $scheduledfor = $tomorrowTime + $userHighConvTime;
                    }

                    $insertMailQuery = "INSERT INTO post_notif_schedule SET
                                                                        `to` = '$to',
                                                                        `scheduled_for`  = '$scheduledfor',    
                                                                        `subject`        = '$subject',
                                                                        `email_body`     = '$tpl',
                                                                        `account_id`     =  '$accountId',
                                                                        `ig_username`    =  '$userName'";


                    $runQuery = mysql_query($insertMailQuery);
                    if ($runQuery) echo "======Inserted into post_notif_schedule====<br><br>";

                    echo "======sent to: " . $Data['email'] . "====<br><br>";
                } else {
                    echo "======No difference is found====<br><br>";
                }

                $Query = "UPDATE accounts SET  `dashboard_email_last_sent` = '$todaysdate' WHERE id =" . $Data['id'];

                $runQuery = mysql_query($Query);
                if ($runQuery) echo "======updated dashboard_email_last_sent to account: " . $Data['id'] . "====<br><br>";

                $Query = "UPDATE account_usernames SET  `last_checkusers_id_done` = '$checkUserId' WHERE id =" . $accountUserID;

                $runQuery = mysql_query($Query);
                if ($runQuery) echo "======updated last_checkusers_id_done to account: " . $Data['id'] . "====<br><br>";
            } else {
                echo "======Data Not found for : " . $userName . "====<br><br>";
                $Query = "UPDATE accounts SET  `dashboard_email_last_sent` = '$todaysdate' WHERE id =" . $Data['id'];
                $runQuery = mysql_query($Query);
                if ($runQuery) echo "======updated last_checkusers_id_done to account: " . $Data['id'] . "====<br><br>";
            }
            echo "===================================================================================<br><br>";
            echo "===================================================================================<br><br>";
        }
    }

    echo '<hr>';
    }

// end tikoid code 

// feedbuzz code
    $dbName = $feedbuzzDB;

    mysql_select_db($dbName , $conn);

    $getemailtemplate = file_get_contents('feedbuzz.io/emailtemplate/emailtemplate.html');

    $QueryRun = mysql_query("SELECT * FROM accounts WHERE viewed_dashboard = 1 AND notification_disabled = 0 AND `dashboard_email_last_sent` != '$todaysdate' LIMIT 140");

    while ($Data = mysql_fetch_array($QueryRun)) {

    echo $Data['id'] . '<br>';

    if ($Data['dashboard_email_last_sent'] != $todaysdate) { // sent once per day



        //////////////////// grab all user in account_username //////////////////////////////////////////
        $accUserQuery = mysql_query("SELECT * FROM account_usernames WHERE `account_id` = '{$Data['id']}' AND active = 1");


        while ($accData = mysql_fetch_array($accUserQuery)) {

            $userName = $accData['username'];
            $accountUserID = $accData['id'];
            echo "======Started for : $userName ====<br><br>";


            // grab stats/////////////////////
            $diffQuery = "SELECT
                            cu.id,
                            cu.followers - cu1.followers AS follower_diff , 
                            cu.avg_post_likes - cu1.avg_post_likes AS avg_post_likes_diff
                            FROM checkusers cu 
                            ,checkusers cu1 
                            WHERE cu.ig_username = '$userName'  AND
                            cu.id = (SELECT id FROM checkusers WHERE ig_username ='$userName' ORDER BY id DESC LIMIT 1) AND
                            cu1.id = (SELECT id FROM checkusers WHERE ig_username ='$userName' ORDER BY id DESC LIMIT 1,1)";

            $diffQueryRun = mysql_query($diffQuery);

            if (mysql_num_rows($diffQueryRun) > 0) {


                $accStats = mysql_fetch_array($diffQueryRun);
                $checkUserId = $accStats['id'];

                if ($accData['last_checkusers_id_done'] == $checkUserId) {
                    echo "======already sent =====<br><br>";
                    continue;
                }

                if ($accStats['follower_diff'] != 0 || $accStats['avg_post_likes_diff'] != 0) {

                    echo 'Difference found!<br>';


                    $subject = '@' . $userName . ': your latest Instagram data is ready';
                    $to = $Data['email'];

                    $token = md5(time() . $to);
                    $tokenExpiry = time() + (3 * 86400); //3 day token expiry
                    $emailHash = $Data['email_hash'];
                    $tokenHash = $Data['token_hash'];
                    $accountId = $Data['id'];


                    $insertTokenQuery = "INSERT INTO auto_login SET
                                                                        `access_token` = '$token',
                                                                        `expiry`  = '$tokenExpiry',    
                                                                        `account_id`        = '$accountId',
                                                                        `email_hash`     = '$emailHash',
                                                                        `token_hash`     = '$tokenHash',
                                                                        `added`     = '$now'";
                    $runTokenQuery = mysql_query($insertTokenQuery);
                    if ($runTokenQuery) echo "======Inserted into auto_login for account: $accountId ====<br><br>";

                    // check country
                    $country = $Data['country'];

                    switch ($country) {
                        case 'us':
                            $dashboardDomain = 'https://superviral.io/us';
                            break;
                        case 'uk':
                            $dashboardDomain = 'https://superviral.io/uk';
                            break;
                        default:
                            $dashboardDomain = 'https://superviral.io';
                            break;
                    }


                    // send email

                    if ($Data['country'] !== 'ww') $loclinkforward = $Data['country'] . '/';
                    if ($loclinkforward == '/') $loclinkforward = '';

                    $emailbody = '
                    <p>As you might remember, your Instagram account data updates every day. It\'s time to check if anything has changed for your Instagram account.</p><br>

                    <p>Checking your Instagram data can help you see if you\'re growing or not.
                    </p><br>

                    <a href="' . $dashboardDomain . '/' . $loclinkforward . 'login/?accessToken=' . $token . '" target="_blank" style="color: #2e00f4;
                    border: 2px solid #2e00f4;display: block;
                    width: 330px;padding: 16px 9px;
                    text-decoration: none;
                    -webkit-border-radius: 45px;
                    -moz-border-radius: 45px;
                    border-radius: 45px;
                    margin: 5px auto;
                    font-weight: 700;
                    text-align:center;">See your new data &raquo;</a>


                    <br>

                    <p style="text-align:center;"><a href="' . $dashboardDomain . '/turn-off-dashboard-notifs.php?id=' . $Data['email_hash'] . '" target="_blank">Edit new data notifications</a></p>';


                    unset($loclinkforward);

                    $tpl = $getemailtemplate;
                    $tpl = str_replace('{body}', $emailbody, $tpl);
                    $tpl = str_replace('{subject}', $subject, $tpl);
                    $tpl = str_replace('<a href="https://superviral.io/unsubscribe.php?unsub=now&id={md5unsub}">Unsubscribe', '', $tpl);

                    $to = addslashes($to);
                    $userName = addslashes($userName);
                    $subject = addslashes($subject);
                    $tpl = addslashes($tpl);

                    $userQueryRun = mysql_query("SELECT *
                                                    FROM users  WHERE emailaddress = '$to' LIMIT 1");
                    $userData = mysql_fetch_array($userQueryRun);

                    $beginOfDay = strtotime("today", time());
                    $userHighConvTime = intval($userData['highestconvtime']) * 3600;
                    $scheduledfor = $beginOfDay + $userHighConvTime;
                    if (time() > $scheduledfor) {
                        $tomorrowTime = strtotime('tomorrow 00:00');
                        $scheduledfor = $tomorrowTime + $userHighConvTime;
                    }

                    $insertMailQuery = "INSERT INTO post_notif_schedule SET
                                                                        `to` = '$to',
                                                                        `scheduled_for`  = '$scheduledfor',    
                                                                        `subject`        = '$subject',
                                                                        `email_body`     = '$tpl',
                                                                        `account_id`     =  '$accountId',
                                                                        `ig_username`    =  '$userName'";


                    $runQuery = mysql_query($insertMailQuery);
                    if ($runQuery) echo "======Inserted into post_notif_schedule====<br><br>";

                    echo "======sent to: " . $Data['email'] . "====<br><br>";
                } else {
                    echo "======No difference is found====<br><br>";
                }

                $Query = "UPDATE accounts SET  `dashboard_email_last_sent` = '$todaysdate' WHERE id =" . $Data['id'];

                $runQuery = mysql_query($Query);
                if ($runQuery) echo "======updated dashboard_email_last_sent to account: " . $Data['id'] . "====<br><br>";

                $Query = "UPDATE account_usernames SET  `last_checkusers_id_done` = '$checkUserId' WHERE id =" . $accountUserID;

                $runQuery = mysql_query($Query);
                if ($runQuery) echo "======updated last_checkusers_id_done to account: " . $Data['id'] . "====<br><br>";
            } else {
                echo "======Data Not found for : " . $userName . "====<br><br>";
                $Query = "UPDATE accounts SET  `dashboard_email_last_sent` = '$todaysdate' WHERE id =" . $Data['id'];
                $runQuery = mysql_query($Query);
                if ($runQuery) echo "======updated last_checkusers_id_done to account: " . $Data['id'] . "====<br><br>";
            }
            echo "===================================================================================<br><br>";
            echo "===================================================================================<br><br>";
        }
    }

    echo '<hr>';
    }

// end feedbuzz code 