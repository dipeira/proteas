<?php
header('Content-type: text/html; charset=utf-8');
require_once "../config.php";
require_once "../include/functions.php";
require "../tools/class.login.php";

$log = new logmein();
if ($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
    exit;
}

// Check if super-user
if ($_SESSION['userlevel'] <> 0) {
    header("Location: ../index.php");
    exit;
}

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

// Filter parameters
$emp_type_filter = isset($_GET['type']) ? (int)$_GET['type'] : 0; // 0: all, 1: monimoi, 2: anaplirotes
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 500;
if ($limit <= 0) {
    $limit = 500;
}
if (isset($_GET['limit']) && $_GET['limit'] === 'all') {
    $limit = 5000;
}

$where = "WHERE 1=1";
if ($emp_type_filter === 1) {
    $where .= " AND l.emp_type = 1";
} elseif ($emp_type_filter === 2) {
    $where .= " AND l.emp_type = 2";
}

$query = "
    SELECT 
        l.id,
        l.emp_type,
        l.emp_id,
        l.action,
        l.ip,
        l.query,
        l.affected_fields,
        l.old_values,
        l.new_values,
        l.created_at,
        o.username,
        CASE 
            WHEN l.emp_type = 1 THEN 'Μόνιμος'
            WHEN l.emp_type = 2 THEN 'Αναπληρωτής'
            ELSE 'Άλλο'
        END AS type_label,
        COALESCE(e.am, ed.am, '') AS am,
        COALESCE(e.surname, ed.surname, k.surname, '') AS surname,
        COALESCE(e.name, ed.name, k.name, '') AS name,
        COALESCE(e.afm, ed.afm, k.afm, '') AS afm,
        CASE WHEN e.id IS NOT NULL THEN 1 ELSE 0 END AS mon_exists,
        CASE WHEN k.id IS NOT NULL THEN 1 ELSE 0 END AS ekt_exists
    FROM employee_log l
    JOIN logon o ON l.user_id = o.userid
    LEFT JOIN employee e ON (l.emp_type = 1 AND e.id = l.emp_id)
    LEFT JOIN employee_deleted ed ON (l.emp_type = 1 AND ed.id = l.emp_id)
    LEFT JOIN ektaktoi k ON (l.emp_type = 2 AND k.id = l.emp_id)
    $where
    ORDER BY l.created_at DESC, l.id DESC
    LIMIT $limit
";

