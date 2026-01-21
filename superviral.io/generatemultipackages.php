<?php

include('header.php');

die;

$premium = 2;
//$premium = 1;
//$premium = 2;

$q = mysql_query("SELECT * FROM `packages` WHERE `type` = 'followers' AND `premium` = '$premium' ORDER BY `amount` ASC");


while($info = mysql_fetch_array($q)){

$info['price'] = explode('.', $info['price']);

$mainprice = $info['price'][0];
$decimal = $info['price'][1];

//if($info['id']==1)$decimalinc = '<sup class="decimal">.'.$decimal.'</sup>';




$mobilepackages1 .= '			

<div class="newpackage dshadow" onclick="location.href = \'/order/choose/'.$info['id'].'\';">
    
    <div class="amount">
    
     '.$info['amount'].'
      
    </div>
    
    <div class="typeofpackage">FOLLOWERS</div>
    
    <div class="price" style="
"><sup class="sign">'.$locas[$loc]['currencysign'].'</sup><div class="mainprice">'.$mainprice.'</div></div>


    
    
    <div class="ctabutton">
      <a href="/order/choose/'.$info['id'].'">BUY NOW</a>
      
    </div>
    
    
    
  </div>

';


//unset($decimalinc);

}




/////////////////////////////////////////////////////////////////LIKES


$q = mysql_query("SELECT * FROM `packages` WHERE `type` = 'likes' AND `premium` = '$premium' ORDER BY `amount` ASC");


while($info = mysql_fetch_array($q)){

$info['price'] = explode('.', $info['price']);

$mainprice = $info['price'][0];
$decimal = $info['price'][1];

if($info['id']==21)$decimalinc = '<sup class="decimal">.'.$decimal.'</sup>';


$mobilepackages2 .= '			

<div class="newpackage dshadow" onclick="location.href = \'/order/choose/'.$info['id'].'\';">
    
    <div class="amount">
    
     '.$info['amount'].'
      
    </div>
    
    <div class="typeofpackage">LIKES</div>
    
    <div class="price" style="
"><sup class="sign">'.$locas[$loc]['currencysign'].'</sup><div class="mainprice">'.$mainprice.'</div>'.$decimalinc.'</div>


    
    
    <div class="ctabutton">
      <a href="/order/choose/'.$info['id'].'">BUY NOW</a>
      
    </div>
    
    
    
  </div>

';


unset($decimalinc);

}


/////////////////////////////////////////////////////////////////VIEWS

$q = mysql_query("SELECT * FROM `packages` WHERE `type` = 'views' AND `premium` = '$premium' ORDER BY `amount` ASC");


while($info = mysql_fetch_array($q)){

$info['price'] = explode('.', $info['price']);

$mainprice = $info['price'][0];
$decimal = $info['price'][1];

if($info['id']==12)$decimalinc = '<sup class="decimal">.'.$decimal.'</sup>';




$mobilepackages3 .= '			

<div class="newpackage dshadow" onclick="location.href = \'/order/choose/'.$info['id'].'\';">
    
    <div class="amount">
    
     '.$info['amount'].'
      
    </div>
    
    <div class="typeofpackage">VIEWS</div>
    
    <div class="price" style="
"><sup class="sign">'.$locas[$loc]['currencysign'].'</sup><div class="mainprice">'.$mainprice.'</div>'.$decimalinc.'</div>


    
    
    <div class="ctabutton">
      <a href="/order/choose/'.$info['id'].'">BUY NOW</a>
      
    </div>
    
    
    
  </div>

';



unset($decimalinc);

}


echo $mobilepackages1.'











############################################################################################################################################





';
echo $mobilepackages2.'









############################################################################################################################################






';
echo $mobilepackages3.'






############################################################################################################################################












';



?>