<?php

function emailTpl($orderArray,$orderDataset){
    
    $website = $orderDataset['website'];
    $brand = $orderDataset['brand'];
    $loc2 = $orderArray['country'];
    if (!empty($loc2)) $loc2 = $loc2 . '/';
    if ($loc2 == 'ww/') $loc2 = '';
    if ($loc2 == 'us/') $loc2 = '';


    if($brand == 'sv'){
        /* SUPERVIRAL EMAIL BODY */
        $svtpl = '<p>Hi there,
        <br><br>
        We\'ve just received confirmation that your order #{ordernum} for @{username} is completed at Superviral.</p><br>
        {refill}
        <p>Please view your tracking history for this order:</p><br>
        {ctabtn}
        <br><br>
        <a href="https://superviral.io/{loc2}buy-instagram-{packagetypelink}/" style="color: #2e00f4;
        border: 2px solid #2e00f4;display: block;
        width: 330px;padding: 16px 9px;
        text-decoration: none;-webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        margin: 5px auto;
        font-weight: 700;
        text-align:center;">Buy More Instagram {packagetype} &raquo;</a>
        <br>    

        <a href="https://superviral.io/{loc2}buy-tiktok-{packagetypelink}/" style="color: #2e00f4;
        border: 2px solid #2e00f4;display: block;
        width: 330px;padding: 16px 9px;
        text-decoration: none;-webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        margin: 5px auto;
        font-weight: 700;
        text-align:center;">Buy TikTok {packagetype} &raquo;</a>
        <br>    

        <p>Since 2012, the customer ALWAYS comes first at Superviral.</p><br>   

        <p>Best wishes,</p><br> 

        <p>Superviral Team</p>';
    }

    if($brand == 'to'){
        /* TIKOID EMAIL BODY */
        $totpl = '<p>Hi there,
        <br><br>
        We\'ve just received confirmation that your order #{ordernum} for @{username} is completed at Tikoid.</p><br>
        {refill}
        <p>Please view your tracking history for this order:</p><br>
        {ctabtn}
        <br><br>
        <a href="https://superviral.io/{loc2}buy-instagram-{packagetypelink}/" style="color: #2e00f4;
            border: 2px solid #2e00f4;display: block;
            width: 330px;padding: 16px 9px;
            text-decoration: none;-webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            margin: 5px auto;
            font-weight: 700;
            text-align:center;">Buy More Instagram {packagetype} &raquo;</a>
        <br>

        <a href="https://tikoid.com/buy-tiktok-{packagetypelink}/" style="color: #2e00f4;
            border: 2px solid #2e00f4;display: block;
            width: 330px;padding: 16px 9px;
            text-decoration: none;-webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            margin: 5px auto;
            font-weight: 700;
            text-align:center;">Buy TikTok {packagetype} &raquo;</a>
        <br>

        <p>Since 2012, the customer ALWAYS comes first at Tikoid.</p><br>

        <p>Best wishes,</p><br>

        <p>Tikoid Team</p>';
    }

    $href = 'https://'. $orderDataset['domain'] .'/' . $loc2 . 'track-my-order/' . $orderArray['order_session'] . '/' . $orderArray['id'];
    $ctabtn = '<a href="' . $href . '" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">View Tracking History &raquo;</a>';
        
    if (($orderArray['packagetype'] == 'likes') && ($orderArray['account_id'] !== '0') && ($orderArray['brand']=='sv')) {

        if ($orderDataset['freeautolikes'] == '0') {

            $ctabtn .= '<a href="https://'. $orderDataset['domain'] .'/' . $loc2 . 'account/orders/" style="color: #2e00f4;
                    border: 2px solid #2e00f4;
                    display: block;
                    width: 330px;
                    padding: 16px 9px;
                    text-decoration: none;
                    -webkit-border-radius: 5px;
                    -moz-border-radius: 5px;
                    border-radius: 5px;
                    margin: 5px auto;
                    font-weight: 700;
                    text-align:center;">Get Free Automatic Likes! &raquo;</a>
            ';
        }
    }        

    if (($orderArray['packagetype'] == 'followers') || ($orderArray['packagetype'] == 'freefollowers')) {
        
        $refillmsg = '<p>Now that we\'ve delivered your '.$orderArray['keyword'].' followers, we\'ll monitor your Instagram account for 30-days after placing your order. This is to ensure that the followers you\'ve received - remains on your account.
            </p><br>
            <p>
            If the followers you\'ve ordered drops, don\'t worry - we\'ll refill your account to the amount you\'ve ordered. Our systems monitor and check your account every 12-24 hours. At '. $website .' - the customers always comes first. ❤️</p><br>';
    }

    $subject = 'Delivered: Your Superviral order #' . $orderArray['id'];
    $md5unsub = $orderDataset['unsubmd5'];

    if($brand == 'to'){
        $tpl = file_get_contents( __DIR__ . '/../emailtemplate/tikoidordercomplete.html');
    }else{
        $tpl = file_get_contents( __DIR__ . '/../emailtemplate/ordercomplete.html');
    }

    // echo $tpl;die;
    if($brand == 'sv') $tpl = str_replace('{body}', $svtpl, $tpl);
    if($brand == 'to') $tpl = str_replace('{body}', $totpl, $tpl);
    $tpl = str_replace('{loc2}', $loc2, $tpl);
    $tpl = str_replace('{subject}', $subject, $tpl);
    $tpl = str_replace('{ordernum}', $orderArray['id'], $tpl);
    $tpl = str_replace('{ctabtn}', $ctabtn, $tpl);
    $tpl = str_replace('{username}', $orderArray['igusername'], $tpl);
    $tpl = str_replace('{refill}', $refillmsg, $tpl);
    $tpl = str_replace('{md5unsub}', $md5unsub, $tpl);
    $tpl = str_replace('{md5unsub}', $md5unsub, $tpl);
    $formattedDate = date('d/m/Y h:i A', $orderArray['added']);
    $tpl = str_replace('{date_added}', $formattedDate, $tpl);

    $thispackagetype = str_replace('free', '', $orderArray['packagetype']);

    $tpl = str_replace('{packagetypelink}', strtolower($thispackagetype), $tpl);
    $tpl = str_replace('{packagetype}', ucfirst($thispackagetype), $tpl);
    $tpl = str_replace('{website}', $website, $tpl);

    if($orderArray['socialmedia'] == 'tt'){
        $tpl = str_ireplace("Instagram", "Tiktok", $tpl);
    }

    return $tpl;

}
function email_sqs($info){

    // initiate
    $MessageBird = new \MessageBird\Client($messagebirdclient);
    $Message = new \MessageBird\Objects\Message();

    // set params
    $Message->originator = $info['from'];
    $Message->recipients = array($info['to']);
    $Message->body = $info['body'];

    try{$MessageBird->messages->create($Message);}catch (Exception $e) {
        $result = 'Caught exception: '.  $e->getMessage() . "<br>";
    }

    if ($MessageBird) {
        $result = 'Text Message Sent to ' . $info['contactnumber'] . '!<br>';
    }

    return $result;
}

