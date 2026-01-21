<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$activelink2 = 'activelink';

include('../db.php');
include('auth.php');
include('header.php');


$now = time();



$q = mysql_query("SELECT * FROM `automatic_likes_packages` WHERE `retention` = '0' AND `brand`='sv' ORDER BY `amount` ASC lIMIT 6");
while($info = mysql_fetch_array($q)){

$info['price'] = explode('.', $info['price']);

$mainprice = $info['price'][0];
$decimal = $info['price'][1];

//if($info['id']==1)$decimalinc = '<sup class="decimal">.'.$decimal.'</sup>';

$packages .= '			
<div class="item dshadow">
						<div class="amount"><img class="heart" src="/imgs/heart.svg">'.$info['amount'].'<br><span class="label">LIKES PER POST</span></div>


				              
				              <ul class="listctn">

				              		<li><span class="tick"></span>Up to 4-posts per day</li>
				              		<li><span class="tick"></span>Real likes from real users</li>
				              		<li><span class="tick"></span>Free views on all videos</li>
				              		<li><span class="tick"></span>Safe & Secure since 2012</li>
				              		<li><span class="tick"></span>24/7 customer support</li>

				              </ul>

				              <div class="price"><sup class="sign">'.$locas[$loc]['currencysign'].'</sup><div class="mainprice">'.$mainprice.'</div><sup class="sign">'.$locas[$loc]['currencyend'].' /mo</sup>'.$decimalinc.'</div>

				              <div class="buyctn"><a title="'.$info['amount'].' {titlepackagetype}" class="btn btn11 color4" href="/'.$loclinkforward.'account/automatic-likes-select/'.$info['id'].'">Get Auto Likes Now</a>
				              </div>

				              <div class="brought">
          30 day money back guarantee<br>Cancel anytime</div>

			            </div>';

//unset($decimalinc);

}



////////////////////////////



$findsubcriptonsq = mysql_query("SELECT * FROM `automatic_likes` WHERE `account_id` = '{$userinfo['id']}' AND `brand`='sv' ORDER BY `id` DESC");

if(mysql_num_rows($findsubcriptonsq)!==0){

	while($subsinfo = mysql_fetch_array($findsubcriptonsq)){

		$fetchimgq = mysql_query("SELECT * FROM `ig_dp` WHERE `igusername` LIKE '%{$subsinfo['igusername']}%' ORDER BY `id` DESC LIMIT 1");
		$fetchimg = mysql_fetch_array($fetchimgq);

		if($subsinfo['disabled']=='1'){

			$status = 'Paused';
			$statuspaused = 'statuspaused';

			}else{
		
		$status = '<span class="livebox"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="livesvg"><path d="M256 0C115.4 0 0 115.4 0 256s115.4 256 256 256 256-115.4 256-256S396.6 0 256 0z"></path></svg> Live</span> Active ';}

		if($subsinfo['cancelbilling']=='3'){$additionalstatus = ' (expires on '.date("d/m/Y",$subsinfo['expires']).')';}


	$subscriptionresults .= '


	<div class="subscriptions dshadow">

		<img class="dp" src="https://cdn.superviral.io/dp/'.$fetchimg['dp'].'.jpg">
		


			
			<div class="substitle subtitlemain"><b>'.$subsinfo['likes_per_post']. ' likes per post</b> <font class="username">  @'.$subsinfo['igusername'].'</font>
			</div>
			<div class="substitle">
				<div class="status '.$statuspaused.'">'.$status.$additionalstatus.'</div>
				<a href="/'.$loclinkforward.'account/edit/'.$subsinfo['md5'].'" class="btn btn3 savingcardbtn dshadow">edit</a>
			</div>

	</div>';

		unset($cardbrandset);
		unset($makeprimary);
		unset($primaryclass);
		unset($expiredmsg);
		unset($status);
		unset($additionalstatus);
		unset($statuspaused);

		}

} else {

	
	$subscriptionresults = 'It seems you do not have an Auto Likes plan active. Click on a package above to get started!<br><br>';


}






////////////////////////////




$tpl = file_get_contents('automatic-likes.html');
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);
$tpl = str_replace('{subscriptionresults}', $subscriptionresults, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


use Google\Cloud\Translate\V2\TranslateClient;

if($notenglish==true){

			require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

            $translate = new TranslateClient(['key' => $googletranslatekey]);

            $result = $translate->translate($tpl, [
                'source' => 'en', 
                'target' => $locas[$loc]['sdb'],
                'format' => 'html'
            ]);

            $tpl = $result['text'];

}


echo $tpl;
?>
