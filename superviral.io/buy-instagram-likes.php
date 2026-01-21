<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));
	
$db=1;
if(empty($_COOKIE['discount'])){$nomaindb=1;}else{$nomaindb=0;}
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

function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }

    return $ip;
}

function getUserIpInfo($ip)
{
    global $ipinfoToken;

    $token = $ipinfoToken;

    $ip_address = trim($ip); 

    $api_url = "https://ipinfo.io/" . $ip_address . "?token=" . $token;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    curl_close($ch);

    $data = json_decode($response, true);

    return $data;
    
}

$ipaddress = getUserIP();



// show flag
if(!empty($_GET['get_country'])){
    $ipinfo = getUserIpInfo($ipaddress);
	$flagShowCall = "";
	if(!empty($ipinfo['country'])){
		echo $ipinfo['country'];
        die;
	}
}

if($_GET['notif_ajax']==1){
	$arr = [50,100,100,100,250,250,250,500,500,1000,1000,2500,5000];
	echo json_encode($arr);die;
}

use Google\Cloud\Translate\V2\TranslateClient;

if(!empty($_GET['freelikesmsg'])){

	$freelikesid = addslashes($_GET['freelikesmsg']);



	$searchfreetrialorderq = mysql_query("SELECT * FROM `orders` WHERE `order_session` = '{$freelikesid}' LIMIT 1");
	$searchfreetrialorder = mysql_fetch_array($searchfreetrialorderq);

	$freelikesmsg = '<div class="message2">Your FREE 50 Instagram Likes are on its way to @'.$searchfreetrialorder['igusername'].'!</div>';

	if($notenglish==true){

			require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

            $translate = new TranslateClient(['key' => $googletranslatekey]);

            $result = $translate->translate($freelikesmsg, [
                'source' => 'en', 
                'target' => $locas[$loc]['sdb'],
                'format' => 'html'
            ]);

            $freelikesmsg = $result['text'];

	}



}

if($locas[$loc]['sdb'] == 'uk'){
	$tpl = file_get_contents('uk/buy-instagram-likes.html');
}else{
	$tpl = file_get_contents('us/buy-instagram-likes.html');
}

// $tpl = file_get_contents('buy-instagram-likes-5.html');
// if($_GET['rabban']=='true')$tpl = file_get_contents('buy-instagram-likes-test.html');
//$tpl = file_get_contents('buy-instagram-likes-3-bf.html');

$q = mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `type` = 'likes' AND `premium` = '0' AND socialmedia = 'ig' ORDER BY `amount` ASC");
if($_GET['p']=='1')$q = mysql_query("SELECT * FROM `brand`='sv' AND `packages` WHERE `type` = 'likes' AND `premium` = '1' AND socialmedia = 'ig' ORDER BY `amount` ASC");
if($_GET['p']=='2')$q = mysql_query("SELECT * FROM `brand`='sv' AND `packages` WHERE `type` = 'likes' AND `premium` = '2' AND socialmedia = 'ig' ORDER BY `amount` ASC");

$minPrice = 0;
$maxPrice = 1000;
$countPackage = mysql_num_rows($q);

while($info = mysql_fetch_array($q)){

$info['price'] = explode('.', $info['price']);

$mainprice = $info['price'][0];
$decimal = $info['price'][1];

if($info['id']==21)$decimalinc = ".{$decimal}";
if($info['id'] == 8){$bestPackageClass = 'best'; $popular_class = 'popular';}else{$bestPackageClass = ''; $popular_class = '';}


if($info['amount'] > 9000) {
	$amount = formatNumber($info['amount']);
}else{
	$amount = $info['amount'];
}

$packages2 .= '<div class="card-package '.$popular_class.'">
                                <div class="quantity">'.$amount.'</div>
                                <div class="label">Likes</div>
                                <div class="seperator"></div>
                                <div class="amount"><span class="currency">'.$locas[$loc]['currencysign'].'</span><span class="value">'.$mainprice.$decimalinc.'</span></div>
                                <a href="'.$loclink.'/{hreforder}/{hrefchoose}/'.$info['id'].'" class="btn btn-primary">Buy Now</a>
                            </div>';

$mobilepackages .= '			

<div class="newpackage dshadow '.$bestPackageClass.'" onclick="location.href = \''.$loclink.'/{hreforder}/{hrefchoose}/'.$info['id'].'\';">
    
    <div class="amount">
    
     '.$amount.'
      
    </div>
    
    <div class="typeofpackage">{packagetype}</div>
    
    <div class="price" style="
"><sup class="sign">'.$locas[$loc]['currencysign'].'</sup><div class="mainprice">'.$mainprice.'</div><sup class="decimal">'.$decimalinc.'</sup></div>


    
    
    <div class="ctabutton">
      <a href="'.$loclink.'/{hreforder}/{hrefchoose}/'.$info['id'].'">{packagebuynow}</a>
      
    </div>
    
    
    
  </div>

';



// max price
if($info['price'][0].'.'. $info['price'][1] > $maxPrice){$maxPrice = $info['price'][0].'.'. $info['price'][1];}

// min price
if($minPrice > $info['price'][0].'.'. $info['price'][1]){$minPrice = $info['price'][0].'.'. $info['price'][1];}

$schemaArr[] = ['@type' => 'Offer','price'=>$info['price'][0].'.'. $info['price'][1],'itemOffered'=>['name'=>$info['amount'] .' Likes']];

unset($decimalinc);

}
$schemaArr = json_encode($schemaArr);

