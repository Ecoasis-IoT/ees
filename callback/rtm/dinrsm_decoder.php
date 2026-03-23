<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

function hex2float($strHex){
    $bin = hex2bin($strHex);
    $array = unpack("Gnum", $bin);
    return $array['num'];
}

$hex = bin2hex(base64_decode($dev_data));
$code = substr($hex, 0, 2);
$header = substr($hex, 2, 2);

$header = hexdec($header);

$get_meters = "SELECT `id`, `meter_name`, `header` from tbl_meters WHERE `header` = $header AND `controller_eui` = '$dev_eui'";
$result = mysqli_query($link, $get_meters);
$meter = mysqli_fetch_assoc($result);


if(mysqli_num_rows($result) > 0 and $code == "03"){
    
    if ((int)$header >= 0 and (int)$header <= 24){
        //schneider IEM3250
        
        $active_power = 0;
        $pf = 0;
        $total_active_energy = 0;
        
        $active_power = hex2float(substr($hex, 4,8));
        $pf = hex2float(substr($hex,12,8));
        
        if(is_nan($pf)){
            $pf = "***";
        }
        else{
            $pf = round($pf, 5);
        }
        
        $total_active_energy = hex2float(substr($hex, 20,8));
        
        
        $query = "INSERT INTO `tbl_sub_meters`(`date`, `meter_id`, `meter_name`, `active_power`, `power_factor`, `total_active_energy`) VALUES ('" . $round_date . "', '" . $meter['id'] . "', '" . $meter['meter_name'] . "', " . round($active_power, 5) . ", '" . $pf . "', " . round($total_active_energy, 5) . ")";
        
        
        $myfile = fopen("test_log170325.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $query);
        fclose($myfile);
            
            $hist_query = "SELECT
                DATE AS 'start_date',
                `total_active_energy` AS 'start_energy'
            FROM
                `tbl_sub_meters`
            WHERE
                `date` =(
                SELECT
                    MAX(`date`)
                FROM
                    tbl_sub_meters
                WHERE
                    `meter_id` = " . $meter['id'] .
            ") AND `meter_id` = ". $meter['id'];
                
            $hist_result = mysqli_query($link, $hist_query);
            $historical = mysqli_fetch_assoc($hist_result);
            
            $start_date = $historical['start_date'];
            $start_energy = $historical['start_energy'];

                
            if($historical['start_date'] == ""){
                //do nothing
                // No previous data
                
            }
            else{
                include 'prod_calc.php';
            }
        
    }
}

if(mysqli_query($link, $query)){
    //Do Nothing! All Good.
}
else{
    $myfile = fopen("Critical_Log.txt", "a") or die("Unable to open file!");
    fwrite($myfile, "Database Error:" . $timenow . ":" . $query . "\n");
    fclose($myfile);
}



?>