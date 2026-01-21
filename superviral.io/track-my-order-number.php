<?php

$db=1;
include('header.php');

$id = addslashes($_GET['id']);
$order_session = addslashes($_GET['hash']);
$notextupdate = addslashes($_GET['notextupdate']);

if(empty($id))$id = addslashes($_POST['id']);
if(empty($order_session))$order_session = addslashes($_POST['hash']);

$sendgaevent = "



<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id=UA-41728467-8\"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-41728467-8');


gtag('event', 'Details', {
  'event_category': 'Submit',
  'event_label': 'Contact Number'
});


</script>


";

$therefresh = '


<script>window.top.location.reload();</script>


';

if($notextupdate == ''){

    $orderinfoq = mysql_query("SELECT * FROM `orders` WHERE `brand`='sv' AND `order_session` = '$order_session' AND `id` = '$id' LIMIT 1");
    if(mysql_num_rows($orderinfoq)==0)die('There was an error, please refresh the page and then contact customer support with error code #422.');
    $orderinfo = mysql_fetch_array($orderinfoq);
    
    $userinfoq = mysql_query("SELECT * FROM `users` WHERE `brand`='sv' AND `emailaddress` = '{$orderinfo['emailaddress']}' LIMIT 1");
    if(mysql_num_rows($userinfoq)==0)die('There was an error, please refresh the page and then contact customer support with error code #410.');
    $userinfo = mysql_fetch_array($userinfoq);
}
/////////////////////////////////////////////////


//THIS IS FOR MODE=UPDATE PAGE

if(!empty($_POST['submit'])){

if(!empty($_POST['input'])){

if(substr($_POST['input'], 0, 2 ) == "07")$_POST['input']  = preg_replace('/^(0*44|(?!\+0*44)0*)/', '+44', $_POST['input']);

mysql_query("UPDATE `users` SET `brand`='sv', `contactnumber` = '{$_POST['input']}',`sentsms` = '2' WHERE `id` = '{$userinfo['id']}' LIMIT 1");
mysql_query("UPDATE `orders` SET `brand`='sv', `askednumber` = '2',`contactnumber` = '{$_POST['input']}' WHERE `order_session` = '$order_session' ORDER BY `id` DESC LIMIT 10");


echo $sendgaevent.$therefresh;die;

}
}

//IF TEXT NO UPDATE == TRUE OR SUBMIT IS TRUE AND NO INPUT THEN
if(($notextupdate=='true')||((!empty($_POST['input']))&&(empty($_POST['submit'])))){
  mysql_query("UPDATE `users` SET `brand`='sv', `sentsms` = '1',`contactnumber` = '' WHERE `id` = '{$userinfo['id']}' LIMIT 1");
  mysql_query("UPDATE `orders` SET `brand`='sv', `askednumber` = '1',`contactnumber` = '' WHERE `order_session` = '$order_session' ORDER BY `id` DESC LIMIT 10");
echo $therefresh;die;
}


//if((!empty($userinfo['contactnumber']))&&(empty($orderinfo['contactnumber']))&&($orderinfo['askednumber']=='0'))


////////////////////////////////////////////////////

$thiscontactnumber= $orderinfo['contactnumber'];



$tpl = file_get_contents('track-my-order-number-2.html');

$tpl = str_replace('{thiscontactnumber}', $thiscontactnumber, $tpl);
$tpl = str_replace('{hash}', $_POST['hash'], $tpl);
$tpl = str_replace('{id}',$_POST['id'], $tpl);
$tpl = str_replace('{ordersession}',$order_session, $tpl);
$tpl = str_replace('{id}',$id, $tpl);


use Google\Cloud\Translate\V2\TranslateClient;

if($notenglish==true){

            require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';

            $translate = new TranslateClient(['key' => $googletranslatekey]);

            $result = $translate->translate($tpl, [
                'source' => 'en', 
                'target' => $locas[$loc]['sdb'],
                'format' => 'html'
            ]);

            $tpl = $result['text'];

}



echo $tpl;

?>
