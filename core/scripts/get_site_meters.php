<?php

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

$site_db = $_POST["site_db"];

$timenow = date('y-m-d H:i');

require("../config/" . $site_db);


$query = "SELECT
            meter_name,
            device_type
        FROM
            `tbl_meters`";
            
$result = mysqli_query($link, $query);
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($data);

?>