<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

*/
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');


include('../header.php');
$success = '';
$error = '';
$now = time();
// register user

if(isset($_COOKIE['plus_id']) && isset($_COOKIE['plus_token'])) {//Check if cookie already exists and redirect to account home page
    
    $plus_id = $_COOKIE['plus_id'];
    $plus_token = $_COOKIE['plus_token'];

    // get logged in user with plus_id and plus_token cookie values
    $result = mysql_query("SELECT * FROM `accounts` WHERE `email_hash` = '$plus_id' AND `token_hash` = '$plus_token' AND `brand` = 'to' LIMIT 1");
    $userinfo = mysql_fetch_array($result);
    $num_rows = mysql_num_rows($result);
    if($num_rows == 1){//no match found, the combination of the email hash and token hash isn't found

        header("location: /account/orders/");
        die;
    }

}


if($_SERVER["REQUEST_METHOD"] == "POST"){
	$email = trim(strtolower(addslashes($_POST['email'])));
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	$passwordlength = strlen($_POST['password']);
	// check if email already exist
	$result = mysql_query("SELECT * FROM `accounts` WHERE `email`='$email' AND `brand` = 'to' LIMIT 1");
	$num_rows = mysql_num_rows($result);

	$email_hash = md5($email);
	$token_hash = md5($tokensecretphrase.md5($email_hash).$password.$now);

	if($num_rows == 1){//EMAIL ALREADY EXISTS
    
        $error1 =  '<div class="notifmsg">Email already exists! Try logging in here: <a href="/login/" class="basiclink">Log in</a></div>';
    
    }

    if(empty($email)){$error1 =  '<div class="notifmsg">Please enter your email address:</div>';}

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    	$error1 =  '<div class="notifmsg">Please ensure you\'ve typed in the correct email address:</div>';
    }

    if(empty($_POST['password'])){$error2 =  '<div class="notifmsg">Please enter a secure password:</div>';}

    if((empty($error1))&&(empty($error2))){
    	
    	// if email not exist then create new user
		$sql = mysql_query("INSERT INTO `accounts`
			SET 
			`country` = 'us',
			`email` = '$email', 
			`password` = '$password', 
			`added` = '$now', 
			`email_hash` = '$email_hash', 
			`token_hash` = '$token_hash', 
			`passwlength` = '$passwordlength', 
			`passwupdated` = '$now', 
			`lastlogin` = '$now',
			`brand` = 'to'
		");

		if ($sql) {
			//if user created set cookies for 1 day and login

	        $plus_id_value1 = $email_hash;
	        $plus_id_value2 = $token_hash;

	        // set plus_id cookie
	        setcookie("plus_id", $plus_id_value1, time() + (86400 * 30), "/",".tikoid.com"); // 86400 = 1 day
	        // set plus_token cookie
	        setcookie("plus_token", $plus_id_value2, time() + (86400 * 30), "/",".tikoid.com"); // 86400 = 1 day


	        header("location: /account/orders/");

		} else {
		  $error1 =  '<div class="notifmsg">Error with signing up. Please contact support with error code #49393.</div>';
		}
	}
}

$tpl = file_get_contents('register.html');

if($autolikes=='true')$tpl = str_replace('Sign up for Tikoid', 'Sign up for Automatic Likes', $tpl);

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $headerscript, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{error2}', $error2, $tpl);
$tpl = str_replace('{loginlink}', 'login', $tpl);
$tpl = str_replace('{registerform}', 'sign-up', $tpl);



// $contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'signup') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') AND `brand` = 'to'");
// while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

echo $tpl;
?>