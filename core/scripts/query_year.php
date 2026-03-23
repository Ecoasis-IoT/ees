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
$year    = intval($_POST['year']  ?? 0);

if (!$site_id || !$year) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$adm  = getDB('admin');
$stmt = $adm->prepare("SELECT site_name, db_name, capacity FROM tbl_site WHERE id = :id");
$stmt->execute([':id' => $site_id]);
$site = $stmt->fetch();
if (!$site) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$pdo = getDB(ees_db_key($site['db_name']));

try {
    $prod = $pdo->prepare(
        "SELECT MONTH(DATETIME) AS datetime, meter_name,
                ROUND(SUM(production),2) as production
         FROM tbl_hourly_prod
         WHERE YEAR(DATETIME) = :year AND meter_id = :meter
         GROUP BY MONTH(DATETIME)"
    );
    $prod->execute([':year' => $year, ':meter' => $meter]);
    $production = $prod->fetchAll();

    $irr = $pdo->prepare(
        "SELECT MONTH(date) AS date, ROUND(SUM(insolation),2) as insolation
         FROM plant_irradiance
         WHERE YEAR(date) = :year
         GROUP BY MONTH(date)"
    );
    $irr->execute([':year' => $year]);
    $irradiance = $irr->fetchAll();

    ob_end_clean();
    echo json_encode([
        'site_name'     => $site['site_name'],
        'site_capacity' => $site['capacity'],
        'prod'          => $production,
        'irradiance'    => $irradiance,
    ]);
} catch (PDOException $e) {
    error_log("query_year error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
