<?php

$site_db = $_POST["site_db"];
$date = $_POST['date'];

// $timenow = date('y-m-d H:i');

require("../config/" . $site_db);

$query = "SELECT
     TIME(date) as 'time',
     IF(active_power < 0, 0, ROUND(active_power,2)) as 'active_power'
FROM
    `plant_active_power`
WHERE
    DATE(date) = DATE('$date')
    ORDER BY date ASC";


$result = mysqli_query($link, $query);
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($data);

?>