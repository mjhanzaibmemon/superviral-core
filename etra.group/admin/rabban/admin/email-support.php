<?php

include 'adminheader.php';

header('Cache-Control: no-transform');


$toemoji = '✋';
$adminemoji  = '👴';
$supportemoji = '😊';

// LEFT MENU BIND/////////////////////////////////////////////////////////

                $Query = mysql_query("SELECT
                            id, `from` AS emailId
                            FROM email_queue
                                WHERE markDone = '0'
                       --     AND `block` = '0'
                            AND `emailSpam` = '0'
                            AND `submitReport` = '0'
                            AND emailDate >= unix_timestamp(CURRENT_DATE - interval 1 month )
                            GROUP BY `from`
                            ORDER BY id DESC;
                        ");
                $allSupportEmailCount = mysql_num_rows($Query);

                $allSupportEmailHtml = "";
                $i = 0;
                if ($allSupportEmailCount > 0) {
                    while ($resArr = mysql_fetch_array($Query)) {
                        $id = "leftMenuEmails". $i;
                        $allSupportEmailHtml .= '
                                         <a href="email-support.php?email='. trim(str_replace('+','thisisaplusspace',$resArr["emailId"])) .'&id='. $id .'" class="supportemails" id="' . $id . '" data-id="'.$resArr['id'].'" >
                                                  ' . $resArr["emailId"] . '
                                             <i class="fa fa-caret-right caret" aria-hidden="true"></i>
                                         </a>';
                        $i++;
                    }
                } else {
                    $allSupportEmailHtml = "<span style=\"text-align:center;display: block;padding-bottom: 17px;\">All done support done!</span>";
                }

                $Query = mysql_query("SELECT
                                            eq.id, `emailaddress` AS emailId
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
                                            ORDER BY an.id DESC;"
                );

                //echo mysql_num_rows($Query);

                $allResponseAdminEmailCount = mysql_num_rows($Query);

                $allResponseAdminEmail = "";
                $i =0;
                if ($allResponseAdminEmailCount > 0) {
                    while ($resArr = mysql_fetch_array($Query)) {
                        $id = "leftMenuAdminEmails". $i;
                        $allResponseAdminEmail .= '
                                        <a href="email-support.php?email='. str_replace('+','thisisaplusspace',$resArr["emailId"]) .'&id='. $id .'" class="supportemails" id="' . $id . '" data-id="'.$resArr["id"].'" >
                                            ' . $resArr["emailId"] . '
                                            <i class="fa fa-caret-right caret" aria-hidden="true"></i>
                                        </a>';
                        $i++;
                    }
                } else {
                    $allResponseAdminEmail = "<span style=\"text-align:center;display: block;padding-bottom: 17px;\">All admin responses done!</span>";
                }

                // $Query = mysql_query("SELECT
                //                             `from` AS emailId
                //                             FROM email_queue
                //                                 WHERE markDone = '0'
                //                             AND `block` = '0'
                //                             AND `emailSpam` = '1'
                //                             AND `submitReport` = '0'
                //                             AND emailDate >= unix_timestamp(CURRENT_DATE - interval 1 month )
                //                             GROUP BY `from`
                //                             ORDER BY id DESC;
                // ");
                // $allSpamEmailCount = mysql_num_rows($Query);

                // $allSpamEmailHtml = "";
                // $i = 0;
                // if ($allSupportEmailCount > 0) {
                //     while ($resArr = mysql_fetch_array($Query)) {
                //         $id = "leftMenuSpamEmails". $i;
                //         $allSpamEmailHtml .= '
                //                          <a href="email-support.php?email='. $resArr["emailId"] .'&id='. $id .'" class="supportemails" id="' . $id . '" >
                //                                   ' . $resArr["emailId"] . '
                //                              <i class="fa fa-caret-right caret" aria-hidden="true"></i>
                //                          </a>';
                //         $i++;
                //     }
                // } else {
                //     $allSpamEmailHtml = "<span style=\"text-align:center;display: block;padding-bottom: 17px;\">All done spam done!</span>";
                // }
                    
                    $blockedTitle = "Block Conversation";   
                    // CENTER BODY BIND /////////////////////////////////////////////////////////
                    $email = addslashes(str_replace('thisisaplusspace','+',$_GET['email']));

/*                    echo "SELECT
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
                                                `from` = '{$email}'
                                                AND emailDate >= unix_timestamp(CURRENT_DATE - interval 2 month );";die;*/

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
                                                `from` = '{$email}'
                                                AND emailDate >= unix_timestamp(CURRENT_DATE - interval 2 month );
                        ");

                        if(mysql_num_rows($Query)==0){

                            $email = str_replace('+','',$email);
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
                                                `from` = '{$email}'
                                                AND emailDate >= unix_timestamp(CURRENT_DATE - interval 12 month );
                                            ");

                        }

                        // $emailListCount = mysql_num_rows($Query);
                        $emailListCount = 0;
                        // $i=0;
                        $allCustomerWiseEmailsHtml ="";
                        $customerOrderHtml = "";
                        $responseOnEmailArr = [];
                        while ($resArr = mysql_fetch_array($Query)) {
                            $responseOnEmailArr[] = $resArr;
                            $recentSubject = $resArr["subject"];
                            $globLastEmailUid = $resArr["emailUid"];
                            if($resArr['block']== '1'){
                                $blockedTitle = "Unblock Conversation";
                                $isBlocked = 0;
                            }else{
                                $blockedTitle = "Block Conversation";
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
                                    WHERE emailUid = '".$resArr["emailUid"]."'
                                AND dateAdded >= unix_timestamp(CURRENT_DATE - interval 1 month )
                            ");
   
                        
                            $firstDisplayChar = $recentSubject;
                             while ($resArrSupport = mysql_fetch_array($QuerySupport)) {
                                $responseOnEmailArr[]= $resArrSupport;
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
                                                                    WHERE emailUid = '".$resArr["emailUid"]."'
                                                                AND response >= unix_timestamp(CURRENT_DATE - interval 1 month )
                            ");

                             while ($resArrAdmin = mysql_fetch_array($QueryAdmin)) {
                                 $responseOnEmailArr[]= $resArrAdmin;
                               
                             }
                        }
                        // $countRcecord = mysql_num_rows($QueryAdmin);
                        // if($countRcecord > 0){
                            $countResponseArr = count($responseOnEmailArr);
                            $time = array_column($responseOnEmailArr, 'dateAdded');

                            $sortedArr = array_multisort($time, SORT_ASC, $responseOnEmailArr);
                        // }

                        for($j = 0; $j< $countResponseArr;$j++){
                                if($responseOnEmailArr[$j]["type"] == "support"){
                                        $btnColor = "#d7ffff";
                                        $btnColor = "#fff";
                                        $emailType = "'support'";
                                        $emailnameshow = $supportemoji." Support Team";
                                        $fromClass = "";
                                }else if($responseOnEmailArr[$j]["type"] == "admin"){
                                        $btnColor = "#d9ffc4";
                                        $btnColor = "#fff";
                                        $emailType = "'admin'";
                                        $emailnameshow = $adminemoji." Admin Team";
                                        $initialsupportmessage = '<div class="initialmsg">'.$responseOnEmailArr[$j]['initialmsg'].'</div>';
                                        $fromClass = "";
                                }else{
                                        $emailType = "''"; 
                                        $emailnameshow = $toemoji. " " . $email;
                                        $fromClass = "recip";
                                }
                               $emailListCount++;
                            if($responseOnEmailArr[$j]["hideMessage"] == "1"){
                                $classForDisplayMessage = "";
                                $btnText = "Show Message";
                                $bgColorClass = "hidemessagefull";
                            } else {
                                $classForDisplayMessage = "";
                                $btnText = "Hide Message";
                                $bgColorClass = "showmessagefull";
                            }



                            $cleanemailbody = $responseOnEmailArr[$j]['reply'];

                                if (strpos($cleanemailbody, '<customer-care@superviral.io>') !== false) {
                                $cleanemailbody = explode('<customer-care@superviral.io>', $cleanemailbody);$cleanemailbody = $cleanemailbody[0];}


                                if (strpos($cleanemailbody, 'orders@superviral.io>') !== false) {$cleanemailbody = explode('<orders@superviral.io>', $cleanemailbody);$cleanemailbody = $cleanemailbody[0];}

                                if (strpos($cleanemailbody, 'support@superviral.io>') !== false) {$cleanemailbody = explode('<support@superviral.io>', $cleanemailbody);$cleanemailbody = $cleanemailbody[0];}



                            $emailUnixTime = gmdate("H:i d-m-Y", $responseOnEmailArr[$j]["dateAdded"]);
                            $NextDisplayChar = '<span class="' . $classForDisplayMessage .'" id="NextDisplayChar' .
                                                    $emailListCount . '">' . $cleanemailbody . '</span>';

                             $allCustomerWiseEmailsHtml .= '<div class="txtSize accordion1 '. $bgColorClass .'" data-time ="' . $responseOnEmailArr[$j]["dateAdded"] .'" >' ;
                             $allCustomerWiseEmailsHtml .= '<a class="fontBold '. $fromClass .'">'.$emailnameshow.'</a><br>';
                             $allCustomerWiseEmailsHtml .= '<div class="rightdetails">';
                             $allCustomerWiseEmailsHtml .= '<span class="timestamp">'.$emailUnixTime.'</span><button class="btn3 revealbtn" onclick="showMoreEmail(' . $emailListCount ;
                             $allCustomerWiseEmailsHtml .= ',\'\', '. $responseOnEmailArr[$j]["id"] .', '. $emailType .');" id="showMoreBtn' . $emailListCount;
                             $allCustomerWiseEmailsHtml .= '">' . $btnText . '</button><span style="display: block;margin-left: 28px;margin-top: 55px;cursor:pointer;text-decoration:underline;" onclick="openAttachmentsModal('. $responseOnEmailArr[$j]["id"] .');">Attachments</span>';
                             $allCustomerWiseEmailsHtml .= '        </div>';
                             $allCustomerWiseEmailsHtml .= '        <div class="emailbody">' . $initialsupportmessage.$NextDisplayChar .'</div>';
                             $allCustomerWiseEmailsHtml .= '    </div>' ;
                           //   $allCustomerWiseEmailsHtml .= '</div>';
                        
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
                            $bindAccBtn .= '<button id="customerAccountCheckId" class="btn1">HAS AN ACCOUNT</button>';
                        } else {
                            $bindAccBtn .= '';
                        }
                        
                        $bindAutoLikesBtn = "";
                        if ($IsAutoLikeExist == 1) {
                            $bindAutoLikesBtn .= '<button id="customerAutoLikeCheckId" class="btn1">HAS AN AUTO LIKES</button>';
                        } else {
                            $bindAutoLikesBtn .= '';
                        }

                        $bindAccAutoLikesBtn = $bindAccBtn . $bindAutoLikesBtn;

                        $Query = mysql_query("SELECT CONCAT('#','',id,' - ',amount,' ',packagetype) AS `order`, `added` FROM orders WHERE emailaddress = '$email' ORDER BY `id` DESC LIMIT 30");
                       if(mysql_num_rows($Query) > 0){
                              $htmOption = "<option value=''>Select Order</option>";
                              
                              while ($orderArr = mysql_fetch_array($Query)) {
                                  $emailUnixTime = gmdate("H:i d-m-Y", $orderArr["added"]);
                                  $orderId = explode("-", $orderArr["order"]);
                                  $orderId = str_replace("#", "", $orderId[0]);
                                  $orderId = str_replace(" ", "", $orderId);
                                  $customerOrderHtml .= '<a class="supportemails" href="check-user.php?orderid=' . $orderId ;
                                  $customerOrderHtml .= '" target="_blank" class="fontBold txtSize accordion">' ;
                                  $customerOrderHtml .= '    ' . $orderArr["order"] . ' - <span>' . $emailUnixTime . '</span>' ;
                                  $customerOrderHtml .= '    <i class="fa fa-caret-right caret" aria-hidden="true"></i>' ;
                                  $customerOrderHtml .= '</a>';
                                  $htmOption .= '<option value="' . $orderId . '">'. $orderArr["order"] . '</option>';
                              }
                       }else{
                        $customerOrderHtml .= 'No orders';
                        $htmOption .= "<option value=''>Select Order</option>";
                       }
                       
                      
                    }else{
                        $allCustomerWiseEmailsHtml = "Please select any email";
                    }
                

                    if($globLastEmailUid == "" || $globLastEmailUid == null){
                        $globLastEmailUid = 0;
                    }


            $query = mysql_query("SELECT * FROM `email_autoreplies`");


            while($autorepliesinfo = mysql_fetch_array($query)){

              $autorepliesinfo['autoreply'] = str_replace('<br />', "\r\n", $autorepliesinfo['autoreply']);  

                $thisautoreply = '<div class="txtSize autoReplySec autoreplyselect">
                    <a class="fontBold ">'.$autorepliesinfo['title'].'</a><br>
                    <textarea id="autoReply'.$autorepliesinfo['id'].'" style="display:none;">Hi there,

'.$autorepliesinfo['autoreply'].'

Kind regards,

James Harris</textarea>
                    <div>
                        <button class="btn3" onclick="populateAutoReply('.$autorepliesinfo['id'].')" style="width:170px;margin-top:10px">Add this
                            auto reply</button>
                    </div>
                </div>';

                if($autorepliesinfo['showdefault']=='1'){

                      $mainautoreplies .= $thisautoreply;

                  }else{


                      $moreautoreplies .= $thisautoreply;


                  }



            }


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Support</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/orderform.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.2/clipboard.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.10.1.min.js"></script>
    <script src="https://kit.fontawesome.com/a282321041.js" crossorigin="anonymous"></script>

    <!-- ////////////////////////////////////////// css /////////////////////////////////////// -->

    <style type="text/css">

        html, body {
        height: 100%;
        }
        
        body{background:#eeeeee;}

        .highlightClassLeftMenu{
    background-color: #000000;
    color: #fff!important;
        }

        /* loader css */

        .stickyheaderall {
            top: 0px;
            padding-top: 9px;
            -webkit-box-shadow: -1px 0px 5px 1px rgb(230 230 230);
            -moz-box-shadow: -1px 0px 5px 1px rgba(230, 230, 230, 1);
            box-shadow: -1px 0px 5px 1px rgb(230 230 230);
        }

        #cover-spin {
            position: fixed;
            width: 100%;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 9999;
            display: none;
        }

        @-webkit-keyframes spin {
            from {
                -webkit-transform: rotate(0deg);
            }

            to {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        #cover-spin::after {
            content: '';
            display: block;
            position: absolute;
            left: 48%;
            top: 40%;
            width: 40px;
            height: 40px;
            border-style: solid;
            border-color: black;
            border-top-color: transparent;
            border-width: 4px;
            border-radius: 50%;
            -webkit-animation: spin .8s linear infinite;
            animation: spin .8s linear infinite;
        }

        /* loader css end */
        .btn {
            width: 101%;
            text-align: center;
        }

        .alignthis {
            padding: 55px 0;
        }

        /* .mainContainer {
            display: flex;
            margin: 1%;
        } */

        .box1 {
            margin: 1%;
            /* display: inline-block; */
            width: 16%;
            border-radius: 5px;
            text-align: left;
            float: left;
            height:100%;
        }

        .box1 .containers{
          margin-bottom:20px;
          background: #fff;
        }

        .box2 {
  
            /* display: inline-block; */
            width: 52%;
            border-radius: 5px;
            text-align: left;
            float: left;
            min-height: 1360px;
                padding-top: 17px;
        }

        .box3 {
            margin: 1%;
            /* display: inline-block; */
            width: 25%;
            background: #fff;
            border-radius: 5px;
            text-align: left;
            float: left;
            padding: 15px;
        }

        .fontBold {
            font-weight: 600;
        }

        hr {
            margin: 5% 0px;
            background-color: #ccc;
            border: none;
            height: 1px;
        }

        .txtSize {
            font-size: 12px;
        }

        .accordion {
            color: #000;
            cursor: pointer;
            border: none;
            text-align: left;
            outline: none;
            font-size: 15px;
            border-bottom: 1px solid #ccc;
        }

        .panel {
            display: none;
            background-color: white;
            overflow: hidden;
        }

        .caret {
            float: right;
            margin-top: 11px;
        }

        .btn3 {
            color: #6e6e6e;
            background: #f1f1f0;
            border:1px solid #bdbdbd;
            padding: 10px;
            font-weight: 400;
            border-radius: 5px !important;
            cursor: pointer;
            width: 130px;
            float: right;
            margin-left: 5px;
            font-weight: bold;
    font-size: 13px;
    z-index:999;
        }

        .btn4 {
            color: red;
             padding: 10px;
            font-weight: 400;
            border: 1px solid red;
            border-radius: 5px !important;
            cursor: pointer;
            width: 151px;
            float: right;
            margin-left: 5px;
            font-weight: bold;
    font-size: 13px;
        }

        .btn1 {
            color: #fff;
            background: orange;
            padding: 7px;
            font-weight: 400;
            border: 1px solid #bbb;
            border-radius: 35px !important;
            cursor: pointer;
            width: 165px;
        }

        .searchBox {
            padding: 12px;
            font-size: 15px;
            border-radius: 5px;
            border: 1px solid #bbb;
            width: 30%;
            box-sizing: border-box;
            height: 39px;
        }

        .pull-right {
            float: right;
        }

        .pull-left {
            float: left;
        }

        .accordion1,
        .autoReplySec {
    color: #000;
    /* cursor: pointer; */
    background: #fff;
    padding: 23px;
    border: none;
    text-align: left;
    outline: none;
    font-size: 15px;
    border-bottom: 1px solid #ccc;
    position: relative;
        }

        .rightdetails{    position: absolute;
    right: 22px;
    top: 24px;
      z-index: 999;}

    .rightdetails .timestamp{    display: block;
     margin-bottom: 15px;
    font-size: 14px;
    text-align: right;}

        .autoReplySec {
            background-color: #e2e2e2;
        }

        /* The Modal (background) */
        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
                z-index: 15000;
            /* Sit on top */
            padding-top: 100px;
            /* Location of the box */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgb(0, 0, 0);
            /* Fallback color */
            background-color: rgba(0, 0, 0, 0.4);
            /* Black w/ opacity */
        }

        /* Modal Content */
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            display: flow-root;
        }

        /* The Close Button */
        .close {
            color: #aaaaaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        .NextDisplayChar {
            display: none;
        }

        .font-size12 {

            font-size: 12px;

        }

        .headerAllEmail {
            position: fixed;
            width: 52%;
            background: white;
            padding: 11px;
            box-sizing: border-box;
            z-index: 9990;
        }

        .bodyAllEmail {
            height: 1000px;
            width: 100%;
            padding-top: 228px;
        }

        .revealbtn{padding: 10px 6px;}

        #orderSelectId{height:43px;}

        .recip{    
            /* margin-top: -23px; */
    display: block;color:#d03340;}

    textarea{font-family: "Open Sans", sans-serif;min-height:150px;background:#fff;}

    .emailbody{    line-height: 32px;
    width: 670px;}

    .showmessagefull{background:#fff;min-height:110px;}




    .hidemessagefull{    background: rgb(120,120,120);
    background: linear-gradient(0deg, rgb(183 183 183) 0%, rgb(217 217 217) 100%);    height:69px;}

    .hidemessagefull .emailbody{opacity:0.6;}
    .hidemessagefull .fontBold {opacity:0.6;}

    .supportemails{color: #000;
    cursor: pointer;
    border: none;
    text-align: left;
    font-size: 15px;
    border-top: 1px solid #ccc;
    display: block;
    width: 100%;
    height: 57px;
    line-height: 37px;
    padding: 9px 15px;
    box-sizing: border-box;
      color: #0000ad;
    font-weight: bold;
     white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;}

    .containerheading{    display: block;
    padding: 15px;
    font-weight: bold;}

    #customerOrdersId{overflow: scroll;
    max-height: 500px;}

    .initialmsg{padding: 12px;
    background: #c8f9fb;
    font-style: italic;
    margin-top: 20px;
    margin-bottom: 14px;
    border-radius: 5px;}

    #supportReplyId{width: 100%;min-height:300px}

    .autoreplyselect{min-height: 125px;padding: 10px;}

    </style>

</head>

<!-- /////////////////////////////////////////// html body ///////////////////////////////////////////// -->

<body>
    <div id="cover-spin"></div>
    <?= $header ?>
    <div class="mainContainer" align="center">
        <div class="box1">

            <div style="    margin-top: -5px;    margin-bottom: 5px;">
                
                    <img onclick="openModalSendEmail();" src="/imgs/mail-3.png" alt="" style="    width: 10%;
    cursor: pointer;">
                
            </div>

            <div class="containers dshadow">
            <div class="containerheading">Response from Admin <span id="responseFromAdminEmailCountId">(<?= $allResponseAdminEmailCount ?>)</span></div>
            <div id="AdminResponseEmailsId">
                    <?= $allResponseAdminEmail ?>
            </div>
            </div>

            <div class="containers dshadow">
            <!-- <div>
                    <button class="modal-button btn3" id="emailSendBtnId"  href="#emailSendModal" style="float: none;  margin-top: 10px;">Send Email</button>
            </div> -->
            <div class="containerheading">Support needs resolving <span id="supportEmailCountId">(<?= $allSupportEmailCount ?>)</span></div>
            <div id="supportEmailsId">
                <?= $allSupportEmailHtml ?>
            </div>
            </div>

            <div class="containers dshadow">
            <div class="containerheading">Potentially Spam <span id="spamEmailCountId">(0)</span></div>
            <button style="float: none;margin: auto !important;display: block;" class="btn3" id="revealSpamBtnId" onclick="getSpamEmails('');" >Reveal</button>

            <div id="spamEmailsId">
                
            </div>
            </div>

        </div>

        <div class="box2 dshadow">
            <div class="headerAllEmail">
                <div style="margin-bottom:20px;">
                    <a class="fontBold " id="customerEmailId"><?= $email; ?></a> <br>
                    <a>Recent Subject: <span id="customerEmailSubjectId"><?= $recentSubject?></span></a>
                </div>
                <div style="margin-bottom: 30px;">
                <button class="btn4" id="deleteBtnId" data-value = "<?=$isBlocked?>" onclick="blockConversation();" ><?=$blockedTitle?></button>
                    <button style="float:left;margin-left: 0;" class="btn3" id="markDoneBtnId" onclick="markDone();" >Mark Done</button>
                    <button style="float:left;margin-left:30px;" class="modal-button btn3" id="submitReportBtnId" href="#submitReportModal" >Submit
                        Report</button>
                </div>
                <div style="    display: inline-block;
    width: 100%;
    margin-top: 12px;
    height: 31px;
    overflow: hidden;">
                <?= $bindAccAutoLikesBtn; ?>
                   
                </div>
                <div style="margin-top: 16px;">
                    <input type="text" style="width:310px" class="searchBox" autocomplete="off" id="searchEmailId" placeholder="Search conversation by keyword...">
                    <button class="modal-button btn3" id="searchEmailsBtnId"  href="#searchEmailModal" style="float: none; width:100px;">Search</button>
                </div>
            </div>
            <div class="bodyAllEmail">
                <div id="allCustomerEmailsId" style="white-space: pre-line;display:grid;word-break: break-all;">
                        <?= $allCustomerWiseEmailsHtml ?>
                </div>

                <!-- <hr style="margin: 0;"> -->
                <div class="autoReplySec">
                    <div class="txtSize accordion1" style="display: flow-root;    padding: 0;    background: none;    border: none;">
                        <textarea class="searchBox"  id="supportReplyId" placeholder="Enter reply..." rows="6">
Hi there,

Thank you for contacting customer care.


Kind regards,

James Harris

                        </textarea>
                        <div>
                            <button class="btn3" onclick="replyOnEmail();" id="replyBtnId"  style="width:90px">Reply</button>
                        </div>

                    </div>
                </div>

                <div class="txtSize autoReplySec" style="padding: 10px;height: 40px;">

                  <button class="btn3" style="width:170px;float:left;" id="viewMoreReplyBtnId" onclick="viewMoreAutoReplies();">+ View all auto replies</button>

                </div>


    

                <?=$mainautoreplies?>


                <div id="hideMoreAutoReplies" style="display: none;">

                <?=$moreautoreplies?>

                
                </div>
            </div>



        </div>

        <div class="box3 dshadow">
            <a class="fontBold">Search all orders</a>
            <p>
                <input type="text" class="searchBox" autocomplete="off" style="width: 70%;" id="searchOrderId">
                <button class="btn3" style="float: none; width:100px;" onclick="searchOperations('order')">Search</button>
            </p>
            <div id="searchOrdersDivId"></div>
        </div>
        <div class="box3 dshadow">
            <a class="fontBold">Search all users</a>
            <p>
                <input type="text" autocomplete="off" class="searchBox" style="width: 70%;" id="searchUserId">
                <button class="btn3" style="float: none; width:100px;" onclick="searchOperations('user')">Search</button>
            </p>
            <div id="searchUsersDivId"></div>
        </div>
        <div class="box3 dshadow">
            <a class="fontBold " style="    display: block;
    margin-bottom: 13px;">Customer orders</a>
            <div id="customerOrdersId">
                <?= $customerOrderHtml ?>
            </div>
        </div>
        <div class="box3 dshadow" id="customerInfoDivId">
            <a class="fontBold">Customer account info</a>
            <p>
                <label class="font-size12">Username</label><br>
                <input type="text" id="uName" autocomplete="off" class="searchBox" style="width: 70%;" readonly>
            </p>
            <p>
                <label class="font-size12">Orders</label><br>
                <input type="text" id="uOrder" autocomplete="off" class="searchBox" style="width: 70%;" readonly>
            </p>
            <!-- <p>
                <label class="font-size12">Contact number</label><br>
                <input type="text" autocomplete="off" class="searchBox" style="width: 70%;" readonly>
            </p> -->
            <p>
                <label class="font-size12">Unsubscribe</label><br>
                <input type="text" id="uUnsubscribe" autocomplete="off" class="searchBox" style="width: 70%;" readonly>
            </p>
            <p>
                <label class="font-size12">Added</label><br>
                <input type="text" id="uAdded" autocomplete="off" class="searchBox" style="width: 70%;" readonly>
            </p>
            <p>
                <label class="font-size12">Free autolikes</label><br>
                <input type="text" id="ufreeAutoLikes" autocomplete="off" class="searchBox" style="width: 70%;" readonly>
            </p>
            <p>
                <label class="font-size12">Free autolikes number</label><br>
                <input type="text" id="uFreeAutoLikesNo" autocomplete="off" class="searchBox" style="width: 70%;" readonly>
            </p>
            <p>
                <label class="font-size12">Last login</label><br>
                <input type="text" id="uLastLogin" autocomplete="off" class="searchBox" style="width: 70%;" readonly>
            </p>
            <p>
                <label class="font-size12">Password updated</label><br>
                <input type="text" id="uPwdUpdate" autocomplete="off" class="searchBox" style="width: 70%;" readonly>
            </p>
            <p>
                <label class="font-size12">Reset pwtime</label><br>
                <input type="text" id="uResetPwdTime" autocomplete="off" class="searchBox" style="width: 70%;" readonly>
            </p>

        </div>
        <div class="box3 dshadow">
            <a class="fontBold">Customer notes</a>
            <p>
                <textarea class="searchBox" style="width: 100%;" rows="5" id="customerNotesId"> </textarea>
            </p>
            <!-- <button class="modal-button btn3" id="viewNotesBtnId"  href="#viewNotesModal"
                style="width:110px;float:left;">View notes</button> -->
            <button class="btn3" id="notesBtnId"  style="width:90px;" onclick="submitCustomerNotes();">Submit</button>
            <br><br><br>
            <div id="allNotesId">
            </div>
        </div>
    </div>
    <!-- The Modal -->
    <div id="submitReportModal" class="modal">

        <!-- Modal content -->
        <div class="modal-content" style="max-height: 600px;overflow-y: auto;">
            <span class="close" style="margin-top:-10px">&times;</span>
            <span class="fontBold">Submit Report</span>
            <br>
            <br />
            <select id="orderSelectId" class="searchBox">
                <?= $htmOption ?>
            </select>
            <br>
            <br>
            <textarea class="searchBox" id="submitReportId" placeholder="Enter report..." style="width: 100%;" rows="6"></textarea>
            <button class="btn3" style="width:100px" onclick="submitReportOnEmail();">Submit</button>
        </div>

    </div>


    <div id="searchEmailModal" class="modal">

        <!-- Modal content -->
        <div class="modal-content" style="max-height: 600px;overflow-y: auto;">
            <span class="close" style="margin-top:-10px">&times;</span>
            <span class="fontBold">View Notes</span>
            <div id="allSearchedEmailId" style="white-space: pre-line;display:grid;word-break: break-all;">


            </div>
        </div>

    </div>

    <div id="emailSendModal" class="modal">

            <div class="modal-content" style="max-height: 600px;overflow-y: auto;">
                <span class="close" style="margin-top:-10px">&times;</span>
                <span class="fontBold">Send Email</span>
                <br>
                <br />
                    <input class="searchBox" style="width: 100%;" type="text" placeholder="Send to" id="emailTo">
                <br>
                <br />
                    <input class="searchBox" style="width: 100%;" type="text" placeholder="Enter subject" id="emailSubject">
                <br>
                <br>
                <textarea class="searchBox" id="emailMessage" placeholder="Enter message..." style="width: 100%;" rows="6"></textarea>
                <button class="btn3" style="width:100px;margin-top: 10px;" onclick="sendEmail();">Send</button>
            </div>

    </div>

    <div id="attachmentModal" class="modal">

<!-- Modal content -->
    <div class="modal-content" style="max-height: 600px;overflow-y: auto;">
        <span class="close" style="margin-top:-10px">&times;</span>
        <span class="fontBold">Attachments</span>
        <br>
        
        <div>
            <ul id="attachmentListId">

            </ul>

        </div>
        <br>
        <br>
    </div>

    </div>
    <!-- ////////////////////////// /////// javascript ///////////////////////////// -->
    <script>
        var acc = document.getElementsByClassName("accordion");
        var i;

        for (i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {
                // this.classList.toggle("active");
                this.style.background = "#00000";
                // var panel = this.nextElementSibling;
                // if (panel.style.display === "block") {
                //   panel.style.display = "none";
                // } else {
                //   panel.style.display = "block";
                // }
            });
        }

        // Get the button that opens the modal
        var btn = document.querySelectorAll("button.modal-button");

        // All page modals
        var modals = document.querySelectorAll('.modal');

        // Get the <span> element that closes the modal
        var spans = document.getElementsByClassName("close");
        // When the user clicks the button, open the modal
        for (var i = 0; i < btn.length; i++) {
            btn[i].onclick = function(e) {
                e.preventDefault();
                modal = document.querySelector(e.target.getAttribute("href"));
                modal.style.display = "block";
            }
        }

        // When the user clicks on <span> (x), close the modal
        for (var i = 0; i < spans.length; i++) {
            spans[i].onclick = function() {
                for (var index in modals) {
                    if (typeof modals[index].style !== 'undefined') modals[index].style.display = "none";
                }
            }
        }

        // When the user clicks anywhere outside of the modal, close it
        // window.onclick = function(event) {
        //     if (event.target.classList.contains('modal')) {
        //         for (var index in modals) {
        //             if (typeof modals[index].style !== 'undefined') modals[index].style.display = "none";
        //         }
        //     }
        // }



        $("#searchEmailsBtnId").click(function() {
            searchOperations('email');
        })

        function populateAutoReply(key) {
            var autoReply = $("#autoReply" + key).html().trim();

            $("#supportReplyId").val(autoReply);

            $("#supportReplyId").each(function () {
  this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
});

            $('html, body').animate({
                scrollTop: $("#supportReplyId").offset().top - 300
            }, "fast");

          return false;


        }


        function getSpamEmails(id) {
        $("#cover-spin").show();
        $.ajax({

            url: '/admin/email-support-handler.php',
            type: 'POST',
            data: {
                'type': 'spamEmails'
            },
            dataType: 'json',
            success: function(data) {

                var i;
                var htm = "";
                if(data != null){
                    for (i = 0; i < data.length; i++) {
                                         htm += '<a href="email-support.php?spamEmail=1&email='+ data[i].emailId.replace("+", "thisisaplusspace") +'&amp;id=leftMenuSpamEmails'+ i +'" class="supportemails" id="leftMenuSpamEmails'+ i +'">'+
                                                 data[i].emailId + 
                                            '<i class="fa fa-caret-right caret" aria-hidden="true"></i>'+
                                         '</a>';
                    }
                    $("#spamEmailCountId").html("(" + data.length + ")");
                }else{
                    htm = '<span style=\"text-align:center;display: block;padding-bottom: 17px;\">All done spam done!</span>';
                }
              
               
                $("#spamEmailsId").html(htm);
                $("#cover-spin").hide();
                $('#revealSpamBtnId').hide();
                $("#"+id).addClass('highlightClassLeftMenu');
            },

        });
    }


        function showMoreEmail(key, type, id, emailType) {

            if (type == "Popup") {//SHOW MESSAGE

                if ($("#" + type + "showMoreBtn" + key).text() == "Show Message") {
                    $("#" + type + "showMoreBtn" + key).text("Hide Message");
                    $("#" + type + "NextDisplayChar" + key).show();
    


                } else {//HIDE MESSAGE
                    $("#" + type + "showMoreBtn" + key).text("Show Message");
                    $("#" + type + "NextDisplayChar" + key).hide();

                }

            } else {
                var val;
                if ($("#showMoreBtn" + key).text() == "Show Message") {
                            val = 0;
                    } else {
                            val = 1;
                    }
                
            $("#cover-spin").show();
            $.ajax({

                url: '/admin/email-support-handler.php',
                type: 'POST',
                data: {
                    'type': 'showMoreEmail',
                    'id': id,
                    "value": val,
                    "emailType": emailType
                },
                dataType: 'json',
                success: function(data) {
                    // alert(data.Message);
                    $("#cover-spin").hide();
                    if ($("#showMoreBtn" + key).text() == "Show Message") {
                             $("#showMoreBtn" + key).text("Hide Message");
                             //$("#NextDisplayChar" + key).show();

                             $("#NextDisplayChar" + key).parent().parent().removeClass("hidemessagefull");
                             $("#NextDisplayChar" + key).parent().parent().addClass("showmessagefull");
                    } else {
                             $("#showMoreBtn" + key).text("Show Message");
                            // $("#NextDisplayChar" + key).hide();
                             $("#NextDisplayChar" + key).parent().parent().removeClass("showmessagefull");
                             $("#NextDisplayChar" + key).parent().parent().addClass("hidemessagefull");
                    }
                  
                },

            });
        }

        }

        function viewMoreAutoReplies() {

            if ($("#viewMoreReplyBtnId").text() == "+ View all auto replies") {
                $("#viewMoreReplyBtnId").text("- Hide all auto replies");
                $("#hideMoreAutoReplies").show();
            } else {
                $("#viewMoreReplyBtnId").text("+ View all auto replies");
                $("#hideMoreAutoReplies").hide();
            }
        }

        function replyOnEmail() {
            var emailUid = <?=$globLastEmailUid?>;
            var reply = $("#supportReplyId").val().trim();
            if (reply == "") {
                alert("reply can't be blank");
                return;
            }
            var subject = $("#customerEmailSubjectId").html().trim();
            var emailId = $("#customerEmailId").text().trim();

            $("#cover-spin").show();
            $.ajax({

                url: '/admin/email-support-handler.php',
                type: 'POST',
                data: {
                    'type': 'replyOnEmail',
                    'emailUid': emailUid,
                    'emailId': emailId,
                    'reply': reply,
                    'subject': subject
                },
                dataType: 'json',
                success: function(data) {

                    //alert(data.Message);
                    location.reload();
                    $("#cover-spin").hide();
                },

            });
        }


        function markDone() {

            let text = "Are you sure to mark it done?";
            if (confirm(text) == true) {
                var emailId = $("#customerEmailId").text().trim();

                $("#cover-spin").show();
                $.ajax({

                    url: '/admin/email-support-handler.php',
                    type: 'POST',
                    data: {
                        'type': 'markDone',
                        'emailId': emailId,
                    },
                    dataType: 'json',
                    success: function(data) {

                        //alert(data.Message);
                        window.location.href = "email-support.php";
                    },

                });
            }

        }

        function blockConversation() {
            var value= $('#deleteBtnId').attr('data-value');
            let text = "";
            if(value == 0){
                 text = "Are you sure to unblock this conversation?";
            }else{
                 text = "Are you sure to block this conversation?";
            }

            if (confirm(text) == true) {
                var emailId = $("#customerEmailId").text().trim();

                $("#cover-spin").show();
                $.ajax({

                    url: '/admin/email-support-handler.php',
                    type: 'POST',
                    data: {
                        'type': 'blockConversation',
                        'emailId': emailId,
                        'value':value
                    },
                    dataType: 'json',
                    success: function(data) {

                        alert(data.Message);
                        window.location.href = "email-support.php";
                    },

                });
            }
        }

        function searchOperations(searchType) {
            var key;
            var emailId = $("#customerEmailId").text().trim();
            switch (searchType) {
                case "order":
                    key = $("#searchOrderId").val();
                    if (key.trim() == "") {
                        alert("key can't be blank");
                        $("#searchOrdersDivId").html("No Records");
                        return;
                    }
                    break;
                case "user":
                    key = $("#searchUserId").val();
                    if (key.trim() == "") {
                        alert("key can't be blank");
                        $("#searchUsersDivId").html("No Records");
                        return;
                    }
                    break;
                case "email":
                    key = $("#searchEmailId").val();

                    if (key.trim() == "") {
                        alert("key can't be blank");
                        $("#allSearchedEmailId").html("No Records");
                        return;
                    }
                    break;

            }

            $("#cover-spin").show();
            $.ajax({

                url: '/admin/email-support-handler.php',
                type: 'POST',
                data: {
                    'type': 'searchOperations',
                    'searchType': searchType,
                    'key': key,
                    'emailId': emailId
                },
                dataType: 'json',
                success: function(data) {
                    var elem;
                    var paramType;
                    var page;
                    switch (searchType) {
                        case "order":
                            $("#searchOrdersDivId").html("");
                            elem = $("#searchOrdersDivId");
                            paramType = "orderid";
                            page = "check-user.php";
                            break;
                        case "user":
                            $("#searchUsersDivId").html("");
                            elem = $("#searchUsersDivId");
                            paramType = "user";
                            page = "check-accounts.php";
                            break;
                        case "email":
                            $("#allSearchedEmailId").html("");
                            elem = $("#allSearchedEmailId");
                            break;

                    }

                    var i;
                    var htm = "";
                    if (data != null) {
                        var record;
                        if (searchType == 'email') {
                            var i;
                            var htm = "";
                            var emailUnixTime = "";
                            var emailDate = "";
                            var emailTime = "";
                            var firstDisplayChar = "";
                            var NextDisplayChar = "";
                            var classForDisplayMessage = "";
                            var btnText = "";
                            for (i = 0; i < data.length; i++) {
                                emailUnixTime = timeConverter(data[i].emailDate).split("-");
                                emailDate = emailUnixTime[0];
                                emailTime = emailUnixTime[1];
                                firstDisplayChar = data[i].subject;

                                if (i < data.length - 1) {
                                    classForDisplayMessage = "NextDisplayChar";
                                    btnText = "Show Message";
                                } else {
                                    classForDisplayMessage = "";
                                    btnText = "Hide Message";
                                }
                                NextDisplayChar = '<span class="' + classForDisplayMessage +
                                    '" id="PopupNextDisplayChar' +
                                    i + '"><hr/><br/>' + data[
                                        i].email + '</span>';

                                htm = '' +
                                    '<div class="txtSize accordion1" style="min-height: 110px;">' +
                                    '    <a class="fontBold ">' + emailId + '</a><br>' +
                                    '        <div>' +
                                    '            <a class="pull-right">' + emailDate + ' ' + emailTime +
                                    '</a><br>' +
                                    '            <button class="btn3" style="width:170px;" id="PopupshowMoreBtn' +
                                    i +
                                    '"  onclick="showMoreEmail(' + i + ',\'Popup\', \'\', \'\');">' + btnText +
                                    '</button>' +
                                    '        </div>' +
                                    '    <div>' +
                                    '        <div><span>' +
                                    '          ' + firstDisplayChar + '</span>' + NextDisplayChar + '  ' +
                                    '        </div> ' +
                                    '    </div>' +
                                    '</div>';
                                elem.append(htm);
                            }
                        } else {
                            for (i = 0; i < data.length; i++) {
                                record = data[i].record.split("-");
                                record = record[0].replace('#', '');
                                htm = '' +
                                    '<a class="supportemails" href="' + page + '?' + paramType + '=' + record +
                                    '" target="_blank" class="fontBold txtSize accordion">' +
                                    '    ' + data[i].record + ' ' +
                                    '    <i class="fa fa-caret-right caret" aria-hidden="true"></i>' +
                                    '</a>';
                                elem.append(htm);
                            }
                        }


                    } else {
                        elem.html("No Records");
                    }

                    $("#cover-spin").hide();
                },

            });
        }

        function submitReportOnEmail() {
            var emailUid = <?=$globLastEmailUid?>;
            var emailsubject = "<?= trim(addslashes($recentSubject))?>";
            var report = $("#submitReportId").val().trim();
            if (report == "") {
                alert("Report can't be blank");
                return;
            }
            var orderId = $("#orderSelectId").val();
            // if (orderId == "") {
            //     alert("Please select orderId");
            //     return;
            // }
            var emailId = $("#customerEmailId").text().trim();

            $("#cover-spin").show();
            $.ajax({

                url: '/admin/email-support-handler.php',
                type: 'POST',
                data: {
                    'type': 'submitReportOnEmail',
                    'emailId': emailId,
                    'orderId': orderId,
                    'report': report,
                    'emailUid': emailUid,
                    'emailsubject': emailsubject
                },
                dataType: 'json',
                success: function(data) {

                    alert(data.Message);
                    location.reload();
                },

            });
        }

        function submitCustomerNotes() {
            var notes = $("#customerNotesId").val().trim();
            if (notes == "") {
                alert("notes can't be blank");
                return;
            }
            var emailId = $("#customerEmailId").text().trim();

            $("#cover-spin").show();
            $.ajax({

                url: '/admin/email-support-handler.php',
                type: 'POST',
                data: {
                    'type': 'submitCustomerNotes',
                    'emailId': emailId,
                    'notes': notes
                },
                dataType: 'json',
                success: function(data) {

                    alert(data.Message);
                    $("#customerNotesId").val('');
                    $("#cover-spin").hide();
                    getCustomerNotes();
                },

            });
        }

        function getCustomerNotes() {
            var emailId = $("#customerEmailId").text().trim();

            $("#cover-spin").show();
            $.ajax({

                url: '/admin/email-support-handler.php',
                type: 'POST',
                data: {
                    'type': 'getCustomerNotes',
                    'emailId': emailId,
                },
                dataType: 'json',
                success: function(data) {
                    $("#allNotesId").html('');


                    var i;
                    var htm = "";
                    var emailUnixTime = "";
                    // var emailDate = "";
                    // var emailTime = "";
                    if (data != null) {
                        for (i = 0; i < data.length; i++) {
                            emailUnixTime = timeConverter(data[i].dateAdded).split("-");
                            // emailDate = emailUnixTime[0];
                            // emailTime = emailUnixTime[1];

                            htm = '' +
                                '<div class="txtSize accordion1">' +
                                '        <div><span>' +
                                '          ' + data[i].notes + '</span>' +
                                '        </div> ' +
                                '        <div>' +
                                '            <a style="font-size: 10px;">' + emailUnixTime + '</a> <br> ' +
                                // '            <a>' + emailDate + '</a><br>' +
                                '        </div>' +
                                '</div>';
                            $("#allNotesId").append(htm);
                        }
                    } else {
                        $("#allNotesId").append("No records");
                    }

                    $("#cover-spin").hide();
                },

            });
        }

       
        document.addEventListener('DOMContentLoaded', function() {
                getCustomerNotes();
                getCustomerInfo();
                var url = window.location.href; 
                var url = new URL(url); 
                var id = url.searchParams.get("id");    
                $("#"+id).addClass("highlightClassLeftMenu");

                var spamEmail = url.searchParams.get("spamEmail");  

                if(spamEmail == '1'){
                    getSpamEmails(id);
                }
                
        }, false);
        function getCustomerInfo() {

            var emailId = $("#customerEmailId").text().trim();

            $("#cover-spin").show();
            $.ajax({

                url: '/admin/email-support-handler.php',
                type: 'POST',
                data: {
                    'type': 'getCustomerInfo',
                    'emailId': emailId,
                },
                dataType: 'json',
                success: function(data) {

                    $('#customerInfoDivId').find('input:text').val('');

                    $("#uName").val(data.username);
                    $("#uOrder").val(data.orders);
                    $("#uUnsubscribe").val(data.unsubscribe);
                    if (typeof data.added != "undefined")
                        $("#uAdded").val(timeConverter(data.added).split("-")[0]);
                    $("#ufreeAutoLikes").val(data.freeautolikes);
                    $("#uFreeAutoLikesNo").val(data.freeautolikesnumber);
                    if (typeof data.lastlogin != "undefined")
                        $("#uLastLogin").val(timeConverter(data.lastlogin).split("-")[0]);
                    if (typeof data.passwupdated != "undefined")
                        $("#uPwdUpdate").val(timeConverter(data.passwupdated).split("-")[0]);
                    $("#uResetPwdTime").val(data.resetpwtime);

                },

            });
        }

        function timeConverter(UNIX_timestamp) {
            var a = new Date(UNIX_timestamp * 1000);
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            var year = a.getFullYear();
            var month = months[a.getMonth()];
            var date = a.getDate();
            var hour = a.getHours();
            var min = a.getMinutes();
            var sec = a.getSeconds();

            if (hour < 10) {
                hour = '0' + hour;
            }
            if (min < 10) {
                min = '0' + min;
            }
            // var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec;
            var time = date + ' ' + month + ' ' + year + '-' + hour + ':' + min;

            return time;
        }
        window.addEventListener("scroll", (event) => {
            let scroll = this.scrollY;
            // alert(scroll)
            if (scroll > 50) {
                $('.headerAllEmail').addClass("stickyheaderall");
                //$('.headerAllEmail').css("top", 0);
            } else {
                $('.headerAllEmail').removeClass("stickyheaderall");
            }
        });


        $(".box1").height($(document).height());
        $(".box2").height($(document).height());

        $("#supportReplyId").each(function () {
          this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
        }).on("input", function () {
          this.style.height = 0;
          this.style.height = (this.scrollHeight) + "px";
        });

        function sendEmail() {
            var emailTo = $('#emailTo').val().trim();
            var emailsubject = $('#emailSubject').val().trim();
            var emailMessage = $("#emailMessage").val().trim();
           
            if (emailTo == "") {
                alert("Email to can't be blank");
                return;
            }
            if (emailsubject == "") {
                alert("Email Subject can't be blank");
                return;
            }
            if (emailMessage == "") {
                alert("Email message can't be blank");
                return;
            }
            $("#cover-spin").show();
            $.ajax({

                url: '/admin/email-support-handler.php',
                type: 'POST',
                data: {
                    'type': 'sendEmail',
                    'emailTo': emailTo,
                    'emailsubject': emailsubject,
                    'emailMessage': emailMessage,
                },
                dataType: 'json',
                success: function(data) {
                    $("#cover-spin").hide();
                    alert(data.Message);
                    location.reload();
                },

            });
        }

        function openModalSendEmail(){
            $("#emailSendModal").attr('style','display:block;')
        }
        function openAttachmentsModal(id){

var emailId = id;

if (emailId == "") {
    alert("Id can't be blank");
    return;
}
$("#cover-spin").show();
$.ajax({

    url: '/admin/email-support-handler.php',
    type: 'POST',
    data: {
        'type': 'attachments',
        'emailId': emailId,
    },
    dataType: 'json',
    success: function(data) {
        $("#cover-spin").hide();

        if(data !=null){
            var i;
            var htm = "";
            for(i = 0; i <data.length ;i++){
                             ext =  get_url_extension(data[i].attachmentFilePath);
                            if(ext == "jpg" || ext == "png"){
                                htm += '<li><img src="'+ data[i].attachmentFilePath +'" style="width:200px"></li>';

                            }else{
                                htm += '<li><a href="'+ data[i].attachmentFilePath +'" target="_blank">file'+ (i+1) +'</a></li>';
                            }
            }
            $('#attachmentListId').html(htm);
        }else{
            $('#attachmentListId').html('No Attachments');
        }

        $("#attachmentModal").attr('style','display:block;')

    },

});

}

function get_url_extension( url ) {
            return url.split(/[#?]/)[0].split('.').pop().trim();
        }
    </script>

</body>


</html>