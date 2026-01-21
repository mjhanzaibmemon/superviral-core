<?php
// start time
$start_time = microtime(true);


if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();

header('Content-type: text/html; charset=utf-8');



$db=1;

include('header.php');

include('ordercontrol.php');



$username = addslashes($_POST['username']);

$username = str_replace('@', '', $username);
$username = str_replace('#', '', $username);
$username = trim($username);  

$emailaddress = addslashes($_POST['emailaddress']);

$package = addslashes($_POST['package']);





//IF SUBMITTED

if(!empty($_POST['submitForm'])){


// validate captcha
$validateError = "";
if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])){  
		
	// Google reCAPTCHA verification API Request  
	$api_url = 'https://www.google.com/recaptcha/api/siteverify';  
	$resq_data = array(  
		'secret' => $googleV3ServerKey,  
		'response' => $_POST['g-recaptcha-response'],  
		'remoteip' => $_SERVER['REMOTE_ADDR']  
	);  

	$curlConfig = array(  
		CURLOPT_URL => $api_url,  
		CURLOPT_POST => true,  
		CURLOPT_RETURNTRANSFER => true,  
		CURLOPT_POSTFIELDS => $resq_data  
	);  

	$ch = curl_init();  
	curl_setopt_array($ch, $curlConfig);  
	$response = curl_exec($ch);  
	curl_close($ch);  

	// Decode JSON data of API response in array  
	$responseData = json_decode($response);  

	// If the reCAPTCHA API response is valid  
	if($responseData->success){ 
	  
		// Success

		if($responseData->score < 0.6){

			$validateErrorMsg = "Error 1092: If you\'re using private browsing or incognito, please try entering your details without private/incognito mode on your browser.";		
			$validateError = ' .validateError {display:block!important;}';
		}
	
	}else{  
		$validateErrorMsg = "Authorisation failed, please contact our support team with error 102";		
			$validateError = ' .validateError {display:block!important;}';
	}  
}else{  
	$validateErrorMsg = "Authorisation failed, please contact our support team with error 200";		
	$validateError = ' .validateError {display:block!important;}';
}  





if(empty($username)){$error1 .= '.error1 {display:block!important;} ';}

if(empty($emailaddress)){$error2 .= ' .error2 {display:block!important;}';}



if(!empty($error1)||!empty($error2)||!empty($validateError)){$errorstyle = '<style>'.$error1.$error2.$validateError.'</style>';}




	if(empty($package)){$package = $info['packageid'];}



	if(empty($errorstyle)){



		$res = mysql_query("UPDATE `order_session` SET 

			`igusername` = '$username', 

			`emailaddress` = '$emailaddress', 

			`packageid` = '$package',

			`chooseposts` = '',

			`upsell` = ''    

			WHERE `order_session` = '$ordersession' AND `brand` = 'to' LIMIT 1");



		$added = time();

		$checkforexistuserq = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$emailaddress}' AND `brand` = 'to' LIMIT 1");


		if(mysql_num_rows($checkforexistuserq)==0){
		$updateuser = mysql_query("INSERT IGNORE INTO `users` SET `emailaddress` = '{$emailaddress}', `source` = 'cart',`added` = '{$added}', `brand` = 'to' ");
		}






$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$package}' AND `brand` = 'to' LIMIT 1"));



if(($packageinfo['type']=='likes')||($packageinfo['type']=='views')){



	// if its for likes the redirect

	header('Location: /order/select/');



}



	else{



	// if its for followers then redirect 

	header('Location: /order/review/');

		

}





	}



}else{



	$username = $info['igusername'];

	$emailaddress = $info['emailaddress'];



}





$packagesq = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' AND `brand` = 'to' LIMIT 1"));

$allpackagesq = mysql_query("SELECT * FROM `packages` WHERE `type` = '{$packagesq['type']}' AND `brand` = 'to' ORDER BY `amount` ASC");



