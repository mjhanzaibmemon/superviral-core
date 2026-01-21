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
$Query_eq = mysql_query('SELECT * FROM restaurants');
$i = 1;
while ($row = mysql_fetch_array($Query_eq)) {
        $address = $row['address'];

        echo $i . ') Processing address: ' . $address . '<br>';
        echo '<br>';
        $clean_name = chat($address);
        $clean_name = addslashes($clean_name);
        echo 'return address: '. $clean_name;
      
        echo '<br>';
        echo '<br><hr>';

        mysql_query("UPDATE restaurants SET address = '$clean_name' WHERE id = " . $row['id']);

        $i++;
        // die;
}

function chat($address){
    global $openAiKey;

    $url = "https://api.openai.com/v1/chat/completions";
    // Message Data
    $data = [
        "model" => "gpt-4.1", 
        "messages" => [
            ["role" => "system", "content" => "You are a data cleaning assistant."],
            ["role" => "user", "content" => "

                standardize this {$address} UK address into the following format:

                [Street Address], [City or Town], [Postcode], United Kingdom
                
                Use title case for all address components.

                Remove any regions or duplicate geographical terms like 'EMEA' or 'England'.

                If the street address includes unit numbers, shopping centres, or road names, preserve them.

                Ensure proper spacing and punctuation.

                Do not invent missing data. Only use what's present.

                Always append 'United Kingdom' at the end.
                Provide the output as a valid address string. Do not include any extra formatting, text, or code block markers.
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
