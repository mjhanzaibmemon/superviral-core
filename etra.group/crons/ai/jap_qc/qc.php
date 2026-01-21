<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// 

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php';

// echo $openAiKey;die;
/* SPAM GUIDELINE */
$Query = mysql_query("SELECT * FROM packages WHERE `type`='followers' AND `socialmedia`='ig' GROUP BY `jap1` ORDER BY id DESC");
// $img = 'followers-grid.png';
$time = time();
while ($row = mysql_fetch_array($Query)){
    $bucket = 'https://etra-live-japqc.s3.us-east-2.amazonaws.com/media';
    $img = $bucket .'/followers_' . $row['jap1'] . '.png';

    // echo $img . "<br>";
    $score = chat($img);
    if(empty($score)){
        $score = 0.0; 
    }
    echo $score . "<br>";
    $update_eq = mysql_query("UPDATE `packages` SET `qc_score`='".$score."', `qc` = '$time' WHERE `jap1`=".$row['jap1']);
}

function chat($img){
    global $openAiKey;

    $url = "https://api.openai.com/v1/chat/completions";

    $data = [
        "model" => "gpt-4o", 
        "messages" => [
            ["role" => "system", "content" => "I am a social media data analyst."],
            [
                "role" => "user", 
                "content" => [
                    ["type" => "text", "text" => "
                        You are analyzing the quality of a person's social media follower list to determine whether it is made up mostly of real users or bots.
                        Based on this analysis, return:
                            A score between 0.1 and 1.0, where:
                            0.1 = mostly bots or fake/inactive followers
                            1.0 = mostly real, active, and human followers
                        Return only the number.
                        Remove any html, css and javascript formatting from this message.
                        Do not provide an introduction, explanations, or any other message.
                    "],
                    ["type" => "image_url", "image_url" => [ "url" => $img ]]
                ]
            ]
        ]     
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $openAiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        return null;
    }

    curl_close($ch);

    $responseDecoded = json_decode($response, true);
    return $responseDecoded['choices'][0]['message']['content'] ?? null;
}


?>
