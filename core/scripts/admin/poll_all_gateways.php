<?php
/**
 * POST: Poll all enabled site gateways now (admin manual trigger)
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

try {
    $results = ees_chirpstack_poll_all($pdo, true);

    foreach ($results as $result) {
        if (($result['db_name'] ?? '') === 'phoenix_mall.php') {
            ees_chirpstack_sync_phoenix_legacy_file($result);
            break;
        }
    }

    $polled  = 0;
    $online  = 0;
    $errors  = 0;
    $skipped = 0;

    foreach ($results as $r) {
        if (!empty($r['skipped'])) {
            $skipped++;
            continue;
        }
        if (!empty($r['ok'])) {
            $polled++;
            if (!empty($r['online'])) {
                $online++;
            }
        } else {
            $errors++;
        }
    }

    ob_end_clean();
    echo json_encode([
        'status'  => 'ok',
        'message' => "Polled {$polled} site(s): {$online} online, {$errors} error(s), {$skipped} skipped",
        'results' => $results,
    ]);
} catch (PDOException $e) {
    error_log('poll_all_gateways: ' . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