function smsTpl($orderArray,$orderDataset){
    global $bitlyhash;
  
    $website = $orderDataset['website'];
    $domain = $orderDataset['domain'];
    $loc2 = $orderArray['country'];
    if (!empty($loc2)) $loc2 = $loc2 . '/';
    if ($loc2 == 'ww/') $loc2 = '';
    if ($loc2 == 'us/') $loc2 = '';

    if($orderArray['socialmedia'] == "tt"){
        $keyword = "tiktok";
    }else if($orderArray['socialmedia'] == "ig"){
        $keyword = "instagram";
    }

    if (str_contains($orderArray['packagetype'], 'free')) {
        if ($orderArray['packagetype'] == 'freefollowers') {
            $messageBody = '@' . trim($orderArray['igusername']) . ': You\'ve gained +' . $orderArray['amount'] . ' Followers. Have a great weekend! '. $website .' Team. Get more: https://'. $domain .'/' . $loc2 . 'buy-'. $keyword .'-followers/';
        }
        if ($orderArray['packagetype'] == 'freelikes') {
            $messageBody = '@' . trim($orderArray['igusername']) . ': You\'ve gained +' . $orderArray['amount'] . ' Likes. Have a great weekend! '. $website .' Team. Get more: https://'. $domain .'/' . $loc2 . 'buy-'. $keyword .'-likes/';
        }


    } else {
        $messageBody = '@' . trim($orderArray['igusername']) . ': You\'ve gained +' . $orderArray['amount'] . ' ' . str_replace('freefollowers', 'Followers', $orderArray['packagetype']) . '. Have a wonderful day! '. $website .' Team. Order again: https://'. $domain .'/a/' . $bitlyhash;
    }

    return $messageBody;
}

