<?php

$discountid = addslashes($_COOKIE['discount']);

$discountq = mysql_query("SELECT * FROM `discounts` WHERE `md5` = '$discountid' AND `brand` = 'to' LIMIT 1");

if(mysql_num_rows($discountq)=='1'){

mysql_query("UPDATE `discounts` SET `views` = `views` + 1 WHERE `md5` = '$discountid' AND `brand` = 'to' LIMIT 1");

$discountinfo = mysql_fetch_array($discountq);


$expirydate = date('D M d Y H:i:s O',$discountinfo['expiry']);

$countdowntimer = '<script> 
var deadline = new Date("'.$expirydate.'").getTime(); 
var x = setInterval(function() { 
var now = new Date().getTime(); 
var t = deadline - now; 
var days = Math.floor(t / (1000 * 60 * 60 * 24)); 
var hours = Math.floor((t%(1000 * 60 * 60 * 24))/(1000 * 60 * 60)); 
var minutes = Math.floor((t % (1000 * 60 * 60)) / (1000 * 60)); 
var seconds = Math.floor((t % (1000 * 60)) / 1000); 
document.getElementById("demo").innerHTML = days + "d "  
+ hours + "h " + minutes + "m " + seconds + "s"; 
    if (t < 0) { 
        clearInterval(x); 
        document.getElementById("demo").innerHTML = "EXPIRED"; 
    } 
}, 1000); 
</script>';

$totalprice1 = $totalprice * "0.{$discountinfo['discountoff']}";
$totalprice = number_format($totalprice - $totalprice1,'2');

//Payment page
$finalprice1 = $finalprice * "0.{$discountinfo['discountoff']}";
$finalprice = number_format($finalprice - $finalprice1,'2');

$discountnotif = $countdowntimer.'

<style>.owl-theme .item .pricecut{display:none;}
.h1desc{display:none;}</style>

<div class="dshadow" style="margin: 10px 27px;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;width:initial;background:#fff;margin-bottom:30px;font-size:18px;font-weight:bold;padding:10px 20px;">'.$discountinfo['title'].'</div>';

$discountnotifcart = $countdowntimer.'<div class="container dshadow" style="padding:10px!important;margin-top:20px;margin-bottom:15px">'.$discountinfo['titlecart'].'</div>';



$discountreview = '<div class="summary thewidth">
                            <div class="thewidthleft"><span class="package">Discount code <i>'.$discountinfo['code'].'</i></span></div>
                            <div class="thewidthright">-'.$discountinfo['discountoff'].'% Off</div>
                    </div>';

                    $discountnotiffinish = '<div style="border:1px solid green;padding:10px;margin-bottom:30px;">Order complete! You\'ve received 10% Off! Remember, you can grow your TikTok account, by an UNLIMITED AMOUNT before this discount expires. Take full advantage! This discount is for special customers Only.<br>
                    <a class="btn dshadow color3" style="width:initial;display:inline-block;margin:0!important;margin-top:20px!important" target="_BLANK" href="/buy-tiktok-followers/" title="Buy TikTok Followers">BUY MORE FOLLOWERS »</a>
                    </div>';

}


?>