<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php';


/* SPAM GUIDELINE */
$guide_q = mysql_query("SELECT * FROM `ai_support_fixed_kb` WHERE `type`='info' AND `parent_id`='278'");
$guide_row = mysql_fetch_array($guide_q);
$guide = $guide_row['value'];



$Query2 = mysql_query("UPDATE email_queue SET `category`='Spam', `markDone` = 1 WHERE (`from` LIKE '%@amazonses.com' OR `email` IS NULL OR `email` = '') AND `markDone` != 1");
$Query = mysql_query("SELECT * FROM email_queue WHERE (`email_formatted` = '' OR `email_formatted` IS NULL) AND `dateAdded` > unix_timestamp(CURRENT_DATE - interval 7 DAY) AND `from` NOT LIKE '%@amazonses.com' AND (`email` IS NOT NULL OR `email` != '') AND markDone != 1 ORDER BY `dateAdded` DESC LIMIT 10");

while ($row = mysql_fetch_array($Query)){
//echo htmlentities($row['email']);die;
    $email_formatted = chat($row['email'],'format'); // $doc is from $dataset

    $spam = chat($email_formatted.' - From '.$row['from'],'spam'); // $doc is from $dataset
    if($spam !== 'Not Spam'){
        $category = "Spam";
        $spam_level = $spam;
        if($spam_level == 'High'){
            $spam_q = mysql_query("UPDATE `email_queue` SET `category`='".$category."', `spam_level`='".$spam_level."' WHERE `from`='".$row['from']."'");
        }
    }
    
    // $system_checks is from $dataset
    echo "UPDATE `email_queue` SET `email_formatted`='".addslashes($email_formatted)."', `category`='".$category."', `spam_level`='".$spam_level."' WHERE `id`=".$row['id']." LIMIT 1<hr>";
    $update_eq = mysql_query("UPDATE `email_queue` SET `email_formatted`='".addslashes($email_formatted)."', `category`='".$category."', `spam_level`='".$spam_level."' WHERE `id`=".$row['id']." LIMIT 1");

    $category = '';
    $spam_level = '';
}

function chat($msg,$type){
    global $openAiKey,$guide;

    $url = "https://api.openai.com/v1/chat/completions";

    if($type == 'format'){
        // Message Data
        $data = [
            "model" => "gpt-4o", 
            "messages" => [
                ["role" => "system", "content" => "I am a customer support agent. I work in a customer support agency. You are an expert on customer support."],
                ["role" => "user", "content" => "

                    A customer has sent this message: ".addslashes($msg)."

                    You will strictly follow these rules:
                    Remove any html, css and javascript formatting from this message.
                    Return only the customer's message, without any extra words, introductions, or explanations.
                "]
            ]     
        ];
    }

    if($type == 'spam'){

        // Message Data
        $data = [
            "model" => "gpt-4o", 
            "messages" => [
                ["role" => "system", "content" => "I am a customer support agent. I work in a customer support agency. You are an expert on customer support."],
                ["role" => "user", "content" => "

                    A customer has sent this message: ".addslashes($msg)."
                    Here is a general spam guideline:".$guide."

                    Identify this message as either 'Spam' or 'Not Spam'.
                    If this message is spam, identify the spam level as either 'High', 'Medium' or 'Low'
                    If the message is spam then return only the spam level.
                    If the message is 'Not Spam' then return only 'Not Spam'.
                "]
            ]     
        ];
    }

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
