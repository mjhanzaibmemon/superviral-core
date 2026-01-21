<?php

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

*/

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php';



// customer email
$Query_eq = mysql_query("SELECT * FROM email_queue WHERE markDone=1 AND ai_trained=0 LIMIT 1");
// support response
$Query_esr = mysql_query("SELECT * FROM email_queue WHERE markDone=1 AND ai_trained=0 LIMIT 1");
// select record to fine tune, using category
$Query_kb = mysql_query("SELECT * FROM ai_support_kb WHERE `category`='".$row['category']."' LIMIT 1");

$convo = "
[CUSTOMER]: Hello xyz
[SUPPORT]: Hi
[CUSTOMER]: Great, thanks!
END CONVO
";

$trained_data = $row_kb['text'];
if($trained_data){
    $new_doc = chat();
}

function chat($row){
    global $openAiKey, $trained_data,$convo;

    $url = "https://api.openai.com/v1/chat/completions";
    // Message Data
    $data = [
        "model" => "gpt-4o", 
        "messages" => [
            ["role" => "system", "content" => "I am a customer support agent. I work in a customer support agency. You are an expert on customer support."],
            ["role" => "user", "content" => "
                I am developing a document to help train new support agents. My document will contain support cases.

                Here is a new customer interaction:
                ".$convo."

                Summarize the new interaction, strictly follow these rules:
                - Only state the main points and problems
                - Remove any greetings

                Return only your summary

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
