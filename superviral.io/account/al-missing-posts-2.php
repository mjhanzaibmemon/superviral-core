<?php

$db=1;
include('header.php');
include('auth.php');





$hash = addslashes($_GET['hash']);

//if(empty($_POST['posts_selected'])){header('Location: al-missing-posts.php?&hash='.$hash);}

$notextupdate = addslashes($_GET['notextupdate']);
if($notextupdate=='true'){
$therefresh = '<script>window.top.location.reload();</script>';
echo $therefresh;die;
}

$now = time();

$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `md5` = '$hash' AND `brand`='sv' AND `missinglikespost` = '0' AND `expires` > $now AND `account_id` = '{$userinfo['id']}' LIMIT 1");

if(mysql_num_rows($q)=='0'){die('

<style>body{background:#fff;font-family:arial;}</style>
  Missing Auto Likes Feature already claimed - your Missing Auto Likes Feature will be reset at the end of today.

<script>

setTimeout(function(){ window.top.location.reload();}, 5000);

</script>

  ');
}

$info = mysql_fetch_array($q);

mysql_query("UPDATE `automatic_likes` SET `missinglikespost` = '1' WHERE `id` = '{$info['id']}' LIMIT 1 ");


$submitted_values = json_decode($_POST['posts_selected'],true);
foreach($submitted_values as $value){$valuesd = addslashes($value);}
$valuesd = explode('###', $valuesd);//$valuesd = $valuesd[1];

if(!empty($valuesd[0])){

$freelikespost = $valuesd[0];


//////

//EMULATE ORDERFULFILL
$added = time();
$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
$info['packageid'] = '20';
$username = $info['igusername'];

$hash = md5($info['packageid'].$valuesd[0]);


$insertfulfill = mysql_query("INSERT INTO `orders` SET 
  `brand` = 'sv',
  `packagetype` = 'freelikes',
  `packageid` = '20',
	`country` = '{$info['country']}',
	`order_session` = '$hash',
	`amount` = '{$info['likes_per_post']}',
	`added` = '$added',
  `next_fulfill_attempt` = '$added',
	`price` = '0.00',
	`emailaddress` = '{$info['emailaddress']}',
	`contactnumber` = '',
  `ipaddress` = '$ipaddress',
	`chooseposts` = '$freelikespost',
	`igusername` = '$username',
  `account_id` = '{$userinfo['id']}',
  `payment_id` = 'FREE - AL# {$info['id']}',
  `socialmedia` = 'ig'");

//////

sendCloudwatchData('Superviral', 'missing-tool-image', 'MissingPost', 'missing-tool-image-loading-function', 1);


include('../orderfulfill.php');



}


$tpl = file_get_contents('al-missing-posts-2.html');

$tpl = str_replace('{values}',$valuesd[1],$tpl);
$tpl = str_replace('{likesperpost}',$info['likes_per_post'],$tpl);
$tpl = str_replace('{ordersession}',$order_session,$tpl);
$tpl = str_replace('{id}',$id,$tpl);



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