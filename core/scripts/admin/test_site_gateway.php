<?php
/**
 * POST: Test ChirpStack gateway for one site (optionally persist status)
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/csrf.php';
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

$id = intval($_POST['id'] ?? 0);
if (!$id) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'Site ID is required']);
    exit;
}

// Allow test with form values before save
$gateway_eui  = strtolower(trim($_POST['gateway_eui'] ?? ''));
$new_token    = trim($_POST['chirpstack_token'] ?? '');
$poll_enabled = !empty($_POST['gateway_poll_enabled']) ? 1 : 0;

try {
    $stmt = $pdo->prepare(
        'SELECT id, site_name, db_name, gateway_eui, chirpstack_token, gateway_poll_enabled
         FROM tbl_site WHERE id = :id LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $site = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$site) {
        ob_end_clean();
        echo json_encode(['status' => 'Err', 'message' => 'Site not found']);
        exit;
    }

    if ($gateway_eui !== '') {
        $site['gateway_eui'] = $gateway_eui;
    }
    if ($new_token !== '') {
        $site['chirpstack_token'] = $new_token;
    }
    $site['gateway_poll_enabled'] = $poll_enabled ?: ($site['gateway_poll_enabled'] ?? 0);

    // Force enabled for test even if checkbox off
    $site['gateway_poll_enabled'] = 1;

    $result = ees_chirpstack_poll_site($pdo, $site, null, true);

    ob_end_clean();
    if (!empty($result['ok'])) {
        echo json_encode([
            'status'    => 'ok',
            'message'   => ($result['online'] ? 'Gateway ONLINE' : 'Gateway OFFLINE'),
            'online'    => !empty($result['online']),
            'last_seen' => $result['last_seen'] ?? null,
            'state'     => $result['state'] ?? null,
            'name'      => $result['name'] ?? null,
        ]);
    } else {
        echo json_encode([
            'status'  => 'Err',
            'message' => $result['error'] ?? 'Gateway test failed',
        ]);
    }
} catch (PDOException $e) {
    error_log('test_site_gateway: ' . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
