<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$site_id = $_POST['site'];
$meter = $_POST['meter'];
$date = $_POST['date'];

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
    
    //get Production
    
    $query = "SELECT
        TIME(datetime) as 'datetime',
        meter_name,
        production
    FROM
        `tbl_hourly_prod`
    WHERE
        DATE(datetime) = DATE('$date') and meter_id = $meter";
    
    $result = mysqli_query($link, $query);
    $production = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    //get active power and power factor
    
    if($meter >= 100){
        $query = "
                SELECT
                   	TIME(date) AS 'date',
                   	`meter_name`,
                    ROUND(active_power,2) as 'active_power'
                FROM
                    `plant_active_power`
                WHERE
                    DATE(date) = DATE('$date') AND `meter_id` = $meter";
                    
        // $query_pf = "
        // SELECT
        //     TIME(date) AS 'date',
        //     'Main Meter' as 'meter_name',
            
        // FROM
        //     `tbl_main_meter`
        // WHERE
        //     DATE(date) = DATE('$date')";
          
    }
    else{
        
        $query = "SELECT
                    TIME(date) AS 'date',
                    meter_name,
                    ROUND(active_power,2) as 'active_power'
                FROM
                    `tbl_sub_meters`
                WHERE
                    DATE(date) = DATE('$date') and meter_id = $meter";
                    
        // $query_pf = "
        //         SELECT
        //             TIME(date) as 'time',
        //             meter_name,
                    
        //         FROM
        //             `tbl_sub_meters`
        //         WHERE
        //             DATE(date) = DATE('$date') and meter_id = $meter";

    }
    
    $result = mysqli_query($link, $query);
    $active_power = mysqli_fetch_all($result, MYSQLI_ASSOC);    
    
    // $result = mysqli_query($link, $query_pf);
    // $pf = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    //get irradiance
    
    $query = "SELECT
                TIME(date) as 'date',
                irradiance,
                insolation,
                ambient_temp,
                panel_temp
            FROM
                `plant_irradiance`
            WHERE
                DATE(date) = DATE('$date')";
    
    $result = mysqli_query($link, $query);
    $irradiance = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    echo json_encode(array("site_name"=>$site_name, "site_capacity"=>$site_capacity, "prod"=>$production, "active_power"=>$active_power, "irradiance"=>$irradiance));
    

} catch (mysqli_sql_exception $e) {
    
    echo json_encode(array("status" => "Err"));
    
}


?>