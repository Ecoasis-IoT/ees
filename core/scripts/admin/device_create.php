<?php
/**
 * POST: Create a device in a per-site database
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/csrf.php';
require_once __DIR__ . '/../../common/validation.php';
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
    logSecurityEvent('csrf_failure', ['endpoint' => 'admin/device_create'], 'WARNING');
    http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$site_id     = intval($_POST['site_id']     ?? 0);
$meter_name  = sanitizeString($_POST['meter_name']  ?? '');
$device_type = sanitizeString($_POST['device_type'] ?? '');

if (!$site_id || !$meter_name) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'site_id and meter_name are required']);
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
    $ins = $site_pdo->prepare(
        "INSERT INTO tbl_meters (meter_name, device_type) VALUES (:name, :type)"
    );
    $ins->execute([':name' => $meter_name, ':type' => $device_type]);
    $new_id = $site_pdo->lastInsertId();

    logSecurityEvent('device_created', ['site_id' => $site_id, 'device_id' => $new_id, 'by' => $_SESSION['id']], 'INFO');

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'id' => $new_id, 'message' => 'Device created']);
} catch (PDOException $e) {
    error_log("device_create error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