function sms_sqs($info){
    return;
}


function getRandomString($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';

    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string;
}

function detectCountryNumber($phoneNumber) {
    $phoneNumber = preg_replace('/[\s\-\(\)]/', '', $phoneNumber);

    if (preg_match('/^\+1\d{10}$|^\d{10}$/', $phoneNumber)) {
        return "us";
    }

    if (preg_match('/^\+44\d{9,10}$|^0\d{9,10}$/', $phoneNumber)) {
        return "uk";
    }

    return "";
}

/////////////////////

function ago($time)
{
    $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths = array("60", "60", "24", "7", "4.35", "12", "10");
    $now = time();
    $difference     = $now - $time;
    $tense         = 'ago';
    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
        $difference /= $lengths[$j];
    }
    $difference = round($difference);
    if ($difference != 1) {
        $periods[$j] .= "s";
    }
    return "$difference $periods[$j]";
}

function call_supplier($fulfillid)
{
    // return json_decode('{"669497698":{"charge":"1.31175","start_count":"551635","status":"Completed","remains":"0","currency":"USD"}}');
    global $api;
    $order_response = $api->multiStatus($fulfillid);
    $balance = json_decode(json_encode($order_response), True);

    return json_encode($balance);
}

function check_privacy($orderArray){

    $data_response = call_api('', $orderArray['igusername'], '' ,'is_private', '');
    // $data_response = '{"data":{"user":{"primary_profile_link_type":0,"show_fb_link_on_profile":false,"show_fb_page_link_on_profile":false,"can_hide_category":true,"account_type":3,"can_add_fb_group_link_on_profile":false,"fbid_v2":17841450245237636,"full_name":"Suraj Chauhan 🇮🇳","is_private":false,"pk":"50221090841","pk_id":"50221090841","third_party_downloads_enabled":1,"username":"surajchauhanyt","current_catalog_id":null,"mini_shop_seller_onboarding_status":null,"has_guides":false,"is_parenting_account":false,"strong_id__":"50221090841","id":"50221090841","biography":"YouTuber 🎥\nMain Account @ursurajchauhan \nYouTube 1 Million Subs❤️\nDM For Collaboration 📥","biography_with_entities":{"raw_text":"YouTuber 🎥\nMain Account @ursurajchauhan \nYouTube 1 Million Subs❤️\nDM For Collaboration 📥","entities":[{"user":{"id":48968499700,"username":"ursurajchauhan"}}]},"external_lynx_url":"https://l.instagram.com/?u=https%3A%2F%2Fappopener.com%2Fyt%2F6bfhz7p2k%3Ffbclid%3DPAZXh0bgNhZW0CMTEAAaYJEfsMf6yxDazFKi_6GnG4n7YKekckvQblqv-iNZBBv3tM9XGhdXOHXlU_aem_wBygRR22HpYJHbpVR7vF8A&e=AT19acDTtXJ5ParoNMOaufLHQaVM3W_OWAURu8uXPZBQQELtfx71IudGsulgD0ewcLuUvNLDXZ4AbQ9FXRqCl-OexGzFt96P1U8FXPY","external_url":"https://appopener.com/yt/6bfhz7p2k","can_hide_public_contacts":true,"category":"Video creator","should_show_category":true,"category_id":1817904601806120,"is_category_tappable":true,"should_show_public_contacts":true,"is_eligible_for_smb_support_flow":true,"is_eligible_for_lead_center":false,"lead_details_app_id":"com.bloks.www.ig.smb.services.lead_gen.all_leads","is_business":false,"professional_conversion_suggested_account_type":3,"direct_messaging":"","instagram_location_id":"","address_street":"","business_contact_method":"UNKNOWN","city_id":null,"city_name":"","contact_phone_number":"","is_profile_audio_call_enabled":false,"public_email":"surajchauhanyt01@gmail.com","public_phone_country_code":"","public_phone_number":"","zip":"","displayed_action_button_partner":null,"smb_delivery_partner":null,"smb_support_delivery_partner":null,"displayed_action_button_type":"","smb_support_partner":null,"is_call_to_action_enabled":false,"num_of_admined_pages":null,"page_id":null,"page_name":null,"ads_page_id":null,"ads_page_name":null,"bio_links":[{"link_id":17950885079052044,"url":"https://appopener.com/yt/6bfhz7p2k","lynx_url":"https://l.instagram.com/?u=https%3A%2F%2Fappopener.com%2Fyt%2F6bfhz7p2k%3Ffbclid%3DPAZXh0bgNhZW0CMTEAAaYJEfsMf6yxDazFKi_6GnG4n7YKekckvQblqv-iNZBBv3tM9XGhdXOHXlU_aem_wBygRR22HpYJHbpVR7vF8A&e=AT19acDTtXJ5ParoNMOaufLHQaVM3W_OWAURu8uXPZBQQELtfx71IudGsulgD0ewcLuUvNLDXZ4AbQ9FXRqCl-OexGzFt96P1U8FXPY","link_type":"external","title":"YouTube Channel Link","media_type":"none","image_url":"","icon_url":"","is_pinned":false,"is_verified":false,"open_external_url_with_in_app_browser":true,"click_id":"PAZXh0bgNhZW0CMTEAAaYJEfsMf6yxDazFKi_6GnG4n7YKekckvQblqv-iNZBBv3tM9XGhdXOHXlU_aem_wBygRR22HpYJHbpVR7vF8A"}],"account_badges":[],"follower_count":559261,"following_count":1,"has_anonymous_profile_picture":false,"is_verified":true,"latest_reel_media":1731049762,"media_count":2524,"profile_pic_id":"2861306965412075269_50221090841","shopping_post_onboard_nux_type":null,"ads_incentive_expiration_date":null,"active_standalone_fundraisers":{"total_count":0,"fundraisers":[]},"has_chaining":true,"has_gen_ai_personas_for_profile_banner":false,"has_igtv_series":false,"hd_profile_pic_url_info":{"height":1080,"url":"https://scontent-scl2-1.cdninstagram.com/v/t51.2885-19/287938726_767312481114381_7578884459085343910_n.jpg?_nc_ht=scontent-scl2-1.cdninstagram.com&_nc_cat=1&_nc_ohc=--nohT_nCroQ7kNvgHcSXxB&_nc_gid=43840341b68c4b52864c0bbb1fbee569&edm=AKralEIBAAAA&ccb=7-5&oh=00_AYC1jqK5lbNUDAnD82w0DO5ovwIr7MXuV5qku5g1ZmZ2qg&oe=6733E6A0&_nc_sid=2fe71f","width":1080},"hd_profile_pic_versions":[{"height":320,"url":"https://scontent-scl2-1.cdninstagram.com/v/t51.2885-19/287938726_767312481114381_7578884459085343910_n.jpg?stp=dst-jpg_s320x320&_nc_ht=scontent-scl2-1.cdninstagram.com&_nc_cat=1&_nc_ohc=--nohT_nCroQ7kNvgHcSXxB&_nc_gid=43840341b68c4b52864c0bbb1fbee569&edm=AKralEIBAAAA&ccb=7-5&oh=00_AYB4TbblG_sKBFSrQk8RtNvcFeTeJSIDFyGg-jbzWXhRvQ&oe=6733E6A0&_nc_sid=2fe71f","width":320},{"height":640,"url":"https://scontent-scl2-1.cdninstagram.com/v/t51.2885-19/287938726_767312481114381_7578884459085343910_n.jpg?stp=dst-jpg_s640x640&_nc_ht=scontent-scl2-1.cdninstagram.com&_nc_cat=1&_nc_ohc=--nohT_nCroQ7kNvgHcSXxB&_nc_gid=43840341b68c4b52864c0bbb1fbee569&edm=AKralEIBAAAA&ccb=7-5&oh=00_AYAzej8p5Xjg-_Txo_IpGRZytVqdLCcByFps3zhH6vEQpw&oe=6733E6A0&_nc_sid=2fe71f","width":640}],"is_favorite":false,"is_favorite_for_clips":false,"is_favorite_for_igtv":false,"is_favorite_for_stories":false,"live_subscription_status":"default","merchant_checkout_style":"none","mutual_followers_count":0,"pinned_channels_info":{"has_public_channels":true,"pinned_channels_list":[{"creator_igid":null,"creator_username":"surajchauhanyt","group_image_background_uri":"","group_image_uri":"https://scontent-scl2-1.cdninstagram.com/v/t51.2885-19/287938726_767312481114381_7578884459085343910_n.jpg?stp=dst-jpg_s206x206&_nc_cat=1&ccb=1-7&_nc_sid=bf7eb4&_nc_ohc=Wp4_gMl05CYQ7kNvgGe_yF_&_nc_zt=24&_nc_ht=scontent-scl2-1.cdninstagram.com&oh=00_AYDlYvmKDt8eDDPHsf49XH0x37xcEbPVJKlhi9KA_0tXxQ&oe=6733E6A0","invite_link":"https://www.instagram.com/channel/AbblnjYzochBcgB_/","is_creator_verified":true,"is_member":false,"number_of_members":11679,"should_badge_channel":null,"social_channel_invite_id":null,"social_context_username":null,"subtitle":"Broadcast channel • 11K members","thread_igid":"340282366841710301281158730886362766084","thread_subtype":29,"title":"Suraj Chauhan Gang 🔥","creator_broadcast_chat_thread_preview_response":{"audience_type":1,"is_added_to_inbox":false,"is_collaborator":null,"is_follower":null,"is_invited_collaborator":null,"is_subscriber":null}}]},"profile_context":"","profile_context_links_with_user_ids":[],"profile_context_facepile_users":[],"profile_pic_url":"https://scontent-scl2-1.cdninstagram.com/v/t51.2885-19/287938726_767312481114381_7578884459085343910_n.jpg?stp=dst-jpg_e0_s150x150&_nc_ht=scontent-scl2-1.cdninstagram.com&_nc_cat=1&_nc_ohc=--nohT_nCroQ7kNvgHcSXxB&_nc_gid=43840341b68c4b52864c0bbb1fbee569&edm=AKralEIBAAAA&ccb=7-5&oh=00_AYBFAiG8G2dpTZ5oMsmM7_riG0UpoRjoEWqEoQ2alspEHg&oe=6733E6A0&_nc_sid=2fe71f","seller_shoppable_feed_type":"none","show_shoppable_feed":false,"total_clips_count":1,"total_igtv_videos":78,"upcoming_events":[],"adjusted_banners_order":[],"is_eligible_for_request_message":false,"is_open_to_collab":false,"profile_reels_sorting_eligibility":"CHECK_VIEWER_QE","views_on_grid_status":"SHOW_VIEWS_ON_GRID","profile_pic_url_hd":"https://scontent-scl2-1.cdninstagram.com/v/t51.2885-19/287938726_767312481114381_7578884459085343910_n.jpg?stp=dst-jpg_s640x640&_nc_ht=scontent-scl2-1.cdninstagram.com&_nc_cat=1&_nc_ohc=--nohT_nCroQ7kNvgHcSXxB&_nc_gid=43840341b68c4b52864c0bbb1fbee569&edm=AKralEIBAAAA&ccb=7-5&oh=00_AYAzej8p5Xjg-_Txo_IpGRZytVqdLCcByFps3zhH6vEQpw&oe=6733E6A0&_nc_sid=2fe71f"},"status":"ok"}}';
    $response = json_decode($data_response);
    // print_r($response);die;
    $userId = $response->data->user->pk_id;
    $isprivate = $response->data->user->is_private;
    if(!empty($userId)){

        if(empty($isprivate)){
            $isprivate = 'Public';
        }else{
            $id = $orderArray['id'];
            $supplier_error = $orderArray['supplier_errors'];
           
            if(!empty($id)){
                $msg = mysql_query("UPDATE `orders` SET `orderfailed` = 1, `supplier_errors`='{$supplier_error}' WHERE `id` = '$id' LIMIT 1");
                
                // Send the SQL query message to SQS
                // $queryQueueUrl = "https://sqs.us-east-2.amazonaws.com/575108918774/etra-test-checkorderfulfilled-query-queue";
                // sendMessageToSqs($msg, $queryQueueUrl, $sqsClient);
            }    
        }
        return true;
    }else{
        $errorMsg = "Unable to get User data (Code 1): Unavailable: 1 ".  $orderArray['igusername']. "\n\n";
        return $errorMsg;
    }
}

