<?php


include('../../../db.php');

session_start();


if(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])){

    if ($_SERVER['HTTP_X_FORWARDED_PROTO']=="http") {
    $url = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $url);
            exit;
    }

}

if(isset($_SESSION['admin_plus_id']) && isset($_SESSION['admin_plus_token']) && $_SESSION['admin_plus_id'] != "" && $_SESSION['admin_plus_token'] != ""){

 

    // success

   $user  = $_SESSION['admin_user'];

 

}else{

    header('Location: /admin/login/' );

}

if($user=='rabban'){

$adminheader = '<div class="adminheader" style="width:85%">
<a href="https://superviral.io/admin/check-user.php">Check Orders</a>
<a href="https://superviral.io/admin/check-al.php">Check AL</a>
<a href="https://superviral.io/admin/email-support.php">Support</a>
<a href="https://superviral.io/admin/orders.php?type=missing">Missing Fulfills</a>
<a href="https://superviral.io/admin/api-stats.php">Check SV Status</a>
<a href="https://superviral.io/admin/tiktok-api-stats.php">Check TT Status</a>
<a href="https://superviral.io/admin/feedback-and-questions.php">Feedback</a>
<div style="float:right;"><a style="color:grey" href="https://superviral.io/admin/article.php">Articles</a>
<a style="color:grey" href="https://superviral.io/admin/defectorders.php">Failed Orders</a>
<a style="color:grey" href="https://superviral.io/admin/emailfunnels.php">Email Funnels</a>
<a style="color:grey" href="https://superviral.io/admin/submitreviews.php">Reviews</a>
<a style="color:grey" href="https://superviral.io/admin/content.php">Content</a>
</div>
</div>';

}

if($user=='manjur'){

    $adminheader = '<div class="adminheader" style="width:85%">
    <a href="https://superviral.io/admin/check-user.php">Check Orders</a>
    <a href="https://superviral.io/admin/check-al.php">Check AL</a>
    <a href="https://superviral.io/admin/email-support.php">Support</a>
    <a href="https://superviral.io/admin/orders.php?type=missing">Missing Fulfills</a>
    <a href="https://superviral.io/admin/api-stats.php">Check SV Status</a>
    <a href="https://superviral.io/admin/tiktok-api-stats.php">Check TT Status</a>
    <a href="https://superviral.io/admin/feedback-and-questions.php">Feedback</a>
    <div style="float:right;"><a style="color:grey" href="https://superviral.io/admin/article.php">Articles</a>
    <a style="color:grey" href="https://superviral.io/admin/defectorders.php">Failed Orders</a>
    <a style="color:grey" href="https://superviral.io/admin/emailfunnels.php">Email Funnels</a>
    <a style="color:grey" href="https://superviral.io/admin/submitreviews.php">Reviews</a>
    <a style="color:grey" href="https://superviral.io/admin/content.php">Content</a>
    </div>
    </div>';
    
    }


if($user=='naeem'){

$adminheader = '<div class="adminheader">
<a href="https://superviral.io/admin/">Home</a>
<a href="https://superviral.io/admin/email-support.php">Superviral Support</a>
<a href="https://tikoid.com/admin/email-support.php">Tikoid Support</a>
<a href="https://superviral.io/admin/feedback.php">Submit Feedback</a>
</div>';

}

if($user=='abu'){

$adminheader = '<div class="adminheader">
<a href="https://superviral.io/admin/">Home</a>
<a href="https://superviral.io/admin/email-support.php">Superviral Support</a>
<a href="https://tikoid.com/admin/email-support.php">Tikoid Support</a>
<a href="https://superviral.io/admin/feedback.php">Submit Feedback</a>
</div>';

}

if($user=='abu'){

$adminheader = '<div class="adminheader">
<a href="https://superviral.io/admin/">Home</a>
<a href="https://superviral.io/admin/email-support.php">Superviral Support</a>
<a href="https://tikoid.com/admin/email-support.php">Tikoid Support</a>
<a href="https://superviral.io/admin/feedback.php">Submit Feedback</a>
</div>';

}


