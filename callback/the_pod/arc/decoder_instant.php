<?php

function hex2float($strHex) {
    $bin   = hex2bin($strHex);
    $array = unpack('Gnum', $bin);
    return $array['num'];
}

$hex    = bin2hex(base64_decode($dev_data));
$code   = substr($hex, 0, 2);
$header = substr($hex, 2, 2);

if ($code === '07' && $header === '7f') {
    if (strlen($hex) === 12) {
        $active_power = hex2float(substr($hex, 4, 8));

        $pdo->prepare(
            'INSERT INTO `plant_active_power`(`date`,`active_power`) VALUES (?,?)'
        )->execute([$timenow, $active_power]);
    }
}
