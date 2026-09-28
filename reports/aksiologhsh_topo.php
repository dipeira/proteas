<?php
require_once "../config.php";
require_once "../include/functions.php";

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

require "../tools/class.login.php";
$log = new logmein();
if ($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
    exit();
}

// check if super-user
if ($_SESSION['userlevel'] <> 0) {
    echo 'Σφάλμα: Δεν επιτρέπεται η πρόσβαση σε αυτή τη σελίδα.';
    echo '<br><br><a href="../index.php">Επιστροφή στην αρχική σελίδα</a>';
    exit();
}

$sxol_etos = getParam('sxol_etos', $mysqlconnection);
$allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ', $mysqlconnection);
$allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ', $mysqlconnection);
$ekswteriko = getSchoolID('Απόσπαση στο εξωτερικό', $mysqlconnection);
$foreas = getSchoolID('Απόσπαση σε φορέα', $mysqlconnection);
$dipe = '398';

// Determine source of evaluated teachers
$emp_ids = [];
$source_desc = '';

if (!empty($_POST['emp_ids'])) {
    // 1. Direct IDs passed from aksiologhsh_report.php search results
    $raw_ids = explode(',', $_POST['emp_ids']);
    $emp_ids = array_filter(array_map('intval', $raw_ids));
    $source_desc = 'Εκπαιδευτικοί από την τρέχουσα αναζήτηση της Αναφοράς Αξιολόγησης';
} elseif (isset($_POST['submit_topo'])) {
    // 2. Direct submission from search filter form
    $source_desc = 'Εκπαιδευτικοί βάσει των επιλεγμένων κριτηρίων της Αναφοράς Αξιολόγησης';
} elseif (!empty($_SESSION['eval_emp_ids'])) {
    // 3. Stored IDs in session from previous search
    $emp_ids = array_filter(array_map('intval', $_SESSION['eval_emp_ids']));
    $source_desc = 'Εκπαιδευτικοί από την τελευταία αναζήτηση της Αναφοράς Αξιολόγησης (από Session)';
} else {
    // 4. Default fallback: all evaluated teachers
    $source_desc = 'Όλοι οι αξιολογούμενοι εκπαιδευτικοί (προεπιλογή)';
}

// Build employee query
if (!empty($emp_ids)) {
    $ids_in = implode(',', $emp_ids);
    $emp_query = "SELECT 
        e.id as emp_id,
        e.surname,
        e.name,
        e.am,
        e.afm,
        k.perigrafh as klados,
        e.sx_yphrethshs,
        e.wres,
        s.name as default_sch_name
    FROM employee e
    LEFT JOIN klados k ON e.klados = k.id
    LEFT JOIN school s ON e.sx_yphrethshs = s.id
    WHERE e.id IN ($ids_in)
    GROUP BY e.id
    ORDER BY e.surname ASC, e.name ASC";
} else {
    $emp_query = "SELECT
        e.id as emp_id,
        e.surname,
        e.name,
        e.am,
        e.afm,
        k.perigrafh as klados,
        e.klados as klados_id,
        s.name as default_sch_name,
        s.id as sch_id,
        e.sx_yphrethshs,
        e.wres
    FROM employee e
    LEFT JOIN klados k ON e.klados = k.id
    LEFT JOIN (
        SELECT 
            emp_id,
            SUBSTRING_INDEX(
                GROUP_CONCAT(
                    yphrethsh 
                    ORDER BY 
                        total_hours DESC, 
                        is_sx_yphr DESC,
                        yphrethsh ASC
                    SEPARATOR ','
                ), 
                ',', 
                1
            ) AS primary_sch_id
        FROM (
            SELECT 
                y.emp_id,
                y.yphrethsh,
                SUM(y.hours + 0) as total_hours,
                MAX(y.yphrethsh = e.sx_yphrethshs) as is_sx_yphr
            FROM yphrethsh y
            JOIN employee e ON y.emp_id = e.id
            WHERE y.sxol_etos = '$sxol_etos'
            GROUP BY y.emp_id, y.yphrethsh
        ) sch_hours
        GROUP BY emp_id
    ) yp ON e.id = yp.emp_id
    LEFT JOIN school s ON COALESCE(yp.primary_sch_id, e.sx_yphrethshs) = s.id
    WHERE e.status = 1 
    AND e.sx_yphrethshs NOT IN ($allo_pysde, $allo_pyspe, $dipe, $foreas, $ekswteriko)
    AND COALESCE(yp.primary_sch_id, e.sx_yphrethshs) NOT IN ($allo_pysde, $allo_pyspe, $dipe, $foreas, $ekswteriko)
    AND s.type2 IN (0, 2)";

    if (!empty($_POST['hm_dior_from'])) {
        $emp_query .= " AND e.hm_dior >= '".mysqli_real_escape_string($mysqlconnection, $_POST['hm_dior_from'])."'";
    }
    if (!empty($_POST['hm_dior_to'])) {
        $emp_query .= " AND e.hm_dior <= '".mysqli_real_escape_string($mysqlconnection, $_POST['hm_dior_to'])."'";
    }
    if (!empty($_POST['klados'])) {
        $kl_arr = is_array($_POST['klados']) ? $_POST['klados'] : explode(',', $_POST['klados']);
        $kl_clean = implode(",", array_map('intval', $kl_arr));
        if ($kl_clean) {
            $emp_query .= " AND e.klados IN ($kl_clean)";
        }
    }
    if (isset($_POST['monimopoihsh']) && $_POST['monimopoihsh'] !== '') {
        $emp_query .= " AND e.monimopoihsh = " . intval($_POST['monimopoihsh']);
    }
    if (isset($_POST['aksiologhsh']) && $_POST['aksiologhsh'] !== '') {
        $emp_query .= " AND e.aksiologhsh = " . intval($_POST['aksiologhsh']);
    }
    if (!empty($_POST['aks_date_from'])) {
        $emp_query .= " AND e.aksiologhsh_date >= '".mysqli_real_escape_string($mysqlconnection, $_POST['aks_date_from'])."'";
    }
    if (!empty($_POST['aks_date_to'])) {
        $emp_query .= " AND e.aksiologhsh_date <= '".mysqli_real_escape_string($mysqlconnection, $_POST['aks_date_to'])."'";
    }

    $emp_query .= " GROUP BY e.id ORDER BY e.surname ASC, e.name ASC";
}

