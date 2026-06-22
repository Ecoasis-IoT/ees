<?php
$json = file_get_contents('php://input');
require_once __DIR__ . '/../shared/bootstrap.php';
verifyWebhookRequest($json);
$pdo = getDB('moka_city');

$timenow  = date('y-m-d H:i');     // legacy minute precision (used by the fPort 123 path)
$timereal = date('Y-m-d H:i:s');   // actual reading time (with seconds) for stored data
$data     = json_decode($json, true);

if (!is_array($data) || !array_key_exists('data', $data)) { exit; }

ees_audit_log_webhook('moka_city', $data);

// Lightweight receipt log so we can confirm data is arriving from prod (ees -> testees).
// Self-rotates at ~2MB. Safe to delete the file anytime.
$ees_fap_log = __DIR__ . '/log.txt';
if (@filesize($ees_fap_log) > 2 * 1024 * 1024) { @rename($ees_fap_log, $ees_fap_log . '.old'); }
@file_put_contents(
    $ees_fap_log,
    sprintf("[%s] RECEIVED | fPort=%s | %s\n", date('Y-m-d H:i:s'), $data['fPort'] ?? 'n/a', $json),
    FILE_APPEND | LOCK_EX
);

$fPort  = $data['fPort'] ?? null;
$object = is_array($data['object'] ?? null) ? $data['object'] : [];

// Timestamp rounded to the nearest hour (used for energy / production)
$ts   = strtotime($timenow);
$mins = $ts % 3600;
$ts  -= $mins;
if ($mins > 1800) $ts += 3600;
$round_date = date('Y-m-d H:i:s', $ts);

// Fire Alarm Panel (gpio_in_1) — record 24/7, independent of the solar window
if (array_key_exists('gpio_in_1', $object)) {
    include 'fap_decoder.php';
}

if ($fPort == 85) {
    // Switchgear Controller UC300: active power + energy in the decoded object
    $time  = date('H:i:s');
    $start = '04:55:00';
    $end   = '20:15:00';

    if ($time >= $start && $time <= $end) {
        include 'uc300_decoder.php';
    }

} elseif ($fPort == 123) {
    // Legacy energy-via-hex path (kept for backward compatibility)
    $dev_data = $data['data'] ?? '';
    include 'plant_energy.php';
}
