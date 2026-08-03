<?php
ob_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db_key_helper.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * @return never
 */
function archive_fail(string $message): void {
    ob_end_clean();
    echo json_encode(['status' => 'Err', 'message' => $message]);
    exit;
}

// Guard: must be an XMLHttpRequest (jQuery sends this automatically)
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    archive_fail('Invalid request.');
}

$site_id    = intval($_POST['site']       ?? 0);
$start_date = trim($_POST['start_date']   ?? '');
$end_date   = trim($_POST['end_date']     ?? '');

if (!$site_id || $start_date === '' || $end_date === '') {
    archive_fail('Please select a site and a full date range.');
}

$adm  = getDB('admin');
$stmt = $adm->prepare("SELECT site_name, db_name FROM tbl_site WHERE id = :id");
$stmt->execute([':id' => $site_id]);
$site = $stmt->fetch();
if (!$site) {
    archive_fail('That site was not found in the directory.');
}

$db_key = ees_db_key($site['db_name']);
$pdo    = tryGetDB($db_key);
if (!$pdo) {
    error_log('get_archive_data: no PDO for site_id=' . $site_id . ' db_name=' . $site['db_name'] . ' resolved_key=' . $db_key);
    archive_fail(
        'Archive database for this site is not configured or cannot be reached. Verify tbl_site.db_name, the mapping in db_key_helper, and .env credentials for that site.'
    );
}

try {
    $s = $pdo->prepare(
        "SELECT date, production, insolation FROM tbl_archive
         WHERE date >= :start AND date <= :end"
    );
    $s->execute([':start' => $start_date, ':end' => $end_date]);
    ob_end_clean();
    echo json_encode(['site_name' => $site['site_name'], 'archive' => $s->fetchAll()]);
} catch (PDOException $e) {
    error_log("get_archive_data error: " . $e->getMessage());
    archive_fail('Could not read archive data (database error). Check that tbl_archive exists and date column types match.');
}
