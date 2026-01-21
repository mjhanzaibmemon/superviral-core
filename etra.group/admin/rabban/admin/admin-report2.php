<?php

include 'adminheader.php';

function ago($time)
{
    $periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
    $lengths = array("60", "60", "24", "7", "4.35", "12", "10");
    $now = time();
    $difference     = $now - $time;
    $tense         = 'ago';
    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
        $difference /= $lengths[$j];
    }
    $difference = round($difference);
    if ($difference != 1) {
        $periods[$j] .= "s";
    }
    return "$difference $periods[$j] ago";
}

$reportdone = addslashes($_POST['reportdone']);
$directions = addslashes($_POST['directions']);

if(!empty($reportdone)){

    ////NOTIFY THE USER HERE VIA EMAIL

$findreportinfoq = mysql_query("SELECT * FROM `admin_notifications` WHERE `id` = '$reportdone' LIMIT 1");
$findreportinfo = mysql_fetch_array($findreportinfoq);

    $to = $findreportinfo['emailaddress'];
    $subject = 'Superviral: Update on your issue';

////////////////////////////////////////////// THE EMAIL GOES HERE




$emailbody = '<p>Hi there,</p>
<br>
<p>This is Helen from Superviral\'s management team to just notify you that I\'ve received your issue and I\'ve given additional support to James to help resolve this issue as soon as possible.</p>
<br>
<p>Speaking to James, he\'s advised me that he will respond within the next 19-24 hours and is available to respond to your issue.</p>
<br>
<p>Other Superviral teams usually get involved with an issue when a customer service rep, requires the assistance of more than one team that specialises in that issue.</p>
<br>
<p>For example, when there\'s a technical issue, our technicians would get involved to diagnose the issue as they\'re trained to deal with those type of issues.</p>
<br>
<p>If there is anything else you need, please do not hesitate to get in touch with my colleague James regarding the issue. He will respond within the next 19-24 hours.</p>
<br>
<p>Thank you for your patience.</p>
<br>
<p>Kind regards,<br>
Superviral Management Team</p>
<br>
<p>160 City Road<br>
London<br>
EC1V 2NX<br>
United Kingdom</p>';

$tpl = file_get_contents('../emailtemplate/emailtemplate.html');
$tpl = str_replace('{body}',$emailbody,$tpl);
$now = time();

$tpl = str_replace('Unsubscribe','',$tpl);

$tpl = str_replace('{subject}',$subject,$tpl);

include('../crons/emailer.php');
emailnow($to,'Superviral','support@superviral.io',$subject,$tpl);

//////////////////////////////////////////////




    $respondedby = $_SESSION['admin_user'];
    $response = time();
	$directions = nl2br($directions);
	mysql_query("UPDATE `admin_notifications` SET `directions` = '$directions',`done` = '1',`response` = '$response',`respondedby` = '$respondedby' WHERE `id` = '$reportdone' LIMIT 1");
    if(!empty($gotopage))header('Location: /admin/admin-report.php?page='.$gotopage);

}

if (isset($_GET['page'])) {
    $page = addslashes($_GET['page']);
} else {
    $page = 1;
}
if ($page < 1) $page = 1;

$no_of_records_per_page = 1;
$offset = ($page - 1) * $no_of_records_per_page;

$q = mysql_query("SELECT * FROM `admin_notifications` WHERE `done` = '0' ORDER BY `id` LIMIT $offset, $no_of_records_per_page");


$q2 = mysql_query("SELECT * FROM `admin_notifications` WHERE `done` = '0' ORDER BY `id` DESC");
$totalleft = mysql_num_rows($q2) . ' Reports Remaining';

$info = mysql_fetch_array($q);
$getreportedinfoq = mysql_query("SELECT * FROM `orders` WHERE `emailaddress` = '{$info['emailaddress']}' order by id desc LIMIT 10");
$getreportedinfo = mysql_fetch_array($getreportedinfoq);
$orderCount = mysql_num_rows($getreportedinfoq);

$orderHistory = "";
$l = 1;

if(!empty($getreportedinfo)){

    while($orderInfo = mysql_fetch_array($getreportedinfoq)){




        $orderHistory .= '<tr><td>#' . $orderInfo['id'] . '</td><td id="statusBind'.$l.'">' . $orderInfo['packagetype'] . '</td><td id="startCountBind'.$l.'">0</td><td>' . $orderInfo['fulfill_id'] . '</td></tr>';
        $orderArr[] = $orderInfo['fulfill_id'];
        $l++;
    
    }
}



