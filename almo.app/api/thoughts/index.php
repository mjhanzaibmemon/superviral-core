<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// 

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
    case 'create_thought':

        if (!empty($input['thought']) && !empty($input['token'])) {
    
            $token = $input['token'];
            $added = time();
    
            $verifyUser = json_decode(verfiyUser($token), true);
    
            if ($verifyUser['status'] == 'error') {
                echo json_encode([
                    "status" => "error",
                    "message" => "You are not authorised"
                ]);
                die;
            }
    
            $account_id = $verifyUser['data'];
    
            $thoughts = $input['thought'];
            if (!is_array($thoughts)) {
                $thoughts = [$thoughts]; // wrap in array if single thought sent
            }
    
            $inserted = 0;
            $failed = 0;
    
            foreach ($thoughts as $thought) {
                $thought = trim($thought);
                if ($thought === '') continue;
    
                // Optional duplicate check logic can be uncommented
                /*
                $checkQuery = "SELECT id FROM thoughts WHERE `thought` = '" . mysql_real_escape_string($thought) . "' AND `account_id` = '$account_id' LIMIT 1";
                $checkResult = mysql_query($checkQuery);
                if (mysql_num_rows($checkResult) > 0) {
                    continue; // Skip duplicate
                }
                */
    
                $safeThought = addslashes($thought);
                $query = "INSERT INTO thoughts (`account_id`, `thought`, `added`) VALUES ('$account_id', '$safeThought', '$added')";
                $queryRun = mysql_query($query);
    
                if ($queryRun) {
                    $inserted++;
                } else {
                    $failed++;
                }
            }
    
            if ($inserted > 0) {
                track_activity($account_id, 'Create Thought', "$inserted Thought(s) Added");
                echo json_encode([
                    "status" => "success",
                    "message" => "$inserted Thought(s) added successfully." . ($failed > 0 ? " $failed failed." : "")
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to add thoughts."
                ]);
            }
    
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid input. Token and thoughts required."
            ]);
        }
    
        break;         
    case 'get_thoughts':

        if (!empty($input['token'])) {
            $token = $input['token'];

            $verifyUser = json_decode(verfiyUser($token), true);

            if($verifyUser['status'] == 'error'){
                echo json_encode([
                    "status" => "error",
                    "message" => "You are not authorised"
                ]);
                die;
            }

            $account_id = $verifyUser['data'];

            $Query = "SELECT * FROM thoughts WHERE `account_id` = '{$account_id}' order by id desc";
            $queryRun = mysql_query($Query);

            if (mysql_num_rows($queryRun) == 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "No thought found."
                ]);
                die;
            }

            $thoughts = [];
            while($data = mysql_fetch_array($queryRun)){
                $thoughts[] = $data;
            }

            echo json_encode([
                "status" => "success",
                "data" => json_encode($thoughts)
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid input, account id required."
            ]);
        }

    break;
    case 'get_thought':

        if (!empty($input['thought_id'])) {
            $thought_id = $input['thought_id'];
            $token = $input['token'];
            
            $verifyUser = json_decode(verfiyUser($token), true);

            if($verifyUser['status'] == 'error'){
                echo json_encode([
                    "status" => "error",
                    "message" => "You are not authorised"
                ]);
                die;
            }

            $Query = "SELECT * FROM thoughts WHERE `id` = '{$thought_id}' order by id desc Limit 1";
            $queryRun = mysql_query($Query);

            if (mysql_num_rows($queryRun) == 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Thought not found."
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
                "message" => "Invalid input, thought id required."
            ]);
        }

    break;
    case 'delete_thought':

        if (!empty($input['thought_id'])) {
            $thought_id = $input['thought_id'];
            $token = $input['token'];
            
            $verifyUser = json_decode(verfiyUser($token), true);

            if($verifyUser['status'] == 'error'){
                echo json_encode([
                    "status" => "error",
                    "message" => "You are not authorised"
                ]);
                die;
            }

            $account_id = $verifyUser['data'];

            $Query = "DELETE FROM thoughts WHERE `id` = '{$thought_id}' order by id desc Limit 1";
            $queryRun = mysql_query($Query);

            // track activity
            track_activity($account_id, 'Delete Thought', 'Thought Deleted');
            if($queryRun){
                echo json_encode([
                    "status" => "success",
                    "message" => "Thought deleted successfully."
                ]);
            }else{
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to delete thought."
                ]);
            }
            
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid input, thought id required."
            ]);
        }

    break;
    case 'update_thought':

        if (!empty($input['thought_id']) && !empty($input['thought'])) {
            $thought_id = $input['thought_id'];
            $thought = $input['thought'];
            $token = $input['token'];
            
            $verifyUser = json_decode(verfiyUser($token), true);

            if($verifyUser['status'] == 'error'){
                echo json_encode([
                    "status" => "error",
                    "message" => "You are not authorised"
                ]);
                die;
            }
            $account_id = $verifyUser['data'];

            $Query = "UPDATE thoughts SET `thought` = '{$thought}' WHERE `id` = '{$thought_id}' Limit 1";
            $queryRun = mysql_query($Query);

            // track activity
            track_activity($account_id, 'Update Thought', 'Thought Updated');
            if($queryRun){
                echo json_encode([
                    "status" => "success",
                    "message" => "Thought updated successfully."
                ]);
            }else{
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to update thought."
                ]);
            }
            
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid input, thought id required."
            ]);
        }

    break;
    case 'save_mood':

        if (!empty($input['thought_id']) && !empty($input['mood'])) {
            $thought_id = $input['thought_id'];
            $mood = $input['mood'];
            $token = $input['token'];
            
            $verifyUser = json_decode(verfiyUser($token), true);

            if($verifyUser['status'] == 'error'){
                echo json_encode([
                    "status" => "error",
                    "message" => "You are not authorised"
                ]);
                die;
            }
            $account_id = $verifyUser['data'];
            $Query = "UPDATE thoughts SET `mood` = '{$mood}' WHERE `id` = '{$thought_id}' Limit 1";
            $queryRun = mysql_query($Query);

            // track activity
            track_activity($account_id, 'Save Mood', 'Mood Updated');

            if($queryRun){
                echo json_encode([
                    "status" => "success",
                    "message" => "Mood updated successfully."
                ]);
            }else{
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to update mood."
                ]);
            }
            
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid input. All fields are required."
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