<?php
ob_start(); // Start output buffering
session_start();
include('../db.php');

if(!empty($_SESSION['id'])){

    header("Location: /blog-manage/");
    exit;
}

function getUserIP()
{
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }

    return $ip;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = addslashes($_POST['email']);
    $ip = getUserIP();
    $query = "SELECT * FROM etra_accounts WHERE email = '$email' AND ipaddress = '$ip' AND is_active = 1 LIMIT 1";
    $result = mysql_query($query);

    if ($result && mysql_num_rows($result) > 0) {
        $row = mysql_fetch_array($result);
        $_SESSION['id'] = $row['id'];
        header("Location: /blog-manage/");
        exit;
    } else {
        $message = "Invalid email or IP not authorized.";
    }
}

$tpl = file_get_contents('login.html');
$tpl = str_replace(
    ['{message}', '{email}'],
    [$message, isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''],
    $tpl
);

echo $tpl;
ob_end_flush(); // Flush output at the end
?>
