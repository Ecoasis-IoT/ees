<?php
/**
 * Moka City — inverter / main-meter gateway webhook.
 *
 * Source split for Moka City:
 *   - ChirpStack (UC300)  -> WEATHER (irradiance / ambient / panel) + FIRE panel.
 *   - This gateway        -> the 6 INVERTERS (reports) + the MAIN METER (dashboards).
 *
 * Payload (params):
 *   inv1..6_energy / inv1..6_active_power -> tbl_sub_meters (meter_id 1..6) + tbl_hourly_prod
 *   mm_energy / mm_active_power (or mm_power)
 *                                          -> tbl_main_meter + plant_active_power + tbl_hourly_prod (meter_id 100)
 *
 * Meter-id convention (same as every other plant):
 *   plant production = SUM(production) WHERE meter_id >= 100 (the MAIN METER).
 *   The 6 inverters live BELOW 100 (ids 1..6) so they show up in reports but never
 *   inflate the dashboard production total (which would double-count the main meter).
 *
 * NOTE: the main meter (mm_*) is not arriving yet — the gateway will start sending it
 *       later. The handler below is already wired so it "just works" the moment those
 *       fields appear in the payload; nothing else needs to change.
 *
 * Storage rules (mirror the rest of the system):
 *   tbl_sub_meters     : every push, real time, meter_id = inverter number (1..6).
 *   plant_active_power : main meter, every push, real time (meter_id 100).
 *   tbl_main_meter     : main meter cumulative energy, every push, real time.
 *   tbl_hourly_prod    : one row per clock hour per meter (closest reading to each
 *                        top-of-hour boundary; negatives clamped to 0).
 */

$json = file_get_contents('php://input');
require_once __DIR__ . '/../shared/bootstrap.php';
verifyWebhookRequest($json);
$pdo = getDB('moka_city');

$timereal = date('Y-m-d H:i:s');   // server clock — same basis as every other callback
$data     = json_decode($json, true);

// Lightweight receipt log (self-rotates at ~2MB).
$log = __DIR__ . '/webhook_log.txt';
if (@filesize($log) > 2 * 1024 * 1024) { @rename($log, $log . '.old'); }
@file_put_contents($log, sprintf("[%s] %s\n", $timereal, $json), FILE_APPEND | LOCK_EX);

if (!is_array($data) || !isset($data['params']) || !is_array($data['params'])) { exit; }
$p = $data['params'];

if (function_exists('ees_audit_log_webhook')) {
    ees_audit_log_webhook('moka_city', $data);
}

// ─────────────────────────────────────────────────────────────────────────────
// Sub-meter (inverter) helpers — hourly production from tbl_sub_meters
// ─────────────────────────────────────────────────────────────────────────────

/** Sub-meter reading (date + energy) closest to $boundary. */
function mc_closest_energy(PDO $pdo, int $meterId, string $boundary): ?array
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
function mc_first_energy(PDO $pdo, int $meterId): ?array
{
    $s = $pdo->prepare('SELECT `date`, `total_active_energy` AS energy FROM `tbl_sub_meters` WHERE `meter_id` = ? ORDER BY `date` ASC, `id` ASC LIMIT 1');
    $s->execute([$meterId]);
    return $s->fetch() ?: null;
}

