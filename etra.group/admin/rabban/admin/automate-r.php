<?php

require_once('../../../../superviral.io/db.php');

die;

//require_once('/adminheader.php');





$rev = $_POST['review'];


if(!empty($rev)){

echo 'Trying to submit:<br>';

echo '<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>';

echo $rev;

require('../../../../superviral.io/orderfulfillraw.php');


$asd = $api->order(['service' => '6252', 'link' => 'https://www.trustpilot.com/review/superviral.io', 'comments' => $rev]);


$status = $asd->status($asd->order);

print_r($status);

echo 'asd 2';
//$reviewthis = $review->review();

var_dump($reviewthis);

die('<br> Added!');

}




////////////////////////////////


$url = 'https://api.openai.com/v1/chat/completions';

$headers = array(
    "Authorization: Bearer {$openaiapiKey}",
    "Content-Type: application/json"
);


$theactualreview = file_get_contents('automate-r.txt');

$theactualreview = explode("\n", $theactualreview);

$random = count($theactualreview);
$rand = rand(0,$random);

$theactualreview = $theactualreview[$rand];



$therequest = "Rewrite the following review In American english and make it look very genuine: $theactualreview";

// Define messages
$messages = array();
$messages[] = array("role" => "user", "content" => "$therequest");


echo $therequest;

echo '<hr>';


// Define data
$data = array();
$data["model"] = "gpt-4.1";
$data["messages"] = $messages;
$data["max_tokens"] = 50;

// init curl
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec($curl);
if (curl_errno($curl)) {
    echo 'Error:' . curl_error($curl);
} else {
    //echo $result;



  $result = json_decode($result,true);

echo $result['choices'][0]['message']['content'];


}

curl_close($curl);









?>
<hr><form method="post">
    <textarea style="width:500px;height:500px" name="review"><?=$result['choices'][0]['message']['content']?></textarea><br>
    <input type="submit" name="submit">
</form>