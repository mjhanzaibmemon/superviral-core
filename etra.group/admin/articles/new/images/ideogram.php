<?php
echo '<pre>';

$apiUrl = 'https://api.ideogram.ai/generate';
$apiKey = '';


// $payload = [
//     "image_request" => [
//         "prompt" => "Guide To Using Hashtags On IG For More Followers. The text should be readable and natural-looking.",
//         "resolution" => "RESOLUTION_1152_704",
//         "model" => "V_2",
//         "magic_prompt_option" => "AUTO",
//         "style_type" => "REALISTIC",
//         "negative_prompt" => "blurry text, illegible writing"
//     ]
// ];

// Call the generate function
// $result = generate($apiKey, $apiUrl, $payload);


$apiUrl = 'https://api.ideogram.ai/remix';
$imagePath = 'https://cdn.superviral.io/media/b864028da879ec018ef90e6ecc767de4.jpg';

$payload = [
    "image_request" => [
        "prompt" => "Guide To Using Hashtags On IG For More Followers — centered and clearly readable with natural, sharp typography. No distortions. White bold text on a clean, minimal background. Do not blur the text. High contrast. Professional style.",
        "resolution" => "RESOLUTION_1152_704",
        "model" => "V_2",
        "magic_prompt_option" => "AUTO",
        "style_type" => "REALISTIC",
        "negative_prompt" => "blurry text, illegible, distorted letters, artifacts, extra characters"
    ]
];

// Call the remix function
$result = remix($apiKey, $apiUrl, $imagePath, $payload);

echo '<img src="' . $result['data'][0]['url'] . '" alt="Image" />';

function remix($apiKey, $apiUrl, $imagePath, $payload)
{
    // Get the image content from the URL
    $ch = curl_init($imagePath);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects if there are any
    $imageData = curl_exec($ch);

    // Error check for fetching the image
    if (curl_errno($ch)) {
        echo 'cURL error (fetching image): ' . curl_error($ch);
        exit;
    }

    curl_close($ch);

    if (!$imageData) {
        echo "Failed to fetch the image from the URL.";
        exit;
    }

    // Save the image temporarily
    $tempImagePath = tempnam(sys_get_temp_dir(), 'image_');
    file_put_contents($tempImagePath, $imageData);

 
    $imageRequest = json_encode($payload['image_request']);

    $imageFile = new CURLFile($tempImagePath, mime_content_type($tempImagePath), basename($tempImagePath));

    // Create the POST data
    $postFields = [
        'image_request' => $imageRequest,
        'image_file' => $imageFile
    ];

    // Initialize CURL
    $ch = curl_init($apiUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Api-Key: ' . $apiKey
    ]);

    // Execute request
    $response = curl_exec($ch);

    // Error check
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
        exit;
    }

    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Display result
    if ($httpStatus === 200) {
        $result = json_decode($response, true);
        echo "Remix success:\n";
        // print_r($result);
        return $result;
    } else {
        echo "Failed with status $httpStatus:\n$response";
    }
}

// generate
function generate($apiKey, $apiUrl, $payload)
{

    // Initialize cURL
    $ch = curl_init($apiUrl);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Api-Key: ' . $apiKey,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    // Execute request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
        exit;
    }

    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Handle response
    if ($httpStatus === 200) {
        $result = json_decode($response, true);
        echo "Success!\n";
        // print_r($result);
        return $result;
    } else {
        echo "Failed with status $httpStatus:\n$response";
    }
}
