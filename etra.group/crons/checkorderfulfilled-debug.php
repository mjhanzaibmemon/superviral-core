<?php


include('../sm-db.php');
require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';

include dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/messagebird/autoload.php';

/* SUPERVIRAL EMAIL BODY */
$svemailbody = '<p>Hi there,
<br><br>
We\'ve just received confirmation that your order #{ordernum} for @{username} is completed at Superviral.</p><br>
{refill}
<p>Please view your tracking history for this order:</p><br>
{ctabtn}
<br><br>
<a href="https://superviral.io/{loc2}buy-instagram-{packagetypelink}/" style="color: #2e00f4;
    border: 2px solid #2e00f4;display: block;
    width: 330px;padding: 16px 9px;
    text-decoration: none;-webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    margin: 5px auto;
    font-weight: 700;
    text-align:center;">Buy More Instagram {packagetype} &raquo;</a>
<br>

<a href="https://superviral.io/{loc2}buy-tiktok-{packagetypelink}/" style="color: #2e00f4;
    border: 2px solid #2e00f4;display: block;
    width: 330px;padding: 16px 9px;
    text-decoration: none;-webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    margin: 5px auto;
    font-weight: 700;
    text-align:center;">Buy TikTok {packagetype} &raquo;</a>
<br>

<p>Since 2012, the customer ALWAYS comes first at Superviral.</p><br>

<p>Best wishes,</p><br>

<p>Superviral Team</p>';

/* TIKOID EMAIL BODY */
$toemailbody = '<p>Hi there,
<br><br>
We\'ve just received confirmation that your order #{ordernum} for @{username} is completed at Tikoid.</p><br>
{refill}
<p>Please view your tracking history for this order:</p><br>
{ctabtn}
<br><br>
<a href="https://superviral.io/{loc2}buy-instagram-{packagetypelink}/" style="color: #2e00f4;
    border: 2px solid #2e00f4;display: block;
    width: 330px;padding: 16px 9px;
    text-decoration: none;-webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    margin: 5px auto;
    font-weight: 700;
    text-align:center;">Buy More Instagram {packagetype} &raquo;</a>
<br>

<a href="https://tikoid.com/buy-tiktok-{packagetypelink}/" style="color: #2e00f4;
    border: 2px solid #2e00f4;display: block;
    width: 330px;padding: 16px 9px;
    text-decoration: none;-webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    margin: 5px auto;
    font-weight: 700;
    text-align:center;">Buy TikTok {packagetype} &raquo;</a>
<br>

<p>Since 2012, the customer ALWAYS comes first at Tikoid.</p><br>

<p>Best wishes,</p><br>

<p>Tikoid Team</p>';

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

class Api
{

    public function setApiKey($value)
    {
        $this->api_key = $value;
    }
    public function setApiUrl($value)
    {
        $this->api_url = $value;
    }

    public function order($data)
    { // add order
        $post = array_merge(array('key' => $this->api_key, 'action' => 'add'), $data);
        return json_decode($this->connect($post));
    }

    public function status($order_id)
    { // get order status
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'status',
            'order' => $order_id
        )));
    }

    public function multiStatus($order_ids)
    { // get order status
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'status',
            'orders' => implode(",", (array)$order_ids)
        )));
    }

    public function services()
    { // get services
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'services',
        )));
    }

    public function balance()
    { // get balance
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'balance',
        )));
    }


    private function connect($post)
    {
        $_post = array();
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                $_post[] = $name . '=' . urlencode($value);
            }
        }

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
        if (is_array($post)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
            $result = false;
        }
        curl_close($ch);
        return $result;
    }
}
/////////////////////

$api = new Api();

$api->setApiKey($fulfillment_api_key);
$api->setApiUrl($fulfillment_url);

$time = time();
$timeafterhours = time() - (6000);

$q = mysql_query("SELECT * FROM `orders` WHERE `fulfill_id` != '' AND `fulfilled` = '0' AND `defect` = '0' AND `refund` = '0' AND `lastchecked` < $timeafterhours ORDER BY `id` DESC LIMIT 11");
$q = mysql_query("SELECT * FROM `orders` WHERE `fulfill_id` != '' AND `fulfilled` = '0' AND `defect` = '0' AND `refund` = '0' AND country='us' AND contactnumber != '' AND `lastchecked` < $timeafterhours ORDER BY `id` DESC LIMIT 11");

