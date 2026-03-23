<?php

$postdata = json_decode(file_get_contents('php://input'), true);
$token=$postdata['token'];

if(strcmp($token, "ecoasis2024") == 0){

    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(-1);
    
    
    require("config.php");
    
    $timenow = date('y-m-d H:i');
    
    $query = "SELECT id, site_name, capacity, db_name FROM `tbl_site`";

    $result = mysqli_query($admin_link, $query);
    $sites = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    $path = "../core/config/";
    
    
    $all_sites = [];
    
    foreach($sites as $site){
        
        $arr = [];
        
        $config_path = "";
        $power = "";
        
        $config_path = $path . $site['db_name'];
        
        try{
        require $config_path;
        
        $query = "
                (SELECT
                    ROUND(IFNULL(SUM(production), 0),2) as 'data'
                FROM
                    `tbl_hourly_prod`
                WHERE
                    meter_id = 26 and DATE(datetime) = DATE('$timenow'))
                
                UNION ALL
                
                (
                SELECT
                    ROUND(IFNULL(active_power,0),2)
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
        
        $arr['name'] = $site['site_name'];
        $arr['id'] = $site['id'];
        $arr['capacity'] = $site['capacity'];
        $arr['prod'] = $power[0][0];
        $arr['active_power'] = $power[1][0];
        
        array_push($all_sites, $arr);
        
        }
        catch (mysqli_sql_exception $e){
            
            $arr['name'] = $site['site_name'];
            $arr['id'] = $site['id'];
            $arr['capacity'] = $site['capacity'];
            $arr['prod'] = "0";
            $arr['active_power'] = "0";
            
            array_push($all_sites, $arr);
        }
        
    }
    
    
    
    echo json_encode(array("statuscode" => "auth", "data" => $all_sites));
    
    
}
else{
    
    echo json_encode(array("statuscode" => "Err"));
    
}

?>