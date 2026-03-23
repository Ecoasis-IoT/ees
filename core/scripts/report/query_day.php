<?php
/**
 * Daily query data — migrated to PDO
 * POST: site (int), meter (int), date (Y-m-d)
 */

$config = file_exists(__DIR__ . '/../../config.php')
    ? __DIR__ . '/../../config.php'
    : __DIR__ . '/../../../config.php';
require_once $config;

$auth = file_exists(dirname($config) . '/core/common/auth.php')
    ? dirname($config) . '/core/common/auth.php'
    : __DIR__ . '/../../common/auth.php';
require_once $auth;
require_once dirname($config) . '/core/common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

// Guard: must be an XMLHttpRequest (jQuery sends this automatically)
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    echo json_encode(['status' => 'Err']); exit;
}

$site_id = intval($_POST['site']  ?? 0);
$meter   = intval($_POST['meter'] ?? 0);
$date    = $_POST['date'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

if (!$site_id) {
    echo json_encode(['status' => 'Err', 'message' => 'site required']);
    exit;
}

$admin_pdo = getDB('admin');

try {
    $stmt = $admin_pdo->prepare(
        "SELECT site_name, db_name, capacity FROM tbl_site WHERE id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $site_id]);
    $res = $stmt->fetch();
} catch (PDOException $e) {
    error_log("query_day (report/) admin error: " . $e->getMessage());
    echo json_encode(['status' => 'Err']);
    exit;
}

if (!$res) {
    echo json_encode(['status' => 'Err', 'message' => 'Site not found']);
    exit;
}

$site_name     = $res['site_name'];
$site_capacity = $res['capacity'];
$db_key        = ees_db_key($res['db_name']);

try {
    $site_pdo = getDB($db_key);

    $q = $site_pdo->prepare(
        "SELECT TIME(datetime) AS datetime, meter_name, production
         FROM tbl_hourly_prod
         WHERE DATE(datetime) = :dt AND meter_id = :meter"
    );
    $q->execute([':dt' => $date, ':meter' => $meter]);
    $production = $q->fetchAll();

    if ($meter == 100 || $meter == 101) {
        $q = $site_pdo->prepare(
            "SELECT TIME(date) AS date, 'Main Meter' AS meter_name, ROUND(active_power,2) AS active_power
             FROM plant_active_power
             WHERE DATE(date) = :dt"
        );
        $q->execute([':dt' => $date]);
    } else {
        $q = $site_pdo->prepare(
            "SELECT TIME(date) AS date, meter_name, ROUND(active_power,2) AS active_power
             FROM tbl_sub_meters
             WHERE DATE(date) = :dt AND meter_id = :meter"
        );
        $q->execute([':dt' => $date, ':meter' => $meter]);
    }
    $active_power = $q->fetchAll();

    $q = $site_pdo->prepare(
        "SELECT TIME(date) AS date, irradiance, insolation, ambient_temp, panel_temp
         FROM plant_irradiance
         WHERE DATE(date) = :dt"
    );
    $q->execute([':dt' => $date]);
    $irradiance = $q->fetchAll();

    echo json_encode([
        'site_name'     => $site_name,
        'site_capacity' => $site_capacity,
        'prod'          => $production,
        'active_power'  => $active_power,
        'irradiance'    => $irradiance,
    ]);
} catch (PDOException $e) {
    error_log("query_day (report/) site error [{$db_key}]: " . $e->getMessage());
    echo json_encode(['status' => 'Err']);
}
