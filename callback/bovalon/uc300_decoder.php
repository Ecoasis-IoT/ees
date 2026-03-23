<?php

$modbus = $data['object'];

        
if($dev_eui == "24e124445e042752"){
        
    $irradiance = $modbus['modbus_chn_1'];
    $ambient_temp = ($modbus['modbus_chn_2'])/10;
    $panel_temp = ($modbus['modbus_chn_3'])/10;
    
    $insolation = ($irradiance/1000) * (5/60);
    
    $query = "INSERT INTO `plant_irradiance`(`date`, `irradiance`, `ambient_temp`, `panel_temp`, `insolation`) VALUES('$timenow', $irradiance, $ambient_temp, $panel_temp, " . round($insolation,5) . ")";
    mysqli_query($link, $query);
    
    $meter_id = 100;
    $meter_name = "MAIN METER 1";
    
    $active_power = $modbus['modbus_chn_4'];
    $power_factor = $modbus['modbus_chn_5'];
    
    $query1 = "INSERT INTO `plant_active_power`(`date`, `meter_id`, `meter_name`, `active_power`)  VALUES ('$timenow', $meter_id, '$meter_name', $active_power)";
    mysqli_query($link, $query1);
    
    $query2 = "INSERT INTO `tbl_main_meter_pf`(`date`,`meter_id`, `meter_name`, `power_factor`) VALUES ('$timenow', $meter_id, '$meter_name', $power_factor)";
    mysqli_query($link, $query2);

}
else{
    
    $meter_id = 101;
    $meter_name = "MAIN METER 2";
    
    $active_power = $modbus['modbus_chn_1'];
    $power_factor = $modbus['modbus_chn_2'];
    
    $query1 = "INSERT INTO `plant_active_power`(`date`, `meter_id`, `meter_name`, `active_power`)  VALUES ('$timenow', $meter_id, '$meter_name', $active_power)";
    mysqli_query($link, $query1);
    
    $query2 = "INSERT INTO `tbl_main_meter_pf`(`date`,`meter_id`, `meter_name`, `power_factor`) VALUES ('$timenow', $meter_id, '$meter_name', $power_factor)";
    mysqli_query($link, $query2);
    
}


// $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
// fwrite($myfile, $query);
// fclose($myfile);



?>