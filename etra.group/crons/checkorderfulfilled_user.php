<?php


include('../sm-db.php');
//include('emailer.php');

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
$timeafterhours = time() - (6800);

echo "SELECT * FROM `orders` WHERE `fulfill_id` != '' AND `fulfilled` = '0' AND `defect` = '0' AND `refund` = '0' AND `lastchecked` < $timeafterhours ORDER BY `id` DESC<hr>";
$q = mysql_query("SELECT * FROM `orders` WHERE id=1463232");

// if(mysql_num_rows($q)=='0')die('no more orders to search for');

while ($info = mysql_fetch_array($q)) {

    //

    $loc2 = $info['country'];
    if (!empty($loc2)) $loc2 = $loc2 . '/';
    if ($loc2 == 'ww/') $loc2 = '';
    if ($loc2 == 'us/') $loc2 = '';

    //
    $brand = $info['brand'];

    switch($brand){
        case 'sv':
            $domain = 'superviral.io';
            $website = 'Superviral';
            $socialmedia = 'Instagram';
        break;
        case 'to':
            $domain = 'tikoid.com';
            $website = 'Tikoid';
            $socialmedia = 'TikTok';
        break;
        case 'fb':
            $domain = 'feedbuzz.io';
            $website = 'Feedbuzz';
            $socialmedia = 'Instagram';
        break;
        case 'tp':
            $domain = 'tokpop.com';
            $website = 'Tokpop';
            $socialmedia = 'TikTok';
        break;
        case 'sz':
            $domain = 'swizzy.io';
            $website = 'Swizzy';
            $socialmedia = 'Instagram';
        break;
    }

    //mysql_query("UPDATE `orders` SET `lastchecked` = '$time' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");

    echo "UPDATE `orders` SET `lastchecked` = '$time' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1<hr>";

    echo 'Order: ' . $info['id'] . ' - Added: ' . date('l jS \of F Y H:i:s ', $info['added']) . '<br>';

    $fulfills = explode(' ', $info['fulfill_id']);
    $fulfills = array_filter($fulfills);

    $fulfillcount = count($fulfills);
    $balance = $api->multiStatus($fulfills);

    $balance = json_decode(json_encode($balance), True);

    print_r($balance);

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

    //if ($cancelled !== 0) mysql_query("UPDATE `orders` SET `defect` = '2' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");
    //if ($partial !== 0) mysql_query("UPDATE `orders` SET `defect` = '3' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");

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


    echo "<hr><br><br>{$completed}<br>{$fulfillcount}<br>{$tooearly}";

    ////////////////SEND EMAIL AND UPDATE MYSQL
    if (($completed == $fulfillcount) && ($tooearly !== 1)) {
        echo 'Mark as done<br>';


        $seconds = '0.' . rand(1, 9);
        $order_response_finish = '~~~' . $time . '###'. $website .' completed delivery of ' . $info['packagetype'] . ' to @' . $info['igusername'] . '###' . $seconds;
        $order_response_finish = addslashes($order_response_finish);


echo "<hr>UPDATE `orders` SET `fulfilled` = '$time',`deliverytime` = '$deliverytime', `order_response_finish` = '$order_response_finish' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1";
        //mysql_query("UPDATE `orders` SET `fulfilled` = '$time',`deliverytime` = '$deliverytime', `order_response_finish` = '$order_response_finish' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");

        //mysql_query("UPDATE `users` SET `delivered` = '1', `lastsent` = '$time' WHERE `emailaddress` = '{$info['emailaddress']}' AND `delivered` = '0' AND brand = '$brand' LIMIT 1");

        ////// MYSQL UPDATE DONE
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


    //echo '<meta http-equiv="refresh" content="1">';
