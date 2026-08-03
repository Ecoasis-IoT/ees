<?php
/**
 * All sites list — migrated to PDO
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDB('admin');

try {
    $stmt  = $pdo->query("SELECT id, site_name, capacity, gateway_status FROM `tbl_site` ORDER BY `id`");
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['statusCode' => 'ok', 'data' => $sites]);
} catch (PDOException $e) {
    error_log("get_all_sites error: " . $e->getMessage());
    echo json_encode(['data' => []]);
}
