<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db=1;
include('header.php');

// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}

$tpl = file_get_contents('automatic-instagram-likes-2.html');

$q = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `brand`='sv' AND `retention` = '0' ORDER BY `amount` ASC lIMIT 6");
while($info = mysql_fetch_array($q)){

$info['price'] = explode('.', $info['price']);

$mainprice = $info['price'][0];
$decimal = $info['price'][1];

//if($info['id']==1)$decimalinc = '';

$packages .= '			
						<div class="item dshadow">
							
				              <div class="amount">'.$info['amount'].'<br><span class="label">LIKES PER POST</span></div>
				              
				              <ul class="listctn">

				              		<li><span class="tick"></span>Up to 4 posts per day</li>
				              		<li><span class="tick"></span><b>Real likes</b> from real users</li>
				              		<li><span class="tick"></span><b>Safe & Secure</b> since 2012</li>
				              		<li><span class="tick"></span><b>Free views</b> on all videos</li>
				              		<li><span class="tick"></span>24/7 Support Team</li>
				              		<li><span class="tick"></span>Cancel anytime</li>

				              </ul>
				              <div class="price"><sup class="sign">'.$currency.'</sup><div class="mainprice">'.$mainprice.'</div><sup class="decimal"> a month</sup></div>
				              <div class="buyctn"><a title="'.$info['amount'].' followers" class="btn dshadow color4" href="/'.$loclinkforward.'sign-up/?autolikes=true">Sign Up Now</a>
				              </div>

				              <div class="brought">Cancel anytime</div>

			            </div>';

unset($decimalinc);

}




$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('class="header"', 'class="header dshadow"', $tpl);
if($loc == "uk"){
	$styleUkVersion = "display:none;";
	$pricingBg = "background:white;";
}
$tpl = str_replace('{styleUkVersion}', $styleUkVersion, $tpl);
$tpl = str_replace('{pricingBg}', $pricingBg, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'automatic-likes-page') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
if($cinfo['name']=='canonical')$htmlcanonical = $cinfo['content'];}

//$tpl = str_replace('<link rel="alternate" hreflang="'.$locas[$loc]['contentlanguage'].'" href="'.$htmlcanonical.'" />', '', $tpl);


echo $tpl;
?>