if($user=='abdullah'){

$adminheader = '<div class="adminheader">
<a href="https://superviral.io/admin/article.php">Articles</a>
</div>';

}


if($user=='admin'){

$adminheader = '<div class="adminheader">
<a target="_blank" href="https://superviral.io/admin/">Home</a>
<a target="_blank" href="https://superviral.io/admin/check-user.php">Check Orders</a>
<a target="_blank" href="https://superviral.io/admin/check-al.php">Check AL</a>
<a target="_blank" href="https://superviral.io/admin/check-accounts.php">Check Accounts</a>
<a target="_blank" href="https://superviral.io/admin/check-mail-list.php">Check Mail List</a>
<a target="_blank" href="https://superviral.io/admin/manage-blacklist.php">Manage Blacklist</a>
<a target="_blank" href="https://superviral.io/admin/complete-refund.php">Refunds</a>
<a target="_blank" href="https://superviral.io/admin/orders.php?type=reported">Reports</a>
<a target="_blank" href="https://superviral.io/admin/email-support.php">Superviral Support</a>
<a target="_blank" href="https://superviral.io/admin/slow-failed-orders.php">Slow Orders</a>
<a target="_blank" href="https://superviral.io/admin/defectorders.php">Failed Orders</a>
<a target="_blank" href="https://superviral.io/admin/feedback-and-questions.php">User Feedback</a>
</div>';

}


$header = '

<style>

body{letter-spacing:0.5px;}

.adminheader {display:inline-block;margin-left:45px;}
.adminheader a{    float: left;
    margin: 5px 28px 5px 10px;
    font-size: 14px;
    font-weight: bold;letter-spacing:initial;}


</style>

