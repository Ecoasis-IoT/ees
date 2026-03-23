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
    curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/24e124445e042752/queue');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6Ijc4OTY3ZDFlLTEzMGEtNDdhMy1iNDY2LTc4NmM4ZDhhYmZmYSIsInR5cCI6ImtleSJ9.AkxIN3b9wOx6NGO3AHGzBXUxRRxUNkdB3KvIRRxagfI',
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"ZAMMgwAEv0Q=\",\n   \"fPort\": 123\n  }\n}");
        // \"fCntDown\": 16,\n 
    $response = curl_exec($ch);
    
    curl_close($ch);
    
    $res = json_decode($response, true);
    
    print_r($res);
    
    date_default_timezone_set('Indian/Mauritius');
    
    $myfile1 = fopen("BV_pvdb1_UC300_Energy_Downlink.txt", "a") or die("Unable to open file!");
    fwrite($myfile1, date("Y-m-d H:i:s") . "\n");
    fclose($myfile1);
    
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/24e124445e048229/queue');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6Ijc4OTY3ZDFlLTEzMGEtNDdhMy1iNDY2LTc4NmM4ZDhhYmZmYSIsInR5cCI6ImtleSJ9.AkxIN3b9wOx6NGO3AHGzBXUxRRxUNkdB3KvIRRxagfI',
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"ZAMMgwAEv0Q=\",\n   \"fPort\": 123\n  }\n}");
        // \"fCntDown\": 16,\n 
    $response = curl_exec($ch);
    
    curl_close($ch);
    
    $res = json_decode($response, true);
    
    print_r($res);
    
    date_default_timezone_set('Indian/Mauritius');
    
    $myfile1 = fopen("BV_pvdb2_UC300_Energy_Downlink.txt", "a") or die("Unable to open file!");
    fwrite($myfile1, date("Y-m-d H:i:s") . "\n");
    fclose($myfile1);

}
else{
    echo "Outside Production Hours";
}    


?>