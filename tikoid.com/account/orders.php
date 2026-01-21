<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$activelink1 = 'activelink';


include('header.php');
include('auth.php');


////////////////////////////////////////////////////////////////////////////////////////

// $dontdisplayfreealbox = 0;

// if($dontdisplayfreealbox ==0){
// //CHECK IF THIS ACCOUNT IS ELEGIBLE
// $q = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$userinfo['id']}' AND `freeautolikes` = '0' AND `brand` = 'to' LIMIT 1 ");
// 	if(mysql_num_rows($q)==0)$dontdisplayfreealbox = 1;

// }

// if($dontdisplayfreealbox ==0){
// //CHECK IF THIS ACCOUNT HAS ATLEAST ONE ORDER
// $q = mysql_query("SELECT * FROM `orders` WHERE `account_id` = '{$userinfo['id']}' AND `price` != '0.00' AND `brand` = 'to' LIMIT 1 ");
// 	if(mysql_num_rows($q)==0)$dontdisplayfreealbox = 1;

// }

// if($dontdisplayfreealbox ==0){
// $q = mysql_query("SELECT * FROM `automatic_likes_free` WHERE 
//   `contactnumber` = '{$userinfo['freeautolikesnumber']}' OR 
//   `emailaddress` = '{$userinfo['email']}' OR 
//   `ipaddress` = '{$userinfo['user_ip']}' AND `brand` = 'to' LIMIT 1 ");

// 	if(mysql_num_rows($q)==1)$dontdisplayfreealbox = 1;



// }

// if($dontdisplayfreealbox ==0){
// $q = mysql_query("SELECT * FROM `automatic_likes` WHERE `account_id` = '{$userinfo['id']}' AND `brand` = 'to' LIMIT 1 ");
// if(mysql_num_rows($q)==1){$dontdisplayfreealbox = 1;}



// }


// if($dontdisplayfreealbox == 1){$freeautolikesdisplay = ' display:none;';}

////////////////////////////////////////////////////////////////////////////////////////

if($_GET['passwordchange']=='true')$message1 = '<div class="emailsuccess">Password changed successfully. It\'s good to have you back!</div>';

$q = mysql_query("SELECT * FROM `orders` WHERE `account_id` = '{$userinfo['id']}' AND `brand` = 'to' ORDER BY `id` DESC LIMIT 10");

if(mysql_num_rows($q)==0){$orders = 'Once you\'ve made an order while logged in, it will show up here.';}
else{

		while($info = mysql_fetch_array($q)){


			if($info['fulfilled']=='0'){
				$class = 'pending';
				$status = 'in progress';
			}else{
				
				$now = time();
				$betweendays = time() - (86400 * 2);
				 if(($betweendays <= $info['fulfilled']) && ($info['fulfilled'] <= $now)){
				 	//its between 3-days
				 	$class = 'complete complete-a';
				 }else{$class = 'complete';}

				$status = 'delivered '.date("l j/n/Y",$info['fulfilled']);
			}

			$orders .='
			<div class="orders">
				<div class="title '.$class.'"><b>+'.$info['amount'].' '.$info['packagetype'].'
									<div class="spinholder"><img class="spinning" src="/imgs/inprogress.svg"></div>
									</b><span class="status" style="word-wrap: break-word;width: 120px;">'.$status.'</span>
									<a href="/track-my-order/'.$info['order_session'].'/'.$info['id'].'" class="btn btn3 btntracking showmo mobilehide" style="margin-right: 90px;">View tracking info</a>
									<a href="/order/choose/?setorder='.$info['order_session'].'&discounton=no" class="btn btn3 mobilehide" style="    height: 15px;
									font-size: 13px;
									width: 125px;
									margin: 0px;
									margin-bottom: 10px;
									line-height: 1px;">Re-order</a>';
								if($info['packagetype'] != "freefollowers")	{
									$orders .='<form method="post" action="/receipt-pdf-generator.php" ><input name="orderCountry" type="hidden" value="'. $info['country'] .'"><input name="orderAmount" type="hidden" value="'. $info['amount'] .'"><input name="billingName" type="hidden" value="'. $info['payment_billingname'] .'"><input name="billingEmail" type="hidden" value="'. $info['emailaddress'] .'"><input name="orderID" type="hidden" value="'. $info['id'] .'"><input name="billingCard" type="hidden" value="'. $info['lastfour'] .'"><input name="orderDate" type="hidden" value="'. date("l j/n/Y",$info['added']) .'"><input name="orderPrice" type="hidden" value="'. intval(intval($info['price'])/100) .'.00"><input name="packageType" type="hidden" value="'. $info['packagetype'] .'"><button type="submit" class="btn btn3 btntracking showmo mobilehide">Receipt</button></form>';
								}
								
				$orders .='</div>
				
				<div class="igusername">#'.$info['id'].' - '.$info['igusername'].'</div>
			
				<a href="/track-my-order/'.$info['order_session'].'/'.$info['id'].'" class="btn btn3 btntracking showmo mobileshow">View tracking info</a>
				<a href="/order/choose/?setorder='.$info['order_session'].'&discounton=no" class="btn btn3 btntracking showmo mobileshow">Re-order</a>';
				if($info['packagetype'] != "freefollowers")	{
						$orders .='<form method="post" action="/receipt-pdf-generator.php" ><input name="orderCountry" type="hidden" value="'. $info['country'] .'"><input name="orderAmount" type="hidden" value="'. $info['amount'] .'"><input name="billingName" type="hidden" value="'. $info['payment_billingname'] .'"><input name="billingEmail" type="hidden" value="'. $info['emailaddress'] .'"><input name="orderID" type="hidden" value="'. $info['id'] .'"><input name="billingCard" type="hidden" value="'. $info['lastfour'] .'"><input name="orderDate" type="hidden" value="'. date("l j/n/Y",$info['added']) .'"><input name="orderPrice" type="hidden" value="'. intval(intval($info['price'])/100) .'.00"><input name="packageType" type="hidden" value="'. $info['packagetype'] .'"><button type="submit" class="btn btn3 btntracking showmo mobileshow">Receipt</button></form>';
				}
			$orders .='</div>';

		unset($class);
		unset($status);

		}

}




