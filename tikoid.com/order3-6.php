<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$db=1;
include('header.php');
include('ordercontrol.php');

if($_GET['existinguser']=='true')$redirectq12 = '?&existinguser=true';

//if($_SERVER['HTTP_X_FORWARDED_FOR']=='212.159.178.222')$info['order_session'] = '2aa0a9f045f4a1a9ce0c96155273defb';

$stylecss = '<style>.lds-dual-ring {
  display: inline-block;
  width: 180px;
  height: 180px;
    box-sizing:border-box;
margin-top:130px;
}
.lds-dual-ring:after {
  box-sizing:border-box;
  content: " ";
  display: block;
  width: 180px;
  height: 180px;
  border-radius: 50%;
  border: 8px solid #000;
  border-color: #000 transparent #000 transparent;
  animation: lds-dual-ring 1.2s linear infinite;
}
@keyframes lds-dual-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
';




$tpl = file_get_contents('order-template.html');

$findorderq = mysql_query("SELECT * FROM `orders` WHERE `order_session` = '{$info['order_session']}' AND `brand` = 'to' ORDER BY `id` DESC LIMIT 1");

if(mysql_num_rows($findorderq)=='1'){ //ORDER HAS BEEN FOUND



$fetchinfo = mysql_fetch_array($findorderq);


$fetchinfo['price'] = sprintf('%.2f', $fetchinfo['price'] / 100);

$fetchpackageq1 = mysql_query("SELECT * FROM `packages` WHERE `id` = '{$fetchinfo['packageid']}' AND `brand` = 'to' LIMIT 1");
$fetchpackageinfo1 = mysql_fetch_array($fetchpackageq1);

if($fetchinfo['recordga']=='0'){


    mysql_query("UPDATE `orders` SET `recordga` = '1' WHERE `id` = '{$fetchinfo['id']}' AND `brand` = 'to' LIMIT 1");

    $script = '<script async src="https://www.googletagmanager.com/gtag/js?id=G-TSK2L3NYYQ"></script>
    <script>
    window.dataLayer = window.dataLayer || [];            

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag(\'js\', new Date());           

    gtag(\'config\', \'G-MJGT8BN6\');

    (function(w, d, s, l, i) {
      w[l] = w[l] || [];
      w[l].push({
          \'gtm.start\': new Date().getTime(),
          event: \'gtm.js\'
      });
      var f = d.getElementsByTagName(s)[0],
          j = d.createElement(s),
          dl = l != \'dataLayer\' ? \'&l=\' + l : \'\';
      j.async = true;
      j.src =
          \'https://www.googletagmanager.com/gtm.js?id=\' + i + dl;
      f.parentNode.insertBefore(j, f);
    })(window, document, \'script\', \'dataLayer\', \'GTM-MJGT8BN6\');
    </script>
    <noscript><iframe src=\'https://www.googletagmanager.com/ns.html?id=GTM-MJGT8BN6\' height=\'0\' width=\'0\'
      style=\'display:none;visibility:hidden\'></iframe></noscript>';

    $Tid = $fetchinfo['id'];
    $Price = $fetchinfo['price'];
    $curr = $locas[$loc]['currencypp'];

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
                          affiliation: "Tikoid",
                          coupon: "Test",
                          discount: 0.00,
                          index: 0,
                          item_brand: "Tikoid",
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
  
  $script .= "<script>


  ".$recordga."
  
  
    setTimeout(function(){
              window.location.href = '/order/finish/".$redirectq12."';
           }, 800);
  
  </script>";

$tpl = str_replace('{body}', $stylecss.'

  <div style="width:100%;height:100%;text-align:center">
  <div class="message" style="display: inline-block;
    margin-top: 60px;
    width: 360px;">Payment complete! You\'ll be redirected in a couple of seconds.</div>
 <br> <div class="lds-dual-ring"></div></div>'.$script, $tpl);



}else{

//NO ORDER FOUND + ONLY REFRESH THE PAGE

$tpl = str_replace('{body}', $stylecss.'<div style="width:100%;height:100%;text-align:center">
<div class="message" style="display: inline-block;
    margin-top: 60px;
    width: 360px;">Payment complete! You\'ll be redirected in a couple of seconds.</div>
 <br> <div class="lds-dual-ring"></div></div><meta http-equiv="refresh" content="3; URL=/order/payment-processing/'.$redirectq12.'">'.$script, $tpl);


}









$tpl = str_replace('class="backto"', 'class="backto" style="display:none;"', $tpl);

// $contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = 'ww' AND `page` = 'order3-processing') OR (`country` = 'ww' AND `page` = 'global') AND `brand` = 'to'");
// while($cinfo = mysql_fetch_array($contentq)){

//   $tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);

// }


echo $tpl;



?>
