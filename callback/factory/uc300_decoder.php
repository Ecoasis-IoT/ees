<?php

$modbus       = $data['object'];
$active_power = $modbus['modbus_chn_1'] / 1000;
$meter_id     = 100;
$meter_name   = 'MAIN METER';

$pdo->prepare(
    'INSERT INTO `plant_active_power`(`date`,`meter_id`,`meter_name`,`active_power`) VALUES (?,?,?,?)'
)->execute([$timenow, $meter_id, $meter_name, $active_power]);