////////////////////////////


// $findsubcriptonsq = mysql_query("SELECT * FROM `automatic_likes` WHERE `account_id` = '{$userinfo['id']}' AND `brand` = 'to' ORDER BY `id` DESC");

// if(mysql_num_rows($findsubcriptonsq)!==0){

// 	while($subsinfo = mysql_fetch_array($findsubcriptonsq)){

// 		$fetchimgq = mysql_query("SELECT * FROM `ig_dp` WHERE `igusername` LIKE '%{$subsinfo['igusername']}%' AND `brand` = 'to' ORDER BY `id` DESC LIMIT 1");
// 		$fetchimg = mysql_fetch_array($fetchimgq);

// 		if($subsinfo['disabled']=='1'){

// 			$status = 'Paused';
// 			$statuspaused = 'statuspaused';

// 			}else{
		
// 		$status = '<span class="livebox"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="livesvg"><path d="M256 0C115.4 0 0 115.4 0 256s115.4 256 256 256 256-115.4 256-256S396.6 0 256 0z"></path></svg> Live</span> Active ';}

// 		if($subsinfo['cancelbilling']=='3'){$additionalstatus = ' (expires on '.date("d/m/Y",$subsinfo['expires']).')';}


// 	$autolikesonaccount .= '


// 	<div class="subscriptions dshadow">

// 		<img class="dp" src="https://cdn.superviral.io/dp/'.$fetchimg['dp'].'.jpg">
		


			
// 			<div class="substitle subtitlemain"><b>'.$subsinfo['likes_per_post']. ' likes per post</b> <font class="username">  @'.$subsinfo['igusername'].'</font>
// 			</div>
// 			<div class="substitle">
// 				<div class="status '.$statuspaused.'">'.$status.$additionalstatus.'</div>
// 				<a href="/account/edit/'.$subsinfo['md5'].'" class="btn btn3 savingcardbtn dshadow">edit</a>
// 			</div>

// 	</div>';

// 		unset($status);
// 		unset($additionalstatus);
// 		unset($statuspaused);

// 		}

// } else {

	
// 	$autolikesonaccount = 'It seems you do not have an Auto Likes plan active. <a href="/account/automatic-likes/">Click here to get automatic likes!></a><br><br>';


// }






////////////////////////////



$tpl = file_get_contents('orders.html');

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{orders}', $orders, $tpl);
$tpl = str_replace('{message1}', $message1, $tpl);
// $tpl = str_replace('{freeautolikesdisplay}', $freeautolikesdisplay, $tpl);
// $tpl = str_replace('{autolikesonaccount}', $autolikesonaccount, $tpl);

// if($_GET['loadfreeautolikes']=='true'){$tpl = str_replace('<body>','<body onload="signup2();return false;">',$tpl);}

// $contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = 'ww' AND `page` = 'home') OR (`country` = 'ww' AND `page` = 'global') AND `brand` = 'to'");
// while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

/*use Google\Cloud\Translate\V2\TranslateClient;

if($notenglish==true){

            require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

            $translate = new TranslateClient(['key' => $googletranslatekey]);

            $result = $translate->translate($tpl, [
                'source' => 'en', 
                'target' => $locas[$loc]['sdb'],
                'format' => 'html'
            ]);

            $tpl = $result['text'];

}*/

echo $tpl;
?>