$result = mysqli_query($mysqlconnection, $query);
$num = $result ? mysqli_num_rows($result) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <?php
    $root_path = '../';
    $page_title = 'Αρχείο Συμβάντων (Audit Log)';
    require '../etc/head.php';
    ?>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery-ui.min.js"></script>
    <?php require_once('../js/datatables/includes.html'); ?>

    <style>
        body {
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .page-container {
            max-width: 1550px;
            margin: 0 auto;
        }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .page-header h1 {
            margin: 0;
            color: #0f172a;
            font-size: 1.6rem;
            font-weight: 700;
        }
        .filter-card {
            background: white;
            border-radius: 8px;
            padding: 14px 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .filter-card label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #475569;
        }
        .filter-card select {
            padding: 6px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.9rem;
            background: white;
        }
        .table-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            overflow-x: auto;
        }
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-monimos {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .badge-anapl {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .badge-add {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .badge-edit {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .badge-delete {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .diff-container,
        .query-container {
            font-size: 0.83rem;
            line-height: 1.45;
            width: 100%;
            max-width: 100%;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
        }
        .diff-field {
            margin-bottom: 4px;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
        }
        .diff-key {
            font-weight: 600;
            color: #334155;
            display: inline;
        }
        .diff-old {
            color: #dc2626;
            text-decoration: line-through;
            background: #fee2e2;
            padding: 1px 4px;
            border-radius: 3px;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            display: inline;
        }
        .diff-new {
            color: #16a34a;
            font-weight: 600;
            background: #dcfce7;
            padding: 1px 4px;
            border-radius: 3px;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            display: inline;
        }
        .json-toggle-btn {
            background: none;
            border: 1px solid #cbd5e1;
            color: #475569;
            padding: 2px 7px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.72rem;
            margin-top: 4px;
        }
        .json-toggle-btn:hover {
            background: #f1f5f9;
        }
        .json-raw-box {
            display: none;
            margin-top: 6px;
            padding: 8px;
            background: #0f172a;
            color: #38bdf8;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.75rem;
            max-height: 160px;
            overflow-y: auto;
            white-space: pre-wrap !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
        }
        #example {
            font-size: 0.88rem;
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse;
        }
        #example th,
        #example td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
        }
        #example th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
        }
        .dt-buttons {
            margin-bottom: 12px;
        }
        .dt-button {
            border-radius: 6px !important;
            padding: 6px 14px !important;
            font-weight: 500 !important;
            margin-right: 6px !important;
        }
    </style>

    <script type="text/javascript">
        $(document).ready(function() {
            var table = $('#example').DataTable({
                autoWidth: false,
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'Όλα']
                ],
                order: [[0, 'desc']], // Sort by ID desc
                dom: '<"top"Bfl>rt<"bottom"ip><"clear">',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Εξαγωγή Excel',
                        className: 'dt-button',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
                    },
                    {
                        extend: 'print',
                        text: 'Εκτύπωση',
                        className: 'dt-button',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
                    }
                ],
                language: {
                    url: '../js/datatables/greek.json'
                }
            });

            // Toggle JSON raw box
            $(document).on('click', '.json-toggle-btn', function(e) {
                e.preventDefault();
                var targetId = $(this).data('target');
                $('#' + targetId).slideToggle(150);
            });

            // Toggle query text expand/collapse for older records
            $(document).on('click', '.query-toggle-link', function(e) {
                e.preventDefault();
                var qId = $(this).data('target');
                var $short = $('#q-short-' + qId);
                var $full = $('#q-full-' + qId);
                if ($full.is(':visible')) {
                    $full.hide();
                    $short.show();
                    $(this).text('[+Περισσότερα]');
                } else {
                    $short.hide();
                    $full.show();
                    $(this).text('[-Απόκρυψη]');
                }
            });
        });
    </script>
