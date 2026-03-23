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

$site_id     = intval($_POST['site']         ?? 0);
$meters      = trim($_POST['meters']         ?? '');
$arr_meters  = $_POST['arr_meters']          ?? [];
$param       = trim($_POST['param']          ?? '');
$start       = trim($_POST['start_date']     ?? '');
$end         = trim($_POST['end_date']       ?? '');
$irradiance  = intval($_POST['irradiance']   ?? 0);
$ambientTemp = intval($_POST['ambientTemp']  ?? 0);
$panelTemp   = intval($_POST['panelTemp']    ?? 0);

if (!$site_id || empty($start) || empty($end)) {
    ob_end_clean(); echo json_encode(['status' => 'Err']); exit;
}

$safe_meters = array_filter(array_map('intval', explode(',', $meters)), fn($v) => $v > 0);
$safe_arr    = array_filter(array_map('intval', (array)$arr_meters),    fn($v) => $v > 0);
$safe_arr    = array_values($safe_arr);

$adm  = getDB('admin');
$stmt = $adm->prepare("SELECT site_name, db_name, capacity FROM tbl_site WHERE id = :id");
$stmt->execute([':id' => $site_id]);
$site = $stmt->fetch();
if (!$site) { ob_end_clean(); echo json_encode(['status' => 'Err']); exit; }

$pdo  = getDB(ees_db_key($site['db_name']));
$data = [];

try {
    if ($param === 'prod' && !empty($safe_meters)) {
        $ph = implode(',', array_fill(0, count($safe_meters), '?'));
        $s  = $pdo->prepare(
            "SELECT datetime, meter_id, meter_name, production
             FROM tbl_hourly_prod
             WHERE meter_id IN ($ph) AND DATE(datetime) >= DATE(?) AND DATE(datetime) <= DATE(?)
             ORDER BY datetime ASC"
        );
        $s->execute([...array_values($safe_meters), $start, $end]);
        array_push($data, ...$s->fetchAll());
    }

    if ($param === 'a_power' && !empty($safe_arr)) {
        $working_arr = $safe_arr;

        if ($site_id === 7780) {
            foreach ([100, 101] as $mm) {
                $key = array_search($mm, $working_arr);
                if ($key !== false) {
                    $s = $pdo->prepare(
                        "SELECT date as datetime, meter_id, meter_name,
                                IF(active_power < 0, 0, ROUND(active_power,2)) as active_power
                         FROM plant_active_power
                         WHERE DATE(date) >= DATE(?) AND DATE(date) <= DATE(?) AND meter_id = ?
                         ORDER BY date ASC"
                    );
                    $s->execute([$start, $end, $mm]);
                    array_push($data, ...$s->fetchAll());
                    array_splice($working_arr, $key, 1);
                    $working_arr = array_values($working_arr);
                }
            }
        } else {
            $key = array_search(100, $working_arr);
            if ($key !== false) {
                $s = $pdo->prepare(
                    "SELECT date as datetime, '100' as meter_id, 'Main Meter' as meter_name,
                            IF(active_power < 0, 0, ROUND(active_power,2)) as active_power
                     FROM plant_active_power
                     WHERE DATE(date) >= DATE(?) AND DATE(date) <= DATE(?)
                     ORDER BY date ASC"
                );
                $s->execute([$start, $end]);
                array_push($data, ...$s->fetchAll());
                array_splice($working_arr, $key, 1);
                $working_arr = array_values($working_arr);
            }
        }

        if (!empty($working_arr)) {
            $ph = implode(',', array_fill(0, count($working_arr), '?'));
            $s  = $pdo->prepare(
                "SELECT date as datetime, meter_id, meter_name, active_power
                 FROM tbl_sub_meters
                 WHERE meter_id IN ($ph) AND DATE(date) >= DATE(?) AND DATE(date) <= DATE(?)
                 ORDER BY date ASC"
            );
            $s->execute([...array_values($working_arr), $start, $end]);
            array_push($data, ...$s->fetchAll());
        }
    }

    if ($irradiance) {
        $s = $pdo->prepare(
            "SELECT date as datetime, '99' as meter_id, irradiance
             FROM plant_irradiance WHERE DATE(date) >= DATE(?) AND DATE(date) <= DATE(?) ORDER BY date ASC"
        );
        $s->execute([$start, $end]);
        array_push($data, ...$s->fetchAll());
    }

    if ($ambientTemp) {
        $s = $pdo->prepare(
            "SELECT date as datetime, '99' as meter_id, ambient_temp
             FROM plant_irradiance WHERE DATE(date) >= DATE(?) AND DATE(date) <= DATE(?) ORDER BY date ASC"
        );
        $s->execute([$start, $end]);
        array_push($data, ...$s->fetchAll());
    }

    if ($panelTemp) {
        $s = $pdo->prepare(
            "SELECT date as datetime, '99' as meter_id, panel_temp
             FROM plant_irradiance WHERE DATE(date) >= DATE(?) AND DATE(date) <= DATE(?) ORDER BY date ASC"
        );
        $s->execute([$start, $end]);
        array_push($data, ...$s->fetchAll());
    }

    ob_end_clean();
    echo json_encode(['site_name' => $site['site_name'], 'data' => $data]);
} catch (PDOException $e) {
    error_log("query_custom error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['status' => 'Err']);
}
