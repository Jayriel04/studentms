
<?php
header('Content-Type: application/json; charset=utf-8');
// adjust the path if your dbconnection file is elsewhere
require_once __DIR__ . '/includes/dbconnection.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid id']);
    exit;
}

try {
    $sql = "SELECT * FROM tblpublicnotice WHERE ID = :id LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Not found']);
        exit;
    }

    // sanitize description but allow basic formatting tags
    $description = isset($row['NoticeDescription']) ? $row['NoticeDescription'] : (isset($row['NoticeContent']) ? $row['NoticeContent'] : '');
    $description = strip_tags($description, '<p><br><strong><em><ul><ol><li><a>');

    $title = isset($row['NoticeTitle']) ? $row['NoticeTitle'] : 'Notice';
    $creationDate = isset($row['CreationDate']) ? $row['CreationDate'] : '';

    echo json_encode([
        'success' => true,
        'title' => $title,
        'creationDate' => $creationDate,
        'description' => $description
    ]);
} catch (Exception $e) {
    error_log('get-public-notice error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Query error']);
}