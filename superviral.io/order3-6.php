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
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  margin: 0;
  background-color : #fffdff;
  overflow:hidden;
}

.container {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  gap: 10px;
  text-align: center;
  font-size: 21px;
  font-weight: bolder;
  background:none!important;
}

.message {
  margin: 0 auto;
  display: block;
}

.sub-message {
  font-size: 13px;
}

.stepsbarholder {
  display: none;
}

.headerorder .cnwidth {
  display: none;
}

img{
  width:100%;
  max-width: 500px;
  height: auto;
}
@media (max-width:425px){
  img{
    max-width: initial;
  }
  .container{padding:0}
}

</style>
';





$tpl = file_get_contents('order-template.html');

$findorderq = mysql_query("SELECT * FROM `orders` WHERE `brand`='sv' AND `order_session` = '{$info['order_session']}' ORDER BY `id` DESC LIMIT 1");

if(mysql_num_rows($findorderq)=='1'){ //ORDER HAS BEEN FOUND



$fetchinfo = mysql_fetch_array($findorderq);


$fetchinfo['price'] = sprintf('%.2f', $fetchinfo['price'] / 100);

$fetchinfo['recordga']='0';


if($fetchinfo['recordga']=='0'){



$fetchpackageq1 = mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `id` = '{$fetchinfo['packageid']}' LIMIT 1");  	
$fetchpackageinfo1 = mysql_fetch_array($fetchpackageq1);

if($_GET['paymentmethod']=='applepay'){


$paymentmethod = "gtag('event', 'payment', {
  'event_category': 'paymentmethod',
  'event_label': 'applepay'
});
";

}

if($_GET['paymentmethod']=='card'){


$paymentmethod = "gtag('event', 'payment', {
  'event_category': 'paymentmethod',
  'event_label': 'card'
});


";

}

if($fetchpackageinfo1['premium']!=='0'){

$premiumpackagetag = "gtag('event', 'premiumpackage', {
  'event_category': 'ispremium',
  'event_label': 'premium'
});
";

}



mysql_query("UPDATE `orders` SET `recordga` = '1' WHERE `id` = '{$fetchinfo['id']}' LIMIT 1");

$script = '<script async src="https://www.googletagmanager.com/gtag/js?id=G-C18K306XYW"></script>
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag(\'js\', new Date());

              gtag(\'config\', \'G-NH3B6FF\');

            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
              new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
              j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
              \'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
              })(window,document,\'script\',\'dataLayer\',\'GTM-NH3B6FF\');

            </script>
            <noscript><iframe src=\'https://www.googletagmanager.com/ns.html?id=GTM-NH3B6FF\' height=\'0\' width=\'0\'
              style=\'display:none;visibility:hidden\'></iframe></noscript>';


$Tid = $fetchinfo['id'];
$Price = $fetchinfo['price'];
$curr = $locas[$loc]['currencypp'];

if(!empty($_GET['split']) && $_GET['split'] == 'b'){

	$delFee = number_format(round($fetchpackageinfo1['price'] * 0.05,2),2);
	$servTax = number_format(round($fetchpackageinfo1['price'] * 0.05,2),2);

	$Price += $delFee + $servTax;

}

$recordga = 'dataLayer.push({
  ecommerce: null
}); // Clear the previous ecommerce object.
dataLayer.push({
  event: "purchase",
  ecommerce: {
      transaction_id: '. $Tid .',
      value: '. $Price .',
      tax: 0.00,
      shipping: 0.00,
      currency: "'.$curr.'",
      coupon: "Test",
      items: [{
              item_id: "Item_'. $fetchinfo['id'] .'",
              item_name: "'.$fetchinfo['amount']." ".$fetchpackageinfo1['type'].'",
              affiliation: "Superviral",
              coupon: "Test",
              discount: 0.00,
              index: 0,
              item_brand: "Superviral",
              item_category: "'. $fetchpackageinfo1['type'] .'",
              item_category2:"'. $fetchpackageinfo1['type'] .'",
              item_category3:"'. $fetchpackageinfo1['type'] .'",
              item_category4:"'. $fetchpackageinfo1['type'] .'",
              item_category5:"'. $fetchpackageinfo1['type'] .'",
              item_list_id: "related_products",
              item_list_name: "Related Products",
              item_variant: "green",
              location_id: "ChIJIQBpAG2ahYAR_6128GcTUEo",
              price: '. $Price .',
              quantity: 1
          }
      ]
  }
});';



}



$script .= "

<script>

".$recordga.$premiumpackagetag.$paymentmethod."

  setTimeout(function(){
    window.location.href = '/".$loclinkforward.$locas[$loc]['order']."/".$locas[$loc]['order4']."/".$redirectq12."';
  }, 5000);

</script>";


$tpl = str_replace('{body}', $stylecss.'
<div class="container" style="margin-top:-100px;">
<img src="/imgs/orderprocess.gif" alt="Processing GIF" style="">
<span class="message" style="display:none">We are processing your order</span>
<span class="sub-message" style="display:none">Please do not refresh.</span>
</div>'.$script, $tpl);



}else{

  //NO ORDER FOUND + ONLY REFRESH THE PAGE

  $tpl = str_replace('{body}', $stylecss.'<div style="width:100%;height:100%;text-align:center">
  <div class="message" style="display: inline-block;
      margin-top: 60px;
      width: 360px;">{1-desc-1}</div>
  <br> <div class="lds-dual-ring"></div></div><meta http-equiv="refresh" content="3; URL=/'.$loclinkforward.$locas[$loc]['order'].'/'.$locas[$loc]['order3-processing'].'/'.$redirectq12.'">'.$script, $tpl);


}









$tpl = str_replace('class="backto"', 'class="backto" style="display:none;"', $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'order3-processing') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");
while($cinfo = mysql_fetch_array($contentq)){

  $tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

}


echo $tpl;



?>