<?php

require_once __DIR__ . '/../config.php';

date_default_timezone_set('Indian/Mauritius');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$token = getenv('CHIRPSTACK_TOKEN_PHOENIX');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://195.35.48.27:8090/api/gateways/24e124fffef8b910');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Grpc-Metadata-Authorization: Bearer ' . $token,
]);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    error_log('gateway_status curl error: ' . curl_error($ch));
    curl_close($ch);
    exit(1);
}
curl_close($ch);

$res = json_decode($result, true);
if (!is_array($res) || empty($res['lastSeenAt'])) {
    error_log('gateway_status: invalid or empty API response');
    exit(1);
}

$last_seen      = date('Y-m-d H:i:s', strtotime($res['lastSeenAt']));
$timenow        = time();
$last_timestamp = strtotime($last_seen);
$str_timenow    = date('Y-m-d H:i:s', $timenow);

$status_file = __DIR__ . '/network_status.txt';
$log_file    = __DIR__ . '/logs/Network_Log.txt';

$size     = is_file($status_file) ? filesize($status_file) : 0;
$myfile   = @fopen($status_file, 'r');
$flag     = 1; // default: assume previously ON

if ($myfile && $size > 0) {
    $last_state = fread($myfile, $size);
    fclose($myfile);
    $flag = (trim($last_state) === 'ON') ? 1 : 0;
} elseif ($myfile) {
    fclose($myfile);
}

if ($timenow - $last_timestamp > 900) {
    if ($flag == 1) {
        $status = 0;
        include __DIR__ . '/email_alerts/network_email_alert.php';
        $lf = fopen($log_file, 'a');
        if ($lf) { fwrite($lf, $last_seen . " | OFFLINE\n"); fclose($lf); }
    }
    file_put_contents($status_file, 'OFF');
    echo $last_seen . ' | OFFLINE';
} else {
    if ($flag == 0) {
        $status = 1;
        include __DIR__ . '/email_alerts/network_email_alert.php';
        $lf = fopen($log_file, 'a');
        if ($lf) { fwrite($lf, $str_timenow . " | ONLINE\n"); fclose($lf); }
    }
    file_put_contents($status_file, 'ON');
    echo $last_seen . ' | ONLINE';
}
