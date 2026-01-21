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

if(empty($type))header('Location: /admin/missing-orders/?type=missing');
$now = time();
if(!empty($reportdone)){

    ////NOTIFY THE USER HERE VIA EMAIL

$findreportinfoq = mysql_query("SELECT * FROM `admin_notifications` WHERE `id` = '$reportdone' LIMIT 1");
$findreportinfo = mysql_fetch_array($findreportinfoq);

$to = $findreportinfo['emailaddress'];
$subject = 'Superviral: Update on your issue';

////////////////////////////////////////////// THE EMAIL GOES HERE




$emailbody = '<p>Hi there,</p>
<br>
<p>This is Helen from Superviral\'s management team to just notify you that I\'ve received your issue and I\'ve given additional support to James to help resolve this issue as soon as possible.</p>
<br>
<p>Speaking to James, he\'s advised me that he will respond within the next 19-24 hours and is available to respond to your issue.</p>
<br>
<p>Other Superviral teams usually get involved with an issue when a customer service rep, requires the assistance of more than one team that specialises in that issue.</p>
<br>
<p>For example, when there\'s a technical issue, our technicians would get involved to diagnose the issue as they\'re trained to deal with those type of issues.</p>
<br>
<p>If there is anything else you need, please do not hesitate to get in touch with my colleague James regarding the issue. He will respond within the next 19-24 hours.</p>
<br>
<p>Thank you for your patience.</p>
<br>
<p>Kind regards,<br>
Superviral Management Team</p>
<br>
<p>160 City Road<br>
London<br>
EC1V 2NX<br>
United Kingdom</p>';

$tpl = file_get_contents('../emailtemplate/emailtemplate.html');
$tpl = str_replace('{body}',$emailbody,$tpl);


$tpl = str_replace('Unsubscribe','',$tpl);

$tpl = str_replace('{subject}',$subject,$tpl);

require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';
emailnow($to,'Superviral','support@superviral.io',$subject,$tpl);

//$bodyText = str_replace("'","\'", $tpl);
//email_stat_insert('Email Support', $to, $bodyText, $findreportinfo['brand']);
//////////////////////////////////////////////




    $respondedby = $_SESSION['first_name'];
    $response = time();
	$directions = nl2br($directions);
	mysql_query("UPDATE `admin_notifications` SET `directions` = '$directions',`done` = '1',`response` = '$response',`respondedby` = '$respondedby' WHERE `id` = '$reportdone' LIMIT 1");
    if(!empty($gotopage))header('Location: /admin/reports/?page='.$gotopage);

}

$ignoreall = addslashes($_POST['ignore_all']);

if(isset($ignoreall) && $ignoreall == "Ignore Defect All"){

    $updateorder = mysql_query("UPDATE `orders` SET `defect` = '5',`fulfilled` = '$now',`norefill` = '1' WHERE `id` > '73663' AND `fulfill_id` = '' AND `defect` = '0' AND `refund` = '0' AND `disputed` = '0' AND `fulfill_attempt` != '0' ORDER BY `packagetype` ASC LIMIT 100");
    header('Location: /admin/missing-orders/?type=missing&message=updateall');
    die;
}

$makeorderall = addslashes($_POST['make_order_all']);

if(isset($makeorderall) && $makeorderall == "Make Order All"){

    $query = mysql_query("UPDATE `orders` SET `fulfill_attempt` = '0', `lambda`='0', `next_fulfill_attempt` = '$now' WHERE `id` > '73663' AND `fulfill_id` = '' AND `defect` = '0' AND `refund` = '0' AND `disputed` = '0' AND `fulfill_attempt` != '0' ORDER BY `packagetype` ASC LIMIT 100");//RESTART THE ORDERS

    header('Location: /admin/missing-orders/?type=missing&message=updateorderall');
    die;
}

if($type=='missing'){$q = mysql_query("SELECT * FROM `orders` WHERE `id` > '73663' AND `fulfill_id` = '' AND `defect` = '0' AND `refund` = '0' AND `disputed` = '0' AND `fulfill_attempt` != '0' ORDER BY `packagetype` ASC LIMIT 100");}


