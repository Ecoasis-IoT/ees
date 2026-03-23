<?php

require("../config/admin.php");

$query = "SELECT * FROM `tbl_site`";

$result = mysqli_query($admin_link, $query);
$sites = mysqli_fetch_all($result);

echo json_encode(array("data" => $sites));

?>