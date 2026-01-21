<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');


// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}


date_default_timezone_set('Europe/London');

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

   ///////////////////////////// from email ////////////////////////////

 ////////////////////////////////////////////////// do for restart order ////////////////////////

$id = addslashes($_GET['id']);
$uid = addslashes($_GET['uid']);
$order_id = addslashes($_POST['order_id']);
$refill_session_id = addslashes($_POST['refill_session_id']);
$refill = addslashes($_POST['refill']);










/////////////////////////////////////////////////////////////


   ///////////////////////////////////////////////////////////////////



if(!empty($refill)){

  if((empty($order_id))||(empty($refill_session_id)))die('Error 40392: Please contact support team with this error.');

  if($refill=='on'){$refillsqlchange = "0";}
  if($refill=='off'){$refillsqlchange = "1";}

  mysql_query("UPDATE `orders` SET `norefill` = '$refillsqlchange' WHERE `id` = '$order_id' AND `order_session` = '$refill_session_id' AND `brand`='sv' LIMIT 1");

}


if(empty($id)){include('track-my-order-main.php');die;}

//$tpl = file_get_contents('track-my-order.html');
$tpl = file_get_contents('track-my-order-3.html');

if(!empty($uid)){$uidwhere = "AND `id` = '$uid' ";}

$q = mysql_query("SELECT * FROM `orders` WHERE `brand`='sv' AND `order_session` = '$id' $uidwhere ORDER BY `id` DESC LIMIT 1");
if(mysql_num_rows($q)=='0'){
  die('No order found');
}

$info = mysql_fetch_array($q);

$packageinfoq = mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `id` = '{$info['packageid']}' LIMIT 1");
$packageinfo = mysql_fetch_array($packageinfoq);

if($packageinfo['premium']=='1'){$premium = ' premium';}else{$premium = '';}

$styleRestart = "";

if($info['defect'] != 0){

  $styleRestart = '<div class="tholder tinfoholder" align="center" >

                     <div class="cnwidth cnwidthtracking" style="text-align:left;font-size: 14px;">

                         <h2>Resume Order</h2>


                             <input type="submit" onclick="restartOrderPopup();" class="btn color4 dshadow" name="restartOrder" value="Resume your order">


                     </div>

                  </div>';




}

$orderSessionQ = mysql_query("SELECT * FROM `order_session` WHERE `brand`='sv' AND `order_session` = '{$info['order_session']}' LIMIT 1");
$orderSessionData = mysql_fetch_array($orderSessionQ);
// upsell follower check
$upsell_follower = $orderSessionData['upsell_all'];
$upsell_follower_htm = '';
if (!empty($upsell_follower)) {
		
				
  $upsellprice1 = explode('###', $upsell_follower);
  
  $upsellamount1 = $upsellprice1[0];

  $upsellprice1 = $upsellprice1[1];

  $upsell_follower_htm = ' and '. $upsellamount1 .' followers';

  $finalprice = $finalprice + $upsellprice1;


} 




if(($info['account_id']=='0') && (isset($_COOKIE['plus_id'])) && (isset($_COOKIE['plus_token']))){

//Check if cookie already exists and redirect to account home page
    
    $plus_id = $_COOKIE['plus_id'];
    $plus_token = $_COOKIE['plus_token'];

    // get logged in user with plus_id and plus_token cookie values
    $result = mysql_query("SELECT * FROM `accounts` WHERE `brand`='sv' AND `email_hash` = '$plus_id' AND `token_hash` = '$plus_token' LIMIT 1");
    $userinfo = mysql_fetch_array($result);
    $num_rows33 = mysql_num_rows($result);
    if($num_rows33 == 1){//match found meaning redirect
        $loggedin = true;
    //MYSQL QUERY "UPDATE orders with "
    //REFRESH THE PARENT FRAME SO THAT IT DOESNT COME UP


        if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222'){






        }else{


             mysql_query("UPDATE `orders` SET `account_id` = '{$userinfo['id']}' WHERE `id` = '{$info['id']}' LIMIT 1");

             $info['account_id'] = $userinfo['id'];


          
        }

    
    }



}
















