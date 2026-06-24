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
    // A plant can have more than one panel device (e.g. a combined weather+switchgear
    // UC300, or separate units). Take the latest reading PER dev_eui and raise the
    // alarm if any of them is active, so a newer "normal" reading from one device can
    // never mask a real alarm reported by another.
    $stmt = $pdo->query(
        "SELECT t.status
           FROM tbl_main_FAP t
           JOIN (SELECT dev_eui, MAX(id) AS max_id FROM tbl_main_FAP GROUP BY dev_eui) m
             ON m.dev_eui = t.dev_eui AND m.max_id = t.id"
    );
    $rows = $stmt->fetchAll();

    $panel_status = 0;
    foreach ($rows as $r) {
        if ((int)$r['status'] === 1) { $panel_status = 1; break; }
    }

    ob_end_clean();
    echo json_encode([
        'status'       => 'OK',
        'alarm_active' => $panel_status === 1,
        'panel_status' => $panel_status,
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
