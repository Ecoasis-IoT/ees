<?php

$hex = bin2hex(base64_decode($dev_data));

$stmt = $pdo->prepare(
    'SELECT `id`, `meter_name` FROM tbl_meters WHERE `address` >= 100 AND `controller_eui` = ?'
);
$stmt->execute([$dev_eui]);
$meter = $stmt->fetch();

$total_active_energy = (hexdec(substr($hex, 6, 16))) / 1000;

$hist = $pdo->prepare(
    'SELECT MAX(`date`) AS start_date, `total_active_energy` AS start_energy
     FROM `tbl_main_meter`
     WHERE `date` = (SELECT MAX(`date`) FROM tbl_main_meter WHERE `meter_id` = ?)
       AND `meter_id` = ?'
);
$hist->execute([$meter['id'], $meter['id']]);
$historical   = $hist->fetch();
$start_date   = $historical['start_date']   ?? '';
$start_energy = (float)($historical['start_energy'] ?? 0);

if ($total_active_energy == 0) {
    $total_active_energy = $start_energy;
}

if ($start_date !== '') {
    $production = bcsub((string)$total_active_energy, (string)$start_energy, 2);

    $pdo->prepare(
        'INSERT INTO `tbl_hourly_prod`(`meter_id`,`datetime`,`meter_name`,`starting_datetime`,`ending_datetime`,`production`)
         VALUES (?,?,?,?,?,?)'
    )->execute([$meter['id'], $round_date, $meter['meter_name'], $start_date, $timenow, $production]);
}

$pdo->prepare(
    'INSERT INTO `tbl_main_meter`(`date`,`meter_id`,`meter_name`,`total_active_energy`) VALUES (?,?,?,?)'
)->execute([$round_date, $meter['id'], $meter['meter_name'], round($total_active_energy, 5)]);
