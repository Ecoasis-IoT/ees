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
        "SELECT meter_id, meter_name, TIME(date) as time,
                IF(active_power < 0, 0, ROUND(active_power,2)) as active_power
         FROM plant_active_power
         WHERE DATE(date) = DATE(:date)
         ORDER BY date ASC"
    );
    $stmt->execute([':date' => $date]);
    ob_end_clean();
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    error_log("get_site_active_powerv3 error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
