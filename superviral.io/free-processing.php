<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');
include('ordercontrol.php');

if($_GET['existinguser']=='true')$redirectq12 = '?&existinguser=true';

//if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222'){$info['order_session'] = '2aa0a9f045f4a1a9ce0c96155273defb';echo 'testmode';}

$stylecss = '<style>
body {
        background-color: #fffdff;
    }

.container {
    height: auto;
}

.container::before {
    content: "";
    display: block;
    height: 16px;
    background: linear-gradient(0deg, rgba(255, 255, 255, 0.00) 0%, #F9F9F9 100%);
}

#main-content {
    margin: 132px 0 152px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.message {
    font-weight: 600;
    display: block;
    text-align: center !important;
}

img{
  width:100%;
  max-width: 500px;
  height: auto;
}

</style>
';

$info = mysql_fetch_array(mysql_query("SELECT * FROM `orders` WHERE `order_session` = '{$_GET['freelikesmsg']}' ORDER BY `id` DESC LIMIT 1"));

$fetchpackageq1 = mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `id` = '{$info['packageid']}' LIMIT 1");  	
$fetchpackageinfo1 = mysql_fetch_array($fetchpackageq1);

$page = '/buy-instagram-followers/'; // for extra security

if($fetchpackageinfo1['socialmedia'] == 'tt' && $fetchpackageinfo1['type'] == 'freelikes') {

  $page = '/buy-tiktok-likes/';
} else if($fetchpackageinfo1['socialmedia'] == 'tt' && $fetchpackageinfo1['type'] == 'freetrial') {
  $page = '/buy-tiktok-followers/';
}

if($fetchpackageinfo1['socialmedia'] == 'ig' && $fetchpackageinfo1['type'] == 'freelikes') {

  $page = '/buy-instagram-likes/';
} else if($fetchpackageinfo1['socialmedia'] == 'ig' && $fetchpackageinfo1['type'] == 'freetrial'){
  $page = '/buy-instagram-followers/';
}

$script .= "

<script>
  setTimeout(function(){
    window.location.href = '". $page ."?freelikesmsg=". $info['order_session'] ."&freefollowers=". $info['order_session'] ."';
  }, 5000);

</script>";



$tpl = file_get_contents('free-processing.html');

// $script .= "

// <script>

// ".$recordga.$premiumpackagetag.$paymentmethod."

//   setTimeout(function(){
//     window.location.href = '/".$loclinkforward.$locas[$loc]['order']."/".$locas[$loc]['order4']."/".$redirectq12."';
//   }, 5000);

// </script>";


$tpl = str_replace('{body}', $stylecss.'
<div class="container">
  <div id="main-content">
      <img src="/imgs/orderprocess.gif" alt="Processing GIF" style="">
      <div class="message">We are processing your order</div>
  </div>
</div>'.$script, $tpl);


$tpl = str_replace('class="backto"', 'class="backto" style="display:none;"', $tpl);
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order3-processing') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");
while($cinfo = mysql_fetch_array($contentq)){

  $tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

}


echo $tpl;



?>