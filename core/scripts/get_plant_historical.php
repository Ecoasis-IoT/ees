<?php
ob_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

$site_id  = intval($_POST['site_id']  ?? 0);
$end_date = trim($_POST['end_date']   ?? date('Y-m-d'));
$month    = (int)date('m', strtotime($end_date));

if (!$site_id) {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'code' => 'bad_params', 'message' => 'Missing site.']);
    exit;
}

$adm  = getDB('admin');
$stmt = $adm->prepare("SELECT db_name, main_meter FROM tbl_site WHERE id = :id");
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
    error_log('get_plant_historical: tryGetDB failed for key ' . $dbKey . ' (site db_name=' . ($site['db_name'] ?? '') . ')');
    ob_end_clean();
    echo json_encode([
        'status'  => 'Err',
        'code'    => 'db_unavailable',
        'message' => 'Could not connect to this plant database. Check .env credentials for the site DB (see server log for key).',
    ]);
    exit;
}

$history      = [];
$current_year = (int)date('Y', strtotime($end_date));

try {
    $hist_stmt = $pdo->prepare(
        "SELECT YEAR(date) as year, production, insolation
         FROM tbl_historical WHERE MONTH(date) = :month"
    );
    $hist_stmt->execute([':month' => $month]);
    $raw = $hist_stmt->fetchAll();
    $history = array_map(function ($r) {
        return [
            'year'       => (string)$r['year'],
            'production' => (float)$r['production'],
            'insolation' => (float)$r['insolation'],
        ];
    }, $raw);
} catch (PDOException $e) {
    error_log('get_plant_historical tbl_historical: ' . $e->getMessage());
}

$current_production = 0.0;
$main_meter         = (int)($site['main_meter'] ?? 0);

try {
    if ($main_meter > 0) {
        $curr_stmt = $pdo->prepare(
            'SELECT MAX(total_active_energy) - MIN(total_active_energy) AS production
             FROM tbl_main_meter
             WHERE YEAR(date) = :yr_filter AND MONTH(date) = :month AND meter_id = :meter'
        );
        $curr_stmt->execute([
            ':yr_filter' => $current_year,
            ':month'     => $month,
            ':meter'     => $main_meter,
        ]);
    } else {
        $curr_stmt = $pdo->prepare(
            'SELECT MAX(total_active_energy) - MIN(total_active_energy) AS production
             FROM tbl_main_meter
             WHERE YEAR(date) = :yr_filter AND MONTH(date) = :month'
        );
        $curr_stmt->execute([':yr_filter' => $current_year, ':month' => $month]);
    }
    $current = $curr_stmt->fetch(PDO::FETCH_ASSOC);
    $current_production = round((float)($current['production'] ?? 0), 2);
} catch (PDOException $e) {
    error_log('get_plant_historical tbl_main_meter: ' . $e->getMessage());
    try {
        $meter_floor = $main_meter > 0 ? $main_meter : 100;
        $fallback = $pdo->prepare(
            "SELECT ROUND(SUM(production), 2) AS production
             FROM tbl_hourly_prod
             WHERE YEAR(datetime) = :yr AND MONTH(datetime) = :month AND meter_id >= :meter"
        );
        $fallback->execute([
            ':yr'     => $current_year,
            ':month'  => $month,
            ':meter'  => $meter_floor,
        ]);
        $row = $fallback->fetch(PDO::FETCH_ASSOC);
        $current_production = round((float)($row['production'] ?? 0), 2);
    } catch (PDOException $e2) {
        error_log('get_plant_historical tbl_hourly_prod fallback: ' . $e2->getMessage());
    }
}

$current_insolation = 0.0;
try {
    $irr_stmt = $pdo->prepare(
        "SELECT SUM(insolation) as insolation FROM plant_irradiance
         WHERE YEAR(date) = :yr AND MONTH(date) = :month"
    );
    $irr_stmt->execute([':yr' => $current_year, ':month' => $month]);
    $curr_irr = $irr_stmt->fetch(PDO::FETCH_ASSOC);
    $current_insolation = round((float)($curr_irr['insolation'] ?? 0), 2);
} catch (PDOException $e) {
    error_log('get_plant_historical plant_irradiance: ' . $e->getMessage());
}

$history[] = [
    'year'       => (string)$current_year,
    'production' => $current_production,
    'insolation' => $current_insolation,
];

ob_end_clean();
require_once __DIR__ . '/../common/audit_logging.php';
ees_audit_log_report('plant_historical', ['site_id' => $site_id]);
echo json_encode($history);
