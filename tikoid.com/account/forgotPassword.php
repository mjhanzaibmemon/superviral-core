<?php
/*ini_set('display_errors', 1);

*/
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));


// include('../db.php');
include('../header.php');

function rand_sha1($length) {
  $max = ceil($length / 40);
  $random = '';
  for ($i = 0; $i < $max; $i ++) {
    $random .= sha1(microtime(true).mt_rand(10000,90000));
  }
  return substr($random, 0, $length);
}

if($_GET['password']=='expired'){$message = '<div class="emailsuccess emailfailed">Expired</div>';}

if($_SERVER["REQUEST_METHOD"] == "POST"){

	$email = trim(strtolower(addslashes($_POST['email'])));

	$q = mysql_query("SELECT * FROM `accounts` where `email` ='$email' AND `brand` = 'to' LIMIT 1");
	
  $num_rows = mysql_num_rows($q);
	if($num_rows < 1){
        $error =  '';
    }else{//SEND EMAIL


          $info=mysql_fetch_array($q);

          $randomstring = rand_sha1(56);
          $now = time();

          mysql_query("UPDATE `accounts` SET `resetpwstring` = '$randomstring',`resetpwtime` = '$now' WHERE `id` = '{$info['id']}' AND `brand` = 'to' LIMIT 1");


///////////////////////////


        //EMAIL BODY
        $to = $info['email'];
        $subject = 'Tikoid Account: Reset Your Password';

        $domain_url = $_SERVER['SERVER_NAME'];
        $link= $domain_url.'/reset-password/?id='.$info['email_hash'].'&key='.$randomstring;

        $emailbody = '
<p>As requested, here\'s your password reset link.</p>
<br>

<a href="https://'.$link.'" style="color: #2e00f4;
    border: 2px solid #2e00f4;
    display: block;
    width: 330px;
    padding: 16px 9px;
    text-decoration: none;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    margin: 5px auto;
    font-weight: 700;
    text-align:center;">Click To Reset password &raquo;</a>
<br>
  <p>If you did not request this password reset email, please contact us IMMEDIATELY by replying to this email address.</p>
';

$tpl = file_get_contents('../emailtemplate/emailtemplate.html');
$tpl = str_replace('{body}',$emailbody,$tpl);

$tpl = str_replace('Unsubscribe','',$tpl);
$tpl = str_replace('{subject}',$subject,$tpl);



include('../crons/emailer.php');



$sent = emailnow($to,'Tikoid','no-reply@tikoid.com',$subject,$tpl);




///////////////////////////









	}

$message = '<div class="emailsuccess" style="">If there\'s an account with this email address on Tikoid, then a password reset message was sent to your email address.
<br><br>
Please click the link in that message to reset your password.
<br><br>
If you do not receive the password reset message within a few moments, please check your spam or junk folder.</div>';

}


$tpl = file_get_contents('forgotPassword.html');

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $headerscript, $tpl);
$tpl = str_replace('{message}', $message, $tpl);
$tpl = str_replace('{error}', $error, $tpl);
$tpl = str_replace('{sign-uplink}', 'sign-up', $tpl);

// $contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'forgotpassword') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') AND `brand` = 'to'");
// while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;
?>