<?php

$site_id = $_POST['site'];

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

try {

    
    $site_db = $res['db_name'];
    
    //FETCH DATA
    
    require("../config/" . $site_db);
    
    
    $query = "
    SELECT
        address as 'meter_id',
        meter_name as 'name'
    FROM
        `tbl_meters`
    WHERE `id` != 99;
    ";
    
    $result = mysqli_query($link, $query);
    $meters = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
        
    
    $query = "
            SELECT
            	MIN(DATE(datetime)) as 'startdate',
            	MAX(DATE(datetime)) as 'enddate',
                
                MIN(MONTH(datetime)) as 'startmth',
                MAX(MONTH(datetime)) as 'endmth',
                
            	MIN(YEAR(datetime)) as 'startyr',
                MAX(YEAR(datetime)) as 'endyr'
            FROM
                `tbl_hourly_prod`";
    $result = mysqli_query($link, $query);
    $validate = mysqli_fetch_assoc($result);
    
    
    
    echo json_encode(array("meters" => $meters, "dates" => $validate));

} catch (mysqli_sql_exception $e) {
    
    echo json_encode(array("status" => "Err"));
    
}

?>