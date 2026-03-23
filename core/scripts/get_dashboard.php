<?php

require("../config/admin.php");

$query = "SELECT `id`, `site_name`, `db_name`,`location`, `commissioned` FROM `tbl_site` ORDER BY `id`";

$result = mysqli_query($admin_link, $query);
$sites = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_close($admin_link);

for($i = 0; $i < count($sites); $i++){
    
    if($sites[$i]["commissioned"] == 1){
    
        $config_path = "../config/";
        $config_path .= $sites[$i]["db_name"];
    
        require $config_path;
        
        $timenow = date('y-m-d H:i');

        $query = "
                (SELECT
                    ROUND(SUM(production),2) as 'data'
                FROM
                    `tbl_hourly_prod`
                WHERE
                    meter_id >= 100 and DATE(datetime) = DATE('$timenow'))
                
                UNION ALL
                
                (
                SELECT
                    IFNULL(active_power,0)
                FROM
                    `plant_active_power`
                WHERE
                    DATE(date) = DATE('$timenow')
                ORDER BY
                    DATE DESC
                    LIMIT 1);
                ";
        
        $result = mysqli_query($link, $query);
        $power = mysqli_fetch_all($result);
        
        mysqli_close($link);
        $sites[$i]["prod"] = round($power[0][0], 2);
        $sites[$i]["active_power"] = round($power[1][0], 2);
        
        
    }
    else if($sites[$i]["commissioned"] == 0){
        
        $sites[$i]["prod"] = 0;
        $sites[$i]["active_power"] = 0;
        
    }
    
}

echo json_encode($sites);

?>