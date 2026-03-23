<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// $site_id = $_POST["site_id"];

// $site_id = 7777;
$site_id = $_POST["site_id"];
$end_date = $_POST["end_date"];

$month = date('m', strtotime(date($end_date)));

// GET Site Database
require("../config/admin.php");

$get_db_name = "
                SELECT
                    db_name
                FROM
                    `tbl_site`
                WHERE
                    id =" . $site_id;

$result = mysqli_query($admin_link, $get_db_name);
$res = mysqli_fetch_assoc($result);

mysqli_close($admin_link);

$site_db = $res['db_name'];

//FETCH DATA

require("../config/" . $site_db);

$query_history = "
                    SELECT
                        YEAR(`date`) as 'year',
                        `production`,
                        `insolation`
                    FROM
                        `tbl_historical`
                    WHERE
                        MONTH(date) = $month
                    ";
                    
$result = mysqli_query($link, $query_history);
$history = mysqli_fetch_all($result, MYSQLI_ASSOC);

// $next_key = count($history);

$current_year = date('Y');

$query_current = "SELECT
                	'$current_year' as 'year',
                    MAX(total_active_energy) - MIN(total_active_energy) as 'production'
                FROM
                    `tbl_main_meter`
                WHERE
                    YEAR(date) = '$current_year' and MONTH(date) = $month";

$result = mysqli_query($link, $query_current);
$current = mysqli_fetch_all($result, MYSQLI_ASSOC);

// print_r($current);

$current_irradiance = "
SELECT
    SUM(insolation) as 'insolation'
FROM
    `plant_irradiance`
WHERE
    YEAR(date) = '$current_year' and MONTH(date) = $month;";

$result = mysqli_query($link, $current_irradiance);
$irradiance = mysqli_fetch_all($result, MYSQLI_ASSOC);

$history[] = array("year" => $current[0]['year'], "production"=>round($current[0]['production'],2), "insolation" => $irradiance[0]['insolation']);

echo json_encode($history);

?>