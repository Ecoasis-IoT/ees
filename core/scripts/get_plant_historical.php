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

$current_year = (int)date('Y', strtotime($end_date));
$main_meter   = (int)($site['main_meter'] ?? 0);
$floor        = $main_meter > 0 ? $main_meter : 100;

// year => ['production' => float, 'insolation' => float]
$byYear = [];
$seeded = []; // years coming from tbl_historical (authoritative; not overwritten by live)

// 1) Seeded historical years (data from before the system was collecting live,
//    e.g. Phoenix 2018..2025). Only some plants have this table populated.
try {
    $hist_stmt = $pdo->prepare(
        "SELECT YEAR(date) AS year, production, insolation
         FROM tbl_historical WHERE MONTH(date) = :month"
    );
    $hist_stmt->execute([':month' => $month]);
    foreach ($hist_stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $yr = (int)$r['year'];
        $byYear[$yr] = [
            'production' => (float)$r['production'],
            'insolation' => (float)$r['insolation'],
        ];
        $seeded[$yr] = true;
    }
} catch (PDOException $e) {
    error_log('get_plant_historical tbl_historical: ' . $e->getMessage());
}

// 2) Live production per year for this month — same source the dashboards use
//    (tbl_hourly_prod, meter_id >= main meter). This is what surfaces 2025/2026
//    for plants that have no seeded tbl_historical.
try {
    $prod_stmt = $pdo->prepare(
        "SELECT YEAR(datetime) AS year, ROUND(SUM(production), 2) AS production
         FROM tbl_hourly_prod
         WHERE MONTH(datetime) = :month AND meter_id >= :floor
         GROUP BY YEAR(datetime)"
    );
    $prod_stmt->execute([':month' => $month, ':floor' => $floor]);
    foreach ($prod_stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $yr = (int)$r['year'];
        if (isset($seeded[$yr])) continue; // keep the seeded value for overlapping years
        if (!isset($byYear[$yr])) $byYear[$yr] = ['production' => 0.0, 'insolation' => 0.0];
        $byYear[$yr]['production'] = (float)$r['production'];
    }
} catch (PDOException $e) {
    error_log('get_plant_historical tbl_hourly_prod: ' . $e->getMessage());
}

// 3) Live insolation per year for this month (non-seeded years only)
try {
    $irr_stmt = $pdo->prepare(
        "SELECT YEAR(date) AS year, ROUND(SUM(insolation), 2) AS insolation
         FROM plant_irradiance
         WHERE MONTH(date) = :month
         GROUP BY YEAR(date)"
    );
    $irr_stmt->execute([':month' => $month]);
    foreach ($irr_stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $yr = (int)$r['year'];
        if (isset($seeded[$yr])) continue;
        if (!isset($byYear[$yr])) $byYear[$yr] = ['production' => 0.0, 'insolation' => 0.0];
        $byYear[$yr]['insolation'] = (float)$r['insolation'];
    }
} catch (PDOException $e) {
    error_log('get_plant_historical plant_irradiance: ' . $e->getMessage());
}

// Always show the current year, even if it has no data yet
if (!isset($byYear[$current_year])) {
    $byYear[$current_year] = ['production' => 0.0, 'insolation' => 0.0];
}

ksort($byYear);

$history = [];
foreach ($byYear as $yr => $vals) {
    $history[] = [
        'year'       => (string)$yr,
        'production' => round($vals['production'], 2),
        'insolation' => round($vals['insolation'], 2),
    ];
}

ob_end_clean();
require_once __DIR__ . '/../common/audit_logging.php';
ees_audit_log_report('plant_historical', ['site_id' => $site_id]);
echo json_encode($history);
