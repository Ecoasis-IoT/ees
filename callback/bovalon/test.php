<?php

$arr = [
    "ZAMIAAAAAEbPm0q1qg==",
    "ZAMIAAAAAEbPm0q1qg==",
    "ZAMIAAAAAEbSszF7jw==",
    "ZAMIAAAAAEbSszF7jw==",
    "ZAMIAAAAAEehgMRfHw==",
    "ZAMIAAAAAEemv2Z+lw==",
    "ZAMIAAAAAEbXvCVucQ==",
    "ZAMIAAAAAEesOpi9hQ==",
    "ZAMIAAAAAEbc6Iugzw==",
    "ZAMIAAAAAEbc6Iugzw=="
];

foreach ($arr as $dev_data) {

    $decoded = base64_decode($dev_data);

    if ($decoded === false) {
        echo "Invalid base64 string<br>";
        continue;
    }

    $hex = bin2hex($decoded);

    $total_active_energy = hexdec(substr($hex, 6, 16)) / 1000;

    echo $total_active_energy . "<br>";
}

?>
