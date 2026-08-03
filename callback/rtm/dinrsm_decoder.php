<?php

function hex2float($strHex) {
    $bin   = hex2bin($strHex);
    $array = unpack('Gnum', $bin);
    return $array['num'];
}

$hex    = bin2hex(base64_decode($dev_data));
$code   = substr($hex, 0, 2);
$header = (int) hexdec(substr($hex, 2, 2));

$stmt = $pdo->prepare(
    'SELECT `id`, `meter_name`, `header` FROM tbl_meters WHERE `header` = ? AND `controller_eui` = ?'
);
$stmt->execute([$header, $dev_eui]);
$meter    = $stmt->fetch();
$rowCount = $stmt->rowCount();

if ($rowCount > 0 && $code === '03') {

    if ($header >= 0 && $header <= 24) {
        $active_power        = hex2float(substr($hex, 4, 8));
        $pf                  = hex2float(substr($hex, 12, 8));
        $total_active_energy = hex2float(substr($hex, 20, 8));

        if (is_nan($pf)) {
            $pf = 0;
        } else {
            $pf = round($pf, 5);
        }

        $hist = $pdo->prepare(
            'SELECT `date` AS start_date, `total_active_energy` AS start_energy
             FROM `tbl_sub_meters`
             WHERE `date` = (SELECT MAX(`date`) FROM tbl_sub_meters WHERE `meter_id` = ?)
               AND `meter_id` = ?'
        );
        $hist->execute([$meter['id'], $meter['id']]);
        $historical   = $hist->fetch();
        $start_date   = $historical['start_date']   ?? '';
        $start_energy = (float)($historical['start_energy'] ?? 0);

        if ($start_date !== '') {
            include 'prod_calc.php';
        }

        $ins = $pdo->prepare(
            'INSERT INTO `tbl_sub_meters`(`date`,`meter_id`,`meter_name`,`active_power`,`power_factor`,`total_active_energy`)
             VALUES (?,?,?,?,?,?)'
        );
        $ins->execute([
            $round_date,
            $meter['id'],
            $meter['meter_name'],
            round($active_power, 5),
            $pf,
            round($total_active_energy, 5),
        ]);
    }
}
