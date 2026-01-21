<?php


include('../sm-db.php');
include 'emailer.php'; //TO EMAIL ONCE ORDER IS COMPLETE
include dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/messagebird/autoload.php';


use Google\Cloud\Translate\V2\TranslateClient;

require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/gtranslate/index.php';


///////////////////////////////////#####################



function getRandomString($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';

    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string;
}




/*

##########################################################################################################
##########################################################################################################
##########################################################################################################
##########################################################################################################
##########################################################################################################
##########################################################################################################
##########################################################################################################

*/


// superviral code
    $dbName = $superViralDB;

    mysql_select_db($dbName , $conn);
    $now = time();
    $tendaysfromnow  = $now + (86400 * 10);

    $q = mysql_query("SELECT * FROM `automatic_likes` WHERE 
        `freeautolikes_session` != '' AND 
        `freeautolikesexpiringemail` = '0' AND 
        `expires` BETWEEN $now AND $tendaysfromnow");

    while($info = mysql_fetch_array($q)){

    echo $info['id'].' - '.date('jS F Y',$info['expires']).'<hr>';

    /////////////////////////////////##################################################


            mysql_query("UPDATE `automatic_likes` SET `freeautolikesexpiringemail` = '1' WHERE `id` =  '{$info['id']}' LIMIT 1");

            //SEND OUT SMS

            //LOC REDIRECT
            $locredirect = $info['country'].'/';
            if($locredirect=='ww/')$locredirect = '';

            $getuserinfoq = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$info['account_id']}' LIMIT 1");
            $getuserinfo = mysql_fetch_array($getuserinfoq);



            //SEND OUT EMAIL
            $subject = 'Free Automatic Likes: Keep Enjoying it! ❤️';

            $emailbody = '
            <p>Hi there,</p>
            <br>
            <p>This is a friendly reminder that your Free 50 Automatic Likes has been running for the last 20-days and we want you to keep enjoying it!
            </p>
            <br>
            <p>With Superviral you will always get the following benefits.</p>
            <br>
            <p>
            - Real likes from real users<br>
            - Free views on all videos<br>
            - Safe & Secure since 2012<br>
            - 24/7 customer support<br>
            - Cancel anytime you like<br></p>

            <br>
            <p>We\'ll do the rest of the hardwork for you!</p>
            <br>
            <p>Kind regards,</p>
            <br>
            <p>Superviral Team</p>
            ';

            $emailtpl = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]).'/superviral.io/emailtemplate/emailtemplate.html');
            $emailtpl = str_replace('{body}',$emailbody,$emailtpl);
            $emailtpl = str_replace('Unsubscribe','',$emailtpl);
            $emailtpl = str_replace('{subject}',$subject,$emailtpl);


           if(($info['country']!=='ww')&&($info['country']!=='us')&&($info['country']!=='uk'))$notenglish2=true;

            if($notenglish2==true){

                  $thisloc = $info['country'];

                  $translate = new TranslateClient(['key' => $googletranslatekey]);

                  $result = $translate->translate($emailtpl, [
                      'source' => 'en', 
                      'target' => $locas[$thisloc]['sdb'],
                      'format' => 'html'
                  ]);

                  $emailtpl = $result['text'];


                  $result = $translate->translate($subject, [
                      'source' => 'en', 
                      'target' => $locas[$thisloc]['sdb'],
                      'format' => 'html'
                  ]);

                  $subject = $result['text'];



            }



            emailnow($info['emailaddress'],'Superviral','no-reply@superviral.io',$subject,$emailtpl);

    /////////////////////////////////##################################################

    echo '<hr>';

    unset($emailtpl);
    unset($bitlyhash);
    unset($bitlyhref);
    unset($bitlyq);
    unset($notenglish2);

    }



    /*

    ##########################################################################################################
    ##########################################################################################################
    ##########################################################################################################
    ##########################################################################################################
    ##########################################################################################################
    ##########################################################################################################
    ##########################################################################################################

    */



    $now = time();
    $threedaysfromnow  = $now + (86400 * 3);

    $q = mysql_query("SELECT * FROM `automatic_likes` WHERE 
        `freeautolikes_session` != '' AND 
        `freeautolikesexpiringemail` = '1' AND 
        `expires` BETWEEN $now AND $threedaysfromnow");

    while($info = mysql_fetch_array($q)){

    echo $info['id'].' - '.date('jS F Y',$info['expires']).'<hr>';

    /////////////////////////////////##################################################


            mysql_query("UPDATE `automatic_likes` SET `freeautolikesexpiringemail` = '2' WHERE `id` =  '{$info['id']}' LIMIT 1");

            //SEND OUT SMS

            //LOC REDIRECT
            $locredirect = $info['country'].'/';
            if($locredirect=='ww/')$locredirect = '';

            $getuserinfoq = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$info['account_id']}' LIMIT 1");
            $getuserinfo = mysql_fetch_array($getuserinfoq);

            if(!empty($getuserinfo['freeautolikesnumber'])){

            ////generate BITLY CODE

            $time = time();
            $bitlyhash = getRandomString();
            $bitlyhref = 'https://superviral.io/'.$locredirect.'account/checkout/'.$info['autolikes_session'];
            $bitlyq = mysql_query("INSERT INTO `bitly` SET `hash` = '$bitlyhash', `href` = '$bitlyhref',`added` = '$time'");

            ////

            $MessageBird = new \MessageBird\Client($messagebirdclient);
            $Message = new \MessageBird\Objects\Message();
            $Message->originator = +447451272012;
            $Message->recipients = array($getuserinfo['freeautolikesnumber']);

            $Message->body = 'Your 50 Auto Likes is expiring in 3-days. Easily Pay to Continue Auto Likes: https://superviral.io/a/'.$bitlyhash;

            $MessageBird->messages->create($Message);

            if($MessageBird){echo 'Text Message Sent to '.$getuserinfo['freeautolikesnumber'].'!<br>';}

            }




            //SEND OUT EMAIL
            $subject = $info['igusername'].': '.$info['likes_per_post'].' Auto Likes - Expiring in 3-Days';

            $emailbody = '
            <p>Hi there,</p>
            <br>
            <p>This is a quick reminder that your Premium Free 50 Automatic Likes is expiring in 3-days.
            </p>
             <br>
             <p>To continue your 50 Automatic Likes you can choose a paid package through the button below.</p>
             <br>
              <a href="https://superviral.io/'.$locredirect.'account/checkout/'.$info['autolikes_session'].'" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Continue My Auto Likes</a>
            <br>                   
            <br>
            <p>
            If you\'re unlucky in choosing a package to continue your Automatic Likes, unfortunately, your <b>'.$info['likes_per_post'].' Automatic Likes will expire in 3-days</b>.
            </p>
            <br>
            <p>With Superviral you will always get the following benefits!</p>
            <br>
            <p>
            <b>- Real likes from real users</b><br>
            <b>- Free views on all videos</b><br>
            <b>- Safe & Secure since 2012</b><br>
            <b>- 24/7 customer support</b><br>
            <b>- Cancel anytime you like</b><br></p>

            <br>
            <p>At Superviral, the customer always comes first.</p>
            <br>
            <p>Kind regards,</p>
            <br>
            <p>Superviral Team</p>
            ';

            $emailtpl = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]).'/superviral.io/emailtemplate/emailtemplate.html');
            $emailtpl = str_replace('{body}',$emailbody,$emailtpl);
            $emailtpl = str_replace('Unsubscribe','',$emailtpl);
            $emailtpl = str_replace('{subject}',$subject,$emailtpl);


           if(($info['country']!=='ww')&&($info['country']!=='us')&&($info['country']!=='uk'))$notenglish2=true;

            if($notenglish2==true){

                  $thisloc = $info['country'];

                  $translate = new TranslateClient(['key' => $googletranslatekey]);

                  $result = $translate->translate($emailtpl, [
                      'source' => 'en', 
                      'target' => $locas[$thisloc]['sdb'],
                      'format' => 'html'
                  ]);

                  $emailtpl = $result['text'];


                  $result = $translate->translate($subject, [
                      'source' => 'en', 
                      'target' => $locas[$thisloc]['sdb'],
                      'format' => 'html'
                  ]);

                  $subject = $result['text'];



            }



            emailnow($info['emailaddress'],'Superviral','no-reply@superviral.io',$subject,$emailtpl);
            email_stat_insert('Autolikes Expiring', $info['emailaddress'], addslashes($emailtpl), 'sv');

    /////////////////////////////////##################################################

    echo '<hr>';

    unset($emailtpl);
    unset($bitlyhash);
    unset($bitlyhref);
    unset($bitlyq);
    unset($notenglish2);

    }

    /*

    ##########################################################################################################
    ##########################################################################################################
    ##########################################################################################################
    ##########################################################################################################
    ##########################################################################################################
    ##########################################################################################################
    ##########################################################################################################


    */

    $q = mysql_query("SELECT * FROM `automatic_likes` WHERE 
        `cancelbilling` != '3' AND
        `freeautolikes_session` != '' AND 
        `freeautolikesexpiringemail` = '2' AND 
        `expires` < $now");

    while($info = mysql_fetch_array($q)){

    echo $info['id'].' - '.date('jS F Y',$info['expires']).'<hr>';

    /////////////////////////////////##################################################


            mysql_query("UPDATE `automatic_likes` SET `freeautolikesexpiringemail` = '3' WHERE `id` =  '{$info['id']}' LIMIT 1");

            //SEND OUT SMS

            //LOC REDIRECT
            $locredirect = $info['country'].'/';
            if($locredirect=='ww/')$locredirect = '';

            $getuserinfoq = mysql_query("SELECT * FROM `accounts` WHERE `id` = '{$info['account_id']}' LIMIT 1");
            $getuserinfo = mysql_fetch_array($getuserinfoq);

            if(!empty($getuserinfo['freeautolikesnumber'])){

            ////generate BITLY CODE

            $time = time();
            $bitlyhash = getRandomString();
            $bitlyhref = 'https://superviral.io/'.$locredirect.'account/checkout/'.$info['autolikes_session'];
            $bitlyq = mysql_query("INSERT INTO `bitly` SET `hash` = '$bitlyhash', `href` = '$bitlyhref',`added` = '$time'");

            ////

            $MessageBird = new \MessageBird\Client($messagebirdclient);
            $Message = new \MessageBird\Objects\Message();
            $Message->originator = +447451272012;
            $Message->recipients = array($getuserinfo['freeautolikesnumber']);

            $Message->body = 'Your 50 Auto Likes has expired. Pay and Continue Auto Likes: https://superviral.io/a/'.$bitlyhash;

            $MessageBird->messages->create($Message);

            if($MessageBird){echo 'Text Message Sent to '.$getuserinfo['freeautolikesnumber'].'!<br>';}

            }




            //SEND OUT EMAIL
            $subject = $info['igusername'].': '.$info['likes_per_post'].' Auto Likes Has Expired';

            $emailbody = '
            <p>Hi there,</p>
            <br>
            <p>This is a reminder that your Free 50 Automatic Likes has expired.
            </p>
             <br>
             <p>To continue your 50 Automatic Likes and keep the free likes you\'ve gained from us in the last 30-days, you can choose a paid package through the button below.</p>
             <br>
              <a href="https://superviral.io/'.$locredirect.'account/checkout/'.$info['autolikes_session'].'" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Continue 50 Auto Likes</a>
            <br>                   
            <br>
            <p>
            In the event you\'re unable to continue your Auto Likes, we cannot guarantee if the previous likes you\'ve received from us will remain</b>.
            </p>
            <br>
            <p>With Superviral you will always get the following benefits when you upgrade.</p>
            <br>
            <p>
            - Real likes from real users<br>
            - Free views on all videos<br>
            - Safe & Secure since 2012<br>
            - 24/7 customer support<br>
            - Cancel anytime you like<br></p>

            <br>
            <p>We\'ll do the rest of the hardwork for you!</p>
            <br>
            <p>Kind regards,</p>
            <br>
            <p>Superviral Team</p>
            ';

            $emailtpl = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]).'/superviral.io/emailtemplate/emailtemplate.html');
            $emailtpl = str_replace('{body}',$emailbody,$emailtpl);
            $emailtpl = str_replace('Unsubscribe','',$emailtpl);
            $emailtpl = str_replace('{subject}',$subject,$emailtpl);




           if(($info['country']!=='ww')&&($info['country']!=='us')&&($info['country']!=='uk'))$notenglish2=true;

            if($notenglish2==true){

                  $thisloc = $info['country'];

                  $translate = new TranslateClient(['key' => $googletranslatekey]);

                  $result = $translate->translate($emailtpl, [
                      'source' => 'en', 
                      'target' => $locas[$thisloc]['sdb'],
                      'format' => 'html'
                  ]);

                  $emailtpl = $result['text'];



                  $result = $translate->translate($subject, [
                      'source' => 'en', 
                      'target' => $locas[$thisloc]['sdb'],
                      'format' => 'html'
                  ]);

                  $subject = $result['text'];



            }



            emailnow($info['emailaddress'],'Superviral','no-reply@superviral.io',$subject,$emailtpl);

    /////////////////////////////////##################################################

    echo '<hr>';

    unset($emailtpl);
    unset($bitlyhash);
    unset($bitlyhref);
    unset($bitlyq);
    unset($notenglish2);

    }








    /*

    ############################################################################################################
    ############################################################################################################
    ############################################################################################################
    ############################################################################################################
    ############################################################################################################
    ############################################################################################################
    ############################################################################################################


    */











    ///////////////////////////////////#####################


    $now = time();
    $inamonth = time() + (86400 * 30);
    $threedaysfromnow  = $now + (86400 * 3);

    $q = mysql_query("SELECT * FROM `automatic_likes` WHERE 
        `recurring` = '1' AND
        `cancelbilling` != '3' AND
        `cardexpiringemail` = '0' AND 
        `cardexpiringtime` != '0' AND 
        `cardexpiringtime` BETWEEN $now AND $inamonth");

    while($info = mysql_fetch_array($q)){


    $sendcheck=0;


    echo $info['id'].' - Card Expiring in: '.date('jS F Y',$info['cardexpiringtime']).'<hr>';//THIS MEANS IT WILL EXPIRE IN THE NEXT 30-DAYS

    if($info['cardexpiringtime'] > $info['lastbilled'] && $info['cardexpiringtime'] < $info['nextbilled']){

        echo 'Need to send email out so far as its within the range of '.date('jS F Y',$info['lastbilled']).' to '.date('jS F Y',$info['nextbilled']).'<hr>';
        $sendcheck=1;

    }

    if($sendcheck==0)continue;

    if(($sendcheck==1)&&($info['cardexpiringtime'] > $now && $info['cardexpiringtime'] < $threedaysfromnow)){echo 'Need to send email out as its within the range of '.date('jS F Y',$now).' to '.date('jS F Y',$threedaysfromnow).'<hr>';


    $daysremaning = ceil(abs($info['cardexpiringtime'] - $now) / 86400);

            mysql_query("UPDATE `automatic_likes` SET `cardexpiringemail` = '1' WHERE `id` =  '{$info['id']}' LIMIT 1");

            //LOC REDIRECT
            $locredirect = $info['country'].'/';
            if($locredirect=='ww/')$locredirect = '';

            $daysremaning = ceil(abs($info['cardexpiringtime'] - $now) / 86400);;

            //SEND OUT EMAIL
            $subject = '@'.$info['igusername'].': Card is Expiring in '.$daysremaning.'-days';

            $emailbody = '
            <p>Hi there,</p>
            <br>
            <p><b>Important!</b> The card on file for your '.$info['likes_per_post'].' Automatic Likes is due to <b>expire in '.$daysremaning.'-days</b>. If you\'re unable to update your card details before this date, <b>you will lose access to your Automatic Likes and the previous likes you\'ve gained from us</b>.
            </p>
             <br>
             <p>Click on the button below to update your card quickly and easily. If your card details are up-to-date, your '.$info['likes_per_post'].' Automatic Likes service will not be interrupted.</p>
             <br>
              <a href="https://superviral.io/'.$locredirect.'account/checkout/'.$info['autolikes_session'].'" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Update Expiring Card</a>
            <br>                   
            <p>Please update your card so you don\'t lose the following benefits:.</p>
            <br>
            <p>
            - Real likes from real users<br>
            - Free views on all videos<br>
            - Safe & Secure since 2012<br>
            - 24/7 customer support<br>
            - Cancel anytime you like<br></p>

            <br>
            <p>Kind regards,</p>
            <br>
            <p>Superviral Team</p>
            ';

            $emailtpl = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]).'/superviral.io/emailtemplate/emailtemplate.html');
            $emailtpl = str_replace('{body}',$emailbody,$emailtpl);
            $emailtpl = str_replace('Unsubscribe','',$emailtpl);
            $emailtpl = str_replace('{subject}',$subject,$emailtpl);

           if(($info['country']!=='ww')&&($info['country']!=='us')&&($info['country']!=='uk'))$notenglish2=true;

            if($notenglish2==true){

                  $thisloc = $info['country'];

                  $translate = new TranslateClient(['key' => $googletranslatekey]);

                  $result = $translate->translate($emailtpl, [
                      'source' => 'en', 
                      'target' => $locas[$thisloc]['sdb'],
                      'format' => 'html'
                  ]);

                  $emailtpl = $result['text'];


                  $result = $translate->translate($subject, [
                      'source' => 'en', 
                      'target' => $locas[$thisloc]['sdb'],
                      'format' => 'html'
                  ]);

                  $subject = $result['text'];



            }

            emailnow($info['emailaddress'],'Superviral','no-reply@superviral.io',$subject,$emailtpl);

    }

    unset($sendcheck);
    unset($subject);
    unset($daysremaning);
    unset($emailbody);
    unset($locredirect);
    unset($emailtpl);
    unset($notenglish2);



    }




    /*

    ############################################################################################################
    ############################################################################################################
    ############################################################################################################
    ############################################################################################################
    ############################################################################################################
    ############################################################################################################
    ############################################################################################################


    */

    $now = time();

    $q = mysql_query("SELECT * FROM `automatic_likes` WHERE `expiredemail` = '0' AND 
        `expires` < $now AND `freeautolikes_session` = '' AND `payment_id` != '' AND `autolikes_session` !=''");

    while($info = mysql_fetch_array($q)){


    echo $info['id'].' - Expired in: '.date('jS F Y',$info['expires']).'<hr>';//THIS MEANS IT WILL EXPIRE IN THE NEXT 30-DAYS


            mysql_query("UPDATE `automatic_likes` SET `expiredemail` = '1' WHERE `id` =  '{$info['id']}' LIMIT 1");

            //LOC REDIRECT
            $locredirect = $info['country'].'/';
            if($locredirect=='ww/')$locredirect = '';

            //SEND OUT EMAIL
            $subject = '@'.$info['igusername'].' Auto Likes: Your Card Has Expired';

            $emailbody = '
            <p>Hi there,</p>
            <br>
            <p><b>Important!</b> Your '.$info['likes_per_post'].' Your card has expired. This means that on '.date('jS F Y',$info['expires']).', your Automatic Likes will not be renewed and you\'ll lose the amazing benefits associated with your Automatic Likes:</p>
           <br>
            <p>
            - Real likes from real users<br>
            - Free views on all videos<br>
            - Safe & Secure since 2012<br>
            - 24/7 customer support<br>
            - Cancel anytime you like<br></p>
            <br>
             <p>Click on the button below to continue your Automatic Likes. If your card details are up-to-date, your '.$info['likes_per_post'].' Automatic Likes per Post will not be interrupted.</p>
             <br>
              <a href="https://superviral.io/'.$locredirect.'account/checkout/'.$info['autolikes_session'].'" style="color: #2e00f4;border: 2px solid #2e00f4;display: block;width: 330px;padding: 16px 9px;text-decoration: none;    -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin: 5px auto;font-weight: 700;text-align:center;">Update Expiring Card</a>
            <br>                   
            <p>Kind regards,</p>
            <br>
            <p>Superviral Team</p>
            ';

            $emailtpl = file_get_contents(dirname($_SERVER["DOCUMENT_ROOT"]).'/superviral.io/emailtemplate/emailtemplate.html');
            $emailtpl = str_replace('{body}',$emailbody,$emailtpl);
            $emailtpl = str_replace('Unsubscribe','',$emailtpl);
            $emailtpl = str_replace('{subject}',$subject,$emailtpl);

           if(($info['country']!=='ww')&&($info['country']!=='us')&&($info['country']!=='uk'))$notenglish2=true;

            if($notenglish2==true){

                  $thisloc = $info['country'];

                  $translate = new TranslateClient(['key' => $googletranslatekey]);

                  $result = $translate->translate($emailtpl, [
                      'source' => 'en', 
                      'target' => $locas[$thisloc]['sdb'],
                      'format' => 'html'
                  ]);

                  $emailtpl = $result['text'];

                  $result = $translate->translate($subject, [
                      'source' => 'en', 
                      'target' => $locas[$thisloc]['sdb'],
                      'format' => 'html'
                  ]);

                  $subject = $result['text'];



            }


            emailnow($info['emailaddress'],'Superviral','no-reply@superviral.io',$subject,$emailtpl);


        unset($subject);
        unset($emailbody);
        unset($locredirect);
        unset($emailtpl);
        unset($notenglish2);


    }

// end superviral code

?>