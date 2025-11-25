<?php
// Example of PDO connection using environment variables
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    // ... rest of your connection setup
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>