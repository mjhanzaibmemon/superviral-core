<?php
// loadEnv.php
function loadEnv($filePath)
{
    if (!file_exists($filePath)) {
        throw new Exception('.env file not found');
    }

    // Open and read the .env file
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Handle multi-line SSH keys or variables enclosed in quotes
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);

            // Trim the name and value
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\""); // Remove surrounding quotes and whitespace

            // If the value starts with '-----BEGIN' and ends with '-----END', it's likely an SSH key
            if (strpos($value, '-----BEGIN') === 0) {
                // Read multiline SSH keys
                while (strpos($line, '-----END') === false) {
                    $line = next($lines);
                    $value .= "\n" . trim($line);
                }
            }

            // Set the environment variable
            putenv(sprintf('%s=%s', $name, $value));

            // Dynamically create a variable with the name of the environment variable
            global $$name;
            $$name = $value;
        }
    }
}
