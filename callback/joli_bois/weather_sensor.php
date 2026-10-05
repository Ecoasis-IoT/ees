<?php

// Weather sensor (Milesight UC300) -> plant_irradiance
//   modbus_chn_1 = solar irradiance   (W/m^2, raw)
//   modbus_chn_2 = ambient temperature (raw x 0.1 = degC)
//   modbus_chn_3 = panel temperature   (raw x 0.1 = degC)
// Insolation follows the standard EES convention used by the other plants:
//   (irradiance / 1000) * (5 / 60) = kWh/m^2 contributed by this 5-minute reading.

$irradiance   = isset($object['modbus_chn_1']) ? (float) $object['modbus_chn_1'] : 0.0;
$ambient_temp = isset($object['modbus_chn_2']) ? (float) $object['modbus_chn_2'] / 10 : 0.0;
$panel_temp   = isset($object['modbus_chn_3']) ? (float) $object['modbus_chn_3'] / 10 : 0.0;
$insolation   = ($irradiance / 1000) * (5 / 60);

$pdo->prepare(
    'INSERT INTO `plant_irradiance` (`date`, `irradiance`, `insolation`, `ambient_temp`, `panel_temp`) VALUES (?, ?, ?, ?, ?)'
)->execute([$timereal, $irradiance, round($insolation, 5), $ambient_temp, $panel_temp]);
