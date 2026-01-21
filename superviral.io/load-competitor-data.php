<?php
/*require_once 'db.php';

$userName = addslashes($_POST['username']);
$now = time();

$url = 'https://www.instagram.com/'. $userName .'/?__a=1&__d=dis';
    
//ATTEMPT TODO IT OUR WAY
$curl = curl_init(); 
curl_setopt($curl, CURLOPT_URL, $url); 
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 5);
curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 
curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
curl_setopt($curl, CURLOPT_ENCODING, '');
curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.82 Safari/537.36');
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

$get = curl_exec($curl);

$get = json_decode($get);
// echo $get->status;die;
$userData = $get;
curl_close($curl);
if ($userData != null && strpos($response[$i], "!DOCTYPE html") == false && $userData->title != "Restricted profile") {
    $followers = $userData -> graphql -> user-> edge_followed_by->count;
    $userProfileName = $userData -> graphql -> user-> username;
    $dp = $userData -> graphql -> user-> profile_pic_url;
    
    $dpimgname = md5('superviralrb'.$userName);
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';
                        $s3 = new S3($amazons3key, $amazons3password);

                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $dp); 
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
                        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                        curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
                        curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 
                        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                        curl_setopt($curl, CURLOPT_ENCODING, '');


                        $get = curl_exec($curl);

                        curl_close($curl);


                        $putobject = S3::putObject($get, 'cdn.superviral.io', 'dp/'.$dpimgname.'.jpg', S3::ACL_PUBLIC_READ);

    $q = mysql_query("SELECT * FROM `checkusers_now` WHERE `ig_username` = '$userName' AND `competitor` = 1 LIMIT 1 ");
    
    if(mysql_num_rows($q)==0){
        $q = mysql_query("INSERT INTO `checkusers_now` SET `ig_username` = '$userName', added= '$now', `competitor` = 1");
    
    }


    $dataReturn = ["followers"=> $followers, "userName" => $userProfileName, 'dp' => "https://cdn.superviral.io/dp/$dpimgname.jpg", "message"=>"Success"];
    echo json_encode($dataReturn);die;
}else{
    echo json_encode(["message"=>"failed"]);die;
}



*/






/////////////////////////////////////////////////////////////////////////////////////////////////////////////


require_once 'db.php';

$userName = addslashes(strtolower(trim($_POST['username'])));
$now = time();
$todaysdate = date("dmY");


if(empty($userName))die();

/*





    Existing data






*/




$findexistingq = mysql_query("SELECT * FROM `checkusers_now` WHERE `ig_username` = '$userName' LIMIT 1");

if(mysql_num_rows($findexistingq)=='1'){

        $findexisting = mysql_fetch_array($findexistingq);

        //OK WE NOW KNOW THERE IS A CHECKUSERS_NOW DATA, BUT WE NEED TO CHECK IF THERES INFORMATION ON THIS USER IN CHECKUSERS
        $checkforexistingcheckusersdata = mysql_query("SELECT * FROM `checkusers` WHERE `checkusers_now_id` = '{$findexisting['id']}' ORDER BY `id` DESC LIMIT 1");



        if(mysql_num_rows($checkforexistingcheckusersdata)=='0')

        {

            echo json_encode(["message"=>"failed"]);die;

        }

        else

        {

            //SHOW THE LATEST ROW

            $fetchcompetitordata = mysql_fetch_array($checkforexistingcheckusersdata); 

            $showimgorno = '/imgs/placeholder.jpg';

            $fetchsaveddp = mysql_query("SELECT * FROM `ig_dp` WHERE `igusername` = '$userName' LIMIT 1");

            if(mysql_num_rows($fetchsaveddp)==1){

                $profileDp = mysql_fetch_array($fetchsaveddp);

                $showimgorno = 'https://cdn.superviral.io/dp/'.$profileDp["dp"].'.jpg';

            }

            $dataReturn = ["followers"=> $fetchcompetitordata['followers'], "userName" => $fetchcompetitordata['ig_username'], 'dp' => $showimgorno, "message"=>"Success"];

            echo json_encode($dataReturn);

            die;
        }



        die();


    }


