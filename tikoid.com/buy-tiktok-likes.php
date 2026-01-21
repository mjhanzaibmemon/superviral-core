<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db=1;
include('header.php');

$tpl = file_get_contents('buy-tiktok-likes.html');
//if($_GET['rabban']=='true')$tpl = file_get_contents('buy-template-3-test2.html');

$q = mysql_query("SELECT * FROM `packages` WHERE `type` = 'likes' AND `brand` = 'to' ORDER BY `amount` ASC");
while($info = mysql_fetch_array($q)){
	
$info['price'] = explode('.', $info['price']);

$mainprice = $info['price'][0];
$decimal = $info['price'][1];

$packages .= '			<div class="item dshadow">
				              <div class="amount">'.$info['amount'].'<br><span class="label">LIKES</span></div>

				              <div class="price color5"><sup class="sign">'.$currency.'</sup><div class="mainprice">'.$mainprice.'</div><sup class="decimal">.'.$decimal.'</sup></div>
				              
				              <ul class="listctn">

				              		<li><span class="tick"></span>Real + Premium Quality Likes</li>
				              		<li><span class="tick"></span>Optionally split likes</li>
				              		<li><span class="tick"></span>Instant Delivery</li>
				              		<li><span class="tick"></span>24/7 Customer Care</li>
				              		<li><span class="tick"></span>100% Satisfaction Guarantee</li>

				              </ul>
				              <div class="buyctn"><a title="'.$info['amount'].' likes" class="btn dshadow color4" href="/order/choose/'.$info['id'].'">Buy Now</a></div>
			            </div>';

}


$q = mysql_query("SELECT * FROM `reviews` WHERE `type` = 'likes' AND `approved` = '1' AND `brand` = 'to'  ORDER BY `id` DESC");
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
		`type` = 'likes', 
		`stars` = '$stars', 
		`name` = '$name', 
		`email` = '$email', 
		`review` = '$review', 
		`timeo` = '$time', 
		`location` = '$city', 
		`ordernumber` = '$ordernumber',
		`brand` = 'to'  
		");


	if($insertq)$reviewmessage = '<div class="emailsuccess">Your review has been submitted. Thank you!</div>';


}

}
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);
$tpl = str_replace('{reviewnumrows}', $reviewnumrows, $tpl);
$tpl = str_replace('{reviewmessage}', $reviewmessage, $tpl);
$tpl = str_replace('{reviews}', $reviews, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{contentlanguage}', $locas[$loc]['contentlanguage'], $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `country` = '{$locas[$loc]['sdb']}' AND `page` IN ('buy-tiktok-likes', 'global') AND brand = 'to' ");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;
?>