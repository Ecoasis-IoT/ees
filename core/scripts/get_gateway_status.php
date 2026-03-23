<?php
ob_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';

header('Content-Type: application/json; charset=utf-8');

$file = __DIR__ . '/../../cron/network_status.txt';

if (!file_exists($file)) {
    ob_end_clean();
    echo json_encode(['status' => 'noFile']);
    exit;
}

$size = filesize($file);

if ($size <= 0) {
    ob_end_clean();
    echo json_encode(['status' => 'emptyFile']);
    exit;
}

$fh    = fopen($file, 'r');
$state = $fh ? trim(fread($fh, $size)) : '';
if ($fh) fclose($fh);

ob_end_clean();
if ($state === 'ON') {
    echo json_encode(['status' => 'connected']);
} elseif ($state === 'OFF') {
    echo json_encode(['status' => 'disconnected']);
} else {
    echo json_encode(['status' => 'unknown']);
}
