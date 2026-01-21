<?php


include('adminheader.php');

date_default_timezone_set('Europe/London');

function ago($time)
{$periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");
   $now = time();
       $difference     = $now - $time;
       $tense         = 'ago';
   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }
   $difference = round($difference);
   if($difference != 1) {
       $periods[$j].= "s";
   }   return "$difference $periods[$j] ago";}

$addedq = addslashes($_GET['ipaddress']);
$showresults1 = 1;
//if(!empty($addedq)){$showresults1 = 1;$addedq = " WHERE `ipaddress` = '$addedq' ";}else{$showresults1 = 0;$showresults = array();}

if(!empty($_GET['checked'])){

$checked = addslashes($_GET['checked']);

mysql_query("UPDATE `payment_logs_checkout` SET `checked` = '1' WHERE `id` = '$checked' LIMIT 1");

header('Location: https://superviral.io/admin/payment-log.php');die;

}

$q = mysql_query("SELECT * FROM `payment_logs_checkout` WHERE `checked` = '0' ORDER BY `id` DESC ");

while($info = mysql_fetch_array($q)){

$allerrors = explode('###', $info['msg']);

if($showresults1 == 1){

$error1 = unserialize($allerrors[0]);
$error2 = unserialize($allerrors[1]);
$error3 = unserialize($allerrors[2]);
$error4 = unserialize($allerrors[3]);

if(!empty($error1)){foreach($error1 as $pererror){$error1a .= $pererror.'<br>';}$error1a = '<font color="red">Error 1:</font> '.$error1a;}
if(!empty($error2)){foreach($error2 as $pererror){$error2a .= $pererror.'<br>';}$error2a = '<font color="red">Error 2:</font> '.$error2a;}
if(!empty($error3)){foreach($error3 as $pererror){$error3a .= $pererror.'<br>';}$error3a = '<font color="red">Error 3:</font> '.$error3a;}
if(!empty($error4)){foreach($error4 as $pererror){$error4a .= $pererror.'<br>';}$error4a = '<font color="red">Error 4:</font> '.$error4a;}

$order_session = unserialize($info['order_session']);

foreach ($order_session as $tkey => $tvalue) {

$mainorder_session .= $tkey.': '.$tvalue.'<br>';

}

/*$results .= '   <div class="box23">
<table class="articles">

<tr>

    <td>
    ID: '.$info['id'].'<br>
    IP ADDRESS: <a href="?&ipaddress='.$info['ipaddress'].'" >'.$info['ipaddress'].'</a><br>
    Order Session: '.$order_session['order_session'].'<br>'.$mainorder_session.'
    Email address: '.$order_session['emailaddress'].'<br>
    Lastfour: '.$info['lastfour'].'<br>
    CVC: '.$info['cvc'].'<br>
    EXP date: '.$info['expdate'].'<br>
    Payment ID: '.$info['payment_id'].'<br>'.ago($info['added']).' ('.date('l jS \of F Y H:i:s ', $info['added']).')<br>
    URL '.$info['url'].'</td>

</tr>
<tr>

    <td>'.$error1a.$error2a.$error3a.$error4a.$info['message'].'</td>

</tr>
</table></div>';*/


$results .= '   <div class="box23">
<table class="articles">

<tr>

    <td>
    Account ID: '.$info['account_id'].'<br>
    Card ID :'.$info['card_id'].'
    <a href="?checked='.$info['id'].'" style="float:right;">Checked</a></td>
    
</tr>
<tr>

    <td>'.$error1a.$error2a.$error3a.$error4a.$info['message'].'</td>

</tr>
</table></div>';


unset($mainorder_session);
unset($order_session);
unset($error1a);
unset($error2a);
unset($error3a);
unset($error4a);

}

else

{

$ipaddress = $info['ipaddress'];

$showresults[] = $ipaddress;


}

}

if($showresults1==0){

  $showresults = array_unique($showresults);

  foreach($showresults as $eachresult){


    $results .='<div class="box23"><a href="?&ipaddress='.$eachresult.'" >'.$eachresult.'</a></div>';


  }

}

?>
<!DOCTYPE html>
<head>
<title>Payment log</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/orderform.css">

<style type="text/css">

.articles a{text-decoration: underline;color:blue;}

.box23{margin: 66px auto;
    width: 950px;
    background: #fff;
    border-radius: 5px;text-align:left;padding:15px;}

h1{text-align: left;max-width:100%;}

.label{margin-top:35px;}

.container div input, .selectric, .input, .btn {padding: 13px;font-size: 14px;}

.btn{width:100px;text-align:center;}

html{overflow-x: hidden;}

.cke_reset_all{background:#f7f7f7!important;}

.articles{width:100%;}
.articles tr td{padding: 30px 10px;vertical-align: top;}
.articles tr:first-child td{background:#f1f1f1;font-weight:bold;padding:10px;}

.status{ font-weight: bold;
    height: 23px;
    width: 55px;
    padding: 5px;font-size:15px;text-align:center;border-radius:3px;}

    .btn{margin: 0!important;}


.adminmenu{display:inline-block;background-color:white;border-top:1px solid #ccc;width:100%;}
.adminmenu a{float:left;padding:15px;}

 .reportmessage{
    width: 100%;
    height: 120px;box-sizing:border-box;
    margin: 0px;
    margin-bottom: 20px;
    padding:10px;font-family:'Open Sans';

    overflow: hidden;
    outline: none;
    resize:none;
}

   .btn{width: initial;}

   .defectorder{}

   .defectorder a{text-decoration: underline;color:blue;}

   .defectorder .sdiv{    margin-bottom: 15px;
    padding: 9px;
    background-color: #f5f7fe;
    border-radius: 5px;}

    .nlbtn{    float: left;
    margin-right: 20px!important;
    width: 176px!important;
    margin-bottom: 10px!important;}

<?=$styles?>

</style>
</head>

  <body onload="init();">


    <?=$header?>


  



                <?=$results?>







  </body>
</html>