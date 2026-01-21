<?php

$host = $_SERVER['HTTP_HOST']; // Get the current host (e.g., anuj.etra.group)
$subdomain = explode('.', $host)[0]; // Get the first part of the domain
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/etra.group';
if (!empty($initial) && $initial != "etra.") $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;

require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/common/core/layout.php';

$tpl = file_get_contents('tpl.html');

$redis = new Redis();
try {
    $redis->connect('127.0.0.1', 6379);
} catch (Exception $e) {
    echo "Redis connection failed: " . $e->getMessage();
    die;
}

$allKeys = $redis->keys('*');
$results = '';
$ssmKey = '';
foreach ($allKeys as $key) {
    $value = $redis->get($key);
    $decoded = json_decode($value, true);

    if (is_array($decoded)) {

        if (strpos($key, 'ssm_cache') !== false) {
            // echo "Found!";
            $ssmKey = $key;
            continue; // Skip the ssm_cache key
        }
        $results .= "<div class='key-block'>";
        $results .= "<div class='key-header'><strong>Key:</strong> <code>$key</code> ⬇️
         <button class='btn delete-btn' id='$id' data-key='$key' style='float:right;'>Delete</button></div>";
        $results .= "<div class='key-details' style='display:none;'><table><tr><th>Field</th><th>Value</th></tr>";

        foreach ($decoded as $field => $val) {
            if (is_array($val) || is_object($val)) {
                $val = json_encode($val);
            }
            $results .= "<tr><td>" . htmlspecialchars($field) . "</td><td>" . htmlspecialchars($val) . "</td></tr>";
        }

        $results .= "</table></div></div>";
    }
}

$tpl = str_replace('{ssmKey}', $ssmKey, $tpl);
$tpl = str_replace('{results}', $results, $tpl);
output($tpl, $options);
