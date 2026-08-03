<?php
/**
 * Shared WHERE clause builder for audit log queries (DataTables + CSV export).
 *
 * @return array{where: string, params: array<string, mixed>}
 */
function ees_audit_logs_build_filters(array $input): array
{
    $search = trim($input['search'] ?? '');
    if ($search === '' && isset($input['search']['value'])) {
        $search = trim((string)$input['search']['value']);
    }

    $filter_category  = trim($input['filter_category'] ?? '');
    $filter_action    = trim($input['filter_action'] ?? '');
    $filter_severity  = trim($input['filter_severity'] ?? '');
    $filter_ip        = trim($input['filter_ip'] ?? '');
    $filter_date_from = trim($input['filter_date_from'] ?? '');
    $filter_date_to   = trim($input['filter_date_to'] ?? '');

    $where  = '1=1';
    $params = [];

    if ($search !== '') {
        $where .= " AND (action LIKE :search OR username LIKE :search OR email LIKE :search
                       OR ip_address LIKE :search OR message LIKE :search OR resource LIKE :search
                       OR details LIKE :search OR category LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    if ($filter_category !== '') {
        $where .= " AND category = :category";
        $params[':category'] = $filter_category;
    }
    if ($filter_action !== '') {
        $where .= " AND action LIKE :action";
        $params[':action'] = '%' . $filter_action . '%';
    }
    if ($filter_severity !== '') {
        $where .= " AND severity = :severity";
        $params[':severity'] = $filter_severity;
    }
    if ($filter_ip !== '') {
        $where .= " AND ip_address LIKE :ip";
        $params[':ip'] = '%' . $filter_ip . '%';
    }
    if ($filter_date_from !== '') {
        $where .= " AND created_at >= :dfrom";
        $params[':dfrom'] = $filter_date_from . ' 00:00:00';
    }
    if ($filter_date_to !== '') {
        $where .= " AND created_at <= :dto";
        $params[':dto'] = $filter_date_to . ' 23:59:59';
    }

    return ['where' => $where, 'params' => $params];
}

function ees_audit_logs_user_label(array $log): string
{
    $label = $log['username'] ?: ($log['email'] ?: '');
    if ($label === '' && !empty($log['user_id'])) {
        $label = 'User #' . $log['user_id'];
    }
    return $label;
}
