<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

*/
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');


include('../header.php');

// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}

$success = '';
$error = '';
$now = time();
// register user

$autolikes = addslashes($_GET['autolikes']);

if($autolikes=='true'){$autolikesquery = '?autolikes=true';}

if(isset($_COOKIE['plus_id']) && isset($_COOKIE['plus_token'])) {//Check if cookie already exists and redirect to account home page
    
    $plus_id = $_COOKIE['plus_id'];
    $plus_token = $_COOKIE['plus_token'];

    // get logged in user with plus_id and plus_token cookie values
    $result = mysql_query("SELECT * FROM `accounts` WHERE `brand`='sv' AND `email_hash` = '$plus_id' AND `token_hash` = '$plus_token' LIMIT 1");
    $userinfo = mysql_fetch_array($result);
    $num_rows = mysql_num_rows($result);
    if($num_rows == 1){//no match found, the combination of the email hash and token hash isn't found


    	if($autolikes=='true'){header("location: /account/automatic-likes/");die;}


        header("location: /".$locas[$loc]['account']."/");
        die;
    }

}


if($_SERVER["REQUEST_METHOD"] == "POST"){
	$email = trim(strtolower(addslashes($_POST['email'])));
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	$passwordlength = strlen($_POST['password']);
	// check if email already exist
	$result = mysql_query("SELECT * FROM `accounts` WHERE `brand`='sv' AND `email`='$email' LIMIT 1");
	$num_rows = mysql_num_rows($result);

	$email_hash = md5($email);
	$token_hash = md5($tokensecretphrase.md5($email_hash).$password.$now);

	if($num_rows == 1){//EMAIL ALREADY EXISTS
    
        $error1 =  '<div class="notifmsg">{error1} <a href="/'.$locas[$loc]['login'].'/'.$autolikesquery.'" class="basiclink">{error1cta}</a></div>';
    
    }

    if(empty($email)){$error1 =  '<div class="notifmsg">{error2}</div>';}

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    	$error1 =  '<div class="notifmsg">{error3}</div>';
    }

    if(empty($_POST['password'])){$error2 =  '<div class="notifmsg">{error4}</div>';}

    if((empty($error1))&&(empty($error2))){
    	
    	// if email not exist then create new user
		$sql = mysql_query("INSERT INTO `accounts`
			SET 
			`brand` = 'sv',
			`email` = '$email', 
			`password` = '$password', 
			`added` = '$now', 
			`email_hash` = '$email_hash', 
			`token_hash` = '$token_hash', 
			`passwlength` = '$passwordlength', 
			`passwupdated` = '$now', 
			`lastlogin` = '$now'
		");

		if ($sql) {
			//if user created set cookies for 1 day and login

	        $plus_id_value1 = $email_hash;
	        $plus_id_value2 = $token_hash;

	        // set plus_id cookie
	        setcookie("plus_id", $plus_id_value1, time() + (86400 * 30), "/","superviral.io"); // 86400 = 1 day
	        // set plus_token cookie
	        setcookie("plus_token", $plus_id_value2, time() + (86400 * 30), "/","superviral.io"); // 86400 = 1 day

	        if($autolikes=='true'){header("location: /account/automatic-likes/");die;}

	        header("location: /".$locas[$loc]['account']."/");

		} else {
		  $error1 =  '<div class="notifmsg">{error5}</div>';
		}
	}
}

$tpl = file_get_contents('register.html');

if($autolikes=='true')$tpl = str_replace('{h1}', '{h1autolikes}', $tpl);

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $headerscript, $tpl);
$tpl = str_replace('{loclinkforward}', $loclinkforward, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{error2}', $error2, $tpl);
$tpl = str_replace('{loginlink}', $locas[$loc]['login'], $tpl);
$tpl = str_replace('{registerform}', $locas[$loc]['signup'], $tpl);
$tpl = str_replace('{autolikesquery}', $autolikesquery, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'signup') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");

while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
if($cinfo['name']=='canonical')$htmlcanonical = $cinfo['content'];}

//$tpl = str_replace('<link rel="alternate" hreflang="'.$locas[$loc]['contentlanguage'].'" href="'.$htmlcanonical.'" />', '', $tpl);

echo $tpl;
?>