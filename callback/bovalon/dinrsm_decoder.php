<?php

// ini_set('display_startup_errors', 1);
// ini_set('display_errors', 1);
// error_reporting(-1);

function hex2float($strHex){
    $bin = hex2bin($strHex);
    $array = unpack("Gnum", $bin);
    return $array['num'];
}

$hex = bin2hex(base64_decode($dev_data));
$code = substr($hex, 0, 2);
$header = substr($hex, 2, 2);

$header = hexdec($header);

$get_meters = "SELECT `id`, `meter_name`, `header` from tbl_meters WHERE `header` = $header AND `controller_eui` = '$dev_eui'";
$result = mysqli_query($link, $get_meters);
$meter = mysqli_fetch_assoc($result);


if(mysqli_num_rows($result) > 0 and $code == "03"){
    
    if ((int)$header >= 0 and (int)$header <= 17){
        //schneider IEM3250
        
        $active_power = hex2float(substr($hex, 4,8));
        $pf = hex2float(substr($hex,12,8));
        $total_active_energy = hex2float(substr($hex, 20,8));
        
        $energy = $total_active_energy * 0.96;
        
        $query = "INSERT INTO `tbl_sub_meters`(`date`, `meter_id`, `meter_name`, `active_power`, `power_factor`, `total_active_energy`) VALUES ('" . $round_date . "', '" . $meter['id'] . "', '" . $meter['meter_name'] . "', " . round($active_power, 5) . ", " . round($pf, 5) . ", " . round($energy, 5) . ")";
            
            $hist_query = "SELECT
                DATE AS 'start_date',
                `total_active_energy` AS 'start_energy'
            FROM
                `tbl_sub_meters`
            WHERE
                `date` =(
                SELECT
                    MAX(`date`)
                FROM
                    tbl_sub_meters
                WHERE
                    `meter_id` = " . $meter['id'] .
            ") AND `meter_id` = ". $meter['id'];
                
            $hist_result = mysqli_query($link, $hist_query);
            $historical = mysqli_fetch_assoc($hist_result);
            
            $start_date = $historical['start_date'];
            $start_energy = $historical['start_energy'];

                
            if($historical['start_date'] == ""){
                //do nothing
                // No previous data
            }
            else{
                include 'prod_calc.php';
            }
        
    }
    // else if((int)$header == 25){
        
    //     //Main Meter Schneider PM5310
    //     // 03193f800d0a000000018a5e405043c94439436d6639
        
        
    //     $pf = hex2float(substr($hex,4,8));
    //     $total_active_energy = (hexdec(substr($hex, 12,16)))/1000;
    //     $active_power = hex2float(substr($hex,28,8));
    //     $avg_voltage = hex2float(substr($hex,36,8));
        
    //     $ap_query = "INSERT INTO `plant_active_power`(`date`, `active_power`) VALUES ('$timenow', $active_power)";
    //     mysqli_query($link, $ap_query);
        
    //     $voltage_query = "INSERT INTO `plant_avg_voltage`(`date`, `avg_voltage`) VALUES ('$timenow', $avg_voltage )";
    //     mysqli_query($link, $voltage_query);
        
        
    //     $query = "INSERT INTO `tbl_main_meter`(`date`, `power_factor`, `total_active_energy`) VALUES ('" . $round_date . "', " . round($pf, 5) . ", " . round($total_active_energy, 5) . ")";
        
    //     $hist_query = "SELECT
    //                         MAX(`date`) AS 'start_date',
    //                         `total_active_energy` AS 'start_energy'
    //                     FROM
    //                         `tbl_main_meter`
    //                     WHERE
    //                         `date` =(
    //                         SELECT
    //                             MAX(`date`)
    //                         FROM
    //                             tbl_main_meter)";
                
    //     $hist_result = mysqli_query($link, $hist_query);
    //     $historical = mysqli_fetch_assoc($hist_result);
        
    //     $start_date = $historical['start_date'];
    //     $start_energy = $historical['start_energy'];
        
    //     //Calculate Consumption
        
    //     if($historical['start_date'] == ""){
    //         //do nothing
    //     }
    //     else{
    //         include 'prod_calc.php';
    //     }
        
    // }
    // else if((int)$header == 26){
        
    //     //Irradiance Sensor Decoder
        
    //     $irradiance = hexdec(substr($hex, 4,4));
    //     $ambient_temp = (hexdec(substr($hex, 8,4)))/10;
    //     $panel_temp = (hexdec(substr($hex, 12,4)))/10;
        
    //     $query = "INSERT INTO `tbl_irradiance`(`date`, `meter_id`, `meter_name`, `measured_irradiance`, `ambient_temp`, `panel_temp`) VALUES ('" . $timenow . "', '" . $meter['id'] . "', '" . $meter['meter_name'] . "', " . $irradiance . ", " . $ambient_temp . ", " . $panel_temp .")";
        
    // }
}

if(mysqli_query($link, $query)){
    //Do Nothing! All Good.
}
else{
    $myfile = fopen("Critical_Log.txt", "a") or die("Unable to open file!");
    fwrite($myfile, "Database Error:" . $timenow . ":" . $query . "\n");
    fclose($myfile);
}



?>