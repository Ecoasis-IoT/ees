<?php
/**
 * Case Noyal — weather-station gateway webhook.
 *
 * Source split for Case Noyal:
 *   - ChirpStack (switchgear UC300)  -> MAIN METER: active power, energy, production.
 *   - This gateway                   -> WEATHER data + the LV sub-meters.
 * So this script deliberately does NOT touch the main meter (mm_*) — ChirpStack
 * owns it. Writing it here too would double-count and corrupt hourly production.
 *
 * Payload (params):
 *   irradiance / ambient_temp / panel_temp   -> plant_irradiance
 *   mm_energy / mm_power                      -> tbl_sub_meters  (LV main, meter_id 8)
 *   meter1..7_energy / meter1..7_power        -> tbl_sub_meters  (LV units, meter_id 1..7)
 *
 * Meter-id convention (verified against the other plants):
 *   plant production = SUM(production) WHERE meter_id >= main_meter (default 100).
 *   The Janitza dashboard main meter is id 100 (written by the ChirpStack path).
 *   The LV main + sub units come from this gateway and are for REPORTS only, so
 *   they live BELOW 100 (LV main = 8, LV sub units = 1..7) and never inflate the
 *   dashboard production total.
 *
 * Storage rules (mirror the rest of the system):
 *   tbl_sub_meters   : every push, real time, meter_id = LV unit number.
 *   plant_irradiance : every push, real time.
 *   tbl_hourly_prod  : one row per clock hour per sub-meter (closest reading to
 *                      each top-of-hour boundary; negatives clamped to 0).
 */

$json = file_get_contents('php://input');
require_once __DIR__ . '/../shared/bootstrap.php';
verifyWebhookRequest($json);
$pdo = getDB('case_noyal');

$timereal = date('Y-m-d H:i:s');   // server clock — same basis as every other callback
$data     = json_decode($json, true);

// Lightweight receipt log (self-rotates at ~2MB).
$log = __DIR__ . '/webhook_log.txt';
if (@filesize($log) > 2 * 1024 * 1024) { @rename($log, $log . '.old'); }
@file_put_contents($log, sprintf("[%s] %s\n", $timereal, $json), FILE_APPEND | LOCK_EX);

if (!is_array($data) || !isset($data['params']) || !is_array($data['params'])) { exit; }
$p = $data['params'];

if (function_exists('ees_audit_log_webhook')) {
    ees_audit_log_webhook('case_noyal', $data);
}

/** Sub-meter reading (date + energy) closest to $boundary. */
function cn_closest_energy(PDO $pdo, int $meterId, string $boundary): ?array
{
    $bStmt = $pdo->prepare('SELECT `date`, `total_active_energy` AS energy FROM `tbl_sub_meters` WHERE `meter_id` = ? AND `date` <= ? ORDER BY `date` DESC, `id` DESC LIMIT 1');
    $bStmt->execute([$meterId, $boundary]);
    $aStmt = $pdo->prepare('SELECT `date`, `total_active_energy` AS energy FROM `tbl_sub_meters` WHERE `meter_id` = ? AND `date` > ?  ORDER BY `date` ASC,  `id` ASC  LIMIT 1');
    $aStmt->execute([$meterId, $boundary]);

    $b = $bStmt->fetch();
    $a = $aStmt->fetch();

    if (!$b && !$a) return null;
    if (!$b) return $a;
    if (!$a) return $b;

    $bDiff = abs(strtotime($b['date']) - strtotime($boundary));
    $aDiff = abs(strtotime($a['date']) - strtotime($boundary));
    return ($aDiff < $bDiff) ? $a : $b;
}

/** Earliest reading for a sub-meter. */
function cn_first_energy(PDO $pdo, int $meterId): ?array
{
    $s = $pdo->prepare('SELECT `date`, `total_active_energy` AS energy FROM `tbl_sub_meters` WHERE `meter_id` = ? ORDER BY `date` ASC, `id` ASC LIMIT 1');
    $s->execute([$meterId]);
    return $s->fetch() ?: null;
}

