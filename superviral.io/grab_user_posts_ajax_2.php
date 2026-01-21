<?php

 

$loc = $_SERVER['SERVER_NAME'];
$loc = str_replace('superviral.','',$loc);
$loc = array_shift((explode('.', $_SERVER['HTTP_HOST'])));

if(empty($loc))$loc = '';
if($loc=='superviral')$loc = '';
if($loc=='www')$loc = '';
if(!empty($loc))$loc = $loc.'.';

header('Access-Control-Allow-Origin: https://'.$loc.'superviral.io');
include('db.php');
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

*/

/*


can specify on query string:

videsonly=1
ordersession=1 (this is to get the profile picture)

BY DEFAULT WE RETRIEVE THE DP

only option to get is the thumbs


//either dp or thumbs
//when thumbs is selected, download a DP regardless


*/


/*

    @username = ig usermame handle 
    @itr = number of times clicked "view more" button

*/

$username = trim(strtolower(addslashes($_POST['username'])));
$itr = trim(strtolower(addslashes($_POST['itr'])));
$userId = trim(strtolower(addslashes($_POST['userId'])));

if($itr == ""){

    if(empty($username))die('1');


        $totalresults = 0;
        
         $starttime = microtime(true);
    
         $url = 'https://api.lamadava.com/a1/user?username='.$username;
         
    
        //ATTEMPT TODO IT OUR WAY
        $curl = curl_init(); 
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "x-access-key: $lamadavaaccess" ));
        curl_setopt($curl, CURLOPT_URL, $url); 
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
    
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        $get2 = curl_exec($curl);
    
    
        //IF PAGE CANT BE FOUND
    
        if (strpos($get2, 'Not Found for url:') !== false){echo 'Error 3420';die;}
    
        $get = json_decode($get2);
    
        curl_close($curl);
    
        if($get == null) {
           //  echo json_encode(['message' =>'Not found try again']);die;
            
           $url = 'https://api.datalama.io/a1/user?username='.$username;
                
                
           //ATTEMPT TODO IT OUR WAY
           $curl = curl_init(); 
           curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
           curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "x-access-key: $lamadavaaccess" ));
           curl_setopt($curl, CURLOPT_URL, $url); 
           curl_setopt($curl, CURLOPT_TIMEOUT, 5);
           curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
           curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
           curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
           curl_setopt($curl, CURLOPT_ENCODING, '');
                   
           $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                   
           $get = curl_exec($curl);
                   
           $get = json_decode($get);
  
          }

          if($get == null) {
             echo json_encode(['message' =>'Not found try again']);die;
          } 

            if(isset($get->state) && $get->state == false){
    
                echo json_encode(['message' => $get->error]);die;
            
            }else{
        
            
            $arrays = $get -> graphql -> user -> edge_owner_to_timeline_media -> edges;
            // print_r($arrays);  die;
            $isprivate = $get -> graphql -> user -> is_private;
            $userId = $get -> graphql -> user -> id;	
        
            if(!$isprivate){
        
            
                $dataArray = [];
        
                if(isset($arrays)){//this means we've successfully requested and received $get, and the account is on public
        
                //////////////////////
        
        
                        foreach($arrays as $thumbnail){
        
                        $isvideo = $thumbnail -> node -> is_video;
        
                        // if(($videosonly=='1')&&($isvideo=='0'))continue;
        
                        $thumbnailurls = $thumbnail -> node -> thumbnail_resources;
                        $thumbnailurl = $thumbnailurls[0] -> src;
                        $shortcode = $thumbnail -> node -> shortcode;
                        $postTime = $thumbnail -> node-> taken_at_timestamp;
                        $videoUrl = $thumbnail -> node-> video_url;
                        $imageUrl = $thumbnail -> node -> display_url;

                        // $like = $thumbnail -> node -> edge_liked_by;
                        // $view = $thumbnail -> node -> edge_liked_by;
                        
                        if ($isvideo == 1) {	
                            $mediaType = "video";	
                            $postUrl = $videoUrl;
                            // $views = $views;	
                        } else {	
                            $mediaType = "image";	
                            $postUrl = $imageUrl;
                            // $views = 0;	
                        }
        
                        if(empty($shortcode))continue;
        
                        $newimgname = md5('superviralrb'.$shortcode);
        
                        if($_GET['rabban']=='true')echo 'Image name: '.$newimgname.'<br>';
                        if($_GET['rabban']=='true')echo 'Thumbnail URL: '.$thumbnailurl.'<br>';
        
                        $dataArray[$totalresults]['media_type'] = $mediaType;  
                        $dataArray[$totalresults]['video_url'] = $videoUrl;  
        
        
        
                        $curlThumb = curl_init();
                        curl_setopt($curlThumb, CURLOPT_URL, $thumbnailurl); 
                        curl_setopt($curlThumb, CURLOPT_RETURNTRANSFER, 1); 
                        curl_setopt($curlThumb, CURLOPT_FOLLOWLOCATION, true); 
                        curl_setopt($curlThumb, CURLOPT_TIMEOUT, 10);
                        // curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
                        // curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 
                        curl_setopt($curlThumb, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                        curl_setopt($curlThumb, CURLOPT_ENCODING, '');
        
        
                        $getThumb = curl_exec($curlThumb);
        
                        curl_close($curlThumb);

                        $htm = '<div class="content-item">
                        <img src="data:image/jpeg;base64,'.base64_encode($getThumb).'" alt="Image">
                        
                        <ul class="icon-bar" style="display: none;">
                            <li>
                                <span class="icon">
                                    <svg width="23" height="20" viewBox="0 0 23 20">
                                        <path d="M6.43655 0.320312C4.92512 0.320312 3.57857 0.952366 2.56178 1.94167C1.57248 2.93097 0.94043 4.27752 0.94043 5.81643C0.94043 7.32787 1.57248 8.67441 2.56178 9.6912L11.9327 19.0621L21.3036 9.6912C22.2929 8.7019 22.9249 7.35535 22.9249 5.81643C22.9249 4.305 22.2929 2.95845 21.3036 1.94167C20.3143 0.952366 18.9677 0.320313 17.4288 0.320313C15.9174 0.320313 14.5708 0.952366 13.554 1.94167C12.5647 2.93097 11.9327 4.27752 11.9327 5.81643C11.9327 4.305 11.3006 2.95845 10.3113 1.94167C9.32201 0.952366 7.97546 0.320312 6.43655 0.320312Z"/>
                                    </svg>
                                </span>
                                <span class="text">
                                    5K
                                </span>
                            </li>
                            <li>
                                <span class="icon">
                                    <svg width="21" height="21" viewBox="0 0 21 21">
                                        <path d="M0.876177 0.530838C0.726304 0.530838 0.651367 0.630754 0.651367 0.755648V15.2683C0.651367 15.3932 0.751282 15.4932 0.876177 15.4932H15.6387L20.6344 20.4889V0.730669C20.6344 0.580796 20.5345 0.505859 20.4096 0.505859H0.901155L0.876177 0.530838Z"/>
                                    </svg>
                                </span>
                                <span class="text">
                                    5.2K
                                </span>
                            </li>
                            <li>
                                <span class="icon">
                                    <svg width="24" height="17" viewBox="0 0 24 17">
                                        <path d="M12.0912 0.0605469C5.06939 0.0605469 0.90625 8.38682 0.90625 8.38682C0.90625 8.38682 5.06939 16.7131 12.0912 16.7131C18.9465 16.7131 23.1097 8.38682 23.1097 8.38682C23.1097 8.38682 18.9465 0.0605469 12.0912 0.0605469ZM12.008 2.83597C15.0887 2.83597 17.5588 5.33385 17.5588 8.38682C17.5588 11.4675 15.0887 13.9377 12.008 13.9377C8.95498 13.9377 6.4571 11.4675 6.4571 8.38682C6.4571 5.33385 8.95498 2.83597 12.008 2.83597ZM12.008 5.6114C10.4815 5.6114 9.23253 6.86034 9.23253 8.38682C9.23253 9.91331 10.4815 11.1622 12.008 11.1622C13.5344 11.1622 14.7834 9.91331 14.7834 8.38682C14.7834 8.10928 14.6724 7.85949 14.6169 7.6097C14.3948 8.05377 13.9507 8.38682 13.3957 8.38682C12.6185 8.38682 12.008 7.77623 12.008 6.99911C12.008 6.44403 12.341 5.99996 12.7851 5.77792C12.5353 5.69466 12.2855 5.6114 12.008 5.6114Z"/>
                                    </svg>
                                </span>
                                <span class="text">
                                    8.3K
                                </span>
                            </li>
                        </ul>
                        <div class="download-btn">
                            <a href="'. $postUrl .'" target ="_blank" class="icon">
                                <svg width="30" height="30" viewBox="0 0 30 30">
                                    <path d="M11.154 0.0615234V11.1315H3.77399L14.844 22.2016L25.914 11.1315H18.534V0.0615234H11.154ZM0.0839844 25.8916V29.5816H29.604V25.8916H0.0839844Z"/>
                                </svg>
                            </a>
                        </div>
                    </div>';
        
                        $dataArray[$totalresults]['thumb'] = $htm;  
        
                        $totalresults++;
        
                        }
                        
                        echo json_encode(['data' => $dataArray, 'userId' => $userId , 'message' => 'success']);die;
        
        
                }else{
        
        
                    $notfound = 1;
        
                    echo json_encode(['message' =>'Posts not found']);die;
        
                }
        
            }else{
                    echo json_encode(['message' =>'Your account is on private please make it public than try again']);die;
            }
        
        
        }
    
}elseif($itr == "second" ){

    $url = 'https://api.lamadava.com/v1/user/medias?user_id='.$userId;

    //ATTEMPT TODO IT OUR WAY
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "x-access-key: $lamadavaaccess" ));
    curl_setopt($curl, CURLOPT_URL, $url); 
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_ENCODING, '');

    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);

    $get = json_decode($get);

    $countPost = count($get);


    
    curl_close($curl);

    if ($countPost > 0) { //this means we've successfully requested and received $get, and the account is on public

    $dataArray = [];
    $totalresults = 0;
    $key = 0;
    foreach ($get as $thumbnail) {

        if($totalresults < 12 || $totalresults > 23){
            $totalresults++;
            continue;
        }

        $isvideo = $thumbnail->media_type;
        // $thumbnailurl = $thumbnail -> node -> thumbnail_resources;
        $thumbnailurl = $thumbnail->thumbnail_url;
        $videoUrl = $thumbnail -> video_url;
        $imageUrl =  $thumbnail->image_versions[0]->url;
       

        $insideCall = false;
        if ($thumbnailurl == null) {
            $thumbnailurl = $thumbnail->resources[0]->thumbnail_url;
            $imageUrl =  $thumbnail->resources[0]->image_versions[0]->url;
            $isvideo = $thumbnail->resources[0]->media_type;
            $insideCall = true;
        }

        if($insideCall){
            if ($isvideo == 0) {	
                $mediaType = "video";
                $postUrl = $videoUrl;	
                // $views = $views;	
            } else {	
                $mediaType = "image";
                $postUrl = $imageUrl;	
                // $views = 0;	
            }
        }else{
            if ($isvideo == 2) {	
                $mediaType = "video";	
                $postUrl = $videoUrl;	
                // $views = $views;	
            } else {	
                $mediaType = "image";	
                $postUrl = $imageUrl;
                // $views = 0;	
            }
        }

        $dataArray[$key]['media_type'] = $mediaType;  
        $dataArray[$key]['video_url'] = $videoUrl;  



        $curlThumb = curl_init();
        curl_setopt($curlThumb, CURLOPT_URL, $thumbnailurl); 
        curl_setopt($curlThumb, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($curlThumb, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($curlThumb, CURLOPT_TIMEOUT, 10);
        // curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
        // curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 
        curl_setopt($curlThumb, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        curl_setopt($curlThumb, CURLOPT_ENCODING, '');


        $getThumb = curl_exec($curlThumb);

        curl_close($curlThumb);

        $htm = '<div class="content-item">
        <img src="data:image/jpeg;base64,'.base64_encode($getThumb).'" alt="Image">
        
        <ul class="icon-bar" style="display: none;">
            <li>
                <span class="icon">
                    <svg width="23" height="20" viewBox="0 0 23 20">
                        <path d="M6.43655 0.320312C4.92512 0.320312 3.57857 0.952366 2.56178 1.94167C1.57248 2.93097 0.94043 4.27752 0.94043 5.81643C0.94043 7.32787 1.57248 8.67441 2.56178 9.6912L11.9327 19.0621L21.3036 9.6912C22.2929 8.7019 22.9249 7.35535 22.9249 5.81643C22.9249 4.305 22.2929 2.95845 21.3036 1.94167C20.3143 0.952366 18.9677 0.320313 17.4288 0.320313C15.9174 0.320313 14.5708 0.952366 13.554 1.94167C12.5647 2.93097 11.9327 4.27752 11.9327 5.81643C11.9327 4.305 11.3006 2.95845 10.3113 1.94167C9.32201 0.952366 7.97546 0.320312 6.43655 0.320312Z"/>
                    </svg>
                </span>
                <span class="text">
                    5K
                </span>
            </li>
            <li>
                <span class="icon">
                    <svg width="21" height="21" viewBox="0 0 21 21">
                        <path d="M0.876177 0.530838C0.726304 0.530838 0.651367 0.630754 0.651367 0.755648V15.2683C0.651367 15.3932 0.751282 15.4932 0.876177 15.4932H15.6387L20.6344 20.4889V0.730669C20.6344 0.580796 20.5345 0.505859 20.4096 0.505859H0.901155L0.876177 0.530838Z"/>
                    </svg>
                </span>
                <span class="text">
                    5.2K
                </span>
            </li>
            <li>
                <span class="icon">
                    <svg width="24" height="17" viewBox="0 0 24 17">
                        <path d="M12.0912 0.0605469C5.06939 0.0605469 0.90625 8.38682 0.90625 8.38682C0.90625 8.38682 5.06939 16.7131 12.0912 16.7131C18.9465 16.7131 23.1097 8.38682 23.1097 8.38682C23.1097 8.38682 18.9465 0.0605469 12.0912 0.0605469ZM12.008 2.83597C15.0887 2.83597 17.5588 5.33385 17.5588 8.38682C17.5588 11.4675 15.0887 13.9377 12.008 13.9377C8.95498 13.9377 6.4571 11.4675 6.4571 8.38682C6.4571 5.33385 8.95498 2.83597 12.008 2.83597ZM12.008 5.6114C10.4815 5.6114 9.23253 6.86034 9.23253 8.38682C9.23253 9.91331 10.4815 11.1622 12.008 11.1622C13.5344 11.1622 14.7834 9.91331 14.7834 8.38682C14.7834 8.10928 14.6724 7.85949 14.6169 7.6097C14.3948 8.05377 13.9507 8.38682 13.3957 8.38682C12.6185 8.38682 12.008 7.77623 12.008 6.99911C12.008 6.44403 12.341 5.99996 12.7851 5.77792C12.5353 5.69466 12.2855 5.6114 12.008 5.6114Z"/>
                    </svg>
                </span>
                <span class="text">
                    8.3K
                </span>
            </li>
        </ul>
        <div class="download-btn">
            <a href="'. $postUrl .'" target ="_blank" class="icon">
                <svg width="30" height="30" viewBox="0 0 30 30">
                    <path d="M11.154 0.0615234V11.1315H3.77399L14.844 22.2016L25.914 11.1315H18.534V0.0615234H11.154ZM0.0839844 25.8916V29.5816H29.604V25.8916H0.0839844Z"/>
                </svg>
            </a>
        </div>
    </div>';

        $dataArray[$key]['thumb'] = $htm;  


        $key++;
        $totalresults++;
    }
    echo json_encode(['data' => $dataArray, 'userId' => $userId , 'message' => 'success']);die;
    
    } else {

    $notfound = 1;
    echo json_encode(['message' =>'Posts not found']);die;
}


}elseif($itr == "third" ){

    $url = 'https://api.lamadava.com/v1/user/medias?user_id='.$userId;

    //ATTEMPT TODO IT OUR WAY
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET' );
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json' , "x-access-key: $lamadavaaccess" ));
    curl_setopt($curl, CURLOPT_URL, $url); 
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($curl, CURLOPT_TCP_FASTOPEN, 1 );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_ENCODING, '');

    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);

    $get = json_decode($get);

    $countPost = count($get);


    
    curl_close($curl);

    
    if ($countPost > 0) { //this means we've successfully requested and received $get, and the account is on public

    $dataArray = [];
    $totalresults = 0;
    $key = 0;
    foreach ($get as $thumbnail) {

        if($totalresults < 24){
            $totalresults++;
            continue;
        }

        $isvideo = $thumbnail->media_type;
        // $thumbnailurl = $thumbnail -> node -> thumbnail_resources;
        $thumbnailurl = $thumbnail->thumbnail_url;
        $videoUrl = $thumbnail -> video_url;
        $imageUrl =  $thumbnail->image_versions[0]->url;
       

        $insideCall = false;
        if ($thumbnailurl == null) {
            $thumbnailurl = $thumbnail->resources[0]->thumbnail_url;
            $imageUrl =  $thumbnail->resources[0]->image_versions[0]->url;
            $isvideo = $thumbnail->resources[0]->media_type;
            $insideCall = true;
        }

        if($insideCall){
            if ($isvideo == 0) {	
                $mediaType = "video";
                $postUrl = $videoUrl;	
                // $views = $views;	
            } else {	
                $mediaType = "image";
                $postUrl = $imageUrl;	
                // $views = 0;	
            }
        }else{
            if ($isvideo == 2) {	
                $mediaType = "video";	
                $postUrl = $videoUrl;	
                // $views = $views;	
            } else {	
                $mediaType = "image";	
                $postUrl = $imageUrl;
                // $views = 0;	
            }
        }

        $dataArray[$key]['media_type'] = $mediaType;  
        $dataArray[$key]['video_url'] = $videoUrl;  



        $curlThumb = curl_init();
        curl_setopt($curlThumb, CURLOPT_URL, $thumbnailurl); 
        curl_setopt($curlThumb, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($curlThumb, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($curlThumb, CURLOPT_TIMEOUT, 10);
        // curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
        // curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 
        curl_setopt($curlThumb, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        curl_setopt($curlThumb, CURLOPT_ENCODING, '');


        $getThumb = curl_exec($curlThumb);

        curl_close($curlThumb);

        $htm = '<div class="content-item">
        <img src="data:image/jpeg;base64,'.base64_encode($getThumb).'" alt="Image">
        
        <ul class="icon-bar" style="display: none;">
            <li>
                <span class="icon">
                    <svg width="23" height="20" viewBox="0 0 23 20">
                        <path d="M6.43655 0.320312C4.92512 0.320312 3.57857 0.952366 2.56178 1.94167C1.57248 2.93097 0.94043 4.27752 0.94043 5.81643C0.94043 7.32787 1.57248 8.67441 2.56178 9.6912L11.9327 19.0621L21.3036 9.6912C22.2929 8.7019 22.9249 7.35535 22.9249 5.81643C22.9249 4.305 22.2929 2.95845 21.3036 1.94167C20.3143 0.952366 18.9677 0.320313 17.4288 0.320313C15.9174 0.320313 14.5708 0.952366 13.554 1.94167C12.5647 2.93097 11.9327 4.27752 11.9327 5.81643C11.9327 4.305 11.3006 2.95845 10.3113 1.94167C9.32201 0.952366 7.97546 0.320312 6.43655 0.320312Z"/>
                    </svg>
                </span>
                <span class="text">
                    5K
                </span>
            </li>
            <li>
                <span class="icon">
                    <svg width="21" height="21" viewBox="0 0 21 21">
                        <path d="M0.876177 0.530838C0.726304 0.530838 0.651367 0.630754 0.651367 0.755648V15.2683C0.651367 15.3932 0.751282 15.4932 0.876177 15.4932H15.6387L20.6344 20.4889V0.730669C20.6344 0.580796 20.5345 0.505859 20.4096 0.505859H0.901155L0.876177 0.530838Z"/>
                    </svg>
                </span>
                <span class="text">
                    5.2K
                </span>
            </li>
            <li>
                <span class="icon">
                    <svg width="24" height="17" viewBox="0 0 24 17">
                        <path d="M12.0912 0.0605469C5.06939 0.0605469 0.90625 8.38682 0.90625 8.38682C0.90625 8.38682 5.06939 16.7131 12.0912 16.7131C18.9465 16.7131 23.1097 8.38682 23.1097 8.38682C23.1097 8.38682 18.9465 0.0605469 12.0912 0.0605469ZM12.008 2.83597C15.0887 2.83597 17.5588 5.33385 17.5588 8.38682C17.5588 11.4675 15.0887 13.9377 12.008 13.9377C8.95498 13.9377 6.4571 11.4675 6.4571 8.38682C6.4571 5.33385 8.95498 2.83597 12.008 2.83597ZM12.008 5.6114C10.4815 5.6114 9.23253 6.86034 9.23253 8.38682C9.23253 9.91331 10.4815 11.1622 12.008 11.1622C13.5344 11.1622 14.7834 9.91331 14.7834 8.38682C14.7834 8.10928 14.6724 7.85949 14.6169 7.6097C14.3948 8.05377 13.9507 8.38682 13.3957 8.38682C12.6185 8.38682 12.008 7.77623 12.008 6.99911C12.008 6.44403 12.341 5.99996 12.7851 5.77792C12.5353 5.69466 12.2855 5.6114 12.008 5.6114Z"/>
                    </svg>
                </span>
                <span class="text">
                    8.3K
                </span>
            </li>
        </ul>
        <div class="download-btn">
            <a href="'. $postUrl .'" target ="_blank" class="icon">
                <svg width="30" height="30" viewBox="0 0 30 30">
                    <path d="M11.154 0.0615234V11.1315H3.77399L14.844 22.2016L25.914 11.1315H18.534V0.0615234H11.154ZM0.0839844 25.8916V29.5816H29.604V25.8916H0.0839844Z"/>
                </svg>
            </a>
        </div>
    </div>';

        $dataArray[$key]['thumb'] = $htm;  

        
        $key++;
        $totalresults++;
    }
    echo json_encode(['data' => $dataArray, 'userId' => $userId , 'message' => 'success']);die;
    
    } else {

    $notfound = 1;
    echo json_encode(['message' =>'Posts not found']);die;
}


}
