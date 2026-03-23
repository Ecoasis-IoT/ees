<?php
/**
 * GET: All devices for a given site
 * Query params: site_id (int)
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

requireAdmin();

$site_id = intval($_GET['site_id'] ?? $_POST['site_id'] ?? 0);

if (!$site_id) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'site_id is required']);
    exit;
}

$admin_pdo = getDB('admin');

try {
    // Resolve db_name for this site
    $stmt = $admin_pdo->prepare("SELECT db_name, site_name FROM tbl_site WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $site_id]);
    $site = $stmt->fetch();

    if (!$site) {
        ob_end_clean();
        echo json_encode(['status' => 'Err', 'message' => 'Site not found']);
        exit;
    }

    // Map db_name to getDB() key
    $db_key = _dbNameToKey($site['db_name']);
    $site_pdo = getDB($db_key);

    $meters = $site_pdo->query(
        "SELECT id, meter_name, device_type FROM tbl_meters ORDER BY meter_name ASC"
    )->fetchAll();

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'site_name' => $site['site_name'], 'data' => $meters]);
} catch (PDOException $e) {
    error_log("get_devices error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
