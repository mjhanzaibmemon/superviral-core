<?php
$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require $_SERVER["DOCUMENT_ROOT"] . '/sm-db.php';

// customer email
$Query_eq = mysql_query("SELECT * FROM `email_queue` WHERE markDone=1 AND ai_trained=0 LIMIT 1");
$Query_eq = mysql_query("SELECT * FROM `email_queue` WHERE `category` IN ( 'Missing Followers', 'Refund Request', 'Losing Followers', 'Missing Likes', 'Missing Order', 'Unclear Inquiry', 'Delivery Issue', 'Order Inquiry' ) AND emailDate >= unix_timestamp(CURRENT_DATE - interval 14 day) AND markDone = 1 AND ai_trained = 0;");
$row = mysql_fetch_array($Query_eq);
$convo = generate_convo($row);
$category = $row['category'];


// company data
$Query_eq = mysql_query("SELECT * FROM `ai_support_fixed_kb` WHERE `value`='".$category."' AND `type`='info' LIMIT 1");
$row_dataset = mysql_fetch_array($Query_eq);
$dataset_value = $row_dataset['value'];
if($dataset_value){$company_data = "Here is a relevant company-related documentation: {$dataset_value}";}

// select record to fine tune, using category
$Query_kb = mysql_query("SELECT * FROM ai_support_dynamic_kb WHERE `category`='" . $category . "' LIMIT 1");
$row_kb = mysql_fetch_array($Query_kb);
if(mysql_num_rows($Query_kb) == 0){$new_case = 1;echo 'new case<hr>';}else{echo 'existing case<hr>';}
$trained_data = stripslashes($row_kb['text']);

update_email_queue($row['id']);

if($trained_data){
    $trained_data = "Work on my existing document. Here is my existing case-study document: ".$trained_data;    
}else{
    $trained_data = "Create a new document. Here is an example, only use the format:
        TOPIC: CUSTOMER IS WAITING FOR ORDER
        CASE 1
        [CUSTOMER]: I ordered 10,000 tiktok views and 5,000 followers. I have received the views, but not the followers.
        [SUPPORT]: We apologise for the inconvenience caused. We can see your order has been successfully fulfilled. It may take up to 24 hours for your order to be delivered. This delay ensures we prioritise the safety of your account and maintain the high quality of service we provide.
        END CONVO
        ====================
        TOPIC: CUSTOMER LOST FOLLOWERS
        CASE 2
        [CUSTOMER]: Hi I had 27k Instagram followers but 7k have disappeared in the last week or so
        [SUPPORT]: We offer real followers who will engage with your content and help you grow your Instagram presence. However, it's possible that some of these followers may unfollow your IG account. This is because they are real people, with their own interests and priorities, and we cannot guarantee that they will continue to follow your account indefinitely. We are refilling your followers as we speak.
        END CONVO
        ====================
    ";
}

echo $row['from'] . '<br>' . $category . '<hr>';
echo $convo;
echo '<hr>';
echo $trained_data;
echo '<hr>';

$new_doc = chat();

if($new_case == 1){
    echo $row['category']." KNOWLEDGE BASE NEW ENTRY <br><br>";  
    echo $new_doc . '<hr>';
    echo "INSERT INTO `ai_support_dynamic_kb` SET `text`='".addslashes($new_doc)."', `category`='".$category."'";
    mysql_query("INSERT INTO `ai_support_dynamic_kb` SET `text`='".addslashes($new_doc)."', `category`='".$category."'");
}

if (strcmp($new_doc, $trained_data) !== 0 && !$new_case) {      
    mysql_query("UPDATE `ai_support_dynamic_kb` SET `text`='".addslashes($new_doc)."' WHERE `id`=".$row_kb['id']." LIMIT 1");
    echo $row['category']." KNOWLEDGE BASE UPDATED <br><br>";
    echo $new_doc;
}else{
    echo "NO CHANGES<br><br>";
    echo $new_doc;
}

function update_email_queue($id){
    mysql_query("UPDATE `email_queue` SET ai_trained=1 WHERE id=".$id." LIMIT 1");
}

