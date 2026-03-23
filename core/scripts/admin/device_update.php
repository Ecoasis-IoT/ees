<?php
/**
 * POST: Update a device
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
    http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$site_id     = intval($_POST['site_id']     ?? 0);
$device_id   = intval($_POST['device_id']   ?? 0);
$meter_name  = sanitizeString($_POST['meter_name']  ?? '');
$device_type = sanitizeString($_POST['device_type'] ?? '');

if (!$site_id || !$device_id || !$meter_name) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'site_id, device_id and meter_name required']);
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
    $upd = $site_pdo->prepare(
        "UPDATE tbl_meters SET meter_name=:name, device_type=:type WHERE id=:id"
    );
    $upd->execute([':name' => $meter_name, ':type' => $device_type, ':id' => $device_id]);

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'message' => 'Device updated']);
} catch (PDOException $e) {
    error_log("device_update error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
