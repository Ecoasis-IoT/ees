<?php
// v2: Bo'Valon Mall — two main meters (meter_id 100 + 101)
ob_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

$site_db = trim($_POST['site_db'] ?? '');
$timenow = date('Y-m-d H:i');

if ($site_db === '') {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'reason' => 'missing_site_db']);
    exit;
}

try {
    $pdo = getDB(ees_db_key($site_db));
} catch (InvalidArgumentException $e) {
    error_log('get_site_card_datav2: unknown db key for site_db=' . $site_db . ' — ' . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'reason' => 'unknown_db_key']);
    exit;
}

try {
    $ap = $pdo->prepare(
        "(SELECT meter_id, active_power FROM plant_active_power
          WHERE DATE(date) = DATE(:now) AND meter_id = 100 ORDER BY date DESC LIMIT 1)
         UNION
         (SELECT meter_id, active_power FROM plant_active_power
          WHERE DATE(date) = DATE(:now) AND meter_id = 101 ORDER BY date DESC LIMIT 1)"
    );
    $ap->execute([':now' => $timenow]);
    $rows = $ap->fetchAll(PDO::FETCH_ASSOC);
    $by_meter = [100 => null, 101 => null];
    foreach ($rows as $row) {
        $mid = isset($row['meter_id']) ? (int) $row['meter_id'] : 0;
        if ($mid === 100 || $mid === 101) {
            $by_meter[$mid] = $row['active_power'] ?? null;
        }
    }

    $daily = $pdo->prepare("SELECT ROUND(SUM(production),2) as daily FROM tbl_hourly_prod WHERE meter_id >= 100 AND DATE(datetime) = DATE(:now)");
    $daily->execute([':now' => $timenow]);
    $site_daily = $daily->fetch();

    $monthly = $pdo->prepare("SELECT ROUND(SUM(production),2) as monthly FROM tbl_hourly_prod WHERE meter_id >= 100 AND MONTH(datetime) = MONTH(DATE(:now))");
    $monthly->execute([':now' => $timenow]);
    $site_monthly = $monthly->fetch();

    $yearly = $pdo->prepare("SELECT ROUND(SUM(production),2) as yearly FROM tbl_hourly_prod WHERE meter_id >= 100 AND YEAR(datetime) = YEAR(DATE(:now))");
    $yearly->execute([':now' => $timenow]);
    $site_yearly = $yearly->fetch();

    $irr = $pdo->prepare("SELECT AVG(irradiance) as avg FROM plant_irradiance WHERE DATE(date) = DATE(:now) AND irradiance != 0");
    $irr->execute([':now' => $timenow]);
    $avg_irradiance = $irr->fetch();

    $sun = $pdo->prepare("SELECT TIMESTAMPDIFF(MINUTE,MIN(date),MAX(date)) as minutes FROM plant_irradiance WHERE DATE(date) = DATE(:now) AND irradiance != 0");
    $sun->execute([':now' => $timenow]);
    $sun_hours = $sun->fetch();

    ob_end_clean();
    echo json_encode([
        'active_power1' => round((float)($by_meter[100] ?? 0), 2),
        'active_power2' => round((float)($by_meter[101] ?? 0), 2),
        'daily_prod'    => round((float)($site_daily['daily']   ?? 0), 2),
        'monthly_prod'  => round((float)($site_monthly['monthly']       ?? 0), 2),
        'yearly_prod'   => round((float)($site_yearly['yearly']         ?? 0), 2),
        'avg_irr'       => round((float)($avg_irradiance['avg']         ?? 0), 2),
        'sun_hours'     => (int)($sun_hours['minutes']                  ?? 0),
    ]);
} catch (PDOException $e) {
    error_log('get_site_card_datav2 error: ' . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'reason' => 'database']);
}
