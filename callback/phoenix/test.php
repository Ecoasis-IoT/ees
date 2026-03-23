<?php

$hex = bin2hex(base64_decode("AQMIAAAAAABV8EkAMQ=="));
$total_active_energy = (hexdec(substr($hex, 6,16)))/1000;

echo $total_active_energy;


?>