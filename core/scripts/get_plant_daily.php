<?php

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

$site_id = $_POST["site_id"];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

// $site_id = 7778;
// $start_date = "2024-09-10";
// $end_date = "2024-09-20";

// GET Site Database
require("../config/admin.php");

$get_db_name = "
                SELECT
                    db_name, capacity, main_meter
                FROM
                    `tbl_site`
                WHERE
                    id =" . $site_id;

$result = mysqli_query($admin_link, $get_db_name);
$res = mysqli_fetch_assoc($result);

mysqli_close($admin_link);

$site_db = $res['db_name'];
$capacity = $res['capacity'];
$main_meter_id = $res['main_meter'];

//FETCH DATA

require("../config/" . $site_db);


$data = [];

//Gets the production of the day but not cumulative missing days

// $query_energy = "SELECT
// 	DATE(date) as 'date',
//     ROUND(max(total_active_energy) - min(total_active_energy),2) as 'production'
// FROM
//     `tbl_main_meter`
// WHERE
//     DATE(date) >= DATE('$start_date') AND DATE(date) <= DATE('$end_date')
// GROUP BY DATE(date)"; 

$query_energy = "SELECT
            DATE(DATETIME) AS 'date',
            ROUND(SUM(production),2) as 'production'
        FROM
            `tbl_hourly_prod`
        WHERE
            DATE(DATETIME) >= DATE('$start_date') AND DATE(DATETIME) <= DATE('$end_date') AND meter_id >= $main_meter_id
        GROUP BY
            DATE(DATETIME)";


//=========================================================================

$result_energy = mysqli_query($link, $query_energy);
$energy = mysqli_fetch_all($result_energy, MYSQLI_ASSOC);

$query_irradiance = "
SELECT
    DATE(date) as 'date',
    ROUND(SUM(insolation),2) as 'insolation'
FROM
    `plant_irradiance`
WHERE
    DATE(date) >= DATE('$start_date') AND DATE(date) <= DATE('$end_date')
GROUP BY DATE(date)";

$result_irradiance = mysqli_query($link, $query_irradiance);
$irradiance = mysqli_fetch_all($result_irradiance, MYSQLI_ASSOC);

//==========================================================================

// print_r($tempirradiance);



$length = count($energy);
$primary = $energy;
$secondary = $irradiance;

if(count($secondary) != 0){

    for($i = 0; $i<$length; $i++){
        $row = [];
        
        $the_date =  $primary[$i]['date'];
        
        
        for($j = 0; $j < count($secondary); $j++){
            
            // echo $primary[$j]['date'];
            
            if($secondary[$j]['date'] == $the_date){
                
                $row['date'] = $secondary[$j]['date'];
                $row['prod'] = round($primary[$i]['production'],2);
                $row['insolation'] = $secondary[$j]['insolation'];
                
                if((($secondary[$j]['insolation'])*$capacity) != 0){
                    
                    try{
                        $row['pr'] = round(($primary[$i]['production']/(($secondary[$j]['insolation'])*$capacity))*100,0);
                    }
                    catch (DivisionByZeroError $e){
                        
                        $row['pr'] = 0;
                        
                    }
                    
                }
                else{
                    $row['pr'] = 0;
                }
                $data[] = $row;
                break;
            }
        }
    
        
    }
    
}else{
    
    
    for($i = 0; $i<$length; $i++){
        
        $row = [];
                        
        $row['date'] = $primary[$i]['date'];
        $row['prod'] = round($primary[$i]['production'],2);
        $row['insolation'] = 0;
        $row['pr'] = 0;
        
        $data[] = $row;
    }
    
}


echo json_encode($data);


?>