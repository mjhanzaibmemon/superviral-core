<?php

include('db.php');

///

$loc5 = $_SERVER['SERVER_NAME'];
$loc5 = str_replace('superviral.','',$loc5);
$loc5 = array_shift((explode('.', $_SERVER['HTTP_HOST'])));



if(empty($loc5))$loc5 = '';
if($loc5=='superviral')$loc5 = '';
if($loc5=='www')$loc5 = '';

if(!empty($loc5))$loc5 = $loc5.'.';

header('Access-Control-Allow-Origin: https://'.$loc5.'superviral.io');

///



$ordersession = addslashes($_GET['ordersession']);
$addpackage = addslashes($_GET['addpackage']);
$addautolikes = addslashes($_GET['addautolikes']);

if(empty($ordersession))die('Invalid session');

$validatesessionq = mysql_query("SELECT * FROM `order_session` WHERE `order_session` = '$ordersession' LIMIT 1");

if(mysql_num_rows($validatesessionq)!==1){die('Invalid session');}



//VALIDATED now search for ordersession
$info = mysql_fetch_array($validatesessionq);


if(!empty($addpackage)){

		$packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$info['packageid']}' LIMIT 1"));

			$discountamount = round($packageinfo['amount'] * 0.50);
			$discountoriginal = number_format(round($packageinfo['price'] * 0.50,2),2);
			$discountactual = number_format(round($discountoriginal * 0.75,2),2);

		if($addpackage==2){//ADD

			




			$upselladd = $discountamount.'###'.$discountactual;

			mysql_query("UPDATE `order_session` SET `upsell` = '$upselladd' WHERE `order_session` = '{$info['order_session']}' LIMIT 1");


     		$tpl = '<div class="thewidth">

                            <div class="thewidthleft"><span class="package">{discountpopup}<div class="tickadded"><span class="tick"></span><span style="">{upselladded}</span></div></span></div>
                            <div class="thewidthright">'.$locas[$loc]['currencysign'].$discountactual.$locas[$loc]['currencyend'].'<br>
                            <a class="remove" onclick="addpackage(\'1\',\''.$info['order_session'].'\');  return false;" href="#">{upsellremove}</a></div>
                  </div>'; 

                  $totalpriceshow = $packageinfo['price'] + $discountactual;
                  $tpl .= '~~~{maincta}';

			}


		if($addpackage==1){//REMOVE

			mysql_query("UPDATE `order_session` SET `upsell` = '' WHERE `order_session` = '{$info['order_session']}' LIMIT 1");

			$tpl = '<div class="thewidth">

                            <div class="thewidthleft"><span class="package">{discountpopup}</span></div>
                            <div class="thewidthright"><a class="btn greenbtn" onclick="addpackage(\'2\',\''.$info['order_session'].'\');  return false;" href="#">{discountbtn}</a></div>
                  </div>'; 
                $totalpriceshow = $packageinfo['price'];
			$tpl .= '~~~{maincta}';



		}	



}



