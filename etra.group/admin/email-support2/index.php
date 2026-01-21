<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('layout.html');

$type = addslashes($_GET['type']);

$main_heading = 'Require Action';

if($type == 'action'){
    $content = file_get_contents('tpl.html');
}else if($type == 'review'){
    $content = file_get_contents('tpl_send.html');
    $main_heading = 'Review & Send';
}else{
    header('Location: ?type=action');
    die;
}


$refund = addslashes($_POST['refund']);  

if(!empty($refund)){

    $orderid = addslashes($_POST['rid']);
    $amount = addslashes($_POST['ramount']);
    $reason = addslashes($_POST['reason']);

    // echo $orderid . ' ' . $amount;die;

    if($orderid == '' || $amount == '' || $reason == ''){
        $refundError = "<span style='color:red;'>Please fill all the fields</span>";
    }else{
        $updatethisq = mysql_query("UPDATE `orders` SET 
        `refund` = '1', `refundamount` = '$amount', `refundreason`='".addslashes($reason)."'  
         WHERE `id` = '$orderid' LIMIT 1");
    
        if ($updatethisq) {
            header("Location: /admin/email-support2/?type=review&refSuccess=refSuccess");
        }
        die;
    }

}

$refSucess = addslashes($_GET['refSuccess']);

if(empty($refSucess)){
    $refSucess = 'display: none;';
    $onrefSucess = '';
}else{
    $refSucess = "";
    $onrefSucess = "display: none;";
}


$toemoji = '✋';
$adminemoji  = '👴';
$supportemoji = '😊';

$blockedTitle = "Block";
// CENTER BODY BIND /////////////////////////////////////////////////////////


$mQuery = "SELECT
                                                id,
                                                `from`,
                                                `emailUid`,
                                                CASE 
                                                WHEN `email_formatted` IS NOT NULL AND 
                                                TRIM(`email_formatted`) != '' 
                                                AND `email_formatted` != '0' THEN `email_formatted`
                                                ELSE `email`
                                                END AS `reply`,
                                                `subject`,
                                                `emailDate` as `dateAdded`,
                                                `hideMessage`,
                                                `block`,
                                                `brand`,
                                                system_checks,
                                                category
                                                FROM email_queue
                                                    WHERE
                                                (recommended_actions IS NOT NULL)
                                                    AND 
                                                (selected_generated_tpl IS NULL)
                                                    AND 
                                                (custom_tpl IS NULL)
                                                    AND
                                                (spam_level IS NULL OR spam_level = '')
                                                    AND
                                                markDone = '0'
						    AND
                                                (category IS NOT NULL OR category != '')
                                                    AND    
                                                `block` = '0'
                                                    AND
                                                `submitReport` = '0'
                                                    AND
                                                `spam_level` != 'High'
						AND
                                                `from` NOT LIKE '%amazonses%'
                                                    AND
                                                emailDate >= unix_timestamp(CURRENT_DATE - interval 3 month) GROUP BY `from`  ORDER BY category,dateAdded asc
                        ";

$aQuery = $mQuery . " LIMIT 1";
$Query = mysql_query($aQuery);
$emailListCount = mysql_num_rows($Query);

$totalActionCount  = mysql_num_rows(mysql_query($mQuery));

// only for admin count 
$adQuery = 
"SELECT
                                             emailaddress AS emailId, eq.brand
                                            FROM admin_notifications an
                                            INNER JOIN email_queue eq 
                                                ON an.emailaddress = eq.from
                                            WHERE emailaddress 
                                                NOT IN 
                                            (SELECT `from` FROM email_queue WHERE submitReport = '0') AND
                                            directions <> ''
                                            AND done = '1' AND type = 'emailSupport' AND markDone = '0' 
														  AND (recommended_actions IS NOT NULL) AND (selected_generated_tpl IS NULL OR selected_generated_tpl= '') 
                                                      AND (custom_tpl IS NULL)
                                            -- AND block = '0'
                                            GROUP BY emailaddress
                                            ORDER BY an.id ASC ";
$totalAdminActionCount  = mysql_num_rows(mysql_query($adQuery));

$totalActionCount = $totalActionCount + $totalAdminActionCount;

if($emailListCount == 0){
    $adQuery = 
        "SELECT
                                              *, CASE 
                                                WHEN `email_formatted` IS NOT NULL AND 
                                                TRIM(`email_formatted`) != '' 
                                                AND `email_formatted` != '0' THEN `email_formatted`
                                                ELSE `email`
                                                END AS `reply`
                                            FROM admin_notifications an
                                            INNER JOIN email_queue eq 
                                                ON an.emailaddress = eq.from
                                            WHERE emailaddress 
                                                NOT IN 
                                            (SELECT `from` FROM email_queue WHERE submitReport = '0') AND
                                            directions <> ''
                                            AND done = '1' AND type = 'emailSupport' AND markDone = '0' 
														  AND (recommended_actions IS NOT NULL) AND (selected_generated_tpl IS NULL OR selected_generated_tpl= '') 
                                                      AND (custom_tpl IS NULL)
                                            -- AND block = '0'
                                            GROUP BY emailaddress
                                            ORDER BY an.id ASC ";
    
    //echo mysql_num_rows($Query);
    $adlQuery = $adQuery . " LIMIT 1";
    $Query = mysql_query($adlQuery);
    $allResponseAdminEmailCount = mysql_num_rows($Query);
    $totalActionCount  = mysql_num_rows(mysql_query($adQuery));
}

// redirect to review page if no action is pending
if($totalActionCount == 0 && $type == 'action'){

    header("Location: /admin/email-support2/?type=review");
    die;
}

if(!empty($type) && $type == 'review'){
    $allResponseAdminEmailCount = 0;
    $emailListCount = 0;
}


if (($allResponseAdminEmailCount == 0 && $emailListCount == 0 )|| $type == 'action') {

    $m1Query = "SELECT 
    eq.*, CASE 
                                                WHEN `email_formatted` IS NOT NULL AND 
                                                TRIM(`email_formatted`) != '' 
                                                AND `email_formatted` != '0' THEN `email_formatted`
                                                ELSE `email`
                                                END AS `reply`
FROM
    email_queue eq
WHERE
    eq.action_done = 1 AND eq.markDone = '0'
        AND eq.block = '0'
        AND NOT EXISTS( SELECT 
            1
        FROM
            admin_notifications an
        WHERE
            an.emailaddress = eq.from
                AND NOT EXISTS( SELECT 
                    1
                FROM
                    email_queue sub_eq
                WHERE
                    sub_eq.from = an.emailaddress
                        AND sub_eq.submitReport = '0')
                AND an.directions <> ''
                AND an.done = '1'
                AND an.`type` = 'emailSupport'
                AND eq.markDone = '0'
                AND eq.recommended_actions IS NOT NULL
                AND (eq.selected_generated_tpl IS NULL
                OR eq.selected_generated_tpl = '')
                AND eq.custom_tpl IS NULL)
        AND eq.emailDate >= UNIX_TIMESTAMP(CURRENT_DATE - INTERVAL 2 MONTH)
ORDER BY eq.category , eq.dateAdded ASC 
";

    // after actions selected
    $rQuery = $m1Query . " LIMIT 1";
    if(empty($type) || $type == 'review'){
        $Query = mysql_query($rQuery);
        $content = file_get_contents('tpl_send.html');
    }
    $totalReviewCount  = mysql_num_rows(mysql_query($m1Query));
}

$tpl = str_replace("{content}", $content, $tpl);

$emailListCount = 0;
// $i=0;
$allCustomerWiseEmailsHtml = "";
$customerOrderHtml = "";
$IPcustomerOrderHtml = "";
$NSOrderHtml = "";
$responseOnEmailArr = [];
$resArr = mysql_fetch_array($Query);
$email = $resArr['from'];
$ticketId = $resArr['id'];
$category = $resArr['category'];
$country = $resArr['country'];
$system_checks = $resArr['system_checks'];
if($system_checks == ''){
    $system_checks = 'orders,account_details,autolikes';
}
$system_checksArr = explode(",", $system_checks);
$system_checksArr = array_map('trim', $system_checksArr);

$selected_generated_tpl = $resArr['selected_generated_tpl'];

if($resArr['custom_tpl'] != null){
    
    $selected_generated_tpl = $resArr['custom_tpl'];

}

// if (!empty($resArr))
    // $responseOnEmailArr[] = $resArr;
// $recentSubject = $resArr["subject"];
// $brand = $resArr["brand"];

// $globLastEmailUid = $resArr["emailUid"];
// if ($resArr['block'] == '1') {
//     $blockedTitle = "Unblock";
//     $isBlocked = 0;
// } else {
//     $blockedTitle = "Block";
//     $isBlocked = 1;
// }
// chat history

$Query = mysql_query("SELECT
id,
`emailUid`,
CASE 
                                                WHEN `email_formatted` IS NOT NULL AND 
                                                TRIM(`email_formatted`) != '' 
                                                AND `email_formatted` != '0' THEN `email_formatted`
                                                ELSE `email`
                                                END AS `reply`,
`subject`,
`emailDate` as `dateAdded`,
`hideMessage`,
`block`
FROM email_queue
    WHERE 
    -- markDone = '0'
--  AND `block` = '0'
--   AND `submitReport` = '0'
`from` = '$email'
AND emailDate >= unix_timestamp(CURRENT_DATE - interval 2 month );
");

while ($resArr = mysql_fetch_array($Query)) {
    $responseOnEmailArr[] = $resArr;
    $recentSubject = $resArr["subject"];
    $recentSubject = $string = str_replace(array("\n", "\r"), ' ', $recentSubject);
    $recentSubject = $string = str_replace(array("\n", "\r", '"'), ' ', $recentSubject);
    $brand = $resArr["brand"];

    $globLastEmailUid = $resArr["emailUid"];
    if ($resArr['block'] == '1') {
        $blockedTitle = "Unblock";
        $isBlocked = 0;
    } else {
        $blockedTitle = "Block";
        $isBlocked = 1;
    }
    // support response
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
                                WHERE emailUid = '" . $resArr["emailUid"] . "'
                            AND dateAdded >= unix_timestamp(CURRENT_DATE - interval 1 month )
                        ");


    $firstDisplayChar = $recentSubject;
    while ($resArrSupport = mysql_fetch_array($QuerySupport)) {
        $responseOnEmailArr[] = $resArrSupport;
    }
    // Admin response
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
                                                                WHERE emailUid = '" . $resArr["emailUid"] . "'
                                                            AND response >= unix_timestamp(CURRENT_DATE - interval 1 month )
                        ");

    while ($resArrAdmin = mysql_fetch_array($QueryAdmin)) {
        $responseOnEmailArr[] = $resArrAdmin;
    }
}


// // support response
// $QuerySupport = mysql_query("SELECT
//                                  id,
//                                 `to`,
//                                 `emailUid`,
//                                 `reply`,
//                                 `from`,
//                                 `dateAdded`,
//                                 hideMessage,
//                                 'support' as `type`
//                                 FROM email_support_replies
//                                     WHERE emailUid = '" . $resArr["emailUid"] . "'
//                                 AND dateAdded >= unix_timestamp(CURRENT_DATE - interval 1 month )
//                             ");


// $firstDisplayChar = $recentSubject;
// while ($resArrSupport = mysql_fetch_array($QuerySupport)) {
//     $responseOnEmailArr[] = $resArrSupport;
// }
// // Admin response
// $QueryAdmin = mysql_query("SELECT
//                                                                  id,
//                                                                  `message` as initialmsg,
//                                                                 `emailaddress` as `to`,
//                                                                 `emailUid`,
//                                                                 `directions` as reply,
//                                                                 `response` as dateAdded,
//                                                                 hideMessage,
//                                                                 'admin' as `type`
//                                                                 FROM admin_notifications
//                                                                     WHERE emailUid = '" . $resArr["emailUid"] . "'
//                                                                 AND response >= unix_timestamp(CURRENT_DATE - interval 1 month )
//                             ");

// while ($resArrAdmin = mysql_fetch_array($QueryAdmin)) {
//     $responseOnEmailArr[] = $resArrAdmin;
// }

// $countRcecord = mysql_num_rows($QueryAdmin);
// if($countRcecord > 0){
$countResponseArr = count($responseOnEmailArr);
$time = array_column($responseOnEmailArr, 'dateAdded');

$sortedArr = array_multisort($time, SORT_ASC, $responseOnEmailArr);
// }

if ($countResponseArr > 0) {
    $responses = '';
    for ($j = 0; $j < $countResponseArr; $j++) {

        if ($responseOnEmailArr[$j]["type"] == "support") {
            $btnColor = "#d7ffff";
            $btnColor = "#fff";
            $emailType = "'support'";
            $emoji = $supportemoji;
            $fromClass = "";
            $divClass = 'support';
        } else if ($responseOnEmailArr[$j]["type"] == "admin") {
            $btnColor = "#d9ffc4";
            $btnColor = "#fff";
            $emailType = "'admin'";
            $emoji = $adminemoji;
            $initialsupportmessage = '<div class="initialmsg">' . $responseOnEmailArr[$j]['initialmsg'] . '</div>';
            $fromClass = "";
            $divClass = 'admin';
        } else {
            $emailType = "''";
            $emoji = $toemoji;
            $fromClass = "recip";
            $divClass = 'customer';
        }
        $emailListCount++;
        if ($responseOnEmailArr[$j]["hideMessage"] == "1") {
            $classForDisplayMessage = "";
            $btnText = "Show";
            $bgColorClass = "hidemessagefull";
        } else {
            $classForDisplayMessage = "";
            $btnText = "Hide";
            $bgColorClass = "showmessagefull";
        }



        $cleanemailbody = $responseOnEmailArr[$j]['reply'];

        if (strpos($cleanemailbody, '<customer-care@superviral.io>') !== false) {
            $cleanemailbody = explode('<customer-care@superviral.io>', $cleanemailbody);
            $cleanemailbody = $cleanemailbody[0];
        }


        if (strpos($cleanemailbody, 'orders@superviral.io>') !== false) {
            $cleanemailbody = explode('<orders@superviral.io>', $cleanemailbody);
            $cleanemailbody = $cleanemailbody[0];
        }

        if (strpos($cleanemailbody, 'support@superviral.io>') !== false) {
            $cleanemailbody = explode('<support@superviral.io>', $cleanemailbody);
            $cleanemailbody = $cleanemailbody[0];
        }

        $timestamp = date('d/n', $responseOnEmailArr[$j]['dateAdded']);

        $responses .= ' <div class="' . $divClass . '">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <b class="label">' . $emoji . ucfirst($divClass) . '</b>
                        <span style="font-size:13px;">'.$timestamp.'</span>
                    </div>
                    <p>' . $cleanemailbody . '</p>
                </div>';

        // $emailUnixTime = gmdate("H:i d-m-y", $responseOnEmailArr[$j]["dateAdded"]);
        // $NextDisplayChar = '<span class="' . $classForDisplayMessage . '" id="NextDisplayChar' .
        // $emailListCount . '">' . $cleanemailbody . '</span><br><span><button class="btn3"  id="copyBtn' .$emailListCount . '" onclick="callCopyFunc('. $emailListCount .')">Copy Message</button></span>';

        // $onclickParam = $emailListCount . ',\'\', ' . $responseOnEmailArr[$j]["id"] . ', ' . $emailType;    

        unset($cleanemailbody);
        unset($initialsupportmessage);
    }

    $RAHtml = '';
    $Query = mysql_query("SELECT * FROM email_queue_actions WHERE email_queue_id = '$ticketId'");
/*
    $display_offer_likes = 'display:none;';
    $display_offer_followers = 'display:none;';
    $display_offer_refund = 'display:none;';
*/
    while ($ActionsArr = mysql_fetch_array($Query)) {

        if(ucwords(trim($ActionsArr['action'])) == 'Offer Likes' && $ActionsArr['selected'] == 1){$display_offer_likes = 'display:block;';}
        if(ucwords(trim($ActionsArr['action'])) == 'Offer Followers' && $ActionsArr['selected'] == 1){$display_offer_likes = 'display:block;';}
        if(ucwords(trim($ActionsArr['action'])) == 'Offer Followers' && $ActionsArr['selected'] == 1){$display_offer_followers = 'display:block;';}
        if(ucwords(trim($ActionsArr['action'])) == 'Offer Likes' && $ActionsArr['selected'] == 1){$display_offer_followers = 'display:block;';}
        if(ucwords(trim($ActionsArr['action'])) == 'Offer Refill' && $ActionsArr['selected'] == 1){$display_offer_likes = 'display:block;';$display_offer_followers = 'display:block;';}
        if(ucwords(trim($ActionsArr['action'])) == 'Offer Refund' && $ActionsArr['selected'] == 1){$display_offer_refund = 'display:block;';}

        $RASnippet = tpl_get('recommended_actions', $tpl);
        $RASnippet = str_replace("{actionName}", ucfirst(trim($ActionsArr['action'])), $RASnippet);
        $RASnippet = str_replace("{actionId}", $ActionsArr['id'], $RASnippet);
        $RASnippet = str_replace("{ticketId}", $ticketId, $RASnippet);
        $RAHtml .= $RASnippet;
    }
}else{
    $responses = "All Caught Up!";
    $done =  "All Caught Up!";
}
if ($globLastEmailUid == "" || $globLastEmailUid == null) {
    $globLastEmailUid = 0;
}

$Query = mysql_query("SELECT * FROM accounts WHERE email = '$email' order by id desc limit 1");
$accArr = mysql_fetch_array($Query);
$accAdded = ago($accArr['added']);
$username = $accArr['username'];    
$account_id = $accArr['id'];    
$IsAccountExist = mysql_num_rows($Query);   

if (in_array('account_details', $system_checksArr, true)) {
    if ($IsAccountExist == 1) {
        $noAccount = 'display:none;';  
        $customerInfo = 'display:block;';
    } else {
        $noAccount = 'display:block;';    
        $customerInfo = 'display:none;';
    }
}else{
    $noAccountsDisplay = 'display:none;';
}



if (in_array('autolikes', $system_checksArr, true)) {

    $q = "SELECT * FROM automatic_likes WHERE emailaddress = '$email' ";
    $q1 = $q . '  order by id desc';
    $Query = mysql_query($q1);
    
    $alHTML = '';
    $alfHTML = '';

    if(mysql_num_rows($Query) > 0){
        $displayEmptyALSub = 'display:none;';
        while ($alArr = mysql_fetch_array($Query)) {
            $alSinppet = tpl_get('al_sub', $tpl);

            if ($alArr['disabled'] == 1 && $alArr['expires'] != time()) {

                $dispPauseBtn = 'display:none;';
                $dispPlayBtn = 'display:inline-flex';
            }  //show the green button

            if ($alArr['disabled'] == 1 || $alArr['expires'] < time()) {

                $dispPauseBtn = 'display:inline-flex';
                $dispPlayBtn = 'display:none;';
                $al_expiry = 'PAUSED';
                $expiry_date_style = 'color: grey;';
            } //show the red button

            if ($alArr['disabled'] == 0 && $alArr['expires'] > time()) {
                // 'Expiry: ' .date("M d, Y", $alArr['expires']);
                $al_expiry = 'ACTIVE';
                $dispPauseBtn = 'display:none;';
                $dispPlayBtn = 'display:inline-flex';
                $expiry_date_style = 'color: green;';
            } else if ($alArr['expires'] < time()) {
                $al_expiry = 'EXPIRED';
                $expiry_date_style = 'color: red;';
            }

            $alSinppet = str_replace("{al_username}", $alArr['igusername'], $alSinppet);
            $alSinppet = str_replace("{amount_type}",  $alArr['likes_per_post'] . ' Likes', $alSinppet);
            $alSinppet = str_replace("{al_expiry}",  $al_expiry, $alSinppet);
            $alSinppet = str_replace("{dispPauseBtn}", $dispPauseBtn, $alSinppet);
            $alSinppet = str_replace("{expiry_date_style}", $expiry_date_style, $alSinppet);
            $alSinppet = str_replace("{dispPlayBtn}",  $dispPlayBtn, $alSinppet);
            $alHTML .= $alSinppet;

            $alfQuery = mysql_query("SELECT al.igusername, al.likes_per_post, alf.expires  FROM automatic_likes al inner join automatic_likes_fulfill alf on al.id = alf.auto_likes_id WHERE auto_likes_id = {$alArr['id']} ORDER BY alf.id DESC LIMIT 5;");
    
            if(mysql_num_rows($alfQuery) > 0){
                
                while($alfArr = mysql_fetch_array($alfQuery)){
                
                    $alfSinppet = tpl_get('al_issued', $tpl);
                
                    $alfSinppet = str_replace("{username}", $alfArr['igusername'], $alfSinppet);
                    $alfSinppet = str_replace("{amount}",  $alfArr['likes_per_post'], $alfSinppet);
                    $alfSinppet = str_replace("{alf_expiry}",  date('d M',$alfArr['expires']),$alfSinppet);
                    $alfHTML .= $alfSinppet;
                
                }
            }
            
            if(!empty($alfHTML)){
                $displayEmptyALOrders = 'display:none;';
            }else{
                $displayEmptyALOrders = 'display:block;';
            }
        }

    }else{
        $displayEmptyALSub = 'display:block;';
    }

    $q2 = $q . ' AND expires > ' . time() . ' order by id desc';
    $Query = mysql_query($q2);

    $ualHTML = '';
    if(mysql_num_rows($Query) > 0){
        while ($ualArr = mysql_fetch_array($Query)) {
            $ualSinppet = tpl_get('update_al_ui', $tpl);

            if($ualArr['disabled'] == 1){
                $ualSinppet = str_replace("{p_selected}", 'selected' , $ualSinppet);
                $ualSinppet = str_replace("{a_selected}", '' , $ualSinppet);
            }else{
                $ualSinppet = str_replace("{a_selected}", 'selected' , $ualSinppet);
                $ualSinppet = str_replace("{p_selected}", '' , $ualSinppet);
            } 
            

            $ualSinppet = str_replace("{al_uname}", $ualArr['igusername'], $ualSinppet);
            $ualSinppet = str_replace("{al_order}",  $ualArr['likes_per_post'] . ' Likes', $ualSinppet);
            $ualSinppet = str_replace("{al_id}",  $ualArr['id'], $ualSinppet);
            $ualHTML .= $ualSinppet;
        }

    }


}else{
    $noAlDisplay = 'display:none;';
}


if (in_array('orders', $system_checksArr, true)) {
    $noOrder = 'display:none;';
    $Query = mysql_query("SELECT *, CONCAT(amount,' ',packagetype) AS `order` FROM orders WHERE emailaddress = '$email' AND packagetype not in ('freefollowers', 'freelikes') ORDER BY `id` DESC LIMIT 30");
    if (mysql_num_rows($Query) > 0) {
        $htmOption = "<option value=''>Select Order</option>";
        $followerHtmOption = "<option value=''>Select Order</option>";
        $likeHtmOption = "<option value=''>Select Order</option>";
        $comp = 0;
        $ip = 0;
        $ns = 0;
        $isFirstRow = true;
        $usernamesArray = [];
        while ($orderArr = mysql_fetch_array($Query)) {
            $usernamesArray[] = "'" . $orderArr['igusername'] . "'";

            $orderAmount = $orderArr['amount'];
            if (empty($orderArr['curr_engagement'])) {
                $orderArr['curr_engagement'] = 0;
            }
            if (empty($orderArr['new_engagement'])) {
                $orderArr['new_engagement'] = 0;
            }

            $orderArr['curr_engagement'] = intval($orderArr['curr_engagement']);
            $orderArr['new_engagement'] = intval($orderArr['new_engagement']);
           
            $missingAmount = ($orderAmount + $orderArr['curr_engagement']) - $orderArr['new_engagement'];
            $missingAmount = max(0, $missingAmount);
            $curr_engagement = !empty($orderArr['curr_engagement']) ? $orderArr['curr_engagement'] : '--';
            $new_engagement =  !empty($orderArr['new_engagement']) ? $orderArr['new_engagement'] : '--';

            if ($curr_engagement == '--' || $new_engagement == '--') {
                $missingAmount = '';
            } else {
                $missingAmount = ' (' . $missingAmount . ')';
            }


            if ($isFirstRow) {
                $isFirstRow = false;
                $latestUserName = $orderArr['igusername'];
                $latestOrderId = $orderArr['id'];
                $latestBrand = $orderArr['brand'];
            }

            $keyword = getSocialMediaSource($orderArr['socialmedia']);
            $socialMediaLogo = "/admin/assets/icons/$keyword-icon.svg";
            $emailUnixTime = gmdate("H:i d-m-y", $orderArr["added"]);
            $orderId = $orderArr['id'];

            $url = "/admin/check-user/?orderid=$orderId";

            if (empty($orderArr['fulfilled']) && !empty($orderArr['fulfill_id'])) {

                $amounto = $orderArr['amount'];

                if ($amounto >= 1 && $amounto <= 150) {
                    $approx = '9-10 hours';
                }
                if ($amounto >= 151 && $amounto <= 250) {
                    $approx = '12-13 hours';
                }
                if ($amounto >= 251 && $amounto <= 380) {
                    $approx = '14-15 hours';
                }
                if ($amounto >= 500 && $amounto <= 999) {
                    $approx = '14-15 hours';
                }
                if ($amounto >= 1000 && $amounto <= 1500) {
                    $approx = '24-28 hours';
                }
                if ($amounto >= 2500 && $amounto <= 3750) {
                    $approx = '27-35 hours';
                }
                if ($amounto >= 5000 && $amounto <= 8000) {
                    $approx = '38-48 hours';
                }

                if (!empty($approx)) $approx1 = '(will take around ' . $approx . ')';

                $orderstatus = 'In progress';
                $customerOrderStatus = $orderstatus;

                if ($orderArr['packagetype'] == 'followers' || $orderArr['packagetype'] == 'comments') {

                    if ($orderArr['socialmedia'] == 'ig') {
                        $domain = 'https://instagram.com/';
                        $postUrl = $domain . $orderArr['igusername'] . '/';

                        if ($orderArr['packagetype'] == 'comments') {
                            $postUrl = $domain . $orderArr['igusername'] . '/p/' . trim($orderArr['chooseposts']);
                        }
                    } else {
                        $domain = 'https://tiktok.com/@';
                        $postUrl = $domain . $orderArr['igusername'] . '/';
                    }

                    $IPcustomerOrderSnippet = tpl_get('IPcustomerOrderList', $tpl);
                    $IPcustomerOrderSnippet = str_replace("{customerOrderUrl}", $url, $IPcustomerOrderSnippet);
                    $IPcustomerOrderSnippet = str_replace("{igusername}", $orderArr['igusername'], $IPcustomerOrderSnippet);
                    $IPcustomerOrderSnippet = str_replace("{customerOrder}", $orderArr["order"], $IPcustomerOrderSnippet);
                    $IPcustomerOrderSnippet = str_replace("{customerOrderId}", $orderArr["id"], $IPcustomerOrderSnippet);
                    $IPcustomerOrderSnippet = str_replace("{trackinghref}", "https://superviral.io/track-my-order/" . $orderArr['order_session'], $IPcustomerOrderSnippet);
                    $IPcustomerOrderSnippet = str_replace("{tracking}", $orderArr['order_session'], $IPcustomerOrderSnippet);
                    if ($ip == 0)
                        $IPcustomerOrderSnippet = str_replace("{customerOrderStatus}", $customerOrderStatus, $IPcustomerOrderSnippet);
                    else
                        $IPcustomerOrderSnippet = str_replace("{customerOrderStatus}", '', $IPcustomerOrderSnippet);
                    $IPcustomerOrderSnippet = str_replace("{added}", date('jS F Y ', $orderArr["added"]), $IPcustomerOrderSnippet);
                    $IPcustomerOrderSnippet = str_replace("{socialMediaLogo}", $socialMediaLogo, $IPcustomerOrderSnippet);
                    $IPcustomerOrderSnippet = str_replace("{curr_engagement}", $curr_engagement, $IPcustomerOrderSnippet);
                    $IPcustomerOrderSnippet = str_replace("{new_engagement}", $new_engagement, $IPcustomerOrderSnippet);
                    $IPcustomerOrderSnippet = str_replace("{missingAmount}", $missingAmount, $IPcustomerOrderSnippet);
                    $IPcustomerOrderSnippet = str_replace("{postUrl}", $postUrl, $IPcustomerOrderSnippet);

                    $IPcustomerOrderHtml .= $IPcustomerOrderSnippet;
                } else {

                    $posts = $orderArr['chooseposts'];
                    $multipleposts = explode(' ', $posts);
                    if (count($multipleposts) > 1) {
                        $perPost = round($orderArr['amount'] / (count($multipleposts) - 1));
                    }
                    $orderArr['order'] = $perPost . ' ' . $orderArr['packagetype'];

                    $curr_post_engage = explode(' ',$orderArr['curr_engagement']);
                    $new_post_engage = explode(' ', $orderArr['new_engagement']);
                   
                    for ($i = 0; $i < count($multipleposts) - 1; $i++) {

                        if(count($curr_post_engage) > 1){

                            if (empty($curr_post_engage[$i])) {
                                $curr_post_engage[$i] = 0;
                            }
                            if (empty($new_post_engage[$i])) {
                                $new_post_engage[$i] = 0;
                            }

                            $curr_post_engage[$i] = intval($curr_post_engage[$i]);
                            $new_post_engage[$i] = intval($new_post_engage[$i]);
                
                            $missingAmount = ($perPost + $curr_post_engage[$i]) - $new_post_engage[$i];
                            $missingAmount = max(0, $missingAmount);

                            $curr_engagement = !empty($curr_post_engage[$i]) ? $curr_post_engage[$i] : '--';
                            $new_engagement =  !empty($new_post_engage[$i]) ? $new_post_engage[$i] : '--';
                
                            if ($curr_engagement == '--' || $new_engagement == '--') {
                                $missingAmount = '';
                            } else {
                                $missingAmount = ' (' . $missingAmount . ')';
                            }
                        }
    
                        if ($orderArr['socialmedia'] == 'ig') {
                            $domain = 'https://instagram.com/';
                            $postUrl = $domain . $orderArr['igusername'] . '/p/' . $multipleposts[$i];
                        } else {
                            $postUrl = $multipleposts[$i];
                        }

                        $IPcustomerOrderSnippet = tpl_get('IPcustomerOrderList', $tpl);
                        $IPcustomerOrderSnippet = str_replace("{customerOrderUrl}", $url, $IPcustomerOrderSnippet);
                        $IPcustomerOrderSnippet = str_replace("{igusername}", $orderArr['igusername'], $IPcustomerOrderSnippet);
                        $IPcustomerOrderSnippet = str_replace("{customerOrder}", $orderArr["order"], $IPcustomerOrderSnippet);
                        $IPcustomerOrderSnippet = str_replace("{customerOrderId}", $orderArr["id"], $IPcustomerOrderSnippet);
                        $IPcustomerOrderSnippet = str_replace("{trackinghref}", "https://superviral.io/track-my-order/" . $orderArr['order_session'], $IPcustomerOrderSnippet);
                        $IPcustomerOrderSnippet = str_replace("{tracking}", $orderArr['order_session'], $IPcustomerOrderSnippet);
                        if ($ip == 0)
                            $IPcustomerOrderSnippet = str_replace("{customerOrderStatus}", $customerOrderStatus, $IPcustomerOrderSnippet);
                        else
                            $IPcustomerOrderSnippet = str_replace("{customerOrderStatus}", '', $IPcustomerOrderSnippet);
                        $IPcustomerOrderSnippet = str_replace("{added}", date('jS F Y ', $orderArr["added"]), $IPcustomerOrderSnippet);
                        $IPcustomerOrderSnippet = str_replace("{socialMediaLogo}", $socialMediaLogo, $IPcustomerOrderSnippet);
                        $IPcustomerOrderSnippet = str_replace("{curr_engagement}", $curr_engagement, $IPcustomerOrderSnippet);
                        $IPcustomerOrderSnippet = str_replace("{new_engagement}", $new_engagement, $IPcustomerOrderSnippet);
                        $IPcustomerOrderSnippet = str_replace("{missingAmount}", $missingAmount, $IPcustomerOrderSnippet);
                        $IPcustomerOrderSnippet = str_replace("{postUrl}", $postUrl, $IPcustomerOrderSnippet);
                        $IPcustomerOrderHtml .= $IPcustomerOrderSnippet;
                        $ip++;
                    }
                }
                $ip++;
            } else if (!empty($orderArr['fulfilled'])) {
                if ($comp > 2) continue;
                $orderstatus = 'Completed';
                $customerOrderStatus = $orderstatus;

                if ($orderArr['packagetype'] == 'followers' || $orderArr['packagetype'] == 'comments') {

                    if ($orderArr['socialmedia'] == 'ig') {
                        $domain = 'https://instagram.com/';
                        $postUrl = $domain . $orderArr['igusername'] . '/';

                        if ($orderArr['packagetype'] == 'comments') {
                            $postUrl = $domain . $orderArr['igusername'] . '/p/' . trim($orderArr['chooseposts']);
                        }
                    } else {
                        $domain = 'https://tiktok.com/@';
                        $postUrl = $domain . $orderArr['igusername'] . '/';
                    }


                    $customerOrderSnippet = tpl_get('ComcustomerOrderList', $tpl);
                    $customerOrderSnippet = str_replace("{customerOrderUrl}", $url, $customerOrderSnippet);
                    $customerOrderSnippet = str_replace("{customerOrder}",  $orderArr["order"], $customerOrderSnippet);
                    $customerOrderSnippet = str_replace("{customerOrderId}",  $orderArr["id"], $customerOrderSnippet);
                    $customerOrderSnippet = str_replace("{customerOrderUnixtime}", $emailUnixTime, $customerOrderSnippet);
                    $customerOrderSnippet = str_replace("{trackinghref}", "https://superviral.io/track-my-order/" . $orderArr['order_session'], $customerOrderSnippet);
                    if ($comp == 0)
                        $customerOrderSnippet = str_replace("{customerOrderStatus}", $customerOrderStatus, $customerOrderSnippet);
                    else
                        $customerOrderSnippet = str_replace("{customerOrderStatus}", '', $customerOrderSnippet);
                    $customerOrderSnippet = str_replace("{added}", date('jS F Y ', $orderArr["added"]), $customerOrderSnippet);
                    $customerOrderSnippet = str_replace("{socialMediaLogo}", $socialMediaLogo, $customerOrderSnippet);
                    $customerOrderSnippet = str_replace("{curr_engagement}", $curr_engagement, $customerOrderSnippet);
                    $customerOrderSnippet = str_replace("{new_engagement}", $new_engagement, $customerOrderSnippet);
                    $customerOrderSnippet = str_replace("{missingAmount}", $missingAmount, $customerOrderSnippet);
                    $customerOrderSnippet = str_replace("{postUrl}", $postUrl, $customerOrderSnippet);
                    $customerOrderHtml .= $customerOrderSnippet;
                } else {

                    $posts = $orderArr['chooseposts'];
                    $multipleposts = explode(' ', $posts);
                    if (count($multipleposts) > 1) {
                        $perPost = round($orderArr['amount'] / (count($multipleposts) - 1));
                    }
                    $orderArr['order'] = $perPost . ' ' . $orderArr['packagetype'];

                    $curr_post_engage = explode(' ',$orderArr['curr_engagement']);
                    $new_post_engage = explode(' ', $orderArr['new_engagement']);

                    for ($i = 0; $i < count($multipleposts) - 1; $i++) {

                        if(count($curr_post_engage) > 1){

                            if (empty($curr_post_engage[$i])) {
                                $curr_post_engage[$i] = 0;
                            }
                            if (empty($new_post_engage[$i])) {
                                $new_post_engage[$i] = 0;
                            }

                            $curr_post_engage[$i] = intval($curr_post_engage[$i]);
                            $new_post_engage[$i] = intval($new_post_engage[$i]);

                            $missingAmount = ($perPost + $curr_post_engage[$i]) - $new_post_engage[$i];
                            $missingAmount = max(0, $missingAmount);

                            $curr_engagement = !empty($curr_post_engage[$i]) ? $curr_post_engage[$i] : '--';
                            $new_engagement =  !empty($new_post_engage[$i]) ? $new_post_engage[$i] : '--';
                
                            if ($curr_engagement == '--' || $new_engagement == '--') {
                                $missingAmount = '';
                            } else {
                                $missingAmount = ' (' . $missingAmount . ')';
                            }
                        }

                        if ($orderArr['socialmedia'] == 'ig') {
                            $domain = 'https://instagram.com/';
                            $postUrl = $domain . $orderArr['igusername'] . '/p/' . $multipleposts[$i];
                        } else {
                            $postUrl = $multipleposts[$i];
                        }

                        $customerOrderSnippet = tpl_get('ComcustomerOrderList', $tpl);
                        $customerOrderSnippet = str_replace("{customerOrderUrl}", $url, $customerOrderSnippet);
                        $customerOrderSnippet = str_replace("{customerOrder}",  $orderArr["order"], $customerOrderSnippet);
                        $customerOrderSnippet = str_replace("{customerOrderId}",  $orderArr["id"], $customerOrderSnippet);
                        $customerOrderSnippet = str_replace("{customerOrderUnixtime}", $emailUnixTime, $customerOrderSnippet);
                        $customerOrderSnippet = str_replace("{trackinghref}", "https://superviral.io/track-my-order/" . $orderArr['order_session'], $customerOrderSnippet);
                        if ($comp == 0)
                            $customerOrderSnippet = str_replace("{customerOrderStatus}", $customerOrderStatus, $customerOrderSnippet);
                        else
                            $customerOrderSnippet = str_replace("{customerOrderStatus}", '', $customerOrderSnippet);
                        $customerOrderSnippet = str_replace("{added}", date('jS F Y ', $orderArr["added"]), $customerOrderSnippet);
                        $customerOrderSnippet = str_replace("{socialMediaLogo}", $socialMediaLogo, $customerOrderSnippet);
                        $customerOrderSnippet = str_replace("{curr_engagement}", $curr_engagement, $customerOrderSnippet);
                        $customerOrderSnippet = str_replace("{new_engagement}", $new_engagement, $customerOrderSnippet);
                        $customerOrderSnippet = str_replace("{missingAmount}", $missingAmount, $customerOrderSnippet);
                        $customerOrderSnippet = str_replace("{postUrl}", $postUrl, $customerOrderSnippet);
                        $customerOrderHtml .= $customerOrderSnippet;
                        $comp++;
                    }
                }
                $comp++;
            } else {
                $orderstatus = 'Not Started';
                $NSOrderStatus = $orderstatus;

                if ($orderArr['packagetype'] == 'followers' || $orderArr['packagetype'] == 'comments') {

                    if ($orderArr['socialmedia'] == 'ig') {
                        $domain = 'https://instagram.com/';
                        $postUrl = $domain . $orderArr['igusername'] . '/';

                        if ($orderArr['packagetype'] == 'comments') {
                            $postUrl = $domain . $orderArr['igusername'] . '/p/' . trim($orderArr['chooseposts']);
                        }
                    } else {
                        $domain = 'https://tiktok.com/@';
                        $postUrl = $domain . $orderArr['igusername'] . '/';
                    }

                    $NSOrderSnippet = tpl_get('NScustomerOrderList', $tpl);

                    $NSOrderSnippet = str_replace("{customerOrderUrl}", $url, $NSOrderSnippet);
                    $NSOrderSnippet = str_replace("{customerOrderId}",  $orderArr["id"], $NSOrderSnippet);
                    $NSOrderSnippet = str_replace("{customerOrder}",  $orderArr["order"], $NSOrderSnippet);
                    $NSOrderSnippet = str_replace("{customerOrderUnixtime}", $emailUnixTime, $NSOrderSnippet);
                    $NSOrderSnippet = str_replace("{trackinghref}", "https://superviral.io/track-my-order/" . $orderArr['order_session'], $NSOrderSnippet);
                    if ($ns == 0)
                        $NSOrderSnippet = str_replace("{customerOrderStatus}", $NSOrderStatus, $NSOrderSnippet);
                    else
                        $NSOrderSnippet = str_replace("{customerOrderStatus}", '', $NSOrderSnippet);
                    $NSOrderSnippet = str_replace("{added}", date('jS F Y ', $orderArr["added"]), $NSOrderSnippet);
                    $NSOrderSnippet = str_replace("{socialMediaLogo}", $socialMediaLogo, $NSOrderSnippet);
                    $NSOrderSnippet = str_replace("{curr_engagement}", $curr_engagement, $NSOrderSnippet);
                    $NSOrderSnippet = str_replace("{new_engagement}", $new_engagement, $NSOrderSnippet);
                    $NSOrderSnippet = str_replace("{missingAmount}", $missingAmount, $NSOrderSnippet);
                    $NSOrderSnippet = str_replace("{postUrl}", $postUrl, $NSOrderSnippet);
                    $NSOrderHtml .= $NSOrderSnippet;
                } else {

                    $posts = $orderArr['chooseposts'];
                    $multipleposts = explode(' ', $posts);
                    if (count($multipleposts) > 1) {
                        $perPost = round($orderArr['amount'] / (count($multipleposts) - 1));
                    }
                    $orderArr['order'] = $perPost . ' ' . $orderArr['packagetype'];

                    $curr_post_engage = explode(' ',$orderArr['curr_engagement']);
                    $new_post_engage = explode(' ', $orderArr['new_engagement']);

                    for ($i = 0; $i < count($multipleposts) - 1; $i++) {

                        if(count($curr_post_engage) > 1){

                            if (empty($curr_post_engage[$i])) {
                                $curr_post_engage[$i] = 0;
                            }
                            if (empty($new_post_engage[$i])) {
                                $new_post_engage[$i] = 0;
                            }

                            $curr_post_engage[$i] = intval($curr_post_engage[$i]);
                            $new_post_engage[$i] = intval($new_post_engage[$i]);

                            $missingAmount = ($perPost + $curr_post_engage[$i]) - $new_post_engage[$i];
                            $missingAmount = max(0, $missingAmount);
                            
                            $curr_engagement = !empty($curr_post_engage[$i]) ? $curr_post_engage[$i] : '--';
                            $new_engagement =  !empty($new_post_engage[$i]) ? $new_post_engage[$i] : '--';
                
                            if ($curr_engagement == '--' || $new_engagement == '--') {
                                $missingAmount = '';
                            } else {
                                $missingAmount = ' (' . $missingAmount . ')';
                            }
                        }

                        if ($orderArr['socialmedia'] == 'ig') {
                            $domain = 'https://instagram.com/';
                            $postUrl = $domain . $orderArr['igusername'] . '/p/' . $multipleposts[$i];
                        } else {
                            $postUrl = $multipleposts[$i];
                        }

                        $NSOrderSnippet = tpl_get('NScustomerOrderList', $tpl);
                        $NSOrderSnippet = str_replace("{customerOrderUrl}", $url, $NSOrderSnippet);
                        $NSOrderSnippet = str_replace("{customerOrderId}",  $orderArr["id"], $NSOrderSnippet);
                        $NSOrderSnippet = str_replace("{customerOrder}",  $orderArr["order"], $NSOrderSnippet);
                        $NSOrderSnippet = str_replace("{customerOrderUnixtime}", $emailUnixTime, $NSOrderSnippet);
                        $NSOrderSnippet = str_replace("{trackinghref}", "https://superviral.io/track-my-order/" . $orderArr['order_session'], $NSOrderSnippet);
                        if ($ns == 0)
                            $NSOrderSnippet = str_replace("{customerOrderStatus}", $NSOrderStatus, $NSOrderSnippet);
                        else
                            $NSOrderSnippet = str_replace("{customerOrderStatus}", '', $NSOrderSnippet);
                        $NSOrderSnippet = str_replace("{added}", date('jS F Y ', $orderArr["added"]), $NSOrderSnippet);
                        $NSOrderSnippet = str_replace("{socialMediaLogo}", $socialMediaLogo, $NSOrderSnippet);
                        $NSOrderSnippet = str_replace("{curr_engagement}", $curr_engagement, $NSOrderSnippet);
                        $NSOrderSnippet = str_replace("{new_engagement}", $new_engagement, $NSOrderSnippet);
                        $NSOrderSnippet = str_replace("{missingAmount}", $missingAmount, $NSOrderSnippet);
                        $NSOrderSnippet = str_replace("{postUrl}", $postUrl, $NSOrderSnippet);
                        $NSOrderHtml .= $NSOrderSnippet;
                        $ns++;
                    }
                }
                $ns++;
            }

            if ($orderArr['packagetype'] == 'followers') {
                $followerHtmOption .= '<option data-pid="' . $orderArr['payment_id'] . '" data-refund="' . $orderArr['refund'] . '" value="' . $orderId . '">' . $orderArr["order"] . '</option>';
            }

            if ($orderArr['packagetype'] == 'likes') {
                $likeHtmOption .= '<option data-pid="' . $orderArr['payment_id'] . '" data-refund="' . $orderArr['refund'] . '" value="' . $orderId . '">' . $orderArr["order"] . '</option>';
            }

            $htmOption .= '<option data-pid="' . $orderArr['payment_id'] . '" data-refund="' . $orderArr['refund'] . '" value="' . $orderId . '">' . $orderArr["order"] . '</option>';
            $customerOrderStatus = '';
        }
    } else {
    $noOrder = 'display:block;';
    $htmOption .= "<option value=''>Select Order</option>";
    }
}else{
    $noOrderDisplay = 'display:none;';
}

$query = mysql_query("SELECT * FROM `email_autoreplies` WHERE `page` = 'email-support'");


while ($autorepliesinfo = mysql_fetch_array($query)) {

    $autorepliesinfo['autoreply'] = str_replace('<br />', "\r\n", $autorepliesinfo['autoreply']);

    $thisautoreply = '<div class="txtSize autoReplySec autoreplyselect">
                    <a class="fontBold ">' . $autorepliesinfo['title'] . '</a><br>
                    <textarea id="autoReply' . $autorepliesinfo['id'] . '" style="display:none;">Hi there,

' . $autorepliesinfo['autoreply'] . '

Kind regards,

James Harris</textarea>
                    <div>
                        <button class="btn3" onclick="populateAutoReply(' . $autorepliesinfo['id'] . ')" style="width:170px;margin-top:10px">Add this
                            auto reply</button>
                    </div>
                </div>';

    if ($autorepliesinfo['showdefault'] == '1') {

        $mainautoreplies .= $thisautoreply;
    } else {


        $moreautoreplies .= $thisautoreply;
    }
}

$uname = !empty($latestUserName) ? $latestUserName : $username ;

$usernamesArray[] = "'" . $uname . "'";
$usernames = implode(',', $usernamesArray);

$checkProfileStatusQuery = mysql_query("SELECT * FROM `orders_checks` WHERE `username` IN ($usernames) group by username order by id desc");
$cnt = 0;
$accountHTML = '';
if (mysql_num_rows($checkProfileStatusQuery) > 0) {
    $displayEmptyAccounts = 'display:none;';
    while ($checkProfileStatusArr = mysql_fetch_array($checkProfileStatusQuery)) {

        $checkProfileStatus = $checkProfileStatusArr['is_private'];
        $acc_username = $checkProfileStatusArr['username'];
        if ($checkProfileStatus == 1) {
            $checkProfileStatus = 'Private';
            $pubDisp = 'display: none';
        } else {
            $checkProfileStatus = 'Public';
            $privDisp = 'display: none';
        }

        $dateCheckStats = ago($checkProfileStatusArr['added']);

        $accountSinppet = tpl_get('account_div', $tpl);

        $accountSinppet = str_replace("{acc_uname}", $acc_username, $accountSinppet);
        $accountSinppet = str_replace("{privDisp}",  $privDisp, $accountSinppet);
        $accountSinppet = str_replace("{pubDisp}",  $pubDisp, $accountSinppet);
        $accountSinppet = str_replace("{dateCheckStats}", $dateCheckStats, $accountSinppet);
        $accountSinppet = str_replace("{cnt}", $cnt, $accountSinppet);
        $accountHTML .= $accountSinppet;
        
        $cnt++;
        $pubDisp = '';
        $privDisp = '';   
    }
    $dispNone = '';
} else {
    $privDisp = 'display: none';
    $pubDisp = 'display: none';
    $dispNone = 'display: none';
    $displayEmptyAccounts = 'display:block;';
}



$brandName = getBrandSelectedName($brand);

$tpl = str_replace('{globLastEmailUid}', $globLastEmailUid, $tpl);
$tpl = str_replace('{custEmail}', $email, $tpl);
$tpl = str_replace('{emailHref}', '/admin/check-user/?user=' . $email, $tpl);
$tpl = str_replace('{recentSubject}', htmlentities($recentSubject), $tpl);
$tpl = str_replace('{brand}', $brandName, $tpl);
$tpl = str_replace('{blockedTitle}', $blockedTitle, $tpl);
$tpl = str_replace('{isBlocked}', $isBlocked, $tpl);
$tpl = tpl_replace('allCustomerWiseEmailList', $allCustomerWiseEmailsHtml, $tpl);
$tpl = tpl_replace('autoLikeBtn', $bindAutoLikesBtn, $tpl);
$tpl = str_replace('{mainautoreplies}', $mainautoreplies, $tpl);
$tpl = str_replace('{moreautoreplies}', $moreautoreplies, $tpl);
$tpl = tpl_replace('ComcustomerOrderList', $customerOrderHtml, $tpl);
$tpl = tpl_replace('IPcustomerOrderList', $IPcustomerOrderHtml, $tpl);
$tpl = tpl_replace('NScustomerOrderList', $NSOrderHtml, $tpl);
$tpl = tpl_replace('recommended_actions', $RAHtml, $tpl);
$tpl = str_replace('{ticketId}', $ticketId, $tpl);
$tpl = str_replace('{htmOption}', $htmOption, $tpl);
$tpl = str_replace('{likeHtmOption}', $likeHtmOption, $tpl);
$tpl = str_replace('{followerHtmOption}', $followerHtmOption, $tpl);
$tpl = str_replace('{responses}', $responses, $tpl);
$tpl = str_replace('{selected_generated_tpl}', $selected_generated_tpl, $tpl);
$tpl = str_replace('{emailUid}', $selected_generated_tpl, $tpl);
if($totalActionCount == 0){
    $tabAction = '#';
    $tab1_bg_color = '#d1d3dc';
}else{
    $tabAction = '?type=action';
    $tab1_bg_color = '#C8D2FF';
}
if($totalReviewCount == 0){
    $tabReview = '#';
    $tab2_bg_color = '#d1d3dc';
}else{
    $tabReview = '?type=review';
    $tab2_bg_color = '#FFE4C8';

}
$tpl = str_replace('{emailListCount}', $emailListCount, $tpl);
$tpl = str_replace('{reviews_remaining}', $totalReviewCount, $tpl);
$tpl = str_replace('{action_remaining}', $totalActionCount, $tpl);
$tpl = str_replace('{displayEmptyOrders}', $noOrder, $tpl);
$tpl = str_replace('{displayOrderDetails}', $noOrderDisplay, $tpl);
$tpl = str_replace('{ad_added}', $accAdded, $tpl);
$tpl = str_replace('{ad_username}', !empty($latestUserName) ? $latestUserName : $username , $tpl);
$tpl = str_replace('{displayEmptyAccount}', $noAccount, $tpl);
$tpl = str_replace('{displayAccountDetails}', $noAccountsDisplay, $tpl);
$tpl = str_replace('{displayAlDetails}', $noAlDisplay, $tpl);
$tpl = str_replace('{displayCustomerInfo}', $customerInfo, $tpl);
$tpl = str_replace('{infoaun}', $latestUserName, $tpl);
$tpl = str_replace('{latestOrderId}', $latestOrderId, $tpl);
$tpl = str_replace('{latestBrand}', $latestBrand, $tpl);
$tpl = str_replace('{done}', $done, $tpl);
$tpl = str_replace('{offer_free_followers}', $display_offer_followers, $tpl);
$tpl = str_replace('{offer_free_likes}', $display_offer_likes, $tpl);
$tpl = str_replace('{offer_refund}', $display_offer_refund, $tpl);
$tpl = str_replace('{category}', $category, $tpl);
$tpl = str_replace('{category_tag}', ($category == "Spam" ? "⚠️ Potential Spam" : $category), $tpl);
$tpl = str_replace('{tabReview}', $tabReview, $tpl);
$tpl = str_replace('{tabAction}', $tabAction, $tpl);
$tpl = str_replace('{tab1_bg_color}', $tab1_bg_color, $tpl);
$tpl = str_replace('{tab2_bg_color}', $tab2_bg_color, $tpl);
$tpl = str_replace('{pubDisp}', $pubDisp, $tpl);
$tpl = str_replace('{privDisp}', $privDisp, $tpl);
$tpl = str_replace('{dispNone}', $dispNone, $tpl);
$tpl = str_replace('{dateCheckStats}', $dateCheckStats, $tpl);
$tpl = str_replace('{main_heading}', $main_heading, $tpl);
$tpl = str_replace('{refundError}', $refundError, $tpl);
$tpl = str_replace('{account_id}', $account_id, $tpl);
$tpl = tpl_replace('account_div', $accountHTML, $tpl);
$tpl = tpl_replace('al_sub', $alHTML, $tpl);
$tpl = tpl_replace('update_al_ui', $ualHTML, $tpl);
$tpl = tpl_replace('al_issued', $alfHTML, $tpl);
$missingAmount = str_replace("(", "", $missingAmount);
$missingAmount = str_replace(")", "", $missingAmount);
$tpl = str_replace('{missingAmount}', $missingAmount , $tpl);
$tpl = str_replace('{refSucess}', $refSucess, $tpl);
$tpl = str_replace('{onrefSucess}', $onrefSucess, $tpl);
$tpl = str_replace('{displayEmptyALSub}', $displayEmptyALSub, $tpl);
$tpl = str_replace('{displayEmptyALOrders}', $displayEmptyALOrders, $tpl);
$tpl = str_replace('{displayEmptyAccounts}', $displayEmptyAccounts, $tpl);


output($tpl, $options);
