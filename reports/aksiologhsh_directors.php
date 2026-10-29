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

$directors = [];
$source_desc = '';

if (!empty($_POST['dir_data'])) {
    $decoded = json_decode($_POST['dir_data'], true);
    if (is_array($decoded)) {
        $directors = $decoded;
        $source_desc = 'Διευθυντές / Υποδιευθυντές / Προϊστάμενοι που εξαιρέθηκαν από τα αρχεία CSV κατά την τρέχουσα εξαγωγή (διορισμός >= 1/1/2020)';
    }
}

if (empty($directors) && !empty($_POST['emp_ids'])) {
    $raw_ids = explode(',', $_POST['emp_ids']);
    $emp_ids = array_filter(array_map('intval', $raw_ids));
    if (!empty($emp_ids)) {
        $ids_in = implode(',', $emp_ids);
        $q = "SELECT e.id as emp_id, e.am as emp_am, e.afm as emp_afm, e.surname as emp_surname, e.name as emp_name, 
                     s.name as sch_name, e.hm_dior, e.thesi, d.afm as dnt_afm
              FROM employee e
              LEFT JOIN school s ON e.sx_yphrethshs = s.id
              LEFT JOIN employee d ON (s.id = d.sx_yphrethshs AND d.thesi = 2 AND d.status IN (1,3))
              WHERE e.id IN ($ids_in) AND (e.thesi IN (1, 2) OR d.afm = e.afm)
              ORDER BY e.surname ASC, e.name ASC";
        $res = mysqli_query($mysqlconnection, $q);
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $directors[] = [
                    'emp_id' => $r['emp_id'],
                    'am' => clean_str($r['emp_am'] ?? ''),
                    'afm' => normalize_afm($r['emp_afm']),
                    'surname' => trim($r['emp_surname']),
                    'name' => trim($r['emp_name']),
                    'sch_name' => clean_str($r['sch_name'] ?? ''),
                    'hm_dior' => (!empty($r['hm_dior']) && $r['hm_dior'] !== '0000-00-00') ? date('d/m/Y', strtotime($r['hm_dior'])) : ''
                ];
            }
        }
        $source_desc = 'Διευθυντές / Υποδιευθυντές / Προϊστάμενοι από τους επιλεγμένους αξιολογούμενους';
    }
}

if (empty($directors)) {
    // Default fallback: query all school directors/heads/vice-principals
    $q = "SELECT e.id as emp_id, e.am as emp_am, e.afm as emp_afm, e.surname as emp_surname, e.name as emp_name, 
                 s.name as sch_name, e.hm_dior
          FROM employee e
          LEFT JOIN school s ON e.sx_yphrethshs = s.id
          WHERE e.thesi IN (1, 2) AND e.status IN (1,3) AND s.type2 IN (0, 2)
          ORDER BY e.surname ASC, e.name ASC";
    $res = mysqli_query($mysqlconnection, $q);
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $directors[] = [
                'emp_id' => $r['emp_id'],
                'am' => clean_str($r['emp_am'] ?? ''),
                'afm' => normalize_afm($r['emp_afm']),
                'surname' => trim($r['emp_surname']),
                'name' => trim($r['emp_name']),
                'sch_name' => clean_str($r['sch_name'] ?? ''),
                'hm_dior' => (!empty($r['hm_dior']) && $r['hm_dior'] !== '0000-00-00') ? date('d/m/Y', strtotime($r['hm_dior'])) : ''
            ];
        }
    }
    $source_desc = 'Όλοι οι εν ενεργεία Διευθυντές / Υποδιευθυντές / Προϊστάμενοι σχολικών μονάδων';
}

function clean_str($val) {
    if ($val === null) return "";
    $s = trim((string)$val);
    $s = str_replace(["\xC2\xA0", "\xEF\xBB\xBF"], [' ', ''], $s);
    return trim($s);
}

