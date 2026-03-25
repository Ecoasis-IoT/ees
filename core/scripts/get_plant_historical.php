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
$stmt = $adm->prepare("SELECT db_name FROM tbl_site WHERE id = :id");
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

try {
    $hist_stmt = $pdo->prepare(
        "SELECT YEAR(date) as year, production, insolation
         FROM tbl_historical WHERE MONTH(date) = :month"
    );
    $hist_stmt->execute([':month' => $month]);
    $raw     = $hist_stmt->fetchAll();
    $history = array_map(function($r) {
        return [
            'year'       => (string)$r['year'],
            'production' => (float)$r['production'],
            'insolation' => (float)$r['insolation'],
        ];
    }, $raw);

    $current_year = (int)date('Y');

    // Literal year in SELECT (int) — avoids driver issues with bound params in the SELECT list
    $curr_stmt = $pdo->prepare(
        'SELECT ' . $current_year . ' AS year,
                MAX(total_active_energy) - MIN(total_active_energy) AS production
         FROM tbl_main_meter
         WHERE YEAR(date) = :yr_filter AND MONTH(date) = :month'
    );
    $curr_stmt->execute([':yr_filter' => $current_year, ':month' => $month]);
    $current = $curr_stmt->fetch(PDO::FETCH_ASSOC);

    $irr_stmt = $pdo->prepare(
        "SELECT SUM(insolation) as insolation FROM plant_irradiance
         WHERE YEAR(date) = :yr AND MONTH(date) = :month"
    );
    $irr_stmt->execute([':yr' => $current_year, ':month' => $month]);
    $curr_irr = $irr_stmt->fetch(PDO::FETCH_ASSOC);

    $history[] = [
        'year'       => (string)$current_year,
        'production' => round((float)($current['production'] ?? 0), 2),
        'insolation' => round((float)($curr_irr['insolation'] ?? 0), 2),
    ];

    ob_end_clean();
    echo json_encode($history);
} catch (PDOException $e) {
    error_log("get_plant_historical error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode([
        'status'  => 'Err',
        'code'    => 'sql_error',
        'message' => 'Data query failed. See server log for details.',
    ]);
}
