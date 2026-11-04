<?php
/**
 * Joli Bois — Bivocom GSM gateway webhook.
 *
 * Everything for this site arrives here (no ChirpStack main-meter path):
 *   janitza_power   (W)   -> plant_active_power   meter_id 100   (stored as kW)
 *   janitza_energy  (Wh)  -> tbl_main_meter + tbl_hourly_prod    (stored as kWh)
 *   fire_alarm      0/1   -> tbl_main_FAP
 *   lv_meter_1..6_*       -> tbl_sub_meters meter_id 1..6        (already kW / kWh)
 *   lv_meter_main_*       -> tbl_sub_meters meter_id 8 MAIN LV   (already kW / kWh)
 *   irradiance (W/m2), ambient_temp / panel_temp (tenths of °C) -> plant_irradiance
 *
 * "-" or a blank LV field means that meter is not wired. It is skipped.
 * Older key names (main_meter_*, meter1_*) are still accepted.
 *
 * Dashboard mapping:
 *   Active Power          <- latest plant_active_power today
 *   Today/Month/Year prod <- SUM(tbl_hourly_prod) WHERE meter_id >= 100
 *   KPI / hourly charts   <- plant_active_power + tbl_hourly_prod + plant_irradiance
 *   Fire Alarm Panel      <- latest tbl_main_FAP.status
 */

$json = file_get_contents('php://input');
require_once __DIR__ . '/../shared/bootstrap.php';
verifyWebhookRequest($json);

$timereal = date('Y-m-d H:i:s');
$log = __DIR__ . '/webhook_log.txt';
if (@filesize($log) > 2 * 1024 * 1024) { @rename($log, $log . '.old'); }
@file_put_contents($log, sprintf("[%s] %s\n", $timereal, $json), FILE_APPEND | LOCK_EX);

$pdo = tryGetDB('joli_bois');
if (!$pdo instanceof PDO) {
    $mapped  = isset($GLOBALS['_ees_db_map']['joli_bois']);
    $hasName = trim((string)($_ENV['JOLI_BOIS_DB_NAME'] ?? '')) !== '';
    $hasUser = trim((string)($_ENV['JOLI_BOIS_DB_USERNAME'] ?? '')) !== '';
    $mysqlError = (string)($GLOBALS['_ees_db_last_error']['joli_bois'] ?? '');
    @file_put_contents(
        $log,
        sprintf(
            "[%s] DB FAIL | mapped=%s | db_name_set=%s | db_user_set=%s | user=%s | db=%s | mysql=%s\n",
            $timereal,
            $mapped ? 'yes' : 'no',
            $hasName ? 'yes' : 'no',
            $hasUser ? 'yes' : 'no',
            (string)($_ENV['JOLI_BOIS_DB_USERNAME'] ?? ''),
            (string)($_ENV['JOLI_BOIS_DB_NAME'] ?? ''),
            $mysqlError !== '' ? $mysqlError : 'no PDO message'
        ),
        FILE_APPEND | LOCK_EX
    );
    http_response_code(500);
    echo 'Database connection error';
    exit;
}

$data = json_decode($json, true);

if (!is_array($data) || !isset($data['params']) || !is_array($data['params'])) { exit; }
$p = $data['params'];

if (function_exists('ees_audit_log_webhook')) {
    ees_audit_log_webhook('joli_bois', $data);
}

/** Main-meter reading closest to $boundary. */
function jb_main_closest(PDO $pdo, string $boundary): ?array
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

