<?php
/**
 * Dashboard data: site list with today's production and active power.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

$admin_pdo = getDB('admin');

try {
    $stmt  = $admin_pdo->query("SELECT `id`, `site_name`, `db_name`, `location`, `commissioned` FROM `tbl_site` ORDER BY `id`");
    $sites = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("get_dashboard admin query error: " . $e->getMessage());
    echo json_encode([]);
    exit;
}

$timenow = date('Y-m-d');

foreach ($sites as &$site) {
    $dk = ees_db_key($site['db_name']);
    if ($dk === 'rtm') {
        $site['dashboard_href'] = 'site-dashboardv3';
    } elseif ($dk === 'bovalon') {
        $site['dashboard_href'] = 'site-dashboardv2';
    } elseif ($dk === 'moka_city' || $dk === 'case_noyal') {
        $site['dashboard_href'] = 'site-dashboardv4';
    } else {
        $site['dashboard_href'] = 'site-dashboard';
    }

    if ((int)$site['commissioned'] !== 1) {
        $site['prod']         = 0;
        $site['active_power'] = 0;
        continue;
    }

    $db_key   = ees_db_key($site['db_name']);
    $site_pdo = tryGetDB($db_key);

    if (!$site_pdo) {
        $site['prod']         = 0;
        $site['active_power'] = 0;
        continue;
    }

    try {
        $sql = "
            (SELECT ROUND(COALESCE(SUM(production), 0), 2) AS data
             FROM tbl_hourly_prod
             WHERE meter_id >= 100 AND DATE(datetime) = :dt)
            UNION ALL
            (SELECT COALESCE(active_power, 0)
             FROM plant_active_power
             WHERE DATE(date) = :dt2
             ORDER BY date DESC
             LIMIT 1)
        ";

        $q = $site_pdo->prepare($sql);
        $q->execute([':dt' => $timenow, ':dt2' => $timenow]);
        $rows = $q->fetchAll(PDO::FETCH_NUM);

        $site['prod']         = round((float)($rows[0][0] ?? 0), 2);
        $site['active_power'] = round((float)($rows[1][0] ?? 0), 2);
    } catch (PDOException $e) {
        error_log("get_dashboard site [{$site['db_name']}] error: " . $e->getMessage());
        $site['prod']         = 0;
        $site['active_power'] = 0;
    }
}
unset($site);

echo json_encode($sites);
