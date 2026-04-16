<?php
/**
 * Sites that have at least one row in tbl_archive (per-site DB).
 * Used by the Archive page dropdown so users only pick sites that can load archive data.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

$pdo_admin = getDB('admin');

try {
    $stmt = $pdo_admin->query(
        "SELECT id, site_name, capacity, gateway_status, db_name FROM `tbl_site` ORDER BY `id`"
    );
    $all_sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('get_archive_sites admin error: ' . $e->getMessage());
    echo json_encode(['statusCode' => 'ok', 'data' => []]);
    exit;
}

$with_archive = [];
foreach ($all_sites as $site) {
    $pdo = tryGetDB(ees_db_key($site['db_name']));
    if (!$pdo) {
        continue;
    }
    try {
        $check = $pdo->query('SELECT 1 FROM tbl_archive LIMIT 1');
        if ($check && $check->fetch() !== false) {
            $with_archive[] = [
                'id'              => $site['id'],
                'site_name'       => $site['site_name'],
                'capacity'        => $site['capacity'],
                'gateway_status'  => $site['gateway_status'],
            ];
        }
    } catch (PDOException $e) {
        continue;
    }
}

echo json_encode(['statusCode' => 'ok', 'data' => $with_archive]);
