<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

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


$api = new Api();

$api->setApiKey($fulfillment_api_key);
$api->setApiUrl($fulfillment_url);

$auto = addslashes($_GET['auto']);
$reportdone = addslashes($_POST['reportdone']);
$directions = addslashes($_POST['directions']);
$type = addslashes($_GET['type']);
$search = trim(addslashes($_GET['search']));
$gotopage = trim(addslashes($_POST['gotopage']));

if(empty($type))header('Location: /admin/reports/?type=reported');

if(!empty($reportdone)){

    // echo "UPDATE email_queue SET `markDone` = '0' WHERE `from` = '$to' order by id desc limit 1";
    // die;
    
    ////NOTIFY THE USER HERE VIA EMAIL

    $findreportinfoq = mysql_query("SELECT * FROM `admin_notifications` WHERE `id` = '$reportdone' LIMIT 1");
    $findreportinfo = mysql_fetch_array($findreportinfoq);

    $brandName = getBrandSelectedName($findreportinfo['brand']);
    $brandDomain = getBrandSelectedDomain($findreportinfo['brand']);
    
    $to = $findreportinfo['emailaddress'];
    $subject = $brandName .': Update on your issue';

    ////////////////////////////////////////////// THE EMAIL GOES HERE


    $emailbody = '<p>Hi there,</p>
    <br>
    <p>This is Helen from '. $brandName .'\'s management team to just notify you that I\'ve received your issue and I\'ve given additional support to James to help resolve this issue as soon as possible.</p>
    <br>
    <p>Speaking to James, he\'s advised me that he will respond within the next 19-24 hours and is available to respond to your issue.</p>
    <br>
    <p>Other '. $brandName .' teams usually get involved with an issue when a customer service rep, requires the assistance of more than one team that specialises in that issue.</p>
    <br>
    <p>For example, when there\'s a technical issue, our technicians would get involved to diagnose the issue as they\'re trained to deal with those type of issues.</p>
    <br>
    <p>If there is anything else you need, please do not hesitate to get in touch with my colleague James regarding the issue. He will respond within the next 19-24 hours.</p>
    <br>
    <p>Thank you for your patience.</p>
    <br>
    <p>Kind regards,<br>
    '. $brandName .' Management Team</p>
    <br>
    <p>160 City Road<br>
    London<br>
    EC1V 2NX<br>
    United Kingdom</p>';    

    $tpl = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]).'/'. $brandDomain .'/emailtemplate/emailtemplate.html');
    $tpl = str_replace('{body}',$emailbody,$tpl);
    $now = time();  

    $tpl = str_replace('Unsubscribe','',$tpl);  

    $tpl = str_replace('{subject}',$subject,$tpl);  

    require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';
    emailnow($to, $brandName ,'support@'.$brandDomain,$subject,$tpl);   
    
    //$bodyText = str_replace("'","\'", $tpl);
    //email_stat_insert('Email Support', $to, $bodyText, $findreportinfo['brand']);
    
    //////////////////////////////////////////////




    $respondedby = $_SESSION['first_name'];
    $response = time();
	$directions = nl2br($directions);
    mysql_query("UPDATE email_queue SET `markDone` = '0' WHERE `from` = '$to' order by id desc limit 1");
	mysql_query("UPDATE `admin_notifications` SET `directions` = '$directions',`done` = '1',`response` = '$response',`respondedby` = '$respondedby' WHERE `id` = '$reportdone' LIMIT 1");
    if(!empty($gotopage))header('Location: /admin/reports/?type=reported&page='.$gotopage);

}

if($type=='all'){$q = mysql_query("SELECT * FROM `orders` WHERE `id` > '73663'  ORDER BY `emailaddress` ASC LIMIT 30");}

//REPORTED Section queries
if($type=='reported'){


         if (isset($_GET['page'])) {$page = addslashes($_GET['page']);} else {$page = 1;}
         if($page < 1)$page = 1;

          $no_of_records_per_page = 1;
        $offset = ($page-1) * $no_of_records_per_page;

        $q = mysql_query("SELECT * FROM `admin_notifications` WHERE `done` = '0'  ORDER BY `id`,`difficulty` DESC LIMIT $offset, $no_of_records_per_page");


        $q2 = mysql_query("SELECT * FROM `admin_notifications` WHERE `done` = '0' ORDER BY `difficulty` DESC");
        $totalleft = mysql_num_rows($q2).' Reports Remaining';

}


$theid = $_GET['theid'];

if(!empty($theid)){$styles = '.first'.$theid.',.second'.$theid.'{background-color:#e9ffe9;}';}

if($_GET['message']=='email1')$message = '<div class="emailsuccess">Private IG account email: Sent</div>';
if($_GET['message']=='updatetrue')$message = '<div class="emailsuccess">Order '.$theid.' successfully updated.</div>';
if($_GET['message']=='updatetrue2')$message = '<div class="emailsuccess">Resubmitted new order for Order '.$theid.'. Updated successfully.</div>';

$i = 0;

