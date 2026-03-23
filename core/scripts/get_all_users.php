<?php
/**
 * All users list — migrated to PDO
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDB('admin');

try {
    $stmt = $pdo->query(
        "SELECT id, CONCAT(firstname,' ',lastname) AS fullname, email, date_added
         FROM tbl_user
         ORDER BY date_added DESC"
    );
    $users = $stmt->fetchAll();
    echo json_encode($users);
} catch (PDOException $e) {
    error_log("get_all_users error: " . $e->getMessage());
    echo json_encode([]);
}
