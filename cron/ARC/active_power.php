<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


date_default_timezone_set('Indian/Mauritius');

$time = date("H:i:s");
$start = date("05:00:00");
$end = date("20:00:00");


if($time >= $start and $time <= $end){

    $timenow = date('y-m-d H:i');
    
    $time = strtotime($timenow);
    $minutes = $time % 3600; // pulls the remainder of the hour.
    
    if ($minutes > 180 and $minutes < 3360){
        
        sleep(40);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/devices/70b3d59ba00114df/queue');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'Grpc-Metadata-Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJjaGlycHN0YWNrIiwiaXNzIjoiY2hpcnBzdGFjayIsInN1YiI6IjRmZTkwNGRmLTZlN2UtNDYzZi04Njg2LTEzM2RhZTc2OTg5NCIsInR5cCI6ImtleSJ9.n-SkdFoM-ubP41jsrFoueiQfivocVOqMz_50D6Ddkro',
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"queueItem\": {\n    \"confirmed\": true,\n    \"data\": \"wQZ/GgPzCwI=\",\n   \"fPort\": 1\n  }\n}");
            // \"fCntDown\": 16,\n 
        $response = curl_exec($ch);
        
        curl_close($ch);
        
        $res = json_decode($response, true);
        
        print_r($res);
    
    }
    else{
        echo "Uplink in progress!";
    }
}
else{
    echo "Outside Production Hours";
}    

    

?>