<?php
ob_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

$site_id  = intval($_POST['site_id']  ?? 0);
$end_date = trim($_POST['end_date']   ?? date('Y-m-d'));
$month    = (int)date('m', strtotime($end_date));

if (!$site_id) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$adm  = getDB('admin');
$stmt = $adm->prepare("SELECT db_name FROM tbl_site WHERE id = :id");
$stmt->execute([':id' => $site_id]);
$site = $stmt->fetch();
if (!$site) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$pdo = tryGetDB(ees_db_key($site['db_name']));
if (!$pdo) { ob_end_clean(); echo json_encode([]); exit; }

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

    // PDO does not allow the same named param twice — use :yr_label vs :yr_filter
    $curr_stmt = $pdo->prepare(
        "SELECT :yr_label as year,
                MAX(total_active_energy) - MIN(total_active_energy) as production
         FROM tbl_main_meter
         WHERE YEAR(date) = :yr_filter AND MONTH(date) = :month"
    );
    $curr_stmt->execute([':yr_label' => $current_year, ':yr_filter' => $current_year, ':month' => $month]);
    $current = $curr_stmt->fetch();

    $irr_stmt = $pdo->prepare(
        "SELECT SUM(insolation) as insolation FROM plant_irradiance
         WHERE YEAR(date) = :yr AND MONTH(date) = :month"
    );
    $irr_stmt->execute([':yr' => $current_year, ':month' => $month]);
    $curr_irr = $irr_stmt->fetch();

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
    echo json_encode(['status' => 'Err']);
}
