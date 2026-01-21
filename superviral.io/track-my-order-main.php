<?php

$order = addslashes($_POST['order']);
$emailaddress = addslashes($_POST['emailaddress']);

$tpl = file_get_contents('track-my-order-main.html');

if(!empty($_POST['submit'])){

if(empty($order)){$error1 = '<div class="emailfailed">{error1}</div>';$error=1;}
if(empty($emailaddress)){$error2 = '<div class="emailfailed">{error2}</div>';$error=1;}

if(empty($error)){

	$q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$order' AND `emailaddress` = '$emailaddress' AND `order_session` !='' LIMIT 1");

	if(mysql_num_rows($q)=='0'){$success = '<div class="emailfailed">{error3}</div>';}
	else{$info = mysql_fetch_array($q);header('Location: /'.$loclinkforward.'track-my-order/'.$info['order_session'].'/'.$order);}
	

}

}

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{success}', $success, $tpl);
$tpl = str_replace('{order}', $order, $tpl);
$tpl = str_replace('{emailaddress}', $emailaddress, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{error2}', $error2, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `brand`='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'track-my-order') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");

while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
if($cinfo['name']=='canonical')$htmlcanonical = $cinfo['content'];}

//$tpl = str_replace('<link rel="alternate" hreflang="'.$locas[$loc]['contentlanguage'].'" href="'.$htmlcanonical.'" />', '', $tpl);

echo $tpl;

?>