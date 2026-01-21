<?php
/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

*/

require_once '../sm-db.php';

$todaysdate = date("dmY");
$now = time();

$query = "SELECT * FROM orders WHERE date(FROM_UNIXTIME(added)) = CURDATE() AND outreach_insert = 0 LIMIT 20";

$queryRun = mysql_query($query);

$howmuchleft = mysql_num_rows($queryRun);

if ($howmuchleft == 0) die('Done');

echo '<h1>Users Found: ' . mysql_num_rows($queryRun) . '</h1><hr>';

while ($data = mysql_fetch_array($queryRun)) {

    echo '<h2>' . $data['id'] . '. ' . $data['igusername'] . '</h2><br><br>';

    $timeStamp = time();
    sendCloudwatchData('EtraGroupCrons', 'supernova-api-user-outreach-getprofile', 'UserOutreach', 'supernova-api-user-outreach-function', 1);

    $url = 'https://i.supernova-493.workers.dev/api/v3/userId?username=' . $data["igusername"];

    //ATTEMPT TODO IT OUR WAY
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('accept: application/json', "X-API-KEY: $superviralsocialscrapekey"));
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 20);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $get = curl_exec($curl);
    $resp = $get;
    $resp = json_decode($resp, true);
    $users = $resp['data'];
    $userId = $users['user']['pk_id'];


    curl_close($curl);

    if (!empty($userId)) {

        echo 'supernova success!<br>';

        $followers = $users['user']['follower_count'];
        echo 'Followers: ' . $followers . '<br>';
        
        // logic for 10k > follower or large package

        if ($followers > 10000 || $data['amount'] > 10000) {

            // check duplicate

            $queryCount = "SELECT * FROM user_outreach WHERE username = '" . $data["igusername"] . "' LIMIT 1";

            $queryCountRun = mysql_query($queryCount);

            $isExist = mysql_num_rows($queryCountRun);

            if ($isExist == 0) {

                $userQuery = "INSERT INTO user_outreach 
                 SET 
                 email            = '" . $data['emailaddress'] . "', 
                 username         = '" . $data["igusername"] . "',  
                 followers        =  " . $followers . ",
                 last_order       =  " . $data["id"] . ",
                 socialmedia       =  '" . $data["socialmedia"] . "',
                 brand            =   '" . $data["brand"] . "' ";

                $runUserQuery = mysql_query($userQuery);

                if ($runUserQuery) {

                    mysql_query("UPDATE orders SET outreach_insert = 1 WHERE id =" . $data["id"]);
                    echo 'Inserted into user_outreach<br>';
                } else {
                    echo '<font color="red">Failed query: Insert into user_outreach</font><br>';
                }
            }

        }

    } else {

        echo 'Unable to get User data (Code 1): Unavailable: 1 <pre>';
        print_r($resp);
        echo '</pre><br>';
        continue;

    }

    echo '<hr>';

    unset($followers);
    unset($userId);
    unset($resp);
}
echo 'done';
