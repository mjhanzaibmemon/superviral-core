<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../aws-sdk/aws-autoloader.php';

// Load database credentials from environment variables
$dbHost = getenv('MYSQL_HOST');
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('MYSQL_DATABASE');
$dbUser = getenv('MYSQL_USER');
$dbPassword = getenv('MYSQL_PASSWORD');

die;

try {
    // Create the Data Source Name (DSN) string
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";

    // Connection options for PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => false,
    ];

    // Establish a new PDO connection
    $pdo = new PDO($dsn, $dbUser, $dbPassword, $options);

    // Test the connection with a simple query
    $query = $pdo->query('SELECT * FROM `orders` LIMIT 10');
    $result = $query->fetch();

    // Output success message
    echo "Database connection successful! Test query result: " . json_encode($result) . PHP_EOL;
} catch (PDOException $e) {
    // Handle connection errors
    echo "Database connection failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

?>
