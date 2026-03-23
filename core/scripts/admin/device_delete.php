<?php
/**
 * POST: Delete a device
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/csrf.php';
require_once __DIR__ . '/../../common/security_logging.php';
require_once __DIR__ . '/../../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'Err', 'message' => 'Method not allowed']);
    exit;
}

requireAdmin();

$csrf_token = trim($_POST['csrf_token'] ?? '');
if (!validateCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$site_id   = intval($_POST['site_id']   ?? 0);
$device_id = intval($_POST['device_id'] ?? 0);

if (!$site_id || !$device_id) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'site_id and device_id required']);
    exit;
}

$admin_pdo = getDB('admin');

try {
    $stmt = $admin_pdo->prepare("SELECT db_name FROM tbl_site WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $site_id]);
    $site = $stmt->fetch();

    if (!$site) {
        ob_end_clean();
        echo json_encode(['status' => 'Err', 'message' => 'Site not found']);
        exit;
    }

    $site_pdo = getDB(_dbNameToKey($site['db_name']));
    $del = $site_pdo->prepare("DELETE FROM tbl_meters WHERE id = :id");
    $del->execute([':id' => $device_id]);

    logSecurityEvent('device_deleted', ['site_id' => $site_id, 'device_id' => $device_id, 'by' => $_SESSION['id']], 'WARNING');

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'message' => 'Device deleted']);
} catch (PDOException $e) {
    error_log("device_delete error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