while($allpackages = mysql_fetch_array($allpackagesq)){



	$packages .= '<option name="packages" value="'.$allpackages['id'].'">'.$allpackages['amount'].' '.$allpackages['type'].' - '.$currency.$allpackages['price'].'</option>';

	$ptype = $allpackages['type'];

}



$packages = str_replace('value="'.$info['packageid'].'"', 'value="'.$info['packageid'].'" selected = "selected"', $packages);



if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}

if($loggedin==true){
	$displayaccountbtn = 'displayaccountbtn';
	$displayemailaddress = 'style="display:none;"';}



$iconPackage = "";
$titlePackage = "";
switch($ptype){

	
		case 'views':
			$iconPackage  = '<svg style="fill:#4d05bb;" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
			width="179px" height="86.5px" viewBox="0 0 179 86.5" style="enable-background:new 0 0 179 86.5;" xml:space="preserve">
	   <style type="text/css">
		   .st0{fill-rule:evenodd;clip-rule:evenodd;fill:#4D05BB;}
	   </style>
	   <g>
		   <path  d="M81.2,21.2c-2.6,0-4.9,1.2-6.3,3.2c-1,1.3-1.6,3-1.6,4.7c0,4.3,3.5,7.9,7.9,7.9c2.6,0,4.9-1.2,6.3-3.2
			   c1-1.3,1.6-2.9,1.6-4.7C89.1,24.7,85.5,21.2,81.2,21.2z M81.2,33.8c-2.6,0-4.7-2.1-4.7-4.7c0-1.1,0.4-2.1,1-2.8
			   c0.9-1.1,2.2-1.9,3.8-1.9c2.6,0,4.7,2.1,4.7,4.7c0,1.1-0.4,2-1,2.8C84.1,33,82.7,33.8,81.2,33.8z"/>
		   <path  d="M115.7,6.8c0-0.1,0-0.2,0-0.3c-0.2-1.4-1.2-2.6-2.5-3.1c-0.2-0.1-0.4-0.1-0.6-0.2c-0.1,0-0.3,0-0.4-0.1
			   c-0.1,0-0.2,0-0.4,0H51.2c-1.1,0-2.2,0.4-2.9,1.1c-0.9,0.8-1.5,2-1.5,3.3v46.2c0,0.3,0.1,0.6,0.3,0.8c0.2,0.2,0.5,0.3,0.8,0.3h66.5
			   c0.3,0,0.6-0.1,0.9-0.3c0,0,0,0,0.1,0c0.2-0.3,0.4-0.6,0.4-0.9V7.2C115.7,7.1,115.7,6.9,115.7,6.8z M81.2,42.2
			   c-7.8,0-14.5-4.8-18.1-11.9c-0.4-0.7-0.4-1.6,0-2.4c1.6-3.1,3.7-5.7,6.2-7.7c3.4-2.7,7.4-4.2,11.9-4.2c7.8,0,14.5,4.8,18.1,11.9
			   c0.4,0.7,0.4,1.6,0,2.4c-1.5,3.1-3.7,5.7-6.2,7.7C89.7,40.6,85.6,42.2,81.2,42.2z"/>
		   <path  d="M170.3,60V38.9c0-3.4-1-6.7-3-9.4l-10.5-14.6c-1.2-1.7-3.1-3.3-5.2-3.3h-26.2h-0.1c-2.1,0-3.8,1.7-3.8,3.8
			   l0,44.6H86.1c2.2,2.4,3.5,5.4,4,8.6H130c1.1-8,7.9-14,16-14c8.1,0,14.9,6,16,14h5.4c1.6,0,2.9-1.3,2.9-2.9L170.3,60L170.3,60z"/>
		   <path  d="M146,58.2c-6.9,0-12.6,5.7-12.6,12.6s5.7,12.6,12.6,12.6s12.6-5.7,12.6-12.6S152.9,58.2,146,58.2z"/>
		   <path  d="M74.1,58.2c-6.9,0-12.6,5.7-12.6,12.6s5.7,12.6,12.6,12.6s12.6-5.7,12.6-12.6S81,58.2,74.1,58.2z"/>
		   <path  d="M36.3,44.8c0-0.9-0.7-1.5-1.5-1.5H10.3c-0.9,0-1.5,0.7-1.5,1.5s0.7,1.5,1.5,1.5h24.5
			   C35.6,46.4,36.3,45.7,36.3,44.8z"/>
		   <path  d="M40.2,50.7H24.6c-0.9,0-1.5,0.7-1.5,1.5s0.7,1.5,1.5,1.5h15.6c0.9,0,1.5-0.7,1.5-1.5S41.1,50.7,40.2,50.7z"/>
		   <path  d="M40.2,59H14c-0.9,0-1.5,0.7-1.5,1.5S13.2,62,14,62h26.2c0.9,0,1.5-0.7,1.5-1.5S41.1,59,40.2,59z"/>
		   <path  d="M48,60c-0.7,0-1.3,0.6-1.3,1.3v5.2c0,1.2,1,2.1,2.1,2.1h9.2c0.4-3.3,1.9-6.3,4-8.6H48z"/>
	   </g>
	   </svg>
	   ';
	
		$titlePackage = '2M Views';
	
	
	
		
	
	
		break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		
		case 'followers':
			$iconPackage = '<svg  style="fill:#4d05bb;" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
			width="179px" height="86.5px" viewBox="0 0 179 86.5" style="enable-background:new 0 0 179 86.5;" xml:space="preserve">
	   <style type="text/css">
		   .st0{fill-rule:evenodd;clip-rule:evenodd;fill:#4D05BB;}
	   </style>
	   <g>
		   <path  d="M47.8,54.9h66.6c0.4,0,0.7-0.1,0.9-0.4c0.3-0.3,0.4-0.6,0.4-0.9V7.2c0-2.2-1.8-4-4.1-4H51.1
			   c-2.5,0-4.4,2-4.4,4.4v46.3c0,0.3,0.1,0.6,0.3,0.8C47.2,54.8,47.5,54.9,47.8,54.9z M81.1,14.3c4.4,0,7.9,3.5,7.9,7.9
			   s-3.5,7.9-7.9,7.9c-4.4,0-7.9-3.5-7.9-7.9S76.7,14.3,81.1,14.3z M65.8,40.1c0-4.1,3-7.4,6.7-7.4h17.3c3.7,0,6.7,3.3,6.7,7.4v3.6
			   H65.8V40.1z"/>
		   <path  d="M170.4,60V38.9c0-3.4-1.1-6.7-3-9.4l-10.5-14.6c-1.2-1.7-3.1-3.3-5.2-3.3h-26.2h-0.1c-2.1,0-3.8,1.7-3.8,3.8
			   l0,44.7H86.1c2.2,2.4,3.5,5.4,4,8.6h40c1.1-8,7.9-14,16-14c8.1,0,15,6,16,14h5.4c1.6,0,2.9-1.3,2.9-2.9L170.4,60L170.4,60z"/>
		   <path  d="M146.1,58.2c-6.9,0-12.6,5.7-12.6,12.6c0,6.9,5.7,12.6,12.6,12.6c6.9,0,12.6-5.7,12.6-12.6
			   C158.7,63.9,153,58.2,146.1,58.2z"/>
		   <path  d="M74.1,58.2c-6.9,0-12.6,5.7-12.6,12.6c0,6.9,5.7,12.6,12.6,12.6c6.9,0,12.6-5.7,12.6-12.6
			   C86.7,63.9,81,58.2,74.1,58.2z"/>
		   <path  d="M36.2,44.8c0-0.9-0.7-1.5-1.5-1.5H10.2c-0.9,0-1.5,0.7-1.5,1.5s0.7,1.5,1.5,1.5h24.5
			   C35.5,46.4,36.2,45.7,36.2,44.8z"/>
		   <path  d="M40.1,50.7H24.5c-0.9,0-1.5,0.7-1.5,1.5s0.7,1.5,1.5,1.5h15.7c0.9,0,1.5-0.7,1.5-1.5S41,50.7,40.1,50.7z"/>
		   <path  d="M40.1,59H13.9c-0.9,0-1.5,0.7-1.5,1.5c0,0.9,0.7,1.5,1.5,1.5h26.2c0.9,0,1.5-0.7,1.5-1.5
			   C41.7,59.7,41,59,40.1,59z"/>
		   <path  d="M48,60c-0.7,0-1.3,0.6-1.3,1.3v5.2c0,1.2,1,2.1,2.1,2.1H58c0.4-3.3,1.9-6.3,4-8.6H48z"/>
	   </g>
	   </svg>';

				$titlePackage = '905K Followers';
	
	
			
		break;
	
	
	
	
	
	
	
	
	
	
	
		
		case 'likes':
			$iconPackage = '<svg style="fill:#4d05bb;" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
			width="179px" height="86.5px" viewBox="0 0 179 86.5" style="enable-background:new 0 0 179 86.5;" xml:space="preserve">
	   <style type="text/css">
		   .st0{fill-rule:evenodd;clip-rule:evenodd;fill:#4D05BB;}
		   .st1{fill:none;stroke:#4D05BB;stroke-width:3.087;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}
	   </style>
	   <path id="truck_00000074412838011544957010000016086435254879338144_"  d="M110.9,3.2H50.4c-2.4,0-4.4,2-4.4,4.4v46.2
		   c0,0.3,0.1,0.6,0.3,0.8c0.2,0.2,0.5,0.3,0.8,0.3h66.5c0.3,0,0.7-0.1,0.9-0.4c0.2-0.3,0.4-0.6,0.4-0.9V7.2
		   C114.9,5,113.1,3.2,110.9,3.2z M93.3,29.8L80.4,42L67.5,29.8c-3.2-3-3.3-8.1-0.3-11.3c3-3.2,8.1-3.3,11.3-0.3l1.9,1.8l1.9-1.8
		   c3.2-3,8.2-2.9,11.3,0.3C96.6,21.7,96.5,26.8,93.3,29.8z"/>
	   <path  d="M169.5,60v5.7c0,1.6-1.3,2.9-2.9,2.9h-5.4c-1.1-8-7.9-14-16-14c-8.1,0-14.9,6-16,14H89.3
		   c-0.4-3.2-1.8-6.2-4-8.6h35.4l0-44.6c0-2.1,1.7-3.8,3.8-3.8h0.1h26.2c2.1,0,4,1.6,5.2,3.3l10.5,14.6c2,2.7,3,6,3,9.4L169.5,60
		   L169.5,60z"/>
	   <ellipse  cx="145.2" cy="70.8" rx="12.6" ry="12.6"/>
	   <ellipse  cx="73.4" cy="70.8" rx="12.6" ry="12.6"/>
	   <line  x1="9.5" y1="44.8" x2="34" y2="44.8"/>
	   <line  x1="23.8" y1="52.3" x2="39.4" y2="52.3"/>
	   <line  x1="13.3" y1="60.5" x2="39.4" y2="60.5"/>
	   <path  d="M61.3,60c-2.1,2.4-3.6,5.3-4,8.6h-9.2c-1.2,0-2.1-1-2.1-2.1v-5.2c0-0.7,0.6-1.3,1.3-1.3H61.3z"/>
	   </svg>';
	
				$titlePackage = '2.7M Likes';
	
	
	
		break;
	
	
	
	
	
	
	
	
	
	
	
		default:
			$iconPackage = '<svg style="fill:#4d05bb;" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
			width="179px" height="86.5px" viewBox="0 0 179 86.5" style="enable-background:new 0 0 179 86.5;" xml:space="preserve">
	   <style type="text/css">
		   .st0{fill-rule:evenodd;clip-rule:evenodd;fill:#4D05BB;}
		   .st1{fill:none;stroke:#4D05BB;stroke-width:3.087;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}
	   </style>
	   <path id="truck_00000074412838011544957010000016086435254879338144_"  d="M110.9,3.2H50.4c-2.4,0-4.4,2-4.4,4.4v46.2
		   c0,0.3,0.1,0.6,0.3,0.8c0.2,0.2,0.5,0.3,0.8,0.3h66.5c0.3,0,0.7-0.1,0.9-0.4c0.2-0.3,0.4-0.6,0.4-0.9V7.2
		   C114.9,5,113.1,3.2,110.9,3.2z M93.3,29.8L80.4,42L67.5,29.8c-3.2-3-3.3-8.1-0.3-11.3c3-3.2,8.1-3.3,11.3-0.3l1.9,1.8l1.9-1.8
		   c3.2-3,8.2-2.9,11.3,0.3C96.6,21.7,96.5,26.8,93.3,29.8z"/>
	   <path  d="M169.5,60v5.7c0,1.6-1.3,2.9-2.9,2.9h-5.4c-1.1-8-7.9-14-16-14c-8.1,0-14.9,6-16,14H89.3
		   c-0.4-3.2-1.8-6.2-4-8.6h35.4l0-44.6c0-2.1,1.7-3.8,3.8-3.8h0.1h26.2c2.1,0,4,1.6,5.2,3.3l10.5,14.6c2,2.7,3,6,3,9.4L169.5,60
		   L169.5,60z"/>
	   <ellipse  cx="145.2" cy="70.8" rx="12.6" ry="12.6"/>
	   <ellipse  cx="73.4" cy="70.8" rx="12.6" ry="12.6"/>
	   <line  x1="9.5" y1="44.8" x2="34" y2="44.8"/>
	   <line  x1="23.8" y1="52.3" x2="39.4" y2="52.3"/>
	   <line  x1="13.3" y1="60.5" x2="39.4" y2="60.5"/>
	   <path  d="M61.3,60c-2.1,2.4-3.6,5.3-4,8.6h-9.2c-1.2,0-2.1-1-2.1-2.1v-5.2c0-0.7,0.6-1.3,1.3-1.3H61.3z"/>
	   </svg>';
			$titlePackage = '2.7M LIKES';
		break;
}



$tpl = file_get_contents('order-template.html');

$body = file_get_contents('order1.html');


$tpl = str_replace('{body}', $body, $tpl);

$tpl = str_replace('{discountnotifcart}', $discountnotifcart, $tpl);

$tpl = str_replace('{back}', '/buy-tiktok-'.$ptype.'/', $tpl);

$tpl = str_replace('{username}', $username, $tpl);

$tpl = str_replace('{ordersession_id}', $info['id'], $tpl);

$tpl = str_replace('{emailaddress}', $emailaddress, $tpl);

$tpl = str_replace('{packages}', $packages, $tpl);

$tpl = str_replace('{errorstyle}', $errorstyle, $tpl);

$tpl = str_replace('{displayaccountbtn}', $displayaccountbtn, $tpl);

$tpl = str_replace('{validateError}', $validateErrorMsg, $tpl);
$tpl = str_replace('{recaptchaClient}', $googleV3ClientKey, $tpl);

$tpl = str_replace('{titlePackage}', $titlePackage, $tpl);

sendCloudwatchData('Tikoid', 'order-details', 'UserFunnel', 'user-funnel-order-details-function', 1);

// End timer
$end_time = microtime(true);

// Calculate execution time in seconds
$execution_time_sec = $end_time - $start_time;

sendCloudwatchData('Tikoid', 'page-load-order-details', 'PageLoadTiming', 'page-load-order-details-function', number_format($execution_time_sec, 2));


echo $tpl;

?>