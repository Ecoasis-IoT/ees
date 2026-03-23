<?php
ob_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

$site_id    = intval($_POST['site_id']    ?? 0);
$start_date = trim($_POST['start_date']   ?? '');
$end_date   = trim($_POST['end_date']     ?? '');

if (!$site_id || empty($start_date) || empty($end_date)) {
    ob_end_clean(); echo json_encode(['status' => 'Err']); exit;
}

$adm  = getDB('admin');
$stmt = $adm->prepare("SELECT db_name, capacity, main_meter FROM tbl_site WHERE id = :id");
$stmt->execute([':id' => $site_id]);
$site = $stmt->fetch();
if (!$site) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$pdo = tryGetDB(ees_db_key($site['db_name']));
if (!$pdo) { ob_end_clean(); echo json_encode([]); exit; }
$capacity   = (float)$site['capacity'];
$main_meter = (int)$site['main_meter'];

try {
    $energy_stmt = $pdo->prepare(
        "SELECT DATE(DATETIME) AS date, ROUND(SUM(production),2) as production
         FROM tbl_hourly_prod
         WHERE DATE(DATETIME) >= DATE(:start) AND DATE(DATETIME) <= DATE(:end) AND meter_id >= :meter
         GROUP BY DATE(DATETIME)"
    );
    $energy_stmt->execute([':start' => $start_date, ':end' => $end_date, ':meter' => $main_meter]);
    $energy = $energy_stmt->fetchAll();

    $irr_stmt = $pdo->prepare(
        "SELECT DATE(date) as date, ROUND(SUM(insolation),2) as insolation
         FROM plant_irradiance
         WHERE DATE(date) >= DATE(:start) AND DATE(date) <= DATE(:end)
         GROUP BY DATE(date)"
    );
    $irr_stmt->execute([':start' => $start_date, ':end' => $end_date]);
    $irradiance = $irr_stmt->fetchAll();

    // Merge energy + irradiance by date and compute PR
    $data = [];
    foreach ($energy as $e_row) {
        $row = ['date' => $e_row['date'], 'prod' => round($e_row['production'], 2), 'insolation' => 0, 'pr' => 0];
        foreach ($irradiance as $i_row) {
            if ($i_row['date'] === $e_row['date']) {
                $row['insolation'] = (float)$i_row['insolation'];
                $denom = $i_row['insolation'] * $capacity;
                $row['pr'] = $denom > 0 ? round(($e_row['production'] / $denom) * 100, 0) : 0;
                break;
            }
        }
        $data[] = $row;
    }

    ob_end_clean();
    echo json_encode($data);
} catch (PDOException $e) {
    error_log("get_plant_daily error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
