<?php
// Example of PDO connection using environment variables
// Use generic keys like DB_HOST, DB_NAME, etc.
$host = getenv('DB_HOST'); 
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

try {
    // The connection string uses the PHP variables defined above
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // ... rest of your connection setup
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
