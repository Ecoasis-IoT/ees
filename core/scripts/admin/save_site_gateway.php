<?php
/**
 * POST: Save per-site gateway configuration
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/csrf.php';
require_once __DIR__ . '/../../common/security_logging.php';
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
    logSecurityEvent('csrf_failure', ['endpoint' => 'admin/save_site_gateway'], 'WARNING');
    http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$pdo = getDB('admin');

if (!ees_chirpstack_schema_ready($pdo)) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'Database migration required']);
    exit;
}

$id              = intval($_POST['id'] ?? 0);
$gateway_eui     = strtolower(trim($_POST['gateway_eui'] ?? ''));
$poll_enabled    = !empty($_POST['gateway_poll_enabled']) ? 1 : 0;
$new_token       = trim($_POST['chirpstack_token'] ?? '');
$clear_token     = !empty($_POST['clear_token']);

if (!$id) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'Site ID is required']);
    exit;
}

try {
    if ($clear_token) {
        $stmt = $pdo->prepare(
            'UPDATE tbl_site
             SET gateway_eui = :eui,
                 gateway_poll_enabled = :enabled,
                 chirpstack_token = NULL
             WHERE id = :id'
        );
        $stmt->execute([':eui' => $gateway_eui ?: null, ':enabled' => $poll_enabled, ':id' => $id]);
    } elseif ($new_token !== '') {
        $stmt = $pdo->prepare(
            'UPDATE tbl_site
             SET gateway_eui = :eui,
                 gateway_poll_enabled = :enabled,
                 chirpstack_token = :token
             WHERE id = :id'
        );
        $stmt->execute([
            ':eui'     => $gateway_eui ?: null,
            ':enabled' => $poll_enabled,
            ':token'   => $new_token,
            ':id'      => $id,
        ]);
    } else {
        $stmt = $pdo->prepare(
            'UPDATE tbl_site
             SET gateway_eui = :eui,
                 gateway_poll_enabled = :enabled
             WHERE id = :id'
        );
        $stmt->execute([':eui' => $gateway_eui ?: null, ':enabled' => $poll_enabled, ':id' => $id]);
    }

    logSecurityEvent('site_gateway_updated', ['site_id' => $id, 'by' => $_SESSION['id'] ?? 0], 'INFO');

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'message' => 'Site gateway settings saved']);
} catch (PDOException $e) {
    error_log('save_site_gateway: ' . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
