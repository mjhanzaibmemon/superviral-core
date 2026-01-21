<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once  $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

if(addslashes($_GET['refill']) == "true"){

    $id = addslashes($_GET['id']);

    if(empty($id)){
        echo "<script>alert('Empty Order Id'); 
        window.location.href = '/admin/reports/?type=reported';</script>";
        die;
    }
    $q = mysql_query("UPDATE orders SET norefill=1 WHERE id ='$id' limit 1");

    if($q){
        echo "<script>alert('Marked Successfully'); 
        window.location.href = '/admin/reports/?type=reported';</script>";
        die;
    }

}

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

$deletereport = addslashes($_POST['deletereport']);
$deletereportid = addslashes($_POST['deletereportid']);
if (!empty($deletereport)) {
	mysql_query("DELETE FROM `admin_notifications` WHERE `id` = '$deletereportid' LIMIT 1");
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

               
                if(mysql_num_rows($getreportedinfoq) == 0){
                    $nofirstOrderCss = 'display:none;';
                }
            
                $supplierError = !empty($getreportedinfo['supplier_errors']) ? $getreportedinfo['supplier_errors'] : "";

               
                if ($getreportedinfo['fulfilled'] == '0') {

                    $amounto = $getreportedinfo['amount'];
        
                    if ($amounto >= 1 && $amounto <= 150) {
                        $approx = '9-10 hours';
                    }
                    if ($amounto >= 151 && $amounto <= 250) {
                        $approx = '12-13 hours';
                    }
                    if ($amounto >= 251 && $amounto <= 380) {
                        $approx = '14-15 hours';
                    }
                    if ($amounto >= 500 && $amounto <= 999) {
                        $approx = '14-15 hours';
                    }
                    if ($amounto >= 1000 && $amounto <= 1500) {
                        $approx = '24-28 hours';
                    }
                    if ($amounto >= 2500 && $amounto <= 3750) {
                        $approx = '27-35 hours';
                    }
                    if ($amounto >= 5000 && $amounto <= 8000) {
                        $approx = '38-48 hours';
                    }
        
                    if (!empty($approx)) $approx1 = '(will take around ' . $approx . ')';
    
                    $orderstatus = '<font color="orange">In progress ' . $approx1 . '</font>';
                    $arstatus = 'in progress';
                    $artime = ' Please provide up to ' . $approx . ' for your order to be delivered. ';
                } else {
                    $orderstatus = '<font color="green">Completed: ' . date('l jS \of F Y H:i:s ', $getreportedinfo['fulfilled']) . '</font>';
                    $arstatus = 'completed';

                }
                $domain = getBrandSelectedDomain($getreportedinfo['brand']);
                $brandName = getBrandSelectedName($getreportedinfo['brand']);
                $keyword = getSocialMediaSource($getreportedinfo['socialmedia']);

                $packagetype = $getreportedinfo['amount'] . ' ' . $getreportedinfo['packagetype'];
                $price = sprintf('%.2f', $getreportedinfo['price'] / 100);

                if ($getreportedinfo['refund'] == '1') {
                    $refundcolor = 'orange';
                    $refunded = '<font color="orange">(refund in progress)</font>';
                } else {
                    $refundcolor = 'grey';
                    $refunded = '';
                }
                if ($getreportedinfo['refund'] == '2') $refunded = '<font color="red">(refunded)</font>';
                $findordercostq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = '{$getreportedinfo['packagetype']}'  AND brand ='{$getreportedinfo['brand']}' LIMIT 1");
		        $findordercostinfo = mysql_fetch_array($findordercostq);

		        $saleamount = $price;
		        $salecost = $findordercostinfo['perone'] * ($getreportedinfo['amount']);

		        $saleamount = round(0.58 * ($saleamount - $salecost), 2);

                if ($getreportedinfo['packagetype'] == 'followers') {
                    $searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'likes'  AND brand ='{$getreportedinfo['brand']}' LIMIT 1");
                    $refundpackageinfo = mysql_fetch_array($searchpackageq);
                    $refundoffertype = 'likes';
                    $ardestination = 'posts';
                }
        
                if ($getreportedinfo['packagetype'] == 'likes') {
                    $searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'followers'  AND brand ='{$getreportedinfo['brand']}' LIMIT 1");
                    $refundpackageinfo = mysql_fetch_array($searchpackageq);
                    $refundoffertype = 'followers';
                    $ardestination = 'profile';
                }
        
                if ($getreportedinfo['packagetype'] == 'views') {
                    $searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'likes'  AND brand ='{$getreportedinfo['brand']}' LIMIT 1");
                    $refundpackageinfo = mysql_fetch_array($searchpackageq);
                    $refundoffertype = 'likes';
                    $ardestination = 'posts';
                }
        
                if (!empty($refundpackageinfo['perone'])) {
                    $refundoffer1 = round($saleamount / $refundpackageinfo['perone']);
                }
        
                $refundoffer2 = round($refundoffer1 * $refundpackageinfo['perone'], 2);
               
                $refundoffers = 'upto ' . $refundoffer1 . ' ' . $refundoffertype . ' - £' . $refundoffer2;

                $brandSource = getBrandSelectedSource($getreportedinfo['socialmedia']);
		        $UserName = $getreportedinfo['igusername'];
		        if($getreportedinfo['socialmedia'] == 'tt') $sourceURL = "https://$brandSource/@$UserName/";
		        else $sourceURL = "https://$brandSource/$UserName/";

                $accountHtm = '<span id="spanAccountId'. $getreportedinfo['id'] .'">' . $getreportedinfo['account_id'] . '</span><a href="javascript:void(0);" class="modal-button btn3 modalBtn" id="editAccountId'. $getreportedinfo['id'] .'" onclick="openModal(this.id);" title="edit" style="display: inline-block;width: 25px;height: 25px;border: solid 1px #ddd;border-radius: 7px;margin-left: 17px;background: #f5f5f5;"> <svg style="width:25px;height: 18px;transform: translateY(2px);fill: black;" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 50 50">
                        <path d="M 43.125 2 C 41.878906 2 40.636719 2.488281 39.6875 3.4375 L 38.875 4.25 L 45.75 11.125 C 45.746094 11.128906 46.5625 10.3125 46.5625 10.3125 C 48.464844 8.410156 48.460938 5.335938 46.5625 3.4375 C 45.609375 2.488281 44.371094 2 43.125 2 Z M 37.34375 6.03125 C 37.117188 6.0625 36.90625 6.175781 36.75 6.34375 L 4.3125 38.8125 C 4.183594 38.929688 4.085938 39.082031 4.03125 39.25 L 2.03125 46.75 C 1.941406 47.09375 2.042969 47.457031 2.292969 47.707031 C 2.542969 47.957031 2.90625 48.058594 3.25 47.96875 L 10.75 45.96875 C 10.917969 45.914063 11.070313 45.816406 11.1875 45.6875 L 43.65625 13.25 C 44.054688 12.863281 44.058594 12.226563 43.671875 11.828125 C 43.285156 11.429688 42.648438 11.425781 42.25 11.8125 L 9.96875 44.09375 L 5.90625 40.03125 L 38.1875 7.75 C 38.488281 7.460938 38.578125 7.011719 38.410156 6.628906 C 38.242188 6.246094 37.855469 6.007813 37.4375 6.03125 C 37.40625 6.03125 37.375 6.03125 37.34375 6.03125 Z"></path>
                        </svg> </a>';

                $firstOrderLoad = '<div class="details">
                                <div style="padding:0 20px;"><div class="bold">Order Details</div>
                                <div style="font-size:30px;">#'. $getreportedinfo['id'] .'<img src="/admin/assets/icons/' . $keyword . '-icon.svg" style ="margin-left:5px;width: 15px;"></div></div>
                                <div class="info-line"><span class="c1">🌍Country:</span><span class="c2">'. $getreportedinfo['country'] .'</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">🏢Company:</span><span class="c2"><img src="/admin/assets/icons/' . $brandName . '.svg"></span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">#️⃣Account ID:</span><span class="c2">'. $accountHtm .'</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">🧍Username:</span><span class="c2"> <a target="_BLANK" style="color: #0000ff;text-decoration: underline;" rel="noopener noreferrer" href="'. $sourceURL .'">'. $getreportedinfo['igusername'] .'</a></span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">📧Emailaddress:</span><span class="c2"><a style="color: #0000ff;text-decoration: underline;" href="/admin/check-user/?user='. $getreportedinfo['emailaddress'] .'" target= "_blank">'. $getreportedinfo['emailaddress'] .'</a></span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">📦Package:</span><span class="c2">'. $packagetype .'</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">💸Amount Paid:</span><span class="c2">£'. $price . $refunded .'</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">📞Contact Number:</span><span class="c2">'. $getreportedinfo['contactnumber'] .'</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">⌚Order Made On:</span><span class="c2"> '. date("l jS \of F Y H:i:s",$getreportedinfo['added']) .'</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">♻️Last Refiled On:</span><span class="c2">'. date('l jS \of F Y H:i:s',$getreportedinfo['lastrefilled']) . '</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">🚚Order Status:</span><span class="c2"> '. $orderstatus .'</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">Tracking Page:</span><span class="c2"><a target="_BLANK" href="https://' . $domain .  '/track-my-order/' . $getreportedinfo['order_session'] . '" style="color:blue;    text-decoration: underline;">' . $getreportedinfo['order_session'] . '</a></span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">Ip Address:</span><span class="c2"> '. $getreportedinfo['ipaddress'] .'</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">Supplier Error:</span><span class="c2" style="color:Red"> '. $supplierError .'</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">Last Four:</span><span class="c2"> '. $getreportedinfo['lastfour'] .'</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                                <div class="info-line"><span class="c1">Refund Offers:</span><span class="c2"> '. $refundoffers .'</span><span class="c3"><button class="btn-copy">Copy</button></span></div>
                            </td>
                        </div>';


                        // reports Data history

                        $adminnotifsq = mysql_query("SELECT * FROM `admin_notifications` WHERE `orderid` = '{$getreportedinfo['id']}' OR `emailaddress` = '{$getreportedinfo['emailaddress']}'  AND brand ='{$getreportedinfo['brand']}' LIMIT 12");
		                $countAdminNotif = mysql_num_rows($adminnotifsq);
                        while ($adminnotifinfo = mysql_fetch_array($adminnotifsq)) {
                        
		                	if ($adminnotifinfo['done'] == '0') {
		                		$ifdelete = '😊 Waiting to be checked<br><input type="submit" name="deletereport" value="Delete Report">';
		                		$adminnotifcolor = 'background-color: #fff579;';
		                	} else {
		                		$ifdelete = '<br>✅ <span style="font-size: 12px;
    	                	font-style: italic;">Checked by Admin, ' . ago($adminnotifinfo['response']) . ', ' . date("d/m/Y H:i:s", $adminnotifinfo['response']) . ')</span>';
		                		$adminnotifcolor = 'background-color: #cfff9b;';
		                	}
                        
		                	if (!empty($adminnotifinfo['directions'])) $adminresponse = '<hr><div>Admin Directions:<br>' . $adminnotifinfo['directions'] . '</div>';
                        
		                	$reportnotifs .= '<div class="adminnotif" style="' . $adminnotifcolor . '">' . $adminnotifinfo['message'] . '<br><span style="font-size: 12px;
    	                	font-style: italic;"> (reported by ' . ucfirst($adminnotifinfo['admin_name']) . ' -  ' . ago($adminnotifinfo['added']) . ', ' . date("d/m/Y H:i:s", $adminnotifinfo['added']) . ')</span>
                        <hr>
    	                			' . $adminresponse . '
                        
		                			<form action="" method="POST">' . $fields . '
		                			<input type="hidden" name="deletereportid" value="' . $adminnotifinfo['id'] . '">
		                			' . $ifdelete . '
		                			</form></div>';
                        
		                	unset($ifdelete);
		                	unset($adminresponse);
		                	unset($adminnotifinfo);
		                	unset($adminnotifinfo['directions']);
		                }

    

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
                        <div style="gap:10px;align-items:center">
                            <span>🏢 Company: </span>
                            <div class="logo"><img src="/admin/assets/icons/'. $brandName .'.svg"></div>
                            <div><img onerror="this.style.display=\'none\'" src="/admin/assets/icons/'.(getSocialMediaSource($getreportedinfo['socialmedia'])).'-icon.svg" style ="max-width:25px;vertical-align:middle;"> '.getSocialMediaSource($getreportedinfo['socialmedia']).'</div>
                        </div>
                        <hr>
                        Customer Order ID:<br><a id="orderIdLink" target="_BLANK" href="'.$hrefurlcheck.'?orderid='.$info['orderid'].'">'.$info['orderid'].'</a><br><a target="_BLANK" href="/admin/check-user/?user='.$info['emailaddress'].'">'.$info['emailaddress'].'</a>
                        <br>
                        <hr>
                        Supplier fullfil ID:<br>'.$show.'<hr>';
                
                        if($getreportedinfo['packagetype'] != "freefollowers" && $getreportedinfo['packagetype'] != "freelikes"){
                            $articles .='<span>
                                            Payment ID:<br><a target="_BLANK" rel="noopener noreferrer" href="'.$paymenturl.$getreportedinfo['payment_id'].'">'.$getreportedinfo['payment_id'].'</a><br> 
                                            <hr>
                                        </span>';
                        }
        
                    $articles .=    '<button id="reportsHistoryBtn" class="btn btn2">View Report History ('. $countAdminNotif .')</button>          
                    </td>
            
                    <td class="reason-side">'.$info['message'].'<br><span class="time">('.$info['admin_name'].' - '.ago($info['added']).', '.date("d/m/Y H:i:s",$info['added']).')</span><br>
                    <form method="POST" action="?type=reported"><input type="hidden" name="reportdone" value="'.$info['id'].'">
                    <textarea class="reportmessage" name="directions" id="text" style="min-height:200px;"></textarea>
                    <div style="display: flex">
                        <input type="hidden" name="gotopage" value="'.$page.'">
                        <input type="submit" onclick="return confirm(\'Are you sure youve dealt with the report?\');" class="btn btn-primary color3" style="width:initial;" value="Report Done">
                        <button id="autoReplyBtn" type="button" class="btn btn2">Select Autoreply</button>
                    </div>
                    </form>
                    </td>
                <tr>
                ';
           
           
            unset($show);
            unset($reason);

            // for inputs action

            if(!empty($_GET['selectedOrderId'])){
                $info['orderid'] = addslashes($_GET['selectedOrderId']);
            }

            $inpDataQ = mysql_query("SELECT * FROM `orders` WHERE `id` = '{$info['orderid']}' limit 1");
            $inpData = mysql_fetch_array($inpDataQ);
            global $orderId;
            $orderId = $inpData['id'];
            global $brand;
            $brand = $inpData['brand'];
            global $ordersession;
            $ordersession = $inpData['order_session'];
            global $refundAmnt;
            $refundAmnt = $inpData['refundamount'];
            global $username;
            $username = $inpData['igusername'];

            
            // get summary
            $getsummaryinfoq = mysql_query("SELECT * FROM `orders` WHERE `emailaddress` = '{$info['emailaddress']}' ORDER BY id DESC limit 10");
            $summaryresults = "";
            $countOrders = mysql_num_rows($getsummaryinfoq);
            if($countOrders > 0){
                $noOrderCallJs = 'toggleCollapse(2)';
                $noOrderListCss = '';
            }else{
                $noOrderListCss = 'display:none';
                $noOrderCallJs = '';

            }
            while($getsummaryinfo = mysql_fetch_array($getsummaryinfoq)){

                $domain = getBrandSelectedDomain($getsummaryinfo['brand']);

             

                $domain = !empty($domain) ? $domain : "superviral.io";
                $getsummaryinfo['supplier_errors'] = !empty($getsummaryinfo['supplier_errors']) ? $getsummaryinfo['supplier_errors'] : "N/A";
                if ($info['fulfilled'] == '0') {

                    $amounto = $getsummaryinfo['amount'];
        
                    if ($amounto >= 1 && $amounto <= 150) {
                        $approx = '9-10 hours';
                    }
                    if ($amounto >= 151 && $amounto <= 250) {
                        $approx = '12-13 hours';
                    }
                    if ($amounto >= 251 && $amounto <= 380) {
                        $approx = '14-15 hours';
                    }
                    if ($amounto >= 500 && $amounto <= 999) {
                        $approx = '14-15 hours';
                    }
                    if ($amounto >= 1000 && $amounto <= 1500) {
                        $approx = '24-28 hours';
                    }
                    if ($amounto >= 2500 && $amounto <= 3750) {
                        $approx = '27-35 hours';
                    }
                    if ($amounto >= 5000 && $amounto <= 8000) {
                        $approx = '38-48 hours';
                    }
        
                    if (!empty($approx)) $approx1 = '(will take around ' . $approx . ')';
        
                    $orderstatus = '<font color="orange">In progress ' . $approx1 . '</font>';
                    $arstatus = 'In progress ' ;
                    $artime = ' Please provide up to ' . $approx . ' for your order to be delivered. ';
                    $statusColor = 'orange';
                } else {
                    $orderstatus = '<font color="green">Completed: ' . date('l jS \of F Y H:i:s ', $getsummaryinfo['fulfilled']) . '</font>';
                    $arstatus = 'Completed';
                    $statusColor = 'green';

                }

                $fulfills = explode(' ', trim($getsummaryinfo['fulfill_id']));

                foreach ($fulfills as $fulfillorder) {
        
                    if (empty($fulfillorder)) continue;
                    $thisorderstatus .= '<a target="_BLANK" rel="noopener noreferrer" href="' . $fulfillmentsite . '/orders?search=' . $fulfillorder . '">' . $fulfillorder . '</a><br>';
                }
                $supplierfulfillidtd = $thisorderstatus;
                $price = sprintf('%.2f', $getsummaryinfo['price'] / 100);

                if ($getsummaryinfo['refund'] == '1') {
                    $refundcolor = 'orange';
                    $refunded = '(refund in progress)';
                } else {
                    $refundcolor = 'grey';
                    $refunded ="";
                }
                if ($getsummaryinfo['refund'] == '2') $refunded = '(refunded)';

                $findordercostq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = '{$getsummaryinfo['packagetype']}'  AND brand ='{$getsummaryinfo['brand']}' LIMIT 1");
		        $findordercostinfo = mysql_fetch_array($findordercostq);

		        $saleamount = $price;
		        $salecost = $findordercostinfo['perone'] * ($getsummaryinfo['amount']);

		        $saleamount = round(0.58 * ($saleamount - $salecost), 2);

                if ($getsummaryinfo['packagetype'] == 'followers') {
                    $searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'likes'  AND brand ='{$getsummaryinfo['brand']}' LIMIT 1");
                    $refundpackageinfo = mysql_fetch_array($searchpackageq);
                    $refundoffertype = 'likes';
                    $ardestination = 'posts';
                }
        
                if ($getsummaryinfo['packagetype'] == 'likes') {
                    $searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'followers'  AND brand ='{$getsummaryinfo['brand']}' LIMIT 1");
                    $refundpackageinfo = mysql_fetch_array($searchpackageq);
                    $refundoffertype = 'followers';
                    $ardestination = 'profile';
                }
        
                if ($getsummaryinfo['packagetype'] == 'views') {
                    $searchpackageq = mysql_query("SELECT `type`,`perone` FROM `packages` WHERE `type` = 'likes'  AND brand ='{$getsummaryinfo['brand']}' LIMIT 1");
                    $refundpackageinfo = mysql_fetch_array($searchpackageq);
                    $refundoffertype = 'likes';
                    $ardestination = 'posts';
                }
        
                if (!empty($refundpackageinfo['perone'])) {
                    $refundoffer1 = round($saleamount / $refundpackageinfo['perone']);
                }
        
                $refundoffer2 = round($refundoffer1 * $refundpackageinfo['perone'], 2);
               
                $brandSource = getBrandSelectedSource($getsummaryinfo['socialmedia']);
		        $UserName = $getsummaryinfo['igusername'];
		        if($getsummaryinfo['socialmedia'] == 'tt') $sourceURL = "https://$brandSource/@$UserName/";
		        else $sourceURL = "https://$brandSource/$UserName/";
                $keyword = getSocialMediaSource($getsummaryinfo['socialmedia']);

                $data = array("id" => $getsummaryinfo['id'], 
                "added"=> $getsummaryinfo['added'], 
                "lastrefilled" =>$getsummaryinfo['lastrefilled'],
                "orderstatus" =>$arstatus,
                "statusColor" =>$statusColor,
                "tracking_ordersession" => $getsummaryinfo['order_session'],
                "tracking_domain" => $domain,
                "ipaddress" =>  $getsummaryinfo['ipaddress'],
                "supplier_errors" =>  $getsummaryinfo['supplier_errors'],
                "lastfour" =>  $getsummaryinfo['lastfour'],
                "brand" =>  $getsummaryinfo['brand'],
                "username" =>  $getsummaryinfo['igusername'],
                "refundamount" => $getsummaryinfo['refundamount'],
                "brandname" => $brandName,
                "emailaddress" => $getsummaryinfo['emailaddress'],
                "amount" => $getsummaryinfo['amount'],
                "packagetype" => $getsummaryinfo['packagetype'],
                "price" => $price,
                "refunded" => $refunded,
                "contactnumber" => $getsummaryinfo['contactnumber'],
                "refundoffer1" => $refundoffer1,
                "refundoffertype" => $refundoffertype,
                "refundoffer2" => $refundoffer2,
                "sourceURL" => $sourceURL,
                "keywordSocialMedia" => $keyword,
                "account_id" => $getsummaryinfo['account_id'],
                "country" => $getsummaryinfo['country']
                );
                $summaryresults .= '<tr id="tr'. $getsummaryinfo['id'] .'">
				<td>' .  $getsummaryinfo['id']. '</td>
				<td>' . date('l jS \of F Y H:i:s ', $getsummaryinfo['added']) . '</td>
				<td>' . $orderstatus . '</td>
				<td>' . $supplierfulfillidtd . '</td>
				<td><button class="btn btn-primary color3" id="btnViewDetails'. $getsummaryinfo['id'] .'" onclick= "populateOrderData('. $getsummaryinfo['id'] .')">View Details</button></td>
                <input type="hidden" id="orderData'. $getsummaryinfo['id'] .'" value='. json_encode($data) .'>
				</tr>
                ';

               

                unset($thisorderstatus);
                unset($supplierfulfillidtd);
                unset($orderstatus);
            }
           
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

            
$query = mysql_query("SELECT * FROM `email_autoreplies` WHERE `page` = 'reports'");
$moreautoreplies = "";

while ($autorepliesinfo = mysql_fetch_array($query)) {

    $autorepliesinfo['autoreply'] = str_replace('<br />', "\r\n", $autorepliesinfo['autoreply']);

    $thisautoreply = '<div class="txtSize autoReplySec autoreplyselect ">
                    <a class="fontBold ">' . $autorepliesinfo['title'] . '</a><br>
                    <textarea id="autoReply' . $autorepliesinfo['id'] . '" style="display:none;" class= "searchBox">Hi there,

' . $autorepliesinfo['autoreply'] . '
</textarea>
                    <div>
                        <button class="btn3" onclick="populateAutoReply(' . $autorepliesinfo['id'] . ')" style="width:170px;margin-top:10px">Add this
                            auto reply</button>
                    </div>
                </div>';

    // if ($autorepliesinfo['showdefault'] == '1') {

    //     $mainautoreplies .= $thisautoreply;
    // } else {


        $moreautoreplies .= $thisautoreply;
    // }
}

}

