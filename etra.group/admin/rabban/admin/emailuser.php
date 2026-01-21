<?php

if($emailtrue!=='asdas4dsdf')die('Error 548483: Not working - contact Admin');

$emailbody = '
<p>Hi there,</p>
<br>
<p>This is an automated email. We are happy to inform you that '.$thefreeservice.' is being processed to your profile. Please ensure your profile stays on public.</p>
<br>
<p>Here is what we\'re delivering to you:</p>
<br>

<table class="ordertbl">
	<tr><td>Instagram Username</td><td>Service</td><td>Payment</td></tr>
	<tr><td>'.$igusername.'</td><td>'.$service.'</td><td>FREE</td></tr>
</table>

<br>
	<a href="'.$ctahref.'" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Track My Order Now</a>
<br>
<p>We\'re going to deliver this to you as soon as possible with the safest delivery methods on our Superviral platform.</p>
<br>
<p>The quality of users here are the same quality you receive with any other package, high quality and bringing growth to your Instagram profile.</p>
<br>
<p>At Superviral, the customer ALWAYS comes first - no matter what. Thank you again for choosing Superviral!</p>
<br>
<p>Kind regards,<br>
Superviral Team</p>
<br>
<p>160 City Road<br>
London<br>
EC1V 2NX<br>
United Kingdom</p>';

$tpl = file_get_contents('../emailtemplate/emailtemplate.html');
$tpl = str_replace('{body}',$emailbody,$tpl);
$now = time();

$tpl = str_replace('Unsubscribe','',$tpl);

$tpl = str_replace('{subject}',$subject,$tpl);

include('../crons/emailer.php');
emailnow($to,'Superviral','support@superviral.io',$subject,$tpl);

?>