<?php

$production = bcsub((string)$energy, (string)$start_energy, 2);

$pdo->prepare(
    'INSERT INTO `tbl_hourly_prod`(`meter_id`,`datetime`,`meter_name`,`starting_datetime`,`ending_datetime`,`production`)
     VALUES (?,?,?,?,?,?)'
)->execute([$meter['id'], $round_date, $meter['meter_name'], $start_date, $timenow, $production]);
