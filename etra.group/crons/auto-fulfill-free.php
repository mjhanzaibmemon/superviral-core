<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');


// dont run cron on minute 5 and 6
$currentMinute = (int) date('i');
if ($currentMinute % 10 === 5 || $currentMinute % 10 === 6) {die;}

include('../sm-db.php');

class Api
{
    public function setApiKey( $value ){$this->api_key = $value;}
    public function setApiUrl( $value ){$this->api_url = $value;}

    public function order($data) { // add order
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
// Examples


///////////////////////////////////////////////////////////// 


$api = new Api();

$api->setApiKey($fulfillment_api_key);
$api->setApiUrl($fulfillment_url);

$now = time();


$q_update = mysql_query("SELECT id FROM `orders_free` WHERE `fulfill_id` = '' AND `fulfill_attempt` < '7' AND `next_fulfill_attempt` < $now AND `next_fulfill_attempt` != '0' AND `packagetype` IN ('freelikes','freetrial','freefollowers') AND `lambda`='0' ORDER BY `id` DESC LIMIT 20");
/* FOR QUEUE TO PREVENT OVERLAP
    lambda = 1 is ec2
    lambda = 2 is aws lambda
*/
while ($row = mysql_fetch_array($q_update)) {$ids[] = $row['id'];}
mysql_query("UPDATE `orders_free` SET `lambda`='1' WHERE `id` IN (".implode(',',$ids).") ");

$q = mysql_query("SELECT * FROM `orders_free` WHERE `fulfill_id` = '' AND `fulfill_attempt` < '7' AND `next_fulfill_attempt` < $now AND `next_fulfill_attempt` != '0' AND `packagetype` IN ('freelikes','freetrial','freefollowers') AND `lambda`='1' ORDER BY `id` DESC LIMIT 20");

while ($info = mysql_fetch_array($q)) {

    if($info['emailaddress']=='mac@etra.group'){continue;}  

    $socialmedia = $info['socialmedia'];
    $pacid = $info['packageid'];
    $username = $info['igusername'];
    $supplier_cost = 0;

    $packageinfoq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1");
    $packageinfo = mysql_fetch_array($packageinfoq);

    $japid = 'jap1';


    $delivquantity = $info['amount'];

    switch ($socialmedia) {
        case 'ig':
            $domain = 'instagram.com';
        break;
        case 'tt':
            $domain = 'tiktok.com';
            $username = '@' . $username;
        break;
    }

    if($brand == 'to'){
        $socialmedia = '';
        $domain = 'tiktok.com';
        $username = '@' . $username;
    }

    //FREE TRIAL 30 FOLLOWERS
    if ($packageinfo['type'] == "freetrial" || $packageinfo['type'] == "freefollowers") {

        $delivquantity = $info['amount'] * 1.1;
        $delivquantity = round($delivquantity);

        $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://' . $domain . '/' . $username, 'quantity' => $delivquantity));
        $orderid = $order1->order;
        
        $order_status = $api->status($order1->order);
        $supplier_cost += $order_status->charge;

        // insert supplier cost log
        mysql_query('INSERT INTO supplier_cost SET `type` = "followers", `amount` = "'.$delivquantity.'", `service_id` = "'.$packageinfo[$japid].'", `cost` ="'. $supplier_cost .'", `page` = "crons/auto-fulfill-free.php", `timestamp` = '.time().', `socialmedia` = "'.$packageinfo['socialmedia'].'", `brand` = "'.$packageinfo['brand'].'"');
    
    }


    if ($packageinfo['type'] == "freelikes") {

        $freelikespost = trim($info['chooseposts']);
        
        $checkifLastOrderDoneQuery = mysql_query('SELECT next_fulfill_attempt FROM orders_free WHERE `fulfill_id` = "" AND `chooseposts` = "'. $info['chooseposts'] .'" AND fulfill_attempt > 0');
        $last_order_row = mysql_fetch_array($checkifLastOrderDoneQuery);
        if(mysql_num_rows($checkifLastOrderDoneQuery) > 0){
            // means last order not done, queue the next same order
            $error = date('d/m/Y').' - Existing order in process';
            
            $status = updateFulfillAttempt($info['fulfill_attempt'],$last_order_row['next_fulfill_attempt'],$error,$info['id']);
            continue;

        }

        $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.' . $domain . '/p/' . $freelikespost . '/', 'quantity' => $delivquantity));
        $orderid = $order1->order;

        $order_status = $api->status($order1->order);
        $supplier_cost += $order_status->charge;

        // insert supplier cost log
        mysql_query('INSERT INTO supplier_cost SET `type` = "likes", `amount` = "'.$delivquantity.'", `service_id` = "'.$packageinfo[$japid].'", `cost` ="'. $supplier_cost .'", `page` = "crons/auto-fulfill-free.php", `timestamp` = '.time().', `socialmedia` = "'.$packageinfo['socialmedia'].'", `brand` = "'.$packageinfo['brand'].'"');
    

        $thisorderpost .= '<br><b>Free Likes:</b><br>Post name:' . $freelikespost . '<br>PID: ' . $packageinfo[$japid] . '<br> Amount per post: ' . $multiamount . '<br>Fulfill ID: ' . $order1->order . '<br>';
    
    }

    $supplier_error = $order1->error;

    if ((!empty($orderid)) && (preg_match('~[0-9]+~', $orderid)) && empty($supplier_error)) {

        $updateq = mysql_query("UPDATE `orders_free` SET `done` = '1',`fulfill_id` = '$orderid', `supplier_cost` = '$supplier_cost' WHERE `id` = '{$info['id']}' ORDER BY `id` DESC LIMIT 1");
        if ($updateq) {
            $status = '<font color="green">Fulfilled!</font>';
        } else {
            $status = '<font color="orange">Fulfilled! but failed to update DB</font>';
        }

    } else {

        //NO ORDER ID HAS COME BACK
        $status = updateFulfillAttempt($info['fulfill_attempt'],$info['next_fulfill_attempt'],$supplier_error,$info['id']);

    }

    unset($username);
    unset($thisorderpost);
    unset($pacid);
    unset($chooseposts);
    unset($eachpost);
    unset($orderid);
    unset($order1);
    unset($totaladdedamount);
    unset($posts1);
    unset($posts2);
    unset($multiamountposts);
    unset($choosepostsql);
    unset($packageinfo);
    unset($last_order_row);
    unset($status);
    unset($nextdelay);
    unset($next_fulfill_attempt);
    unset($findchoosepostsq);
    unset($findchooseposts);
    unset($thechoosepostsfound);
    unset($theupdatequery);
    unset($theupdatequery1);
    unset($chooseposts);
    unset($updateq);
    unset($freelikespost);
    unset($delivquantity);
    unset($fetchcommentbyid);
    unset($comments);
    unset($findcommentbyidq);
    unset($multipleposts);
    unset($multiplefreeposts);
    unset($supplier_cost);
    unset($supplier_error);
}

echo '
    <style>
    body{font-family:arial;}
    h3{font-size:16px;}
    </style>';


function updateFulfillAttempt($i,$timestamp,$error,$id){
    
    //DELAY BASED ON STAGE OF FULFILL ATTEMPTS
    if ($i == '1') $nextdelay = '100';
    if ($i == '2') $nextdelay = '600';
    if ($i == '3') $nextdelay = '1800';
    if ($i == '4') $nextdelay = '3600';
    if ($i == '5') $nextdelay = '7200';
    if ($i == '6') $nextdelay = '15800';

    $next_fulfill_attempt = $timestamp + $nextdelay;

    $updateq = mysql_query("UPDATE `orders_free` SET `fulfill_attempt` = `fulfill_attempt` + 1, `next_fulfill_attempt` = '$next_fulfill_attempt', `supplier_errors` = '$error' WHERE `id` = '{$id}' LIMIT 1");

    if ($updateq) {
        return '<font color="red">Not fulfilled!</font>';
    } else {
        return '<font color="orange">Not fulfilled! but failed to update DB</font>';
    }
}