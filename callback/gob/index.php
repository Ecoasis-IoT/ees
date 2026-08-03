<?php
$json = file_get_contents('php://input');
require_once __DIR__ . '/../shared/bootstrap.php';
verifyWebhookRequest($json);
$pdo = getDB('gob');

$timenow = date('y-m-d H:i');
$data    = json_decode($json, true);

if (!is_array($data) || !array_key_exists('data', $data)) { exit; }

$fPort = $data['fPort'];

if ($fPort == 85) {
    $time  = date('H:i:s');
    $start = date('04:55:00');
    $end   = date('20:15:00');

    if ($time >= $start && $time <= $end) {
        include 'uc300_decoder.php';
    }

} elseif ($fPort == 123) {
    $dev_data = $data['data'] ?? '';

    $ts   = strtotime($timenow);
    $mins = $ts % 3600;
    $ts  -= $mins;
    if ($mins > 1800) $ts += 3600;
    $round_date = date('Y-m-d H:i:s', $ts);

    include 'plant_energy.php';
}
