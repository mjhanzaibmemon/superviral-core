<?php
require_once 'db.php';

$userName = addslashes(strtolower(trim($_POST['username'])));
$now = time();
$todaysdate = date("dmY");



$findexistingq = mysql_query("SELECT * FROM `checkusers_now` WHERE `ig_username` = '$userName' LIMIT 1");

if(mysql_num_rows($findexistingq)=='1'){


        $fetchthisone = mysql_fetch_array($findexistingq);

        //ENSURING THIS PAID USER CAN GET THEIR POSTS DOWNLOADED AS WELL
        mysql_query("UPDATE `checkusers_now` SET `competitor` = '0' WHERE `id` = '{$fetchthisone['id']}' LIMIT 1"); 

        die;

}


if(empty($userName))die();

for($i = 0; $i < 5; $i++){

        $url = 'https://www.instagram.com/' . $userName . '/?__a=1&__d=dis';

        //ATTEMPT TODO IT OUR WAY
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
        curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword");
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.82 Safari/537.36');
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $get = curl_exec($curl);

        curl_close($curl);
    
        if (strpos($userData, "!DOCTYPE html") == false) {
            $get = json_decode($get);
            $userData = $get;
            $Query = "INSERT INTO checkusers_now SET `ig_username` = '$userName', added = '$now'";
            $runQuery = mysql_query($Query);

            $lastCheckUserId = mysql_insert_id();
        } else {
        
            $nextcheck = $now + (3600); // check again in one hour
            // $userData = "unavailable";
            $Query = "UPDATE checkusers_now SET `unavailable` = `unavailable` + 1, next_check = $nextcheck WHERE ig_username= '" . $userName . "' LIMIT 1";
            $runQuery = mysql_query($Query);
            if ($runQuery) {
                echo $userName . ' <font color="red">marked as unavailable!</font><br>';
            } else {
                echo '<font color="red">Failed query: marked as unavailable!</font><br>';
            }
        
            break;
        }


        if ($userData->title == "Restricted profile") {
        
            $Query = "UPDATE checkusers_now SET unavailable = 2, `lastdatedone` = '$todaysdate'  WHERE ig_username= '" . $userName . "' LIMIT 1";
            $runUserUpdateQuery = mysql_query($Query);
            if ($runQuery) {
                echo $userName . ' <font color="green">Restricted Profile!</font><br>';
            } else {
                echo '<font color="red">Failed query: marked as RP!</font><br>';
            }
        
            break;
        }

        if ($userData->status != "fail" && $userData != null) {
        
        
            echo '<font color="green">Instagram Sucess</font><br>';
        
        

            $isPrivate = $userData->graphql->user->is_private;
            $userName = $userData->graphql->user->username;
            $userId = $userData->graphql->user->id;
            $followers = $userData->graphql->user->edge_followed_by->count;
            $following = $userData->graphql->user->edge_follow->count;
            $dataArr = $userData->graphql->user->edge_owner_to_timeline_media->edges;

            $dp = $userData -> graphql -> user -> profile_pic_url;
        
            if (is_numeric($userId)) {

                echo 'Is Numeric, User ID!<br>';

                $fetchuseridq = mysql_query("SELECT * FROM `searchbyusername` WHERE  ig_username= '" . $userName . "' LIMIT 1");

                $userIdQueryUpdate = "UPDATE checkusers_now SET instagram_user_id = $userId  WHERE ig_username= '" . $userName . "' LIMIT 1";

                $runUserIdQueryUpdate = mysql_query($userIdQueryUpdate);

                if (mysql_num_rows($fetchuseridq) == '1') {

                    echo 'Already exists the USER ID<br>';

                    mysql_query("UPDATE `searchbyusername` SET `ig_id` = '$userId' WHERE ig_username= '" . $userName . "' LIMIT 1");

                } else {

                    $insertthisq = mysql_query("INSERT INTO `searchbyusername`

                        SET
                        `ig_username` = '$userName',
                        `ig_id` = '$userId'
                        ");

                    if ($insertthisq) {
                        echo 'Insert the USER ID<br>';
                    }

                }

            }

            $userQuery = "INSERT INTO checkusers
            SET
            checkusers_now_id = " . $lastCheckUserId . ",
            ig_username       = '" . $userName . "',
            followers         =  " . $followers . ",
            following         =  " . $following . ",
            avg_post_likes    =   0,
            added             =  " . $now;

            $runUserQuery = mysql_query($userQuery);
            if ($runUserQuery) {echo 'Inserted into users stats<br>';} else {echo '<font color="red">Failed query: Insert into checkusers stats</font><br>';}           

            $lastUserId = mysql_insert_id();

             // handle private accounts
             if ($isPrivate) {

                echo 'Account is private<br>';
                $nextDate = strtotime('today');
                $nextDate = strtotime("+7 day", $nextDate);

                $nextcheck = $now + (86400);

                $Query = "UPDATE checkusers_now SET is_private = '1', `failed` = 0,`next_check` = '$nextcheck',`lastdatedone` = '$todaysdate'  WHERE ig_username= '" . $userName . "' LIMIT 1";
                $runUserUpdateQuery = mysql_query($Query);
                if ($runUserUpdateQuery) {echo 'Account is detected as private<br>';} else {echo '<font color="red">Failed query: Chekusers_now is private</font><br>';}


            } else {

                echo 'Account is public<br>';
                $Query = "UPDATE checkusers_now SET is_private = 0 WHERE ig_username= '" . $userName . "' LIMIT 1";
                $runQuery = mysql_query($Query);
                if ($runQuery) {echo 'Account is detected as public<br>';} else {echo '<font color="red">Failed query: Chekusers_now is public</font><br>';}
            }

            foreach ($dataArr as $thumbnail) {

                $likes = $thumbnail->node->edge_liked_by->count;
                $countLikes += intval($likes);

            }
             //START CALCULATING THE AVERAGE LIKES
             $avgLikes = round($countLikes / 12);
             $avgUpdateQuery = "UPDATE checkusers SET avg_post_likes = $avgLikes WHERE id= '" . $lastUserId . "' LIMIT 1";
             $runavgUpdateQuery = mysql_query($avgUpdateQuery);
             if ($runavgUpdateQuery) {echo 'Average post calculation query success!<br>';} else {echo '<font color="red">Failed query: Average post calculation query success!</font><br>';}
 
 
         //$nextcheck = $now + (86400);
             $nextcheck = strtotime('tomorrow');//We want to set this, in a way, where it starts at 00:00 GMT of the next day
 
             // is NOT set: $userData->exc_type, then this is a success!
             $userIdQueryUpdate = "UPDATE checkusers_now SET `unavailable` = 0, failed = 0,`next_check` = '$nextcheck',`lastdatedone` = '$todaysdate' WHERE ig_username= '" . $userName . "'";
             $runUserIdQueryUpdate = mysql_query($userIdQueryUpdate);
             if ($runUserIdQueryUpdate) {echo 'Checkusers_now successfull query success!<br>';} else {echo '<font color="red">Failed query: Checkusers_now query success!</font><br>';}
 
    
         // ig dp
            if(!empty($dp)){
                mysql_query("INSERT INTO `ig_dp` SET `igusername` = '$userName', `dp_url` = '$dp',`dnow` = '1'");
            }

        
            break;
        }

            $nextcheck = $now + (60);
            $Query = "UPDATE checkusers_now SET failed = 1, next_check = $nextcheck WHERE ig_username= '" . $userName . "' LIMIT 1";
            $runQuery = mysql_query($Query);
            if ($runQuery) {echo "<b>" .$userData['ig_username'].' marked as Failed!</b><br>';} else {echo '<font color="red">Failed query: Checkusers_now query!</font><br>';}
            

}
echo 'done';