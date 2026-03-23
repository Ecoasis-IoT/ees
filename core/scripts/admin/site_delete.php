<?php
/**
 * POST: Delete a site
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
    logSecurityEvent('csrf_failure', ['endpoint' => 'admin/site_delete'], 'WARNING');
    http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if (!$id) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'Invalid site ID']);
    exit;
}

$pdo = getDB('admin');

try {
    $stmt = $pdo->prepare("DELETE FROM tbl_site WHERE id = :id");
    $stmt->execute([':id' => $id]);

    logSecurityEvent('site_deleted', ['site_id' => $id, 'by' => $_SESSION['id']], 'WARNING');

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'message' => 'Site deleted']);
} catch (PDOException $e) {
    error_log("site_delete error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
