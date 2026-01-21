<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$action ='reset';

switch ($action) {
      case 'reset':
        $allKeys = $redis->keys('*');
        foreach ($allKeys as $k) {
            $redis->del($k);
        }
echo 'done';
        break;
}
