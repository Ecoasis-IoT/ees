<?php

function hex2float($strHex){
    $bin = hex2bin($strHex);
    $array = unpack("Gnum", $bin);
    return $array['num'];
}

$hex = bin2hex(base64_decode($dev_data));
$code = substr($hex, 0, 2); //07
$header = substr($hex, 2, 2); //7F

// $myfile = fopen("test.txt", "a") or die("Unable to open file!");
// fwrite($myfile, $code . " - ");
// fwrite($myfile, $header . "\n");
// fclose($myfile);


// $myfile = fopen("test1.txt", "a") or die("Unable to open file!");
// fwrite($myfile, ($code == "07" && $header == "7f") . "\n");
// fclose($myfile);


if($code == "07" && $header == "7f"){

    if(strlen($hex) == 12){
        //active power
        $active_power = hex2float(substr($hex, 4,8));
        
        $query = "INSERT INTO `plant_active_power`(`date`, `active_power`) VALUES ('$timenow', $active_power)";
        
    }
    // else if(strlen($hex) == 20){
    //     //Irradiance
    //     $voltage = hex2float(substr($hex, 4,8));
        
    //     $query = "INSERT INTO `plant_avg_voltage`(`date`, `avg_voltage`) VALUES ('$timenow', $voltage )";
        
    // }
    
    
    // $myfile = fopen("log2.txt", "a") or die("Unable to open file!");
    // fwrite($myfile, $query . "\n");
    // fclose($myfile);
    
    mysqli_query($phoenix_link, $query);

}

?>