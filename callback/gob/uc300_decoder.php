<?php

$modbus       = $data['object'];
$active_power = $modbus['modbus_chn_1'];
$irradiance   = $modbus['modbus_chn_2'];
$panel_temp   = ($modbus['modbus_chn_3']) / 10;
$ambient_temp = ($modbus['modbus_chn_4']) / 10;
$insolation   = ($irradiance / 1000) * (5 / 60);

$pdo->prepare(
    'INSERT INTO `plant_irradiance`(`date`,`irradiance`,`ambient_temp`,`panel_temp`,`insolation`) VALUES (?,?,?,?,?)'
)->execute([$timenow, $irradiance, $ambient_temp, $panel_temp, round($insolation, 5)]);

$meter_id   = 100;
$meter_name = 'MAIN METER';

$pdo->prepare(
    'INSERT INTO `plant_active_power`(`date`,`meter_id`,`meter_name`,`active_power`) VALUES (?,?,?,?)'
)->execute([$timenow, $meter_id, $meter_name, $active_power]);
