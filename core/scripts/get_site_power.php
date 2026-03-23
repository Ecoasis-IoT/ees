<?php
ob_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

// Guard: must be an XMLHttpRequest (jQuery sends this automatically)
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    ob_end_clean(); echo json_encode(['status' => 'Err']); exit;
}

$site_db = trim($_POST['site_db'] ?? 'phoenix_mall.php');
$timenow = date('Y-m-d H:i');

$pdo = getDB(ees_db_key($site_db));

try {
    $stmt = $pdo->prepare(
        "(SELECT ROUND(SUM(production),2) as data
          FROM tbl_hourly_prod
          WHERE meter_id = 100 AND DATE(datetime) = DATE(:now))
         UNION ALL
         (SELECT IFNULL(active_power,0)
          FROM plant_active_power
          WHERE DATE(date) = DATE(:now)
          ORDER BY date DESC LIMIT 1)"
    );
    $stmt->execute([':now' => $timenow]);
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);

    ob_end_clean();
    if (count($rows) === 2) {
        echo json_encode([
            'ref'          => pathinfo($site_db, PATHINFO_FILENAME),
            'prod'         => round((float)$rows[0][0], 2),
            'active_power' => round((float)$rows[1][0], 2),
        ]);
    } else {
        echo json_encode(['ref' => pathinfo($site_db, PATHINFO_FILENAME), 'prod' => 0, 'active_power' => 0]);
    }
} catch (PDOException $e) {
    error_log("get_site_power error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