while($info = mysql_fetch_array($q)){

        ////////////////// IF SHOW POSTS INSTEAD OF USERNAME


            if($info['amount'] >= 5000 && $info['amount'] <= 8000)$fraudwarning = '<div style="padding: 5px;background-color: orange;margin-bottom: 15px;">⏸️ Fraud warning: check if this order is legitimate.</div>';

            if(empty($info['chooseposts'])){                
                if($info['packagetype']=='likes'||$info['packagetype']=='views'){

                $findchoosepostsq = mysql_query("SELECT * FROM `order_session_paid` WHERE `order_session` = '{$info['order_session']}' LIMIT 1");

                if(mysql_num_rows($findchoosepostsq)=='0')$findchoosepostsq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$info['order_session']}'  LIMIT 1");


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

                            }

                            $theupdatequery1 = ' Update query: "'.$theupdatequery.'" ';
                            $chooseposts = ' FOUND: ';
                            mysql_query("UPDATE `orders` SET `chooseposts` = '$theupdatequery' WHERE `id` = '{$info['id']}' LIMIT 1");
                        


                    }


                } 
            }


            ////////////////////

            $info['price'] = sprintf('%.2f', $info['price'] / 100);
                    
        

            if($type=='reported'){//REPORTED

                $getreportedinfoq = mysql_query("SELECT * FROM `orders` WHERE `id` = '{$info['orderid']}'  LIMIT 1");
                $getreportedinfo = mysql_fetch_array($getreportedinfoq);
            
                $show = '<a  target="_BLANK" rel="noopener noreferrer" href="'.$fulfillmentsite.'/orders?search='.$getreportedinfo['fulfill_id'].'">'.$getreportedinfo['fulfill_id'].'</a>';
            
            
                if (strpos($getreportedinfo['fulfill_id'],' ') !== false){
                
                    unset($show);
                
                    $fulfillids = explode(' ', $getreportedinfo['fulfill_id']);
                
                    foreach ($fulfillids as $eachfid) {
                        $show .= '<a target="_BLANK" rel="noopener noreferrer" href="'.$fulfillmentsite.'/orders?search='.$eachfid.'">'.$eachfid.'</a><br>';
                    }
                
                }
            
            
               if (strpos($getreportedinfo['payment_id'], 'pi_') !== false)$paymenturl = 'https://dashboard.stripe.com/payments/';
               if (strpos($getreportedinfo['payment_id'], '-') !== false)$paymenturl = 'https://my.cardinity.com/payment/show/';
            
               if (strpos($info['orderid'], 'AL') !== false){
            
                    $hrefurlcheck = '/admin/check-al/';
            

            
               }else{
            
                    $hrefurlcheck = '/admin/check-user/';
            
               }

               $brandName = getBrandSelectedName($info['brand']);
               $keyword = getSocialMediaSource($getreportedinfo['socialmedia']);

            	$articles .='
                <tr>

            	<td class="order-side">
                <div style="display:flex;gap:10px;align-items:center">
                    <span>🏢 Company: </span>
                    <div class="logo">
                        <img src="/admin/assets/icons/'. $brandName .'.svg">
                    </div>
                </div>
                <hr>

                Customer Order ID:<br><a target="_BLANK" href="'.$hrefurlcheck.'?orderid='.$info['orderid'].'">'.$info['orderid'].'</a><br><a target="_BLANK" href="/admin/check-user/?user='.$info['emailaddress'].'">'.$info['emailaddress'].'</a>
                <br>
                <img onerror="this.style.display=\'none\'" src="/admin/assets/icons/'.(getSocialMediaSource($getreportedinfo['socialmedia'])).'-icon.svg" style ="margin-right: 20px;max-width:25px;">
                <hr>
                Supplier fullfil ID:<br>'.$show.'<hr>
                Payment ID:<br><a target="_BLANK" rel="noopener noreferrer" href="'.$paymenturl.$getreportedinfo['payment_id'].'">'.$getreportedinfo['payment_id'].'</a><br>
           
                </td>
           
                <td class="reason-side">'.$info['message'].'<br><span class="time">('.$info['admin_name'].' - '.ago($info['added']).', '.date("d/m/Y H:i:s",$info['added']).')</span><br>
           
                <form method="POST" action="?type=reported"><input type="hidden" name="reportdone" value="'.$info['id'].'">
            	<textarea class="reportmessage" name="directions" id="text" style="min-height:200px;"></textarea>
                <input type="hidden" name="gotopage" value="'.$page.'">
                <input type="submit" onclick="return confirm(\'Are you sure youve dealt with the report?\');" class="btn btn-primary color3" style="width:initial;" value="Report Done">
                </form></td><tr>';
           
           
            unset($show);
            unset($reason);
           
            }

}

if($type=='reported'){//REPORTED TABLE


$articles = '<div class="box24" style="color: grey;
    margin: 0;
    margin-bottom: 1px;
    text-align: right;
    padding-left: 0;">

    <a href="?type=reported" class="btn btn3 report copy-button nlbtn">⏪ First Report</a>
    <a href="?type=reported&page='.($page-1).'" class="btn btn3 report copy-buttonn nlbtn">◀️ Previous Report</a>
    <a href="?type=reported&page='.($page+1).'" class="btn btn3 report copy-button nlbtn">Next Report ▶️</a>

    <span>'.$totalleft.'</span></div><table class="articles">

				<tr>

					<td>Reported Order</td>
					<td>Reported Reason</td>
				</tr>

				'.$articles.'

			</table>';


}

$tpl = str_replace('{message}',$message,$tpl);
$tpl = str_replace('{articles}',$articles,$tpl);
$tpl = str_replace('{autojs}',$autojs,$tpl);

output($tpl, $options);
