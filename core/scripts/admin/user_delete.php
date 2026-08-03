<?php
/**
 * POST: Delete a user
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/csrf.php';
require_once __DIR__ . '/../../common/security_logging.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'Err', 'message' => 'Method not allowed']);
    exit;
}

requireAdmin();

$csrf_token = trim($_POST['csrf_token'] ?? '');
if (!validateCSRFToken($csrf_token)) {
    logSecurityEvent('csrf_failure', ['endpoint' => 'admin/user_delete'], 'WARNING');
    http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if (!$id) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'Invalid user ID']);
    exit;
}

// Prevent self-deletion
if ($id === intval($_SESSION['id'])) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'You cannot delete your own account']);
    exit;
}

$pdo = getDB('admin');

try {
    $stmt = $pdo->prepare("DELETE FROM tbl_user WHERE id = :id");
    $stmt->execute([':id' => $id]);

    logSecurityEvent('user_deleted', ['deleted_id' => $id, 'by' => $_SESSION['id']], 'WARNING');

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'message' => 'User deleted']);
} catch (PDOException $e) {
    error_log("user_delete error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
