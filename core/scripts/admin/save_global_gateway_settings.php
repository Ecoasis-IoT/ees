<?php
/**
 * @deprecated Use save_settings.php with setting_group=gateway
 */
$_POST['setting_group'] = 'gateway';
require __DIR__ . '/save_settings.php';
