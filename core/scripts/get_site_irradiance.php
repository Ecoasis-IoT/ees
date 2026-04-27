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
        "SELECT TIME(date) as time, irradiance, ambient_temp, panel_temp
         FROM plant_irradiance
         WHERE DATE(date) = DATE(:date)
         ORDER BY date ASC"
    );
    $stmt->execute([':date' => $date]);
    ob_end_clean();
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    error_log("get_site_irradiance error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
