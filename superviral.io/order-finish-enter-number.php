<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');
// include('ordercontrol.php');
 //if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222'){$ordersession = '7f741bb74066756e280a51edade6f4fa';}

// /*mysql_query("UPDATE `orders` SET `account_id` = '0',`noaccount` = '0' 
// 	WHERE `order_session` = '$ordersession' ORDER BY `id` DESC LIMIT 1");
// */
// }

$ordersession =  addslashes($_GET['orderid']);

$orderinfoq = mysql_query("SELECT * FROM `orders` WHERE `brand`='sv' AND `order_session` = '$ordersession' ORDER BY `id` DESC LIMIT 1");
$orderinfo = mysql_fetch_array($orderinfoq);
$socialmedia = $orderinfo['socialmedia'];

$userinfoq = mysql_query("SELECT * FROM `users` WHERE `brand`='sv' AND `emailaddress` = '{$info['emailaddress']}' LIMIT 1");
$userinfo = mysql_fetch_array($userinfoq);

if($_GET['setga']=='true'){
	


	mysql_query("UPDATE `orders` SET `recordga` = '1' WHERE `id` = '{$orderinfo['id']}' LIMIT 1");


	die('111');
}


if($_GET['setga']=='true234'){
	


	mysql_query("UPDATE `orders` SET `recordga` = '2' WHERE `id` = '{$orderinfo['id']}' LIMIT 1");


	die('324');
}

//THIS IS FOR MODE=UPDATE PAGE

if(!empty($_POST['input'])){

$_POST['input'] = addslashes($_POST['input']);

mysql_query("UPDATE `users` SET `contactnumber` = '{$_POST['input']}',`sentsms` = '2' WHERE `emailaddress` = '{$info['emailaddress']}' AND `brand`='sv' LIMIT 1");
mysql_query("UPDATE `orders` SET `askednumber` = '2',`contactnumber` = '{$_POST['input']}' WHERE `order_session` = '$ordersession' AND `brand`='sv' LIMIT 1");

header('Location: /order/finish/');die;

}


//IF MODE IS TO UPDATE IF NOT THEN REDIRECT
$body = file_get_contents('order-finish-enter-number.html');

// new tpl added if not having contactnumber 
if($orderinfo['packagetype'] == 'views' || $orderinfo['packagetype'] == 'likes' && $socialmedia == 'ig'){

	$userOrder = mysql_query("SELECT * FROM `orders` WHERE `brand`='sv' AND `emailaddress` = '{$info['emailaddress']}' ORDER BY `id` DESC limit 3");
	$userOrderCount = mysql_num_rows($userOrder);

	// SHOW order4-5b.html by default
	$show_tpl_b = 1;

	if($userOrderCount <= 2){
		// CUSTOMER HAS TO MAKE 2 PREVIOUS ORDERS (THIS WILL BE THE THIRD) TO SEE order4-5b.html
		$show_tpl_b = '';
	}else{
		
		// LOOP THROUGH PREVIOUS 3 ORDERS (INCLUDING THIS ONE) AND CHECK FOR CONTACT NUMBER
		while($dataUserOrder = mysql_fetch_array($userOrder)){
						
			$contactnumber = $dataUserOrder['contactnumber'];
			
			// IF FOUND AN ORDER WITH A CONTACT NUMBER, DON'T SHOW order4-5b.html
			if(!empty($contactnumber)){$show_tpl_b = '';}

		}

	}

	if( $show_tpl_b ){$body = file_get_contents('order4-5b.html');}
	
}

if(!empty($orderinfo['contactnumber'])){$contactnumber = $orderinfo['contactnumber'].' <a class="thehref" href="?mode=update">(Change)</a>';}else{
	$contactnumber = '<a class="thehref" href="?mode=update">Add contact number for free text notifications</a>';
}

if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}