if(mysql_num_rows($q)=='0'){die('no more orders to search for');}

//echo '<meta http-equiv="refresh" content="5">';  

while ($info = mysql_fetch_array($q)) {

    if($info['emailaddress']=='mac@etra.group'){continue;}

    if(empty($info['brand']))$info['brand']=='sv';  

    $loc2 = $info['country'];
    if (!empty($loc2)) $loc2 = $loc2 . '/';
    if ($loc2 == 'ww/') $loc2 = '';
    if ($loc2 == 'us/') $loc2 = '';

    //
    $brand = $info['brand'];

    $socialmedia = $info['socialmedia'];

    switch($socialmedia){
        case 'ig':
            $keyword = "instagram";
            $socialmedia = 'Instagram';
        break;
        case 'tt':
            $keyword = "tiktok";
            $socialmedia = 'TikTok';
        break;
    }

    switch($brand){
        case 'sv':
            $domain = 'superviral.io';
            $website = 'Superviral';
        break;
        case 'to':
            $domain = 'tikoid.com';
            $website = 'Tikoid';
        break;
        case 'fb':
            $domain = 'feedbuzz.io';
            $website = 'Feedbuzz';
        break;
        case 'tp':
            $domain = 'tokpop.com';
            $website = 'Tokpop';
        break;
        case 'sz':
            $domain = 'swizzy.io';
            $website = 'Swizzy';
        break;
    }

    mysql_query("UPDATE `orders` SET `lastchecked` = '$time' WHERE `id` = '{$info['id']}' LIMIT 1");

    echo 'Order: ' . $info['id'] . ' - Added: ' . date('l jS \of F Y H:i:s ', $info['added']) . '<br>';

    $fulfills = explode(' ', $info['fulfill_id']);
    $fulfills = array_filter($fulfills);

    $fulfillcount = count($fulfills);
    $balance = $api->multiStatus($fulfills);

    $balance = json_decode(json_encode($balance), True);

    $partial = 0;
    $cancelled = 0;
    $pending = 0;
    $completed = 0;

    foreach ($balance as $key => $order) {
        echo 'KEY: ' . $key . ' - Status:' . $order['status'] . '<br>';
        if ($order['status'] == 'Pending') $pending++;
        if ($order['status'] == 'Partial') $partial++;
        if ($order['status'] == 'Canceled') $cancelled++;
        if ($order['status'] == 'Completed') $completed++;
    }

    echo 'Pending: ' . $pending . '<br>';
    echo 'Partial: ' . $partial . '<br>';
    echo 'Cancelled: ' . $cancelled . '<br>';
    echo 'Completed: ' . $completed . '<br>';
    echo 'Count: ' . $fulfillcount . '<br>';

    if ($cancelled !== 0) mysql_query("UPDATE `orders` SET `defect` = '2' WHERE `id` = '{$info['id']}' LIMIT 1");
    if ($partial !== 0) mysql_query("UPDATE `orders` SET `defect` = '3' WHERE `id` = '{$info['id']}' LIMIT 1");

    $time = time();

    //MAKE SURE WE GET THE DELIVERY TIME SET IN AS WELL
    $deliverytime = $time - $info['added'];


    if (($deliverytime <= 4600)) {


        if ($info['packageid'] !== '18') {

            echo 'Too early to mark as done - non free followers<br>';
            $tooearly = 1;
        }

        if (($info['packageid'] == '18') && ($deliverytime <= 600)) {

            echo 'Too early to mark as done - free followers<br>';
            $tooearly = 1;
        }
    }

    $morethansixmonths = $time - 15552000;


    if($info['added'] >  $morethansixmonths && $info['added'] < ($time + 4000)){

        echo 'Within 6-months<br>';

    }else{


    echo 'NOT Within 6-months<br>';//mark this as done, customer lost interest at this point
    $completed = 1;
    $fulfillcount = 1;
    }



    ////////////////SEND EMAIL AND UPDATE MYSQL
    if (($completed == $fulfillcount) && ($tooearly !== 1)) {

        echo 'Mark as done<br>';


        $seconds = '0.' . rand(1, 9);
        $order_response_finish = '~~~' . $time . '###'. $website .' completed delivery of ' . $info['packagetype'] . ' to @' . $info['igusername'] . '###' . $seconds;
        $order_response_finish = addslashes($order_response_finish);



        mysql_query("UPDATE `orders` SET `fulfilled` = '$time',`deliverytime` = '$deliverytime', `order_response_finish` = '$order_response_finish' WHERE `id` = '{$info['id']}' LIMIT 1");

        mysql_query("UPDATE `users` SET `delivered` = '1', `lastsent` = '$time' WHERE `emailaddress` = '{$info['emailaddress']}' AND `delivered` = '0' AND brand = '$brand' LIMIT 1");

        ////// MYSQL UPDATE DONE

        // check if order is delivered and account is private

        $response = checkIfAccountisPrivate($info['igusername']);
        if($response){
            echo "Account status: Private";
            $supplier_error = "Account is private: order may be incomplete";
            $res= mysql_query("UPDATE `orders` SET `orderfailed` = 1, `supplier_errors`='{$supplier_error}' WHERE `id` = '{$info['id']}' LIMIT 1");
        }else{
            echo "Account status: Public";
        }

        if (!empty($info['contactnumber'])) {

            ////generate BITLY CODE

            echo 'Begin sending a text...<br>';  


            $bitlyhash = getRandomString();
            $bitlyhref = 'https://'. $domain .'/' . $loc2 . 'order/choose/?setorder=' . $info['order_session'] . '&discounton=no';
            $bitlyq = mysql_query("INSERT INTO `bitly` SET `hash` = '$bitlyhash', `href` = '$bitlyhref',`added` = '$time', `brand` = '$brand'");

            ////


            $orginator = ucwords($website);

            $MessageBird = new \MessageBird\Client($messagebirdclient);
            $Message = new \MessageBird\Objects\Message();
            //$Message->originator = +447451272012;
            $Message->originator = 'SUPERVIRAL';
            if($info['country'] == 'us'){
                $Message->originator = '+12087798450';
            }
            $Message->recipients = array($info['contactnumber']);

            if($info['socialmedia'] == "tt"){
                $keyword = "tiktok";

            }else if($info['socialmedia'] == "ig"){
                $keyword = "instagram";

            }

            if (str_contains($info['packagetype'], 'free')) {
                if ($info['packagetype'] == 'freefollowers') {

                $Message->body = '@' . trim($info['igusername']) . ': You\'ve gained +' . $info['amount'] . ' Followers. Have a great weekend! '. $website .' Team. Get more: https://'. $domain .'/' . $loc2 . 'buy-'. $keyword .'-followers/';
                }
                if ($info['packagetype'] == 'freelikes') {

                $Message->body = '@' . trim($info['igusername']) . ': You\'ve gained +' . $info['amount'] . ' Likes. Have a great weekend! '. $website .' Team. Get more: https://'. $domain .'/' . $loc2 . 'buy-'. $keyword .'-likes/';
                }



            } else {

                $Message->body = '@' . trim($info['igusername']) . ': You\'ve gained +' . $info['amount'] . ' ' . str_replace('freefollowers', 'Followers', $info['packagetype']) . '. Have a wonderful day! '. $website .' Team. Order again: https://'. $domain .'/a/' . $bitlyhash;
            }


            try{$MessageBird->messages->create($Message);}catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "<br>";
            }

            if ($MessageBird) {
                echo 'Text Message Sent to ' . $info['contactnumber'] . '!<br>';
            }
        }

        $href = 'https://'. $domain .'/' . $loc2 . 'track-my-order/' . $info['order_session'] . '/' . $info['id'];

        if (($info['packagetype'] == 'followers') || ($info['packagetype'] == 'freefollowers')) {

            $refillmsg = '<p>Now that we\'ve delivered your '.$socialmedia.' followers, we\'ll monitor your Instagram account for 30-days after placing your order. This is to ensure that the followers you\'ve received - remains on your account.
                </p><br>
                <p>
                If the followers you\'ve ordered drops, don\'t worry - we\'ll refill your account to the amount you\'ve ordered. Our systems monitor and check your account every 12-24 hours. At '. $website .' - the customers always comes first. ❤️</p><br>';
        }

        $ctabtn = '<a href="' . $href . '" style="color: #2e00f4;
            border: 2px solid #2e00f4;
            display: block;
            width: 330px;
            padding: 16px 9px;
            text-decoration: none;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            margin: 5px auto;
            font-weight: 700;
            text-align:center;">View Tracking History &raquo;</a>';

        if (($info['packagetype'] == 'likes') && ($info['account_id'] !== '0') && ($info['brand']=='sv')) {



            $searchaccountq = mysql_query("SELECT * FROM `accounts` WHERE `id` =  '{$info['account_id']}' LIMIT 1");
            $searchaccountinfo = mysql_fetch_array($searchaccountq);

            if ($searchaccountinfo['freeautolikes'] == '0') {

                $ctabtn .= '<a href="https://'. $domain .'/' . $loc2 . 'account/orders/" style="color: #2e00f4;
                        border: 2px solid #2e00f4;
                        display: block;
                        width: 330px;
                        padding: 16px 9px;
                        text-decoration: none;
                        -webkit-border-radius: 5px;
                        -moz-border-radius: 5px;
                        border-radius: 5px;
                        margin: 5px auto;
                        font-weight: 700;
                        text-align:center;">Get Free Automatic Likes! &raquo;</a>';
            }
        }

        echo 'Begin sending an email...<br>';

       $subject = 'Delivered: Your Superviral order #' . $info['id'];

        $fetchuserinfo = mysql_fetch_array(mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$info['emailaddress']}' AND brand = '$brand' LIMIT 1"));
        $md5unsub = $fetchuserinfo['md5'];


        $tpl = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]).'/'.$domain.'/emailtemplate/ordercomplete.html');
        if($brand == 'sv') $tpl = str_replace('{body}', $svemailbody, $tpl);
        if($brand == 'to') $tpl = str_replace('{body}', $toemailbody, $tpl);
        if($brand == 'fb') $tpl = str_replace('{body}', $fbemailbody, $tpl);
        $tpl = str_replace('{loc2}', $loc2, $tpl);
        $tpl = str_replace('{subject}', $subject, $tpl);
        $tpl = str_replace('{ordernum}', $info['id'], $tpl);
        $tpl = str_replace('{ctabtn}', $ctabtn, $tpl);
        $tpl = str_replace('{username}', $info['igusername'], $tpl);
        $tpl = str_replace('{refill}', $refillmsg, $tpl);
        $tpl = str_replace('{md5unsub}', $md5unsub, $tpl);

        $thispackagetype = str_replace('free', '', $info['packagetype']);

        $tpl = str_replace('{packagetypelink}', strtolower($thispackagetype), $tpl);
        $tpl = str_replace('{packagetype}', ucfirst($thispackagetype), $tpl);
        $tpl = str_replace('{website}', $website, $tpl);

        if($info['socialmedia'] == 'tt'){
            $tpl = str_ireplace("Instagram", "Tiktok", $tpl);
        }

        emailnow($info['emailaddress'], $website, 'orders@'. $domain, '🤩🌟 ' . $subject, $tpl);

        //$bodyText = str_replace("'","\'", $tpl);
        email_stat_insert('Order Delivered', $info['emailaddress'], $bodyText, $info['brand']);
        
        echo 'Email sent!';
    }


    unset($ctabtn);
    unset($pending);
    unset($partial);
    unset($cancelled);
    unset($multistatus);
    unset($completed);
    unset($fulfillcount);
    unset($fulfills);
    unset($refillmsg);
    unset($searchaccountq);
    unset($searchaccountinfo);
    unset($thispackagetype);
    unset($tooearly);


    echo '<hr>';
}

if($_GET['speedrun']=='true')echo '<meta http-equiv="refresh" content="0">';


function checkIfAccountisPrivate($username){

    global $superviralsocialscrapekey;

	$url = 'https://i.supernova-493.workers.dev/api/v3/userId?username=' . $username;

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_TIMEOUT, 20);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	$get = curl_exec($curl);
	$resp = $get;

	$resp = json_decode($resp, true);
	$users = $resp['data'];
	$userId = $users['user']['pk_id'];
    $is_private = $users['user']['is_private'];
    
    if(!empty($resp)){
        return $is_private;
    }else{
        return true;
    }

	curl_close($curl);
}