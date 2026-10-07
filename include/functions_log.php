<?php
/**
 * Audit Logging Functions for Proteas
 */

/**
 * Logs an action (add, edit, delete) on permanent or substitute employees.
 *
 * @param mysqli $db MySQLi connection
 * @param int $empId Affected employee ID
 * @param int $userId ID of user performing the action
 * @param string $table Table name ('employee' or 'ektaktoi')
 * @param string $action 'add' | 'edit' | 'delete'
 * @param int|null $empType 1 for permanent (employee), 2 for substitute (ektaktoi)
 * @param string|null $sql Human readable summary or raw SQL query
 * @param array|null $oldValues Array of values before modification
 * @param array|null $newValues Array of values after modification
 * @return bool
 */
function employeeAuditLog(
    mysqli $db,
    int $empId,
    int $userId,
    string $table,
    string $action,
    ?int $empType = null,
    ?string $sql = '',
    ?array $oldValues = null,
    ?array $newValues = null
): bool {
    if (!$db) {
        return false;
    }

    // Auto-detect empType if not specified
    if ($empType === null) {
        $empType = ($table === 'ektaktoi' || $table === 'employee_ekt') ? 2 : 1;
    }

    if ($table === 'employee_ekt') {
        $table = 'ektaktoi';
    }

    $action = strtolower(trim($action));
    if (!in_array($action, ['add', 'edit', 'delete'], true)) {
        $action = 'edit';
    }

    $affectedFields = null;
    $changedOld = null;
    $changedNew = null;
    $diffSummary = [];

    // Fields to exclude from diff logging (noise or sensitive)
    $ignoredFields = ['updated', 'email_psd'];

    if ($action === 'edit' && is_array($oldValues) && is_array($newValues)) {
        foreach ($newValues as $key => $newVal) {
            if (in_array($key, $ignoredFields, true)) {
                continue;
            }
            $oldVal = $oldValues[$key] ?? null;

            // Normalize nulls and trim strings for fair comparison
            $oldNormalized = is_string($oldVal) ? trim($oldVal) : $oldVal;
            $newNormalized = is_string($newVal) ? trim($newVal) : $newVal;

            if ((string)$oldNormalized !== (string)$newNormalized) {
                if ($affectedFields === null) {
                    $affectedFields = [];
                    $changedOld = [];
                    $changedNew = [];
                }
                $affectedFields[] = $key;
                $changedOld[$key] = $oldVal;
                $changedNew[$key] = $newVal;

                if ($key === 'sx_yphrethshs' || $key === 'sx_organikhs') {
                    $schOld = getSchoolNameCached((int)$oldVal, $db);
                    $schNew = getSchoolNameCached((int)$newVal, $db);
                    $oldLabel = $schOld !== '' ? $schOld : ($oldVal !== null && $oldVal !== '' ? $oldVal : '[κενό]');
                    $newLabel = $schNew !== '' ? $schNew : ($newVal !== null && $newVal !== '' ? $newVal : '[κενό]');
                    $diffSummary[] = "$key: $oldLabel -> $newLabel";
                } else {
                    $diffSummary[] = "$key: " . ($oldVal !== null && $oldVal !== '' ? $oldVal : '[κενό]') . ' -> ' . ($newVal !== null && $newVal !== '' ? $newVal : '[κενό]');
                }
            }
        }
    } elseif ($action === 'add' && is_array($newValues)) {
        $changedNew = [];
        foreach ($newValues as $k => $v) {
            if (!in_array($k, $ignoredFields, true)) {
                $changedNew[$k] = $v;
            }
        }
    } elseif ($action === 'delete' && is_array($oldValues)) {
        $changedOld = [];
        foreach ($oldValues as $k => $v) {
            if (!in_array($k, $ignoredFields, true)) {
                $changedOld[$k] = $v;
            }
        }
    }

    // If sql/query text is empty, generate an informative message
    $queryText = trim((string)$sql);
    if ($queryText === '') {
        if ($action === 'edit') {
            $queryText = !empty($diffSummary) ? implode(", ", $diffSummary) : 'Ενημέρωση εγγραφής (χωρίς μεταβολές)';
        } elseif ($action === 'add') {
            $queryText = 'Προσθήκη εγγραφής';
        } elseif ($action === 'delete') {
            $queryText = 'Διαγραφή εγγραφής';
        }
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    if (strlen($ip) > 45) {
        $ip = substr($ip, 0, 45);
    }

    $affJson = ($affectedFields !== null && count($affectedFields) > 0) 
        ? json_encode($affectedFields, JSON_UNESCAPED_UNICODE) 
        : null;
    $oldJson = ($changedOld !== null && count($changedOld) > 0) 
        ? json_encode($changedOld, JSON_UNESCAPED_UNICODE) 
        : null;
    $newJson = ($changedNew !== null && count($changedNew) > 0) 
        ? json_encode($changedNew, JSON_UNESCAPED_UNICODE) 
        : null;

    $stmt = $db->prepare("
        INSERT INTO employee_log
        (emp_type, emp_id, user_id, table_name, action, ip, `query`,
         affected_fields, old_values, new_values, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    if (!$stmt) {
        error_log("employeeAuditLog prepare failed: " . $db->error);
        return false;
    }

    $stmt->bind_param(
        "iiisssssss",
        $empType,
        $empId,
        $userId,
        $table,
        $action,
        $ip,
        $queryText,
        $affJson,
        $oldJson,
        $newJson
    );

    $result = $stmt->execute();
    if (!$result) {
        error_log("employeeAuditLog execute failed: " . $stmt->error);
    }
    $stmt->close();

    return $result;
}

/**
 * Helper to get school name by ID with in-memory caching.
 *
 * @param int $id School ID
 * @param mysqli $db MySQLi connection
 * @return string School name or empty string if not found
 */
function getSchoolNameCached(int $id, mysqli $db): string {
    static $schoolNameCache = [];
    if ($id <= 0) {
        return '';
    }
    if (!array_key_exists($id, $schoolNameCache)) {
        $stmt = $db->prepare("SELECT name FROM school WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                $schoolNameCache[$id] = (string)$row['name'];
            } else {
                $schoolNameCache[$id] = '';
            }
            $stmt->close();
        } else {
            $schoolNameCache[$id] = '';
        }
    }
    return $schoolNameCache[$id];
}

/**
 * Return action badge HTML for employee log.
 *
 * @param string $action Action name ('add', 'edit', 'delete')
 * @return string HTML badge
 */
function getEmployeeLogActionBadge(string $action): string {
    switch ($action) {
        case 'add':
            return "<span class='badge badge-add'>Προσθήκη</span>";
        case 'delete':
            return "<span class='badge badge-delete'>Διαγραφή</span>";
        case 'edit':
        default:
            return "<span class='badge badge-edit'>Μεταβολή</span>";
    }
}

/**
 * Render diff HTML for an employee log record (with colors, strikethrough, JSON toggle).
 *
 * @param array $row Array containing log record columns (id, action, query, old_values, new_values, etc.)
 * @param mysqli $db MySQLi connection
 * @return string HTML diff
 */
function renderEmployeeLogDiffHtml(array $row, mysqli $db): string {
    $id = isset($row['id']) ? (int)$row['id'] : 0;
    $action = $row['action'] ?? 'edit';
    $diff_html = '';

    $old = !empty($row['old_values']) ? json_decode($row['old_values'], true) : null;
    $new = !empty($row['new_values']) ? json_decode($row['new_values'], true) : null;

    if ($action === 'edit' && is_array($old) && is_array($new) && !empty($old)) {
        $diff_html .= "<div class='diff-container'>";
        foreach ($new as $key => $nval) {
            $oval = $old[$key] ?? '[κενό]';
            if ($oval === '') $oval = '[κενό]';
            if ($nval === '') $nval = '[κενό]';

            if ($key === 'sx_yphrethshs' || $key === 'sx_organikhs') {
                $schOld = getSchoolNameCached((int)$oval, $db);
                $schNew = getSchoolNameCached((int)$nval, $db);
                $oval_display = $schOld !== '' ? $schOld : $oval;
                $nval_display = $schNew !== '' ? $schNew : $nval;
            } else {
                $oval_display = $oval;
                $nval_display = $nval;
            }

            $oval_esc = htmlspecialchars((string)$oval_display, ENT_QUOTES, 'UTF-8');
            $nval_esc = htmlspecialchars((string)$nval_display, ENT_QUOTES, 'UTF-8');
            $diff_html .= "<div class='diff-field'><span class='diff-key'>" . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . ":</span> <span class='diff-old'>$oval_esc</span> &rarr; <span class='diff-new'>$nval_esc</span></div>";
        }
        if ($id > 0) {
            $diff_html .= "<button type='button' class='json-toggle-btn' data-target='raw-$id'>Προβολή JSON</button>";
            $diff_html .= "<pre id='raw-$id' class='json-raw-box'>" . htmlspecialchars(json_encode(['old' => $old, 'new' => $new], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . "</pre>";
        }
        $diff_html .= "</div>";
    } elseif ($action === 'add' && is_array($new) && !empty($new)) {
        $diff_html .= "<span style='color:#15803d; font-weight:600;'>Προσθήκη νέας εγγραφής</span>";
        if ($id > 0) {
            $diff_html .= "<br><button type='button' class='json-toggle-btn' data-target='raw-$id'>Προβολή Στοιχείων (JSON)</button>";
            $diff_html .= "<pre id='raw-$id' class='json-raw-box'>" . htmlspecialchars(json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . "</pre>";
        }
    } elseif ($action === 'delete') {
        $diff_html .= "<span style='color:#b91c1c; font-weight:600;'>Διαγραφή εγγραφής</span>";
        if (is_array($old) && !empty($old) && $id > 0) {
            $diff_html .= "<br><button type='button' class='json-toggle-btn' data-target='raw-$id'>Προβολή Στοιχείων Διαγραφής</button>";
            $diff_html .= "<pre id='raw-$id' class='json-raw-box'>" . htmlspecialchars(json_encode($old, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . "</pre>";
        }
    } else {
        $raw_query = (string)($row['query'] ?? '');

        // Replace sx_yphrethshs: ID -> ID or sx_organikhs: ID -> ID with school names
        $formatted_query = preg_replace_callback(
            '/\b(sx_yphrethshs|sx_organikhs):\s*(\d+)\s*->\s*(\d+)/',
            function ($matches) use ($db) {
                $field = $matches[1];
                $oldSch = getSchoolNameCached((int)$matches[2], $db);
                $newSch = getSchoolNameCached((int)$matches[3], $db);
                $oldText = $oldSch !== '' ? $oldSch : $matches[2];
                $newText = $newSch !== '' ? $newSch : $matches[3];
                return "$field: $oldText -> $newText";
            },
            $raw_query
        );

        // Check if query contains diff entries like "field: old -> new"
        if (preg_match_all('/([a-zA-Z0-9_\x{0370}-\x{03FF}]+):\s*([^,->\n]+?)\s*->\s*([^,\n]+)/u', $formatted_query, $m, PREG_SET_ORDER)) {
            $diff_html .= "<div class='diff-container'>";
            foreach ($m as $match) {
                $key = htmlspecialchars(trim($match[1]), ENT_QUOTES, 'UTF-8');
                $oval_esc = htmlspecialchars(trim($match[2]), ENT_QUOTES, 'UTF-8');
                $nval_esc = htmlspecialchars(trim($match[3]), ENT_QUOTES, 'UTF-8');
                $diff_html .= "<div class='diff-field'><span class='diff-key'>$key:</span> <span class='diff-old'>$oval_esc</span> &rarr; <span class='diff-new'>$nval_esc</span></div>";
            }
            $diff_html .= "</div>";
        } else {
            $diff_html .= "<div class='query-container' style='font-size:0.83rem; color:#475569;'>" . htmlspecialchars($formatted_query, ENT_QUOTES, 'UTF-8') . "</div>";
        }
    }

    return $diff_html;
}
