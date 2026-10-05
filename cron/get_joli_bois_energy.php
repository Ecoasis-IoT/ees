<?php

require_once __DIR__ . '/../config.php';

date_default_timezone_set('Indian/Mauritius');

$time  = date('H:i:s');
$start = '04:50:00';
$end   = '20:15:00';

if ($time >= $start && $time <= $end) {

    $token = getenv('CHIRPSTACK_TOKEN_JOLI_BOIS');

    // TODO: replace DEVICE_EUI with the Joli Bois UC300 DevEUI before scheduling this cron.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/DEVICE_EUI/queue');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'Grpc-Metadata-Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"AQMASAACRB0=\",\n   \"fPort\": 123\n  }\n}");

    $response = curl_exec($ch);
    curl_close($ch);

    $res = json_decode($response, true);
    print_r($res);

    $myfile1 = fopen(__DIR__ . '/joli_bois_Energy_Downlink.txt', 'a') or die('Unable to open file!');
    fwrite($myfile1, date('Y-m-d H:i:s') . "\n");
    fclose($myfile1);

} else {
    echo 'Outside Production Hours';
}