/** Close completed hours for MAIN METER (id 100) from tbl_main_meter. */
function jb_main_hourly_prod(PDO $pdo, string $timereal): void
{
    $meterId   = 100;
    $meterName = 'MAIN METER';

    $lastProd = $pdo->prepare('SELECT `datetime` FROM `tbl_hourly_prod` WHERE `meter_id` = ? ORDER BY `id` DESC LIMIT 1');
    $lastProd->execute([$meterId]);
    $lp = $lastProd->fetch();

    if ($lp && !empty($lp['datetime'])) {
        $startReading = jb_main_closest($pdo, $lp['datetime']);
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
        $endReading  = jb_main_closest($pdo, $boundaryStr);
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

/** Sub-meter reading closest to $boundary. */
function jb_closest_energy(PDO $pdo, int $meterId, string $boundary): ?array
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

function jb_first_energy(PDO $pdo, int $meterId): ?array
{
    $s = $pdo->prepare('SELECT `date`, `total_active_energy` AS energy FROM `tbl_sub_meters` WHERE `meter_id` = ? ORDER BY `date` ASC, `id` ASC LIMIT 1');
    $s->execute([$meterId]);
    return $s->fetch() ?: null;
}

function jb_sub_hourly_prod(PDO $pdo, int $meterId, string $meterName, string $timereal): void
{
    $lastProd = $pdo->prepare('SELECT `datetime` FROM `tbl_hourly_prod` WHERE `meter_id` = ? ORDER BY `id` DESC LIMIT 1');
    $lastProd->execute([$meterId]);
    $lp = $lastProd->fetch();

    if ($lp && !empty($lp['datetime'])) {
        $startReading = jb_closest_energy($pdo, $meterId, $lp['datetime']);
        $nextBoundary = strtotime($lp['datetime']) + 3600;
    } else {
        $first        = jb_first_energy($pdo, $meterId);
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
        $endReading  = jb_closest_energy($pdo, $meterId, $boundaryStr);
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

/** Numeric gateway value. "-" and blanks mean "no reading". */
function jb_num($raw): ?float
{
    if ($raw === null || is_bool($raw)) {
        return null;
    }
    if (is_string($raw)) {
        $raw = trim($raw);
        if ($raw === '' || $raw === '-' || !is_numeric($raw)) {
            return null;
        }
    } elseif (!is_numeric($raw)) {
        return null;
    }
    return (float)$raw;
}

/** First present numeric value among $keys. */
function jb_first_num(array $p, array $keys): ?float
{
    foreach ($keys as $key) {
        if (!array_key_exists($key, $p)) {
            continue;
        }
        $n = jb_num($p[$key]);
        if ($n !== null) {
            return $n;
        }
    }
    return null;
}

/** Bivocom temperatures arrive as tenths of a degree (446 = 44.6°C). */
function jb_celsius(?float $raw): float
{
    if ($raw === null) {
        return 0.0;
    }
    if (abs($raw) >= 100) {
        return round($raw / 10, 1);
    }
    return $raw;
}

/** Record Bivocom fire_alarm (0 = normal, 1 = alarm). */
function jb_record_fap(PDO $pdo, $raw, string $stamp): void
{
    if (is_string($raw)) {
        $status = in_array(strtolower(trim($raw)), ['on', '1', 'true', 'alarm'], true) ? 1 : 0;
    } else {
        $status = ((int)$raw !== 0) ? 1 : 0;
    }

    $devEui = 'bivocom';
    $last = $pdo->prepare(
        'SELECT `id`, `status`, `date` FROM `tbl_main_FAP` WHERE `dev_eui` = ? ORDER BY `id` DESC LIMIT 1'
    );
    $last->execute([$devEui]);
    $prev = $last->fetch();

    $insertRow = static function () use ($pdo, $stamp, $devEui, $status) {
        $pdo->prepare(
            'INSERT INTO `tbl_main_FAP` (`date`, `dev_eui`, `status`) VALUES (?, ?, ?)'
        )->execute([$stamp, $devEui, $status]);
    };

    if (!$prev || (int)$prev['status'] !== $status) {
        $insertRow();
        return;
    }

    $prevHour = date('Y-m-d H', strtotime($prev['date']));
    $nowHour  = date('Y-m-d H', strtotime($stamp));
    if ($prevHour !== $nowHour) {
        $insertRow();
    } else {
        $pdo->prepare('UPDATE `tbl_main_FAP` SET `date` = ? WHERE `id` = ?')
            ->execute([$stamp, $prev['id']]);
    }
}

try {
// ── Janitza main meter (W / Wh). Negative power is export and is kept. ─────
$mmPower  = jb_first_num($p, ['janitza_power', 'main_meter_power', 'mm_active_power', 'mm_power']);
$mmEnergy = jb_first_num($p, ['janitza_energy', 'main_meter_energy', 'mm_energy']);

if ($mmPower !== null) {
    $pdo->prepare(
        'INSERT INTO `plant_active_power`(`date`,`meter_id`,`meter_name`,`active_power`) VALUES (?,?,?,?)'
    )->execute([$timereal, 100, 'MAIN METER', $mmPower / 1000]);
}

if ($mmEnergy !== null && $mmEnergy > 0) {
    $pdo->prepare(
        'INSERT INTO `tbl_main_meter`(`date`,`total_active_energy`) VALUES (?,?)'
    )->execute([$timereal, round($mmEnergy / 1000, 5)]);
    jb_main_hourly_prod($pdo, $timereal);
}

// ── Fire alarm ──────────────────────────────────────────────────────────────
if (array_key_exists('fire_alarm', $p)) {
    jb_record_fap($pdo, $p['fire_alarm'], $timereal);
}

// ── LV meters 1..6, already in kW / kWh. Meter 7 kept for the old key names.
$jb_lv_insert = static function (PDO $pdo, int $meterId, string $name, ?float $power, ?float $energy, string $timereal): void {
    if ($power === null && $energy === null) {
        return;
    }
    $pdo->prepare(
        'INSERT INTO `tbl_sub_meters`(`date`,`meter_id`,`meter_name`,`active_power`,`power_factor`,`total_active_energy`)
         VALUES (?,?,?,?,?,?)'
    )->execute([$timereal, $meterId, $name, $power ?? 0.0, 0, $energy ?? 0.0]);

    if ($energy !== null && $energy > 0) {
        jb_sub_hourly_prod($pdo, $meterId, $name, $timereal);
    }
};

for ($i = 1; $i <= 7; $i++) {
    $energyKeys = ["meter{$i}_energy"];
    $powerKeys  = ["meter{$i}_power"];
    if ($i <= 6) {
        array_unshift($energyKeys, "lv_meter_{$i}_energy");
        array_unshift($powerKeys, "lv_meter_{$i}_power");
    }
    $jb_lv_insert($pdo, $i, "METER {$i}", jb_first_num($p, $powerKeys), jb_first_num($p, $energyKeys), $timereal);
}

$jb_lv_insert(
    $pdo,
    8,
    'MAIN LV',
    jb_first_num($p, ['lv_meter_main_power']),
    jb_first_num($p, ['lv_meter_main_energy']),
    $timereal
);

// ── Weather. Every push is stored. Insolation follows the real gap (5s now, 300s later).
if (isset($p['irradiance']) || isset($p['ambient_temp']) || isset($p['panel_temp'])) {
    $lastWx = $pdo->query('SELECT `date` FROM `plant_irradiance` ORDER BY `id` DESC LIMIT 1')->fetch();
    $gap = $lastWx && !empty($lastWx['date']) ? (strtotime($timereal) - strtotime($lastWx['date'])) : 300;
    if ($gap < 0)   { $gap = 0; }
    if ($gap > 300) { $gap = 300; }
    $irradiance = jb_num($p['irradiance'] ?? null) ?? 0.0;
    $insolation = ($irradiance / 1000) * ($gap / 3600);
    $pdo->prepare(
        'INSERT INTO `plant_irradiance`(`date`,`irradiance`,`insolation`,`ambient_temp`,`panel_temp`) VALUES (?,?,?,?,?)'
    )->execute([
        $timereal,
        $irradiance,
        round($insolation, 5),
        jb_celsius(jb_num($p['ambient_temp'] ?? null)),
        jb_celsius(jb_num($p['panel_temp'] ?? null)),
    ]);
}
} catch (Throwable $e) {
    @file_put_contents(
        $log,
        sprintf("[%s] SAVE FAIL | %s\n", $timereal, $e->getMessage()),
        FILE_APPEND | LOCK_EX
    );
    error_log('joli_bois webhook: ' . $e->getMessage());
    http_response_code(500);
    echo 'Save failed';
    exit;
}