$info['packagetype'] = str_replace('freefollowers','followers',$info['packagetype']);

if(empty($info['order_response'])){

//echo 'Added: '.$info['added'].'<br>';

$added = $info['added'];


$dif = time() - $added;


$time = $added + rand(0, 10);
$text = 'Superviral algorithm scanning @'.$info['igusername'].' account statistics age, usage, current followers and post engagement for best delivery method';
$duration = '0.'.rand(1,9);
$update1 = '~~~'.$time.'###'.$text.'###'.$duration;


$time = $added + rand(50, 200);
$algo = rand(40, 470);
$text = 'Using algorithm #'.$algo.' out of 477 to deliver the safest way to @'.$info['igusername'];
$duration = '0.'.rand(1,9);
$update2 = '~~~'.$time.'###'.$text.'###'.$duration;



$time = $added + rand(201, 320);
$text = 'Choosing '.$info['amount'].' followers out of 3.4 million super high quality followers to follow @'.$info['igusername'];
if($info['packagetype']=='likes')$text = 'Choosing '.$info['amount'].' of the highest quality accounts to like post(s) at @'.$info['igusername'];
if($info['packagetype']=='views')$text = 'Choosing '.$info['amount'].' of the highest quality accounts to watch video post(s) at @'.$info['igusername'];
$duration = '0.'.rand(1,9);
$update3 = '~~~'.$time.'###'.$text.'###'.$duration;


$time = $added + rand(321, 600);
$text = 'Sending Instagram '. $premium .' followers to @'.$info['igusername'].' with Superviral RapidDelivery™';
if($info['packagetype']=='likes')$text = 'Sending Instagram '. $premium .' likes to @'.$info['igusername'].' with Superviral RapidDelivery™';
if($info['packagetype']=='views')$text = 'Sending Instagram '. $premium .' views to @'.$info['igusername'].' with Superviral RapidDelivery™';
$duration = '0.'.rand(1,9);
$update4 = '~~~'.$time.'###'.$text.'###'.$duration;

$order_response = addslashes($update1.$update2.$update3.$update4);

if($info['socialmedia'] == 'tt'){
	$order_response = str_ireplace("Instagram", "Tiktok", $order_response);
}

mysql_query("UPDATE `orders` SET `order_response` = '$order_response' WHERE `id` = '{$info['id']}' LIMIT 1");

header('Location: https://superviral.io/'.$loclinkforward.'track-my-order/'.$id.'/'.$uid);
die;

}

  $i = 0;
  //TRACKING INFORMATION TABLE
  if(!empty($info['order_response_finish'])){$info['order_response'] = $info['order_response'].$info['order_response_finish'];}
  
  //SORT BY DATE AND TIME - the ordering
  $trackinghistory2 = explode('~~~', $info['order_response']);

  //Foreach
  foreach($trackinghistory2 as $trackingsinfo2){

    $trackingsinfo3 = explode('###',$trackingsinfo2);
    $trackingtime = $trackingsinfo3[0];
    $trackinghistory1[$trackingtime] = $trackingsinfo2;

    unset($trackingtime); 


  }


//if($_GET['rabban']=='true'){print_r($trackinghistory1);}

  //$trackinghistory1 = array_reverse($trackinghistory1);
  krsort($trackinghistory1);

  foreach($trackinghistory1 as $trackingdate){

    if(empty($trackingdate))continue;

    $trackinginfod = explode('###',$trackingdate);

    if($trackinginfod[0] > time())continue;

    $i++;

    $trackingtable .= '

                  <div class="tboxholder">

                    <h3>'.ago($trackinginfod[0]).'</h3>
                    <span>'.$trackinginfod[1].' ('.$trackinginfod[2].' seconds)</span>

                </div>



    ';

  }

