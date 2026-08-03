<?php

$modbus = $data['object'];

if ($dev_eui === '24e124468e236111') {
    $irradiance   = $modbus['modbus_chn_1'];
    $ambient_temp = ($modbus['modbus_chn_2']) / 10;
    $panel_temp   = ($modbus['modbus_chn_3']) / 10;
    $insolation   = ($irradiance / 1000) * (5 / 60);

    $pdo->prepare(
        'INSERT INTO `plant_irradiance`(`date`,`irradiance`,`ambient_temp`,`panel_temp`,`insolation`) VALUES (?,?,?,?,?)'
    )->execute([$timenow, $irradiance, $ambient_temp, $panel_temp, round($insolation, 5)]);

    $meter_id     = 101;
    $meter_name   = 'MAIN METER 2';
    $active_power = $modbus['modbus_chn_4'];

    $pdo->prepare(
        'INSERT INTO `plant_active_power`(`date`,`meter_id`,`meter_name`,`active_power`) VALUES (?,?,?,?)'
    )->execute([$timenow, $meter_id, $meter_name, $active_power]);

} elseif ($dev_eui === '24e124468e237774') {
    $meter_id     = 102;
    $meter_name   = 'MAIN METER 3';
    $active_power = $modbus['modbus_chn_1'];

    $pdo->prepare(
        'INSERT INTO `plant_active_power`(`date`,`meter_id`,`meter_name`,`active_power`) VALUES (?,?,?,?)'
    )->execute([$timenow, $meter_id, $meter_name, $active_power]);

} elseif ($dev_eui === '24e124468e283462') {
    $meter_id     = 100;
    $meter_name   = 'MAIN METER 1';
    $active_power = $modbus['modbus_chn_1'];

    $pdo->prepare(
        'INSERT INTO `plant_active_power`(`date`,`meter_id`,`meter_name`,`active_power`) VALUES (?,?,?,?)'
    )->execute([$timenow, $meter_id, $meter_name, $active_power]);
}
