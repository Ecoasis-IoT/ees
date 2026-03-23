<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$site_id = $_POST['site'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

// GET Site Database
require("../config/admin.php");

$get_db_name = "
                SELECT
                    site_name,
                    db_name,
                    capacity
                FROM
                    `tbl_site`
                WHERE
                    id =" . $site_id;

$result = mysqli_query($admin_link, $get_db_name);
$res = mysqli_fetch_assoc($result);

mysqli_close($admin_link);
    
$site_db = $res['db_name'];
$site_name = $res['site_name'];

//Create Connection to site db

require("../config/" . $site_db);



$query = "SELECT `date`, `production`, `insolation` FROM `tbl_archive` WHERE `date` >= '$start_date' and `date` <= '$end_date'";
$result = mysqli_query($link, $query);

$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode(array("site_name"=>$site_name, "archive"=>$data));

// echo $query;


?>