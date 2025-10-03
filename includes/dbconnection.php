<?php
// Local defaults
$db_host = '127.0.0.1';
$db_name = 'studentms';
$db_user = 'root';
$db_pass = '';

// If running on Heroku with JAWSDB or CLEARDB, parse URL
$cleardbUrl = getenv('CLEARDB_DATABASE_URL') ?: getenv('JAWSDB_URL') ?: getenv('DATABASE_URL');
if ($cleardbUrl) {
    $parts = parse_url($cleardbUrl);
    if ($parts) {
        $db_host = $parts['host'] ?? $db_host;
        $db_user = $parts['user'] ?? $db_user;
        $db_pass = $parts['pass'] ?? $db_pass;
        // path may start with '/', remove it
        $db_name = isset($parts['path']) ? ltrim($parts['path'], '/') : $db_name;
    }
}

try {
    $dbh = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // don't expose details in production; log or show friendly message
    error_log('DB connect error: ' . $e->getMessage());
    die('Database connection error.');
}
?>