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
    case 'stats':

        $sql = "
                SELECT
                    SUM(CASE WHEN activity_type = 'Visited Homepage' THEN 1 ELSE 0 END) AS homepage,
                    SUM(CASE WHEN activity_type = 'Visited Thoughtpage' THEN 1 ELSE 0 END) AS thought,
                    SUM(CASE WHEN activity_type = 'Create Thought' THEN 1 ELSE 0 END) AS saveThoughtClicks,
                    SUM(CASE WHEN activity_type = 'start-breathing' THEN 1 ELSE 0 END) AS breathingStarted,
                    SUM(CASE WHEN activity_type = 'calm tab' THEN 1 ELSE 0 END) AS moodToggleCalm,
                    SUM(CASE WHEN activity_type = 'sleep tab' THEN 1 ELSE 0 END) AS moodToggleSleep,
                    SUM(CASE WHEN activity_type = 'active tab' THEN 1 ELSE 0 END) AS moodToggleActive,
                    SUM(CASE WHEN activity_type = 'deep tab' THEN 1 ELSE 0 END) AS moodToggleDeep,
                    SUM(CASE WHEN activity_type = 'calm' THEN 1 ELSE 0 END) AS breathingCalm,
                    SUM(CASE WHEN activity_type = 'sleep' THEN 1 ELSE 0 END) AS breathingSleep,
                    SUM(CASE WHEN activity_type = 'active' THEN 1 ELSE 0 END) AS breathingActive,
                    SUM(CASE WHEN activity_type = 'deep' THEN 1 ELSE 0 END) AS breathingDeep
                FROM account_stats
            ";


        $queryRun = mysql_query($sql);
        $data = mysql_fetch_array($queryRun);

        $response = [
            "pageVisits" => [
                "homepage" => (int)$data["homepage"],
                "thoughts" => (int)$data["thought"]
            ],
            "userActions" => [
                "saveThoughtClicks" => (int)$data["saveThoughtClicks"],
                "breathingStarted" => (int)$data["breathingStarted"]
            ],
            // "moodToggles" => [
            //     "calm" => (int)$data["moodToggleCalm"],
            //     "sleep" => (int)$data["moodToggleSleep"],
            //     "active" => (int)$data["moodToggleActive"],
            //     "deep" => (int)$data["moodToggleDeep"]
            // ],
            // "breathingByMood" => [
            //     "calm" => (int)$data["breathingCalm"],
            //     "sleep" => (int)$data["breathingSleep"],
            //     "active" => (int)$data["breathingActive"],
            //     "deep" => (int)$data["breathingDeep"]
            // ]
        ];

        echo json_encode([
            "status" => "success",
            "data" => $response 
        ], JSON_PRETTY_PRINT);
        die;
        break;
    default:
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request type."
        ]);
        break;
}
