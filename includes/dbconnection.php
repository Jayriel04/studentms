<?php
// DB credentials.

// Read from environment variables, with local fallbacks for XAMPP.
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'studentmsdb';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

// For hosting providers like Render, a single DATABASE_URL is often provided.
$dbUrl = getenv('DATABASE_URL');
if ($dbUrl) {
    $dbConfig = parse_url($dbUrl);
    $dbHost = $dbConfig['host'];
    $dbName = ltrim($dbConfig['path'], '/');
    $dbUser = $dbConfig['user'];
    $dbPass = $dbConfig['pass'];
}

// Establish database connection.
try {
    $dbh = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    // Set PDO to throw exceptions on error, which is good practice.
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // In production, you might want to log this error instead of showing it to the user.
    exit("Database connection failed: " . $e->getMessage());
}
?>