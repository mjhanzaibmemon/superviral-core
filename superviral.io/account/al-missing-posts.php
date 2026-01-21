<?php

$db=1;
include('header.php');
include('auth.php');

$hash = addslashes($_GET['hash']);


$notextupdate = addslashes($_GET['notextupdate']);
if($notextupdate=='true'){
$therefresh = '<script>window.top.location.reload();</script>';
echo $therefresh;die;
}

$now = time();

$q = mysql_query("SELECT * FROM `automatic_likes` WHERE `md5` = '$hash' AND `brand`='sv' AND `missinglikespost` = '0' AND `expires` > $now AND `account_id` = '{$userinfo['id']}' LIMIT 1");
if(mysql_num_rows($q)=='0'){die('

<style>body{background:#fff;font-family:arial;text-align:center;}</style>
  Missing Auto Likes Feature already claimed - your Missing Auto Likes Feature will be reset at the end of today.

<script>

setTimeout(function(){ window.top.location.reload();}, 5000);

</script>

  ');}

$locredirect = $loc.'.';
if($locredirect=='ww.')$locredirect = '';


$info = mysql_fetch_array($q);

$tpl = file_get_contents('al-missing-posts.html');

$tpl = str_replace('{likesperpost}',$info['likes_per_post'],$tpl);
$tpl = str_replace('{hash}',$hash,$tpl);
$tpl = str_replace('{hash2}',$order_session,$tpl);
$tpl = str_replace('{id}',$id,$tpl);
$tpl = str_replace('{locredirect}',$locredirect,$tpl);
$tpl = str_replace('{loclinkforward}',$loclinkforward,$tpl);
$tpl = str_replace('{igusername}',$info['igusername'],$tpl);



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