$qp = mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `type` = 'likes' AND `premium` = '1' AND socialmedia = 'ig' ORDER BY `amount` ASC;");

while($info = mysql_fetch_array($qp)){

		$info['price'] = explode('.', $info['price']);

		$mainprice = $info['price'][0];
		$decimal = $info['price'][1];

		if($info['id']==80)$decimalinc = ".{$decimal}";
		if($info['id'] == 69){$bestPackageClass = 'best'; $popular_class = 'popular';}else{$bestPackageClass = ''; $popular_class = '';}


		if($info['amount'] > 9000) {
			$amount = formatNumber($info['amount']);
		}else{
			$amount = $info['amount'];
		}

		$packages3 .= '<div class="card-package '.$popular_class.'">
										<div class="quantity">'.$amount.'</div>
										<div class="label">Likes</div>
										<div class="seperator"></div>
										<div class="amount"><span class="currency">'.$locas[$loc]['currencysign'].'</span><span class="value">'.$mainprice.$decimalinc.'</span></div>
										<a href="'.$loclink.'/{hreforder}/{hrefchoose}/'.$info['id'].'" class="btn btn-primary">Buy Now</a>
									</div>';

		$mobilepackages2 .= '			

		<div class="newpackage dshadow '.$bestPackageClass.'" onclick="location.href = \''.$loclink.'/{hreforder}/{hrefchoose}/'.$info['id'].'\';">

			<div class="amount">

			 '.$amount.'

			</div>

			<div class="typeofpackage">{packagetype}</div>

			<div class="price" style="
		"><sup class="sign">'.$locas[$loc]['currencysign'].'</sup><div class="mainprice">'.$mainprice.'</div><sup class="decimal">'.$decimalinc.'</sup></div>




			<div class="ctabutton">
			  <a href="'.$loclink.'/{hreforder}/{hrefchoose}/'.$info['id'].'">{packagebuynow}</a>

			</div>



		  </div>

		';
	
	
	
		// max price
		if($info['price'][0].'.'. $info['price'][1] > $maxPrice){$maxPrice = $info['price'][0].'.'. $info['price'][1];}
		
		// min price
		if($minPrice > $info['price'][0].'.'. $info['price'][1]){$minPrice = $info['price'][0].'.'. $info['price'][1];}
				
		unset($decimalinc);
	
}

$q = mysql_query("SELECT * FROM `reviews` WHERE `brand`='sv' AND `type` = 'ig-likes' AND `approved` = '1' AND `country` = '{$locas[$loc]['sdb']}' ORDER BY `id` DESC");
$reviewnumrows = mysql_num_rows($q);
while($rinfo = mysql_fetch_array($q)){

for ($x = 1; $x <= $rinfo['stars']; $x++) {$rating .= '★';}

 $reviews .= '


			<div class="test">
					<div class="tratingbg">★★★★★</div>
					 <h4 class="title">'.ucfirst($rinfo['title']).'</h4>

				    <p class="review">'.ucfirst($rinfo['review']).'</p>
				<div><span class="name">'.$rinfo['name'].'</span></div>



 			</div>

			';
/*
 $reviews .= '


			<div class="test" itemprop="review" itemscope itemtype="http://schema.org/Review">
					<div class="tratingbg">'.$rating.'</div>
					 <h3 class="title" itemprop="name">'.ucfirst($rinfo['title']).'</h3>

				    <meta itemprop="datePublished" content="'.gmdate("Y-m-d",$rinfo['timeo']).'" style="display:none;">
				    <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" style="display:none">
				      <meta itemprop="worstRating" content = "1">
				      <span itemprop="ratingValue">'.$rinfo['stars'].'</span>/
				      <span itemprop="bestRating">5</span>stars
				    </div>
				    <p class="review" itemprop="description">'.ucfirst($rinfo['review']).'</p>
				<div itemprop="author"><span class="name" itemprop="author">'.$rinfo['name'].'</span></div>



 			</div>

			';
*/

unset($rating);

}