/** Close any whole hours that have elapsed for a sub-meter. */
function cn_hourly_prod(PDO $pdo, int $meterId, string $meterName, string $timereal): void
{
    $lastProd = $pdo->prepare('SELECT `datetime` FROM `tbl_hourly_prod` WHERE `meter_id` = ? ORDER BY `id` DESC LIMIT 1');
    $lastProd->execute([$meterId]);
    $lp = $lastProd->fetch();

    if ($lp && !empty($lp['datetime'])) {
        $startReading = cn_closest_energy($pdo, $meterId, $lp['datetime']);
        $nextBoundary = strtotime($lp['datetime']) + 3600;
    } else {
        $first        = cn_first_energy($pdo, $meterId);
        $startReading = $first ?: null;
        $nextBoundary = $first
            ? strtotime(date('Y-m-d H:00:00', strtotime($first['date']))) + 3600
            : PHP_INT_MAX;
    }

    $nowTs = strtotime($timereal);
    $guard = 0; // never let a big gap run away inside one request

    while ($startReading && $nowTs >= $nextBoundary && $guard < 50) {
        $guard++;
        $boundaryStr = date('Y-m-d H:i:s', $nextBoundary);
        $endReading  = cn_closest_energy($pdo, $meterId, $boundaryStr);
        if (!$endReading) break;

        $production = bcsub((string)$endReading['energy'], (string)$startReading['energy'], 2);
        if ((float)$production < 0) { $production = '0'; }

        $pdo->prepare(
            'INSERT INTO `tbl_hourly_prod`(`meter_id`,`datetime`,`meter_name`,`starting_datetime`,`ending_datetime`,`production`)
             VALUES (?,?,?,?,?,?)'
        )->execute([$meterId, $boundaryStr, $meterName, $startReading['date'], $endReading['date'], $production]);

        $startReading  = $endReading;
        $nextBoundary += 3600;
    }
}

// ── LV sub-meters 1..7 ────────────────────────────────────────────────────────
for ($i = 1; $i <= 7; $i++) {
    $eKey = "meter{$i}_energy";
    $wKey = "meter{$i}_power";
    if (!isset($p[$eKey]) && !isset($p[$wKey])) { continue; }

    $power  = isset($p[$wKey]) ? (float)$p[$wKey] : 0.0;
    $energy = isset($p[$eKey]) ? (float)$p[$eKey] : 0.0;
    $name   = "METER {$i}";

    $pdo->prepare(
        'INSERT INTO `tbl_sub_meters`(`date`,`meter_id`,`meter_name`,`active_power`,`power_factor`,`total_active_energy`)
         VALUES (?,?,?,?,?,?)'
    )->execute([$timereal, $i, $name, $power, 0, $energy]);

    if ($energy > 0) {
        cn_hourly_prod($pdo, $i, $name, $timereal);
    }
}

// ── LV main (parent of the 7 sub-meters) ──────────────────────────────────────
// Stored as meter_id 8 (below 100) so it shows in reports without being summed
// into the Janitza dashboard production. NOT the same meter as the ChirpStack
// main meter (id 100), so there is no double-count.
if (isset($p['mm_power']) || isset($p['mm_energy'])) {
    $power  = isset($p['mm_power'])  ? (float)$p['mm_power']  : 0.0;
    $energy = isset($p['mm_energy']) ? (float)$p['mm_energy'] : 0.0;

    $pdo->prepare(
        'INSERT INTO `tbl_sub_meters`(`date`,`meter_id`,`meter_name`,`active_power`,`power_factor`,`total_active_energy`)
         VALUES (?,?,?,?,?,?)'
    )->execute([$timereal, 8, 'MAIN LV', $power, 0, $energy]);

    if ($energy > 0) {
        cn_hourly_prod($pdo, 8, 'MAIN LV', $timereal);
    }
}

// ── Weather sensor ────────────────────────────────────────────────────────────
if (isset($p['irradiance']) || isset($p['ambient_temp']) || isset($p['panel_temp'])) {
    $irradiance   = isset($p['irradiance'])   ? (float)$p['irradiance']   : 0.0;
    $ambient_temp = isset($p['ambient_temp']) ? (float)$p['ambient_temp'] : 0.0;
    $panel_temp   = isset($p['panel_temp'])   ? (float)$p['panel_temp']   : 0.0;
    $insolation   = ($irradiance / 1000) * (5 / 60); // kWh/m^2 over a ~5-min reading

    $pdo->prepare(
        'INSERT INTO `plant_irradiance`(`date`,`irradiance`,`insolation`,`ambient_temp`,`panel_temp`) VALUES (?,?,?,?,?)'
    )->execute([$timereal, $irradiance, round($insolation, 5), $ambient_temp, $panel_temp]);
}
