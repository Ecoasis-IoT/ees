<?php
/**
 * ChirpStack gateway polling helpers.
 */

require_once __DIR__ . '/app_settings.php';

function ees_chirpstack_env_token_key(string $db_name): ?string
{
    static $map = [
        'phoenix_mall.php'  => 'CHIRPSTACK_TOKEN_PHOENIX',
        'home_leisure.php'  => 'CHIRPSTACK_TOKEN_HOME_LEISURE',
        'bovalon_mall.php'  => 'CHIRPSTACK_TOKEN_BOVALON',
        'r_terre_mall.php'  => 'CHIRPSTACK_TOKEN_RTM',
        'gob_config.php'    => 'CHIRPSTACK_TOKEN_GOB',
        'pod_config.php'    => 'CHIRPSTACK_TOKEN_POD',
        'factory.php'       => 'CHIRPSTACK_TOKEN_FACTORY',
        'helvetia.php'      => 'CHIRPSTACK_TOKEN_MOKA_CITY',
        'moka_city.php'     => 'CHIRPSTACK_TOKEN_MOKA_CITY',
        'case_noyal.php'    => 'CHIRPSTACK_TOKEN_CASE_NOYAL',
    ];

    return $map[$db_name] ?? null;
}

function ees_chirpstack_resolve_token(string $db_name, ?string $db_token): ?string
{
    $db_token = trim((string)$db_token);
    if ($db_token !== '') {
        return $db_token;
    }

    $key = ees_chirpstack_env_token_key($db_name);
    if (!$key) {
        return null;
    }

    $env = getenv($key);
    if ($env === false || trim($env) === '') {
        return null;
    }

    return trim($env);
}

function ees_chirpstack_schema_ready(PDO $pdo): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        $pdo->query('SELECT gateway_eui FROM tbl_site LIMIT 1');
        if (!ees_settings_table_ready($pdo)) {
            $ready = false;
            return $ready;
        }
        $ready = true;
    } catch (PDOException $e) {
        $ready = false;
    }

    return $ready;
}

function ees_chirpstack_migrate_legacy_table(PDO $pdo): void
{
    try {
        $pdo->query('SELECT api_url FROM tbl_chirpstack_settings LIMIT 1');
    } catch (PDOException $e) {
        return;
    }

    try {
        $row = $pdo->query('SELECT api_url, tenant_id, offline_threshold_seconds FROM tbl_chirpstack_settings WHERE id = 1 LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }
        ees_settings_set_many($pdo, 'gateway', [
            'chirpstack.api_url'                   => $row['api_url'],
            'chirpstack.tenant_id'                 => $row['tenant_id'],
            'chirpstack.offline_threshold_seconds' => (string)($row['offline_threshold_seconds'] ?? 900),
        ]);
    } catch (PDOException $e) {
        error_log('chirpstack_migrate_legacy: ' . $e->getMessage());
    }
}

function ees_chirpstack_get_settings(PDO $pdo): array
{
    $defaults = [
        'api_url'                   => 'http://195.35.48.27:8090',
        'tenant_id'                 => '',
        'offline_threshold_seconds' => 900,
    ];

    if (!ees_chirpstack_schema_ready($pdo)) {
        return $defaults;
    }

    ees_chirpstack_migrate_legacy_table($pdo);

    try {
        return [
            'api_url'                   => ees_setting_get($pdo, 'chirpstack.api_url', $defaults['api_url']),
            'tenant_id'                 => ees_setting_get($pdo, 'chirpstack.tenant_id', $defaults['tenant_id']) ?? '',
            'offline_threshold_seconds' => (int)ees_setting_get($pdo, 'chirpstack.offline_threshold_seconds', $defaults['offline_threshold_seconds']),
        ];
    } catch (PDOException $e) {
        error_log('chirpstack_get_settings: ' . $e->getMessage());
    }

    return $defaults;
}

function ees_chirpstack_save_settings(PDO $pdo, string $api_url, string $tenant_id, int $offline_threshold_seconds): bool
{
    ees_settings_set_many($pdo, 'gateway', [
        'chirpstack.api_url'                   => rtrim($api_url, '/'),
        'chirpstack.tenant_id'                 => $tenant_id !== '' ? $tenant_id : null,
        'chirpstack.offline_threshold_seconds' => (string)max(60, $offline_threshold_seconds),
    ], [
        'chirpstack.api_url' => [
            'label'       => 'ChirpStack API URL',
            'description' => 'Base URL for ChirpStack REST API',
        ],
        'chirpstack.tenant_id' => [
            'label'       => 'ChirpStack Tenant ID',
            'description' => 'Optional tenant UUID for listing gateways',
        ],
        'chirpstack.offline_threshold_seconds' => [
            'label'       => 'Offline threshold (seconds)',
            'description' => 'Gateway offline if lastSeenAt is older than this',
        ],
    ]);

    return true;
}

function ees_chirpstack_is_online(?string $last_seen_at, int $threshold_seconds): bool
{
    if ($last_seen_at === null || trim($last_seen_at) === '') {
        return false;
    }

    $ts = strtotime($last_seen_at);
    if ($ts === false) {
        return false;
    }

    return (time() - $ts) <= $threshold_seconds;
}

function ees_chirpstack_fetch_gateway(string $api_url, string $gateway_eui, string $token): array
{
    $gateway_eui = strtolower(trim($gateway_eui));
    $token       = trim($token);

    if ($gateway_eui === '' || $token === '') {
        return ['ok' => false, 'error' => 'Gateway EUI and API token are required'];
    }

    $url = rtrim($api_url, '/') . '/api/gateways/' . rawurlencode($gateway_eui);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Grpc-Metadata-Authorization: Bearer ' . $token,
        ],
    ]);

    $result   = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr !== '') {
        return ['ok' => false, 'error' => 'cURL error: ' . $curlErr];
    }

    $data = json_decode((string)$result, true);
    if ($httpCode !== 200 || !is_array($data)) {
        $msg = is_array($data) && isset($data['message']) ? $data['message'] : ('HTTP ' . $httpCode);
        return ['ok' => false, 'error' => $msg, 'http_code' => $httpCode];
    }

    $lastSeenRaw = $data['lastSeenAt'] ?? null;
    $lastSeen    = null;
    if ($lastSeenRaw) {
        $lastSeen = date('Y-m-d H:i:s', strtotime($lastSeenRaw));
    }

    return [
        'ok'        => true,
        'last_seen' => $lastSeen,
        'state'     => $data['state'] ?? null,
        'name'      => $data['name'] ?? null,
    ];
}