if(!empty($_POST['submit'])){

	$stars = addslashes($_POST['stars']);
	$name = addslashes($_POST['name']);
	$email = addslashes($_POST['email']);
	$review = addslashes($_POST['review']);
	$city = addslashes($_POST['city']);
	$ordernumber = addslashes($_POST['ordernumber']);
	$time = time();


	//if((empty($stars))||(empty($name))||(empty($email))||(empty($review))||(empty($ordernumber)))$failed='1';


if(empty($failed)){
	$insertq = mysql_query("INSERT INTO `reviews`
		SET 
		`brand`='sv',
		`type` = 'likes', 
		`stars` = '$stars', 
		`name` = '$name', 
		`email` = '$email', 
		`review` = '$review', 
		`timeo` = '$time', 
		`location` = '$city', 
		`ordernumber` = '$ordernumber' 
		");


	if($insertq)$reviewmessage = '<div class="emailsuccess">Your review has been submitted. Thank you!</div>';


}

}

if(!empty($_COOKIE['discount'])){include('detectdiscount.php');}



$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{discountnotif}', $discountnotif, $tpl);
$tpl = str_replace('{mobilepackages}', $mobilepackages, $tpl);
$tpl = str_replace('{mobilepackages2}', $mobilepackages2, $tpl);
$tpl = str_replace('{packages3}', $packages3, $tpl);
$tpl = str_replace('{packages2}', $packages2, $tpl);
$tpl = str_replace('{reviewnumrows}', $reviewnumrows, $tpl);
$tpl = str_replace('{reviewmessage}', $reviewmessage, $tpl);
$tpl = str_replace('{reviews}', $reviews, $tpl);
$tpl = str_replace('{contentlanguage}', $locas[$loc]['contentlanguage'], $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{currencypp}', $locas[$loc]['currencysign'], $tpl);
$tpl = str_replace('{minprice}', $minPrice, $tpl);
$tpl = str_replace('{maxprice}', $maxPrice, $tpl);
$tpl = str_replace('{packageCount}', $countPackage, $tpl);
$tpl = str_replace('{schema_arr}', $schemaArr, $tpl);
$tpl = str_replace('{loclocation}', $loclinkforward , $tpl);
$tpl = str_replace('{freelikesmsg}', $freelikesmsg, $tpl);
$tpl = str_replace('{flagShowCall}', $flagShowCall , $tpl);

$guidelineUk = '<div class="guideline" {styleUkVersion}>
				<div class="guide">
					<div class="seqNum">1</div>
					<div class="para">{3-step1}</div>
				</div>
				<div class="guide">
					<div class="seqNum">2</div>
					<div class="para">{3-step2}</div>
				</div>
				<div class="guide">
					<div class="seqNum">3</div>
					<div class="para">{3-step3}</div>
				</div>
				<div class="guide">
					<div class="seqNum">4</div>
					<div class="para">{3-step4}</div>
				</div>
				</div>
				<div class="purchase-content" {styleUkVersion}>
				<p>{3-step-desc}</p>
				</div>';
if($loc == "uk"){
	$guidelineUk = "";
	// $styleUkVersion = "style='display:none;'";
}
$tpl = str_replace('{guidelineUk}', $guidelineUk, $tpl);
$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND (`country` = '{$locas[$loc]['sdb']}' AND `page` IN ('buy-instagram-likes','global'))");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
if($cinfo['name']=='canonical')$htmlcanonical = $cinfo['content'];}

//$tpl = str_replace('<link rel="alternate" hreflang="'.$locas[$loc]['contentlanguage'].'" href="'.$htmlcanonical.'" />', '', $tpl);


echo $tpl;
?>