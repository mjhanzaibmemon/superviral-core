<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db=1;
if(empty($_COOKIE['discount'])){$nomaindb=1;}else{$nomaindb=0;}
include('header.php');
// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){

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




/*//IF ITS UK THEN SHOW STATIC PAGE
if(($loc=='uk')&&($_GET['rabban']!=='true')){
$tpl = file_get_contents('uk/buy-instagram-likes.html');
echo $tpl;
die;
}
*/

if($locas[$loc]['sdb'] == 'uk'){
	$tpl = file_get_contents('uk/buy-instagram-comments.html');
	if($_GET['test']=='1')$tpl = file_get_contents('uk/test/buy-instagram-comments.html');
	if($_GET['split'] == 'b')
        $tpl = file_get_contents('uk/split-test/buy-instagram-comments-b.html');
}else{
	$tpl = file_get_contents('us/buy-instagram-comments.html');
	if($_GET['test']=='1')$tpl = file_get_contents('us/test/buy-instagram-comments.html');
	
	if($_GET['split'] == 'b'){
		$tpl = file_get_contents('us/split-test/buy-instagram-comments-b.html');
		$ipinfo = getUserIpInfo($ipaddress);
		$flagShowCall = "";
		if(!empty($ipinfo['country'])){
		
			$flagShowCall = 'flagShow("'.$ipinfo['country'].'")';
		}

	}
        
}

// $tpl = file_get_contents('buy-instagram-comments.html');


$q = mysql_query("SELECT * FROM `packages` WHERE `type` = 'comments' AND socialmedia = 'ig' ORDER BY `amount` ASC");

$maxPrice = 0;
$minPrice = 1000;
$countPackage = mysql_num_rows($q);

while($info = mysql_fetch_array($q)){

$info['price'] = explode('.', $info['price']);

$mainprice = $info['price'][0];
$decimal = $info['price'][1];

if($info['id']==21)$decimalinc = '.'.$decimal.'';


$packages .= '			<div class="item dshadow">
 				              <div class="amount">'.$info['amount'].'<br><span class="label">{packagetype}</span></div>

 				              <div class="price"><sup class="sign">'.$locas[$loc]['currencysign'].'</sup><div class="mainprice">'.$mainprice.'</div>'.$decimalinc.'<sup class="sign">'.$locas[$loc]['currencyend'].'</sup></div>
				              
 				              <ul class="listctn">

 				              		<li><span class="tick"></span>{packagetick1}</li>
 				              		<li><span class="tick"></span>{packagetick2}</li>
 				              		<li><span class="tick"></span>{packagetick3}</li>
 				              		<li><span class="tick"></span>{packagetick4}</li>
 				              		<li><span class="tick"></span>{packagetick5}</li>
 				              		<li><span class="tick"></span>{packagetick6}</li>

 				              </ul>
 				              <div class="buyctn"><a title="'.$info['amount'].' {titlepackagetype}" class="btn color4" href="/'. $loclinkforward .'{hreforder}/{hrefchoose}/'.$info['id'].'">{packagebuynow}</a></div>

 			            </div>';

if($info['amount'] > 5000) {
	$amount = formatNumber($info['amount']);
}else{
	$amount = $info['amount'];
}

$packages2 .= '<div class="card-package '.$popular_class.'">
                                <div class="quantity">'.$amount.'</div>
                                <div class="label">Comments</div>
                                <div class="seperator"></div>
                                <div class="amount"><span class="currency">'.$locas[$loc]['currencysign'].'</span><span class="value">'.$mainprice.$decimalinc.'</span></div>
                                <a href="'.$loclink.'/{hreforder}/{hrefchoose}/'.$info['id'].'" class="btn btn-primary">Buy Now</a>
                            </div>';

$mobilepackages .= '			

<div class="newpackage dshadow" onclick="location.href = \'/'. $loclinkforward .'{hreforder}/{hrefchoose}/'.$info['id'].'\';">
    
    <div class="amount">
    
     '.$amount.'
      
    </div>
    
    <div class="typeofpackage">{packagetype}</div>
    
    <div class="price" style="
"><sup class="sign">'.$locas[$loc]['currencysign'].'</sup><div class="mainprice">'.$mainprice.'</div>'.$decimalinc.'</div>


    
    
    <div class="ctabutton">
      <a href="/{hreforder}/{hrefchoose}/'.$info['id'].'">{packagebuynow}</a>
      
    </div>
    
    
    
  </div>

';

// max price
if($info['price'][0].'.'. $info['price'][1] > $maxPrice) $maxPrice = $info['price'][0].'.'. $info['price'][1]; 
// min price
if($minPrice > $info['price'][0].'.'. $info['price'][1]) $minPrice = $info['price'][0].'.'. $info['price'][1]; 

$schemaArr[] = ['@type' => 'Offer','price'=>$info['price'][0].'.'. $info['price'][1],'itemOffered'=>['name'=>$info['amount'] .' Comments']];


unset($decimalinc);

}

$schemaArr = json_encode($schemaArr);

$q = mysql_query("SELECT * FROM `reviews` WHERE `type` = 'comments' AND `approved` = '1' AND `country` = '{$locas[$loc]['sdb']}' ORDER BY `id` DESC");
$reviewnumrows = mysql_num_rows($q);
while($rinfo = mysql_fetch_array($q)){

for ($x = 1; $x <= $rinfo['stars']; $x++) {$rating .= '★';}

 $reviews .= '


			<div class="test">
					<div class="tratingbg">★★★★★</div>
					 <h3 class="title">'.ucfirst($rinfo['title']).'</h3>

				    <p class="review">'.ucfirst($rinfo['review']).'</p>
				<div><span class="name">'.$rinfo['name'].'</span></div>



 			</div>

			';


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




if(empty($failed)){
	$insertq = mysql_query("INSERT INTO `reviews`
		SET 
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
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{discountnotif}', $discountnotif, $tpl);
$tpl = str_replace('{mobilepackages}', $mobilepackages, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);$tpl = str_replace('{packages2}', $packages2, $tpl);
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
$tpl = str_replace('{flagShowCall}', $flagShowCall , $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `country` = '{$locas[$loc]['sdb']}' AND `page` IN ('buy-instagram-comments','global')");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;
?>