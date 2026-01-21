<?php



$loc = $_SERVER['SERVER_NAME'];
$loc = str_replace('superviral.', '', $loc);
$loc = array_shift((explode('.', $_SERVER['HTTP_HOST'])));

if (empty($loc)) $loc = '';
if ($loc == 'superviral') $loc = '';
if ($loc == 'www') $loc = '';
if (!empty($loc)) $loc = $loc . '.';

header('Access-Control-Allow-Origin: https://' . $loc . 'superviral.io');
include('db.php');


$username = trim(strtolower(addslashes($_POST['username'])));


$username = str_replace('@', '', $username);


if ($videosonly == '1') $videosonly = '1';


// downloadVideo

if (isset($_GET['videoUrl'])) {
    $videoUrl = $_GET['videoUrl'];

    // Set headers for file download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="video.mp4"');

    // Read the video file and output it directly
    readfile($videoUrl);
    exit;
}




if ($_POST['type'] != 'post' && $_POST['type'] != 'tiktok_post') {

    if (empty($username)) {
        echo json_encode(['message' => 'Username Can\'t be blank']);
        die;
    }
}



switch ($_POST['type']) {
        case "follower_count":
        case "dp":
            $recaptchaResp = checkRecaptcha($googleV3ServerKey);
            $now = time();
            if ($recaptchaResp) {
                $doCurl = 1;
                $finddpfile = mysql_query("SELECT * FROM `ig_dp` WHERE `igusername` = '$username' ORDER BY `id` DESC LIMIT 1");
                $fetchdpfile = mysql_fetch_array($finddpfile);
                if (mysql_num_rows($finddpfile) > 0) {
                    $doCurl = 0;
                    $profile = "https://cdn.superviral.io/dp/" . $fetchdpfile['dp'] . ".jpg";
                }
    
                $query = mysql_query("INSERT INTO `free_api_stats`
                
                SET 
                `igusername` = '$username', 
                `added` = '$now', 
                `ordersession` = '',
                `source` = 'rapidapi',
                `type` = 'followercount'
                            
                ");

                $lastApiStatsId = mysql_insert_id();		

                $starttime = microtime(true);

                $url = 'https://flashapi1.p.rapidapi.com/ig/info_username/?user='. $username .'&nocors=false';

                //ATTEMPT TODO IT OUR WAY
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-rapidapi-host: $rapidapihost","x-rapidapi-key: $rapidapikey"));
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_ENCODING, '');
                curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        
                $get = curl_exec($curl);
        
                $resp = json_decode($get, true);
                $users = $resp;
                
                $userId = $users['user']['pk_id'];

                curl_close($curl);
                $endtime = microtime(true);

                $loadtime = $endtime - $starttime;
                if(empty($loadtime)) $loadtime = 0;
                mysql_query("UPDATE `free_api_stats`
                                SET 
                                `loadtime` = '$loadtime'
                                WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

                if(!empty($userId)){
                    
                    $dp = $users['user']['profile_pic_url'];
                    $followers = $users['user']['follower_count'];
                    $following = $users['user']['following_count'];
                    $isprivate = $users['user']['is_private'];

                }else{

                    // mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_freetools_profile' AND `brand` = 'sv' LIMIT 1");
                    // sendCloudwatchData('Superviral', 'supernova-api-freetools-profile', 'FreeToolsAjax', 'supernova-api-freetools-profile-function', 1);
                    
                    $query = mysql_query("INSERT INTO `free_api_stats`
                
                    SET 
                    `igusername` = '$username', 
                    `added` = '$now', 
                    `ordersession` = '',
                    `source` = 'rapidapi',
                    `type` = 'followercount'
                                
                    ");
    
                    $lastApiStatsId = mysql_insert_id();		
                    
                    $starttime = microtime(true);
                    
                    $url = 'https://flashapi1.p.rapidapi.com/ig/info_username/?user='. $username .'&nocors=false';
                    
                    //ATTEMPT TODO IT OUR WAY
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-rapidapi-host: $rapidapihost","x-rapidapi-key: $rapidapikey"));
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                    curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_ENCODING, '');
                    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
                    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    
                    $get = curl_exec($curl);
                    
                    $resp = json_decode($get, true);
                    $users = $resp;
                    
                    $userId = $users['user']['pk_id'];
                    
                    curl_close($curl);
                    $endtime = microtime(true);
                    
                    $loadtime = $endtime - $starttime;
                    if(empty($loadtime)) $loadtime = 0;
                    mysql_query("UPDATE `free_api_stats`
                                SET 
                                `loadtime` = '$loadtime'
                                WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");
                    
                    if(!empty($userId)){
                    
                        $dp = $users['user']['profile_pic_url'];
                        $followers = $users['user']['follower_count'];
                        $following = $users['user']['following_count'];
                        $isprivate = $users['user']['is_private'];
                    }else {
                        echo json_encode(['message' => 'Error while fetching, please try again!']);
                        die;
                    }
                }
    
                if ($doCurl == 1) {
    
                    require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';
                    $s3 = new S3($amazons3key, $amazons3password);
                    $dpimgname = md5(time() . $username);
                    
                    $randnum = rand(0, 3);
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $dp);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                    // curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
                    //curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 
                    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                    curl_setopt($curl, CURLOPT_ENCODING, '');
                    $get = curl_exec($curl);
                    curl_close($curl);
                    $putobject = S3::putObject($get, 'cdn.superviral.io', 'dp/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);
                    if($putobject){
                        sendCloudwatchData('Superviral', 's3-image-upload-success', 'FreeToolsAjax', 's3-image-upload-success-function', 1);
        
                    }else{
                        sendCloudwatchData('Superviral', 's3-image-upload-failure', 'FreeToolsAjax', 's3-image-upload-failure-function', 1);
        
                    }
                    $profile = "https://cdn.superviral.io/dp/" . $dpimgname . ".jpg";
                    mysql_query("INSERT INTO `ig_dp` SET `dp` = '$dpimgname',`order_session` ='', `igusername` = '$username', `dp_url` = '$dp',`dnow` = '0'");
                }

                if(!empty($profile) && $_POST['type'] == 'dp'){
					sendCloudwatchData('Superviral', 'profile-picture-download', 'ProfilePicDownload', 'pp-download-success-function', 1);
                }

                if(!empty($followers) && $_POST['type'] == 'follower_count'){
					sendCloudwatchData('Superviral', 'free-followers-count', 'FollowersCount', 'ff-count-success-function', 1);
                }
    
                $dataArray = ['profile_pic' => $profile, 'followerCount' => $followers, 'followingCount' => $following];
                echo json_encode(['data' => $dataArray, 'message' => 'success']);
    
                $endtime = microtime(true);
                $loadtime = $endtime - $starttime;
            } else {
                // recaptcha failed
                sendCloudwatchData('Superviral', 'instagram-followercount-dp', 'recaptcha-error', 'instagram-followercount-dp-function', 1);
                echo json_encode(['message' => 'Recaptcha Error']);
                die;
            }
    
    
            die;
            break;
        case "stories":
    
            $recaptchaResp = checkRecaptcha($googleV3ServerKey);
    
            if ($recaptchaResp) {
                $now = time();
                // mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_freetools_userid' AND `brand` = 'sv' LIMIT 1");
                // sendCloudwatchData('Superviral', 'supernova-api-freetools-userid', 'FreeToolsAjax', 'supernova-api-freetools-userid-function', 1);
                $query = mysql_query("INSERT INTO `free_api_stats`
                
                SET 
                `igusername` = '$username', 
                `added` = '$now', 
                `ordersession` = '',
                `source` = 'rapidapi',
                `type` = 'stories'
                            
                ");

                $lastApiStatsId = mysql_insert_id();		
                
                $starttime = microtime(true);

                $url = 'https://flashapi1.p.rapidapi.com/ig/info_username/?user='. $username .'&nocors=false';

                //ATTEMPT TODO IT OUR WAY
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-rapidapi-host: $rapidapihost","x-rapidapi-key: $rapidapikey"));
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_ENCODING, '');
                curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        
                $get = curl_exec($curl);
        
                $resp = json_decode($get, true);
                $users = $resp;
                
                $userId = $users['user']['pk_id'];

                curl_close($curl);
                $endtime = microtime(true);
                    
                $loadtime = $endtime - $starttime;
                if(empty($loadtime)) $loadtime = 0;
                mysql_query("UPDATE `free_api_stats`
                            SET 
                            `loadtime` = '$loadtime'
                            WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

                if (!empty($userId)) {
    
                    // mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_freetools_stories' AND `brand` = 'sv' LIMIT 1");
                    // sendCloudwatchData('Superviral', 'supernova-api-freetools-stories', 'FreeToolsAjax', 'supernova-api-freetools-stories-function', 1);
                    $query = mysql_query("INSERT INTO `free_api_stats`
                
                    SET 
                    `igusername` = '$username', 
                    `added` = '$now', 
                    `ordersession` = '',
                    `source` = 'rapidapi',
                    `type` = 'stories'
                                
                    ");
    
                    $lastApiStatsId = mysql_insert_id();		
                    
                    $starttime = microtime(true);

                    $url = 'https://flashapi1.p.rapidapi.com/ig/stories/?id_user='. $userId .'&nocors=false';

                    //ATTEMPT TODO IT OUR WAY
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-rapidapi-host: $rapidapihost","x-rapidapi-key: $rapidapikey"));
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                    curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_ENCODING, '');
                    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
                    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            
                    $get = curl_exec($curl);
            
                    $get = json_decode($get);
                    
                    curl_close($curl);

                    $endtime = microtime(true);
                    
                    $loadtime = $endtime - $starttime;
                    if(empty($loadtime)) $loadtime = 0;

                    mysql_query("UPDATE `free_api_stats`
                                SET 
                                `loadtime` = '$loadtime'
                                WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

                    if (!empty($get->reel)) {
                        sendCloudwatchData('Superviral', 'free-stories-download', 'StoriesDownload', 'fs-download-success-function', 1);
                        $i = 0;
                        require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';
    
                        $s3 = new S3($amazons3key, $amazons3password);
    
                        while ($i < $get->reel->media_count) {
    
                            if ($get->reel->items[$i]->media_type == 1) {
                                $img_url = $get->reel->items[$i]->image_versions2->candidates[0]->url;
                                $media_type = $get->reel->items[$i]->media_type;
                            } else {
                                $video_url = $get->reel->items[$i]->video_versions[2]->url;
                                $media_type = $get->reel->items[$i]->media_type;
                                $img_url = $get->reel->items[$i]->image_versions2->candidates[0]->url;
                            }
    
                            $dpimgname = md5(time() . $username);
    
                            $curl = curl_init();
                            curl_setopt($curl, CURLOPT_URL, $img_url);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                            curl_setopt($curl, CURLOPT_ENCODING, '');
    
    
                            $get1 = curl_exec($curl);
    
                            curl_close($curl);
                            $putobject = S3::putObject($get1, 'cdn.superviral.io', 'thumbs/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);
                            if($putobject){
                                sendCloudwatchData('Superviral', 's3-image-upload-success', 'FreeToolsAjax', 's3-image-upload-success-function', 1);
                
                            }else{
                                sendCloudwatchData('Superviral', 's3-image-upload-failure', 'FreeToolsAjax', 's3-image-upload-failure-function', 1);
                
                            }
                            $thumb_url = "https://cdn.superviral.io/thumbs/" . $dpimgname . ".jpg"; //thumb
    
                            $dataArray[$i]['video_url'] = $video_url;
                            $dataArray[$i]['media_type'] = $media_type;
                            $dataArray[$i]['url'] = $thumb_url;
    
                            $i++;
                            sleep(1);
                        }
                    } else {
                        echo json_encode(['message' => 'Not found, please try again later!']);
                        die;
                    }
    
                    $dataArray = ['stories' => $dataArray];
    
                    $endtime = microtime(true);
                    $loadtime = $endtime - $starttime;
    
                    echo json_encode(['data' => $dataArray, 'message' => 'success']);
                } else {
                    echo json_encode(['message' => 'Error while fetching, please try again!']);
                    die;
                }
            } else {
                 // recaptcha failed
                sendCloudwatchData('Superviral', 'instagram-stories-download', 'recaptcha-error', 'instagram-stories-download-function', 1);
                echo json_encode(['message' => 'Recaptcha Error']);
                die;
            }
    
            die;
    
            break;
    
        case "post":
            $now = time();
            $post_url = addslashes($_POST['post_url']);
    
            $recaptchaResp = checkRecaptcha($googleV3ServerKey);
    
            if ($recaptchaResp) {
    
                $matches = 'instagram';
    
                $parse = parse_url($post_url);
                $domain = $parse['host'];
    
                if (strpos($post_url, $matches) == false || strpos($domain, $matches) == false) {
                    echo json_encode(['message' => 'Invalid url']);
                    die;
                }
    
                $shortcodes = explode('/', $post_url);
                $shortcode = $shortcodes[4];
    
                if ($shortcode == 'p' || strlen($shortcode) < 11) {
                    $shortcode = $shortcodes[5];
                }
    
                if (strlen($shortcode) < 11) {
                    echo json_encode(['message' => 'Invalid url']);
                    die;
                }

                $query = mysql_query("INSERT INTO `free_api_stats`
                
                SET 
                `added` = '$now', 
                `ordersession` = '',
                `source` = 'rapidapi',
                `type` = 'post'
                            
                ");

                $lastApiStatsId = mysql_insert_id();		
                
                $starttime = microtime(true);
                
                $url = 'https://flashapi1.p.rapidapi.com/ig/post_info/?shortcode='. $shortcode .'&nocors=false';

                //ATTEMPT TODO IT OUR WAY
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-rapidapi-host: $rapidapihost","x-rapidapi-key: $rapidapikey"));
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_ENCODING, '');
                curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        
                $get = curl_exec($curl);
        
                $resp = json_decode($get);
                curl_close($curl);
                $username = $resp->items[0]->user->username;
                $img_url = $resp->items[0]->image_versions2->candidates[0]->url;
                $media_type = $resp->items[0]->media_type;
                $video_url = $resp->items[0]->video_versions[2]->url; //video

                $endtime = microtime(true);

                $loadtime = $endtime - $starttime;
                if(empty($loadtime)) $loadtime = 0;
                mysql_query("UPDATE `free_api_stats`
                                SET 
                                `loadtime` = '$loadtime', `igusername` = '$username'
                                WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

                if(empty($video_url)){

                     // mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_freetools_post' AND `brand` = 'sv' LIMIT 1");
                    // sendCloudwatchData('Superviral', 'supernova-api-freetools-post', 'FreeToolsAjax', 'supernova-api-freetools-post-function', 1);
                    $query = mysql_query("INSERT INTO `free_api_stats`
                
                    SET 
                    `added` = '$now', 
                    `ordersession` = '',
                    `source` = 'rapidapi',
                    `type` = 'post'

                    ");

                    $lastApiStatsId = mysql_insert_id();		
                    
                    $starttime = microtime(true);

                    $url = 'https://flashapi1.p.rapidapi.com/ig/post_info/?shortcode='. $shortcode .'&nocors=false';

                    //ATTEMPT TODO IT OUR WAY
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array("x-rapidapi-host: $rapidapihost","x-rapidapi-key: $rapidapikey"));
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                    curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_ENCODING, '');
                    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
                    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            
                    $get = curl_exec($curl);
            
                    $resp = json_decode($get);
                    curl_close($curl);
                    $username = $resp->items[0]->user->username;
                    $img_url = $resp->items[0]->image_versions2->candidates[0]->url;
                    $media_type = $resp->items[0]->media_type;
                    $video_url = $resp->items[0]->video_versions[2]->url; //video
                    
                    $endtime = microtime(true);

                    $loadtime = $endtime - $starttime;
                    if(empty($loadtime)) $loadtime = 0;
                    mysql_query("UPDATE `free_api_stats`
                                        SET 
                                        `loadtime` = '$loadtime', `igusername` = '$username'
                                        WHERE id = '$lastApiStatsId' ORDER BY id DESC LIMIT 1;");

                }
    
                if (!empty($img_url)) {
    
    
                    if ($media_type != 2) {

                    } else {

                        sendCloudwatchData('Superviral', 'free-video-download', 'VideoDownload', 'fv-download-success-function', 1);

                        require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';
    
                        $s3 = new S3($amazons3key, $amazons3password);
    
                        $dpimgname = md5(time() . $username);
    
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $img_url);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                        curl_setopt($curl, CURLOPT_ENCODING, '');
    
    
                        $get1 = curl_exec($curl);
    
                        curl_close($curl);
                        $putobject = S3::putObject($get1, 'cdn.superviral.io', 'thumbs/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);
                        if($putobject){
                            sendCloudwatchData('Superviral', 's3-image-upload-success', 'FreeToolsAjax', 's3-image-upload-success-function', 1);
            
                        }else{
                            sendCloudwatchData('Superviral', 's3-image-upload-failure', 'FreeToolsAjax', 's3-image-upload-failure-function', 1);
            
                        }
                        $thumb_url = "https://cdn.superviral.io/thumbs/" . $dpimgname . ".jpg"; //thumb
                    }
    
                    $dataArray['url'] = $thumb_url;
                    $dataArray['video_url'] = $video_url;
                    $dataArray['media_type'] = $media_type;
    
                } else {
                    echo json_encode(['message' => 'Not found, please try again later!']);
                    die;
                }
    
                $dataArray = ['post' => $dataArray];
    
                $endtime = microtime(true);
                $loadtime = $endtime - $starttime;
    
                echo json_encode(['data' => $dataArray, 'message' => 'success']);
            } else {
                sendCloudwatchData('Superviral', 'instagram-post-download', 'recaptcha-error', 'instagram-post-download-function', 1);
                echo json_encode(['message' => 'Recaptcha Error']);
                die;
            }
    
            die;
    
        break;
        case "tiktok_post":
    
            $post_url = addslashes($_POST['post_url']);
    
            $recaptchaResp = checkRecaptcha($googleV3ServerKey);
    
            if ($recaptchaResp) {
    
                $matches = 'tiktok';
    
                $parse = parse_url($post_url);
                $domain = $parse['host'];
    
                if (strpos($post_url, $matches) == false || strpos($domain, $matches) == false) {
                    echo json_encode(['message' => 'Invalid url']);
                    die;
                }
    
                $shortcodes = explode('/', $post_url);
                $shortcode = trim($shortcodes[5]);
    
                if (strlen($shortcode) < 19) {
                    echo json_encode(['message' => 'Invalid url']);
                    die;
                }
                // mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'supernova_api_freetools_tiktok_post' AND `brand` = 'sv' LIMIT 1");
                sendCloudwatchData('Superviral', 'supernova-api-freetools-tiktok-post', 'FreeToolsAjax', 'supernova-api-freetools-tiktok-post-function', 1);
    
                $starttime = microtime(true);
                $url = 'https://t.supernova-493.workers.dev/api/v2/tiktok/video?videoId=' . $shortcode;
    
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $tikoidSocialScrapeKey"));
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_TIMEOUT, 20);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    
                $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
                $get = curl_exec($curl);
                $get = json_decode($get);
    
                if (!empty($get->data->aweme_detail)) {
    
                    $img_url = $get->data->aweme_detail->video->ai_dynamic_cover->url_list[0];
                    // require dirname($_SERVER["DOCUMENT_ROOT"]) . '/etra.group/common/s3/S3.php';
                    // $s3 = new S3($amazons3key, $amazons3password);
                    // $dpimgname = md5(time() . $username);
                    // $curl = curl_init();
                    // curl_setopt($curl, CURLOPT_URL, $img_url);
                    // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                    // curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                    // curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                    // curl_setopt($curl, CURLOPT_ENCODING, '');
                    // $get1 = curl_exec($curl);
                    // curl_close($curl);
                    // $putobject = S3::putObject($get1, 'cdn.superviral.io', 'thumbs/' . $dpimgname . '.jpg', S3::ACL_PUBLIC_READ);
                    $thumb_url = $img_url;
                    // $thumb_url = "https://cdn.superviral.io/thumbs/" . $dpimgname . ".jpg"; //thumb
                    $video_url = $get->data->aweme_detail->video->download_addr->url_list[0]; //video
                    $media_type = $get->data->aweme_detail->content_type;
                    $dataArray['url'] = $thumb_url;
                    $dataArray['video_url'] = $video_url;
                    $dataArray['media_type'] = $media_type;
    
                } else {
                    echo json_encode(['message' => 'Not found, please try again later!']);
                    die;
                }
    
                $dataArray = ['post' => $dataArray];
    
                $endtime = microtime(true);
                $loadtime = $endtime - $starttime;
    
                echo json_encode(['data' => $dataArray, 'message' => 'success']);
            } else {
                echo json_encode(['message' => 'Recaptcha Error']);
                die;
            }
    
            die;
        break;    
}


function checkRecaptcha($secret)
{

    $statsQuery =  mysql_query("SELECT * FROM `admin_statistics` WHERE `type` = 'free_tools_service' LIMIT 1");   
    $statsData = mysql_fetch_array($statsQuery);
    $metricCount = $statsData['metric'];

    mysql_query("UPDATE `admin_statistics` SET `metric` = `metric` + 1 WHERE `type` = 'free_tools_service' LIMIT 1");

    sendCloudwatchData('Superviral', 'free-tools-service', 'AdminStats', 'free-tools-service-function', 1);

    if($metricCount > 30){

    }else{
        return true;
    }

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
