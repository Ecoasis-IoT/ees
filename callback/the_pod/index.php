<?php
// Takes raw data from the request
$json 	= file_get_contents('php://input');

require("pod_config.php");

$timenow = date('y-m-d H:i');

// Converts it into a PHP object
$data 	= json_decode($json, TRUE);


if (array_key_exists("data", $data)){
    
    $fPort = $data['fPort'];
    
    if($fPort == 85){
        
        $time = date("H:i:s");
        $start = date("04:55:00");
        $end = date("20:15:00");

        if($time >= $start and $time <= $end){
        
            include "uc300_decoder.php";
        }
        
    }
    else if($fPort == 123){
        
        $dev_data = $data['data'];
        
        $time = strtotime($timenow);
        $minutes = $time % 3600; // pulls the remainder of the hour.
        $time -= $minutes; // just start off rounded down.
        if ($minutes > 1800) $time += 3600; // add one hour if 30 mins or higher.
        $round_date = date("Y-m-d H:i:s", $time);
        
        
        include 'plant_energy.php';
   
    }
    
}

?>