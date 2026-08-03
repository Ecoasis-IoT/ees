<?php
/**
 * Phoenix cron bootstrap.
 * Usage: require __DIR__ . '/phoenix_config.php'; then use $pdo.
 */
require_once __DIR__ . '/../config.php';

$pdo = getDB('phoenix');
