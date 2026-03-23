<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

date_default_timezone_set('Indian/Mauritius');

$time = date("H:i:s");
$start = date("04:50:00");
$end = date("20:15:00");


if($time >= $start and $time <= $end){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/24e124468f082638/queue');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjE1NWI0ZDhiLWZhYjctNDc4ZC04MGNhLWY1Y2E0MTY1YjJhYiIsInR5cCI6ImtleSJ9.kJX83jyk1KGcG1E4GME3OfW4CLRO5oKzF54641-GJgw',
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"AQMASAACRB0=\",\n   \"fPort\": 123\n  }\n}");
        // \"fCntDown\": 16,\n 
    $response = curl_exec($ch);
    
    curl_close($ch);
    
    $res = json_decode($response, true);
    
    print_r($res);
    
    date_default_timezone_set('Indian/Mauritius');
    $myfile1 = fopen("factory_Energy_Downlink.txt", "a") or die("Unable to open file!");
    fwrite($myfile1, date("Y-m-d H:i:s") . "\n");
    fclose($myfile1);

    
}
else{
    echo "Outside Production Hours";
}    


?>