<?php


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');



$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/'. $subdomain . '/etra.group';
if(!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php';

$q = mysql_query("SELECT `value` FROM `ai_support_fixed_kb` WHERE type='category'");
while($row = mysql_fetch_array($q)){
    $categories_arr[] = trim($row['value']);
}
$categories = implode(',',$categories_arr);

$Query = mysql_query("SELECT * FROM email_queue WHERE (`category`='' OR `category` IS NULL) AND `emailSpam`!=1 AND `emailDate` >= unix_timestamp(CURRENT_DATE - interval 1 hour) AND `from` NOT LIKE '%@amazonses.com' AND `spam_level` != 'High' AND (`email` IS NOT NULL || `email` != '') ORDER BY `dateAdded` DESC");
$allSupportEmailCount = mysql_num_rows($Query);
while($row = mysql_fetch_array($Query)){
    
    if(strpos($row['from'],"amazonses") !== false){
        $update = mysql_query("UPDATE `email_queue` SET `markDone`=1 WHERE `id`=".$row['id']);
        continue;
    };
    
    $category = chat($row);
    
    if(!in_array($category,$categories_arr)){
        echo 'new category<br>';
        echo $category.'<br>';
        print_r($categories_arr);
        echo '<hr>';

        $q = mysql_query("INSERT INTO `ai_support_fixed_kb` SET `value`='".addslashes($category)."', `type`='category', `count`='1'");
        $lastId = mysql_insert_id();

        // insert other types
        
        mysql_query("
        INSERT INTO `ai_support_fixed_kb` (`type`, `parent_id`) 
        VALUES 
        ('info', '$lastId'),
        ('actions', '$lastId'),
        ('api', '$lastId'),
        ('user_info', '$lastId')
        ");      
        
    }else{
        // increment count
        mysql_query("UPDATE `ai_support_fixed_kb` SET `count` = `count` + 1 WHERE `value`='".addslashes($category)."' AND type='category'");
        echo "existing category<br>UPDATE `ai_support_fixed_kb` SET `count` = `count` + 1 WHERE `value`='".addslashes($category)."' AND type='category'<hr>";

    }

    $update = mysql_query("UPDATE `email_queue` SET `category`='".$category."', `spam_level`='".$spam_level."' WHERE `id`=".$row['id']);
    
    $spam_level = '';
}


function chat($row){
    global $openAiKey, $categories;

    $url = "https://api.openai.com/v1/chat/completions";
    // Message Data
    $data = [
        "model" => "gpt-4o", 
        "messages" => [
            ["role" => "system", "content" => "I am a customer support agent. I work in a customer support agency. You are an expert on customer support."],
            ["role" => "user", "content" => "
                Here is a customer enquiry: ".addslashes($row['email'])."
                
                Classify the following customer enquiry into a category.
                Here are some previously suggested categories: ".$categories.".
                Do not return anything other than a single category.
                Do not return a list of categories.
                Do not include fullstops in your response.
            "]
        ]
    ];
        
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
