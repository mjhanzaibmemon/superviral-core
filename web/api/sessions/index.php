<?php

$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';

$input = json_decode(file_get_contents("php://input"), true);

$type = $input['type'];


switch ($type) {
    case 'user_session':

    $userId = $input['userId'];
    $sessionId = md5(time().$userId);
    $timeStamp = time();
    $events = $input['events'];
    $encodedEvents = addslashes(json_encode($events));
    $lastInsertId = null;

    $checkQuery = "SELECT * FROM `sessions` WHERE user_id = '$userId' ORDER BY added ASC";
    $checkResult = mysql_query($checkQuery);

    $isNewUser = (mysql_num_rows($checkResult) == 0);
    $firstSessionTime = null;
    if (!$isNewUser) {
        $firstRow = mysql_fetch_array($checkResult);
        $firstSessionTime = $firstRow['added'];
    }

    if ($isNewUser) {
        $insertQuery = "INSERT INTO `sessions` (user_id, session_id, added, last_visit) 
                        VALUES ('$userId', '$sessionId', '$timeStamp', '$timeStamp')";
        mysql_query($insertQuery);
        $lastInsertId = mysql_insert_id();
    }

    $checkIdleQuery = "SELECT * FROM `sessions` WHERE user_id = '$userId' ORDER BY added DESC LIMIT 1";
    $checkIdleResult = mysql_query($checkIdleQuery);

    if (mysql_num_rows($checkIdleResult) > 0) {
        $idleSession = mysql_fetch_array($checkIdleResult);
        $lastVisit = $idleSession['last_visit'];

        if ($firstSessionTime && ($timeStamp - $firstSessionTime) < 600) {
            $idleLimit = 600; // 10 mins for first session only
        } else {
            $idleLimit = 1800; // 30 mins for all later sessions
        }

        $timeDifference = $timeStamp - $lastVisit;

        if ($timeDifference > $idleLimit) {
            // New session
            $insertQuery = "INSERT INTO `sessions` (user_id, session_id, added, last_visit) 
                            VALUES ('$userId', '$sessionId', '$timeStamp', '$timeStamp')";
            mysql_query($insertQuery);
            $lastInsertId = mysql_insert_id();
        } else {
            // Update last visit
            $updateQuery = "UPDATE `sessions` SET last_visit = '$timeStamp' WHERE id = " . $idleSession['id'];
            mysql_query($updateQuery);
            $lastInsertId = $idleSession['id'];
        }
    }

    if ($lastInsertId) {
        $insertAction = "INSERT INTO `session_actions` (session_id, `events`) VALUES ('$lastInsertId', '$encodedEvents')";
        mysql_query($insertAction);
    }

    echo json_encode(['status' => 'success']);

    break;

}