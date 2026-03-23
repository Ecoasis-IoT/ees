<?php


function hex2float($strHex){
    $bin = hex2bin($strHex);
    $array = unpack("Gnum", $bin);
    return $array['num'];
}


$hex = bin2hex(base64_decode($dev_data));

$get_meters = "SELECT `id`, `meter_name` from tbl_meters WHERE address = 100";
$result = mysqli_query($link, $get_meters);
$meter = mysqli_fetch_assoc($result);

// $total_active_energy = (hexdec(substr($hex, 6,16)))/1000;

// $myfile1 = fopen("log_pod.txt", "a") or die("Unable to open file!");
//     fwrite($myfile1, $timenow . ", " . $meter['id'] . ", " . hex2float(substr($hex,6,8)) . "\n");
// fclose($myfile1);


$total_active_energy = hex2float(substr($hex,6,8));


$hist_query = "SELECT
                    MAX(`date`) AS 'start_date',
                    `total_active_energy` AS 'start_energy'
                FROM
                    `tbl_main_meter`
                WHERE
                    `date` =(
                    SELECT
                        MAX(`date`)
                    FROM
                        tbl_main_meter)";
        
$hist_result = mysqli_query($link, $hist_query);
$historical = mysqli_fetch_assoc($hist_result);

$start_date = $historical['start_date'];
$start_energy = $historical['start_energy'];

if($total_active_energy == 0){
    $total_active_energy = $start_energy;
}

//Calculate Consumption

if($historical['start_date'] == ""){
    //do nothing
}
else{
    
    try {
        $production = bcsub($total_active_energy, $start_energy, 2);
    
        $prod_query = "INSERT INTO `tbl_hourly_prod`(`meter_id`, `datetime`, `meter_name`, `starting_datetime`, `ending_datetime`, `production`) VALUES (" . $meter['id'] . ", '" . $round_date . "', '". $meter['meter_name'] .  "', '"  . $start_date .  "', '" . $timenow . "'," . $production . ")";
        
        mysqli_query($link, $prod_query);
    } catch (mysqli_sql_exception $e) {
        $myfile1 = fopen("Log_Production_Calculator.txt", "a") or die("Unable to open file!");
        fwrite($myfile1, date("Y-m-d H:i:s") . " : " . $prod_query . "\n");
        fclose($myfile1);
        
        
    }
    
}

// $myfile1 = fopen("LOG_energy_test_16072024.txt", "a") or die("Unable to open file!");
//     fwrite($myfile1, $timenow . ", " . $meter['id'] . ", " . $hex . ", " . $total_active_energy . ", " . $production . "\n");
// fclose($myfile1);

try{
    
    $query = "INSERT INTO `tbl_main_meter`(`date`, `total_active_energy`) VALUES ('" . $round_date . "', " . round($total_active_energy, 5) . ")";
    mysqli_query($link, $query);
}
catch (mysqli_sql_exception $e){
    $myfile1 = fopen("The Pod Main Meter Energy.txt", "a") or die("Unable to open file!");
    fwrite($myfile1, date("Y-m-d H:i:s") . " : " . $query . "\n");
    fclose($myfile1);
}

?>