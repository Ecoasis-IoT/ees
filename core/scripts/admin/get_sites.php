<?php
/**
 * GET: All sites from tbl_site
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';

header('Content-Type: application/json; charset=utf-8');

requireAdmin();

$pdo = getDB('admin');

try {
    $stmt = $pdo->query(
        "SELECT id, site_name, db_name, capacity, location, commissioned, gateway_status, num_pvdb
         FROM tbl_site
         ORDER BY site_name ASC"
    );
    $sites = $stmt->fetchAll();
    ob_end_clean();
    echo json_encode(['status' => 'ok', 'data' => $sites]);
} catch (PDOException $e) {
    error_log("get_sites error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
