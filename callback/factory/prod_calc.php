<?php

// function dateDiff($date1, $date2)
// {
//     $date1_ts = strtotime($date1);
//     $date2_ts = strtotime($date2);
//     $diff = $date2_ts - $date1_ts;
//     return round($diff/60);
// }

// $prod_period = dateDiff($start_date, $timenow);

// if($prod_period >= 50 and $prod_period <= 70){
    
$production = bcsub($total_active_energy, $start_energy, 2);
    
$prod_query = "INSERT INTO `tbl_hourly_prod`(`meter_id`, `datetime`, `meter_name`, `starting_datetime`, `ending_datetime`, `production`) VALUES (" . $meter['id'] . ", '" . $round_date . "', '". $meter['meter_name'] .  "', '"  . $start_date .  "', '" . $timenow . "'," . $production . ")";

mysqli_query($phoenix_link, $prod_query);
    
// }
// else{
    
//     $production = bcsub($total_active_energy, $start_energy, 2);
    
//     $prod_query = "INSERT INTO `tbl_hourly_prod`(`meter_id`, `datetime`, `meter_name`, `starting_datetime`, `ending_datetime`, `production`) VALUES (" . $meter['id'] . ", '" . $round_date . "', '". $meter['meter_name'] .  "', '"  . $start_date .  "', '" . $timenow . "'," . $production . ")";
    
//     mysqli_query($phoenix_link, $prod_query);
    
// }

// $myfile1 = fopen("tester_log.txt", "a") or die("Unable to open file!");
// fwrite($myfile1, $hist_query . "\n");
// fwrite($myfile1, $meter['meter_name'] . ": " . $start_date . " - " . $start_energy . " ; " . $timenow . " - " . $total_active_energy . "; " . $production . "\n\n");
// fclose($myfile1);


?>