if(!empty($addautolikes)){


			if($addautolikes==2){//ADD



//
			$auto_likes = array(
			    'likes_per_post' => '50',
			    'max_per_day' => '4',
			    'price' => '0.00',
			    'original_price' => '10.94',
			    'save' => '50%'
			);


			$discountamount2 = $auto_likes['likes_per_post'];
			$discountoriginal2 = $auto_likes['original_price'];
			$discountactual2 = $auto_likes['price'];

			$upselladd_autolikes = $discountamount2.'###'.$discountactual2.'###'.$auto_likes['likes_per_post'].'###'.$auto_likes['max_per_day'].'###'.$auto_likes['price'].'###'.$auto_likes['original_price'].'###'.$auto_likes['save'];


			$upsell_autolikesdb = explode('###', $upselladd_autolikes);
			$upsell_autolikesdbprice = $upsell_autolikesdb[1];


				$tpl  = '<div class="thewidth">

	                <div class="thewidthleft">
	                <span class="package">
	                <div class="bftag">{altag1}</div>
	                {altag2}
	                <div class="tickadded"><span class="tick"></span><span style="">{upselladded}</span></div>
	                </span>
	                </div>

	                <div class="thewidthright">'.$currency.$upsell_autolikesdbprice.$locas[$loc]['currencyend'].'<br><a class="remove" onclick="addautolikes(\'1\',\''.$info['order_session'].'\'); return false;" href="#">{upsellremove}</a></div>
	         </div>';

	
			mysql_query("UPDATE `order_session` SET `upsell_autolikes` = '$upselladd_autolikes' WHERE `order_session` = '{$info['order_session']}' LIMIT 1");




			}
			




			if($addautolikes==1){//REMOVE


				$upsell_autolikesdb = explode('###', $info['upsell_autolikes']);
				$upsell_autolikesdbprice = $upsell_autolikesdb[1];

			$tpl  = '<div class="thewidth">

                            <div class="thewidthleft">
                            <span class="package"><div class="bftag">{altag1}</div>
	                				{altag2}</span>
                                <div class="autolikesdesc" {autolikesdesc}>

                                    <div class="tickadded"><span class="tick"></span><span>{alpoint3}</span></div>
                                    <div class="tickadded"><span class="tick"></span><span>{alpoint2}</span></div>
                                    <div class="tickadded"><span class="tick"></span><span>{alpoint4}</span></div>
                                    <div class="tickadded"><span class="tick"></span><span>{alpoint1}</span></div>
                                </div>
                             </span>
                            </div>
                            <div class="thewidthright">
                            <a class="btn greenbtn" onclick="addautolikes(\'2\',\''.$info['order_session'].'\'); return false;" href="#">{aladdfor}</a>

                            </div>
                     </div>';


			mysql_query("UPDATE `order_session` SET `upsell_autolikes` = '' WHERE `order_session` = '{$info['order_session']}' LIMIT 1");



			}




}





$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order2') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
while($cinfo = mysql_fetch_array($contentq)){

$foundcontent=0;

if($cinfo['name']=='additionalfollowers')

	{

		$cinfo['content'] = str_replace('ucwords($packagetype)',ucwords($packageinfo['type']),$cinfo['content']);
		$cinfo['content'] = str_replace('$discountamount',$discountamount,$cinfo['content']);
		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
		$foundcontent = 1;

	}

if($cinfo['name']=='discountactual')

	{

		$cinfo['content'] = str_replace('$discountactual',$discountactual,$cinfo['content']);
		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
		$foundcontent = 1;
	}


if($cinfo['name']=='discountpopup')

	{

		$cinfo['content'] = str_replace('ucwords($packagetype)',ucwords($packageinfo['type']),$cinfo['content']);
		$cinfo['content'] = str_replace('$discountamount',$discountamount,$cinfo['content']);
		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
		$foundcontent = 1;

	}

if($cinfo['name']=='discountbtn')

	{

		$cinfo['content'] = str_replace('$currency',$currency,$cinfo['content']);
		$cinfo['content'] = str_replace('$locas[$loc][\'currencyend\']',$locas[$loc]['currencyend'],$cinfo['content']);
		$cinfo['content'] = str_replace('$discountactual',$discountactual,$cinfo['content']);
		$cinfo['content'] = str_replace('$discountoriginal',$discountoriginal,$cinfo['content']);



		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
		$foundcontent = 1;
	}


if($cinfo['name']=='aladdfor')

	{



		$cinfo['content'] = str_replace('$currency',$currency,$cinfo['content']);
		$cinfo['content'] = str_replace('$locas[$loc][\'currencyend\']',$locas[$loc]['currencyend'],$cinfo['content']);
		$cinfo['content'] = str_replace('$discountactual',number_format(round($discountactual2,2),2),$cinfo['content']);
		$cinfo['content'] = str_replace('$discountoriginal',number_format(round($discountoriginal2,2),2),$cinfo['content']);
		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
		$foundcontent = 1;
	}

if($cinfo['name']=='maincta')

	{


		$cinfo['content'] = str_replace('$price',$totalpriceshow,$cinfo['content']);
		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
		$foundcontent = 1;
	}


if($foundcontent==0)

	{

		$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

	}

}

echo $tpl;


?>

