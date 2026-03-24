<?php

require_once __DIR__ . '/../config.php';

date_default_timezone_set('Indian/Mauritius');

$time  = date('H:i:s');
$start = '04:50:00';
$end   = '20:15:00';

if ($time >= $start && $time <= $end) {

    $token = getenv('CHIRPSTACK_TOKEN_RTM');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/24e124468e283462/queue');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'Grpc-Metadata-Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"ZAMMgwAEv0Q=\",\n   \"fPort\": 123\n  }\n}");
    curl_exec($ch);
    curl_close($ch);

} else {
    echo 'Outside Production Hours';
}
