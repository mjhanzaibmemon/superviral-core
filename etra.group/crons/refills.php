<?php


$db = 1;
include('../sm-db.php');

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


    public function refill($order_id)
    { // get order status
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'refill',
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

// Examples

$api = new Api();

$api->setApiKey($fulfillment_api_key);
$api->setApiUrl($fulfillment_url);


//$timenow = time() - (1782000);
$timenow = time() - (2592000); //within 30 days
//$timenow = time() - (15811200);//within 163 days
$timeafterhours = time() - (90000); //once per day
$now = time();


$q = mysql_query("SELECT * FROM `orders` WHERE `refund` = '0' AND `disputed` = '0' AND `packagetype` = 'followers' AND `fulfilled` != '0' AND `added` > $timenow AND `lastrefilled` < $timeafterhours AND `norefill` = '0' ORDER BY `id` ASC LIMIT 3");

//Refresh with a number between 4 and 7 seconds
if (mysql_num_rows($q) == 0) {
    $message = 'All Refills Done For Today!';
    echo $message;
}

while ($info = mysql_fetch_array($q)) {

    $brand = $info['brand'];

    $info['fulfill_id'] = trim($info['fulfill_id']);
    $info['fulfill_id'] = $info['fulfill_id'] . ' ';


    $fulfills = explode(' ', $info['fulfill_id']);

    foreach ($fulfills as $fulfillid) {
        $id = $info['id'];


        if (empty($fulfillid)) continue;

        $fulfillid = trim($fulfillid);

        $refillthis = $api->refill($fulfillid);

        print_r($refillthis);


        echo '<b>' . $info['id'] . '</b> - <i>/order/' . $fulfillid . '/refill' . '</i><br>';

        mysql_query("UPDATE `orders` SET `lastrefilled` = '$now' WHERE `id` = '$id' AND brand = '$brand' LIMIT 1");
    }

    unset($fulfills);
}





////////////////////////////////////////////////////////////////



require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';


$s3 = new S3($amazons3key, $amazons3password);

$i = 1;

//Showing rows 0 - 24 (492416 total, Query took 0.0006 seconds.) [id: 499711... - 499687...]

$q = mysql_query("SELECT * FROM `ig_thumbs` WHERE `checked` = '0' AND `dnow` = '0' ORDER BY `id` ASC LIMIT 300");

if($_GET['manjur']=='true'){
    echo "SELECT * FROM `ig_thumbs` WHERE `checked` = '0' AND `dnow` = '0' ORDER BY `id` ASC LIMIT 300";die;
}

if (mysql_num_rows($q) == '0') die('All Done');

while ($info = mysql_fetch_array($q)) {

    $brand = $info['brand'];

    $actualimagename = md5('superviralrb' . $info['shortcode']);

    $check = S3::getObjectInfo('cdn.superviral.io', 'thumbs/' . $actualimagename . '.jpg');



    if (!empty($check['time'])) {

        $existsornot = '<font color="green">Exists</font>';
    } else {


        $existsornot = '<font color="red">Not exist - Delete!</font>';

        mysql_query("DELETE FROM `ig_thumbs` WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");

        echo $i . '. ' . $info['shortcode'] . ' - ' . $actualimagename . ': ' . $existsornot . '<hr>';
    }




    $i++;

    mysql_query("UPDATE `ig_thumbs` SET `checked` = '1' WHERE `id` = '{$info['id']}' AND brand = '$brand' LIMIT 1");

    unset($check);
}

?>
<style>
    body {
        font-family: arial;
    }
</style>