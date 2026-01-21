<?php



include('../sm-db.php');
include('emailer.php');

$email_txt = file_get_contents('emails.txt');

$list = explode("\n",$email_txt);

$emailaddress = trim($list[0]);

if(empty($emailaddress))die('Empty - done!');


//////////////////////////////////////////////////////////////////////





$svemailbody = '<p>Hi there,</p>
<br>
<p>We are contacting you in relation to your payment with Superviral. If you’re receiving this email this means, our systems show you’ve made a payment to us while one of our systems were being upgraded for a greater service.</p>
<br>
<p>We want to sincerely apologise for the inconvenience this has caused. We understand the impact it may have had on your experience at Superviral, and we genuinely regret any frustration it has caused.</p>
<br>
<p>As the largest Instagram service provider, we have successfully and quickly resolved the issue, and all payments affected by this incident have been reversed and refunded. Please provide upto 3-working days for your payment to appear on your bank transactions.</p>
<br>
<p>Any order for follower, likes and views have been cancelled during this time and will need to be re-ordered.
Thank you for your patience and continued trust and once again we do apologise for the inconvenience this may have caused you.</p>
<br>
<p>Re-place your order here on: <a href="https://superviral.io/">https://superviral.io/</a></p>
<br>
<p>Since 2012, the customer always comes first. Wishing you a great weekend,<br>
Superviral Team</p>

';


 $tpl = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]).'/superviral.io/emailtemplate/emailtemplate.html');
$tpl = str_replace('{body}', $svemailbody, $tpl);
$tpl = str_replace('{subject}', 'RE: Your payment on Superviral', $tpl);

emailnow($emailaddress, 'Superviral Team', 'no-reply@superviral.io', 'Your payment on Superviral', $tpl);


echo $emailaddress.'<hr>';
echo $tpl;


//////////////////////////////////////////////////////////////////////











$arraysplice = array_shift($list);

$email_txt = implode("\n", $list);

file_put_contents('emails.txt',$email_txt); // in case u f up

echo ' <meta http-equiv="refresh" content="1">';

?>