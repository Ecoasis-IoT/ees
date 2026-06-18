<?php
/**
 * Switchgear Controller UC300 (fPort 85) — decoded object payload.
 *   modbus_chn_1 = active power in W  -> kW (÷1000)
 *   modbus_chn_2 = energy in Wh       -> kWh (÷1000)
 * Energy is stored once per rounded hour so production stays hourly.
 */

$object = is_array($data['object'] ?? null) ? $data['object'] : [];

// --- Active power (every uplink) -----------------------------------------
if (isset($object['modbus_chn_1'])) {
    $active_power = (float)$object['modbus_chn_1'] / 1000;

    $pdo->prepare(
        'INSERT INTO `plant_active_power`(`date`,`meter_id`,`meter_name`,`active_power`) VALUES (?,?,?,?)'
    )->execute([$timenow, 100, 'MAIN METER', $active_power]);
}

// --- Energy + production (once per rounded hour) --------------------------
if (isset($object['modbus_chn_2'])) {
    $total_active_energy = (float)$object['modbus_chn_2'] / 1000;

    // Skip if a reading already exists for this rounded hour
    $exists = $pdo->prepare('SELECT COUNT(*) FROM `tbl_main_meter` WHERE `date` = ?');
    $exists->execute([$round_date]);

    if ((int)$exists->fetchColumn() === 0) {
        $meterStmt = $pdo->prepare('SELECT `id`, `meter_name` FROM tbl_meters WHERE address = 100');
        $meterStmt->execute();
        $meter = $meterStmt->fetch();

        $hist = $pdo->prepare(
            'SELECT MAX(`date`) AS start_date, `total_active_energy` AS start_energy
             FROM `tbl_main_meter`
             WHERE `date` = (SELECT MAX(`date`) FROM tbl_main_meter)'
        );
        $hist->execute();
        $historical   = $hist->fetch();
        $start_date   = $historical['start_date']   ?? '';
        $start_energy = (float)($historical['start_energy'] ?? 0);

        // A zero reading means the meter did not report; carry the last value
        if ($total_active_energy == 0) {
            $total_active_energy = $start_energy;
        }

        if ($start_date !== '' && $meter) {
            $production = bcsub((string)$total_active_energy, (string)$start_energy, 2);

            $pdo->prepare(
                'INSERT INTO `tbl_hourly_prod`(`meter_id`,`datetime`,`meter_name`,`starting_datetime`,`ending_datetime`,`production`)
                 VALUES (?,?,?,?,?,?)'
            )->execute([$meter['id'], $round_date, $meter['meter_name'], $start_date, $timenow, $production]);
        }

        $pdo->prepare(
            'INSERT INTO `tbl_main_meter`(`date`,`total_active_energy`) VALUES (?,?)'
        )->execute([$round_date, round($total_active_energy, 5)]);
    }
}
