<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');


$apiKey = "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";


// chat();

//creating doc
create_ref("support_doc.json","It may take up to 61 hours for your order to be fulfilled.");

$stored_embeddings = json_decode(file_get_contents('support_doc.json'), true);
$query_embed = embed("How long will it take to fulfill my order?");

$best_match = null;
$highest_similarity = -1;

foreach ($stored_embeddings as $embedding) {
    $similarity = cosine_similarity($query_embedding, $embedding['vector']);
    if ($similarity > $highest_similarity) {
        $highest_similarity = $similarity;
        // Assuming each embedding has an associated text
        $best_match = $embedding['text'];
    }
}

echo $best_match;
die;

function cosine_similarity($vec1, $vec2) {
    $dot_product = array_sum(array_map(fn($x, $y) => $x * $y, $vec1, $vec2));
    $magnitude1 = sqrt(array_sum(array_map(fn($x) => $x * $x, $vec1)));
    $magnitude2 = sqrt(array_sum(array_map(fn($x) => $x * $x, $vec2)));
    return $dot_product / ($magnitude1 * $magnitude2);
}

function embed($msg){
    global $apiKey;
    
    $url = "https://api.openai.com/v1/embeddings";

    $data = [
        "model" => "text-embedding-ada-002",
        "encoding_format" => "float",
        "input" => $msg
    ];

    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL Options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
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
        $decodedResponse = json_decode($response,true);
        return $decodedResponse['data'][0]['embedding'];
    }
    
    // Close cURL
    curl_close($ch);    
}

function create_ref($filename,$msg){
    global $apiKey;
    
    $url = "https://api.openai.com/v1/embeddings";

    $data = [
        "model" => "text-embedding-ada-002",
        "encoding_format" => "float",
        "input" => $msg
    ];

    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL Options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
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
        $decodedResponse = json_decode($response,true);

        file_put_contents($filename, json_encode($decodedResponse['data'][0]['embedding']));
        echo '<a href="'.$filename.'">open '.$filename.'</a>';
    }
    
    // Close cURL
    curl_close($ch);    
}

function chat(){
    global $apiKey;

    $url = "https://api.openai.com/v1/chat/completions";
    // Message Data
    $data = [
        "model" => "gpt-3.5-turbo", 
        "messages" => [
            ["role" => "system", "content" => "I am a customer support agent. I work in a customer support agency. You are an expert on customer support."],

            ["role" => "assistant", "content" => $dataset],
            ["role" => "user", "content" => "A customer sent me this message: \"Hello
    
    It has been over a week since I purchased this package and it still hasn't completed and been fully delivered yet?
    
    This is my second time of writing to chase this up and so far have had no reply.
    
    I have to say that you customer service is very poor and expected much better especially after the first order went so well.
    
    Can someone please get back to me with a reply on why it's taking so long as a over a week for some little follower is far too long.
    
    
    Regards,
    
    Ying Man.\"
    
    
    Recommend me 3 actions directly interacting with the customer, and 3 actions related to organisation. Provide 1 word for each action.
    
    Return json only

    "]
        ],
        "max_tokens" => 150,
        "temperature" => 0,  
        "top_p" => 1,        
    ];
    
    print_r($data);
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL Options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
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

        $json = json_decode($responseDecoded['choices'][0]['message']['content']);
        
        $action_ids = update_email_queue_actions($row['id'],$json); // SET action=action_name, email_msg=email_msg, ref=$row[id]
        update_email_queue($row['id'], $action_ids); // SET recommended_actions = action_ids WHERE id =$row[id]
        
    }
    
    // Close cURL
    curl_close($ch);
    }

}

function update_email_queue_actions($id,$json){
    // loop through each action, 5 in total
    foreach ($json as $item){
        $insert_values[] = "(".addslashes($item['action_name']).", ".addslashes($item['email_msg']).", ".$id.")";
    }

    $query = "INSERT INTO email_queue_actions (first_name, last_name, email) VALUES " . implode(',', $insert_values);
    // return row ids
}

function update_email_queue($id,$action_ids){
    // code
}

?>
