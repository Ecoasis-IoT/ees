<?php
$json = file_get_contents('php://input');
require_once __DIR__ . '/../shared/bootstrap.php';
verifyWebhookRequest($json);
$pdo = getDB('phoenix');

$timenow = date('y-m-d H:i');
$data    = json_decode($json, true);

if (!is_array($data) || !array_key_exists('data', $data)) { exit; }

$fPort = $data['fPort'];

if ($fPort == 5) {
    $dev_eui        = $data['deviceInfo']['devEui']            ?? '';
    $device_name    = $data['deviceInfo']['deviceName']        ?? '';
    $dev_type       = $data['deviceInfo']['deviceProfileName'] ?? '';
    $application_id = $data['deviceInfo']['applicationId']    ?? '';
    $gate_id        = $data['rxInfo'][0]['gatewayId']          ?? '';
    $uplink_id      = $data['rxInfo'][0]['uplinkId']           ?? '';
    $rssi           = $data['rxInfo'][0]['rssi']               ?? 0;
    $lorasnr        = $data['rxInfo'][0]['snr']                ?? 0;
    $latitude       = $data['rxInfo'][0]['location']['latitude']  ?? 0;
    $longitude      = $data['rxInfo'][0]['location']['longitude'] ?? 0;
    $frequency      = $data['txInfo']['frequency'] ?? 0;
    $dr             = $data['dr']   ?? 0;
    $adr            = $data['adr']  ?? 0;
    $fCnt           = $data['fCnt'] ?? 0;
    $dev_data       = $data['data'] ?? '';

    $temp = bin2hex(base64_decode($dev_data));

    if (substr($temp, 0, 2) === '03') {
        $time  = date('H:i:s');
        $start = date('04:55:00');
        $end   = date('20:15:00');

        if ($time >= $start && $time <= $end) {
            $ts   = strtotime($timenow);
            $mins = $ts % 3600;
            $ts  -= $mins;
            if ($mins > 1800) $ts += 3600;
            $round_date = date('Y-m-d H:i:s', $ts);

            $stmt = $pdo->prepare(
                'INSERT INTO `tbl_data`(`date`,`device_name`,`device_type`,`dev_eui`,`application_id`,
                 `gatewayID`,`uplinkID`,`rssi`,`loRaSNR`,`latitude`,`longitude`,
                 `frequency`,`dr`,`adr`,`fCnt`,`fPort`,`data`)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $timenow, $device_name, $dev_type, $dev_eui, $application_id,
                $gate_id, $uplink_id, $rssi, $lorasnr, $latitude, $longitude,
                $frequency, $dr, $adr, $fCnt, $fPort, $dev_data,
            ]);

            $stmt2 = $pdo->prepare(
                'SELECT id FROM tbl_data WHERE `date`=? AND uplinkID=? AND dev_eui=?'
            );
            $stmt2->execute([$timenow, $uplink_id, $dev_eui]);
            $data_id = (int)($stmt2->fetchColumn() ?: 0);

            include 'dinrsm_decoder.php';
        }
    }

} elseif ($fPort == 85) {
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
