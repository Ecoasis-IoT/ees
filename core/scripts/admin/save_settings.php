<?php
/**
 * POST: Save settings for a group (currently: gateway)
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/csrf.php';
require_once __DIR__ . '/../../common/security_logging.php';
require_once __DIR__ . '/../../common/app_settings.php';
require_once __DIR__ . '/../../common/chirpstack_gateway.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'Err', 'message' => 'Method not allowed']);
    exit;
}

requireAdmin();

$csrf_token = trim($_POST['csrf_token'] ?? '');
if (!validateCSRFToken($csrf_token)) {
    logSecurityEvent('csrf_failure', ['endpoint' => 'admin/save_settings'], 'WARNING');
    http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$pdo = getDB('admin');

if (!ees_chirpstack_schema_ready($pdo)) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => ees_settings_migration_message()]);
    exit;
}

$group = trim($_POST['setting_group'] ?? 'gateway');

try {
    if ($group === 'gateway') {
        $api_url   = trim($_POST['api_url'] ?? '');
        $tenant_id = trim($_POST['tenant_id'] ?? '');
        $threshold = intval($_POST['offline_threshold_seconds'] ?? 900);

        if ($api_url === '') {
            ob_end_clean();
            echo json_encode(['status' => 'Err', 'message' => 'API URL is required']);
            exit;
        }

        ees_chirpstack_save_settings($pdo, $api_url, $tenant_id, $threshold);
        logSecurityEvent('settings_updated', ['group' => 'gateway', 'by' => $_SESSION['id'] ?? 0], 'INFO');
        ob_end_clean();
        echo json_encode(['status' => 'ok', 'message' => 'Gateway settings saved']);
        exit;
    }

    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'Unknown settings group']);
} catch (PDOException $e) {
    error_log('save_settings: ' . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
