<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db=1;
include('header.php');

$tpl = file_get_contents('buy-instagram-views-test.html');

$q = mysql_query("SELECT * FROM `packages` WHERE `type` = 'views' lIMIT 5");
while($info = mysql_fetch_array($q)){

$info['price'] = explode('.', $info['price']);

$mainprice = $info['price'][0];
$decimal = $info['price'][1];

$packages .= '			<div class="item dshadow"><div class="pricecut">30% OFF NOW</div>
				              <div class="amount">'.$info['amount'].'<br><span class="label">VIEWS</span></div>

				              <div class="price color3"><sup class="sign">£</sup><div class="mainprice">'.$mainprice.'</div><sup class="decimal">.'.$decimal.'</sup></div>
				              
				              <ul class="listctn">

				              		<li><span class="tick"></span>High-Quality Views</li>
				              		<li><span class="tick"></span>Instant Delivery</li>
				              		<li><span class="tick"></span>No Password Required</li>
				              		<li><span class="tick"></span>100% satisfaction guarantee</li>

				              </ul>
				              <div class="buyctn"><a title="'.$info['amount'].' followers" class="btn dshadow color3" href="/order/choose/'.$info['id'].'">BUY NOW</a></div>
			            </div>';

}


$q = mysql_query("SELECT * FROM `reviews` WHERE `type` = 'views' AND `approved` = '1' ORDER BY `id` DESC");
$reviewnumrows = mysql_num_rows($q);
while($rinfo = mysql_fetch_array($q)){

for ($x = 1; $x <= $rinfo['stars']; $x++) {$rating .= '★';}

 $reviews .= '


			<div class="test">
					<div class="tratingbg">'.$rating.'</div>
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

$time = time() - (rand('86400','777,600'));

	//if((empty($stars))||(empty($name))||(empty($email))||(empty($review))||(empty($ordernumber)))$failed='1';

if(empty($failed)){
	mysql_query("INSERT INTO `reviews`
		SET 
		`type` = 'views', 
		`stars` = '$stars', 
		`name` = '$name', 
		`email` = '$email', 
		`review` = '$review', 
		`timeo` = '$time', 
		`location` = '$city', 
		`ordernumber` = '$ordernumber' 
		");

	$reviewmessage = '<div class="emailsuccess">Your review has been submitted. Thank you!</div>';
}

}

$contentq = mysql_query("SELECT * FROM `content` WHERE `country` = 'uk' AND `page` = 'buy-instagram-views' ");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);
$tpl = str_replace('{reviewnumrows}', $reviewnumrows, $tpl);
$tpl = str_replace('{reviewmessage}', $reviewmessage, $tpl);
$tpl = str_replace('{reviews}', $reviews, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);

echo $tpl;
?>