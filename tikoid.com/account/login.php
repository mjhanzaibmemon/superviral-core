<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

// include('../db.php');
include('../header.php');

$accessToken = addslashes($_GET['accessToken']);

if($accessToken != ""){

        $result = mysql_query("SELECT * FROM `auto_login` WHERE `access_token` = '$accessToken' LIMIT 1");
       
        if(mysql_num_rows($result) > 0){
            $userinfo = mysql_fetch_array($result);

            if(time() < $userinfo['expiry']){
    
                    // set cookies for 30 days and login
                    $cookie_name1 = 'plus_id';
                    $cookie_value1= $userinfo['email_hash'];
                    $cookie_name2 = 'plus_token';
                    $cookie_value2 = $userinfo['token_hash'];
                     // set plus_id cookie
                    setcookie($cookie_name1, $cookie_value1, time() + (86400 * 365), "/",".tikoid.com"); // 86400 = 1 day
                    // set plus_token cookie
                    setcookie($cookie_name2, $cookie_value2, time() + (86400 * 365), "/",".tikoid.com"); // 86400 = 1 day
    
                    header("location: /account/orders/");
                    die;
            }
            else{
                $error =  '<div class="notifmsg">Your email address or password was incorrect. Please try again.
                <br><br>
                If you haven\'t signed up before, then you can sign up on the following link: <a href="/sign-up/" class="basiclink">Sign up</a></div>';
            }
        }else{
            $error =  '<div class="notifmsg">User Not Found!</div>';

        }
       
      
    // die;
}


if(isset($_COOKIE['plus_id']) && isset($_COOKIE['plus_token'])) {//Check if cookie already exists and redirect to account home page
    
    $plus_id = $_COOKIE['plus_id'];
    $plus_token = $_COOKIE['plus_token'];

    // get logged in user with plus_id and plus_token cookie values
    $result = mysql_query("SELECT * FROM `accounts` WHERE `email_hash` = '$plus_id' AND `token_hash` = '$plus_token' LIMIT 1");
    $userinfo = mysql_fetch_array($result);
    $num_rows = mysql_num_rows($result);
    if($num_rows == 1){//match found, the combination of the email hash and token hash is found so redirect as a goodwill gesture

        header("location: /account/orders/");
        exit;
    }

}

// login user if credentials are correct
if($_SERVER["REQUEST_METHOD"] == "POST"){

	$email = trim(strtolower($_POST['email']));
	$password = $_POST['password'];
	$result = mysql_query("SELECT * FROM `accounts` WHERE `email` ='$email' LIMIT 1");
    $info = mysql_fetch_array($result);
    $password_hashed = $info['password'];
	$num_rows = mysql_num_rows($result);

    if(empty($_POST['email']))$error1 = '<div class="notifmsg">Please enter your email address:</div>';
    if(empty($_POST['password']))$error2 = '<div class="notifmsg">Please enter your password:</div>';

	if(($num_rows == 1)&&(password_verify($password, $password_hashed))){//WE'VE VERIFIED THE ACCOUNT



        // set cookies for 30 days and login
        $cookie_name1 = 'plus_id';
        $cookie_value1= $info['email_hash'];
        $cookie_name2 = 'plus_token';
        $cookie_value2 = $info['token_hash'];

        // set plus_id cookie
        setcookie($cookie_name1, $cookie_value1, time() + (86400 * 365), "/",".tikoid.com"); // 86400 = 1 day
        // set plus_token cookie
        setcookie($cookie_name2, $cookie_value2, time() + (86400 * 365), "/",".tikoid.com"); // 86400 = 1 day

        header("location: /account/orders/");

    }else{
        $error =  '<div class="notifmsg">Your email address or password was incorrect. Please try again.
        <br><br>
        If you haven\'t signed up before, then you can sign up on the following link: <a href="/sign-up/" class="basiclink">Sign up</a></div>';
	}
}


$tpl = file_get_contents('login.html');

if($autolikes=='true')$tpl = str_replace('Log into your Tikoid Account', 'Log in to Start Your Automatic Likes', $tpl);

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $headerscript, $tpl);
$tpl = str_replace('{error}', $error, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{error2}', $error2, $tpl);
$tpl = str_replace('{forgotpasswordlink}', 'forgot-password', $tpl);
$tpl = str_replace('{signuplink}', 'sign-up', $tpl);

// $contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'login') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
// while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;
?>