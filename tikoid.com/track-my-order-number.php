<?php

$db=1;
include('header.php');

$id = addslashes($_GET['id']);
$order_session = addslashes($_GET['hash']);
$notextupdate = addslashes($_GET['notextupdate']);

if(empty($id))$id = addslashes($_POST['id']);
if(empty($order_session))$order_session = addslashes($_POST['hash']);

$therefresh = '<script>window.top.location.reload();</script>';

$orderinfoq = mysql_query("SELECT * FROM `orders` WHERE `order_session` = '$order_session' AND `id` = '$id' AND `brand` = 'to' LIMIT 1");
if(mysql_num_rows($orderinfoq)==0)die('There as an error, please refresh the page and then contact customer support with error code #422.');
$orderinfo = mysql_fetch_array($orderinfoq);

$userinfoq = mysql_query("SELECT * FROM `users` WHERE `emailaddress` = '{$orderinfo['emailaddress']}' AND `brand` = 'to' LIMIT 1");
if(mysql_num_rows($userinfoq)==0)die('There as an error, please refresh the page and then contact customer support with error code #410.');
$userinfo = mysql_fetch_array($userinfoq);


/////////////////////////////////////////////////


//THIS IS FOR MODE=UPDATE PAGE

if(!empty($_POST['submit'])){

if(!empty($_POST['input'])){

mysql_query("UPDATE `users` SET `contactnumber` = '{$_POST['input']}',`sentsms` = '2' WHERE `id` = '{$userinfo['id']}' AND `brand` = 'to' LIMIT 1");
mysql_query("UPDATE `orders` SET `askednumber` = '2',`contactnumber` = '{$_POST['input']}' WHERE `order_session` = '$order_session' AND `brand` = 'to' LIMIT 1");


echo $therefresh;die;

}
}

//IF TEXT NO UPDATE == TRUE OR SUBMIT IS TRUE AND NO INPUT THEN
if(($notextupdate=='true')||((!empty($_POST['input']))&&(empty($_POST['submit'])))){
  mysql_query("UPDATE `users` SET `sentsms` = '1',`contactnumber` = '' WHERE `id` = '{$userinfo['id']}' AND `brand` = 'to' LIMIT 1");
  mysql_query("UPDATE `orders` SET `askednumber` = '1',`contactnumber` = '' WHERE `order_session` = '$order_session' AND `brand` = 'to' LIMIT 1");
echo $therefresh;die;
}

////////////////////////////////////////////////////

$thiscontactnumber= $orderinfo['contactnumber'];


?>
<!DOCTYPE html>
<head>
<title>Tikoid</title>
<meta name="description" content="" />
<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1.0, user-scalable=no">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.min.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">
</head>

  <body>

<style type="text/css">
h1{    text-align: left;
    font-size: 25px;
    width: 100%;
    display: block;
    max-width: 100%;}
h2{text-align: left;font-size:17px;}
p{text-align: left;margin-bottom:30px;color:#525252;font-size:15px;    line-height: 26px;}
ul{    padding: 0;
    list-style: none;}
ul li{text-align: left;
    font-size: 13px;
    position: relative;
    padding-left: 28px;
    margin-bottom: 9px;
    line-height: 26px;}
.tick{    width: 20px;
    height: 20px;
    background: url(/imgs/all-images.png) no-repeat 0 -26px;
    display: block;
    position: absolute;
    left: 0;
    top: 3px;}

.contactnumberupdate{display:inline-block;width:100%;}
.iti{float:left;    width: 100%;margin-bottom:20px;}
.btn{margin-bottom:15px;}
.container div span, .container label{display: initial;}

.nothankyou{    text-decoration: underline;
    width: 100%;
    display: block;
    text-align: center;}
</style>
  <script>
    document.body.className += ' variation-' + window.chosenVariation;
  </script>
<link rel="stylesheet" href="/intlinput/build/css/intlTelInput.css">
<link rel="stylesheet" href="/intlinput/build/css/demo.css">
		<div  class="orderbody" align="center">


			<div class="cnwidth">


				<div class="container tycontainer dshadow">

					<h1>Get Free Updates On This Order</h1>
                    <h2>Enter your number for live updates:</h2>

                    <div class="contactnumberupdate">
      <form method="POST">
      <input type="tel" id="phone1" class="form-control" name="input2" value="<?=$thiscontactnumber?>">
      <input type="hidden" id="output" name="input" value="<?=$thiscontactnumber?>">
      <input type="hidden" name="hash" value="<?=$_POST['hash']?>">
      <input type="hidden" name="id" value="<?=$_POST['id']?>">
      <p style="color:green;font-size:14px;">Your contact number will only be used to send you notifications on your order status and is protected by UK GDPR.</p>
      <input class="btn color3" type="submit" name="submit" value="Text Me Free Updates">
      <a href="?notextupdate=true&hash=<?=$order_session?>&id=<?=$id?>" class="nothankyou">I don't want free text updates</a>
      </form>
                  </div>


				</div>




			</div>

		</div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script src="/intlinput/build/js/intlTelInput.js?68"></script>
  <script>
/*
var input = document.querySelector("#phone");
window.intlTelInput(input, {
  alert( iti.getNumber());
   initialCountry: "auto",
  nationalMode: "true",
  geoIpLookup: function(callback) {
    $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
      var countryCode = (resp && resp.country) ? resp.country : "";
      callback(countryCode);
    });
  },
  utilsScript: "/intlinput/build/js/utils.js",
});
*/




var input = document.querySelector("#phone1"),output = document.querySelector("#output");

var iti = window.intlTelInput(input, {
  nationalMode: true,
  initialCountry: "auto",
    geoIpLookup: function(callback) {
    $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
      var countryCode = (resp && resp.country) ? resp.country : "";
      callback(countryCode);
    });
  },
  utilsScript: "/intlinput/build/js/utils.js" // just for formatting/placeholders etc
});

var handleChange = function() {
  var text = iti.getNumber();
  var textNode = document.createTextNode(text);
  output.innerHTML = "";
  output.appendChild(textNode);
  $(output).val(text);
};



// listen to "keyup", but also "change" to update when the user selects a country
input.addEventListener('change', handleChange);
input.addEventListener('keyup', handleChange);

  </script>

<script async src="https://www.googletagmanager.com/gtag/js?id=UA-41728467-8"></script>

  </body>
</html>