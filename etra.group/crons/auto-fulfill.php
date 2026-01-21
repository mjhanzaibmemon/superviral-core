<?php

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

*/

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


$q_update = mysql_query("SELECT * FROM `orders` WHERE `fulfill_id` = '' AND `fulfill_attempt` < '7' AND `next_fulfill_attempt` < $now AND `next_fulfill_attempt` != '0' AND `refund` = '0' AND `lambda`='0' ORDER BY `id` DESC LIMIT 40");
/* FOR QUEUE TO PREVENT OVERLAP
    lambda = 1 is ec2
    lambda = 2 is aws lambda
*/
while ($row = mysql_fetch_array($q_update)) {$ids[] = $row['id'];}
mysql_query("UPDATE `orders` SET `lambda`='1' WHERE `id` IN (".implode(',',$ids).") ");

$q = mysql_query("SELECT * FROM `orders` WHERE `fulfill_id` = '' AND `fulfill_attempt` < '7' AND `next_fulfill_attempt` < $now AND `next_fulfill_attempt` != '0' AND `refund` = '0' ORDER BY `id` DESC LIMIT 40");

while ($info = mysql_fetch_array($q)) {

    //if($info['emailaddress']=='mac@etra.group'){continue;}  

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

    //FREE TRIAL / FREE FOLLOWERS 30 FOLLOWERS
    if ($packageinfo['type'] == "freetrial" || $packageinfo['type'] == "freefollowers") {

        $delivquantity = $info['amount'] * 1.1;
        $delivquantity = round($delivquantity);

        $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://' . $domain . '/' . $username, 'quantity' => $delivquantity));
        $orderid = $order1->order;
        
        $order_status = $api->status($order1->order);
        $supplier_cost += $order_status->charge;
    }


    if ($packageinfo['type'] == "freelikes") {

        $freelikespost = trim($info['chooseposts']);
        
        $checkifLastOrderDoneQuery = mysql_query('SELECT next_fulfill_attempt FROM orders WHERE `fulfill_id` = "" AND `chooseposts` = "'. $info['chooseposts'] .'" AND fulfill_attempt > 0 AND defect=0 AND refund=0');
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

        $thisorderpost .= '<br><b>Free Likes:</b><br>Post name:' . $freelikespost . '<br>PID: ' . $packageinfo[$japid] . '<br> Amount per post: ' . $multiamount . '<br>Fulfill ID: ' . $order1->order . '<br>';
    
    }

    //FOLLOWERS

    if ($packageinfo['type'] == 'followers') {

        if($info['amount']=='25'){$info['amount'] = '51';}


        $delivquantity = $info['amount'] * 1.06;
        $delivquantity = round($delivquantity);
        
        $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://' . $domain . '/' . $username, 'quantity' => $delivquantity));

        $orderid = $order1->order;
        
        $order_status = $api->status($order1->order);
        $supplier_cost += $order_status->charge;

        $sc = $order_status->charge;

        // insert supplier cost log
        mysql_query('INSERT INTO supplier_cost SET `type` = "followers", `amount` = "'.$delivquantity.'", `service_id` = "'.$packageinfo[$japid].'", `cost` ="'. $sc .'", `page` = "crons/auto-fulfill.php", `timestamp` = '.time().', `socialmedia` = "'.$packageinfo['socialmedia'].'", `brand` = "'.$packageinfo['brand'].'"');
    

        $thisorderpost .= '<br><b>Followers</b>:<br>PID: ' . $packageinfo[$japid] . '<br>Amount: ' . $delivquantity . '<br>Order ID: ' . $orderid . '<br>';
    }

    //LIKES & VIDEO VIEWS

    if (($packageinfo['type'] == 'likes') || ($packageinfo['type'] == 'views')) {

        /// WORKOUT HOW MANY POSTS THE USER HAS SELECTED
       
        if (empty($info['chooseposts'])) {


            $findchoosepostsq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$info['order_session']}' ORDER BY `id` DESC LIMIT 1");

            if (mysql_num_rows($findchoosepostsq) == 1) {
                $findchooseposts = mysql_fetch_array($findchoosepostsq);

                $thechoosepostsfound = $findchooseposts['chooseposts'];


                if (!empty($thechoosepostsfound)) {

                    $thechoosepostsfound = explode('~~~', $thechoosepostsfound);

                    foreach ($thechoosepostsfound as $posts1) {

                        if (empty($posts1)) continue;

                        $posts2 = explode('###', $posts1);

                        $theupdatequery .= $posts2[0] . ' ';
                        $info['chooseposts'] .= $posts2[0] . ' ';
                    }

                    $theupdatequery1 = ' Update query: "' . $theupdatequery . '" ';
                    $chooseposts = ' FOUND: ';
                    mysql_query("UPDATE `orders` SET `chooseposts` = '$theupdatequery' WHERE `id` = '{$info['id']}'  LIMIT 1");
                }
            }
        }

        $multiamountposts = 0;

        if (!empty($info['chooseposts'])) {

            $chooseposts = explode(' ', $info['chooseposts']);

            foreach ($chooseposts as $posts1) {

                if (empty($posts1)) continue;

                $posts2 = explode('###', $posts1);

                $multiamountposts++;

                $choosepostsql .= $posts2[0] . ' ';
            }
        }
         
        // free views

        if (!empty($info['freeviewsposts'])) {

            $freeviewsposts = explode(' ', $info['freeviewsposts']);

            foreach ($freeviewsposts as $freeposts1) {

                if (empty($freeposts1)) continue;

                $freeposts2 = explode('###', $freeposts1);

                $choosefreepostsql .= $freeposts2[0] . ' ';

                // if package is view , combining both columns
                if($packageinfo['type'] == 'views'){
                    $choosepostsql .= $freeposts2[0] . ' ';
                }

            }
        }

        if ($multiamountposts == 0) continue;

        // remove duplicates post if exist
        $substrings = explode(" ", $choosepostsql);
        $uniqueSubstrings = array_unique($substrings);
        $choosepostsql = implode(" ", $uniqueSubstrings);
        
        $totaladdedamount = round($delivquantity * 1.3);

        $multiamount = $totaladdedamount / $multiamountposts;
        $multiamount = round($multiamount);

        $multipleposts = explode(' ', $choosepostsql);

        $postCount = count($multipleposts) - 1;
      
        foreach ($multipleposts as $eachpost) {

            if (empty($eachpost)) continue;
           

            if ($packageinfo['type'] == 'likes') {
               
                if ($socialmedia == 'ig'){
                    $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.' . $domain . '/p/' . $eachpost . '/', 'quantity' => $multiamount));echo 'ig';

                }

                if ($socialmedia == 'tt')
                {
                    $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => $eachpost, 'quantity' => $multiamount));echo 'tt';

                }

                $order_status = $api->status($order1->order);

                if($supplier_cost == 0){
                    $supplier_cost = $order_status->charge;
                }

                $sc = $order_status->charge;

                // insert supplier cost log
                mysql_query('INSERT INTO supplier_cost SET `type` = "likes", `amount` = "'.$multiamount.'", `service_id` = "'.$packageinfo[$japid].'", `cost` ="'. $sc .'", `page` = "crons/auto-fulfill.php", `timestamp` = '.time().', `socialmedia` = "'.$packageinfo['socialmedia'].'", `brand` = "'.$packageinfo['brand'].'"');
            

                $thisorderpost .= '<br><b>Likes:</b><br>Post name:' . $eachpost . '<br>PID: ' . $packageinfo[$japid] . '<br> Amount per post: ' . $multiamount . '<br>Fulfill ID: ' . $order1->order . '<br>';
            }

            if ($packageinfo['type'] == 'views') {

                    if ($socialmedia == 'ig'){
                        $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.' . $domain . '/p/' . $eachpost . '/', 'quantity' => $multiamount));

                    }

                    if ($socialmedia == 'tt'){
                        $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => $eachpost, 'quantity' => $multiamount));

                    }

                $order_status = $api->status($order1->order);
                if($supplier_cost == 0){
                    $supplier_cost = $order_status->charge;
                }

                $sc = $order_status->charge;

                // insert supplier cost log
                mysql_query('INSERT INTO supplier_cost SET `type` = "views", `amount` = "'.$multiamount.'", `service_id` = "'.$packageinfo[$japid].'", `cost` ="'. $sc .'", `page` = "crons/auto-fulfill.php", `timestamp` = '.time().', `socialmedia` = "'.$packageinfo['socialmedia'].'", `brand` = "'.$packageinfo['brand'].'"');
            


                $thisorderpost .= '<br><b>Views:</b><br>Post name:' . $eachpost . '<br>PID: ' . $packageinfo[$japid] . '<br> Amount per post: ' . $multiamount . '<br>Fulfill ID: ' . $order1->order . '<br>';
            }



            $orderid .= $order1->order;
            $orderid .= ' ';
        }

        
        // free views
        $supplier_cost_free_views = 0;

        if($packageinfo['type'] == "likes") {

            if($multiamount < 100){
                $multiamount  = 100;
            }
            
            $multiplefreeposts = explode(' ', $choosefreepostsql);

            $packageviewinfoq = mysql_query("SELECT * FROM `packages` WHERE `socialmedia`= '$socialmedia' AND `type`= 'views' ORDER BY `amount` LIMIT 1");
            $packageviewinfo = mysql_fetch_array($packageviewinfoq);

            foreach ($multiplefreeposts as $eachfreepost) {

                    if (empty($eachfreepost)) continue;

                    if ($socialmedia == 'ig')
                        $order1 = $api->order(array('service' => $packageviewinfo[$japid], 'link' => 'https://www.' . $domain . '/p/' . $eachfreepost . '/', 'quantity' => $multiamount));

                    if ($socialmedia == 'tt')
                        $order1 = $api->order(array('service' => $packageviewinfo[$japid], 'link' => $eachfreepost, 'quantity' => $multiamount));

                    $order_status = $api->status($order1->order);
                    if($supplier_cost_free_views == 0){
                        $supplier_cost_free_views = $order_status->charge;
                    }

                    $sc = $order_status->charge;

                    // insert supplier cost log
                    mysql_query('INSERT INTO supplier_cost SET `type` = "views", `amount` = "'.$multiamount.'", `service_id` = "'.$packageinfo[$japid].'", `cost` ="'. $sc .'", `page` = "crons/auto-fulfill.php", `timestamp` = '.time().', `socialmedia` = "'.$packageinfo['socialmedia'].'", `brand` = "'.$packageinfo['brand'].'"');
                
    
                    $thisorderpost .= '<br><b>Views:</b><br>Post name:' . $eachfreepost . '<br>PID: ' . $packageinfo[$japid] . '<br> Amount per post: ' . $multiamount . '<br>Fulfill ID: ' . $order1->order . '<br>';
            
            }
        }

        $supplier_cost = ($supplier_cost + $supplier_cost_free_views) * $postCount;
        
    }

    //COMMENTS

    if ($packageinfo['type'] == 'comments') {

        echo $info['id'] . '<hr>';

        $eachpost = trim($info['chooseposts']);

        $multipleposts = explode(' ', $info['choose_comments']);

        $c = 0;
        foreach ($multipleposts as $eachcommentid) {

            if (empty($eachcommentid)) continue;

            $eachcommentid = trim($eachcommentid);


            $findcommentbyidq = mysql_query("SELECT * FROM `order_comments` WHERE `id` = '$eachcommentid' LIMIT 1");
            $fetchcommentbyid = mysql_fetch_array($findcommentbyidq);

            //echo $eachcommentid.' - '.$fetchcommentbyid['comment'].'<br>';
            $comments .= $fetchcommentbyid['comment'] . "\r\n";

            $c++;
            //echo '<hr>';

        }


        $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.' . $domain . '/p/' . $eachpost . '/', 'comments' => $comments));
        $orderid .= $order1->order;
        $orderid .= ' ';

        $order_status = $api->status($order1->order);
        $supplier_cost += $order_status->charge;

        $sc = $order_status->charge;

        // insert supplier cost log
        mysql_query('INSERT INTO supplier_cost SET `type` = "likes", `amount` = "'.$c.'", `service_id` = "'.$packageinfo[$japid].'", `cost` ="'. $sc .'", `page` = "crons/auto-fulfill.php", `timestamp` = '.time().', `socialmedia` = "'.$packageinfo['socialmedia'].'", `brand` = "'.$packageinfo['brand'].'"');
    

        //echo $comments;

    }


    

    $supplier_error = $order1->error;

    if ((!empty($orderid)) && (preg_match('~[0-9]+~', $orderid)) && empty($supplier_error)) {

        $updateq = mysql_query("UPDATE `orders` SET `done` = '1',`fulfill_id` = '$orderid', `supplier_cost` = '$supplier_cost' WHERE `id` = '{$info['id']}' ORDER BY `id` DESC LIMIT 1");
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

    $updateq = mysql_query("UPDATE `orders` SET `fulfill_attempt` = `fulfill_attempt` + 1, `next_fulfill_attempt` = '$next_fulfill_attempt', `supplier_errors` = '$error' WHERE `id` = '{$id}' LIMIT 1");

    if ($updateq) {
        return '<font color="red">Not fulfilled!</font>';
    } else {
        return '<font color="orange">Not fulfilled! but failed to update DB</font>';
    }
}