//  get auto response

$searchKey = addslashes($_POST['searchKey']);
$searchQueryAdd = "";
if(!empty($searchKey)){
        $searchQueryAdd = " WHERE `response` LIKE '%$searchKey%' ";
}

if(addslashes($_POST['UP']) || addslashes($_POST['DOWN'])){
    $countAutoResponse = mysql_fetch_array(mysql_query("SELECT max(position) as `position` FROM auto_response $searchQueryAdd order by position asc"));
    $countAutoResponse2 = mysql_fetch_array(mysql_query("SELECT min(position) as `position` FROM auto_response $searchQueryAdd order by position asc"));


    $resId = (int) addslashes($_POST['resId']);
    $sort = (int) addslashes($_POST['sort']);

    $update_pid = false;
    if (addslashes($_POST['UP'] == "UP")) {
        $update_pid = '-';
        $update_other = '+';
        $sort_other = $sort - 1;
    } elseif (addslashes($_POST['DOWN'] == "DOWN")) {
        $update_pid = '+';
        $update_other = '-';
        $sort_other = $sort + 1;
    }

    if ($update_pid) {
        if($sort_other == 0){
            $sort_other = 1;
        }
        if($sort_other <= $countAutoResponse['position'] && $sort_other >= $countAutoResponse2['position']){
            $query_one = "
            UPDATE auto_response 
            SET `position` = `position` " . $update_other ." 1 
            WHERE `position` = " . $sort_other;
            mysql_query($query_one);
       
            $query_two = "
            UPDATE auto_response 
            SET `position` = `position` " . $update_pid ." 1 
            WHERE id = " . $resId;
            mysql_query($query_two);
        }
       
    }
}

$autoResponseText = addslashes($_POST['autoResponseText']);
if(!empty($autoResponseText)){

        // getposition

        $query = "SELECT MAX(`position`) as pos FROM auto_response";
        $positionArr = mysql_fetch_array(mysql_query($query));
        $position = (int) $positionArr['pos'];
        $nextPostion = $position + 1;
        $query = "
        INSERT INTO auto_response 
        SET `response` = '$autoResponseText', `position` = '$nextPostion'";
        mysql_query($query);
   

}

if(addslashes($_POST['Delete'])){
    $resId = (int) addslashes($_POST['resId']);
    if (!empty($resId)) {
        
            $query = "
            DELETE FROM auto_response 
            WHERE `id` = " . $resId;
            mysql_query($query);
       
    }
}

$autoResponseQuery = mysql_query("SELECT * FROM auto_response $searchQueryAdd order by position,id asc");