if($info['fulfilled']=='0'){

$packageamount1 = $info['amount'];
$packageamountupsell1 = $info['amount'] - ($info['amount'] / 2);

$q = mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `amount` = '$packageamount1' OR `amount` = '$packageamountupsell1' LIMIT 1");

/////////

if($info['estdeliverytime']=='0'){



  $fetchordersessionq = mysql_query("SELECT * FROM `order_session` WHERE `brand`='sv' AND `order_session` = '{$info['order_session']}' LIMIT 1");
  $fetchordersession = mysql_fetch_array($fetchordersessionq);

  $getpackageinfoq = mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `id` = '{$fetchordersession['packageid']}' LIMIT 1");
  $getpackageinfo = mysql_fetch_array($getpackageinfoq);
  if(empty($getpackageinfo['delivtime'])) $getpackageinfo['delivtime'] = 0;
  mysql_query("UPDATE `orders` SET `estdeliverytime` = '{$getpackageinfo['delivtime']}' WHERE `id` = '{$info['id']}' LIMIT 1");

 
$timeget = $getpackageinfo['delivtime'];

if($timeget=='0'){$timeget = 216000;}//IF ITS STILL 0 THEN DEFAULT TO 2 DAYS


}else{


$timeget = $info['estdeliverytime'];


}


//$timeremaining = $info['added'] + 13320;
//$timeremaining = $info['added'] + 64800;
//$timeremaining = $info['added'] + $timeget;
$timeremaining = $info['added'] + ($timeget * 1.1);


//if($_GET['rabban']=='true'){echo $timeget;}

if($timeremaining > time()){//STILL TIMELEFT

//Calculate difference    
$diff=$timeremaining-time();//time returns current time in seconds
$days=floor($diff/(60*60*24));//seconds/minute*minutes/hour*hours/day)
$hours=round(($diff-$days*60*60*24)/(60*60));
$minutes=round($diff/60);

      if(($hours=='1'||$hours=='0')&&($days==0)){$time= $minutes.' mins';}else{



      $time = $hours.' hrs';
      $time = $days.' days, '.$hours.' hrs';


      if($days==0){unset($time); $time= $hours.' hours';if($_GET['rabban']=='true')echo 'asd';}


      }//CHANGE THIS


     //if ($timeremaining > time() && $timeremaining < (time() + 86400)){ //within 24 hours
      if ($timeremaining > time() && $timeremaining < (time() + 7200)){ //within 2 hours



        $deliverystatus = $time;
        $deliverystatusspan = 'Estimated delivery time';

        $javascript = '

var timestamp = ('.round($timeremaining * 1000).' - Date.now()) ;

timestamp /= 1000; // from ms to seconds

function component(x, v) {
    return Math.floor(x / v);
}

var $div = $(\'h1\');

setInterval(function() {
    
    timestamp--;
    
    var days    = component(timestamp, 24 * 60 * 60),
        hours   = component(timestamp,      60 * 60) % 24,
        minutes = component(timestamp,           60) % 60,
        seconds = component(timestamp,            1) % 60;
    
    if(hours==0){$div.html( minutes + ":" + seconds);}
    else{$div.html( hours + ":" + minutes + ":" + seconds);}
    
}, 1000);';



      }



        $deliverystatus = $time;
        $deliverystatusspan = 'Estimated delivery time';



}else{//GONE PAST THE TIME

      $deliverystatus = 'Today';
      $deliverystatusspan = 'Estimated delivery time';

 }




$status = 'In progress <a href="#trackinghistory" style="color:#2e00f4;text-decoration:underline;">(check tracking history)</a>';
$statusnow = 'Your '.$info['amount'].' ' . $premium. ' ' .$info['packagetype']. $upsell_follower_htm .' are on its way';

$stillondelivery=true;

$timesplit = $timeremaining - $info['added'];

$timesplit = $timesplit / 3;

$timesplitarray = array(
  1 => round($timesplit),
  2 => round($timesplit * 2),
  3 => round($timesplit * 3)
  );


$loadingbar = '<div class="loadingbarmoving"></div>';

if(time() > $info['added'] && time() < ($info['added'] + $timesplitarray[1])) $stage1 = true;
if(time() > ($info['added'] + $timesplitarray[1]) && time() < ($info['added'] + $timesplitarray[2])) $stage2 = true; 
if(time() > ($info['added'] + $timesplitarray[3]) && time() < ($info['added'] + 1500000))  $stage3 = true;

if($stage1==true){

$loadingbarstatus1 = 'loadingbarloading';
$loadingbar1 = $loadingbar;
$loadingbarstatus2 = 'loadingbarinactive';
$loadingbarstatus3 = 'loadingbarinactive';
}

if($stage2==true){

$loadingbarstatus1 = 'loadingbaractive';
$loadingbarstatus2 = 'loadingbarloading';
$loadingbar2 = $loadingbar;
$loadingbarstatus3 = 'loadingbarinactive';
}

if($stage3==true){

$loadingbarstatus1 = 'loadingbaractive';
$loadingbarstatus2 = 'loadingbaractive';
$loadingbarstatus3 = 'loadingbarloading';
$loadingbar3 = $loadingbar;
}




}else{

$status = 'Delivery complete';
if($info['country'] == 'uk'){
  $fulDate = date("j/n/Y",$info['fulfilled']);
}else{
  $fulDate = date("n/j/Y",$info['fulfilled']);
}
$deliverystatus = 'Delivered: '.$fulDate.' at '.date("G:i a",$info['fulfilled']);

$deliverystatusspan = '';
$loadinganimationnone = 'style="display:none;"';
$h1resize = 'style="font-size:24px;"';

$loadingbarstatus1 = 'loadingbaractive';
$loadingbarstatus2 = 'loadingbaractive';
$loadingbarstatus3 = 'loadingbaractive';
$colorstate = 'greenstate';
}

if($info['refund']=='2'){


$status = 'Order refunded <a href="#trackinghistory" style="color:#2e00f4;text-decoration:underline;">(check tracking history)</a>';

$loadingbarstatus1 = 'loadingbaractive';
$loadingbarstatus2 = 'loadingbaractive';
$loadingbarstatus3 = 'loadingbaractive';

if($info['country'] == 'uk'){
$refundDate = date("j/n/Y",$info['refundtime']);
}else{
$refundDate = date("l n/j/Y",$info['refundtime']);
}

$deliverystatus = 'Refunded: '.$refundDate.' at '.date("G:i a",$info['refundtime']);
$deliverystatusspan = '';
$h1resize = 'style="font-size:24px;"';
$statusnow = '';
$loadinganimationnone = 'style="display:none;"';
$colorstate = 'redstate';
}


if(empty($info['contactnumber'])){$contactnumber = '<a class="thehref" onclick="signup();return false;" href="#">Add phone number for free order update</a>';$textnotifsbtn = '<a onclick="signup();return false;" href="#" class="btn color4 dshadow" style="margin-top:15px;">Get Free Order Notifications</a>';}
  else{$contactnumber = '<a class="thehref" onclick="signup();return false;" href="#">'.$info['contactnumber'].' (change)</a>';}

if(($info['freelikes']=='0')&&($info['packagetype']=='followers'))$freelikesbtn = '<a onclick="signup2();return false;" href="#" class="btn color4 dshadow" style="margin-top:10px;">Get Free Instagram Likes</a>';


if((($info['packagetype']=='followers')||($info['packagetype']=='freefollowers'))&&($info['fulfilled']!=='0')){

if($info['lastrefilled']=='0'){$lastchecked = $info['fulfilled'];}else{$lastchecked = $info['lastrefilled'];}

$lastrefilldate = $info['added'] + 2592000;
$now = time();

if($now > $info['added'] && $now < $lastrefilldate){$refillstatus = '<font color="green">Refills are on-going</font>';

if($info['norefill']=='1'){$refillstatus = '<font color="orange">Refills have been paused</font>';$refillbtn = '<input type="hidden" name="refill" value="on">
  <input type="submit" class="btn btn3 refillsbtn" name="submit" value="Enable Refills">';}else{

$refillbtn = '<input type="hidden" name="refill" value="off">
  <input type="submit" class="btn btn3 refillsbtn" name="submit" value="Disable Refills">';
  
  }

$refillstatus .= '<br><form method="POST" action="#refill"><input type="hidden" name="refill_session_id" value="'.$info['order_session'].'"><input type="hidden" name="order_id" value="'.$info['id'].'">
'.$refillbtn.'</form>';

  $lastchecked = '
              <div class="tboxholder">

                  <h3>Last checked:</h3>
                  <span>@{igusername} was checked '.ago($lastchecked).'</span>

              </div>';

}
  else{
    $refillstatus = '30-day refill period for your followers have finished';
    unset($lastchecked);
  }



$refilltable = '

<div class="tholder tinfoholder" align="center">

  <div class="cnwidth cnwidthtracking" style="text-align:left">

              <h2 id="refill">♻️ 30-Day Auto-Refill Guarantee</h2>
              <div class="refillnotice">Now that we\'ve delivered your Instagram followers, we will monitor your Instagram account for 30-days after placing your order. This is to ensure that the followers you\'ve received - remains on your account.<br><br>If the followers you\'ve ordered drops, don\'t worry - we\'ll refill your account to the amount you\'ve ordered. Our systems monitor and check your account every 12-24 hours. At Superviral - the customers always comes first. ❤️</div>

              <div class="tboxholder">

                  <h3>Service</h3>
                  <span>Superviral AutoRefill™ ({amount} Followers)</span>

              </div>
              <div class="tboxholder">

                  <h3>Order first made</h3>
                  <span>'.date('l jS \of F Y H:i:s ', $info['added']).'</span>

              </div>
              <div class="tboxholder">

                  <h3>Username</h3>
                  <span>{igusername}</span>

              </div>
              '.$lastchecked.'
              <div class="tboxholder">

                  <h3>Status</h3>
                  <span>'.$refillstatus.'</span>

              </div>
          
 


  </div>
</div>
';
      }

$yesterday = strtotime("today", time());
$threedaysago = $yesterday - 259200;

$q = mysql_query("SELECT * FROM `orders` WHERE `brand`='sv' AND (`added` BETWEEN '$threedaysago' AND '$yesterday')");
$orderamount = round(mysql_num_rows($q) * 7.74);

if($info['packagetype']=='followers'){$showwarning = 'display:block';}


$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'track-my-order-inside') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");


