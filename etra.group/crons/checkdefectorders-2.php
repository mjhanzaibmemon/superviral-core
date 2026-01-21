<?php

include('../sm-db.php');
include('emailer.php');

include('orderfulfillraw.php');

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

$domain = "https://superviral.io";

// superviral code

$q = mysql_query("SELECT * FROM `orders` 
                                            WHERE `defect` != '0' 
                                            AND `sentdefectemail` = '0' 
                                            AND `packageid` != 20 
                                            AND `packageid` != 18 
                                            AND `restart` != 3 
                                            AND `fixedbyuser` = 0 
                                            AND `added` > '1667475464'
                                            AND brand = 'sv'
                                            LIMIT 6");


if (mysql_num_rows($q) == '0') die('no more orders to search for');


while ($info = mysql_fetch_array($q)) {


    echo 'Order ID: ' . $info['id'] . ' (' . ago($info['added']) . ' ago) ' . $info['amount'] . ' ' . $info['packagetype'] . '<br>';



    $tpl = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]) . '/superviral.io/emailtemplate/emailtemplate.html');



    $japid = 'jap1';

    if ($info['reorder'] == '1') $japid = 'jap2';
    if ($info['reorder'] == '2') $japid = 'jap3';


    $packagefetchq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND brand = 'sv' LIMIT 1");
    $packageinfo = mysql_fetch_array($packagefetchq);

    if($info['amount']!==$packageinfo['amount']){//ITS AN UPSELL

            $upsellamount = round($packageinfo['amount'] * 0.50);
            $packageinfo['amount'] = $packageinfo['amount'] + $upsellamount;
            
    }

    if($packageinfo['type']=='freelikes')continue;

    /////////////////////////////////////////////////////////////  Lamadava //////////////////////


    $url = 'https://api.lamadava.com/a1/user?username=' . $info['igusername'];


    //ATTEMPT TODO IT OUR WAY
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "x-access-key: $lamadavaaccess"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_ENCODING, '');

    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get2 = curl_exec($curl);

    $get = json_decode($get2);
    curl_close($curl);

    // print_r($arrays);  die;
    $isprivate = $get->graphql->user->is_private;
    $userId = $get->graphql->user->id;

    if (strpos($get2, '"logging_page_id"') === false) {

        echo 'Not found ' . $info['igusername'] . '<br>';
    } else {

        if ($isprivate == true) {
            echo 'Account exists 2 - and this is private<br>';



            echo 'Private - send message<br>';

            mysql_query("UPDATE `orders` SET `sentdefectemail` = '2' WHERE `id` = '{$info['id']}' AND brand = 'sv' LIMIT 1");

            $subject = 'Instagram Order #' . $info['id'] . ': We need your help to fix your order';

            $emailbody = '<p>Hi there,
                <br><br>

                <p>Unfortunately, we\'ve tried processing your order #' . $info['id'] . ', but the Instagram username <b>@' . $info['igusername'] . '</b> you\'ve provided is set to <b>private</b> and <b>our followers cannot engage with your account</b>.</p><br>


                <p>In order for you to receive your ' . $info['packagetype'] . ', your account will need to be on public.</b></p><br>

                <p><b>To set your account on public:</b> go to your profile tab and click the gear icon on the top of the page. Go to "Privacy and Security" and uncheck the "Private Account" box, so that your account becomes "Public".</p><br>

                <p>Please visit the below link to resume your order after setting your account on public:</p><br>
            
                <p><a href="' . $domain . '/track-my-order/' . $info["order_session"] . '/' . $info["id"] . '" target="_blank">Resume my order</a></p><br>

                <p>Kind regards,</p><br>

                <p>Superviral Team</p>';


            $tpl = str_replace('{body}', $emailbody, $tpl);
            $tpl = str_replace('{subject}', $subject, $tpl);
            $tpl = str_replace('<a href="https://superviral.io/unsubscribe.php', '<a style="display:none;" href="https://superviral.io/unsubscribe.php', $tpl);

            emailnow($info['emailaddress'], 'Superviral', 'support@superviral.io', $subject, $tpl);

            echo "sent email - 3<br>";
        } else {

            echo 'auto restart<br>';


            // we will restart + 1 first and then fulfill incase this doesn't work
            mysql_query("UPDATE `orders` SET defect = 0, `restart` = `restart` + 1 WHERE `id` = '{$info['id']}' AND brand = 'sv' LIMIT 1");




            // attempting restart upto 3 times

            if ($info['packagetype'] == 'followers') {


                $orderid = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://instagram.com/' . $info['igusername'], 'quantity' => $packageinfo['amount']));

                $orderid = $orderid->order;

                if ($orderid) {
                    $updatefulfillid = mysql_query("UPDATE `orders` SET `fulfill_id` = '$orderid' WHERE `id` = '{$info['id']}' AND brand = 'sv' ORDER BY `id` DESC LIMIT 1");
                }
            }


            if (($info['packagetype'] == 'likes') || ($info['packagetype'] == 'views')) {


                //DETECT FULFILLS OR JUST ONE
                $chooseposts = $info['chooseposts'];
                $fulfillids = trim($info['fulfill_id']);

                if (strpos($fulfillids, ' ') !== false) {

                    echo 'Multiple posts found for this package: ' . $chooseposts . '<br>';
                    echo 'Multiple fulfill ids found for this package: ' . $fulfillids . '<br>';

                    //SPACE MEANS THERES MORE THAN ONE POST SELECTED FOR THIS ORDER

                    $choosepostsarray = explode(' ', $chooseposts);
                    $choosepostsarray = array_filter($choosepostsarray);
                    $totalposts = count($choosepostsarray);


                    $fulfillidsarray = explode(' ', $fulfillids);
                    $fulfillidsarray = array_filter($fulfillidsarray);

                    $checkfulfillids = $api->multiStatus($fulfillidsarray);

                    $checkfulfillids = json_decode(json_encode($checkfulfillids), True);

                    $i = 0;
                    foreach ($checkfulfillids as $key => $order) {

                        //FULFILL ONLY THIS FULFILL ID

                        if (($order['status'] == 'Partial') || ($order['status'] == 'Canceled')) {

                            //echo $i.' - '.$choosepostsarray[$i].' - '.$key.'<br>';

                            $totaladdedamount = $packageinfo['amount'] * 1.3;

                            $multiamount = $totaladdedamount / $totalposts;
                            $multiamount = round($multiamount);

                            $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.instagram.com/p/' . $choosepostsarray[$i] . '/', 'quantity' => $multiamount));

                            $orderidd = $order1->order;

                            $fulfillids = str_replace($key, $orderidd, $fulfillids);
                        }

                        $i++;
                    }

                    echo 'New FulFill ID: ' . $fulfillids . '<br>';

                    $updateordersfulfillid = mysql_query("UPDATE `orders` SET `fulfill_id` = '$fulfillids' WHERE `id` = '{$info['id']}' AND brand= 'sv' LIMIT 1");
                } else {


                    $chooseposts = trim($chooseposts);
                    $info['amount'] = round($info['amount'] * 1.093);
                    echo 'Only post found for this package<br>';

                    $orderid = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://instagram.com/p/' . $chooseposts, 'quantity' => $info['amount']));

                    $orderid3 = $orderid->order;

                    echo 'New FulFill ID: ' . $orderid3 . '<br>';

                    if ($orderid3) {
                        $updatefulfillid = mysql_query("UPDATE `orders` SET `fulfill_id` = '$orderid3' WHERE `id` = '{$info['id']}' AND brand = 'sv' LIMIT 1");
                    }
                }
            }


            unset($orderid);
            unset($japid);
        }
    }

    echo '<hr>';
}
// end superviral code