function check_fulfill($data){

    $result = [];

    $fulfills = explode(' ', $data);
    $fulfills = array_filter($fulfills);
    $result['fulfillcount'] = count($fulfills);
   
    $balance = call_supplier($fulfills);
    // $balance = '{"669497727":{"charge":"1.31175","start_count":"552863","status":"Completed","remains":"0","currency":"USD"}}';
    $balance = json_decode($balance, True);
    
    $partial = 0;
    $cancelled = 0;
    $pending = 0;
    $completed = 0;

    foreach ($balance as $key => $order) {
        if ($order['status'] == 'Pending') $pending++;
        if ($order['status'] == 'Partial') $partial++;
        if ($order['status'] == 'Canceled') $cancelled++;
        if ($order['status'] == 'Completed') $completed++;
    }

    if ($pending > 0) {
        sendCloudwatchData('AWSLambda', 'pending-status-order', 'CheckOrderfulfilled', 'pending-status-order-function', $pending);

    } 
    if ($cancelled > 0) {
        sendCloudwatchData('AWSLambda', 'canceled-status-order', 'CheckOrderfulfilled', 'canceled-status-order-function', $cancelled);

    } 
    if ($partial > 0) {
        sendCloudwatchData('AWSLambda', 'partial-status-order', 'CheckOrderfulfilled', 'partial-status-order-function', $partial);

    } 
    if ($completed > 0) {
        sendCloudwatchData('AWSLambda', 'completed-status-order', 'CheckOrderfulfilled', 'completed-status-order-function', $completed);

    } 
    

    $result['partial'] = $partial;
    $result['cancelled'] = $cancelled;
    $result['pending'] = $pending;
    $result['completed'] = $completed;

    // print_r($result);die;
    return json_encode($result);

}

