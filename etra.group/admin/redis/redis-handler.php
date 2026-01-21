<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$action = $_POST['action'] ?? '';
$key = $_POST['key'] ?? '';
$value = $_POST['value'] ?? '';

switch ($action) {
    case 'add':
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $redis->set($key, json_encode($decoded));
        }
        break;

    case 'delete':
        $redis->del($key);
        break;

    case 'reset':
        $allKeys = $redis->keys('*');
        foreach ($allKeys as $k) {
            $redis->del($k);
        }
        break;
}
