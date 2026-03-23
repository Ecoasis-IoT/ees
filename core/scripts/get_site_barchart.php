<?php

$site_db = $_POST["site_db"];
$date = $_POST['date'];

// $timenow = date('y-m-d H:i');

require("../config/" . $site_db);


$query = "SELECT
            TIME_FORMAT(TIME(DATETIME),
            '%H:%i:%s') AS 'time',
            ROUND(sum(production),2) as 'production'
        FROM
            `tbl_hourly_prod`
        WHERE
            meter_id >= 100 AND DATE(DATETIME) = '$date'
        GROUP BY TIME(DATETIME)
        ORDER BY DATETIME ASC
            ";
            
$result = mysqli_query($link, $query);
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($data);

?>