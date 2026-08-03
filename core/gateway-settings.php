<?php
require_once __DIR__ . '/../config.php';
header('Location: ' . ees_url_path('admin-settings.php') . '#tab-sites', true, 302);
exit;