/*if($type=='missing'){$q = mysql_query("SELECT * FROM `orders` WHERE `id` > '73663' AND `fulfill_id` = '' AND `defect` = '0' AND `refund` = '0' AND `disputed` = '0' ORDER BY `emailaddress` ASC LIMIT 170");}/// TEMPORARY 9/3/2021 ONLY and adjust email to notify customers of duplicate orders
*/

if($type=='all'){$q = mysql_query("SELECT * FROM `orders` WHERE `id` > '73663' AND `fulfill_attempt` != '0' ORDER BY `emailaddress` ASC LIMIT 30");}



if($type=='defect'){

        if(!empty($search)){

        $q = mysql_query("SELECT * FROM `orders` WHERE `id` LIKE '%$search%' AND `refund` = '0' ORDER BY `id` ASC LIMIT 1");

        }else{

            $q = mysql_query("SELECT * FROM `orders` WHERE `defect` = '1' AND `refund` = '0' ORDER BY `id` ASC LIMIT 1");
        }
}

$theid = $_GET['theid'];
$msg = $_GET['message'];

if(!empty($msg)){

    switch($msg){
        case '0':
            $note = "Order Not Found";
        break;
        case '1':
            $note = "Order $theid: successfully resubmitted to crons to be fulfilled.";
        break;
        case '2':
            $note = "Failed to resubmit order $theid: try again in sometime.";
        break;
        case 'updatetrue':
            $note = "Order $theid: successfully ignored";
        break;    
        case 'updateall':
            $note = "Ignored all successfully";
        break;   
        case 'updateorderall':
            $note = "Done successfully";
        break;   
        
    }
    $message = '<div class="emailsuccess">'. $note .'</div>';
}

$i = 0;

