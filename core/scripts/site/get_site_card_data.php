<?php
/**
 * Site card data: active power, daily/monthly/yearly production.
 * Works from both scripts/ and scripts/site/ locations.
 */

$config = file_exists(__DIR__ . '/../../config.php')
    ? __DIR__ . '/../../config.php'
    : __DIR__ . '/../../../config.php';
require_once $config;

$auth = file_exists(__DIR__ . '/../../common/auth.php')
    ? __DIR__ . '/../../common/auth.php'
    : __DIR__ . '/../../../core/common/auth.php';
require_once $auth;

require_once dirname($config) . '/core/common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

// Guard: must be an XMLHttpRequest (jQuery sends this automatically)
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    echo json_encode(['error' => 'Forbidden']); exit;
}

$site_db  = trim($_POST['site_db'] ?? '');
$timenow  = date('Y-m-d');

if (empty($site_db)) {
    echo json_encode(['error' => 'site_db required']);
    exit;
}

try {
    $db_key   = ees_db_key($site_db);
    $site_pdo = getDB($db_key);

    $q = $site_pdo->prepare(
        "SELECT COALESCE(active_power, 0) AS active_power
         FROM plant_active_power
         WHERE DATE(date) = :dt
         ORDER BY date DESC
         LIMIT 1"
    );
    $q->execute([':dt' => $timenow]);
    $site_power = $q->fetch() ?: ['active_power' => 0];

    $q = $site_pdo->prepare(
        "SELECT ROUND(COALESCE(SUM(production), 0), 2) AS daily
         FROM tbl_hourly_prod
         WHERE meter_id >= 100 AND DATE(datetime) = :dt"
    );
    $q->execute([':dt' => $timenow]);
    $site_daily = $q->fetch() ?: ['daily' => 0];

    $q = $site_pdo->prepare(
        "SELECT ROUND(COALESCE(SUM(production), 0), 2) AS monthly
         FROM tbl_hourly_prod
         WHERE meter_id >= 100 AND MONTH(datetime) = MONTH(:dt) AND YEAR(datetime) = YEAR(:dt2)"
    );
    $q->execute([':dt' => $timenow, ':dt2' => $timenow]);
    $site_monthly = $q->fetch() ?: ['monthly' => 0];

    $q = $site_pdo->prepare(
        "SELECT ROUND(COALESCE(SUM(production), 0), 2) AS yearly
         FROM tbl_hourly_prod
         WHERE meter_id >= 100 AND YEAR(datetime) = YEAR(:dt)"
    );
    $q->execute([':dt' => $timenow]);
    $site_yearly = $q->fetch() ?: ['yearly' => 0];

    echo json_encode([
        'active_power' => round((float)$site_power['active_power'], 2),
        'daily'        => round((float)$site_daily['daily'], 2),
        'monthly'      => round((float)$site_monthly['monthly'], 2),
        'yearly'       => round((float)$site_yearly['yearly'], 2),
    ]);
} catch (PDOException $e) {
    error_log("get_site_card_data (site/) error [{$site_db}]: " . $e->getMessage());
    echo json_encode(['active_power' => 0, 'daily' => 0, 'monthly' => 0, 'yearly' => 0]);
}
