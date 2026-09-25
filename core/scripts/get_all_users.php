<?php
/**
 * All users list — migrated to PDO
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/authorization.php';
require_once __DIR__ . '/../common/two_factor_auth.php';

header('Content-Type: application/json; charset=utf-8');

requireAdmin();

$pdo = getDB('admin');
ees_ensure_tbl_user_2fa($pdo);

try {
    $stmt = $pdo->query(
        "SELECT u.id,
                CONCAT(u.firstname,' ',u.lastname) AS fullname,
                u.email,
                u.date_added,
                CASE WHEN t.enabled = 1 THEN 1 ELSE 0 END AS tfa_enabled
         FROM tbl_user u
         LEFT JOIN tbl_user_2fa t ON t.user_id = u.id
         ORDER BY u.date_added DESC"
    );
    $users = $stmt->fetchAll();
    echo json_encode($users);
} catch (PDOException $e) {
    error_log("get_all_users error: " . $e->getMessage());
    echo json_encode([]);
}
