<?php

$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';

if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}
echo '<pre>';
set_time_limit(0);
ini_set('memory_limit', '-1');

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';

// Fetch one record
$stmt = mysql_query("SELECT sp.id,video_url,shortcode,sp.username,sp.media_type,user_share_count,user_like_count,profile_pic_url, r.id AS restaurant_id, r.latitude,r.longitude, sp.post_embed FROM socialmedia_posts sp JOIN restaurants r ON r.id = sp.restaurant_id WHERE sp.post_embed is null LIMIT 10");

$allData = [];
$allData_embed = [];

while ($row = mysql_fetch_array($stmt)) {
    $id = $row['id'];
    $allData_embed = [
        'video_url' => $row['video_url'],
        'job_id' => 'sm_post_' . $id
    ];
    print_r($allData_embed);
    echo '<br><br>';

    $embedding = get_embedding_from_lambda($allData_embed);
    
    mysql_query("UPDATE socialmedia_posts SET `post_embed`='".$embedding['response']."' WHERE `id`={$id}");

    echo '<hr>';
}

function get_embedding_from_lambda($payload)
{
    $lambdaUrl = "https://wyol2ilgpcz4rrzsggw322daku0ndkqb.lambda-url.us-east-2.on.aws/";
    $jsonPayload = json_encode($payload);
    echo $jsonPayload . '<br><br>';
    $ch = curl_init($lambdaUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Content-Length: " . strlen($jsonPayload)
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        return ["error" => curl_error($ch)];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response,true);
    $result = implode(',',json_decode($result['body'],true)['embeddings'][0]);
    
    return [
        "http_code" => $httpCode,
        "response" => $result
    ];
}
