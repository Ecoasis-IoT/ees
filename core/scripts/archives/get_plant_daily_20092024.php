<?php

error_reporting(E_ALL);
ini_set('display_errors', '0');

$site_id    = intval($_POST['site_id']    ?? 0);
$start_date = $_POST['start_date'] ?? '';
$end_date   = $_POST['end_date']   ?? '';

if (!$site_id || !$start_date || !$end_date) {
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/../../../config.php';

// Resolve site config from admin DB
$admin = getDB('admin');
$stmt  = $admin->prepare(
    "SELECT db_name, capacity, main_meter FROM tbl_site WHERE id = ? LIMIT 1"
);
$stmt->execute([$site_id]);
$res = $stmt->fetch();

if (!$res) {
    echo json_encode([]);
    exit;
}

$site_key      = $res['db_name'];   // matches getDB() key e.g. 'factory', 'phoenix'
$capacity      = (float)$res['capacity'];
$main_meter_id = (int)$res['main_meter'];

$site_pdo = getDB($site_key);

// Production per day (from hourly production records)
$stmt_energy = $site_pdo->prepare(
    "SELECT
         DATE(DATETIME) AS `date`,
         ROUND(SUM(production), 2) AS `production`
     FROM `tbl_hourly_prod`
     WHERE
         DATE(DATETIME) >= DATE(:start)
         AND DATE(DATETIME) <= DATE(:end)
         AND meter_id = :meter_id
     GROUP BY DATE(DATETIME)"
);
$stmt_energy->execute([
    ':start'    => $start_date,
    ':end'      => $end_date,
    ':meter_id' => $main_meter_id,
]);
$energy = $stmt_energy->fetchAll(PDO::FETCH_ASSOC);

// Insolation per day
$stmt_irr = $site_pdo->prepare(
    "SELECT
         DATE(date) AS `date`,
         ROUND(SUM(insolation), 2) AS `insolation`
     FROM `plant_irradiance`
     WHERE DATE(date) >= DATE(:start) AND DATE(date) <= DATE(:end)
     GROUP BY DATE(date)"
);
$stmt_irr->execute([':start' => $start_date, ':end' => $end_date]);
$irradiance = $stmt_irr->fetchAll(PDO::FETCH_ASSOC);

// Merge by date
$length = max(count($energy), count($irradiance));
$data   = [];

for ($i = 0; $i < $length; $i++) {
    if (!isset($irradiance[$i])) break;
    $the_date = $irradiance[$i]['date'];

    for ($j = 0; $j < count($energy); $j++) {
        if ($energy[$j]['date'] === $the_date) {
            $prod      = (float)$energy[$j]['production'];
            $insolation = (float)$irradiance[$i]['insolation'];
            $pr = ($insolation > 0 && $capacity > 0)
                ? round(($prod / ($insolation * $capacity)) * 100, 0)
                : 0;

            $data[] = [
                'date'       => $the_date,
                'prod'       => round($prod, 2),
                'insolation' => $insolation,
                'pr'         => $pr,
            ];
            break;
        }
    }
}

echo json_encode($data);
