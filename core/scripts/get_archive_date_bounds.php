<?php

$site_id = $_POST['site'];

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



$query = "
SELECT
    MIN(date) as 'min',
    MAX(date) as 'max'
FROM
    `tbl_archive`

";
$result = mysqli_query($link, $query);

$bounds = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode(array("min"=>$bounds["0"]["min"], "max"=>$bounds["0"]["max"]));











?>