<?php
/**
 * POST: Create a new site
 */

ob_start();

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../common/auth.php';
require_once __DIR__ . '/../../common/authorization.php';
require_once __DIR__ . '/../../common/csrf.php';
require_once __DIR__ . '/../../common/validation.php';
require_once __DIR__ . '/../../common/security_logging.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'Err', 'message' => 'Method not allowed']);
    exit;
}

requireAdmin();

$csrf_token = trim($_POST['csrf_token'] ?? '');
if (!validateCSRFToken($csrf_token)) {
    logSecurityEvent('csrf_failure', ['endpoint' => 'admin/site_create'], 'WARNING');
    http_response_code(403);
    echo json_encode(['status' => 'Err', 'message' => 'Invalid request token']);
    exit;
}

$site_name = sanitizeString($_POST['site_name'] ?? '');
$db_name   = sanitizeString($_POST['db_name']   ?? '');
$capacity  = floatval($_POST['capacity']        ?? 0);

if (!$site_name || !$db_name) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => 'Site name and DB name are required']);
    exit;
}

$pdo = getDB('admin');

try {
    $ins = $pdo->prepare(
        "INSERT INTO tbl_site (site_name, db_name, capacity)
         VALUES (:site_name, :db_name, :capacity)"
    );
    $ins->execute([':site_name' => $site_name, ':db_name' => $db_name, ':capacity' => $capacity]);
    $new_id = $pdo->lastInsertId();

    logSecurityEvent('site_created', ['site_id' => $new_id, 'site_name' => $site_name, 'by' => $_SESSION['id']], 'INFO');

    ob_end_clean();
    echo json_encode(['status' => 'ok', 'id' => $new_id, 'message' => 'Site created successfully']);
} catch (PDOException $e) {
    error_log("site_create error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['status' => 'Err', 'message' => 'Server error']);
}