///////////////////////////////

if($info['socialmedia']=='tt'){
			
  $fetchimgq = mysql_query("SELECT * FROM `tt_dp` WHERE `ttusername` LIKE '%{$info['igusername']}%' ORDER BY `id` DESC LIMIT 1");

  $bucket = 'tt-dp/';

}else{

  $fetchimgq = mysql_query("SELECT * FROM `ig_dp` WHERE `igusername` LIKE '%{$info['igusername']}%' ORDER BY `id` DESC LIMIT 1");

  $bucket = 'dp/';

}


  if(mysql_num_rows($fetchimgq)=='0'){$loadinganimationnone = 'style="display:none;"';}else{

    $fetchimg = mysql_fetch_array($fetchimgq);
    $igicon = '<img class="igicon" src="https://cdn.superviral.io/'.$bucket.$fetchimg['dp'].'.jpg">';

  }


if($info['packagetype']=='followers')$deliveryanimationtype = 'deliveryfollowers';
if($info['packagetype']=='likes')$deliveryanimationtype = 'deliverylikes';
if($info['packagetype']=='freelikes')$deliveryanimationtype = 'deliverylikes'; 
if($info['packagetype']=='views')$deliveryanimationtype = 'deliveryviews';


if($info['defect'] != 0){



$deliverystatus = 'Paused';
$statusnow = 'Your order has been paused,<br>please click the resume button to resolve the issue:';



}

