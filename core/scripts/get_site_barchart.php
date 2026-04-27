<?php
ob_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

$site_db = trim($_POST['site_db'] ?? '');
$date    = trim($_POST['date']    ?? date('Y-m-d'));

if (empty($site_db)) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$pdo = getDB(ees_db_key($site_db));

try {
    $stmt = $pdo->prepare(
        "SELECT TIME_FORMAT(TIME(DATETIME),'%H:%i:%s') AS time,
                ROUND(SUM(production),2) as production
         FROM tbl_hourly_prod
         WHERE meter_id >= 100 AND DATE(DATETIME) = :date
         GROUP BY TIME(DATETIME)
         ORDER BY DATETIME ASC"
    );
    $stmt->execute([':date' => $date]);
    ob_end_clean();
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    error_log("get_site_barchart error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
