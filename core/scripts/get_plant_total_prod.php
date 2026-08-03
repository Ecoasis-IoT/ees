<?php
ob_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

$site_id    = intval($_POST['site_id']  ?? 0);
$start_date = trim($_POST['start_date'] ?? '');
$end_date   = trim($_POST['end_date']   ?? '');

if (!$site_id || $start_date === '' || $end_date === '') {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'code' => 'bad_params', 'message' => 'Missing site or date range.']);
    exit;
}

$adm  = getDB('admin');
$stmt = $adm->prepare("SELECT db_name, capacity FROM tbl_site WHERE id = :id");
$stmt->execute([':id' => $site_id]);
$site = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$site) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'code' => 'site_not_found', 'message' => 'Unknown site. Refresh the page and choose a plant again.']);
    exit;
}

$dbKey = ees_db_key((string)$site['db_name']);
$pdo   = tryGetDB($dbKey);
if (!$pdo) {
    error_log('get_plant_total_prod: tryGetDB failed for key ' . $dbKey . ' (site db_name=' . ($site['db_name'] ?? '') . ')');
    ob_end_clean();
    echo json_encode([
        'status'  => 'Err',
        'code'    => 'db_unavailable',
        'message' => 'Could not connect to this plant database. Check .env credentials for the site DB (see server log for key).',
    ]);
    exit;
}
$capacity = (float)$site['capacity'];

try {
    $prod_stmt = $pdo->prepare(
        "SELECT ROUND(SUM(production),2) as total_prod
         FROM tbl_hourly_prod
         WHERE meter_id >= 100 AND DATE(datetime) >= DATE(:start) AND DATE(datetime) <= DATE(:end)"
    );
    $prod_stmt->execute([':start' => $start_date, ':end' => $end_date]);
    $data_prod = $prod_stmt->fetch(PDO::FETCH_ASSOC);

    $ins_stmt = $pdo->prepare(
        "SELECT ROUND(SUM(insolation),2) as insolation
         FROM plant_irradiance
         WHERE DATE(date) >= DATE(:start) AND DATE(date) <= DATE(:end)"
    );
    $ins_stmt->execute([':start' => $start_date, ':end' => $end_date]);
    $data_ins = $ins_stmt->fetch(PDO::FETCH_ASSOC);

    $insolation = (float)($data_ins['insolation'] ?? 0);
    $total_prod = (float)($data_prod['total_prod'] ?? 0);
    $denom      = $insolation * $capacity;
    $pr         = $denom > 0 ? round(($total_prod / $denom) * 100, 0) : 0;
    $co2        = round(($total_prod * 0.001) * 966, 2);

    ob_end_clean();
    require_once __DIR__ . '/../common/audit_logging.php';
    ees_audit_log_report('plant_total_prod', [
        'site_id'    => $site_id,
        'start_date' => $start_date,
        'end_date'   => $end_date,
    ]);
    echo json_encode(['prod' => $total_prod, 'insolation' => $insolation, 'pr' => $pr, 'co2' => $co2]);
} catch (PDOException $e) {
    error_log("get_plant_total_prod error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode([
        'status'  => 'Err',
        'code'    => 'sql_error',
        'message' => 'Data query failed. See server log for details.',
    ]);
}
