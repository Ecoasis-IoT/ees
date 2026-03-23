<?php

$time = strtotime("2024-05-24 05:58:00");

$minutes = $time % 3600; # pulls the remainder of the hour.

$time -= $minutes; # just start off rounded down.

if ($minutes > 1800) $time += 3600; # add one hour if 30 mins or higher.

$etime = date("Y-m-d H:i:s", $time);

echo $etime;

?>