</head>
<body>
    <?php require '../etc/menu.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <div>
                <h1>Αρχείο Συμβάντων</h1>
                <small style="color: #64748b;">Καταγραφή ενεργειών & μεταβολών σε μόνιμους και αναπληρωτές εκπαιδευτικούς</small>
            </div>
            <div>
                <input type="button" class="btn-red" value="Επιστροφή" onClick="parent.location='../index.php'">
            </div>
        </div>

        <form method="GET" action="" class="filter-card">
            <div>
                <label for="type">Κατηγορία: </label>
                <select name="type" id="type" onchange="this.form.submit()">
                    <option value="0" <?php echo $emp_type_filter === 0 ? 'selected' : ''; ?>>Όλοι (Μόνιμοι & Αναπληρωτές)</option>
                    <option value="1" <?php echo $emp_type_filter === 1 ? 'selected' : ''; ?>>Μόνο Μόνιμοι</option>
                    <option value="2" <?php echo $emp_type_filter === 2 ? 'selected' : ''; ?>>Μόνο Αναπληρωτές</option>
                </select>
            </div>

            <div>
                <label for="limit">Πλήθος εγγραφών: </label>
                <select name="limit" id="limit" onchange="this.form.submit()">
                    <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>Τελευταίες 100</option>
                    <option value="300" <?php echo $limit === 300 ? 'selected' : ''; ?>>Τελευταίες 300</option>
                    <option value="500" <?php echo $limit === 500 ? 'selected' : ''; ?>>Τελευταίες 500</option>
                    <option value="1000" <?php echo $limit === 1000 ? 'selected' : ''; ?>>Τελευταίες 1000</option>
                    <option value="all" <?php echo $limit === 5000 ? 'selected' : ''; ?>>Τελευταίες 5000</option>
                </select>
            </div>

            <span style="color:#64748b; font-size:0.85rem; margin-left:auto;">
                Εμφανίζονται <strong><?php echo $num; ?></strong> συμβάντα
            </span>
        </form>

        <div class="table-card">
            <table id="example" class="display cell-border" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th width="65">ID</th>
                        <th width="75">Τύπος</th>
                        <th width="95">Ενέργεια</th>
                        <th width="220">Εκπαιδευτικός</th>
                        <th width="100">Χρήστης</th>
                        <th width="130">Ημ/νία - Ώρα</th>
                        <th>Μεταβολές / Στοιχεία</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($num > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $emp_type = (int)$row['emp_type'];
                        $emp_id = (int)$row['emp_id'];
                        $action = $row['action'];
                        $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
                        $date_str = date('d-m-Y H:i:s', strtotime($row['created_at']));
                        
                        $type_badge = $emp_type === 1 
                            ? "<span class='badge badge-monimos' title='Μόνιμος'>ΜΟΝ</span>"
                            : "<span class='badge badge-anapl' title='Αναπληρωτής'>ΑΝΑΠΛ</span>";

                        $action_badge = '';
                        switch ($action) {
                            case 'add':
                                $action_badge = "<span class='badge badge-add'>Προσθήκη</span>";
                                break;
                            case 'delete':
                                $action_badge = "<span class='badge badge-delete'>Διαγραφή</span>";
                                break;
                            case 'edit':
                            default:
                                $action_badge = "<span class='badge badge-edit'>Μεταβολή</span>";
                                break;
                        }

                        // Employee link & name
                        $emp_name = trim($row['surname'] . ' ' . $row['name']);
                        if ($emp_name === '') {
                            $emp_name = "ID: " . $emp_id;
                        }
                        $code = !empty($row['am']) ? htmlspecialchars($row['am'], ENT_QUOTES, 'UTF-8') : htmlspecialchars($row['afm'], ENT_QUOTES, 'UTF-8');
                        $emp_title = $code ? " title='Α.Μ./Α.Φ.Μ.: $code'" : "";

                        $emp_link = "<span$emp_title>$emp_name</span>";
                        if ($emp_type === 1 && $row['mon_exists']) {
                            $emp_link = "<a href='../employee/employee.php?id=$emp_id&op=view' target='_blank'$emp_title style='font-weight:600;color:#0284c7;text-decoration:none;'>$emp_name</a>";
                        } elseif ($emp_type === 2 && $row['ekt_exists']) {
                            $emp_link = "<a href='../employee/ektaktoi.php?id=$emp_id&op=view' target='_blank'$emp_title style='font-weight:600;color:#d97706;text-decoration:none;'>$emp_name</a>";
                        }

                        // Render diff or query
                        $diff_html = '';
                        $aff = !empty($row['affected_fields']) ? json_decode($row['affected_fields'], true) : null;
                        $old = !empty($row['old_values']) ? json_decode($row['old_values'], true) : null;
                        $new = !empty($row['new_values']) ? json_decode($row['new_values'], true) : null;

                        if ($action === 'edit' && is_array($old) && is_array($new) && !empty($old)) {
                            $diff_html .= "<div class='diff-container'>";
                            foreach ($new as $key => $nval) {
                                $oval = $old[$key] ?? '[κενό]';
                                if ($oval === '') $oval = '[κενό]';
                                if ($nval === '') $nval = '[κενό]';

                                if ($key === 'sx_yphrethshs' || $key === 'sx_organikhs') {
                                    $schOld = getSchoolNameCached((int)$oval, $mysqlconnection);
                                    $schNew = getSchoolNameCached((int)$nval, $mysqlconnection);
                                    $oval_display = $schOld !== '' ? $schOld : $oval;
                                    $nval_display = $schNew !== '' ? $schNew : $nval;
                                } else {
                                    $oval_display = $oval;
                                    $nval_display = $nval;
                                }

                                $oval_esc = htmlspecialchars((string)$oval_display, ENT_QUOTES, 'UTF-8');
                                $nval_esc = htmlspecialchars((string)$nval_display, ENT_QUOTES, 'UTF-8');
                                $diff_html .= "<div class='diff-field'><span class='diff-key'>$key:</span> <span class='diff-old'>$oval_esc</span> &rarr; <span class='diff-new'>$nval_esc</span></div>";
                            }
                            $diff_html .= "<button type='button' class='json-toggle-btn' data-target='raw-$id'>Προβολή JSON</button>";
                            $diff_html .= "<pre id='raw-$id' class='json-raw-box'>" . htmlspecialchars(json_encode(['old' => $old, 'new' => $new], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . "</pre>";
                            $diff_html .= "</div>";
                        } elseif ($action === 'add' && is_array($new) && !empty($new)) {
                            $diff_html .= "<span style='color:#15803d; font-weight:600;'>Προσθήκη νέας εγγραφής</span>";
                            $diff_html .= "<br><button type='button' class='json-toggle-btn' data-target='raw-$id'>Προβολή Στοιχείων (JSON)</button>";
                            $diff_html .= "<pre id='raw-$id' class='json-raw-box'>" . htmlspecialchars(json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . "</pre>";
                        } elseif ($action === 'delete') {
                            $diff_html .= "<span style='color:#b91c1c; font-weight:600;'>Διαγραφή εγγραφής</span>";
                            if (is_array($old) && !empty($old)) {
                                $diff_html .= "<br><button type='button' class='json-toggle-btn' data-target='raw-$id'>Προβολή Στοιχείων Διαγραφής</button>";
                                $diff_html .= "<pre id='raw-$id' class='json-raw-box'>" . htmlspecialchars(json_encode($old, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . "</pre>";
                            }
                        } else {
                            $raw_query = htmlspecialchars((string)$row['query'], ENT_QUOTES, 'UTF-8');

                            // Replace sx_yphrethshs: ID -> ID or sx_organikhs: ID -> ID with school names
                            $formatted_query = preg_replace_callback(
                                '/\b(sx_yphrethshs|sx_organikhs):\s*(\d+)\s*->\s*(\d+)/',
                                function ($matches) use ($mysqlconnection) {
                                    $field = $matches[1];
                                    $oldSch = getSchoolNameCached((int)$matches[2], $mysqlconnection);
                                    $newSch = getSchoolNameCached((int)$matches[3], $mysqlconnection);
                                    $oldText = $oldSch !== '' ? $oldSch : $matches[2];
                                    $newText = $newSch !== '' ? $newSch : $matches[3];
                                    return "$field: $oldText -> $newText";
                                },
                                $raw_query
                            );

                            if (mb_strlen($formatted_query, 'UTF-8') > 90) {
                                $short_query = mb_substr($formatted_query, 0, 90, 'UTF-8') . '...';
                                $diff_html .= "<div class='query-container' style='font-size:0.82rem; color:#475569;'>";
                                $diff_html .= "<span id='q-short-$id'>$short_query </span>";
                                $diff_html .= "<span id='q-full-$id' style='display:none; white-space:pre-wrap; word-break:break-word;'>$formatted_query</span>";
                                $diff_html .= "<a href='#' class='query-toggle-link' data-target='$id' style='color:#0284c7; font-weight:600; text-decoration:none; margin-left:4px; font-size:0.78rem;'>[+Περισσότερα]</a>";
                                $diff_html .= "</div>";
                            } else {
                                $diff_html .= "<div style='font-size:0.82rem; color:#475569;'>$formatted_query</div>";
                            }
                        }

                        echo "<tr>";
                        echo "<td>$id</td>";
                        echo "<td>$type_badge</td>";
                        echo "<td>$action_badge</td>";
                        echo "<td>$emp_link</td>";
                        echo "<td>$username</td>";
                        echo "<td>$date_str</td>";
                        echo "<td>$diff_html</td>";
                        echo "</tr>";
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 15px;">
            <input type="button" class="btn-red" value="Επιστροφή" onClick="parent.location='../index.php'">
        </div>
    </div>
</body>
</html>