function ees_chirpstack_poll_site(PDO $pdo, array $site, ?array $settings = null, bool $persist = true): array
{
    $settings = $settings ?? ees_chirpstack_get_settings($pdo);
    $siteId   = (int)($site['id'] ?? 0);
    $dbName   = (string)($site['db_name'] ?? '');
    $eui      = trim((string)($site['gateway_eui'] ?? ''));
    $enabled  = (int)($site['gateway_poll_enabled'] ?? 0) === 1;
    $token    = ees_chirpstack_resolve_token($dbName, $site['chirpstack_token'] ?? null);

    $base = [
        'site_id'   => $siteId,
        'site_name' => $site['site_name'] ?? '',
        'db_name'   => $dbName,
        'gateway_eui' => $eui,
    ];

    if (!$enabled) {
        return array_merge($base, ['ok' => false, 'skipped' => true, 'error' => 'Polling disabled']);
    }

    if ($eui === '') {
        return array_merge($base, ['ok' => false, 'skipped' => true, 'error' => 'Gateway EUI not configured']);
    }

    if ($token === null) {
        return array_merge($base, ['ok' => false, 'skipped' => true, 'error' => 'No API token (DB or .env)']);
    }

    $fetch = ees_chirpstack_fetch_gateway(
        (string)$settings['api_url'],
        $eui,
        $token
    );

    if (!$fetch['ok']) {
        return array_merge($base, $fetch);
    }

    $threshold = (int)($settings['offline_threshold_seconds'] ?? 900);
    $online      = ees_chirpstack_is_online($fetch['last_seen'] ?? null, $threshold);
    $statusValue = $online ? 1 : 0;

    if ($persist && $siteId > 0) {
        $stmt = $pdo->prepare(
            'UPDATE tbl_site
             SET gateway_status = :status,
                 gateway_last_seen = :last_seen
             WHERE id = :id'
        );
        $stmt->execute([
            ':status'    => $statusValue,
            ':last_seen' => $fetch['last_seen'],
            ':id'        => $siteId,
        ]);
    }

    return array_merge($base, [
        'ok'             => true,
        'online'         => $online,
        'gateway_status' => $statusValue,
        'last_seen'      => $fetch['last_seen'],
        'state'          => $fetch['state'] ?? null,
        'name'           => $fetch['name'] ?? null,
    ]);
}

function ees_chirpstack_poll_all(PDO $pdo, bool $persist = true): array
{
    if (!ees_chirpstack_schema_ready($pdo)) {
        return [];
    }

    $settings = ees_chirpstack_get_settings($pdo);
    $stmt     = $pdo->query(
        'SELECT id, site_name, db_name, gateway_eui, chirpstack_token, gateway_poll_enabled, gateway_status
         FROM tbl_site
         ORDER BY id'
    );
    $sites   = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $results = [];

    foreach ($sites as $site) {
        $results[] = ees_chirpstack_poll_site($pdo, $site, $settings, $persist);
    }

    return $results;
}

function ees_chirpstack_sync_phoenix_legacy_file(array $pollResult): void
{
    if (($pollResult['db_name'] ?? '') !== 'phoenix_mall.php' || empty($pollResult['ok'])) {
        return;
    }

    $status_file = dirname(__DIR__, 2) . '/cron/network_status.txt';
    $log_file    = dirname(__DIR__, 2) . '/cron/logs/Network_Log.txt';
    $online      = !empty($pollResult['online']);
    $last_seen   = $pollResult['last_seen'] ?? '';
    $str_timenow = date('Y-m-d H:i:s');

    $prev = 'ON';
    if (is_file($status_file) && filesize($status_file) > 0) {
        $prev = trim((string)file_get_contents($status_file));
    }

    $newState = $online ? 'ON' : 'OFF';
    if ($prev === 'ON' && !$online) {
        $status = 0;
        include dirname(__DIR__, 2) . '/cron/email_alerts/network_email_alert.php';
        $lf = @fopen($log_file, 'a');
        if ($lf) {
            fwrite($lf, $last_seen . " | OFFLINE\n");
            fclose($lf);
        }
    } elseif ($prev === 'OFF' && $online) {
        $status = 1;
        include dirname(__DIR__, 2) . '/cron/email_alerts/network_email_alert.php';
        $lf = @fopen($log_file, 'a');
        if ($lf) {
            fwrite($lf, $str_timenow . " | ONLINE\n");
            fclose($lf);
        }
    }

    file_put_contents($status_file, $newState);
}