sendCloudwatchData('Superviral', 'track-order-success', 'TrackOrder', 'track-order-load-success-function', 1);


$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);

while($cinfo = mysql_fetch_array($contentq)){

	$text = $cinfo['content'];
	
  if($packageinfo['socialmedia'] == 'tt' && $cinfo['page']=='track-my-order-inside'){
		$text = str_ireplace('Instagram', 'Tiktok', $text);
	}

	$tpl = str_replace('{'.$cinfo['name'].'}',$text,$tpl);
  
}

$tpl = str_replace('{title}', '#'.$info['id'].': Track My Order', $tpl);
$tpl = str_replace('{showwarning}', $showwarning, $tpl);
$tpl = str_replace('{contactnumber}', $contactnumber, $tpl);
$tpl = str_replace('{textnotifsbtn}', $textnotifsbtn, $tpl);
$tpl = str_replace('{freelikesbtn}', $freelikesbtn, $tpl);
$tpl = str_replace('{refilltable}', $refilltable, $tpl);
$tpl = str_replace('{trackingtable}', $trackingtable, $tpl);
$tpl = str_replace('{percentbar}', $percentbar, $tpl);
$tpl = str_replace('{orderamount}', $orderamount, $tpl);
$tpl = str_replace('{deliverystatus}', $deliverystatus, $tpl);
$tpl = str_replace('{deliverystatusspan}', $deliverystatusspan, $tpl);
$tpl = str_replace('{trackingnumber}', substr($info['order_session'],0,15), $tpl);
$tpl = str_replace('{hash}', $info['order_session'], $tpl);
$tpl = str_replace('{ordernum}', $info['id'], $tpl);
$tpl = str_replace('{h1}', $info['id'], $tpl);
$tpl = str_replace('{igusername}', $info['igusername'], $tpl);
$tpl = str_replace('{amount}', $info['amount'], $tpl);
$tpl = str_replace('{service}', ucfirst($info['packagetype']), $tpl);
$tpl = str_replace('{socialmedia}', $packageinfo['socialmedia'] == 'tt' ? 'Tiktok' : 'Instagram', $tpl);
$tpl = str_replace('{status}', $status, $tpl);
$tpl = str_replace('{javascript}', $javascript, $tpl);
$tpl = str_replace('{loadinganimationnone}', $loadinganimationnone, $tpl);
$tpl = str_replace('{igicon}', $igicon, $tpl);
$tpl = str_replace('{statusnow}', $statusnow, $tpl);
$tpl = str_replace('{deliveryanimationtype}', $deliveryanimationtype, $tpl);
$tpl = str_replace('{h1resize}', $h1resize, $tpl);
$tpl = str_replace('{colorstate}', $colorstate, $tpl);


$tpl = str_replace('{loadingbarstatus1}', $loadingbarstatus1, $tpl);
$tpl = str_replace('{loadingbarstatus2}', $loadingbarstatus2, $tpl);
$tpl = str_replace('{loadingbarstatus3}', $loadingbarstatus3, $tpl);

$tpl = str_replace('{loadingbar1}', $loadingbar1, $tpl);
$tpl = str_replace('{loadingbar2}', $loadingbar2, $tpl);
$tpl = str_replace('{loadingbar3}', $loadingbar3, $tpl);
$tpl = str_replace('{styleRestart}', $styleRestart, $tpl);
$tpl = str_replace('{contentlanguage}', $locas[$loc]['contentlanguage'], $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);

use Google\Cloud\Translate\V2\TranslateClient;

if($notenglish==true){

            require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

            $translate = new TranslateClient(['key' => $googletranslatekey]);

            $result = $translate->translate($tpl, [
                'source' => 'en', 
                'target' => $locas[$loc]['sdb'],
                'format' => 'html'
            ]);

            $tpl = $result['text'];

}

echo $tpl;
?>