$autoResponse = "";
$k = 1;
while($autoResponseArr = mysql_fetch_array($autoResponseQuery)){

    $autoResponse .= '<span style="float:left;padding:10px; background-color: #f5f7fe;width:97%;margin-bottom:10px;" class="foo">
        '. $autoResponseArr['response'] .'
        <br>
        <textarea id="textAreaAR'. $k .'" style="max-height: 0px;opacity:0;
        /* display: none; */
        min-height: 0px !important;
        width: 0px;
        resize: none;" class="language-less">
        '. $autoResponseArr['response'] .'
        </textarea>
        <button class="btn3" style="margin-top: 23px; float: right; width: 75px; margin-left: 0;" onclick="copyToTextArea(\'textAreaAR'. $k .'\')">Copy #</button>
        <form method="post">
            <input type="hidden" name="resId" value="'. $autoResponseArr['id'] .'">
            <input type="hidden" name="sort" value="'. $autoResponseArr['position'] .'">
            <button class="btn3" value="Delete" onclick="return confirm(\'Are you sure you want to delete?\');" name="Delete"  style="margin-top: 4px; float: none; width: 75px;">Delete</button>
            <button class="btn3" value="UP" name="UP" style="margin-top: 5px; float: none; width: 75px;">Up</button>
            <button class="btn3" value="DOWN" name="DOWN" style="margin-top: 5px; float: none; width: 75px;">Down</button>
        </form>
    </span>';
$k++;
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Report</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/orderform.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.2/clipboard.min.js"></script>
    <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
    <script src="https://kit.fontawesome.com/a282321041.js" crossorigin="anonymous"></script>

    <!-- ////////////////////////////////////////// css /////////////////////////////////////// -->

    <style type="text/css">
        html,
        body {
            height: 100%;
            background-color: #f5f7fe !important;
        }

        .highlightClassLeftMenu {
            background-color: #000000;
            color: #fff !important;
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

         .mainContainer {
    padding: 13px;
        } 

        .box1 {
            margin: 1%;
            /* display: inline-block; */
            width: 25%;
            border-radius: 5px;
            text-align: left;
            float: left;
            height: 100%;
        }

        .box1 .containers {
            margin-bottom: 20px;
            background: #fff;
        }

        .box2 {

            /* display: inline-block; */
            width: 50%;
            border-radius: 5px;
            text-align: left;
            float: left;
            min-height: 1360px;
                margin-top: 17px;
        }

        .box3 {
            margin: 1%;
            /* display: inline-block; */
            width: 18%;
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
            color: #000;
            background: #fff;
            border: 1px solid #bbb;
            padding: 10px;
            font-weight: 400;
            border-radius: 30px !important;
            cursor: pointer;
            width: 130px;
            float: right;
            margin-left: 5px;
            font-weight: bold;
            font-size: 13px;
            z-index: 999;
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
            /* border-bottom: 1px solid #ccc; */
            position: relative;
        }

        .rightdetails {
            position: absolute;
            right: 22px;
            top: 24px;
            z-index: 999;
        }

        .rightdetails .timestamp {
            display: block;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: right;
        }

        /* .autoReplySec {
        background-color: #e2e2e2;
    } */

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
            /* position: fixed; */
            /* width: 52%; */
            background: white;
            padding: 11px;
            box-sizing: border-box;
            padding-bottom: 65px;
            z-index: 9990;
        }

        .bodyAllEmail {
            /* height: 1000px; */
            width: 100%;
            margin-top: 28px;
        }

        .revealbtn {
            padding: 10px 6px;
        }

        #orderSelectId {
            height: 43px;
        }

        .recip {
            /* margin-top: -23px; */
            display: block;
            color: #d03340;
        }

        textarea {
            font-family: "Open Sans", sans-serif;
            min-height: 150px;
            background: #fff;
        }

        .emailbody {
            line-height: 32px;
            width: 670px;
        }

        .showmessagefull {
            background: #fff;
            min-height: 110px;
        }




        .hidemessagefull {
            background: rgb(120, 120, 120);
            background: linear-gradient(0deg, rgb(183 183 183) 0%, rgb(217 217 217) 100%);
            height: 69px;
        }

        .hidemessagefull .emailbody {
            opacity: 0.6;
        }

        .hidemessagefull .fontBold {
            opacity: 0.6;
        }

        .supportemails {
            color: #000;
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
            text-overflow: ellipsis;
        }

        .containerheading {
            display: block;
            padding: 15px;
            font-weight: bold;
        }

        #customerOrdersId {
            overflow: scroll;
            max-height: 500px;
        }

        .initialmsg {
            padding: 12px;
            background: #c8f9fb;
            font-style: italic;
            margin-top: 20px;
            margin-bottom: 14px;
            border-radius: 5px;
        }

        #supportReplyId {
            width: 100%;
            min-height: 216px
        }

        .autoreplyselect {
            min-height: 125px;
            padding: 10px;
        }

        .leftColHeadng {
            margin-left: 15px;
            font-size: 15px;
            font-weight: 600;
        }

        .leftColSubHeadng {
            font-size: 14px;
            color: #049bf4;
            margin-left: 15px;
        }

        .articles a {
            text-decoration: underline;
            color: blue;
        }

        .articles {
            width: 100%;
        }

        .articles tr td {
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 10px 10px;
            vertical-align: top;
        }

        .articles tr:first-child td {
            background: #f1f1f1;
            font-weight: bold;
            padding: 10px;
        }
    </style>

</head>

<!-- /////////////////////////////////////////// html body ///////////////////////////////////////////// -->

