<?php
/*
  Flexible PDO connection supporting MySQL (default) and PostgreSQL.
  Control via environment variables:
    DB_DRIVER  = mysql | pgsql
    DB_HOST
    DB_PORT
    DB_NAME
    DB_USER
    DB_PASS
*/
$driver = getenv('DB_DRIVER') ?: 'mysql';
$host   = getenv('DB_HOST') ?: 'localhost';
$port   = getenv('DB_PORT') ?: ($driver === 'pgsql' ? '5432' : '3306');
$dbname = getenv('DB_NAME') ?: 'studentmsdb';
$user   = getenv('DB_USER') ?: 'root';
$pass   = getenv('DB_PASS') ?: '';

if ($driver === 'pgsql') {
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
} else {
    // default to MySQL
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8";
}

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

if ($driver === 'mysql') {
    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8'";
}

try {
    $dbh = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit("Database connection error: " . $e->getMessage());
}
?>