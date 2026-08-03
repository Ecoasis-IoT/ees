<?php
/**
 * GET: All users
 * Returns JSON array of user objects.
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/security_logging.php';

header('Content-Type: application/json; charset=utf-8');

requireAdmin();

$pdo = getDB('admin');

try {
    $stmt = $pdo->query(
        "SELECT id,
                CONCAT(firstname, ' ', lastname) AS fullname,
                firstname,
                lastname,
                username,
                email,
                group_id,
                date_added
         FROM tbl_user
         ORDER BY date_added DESC"
    );
    $users = $stmt->fetchAll();
    ob_end_clean();
    echo json_encode(['status' => 'ok', 'data' => $users]);
} catch (PDOException $e) {
    error_log("get_users error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
