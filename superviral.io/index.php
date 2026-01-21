<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db=1;
include('header.php');

// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us","" ,$_SERVER['REQUEST_URI']);
if($queryLoc == 'us'){
    // echo $queryLoc;
    setcookie("IsUS", "Yes", time()+3600, '*/', NULL, 0 ); // 1 hour
    header('Location: '. $siteDomain . $uri ,TRUE,301);die;
}

function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }

    return $ip;
}

function getUserIpInfo($ip)
{
    global $ipinfoToken;

    $token = $ipinfoToken;

    $ip_address = trim($ip); 

    $api_url = "https://ipinfo.io/" . $ip_address . "?token=" . $token;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    curl_close($ch);

    $data = json_decode($response, true);

    return $data;
    
}

$ipaddress = getUserIP();

// show flag
if(!empty($_GET['get_country'])){
    $ipinfo = getUserIpInfo($ipaddress);
	$flagShowCall = "";
	if(!empty($ipinfo['country'])){
		echo $ipinfo['country'];
        die;
	}
}


// echo var_dump($_COOKIE);die;

$tpl = file_get_contents('index3.html');

if($loc=='fr'){header("Location: https://superviral.io/", true, 301);die;}
if($loc=='es'){header("Location: https://superviral.io/", true, 301);die;}
if($loc=='de'){header("Location: https://superviral.io/", true, 301);die;}
if($loc=='it'){header("Location: https://superviral.io/", true, 301);die;}


if($_GET['unsub']=='true'){



	$domessage = '<div class="message">You\'ve successfully unsubscribed from Superviral\'s alerts and notificaitons. We hope to see you again.</div>';


}



$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{loclinkforward}', $loclinkforward, $tpl);
$tpl = str_replace('{domessage}', $domessage, $tpl);
$tpl = str_replace('{contentlanguage}', $locas[$loc]['contentlanguage'], $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{loclocation}', $loclinkforward , $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE brand='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global'))");

while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);
if($cinfo['name']=='canonical')$htmlcanonical = $cinfo['content'];}

//$tpl = str_replace('<link rel="alternate" hreflang="'.$locas[$loc]['contentlanguage'].'" href="'.$htmlcanonical.'" />', '', $tpl);



echo $tpl;
?>
