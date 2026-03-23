<?php

$site_db = $_POST["site_db"];

$timenow = date('y-m-d H:i');

require("../config/" . $site_db);

//==================active power==================

$active_power = "SELECT
                    active_power
                FROM
                    `plant_active_power`
                WHERE
                    DATE(date) = DATE('$timenow')
                ORDER BY date DESC
                LIMit 1";

$result = mysqli_query($link, $active_power);
$site_power = mysqli_fetch_assoc($result);

//=======================Daily Production=====================

// $query_daily = "SELECT
//                     MAX(total_active_energy) - MIN(total_active_energy) as 'daily'
//                 FROM
//                     `tbl_main_meter`
//                 WHERE
//                     DATE(date) = DATE('$timenow')
//                 ORDER BY date DESC";

$query_daily = "SELECT
                    ROUND(SUM(production),2) as 'daily'
                FROM
                    `tbl_hourly_prod`
                WHERE
                    meter_id >= 100 and DATE(datetime) = DATE('$timenow')";

$result = mysqli_query($link, $query_daily);
$site_daily = mysqli_fetch_assoc($result);

//=======================Monthly production=====================

// $query_monthly = "SELECT
//                     MAX(total_active_energy) - MIN(total_active_energy) as 'monthly'
//                 FROM
//                     `tbl_main_meter`
//                 WHERE
//                     MONTH(date) = MONTH(DATE('$timenow'));";

$query_monthly = "SELECT
                    ROUND(SUM(production),2) as 'monthly'
                FROM
                    `tbl_hourly_prod`
                WHERE
                    `meter_id` >= 100 and MONTH(datetime) = MONTH(DATE('$timenow'))";


$result = mysqli_query($link, $query_monthly);
$site_monthly = mysqli_fetch_assoc($result);

//===================Yearly Production===================

// $query_yearly = "SELECT
//                     MAX(total_active_energy) - MIN(total_active_energy) as 'yearly'
//                 FROM
//                     `tbl_main_meter`
//                 WHERE
//                     YEAR(date) = YEAR(DATE('$timenow'))";


$query_yearly = "SELECT
                    ROUND(SUM(production),2) as 'yearly'
                FROM
                    `tbl_hourly_prod`
                WHERE
                    `meter_id` >= 100 and YEAR(datetime) = YEAR(DATE('$timenow'))";


$result = mysqli_query($link, $query_yearly);
$site_yearly = mysqli_fetch_assoc($result);

//=============Average Irradiance=================================

$query_avg_irr = "
SELECT
    AVG(irradiance) as 'avg'
FROM
    `plant_irradiance`
WHERE
DATE(date) = DATE('$timenow') and irradiance != 0";

$result = mysqli_query($link, $query_avg_irr);
$avg_irradiance = mysqli_fetch_assoc($result);


//==============sun hours=========================================

$query_sun_hours = "SELECT
    TIMESTAMPDIFF(MINUTE,MIN(date),MAX(date)) as 'minutes'
FROM
    `plant_irradiance`
WHERE
DATE(date) = DATE('$timenow') and irradiance != 0";

$result = mysqli_query($link, $query_sun_hours);
$sun_hours = mysqli_fetch_assoc($result);


//=====================Grid Availability===========================================

// $grid_query = "SELECT
//     ((SELECT COUNT(id) from plant_avg_voltage WHERE DATE(date) = DATE('2024-06-03') and avg_voltage > 216) / COUNT(id)) * 100 as 'availability'
// FROM
//     `plant_avg_voltage`
// WHERE
// DATE(date) = DATE('$timenow') and avg_voltage > 0";

// $result = mysqli_fetch_assoc($link, $grid_query);
// $grid = mysqli_fetch_assoc($result);


mysqli_close($link);

echo json_encode(array("active_power"=>round($site_power['active_power'],2), "daily_prod"=>round($site_daily['daily'],2), "monthly_prod"=>round($site_monthly['monthly'],2), "yearly_prod"=>round($site_yearly['yearly'],2), "avg_irr" => round($avg_irradiance['avg'], 2), "sun_hours" => $sun_hours['minutes']));


?>