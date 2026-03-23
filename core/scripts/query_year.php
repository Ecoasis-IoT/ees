<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$site_id = $_POST['site'];
$meter = $_POST['meter'];
$year = $_POST['year'];

// GET Site Database
require("../config/admin.php");

$get_db_name = "
                SELECT
                    site_name,
                    db_name,
                    capacity
                FROM
                    `tbl_site`
                WHERE
                    id =" . $site_id;

$result = mysqli_query($admin_link, $get_db_name);
$res = mysqli_fetch_assoc($result);

    
mysqli_close($admin_link);
    
$site_db = $res['db_name'];
$site_name = $res['site_name'];
$site_capacity = $res['capacity'];

//Create Connection to site db

require("../config/" . $site_db);

try {
    
//     //get Production
    
    $query = "SELECT
                MONTH(DATETIME) AS 'datetime',
                meter_name,
                ROUND(SUM(production),2) as 'production'
            FROM
                `tbl_hourly_prod`
            WHERE
                YEAR(DATETIME) = $year AND meter_id = $meter
            GROUP BY
                MONTH(DATETIME)";
    
    $result = mysqli_query($link, $query);
    $production = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
//     //get active power
    
    // if($meter == 100){
    //     $query = "
    //             SELECT
    //                 date AS 'date',
    //                 'Main Meter' AS 'meter_name',
    //                 ROUND(active_power, 2) AS 'active_power'
    //             FROM
    //                 `plant_active_power`
    //             WHERE
    //                 YEAR(date) = $year";
          
    // }
    // else{
        
    //     $query = "
    //             SELECT
    //                 date AS 'date',
    //                 meter_name,
    //                 ROUND(active_power, 2) AS 'active_power'
    //             FROM
    //                 `tbl_sub_meters`
    //             WHERE
    //                 YEAR(date) = $year AND meter_id = $meter";
                
    // }
    
    // $result = mysqli_query($link, $query);
    // $active_power = mysqli_fetch_all($result, MYSQLI_ASSOC);    
    
    //get irradiance
    
    $query = "
            SELECT
                MONTH(date) AS 'date',
                ROUND(sum(insolation),2) as 'insolation'
            FROM
                `plant_irradiance`
            WHERE
                YEAR(date) = $year 
            GROUP BY MONTH(date)";
    
    $result = mysqli_query($link, $query);
    $irradiance = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
//     echo json_encode(array("site_name"=>$site_name, "site_capacity"=>$site_capacity, "prod"=>$production, "active_power"=>$active_power, "pf"=>$pf, "irradiance"=>$irradiance));
    echo json_encode(array("site_name"=>$site_name, "site_capacity"=>$site_capacity, "prod"=>$production, "irradiance"=>$irradiance));

} catch (mysqli_sql_exception $e) {
    
    echo json_encode(array("status" => "Err"));
    
}


?>