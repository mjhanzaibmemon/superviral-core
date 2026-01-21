<?php

require_once '../sm-db.php';
include 'emailer.php';


$thismonth = date("mY");

//SORTED BY HIGHEST CONVERTING HOUR

$ourdata = array(

    '00' => '25.96',
    '01' => '25.84',
    '02' => '25.14',
    '03' => '22.92',
    '04' => '24.17',
    '05' => '25.94',
    '06' => '26.74',
    '07' => '28.78',
    '08' => '30.63',
    '09' => '31.18',
    '10' => '29.60',
    '11' => '26.90',
    '12' => '24.96',
    '13' => '26.93',
    '14' => '27.48',
    '15' => '27.50',
    '16' => '30.95',
    '17' => '33.90',
    '18' => '35.64',
    '19' => '36.15',
    '20' => '35.53',
    '21' => '33.04',
    '22' => '30.42',
    '23' => '29.98'


);


$findusersq = mysql_query("SELECT * FROM `users` WHERE `source` = 'order' AND `lastupdatedconvtime` != '$thismonth' ORDER BY `orders` DESC LIMIT 100");

if (mysql_num_rows($finduserq) !== '0') {

    while ($userinfo = mysql_fetch_array($findusersq)) {

        $brand = $userinfo['brand'];

        $orderq = mysql_query("SELECT * FROM `orders` WHERE `emailaddress` = '{$userinfo['emailaddress']}' AND brand ='$brand'");

        $i = 0;


        $hours = array();
        $daysanhours = array();

        while ($info = mysql_fetch_array($orderq)) {

            $conversionhour = date('H', $info['added']);

            $hours[] = $conversionhour;
            //  $daysanhours[] = date('H D',$info['added']);

            $i++;

            //echo $info['id'].'  '.date('H:i',$info['added']).'<br>';

        }


        $hours = array_count_values($hours);
        arsort($hours);

        $chosen = key($hours);

        echo 'Chosen: ' . $chosen . '<br>';


        $findduplicaets = array_count_values($hours);

        echo 'What our system is showing:';

        echo '<pre>';
        print_r($hours);
        echo '</pre><br>';

        $duplicateamount = key($findduplicaets);


        $compareto = array_pop(array_reverse($findduplicaets));

        if ($compareto !== 1) {
            echo '<font color="red">Chosen: ' . $chosen . ' has a duplicate!, Choose between these duplicates</font><br>';




            foreach ($hours as $key => $hour) {
                if ($hour == $duplicateamount) $filtered[$key] = $hour;
            }

            foreach ($filtered as $key => $filteredhour) {

                $filtered2[$key] = $ourdata[$key];
            }

            arsort($filtered2);


            echo '<pre>';
            print_r($filtered2);
            echo '</pre>';

            $chosen = key($filtered2);

            echo '<br>';

            echo 'New Chosen: ' . $chosen . '<br>';
        }

        if (empty($chosen)) $chosen = '19';
        if (mysql_num_rows($orderq) == 0) $chosen = '19';

        echo 'FINAL chosen:' . $chosen;

        echo '<br>';

        $q = mysql_query("UPDATE `users` SET `highestconvtime` = '$chosen',`lastupdatedconvtime` = '$thismonth' WHERE `id` = '{$userinfo['id']}' AND brand ='$brand' LIMIT 1");

        if (!$q) die('Failed to update');

        echo '<hr>';

        unset($filtered);
        unset($filtered2);
        unset($duplicateamount);
        unset($hours);
    }
}

$now = time();

$scheduledforq = mysql_query("SELECT * FROM `post_notif_schedule` WHERE `email_sent` = '0' AND `scheduled_for` < '$now' LIMIT 10");


if (mysql_num_rows($scheduledforq) !== '0') {

    echo '<h2>Scheduled emails togo!</h2><br>';

    while ($scheduledforinfo = mysql_fetch_array($scheduledforq)) {

        $brand = $scheduledforinfo['brand'];

        mysql_query("UPDATE `post_notif_schedule` SET `email_sent` = '1' WHERE `id` = '{$scheduledforinfo['id']}' AND brand ='$brand' LIMIT 1");

        $to = $scheduledforinfo['to'];
        $subject = $scheduledforinfo['subject'];
        $htmlBody = stripslashes($scheduledforinfo['email_body']);

        emailnow($to, 'Superviral', "no-reply@superviral.io", '🦑 ' . $subject, $htmlBody);

        echo $to . '<br>';
        echo $subject . '<br>';
        echo $htmlBody . '<br>';

        echo '<hr>';

        unset($to);
        unset($subject);
        unset($htmlBody);
    }
}