/*





    New data






*/
















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
            $Query = "INSERT INTO checkusers_now SET `ig_username` = '$userName', added = '$now',`competitor` = '1'";
            $runQuery = mysql_query($Query);

            $lastCheckUserId = mysql_insert_id();
        } else {
        
            $nextcheck = $now + (3600); // check again in one hour
            // $userData = "unavailable";
            $Query = "UPDATE checkusers_now SET `unavailable` = `unavailable` + 1, next_check = $nextcheck WHERE ig_username= '" . $userName . "' LIMIT 1";
            $runQuery = mysql_query($Query);
            if ($runQuery) {
                $failedtoretrieve=1;
            }
        
            break;
        }


        if ($userData->title == "Restricted profile") {
        
            $Query = "UPDATE checkusers_now SET unavailable = 2, `lastdatedone` = '$todaysdate'  WHERE ig_username= '" . $userName . "' LIMIT 1";
            $runUserUpdateQuery = mysql_query($Query);
            if ($runQuery) {
                $failedtoretrieve=1;
            }
        
            break;
        }

        if ($userData->status != "fail" && $userData != null) {
        

            $isPrivate = $userData->graphql->user->is_private;
            $userName = $userData->graphql->user->username;
            $userId = $userData->graphql->user->id;
            $followers = $userData->graphql->user->edge_followed_by->count;
            $following = $userData->graphql->user->edge_follow->count;
            $dataArr = $userData->graphql->user->edge_owner_to_timeline_media->edges;
            $dp = $userData->graphql->user->profile_pic_url;
        
        
            if (is_numeric($userId)) {


                $fetchuseridq = mysql_query("SELECT * FROM `searchbyusername` WHERE  ig_username= '" . $userName . "' LIMIT 1");

                $userIdQueryUpdate = "UPDATE checkusers_now SET instagram_user_id = $userId  WHERE ig_username= '" . $userName . "' LIMIT 1";

                $runUserIdQueryUpdate = mysql_query($userIdQueryUpdate);

                if (mysql_num_rows($fetchuseridq) == '1') {

                    mysql_query("UPDATE `searchbyusername` SET `ig_id` = '$userId' WHERE ig_username= '" . $userName . "' LIMIT 1");

                } else {

                    $insertthisq = mysql_query("INSERT INTO `searchbyusername`

                        SET
                        `ig_username` = '$userName',
                        `ig_id` = '$userId'
                        ");


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

            $lastUserId = mysql_insert_id();

             // handle private accounts
             if ($isPrivate) {

                $nextDate = strtotime('today');
                $nextDate = strtotime("+7 day", $nextDate);

                $nextcheck = $now + (86400);

                $Query = "UPDATE checkusers_now SET is_private = '1', `failed` = 0,`next_check` = '$nextcheck',`lastdatedone` = '$todaysdate'  WHERE ig_username= '" . $userName . "' LIMIT 1";
                $runUserUpdateQuery = mysql_query($Query);


            } else {

                $Query = "UPDATE checkusers_now SET is_private = 0 WHERE ig_username= '" . $userName . "' LIMIT 1";
                $runQuery = mysql_query($Query);
            }

            foreach ($dataArr as $thumbnail) {

                $likes = $thumbnail->node->edge_liked_by->count;
                $countLikes += intval($likes);

            }
             //START CALCULATING THE AVERAGE LIKES
             $avgLikes = round($countLikes / 12);
             $avgUpdateQuery = "UPDATE checkusers SET avg_post_likes = $avgLikes WHERE id= '" . $lastUserId . "' LIMIT 1";
             $runavgUpdateQuery = mysql_query($avgUpdateQuery);
  
         //$nextcheck = $now + (86400);
             $nextcheck = strtotime('tomorrow');//We want to set this, in a way, where it starts at 00:00 GMT of the next day
 
             // is NOT set: $userData->exc_type, then this is a success!
             $userIdQueryUpdate = "UPDATE checkusers_now SET `unavailable` = 0, failed = 0,`next_check` = '$nextcheck',`lastdatedone` = '$todaysdate' WHERE ig_username= '" . $userName . "'";
             $runUserIdQueryUpdate = mysql_query($userIdQueryUpdate);
             if ($runUserIdQueryUpdate) {echo 'Checkusers_now successfull query success!<br>';}




                $dataReturn = ["followers"=> $followers, "userName" => $userName, 'dp' => $dp, "message"=>"Success"];
                echo json_encode($dataReturn);


               $failedtoretrieve=0;


                if(!empty($dp)){        
                            
                        $dpimgname = md5('superviralrb'.$userName);
                        require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';

                        $s3 = new S3($amazons3key, $amazons3password);

                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $dp); 
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
                        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                        curl_setopt($curl, CURLOPT_PROXY, 'us.smartproxy.com:10000');
                        curl_setopt($curl, CURLOPT_PROXYUSERPWD, "$spusername:$sppassword"); 
                        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                        curl_setopt($curl, CURLOPT_ENCODING, '');


                        $getdp = curl_exec($curl);

                        curl_close($curl);


                        $putobject = S3::putObject($getdp, 'cdn.superviral.io', 'dp/'.$dpimgname.'.jpg', S3::ACL_PUBLIC_READ);


                        if(!empty($getdp)){

                        $putobject = S3::putObject($getdp, 'cdn.superviral.io', 'dp/'.$dpimgname.'.jpg', S3::ACL_PUBLIC_READ);

                        mysql_query("INSERT INTO `ig_dp` SET `dp` = '$dpimgname', `igusername` = '$userName'");

                        }

                }








   
        
             die;

            break;//STOP THE LOOP, its SUCCESSFUL
        }

            $nextcheck = $now + (60);
            $Query = "UPDATE checkusers_now SET failed = 1, next_check = $nextcheck WHERE ig_username= '" . $userName . "' LIMIT 1";
            $runQuery = mysql_query($Query);
            if ($runQuery) {$failedtoretrieve=1;}
            

}


if($failedtoretrieve==1)echo json_encode(["message"=>"failed"]);


?>

