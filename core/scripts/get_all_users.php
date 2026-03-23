<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

require("../config/admin.php");

$query = "
SELECT
    `id`,
    CONCAT(`firstname`,' ',`lastname`) as 'fullname',
    `email`,
    `date_added`
FROM
    `tbl_user`
    ";

$result = mysqli_query($admin_link, $query);
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($data);

?>