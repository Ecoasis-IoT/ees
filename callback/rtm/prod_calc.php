<?php
    
$production = bcsub($total_active_energy, $start_energy, 2);
    
$prod_query = "INSERT INTO `tbl_hourly_prod`(`meter_id`, `datetime`, `meter_name`, `starting_datetime`, `ending_datetime`, `production`) VALUES (" . $meter['id'] . ", '" . $round_date . "', '". $meter['meter_name'] .  "', '"  . $start_date .  "', '" . $timenow . "'," . $production . ")";

mysqli_query($link, $prod_query);

?>