while($info = mysql_fetch_array($q)){

        ////////////////// IF SHOW POSTS INSTEAD OF USERNAME


            if($info['amount'] >= 5000 && $info['amount'] <= 8000)$fraudwarning = '<div style="padding: 5px;background-color: orange;margin-bottom: 15px;">⏸️ Fraud warning: check if this order is legitimate.</div>';

            if(empty($info['chooseposts'])){                
                if($info['packagetype']=='likes'||$info['packagetype']=='views'||$info['packagetype']=='comments'){

                $findchoosepostsq = mysql_query("SELECT * FROM `order_session_paid` WHERE `order_session` = '{$info['order_session']}' LIMIT 1");

                if(mysql_num_rows($findchoosepostsq)=='0')$findchoosepostsq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '{$info['order_session']}' LIMIT 1");


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

                            $theupdatequery1 = ' Posts were missing, re-updated from order session: "'.$theupdatequery.'" ';
                            $chooseposts = ' FOUND: ';
                            mysql_query("UPDATE `orders` SET `chooseposts` = '$theupdatequery' WHERE `id` = '{$info['id']}' LIMIT 1");
                        


                    }


                } 
            }


            ////////////////////

            $info['price'] = sprintf('%.2f', $info['price'] / 100);
                    
                    
                    
            if($type=='missing'){//MISSING ORDER NUMBERS
            
                
            
                            if(!empty($info['chooseposts'])){
                            
                            $thispost = $info['chooseposts'];
                            $thispost = explode(' ', $thispost);
                            
                            foreach($thispost as $thisposta){
                            
                                if(empty($thisposta))continue;
                            
                                $posts .= '<a target="_BLANK" rel="noopener noreferrer" href="'.$fulfillmentsite.'/orders?search='.$thisposta.'">'.$thisposta.'</a><br>';
                            
                            }
                        
                        
                        
                            $posts = $posts.$chooseposts.$theupdatequery1;
                        
                            $show = $posts;
                        
                            }else{
                            
                            $show = '<a target="_BLANK" rel="noopener noreferrer" href="'.$fulfillmentsite.'/orders?search='.$info['igusername'].'">'.$info['igusername'].'</a>';
                            
                            }
                        
                            //if($info['packagetype']=='freefollowers'){$freefollowers='1';}
                        
                        
                            if (strpos($info['payment_id'], 'pi_') !== false)$payment_id = '<a target="_BLANK" rel="noopener noreferrer" href="https://dashboard.stripe.com/payments/'.$info['payment_id'].'">'.$info['payment_id'].'</a>';
                            
                            if (strpos($info['payment_id'], '-') !== false)$payment_id = '<a target="_BLANK" rel="noopener noreferrer" href="https://my.cardinity.com/payment/show/'.$info['payment_id'].'">'.$info['payment_id'].'</a>';

                            if($info['supplier_errors']){$supplier_error = '<div style="color:red;">'.$info['supplier_errors'].'</div>';}

                            $attempt_date = dateDiff($info['next_fulfill_attempt']);

                            $i++;                          
                            
                        	$articles .= '<tr>
                            
                        					<td class="first'.$info['id'].'">
                                            '.$fraudwarning.'
                                            <b>'.$i.'. <a target="_BLANK" href="/admin/check-user/?orderid='.$info['id'].'#order'.$info['id'].'">'.$info['id'].'</a> - '.$show.'</b>
                                            <br>
                                            £'.$info['price'].' - '.$info['amount'].' '.$info['packagetype'].'
                                            <br>
                                            '.date('l jS \of F Y H:i:s ', $info['added']).'
                                            <br>
                                            Attempts: '.$info['fulfill_attempt'].' / 7
                                            <br>
                                            '.$attempt_date.'
                                            '.$supplier_error.'
                                            <img style="max-width:25px;display:inline-block;" src="/admin/assets/icons/'.(getBrandSelectedName($info['brand'])).'-icon.svg">
                                            <img style="max-width:25px;display:inline-block;" src="/admin/assets/icons/'.(getSocialMediaSource($info['socialmedia'])).'-icon.svg"><br>
                                            <a target="_BLANK" href="/admin/check-user/?user='.$info['emailaddress'].'">'.$info['emailaddress'].'</a>
                                            <br>
                                            '.$payment_id.'
                                            <br>
                            
                        						<form action="/admin/api/ordermake.php" method="POST">
                                                <input type="hidden" name="update" value="save">
                                                <input type="hidden" name="pagefrom" value="'.$type.'">
                        						<input type="hidden" name="id" value="'.$info['id'].'">
                                                <input type="hidden" name="brand" value="'.$info['brand'].'">
                        						<input type="hidden" name="ordersession" value="'.$info['order_session'].'">
                        						<input type="submit" onclick="return confirm(\'Are you sure you want to make this order?\');" class="btn btn-primary color3" style="width:150px;float:left;" value="Make Order" fdprocessedid="joyncc"></form>
                            
                            
                                                <form method="POST" action="/admin/api/ordersupdate.php">
                                <input type="hidden" name="pagefrom" value="'.$type.'">
                                <input type="hidden" name="update" value="ignore">
                                <input type="hidden" name="id" value="'.$info['id'].'">
                                <input type="submit" onclick="return confirm(\'Are you sure you want to ignore this order?\');" class="btn btn3 report copy-buttonn nlbtn" value="Ignore Defect" style="float:right;">
                                </form>
                            
                            
                                                </td>
                            
                        					<td class="second'.$info['id'].'">
                                            <form method="POST" action="/admin/api/ordersupdate.php">
                                            <input type="hidden" name="pagefrom" value="'.$type.'">
                                            <input type="hidden" name="update" value="save">
                                            <input type="hidden" name="id" value="'.$info['id'].'">
                                            <input class="input" name="orderid" value="'.$info['fulfill_id'].'">
                                            <input type="submit" class="btn btn-primary color3" value="SAVE" style="width:100px;margin-top: 10px!important;" fdprocessedid="vtvt59">
                                            </form></td>
                            
                        				<tr>';
                            
                            unset($posts);
                            unset($posts1);
                            unset($posts2);
                            unset($chooseposts);
                            unset($choosepostsql);
                            unset($theupdatequery);
                            unset($theupdatequery1);
                            unset($fraudwarning);
                            unset($freefollowers);
                        	unset($payment_id);
                            
                            
            }
            
            if($type=='defect'){//DEFECTIVE
            
            
                            if(!empty($info['chooseposts'])){
                            
                            $thispost = $info['chooseposts'];
                            $thispost = explode(' ', $thispost);
                            
                            foreach($thispost as $thisposta){
                            
                                if(empty($thisposta))continue;
                            
                                $posts .= '<a target="_BLANK" rel="noopener noreferrer" href="'.$fulfillmentsite.'/orders?search='.$thisposta.'">'.$thisposta.'</a><br>';
                            
                            }
                        
                        
                        
                            $posts = $posts.$chooseposts.$theupdatequery1;
                        
                            $show = $posts;
                        
                            }else{
                            
                            $show = '<a target="_BLANK" rel="noopener noreferrer" href="'.$fulfillmentsite.'/orders?search='.$info['igusername'].'">'.$info['igusername'].'</a>';
                            
                            }
                        
                        
                            $fulfills = explode(' ',trim($info['fulfill_id']));

                            $fulfillcount = count(array_filter($fulfills));
                            $balance = $api->multiStatus($fulfills);

                            $balance = json_decode(json_encode($balance), True);

                            foreach($balance as $key => $order){
                            
                            
                            
                                if($order['status']=='Completed')continue;
                                if($order['status']=='Partial'){$auto='pause';}
                                if($order['status']=='Pending'){$auto='pause';mysql_query("UPDATE `orders` SET `defect` = '0' WHERE `id` = '{$info['id']}' LIMIT 1");}
                                //if($order['status']=='Canceled'){$auto='resume';}
                            
                            //    if($order['status']=='Partial')$left = ' - '.($info['amount'] - $order['remains']).'/'.$info['amount'];
                                if($order['status']=='Partial')$left = ' - '.$order['remains'].'/'.$info['amount'];
                            
                            	$thisorderstatus .= '<a target="_BLANK" rel="noopener noreferrer" href="'.$fulfillmentsite.'/orders?search='.$key.'">'.$key.'</a> - '.$order['status'].$left.'<br>';
                            
                                unset($left);
            
            
                            }

                    //RESUME AUTO as no issues have come up
                    if($auto=='resume'){$autojs = 'setTimeout(() => {  document.getElementById("makeorder").submit(); }, 1000);';}
                                
                    $notesadminq = mysql_query("SELECT * FROM `admin_order_notes` WHERE `orderid` ='{$info['id']}'");
                    if(mysql_num_rows($notesadminq)!=='0'){
                    
                    while($notesinfo = mysql_fetch_array($notesadminq)){
                    
                        $notesadmin .= '<div style="padding:5px;border-bottom:1px dashed grey">'.ago($notesinfo['added']).' - '.$notesinfo['notes'].'</div>';
                    }
                    
                    $notesadmin = '<div class="sdiv">'.$notesadmin.'</div>';
                    
                    }
            
            
            	$articles .= '<div class="defectorder">
            
                <div class="sdiv">
                <b><a target="_BLANK" href="/admin/check-user/?orderid='.$info['id'].'#order'.$info['id'].'">'.$info['id'].'</a> - '.$show.'</b><br>£'.$info['price'].' - '.$info['amount'].' '.$info['packagetype'].'<br>'.date('l jS \of F Y H:i:s ', $info['added']).'
                </div>
            
                <div class="sdiv">
            					'.$thisorderstatus.'
                </div>
            
                <div class="sdiv">
                                    <form id="makeorder" action="/admin/api/ordermake.php" method="POST">
                                    <input type="hidden" name="update" value="save">
                                    <input type="hidden" name="auto" value="'.$auto.'">
                                    <input type="hidden" name="pagefrom" value="'.$type.'">
                                    <input type="hidden" name="id" value="'.$info['id'].'">
                                    <input type="hidden" name="ordersession" value="'.$info['order_session'].'">
                                    <input type="submit" onclick="return confirm(\'Are you sure you want to create a new order?\');" class="btn color3" style="width:150px;" value="Make Order"></form>
                </div>
            
                <div class="sdiv">
            					<form method="POST" action="/admin/api/ordersupdate.php">
                                <input type="hidden" name="pagefrom" value="'.$type.'">
                                <input type="hidden" name="update" value="ignore">
                                <input type="hidden" name="id" value="'.$info['id'].'">
                                <input type="hidden" class="input" name="orderid" value="'.$info['fulfill_id'].'">
                                <input type="submit" class="btn color3" value="Ignore Defect">
                                </form>
            
                 </div>
            
            
                <div class="sdiv">
                                <form method="POST" action="/admin/api/ordersupdate.php" style="display:none;">
                                <input type="hidden" name="pagefrom" value="'.$type.'">
                                <input type="hidden" name="update" value="save">
                                <input type="hidden" name="id" value="'.$info['id'].'">
                                <input class="input" name="orderid" value="'.$info['fulfill_id'].'">
                                <input type="submit" class="btn color3" value="SAVE">
                                </form>
            
                                <form method="POST" action="/admin/api/emailissue.php">
                                <input type="hidden" name="pagefrom" value="'.$type.'">
                                <input type="hidden" name="id" value="'.$info['id'].'">
                                <input type="hidden" name="ordersession" value="'.$info['order_session'].'">
                                <input type="submit" class="btn color3" value="Send Email for private profile">
                                </form>
            
                </div>
            
                <div class="sdiv">
                
            
                                <form method="POST" action="/admin/api/emailissue2.php">
                                <input type="hidden" name="pagefrom" value="'.$type.'">
                                <input type="hidden" name="id" value="'.$info['id'].'">
                                <input type="hidden" name="ordersession" value="'.$info['order_session'].'">
                                <input type="submit" class="btn color3" value="Send Email for non-working page">
                                </form>
            
            
                </div>
            
                '.$notesadmin.'
            
            
                            </div>';
            
            	unset($posts);
            	unset($thisorderstatus);
            
            }

}

