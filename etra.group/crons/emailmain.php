<?php

function ago($time)
{
    $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
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

function secondsToTime($seconds)
{
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}


include('../sm-db.php');
include('emailer.php');



use Google\Cloud\Translate\V2\TranslateClient;

require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/gtranslate/index.php';

////////////////////////////////////////////////////////////////
$timeafter = time() - 5000;
$now = time();

//CHECK FOR DUE EMAILS AND IN THE PAST = this symbol "<" checks for past
$q = mysql_query("SELECT * FROM `users` WHERE `unsubscribe` = '0' AND `emailaddress` != '' AND `locked` = '0' AND `lastchecked` < '$timeafter' ORDER BY `id` DESC LIMIT 30");
//$q = mysql_query("SELECT * FROM `users` WHERE `id` = '659895' ORDER BY `id` DESC LIMIT 30");

// if(mysql_num_rows($q)=='0'){die('All Emails Done');}

while ($info = mysql_fetch_array($q)) {

    $brand = $info['brand'];


    if($brand == 'sv'){
        $domain = 'superviral.io';
    }

    if($brand == 'to'){
        $domain = 'tikoid.com';
    }



    //IF THE MD5 IS NOT SET - THEN SET IT
    if (empty($info['md5'])) {
        $md5 = md5($info['emailaddress'] . $info['source'] . $info['added']);
        mysql_query("UPDATE `users` SET `md5` = '$md5' WHERE `id` = '{$info['id']}' AND brand ='$brand' LIMIT 1");
    }

    //TELL DATABASE YOU JUST CHECKED IT SO DONT CHECK IT FOR ANOTHER
    mysql_query("UPDATE `users` SET `lastchecked` = '$now' WHERE `id` = '{$info['id']}' AND brand ='$brand' LIMIT 1");

    //IF THE LAST SENT IS NOT SET, then set it and continue for the next run
    if ($info['lastsent'] == '0') {
        mysql_query("UPDATE `users` SET `lastsent` = `added` WHERE `id` = '{$info['id']}' AND brand ='$brand' LIMIT 1");
    }
    if ($info['lastsent'] == '0') continue;

    //DETERMINE THE ACTUAL TYPE AND THEIR EQUIVELENTS
    if ($info['source'] == 'freetrial') $theactualtype = 'cold';
    if ($info['source'] == 'cart') $theactualtype = 'warm';
    if ($info['source'] == 'order') $theactualtype = 'hot';

    $nextfunnelstate = $info['funnelstate'] + 1;

    //CHECK IF A NEW FUNNEL EXISTS#####################################################################
    $funnelq = mysql_query("SELECT * FROM `email_funnels` WHERE `type` = '$theactualtype' AND `{$theactualtype}sequence` = '{$nextfunnelstate}' AND `published` = '1' AND brand ='$brand' LIMIT 1"); //GET THE NEXT FUNNEL TO SEND THE EMAIL NOW

    $checkifnewfunnelexists = mysql_num_rows($funnelq);

    ///SET CONDITIONS ####################################################################################

    $funnelinfo = mysql_fetch_array($funnelq);

    echo $info['id'] . ' - ' . $info['emailaddress'] . ' - ' . $theactualtype . 'sequence: ' . $nextfunnelstate . ' - delivered: ' . $info['delivered'] . '<br>';

    if ($checkifnewfunnelexists == '0') {
        mysql_query("UPDATE `users` SET `locked` = '1' WHERE `id` = '{$info['id']}' AND brand ='$brand' LIMIT 1");
    }
    if ($checkifnewfunnelexists == '0') continue; //The next funnel does not exist so continue until a new funnel is created by me

    $sendernexttime = $info['lastsent'] + $funnelinfo['timeafterunix'];

    echo 'Last checked: ' . ago($info['lastchecked']) . '<br>';

    if ($sendernexttime > $now) {
        echo 'Last Sent: ' . date('l jS \of F Y H:i:s ', $info['lastsent']) . '<br>' . ago($info['lastsent']) . ' - <font color="red">Not time yet to send</font> will send after ' . secondsToTime($funnelinfo['timeafterunix']) . '.after ' . date('l jS \of F Y H:i:s ', $sendernexttime) . '<hr>';
    } else {
        echo 'Last Sent: ' . date('l jS \of F Y H:i:s ', $info['lastsent']) . '<br>' . ago($info['lastsent']) . ' - <font color="green">Time to send</font> will send now ' . date('l jS \of F Y H:i:s ', $sendernexttime) . '<hr>';
    }

    if ($sendernexttime > $now) continue;


    //CHECKED IF USER HAS ATLEAST ONE DELIVERY - IF NOT THEN CONTINUE
    if (($info['delivered'] == '0') && ($info['funnelstate'] == '0') && ($theactualtype == 'hot')) continue;

    $tpl = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]).'/'.$domain.'/emailtemplate/emailtemplate.html');

    $subject = $funnelinfo['subject'];
    $senderName = $funnelinfo['name'];

    $tpl = str_replace('{md5unsub}', $info['md5'], $tpl);
    $tpl = str_replace('{body}', $funnelinfo['body'], $tpl);

    $formattedDate = date('d/m/Y h:i A', $info['added']);
    $tpl= str_replace('{date_added}', $formattedDate, $tpl);

    ///EMAIL FUNNEL INFORMATION LOADED AND CHECKS DONE#####################################################################
    $lastsent = time();
    $ffunnelstate = $funnelinfo[$theactualtype . 'sequence'];

    mysql_query("UPDATE `users` SET `lastsent` = '$lastsent',`funnelstate` = '{$ffunnelstate}' WHERE `id` = '{$info['id']}' AND brand ='$brand' LIMIT 1");
    mysql_query("UPDATE `email_funnels` SET `sentamount` = `sentamount` + 1 WHERE `id` = '{$funnelinfo['id']}' AND brand ='$brand' LIMIT 1");


    include('main' . $funnelinfo['id'] . '.php');

    echo $info['emailaddress'] . ' - ' . $info['source'] . '<br>';

    $tpl = str_replace('{subject}', $subject, $tpl);

    emailnow($info['emailaddress'], $funnelinfo['name'], 'no-reply@superviral.io', $subject, $tpl);


    if (($info['country'] !== 'ww') && ($info['country'] !== 'us') && ($info['country'] !== 'uk')) $notenglish2 = true;

    if ($notenglish2 == true) {

        $thisloc = $info['country'];

        $translate = new TranslateClient(['key' => $googletranslatekey]);

        $result = $translate->translate($tpl, [
            'source' => 'en',
            'target' => $locas[$thisloc]['sdb'],
            'format' => 'html'
        ]);

        $tpl = $result['text'];



        $translate2 = new TranslateClient(['key' => $googletranslatekey]);

        $result = $translate2->translate($subject, [
            'source' => 'en',
            'target' => $locas[$thisloc]['sdb'],
            'format' => 'html'
        ]);

        $subject = $result['text'];
    }


    echo $tpl . '';

    unset($tpl);
    unset($notenglish2);
}
