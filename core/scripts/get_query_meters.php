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
$stmt = $adm->prepare("SELECT db_name FROM tbl_site WHERE id = :id");
$stmt->execute([':id' => $site_id]);
$site = $stmt->fetch();
if (!$site) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$pdo = getDB(ees_db_key($site['db_name']));

try {
    $meters_stmt = $pdo->query(
        "SELECT address as meter_id, meter_name as name FROM tbl_meters WHERE id != 99"
    );
    $meters = $meters_stmt->fetchAll();

    $dates_stmt = $pdo->query(
        "SELECT MIN(DATE(datetime)) as startdate, MAX(DATE(datetime)) as enddate,
                MIN(MONTH(datetime)) as startmth, MAX(MONTH(datetime)) as endmth,
                MIN(YEAR(datetime)) as startyr,   MAX(YEAR(datetime)) as endyr
         FROM tbl_hourly_prod"
    );
    $validate = $dates_stmt->fetch();

    ob_end_clean();
    echo json_encode(['meters' => $meters, 'dates' => $validate]);
} catch (PDOException $e) {
    error_log("get_query_meters error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
