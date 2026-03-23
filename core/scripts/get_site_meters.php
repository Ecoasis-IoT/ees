<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

// Guard: must be an XMLHttpRequest (jQuery sends this automatically)
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    echo json_encode([]); exit;
}

$site_db = trim($_POST['site_db'] ?? '');

if (empty($site_db)) {
    echo json_encode([]);
    exit;
}

try {
    $db_key   = ees_db_key($site_db);
    $site_pdo = getDB($db_key);

    $stmt = $site_pdo->query("SELECT meter_name, device_type FROM tbl_meters ORDER BY meter_name ASC");
    $data = $stmt->fetchAll();
    echo json_encode($data);
} catch (PDOException $e) {
    error_log("get_site_meters error [{$site_db}]: " . $e->getMessage());
    echo json_encode([]);
}
