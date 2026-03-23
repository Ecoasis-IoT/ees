<?php

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

$site_id = $_POST["site_id"];

$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];


// GET Site Database
require("../config/admin.php");

$get_db_name = "SELECT
    db_name, capacity
FROM
    `tbl_site`
WHERE
    id =" . $site_id;

$result = mysqli_query($admin_link, $get_db_name);
$res = mysqli_fetch_assoc($result);

mysqli_close($admin_link);

$site_db = $res['db_name'];
$capacity = $res['capacity'];

//FETCH DATA

require("../config/" . $site_db);

//get_total_production
// $query_prod = "SELECT
//     ROUND(MAX(total_active_energy) - MIN(total_active_energy),2) as 'total_prod'
// FROM
//     `tbl_main_meter`
// WHERE
//     DATE(date) >= DATE('$start_date') AND DATE(date) <= DATE('$end_date')";
    
$query_prod = "SELECT
                    ROUND(SUM(production),2) as 'total_prod'
                FROM
                    `tbl_hourly_prod`
                WHERE
                    meter_id >= 100 and DATE(datetime) >= DATE('$start_date') and DATE(datetime) <= DATE('$end_date')";
    


$result = mysqli_query($link, $query_prod);
$data_prod = mysqli_fetch_assoc($result);

//get_total_insolation
$query_insolation = "SELECT
                        ROUND(SUM(insolation),2) as 'insolation'
                    FROM
                        `plant_irradiance`
                    WHERE DATE(date) >= DATE('$start_date') AND DATE(date) <= DATE('$end_date')";

$result = mysqli_query($link, $query_insolation);
$data_ins = mysqli_fetch_assoc($result);

if ($data_ins["insolation"] == NULL) { $data_ins["insolation"] = 0; }

try{
    $pr = round(($data_prod["total_prod"]/($data_ins["insolation"] * $capacity))*100,0);
}
catch(DivisionByZeroError $e){
    $pr = 0;
}

$co2 = round(($data_prod["total_prod"]*0.001)*966,2);


echo json_encode(array("prod"=>$data_prod["total_prod"], "insolation"=>$data_ins["insolation"], "pr"=>$pr, "co2"=>$co2));


?>