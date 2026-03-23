<?php
/**
 * SQL Security Helper
 * Whitelist-based validation for dynamic table/column identifiers
 * that cannot be bound as PDO parameters.
 */

/**
 * Validate that a table name is in the allowed whitelist.
 *
 * @param  string   $table_name  Candidate table name
 * @param  string[] $whitelist   Allowed table names
 * @return string   The validated table name
 * @throws InvalidArgumentException
 */
function validateTableName(string $table_name, array $whitelist): string {
    if (!in_array($table_name, $whitelist, true)) {
        throw new InvalidArgumentException("Invalid table name: " . htmlspecialchars($table_name, ENT_QUOTES, 'UTF-8'));
    }
    return $table_name;
}

/**
 * Validate that a column name matches a safe identifier pattern
 * and is present in the optional whitelist.
 *
 * @param  string        $column_name  Candidate column name
 * @param  string[]|null $whitelist    Optional list of allowed column names
 * @return string   The validated column name
 * @throws InvalidArgumentException
 */
function validateColumnName(string $column_name, ?array $whitelist = null): string {
    // Only allow alphanumeric and underscores (no injection vectors)
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column_name)) {
        throw new InvalidArgumentException("Invalid column name: " . htmlspecialchars($column_name, ENT_QUOTES, 'UTF-8'));
    }

    if ($whitelist !== null && !in_array($column_name, $whitelist, true)) {
        throw new InvalidArgumentException("Column not allowed: " . htmlspecialchars($column_name, ENT_QUOTES, 'UTF-8'));
    }

    return $column_name;
}

/**
 * Validate an ORDER BY direction.
 *
 * @param  string $direction  'ASC' or 'DESC'
 * @return string
 * @throws InvalidArgumentException
 */
function validateSortDirection(string $direction): string {
    $direction = strtoupper(trim($direction));
    if (!in_array($direction, ['ASC', 'DESC'], true)) {
        throw new InvalidArgumentException("Invalid sort direction: " . htmlspecialchars($direction, ENT_QUOTES, 'UTF-8'));
    }
    return $direction;
}
