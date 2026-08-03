<?php
/**
 * Global application settings (tbl_settings key-value store).
 */

function ees_settings_table_ready(PDO $pdo): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        $pdo->query('SELECT setting_key FROM tbl_settings LIMIT 1');
        $ready = true;
    } catch (PDOException $e) {
        $ready = false;
    }

    return $ready;
}

function ees_setting_get(PDO $pdo, string $key, $default = null)
{
    if (!ees_settings_table_ready($pdo)) {
        return $default;
    }

    try {
        $stmt = $pdo->prepare('SELECT setting_value FROM tbl_settings WHERE setting_key = :key LIMIT 1');
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && array_key_exists('setting_value', $row)) {
            return $row['setting_value'];
        }
    } catch (PDOException $e) {
        error_log('ees_setting_get: ' . $e->getMessage());
    }

    return $default;
}

function ees_setting_set(
    PDO $pdo,
    string $key,
    ?string $value,
    string $group = 'general',
    ?string $label = null,
    ?string $description = null
): bool {
    $stmt = $pdo->prepare(
        'INSERT INTO tbl_settings (setting_key, setting_value, setting_group, label, description)
         VALUES (:key, :value, :grp, :label, :description)
         ON DUPLICATE KEY UPDATE
             setting_value = VALUES(setting_value),
             setting_group = VALUES(setting_group),
             label = COALESCE(VALUES(label), label),
             description = COALESCE(VALUES(description), description)'
    );

    return $stmt->execute([
        ':key'         => $key,
        ':value'       => $value,
        ':grp'         => $group,
        ':label'       => $label,
        ':description' => $description,
    ]);
}

function ees_settings_get_group(PDO $pdo, string $group): array
{
    if (!ees_settings_table_ready($pdo)) {
        return [];
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT setting_key, setting_value, label, description
             FROM tbl_settings
             WHERE setting_group = :grp
             ORDER BY setting_key ASC'
        );
        $stmt->execute([':grp' => $group]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out  = [];
        foreach ($rows as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        return $out;
    } catch (PDOException $e) {
        error_log('ees_settings_get_group: ' . $e->getMessage());
        return [];
    }
}

function ees_settings_set_many(PDO $pdo, string $group, array $values, array $meta = []): void
{
    foreach ($values as $key => $value) {
        $info = $meta[$key] ?? [];
        ees_setting_set(
            $pdo,
            $key,
            $value === null ? null : (string)$value,
            $group,
            $info['label'] ?? null,
            $info['description'] ?? null
        );
    }
}

function ees_settings_migration_message(): string
{
    return 'Run database/settings_migration.sql on the admin database first.';
}
