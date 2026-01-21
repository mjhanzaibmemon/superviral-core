<?php


include('adminheader.php');

date_default_timezone_set('Europe/London');


header('Cache-Control: no-transform');  

function ago($time)
{$periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");
   $now = time();
       $difference     = $now - $time;
       $tense         = 'ago';
   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }
   $difference = round($difference);
   if($difference != 1) {
       $periods[$j].= "s";
   }   return "$difference $periods[$j] ago";}

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

if(empty($type))header('Location: https://superviral.io/admin/orders.php?type=missing');

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
$now = time();

$tpl = str_replace('Unsubscribe','',$tpl);

$tpl = str_replace('{subject}',$subject,$tpl);

include('../crons/emailer.php');
emailnow($to,'Superviral','support@superviral.io',$subject,$tpl);

//////////////////////////////////////////////




    $respondedby = $_SESSION['admin_user'];
    $response = time();
	$directions = nl2br($directions);

 $adminnotificationupdate =	mysql_query("UPDATE `admin_notifications` SET `directions` = '$directions',`done` = '1',`response` = '$response',`respondedby` = '$respondedby' WHERE `id` = '$reportdone' LIMIT 1");
   $updateemailqueue = mysql_query("UPDATE `email_queue` SET `markDone` = '0' WHERE `from` = '{$findreportinfo['emailaddress']}' LIMIT 1");

if(!$adminnotificationupdate)die('Contact Rabban with Error 1929');
if(!$updateemailqueue)die('Contact Rabban with Error 3929');


    if(!empty($gotopage))header('Location: https://superviral.io/admin/orders.php?type=reported&page='.$gotopage);

}

if($type=='missing'){$q = mysql_query("SELECT * FROM `orders` WHERE `id` > '73663' AND `fulfill_id` = '' AND `defect` = '0' AND `refund` = '0' AND `disputed` = '0' AND `fulfill_attempt` > '5' ORDER BY `packagetype` ASC LIMIT 100");}


/*if($type=='missing'){$q = mysql_query("SELECT * FROM `orders` WHERE `id` > '73663' AND `fulfill_id` = '' AND `defect` = '0' AND `refund` = '0' AND `disputed` = '0' ORDER BY `emailaddress` ASC LIMIT 170");}/// TEMPORARY 9/3/2021 ONLY and adjust email to notify customers of duplicate orders
*/

if($type=='all'){$q = mysql_query("SELECT * FROM `orders` WHERE `id` > '73663' ORDER BY `emailaddress` ASC LIMIT 30");}

//REPORTED Section queries
if($type=='reported'){


         if (isset($_GET['page'])) {$page = addslashes($_GET['page']);} else {$page = 1;}
         if($page < 1)$page = 1;

          $no_of_records_per_page = 1;
        $offset = ($page-1) * $no_of_records_per_page;

        $q = mysql_query("SELECT * FROM `admin_notifications` WHERE `done` = '0' ORDER BY `difficulty` LIMIT $offset, $no_of_records_per_page");


        $q2 = mysql_query("SELECT * FROM `admin_notifications` WHERE `done` = '0' ORDER BY `difficulty` DESC");
        $totalleft = mysql_num_rows($q2).' Reports Remaining';

}

if($type=='defect'){

        if(!empty($search)){

        $q = mysql_query("SELECT * FROM `orders` WHERE `id` LIKE '%$search%' AND `refund` = '0' ORDER BY `id` ASC LIMIT 1");

        }else{

            $q = mysql_query("SELECT * FROM `orders` WHERE `defect` = '1' AND `refund` = '0' ORDER BY `id` ASC LIMIT 1");
        }
}

$theid = $_GET['theid'];

if(!empty($theid)){$styles = '.first'.$theid.',.second'.$theid.'{background-color:#e9ffe9;}';}

if($_GET['message']=='email1')$message = '<div class="emailsuccess">Private IG account email: Sent</div>';
if($_GET['message']=='updatetrue')$message = '<div class="emailsuccess">Order '.$theid.' successfully updated.</div>';
if($_GET['message']=='updatetrue2')$message = '<div class="emailsuccess">Resubmitted new order for Order '.$theid.'. Updated successfully.</div>';