function normalize_afm($afm) {
    $afm_clean = clean_str($afm);
    if (ctype_digit($afm_clean) && strlen($afm_clean) <= 9) {
        return str_pad($afm_clean, 9, '0', STR_PAD_LEFT);
    }
    return $afm_clean;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Αναφορά Διευθυντών / Προϊσταμένων</title>
    <script type="text/javascript" language="javascript" src='../js/jquery.js'></script>
    <?php 
      $root_path = '../';
      $page_title = 'Αναφορά Διευθυντών / Προϊσταμένων';
      require '../etc/head.php'; 
      require_once '../js/datatables/includes.html';
    ?>
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
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
            font-size: 13px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 9999px;
            border: 1px solid #bae6fd;
        }
        .badge-excluded {
            background: #fef2f2;
            color: #991b1b;
            font-size: 13px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 9999px;
            border: 1px solid #fecaca;
        }
        .info-banner {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #0d9488;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #334155;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            overflow-x: auto;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: #f1f5f9;
            color: #334155;
            text-decoration: none;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-back:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: #0284c7;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-download:hover {
            background: #0369a1;
            color: #ffffff;
        }
        .dt-buttons {
            margin-bottom: 15px;
        }
        .dt-button {
            padding: 6px 12px !important;
            border-radius: 4px !important;
            font-size: 13px !important;
            margin-right: 6px !important;
            font-weight: 500 !important;
        }
    </style>
    <script type="text/javascript">
    $(document).ready(function() {
        if ($('#directorsTable').length) {
            $('#directorsTable').DataTable({
                pageLength: 50,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Όλοι"]],
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
                        filename: 'dieythyntes_proistamenoi_' + new Date().toISOString().slice(0, 10),
                        title: 'Αναφορά Διευθυντών / Προϊσταμένων',
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
                        title: 'Αναφορά Διευθυντών / Υποδιευθυντών / Προϊσταμένων',
                        exportOptions: {
                            stripHtml: false
                        }
                    }
                ],
                language: {
                    url: "../js/datatables/greek.json"
                },
                order: [[3, 'asc'], [4, 'asc']]
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
                    👔 Αναφορά Διευθυντών / Υποδιευθυντών / Προϊσταμένων
                </h1>
                <span class="badge-count"><?php echo count($directors); ?> Εγγραφές</span>
                <span class="badge-excluded">Εξαιρούνται από τα CSV (εισαγωγή manually)</span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="create_eval_csv.php?download=directors" class="btn-download">
                    📥 Λήψη directors.csv
                </a>
                <a href="create_eval_csv.php" class="btn-back">
                    ⬅️ Επιστροφή στη Δημιουργία CSV
                </a>
            </div>
        </div>

        <!-- Info / Context Banner -->
        <div class="info-banner">
            <div>
                <span style="font-weight: 600;">Προέλευση δεδομένων:</span> <?php echo htmlspecialchars($source_desc); ?>
            </div>
            <div style="font-size: 13px; color: #64748b;">
                Κανόνας: Οι δ/ντές - υποδ/ντές - πρ/νοι δεν περιλαμβάνονται στα αρχεία CSV (a1, a2, b).
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <table id="directorsTable" class="display cell-border stripe hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">Α/Α</th>
                        <th style="width: 90px; text-align: center;">Α.Μ.</th>
                        <th style="width: 100px; text-align: center;">Α.Φ.Μ.</th>
                        <th style="width: 220px;">Επώνυμο</th>
                        <th style="width: 180px;">Όνομα</th>
                        <th>Σχολείο Υπηρέτησης</th>
                        <th style="width: 130px; text-align: center;">Ημ/νία Διορισμού</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $aa = 1;
                    foreach ($directors as $d):
                        $am = htmlspecialchars($d['am'] ?: '-');
                        $afm = htmlspecialchars($d['afm'] ?: '-');
                        $surname = htmlspecialchars($d['surname']);
                        $name = htmlspecialchars($d['name']);
                        $sch_name = htmlspecialchars($d['sch_name'] ?: '-');
                        $hm_dior = htmlspecialchars($d['hm_dior'] ?: '-');
                        $emp_id = $d['emp_id'] ?? null;
                    ?>
                    <tr>
                        <td style="text-align: center; color: #64748b; font-weight: 600;"><?php echo $aa; ?></td>
                        <td style="text-align: center; color: #475569; font-weight: 500;"><?php echo $am; ?></td>
                        <td style="text-align: center; color: #475569; font-weight: 500;"><?php echo $afm; ?></td>
                        <td>
                            <?php if ($emp_id): ?>
                                <a href="../employee/employee.php?id=<?php echo (int)$emp_id; ?>&op=view" target="_blank" style="color: #0f172a; font-weight: 600; text-decoration: none;" class="hover:underline" title="Προβολή καρτέλας εκπαιδευτικού">
                                    <?php echo $surname; ?>
                                </a>
                            <?php else: ?>
                                <strong style="color: #0f172a;"><?php echo $surname; ?></strong>
                            <?php endif; ?>
                        </td>
                        <td style="color: #334155; font-weight: 500;"><?php echo $name; ?></td>
                        <td style="color: #1e293b;"><?php echo $sch_name; ?></td>
                        <td style="text-align: center; color: #475569; font-weight: 500;"><?php echo $hm_dior; ?></td>
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
