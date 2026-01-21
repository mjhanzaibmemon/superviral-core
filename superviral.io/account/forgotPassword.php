<?php
/*ini_set('display_errors', 1);

*/
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));


use Google\Cloud\Translate\V2\TranslateClient;

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


function rand_sha1($length) {
  $max = ceil($length / 40);
  $random = '';
  for ($i = 0; $i < $max; $i ++) {
    $random .= sha1(microtime(true).mt_rand(10000,90000));
  }
  return substr($random, 0, $length);
}

if($_GET['password']=='expired'){$message = '<div class="emailsuccess emailfailed">{error1}</div>';}

if($_SERVER["REQUEST_METHOD"] == "POST"){

	$email = trim(strtolower(addslashes($_POST['email'])));

	$q = mysql_query("SELECT * FROM `accounts` WHERE `brand`='sv' AND `email` ='$email' LIMIT 1");
	
  $num_rows = mysql_num_rows($q);
	if($num_rows !== 1){
        $error =  '';
    }else{//SEND EMAIL


          $info=mysql_fetch_array($q);

          $randomstring = rand_sha1(56);
          $now = time();

          mysql_query("UPDATE `accounts` SET `resetpwstring` = '$randomstring',`resetpwtime` = '$now' WHERE `id` = '{$info['id']}' AND `brand`='sv' LIMIT 1");


///////////////////////////


        //EMAIL BODY
        $to = $info['email'];
        $subject = 'Superviral Account: Reset Your Password';

        $domain_url = $_SERVER['SERVER_NAME'];
        $link= $domain_url.'/'.$loclinkforward.'reset-password/?id='.$info['email_hash'].'&key='.$randomstring;

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
$formattedDate = date('d/m/Y h:i A', $info['added']);
$tpl= str_replace('{date_added}', $formattedDate, $tpl);


include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';





if($notenglish==true){

            require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

            $translate = new TranslateClient(['key' => $googletranslatekey]);

            $result = $translate->translate($subject, [
                'source' => 'en', 
                'target' => $locas[$loc]['sdb'],
                'format' => 'html'
            ]);

            $subject = $result['text'];



            $translate2 = new TranslateClient(['key' => $googletranslatekey]);

            $result = $translate2->translate($tpl, [
                'source' => 'en', 
                'target' => $locas[$loc]['sdb'],
                'format' => 'html'
            ]);

            $tpl = $result['text'];




}


$sent = emailnow($to,'Superviral','no-reply@superviral.io',$subject,$tpl);




///////////////////////////









	}

$message = '<div class="emailsuccess" style="">{error2}</div>';

}


$tpl = file_get_contents('forgotPassword.html');

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $headerscript, $tpl);
$tpl = str_replace('{message}', $message, $tpl);
$tpl = str_replace('{error}', $error, $tpl);
$tpl = str_replace('{sign-uplink}', '/'.$loclinkforward.$locas[$loc]['signup'], $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'forgotpassword') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");


while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
if($cinfo['name']=='canonical')$htmlcanonical = $cinfo['content'];}

//$tpl = str_replace('<link rel="alternate" hreflang="'.$locas[$loc]['contentlanguage'].'" href="'.$htmlcanonical.'" />', '', $tpl);


echo $tpl;
?>