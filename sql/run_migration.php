<?php
require_once __DIR__ . '/../includes/dbconnection.php';
$sqlFile = __DIR__ . '/add_approval_columns.sql';
if (!file_exists($sqlFile)) {
    echo "Migration file not found: $sqlFile\n";
    exit(1);
}
$sql = file_get_contents($sqlFile);
try {
    $dbh->exec($sql);
    echo "Migration applied successfully.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
