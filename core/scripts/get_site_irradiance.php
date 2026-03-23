<?php

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

$site_db = $_POST["site_db"];
$date = $_POST['date'];

// $timenow = date('y-m-d H:i');

require("../config/" . $site_db);

$query = "SELECT TIME(date) as 'time', irradiance, ambient_temp, panel_temp FROM `plant_irradiance` WHERE DATE(date) = DATE('$date') ORDER BY date ASC";
$results = mysqli_query($link, $query);
$data = mysqli_fetch_all($results, MYSQLI_ASSOC);

echo json_encode($data);

?>