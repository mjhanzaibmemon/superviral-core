<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");
else ob_start();
header('Content-type: text/html; charset=utf-8');

$db = 0;
include('../header.php');


// for redirecting US to main site
$queryLoc = addslashes($_GET['loc']);
// echo $queryLoc;die;
$uri = str_replace("/us", "", $_SERVER['REQUEST_URI']);
if ($queryLoc == 'us') {
    // echo $queryLoc;
    header('Location: ' . $siteDomain . $uri, TRUE, 301);
    die;
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

function getIpVersion($ip) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return 'IPv4';
    } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        return 'IPv6';
    } else {
        return 'Invalid IP';
    }
}

function checkRecaptcha($secret)
{
    mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'free_tools_service' LIMIT 1");

    sendCloudwatchData('Superviral', 'free-tools-service', 'AdminStats', 'free-tools-service-function', 1);

    if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])){  
            
        // Google reCAPTCHA verification API Request  
        $api_url = 'https://www.google.com/recaptcha/api/siteverify';  
        $resq_data = array(  
            'secret' => $secret,  
            'response' => $_POST['g-recaptcha-response'],  
            'remoteip' => $_SERVER['REMOTE_ADDR']  
        );  
    
        $curlConfig = array(  
            CURLOPT_URL => $api_url,  
            CURLOPT_POST => true,  
            CURLOPT_TIMEOUT => 10,  
            CURLOPT_RETURNTRANSFER => true,  
            CURLOPT_POSTFIELDS => $resq_data  
        );  
    
        $ch = curl_init();  
        curl_setopt_array($ch, $curlConfig);  
        $response = curl_exec($ch);  
        curl_close($ch);  
    
        // Decode JSON data of API response in array  
        $responseData = json_decode($response);  
    
        // If the reCAPTCHA API response is valid  
        if($responseData->success){ 
          
            if($responseData->score > 0.3){
                return true;
            }else{
                return false;
            }
           
        }else{  
            return false;
        }  
    }
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


if ($locas[$loc]['sdb'] == 'uk') {
    $tpl = file_get_contents('tiktok_freefollowers_landing_uk.html');
} else {
    $tpl = file_get_contents('tiktok_freefollowers_landing.html');
}

$username = addslashes($_POST['username']);
$username = str_replace('@', '', $username);

$alreadyClaimed = 0;
$profileFlag = 0;

$statsQuery =  mysql_query("SELECT * FROM `admin_statistics` WHERE `type` = 'free_tools_service' LIMIT 1");
$statsData = mysql_fetch_array($statsQuery);
$metricCount = $statsData['metric'];

$blockQuery =  mysql_query("SELECT * FROM `admin_statistics` WHERE `type` = 'block_free_package' LIMIT 1");
$blockQueryData = mysql_fetch_array($blockQuery);
$blockMetric = $blockQueryData['metric'];

$added = time();
$ipaddress = getUserIP();
$countryuser = $locas[$loc]['sdb'];


