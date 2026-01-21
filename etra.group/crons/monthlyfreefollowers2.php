<?php

if ($_SERVER['HTTP_X_FORWARDED_FOR'] !== '77.102.160.65' && $_SERVER['HTTP_X_FORWARDED_FOR'] !== '212.159.178.222') {
    die('Unauthorized access');
}

require_once '../sm-db.php';

$seconds = '0';

$now = time();

////////////////


function getRandomString($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';

    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string;
}

/////////////////////

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
    return "$difference $periods[$j]";
}

/////////////////////

$o = 0;

$morethanatimeago = '604800';
$morethanatimeago = time() - $morethanatimeago;

$q2 = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'order' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC");

$q = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'order' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC LIMIT 25");

if (mysql_num_rows($q) == '0') {

    $q2 = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'cart' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC");

    $q = mysql_query("SELECT * FROM `users` WHERE `donemonthlysearchusername` = '1' AND `source` = 'cart' AND `unsubscribe` = '0' AND `monthlyfreefollowers` = '0' AND `added` < $morethanatimeago ORDER BY `id` DESC LIMIT 25");
    echo 'CART MODE<hr>';
}


if (mysql_num_rows($q) == 0) {
    die('no more users to search for');
} else {
    echo 'Total left:' . mysql_num_rows($q2) . '<hr>';
}

while ($info = mysql_fetch_array($q)) {


    $brand = $info['brand'];

    switch($brand){
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
    mysql_query("UPDATE `users` SET `monthlyfreefollowers` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");



    //VALIDATE EMAIL


    if (!filter_var($info['emailaddress'], FILTER_VALIDATE_EMAIL)) {

        mysql_query("UPDATE `users` SET `unsubscribe` = '9' WHERE `id` = '{$info['id']}' LIMIT 1");
    }

    if (!filter_var($info['emailaddress'], FILTER_VALIDATE_EMAIL)) continue;

    //


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
            `emailaddress`='{$info['emailaddress']}',
            $igusernameqinsert
            `type`='1'
            ");



    //if(!$insertq)die('Not inserted free trial');


    $fetchuserinfo = mysql_fetch_array(mysql_query("SELECT `emailaddress`,`md5`,`brand` FROM `users` WHERE `brand` = '$brand' AND `emailaddress` = '{$info['emailaddress']}' LIMIT 1"));
    $md5unsub = $fetchuserinfo['md5'];


    $email = urlencode($info['emailaddress']);
    $loc2 = urlencode($loc2);
    $subject = urlencode($subject);
    $freetrialmd5 = urlencode($freetrialmd5);
    $md5unsub = urlencode($md5unsub);
    $source = urlencode($info['source']);
    $country = urlencode($info['country']);

    $o++;

    echo "<div style='width:600px;float:left;padding:5px;border:1px solid #000'><b>$o</b><br>
        <iframe width=600 frameborder=0 height=242 
        src='/crons/monthlyffwindows2.php?&email={$email}&loc2={$loc2}&subject={$subject}&freetrialmd5={$freetrialmd5}&tikoidfreetrialmd5={$freetrialmd5}&md5unsub={$md5unsub}&source={$source}&brand={$brand}&country={$country}{$accountquerylogin}'></iframe></div>";


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



echo '<meta http-equiv="refresh" content="0">';

echo '<script>


/*setTimeout("document.location.reload(true)",' . $seconds . '000);*/

console.log(\'Refresh!\');


</script>';
