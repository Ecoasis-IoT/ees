<?php
/**
 * POST: Update a site
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/csrf.php';
require_once __DIR__ . '/../../common/validation.php';
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
    logSecurityEvent('csrf_failure', ['endpoint' => 'admin/site_update'], 'WARNING');
    http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$id        = intval($_POST['id']        ?? 0);
$site_name = sanitizeString($_POST['site_name'] ?? '');
$db_name   = sanitizeString($_POST['db_name']   ?? '');
$capacity  = floatval($_POST['capacity']        ?? 0);

if (!$id || !$site_name) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'ID and site name are required']);
    exit;
}

$pdo = getDB('admin');

try {
    $stmt = $pdo->prepare(
        "UPDATE tbl_site SET site_name=:name, db_name=:db, capacity=:cap WHERE id=:id"
    );
    $stmt->execute([':name' => $site_name, ':db' => $db_name, ':cap' => $capacity, ':id' => $id]);

    logSecurityEvent('site_updated', ['site_id' => $id, 'by' => $_SESSION['id']], 'INFO');

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'message' => 'Site updated successfully']);
} catch (PDOException $e) {
    error_log("site_update error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