<body>
    <div id="cover-spin"></div>
    <?= $header ?>
    <div class="mainContainer" align="center">
        <div class="box1">


            <div class="containers dshadow" style="padding-bottom: 20px;">
                <div class="containerheading">Customer Info</div>
                <span class="leftColHeadng">
                    Order id
                </span>
                <br>
                <span class="leftColSubHeadng foo">
                    #<?= $info['orderid'] ?>
                    <textarea style="max-height: 0px;
    /* display: none; */
    min-height: 0px !important;
    width: 0px;
    resize: none;" class="language-less">#<?= $info['orderid'] ?></textarea>
                        <a href="#0"  style="margin: 0px;margin-top: -15px !IMPORTANT;width:80px;margin-bottom: -13px;    margin-right: 10px;" class="btn btn3 copy-button">Copy #</a>
                </span>
                <br>
                <br>
                <span class="leftColHeadng">
                    xyz
                </span>
                <br>
                <span class="leftColSubHeadng foo">
                    #0000000
                    <textarea style="max-height: 0px;
    /* display: none; */
    min-height: 0px !important;
    width: 0px;
    resize: none;">#0000000</textarea>
                    <a href="#0" style="margin: 0px;margin-top: -15px !IMPORTANT;width:80px;margin-bottom: -13px;    margin-right: 10px;" class="btn btn3 copy-button">Copy #</a>

                </span>

                <br>
                <br>
                <span class="leftColHeadng">
                    Email address
                </span>
                <br>
                <span class="leftColSubHeadng foo">
                    <span><?= $info['emailaddress'] ?></span>
                    <textarea style="max-height: 0px;
    /* display: none; */
    min-height: 0px !important;
    width: 0px;
    resize: none;" type="hidden" ><?= $info['emailaddress'] ?></textarea>
                <a href="#0" style="margin: 0px;margin-top: -15px !IMPORTANT;width:80px;    margin-right: 10px;" class="btn btn3 copy-button">Copy #</a>
                </span>

            </div>

            <div class="containers dshadow">
                <div class="containerheading">Order History</div>
                <div>
                    <table class="articles">

                        <tbody>
                            <tr>
                                <td style="background: white;   border-right: none;">Order #</td>
                                <td style="background: white;   border-right: none;">Status</td>
                                <td style="background: white;   border-right: none;">Start count</td>
                                <td style="background: white;   border-right: none;">Fulfillment ID</td>
                            </tr>

                            <?= $orderHistory ?>
                        </tbody>
                    </table>
                </div>
            </div>


        </div>

        <div class="box2">
            <div class="headerAllEmail dshadow">

                <div class="box23" style="color: grey;">

                    <a href="?page=0" class="btn btn3" style="margin-left:0px;float: left;margin-top:15px !important;margin-bottom: 15px;">First
                        Report</a>
                    <a href="?page=<?= ($page - 1) ?>" class="btn btn3" style="float: left;margin-top:15px !important;margin-bottom: 15px;margin-left: 15px;">Previous Report</a>
                    <a href="?page=<?= ($page + 1) ?>" class="btn btn3" style="float: left;margin-top:15px !important;margin-bottom: 15px;margin-left: 15px;">Next Report </a>

                    <span style="float:right;margin-top:20px;"><?= $totalleft ?></span>
                </div>

                <table class="articles">

                    <tbody>
                        <tr>

                            <td>Reported Reason</td>
                        </tr>

                        <tr>

                        </tr>
                    </tbody>
                </table>
                <span style="float:left;padding-bottom:10px;    padding-top: 10px;">
                    <?= $info['message'] ?> <br>
                    <span style="font-size:15px;">(<?= $info['admin_name'] ?>- <?= ago($info['added']) ?>, <?= date("d/m/Y H:i:s", $info['added']) ?>)</span>
                </span>
                <form method="POST" action="">
                    <input type="hidden" name="reportdone" value="<?=$info['id']?>">
                    <input type="hidden" name="gotopage" value="<?=$page?>">
                    <textarea class="searchBox" name="directions" id="supportReplyId" placeholder="Enter here..." rows="6"></textarea>
                    <div>
                        <button class="btn3" onclick="return confirm('Are you sure youve dealt with the report?');" id="replyBtnId" style="width:90px;    margin-top: 10px;">Done</button>
                    </div>
                </form>
            </div>

            <div class="bodyAllEmail dshadow">

                <!-- <hr style="margin: 0;"> -->
                <div class="autoReplySec">
                    <div class="containerheading" style="padding-left: 0px;">Auto Response 
                        <a class="modal-button btn3" id="addAutoResponseBtn" style="float: right; width:11%;text-align:center">Add</a>
                    </div>
                    <form action="" method="POST">
                        <div style="margin-top: 16px;">
                            <input type="text" style="width:85%;border-radius: 5px 0px 0px 5px;" class="searchBox" autocomplete="off" value="<?=$searchKey?>" name="searchKey" placeholder="Search conversation by keyword...">
                            <button class="modal-button btn3" style="float: none; width:100px;margin-left:-6px;padding:13px;border: 1px solid;border-radius: 0px !important;">Search</button>
                        </div>
                    </form>
                </div>

                <div class="txtSize autoReplySec" style="padding: 24px;padding-top:0px;height: 350px;display:table;">

                    <?=$autoResponse?>

                </div>
            </div>



        </div>

        <div class="box3 dshadow">
            <a class="fontBold">Calculator</a>
            <p>
                <input type="text" class="searchBox" autocomplete="off" style="width: 100%;" id="inpCalculator">
                <span style="    margin-top: 10px; float: left; color: #24de69;" id="calcResultBind">0</span>
                <button class="btn3" style="    font-size: 17px; margin-top: 5px; float: right; width: 60px;" onclick="calculator()">=</button>
            </p>
            <div id="searchOrdersDivId"></div>
        </div>
        <div class="box3 dshadow">
            <a class="fontBold">Quick Actions</a>
            <p>
            <form action="ordermake-changeusername.php?update=nodefect" method="POST">
                        <input type="hidden" name="id" value="<?=$getreportedinfo['id']?>">
                        <input type="hidden" name="page" value="adminReport">
						<input type="hidden" name="ordersession" value="<?=$getreportedinfo['order_session']?>">
                <input type="text" autocomplete="off" class="searchBox" name="igusername" value="<?= $getreportedinfo['igusername'] ?>" style="width: 42%;">
                <button class="btn3" style="float: none; width:43%;    font-size: 12px;" onclick="return confirm('Are you sure you want to change the username?');">Change
                    username</button>
            </form>
            </p>
            <p>
            <form action="refundorder.php?" method="POST">
                <input type="hidden" name="id" value="<?=$getreportedinfo['id']?>">
                <input type="hidden" name="page" value="adminReport">
				<input type="hidden" name="ordersession" value="<?=$getreportedinfo['order_session']?>">
                <input type="text" autocomplete="off" name="amount" class="searchBox" style="width: 42%;" placeholder="'percentage' or 'full'" value="<?=$getreportedinfo['refundamount']?>">
                <button class="btn3" style="float: none; width:42%;" onclick="return confirm('Are you sure you want to issue the refund?')">Issue
                    refund</button> %
            </form>        
            </p>
            <p>
                <a href="/admin/offer-free-followers.php?id=<?=$getreportedinfo['id']?>" class="btn3" target="_blank" style="margin-left: 0;float: left;width: 36%;    text-align: center;" >Offer
                    followers</a>
                <a href="/admin/offer-free-likes.php?id=<?=$getreportedinfo['id']?>" class="btn3" target="_blank" style="float: left; width:36%;margin-left: 10px;    text-align: center;">Offer
                    likes</a>
            </p>
            <div id="searchUsersDivId"></div>
        </div>


        <!-- The Modal -->
        <div id="addAutoReponseModal" class="modal">

