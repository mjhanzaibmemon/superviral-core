<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');



$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php';


$ids = array();

$Query = mysql_query("SELECT * FROM email_queue WHERE (`recommended_actions` = '' OR `recommended_actions` IS NULL) AND (`category` != '' OR `category` IS NOT NULL) AND `spam_level` != 'High' ORDER BY `dateAdded` asc LIMIT 10");

while ($row = mysql_fetch_array($Query)){

    $category_q = mysql_query("SELECT `id`,`value` FROM `ai_support_fixed_kb` WHERE `type`='category' AND `value`='".$row['category']."'");
    $category_row = mysql_fetch_array($category_q);
    $category = $category_row['value'];
    $category_id = $category_row['id'];

    $category_info = category_info($category_id);
    $category_actions = category_actions($category_id);
    $category_uinfo = category_uinfo($category_id);
    $chat_cases = chat_case($category);

    $response = chat($row['email']); // $doc is from $dataset
    $actions = json_decode($response,true);
    
    foreach($actions as $action){
        $insert_eqa = mysql_query("INSERT INTO `email_queue_actions` SET `email_queue_id`='".$row['id']."', `action`='".addslashes($action['action'])."', `email_msg`='".addslashes($action['email_msg'])."'");
        $last_insert_id = mysql_insert_id();
        $ids[] = array("name"=>$action['action'],"id"=>$last_insert_id);
    }

    // $system_checks is from $dataset
    $update_eq = mysql_query("UPDATE `email_queue` SET `recommended_actions`='".json_encode($ids)."', `system_checks`='".$category_uinfo."' WHERE `id`=".$row['id']." LIMIT 1");

    $ids = array();
    $chat_cases = '';
    $category_actions = '';
    $category_info = '';
    $category_uinfo = '';

}

function category_info($id){
    $category_info_q = mysql_query("SELECT `value` FROM `ai_support_fixed_kb` WHERE `type`='info' AND `parent_id`='".$id."'");
    $category_info_row = mysql_fetch_array($category_info_q);
    return $category_info_row['value'];
}

function category_actions($id){
    $category_info_q = mysql_query("SELECT `value` FROM `ai_support_fixed_kb` WHERE `type`='actions' AND `parent_id`='".$id."'");
    $category_info_row = mysql_fetch_array($category_info_q);
    return $category_info_row['value'];
}

function category_uinfo($id){
    $category_info_q = mysql_query("SELECT `value` FROM `ai_support_fixed_kb` WHERE `type`='user_info' AND `parent_id`='".$id."'");
    $category_info_row = mysql_fetch_array($category_info_q);
    return $category_info_row['value'];
}

function chat_case($cat){
    $chat_q = mysql_query("SELECT `text` FROM `ai_support_dynamic_kb` WHERE `category`='".$cat."'");
    $chat_row = mysql_fetch_array($chat_q);
    return $chat_row['text'];
}

function chat($msg){
    global $openAiKey, $chat_cases,$category_actions,$category_info;

    if($category_info){$prompt_context = "Here is a relevant support document: [CONTEXT START]".$category_info."[CONTEXT END]";}
    if($chat_cases){$prompt_cases = "Here are some relevant example cases: [EXAMPLES START]".$chat_cases."[EXAMPLES END]";}
    if($category_actions){$prompt_actions = "Here are some previously recommended actions:[START]".$category_actions."[END]";}


    $url = "https://api.openai.com/v1/chat/completions";
    // Message Data
    $data = [
        "model" => "gpt-4o", 
        "messages" => [
            ["role" => "system", "content" => "I am a customer support agent. I work in a customer support agency. You are an expert on customer support."],
            ["role" => "user", "content" => "

                A customer has sent this message: ".addslashes($msg)."

                ".addslashes($prompt_cases)."
                
                ".addslashes($prompt_context)."
                
                ".addslashes($prompt_actions)."

                My current set of actions: Offer Followers, Offer Likes, Offer Refill.
                Generate 5 more actions in plain text.
                Re-suggest previously recommended actions if revelant.
                Your word rate limit per action is 2 words.
                For your newly generated actions, generate an email response.
                Store it in a JSON array using the keys 'action' containing the action name and 'email_msg' for the email response.
                Provide the output as a valid JSON array. Do not include any extra formatting, text, or code block markers. Only return the JSON array.
            "]
        ]     
    ];


    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL Options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $openAiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    // Execute cURL and Get Response
    $response = curl_exec($ch);
    
    // Handle Errors
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        // Decode and Display Response
        $responseDecoded = json_decode($response, true);
        $json = $responseDecoded['choices'][0]['message']['content'];

        return $json;
    }
    
    // Close cURL
    curl_close($ch);
}

?>
