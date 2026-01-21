<?php


if(empty($info))die('Not authorised to access this. AL Error.');

/*$upsell_autolikesdb = explode('###',$info['upsell_autolikes']);

$now = time();
$al_expiry = date("d/m/Y", strtotime('tomorrow'));//the next day so the cron job can automate this for the next fulfill id
$al_endexpiry = $now + 2592000;//when the entire package expirys not the fulfill id

$al_username = $info['igusername'];
$al_min = $upsell_autolikesdb[2];
$al_max = round($upsell_autolikesdb[2] * 1.2);
$al_likes_per_post = $upsell_autolikesdb[2];
$al_max_perday = $upsell_autolikesdb[3];
$original_id = $info['order_session'];
$al_payment_id = $uniquepaymentid;
$al_price = $upsell_autolikesdb[4];

$al_md5 = md5($now.$al_username.$al_min.$al_expiry);

echo "AL variables set \n";


if(!empty($al_fullfill_id ))echo "Fulfilled AL \n";

mysql_query("INSERT INTO `automatic_likes`
	SET 
	`md5` = '$al_md5', 
	`origin_order` = '$original_id', 
	`added` = '$now', 
	`expires` = '$al_endexpiry', 
	`last_updated` = '0', 
	`payment_id` = '$al_payment_id', 
	`likes_per_post` = '$al_likes_per_post', 
	`max_post_per_day` = '$al_max_perday', 
	`fulfill_id` = '$al_fullfill_id', 
	`igusername` = '$al_username', 
	`price` = '$al_price',
    `payment_creq_crdi` = '',
	`emailaddress` = '{$info['emailaddress']}'
	");


if(!empty($al_fullfill_id ))echo "AL Inserted into DB \n";*/

//////////////////// SEND EMAIL TIME!!!!

$sender = 'no-reply@superviral.io';
$senderName = 'Superviral';
$usernameSmtp = $sesusernamev2;
$passwordSmtp = $sespasswordv2;
$host = 'email-smtp.us-east-2.amazonaws.com';
$port = 587;


$recipient = $info['emailaddress'];
$subject = 'Get Your Free Automatic Likes - Offer Expires in 48 hours!';

/*
$service = $al_likes_per_post.' Automatic Instagram Likes ('.$al_max_perday.' posts per day)';
$payment = $al_price;
*/

$ctahref = 'https://superviral.io/'.$loclinkforward.'account/dashboard/?loadfreeautolikes=true';

$bodyHtml = file_get_contents('../emailtemplate/autolikescon.html');
if(empty($bodyHtml)){$bodyHtml = file_get_contents('emailtemplate/autolikescon.html');}

/*
$bodyHtml = str_replace('{payment}', $payment, $bodyHtml);
$bodyHtml = str_replace('{service}', $service, $bodyHtml);
*/
$bodyHtml = str_replace('{username}', $info['igusername'], $bodyHtml);
$bodyHtml = str_replace('{subject}', $subject, $bodyHtml);
$bodyHtml = str_replace('{ctahref}', $ctahref, $bodyHtml);

$bodyText =  strip_tags($bodyHtml);

//if($_GET['rabban']=='true'){$mail = new PHPMailer(true);}

try {
    // Specify the SMTP settings.
    $mail->isSMTP();
    $mail->setFrom($sender, $senderName);
    $mail->Username   = $usernameSmtp;
    $mail->Password   = $passwordSmtp;
    $mail->Host       = $host;
    $mail->Port       = $port;
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = 'tls';
    $mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configurationSet);
    $mail->CharSet = 'UTF-8';

    // Specify the message recipients.
    $mail->addAddress($recipient);
    // You can also add CC, BCC, and additional To recipients here.

    // Specify the content of the message.
    $mail->isHTML(true);
    $mail->Subject    = $subject;
    $mail->Body       = $bodyHtml;
    $mail->AltBody    = $bodyText;
    $mail->Send();
   // echo "Email AL sent!" , PHP_EOL;
} catch (phpmailerException $e) {
    //echo "An error occurred with AL email. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
} catch (Exception $e) {
    //echo "Email not sent with AL email. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
}


?>