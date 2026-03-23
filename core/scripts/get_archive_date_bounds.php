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

$site_id = intval($_POST['site'] ?? 0);

if (!$site_id) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$adm  = getDB('admin');
$stmt = $adm->prepare("SELECT site_name, db_name FROM tbl_site WHERE id = :id");
$stmt->execute([':id' => $site_id]);
$site = $stmt->fetch();
if (!$site) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$pdo = getDB(ees_db_key($site['db_name']));

try {
    $s      = $pdo->query("SELECT MIN(date) as min, MAX(date) as max FROM tbl_archive");
    $bounds = $s->fetch();
    ob_end_clean();
    echo json_encode(['min' => $bounds['min'], 'max' => $bounds['max']]);
} catch (PDOException $e) {
    error_log("get_archive_date_bounds error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
