<?php
// Example of PDO connection using environment variables
$host = getenv('dpg-d4ijlsili9vc73ejsmc0-a.oregon-postgres.render.com');
$dbname = getenv('studentmsdb');
$user = getenv('studentmsdb_user');
$password = getenv('11t2icxENdAP3KowXwkjREBznMXKV5Sq');

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    // ... rest of your connection setup
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>