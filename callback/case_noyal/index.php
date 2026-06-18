<?php
$json = file_get_contents('php://input');
require_once __DIR__ . '/../shared/bootstrap.php';
verifyWebhookRequest($json);
$pdo = getDB('case_noyal');

$timenow = date('y-m-d H:i');
$data    = json_decode($json, true);

if (!is_array($data) || !array_key_exists('data', $data)) { exit; }

ees_audit_log_webhook('case_noyal', $data);

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
