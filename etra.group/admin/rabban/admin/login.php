<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

include('../db.php');
// include('../header.php');
session_start();


// login user if credentials are correct
if($_SERVER["REQUEST_METHOD"] == "POST"){

	$adminauthenticate = 0;
    if(empty($_POST['email']))$error1 = '<div class="notifmsg">{error1}</div>';
    if(empty($_POST['password']))$error2 = '<div class="notifmsg">{error2}</div>';

    foreach($admins as $key => $value){


            if((addslashes($_POST['email']) == $key) && (addslashes($_POST['password']) == $value))
        
            {
                $adminauthenticate = 1;
            }
        
    }
	if($adminauthenticate == 1){//WE'VE VERIFIED THE ACCOUNT



        $session_name1 = 'admin_plus_id';
        $session_value1= md5(time());
        $session_name2 = 'admin_plus_token';
        $session_value2 = md5(time());
        $session_name3 = 'admin_user';
        $session_value3 = addslashes($_POST['email']);

        $_SESSION[$session_name1] = $session_value1;
        $_SESSION[$session_name2] = $session_value2;
        $_SESSION[$session_name3] = $session_value3;
        

        header("location: /admin/check-user.php");


    }else{
        $error =  '<div class="notifmsg">Credentials Not Match</div>';
	}
}


$tpl = file_get_contents('login.html');

if($autolikes=='true')$tpl = str_replace('{h1}', '{h1autolikes}', $tpl);

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $headerscript, $tpl);
$tpl = str_replace('{error}', $error, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{error2}', $error2, $tpl);
$tpl = str_replace('{email}', $_POST['email'], $tpl);
$tpl = str_replace('{forgotpasswordlink}', $locas[$loc]['forgotpassword'], $tpl);
$tpl = str_replace('{signuplink}', $locas[$loc]['signup'], $tpl);
$tpl = str_replace('{autolikesquery}', $autolikesquery, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'login') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;
?>