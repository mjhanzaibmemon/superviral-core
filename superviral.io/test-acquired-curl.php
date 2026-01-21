<?php


include('header.php');
// API Endpoint URL
$URL = 'https://api.acquired.com/v1/transactions/258040375';

// $URL = 'https://qaapi.acquired.com/api.php/status/';

// $URL = 'https://qaapi.acquired.com/api.php/status/'; 

// Bearer token
$bearerToken = $acquiredapikey;

// Initialize cURL
$curl = curl_init($URL);

// Set cURL options
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt(
    $curl,
    CURLOPT_HTTPHEADER,
    array(
        "Content-type: application/json",
        "Authorization: Bearer $bearerToken"
    )
);
curl_setopt($curl, CURLOPT_POST, true);

// Uncomment and add your data if a POST body is required
// $Data = json_encode(['transaction_id' => '258040375']);
// curl_setopt($curl, CURLOPT_POSTFIELDS, $Data);

// Execute the cURL request
$json_response = curl_exec($curl);

// Check HTTP status code
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

// Close the cURL session
curl_close($curl);

// Decode the JSON response
$response = json_decode($json_response, true);

// Output the response
echo '<pre>';
if ($status === 200) {
    echo "Request was successful.\n";
    print_r($response);
} else {
    echo "Request failed with status code $status.\n";
    print_r($response);
}

?>
