<?php

//if($_SERVER['HTTP_X_FORWARDED_FOR']!=='77.102.160.65'){die('Unauthorized access');}
//if($_SERVER['HTTP_X_FORWARDED_FOR']!=='89.243.116.167'){die('Unauthorized access');}
//if($_SERVER['HTTP_X_FORWARDED_FOR']!=='212.159.178.222'){die('Unauthorized access');}

include('../sm-db.php');
require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/phpmailer/emailer.php';


/*use Google\Cloud\Translate\V2\TranslateClient;

require(dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php');*/

echo '<style>body{font-family:arial;text-align:left;}</style>';

$email = urldecode($_GET['email']);
$subject = urldecode($_GET['subject']);
$loc2 = urldecode($_GET['loc2']);
$freetrialmd5 = urldecode($_GET['freetrialmd5']);
$tikoidfreetrialmd5 = urldecode($_GET['tikoidfreetrialmd5']);
$username = urldecode($_GET['username']);
$md5unsub = urldecode($_GET['md5unsub']);
$source = urldecode($_GET['source']);
$country = urldecode($_GET['country']);
$altn = urldecode($_GET['altn']);
$brand = urldecode($_GET['brand']);

if($loc2=='ww')$loc2 = '';
if($loc2=='us')$loc2 = '';

if(empty($loc2))$loc2 = 'us/';

$loc3withoutdot = str_replace('/','',$loc2);


        if(empty($loc2))$loc2 = 'us';

        if(!empty($loc2))$loc2 = $loc2.'/';
        if($loc2=='ww/')$loc2 = '';
        if($loc2=='us/')$loc2 = '';




echo 'Email: '.$email.'<br>';
echo 'Subject: '.$subject.'<br>';
echo 'loc2: '.$loc2.'<br>';
echo 'freetrialmd5: '.$freetrialmd5.'<br>';
echo 'md5unsub: '.$md5unsub.'<br>';
echo 'brand: '.$brand.'<br>';

if($source=='cart')$gatracking = '&utm_source=freefollowers&utm_medium=email&utm_campaign=freefollowerscart';
if($source=='order')$gatracking = '&utm_source=freefollowers&utm_medium=email&utm_campaign=freefollowersorder';

echo '<hr>';
echo '<hr>';
echo '<hr>';
echo '<hr>';


switch($brand){
    case 'sv':
        $brandName = 'Superviral';
        $domain = 'superviral.io';
        $path = 'superviral.io/' . $loc2;
        $product = "Instagram";
        break;
    case 'to':
        $brandName = 'Tikoid';
        $domain = 'tikoid.com';
        $path = 'tikoid.com/';
        $product = "Tiktok";
        break;   
         
}



$emailbody = '
<br>
<p>It looks like your followers haven\'t grown much in the last couple of days.</p>

<br>

<p><b>Get your <b>Free 30 '. $product .' Followers</b> here</b>:</p>

<br>

<a href="https://'. $path .'free-followers-reels/?id='.$freetrialmd5.$gatracking.'&emailtype=freefollowers" style="color: #2e00f4;
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
            text-align:center;">Get 30 Free '. $product .' Followers Now &raquo;</a>

<br>

'.$viewaccountdashboardhtml.'


<p>All you need to do is enter your '. $product .' username, and that\'s it!</p> 

<br>

<p>We\'ll immediately start delivering high-quality followers to your account. Have a great weekend!</p>

<br>

<p style="font-size:12px">This no-reply email address doesn\'t accept incoming emails.</p>



';





$tpl = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]).'/'. $domain .'/emailtemplate/emailtemplate.html');

$tpl = str_replace('{body}',$emailbody,$tpl);
$tpl = str_replace('{loc2}',$loc2,$tpl);
$tpl = str_replace('{subject}',$subject,$tpl);
//$tpl = str_replace('Black Friday - 30 '. $product .' Followers!','FREE 30 '. $product .' Followers for Black Friday!',$tpl);
$tpl = str_replace('{username}',$info['igusername'],$tpl);
$tpl = str_replace('{md5unsub}',$md5unsub,$tpl);


///////////////////////////////////////////////////////////////////////////////// TRANSLATE HERE


/*        if(($loc3withoutdot!=='ww')&&($loc3withoutdot!=='us')&&($loc3withoutdot!=='uk')){$notenglish2=true;}

        if($notenglish2==true){


              $translate = new TranslateClient(['key' => $googletranslatekey]);

              $result = $translate->translate($tpl, [
                  'source' => 'en', 
                  'target' => $locas[$loc2]['sdb'],
                  'format' => 'html'
              ]);

              $tpl = $result['text'];


              $result = $translate->translate($subject, [
                  'source' => 'en', 
                  'target' => $locas[$loc2]['sdb'],
                  'format' => 'html'
              ]);

              $subject = $result['text'];




        }*/



////////////////////////////////////////////////////////////




//echo $tpl;



if($brand == 'sv'){
    emailnow($email,'Superviral','no-reply@superviral.io','🐙 '.$subject,$tpl);
}
if($brand == 'to'){
    emailnow($email,'Tikoid','support@tikoid.com','🐙 '.$subject,$tpl);
}


$bodyText = str_replace("'","\'", $tpl);
email_stat_insert('Monthly Free Followers', $email, $bodyText, $brand);


echo "<script>document.bgColor = '#D9FFD9';</script>";




?>