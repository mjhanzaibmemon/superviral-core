<?php


// die('Unfortunately, due to high demand we\'re unable to provide free likes for today. Get Instagram likes <a href="https://superviral.io/buy-instagram-likes/">here</a>.');

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");
else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db = 1;
$nomaindb = 1;
include('../header.php');

// $id = addslashes($_GET['id']);
$hash = addslashes($_GET['id']);
$contactnumber = addslashes($_POST['input']);

 //CHECK IF ID AND SESSION EXISTS IN DATABASE
if(!empty($hash)){

    $validq = mysql_query("SELECT * FROM `freetrial` WHERE `md5` = '$hash' LIMIT 1");
    
    if(mysql_num_rows($validq)=='0'){ $error = 'Free likes are currently unavailable';}
    
    mysql_query("UPDATE `freetrial` SET `views` = `views` + 1 WHERE `md5` = '$hash' LIMIT 1");
    
    $info = mysql_fetch_array($validq);
    
}else{
    
    
    $error = 'Oops something went wrong. Please click the link provided in your email again.';
    
}

$username = addslashes($_POST['username']);
$username = str_replace('@','',$username);

// print_r($_POST);


$emailaddress = $info['emailaddress'];
$styleIframe = "display:none;";

if (!empty(addslashes($_POST['submitForm']))&&($info['done']=='0')) {
    
    
    //PREVENT DUPLICATE INSERTS
		// $updatefulfill = mysql_query("UPDATE `freetrial` SET `done` = '1' WHERE `md5` = '$id' ORDER BY `id` DESC LIMIT 1");

    unset($_COOKIE['ordersession']); 
    setcookie('ordersession', '', -1, '/'); 

    if (empty($username)) {
        $error1 = '<div style="color: red;">Please enter a username</div>';
    }
   
    if (empty($error1)) {

        $username = trim($username);
        $username = str_replace('@', '', $username);
        $username = str_replace('https://instagram.com/', '', $username);
        $username = str_replace('instagram.com/', '', $username);

        $username = str_replace('?utm_medium=copy_link', '', $username);
        $username = str_replace('?r=nametag', '', $username);
        $username = str_replace('https://www.', '', $username);
        $username = str_replace('?hl=en.', '', $username);


        if (strpos($username, '?') !== false) {

            $username = explode('?', $username);
            $username = $username[0];
        }

        $checkq = mysql_query("SELECT * FROM `packages` WHERE `type` = 'freelikes' AND socialmedia ='ig' LIMIT 1");
        $packages = mysql_fetch_array($checkq);
        $pid = $packages['id'];
        $socialmedia = $packages['socialmedia'];
        include('create-ordersession.php');
        global $order_session;
        $ordersession = $order_session;
        if(empty($ordersession)) $ordersession = addslashes($_COOKIE['ordersession']);
        $username = str_replace('?', '', $username);

        if(!empty($contactnumber)){


            if(substr($contactnumber, 0, 2 ) == "07")$contactnumber = preg_replace('/^(0*44|(?!\+0*44)0*)/', '+44', $contactnumber);
    
    
            $contactnumberupdate = ", `contactnumber` = '$contactnumber' ";
    
        }
        mysql_query("UPDATE `order_session` SET 
				`igusername` = '$username', 
				`emailaddress` = '$emailaddress', 
				`packageid` = '$pid',
				`chooseposts` = '',
				`upsell` = '',    
				`upsell_all` = '',
                `socialmedia` = 'ig' 

				WHERE `order_session` = '$ordersession' LIMIT 1");

        if ($loggedin == true) {

            mysql_query("UPDATE `accounts` SET `username` = '$username' WHERE `id` = '{$userinfo['id']}' LIMIT 1");
        }

        $added = time();
        $countryuser = $locas[$loc]['sdb'];

        $updateuser = mysql_query("INSERT IGNORE INTO `users` SET 
				`country` = '$countryuser',
				`emailaddress` = '{$emailaddress}', 
				`source` = 'cart',
				`added` = '{$added}',
                `brand` = 'sv',
                `md5` = '{$hash}'
                $contactnumberupdate 
				 ");

        $updateuser = mysql_query("UPDATE `users` SET 
        `contactnumber` = '$contactnumber' WHERE `emailaddress` = '{$emailaddress}' ");

        $packageinfo = mysql_fetch_array(mysql_query("SELECT * FROM `packages` WHERE `id` = '{$pid}' LIMIT 1"));

        if (($packageinfo['type'] == 'freelikes')) {


            $form1 = '<iframe id="iframeProcessOrder" src="/' . $locas[$loc]['order'] . '/' . $locas[$loc]['order1select'] . '/?free_likes=1" width="100%" height="1000px" frameborder="0"></iframe>';
            $styleForIframe = 'style="display:none;"';
            $styleForIframeCheckList = 'style="display:none;"';
            $styleIframe = "display:block;height:1000px !important";
        } 
    }
} else {

    if((empty($info['igusername']))&&($loggedin==true)){$username = $userinfo['username'];}
    $emailaddress = $info['emailaddress'];
    
}

if($info['done'] > 0){
    $error =  "You've claimed your free likes and they will be delivered to you soon!";
}

$form = '<form method="POST" enctype="multipart/form-data" onsubmit="return checkForm(this);">



                            
                            {error1}
                            <div class="igusernameholder">
                            <svg id="Bold" enable-background="new 0 0 24 24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m12.004 5.838c-3.403 0-6.158 2.758-6.158 6.158 0 3.403 2.758 6.158 6.158 6.158 3.403 0 6.158-2.758 6.158-6.158 0-3.403-2.758-6.158-6.158-6.158zm0 10.155c-2.209 0-3.997-1.789-3.997-3.997s1.789-3.997 3.997-3.997 3.997 1.789 3.997 3.997c.001 2.208-1.788 3.997-3.997 3.997z"></path><path d="m16.948.076c-2.208-.103-7.677-.098-9.887 0-1.942.091-3.655.56-5.036 1.941-2.308 2.308-2.013 5.418-2.013 9.979 0 4.668-.26 7.706 2.013 9.979 2.317 2.316 5.472 2.013 9.979 2.013 4.624 0 6.22.003 7.855-.63 2.223-.863 3.901-2.85 4.065-6.419.104-2.209.098-7.677 0-9.887-.198-4.213-2.459-6.768-6.976-6.976zm3.495 20.372c-1.513 1.513-3.612 1.378-8.468 1.378-5 0-7.005.074-8.468-1.393-1.685-1.677-1.38-4.37-1.38-8.453 0-5.525-.567-9.504 4.978-9.788 1.274-.045 1.649-.06 4.856-.06l.045.03c5.329 0 9.51-.558 9.761 4.986.057 1.265.07 1.645.07 4.847-.001 4.942.093 6.959-1.394 8.453z"></path><circle cx="18.406" cy="5.595" r="1.439"></circle></svg>
                            <input class="input inputcontact inputusername" name="username" id="userName" onchange = "callAjax();" value="'.$info['username'].'">
                            <input class="input inputcontact" type="hidden" name="id" value="'. $hash .'">
                            <input class="input inputcontact" type="hidden" name="hash" value="'. $hash .'">
                            </div>
                            <div class="profile-box-wrapper" id="loadingProf">
                              <div class="profile-box">
                                  <div class="dp-username">
                                      <div style="width: 46px;height: 46px;border-radius: 50%;background-color: #BBB;display: flex;align-items:center;justify-content:center;"><span class="loader"></span></div>
                                      <div class="uName">Loading profile</div>
                                  </div>
                              </div>
                            </div> 
                            <div class="profile-box-wrapper" id="publicProf" style="display: none;">
                              <div class="profile-box">
                                  <div class="dp-username">
                                      <img src="/imgs/home/customer5.png" id="publicProfilePic" alt="Instagram Profile Picture" />
                                      <div class="uName">@'.$info['username'].'</div>
                                  </div>
                                  <div class="icon">
                                      <svg xmlns="http://www.w3.org/2000/svg" width="19" height="15" viewBox="0 0 19 15" fill="none">
                                          <path d="M2 6.54545L7.29412 12L17 2" stroke="white" stroke-width="4"/>
                                      </svg>
                                  </div>
                              </div>
                            </div>
                            <div class="profile-box-wrapper" id="notFoundProf" style="display: none;">
                              <div class="profile-box">
                                  <div class="dp-username">
                                      <div class="placeholder"></div>
                                      <div class="uName">@'.$info['username'].'</div>
                                  </div>
                                  <div class="icon danger">
                                      <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none">
                                          <path d="M2 2L15 15" stroke="white" stroke-width="4"/>
                                          <path d="M15 2L2 15" stroke="white" stroke-width="4"/>
                                      </svg>
                                  </div>
                              </div>
                              <button>
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none">
                                    <path d="M12 1V4.05556M12 4.05556H8.94444M12 4.05556L10.1667 2.40047C9.19359 1.52956 7.90867 1 6.5 1C3.46244 1 1 3.46244 1 6.5C1 9.53759 3.46244 12 6.5 12C9.11751 12 11.3079 10.1716 11.8637 7.72222" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="txt" onclick="callAjax();return false;">Try Again</div>
                              </button>
                            </div>
                            <div class="profile-box-wrapper" id="privateProf" style="display: none;">
                              <div class="profile-box">
                                  <div class="dp-username">
                                      <img src="/imgs/home/customer5.png" id="privateProfilePic" alt="Instagram Profile Picture" />
                                      <div class="uName">@'.$info['username'].'</div>
                                  </div>
                                  <div class="danger label">Private</div>
                              </div>
                              <button>
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none">
                                    <path d="M12 1V4.05556M12 4.05556H8.94444M12 4.05556L10.1667 2.40047C9.19359 1.52956 7.90867 1 6.5 1C3.46244 1 1 3.46244 1 6.5C1 9.53759 3.46244 12 6.5 12C9.11751 12 11.3079 10.1716 11.8637 7.72222" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="txt" onclick="callAjax();return false;">Try Again</div>
                              </button>
                            </div>
                          
                            <div class="label labelcontact">Your contact number<br><font style="font-weight:400;">(for free status notification on your likes)</font></div>

                            <input type="tel" id="phone1" class="input inputcontact" name="contactnumber" value="" required>

                            <input type="hidden" id="output" name="input" value="">

                            <input type="submit" name="submitForm" class="btn color4" style="margin-bottom:0;" value="Select post »">


                        </form>';

if(!empty($error)){
    $form = "<div style='text-align:center'>{$error}</div>";
}


$tpl = file_get_contents('freelikes.html');
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{form}', $form, $tpl);
$tpl = str_replace('{loclocation}', $loclinkforward, $tpl);
$tpl = str_replace('{h1}', $h1, $tpl);
$tpl = str_replace('{username}', $username, $tpl);
$tpl = str_replace('{error1}', $error1, $tpl);
$tpl = str_replace('{styleForIframe}', $styleForIframe, $tpl);
$tpl = str_replace('{styleIframe}', $styleIframe, $tpl);
$tpl = str_replace('{styleForIframeCheckList}', $styleForIframeCheckList, $tpl);
$tpl = str_replace('{form1}', $form1, $tpl);
$tpl = str_replace('{ordersession}', $hash, $tpl);
$tpl = str_replace('{ordersession_id}', $info['id'], $tpl);




$contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` IN ('global','freelikes')) ");
while ($cinfo = mysql_fetch_array($contentq)) {
    $tpl = str_replace('{' . $cinfo['name'] . '}', $cinfo['content'], $tpl);
}


echo $tpl;
