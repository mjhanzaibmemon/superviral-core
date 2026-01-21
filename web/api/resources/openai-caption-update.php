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
$Query_eq = mysql_query('SELECT * FROM ext_post_qc');
$i = 1;
while ($row = mysql_fetch_array($Query_eq)) {
    $caption = $row['caption'];

        $clean_name = chat($caption);

        echo $i .'. '. $clean_name;
        echo '<hr>';

        echo '<br>';
        echo '<br>';

        $i++;
        // die;
        mysql_query("UPDATE ext_post_qc SET caption_items = '$clean_name' WHERE id = " . $row['id']);
}

function chat($caption){
    global $openAiKey;

    $url = "https://api.openai.com/v1/chat/completions";
    // Message Data
    $data = [
        "model" => "gpt-4.1",
        "messages" => [
            ["role" => "system", "content" => "You are a helpful assistant."],
            ["role" => "user", "content" => "
                I have a description related to a restaurant:
                
                {$caption}

                Give me a list of purchasable products.
                Make plural items into singular.
                Ignore guidelines/urls/ingredients/single ingredient/percentages/marketing language / hashtags.
                Exclude ambiguous items.
                Make plural items into singular.
                Provide variations of the product's name if necessary.
                Return a simple list, separated by a comma.
                If no products found, return a blank list.
            "]
        ]     
    ];
    print_r($data);
    echo '<br><br>';

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