<!-- Modal content -->
<div class="modal-content" style="max-height: 600px;overflow-y: auto;">
    <span class="close" style="margin-top:-10px">&times;</span>
    <span class="fontBold">Add Auto Response</span>
    <br>
    <br />
    <form action="" method="POST">
        <textarea class="searchBox" name="autoResponseText" placeholder="Enter auto response..." style="width: 100%;" rows="6"></textarea>
        <button class="btn3" style="width:100px" >Add</button>
    </form>
   
</div>

</div>


        <!-- ////////////////////////// /////// javascript ///////////////////////////// -->
        <script>
(function(){

// Get the elements.
// - the 'pre' element.


var pre = document.getElementsByClassName('foo');


// Add a copy button in the 'pre' element.
// which only has the className of 'language-'.

for (var i = 0; i < pre.length; i++) {
    var isLanguage = pre[i].children[0].className.indexOf('language-');
    
    /*
    if ( isLanguage === 0 ) {
        var button           = document.createElement('button');
                button.className = 'copy-button';
                button.textContent = 'Copy';

                pre[i].appendChild(button);
    }*/
};

// Run Clipboard

var copyCode = new Clipboard('.copy-button', {
    target: function(trigger) {
        return trigger.previousElementSibling;
}
});

// On success:
// - Change the "Copy" text to "Copied".
// - Swap it to "Copy" in 2s.
// - Lead user to the "contenteditable" area with Velocity scroll.

copyCode.on('success', function(event) {
    event.clearSelection();
    event.trigger.textContent = 'Copied';
    window.setTimeout(function() {
        event.trigger.textContent = 'Copy #';
    }, 2000);

});

// On error (Safari):
// - Change the  "Press Ctrl+C to copy"
// - Swap it to "Copy" in 2s.

copyCode.on('error', function(event) { 
    event.trigger.textContent = 'Press "Ctrl + C" to copy';
    window.setTimeout(function() {
        event.trigger.textContent = 'Copy';
    }, 5000);
});

})();
   

