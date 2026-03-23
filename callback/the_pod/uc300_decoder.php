<?php

$modbus = $data['object'];

$active_power = $modbus['modbus_chn_1'];
$irradiance = $modbus['modbus_chn_2'];
$panel_temp = ($modbus['modbus_chn_3'])/10;
$ambient_temp = ($modbus['modbus_chn_4'])/10;

$insolation = ($irradiance/1000) * (5/60);

$query = "INSERT INTO `plant_irradiance`(`date`, `irradiance`, `ambient_temp`, `panel_temp`, `insolation`) VALUES('$timenow', $irradiance, $ambient_temp, $panel_temp, " . round($insolation,5) . ")";
mysqli_query($link, $query);

// $avg_voltage = $modbus['modbus_chn_6'];

$meter_id = 100;
$meter_name = "MAIN METER";

$query1 = "INSERT INTO `plant_active_power`(`date`, `meter_id`, `meter_name`, `active_power`)  VALUES ('$timenow', $meter_id, '$meter_name', $active_power)";
mysqli_query($link, $query1);



//insert into Factory DB as well - Using the same Irradiance Sensor

$conn = mysqli_connect('localhost', 'u889201362_factory_admin', '#T0nvAG5NLe4', 'u889201362_factory');

$query = "INSERT INTO `plant_irradiance`(`date`, `irradiance`, `ambient_temp`, `panel_temp`, `insolation`) VALUES('$timenow', $irradiance, $ambient_temp, $panel_temp, " . round($insolation,5) . ")";
mysqli_query($conn, $query);

mysqli_close($conn);

?>