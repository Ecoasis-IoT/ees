<?php



date_default_timezone_set('Indian/Mauritius');

$time = date("H:i:s");
$start = date("04:50:00");
$end = date("20:15:00");

if($time >= $start and $time <= $end){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/24e124468e237774/queue');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRhOGM3MWY2LTZiM2ItNDM1Ny1iNjVkLTRiMDgxYWI1MzE0MiIsInR5cCI6ImtleSJ9.7nroIXftRx4ZhhNLGJ8oXnL5tXjw4cRa79AgCBuu0yk',
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"ZgMMgwAEvqY=\",\n   \"fPort\": 123\n  }\n}");
        // \"fCntDown\": 16,\n 
    $response = curl_exec($ch);
    
    curl_close($ch);
    
    $res = json_decode($response, true);
    
    // print_r($res);
}
else{
    echo "Outside Production Hours";
}   


    


?>