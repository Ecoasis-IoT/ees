<?php
ob_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

// Guard: must be an XMLHttpRequest (jQuery sends this automatically)
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    ob_end_clean(); echo json_encode(['status' => 'Err']); exit;
}

$site_id    = intval($_POST['site']       ?? 0);
$start_date = trim($_POST['start_date']   ?? '');
$end_date   = trim($_POST['end_date']     ?? '');

if (!$site_id || empty($start_date) || empty($end_date)) {
    ob_end_clean(); echo json_encode(['status' => 'Err']); exit;
}

$adm  = getDB('admin');
$stmt = $adm->prepare("SELECT site_name, db_name FROM tbl_site WHERE id = :id");
$stmt->execute([':id' => $site_id]);
$site = $stmt->fetch();
if (!$site) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$pdo = getDB(ees_db_key($site['db_name']));

try {
    $s = $pdo->prepare(
        "SELECT date, production, insolation FROM tbl_archive
         WHERE date >= :start AND date <= :end"
    );
    $s->execute([':start' => $start_date, ':end' => $end_date]);
    ob_end_clean();
    echo json_encode(['site_name' => $site['site_name'], 'archive' => $s->fetchAll()]);
} catch (PDOException $e) {
    error_log("get_archive_data error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
