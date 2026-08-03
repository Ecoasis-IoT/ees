<?php

//Set the Interval to 1H as from 03:59

require_once __DIR__ . '/../config.php';

date_default_timezone_set('Indian/Mauritius');

$tok_phoenix      = getenv('CHIRPSTACK_TOKEN_PHOENIX');
$tok_home_leisure = getenv('CHIRPSTACK_TOKEN_HOME_LEISURE');
$tok_bovalon      = getenv('CHIRPSTACK_TOKEN_BOVALON');
$tok_rtm          = getenv('CHIRPSTACK_TOKEN_RTM');

function dinrsm_set_interval(string $device_eui, string $token): void {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/' . $device_eui . '/queue');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'Grpc-Metadata-Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"TjwA\",\n   \"fPort\": 1\n  }\n}");
    curl_exec($ch);
    curl_close($ch);
}

// Jumbo Phoenix
dinrsm_set_interval('70b3d59ba0011543', $tok_phoenix);
dinrsm_set_interval('70b3d59ba00114df', $tok_phoenix);

// Home & Leisure
dinrsm_set_interval('70b3d59ba001154c', $tok_home_leisure);

// BoValon PVDB1
dinrsm_set_interval('70b3d59ba0011541', $tok_bovalon);
dinrsm_set_interval('70b3d59ba001141d', $tok_bovalon);

// BoValon PVDB2
dinrsm_set_interval('70b3d59ba0011573', $tok_bovalon);
dinrsm_set_interval('70b3d59ba001141b', $tok_bovalon);

// Riche Terre Mall TX1
dinrsm_set_interval('70b3d59ba0011428', $tok_rtm);
dinrsm_set_interval('70b3d59ba00115b0', $tok_rtm);

// Riche Terre Mall TX2
dinrsm_set_interval('70b3d59ba0011542', $tok_rtm);

// Riche Terre Mall TX3
dinrsm_set_interval('70b3d59ba0011587', $tok_rtm);

$logFile = fopen(__DIR__ . '/DINRSM INTERVAL Log.txt', 'a');
if ($logFile) {
    fwrite($logFile, date('Y-m-d H:i:s') . "\n");
    fclose($logFile);
}
