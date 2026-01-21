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

$apiKey = $pinecone_key;

// Fetch one record
$stmt = mysql_query("SELECT * FROM `algo_post_finetune` WHERE `done`=0 LIMIT 100");

$allData = [];
$allData_embed = [];

while ($row = mysql_fetch_array($stmt)) {
    $embedding = json_decode($row['post_embed'], true);
    if (!is_array($embedding)) {
        $embedding = array_map('floatval', explode(',', $row['post_embed']));
    }
    
    $allData = [
        'id' => 'post_test_' . $row['id'], // unique ID for Pinecone
        'post_url' => $row['post_url'] ?? '',
        'post_id' => $row['post_id'] ?? '',
        'media_type' => $row['media_type'] ?? '',
        'location' => $row['location'] ?? '',
        'video_length' => floatval($row['video_length']) ?? 0,
        'latitude' => floatval($row['latitude']) ?? 0,
        'longitude' => floatval($row['longitude']) ?? 0,
        'keywords' => $row['keywords'] ?? '',
        'post_embed' => $embedding ?? [],
        'posted_at' => $row['posted_at']
    ];

    // ----------- SEND TO PINECONE -----------

    // Prepare the payload
    $payload = [
        'namespace' => "posts",        
        'vectors' => [
            [
                'id' => $allData['id'],
                'values' => $allData['post_embed'],
                'metadata' => [
                    'post_url' => $allData['post_url'] ?? '',
                    'post_id' => $allData['post_id'] ?? '',
                    'media_type' => $allData['media_type'] ?? '',
                    'location' => $allData['location'] ?? '',
                    'latitude' => $allData['latitude'] ?? 0,
                    'longitude' => $allData['longitude'] ?? 0,
                    'video_length' => $allData['video_length'] ?? 0,
                    'keywords' => $allData['keywords'] ?? '',
                    'posted_at' => $allData['posted_at'] ?? ''
                ]
            ]
        ]
    ];
    echo(json_encode($payload));
    echo '<br><br>';

    $send = upload_pinecone($payload, $apiKey);
    print_r($send);
    
    mysql_query("UPDATE `algo_post_finetune` SET `done`=1 WHERE `id`=".$row['id']." LIMIT 1");    
    echo '<hr>';
}

function upload_pinecone($payload, $apiKey)
{

    $pineconeUrl = "https://posts-mqpevh9.svc.aped-4627-b74a.pinecone.io/vectors/upsert";
    // Send to Pinecone
    $ch = curl_init($pineconeUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Api-Key: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    // echo $response;
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    } else {
        echo "\n\nPinecone response:\n";
        echo $response;
    }
    curl_close($ch);
}

function fetch_pinecone($vectorIds, $apiKey, $namespace = 'posts')
{
    $pineconeUrl = "https://posts-mqpevh9.svc.aped-4627-b74a.pinecone.io/vectors/fetch?ids=" . implode(',', $vectorIds) . "&namespace=" . urlencode($namespace);

    // Complete URL
    $url = $pineconeUrl;
    // echo $url;
    // Init cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Api-Key: ' . $apiKey,
        'X-Pinecone-API-Version: 2025-04'
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
    } else {
        echo "Pinecone Response:\n";
        curl_close($ch);

        return json_decode($response, true);

        // echo $response;
    }

}
