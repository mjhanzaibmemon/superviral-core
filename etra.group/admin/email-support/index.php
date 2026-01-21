<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');


$toemoji = '✋';
$adminemoji  = '👴';
$supportemoji = '😊';

// LEFT MENU BIND/////////////////////////////////////////////////////////

$Query = mysql_query("SELECT
                            `from` AS emailId, brand 
                            FROM email_queue
                                WHERE markDone = '0'
                       --     AND `block` = '0'
                            AND `emailSpam` = '0'
                            AND `submitReport` = '0'
                            AND emailDate >= unix_timestamp(CURRENT_DATE - interval 1 month )
                            GROUP BY `from`
                            ORDER BY id ASC;
                        ");
$allSupportEmailCount = mysql_num_rows($Query);

$allSupportEmailHtml = "";
$i = 0;
if (isset($_GET['page'])) {$page = addslashes($_GET['page']);} else {$page = 1;}
if($page < 1)$page = 1;
// $no_of_records_per_page = 1;
// $offset = ($page-1) * $no_of_records_per_page;

if ($allSupportEmailCount > 0) {
    while ($resArr = mysql_fetch_array($Query)) {
        $id = "leftMenuEmails" . $i;
      
        $url = "?email=" .$resArr['emailId'] ."&id=$id&brand=".$resArr["brand"];

        if(strpos($_GET['id'], 'leftMenuEmails') !== false) {
            // pagination
            if($i == $page){
                // echo $url . 'next<br>';
                $nextSupportUrl = $url . '&page={nextpage}';
            }else if($i == ($page - 1)){
                // echo $url . 'current<br>';
                $currSupportUrl = $url. '&page={prevpage}';
            }
        }
        $SupportEmailSnippet = tpl_get('supportEmailList', $tpl);
        $SupportEmailSnippet = str_replace("{supportEmailUrl}", $url, $SupportEmailSnippet);
        $SupportEmailSnippet = str_replace("{supportEmailId}", $id, $SupportEmailSnippet);
        $SupportEmailSnippet = str_replace("{supportEmail}", $resArr["emailId"], $SupportEmailSnippet);
        $SupportEmailSnippet = str_replace("{id}", $resArr["id"], $SupportEmailSnippet);
        $brndNm = getBrandSelectedName($resArr['brand']);
        $SupportEmailSnippet = str_replace("{brand}", $brndNm, $SupportEmailSnippet);
        $allSupportEmailHtml .= $SupportEmailSnippet;
        $i++;
    }
} else {
    $allSupportEmailHtml = "<span style=\"text-align:center;display: block;padding-bottom: 17px;\">All done support done!</span>";
}

$Query = mysql_query(
    "SELECT
                                             `emailaddress` AS emailId, eq.brand
                                            FROM admin_notifications an
                                            INNER JOIN email_queue eq 
                                                ON an.emailaddress = eq.`from`
                                            WHERE emailaddress 
                                                NOT IN 
                                            (SELECT `from` FROM email_queue WHERE submitReport = '0') AND
                                            `directions` <> ''
                                            AND done = '1' AND `type` = 'emailSupport' AND markDone = '0' 
                                            -- AND `block` = '0'
                                            GROUP BY `emailaddress`
                                            ORDER BY an.id ASC;"
);

//echo mysql_num_rows($Query);

$allResponseAdminEmailCount = mysql_num_rows($Query);

$allResponseAdminEmail = "";
$i = 0;
if ($allResponseAdminEmailCount > 0) {
    while ($resArr = mysql_fetch_array($Query)) {
        $id = "leftMenuAdminEmails" . $i;
        $url = "?email=" .$resArr['emailId'] ."&id=$id&brand=".$resArr["brand"];

        if(strpos($_GET['id'], 'leftMenuAdminEmails') !== false) {
            // pagination
            if($i == $page){
                // echo $url . 'next<br>';
                $nextSupportUrl = $url . '&page={nextpage}';
            }else if($i == ($page - 1)){
                // echo $url . 'current<br>';
                $currSupportUrl = $url. '&page={prevpage}';
            }

        }
       
        $ResponseAdminSnippet = tpl_get('responseEmailList', $tpl);
        $ResponseAdminSnippet = str_replace("{responseEmailUrl}", $url, $ResponseAdminSnippet);
        $ResponseAdminSnippet = str_replace("{responseEmailId}", $id, $ResponseAdminSnippet);
        $ResponseAdminSnippet = str_replace("{responseEmail}", $resArr["emailId"], $ResponseAdminSnippet);
        $brndNm = getBrandSelectedName($resArr['brand']);
        $ResponseAdminSnippet = str_replace("{brand}", $brndNm, $ResponseAdminSnippet);
        $allResponseAdminEmail .= $ResponseAdminSnippet;

        $i++;
    }
} else {
    $allResponseAdminEmail = "<span style=\"text-align:center;display: block;padding-bottom: 17px;\">All admin responses done!</span>";
}

$blockedTitle = "Block";
// CENTER BODY BIND /////////////////////////////////////////////////////////
$email = trim(addslashes($_GET['email']));

if ($email != "") {

   
    $Query = mysql_query("SELECT
                                                id,
                                                `emailUid`,
                                                `email` as `reply`,
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
    // $emailListCount = mysql_num_rows($Query);
    $emailListCount = 0;
    // $i=0;
    $allCustomerWiseEmailsHtml = "";
    $customerOrderHtml = "";
    $responseOnEmailArr = [];
    while ($resArr = mysql_fetch_array($Query)) {
        $responseOnEmailArr[] = $resArr;
        $recentSubject = $resArr["subject"];
        $recentSubject = $string = str_replace(array("\n", "\r"), ' ', $recentSubject);
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
    // $countRcecord = mysql_num_rows($QueryAdmin);
    // if($countRcecord > 0){
    $countResponseArr = count($responseOnEmailArr);
    $time = array_column($responseOnEmailArr, 'dateAdded');

    $sortedArr = array_multisort($time, SORT_ASC, $responseOnEmailArr);
    // }

    for ($j = 0; $j < $countResponseArr; $j++) {
        if ($responseOnEmailArr[$j]["type"] == "support") {
            $btnColor = "#d7ffff";
            $btnColor = "#fff";
            $emailType = "'support'";
            $emailnameshow = $supportemoji . " Support Team";
            $fromClass = "";
        } else if ($responseOnEmailArr[$j]["type"] == "admin") {
            $btnColor = "#d9ffc4";
            $btnColor = "#fff";
            $emailType = "'admin'";
            $emailnameshow = $adminemoji . " Admin Team";
            $initialsupportmessage = '<div class="initialmsg">' . $responseOnEmailArr[$j]['initialmsg'] . '</div>';
            $fromClass = "";
        } else {
            $emailType = "''";
            $emailnameshow = $toemoji . " " . $email;
            $fromClass = "recip";
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



        $emailUnixTime = gmdate("H:i d-m-y", $responseOnEmailArr[$j]["dateAdded"]);
        $NextDisplayChar = '<span class="' . $classForDisplayMessage . '" id="NextDisplayChar' .
            $emailListCount . '">' . $cleanemailbody . '</span><br><span><button class="btn3"  id="copyBtn' .$emailListCount . '" onclick="callCopyFunc('. $emailListCount .')">Copy Message</button></span>';

        $onclickParam = $emailListCount . ',\'\', ' . $responseOnEmailArr[$j]["id"] . ', ' . $emailType;    
        $allCustomerWiseEmailSnippet = tpl_get('allCustomerWiseEmailList', $tpl);
        $allCustomerWiseEmailSnippet = str_replace("{bgColorClass}", $bgColorClass, $allCustomerWiseEmailSnippet);
        $allCustomerWiseEmailSnippet = str_replace("{CustomerWiseEmaildateAdded}",  $responseOnEmailArr[$j]["dateAdded"], $allCustomerWiseEmailSnippet);
        $allCustomerWiseEmailSnippet = str_replace("{fromClass}", $fromClass, $allCustomerWiseEmailSnippet);    
        $allCustomerWiseEmailSnippet = str_replace("{emailnameshow}", $emailnameshow, $allCustomerWiseEmailSnippet);    
        $allCustomerWiseEmailSnippet = str_replace("{emailUnixTime}", $emailUnixTime, $allCustomerWiseEmailSnippet);    
        $allCustomerWiseEmailSnippet = str_replace("{onClickEvent}", "showMoreEmail($onclickParam)", $allCustomerWiseEmailSnippet);    
        $allCustomerWiseEmailSnippet = str_replace("{showMoreBtnId}", 'showMoreBtn' . $emailListCount, $allCustomerWiseEmailSnippet);     
        $allCustomerWiseEmailSnippet = str_replace("{btnText}", $btnText, $allCustomerWiseEmailSnippet);     
        $allCustomerWiseEmailSnippet = str_replace("{openAttachmentsModalClickEvent}", "openAttachmentsModal(". $responseOnEmailArr[$j]["id"] .");", $allCustomerWiseEmailSnippet);     
        $allCustomerWiseEmailSnippet = str_replace("{initialsupportmessage}", $initialsupportmessage . nl2br($NextDisplayChar), $allCustomerWiseEmailSnippet);     

        $allCustomerWiseEmailsHtml .=  $allCustomerWiseEmailSnippet;

        unset($cleanemailbody);
        unset($initialsupportmessage);
    }


    $Query = mysql_query("SELECT COUNT(1) AS cnt FROM accounts WHERE email = '$email'");
    $CountArr = mysql_fetch_array($Query);
    $IsAccountExist = $CountArr['cnt'];

    $Query = mysql_query("SELECT COUNT(1) AS cnt FROM automatic_likes WHERE emailaddress = '$email'");
    $CountArr = mysql_fetch_array($Query);
    $IsAutoLikeExist = $CountArr['cnt'];

    $bindAccBtn = "";
    if ($IsAccountExist == 1) {
        $accBtnSnippet = tpl_get('accountBtn', $tpl);
        $bindAccBtn = $accBtnSnippet;
    } else {
        $bindAccBtn .= '';
    }

    $bindAutoLikesBtn = "";
    if ($IsAutoLikeExist == 1) {
        $autoLikeBtnSnippet = tpl_get('autoLikeBtn', $tpl);
        $bindAutoLikesBtn = $autoLikeBtnSnippet;
    } else {
        $bindAutoLikesBtn .= '';
    }

    $Query = mysql_query("SELECT CONCAT('#','',id,' - ',amount,' ',packagetype) AS `order`, `added`, `socialmedia`,`fulfilled` FROM orders WHERE emailaddress = '$email' ORDER BY `id` DESC LIMIT 30");
    if (mysql_num_rows($Query) > 0) {
        $htmOption = "<option value=''>Select Order</option>";

        while ($orderArr = mysql_fetch_array($Query)) {

            $keyword = getSocialMediaSource($orderArr['socialmedia']);
            $socialMediaLogo = '<img src="/admin/assets/icons/' . $keyword . '-icon.svg" style ="margin-right:5px;width: 15px;">';
            $emailUnixTime = gmdate("H:i d-m-y", $orderArr["added"]);
            $orderId = explode("-", $orderArr["order"]);
            $orderId = str_replace("#", "", $orderId[0]);
            $orderId = str_replace(" ", "", $orderId);
            $url = "/admin/check-user/?orderid=$orderId";

            if ($orderArr['fulfilled'] == '0') {

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
    
                $orderstatus = '<font color="orange">In progress ' . $approx1 . '</font>';
                $customerOrderStatus = $orderstatus;
                $IPcustomerOrderSnippet = tpl_get('IPcustomerOrderList', $tpl);
                $IPcustomerOrderSnippet = str_replace("{customerOrderUrl}", $url, $IPcustomerOrderSnippet);
                $IPcustomerOrderSnippet = str_replace("{customerOrder}", $socialMediaLogo .$orderArr["order"], $IPcustomerOrderSnippet);
                $IPcustomerOrderSnippet = str_replace("{customerOrderUnixtime}", $emailUnixTime , $IPcustomerOrderSnippet);
                $IPcustomerOrderSnippet = str_replace("{customerOrderStatus}", $customerOrderStatus , $IPcustomerOrderSnippet);
                $IPcustomerOrderHtml .= $IPcustomerOrderSnippet;

            } else {
                $orderstatus = '<font color="green">Completed: ' . date('d/m/Y H:i:s ', $orderArr['fulfilled']) . '</font>';
                
                $customerOrderStatus = $orderstatus;
                $customerOrderSnippet = tpl_get('ComcustomerOrderList', $tpl);
                $customerOrderSnippet = str_replace("{customerOrderUrl}", $url, $customerOrderSnippet);
                $customerOrderSnippet = str_replace("{customerOrder}", $socialMediaLogo .$orderArr["order"], $customerOrderSnippet);
                $customerOrderSnippet = str_replace("{customerOrderUnixtime}", $emailUnixTime , $customerOrderSnippet);
                $customerOrderSnippet = str_replace("{customerOrderStatus}", $customerOrderStatus , $customerOrderSnippet);
                $customerOrderHtml .= $customerOrderSnippet;
            }

           

            $htmOption .= '<option value="' . $orderId . '">' . $orderArr["order"] . '</option>';
            $customerOrderStatus = '';
        }
    } else {
        $customerOrderHtml .= 'No orders';
        $htmOption .= "<option value=''>Select Order</option>";
    }

   
} else {
    $allCustomerWiseEmailsHtml = "Please select any email";
}


if ($globLastEmailUid == "" || $globLastEmailUid == null) {
    $globLastEmailUid = 0;
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
if(isset($_GET['brand']) && !empty($_GET['brand'])){
    $brand = $_GET['brand'];
    $brandName = getBrandSelectedName($brand);
    $tpl = str_replace('{brand}',$brandName,$tpl);
}else{
    $tpl = str_replace('{brand}',strtolower($brand_arr[$brand]),$tpl);
}

if(!empty($_GET['email'])){
    $showpagination = 'display: flex ;';
}else{
    $showpagination = 'display: none ;';
    
}



$tpl = str_replace('{globLastEmailUid}',$globLastEmailUid,$tpl);
$tpl = str_replace('{allResponseAdminEmailCount}',$allResponseAdminEmailCount,$tpl);
$tpl = tpl_replace('responseEmailList', $allResponseAdminEmail, $tpl);
$tpl = str_replace('{allSupportEmailCount}',$allSupportEmailCount,$tpl);
$tpl = tpl_replace('supportEmailList', $allSupportEmailHtml, $tpl);
$tpl = str_replace('{custEmail}',$email,$tpl);
$tpl = str_replace('{recentSubject}',$recentSubject,$tpl);
$tpl = str_replace('{brand}',$brandName,$tpl);
$tpl = str_replace('{blockedTitle}',$blockedTitle,$tpl);
$tpl = str_replace('{isBlocked}',$isBlocked,$tpl);
$tpl = tpl_replace('allCustomerWiseEmailList', $allCustomerWiseEmailsHtml, $tpl);
$tpl = tpl_replace('accountBtn', $bindAccBtn, $tpl);
$tpl = tpl_replace('autoLikeBtn', $bindAutoLikesBtn, $tpl);
$tpl = str_replace('{mainautoreplies}',$mainautoreplies,$tpl);
$tpl = str_replace('{moreautoreplies}',$moreautoreplies,$tpl);
$tpl = tpl_replace('ComcustomerOrderList', $customerOrderHtml, $tpl);
$tpl = tpl_replace('IPcustomerOrderList', $IPcustomerOrderHtml, $tpl);
$tpl = str_replace('{htmOption}',$htmOption,$tpl);
$tpl = str_replace('{currSupportUrl}',($currSupportUrl),$tpl);
$tpl = str_replace('{nextSupportUrl}',($nextSupportUrl),$tpl);
$tpl = str_replace('{prevpage}',($page-1),$tpl);
$tpl = str_replace('{nextpage}',($page+1),$tpl);
$tpl = str_replace('{showpagination}',$showpagination,$tpl);
$tpl = str_replace('{privateBtnHtm}',$privateBtnHtm,$tpl);

output($tpl, $options);
