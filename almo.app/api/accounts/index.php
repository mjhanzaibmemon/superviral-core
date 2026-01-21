<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 

require_once '../../common/emailer.php';

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/almo.app';
if (!empty($initial) && $initial != "almo.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once dirname($_SERVER["DOCUMENT_ROOT"]).'/almo.app/config/config.php';
require '../../common/func.php';

$input = json_decode(file_get_contents("php://input"), true);

$type = $input['type'];

switch ($type) {
    case 'signup':

        if (!empty($input['email']) && !empty($input['password'])) {
           
            $email = trim(strtolower(addslashes($input['email'])));
	        $password = password_hash($input['password'], PASSWORD_DEFAULT);
            $added = time();

            $email_hash = md5($email);
	        $token_hash = md5(md5($email_hash).$password.$added);
            $ipaddress = getUserIP();
            // Check if email already exists
            $checkQuery = "SELECT * FROM accounts WHERE `email` = '{$email}' order by id desc Limit 1";
            $queryRun = mysql_query($checkQuery);

            if (mysql_num_rows($queryRun) > 0) {
                // email already exists
                echo json_encode([
                    "status" => "error",
                    "message" => "Email already exists."
                ]);
                die;
            }

            // Insert new user
            $query = "INSERT INTO accounts (email, `password`, `added`, `email_hash`, `token_hash`, `ipaddress`) VALUES ('$email', '$password', '$added', '$email_hash', '$token_hash', '$ipaddress')";
            $queryRun = mysql_query($query);
            $last_id = mysql_insert_id();
            if ($queryRun) {

                // track activity
                track_activity($last_id, 'Signup', 'Account Created');

                echo json_encode([
                    "status" => "success",
                    "message" => "Account created successfully."
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to create account."
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid input. All fields are required."
            ]);
        }

    break;

    case 'login':

            $email = trim(strtolower($input['email']));
	        $password = $input['password'];

            if (!empty($email) && !empty($password)) {

	            $result = mysql_query("SELECT * FROM `accounts` WHERE `email` ='$email' LIMIT 1");
                $info = mysql_fetch_array($result);
                $password_hashed = $info['password'];
	            $num_rows = mysql_num_rows($result);
                
                if($num_rows == 0){
                    echo json_encode([
                        "status" => "error",
                        "message" => "User Not Found!, Please signup."
                    ]);
                    die;
                }
    
                if(($num_rows == 1)&&(password_verify($password, $password_hashed))){

                    // track activity
                    track_activity($info['id'], 'Login', 'Account Login');

                    $data = array(
                        'token_hash' => $info['token_hash'],
                        // 'id' => $info['id']
                    );
                    echo json_encode([
                        "status" => "success",
                        "data" => json_encode($data)
                    ]);
                }else{
                    echo json_encode([
                        "status" => "error",
                        "data" => "Email or password is incorrect."
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Email and password are required."
                ]);
            }
    
    break;

    case 'get_account':

        if (!empty($input['email'])) {
            $email = $input['email'];

            $checkQuery = "SELECT * FROM accounts WHERE `email` = '{$email}' order by id desc Limit 1";
            $queryRun = mysql_query($checkQuery);

            if (mysql_num_rows($queryRun) == 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Account not exists."
                ]);
                die;
            }

            $data = mysql_fetch_array($queryRun);

            echo json_encode([
                "status" => "success",
                "data" => json_encode($data)
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid input, Email required."
            ]);
        }

    break;
    case 'forgot_password':    

            if (!empty($input['email'])) {
                $email = $input['email'];
            
                $checkQuery = "SELECT * FROM accounts WHERE `email` = '{$email}' order by id desc Limit 1";
                $queryRun = mysql_query($checkQuery);
                $info = mysql_fetch_array($queryRun);
                if (mysql_num_rows($queryRun) == 0) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Account not exists."
                    ]);
                    die;
                }

                // track activity
                track_activity($info['id'], 'Forgot Password', 'Forgot Password Request');

                $recipient = $email;
                $senderName = "Superviral";
                $sender = "support@superviral.io";
                $subject = "Reset Password";
                $emailhtml = "Hi there,<br><br>
                Click the link below to reset your password.<br><br>
                <a href='https://almo.app/reset-password/?email={$email}'>Reset Password</a><br><br>
                Thanks,<br>Almo Team";

                emailnow($recipient,$senderName,$sender,$subject,$emailhtml);
            
                echo json_encode([
                    "status" => "success",
                    "message" => "We have sent an email to your email address with a link to reset your password.",
                ]);
                die;
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid input, Email required."
                ]);
            }
    break;
    case 'reset_password':

        $email = trim(strtolower($input['email']));
        $password = $input['password'];

        if (!empty($email) && !empty($password)) {

            $result = mysql_query("SELECT * FROM `accounts` WHERE `email` ='$email' LIMIT 1");
            $num_rows = mysql_num_rows($result);
            $password = password_hash($input['password'], PASSWORD_DEFAULT);
            $info = mysql_fetch_array($result);
            if($num_rows == 0){
                echo json_encode([
                    "status" => "error",
                    "message" => "User Not Found!, Please signup."
                ]);
                die;
            }

            if($num_rows == 1){

                // track activity
                track_activity($info['id'], 'Reset Password', 'Password Reset Successfully');

                $query = "UPDATE accounts SET `password`= '$password' WHERE `email` = '{$email}' Limit 1";
                $queryRun = mysql_query($query);

                echo json_encode([
                    "status" => "success",
                    "data" => "Password updated successfully."
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Email and password are required."
            ]);
        }

    break;
    case 'track_activity':

        if (!empty($input['activity'])) {
            $activity = $input['activity'];
            $activity_desc = $activity . ' tracked';
            $token = $input['token'];
            if(!empty($token)){
                $verifyUser = json_decode(verfiyUser($token), true);
                $account_id = $verifyUser['data'];
            }else{
                $account_id = 0;
            }
           
            // track activity
            track_activity($account_id, $activity, $activity_desc);

            echo json_encode([
                "status" => "success",
                "message" => "Activity tracked successfully."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid input, all fields are required."
            ]);
        }

    break;
    default:
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request type."
        ]);
    break;
}