$res = mysqli_query($mysqlconnection, $emp_query);
$teachers = [];
$retrieved_ids = [];
$seen = [];

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $afm = trim($row['afm']);
        if (isset($seen[$afm])) {
            continue;
        }
        $seen[$afm] = true;
        $eid = (int)$row['emp_id'];
        $retrieved_ids[] = $eid;
        $teachers[$eid] = $row;
    }
}

// Fetch all placements for the retrieved teachers from yphrethsh table
$placements_map = [];
if (!empty($retrieved_ids)) {
    $ids_chunk = implode(',', $retrieved_ids);
    $yp_query = "SELECT 
        y.emp_id, 
        y.yphrethsh as sch_id, 
        s.name as school_name, 
        SUM(y.hours + 0) as total_hours
    FROM yphrethsh y
    JOIN school s ON y.yphrethsh = s.id
    WHERE y.sxol_etos = '$sxol_etos'
      AND y.emp_id IN ($ids_chunk)
    GROUP BY y.emp_id, y.yphrethsh, s.name
    ORDER BY total_hours DESC, s.name ASC";
    
    $yp_res = mysqli_query($mysqlconnection, $yp_query);
    if ($yp_res) {
        while ($yp = mysqli_fetch_assoc($yp_res)) {
            $eid = (int)$yp['emp_id'];
            $hours = (float)$yp['total_hours'];
            $hours_word = ($hours == 1) ? 'ώρα' : 'ώρες';
            $hours_str = ($hours > 0) ? " ({$hours} {$hours_word})" : "";
            $placements_map[$eid][] = $yp['school_name'] . $hours_str;
        }
    }
}

// Attach placements to each teacher
foreach ($teachers as $eid => &$t) {
    if (!empty($placements_map[$eid])) {
        $t['placements'] = $placements_map[$eid];
    } elseif (!empty($t['default_sch_name'])) {
        $h = (float)$t['wres'];
        $h_word = ($h == 1) ? 'ώρα' : 'ώρες';
        $h_str = ($h > 0) ? " ({$h} {$h_word})" : "";
        $t['placements'] = [$t['default_sch_name'] . $h_str];
    } else {
        $t['placements'] = ['Δεν έχει καταχωρηθεί'];
    }
}
unset($t);

