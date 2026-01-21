<?php



// Define a whitelist of allowed IPv4 addresses
$allowed_ips = array(
    '198.27.83.222',
    '192.99.21.124',
    '167.114.64.88',
    '167.114.64.21',
    '2607:5300:60:24de::',
    '2607:5300:60:467c::',
    '2607:5300:60:6558::',
    '2607:5300:60:6515::'
);


// Check if the 'X-Forwarded-For' header exists
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    // The 'X-Forwarded-For' header can contain a comma-separated list of IPs
    // We want the first one, which is the original client IP
    $forwarded_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $remote_ip = trim($forwarded_ips[0]);  // Use the first IP in the list
} else {
    // Fallback to the 'REMOTE_ADDR' if no forwarded IP is available
    $remote_ip = $_SERVER['REMOTE_ADDR'];
}

// Check if the remote IP is in the allowed list
if (!in_array($remote_ip, $allowed_ips)) {
    // If not allowed, send a 403 Forbidden response and exit
    header('HTTP/1.0 403 Forbidden');
    echo "Access denied!";
    exit;
}


//////////////////////////////////////////////////////////////////////////////


include('../sm-db.php');

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

*/

$rev = $_POST['review'];


if(!empty($rev)){

die('automated on;');

echo '<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>';

echo $rev;

include_once($_SERVER['DOCUMENT_ROOT'].'/crons/orderfulfillraw.php');

$asd = $api->order(['service' => '6183', 'link' => 'https://www.trustpilot.com/review/superviral.io', 'comments' => $rev]);

$status = $asd->status($asd->order);


print_r($status);

//$reviewthis = $review->review();

var_dump($reviewthis);

die('<br> Added!');

}




////////////////////////////////



$headers = array(
    "Authorization: Bearer {$openaiapiKey}",
    "Content-Type: application/json"
);


$theactualreviewsource = file_get_contents('automate-r.txt');

$theactualreview = explode("\n", $theactualreviewsource);
$otherreviews = explode("\n", $theactualreviewsource);

$maximumamount = count($theactualreview);
$rand = rand(0,$maximumamount);

$theactualreview = $theactualreview[$rand];

$otherreview1 = $otherreviews[rand(0,$maximumamount)];
$otherreview2 = $otherreviews[rand(0,$maximumamount)];
$otherreview3 = $otherreviews[rand(0,$maximumamount)];
$otherreview4 = $otherreviews[rand(0,$maximumamount)];
$otherreview5 = $otherreviews[rand(0,$maximumamount)];
$otherreview6 = $otherreviews[rand(0,$maximumamount)];
$otherreview7 = $otherreviews[rand(0,$maximumamount)];
$otherreview8 = $otherreviews[rand(0,$maximumamount)];
$otherreview9 = $otherreviews[rand(0,$maximumamount)];
$otherreview10 = $otherreviews[rand(0,$maximumamount)];


$therequest = "Rewrite the following review In American english and make it look very genuine: \"$theactualreview\". 

\n
\n
\n


Also vary the writing style of this review to these reviews:
$otherreview1 \n
$otherreview2 \n
$otherreview3 \n
$otherreview4 \n
$otherreview5 \n
$otherreview6 \n
$otherreview7 \n
$otherreview8 \n
$otherreview9 \n
$otherreview10 \n
";

// Define messages
$messages = array();
$messages[] = array("role" => "user", "content" => "$therequest");


echo str_replace("\n",'<br>',$therequest);

echo '<hr>';


// Define data
$data = array();
$data["model"] = "gpt-4o-mini";
$data["messages"] = $messages;
$data["max_tokens"] = 1000;

// init curl
$curl = curl_init($chatgpturl);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 60 seconds timeout


$result = curl_exec($curl);
if (curl_errno($curl)) {
    echo 'Error:' . curl_error($curl);
} else {
    //echo $result;



  $result = json_decode($result,true);

echo $result['choices'][0]['message']['content'];
$suggested = $result['choices'][0]['message']['content'];


}

//print_r($result);

curl_close($curl);






echo '<hr><form method="post">
    <textarea style="width:500px;height:500px" name="review">'.$suggested.'</textarea><br>
    <input type="submit" name="submit">
</form>';



if(!empty($suggested)){



include_once($_SERVER['DOCUMENT_ROOT'].'/crons/orderfulfillraw.php');

$asd = $api->order(['service' => '6183', 'link' => 'https://www.trustpilot.com/review/superviral.io', 'comments' => $suggested]);

$status = $asd->status($asd->order);


}



?>
