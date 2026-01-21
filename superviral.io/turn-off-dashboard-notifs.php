<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));


$db=1;
include('header.php');

$tpl = file_get_contents('turn-off-dashboard-notifs.html');

$id = addslashes($_GET['id']);
if(empty($id))die('Not valid');





if((!empty($_POST['submit']))&&(!empty($_POST['id']))){


		if(!empty($_POST['disable'])){

				$data = mysql_query("UPDATE `accounts` SET 
				                                    `notification_disabled` = 1
				                                    WHERE `email_hash` = '$id' LIMIT 1");

				if($data){
				    	$domessage = '<div class="message">You\'ve successfully disabled dashboard notifications from Superviral. We hope to see you again.</div>';

				}

				$accounts = mysql_fetch_array(mysql_query("SELECT id from accounts WHERE `email_hash` = '$id' LIMIT 1"));
				$accountId = $accounts['id'];
				
				$data = mysql_query("UPDATE `post_notif_schedule` SET 
				                                    `email_sent` = '2'
				                                    WHERE `account_id` = '$accountId' AND `email_sent` = '0' LIMIT 1");
		}

		if(!empty($_POST['enable'])){

				$data = mysql_query("UPDATE `accounts` SET 
				                                    `notification_disabled` = 0
				                                    WHERE `email_hash` = '$id' LIMIT 1");

				if($data){
				    	$domessage = '<div class="message">You\'ve successfully enabled email notifications with Superviral.</div>';

				}

		}



}






$q = mysql_query("SELECT * FROM `accounts` WHERE `email_hash` = '$id' LIMIT 1");

if(mysql_num_rows($q)==0)die('Not found');

$info = mysql_fetch_array($q);


if($info['notification_disabled']==0){


$enableordisable = 'disable';
$btn = '<input type="submit" name="submit" value="Disable dashboard notifications" class="btn btn3 showmo disablebtn" style="width:250px;">';


}else{


$enableordisable = 'enable';
$btn = '<input type="submit" name="submit" value="Enable dashboard notifications" class="btn btn3 color4 enablebtn" style="width:250px;">';



}





$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{loclinkforward}', $loclinkforward, $tpl);
$tpl = str_replace('{enableordisable}', $enableordisable, $tpl);
$tpl = str_replace('{btn}', $btn, $tpl);
$tpl = str_replace('{domessage}', $domessage, $tpl);
$tpl = str_replace('{userid}', $id, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') ");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
if($cinfo['name']=='canonical')$htmlcanonical = $cinfo['content'];}


$tpl = str_replace('{footer}', $footer, $tpl);


echo $tpl;

?>