/** Close any whole hours that have elapsed for a sub-meter (inverter). */
function mc_sub_hourly_prod(PDO $pdo, int $meterId, string $meterName, string $timereal): void
{
    $lastProd = $pdo->prepare('SELECT `datetime` FROM `tbl_hourly_prod` WHERE `meter_id` = ? ORDER BY `id` DESC LIMIT 1');
    $lastProd->execute([$meterId]);
    $lp = $lastProd->fetch();

    if ($lp && !empty($lp['datetime'])) {
        $startReading = mc_closest_energy($pdo, $meterId, $lp['datetime']);
        $nextBoundary = strtotime($lp['datetime']) + 3600;
    } else {
        $first        = mc_first_energy($pdo, $meterId);
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
        $endReading  = mc_closest_energy($pdo, $meterId, $boundaryStr);
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

// ─────────────────────────────────────────────────────────────────────────────
// Main-meter helpers — hourly production from tbl_main_meter (meter_id 100)
// ─────────────────────────────────────────────────────────────────────────────

/** Main-meter reading (date + energy) closest to $boundary. */
function mc_main_closest(PDO $pdo, string $boundary): ?array
{
    $bStmt = $pdo->prepare('SELECT `date`, `total_active_energy` AS energy FROM `tbl_main_meter` WHERE `date` <= ? ORDER BY `date` DESC, `id` DESC LIMIT 1');
    $bStmt->execute([$boundary]);
    $aStmt = $pdo->prepare('SELECT `date`, `total_active_energy` AS energy FROM `tbl_main_meter` WHERE `date` > ?  ORDER BY `date` ASC,  `id` ASC  LIMIT 1');
    $aStmt->execute([$boundary]);

    $b = $bStmt->fetch();
    $a = $aStmt->fetch();

    if (!$b && !$a) return null;
    if (!$b) return $a;
    if (!$a) return $b;

    $bDiff = abs(strtotime($b['date']) - strtotime($boundary));
    $aDiff = abs(strtotime($a['date']) - strtotime($boundary));
    return ($aDiff < $bDiff) ? $a : $b;
}

/** Close any whole hours that have elapsed for the MAIN METER (meter_id 100). */
function mc_main_hourly_prod(PDO $pdo, string $timereal): void
{
    $meterId   = 100;
    $meterName = 'MAIN METER';

    // Scoped to this meter so it never picks up the inverter (1..6) rows.
    $lastProd = $pdo->prepare('SELECT `datetime` FROM `tbl_hourly_prod` WHERE `meter_id` = ? ORDER BY `id` DESC LIMIT 1');
    $lastProd->execute([$meterId]);
    $lp = $lastProd->fetch();

    if ($lp && !empty($lp['datetime'])) {
        $startReading = mc_main_closest($pdo, $lp['datetime']);
        $nextBoundary = strtotime($lp['datetime']) + 3600;
    } else {
        $first = $pdo->query('SELECT `date`, `total_active_energy` AS energy FROM `tbl_main_meter` ORDER BY `date` ASC, `id` ASC LIMIT 1')->fetch();
        $startReading = $first ?: null;
        $nextBoundary = $first
            ? strtotime(date('Y-m-d H:00:00', strtotime($first['date']))) + 3600
            : PHP_INT_MAX;
    }

    $nowTs = strtotime($timereal);
    $guard = 0;

    while ($startReading && $nowTs >= $nextBoundary && $guard < 50) {
        $guard++;
        $boundaryStr = date('Y-m-d H:i:s', $nextBoundary);
        $endReading  = mc_main_closest($pdo, $boundaryStr);
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

// ── Inverters 1..6 (reports — below 100, never summed into dashboard production) ──
$invPowerSum  = 0.0;   // running total of inverter active power (kW)
$haveInverter = false; // did this push carry any inverter data?

for ($i = 1; $i <= 6; $i++) {
    $eKey = "inv{$i}_energy";
    $wKey = "inv{$i}_active_power";
    if (!isset($p[$eKey]) && !isset($p[$wKey])) { continue; }

    $power  = isset($p[$wKey]) ? (float)$p[$wKey] : 0.0;
    $energy = isset($p[$eKey]) ? (float)$p[$eKey] : 0.0;
    $name   = "INVERTER {$i}";

    $invPowerSum += $power;
    $haveInverter = true;

    $pdo->prepare(
        'INSERT INTO `tbl_sub_meters`(`date`,`meter_id`,`meter_name`,`active_power`,`power_factor`,`total_active_energy`)
         VALUES (?,?,?,?,?,?)'
    )->execute([$timereal, $i, $name, $power, 0, $energy]);

    if ($energy > 0) {
        mc_sub_hourly_prod($pdo, $i, $name, $timereal);
    }
}

// ── Main meter (dashboards) — meter_id 100 ───────────────────────────────────
// The real main meter (mm_*) is not arriving yet. As a TEMPORARY stopgap (test
// only, confirmed acceptable for ACTIVE POWER): the dashboard main active power
// is the SUM of the inverters' active power. When the real mm_active_power starts
// arriving it takes precedence (so there is no double source / double count).
//
// PRODUCTION is intentionally NOT derived from the inverters here yet — pending
// confirmation of the correct method. tbl_main_meter / hourly production (id 100)
// stay driven only by the real mm_energy when it arrives.
$mmPower  = $p['mm_active_power'] ?? ($p['mm_power'] ?? null);
$mmEnergy = $p['mm_energy'] ?? null;

$mainPower = null;
if ($mmPower !== null) {
    $mainPower = (float)$mmPower;          // real main meter active power (preferred)
} elseif ($haveInverter) {
    $mainPower = round($invPowerSum, 3);   // stopgap: sum of inverter active power
}

// Active power — every push, real time (feeds the dashboard active-power chart).
if ($mainPower !== null) {
    $pdo->prepare(
        'INSERT INTO `plant_active_power`(`date`,`meter_id`,`meter_name`,`active_power`) VALUES (?,?,?,?)'
    )->execute([$timereal, 100, 'MAIN METER', $mainPower]);
}

// Energy + hourly production — ONLY from the real main meter (mm_energy).
// A cumulative meter never legitimately reports 0; skip bad reads.
if ($mmEnergy !== null && (float)$mmEnergy > 0) {
    $pdo->prepare(
        'INSERT INTO `tbl_main_meter`(`date`,`total_active_energy`) VALUES (?,?)'
    )->execute([$timereal, round((float)$mmEnergy, 5)]);

    mc_main_hourly_prod($pdo, $timereal);
}