<div style="    padding: 10px;
    background: #fff;
    display: inline-block;
    width: 100%;
    box-sizing: border-box;
    height: 61px;"><svg style="margin-top: 5px;enable-background:new 0 0 567.3 114;version:1;float:left;" width="121" xmlns="http://www.w3.org/2000/svg" id="logo" x="0" y="0" viewBox="0 0 567.3 114" ><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="11.9" y1="17.7" x2="90.6" y2="96.3"><stop offset="0" stop-color="#DA4453"></stop><stop offset="0.55" stop-color="#89216B"></stop><stop offset="1" stop-color="#4A00E8"></stop></linearGradient><path d="M18.5 92.7c-2.5-0.2-5.1-0.2-7.5-0.7C2.5 90.3-2.2 81.6 1 73.5c1.3-3.2 3-6.2 4.9-9.1 3.2-5 3.3-9.8 0-14.8 -1.7-2.6-3.3-5.3-4.5-8.1 -4.3-9.8 1.9-19.6 12.5-20 2.9-0.1 5.9-0.1 8.8 0 5.4 0.1 9.3-2.1 11.9-6.9 1.5-2.8 3.1-5.6 4.9-8.2 3.6-5.3 8.6-7.2 14.9-6C57.9 1 60.6 3 62.6 5.8c1.7 2.4 3.3 4.9 4.5 7.5 2.9 6 7.6 8.5 14.2 8.2 3.4-0.2 6.9-0.2 10.3 0.5 8.4 1.6 13.1 10.3 9.9 18.4 -1.2 2.9-2.7 5.8-4.5 8.4 -3.7 5.5-3.8 10.8 0 16.4 1.6 2.3 3 4.9 4.1 7.5 4.2 9.6-1.9 19.4-12.4 19.9 -2.9 0.1-5.9 0.1-8.8 0 -5.5-0.1-9.5 2.1-12.1 7 -1.5 2.8-3.1 5.6-4.9 8.2 -3.5 5.2-8.5 7.1-14.7 6 -3.6-0.7-6.3-2.7-8.4-5.6 -1.6-2.3-3.1-4.6-4.3-7.1 -3-6.4-7.9-9.1-14.8-8.5 -0.7 0.1-1.5 0-2.3 0C18.5 92.6 18.5 92.7 18.5 92.7z" style="clip-rule:evenodd;fill-rule:evenodd;fill:url(#SVGID_1_)"></path><path d="M159.1 43.7c-2.7 0-4.7 1.8-4.7 4.3 0 2.6 0.3 2.8 8.6 5.4 10.7 3.3 14.1 7.2 14.1 14.7 0 9.6-8 17.1-18.2 17.1 -10.1 0-17.4-5.4-18.6-17.1h12.3c1.2 4.3 3.2 6.1 6.8 6.1 3.1 0 5.5-2.1 5.5-4.9 0-2.9-0.5-3.7-8.6-6.4 -10.1-3.3-14.1-7.5-14.1-14.9 0-8.6 7.4-15.3 16.9-15.3 8.6 0 16.3 5.6 16.7 15h-11.9C163.4 45 161.7 43.7 159.1 43.7L159.1 43.7zM218.5 84v-4.8c-4.6 4.4-8.4 6-14.6 6 -11.8 0-19.4-6.8-19.4-24.5V33.8h12.2v24.6c0 13.3 3.8 15.8 9.6 15.8 4.1 0 7.4-1.7 9.3-4.6 1.4-2.3 1.9-5.3 1.9-12V33.8h12.2V84H218.5L218.5 84zM267.9 85.2c-6.2 0-11-1.8-15.6-5.9v21.5H240v-67h11.2v5.9c3.9-4.5 9.8-7.1 16.7-7.1 14.7 0 25.4 11 25.4 26.1C293.3 73.9 282.6 85.2 267.9 85.2L267.9 85.2zM266.4 43.7c-8.5 0-14.9 6.5-14.9 15.1 0 8.8 6.4 15.3 15.1 15.3 8.1 0 14.4-6.6 14.4-15.1C281 50.3 274.7 43.7 266.4 43.7L266.4 43.7zM351.5 64.2h-39.4c1.5 6.1 6.8 9.9 14.1 9.9 5.1 0 8.1-1.4 11.2-5h13.3c-3.4 10.3-14 16-24.2 16 -15.5 0-27.6-11.4-27.6-26.1 0-14.8 11.8-26.5 26.8-26.5 15.2 0 26.3 11.4 26.3 27C352 61.4 351.9 62.5 351.5 64.2L351.5 64.2zM325.9 43.7c-7.3 0-12.2 3.5-14.1 10H340C338.4 47.2 333.4 43.7 325.9 43.7L325.9 43.7zM372.6 56.1V84h-12.2V33.8h11.2v4.9c3.2-4.6 5.8-6.1 11-6.1h0.9v11.6C376.2 44.4 372.6 48.3 372.6 56.1L372.6 56.1zM416.9 84h-9.7l-20.5-50.2h13.6L412.1 67l11.6-33.3h13.8L416.9 84 416.9 84zM443.6 26.5V14.5h12.2v12.1H443.6L443.6 26.5zM455.7 84h-12.2V33.8h12.2V84L455.7 84zM478.2 56.1V84H466V33.8h11.2v4.9c3.2-4.6 5.8-6.1 11-6.1h0.9v11.6C481.8 44.4 478.2 48.3 478.2 56.1L478.2 56.1zM534.3 84v-6.5c-4.9 5.4-9.7 7.7-16.9 7.7 -14.9 0-25.6-11-25.6-26.1 0-15.3 10.8-26.5 25.9-26.5 7.4 0 12.4 2.4 16.6 7.9v-6.7h11.2V84H534.3L534.3 84zM519 43.7c-8.7 0-14.9 6.5-14.9 15.7 0 8.8 6.2 14.8 15.1 14.8 9.3 0 14.8-6.5 14.8-14.9C533.9 50.1 527.7 43.7 519 43.7L519 43.7zM555.1 84V17h12.2v67H555.1L555.1 84z" fill="#231F20"></path></svg>

'.$adminheader.'

</div>';



$svgtiktokh1 = '<svg style="    width: 20px;    fill: #ff2791;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2859 3333" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd"><path d="M2081 0c55 473 319 755 778 785v532c-266 26-499-61-770-225v995c0 1264-1378 1659-1932 753-356-583-138-1606 1004-1647v561c-87 14-180 36-265 65-254 86-398 247-358 531 77 544 1075 705 992-358V1h551z"/></svg>';

?>