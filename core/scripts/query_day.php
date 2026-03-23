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

$site_id = intval($_POST['site']   ?? 0);
$meter   = intval($_POST['meter']  ?? 0);
$date    = trim($_POST['date']     ?? '');

if (!$site_id || empty($date)) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$adm  = getDB('admin');
$stmt = $adm->prepare("SELECT site_name, db_name, capacity FROM tbl_site WHERE id = :id");
$stmt->execute([':id' => $site_id]);
$site = $stmt->fetch();
if (!$site) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$pdo = getDB(ees_db_key($site['db_name']));

try {
    $prod = $pdo->prepare(
        "SELECT TIME(datetime) as datetime, meter_name, production
         FROM tbl_hourly_prod
         WHERE DATE(datetime) = DATE(:date) AND meter_id = :meter"
    );
    $prod->execute([':date' => $date, ':meter' => $meter]);
    $production = $prod->fetchAll();

    if ($meter == 100 || $meter == 101) {
        $ap = $pdo->prepare(
            "SELECT TIME(date) AS date, 'Main Meter' as meter_name,
                    ROUND(active_power,2) as active_power
             FROM plant_active_power WHERE DATE(date) = DATE(:date)"
        );
    } else {
        $ap = $pdo->prepare(
            "SELECT TIME(date) AS date, meter_name,
                    ROUND(active_power,2) as active_power
             FROM tbl_sub_meters
             WHERE DATE(date) = DATE(:date) AND meter_id = :meter"
        );
        $ap->bindValue(':meter', $meter, PDO::PARAM_INT);
    }
    $ap->bindValue(':date', $date);
    $ap->execute();
    $active_power = $ap->fetchAll();

    $irr = $pdo->prepare(
        "SELECT TIME(date) as date, irradiance, insolation, ambient_temp, panel_temp
         FROM plant_irradiance WHERE DATE(date) = DATE(:date)"
    );
    $irr->execute([':date' => $date]);
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
    error_log("query_day error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
