<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");
else ob_start();
header('Content-type: text/html; charset=utf-8');

$db = 0;
include('../header.php');



// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us", "", $_SERVER['REQUEST_URI']);
if ($queryLoc == 'us') {
    // echo $queryLoc;
    header('Location: ' . $siteDomain . $uri, TRUE, 301);
    die;
}

$id = addslashes($_GET['id']);
$hash = addslashes($_GET['id']);
$username = addslashes($_POST['username']);
$username = str_replace('@', '', $username);
$contactnumber = addslashes($_POST['input']);


//CHECK IF ID AND SESSION EXISTS IN DATABASE
if (!empty($id)) {

    $validq = mysql_query("SELECT * FROM `freetrial` WHERE `md5` = '$id' AND brand = 'sv' LIMIT 1");

    if (mysql_num_rows($validq) == '0') {
        die('Invalid Session');
    }

    mysql_query("UPDATE `freetrial` SET `views` = `views` + 1 WHERE `md5` = '$id' AND brand = 'sv' LIMIT 1");
} else {


    die('Try clicking on the link from the email again.');
}

$info = mysql_fetch_array($validq);


//IF SUBMITTED, SUBMIT AND FULFILL
if ((!empty($username)) && ($info['done'] == '0')) {

    //PREVENT DUPLICATE INSERTS
    // $updatefulfill = mysql_query("UPDATE `freetrial` SET `done` = '1' WHERE `md5` = '$id' and brand = 'sv' ORDER BY `id` DESC LIMIT 1");


    //POST VARIABLES
    // $id = addslashes($_POST['id']);
    // $hash = addslashes($_POST['hash']);

    //EMULATE ORDERFULFILL
    $info['packageid'] = '18';
    $info['igusername'] = $username;


    $loc2 = $loc;
    if (empty($loc2)) $loc2 = $info['country'];
    if (!empty($loc2)) $loc2 = $loc2 . '.';
    if ($loc2 == 'ww.') $loc2 = '';

    $cta = 'https://' . $loc2 . 'superviral.io/track-my-order/' . $id;

    // include('emailfulfill2.php');



    if (!empty($contactnumber)) {


        if (substr($contactnumber, 0, 2) == "07") $contactnumber = preg_replace('/^(0*44|(?!\+0*44)0*)/', '+44', $contactnumber);


        $contactnumberupdate = ", `contactnumber` = '$contactnumber' ";
    }

    if (!empty($contactnumber)) {
        $askednumber = " `askednumber` = '2', ";
    }



    $added = time();
    $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];

    $insertfulfill = mysql_query("INSERT INTO `orders` SET 
                `packagetype` = 'freefollowers',
                `packageid` = '18',
                `country` = '{$locas[$loc]['sdb']}',
                `order_session` = '$id',
                `amount` = '{$info['amount']}',
                `added` = '$added',
                `price` = '0.00',
                `emailaddress` = '{$info['emailaddress']}',
                $askednumber
                `contactnumber` = '$contactnumber',
                `ipaddress` = '$ipaddress',
                `socialmedia` = 'ig',`brand` = 'sv',
                `igusername` = '$username'");

    $uniqueorderinsertid = mysql_insert_id();

    $updatefulfill = mysql_query("UPDATE `freetrial` SET `done` = '1',`orderid`='$uniqueorderinsertid',`username` = '$username' $contactnumberupdate  WHERE `md5` = '$id' LIMIT 1");

    mysql_query("UPDATE `freetrial` SET `done` = '1' WHERE `emailaddress` = '{$info['emailaddress']}' AND brand = 'sv'");

    $updateuser = mysql_query("UPDATE `users` SET `freetrialclaimed` = '1' $contactnumberupdate WHERE `emailaddress` = '{$info['emailaddress']}' AND brand = 'sv'");

    $info['done'] = 1;
}


if (empty($error)) $error = '<div class="label labelcontact">Enter your Instagram username and that\'s it!</div>';

if ($info['done'] == 1) {

    header('Location: /buy-instagram-followers/?freefollowers=' . $id .'&em_split=b');
}


$tpl = file_get_contents('freefollowers_reelsform.html');



$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{igusername}', $info['username'], $tpl);
$tpl = str_replace('{followeramount}', $info['amount'], $tpl);


$contentq = mysql_query("SELECT * FROM `content` WHERE `country` = '{$locas[$loc]['sdb']}' AND `page` IN ('instagram-story-downloader','global')");
while ($cinfo = mysql_fetch_array($contentq)) {
    $tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);
}



echo $tpl;
