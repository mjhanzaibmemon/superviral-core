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

$info['packagetypes'] = str_replace('free','',$info['packagetypes']);

$emailbody = '<p>Hi there,</p>
<br>
<p>We\'re contacting you from Superviral in regards to your order #'.$info['id'].'. According to our systems, we can see that your Instagram username <b>"@'.$info['igusername'].'"</b> is set on <b>private</b>.</p>
<br>
<p>For us to completely deliver the '.$info['amount'].' '.$info['packagetype'].' you\'ve ordered, <b>please set your profile on public</b>. Also, please ensure that you keep your account on public for 30-days after your order, to ensure that we can refill your followers if they drop. <b>Once you\'ve set your account on public, please reply to this email.</b></p>
<br>
<p>If you need help setting your Instagram profile on public, please follow the following instructions:</p>
<br>
<p>On Desktop:
<br><br>
1. Go to Instagram on your computer/laptop and login to your account.<br>
2. Click user icon (resembling a person).<br>
3. Click Privacy and Security.<br>
4. Below Account Privacy, click to check the box next to the public Account.</p>
<br>
<p>On Mobile:
<br><br>
1. Go to your profile, then tap the three lines.<br>
2. Tap Settings with the gear icon.<br>
3. Tap Privacy and Security.<br>
4. Tap Account Privacy then tap to toggle public Account on.</p>
<br>
<p>We hope to resolve this issue as soon as possible.</p>';

$tpl = file_get_contents('../emailtemplate/emailtemplate.html');
$tpl = str_replace('{body}',$emailbody,$tpl);
$now = time();
$subject = 'Order #'.$info['id'].' paused: Your Account is on Private';

$tpl = str_replace('{subject}',$subject,$tpl);

include('../crons/emailer.php');
emailnow($info['emailaddress'],'Superviral','support@superviral.io','⏸ '.$subject,$tpl);

$updateorder = mysql_query("UPDATE `orders` SET `defect` = '5',`fulfilled` = '$now',`norefill` = '0' WHERE `id` = '$orderid123' AND `order_session` = '$ordersession' LIMIT 1");

header('Location: orders.php?type='.$pagefrom.'&message=email1&theid='.$orderid.$noorderstate);

?>