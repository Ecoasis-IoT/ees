<?php
/**
 * Log plant PDF generation and create an in-app notification.
 */
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/csrf.php';
require_once __DIR__ . '/../common/user_notifications.php';
require_once __DIR__ . '/../common/audit_logging.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$csrf = trim($_POST['csrf_token'] ?? '');
if (!validateCSRFToken($csrf)) {
    ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$user_id = (int)($_SESSION['id'] ?? 0);
if ($user_id <= 0) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$site_id    = (int)($_POST['site_id'] ?? 0);
$site_name  = trim((string)($_POST['site_name'] ?? ''));
$start_date = trim((string)($_POST['start_date'] ?? ''));
$end_date   = trim((string)($_POST['end_date'] ?? ''));

if ($site_id <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Missing site']);
    exit;
}

if ($site_name === '') {
    try {
        $pdo  = getDB('admin');
        $stmt = $pdo->prepare('SELECT site_name FROM tbl_site WHERE id = ? LIMIT 1');
        $stmt->execute([$site_id]);
        $site_name = (string)($stmt->fetchColumn() ?: 'Plant');
    } catch (PDOException $e) {
        $site_name = 'Plant';
    }
}

$context = [
    'site_id'    => $site_id,
    'site_name'  => $site_name,
    'start_date' => $start_date,
    'end_date'   => $end_date,
];

$ok = ees_notify_plant_pdf_generated($user_id, $context);

ees_audit_log_report('plant_pdf_generated', $context);

ob_clean();
echo json_encode(['success' => $ok]);
