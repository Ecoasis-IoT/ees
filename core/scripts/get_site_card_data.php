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

$site_db  = trim($_POST['site_db'] ?? '');
$timenow  = date('Y-m-d H:i');

if (empty($site_db)) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$pdo = getDB(ees_db_key($site_db));

try {
    $ap = $pdo->prepare("SELECT active_power FROM plant_active_power WHERE DATE(date) = DATE(:now) ORDER BY date DESC LIMIT 1");
    $ap->execute([':now' => $timenow]);
    $site_power = $ap->fetch();

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

    $roundOrNull = static function ($value) {
        return ($value === null || $value === '') ? null : round((float) $value, 2);
    };

    ob_end_clean();
    echo json_encode([
        'active_power'  => $site_power ? $roundOrNull($site_power['active_power'] ?? null) : null,
        'daily_prod'    => $roundOrNull($site_daily['daily'] ?? null),
        'monthly_prod'  => $roundOrNull($site_monthly['monthly'] ?? null),
        'yearly_prod'   => $roundOrNull($site_yearly['yearly'] ?? null),
        'avg_irr'       => $roundOrNull($avg_irradiance['avg'] ?? null),
        'sun_hours'     => isset($sun_hours['minutes']) && $sun_hours['minutes'] !== null
            ? (int) $sun_hours['minutes']
            : null,
    ]);
} catch (PDOException $e) {
    error_log("get_site_card_data error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
