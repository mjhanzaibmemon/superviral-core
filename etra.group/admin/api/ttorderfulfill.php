<?php

if(!$info)exit('No details found');

$pacid = $info['packageid'];
$username = $info['igusername'];
$packagetype = $info['packagetype'];

$packageinfoq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND brand = '$brand' LIMIT 1");
$packageinfo = mysql_fetch_array($packageinfoq);

$japid = 'jap1';

if($info['reorder']=='1')$japid = 'jap2';
if($info['reorder']=='2')$japid = 'jap3';

if(!empty($info['upsell'])){

$packageinfo['amount'] = $packageinfo['amount'] + $upsellamount;
    

}

if (!class_exists('Api')) {
class Api
{
    public function setApiKey( $value ){$this->api_key = $value;}
    public function setApiUrl( $value ){$this->api_url = $value;}

    public function order($data) { // add order
        $post = array_merge(array('key' => $this->api_key, 'action' => 'add'), $data);
        return json_decode($this->connect($post));
    }

    public function status($order_id) { // get order status
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'status',
            'order' => $order_id
        )));
    }

    public function multiStatus($order_ids) { // get order status
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'status',
            'orders' => implode(",", (array)$order_ids)
        )));
    }

    public function services() { // get services
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'services',
        )));
    }

    public function balance() { // get balance
        return json_decode($this->connect(array(
            'key' => $this->api_key,
            'action' => 'balance',
        )));
    }


    private function connect($post) {
        $_post = Array();
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                $_post[] = $name.'='.urlencode($value);
            }
        }

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
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
}
// Examples

$api = new Api();

$api->setApiKey($fulfillment_api_key);
$api->setApiUrl($fulfillment_url);

// for no packageid

if(empty($packageinfo['jap1'])){

    if($packagetype == 'followers'){
        
        $packageinfo['jap1'] = $ttfreefollowersorderid;
        $packageinfo['jap2'] = $ttfreefollowersorderid;
        $packageinfo['jap3'] = $ttfreefollowersorderid;
        $packageinfo['type'] = 'followers';
    }

    if($packagetype == 'likes'){
        
        $packageinfo['jap1'] = $ttfreelikesorderid;
        $packageinfo['jap2'] = $ttfreelikesorderid;
        $packageinfo['jap3'] = $ttfreelikesorderid;
        $packageinfo['type'] = 'likes';
    }

    $packageinfo['amount'] = $info['amount'];
}

//FREE TRIAL 30 FOLLOWERS
if($pacid=='18'){
    $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.tiktok.com/@'.$username, 'quantity' => $packageinfo['amount']));
}


//FOLLOWERS
//2196 for smaller orders, bigger followers is 1719

if($packageinfo['type']=='followers'){

$order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.tiktok.com/@'.$username, 'quantity' => $packageinfo['amount']));


$orderid = $order1->order.' '.$order2->order;

$order_status = $api->status($order1->order);
$supplier_cost = $order_status->charge;
// insert supplier cost log
mysql_query('INSERT INTO supplier_cost SET `type` = "followers", `amount` = "'.$packageinfo['amount'].'", `service_id` = "'.$packageinfo[$japid].'", `cost` ="'. $supplier_cost .'", `page` = "admin/api/ttorderfulfill.php", `timestamp` = '.time().', `socialmedia` = "'.$packageinfo['socialmedia'].'", `brand` = "'.$packageinfo['brand'].'"');


}

//LIKES & VIDEO VIEWS

if(($packageinfo['type']=='likes')||($packageinfo['type']=='views')){

$totaladdedamount = $packageinfo['amount'] * 1.3;

$multiamount = $totaladdedamount / $multiamountposts;
$multiamount = round($multiamount);

$multipleposts = explode(' ',$choosepostsql);

foreach($multipleposts as $eachpost){

if(empty($eachpost))continue;

if($packageinfo['type']=='likes'){
    $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => $eachpost, 'quantity' => $multiamount));

    $order_status = $api->status($order1->order);
    $supplier_cost = $order_status->charge;
    // insert supplier cost log
    mysql_query('INSERT INTO supplier_cost SET `type` = "likes", `amount` = '.$multiamount.', `service_id` = '.$packageinfo[$japid].', `cost` ="'. $supplier_cost .'", `page` = "admin/api/ttorderfulfill.php", timsetamp = '.time().', `socialmedia` = "'.$packageinfo['socialmedia'].'", `brand` = "'.$packageinfo['brand'].'"');

}

if($packageinfo['type']=='views'){
    $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => $eachpost, 'quantity' => $multiamount));

    $order_status = $api->status($order1->order);
    $supplier_cost = $order_status->charge;
    // insert supplier cost log
    mysql_query('INSERT INTO supplier_cost SET `type` = "views", `amount` = '.$multiamount.', `service_id` = '.$packageinfo[$japid].', `cost` ="'. $supplier_cost .'", `page` = "admin/api/ttorderfulfill.php", timsetamp = '.time().', `socialmedia` = "'.$packageinfo['socialmedia'].'", `brand` = "'.$packageinfo['brand'].'"');
}

$orderid .= $order1->order;
$orderid .= ' ';

}

}




if(($orderid)&&($info['packageid']!=='18')&&($info['packageid']!=='20')){mysql_query("UPDATE `orders` SET `done` = '1',`fulfill_id` = '$orderid' WHERE `order_session` = '{$info['order_session']}' AND brand = '$brand' ORDER BY `id` DESC LIMIT 1");}

if(empty($orderid)){//NO ORDER ID HAS COME BACK


    if($reorder=='yes')mysql_query("UPDATE `orders` SET `reorder` = `reorder` - 1 WHERE `id` = '$orderid123' AND brand = '$brand' LIMIT 1");
    $noorderid = 1;
}

//FREE TRIAL or free LIKES
if(($info['packageid']=='18')||($info['packageid']=='20')){
$orderid = $order1->order;

    mysql_query("UPDATE `orders` SET `fulfill_id` = '$orderid' WHERE `order_session` = '$hash' AND brand = '$brand' ORDER BY `id` DESC LIMIT 1");}


?>