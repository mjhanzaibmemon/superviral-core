<?php

$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] .= $subdomain;
}

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';
global $openAiKey;
$endpoint = 'https://api.openai.com/v1/chat/completions';

// user prompt
$prompt = "Give me the top 50 non franchise pizza places in and around 
Birmingham City Centre, Birmingham, UK with their instagram and tiktok account. 
Return an array containing the restaurants name, instagram & tiktok name. 
If a social media is not available, do not include it in the array. 
Elaborate in associative array for socialmedia either tiktok or instagram. Need both username.";

// prepare request
$payload = [
  'model' => 'gpt-4.1',
  'messages' => [
    ['role' => 'user', 'content' => $prompt]
  ],
  // 'temperature' => 0.2, // low randomness
  // 'max_tokens' => 60
];

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer {$openAiKey}",
  "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$res = curl_exec($ch);
if (!$res) {
    die("cURL error: " . curl_error($ch));
}
curl_close($ch);
echo '<pre>';
// print_r($res);
$js = json_decode($res, true);

$output = $js['choices'][0]['message']['content'] ?? '';
echo "AI says:\n$output\n";
