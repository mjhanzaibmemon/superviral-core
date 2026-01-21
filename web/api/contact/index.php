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
    case 'contact':
        if (!empty($input['name']) && !empty($input['email']) && !empty($input['subject']) && !empty($input['message'])) {
            $name = trim(addslashes($input['name']));
            $email = trim(strtolower(addslashes($input['email'])));
            $message = trim(addslashes($input['message']));
            $subject = trim(addslashes($input['subject']));
            $added = time();

            // Insert contact message
            $query = "INSERT INTO contact_us (name, email, message, subject, added) VALUES ('$name', '$email', '$message', '$subject' ,'$added')";
            $queryRun = mysql_query($query);
            if($queryRun){
                echo json_encode([
                    "status" => "success",
                    "message" => "Your message has been sent successfully."
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