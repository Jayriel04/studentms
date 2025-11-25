<?php
// Example of PDO connection using environment variables
// Use environment variables with safe defaults and explicit DSN
$host = getenv('DB_HOST') ?: 'dpg-d4ijlsili9vc73ejsmc0-a';
$dbname = getenv('DB_NAME') ?: 'studentmsdb';
$user = getenv('DB_USER') ?: 'studentmsdb_user';
$password = getenv('DB_PASSWORD') ?: '11t2icxENdAP3KowXwkjREBznMXKV5Sq';

$dsn = "pgsql:host={$host};port=5432;dbname={$dbname}";

try {
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    // ... rest of your connection setup ...
} catch(PDOException $e) {
    // Log DSN/user (do not log password) for debugging
    error_log("DB connection failed. DSN={$dsn} user={$user}");
    die("Connection failed: " . $e->getMessage());
}
?>