if(empty($orderinfo['contactnumber'])){
	
	$checkforcontactnumberq = mysql_query("SELECT * FROM `orders` WHERE 

		`brand`='sv' AND

		`askednumber` = '1' AND

		(`emailaddress` LIKE '%{$info['emailaddress']}%'

		OR

		`igusername` LIKE '%{$info['igusername']}%')

		ORDER BY `id` DESC LIMIT 1

		");

if(mysql_num_rows($checkforcontactnumberq)==0)$redoutline = 'redoutline';



	$datalayer= '

<script>

var contactnumberenter2 = 1;


</script>';
}

if(!empty($_COOKIE['contactnumber_order_finish'])){
	$orderinfo['contactnumber'] = $_COOKIE['contactnumber_order_finish'];
}

$body = str_replace('{thiscontactnumber}',$orderinfo['contactnumber'],$body);

$orderinfo['price'] = sprintf('%.2f', $orderinfo['price'] / 100);

if($_GET['new']=='true')$gaext = '234';

$recordga = '<script async src="https://www.googletagmanager.com/gtag/js?id=G-C18K306XYW"></script>
				<script>
				window.dataLayer = window.dataLayer || [];            

				function gtag() {
					dataLayer.push(arguments);
				}
				gtag(\'js\', new Date());           

				gtag(\'config\', \'G-C18K306XYW\', {
					\'debug_mode\': true
				});

				(function(w, d, s, l, i) {
				  w[l] = w[l] || [];
				  w[l].push({
					  \'gtm.start\': new Date().getTime(),
					  event: \'gtm.js\'
				  });
				  var f = d.getElementsByTagName(s)[0],
					  j = d.createElement(s),
					  dl = l != \'dataLayer\' ? \'&l=\' + l : \'\';
				  j.async = true;
				  j.src =
					  \'https://www.googletagmanager.com/gtm.js?id=\' + i + dl;
				  f.parentNode.insertBefore(j, f);
				})(window, document, \'script\', \'dataLayer\', \'GTM-NH3B6FF\');
				</script>
				<noscript><iframe src=\'https://www.googletagmanager.com/ns.html?id=GTM-NH3B6FF\' height=\'0\' width=\'0\'
				  style=\'display:none;visibility:hidden\'></iframe></noscript>';


if(($orderinfo['askednumber']=='0')&&($userinfo['sentsms']=='0')){$askednumber = '<div id="askednumber" style="display:none;"></div>';
mysql_query("UPDATE `orders` SET `askednumber` = '1' WHERE `id` = '{$orderinfo['id']}' LIMIT 1");
}


//////////////////////////

if(($orderinfo['account_id']=='0')&&($orderinfo['noaccount']=='0')){

	if($loggedin!==true)$showaccountsignup =1;
	//if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222')echo 'asd';//testing
	//if($loggedin==true)


}

//////////////////////////


if(($orderinfo['account_id']!=='0')&&($loggedin==true)){

$mainctabtnoption = '<a class="color4 btn dshadow" target="_BLANK" style="text-align:center;margin-top: 10px!important;" href="/'.$loclinkforward.'account/dashboard/">View Order & Dashboard</a>';

$mainctabtnoption = '<a onclick="ga(\'send\', \'event\', \'View\', \'Click\', \'Trackingpage\',\'1\');" class="color4 btn dshadow" target="_BLANK" style="text-align:center;margin-top: 10px!important;" href="/'.$loclinkforward.'track-my-order/{order_session}/{ordernumber}">{trackingdetailscta}</a>';

}else{

$mainctabtnoption = '<a onclick="ga(\'send\', \'event\', \'View\', \'Click\', \'Trackingpage\',\'1\');" class="color4 btn dshadow" target="_BLANK" style="text-align:center;margin-top: 10px!important;" href="/'.$loclinkforward.'track-my-order/{order_session}/{ordernumber}">{trackingdetailscta}</a>';

}

///~~~
/*$mainctabtnoption = '<a onclick="ga(\'send\', \'event\', \'View\', \'Click\', \'Trackingpage\',\'1\');" class="color4 btn dshadow" target="_BLANK" style="text-align:center;margin-top: 10px!important;" href="/track-my-order/{order_session}/{ordernumber}">{trackingdetailscta}</a>';*/


//////////////////////////
$accountExist = 0;
if(($loggedin==true)&&(!empty($info['upsell_autolikes']))){


$autolikesfree = '<div style="border-top: 1px solid #e8e8e8;display: inline-block; width: 100%;padding: 22px 0;">

	

<a class="color4 btn dshadow" target="_BLANK" style="text-align:center;margin-top: 10px!important;" href="/account/dashboard/?loadfreeautolikes=true">Activate Free Auto Likes Now</a>

</div>';

$dontdisplayfreealbox = 0;

if($dontdisplayfreealbox ==0){
//CHECK IF THIS ACCOUNT IS ELEGIBLE
$q = mysql_query("SELECT * FROM `accounts` WHERE `brand`='sv' AND `id` = '{$userinfo['id']}' AND `freeautolikes` = '0' LIMIT 1 ");
	if(mysql_num_rows($q)==0)$dontdisplayfreealbox = 1;

}

if($dontdisplayfreealbox ==0){
//CHECK IF THIS ACCOUNT HAS ATLEAST ONE ORDER
$q = mysql_query("SELECT * FROM `orders` WHERE `brand`='sv' AND `account_id` = '{$userinfo['id']}' AND `price` != '0.00' LIMIT 1 ");
	if(mysql_num_rows($q)==0)$dontdisplayfreealbox = 1;

}

if($dontdisplayfreealbox ==0){
$q = mysql_query("SELECT * FROM `automatic_likes_free` WHERE 
  `brand`='sv' AND (
	`contactnumber` = '{$userinfo['freeautolikesnumber']}' OR 
  	`emailaddress` = '{$userinfo['email']}' OR 
  	`ipaddress` = '{$userinfo['user_ip']}'
  ) LIMIT 1 ");

	if(mysql_num_rows($q)==1)$dontdisplayfreealbox = 1;



}

if($dontdisplayfreealbox ==0){
$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `brand`='sv' AND `account_id` = '{$userinfo['id']}' LIMIT 1 ");
if(mysql_num_rows($q)==1){$dontdisplayfreealbox = 1;}

}


if($dontdisplayfreealbox == 1){$autolikesfree = '';}

}

$qCnt = mysql_query("SELECT * FROM `accounts` WHERE `brand`='sv' AND email = '{$info['emailaddress']}' LIMIT 1 ");	
if(addslashes($_GET['new']) == "true" && mysql_num_rows($qCnt) > 0){	
			
	$accountExist = 1;	
}

//////////////////////////



// Code Exclusive for Members
/*
$varKey = isset($_GET['var']) ? addslashes($_GET['var']) : '0'; // If no key, set default as 0
$varKey = ($varKey > 6) ? '0' : $varKey; // if more than 6, set as 0
$Heading = "";
$SubHeading = "";
$Benefits = "";
$BenefitHtml = "";


if($varKey != null && $varKey != ""){
	
		$headingArr = [
						'Daily Viral Photos Explorer', 
					    'Trending Topics', 
						'Post Caption Generator', 
						'DM Reply Tool',
						'Outreach Messaging Tool',
						'Find Fast Growing Pages',
						'Top Comment Explorer Ideas'
					  ];
		$subHeadingArr = [	
							'See the top posts for today from Instagram top creators', 
						  	'See the latest buzz and hottest topics people are talking about',
						  	'Generate killer captions with our caption generator',
					  	  	'Don\'t have the time, or can\'t think of a reply to DMs? We got you covered',
						  	'First impressions are everything, start a lucrative collaboration with a lucrative message',
						  	'Find pages and influencers on the come up or seeing fast growth',
						  	'Dominate the comment section with our top comment explorer'	
						];
		$benefitsArr = [
							0 => ['View latest trends', 'Get inspiration from top creators'],
							1 => ['Find content gaps', 'Piggyback off other influencers', 'Discover new audiences'],
							2 => ['Optimized for high engagement', 'Get a variety of caption ideas', 'Test new captions'],
							3 => ['Quick and Thoughtful DM Responses Made Easy', 'Never Leave Your DMs Unanswered Again', 'Save Time and Respond with Confidence'],
							4 => ['Stand Out with a unique on brand Outreach Messages', 'Maximize Outreach Success with a powerful persuasive message', 'It only takes 5 seconds'],
							5 => ['Get insights into emerging pages', 'Find new inspiration and ideas', 'Stay in the know on new trends'],
							6 => ['New ideas on how to hit top comment', 'Maximize your brand exposure', 'View sentiment analysis data'],
						];

						$Heading = $headingArr[$varKey];
						$SubHeading = $subHeadingArr[$varKey];

						$Benefits = $benefitsArr[$varKey];

						foreach($Benefits as $Benefit){
							$BenefitHtml .= '<li class="item">
                            <span class="icon"></span>
                            <span class="text">'. $Benefit .'</span></li>';

						}
						 
						$viewBtnId = "viewBtnId$varKey";						 
						$requestAccessId = "requestAccessId$varKey";						 
					

}
*/
//END


$tpl = file_get_contents('order-template-new.html');

$tpl = str_replace('{body}', $body, $tpl);

if(($_GET['existinguser']=='true')&&($_GET['nologinbox']=='true'))unset($showaccountsignup);

if($showaccountsignup==1 && $accountExist == 0){$tpl = str_replace('<body', '<body onload="signupaccount();"', $tpl);}else{

	$showconfetti = 'realistic();';
}

$typeofpackage = $orderinfo['packagetype'];

if($typeofpackage == 'freelikes') $typeofpackage = 'Free Likes';


$tpl = str_replace('{mainctabtnoption}', $mainctabtnoption, $tpl);
$tpl = str_replace('{recordga}', $recordga, $tpl);
$tpl = str_replace('{recordga2}', $recordga2, $tpl);
$tpl = str_replace('{discountnotiffinish}', $discountnotiffinish, $tpl);
$tpl = str_replace('{discountnotifcart}', '', $tpl);
$tpl = str_replace('{order_session}', $orderinfo['order_session'], $tpl);
$tpl = str_replace('{back}', '#', $tpl);
$tpl = str_replace('{ordernumber}', $orderinfo['id'], $tpl);
$tpl = str_replace('{contactnumber}', $contactnumber, $tpl);
$tpl = str_replace('{redoutline}', $redoutline, $tpl);
$tpl = str_replace('{datalayer}', $datalayer, $tpl);
$tpl = str_replace('{productname}', $orderinfo['amount'].' '.$orderinfo['packagetype'], $tpl);
$tpl = str_replace('{value}', $orderinfo['price'], $tpl);
$tpl = str_replace('{askednumber}', $askednumber, $tpl);
$tpl = str_replace('{emailaddress}', $info['emailaddress'], $tpl);
$tpl = str_replace('{autolikesfree}', $autolikesfree, $tpl);
$tpl = str_replace('{loc}', $loc, $tpl);
$tpl = str_replace('{showconfetti}', $showconfetti, $tpl);
$tpl = str_replace('{typeofpackage}', $typeofpackage, $tpl);
$tpl = str_replace('{premiumpackagetag}', $premiumpackagetag, $tpl);
$tpl = str_replace('{userName}', $orderinfo['igusername'], $tpl);  
$tpl = str_replace('{loclinkforward}', $loclinkforward, $tpl);  


if($orderinfo['packagetype'] == 'freelikes'){
	unset($_COOKIE['ordersession']); 
	setcookie('ordersession', '', -1, '/'); 
}


$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order4') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");
while($cinfo = mysql_fetch_array($contentq)){
	$text = $cinfo['content'];
	if($orderinfo['socialmedia'] == 'tt' && $cinfo['page'] == 'order4'){
		$tpl = str_replace('{trackingdetailscta}', "Check Tracking Details", $tpl);
		$text = str_ireplace('Instagram', 'Tiktok', $text);
	}

	$tpl = str_replace('{'.$cinfo['name'].'}',$text,$tpl);
}



echo $tpl;
?>