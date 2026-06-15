<?php
ob_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
    exit;
}

$site_db = trim($_POST['site_db'] ?? '');
if ($site_db === '') {
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
    exit;
}

$pdo = getDB(ees_db_key($site_db));

try {
    $stmt = $pdo->query(
        "SELECT status FROM tbl_main_FAP ORDER BY date DESC, id DESC LIMIT 1"
    );
    $row = $stmt->fetch();

    ob_end_clean();
    echo json_encode([
        'status'       => 'OK',
        'alarm_active' => ((int)($row['status'] ?? 0)) === 1,
        'panel_status' => (int)($row['status'] ?? 0),
    ]);
} catch (PDOException $e) {
    error_log('get_site_fap_status error: ' . $e->getMessage());
    ob_end_clean();
    echo json_encode([
        'status'       => 'OK',
        'alarm_active' => false,
        'panel_status' => 0,
        'missing'      => true,
    ]);
}
