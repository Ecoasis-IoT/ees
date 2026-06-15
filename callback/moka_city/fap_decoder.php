<?php
/**
 * Fire Alarm Panel uplink (fPort 10).
 * status: 0 = Normal, 1 = General Alarm (tbl_main_FAP)
 */

$dev_eui = $data['deviceInfo']['devEui'] ?? '';
$object  = $data['object'] ?? [];

$status = 0;
foreach (['status', 'alarm', 'general_alarm', 'main_fap', 'modbus_chn_1'] as $key) {
    if (array_key_exists($key, $object)) {
        $status = ((int)$object[$key] !== 0) ? 1 : 0;
        break;
    }
}

if ($dev_eui === '') {
    exit;
}

$pdo->prepare(
    'INSERT INTO `tbl_main_FAP` (`date`, `dev_eui`, `status`) VALUES (?, ?, ?)'
)->execute([$timenow, $dev_eui, $status]);
