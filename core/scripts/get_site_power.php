<?php

require "../config/phoenix_mall.php";

$timenow = date('y-m-d H:i');

$query = "
(SELECT
    ROUND(SUM(production),2) as 'data'
FROM
    `tbl_hourly_prod`
WHERE
    meter_id = 100 and DATE(datetime) = DATE('$timenow'))

UNION ALL

(
SELECT
    IFNULL(active_power,0)
FROM
    `plant_active_power`
WHERE
    DATE(date) = DATE('$timenow')
ORDER BY
    DATE DESC
    LIMIT 1);
";

$result = mysqli_query($link, $query);
$phoenix_prod = mysqli_fetch_all($result);
$num = mysqli_num_rows($result);

mysqli_close($link);

// print_r($phoenix_prod);

// echo $num;

if($num == 2){
    echo json_encode(array("ref"=>"phoenix_mall","prod"=>round($phoenix_prod[0][0], 2), "active_power"=>round($phoenix_prod[1][0],2)));
}
else{
    echo json_encode(array("ref"=>"phoenix_mall","prod"=>0, "active_power"=>0));
}



?>