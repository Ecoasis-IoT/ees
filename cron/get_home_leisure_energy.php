<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

date_default_timezone_set('Indian/Mauritius');

$time  = date("H:i:s");
$start = "04:50:00";
$end   = "20:15:00";

if ($time >= $start && $time <= $end) {

    $token = getenv('CHIRPSTACK_TOKEN_HOME_LEISURE');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/24e124445e048181/queue');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'Grpc-Metadata-Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"AQMMgwAEtrE=\",\n   \"fPort\": 123\n  }\n}");
        // \"fCntDown\": 16,\n 
    $response = curl_exec($ch);
    
    curl_close($ch);
    
    $res = json_decode($response, true);
    
    print_r($res);
    
    $myfile1 = fopen(__DIR__ . "/HL_UC300_Energy_Downlink.txt", "a") or die("Unable to open file!");
    fwrite($myfile1, date("Y-m-d H:i:s") . "\n");
    fclose($myfile1);

} else {
    echo "Outside Production Hours";
}
