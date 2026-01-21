<?php


$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/common/func.php';
require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/common/emailer.php';

$input = json_decode(file_get_contents("php://input"), true);

$type = $input['type'];

switch ($type) {
    case 'feedback':
        if (!empty($input['feedback'])) {
            $feedback = trim(addslashes($input['feedback']));
            $added = time();

            // Insert contact message
            $query = "INSERT INTO feedback (feedback, added) VALUES ('$feedback', '$added')";
            $queryRun = mysql_query($query);
            if($queryRun){
                echo json_encode([
                    "status" => "success",
                    "message" => "Your feedback has been sent successfully."
                ]);
            }
        }else{
            echo json_encode([
                "status" => "error",
                "message" => "All fields are required."
            ]);
            die;
        }

    break;    

}