<?php

die;

function readEnvFile($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("File not found: $filePath");
    }
    return file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

function updateEnvValue($filePath, $key, $newValue) {
    $lines = readEnvFile($filePath);
    $updatedLines = [];
    $keyFound = false;

    foreach ($lines as $line) {
        // Skip comments or empty lines
        if (str_starts_with(trim($line), '#') || empty(trim($line))) {
            $updatedLines[] = $line;
            continue;
        }

        // Parse the line
        [$currentKey, $currentValue] = explode('=', $line, 2);
        $currentKey = trim($currentKey);

        // Update the key if it matches
        if ($currentKey === $key) {
            $updatedLines[] = "$currentKey=$newValue";
            $keyFound = true;
        } else {
            $updatedLines[] = $line;
        }
    }

    // If the key doesn't exist, add it to the end of the file
    if (!$keyFound) {
        $updatedLines[] = "$key=$newValue";
    }

    // Write updated lines back to the file
    file_put_contents($filePath, implode(PHP_EOL, $updatedLines));
}


$envPath = '/home/etra/.env';
// updateEnvValue($envPath, 'dbUser', 'test');
// updateEnvValue($envPath, 'dbPass', 'test');
// updateEnvValue($envPath, 'dbName', 'test');
// updateEnvValue($envPath, 'dbUser', 'test');
// updateEnvValue($envPath, 'dbUser', 'test');
// 18.216.125.105

$data = readEnvFile($envPath);
echo '<pre>';
// print_r($data);
