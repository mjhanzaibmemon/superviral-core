<?php
function verfiyUser($token)
{

    $query = "SELECT id FROM accounts WHERE `token_hash` = '{$token}' order by id desc Limit 1";

    $queryRun = mysql_query($query);

    if (mysql_num_rows($queryRun) == 0) {
        return json_encode([
            "status" => "error",
            "code" => 404
        ]);
        die;
    } else {
        $data = mysql_fetch_array($queryRun);
        return json_encode([
            "status" => "success",
            "code" => 200,
            "data" => $data['id']
        ]);
        die;
    }
}

function getUserIP() {
    // Cloudflare: Get real IP if behind their proxy
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }

    $clientIP  = $_SERVER['HTTP_CLIENT_IP'] ?? null;
    $forwardedIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
    $remoteIP  = $_SERVER['REMOTE_ADDR'] ?? null;

    // Validate each option in priority order
    if (filter_var($clientIP, FILTER_VALIDATE_IP)) {
        return $clientIP;
    } elseif (filter_var($forwardedIP, FILTER_VALIDATE_IP)) {
        // Sometimes X_FORWARDED_FOR contains multiple IPs, get the first one
        $forwardedParts = explode(',', $forwardedIP);
        return trim($forwardedParts[0]);
    } else {
        return $remoteIP;
    }
}
