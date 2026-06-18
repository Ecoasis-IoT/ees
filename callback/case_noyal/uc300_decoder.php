<?php
/**
 * Switchgear Controller UC300 (fPort 85) — decoded object payload.
 *   modbus_chn_1 = active power in W  -> kW (÷1000)
 *   modbus_chn_2 = energy in Wh       -> kWh (÷1000)
 *
 * Storage rules:
 *   - plant_active_power : every reading, at its real timestamp.
 *   - tbl_main_meter     : every reading, at its real timestamp (no rounding, no dedup).
 *   - tbl_hourly_prod    : one row per clock hour. Production for the hour ending at
 *                          each top-of-hour boundary = energy(reading closest to that
 *                          boundary) - energy(reading closest to the previous boundary).
 *                          The very first bucket starts at the first reading (e.g. 13:27
 *                          -> 14:00), then 14:00 -> 15:00, etc. If no reading lands exactly
 *                          on a boundary, the closest available reading is used.
 */

$object = is_array($data['object'] ?? null) ? $data['object'] : [];

// --- Active power (every uplink, real time) -------------------------------
if (isset($object['modbus_chn_1'])) {
    $active_power = (float)$object['modbus_chn_1'] / 1000;

    $pdo->prepare(
        'INSERT INTO `plant_active_power`(`date`,`meter_id`,`meter_name`,`active_power`) VALUES (?,?,?,?)'
    )->execute([$timereal, 100, 'MAIN METER', $active_power]);
}

// --- Energy (every uplink, real time) + hourly production -----------------
if (isset($object['modbus_chn_2'])) {
    $total_active_energy = (float)$object['modbus_chn_2'] / 1000;

    // A cumulative energy meter never legitimately reports 0; skip bad reads so
    // they don't corrupt the production calculation.
    if ($total_active_energy > 0) {
        $pdo->prepare(
            'INSERT INTO `tbl_main_meter`(`date`,`total_active_energy`) VALUES (?,?)'
        )->execute([$timereal, round($total_active_energy, 5)]);

        // Reading in tbl_main_meter closest to a given timestamp
        $closestReading = static function (PDO $pdo, string $boundary) {
            $before = $pdo->prepare(
                'SELECT `date`,`total_active_energy` FROM `tbl_main_meter`
                 WHERE `date` <= ? ORDER BY `date` DESC, `id` DESC LIMIT 1'
            );
            $before->execute([$boundary]);
            $b = $before->fetch();

            $after = $pdo->prepare(
                'SELECT `date`,`total_active_energy` FROM `tbl_main_meter`
                 WHERE `date` > ? ORDER BY `date` ASC, `id` ASC LIMIT 1'
            );
            $after->execute([$boundary]);
            $a = $after->fetch();

            if (!$b && !$a) return null;
            if (!$b) return $a;
            if (!$a) return $b;

            $bDiff = abs(strtotime($b['date']) - strtotime($boundary));
            $aDiff = abs(strtotime($a['date']) - strtotime($boundary));
            return ($aDiff < $bDiff) ? $a : $b;
        };

        $meterStmt = $pdo->prepare('SELECT `id`, `meter_name` FROM tbl_meters WHERE address = 100');
        $meterStmt->execute();
        $meter = $meterStmt->fetch();

        if ($meter) {
            // Find the next top-of-hour boundary that still needs closing
            $lastProd = $pdo->query(
                'SELECT `datetime` FROM `tbl_hourly_prod` ORDER BY `id` DESC LIMIT 1'
            )->fetch();

            if ($lastProd && !empty($lastProd['datetime'])) {
                $startReading = $closestReading($pdo, $lastProd['datetime']);
                $nextBoundary = strtotime($lastProd['datetime']) + 3600;
            } else {
                // First bucket ever: anchor on the first reading, end at the next top-of-hour
                $first = $pdo->query(
                    'SELECT `date`,`total_active_energy` FROM `tbl_main_meter` ORDER BY `date` ASC, `id` ASC LIMIT 1'
                )->fetch();
                $startReading = $first ?: null;
                $nextBoundary = $first
                    ? strtotime(date('Y-m-d H:00:00', strtotime($first['date']))) + 3600
                    : PHP_INT_MAX;
            }

            $nowTs = strtotime($timereal);
            $guard = 0; // safety cap so a large gap can never run away in one request

            while ($startReading && $nowTs >= $nextBoundary && $guard < 50) {
                $guard++;
                $boundaryStr = date('Y-m-d H:i:s', $nextBoundary);
                $endReading  = $closestReading($pdo, $boundaryStr);
                if (!$endReading) break;

                $production = bcsub(
                    (string)$endReading['total_active_energy'],
                    (string)$startReading['total_active_energy'],
                    2
                );

                $pdo->prepare(
                    'INSERT INTO `tbl_hourly_prod`(`meter_id`,`datetime`,`meter_name`,`starting_datetime`,`ending_datetime`,`production`)
                     VALUES (?,?,?,?,?,?)'
                )->execute([
                    $meter['id'], $boundaryStr, $meter['meter_name'],
                    $startReading['date'], $endReading['date'], $production,
                ]);

                $startReading  = $endReading;   // next bucket continues from here
                $nextBoundary += 3600;
            }
        }
    }
}
