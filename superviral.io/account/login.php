<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

include('../db.php');
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


$accessToken = addslashes($_GET['accessToken']);
$orderlogin = addslashes($_GET['orderlogin']);

if($orderlogin=='true'){$orderloginquerystring = '&orderlogin=true';}

if($accessToken != ""){

        $result = mysql_query("SELECT * FROM `auto_login` WHERE `brand`='sv' AND `access_token` = '$accessToken' LIMIT 1");
        
        if(mysql_num_rows($result) > 0){
                 $userinfo = mysql_fetch_array($result);
                    
                 if(time() < $userinfo['expiry']){
                    
                         // set cookies for 30 days and login
                         $cookie_name1 = 'plus_id';
                         $cookie_value1= $userinfo['email_hash'];
                         $cookie_name2 = 'plus_token';
                         $cookie_value2 = $userinfo['token_hash'];
                          // set plus_id cookie
                         setcookie($cookie_name1, $cookie_value1, time() + (86400 * 365), "/","superviral.io"); // 86400 = 1 day
                         // set plus_token cookie
                         setcookie($cookie_name2, $cookie_value2, time() + (86400 * 365), "/","superviral.io"); // 86400 = 1 day
                    

                         if($orderlogin=='true'){
                            header("location: /".$loclinkforward."order/finish/?utm_source=autologin&utm_medium=email&utm_campaign=orderfinish&utm_content=orderfinish".$orderloginquerystring);
                            
                            die;
                        }

                         else

                        { header("location: /".$loclinkforward.$locas[$loc]['account']."/dashboard/?utm_source=autologin&utm_medium=email&utm_campaign=dashboardaccess&utm_content=viewdashboard".$orderloginquerystring);

                            die;
                        }

                 }
                 else{
                     $error =  '<div class="notifmsg">{mainerror} <a href="/{mainerrorhref}/'.$autolikesquery.'" class="basiclink">{mainerrorhreftitle}</a></div>';
                 }
        }else{
            $error =  '<div class="notifmsg">User Not Found!</div>';

        }
      
    // die;
}

$autolikes = addslashes($_GET['autolikes']);

if($autolikes=='true'){$autolikesquery = '?autolikes=true';}

if(isset($_COOKIE['plus_id']) && isset($_COOKIE['plus_token'])) {//Check if cookie already exists and redirect to account home page
    
    $plus_id = $_COOKIE['plus_id'];
    $plus_token = $_COOKIE['plus_token'];

    // get logged in user with plus_id and plus_token cookie values
    $result = mysql_query("SELECT * FROM `accounts` WHERE `brand`='sv' AND `email_hash` = '$plus_id' AND `token_hash` = '$plus_token' LIMIT 1");
    $userinfo = mysql_fetch_array($result);
    $num_rows = mysql_num_rows($result);
    if($num_rows == 1){//match found, the combination of the email hash and token hash is found so redirect as a goodwill gesture

        if($autolikes=='true'){header('location: /'.$loclinkforward.'account/automatic-likes/');die;}

        header("location: /".$loclinkforward.$locas[$loc]['account']."/");
        exit;
    }

}

// login user if credentials are correct
if($_SERVER["REQUEST_METHOD"] == "POST"){

	$email = trim(strtolower($_POST['email']));
	$password = $_POST['password'];
	$result = mysql_query("SELECT * FROM `accounts` WHERE `brand`='sv' AND `email` ='$email' LIMIT 1");
    $info = mysql_fetch_array($result);
    $password_hashed = $info['password'];
	$num_rows = mysql_num_rows($result);

    if(empty($_POST['email']))$error1 = '<div class="notifmsg">{error1}</div>';
    if(empty($_POST['password']))$error2 = '<div class="notifmsg">{error2}</div>';

	if(($num_rows == 1)&&(password_verify($password, $password_hashed))){//WE'VE VERIFIED THE ACCOUNT



        // set cookies for 30 days and login
        $cookie_name1 = 'plus_id';
        $cookie_value1= $info['email_hash'];
        $cookie_name2 = 'plus_token';
        $cookie_value2 = $info['token_hash'];

        // set plus_id cookie
        setcookie($cookie_name1, $cookie_value1, time() + (86400 * 365), "/","superviral.io"); // 86400 = 1 day
        // set plus_token cookie
        setcookie($cookie_name2, $cookie_value2, time() + (86400 * 365), "/","superviral.io"); // 86400 = 1 day

        if($autolikes=='true'){header("location: /".$loclinkforward."account/automatic-likes/");die;}

        header("location: /".$loclinkforward.$locas[$loc]['account']."/");


    }else{
        $error =  '<div class="notifmsg">{mainerror} <a href="/'.$loclinkforward.'{mainerrorhref}/'.$autolikesquery.'" class="basiclink">{mainerrorhreftitle}</a></div>';
	}
}


$tpl = file_get_contents('login.html');

if($autolikes=='true')$tpl = str_replace('{h1}', '{h1autolikes}', $tpl);

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $headerscript, $tpl);
$tpl = str_replace('{loclinkforward}', $loclinkforward, $tpl);
$tpl = str_replace('{error}', $error, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{error2}', $error2, $tpl);
$tpl = str_replace('{email}', $email, $tpl);
$tpl = str_replace('{forgotpasswordlink}', $locas[$loc]['forgotpassword'], $tpl);
$tpl = str_replace('{signuplink}', $locas[$loc]['signup'], $tpl);
$tpl = str_replace('{autolikesquery}', $autolikesquery, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'login') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");

while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
if($cinfo['name']=='canonical')$htmlcanonical = $cinfo['content'];}

//$tpl = str_replace('<link rel="alternate" hreflang="'.$locas[$loc]['contentlanguage'].'" href="'.$htmlcanonical.'" />', '', $tpl);


echo $tpl;
?>