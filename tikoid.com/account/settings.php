<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');

$activelink3 = 'activelink';

include('header.php');
include('auth.php');


$username = '';
$userEmail = '';
$userData = [];
$id = '';
$passworderrors = [];
// update user profile
if(isset($_POST['update_password'])) {	

   
    	$id = $_POST['id'];
        $email = $_POST['email'];

	$user = mysql_query("SELECT * from `accounts` WHERE `id`= '{$userinfo['id']}' AND `brand` = 'to' LIMIT 1");
    $row = mysql_fetch_array($user);
    // if not empty new password then compare it with confirm password
    	if(!empty($_POST['new_password'])){
    		// if not empty new password and empty old password
    		if(!empty($_POST['new_password']) && empty($_POST['old_password'])){
    			$passworderrors[] =  "Please enter your current password";
    			// if not empty new password and not empty old password
            }elseif (!empty($_POST['new_password']) && !empty($_POST['old_password'])) {

    			if(!password_verify($_POST['old_password'], $userinfo['password'])){
    				$passworderrors[] =  "Current Password is not correct";
    				// if not empty new password and empty confirm password
    			}elseif (!empty($_POST['new_password']) && empty($_POST['confirm_password'])) {
    				$passworderrors[] =  "Please confirm your password";
    				// compare password and confirm password
    			}elseif(!empty($_POST['new_password']) && !empty($_POST['confirm_password'])){
    				if($_POST['new_password'] == $_POST['confirm_password']){
    					   
                        $passwordsuccess = '<div class="emailsuccess" style="padding: 10px;">Password changed successfully!</div>';


                        $passwordlength = strlen($_POST['new_password']);
                        $passwordhashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                        $token_hash = md5($tokensecretphrase.md5($userinfo['email_hash']).$passwordhashed.time());
                        $passwupdated = time();

                        $updatepasswordq = mysql_query("UPDATE `accounts` SET 
                                
                                `password` = '$passwordhashed',
                                `passwlength` = '$passwordlength',
                                `passwupdated` = '$passwupdated',
                                `token_hash` = '$token_hash'


                            WHERE `id`={$userinfo['id']} AND `brand` = 'to'");

                        if($updatepasswordq)setcookie("plus_token", $token_hash, time() + (86400 * 30), "/","superviral.io"); // 86400 = 1 day


    				}else{
    					$passworderrors[] =  "The New Password doesn't match with the Confirm Password.";
    				}
    			}
    		}
    	}

	}else if(isset($_POST['update_username'])) {
		// unset cookie
			$id = $_POST['id'];
            $username = $_POST['username'];
		    $sql = "UPDATE accounts SET username='$username'WHERE id=$id";
		if (mysqli_query($conn, $sql)) {
			// if username or email updated then update the cookie value
			$success =  "Username Updated successfully";
		} else {
			$error =  "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	}

		foreach($passworderrors as $perpasserror){

			$passworderrors2 .= '<div class="emailsuccess emailfailed" style="padding: 10px;">'.$perpasserror.'</div>';
			
		}

    if(!empty($passworderrors2)){$passworderrors2 = '<div>'.$passworderrors2.'</div>';$autoopenpasswordchanger = 'onload="document.getElementById(\'autoopenpasswordchanger\').click();"';}

    if(!empty($passwordsuccess))$autoopenpasswordchanger = 'onload="document.getElementById(\'autoopenpasswordchanger\').click();"';

$user = mysqli_query($conn, "SELECT * from accounts WHERE id='" . $userinfo['id'] . "'");
$row = mysqli_fetch_array($user);
$tpl = file_get_contents('settings.html');
$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{username}', $row['username'], $tpl);
$tpl = str_replace('{userEmail}', $row['email'], $tpl);
$tpl = str_replace('{id}', $row['id'], $tpl);
$tpl = str_replace('{passworderrors}', $passworderrors2, $tpl);
$tpl = str_replace('{passwordsuccess}', $passwordsuccess, $tpl);
$tpl = str_replace('{autoopenpasswordchanger}', $autoopenpasswordchanger, $tpl);

// $contentq = mysql_query("SELECT * FROM `content` WHERE (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'global') AND `brand` = 'to'");
// while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;
?>