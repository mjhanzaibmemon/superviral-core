<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

include('../db.php');
include('../header.php');

//1. CHECK FIRST IF ID AND KEY IS SET
if((empty($_GET['id'])) || (empty($_GET['key']))){

	header("Location: /".$loclinkforward.$locas[$loc]['account']."/".$locas[$loc]['login']."/"); //NO ID OR KEY SET
	die;

}

//2. CHECK IF ID AND KEY IS VALID AND NOT EXPIRED
if((!empty($_GET['id'])) && (!empty($_GET['key']))){

  $id=addslashes($_GET['id']);
  $key=addslashes($_GET['key']);

  $q = mysql_query("SELECT * FROM `accounts` WHERE `email_hash` ='$id' and `resetpwstring` = '$key' AND `brand`='sv' LIMIT 1");
  $num_rows = mysql_num_rows($q);

	//FETCH ACCOUNT INFO
	$info = mysql_fetch_array($q);

	if($num_rows==0){//ALL CHECKS DONE if its not equal to one, then redirect
	 	header("Location: /".$loclinkforward.$locas[$loc]['login']."/?password=changedordone"); //NO MATCH FOUND
	  	die;
	}

	//CHECK IF KEY HAS EXPIRED
	if (time() - $info['resetpwtime'] > 2 * 3600) {

		//30 minutes has passed
		$expired=1;
		echo 'Expired';
		header("Location: /".$loclinkforward.$locas[$loc]['forgotpassword']."/?password=expired");
		die;

	}


}

//3. IF USERNAME AND PASSWORD POSTED THEN PROCESS NEW PASSWORD
if(!empty($_POST['submitted'])){

	$newpassword = addslashes($_POST['newpassword']);
	$confirmpassword = addslashes($_POST['confirmpassword']);

	if(empty($newpassword)){
		$error1 = '<div class="notifmsg">Please type in a new password that has a number, captiral letter and is atleast 8-characters:</div>';
	}


	if(empty($confirmpassword)){
		$error2 = '<div class="notifmsg">Please ensure that you\'ve re-typed in your NEW password into the form below:</div>';
	}

	if($newpassword!==$confirmpassword){
		$error2 = '<div class="notifmsg">Please ensure that your re-typed password is the same as your NEW password.</div>';
	}

	if($newpassword==$confirmpassword){//Confirmed that both password's match, now time to log user in with password change notification
	


		$now = time();
		$email_hash = $info['email_hash']; 
		$passwordlength = strlen($newpassword);
		$password_hash = password_hash($newpassword, PASSWORD_DEFAULT);
		$token_hash = md5($tokensecretphrase.md5($email_hash).$password_hash.$now);

		$updateaccountq = mysql_query("UPDATE `accounts`
			SET 
			`password` = '$password_hash', 
			`token_hash` = '$token_hash', 
			`resetpwstring` = '', 
			`passwlength` = '$passwordlength' 

			WHERE `email_hash` = '$id' AND brand='sv' LIMIT 1");

		if($updateaccountq){

			// set cookies for 30 days and login
	        $cookie_name1 = 'plus_id';
	        $cookie_value1= $info['email_hash'];
	        $cookie_name2 = 'plus_token';
	        $cookie_value2 = $token_hash;
	        
	        // set plus_id  and plus_token cookie
	        setcookie($cookie_name1, $cookie_value1, time() + (86400 * 30), "/"); // 86400 = 1 day
	        setcookie($cookie_name2, $cookie_value2, time() + (86400 * 30), "/"); // 86400 = 1 day

			header('Location: /'.$loclinkforward.'account/dashboard/?passwordchange=true');

		}

		else{


				die('Error 4921: There as an error, please contact our Support Team so that we can resolve this issue.');


			}

	}



}

$tpl = file_get_contents('reset_pass.html');

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $headerscript, $tpl);
$tpl = str_replace('{email}', $email, $tpl);
$tpl = str_replace('{id}', $id, $tpl);
$tpl = str_replace('{key}', $key, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{error2}', $error2, $tpl);
$tpl = str_replace('{formsubmit}', $loclinkforward.$locas[$loc]['resetpassword'], $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


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