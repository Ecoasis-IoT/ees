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

$site_id = intval($_POST['site']  ?? 0);
$meter   = intval($_POST['meter'] ?? 0);
$month   = intval($_POST['month'] ?? 0);
$year    = intval($_POST['year']  ?? 0);

if (!$site_id || !$month || !$year) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$adm  = getDB('admin');
$stmt = $adm->prepare("SELECT site_name, db_name, capacity FROM tbl_site WHERE id = :id");
$stmt->execute([':id' => $site_id]);
$site = $stmt->fetch();
if (!$site) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$pdo = getDB(ees_db_key($site['db_name']));

try {
    $prod = $pdo->prepare(
        "SELECT DATE(DATETIME) AS datetime, meter_name,
                ROUND(SUM(production),2) as production
         FROM tbl_hourly_prod
         WHERE MONTH(DATETIME) = :month AND YEAR(DATETIME) = :year AND meter_id = :meter
         GROUP BY DATE(DATETIME)"
    );
    $prod->execute([':month' => $month, ':year' => $year, ':meter' => $meter]);
    $production = $prod->fetchAll();

    if ($meter >= 100) {
        $ap = $pdo->prepare(
            "SELECT date, meter_name, ROUND(active_power,2) AS active_power
             FROM plant_active_power
             WHERE MONTH(date) = :month AND YEAR(date) = :year AND meter_id = :meter"
        );
    } else {
        $ap = $pdo->prepare(
            "SELECT date, meter_name, ROUND(active_power,2) AS active_power
             FROM tbl_sub_meters
             WHERE MONTH(date) = :month AND YEAR(date) = :year AND meter_id = :meter"
        );
    }
    $ap->execute([':month' => $month, ':year' => $year, ':meter' => $meter]);
    $active_power = $ap->fetchAll();

    $irr = $pdo->prepare(
        "SELECT DATE(date) AS date, ROUND(SUM(insolation),2) as insolation
         FROM plant_irradiance
         WHERE MONTH(date) = :month AND YEAR(date) = :year
         GROUP BY DATE(date)"
    );
    $irr->execute([':month' => $month, ':year' => $year]);
    $irradiance = $irr->fetchAll();

    ob_end_clean();
    echo json_encode([
        'site_name'     => $site['site_name'],
        'site_capacity' => $site['capacity'],
        'prod'          => $production,
        'active_power'  => $active_power,
        'irradiance'    => $irradiance,
    ]);
} catch (PDOException $e) {
    error_log("query_month error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
