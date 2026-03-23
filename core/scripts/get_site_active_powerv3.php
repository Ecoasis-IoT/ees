<?php

$site_db = $_POST["site_db"];
$date = $_POST['date'];

// $timenow = date('y-m-d H:i');

require("../config/" . $site_db);

$query = "SELECT
    `meter_id`,
    `meter_name`,
     TIME(date) as 'time',
     ROUND(active_power,2) as 'active_power'
FROM
    `plant_active_power`
WHERE
    DATE(date) = DATE('$date')
    ORDER BY date ASC";


$result = mysqli_query($link, $query);
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($data);

?>