$teacher_count = count($teachers);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Αναφορά Τοποθετήσεων Αξιολογούμενων</title>
    <script type="text/javascript" language="javascript" src="../js/jquery.js"></script>

    <?php 
      $root_path = '../';
      $page_title = 'Αναφορά Τοποθετήσεων Αξιολογούμενων';
      require '../etc/head.php'; 
      require_once '../js/datatables/includes.html';
    ?>
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }
        .header-title-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .badge-count {
            background: #e0f2fe;
            color: #0369a1;
            font-size: 0.875rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 9999px;
            border: 1px solid #bae6fd;
        }
        .badge-year {
            background: #f1f5f9;
            color: #475569;
            font-size: 0.8125rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .info-banner {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #3b82f6;
            padding: 10px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: #334155;
        }
        .table-card {
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 18px;
        }
        #dataTable {
            width: 100% !important;
            border-collapse: collapse;
        }
        #dataTable thead th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            padding: 10px 12px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        #dataTable tbody td {
            padding: 9px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        #dataTable tbody tr:hover {
            background-color: #f8fafc;
        }
        .placement-line {
            display: block;
            line-height: 1.5;
            padding: 2px 0;
        }
        .placement-line:not(:last-child) {
            border-bottom: 1px dashed #f1f5f9;
        }
        .klados-pill {
            display: inline-block;
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #dbeafe;
            font-weight: 600;
            font-size: 0.775rem;
            padding: 2px 8px;
            border-radius: 4px;
            text-align: center;
            white-space: nowrap;
        }
        .btn-back {
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 7px 14px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .btn-back:hover {
            background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
        }
        .dt-buttons .dt-button {
            border-radius: 4px !important;
            font-weight: 600 !important;
            font-size: 0.8125rem !important;
            padding: 5px 12px !important;
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            color: #334155 !important;
            margin-right: 6px !important;
        }
        .dt-buttons .dt-button:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }
    </style>
    <script type="text/javascript">
    $(document).ready(function() {
        if ($('#dataTable').length) {
            $('#dataTable').DataTable({
                pageLength: 50,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Όλα"]],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        text: '📋 Αντιγραφή',
                        exportOptions: {
                            format: {
                                body: function (data, row, column, node) {
                                    return data.replace(/<br\s*[\/]?>/gi, "\n").replace(/<[^>]+>/g, '').trim();
                                }
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        text: '📊 Εξαγωγή Excel',
                        filename: 'topothethseis_aksiologoumenwn_' + new Date().toISOString().slice(0, 10),
                        title: 'Τοποθετήσεις Αξιολογούμενων Εκπαιδευτικών',
                        exportOptions: {
                            format: {
                                body: function (data, row, column, node) {
                                    return data.replace(/<br\s*[\/]?>/gi, "\r\n").replace(/<[^>]+>/g, '').trim();
                                }
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: '🖨️ Εκτύπωση',
                        title: 'Τοποθετήσεις Αξιολογούμενων Εκπαιδευτικών',
                        exportOptions: {
                            stripHtml: false
                        }
                    }
                ],
                language: {
                    url: "../js/datatables/greek.json"
                }
            });
        }
    });
    </script>
</head>
<body>
    <?php require '../etc/menu.php'; ?>
    <div id="container" style="max-width: 1300px; margin: 0 auto; padding: 20px 15px;">
        <!-- Header -->
        <div class="page-header">
            <div class="header-title-group">
                <h1 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    🏫 Αναφορά Τοποθετήσεων Αξιολογούμενων
                </h1>
                <span class="badge-count"><?php echo $teacher_count; ?> Εκπαιδευτικοί</span>
                <span class="badge-year">Σχολ. Έτος: <?php echo htmlspecialchars($sxol_etos); ?></span>
            </div>
            <div>
                <a href="aksiologhsh_report.php" class="btn-back">
                    ⬅️ Επιστροφή στην Αναφορά Αξιολόγησης
                </a>
            </div>
        </div>

        <!-- Info / Context Banner -->
        <div class="info-banner">
            <div>
                <span style="font-weight: 600;">Προέλευση δεδομένων:</span> <?php echo htmlspecialchars($source_desc); ?>
            </div>
            <div>
                <a href="aksiologhsh_report.php" style="color: #2563eb; text-decoration: underline; font-weight: 500;">
                    Αλλαγή κριτηρίων
                </a>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <table id="dataTable" class="display cell-border stripe hover">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">Α/Α</th>
                        <th style="width: 220px;">Επώνυμο</th>
                        <th style="width: 180px;">Όνομα</th>
                        <th style="width: 90px; text-align: center;">Α.Μ.</th>
                        <th style="width: 100px; text-align: center;">Α.Φ.Μ.</th>
                        <th style="width: 100px; text-align: center;">Κλάδος</th>
                        <th>Σχολεία Τοποθέτησης</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $aa = 1;
                    foreach ($teachers as $eid => $t):
                        $surname = htmlspecialchars($t['surname']);
                        $name = htmlspecialchars($t['name']);
                        $klados = htmlspecialchars($t['klados'] ?: '-');
                        
                        // Format placements: one per line
                        $placements_html = '';
                        $count_lines = count($t['placements']);
                        foreach ($t['placements'] as $idx => $line) {
                            $placements_html .= htmlspecialchars($line);
                            if ($idx < $count_lines - 1) {
                                $placements_html .= '<br>';
                            }
                        }
                    ?>
                    <tr>
                        <td style="text-align: center; color: #64748b; font-weight: 600;"><?php echo $aa; ?></td>
                        <td>
                            <a href="../employee/employee.php?id=<?php echo $eid; ?>&op=view" target="_blank" style="color: #0f172a; font-weight: 600; text-decoration: none;" class="hover:underline" title="Προβολή καρτέλας εκπαιδευτικού">
                                <?php echo $surname; ?>
                            </a>
                        </td>
                        <td style="color: #334155; font-weight: 500;"><?php echo $name; ?></td>
                        <td style="text-align: center; color: #475569; font-weight: 500;"><?php echo htmlspecialchars($t['am'] ?: '-'); ?></td>
                        <td style="text-align: center; color: #475569; font-weight: 500;"><?php echo htmlspecialchars($t['afm'] ?: '-'); ?></td>
                        <td style="text-align: center;">
                            <span class="klados-pill"><?php echo $klados; ?></span>
                        </td>
                        <td style="color: #1e293b;">
                            <?php echo $placements_html; ?>
                        </td>
                    </tr>
                    <?php
                        $aa++;
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
