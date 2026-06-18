<?php
/**
 * Fire Alarm Panel state from the UC300 GPIO input (gpio_in_1).
 *   gpio_in_1 = "on"  / 1  -> status 1 (alarm)
 *   gpio_in_1 = "off" / 0  -> status 0 (normal)
 *
 * Recording rules (keeps tbl_main_FAP compact but auditable):
 *   - State CHANGE        -> insert a new row immediately.
 *   - New hour, same state -> insert a new hourly heartbeat row (10h, 11h, 12h ...).
 *   - Same hour, same state -> just refresh the existing row's timestamp.
 *
 * NOTE: if your panel wiring is inverted (closed = normal), flip the mapping.
 */

$dev_eui = $data['deviceInfo']['devEui'] ?? '';
$object  = is_array($data['object'] ?? null) ? $data['object'] : [];

if ($dev_eui === '' || !array_key_exists('gpio_in_1', $object)) {
    return;
}

$raw = $object['gpio_in_1'];
if (is_string($raw)) {
    $status = in_array(strtolower(trim($raw)), ['on', '1', 'true'], true) ? 1 : 0;
} else {
    $status = ((int)$raw !== 0) ? 1 : 0;
}

// Latest recorded reading for this panel
$last = $pdo->prepare(
    'SELECT `id`, `status`, `date` FROM `tbl_main_FAP` WHERE `dev_eui` = ? ORDER BY `id` DESC LIMIT 1'
);
$last->execute([$dev_eui]);
$prev = $last->fetch();

$insertRow = static function () use ($pdo, $timenow, $dev_eui, $status) {
    $pdo->prepare(
        'INSERT INTO `tbl_main_FAP` (`date`, `dev_eui`, `status`) VALUES (?, ?, ?)'
    )->execute([$timenow, $dev_eui, $status]);
};

// First ever reading, or a state change -> new row
if (!$prev || (int)$prev['status'] !== $status) {
    $insertRow();
    return;
}

// Same state: one row per hour. New hour -> new row, same hour -> refresh time.
$prevHour = date('Y-m-d H', strtotime($prev['date']));
$nowHour  = date('Y-m-d H', strtotime($timenow));

if ($prevHour !== $nowHour) {
    $insertRow();
} else {
    $pdo->prepare('UPDATE `tbl_main_FAP` SET `date` = ? WHERE `id` = ?')
        ->execute([$timenow, $prev['id']]);
}
