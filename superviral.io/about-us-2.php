<?php

use FontLib\Table\Type\head;

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");
else ob_start();

$db = 1;

include('header.php');

// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us", "", $_SERVER['REQUEST_URI']);
if ($queryLoc == 'us') {
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time() + 3600, '*/', NULL, 0); // 1 hour
    header('Location: ' . $siteDomain . $uri, TRUE, 301);
    die;
}



$tpl = file_get_contents('about-us-2.html');
if ($_GET['test']) $tpl = file_get_contents('about-us-temp.html');

$country = $locas[$loc]['sdb'];
$q = mysql_query("SELECT * FROM `articles` WHERE `published` = '1' AND superadmin_approve = 1 AND country = '$country' ORDER BY `author_image` DESC, `id` DESC LIMIT 3");

$blogs_arr =''; 
while ($info = mysql_fetch_array($q)) {

    if($info['written'] > time()){continue;}

    $author_image = $info['author'] != 'The Superviral Team' ? $info['author_image'] : '/imgs/author-superviral.png';

    $article_link = $locas[$loc]['sdb'] == 'uk' ? 'https://superviral.io/uk/blog/'. $info['url'] : 'https://superviral.io/blog/'. $info['url'];

    $blogs_arr .= '`<a class="card" href="'.$article_link.'">
                <div class="img-container">
                    <img src="'. $info['thumb_image'] .'" alt="community">
                </div>
                <div class="label">'. $info['title'] .'</div>
                <div class="para">'. $info['shortdesc'] .'</div>
                <div class="tipper">
                    <div class="user-img">
                        <img src="'. $author_image .'" alt="user">
                    </div>
                    <div class="bio">
                        <div class="name">'. $info['author'] .'</div>
                        <div class="status">
                            <span class="txt-secondary">Social media expert</span>
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 9 9"
                                    fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M4.5 9C6.98528 9 9 6.98528 9 4.5C9 2.01472 6.98528 0 4.5 0C2.01472 0 0 2.01472 0 4.5C0 6.98528 2.01472 9 4.5 9ZM4.41697 6.48463L6.94822 3.48463L5.80178 2.51732L3.78116 4.91213L2.76402 3.95483L1.73598 5.04713L3.32973 6.54713L3.90634 7.08982L4.41697 6.48463Z"
                                        fill="#00E0FF" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </a>`,';

    $author_image = '';
}



if (!empty($_POST['submit'])) {

    $failed = 0;

    //SECRET KEY

    $secret = $recaptchasecret;

    $data = array('secret' => $secret, 'response' => $_POST['g-recaptcha-response']);

    $verify = curl_init();
    curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($verify, CURLOPT_POST, true);
    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($verify);

    $response = json_decode($response, true);

    if ($response["success"] === true) {
        // actions if successful


    } else {
        $emailsuccess = '<div class="emailsuccess emailfailed">Recaptcha Error</div>';
        $failed = 1;
    }



    ////////////////////// EMAILER

    if ($failed == 0) {

        $first_name = addslashes($_POST['name']); // required
        $orderid    = addslashes($_POST['orderid']); // required
        $replyto    = trim(addslashes($_POST['emailaddress'])); // required
        $subject    = addslashes($_POST['subject']); // required
        $email  = strip_tags(addslashes($_POST['message']));
        // $email = str_replace("'","\'",$emailhtml);
        // $email = str_replace('"','\"',$email); 
        $dateAdded = time();

        $fetchMaxUidContactForm = mysql_query("SELECT max(`emailUid`) as maxId FROM `email_queue` WHERE `brand`='sv' AND `source` = 'aboutus-contactform'");
        $fetchMaxUid = mysql_fetch_array($fetchMaxUidContactForm);
        $maxUid = $fetchMaxUid["maxId"];

        if ($maxUid == "" || $maxUid == null) {
            $emailUid = 1;
        } else {
            $emailUid = intval($maxUid) + 1;
        }

        ////////////////////////EMAIL SPAM


        $emailSpam = '0';


        $insertEmailQueueQuery = mysql_query("INSERT INTO  `email_queue` 
                                                                    SET `brand` = 'sv',
                                                                        `subject` = '$subject',
                                                                        `email`   = '$email',
                                                                        `seenFlag`= 'seen',
                                                                        `source`  = 'aboutus-contactform',
                                                                        `from`    = '$replyto',
                                                                        `to`      = 'support@superviral.io',
                                                                        `emailDate` = '$dateAdded',
                                                                        `dateAdded` = '$dateAdded',
                                                                        `emailUid`  = '$emailUid',
                                                                        `emailSpam` = '$emailSpam'
    
                                            ");

echo "<!-- INSERT INTO  `email_queue` 
                                                                    SET `brand` = 'sv',
                                                                        `subject` = '$subject',
                                                                        `email`   = '$email',
                                                                        `seenFlag`= 'seen',
                                                                        `source`  = 'aboutus-contactform',
                                                                        `from`    = '$replyto',
                                                                        `to`      = 'support@superviral.io',
                                                                        `emailDate` = '$dateAdded',
                                                                        `dateAdded` = '$dateAdded',
                                                                        `emailUid`  = '$emailUid',
                                                                        `emailSpam` = '$emailSpam'
    
                                            -->";

        $didemailsend = 'Email Sent!';
        $emailsuccess = '<div class="emailsuccess">Submitted Successfully</div>';
    }

    ///////////////////////



}



$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{hsince}', "since 2012", $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{loclocation}', $loclinkforward, $tpl);
$tpl = str_replace('{loc}', $locas[$loc]['sdb'], $tpl);
$tpl = str_replace('{blogs_arr}', $blogs_arr, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{emailsuccess}', $emailsuccess, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'about-us') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");

while ($cinfo = mysql_fetch_array($contentq)) {
    $tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);
    if ($cinfo['name'] == 'canonical') $htmlcanonical = $cinfo['content'];
}

echo $tpl;