if ($blockMetric == 0) {
    
    //IF SUBMITTED, SUBMIT AND FULFILL
    if ((!empty($username)) && !empty($_POST['submitBtn'])) {

        $countryBlock = false;
        $allowedCountry = false;
        $ipVersion = getIpVersion($ipaddress);

        if($ipVersion == 'IPv6') {
            $checkIpQ = mysql_query("SELECT *
            FROM ipinfo
            WHERE INET6_ATON('$ipaddress') BETWEEN INET6_ATON(start_ip) AND INET6_ATON(end_ip)
	    LIMIT 1;");

        }else{
            $checkIpQ = mysql_query("SELECT *
            FROM ipinfo
            WHERE INET_ATON('$ipaddress') BETWEEN INET_ATON(start_ip) AND INET_ATON(end_ip);");

        }
 
        if(mysql_num_rows($checkIpQ) > 0){
            $countryBlock = true;
            $ipData = mysql_fetch_array($checkIpQ);

		    // for us ,uk, canada
            $allowedCountry = in_array($ipData['country_code'], ['US', 'CA', 'GB']) ? true : false;
            $updateIpinfo = mysql_query("UPDATE `ipinfo` SET count = count + 1 WHERE `id` = '".$ipData['id']."'");
            if (!$allowedCountry) {
                $checkBlacklistQ = mysql_query("SELECT * FROM `country_ip_blacklist` WHERE `ip_address` = '$ipaddress'");
                if (mysql_num_rows($checkBlacklistQ) == 0){mysql_query('INSERT INTO `country_ip_blacklist` SET `ip_address` = "'.$ipaddress.'", `country_code` = "'.$ipData['country_code'].'", `added` = "'.time().'"');}
            }
        }

        if (!$countryBlock  || !$allowedCountry) {
            $checkBlacklistQ = mysql_query("SELECT * FROM `country_ip_blacklist` WHERE `ip_address` = '$ipaddress'");
            if(mysql_num_rows($checkBlacklistQ) > 0){
            $countryBlock = true;
            }else{
            $ipinfo = getUserIpInfo($ipaddress);
            $countryBlock = in_array($ipinfo['country'], ['IN', 'ID', 'PK', 'EG', 'PH', 'MA', 'DZ', 'NP', 'BR', 'LK', 'BD']) ? true : false;
            if($countryBlock)
            mysql_query('INSERT INTO `country_ip_blacklist` SET `ip_address` = "'.$ipaddress.'", `country_code` = "'.$ipinfo['country'].'", `added` = "'.time().'"');
       
            }
        }

        if($countryBlock && !$allowedCountry){
            $mainError = '<div class="label labelcontact" style="color:red;text-align:center;">Free tiktok followers are not available in your country';
            $successHide = "display:none;";

        }else{
            if ($metricCount > 50) {
                $recaptchaResp = checkRecaptcha($googleV3ServerKey);
            } else {
                $recaptchaResp = true;
            }
            if ($recaptchaResp) {
    
                $dpimgname = md5($id . $username);
                
    
                //by default acquire the thumbnail
                $finddpfile = mysql_query("SELECT `dp` FROM `tt_dp` WHERE `dp` = '$dpimgname' AND `ttusername` = '$username' LIMIT 1");
                if (mysql_num_rows($finddpfile) == 0) { //no thumbnailfound in database
                   
                   $query = mysql_query("INSERT INTO `free_api_stats`
                
                    SET 
                    `igusername` = '$username',
                    `added` = '$added', 
                    `ordersession` = '',
                    `source` = 'rapidapi',
                    `type` = 'tt-freefollowers'
                                
                    ");
                    $lastApiStatsId = mysql_insert_id();		

                    $starttime = microtime(true);
                    // mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_tiktok_freefollowers_userid' AND `brand` = 'sv' LIMIT 1");
                    sendCloudwatchData('Superviral', 'rapidapi-tiktok-api-userid', 'FreeFollowersTiktok', 'supernova-tiktok-api-userid-function', 1);
                    $curl = curl_init();

                    curl_setopt_array($curl, [
                        CURLOPT_URL => "https://scraptik.p.rapidapi.com/get-user?username=$username",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => [
                            "x-rapidapi-host: $tiktok_rapid_api_host",
                            "x-rapidapi-key: $tiktok_rapid_api_key"
                        ],
                    ]);

                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    $resp = json_decode($response, true);

                    curl_close($curl);

                    $users = $resp['user'];
                    $userId = $users['uid'];
                    // echo $userId;
                    // echo "<pre>";
                    // print_r($users);

                    $endtime = microtime(true);

                    $loadtime = $endtime - $starttime;
                    if(empty($loadtime)) $loadtime = 0;
                    mysql_query("UPDATE `free_api_stats`
                                    SET 
                                    `loadtime` = '$loadtime'
                                    WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

                    if (!empty($userId)) {
    
                        // echo "<pre>";
                        // print_r($get);die;
                        require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';
    
                        $s3 = new S3($amazons3key, $amazons3password);
    
    
                        $dp = $users['avatar_300x300']['url_list'][0];
                        // echo $dp;
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $dp);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                        curl_setopt($curl, CURLOPT_PROXY, 'gate.smartproxy.com:10000');
                        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                        curl_setopt($curl, CURLOPT_ENCODING, '');
    
    
                        $get = curl_exec($curl);
    
                        curl_close($curl);

                        $putobject = S3::putObject($get, 'cdn.superviral.io', 'dp/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);
                        if($putobject){
                            mysql_query("INSERT INTO `tt_dp` SET `dp` = '$dpimgname',`order_session` ='$id', `ttusername` = '$username', `dp_url` = '$dp',`dnow` = '0'");
                           
                            sendCloudwatchData('Superviral', 's3-image-upload-success', 'FreFollowersTiktok', 's3-image-upload-success-function', 1);
                        }else{
                            sendCloudwatchData('Superviral', 's3-image-upload-failure', 'FreFollowersTiktok', 's3-image-upload-failure-function', 1);
            
                        }
                    } else {
                        $error = '<div class="label labelcontact" style="color:red;">Tiktok profile couldn\'t be found</div>';
                        $profileFlag = 1;
                    }
                }
               
                $checkq = mysql_query("SELECT * FROM orders_free WHERE igusername = '$username' 
                AND `brand` = 'sv' 
                AND packagetype = 'freetrial' 
                AND amount = '10' 
                AND `socialmedia` = 'tt' 
                AND DATE(FROM_UNIXTIME(added)) = CURDATE()
                LIMIT 1");
                $checkarr = mysql_fetch_array($checkq);
    
                $restrictPerIpUser = false;
    
                $findip = mysql_query("SELECT * FROM `orders_free` WHERE `ipaddress` = '$ipaddress' AND added >= UNIX_TIMESTAMP(NOW()) - 172800 AND packagetype = 'freetrial' AND socialmedia = 'tt'");
                if(mysql_num_rows($findip) >= 3){
                    $restrictPerIpUser = true;
                }
    
    
                if (mysql_num_rows($checkq) == 0 && $profileFlag == 0 && !$restrictPerIpUser) {
    
                    $amount_q = mysql_query("SELECT SUM(amount) AS `total_amount` FROM `orders_free` WHERE igusername = '$username' AND `brand` = 'sv' AND packagetype = 'freetrial' AND amount = '10' AND `socialmedia` = 'tt'");
                    $amount_arr = mysql_fetch_array($amount_q);
                    $amount = $amount_arr['total_amount'];
                    if($amount >= 300){
                        $max_claimed = 1;
                        $mainError = "<div class='label labelcontact' style='color:red;text-align:center;'>You've claimed the max amount of followers for this account";
                        $successHide = "display:none;";            
                    }

                    if($max_claimed !== 1){
                        $id = md5($username . time());
        
                        //EMULATE ORDERFULFILL
                        $info['igusername'] = $username;
        
        
                        $loc2 = $loc;
                        if (empty($loc2)) $loc2 = $info['country'];
                        if (!empty($loc2)) $loc2 = $loc2 . '.';
                        if ($loc2 == 'ww.') $loc2 = '';
        
                        $cta = 'https://' . $loc2 . 'superviral.io/track-my-order/' . $id;
        
                        // include('emailfulfill2.php');
        
                      
                        $packq = mysql_query("SELECT * FROM packages WHERE 
                        `brand` = 'sv' 
                       AND `TYPE` = 'freetrial' 
                       AND amount = '10' 
                       AND `socialmedia` = 'tt'
                       LIMIT 1");
                        $packarr = mysql_fetch_array($packq);
        
                        $updateuser = mysql_query("INSERT IGNORE INTO `users` SET 
                                                    `country` = '$countryuser',
                                                    `usernames` = '$username', 
                                                    `source` = 'cart',
                                                    `added` = '{$added}',
                                                    `brand` = 'sv',
                                                    `md5` = '{$id}'
                                                    ");
        
        
                        $insertfulfill = mysql_query("INSERT INTO `orders_free` SET 
                                                    `packagetype` = 'freetrial',
                                                    `packageid` = '{$packarr['id']}',
                                                    `country` = '$countryuser',
                                                    `order_session` = '$id',
                                                    `amount` = '10',
                                                    
                                                    `added` = '$added',
                                                    `ipaddress` = '$ipaddress',
                                                    `next_fulfill_attempt` = '$added',  
                                                    `socialmedia` = 'tt', brand = 'sv',
                                                    `igusername` = '$username'");
        
                        $uniqueorderinsertid = mysql_insert_id();
        
        
                        // $showHideAnimation = "display:block;";
                        $checkarr['igusername'] = $username;
                        $claimed = 1;
                    }
    
                }else {
                    $alreadyClaimed = 1;
                    $claimed = 1;
                }
               
            }else {
                $recaptchaError = '<div class="label labelcontact" style="color:red;">Recaptcha Error</div>';
                
                // recaptcha failed
                sendCloudwatchData('Superviral', 'tiktok-freefollowers', 'recaptcha-error', 'tiktok-freefollowers-function', 1);
                 
            }
        }

        
    } 



if (empty($username) && !empty($_POST['submit'])) {
    $inpError = '<div style="color:red;    position: absolute; margin-top: 80px;">Enter your Tiktok username</div>';
}


$finddpfile = mysql_query("SELECT * FROM `tt_dp` WHERE `ttusername` = '{$username}' ORDER BY `id` DESC LIMIT 1");
$fetchdpfile = mysql_fetch_array($finddpfile);
$dpimgname = $fetchdpfile['dp'];
//echo $claimed;

if ($claimed == 1 &&  $profileFlag == 0) {

    $successHide = "display:none;";

    if ($alreadyClaimed == 1) $msgClaimed = ' Already claimed, ';
    $showHideAnimation = '<div class="tholder tholderanimation" align="center">
    <span class="green-text">
    ' . $msgClaimed . ' 10 free followers on its way to
    <span style="display: block;"> ' . $checkarr['igusername'] . '</span>
    </span>
        <div class="cnwidth cnwidthtracking">
            <div class="animationholder">
                <img class="igicon"
                    src="https://cdn.superviral.io/dp/' . $dpimgname . '.jpg">
                <img class="mainanimation" src="/imgs/t-page/deliveryfollowers.gif?type=438228">
            </div>
        </div>
        <br>
        <span>
            <a href="" style="text-decoration: underline;">Try Another Profile</a> or <br><br> 

                                <button class="btn btn-primary btn-download" onclick="onSubmitDataASD(event);" style="text-align: center;border:0;width:100%;">

                                                    <span class="text">Get More Followers!</span>
                                                </button>
                        </span>
                    </div>

                <script>
                function onSubmitDataASD(event) {
                    event.preventDefault(); // Optional if you need to prevent form submission or other default actions
                    window.location.href = \'https://superviral.io/'.$loclinkforward.'buy-tiktok-followers/\'; 
                }
                </script>
    ';
    if($restrictPerIpUser) $showHideAnimation  = '<div class="tholder tholderanimation" align="center"><span class="green-text">Already claimed 3 orders per day</span>
    <br>        
    <br>        
    <span>
                <a href="" style="text-decoration: underline;">Try Another Profile</a>  or <br><br> 

                <button class="btn btn-primary btn-download" onclick="onSubmitDataASD(event);" style="text-align: center;border:0;width:100%;">

                                    <span class="text">Get More Followers!</span>
                                </button>
        </span>
    </div>

<script>
function onSubmitDataASD(event) {
    event.preventDefault(); // Optional if you need to prevent form submission or other default actions
    window.location.href = \'https://superviral.io/'.$loclinkforward.'buy-tiktok-followers/\'; 
}
</script>';

}

}else {
    $error = '<div class="label labelcontact" style="color:red;text-align:center;">Free tiktok followers are currently unavailable, please try again later</div>';
    $successHide = "display:none;";
}



$q = mysql_query("SELECT * FROM `packages` WHERE `brand`='sv' AND `type` = 'followers' AND `premium` = '0' AND socialmedia = 'tt' ORDER BY `amount` ASC");

$maxPrice = 0;
$minPrice = 1000;
$countPackage = mysql_num_rows($q);

while($info = mysql_fetch_array($q)){

        $info['price'] = explode('.', $info['price']);

        $mainprice = $info['price'][0];
        $decimal = $info['price'][1];

        if($info['id']==182)$decimalinc = '.' .$decimal;
        if($info['amount'] == 1000){$bestPackageClass = 'best'; $popular_class = 'popular';}else{$bestPackageClass = ''; $popular_class = '';}

        if($info['amount'] > 9000) {
            $amount = formatNumber($info['amount']);
        }else{
            $amount = $info['amount'];
        }
        $packages .= '			
        <div class="card-package '.$popular_class.'">
                                <div class="quantity">'.$amount.'</div>
                                <div class="label">Followers</div>
                                <div class="seperator"></div>
                                <div class="amount"><span class="currency">'.$locas[$loc]['currencysign'].'</span><span class="value">'.$mainprice.$decimalinc.'</span></div>
                                <a href="'.$loclink.'/{hreforder}/{hrefchoose}/'.$info['id'].'" class="btn btn-primary">Buy Now</a>
                            </div>';

                                

        $mobilepackages .= '			

        <div class="newpackage dshadow '.$bestPackageClass.' " onclick="location.href = \''.$loclink.'/{hreforder}/{hrefchoose}/'.$info['id'].'\';">
    
    <div class="amount">
    
     '.$amount.'
      
    </div>
    
    <div class="typeofpackage">{packagetype}</div>
    
    <div class="price" style="
"><sup class="sign">'.$locas[$loc]['currencysign'].'</sup><div class="mainprice">'.$mainprice.'</div><sup class="decimal">'.$decimalinc.'</sup></div>


    
    
    <div class="ctabutton">
      <a href="'.$loclink.'/{hreforder}/{hrefchoose}/'.$info['id'].'">{packagebuynow}</a>
      
    </div>
    
    
    
  </div>

        ';

        unset($decimalinc);

}

$secondPointContext = "2. Tap the button below";
$thirdPointContext = "";

if ($metricCount > 50) {

    // $recaptchaUrl = '<script src="https://www.google.com/recaptcha/api.js?render='.$googleV3ClientKey.'"></script>';	
    $onClickEvent = "onSubmitData(event);";

} else {
    // $recaptchaUrl = "";
    $onClickEvent = "formSubmit(event);";
}

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{loclink}', $loclink, $tpl);
$tpl = str_replace('{inpError}', $inpError, $tpl);
$tpl = str_replace('{error}', $error, $tpl);
$tpl = str_replace('{recaptchaError}', $recaptchaError, $tpl);
$tpl = str_replace('{igusername}', $checkarr['igusername'], $tpl);
$tpl = str_replace('{showHideAnimation}', $showHideAnimation, $tpl);
$tpl = str_replace('{banner-instruction-2}', $secondPointContext, $tpl);
$tpl = str_replace('{banner-instruction-3}', $thirdPointContext, $tpl);
$tpl = str_replace('{successHide}', $successHide, $tpl);
$tpl = str_replace('{packages}', $packages, $tpl);
$tpl = str_replace('{mobilepackages}', $mobilepackages, $tpl);
$tpl = str_replace('{googlev3recaptchakey}', $googleV3ClientKey, $tpl);
// $tpl = str_replace('{recaptchaUrl}', $recaptchaUrl, $tpl);
$tpl = str_replace('{onClickEvent}', $onClickEvent, $tpl);
$tpl = str_replace('{mainError}', $mainError, $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `country` = '{$locas[$loc]['sdb']}' AND `page` IN ('tiktok-freefollowers-landing','global')");
while ($cinfo = mysql_fetch_array($contentq)) {
    $tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);
}



echo $tpl;
