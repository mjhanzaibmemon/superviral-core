<?php

include('adminheader.php');

$orderid123 = addslashes($_POST['id']);
$ordersession = addslashes($_POST['ordersession']);
$pagefrom = addslashes($_POST['pagefrom']);

echo $orderid123.' - '.$ordersession;

if((empty($orderid123))||(empty($ordersession))){die('Error: 43344');}

$findorderq = mysql_query("SELECT * FROM `orders` WHERE `id` = '$orderid123' AND `order_session` = '$ordersession' LIMIT 1");
if(mysql_num_rows($findorderq)=='0'){die('Error: 46ds3');}

$info = mysql_fetch_array($findorderq);

$info['packagetype'] = str_replace('free','',$info['packagetype']);

if($info['packagetype']=='followers'){

$subject = 'Order #'.$info['id'].' paused: Username is not working';

$emailbody = '<p>Hi there,</p>
<br>
<p>We\'re contacting you from Superviral in regards to your order #'.$info['id'].'. According to our systems, we can see that your Instagram username <b>"@'.$info['igusername'].'"</b> is not valid</b>.</p>
<br>
<p>This can happen if:</p>
<br>
<p>- you decide to change your username after while we\'re delivering your order</p>
<p>- you provided us with the wrong username before your order</p>
<br>
<p>Don\'t forget that Instagram usernames don\'t have spaces and the username must be in use by you.
<br><br>
Can you kindly reply to this email with a working username? Also, please ensure that your Instagram profile is on public for 30-days after your order, to ensure that we can send any refills. We want to resolve this issue as soon as possible.
<br><br>
Thank you!
</p>';

}else{

$subject = 'Order #'.$info['id'].' paused: Post Not Found';

if(!empty($info['chooseposts'])){

$chooseposts = explode(' ', $info['chooseposts']);

foreach($chooseposts as $apost){

	if(empty($apost))continue;

	$posts .= '<p>- <a href="https://www.instagram.com/p/'.$apost.'/">https://www.instagram.com/p/'.$apost.'/</a></p>';
}

}



$emailbody = '<p>Hi there,</p>
<br>
<p>We\'re contacting you from Superviral in regards to your order #'.$info['id'].'. According to our systems, we can see that the post(s) you\'ve provided us with from your Instagram username <b>"@'.$info['igusername'].' are invalid:</p>

'.$posts.'

<br>
<p>This can happen if you\'ve removed the post on your Instagram account</p>
<br>
<p>Can you kindly check through the posts you\'ve provided us with and reply to this email with a link to another Instagram post where you would like the order delivered to? We want to resolve this issue as soon as possible.
<br><br>
Thank you!
</p>';


}

$tpl = file_get_contents('../emailtemplate/emailtemplate.html');
$tpl = str_replace('{body}',$emailbody,$tpl);
$now = time();


$tpl = str_replace('{subject}',$subject,$tpl);

include('../crons/emailer.php');
emailnow($info['emailaddress'],'Superviral','support@superviral.io','⏸ '.$subject,$tpl);

$updateorder = mysql_query("UPDATE `orders` SET `defect` = '5',`fulfilled` = '$now',`norefill` = '0' WHERE `id` = '$orderid123' AND `order_session` = '$ordersession' LIMIT 1");

header('Location: orders.php?type='.$pagefrom.'&message=email1&theid='.$orderid.$noorderstate);

?>