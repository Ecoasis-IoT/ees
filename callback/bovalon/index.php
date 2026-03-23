<?php
// Takes raw data from the request
$json 	= file_get_contents('php://input');

require("bovalon_config.php");

$timenow = date('y-m-d H:i');

// Converts it into a PHP object
$data 	= json_decode($json, TRUE);
if (array_key_exists("data", $data)){
    
    $fPort = $data['fPort'];
    
    if($fPort == 5){
        
        $dev_eui = $data['deviceInfo']['devEui'];
        $device_name = $data['deviceInfo']['deviceName'];
        $dev_type = $data['deviceInfo']['deviceProfileName'];
        $application_id = $data['deviceInfo']['applicationId'];
        
        
        $gate_id = $data['rxInfo']['0']['gatewayId'];
        $uplink_id = $data['rxInfo']['0']['uplinkId'];
        $rssi = $data['rxInfo']['0']['rssi'];
        $lorasnr = $data['rxInfo']['0']['snr'];
        
        $latitude = $data['rxInfo']['0']['location']['latitude'];
        $longitude = $data['rxInfo']['0']['location']['longitude'];
        //$altitude = $data['rxInfo']['0']['location']['altitude'];
        
        $frequency = $data['txInfo']['frequency'];
        $dr = $data['dr'];
        $adr = $data['adr'];
        $fCnt = $data['fCnt'];
        $dev_data = $data['data'];
        
        $temp = bin2hex(base64_decode($dev_data));
        
        // $myfile = fopen("log.txt", "a") or die("Unable to open file!");
        // fwrite($myfile, $temp . "\n");
        // fclose($myfile);
        
        // if(substr($temp,0,2) == "07"){
        //     // file_get_contents('https://hc-ping.com/7a5ceb27-f1e7-4b48-857b-b14278c62ab0');
        //     include 'decoder_instant.php';
            
        // }
        // else 
        if(substr($temp,0,2) == "03"){
            
                $time = date("H:i:s");
                $start = date("04:55:00");
                $end = date("20:15:00");
                if($time >= $start and $time <= $end){
        
                    $time = strtotime($timenow);
                    $minutes = $time % 3600; // pulls the remainder of the hour.
                    $time -= $minutes; // just start off rounded down.
                    if ($minutes > 1800) $time += 3600; // add one hour if 30 mins or higher.
                    $round_date = date("Y-m-d H:i:s", $time);
            
                
                    $query = "INSERT INTO `tbl_data`(`date`, `device_name`, `device_type`, `dev_eui`, `application_id`, `gatewayID`, `uplinkID`, `rssi`, `loRaSNR`, `latitude`, `longitude`, `frequency`, `dr`, `adr`, `fCnt`, `fPort`, `data`) VALUES ('$timenow', '$device_name', '$dev_type', '$dev_eui', '$application_id', '$gate_id', '$uplink_id', '$rssi', '$lorasnr', '$latitude', '$longitude', '$frequency', '$dr', '$adr', '$fCnt', '$fPort', '$dev_data');";
                        
                    mysqli_query($link, $query);
                    
                    //Get Auto-Increment ID after Insert
                    
                    $get_data_id = "SELECT id from tbl_data WHERE `date` = '$timenow' AND `uplinkID` = '$uplink_id' AND `dev_eui` = '$dev_eui'";
                    $result = mysqli_query($link, $get_data_id);
                    $arr_result = mysqli_fetch_assoc($result);
                    $data_id = $arr_result["id"];
                    
                    //Decoding Energy Meter Schneider IEM3250 sub meters
                    include "dinrsm_decoder.php";
                }
        }
    }
    else if($fPort == 85){
        $dev_eui = $data['deviceInfo']['devEui'];
        $time = date("H:i:s");
        $start = date("04:55:00");
        $end = date("20:15:00");

        if($time >= $start and $time <= $end){
        
            include "uc300_decoder.php";
        }
        
    }
    else if($fPort == 123){
        
        $dev_data = $data['data'];
        $dev_eui = $data['deviceInfo']['devEui'];
        
        $myfile = fopen("log_energy.txt", "a") or die("Unable to open file!");
        fwrite($myfile, $dev_data . "\n");
        fclose($myfile);
        
        $time = strtotime($timenow);
        $minutes = $time % 3600; // pulls the remainder of the hour.
        $time -= $minutes; // just start off rounded down.
        if ($minutes > 1800) $time += 3600; // add one hour if 30 mins or higher.
        $round_date = date("Y-m-d H:i:s", $time);
        
        
        include 'plant_energy.php';
        
        
        
    }
    
}

?>