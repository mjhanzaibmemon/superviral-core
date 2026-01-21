<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

*/
include('../sm-db.php');

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


    $q = mysql_query("SELECT * FROM `orders` WHERE `fulfill_id` = '' AND `fulfill_attempt` < '7' AND `next_fulfill_attempt` < $now AND `next_fulfill_attempt` != '0' AND `refund` = '0' LIMIT 40");

  $q = mysql_query("SELECT * FROM `orders` WHERE `fulfill_id` = '' AND `refund` = '0' AND `packagetype` = 'comments' LIMIT 40");



    while($info = mysql_fetch_array($q)){

    $brand = $info['brand'];
    $pacid = $info['packageid'];
    $username = $info['igusername'];

    $packageinfoq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND brand = '$brand' LIMIT 1");
    $packageinfo = mysql_fetch_array($packageinfoq);

    $japid = 'jap1';

    if($info['reorder']=='1')$japid = 'jap2';
    if($info['reorder']=='2')$japid = 'jap3';


    $delivquantity = $info['amount'];

    switch($brand){
        case 'sv':
        case 'fb':
        case 'sz':
        $domain = 'instagram.com';    
        break;
        case 'to':
        case 'tp':
        $domain = 'tiktok.com'; 
        $username = '@'.$username;   
        break;
    }


    //FREE TRIAL 30 FOLLOWERS
    if($pacid=='18'){

        $delivquantity = $info['amount'] * 1.1;
        $delivquantity = round($delivquantity);

                    $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://'. $domain .'/'.$username, 'quantity' => $delivquantity));
                    $orderid = $order1->order.' '.$order2->order;

    }

    if($pacid=='20'){

                    $freelikespost = trim($info['chooseposts']);

                    $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.'. $domain .'/p/'.$freelikespost.'/', 'quantity' => $delivquantity));

                    $orderid = $order1->order.' '.$order2->order;


                    $thisorderpost .= '<br><b>Free Likes:</b><br>Post name:'.$freelikespost.'<br>PID: '.$packageinfo[$japid].'<br> Amount per post: '.$multiamount.'<br>Fulfill ID: '.$order1->order.'<br>';


    }

    //FOLLOWERS

    if($packageinfo['type']=='followers'){

        if($info['amount']=='25'){$info['amount'] = '51';}


    $delivquantity = $info['amount'] * 1.06;
    $delivquantity = round($delivquantity);

                    $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://'. $domain .'/'.$username, 'quantity' => $delivquantity));

                    $orderid = $order1->order.' '.$order2->order;

                    $thisorderpost .= '<br><b>Followers</b>:<br>PID: '.$packageinfo[$japid].'<br>Amount: '.$delivquantity.'<br>Order ID: '.$orderid.'<br>';

    }

    //LIKES & VIDEO VIEWS

    if(($packageinfo['type']=='likes')||($packageinfo['type']=='views')){




                    /// WORKOUT HOW MANY POSTS THE USER HAS SELECTED

                    if(empty($info['chooseposts'])){


                            $findchoosepostsq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$info['order_session']}'  AND brand = '$brand' ORDER BY `id` DESC LIMIT 1");

                            if(mysql_num_rows($findchoosepostsq)==1){$findchooseposts = mysql_fetch_array($findchoosepostsq);

                            $thechoosepostsfound = $findchooseposts['chooseposts'];


                                if(!empty($thechoosepostsfound)){

                                        $thechoosepostsfound = explode('~~~', $thechoosepostsfound);

                                        foreach($thechoosepostsfound as $posts1){

                                        if(empty($posts1))continue;

                                        $posts2 = explode('###', $posts1);

                                        $theupdatequery .= $posts2[0].' ';
                                        $info['chooseposts'] .= $posts2[0].' ';

                                        }

                                        $theupdatequery1 = ' Update query: "'.$theupdatequery.'" ';
                                        $chooseposts = ' FOUND: ';
                                        mysql_query("UPDATE `orders` SET `chooseposts` = '$theupdatequery' WHERE `id` = '{$info['id']}'  AND brand = '$brand' LIMIT 1");

                                }

                            }


                    }

                    $multiamountposts=0;

                    if(!empty($info['chooseposts'])){

                                $chooseposts = explode(' ', $info['chooseposts']);

                                foreach($chooseposts as $posts1){

                                if(empty($posts1))continue;

                                $posts2 = explode('###', $posts1);

                                $multiamountposts++;

                                $choosepostsql .= $posts2[0].' ';


                                }

                    }

                    if($multiamountposts==0)continue;



                    $totaladdedamount = round($delivquantity * 1.3);

                    $multiamount = $totaladdedamount / $multiamountposts;
                    $multiamount = round($multiamount);

                    $multipleposts = explode(' ',$choosepostsql);

                    foreach($multipleposts as $eachpost){

                    if(empty($eachpost))continue;
                    

                    if($packageinfo['type']=='likes'){

                        if($brand== 'sv' || $brand == 'fb' || $brand == 'sz')
                        $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.'. $domain .'/p/'.$eachpost.'/', 'quantity' => $multiamount));
                        
                        if($brand == 'to' || $brand == 'tp')
                        $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => $eachpost, 'quantity' => $multiamount));
                        
                        $thisorderpost .= '<br><b>Likes:</b><br>Post name:'.$eachpost.'<br>PID: '.$packageinfo[$japid].'<br> Amount per post: '.$multiamount.'<br>Fulfill ID: '.$order1->order.'<br>';
                    

                    }

                    if($packageinfo['type']=='views'){
                        
                        if($brand== 'sv' || $brand == 'fb' || $brand == 'sz')
                        $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.'. $domain .'/p/'.$eachpost.'/', 'quantity' => $multiamount));

                        if($brand == 'to' || $brand == 'tp')
                        $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => $eachpost, 'quantity' => $multiamount));

                        $thisorderpost .= '<br><b>Views:</b><br>Post name:'.$eachpost.'<br>PID: '.$packageinfo[$japid].'<br> Amount per post: '.$multiamount.'<br>Fulfill ID: '.$order1->order.'<br>';


                    }



                    $orderid .= $order1->order;
                    $orderid .= ' ';

                    }

        }

        //LIKES & VIDEO VIEWS

        if($packageinfo['type']=='comments'){

                echo $info['id'].'<hr>';

                    $eachpost = trim($info['chooseposts']);

                    $multipleposts = explode(' ',$info['choose_comments']);

                    foreach($multipleposts as $eachcommentid){

                        if(empty($eachcommentid))continue;

                        $eachcommentid = trim($eachcommentid);


                        $findcommentbyidq = mysql_query("SELECT * FROM `order_comments` WHERE `id` = '$eachcommentid' LIMIT 1");
                        $fetchcommentbyid = mysql_fetch_array($findcommentbyidq);

                        //echo $eachcommentid.' - '.$fetchcommentbyid['comment'].'<br>';
                        $comments .= $fetchcommentbyid['comment']."\r\n";


                        //echo '<hr>';

                    }


                    $order1 = $api->order(array('service' => $packageinfo[$japid], 'link' => 'https://www.'. $domain .'/p/'.$eachpost.'/', 'comments' => $comments));
                    $orderid .= $order1->order;
                    $orderid .= ' ';

                    //echo $comments;

        }



        if((!empty($orderid))&&(preg_match('~[0-9]+~', $orderid))){

           $updateq =  mysql_query("UPDATE `orders` SET `done` = '1',`fulfill_id` = '$orderid' WHERE `id` = '{$info['id']}'  AND brand = '$brand' ORDER BY `id` DESC LIMIT 1");

           if($updateq){ $status = '<font color="green">Fulfilled!</font>';} else{$status = '<font color="orange">Fulfilled! but failed to update DB</font>';}

        } else {//NO ORDER ID HAS COME BACK

            //DELAY BASED ON STAGE OF FULFILL ATTEMPTS
            if($info['fulfill_attempt']=='1')$nextdelay = '100';
            if($info['fulfill_attempt']=='2')$nextdelay = '600';
            if($info['fulfill_attempt']=='3')$nextdelay = '1800';
            if($info['fulfill_attempt']=='4')$nextdelay = '3600';
            if($info['fulfill_attempt']=='5')$nextdelay = '7200';
            if($info['fulfill_attempt']=='6')$nextdelay = '10800';

            $next_fulfill_attempt = $info['next_fulfill_attempt'] + $nextdelay;

           $updateq = mysql_query("UPDATE `orders` SET `fulfill_attempt` = `fulfill_attempt` + 1, `next_fulfill_attempt` = '$next_fulfill_attempt' WHERE `id` = '{$info['id']}'  AND `brand` = '$brand' LIMIT 1");


            if($updateq){  $status = '<font color="red">Not fulfilled!</font>';}else{$status = '<font color="orange">Not fulfilled! but failed to update DB</font>';}

        }

    if( $_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222')echo '<h3>'.$pacid.' '.$packageinfo['type'].' - '.$info['id'].' - '.$orderid.' - '.$username.' - '.$status.'</h3>'.$thisorderpost.'<hr>';

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



    }

    echo '
    <style>
    body{font-family:arial;}
    h3{font-size:16px;}
    </style>';



?>