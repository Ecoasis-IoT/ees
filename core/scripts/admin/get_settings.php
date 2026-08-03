<?php
/**
 * GET: Application settings (gateway tab + future groups)
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/app_settings.php';
require_once __DIR__ . '/../../common/chirpstack_gateway.php';

header('Content-Type: application/json; charset=utf-8');

requireAdmin();

$pdo = getDB('admin');

if (!ees_chirpstack_schema_ready($pdo)) {
    ob_end_clean();
    echo json_encode([
        'status'  => 'migration_required',
        'message' => ees_settings_migration_message(),
        'gateway' => [
            'global' => ees_chirpstack_get_settings($pdo),
            'sites'  => [],
        ],
    ]);
    exit;
}

try {
    $global = ees_chirpstack_get_settings($pdo);

    $stmt = $pdo->query(
        'SELECT id, site_name, db_name, gateway_status, gateway_eui,
                chirpstack_token, gateway_poll_enabled, gateway_last_seen
         FROM tbl_site
         ORDER BY site_name ASC'
    );
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sites as &$site) {
        $site['token_set'] = trim((string)($site['chirpstack_token'] ?? '')) !== '';
        $envKey = ees_chirpstack_env_token_key((string)$site['db_name']);
        $site['env_token_key'] = $envKey;
        $site['env_token_set'] = $envKey && trim((string)getenv($envKey)) !== '';
        unset($site['chirpstack_token']);
    }
    unset($site);

    ob_end_clean();
    echo json_encode([
        'status'  => 'ok',
        'gateway' => [
            'global' => $global,
            'sites'  => $sites,
        ],
    ]);
} catch (PDOException $e) {
    error_log('get_settings: ' . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