function calculator(){

    var InptVal = $('#inpCalculator').val();

    var plusOperator = InptVal.includes('+');
    var minusOperator = InptVal.includes('-');
    var multiplyOperator = InptVal.includes('*');
    var DivideOperator = InptVal.includes('/');

    var firstDigit = 0;
    var lastDigit  = 0
    var result = 0;
    // if plus
    if(plusOperator){
         firstDigit = InptVal.split('+')[0].trim();
         lastDigit =  InptVal.split('+')[1].trim();
         result = parseFloat(firstDigit) + parseFloat(lastDigit);
    }

     // if minus
     if(minusOperator){
         firstDigit = InptVal.split('-')[0].trim();
         lastDigit =  InptVal.split('-')[1].trim();
         result = parseFloat(firstDigit) - parseFloat(lastDigit);

    }

     // if plus
     if(multiplyOperator){
         firstDigit = InptVal.split('*')[0].trim();
         lastDigit =  InptVal.split('*')[1].trim();
         result = parseFloat(firstDigit) * parseFloat(lastDigit);

    }

     // if plus
     if(DivideOperator){
         firstDigit = InptVal.split('/')[0].trim();
         lastDigit =  InptVal.split('/')[1].trim();
         result = parseFloat(firstDigit) / parseFloat(lastDigit);

    }

    $("#calcResultBind").html(result);

}

            // window.addEventListener("scroll", (event) => {
            //     let scroll = this.scrollY;
            //     // alert(scroll)
            //     if (scroll > 50) {
            //         $('.headerAllEmail').addClass("stickyheaderall");
            //         //$('.headerAllEmail').css("top", 0);
            //     } else {
            //         $('.headerAllEmail').removeClass("stickyheaderall");
            //     }
            // });


            // $(".box1").height($(document).height());
            // $(".box2").height($(document).height());

            // $("#supportReplyId").each(function() {
            //     this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
            // }).on("input", function() {
            //     this.style.height = 0;
            //     this.style.height = (this.scrollHeight) + "px";
            // });

            
               // Get the modal
var modal = document.getElementById("addAutoReponseModal");

// Get the button that opens the modal
var btn = document.getElementById("addAutoResponseBtn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btn.onclick = function() {
  modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
function copyToTextArea(id){

var val = $('#'+id).val();
 $('#supportReplyId').val('');
 $('#supportReplyId').val(val.trim());

}



// AJAX request for status

var countOrderHistory = <?= $orderCount ?>;
var orderArr = <?php echo json_encode($orderArr); ?>;
var jsonString = JSON.stringify(orderArr);


    function sendRequest(){
        
        $.ajax({	
            url: "/admin/fetch_status.php",	
            type: "POST",	
            async: true,	
            dataType:'json',	
            data: {	
            "fulfill_id" : jsonString,	
            },	
            success: function(response){ 	
                var i = 0;
                for (i =0; i<response.status.length;i++){
                    $('#statusBind'+(parseInt(i)+1)).html(response.status[i]);
                    $('#startCountBind'+(parseInt(i)+1)).html(response.startCount[i]);
                    
                }
               
            }	
          })
    }
    sendRequest();


</script>

</body>


</html>