/////TABLES

if($type=='missing'){


$articles = '<table class="articles">
                <form method ="post">
                    <input type="submit" onclick="return confirm(\'Are you sure you want to ignore all order?\');" class="btn btn3 report copy-buttonn nlbtn" value="Ignore Defect All" name="ignore_all" style="float:right;">
                    <input type="submit" onclick="return confirm(\'Are you sure you want to make all order?\');" class="btn btn3 report copy-buttonn nlbtn" value="Make Order All" name="make_order_all" style="float:right;">
                </form>
				<tr>

					<td>Missing Order</td>
					<td>Order ID</td>

				</tr>

				'.$articles.'

			</table>';
}

if($type=='defect'){


$articles = '

<div style="margin-bottom:30px;"><form method="GET" action="/admin/defectorders/?type=defect">
<input type="hidden" name="type" value="defect"><input autocomplete="off" name="search" value="'.$search.'" class="input" placeholder="Search by Order ID"></form></div>


				'.$articles.'
';
}


$tpl = str_replace('{message}',$message,$tpl);
$tpl = str_replace('{articles}',$articles,$tpl);
$tpl = str_replace('{autojs}',$autojs,$tpl);

output($tpl, $options);


function dateDiff($date)
{
    $now = date("Y-m-d H:i:s");
    $date = date("Y-m-d H:i:s",$date);
    
    $datetime1 = date_create($date);
    $datetime2 = date_create($now);
    $interval = date_diff($datetime1, $datetime2);

    $isFuture = $datetime1 > $datetime2; // True if date is in the future

    $min = $interval->format('%i');
    $sec = $interval->format('%s');
    $hour = $interval->format('%h');
    $day = $interval->format('%d');
    $mon = $interval->format('%m');
    $year = $interval->format('%y');

    $prefix = $isFuture ? "Next attempt in " : "Last attempt ";
    $suffix = $isFuture ? "" : " ago";

    if ($interval->format('%i%h%d%m%y') == "00000") {
        return $prefix . $sec . " seconds" . $suffix;
    } else if ($interval->format('%h%d%m%y') == "0000") {
        return $prefix . $min . " minutes" . $suffix;
    } else if ($interval->format('%d%m%y') == "000") {
        return $prefix . $hour . " hours" . $suffix;
    } else if ($interval->format('%m%y') == "00") {
        return $prefix . $day . " days" . $suffix;
    } else if ($interval->format('%y') == "0") {
        return $prefix . $mon . " months" . $suffix;
    } else {
        return $prefix . $year . " years" . $suffix;
    }
}