$i = 0;

while($info = mysql_fetch_array($q)){

////////////////// IF SHOW POSTS INSTEAD OF USERNAME


            //if($info['amount'] >= 5000 && $info['amount'] <= 8000)$fraudwarning = '<div style="padding: 5px;background-color: orange;margin-bottom: 15px;">⏸️ Fraud warning: check if this order is legitimate.</div>';

            if(empty($info['chooseposts'])){                
                if($info['packagetype']=='likes'||$info['packagetype']=='views'){

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

                            $theupdatequery1 = ' Update query: "'.$theupdatequery.'" ';
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

                if($info['packagetype']=='freefollowers'){$freefollowers='1';}


                if (strpos($info['payment_id'], 'pi_') !== false)$payment_id = '<a target="_BLANK" rel="noopener noreferrer" href="https://dashboard.stripe.com/payments/'.$info['payment_id'].'">'.$info['payment_id'].'</a>';

                if (strpos($info['payment_id'], '-') !== false)$payment_id = '<a target="_BLANK" rel="noopener noreferrer" href="https://my.cardinity.com/payment/show/'.$info['payment_id'].'">'.$info['payment_id'].'</a>';

$i++;


               $getsupplieridq = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1");
               $sserviceid = mysql_fetch_array($getsupplieridq);
               $supplierserviceid = $sserviceid['jap1'];

            	$articles .= '<tr>

            					<td class="first'.$info['id'].'">'.$fraudwarning.'<b> '.$i.'. <a target="_BLANK" href="https://superviral.io/admin/check-user.php?orderid='.$info['id'].'#order'.$info['id'].'">'.$info['id'].'</a> - '.$show.'</b><br>£'.$info['price'].' - '.$info['amount'].' '.$info['packagetype'].'<br>'.date('l jS \of F Y H:i:s ', $info['added']).'<br>
                                <a target="_BLANK" href="https://superviral.io/admin/check-user.php?user='.$info['emailaddress'].'">'.$info['emailaddress'].'</a>
                                <br>
                                '.$payment_id.'<br>


            						<form action="ordermake'.$freefollowers.'.php" method="POST">
                                    <input type="hidden" name="update" value="save">
                                    <input type="hidden" name="pagefrom" value="'.$type.'">
            						<input type="hidden" name="id" value="'.$info['id'].'">
            						<input type="hidden" name="ordersession" value="'.$info['order_session'].'">
            						<input type="submit" onclick="return confirm(\'Are you sure you want to make this order?\');" class="btn color3" style="width:150px;float:left;" value="Make Order"></form>


                                    <form method="POST" action="ordersupdate.php">
                    <input type="hidden" name="pagefrom" value="'.$type.'">
                    <input type="hidden" name="update" value="ignore">
                    <input type="hidden" name="id" value="'.$info['id'].'">
                    <input type="submit" onclick="return confirm(\'Are you sure you want to ignore this order?\');" class="btn btn3 report copy-buttonn nlbtn" value="Ignore Defect" style="float:right;">
                    </form>


                                    </td>

            					<td class="second'.$info['id'].'">
                                 <div style="    padding: 10px;    background: #fcffc6;    margin-top: 10px;">Supplier Service ID: '.$supplierserviceid.'</div>
                                <form method="POST" action="ordersupdate.php">
                                <input type="hidden" name="pagefrom" value="'.$type.'">
                                <input type="hidden" name="update" value="save">
                                <input type="hidden" name="id" value="'.$info['id'].'">
                                <input class="input" name="orderid" value="'.$info['fulfill_id'].'">
                                <input type="submit" class="btn color3" value="SAVE"  style="width:100px;margin-top: 10px!important;">
                                </form>



                                   



                                </td>

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
    <b><a target="_BLANK" href="https://superviral.io/admin/check-user.php?orderid='.$info['id'].'#order'.$info['id'].'">'.$info['id'].'</a> - '.$show.'</b><br>£'.$info['price'].' - '.$info['amount'].' '.$info['packagetype'].'<br>'.date('l jS \of F Y H:i:s ', $info['added']).'
    </div>

    <div class="sdiv">
					'.$thisorderstatus.'
    </div>

    <div class="sdiv">
                        <form id="makeorder" action="ordermake.php" method="POST">
                        <input type="hidden" name="update" value="save">
                        <input type="hidden" name="auto" value="'.$auto.'">
                        <input type="hidden" name="pagefrom" value="'.$type.'">
                        <input type="hidden" name="id" value="'.$info['id'].'">
                        <input type="hidden" name="ordersession" value="'.$info['order_session'].'">
                        <input type="submit" onclick="return confirm(\'Are you sure you want to create a new order?\');" class="btn color3" style="width:150px;" value="Make Order"></form>
    </div>

    <div class="sdiv">
					<form method="POST" action="ordersupdate.php">
                    <input type="hidden" name="pagefrom" value="'.$type.'">
                    <input type="hidden" name="update" value="ignore">
                    <input type="hidden" name="id" value="'.$info['id'].'">
                    <input type="hidden" class="input" name="orderid" value="'.$info['fulfill_id'].'">
                    <input type="submit" class="btn color3" value="Ignore Defect">
                    </form>
					
     </div>


    <div class="sdiv">
                    <form method="POST" action="ordersupdate.php" style="display:none;">
                    <input type="hidden" name="pagefrom" value="'.$type.'">
                    <input type="hidden" name="update" value="save">
                    <input type="hidden" name="id" value="'.$info['id'].'">
                    <input class="input" name="orderid" value="'.$info['fulfill_id'].'">
                    <input type="submit" class="btn color3" value="SAVE">
                    </form>

                    <form method="POST" action="emailissue.php">
                    <input type="hidden" name="pagefrom" value="'.$type.'">
                    <input type="hidden" name="id" value="'.$info['id'].'">
                    <input type="hidden" name="ordersession" value="'.$info['order_session'].'">
                    <input type="submit" class="btn color3" value="Send Email for private profile">
                    </form>

    </div>

    <div class="sdiv">
    

                    <form method="POST" action="emailissue2.php">
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

if($type=='reported'){//REPORTED

    $getreportedinfoq = mysql_query("SELECT * FROM `orders` WHERE `id` = '{$info['orderid']}' LIMIT 1");
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

        $hrefurlcheck = 'https://superviral.io/admin/check-al.php';

        

   }else{

        $hrefurlcheck = 'https://superviral.io/admin/check-user.php';

   }



	$articles .='
    <tr>
	<td>Customer Order ID: <a target="_BLANK" href="'.$hrefurlcheck.'?orderid='.$info['orderid'].'">'.$info['orderid'].'</a><br><a target="_BLANK" href="https://superviral.io/admin/check-user.php?user='.$info['emailaddress'].'">'.$info['emailaddress'].'</a>

    <hr>
    Supplier fullfil ID:<br>'.$show.'<hr>
    Payment ID:<br><a target="_BLANK" rel="noopener noreferrer" href="'.$paymenturl.$getreportedinfo['payment_id'].'">'.$getreportedinfo['payment_id'].'</a><br>

    </td>
	
    <td>'.$info['message'].'<br><span style="font-size:15px;">('.$info['admin_name'].' - '.ago($info['added']).', '.date("d/m/Y H:i:s",$info['added']).')</span><br>
	
    <form method="POST" action="?type=reported"><input type="hidden" name="reportdone" value="'.$info['id'].'">
	<textarea class="reportmessage" name="directions" id="text" style="min-height:200px;"></textarea>
    <input type="hidden" name="gotopage" value="'.$page.'">
	<input type="submit" onclick="return confirm(\'Are you sure youve dealt with the report?\');" class="btn color3" style="width:initial;" value="Report Done">
    </form>

   
    </td><tr>';


unset($show);
unset($reason);

}

}

/////TABLES

if($type=='missing'){


$articles = '<table class="articles">

				<tr>

					<td>Missing Order</td>
					<td>Supplier ID (from Supplier)</td>

				</tr>

				'.$articles.'

			</table>';
}

if($type=='defect'){


$articles = '

<div style="margin-bottom:30px;"><form method="GET" action="https://superviral.io/admin/orders.php?&type=defect">
<input type="hidden" name="type" value="defect"><input autocomplete="off" name="search" value="'.$search.'" class="input" placeholder="Search by Order ID"></form></div>


				'.$articles.'
';
}

if($type=='reported'){//REPORTED TABLE


echo "<!-- "."SELECT * FROM `admin_notifications` WHERE `done` = '0' ORDER BY `difficulty` LIMIT $offset, $no_of_records_per_page"." -->";

$articles = '<div class="box23" style="color: grey;
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

?>
<!DOCTYPE html>
<head>
<title>Orders</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">

<style type="text/css">

.articles a{text-decoration: underline;color:blue;}

.box23{margin: 66px auto;
    width: 950px;
    background: #fff;
    border-radius: 5px;text-align:left;padding:15px;}

h1{text-align: left;max-width:100%;}

.label{margin-top:35px;}

.container div input, .selectric, .input, .btn {padding: 13px;font-size: 14px;}

.btn{width:100px;text-align:center;}


.cke_reset_all{background:#f7f7f7!important;}

.articles{width:100%;}
.articles tr td{border-right:1px solid #ccc;border-bottom:1px solid #000;padding: 30px 10px;vertical-align: top;}
.articles tr:first-child td{background:#f1f1f1;font-weight:bold;padding:10px;}

.status{ font-weight: bold;
    height: 23px;
    width: 55px;
    padding: 5px;font-size:15px;text-align:center;border-radius:3px;}

    .btn{margin: 0!important;}


.adminmenu{display:inline-block;background-color:white;border-top:1px solid #ccc;width:100%;}
.adminmenu a{float:left;padding:15px;}

 .reportmessage{
    width: 100%;
    height: 120px;box-sizing:border-box;
    margin: 0px;
    margin-bottom: 20px;
    padding:10px;font-family:'Open Sans';

    overflow: hidden;
    outline: none;
    resize:none;
}

   .btn{width: initial;}

   .defectorder{}

   .defectorder a{text-decoration: underline;color:blue;}

   .defectorder .sdiv{    margin-bottom: 15px;
    padding: 9px;
    background-color: #f5f7fe;
    border-radius: 5px;}

    .nlbtn{    float: left;
    margin-right: 20px!important;
    width: 176px!important;
    margin-bottom: 10px!important;}

<?=$styles?>

</style>
</head>

	<body onload="init();">

		<?=$header?>

		<div class="adminmenu" style="display:none;">
			<a href="?type=">Missing/approval orders</a>
			<a href="?type=reported">Reported orders</a>
			<!--<a href="?type=all">All orders</a>-->
		</div>

        <div style="overflow:auto;">
		<div class="box23">

			<?=$message?>

			<?=$articles?>

		</div>
        </div>

        <script type="text/javascript">

        <?=$autojs?>


                var observe;
        if (window.attachEvent) {
            observe = function (element, event, handler) {
                element.attachEvent('on'+event, handler);
            };
        }
        else {
            observe = function (element, event, handler) {
                element.addEventListener(event, handler, false);
            };
        }
        function init () {
            var text = document.getElementById('text');
            function resize () {
                text.style.height = 'auto';
                text.style.height = text.scrollHeight+'px';
            }
            /* 0-timeout to get the already changed text */
            function delayedResize () {
                window.setTimeout(resize, 0);
            }
            observe(text, 'change',  resize);
            observe(text, 'cut',     delayedResize);
            observe(text, 'paste',   delayedResize);
            observe(text, 'drop',    delayedResize);
            observe(text, 'keydown', delayedResize);

            text.focus();
            text.select();
            resize();
        }

        </script>


	</body>
</html>