$tpl = str_replace('{message}',$message,$tpl);
$tpl = str_replace('{articles}',$articles,$tpl);
$tpl = str_replace('{autojs}',$autojs,$tpl);
$tpl = str_replace('{summaryresults}',$summaryresults,$tpl);
$tpl = str_replace('{moreautoreplies}',$moreautoreplies,$tpl);
$tpl = str_replace('{presentOrderId}',$orderId,$tpl);
$tpl = str_replace('{offerFreeFollower}',"/admin/free-followers/?id=$orderId&brand=$brand",$tpl);
$tpl = str_replace('{offerFreeLikes}',"/admin/free-likes/?id=$orderId&brand=$brand",$tpl);
$tpl = str_replace('{offerFreeAL}',"/admin/auto-likes/?id=$orderId&brand=$brand",$tpl);
$tpl = str_replace('{brand}',$brand,$tpl);
$tpl = str_replace('{ordersession}',$ordersession,$tpl);
$tpl = str_replace('{refundAmnt}',$refundAmnt,$tpl);
$tpl = str_replace('{username}',$username,$tpl);
$tpl = str_replace('{firstOrderLoad}',$firstOrderLoad,$tpl);
$tpl = str_replace('{countOrders}',$countOrders,$tpl);
$tpl = str_replace('{nofirstOrderCss}',$nofirstOrderCss,$tpl);
$tpl = str_replace('{noOrderCallJs}',$noOrderCallJs,$tpl);
$tpl = str_replace('{noOrderListCss}',$noOrderListCss,$tpl);
$tpl = str_replace('{reportnotifs}',$reportnotifs,$tpl);
$tpl = str_replace('{cssPaymentId}',$cssPaymentId,$tpl);


output($tpl, $options);
