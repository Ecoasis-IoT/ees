<?php
/**
 * GET: All sites from tbl_site (includes gateway fields when available)
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/chirpstack_gateway.php';

header('Content-Type: application/json; charset=utf-8');

requireAdmin();

$pdo = getDB('admin');

try {
    $hasGateway = ees_chirpstack_schema_ready($pdo);

    if ($hasGateway) {
        $stmt = $pdo->query(
            "SELECT id, site_name, db_name, capacity, location, commissioned, gateway_status,
                    gateway_eui, gateway_poll_enabled, gateway_last_seen, chirpstack_token, num_pvdb
             FROM tbl_site
             ORDER BY site_name ASC"
        );
    } else {
        $stmt = $pdo->query(
            "SELECT id, site_name, db_name, capacity, location, commissioned, gateway_status, num_pvdb
             FROM tbl_site
             ORDER BY site_name ASC"
        );
    }

    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sites as &$site) {
        if ($hasGateway) {
            $site['token_set'] = trim((string)($site['chirpstack_token'] ?? '')) !== '';
            $envKey = ees_chirpstack_env_token_key((string)$site['db_name']);
            $site['env_token_key'] = $envKey;
            $site['env_token_set'] = $envKey && trim((string)getenv($envKey)) !== '';
            unset($site['chirpstack_token']);
        }
    }
    unset($site);

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'data' => $sites, 'gateway_schema' => $hasGateway]);
} catch (PDOException $e) {
    error_log("get_sites error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
