<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if(isset($_POST['site'])){$site_id = $_POST['site'];}
if(isset($_POST['meters'])){$meters = $_POST['meters'];}
if(isset($_POST['arr_meters'])){$arr_meters = $_POST['arr_meters'];}
if(isset($_POST['param'])){$param = $_POST['param'];}
if(isset($_POST['start_date'])){$start = $_POST['start_date'];}
if(isset($_POST['end_date'])){$end = $_POST['end_date'];}
if(isset($_POST['irradiance'])){$irradiance = $_POST['irradiance'];}
if(isset($_POST['ambientTemp'])){$ambientTemp = $_POST['ambientTemp'];}
if(isset($_POST['panelTemp'])){$panelTemp = $_POST['panelTemp'];}



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

$data = [];

if($param == "prod"){
    
    $query = "
    SELECT
        datetime,
        meter_id,
        meter_name,
        production
    FROM
        `tbl_hourly_prod`
    WHERE
        meter_id in ($meters) AND DATE(datetime) >= DATE('$start') AND DATE(datetime) <= DATE('$end')
    ORDER BY datetime ASC
    ";
    
    $result = mysqli_query($link, $query);
    $prod = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    array_push($data, ...$prod);
    
    
}
else if($param == "a_power"){
    //Bo'Valon Mall
    if($site_id == "7780"){
        
        if(in_array('100', $arr_meters)){
            
            $query = "
            SELECT
                date as 'datetime',
                `meter_id`,
                `meter_name`,
                IF(active_power < 0, 0, ROUND(active_power,2)) as 'active_power'
            FROM
                `plant_active_power`
            WHERE
                DATE(date) >= DATE('$start') AND DATE(date) <= DATE('$end') AND `meter_id` = 100
            ORDER BY date ASC
            ";
            
            $result = mysqli_query($link, $query);
            
            $main_meter = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            array_push($data, ...$main_meter);
            
            $key = array_search('100', $arr_meters);
            
            array_splice($arr_meters, $key, 1);
            
        }
       
        if(in_array('101', $arr_meters)){
            
            $query = "
            SELECT
                date as 'datetime',
                `meter_id`,
                `meter_name`,
                IF(active_power < 0, 0, ROUND(active_power,2)) as 'active_power'
            FROM
                `plant_active_power`
            WHERE
                DATE(date) >= DATE('$start') AND DATE(date) <= DATE('$end') AND `meter_id` = 101
            ORDER BY date ASC
            ";
            
            $result = mysqli_query($link, $query);
            
            $main_meter = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            array_push($data, ...$main_meter);
            
            $key = array_search('101', $arr_meters);
            
            array_splice($arr_meters, $key, 1);
            
        }
        
        if(count($arr_meters) > 0){
        
            $meter_ids = implode(",",$arr_meters);
            
            $query = "
            SELECT
                date as 'datetime',
                meter_id,
                meter_name,
                active_power
            FROM
                `tbl_sub_meters`
            WHERE
                meter_id in ($meter_ids) and DATE(date) >= DATE('$start') AND DATE(date) <= DATE('$end')
            ORDER BY date ASC
            ";
            
            $result = mysqli_query($link, $query);
            
            $sub_meters = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            array_push($data, ...$sub_meters);
        
        }
        
        
        
        
    }
    else{
        // All other Sites with only 1 main meter
    
        if(in_array('100', $arr_meters)){
            
            $query = "
            SELECT
                date as 'datetime',
                '100' as 'meter_id',
                'Main Meter' as 'meter_name',
                IF(active_power < 0, 0, ROUND(active_power,2)) as 'active_power'
            FROM
                `plant_active_power`
            WHERE
                DATE(date) >= DATE('$start') AND DATE(date) <= DATE('$end')
            ORDER BY date ASC
            ";
            
            $result = mysqli_query($link, $query);
            
            $main_meter = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            array_push($data, ...$main_meter);
            
            $key = array_search('100', $arr_meters);
            
            array_splice($arr_meters, $key, 1);
        }
        
        if(count($arr_meters) > 0){
        
            $meter_ids = implode(",",$arr_meters);
            
            $query = "
            SELECT
                date as 'datetime',
                meter_id,
                meter_name,
                active_power
            FROM
                `tbl_sub_meters`
            WHERE
                meter_id in ($meter_ids) and DATE(date) >= DATE('$start') AND DATE(date) <= DATE('$end')
            ORDER BY date ASC
            ";
            
            $result = mysqli_query($link, $query);
            
            $sub_meters = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            array_push($data, ...$sub_meters);
        
        }
    }
}

if($irradiance == 1){
    
    $query = "
            SELECT
                date as 'datetime',
                '99' as 'meter_id',
                irradiance
            FROM
                `plant_irradiance`
            WHERE
            DATE(date) >= DATE('$start') AND DATE(date) <= DATE('$end')
            ORDER BY date ASC
    ";
    
    
    $result = mysqli_query($link, $query);
    $irr_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    array_push($data, ...$irr_data);   
    
}


if($ambientTemp == 1){
    
    $query = "
            SELECT
                date as 'datetime',
                '99' as 'meter_id',
                ambient_temp
            FROM
                `plant_irradiance`
            WHERE
            DATE(date) >= DATE('$start') AND DATE(date) <= DATE('$end')
            ORDER BY date ASC
    ";
    
    
    $result = mysqli_query($link, $query);
    $aTemp_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    array_push($data, ...$aTemp_data);   
    
}

if($panelTemp == 1){
    
    $query = "
            SELECT
                date as 'datetime',
                '99' as 'meter_id',
                panel_temp
            FROM
                `plant_irradiance`
            WHERE
            DATE(date) >= DATE('$start') AND DATE(date) <= DATE('$end')
            ORDER BY date ASC
    ";
    
    
    $result = mysqli_query($link, $query);
    $panelTemp_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    array_push($data, ...$panelTemp_data);   
    
}


echo json_encode(array("site_name"=>$site_name, 'data'=>$data));




?>