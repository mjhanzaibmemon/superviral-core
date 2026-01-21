<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/foodie.app/config/config.php';



// customer email
$Query_eq = mysql_query('SELECT id,name FROM `restaurants` WHERE name LIKE "%(%" OR name LIKE "%-%" LIMIT 1');

$clean_name = chat();

echo $clean_name;

function chat($row){
    global $openAiKey;

    $url = "https://api.openai.com/v1/chat/completions";
    // Message Data
    $data = [
        "model" => "gpt-4o", 
        "messages" => [
            ["role" => "system", "content" => "You are an expert on data entry and analysis."],
            ["role" => "user", "content" => "
                I have the names of stores with the location in the name.
                I am removing the location from the name.
                
                Remove the location from this name:
                ".$row['name']."

                You will strictly follow these rules:
                Return only the company name without any extra words, introductions, or explanations.
                Do not include fullstops in your response.
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