function chat()
{
    global $openAiKey, $trained_data, $company_data, $convo,$new_case;

    if($new_case == 1){
        $prompts = "
            Return only the newly created case-study document. Do not add any other message.
        ";
    }else{
        $prompts = "
            With the newly generated case, compare it to the existing case-study document.
            If a similar case already exists, do not add the new one to the case-study document.
            If you can't find a relevant topic for this case, create a new one in the case-study document.
            If the customer's issue has not been resolved, do not make any changes to the case-study document.
            Return only the newly edited case-study document. Do not add any other message.
        ";        
    }

    $url = "https://api.openai.com/v1/chat/completions";
    
    // Message Data
    $data = [
        "model" => "gpt-4o",
        "messages" => [
            ["role" => "system", "content" => "I am a customer support agent. I work in a customer support agency. You are an expert on customer support."],
            ["role" => "user", "content" => "
                I am developing a case-study document to help train new support agents. My case-study document will contain support cases.
                
                ".$company_data."

                " . addslashes($trained_data) . "

                Here is a new customer interaction:
                " . addslashes($convo) . "

                Summarize the new interaction and create a new case, strictly follow these rules:
                - Only state the main points and problems.
                - Remove any greetings or filler words.
                - Remove any small talk.
                - Remove any links.

                ".$prompts."

                Return only the document in plain text. Do not return any messages or conclusions. Do not include any extra formatting, text, or code block markers.

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

function generate_convo($arr)
{
    $Query = mysql_query("SELECT
                                id,
                                `from`,
                                `emailUid`,
                                `email` as `reply`,
                                `subject`,
                                `emailDate` as `dateAdded`,
                                `hideMessage`,
                                `block`,
                                `brand`
                                FROM `email_queue`
                                WHERE `from`='".$arr['from']."' AND dateAdded >= unix_timestamp(CURRENT_DATE - interval 14 day )
                            ");

    $responseOnEmailArr = [];

    while ($resArr = mysql_fetch_array($Query)) {
        $responses = '';
        $responseOnEmailArr[] = $resArr;
    }
    // support response
    $QuerySupport = mysql_query("SELECT
                                        id,
                                        `to`,
                                        `emailUid`,
                                        `reply`,
                                        `from`,
                                        `dateAdded`,
                                        hideMessage,
                                        'support' as `type`
                                        FROM email_support_replies
                                        WHERE `to` = '" . $arr['from'] . "'
                                        AND dateAdded >= unix_timestamp(CURRENT_DATE - interval 14 day )
                                        ");


    while ($resArrSupport = mysql_fetch_array($QuerySupport)) {
        $responseOnEmailArr[] = $resArrSupport;
    }

    $countResponseArr = count($responseOnEmailArr);
    $time = array_column($responseOnEmailArr, 'dateAdded');

    $sortedArr = array_multisort($time, SORT_ASC, $responseOnEmailArr);

    if ($countResponseArr > 0) {

        /*
        echo '<pre>';
        print_r($responseOnEmailArr);
        echo '</pre>';
        */

        for ($j = 0; $j < $countResponseArr; $j++) {

            $cleanemailbody = $responseOnEmailArr[$j]['reply'];
            $divClass = ( !empty($responseOnEmailArr[$j]['type']) ? 'SUPPORT' : 'CUSTOMER');

            if (strpos($cleanemailbody, '<customer-care@superviral.io>') !== false) {
                $cleanemailbody = explode('<customer-care@superviral.io>', $cleanemailbody);
                $cleanemailbody = $cleanemailbody[0];
            }


            if (strpos($cleanemailbody, 'orders@superviral.io>') !== false) {
                $cleanemailbody = explode('<orders@superviral.io>', $cleanemailbody);
                $cleanemailbody = $cleanemailbody[0];
            }

            if (strpos($cleanemailbody, 'support@superviral.io>') !== false) {
                $cleanemailbody = explode('<support@superviral.io>', $cleanemailbody);
                $cleanemailbody = $cleanemailbody[0];
            }

            $cleanemailbody = preg_replace('/\s+/', ' ', trim($cleanemailbody));

            $responses .= '[' . strtoupper($divClass) . ']:'  . $cleanemailbody . '<br>';

            // $emailUnixTime = gmdate("H:i d-m-y", $responseOnEmailArr[$j]["dateAdded"]);
            // $NextDisplayChar = '<span class="' . $classForDisplayMessage . '" id="NextDisplayChar' .
            // $emailListCount . '">' . $cleanemailbody . '</span><br><span><button class="btn3"  id="copyBtn' .$emailListCount . '" onclick="callCopyFunc('. $emailListCount .')">Copy Message</button></span>';

            // $onclickParam = $emailListCount . ',\'\', ' . $responseOnEmailArr[$j]["id"] . ', ' . $emailType;    

            unset($cleanemailbody);
            unset($initialsupportmessage);
        }
        $responses .= 'END CONVO';
    }

    return $responses;
}
