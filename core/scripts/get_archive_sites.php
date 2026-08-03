<?php
/**
 * Sites whose per-site DB has a tbl_archive table (empty or not).
 * Used by the Archive page so only sites that can run archive queries are listed.
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
        $t = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'tbl_archive'"
        );
        if ($t && (int) $t->fetchColumn() > 0) {
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
