<?php
require_once "../config.php";
require_once "../include/functions.php";


$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

require "../tools/class.login.php";
$log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
    exit();
}

// check if super-user
if ($_SESSION['userlevel']<>0) {
    echo 'Σφάλμα: Δεν επιτρέπεται η πρόσβαση σε αυτή τη σελίδα.';
    echo '<br><br><a href="../index.php">Επιστροφή στην αρχική σελίδα</a>';
    exit();
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Αναφορά Αξιολόγησης Εκπαιδευτικών</title>
    <script type="text/javascript" language="javascript" src='../js/jquery.js'></script>

    <?php 
      $root_path = '../';
      $page_title = 'Αναφορά Αξιολόγησης Εκπαιδευτικών';
      require '../etc/head.php'; 
      require_once '../js/datatables/includes.html';
    ?>
    <script type="text/javascript" src="../js/select2.min.js"></script>
    <link href="../css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container {
            min-width: 220px;
            vertical-align: middle;
        }
        .consultant-popover-wrapper:hover .consultant-popover-box {
            display: block !important;
        }
    </style>
    <script type="text/javascript">
    $(document).ready(function() {
        // Initialize select2 for multiple klados selection
        $('#klados').select2({
            placeholder: "Επιλέξτε κλάδο/ους",
            allowClear: true,
            width: 'resolve'
        });
        
        // Initialize DataTables if table exists
        if ($('#dataTable').length) {
            var table = $('#dataTable').DataTable({
                "pageLength": 50,
                "scrollX": true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Όλα"]],
                "dom": 'Bfrtip',
                "buttons": [
                    'copy', 'excel', 'pdf'
                ],
                "order": [],
                "language": {
                    "url": "../js/datatables/greek.json"
                }
            });
        }
    });
    </script>
</head>
<body>
    <?php require '../etc/menu.php'; ?>
<div id="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
        <h1 style="margin: 0;">Αναφορά Αξιολόγησης Εκπαιδευτικών</h1>
        <div style="display: flex; gap: 8px;">
            <a href="report_eval_symv.php" class="btn btn-primary" style="text-decoration: none; padding: 7px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-weight: bold; background: #0284c7;">
                👥 Υποχρεώσεις ανά Σύμβουλο (Excel / Email)
            </a>
            <a href="create_eval_csv.php" class="btn btn-green" style="text-decoration: none; padding: 7px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-weight: bold;">
                ⚙️ Δημιουργία CSV Αξιολόγησης
            </a>
        </div>
    </div>
    
    <!-- Search Form -->
    <form method="post">
        <div class="filter-section">
            <label>Ημ/νία Διορισμού από:</label>
            <input type="date" name="hm_dior_from" value="<?php echo isset($_POST['hm_dior_from']) ? $_POST['hm_dior_from'] : ''; ?>">
            
            <label>έως:</label>
            <input type="date" name="hm_dior_to" value="<?php echo isset($_POST['hm_dior_to']) ? $_POST['hm_dior_to'] : ''; ?>">
            
            <label>Κλάδος:</label>
            <select name="klados[]" id="klados" multiple="multiple">
                <?php
                $query = "SELECT id, perigrafh FROM klados ORDER BY perigrafh";
                $result = mysqli_query($mysqlconnection, $query);
                while ($row = mysqli_fetch_array($result)) {
                    $selected = (isset($_POST['klados']) && in_array($row['id'], $_POST['klados'])) ? ' selected="selected"' : '';
                    echo "<option value='".$row['id']."'$selected>".$row['perigrafh']."</option>";
                }
                ?>
            </select>
            
            <label>Αριθμός αξιολογούμενων ανά σύμβουλο:</label>
            <input type="number" name="eval_per_consultant" min="1" style="width: 80px;" value="<?php echo isset($_POST['eval_per_consultant']) ? htmlspecialchars($_POST['eval_per_consultant']) : ''; ?>" placeholder="π.χ. 25">
            
            <input type="submit" name="submit" value="Αναζήτηση">
        </div>
    </form>

    <?php
    if (isset($_POST['submit'])) {
        $sxol_etos = getParam('sxol_etos', $mysqlconnection);
        $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ',$mysqlconnection);
        $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ',$mysqlconnection);
        $ekswteriko = getSchoolID('Απόσπαση στο εξωτερικό',$mysqlconnection);
        $foreas = getSchoolID('Απόσπαση σε φορέα',$mysqlconnection);
        $sxol_symv = getSchoolID('Σύμβουλος Εκπαίδευσης', $mysqlconnection);
        if (!$sxol_symv) {
            $sxol_symv = getSchoolID('Σύμβουλος εκπαίδευσης', $mysqlconnection);
        }
        $dipe = '398';

        $symv_school_ids = [];
        if ($sxol_symv) {
            $symv_school_ids[] = (int)$sxol_symv;
        }
        $res_symv = mysqli_query($mysqlconnection, "SELECT id FROM school WHERE name LIKE '%Σύμβουλος Εκπαίδευσης%' OR name LIKE '%Σύμβουλος εκπαίδευσης%'");
        if ($res_symv) {
            while ($r_s = mysqli_fetch_assoc($res_symv)) {
                $symv_school_ids[] = (int)$r_s['id'];
            }
        }
        if (empty($symv_school_ids)) {
            $symv_school_ids = [397];
        }
        $symv_school_ids = array_unique($symv_school_ids);
        $symv_school_ids_str = implode(',', $symv_school_ids);

        $excluded_schools = array_unique(array_filter([$allo_pysde, $allo_pyspe, $dipe, $foreas, $ekswteriko, ...$symv_school_ids]));
        $excluded_schools_str = implode(',', $excluded_schools);
        
        // Pre-load symvouloi_epist once to avoid N+1 queries and duplicate join rows
        $se_by_klados = [];
        $se_edra_allou = [];
        $se_aksiologhsh_ews = [];
        $se_res = mysqli_query($mysqlconnection, "SELECT id, klados, afm, eponymo, onoma, emp_id, sch_ids, edra_allou, aksiologhsh_ews FROM symvouloi_epist");
        while ($se_row = mysqli_fetch_assoc($se_res)) {
            $se_by_klados[$se_row['klados']][] = $se_row;
            $c_afm = trim($se_row['afm']);
            if (!empty($c_afm)) {
                if (!empty($se_row['edra_allou'])) {
                    $se_edra_allou[$c_afm] = true;
                }
                if (!empty($se_row['aksiologhsh_ews']) && $se_row['aksiologhsh_ews'] !== '0000-00-00') {
                    $se_aksiologhsh_ews[$c_afm] = $se_row['aksiologhsh_ews'];
                }
            }
        }

        // Pre-load PE60 consultant AFMs
        $pe60_consultant_afms = [];
        $res_pe60 = mysqli_query($mysqlconnection, "SELECT e.afm FROM symvouloi s JOIN employee e ON s.emp_id = e.id WHERE s.perif >= 600 OR e.klados = 1");
        while ($r_pe60 = mysqli_fetch_assoc($res_pe60)) {
            if (!empty($r_pe60['afm'])) {
                $pe60_consultant_afms[trim($r_pe60['afm'])] = true;
            }
        }

        // Build the query based on filters
        $query = "SELECT
            e.id as emp_id,
            e.surname as emp_surname,
            e.name as emp_name,
            e.afm as emp_afm,
            sp.surname as symv_paid_surname,
            sp.name as symv_paid_name,
            sp.afm as symv_paid_afm,
            d.surname as dnt_surname,
            d.name as dnt_name,
            d.afm as dnt_afm,
            k.perigrafh as klados,
            e.klados as klados_id,
            s.name as sch_name,
            s.id as sch_id,
            s.type2,
            s.perif,
            e.hm_dior,
            e.thesi
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
        LEFT JOIN symvouloi sm ON s.perif = sm.perif
        LEFT JOIN employee sp ON sm.emp_id = sp.id
        LEFT JOIN employee d ON (s.id = d.sx_yphrethshs AND d.thesi = 2 AND d.status IN (1,3))
        WHERE e.status = 1 
        AND e.sx_yphrethshs NOT IN ($excluded_schools_str)
        AND COALESCE(yp.primary_sch_id, e.sx_yphrethshs) NOT IN ($excluded_schools_str)
        AND (e.sx_organikhs NOT IN ($symv_school_ids_str) OR e.sx_organikhs IS NULL)
        AND e.id NOT IN (SELECT emp_id FROM yphrethsh WHERE yphrethsh IN ($symv_school_ids_str) AND sxol_etos = '$sxol_etos')
        AND s.id NOT IN ($symv_school_ids_str)
        AND s.type2 IN (0, 2)
        AND (e.aksiologhsh = 0 OR e.aksiologhsh IS NULL)"; // dhmosio, eidiko, un-evaluated

        // Add filters
        if (!empty($_POST['hm_dior_from'])) {
            $query .= " AND e.hm_dior >= '".$_POST['hm_dior_from']."'";
        }
        if (!empty($_POST['hm_dior_to'])) {
            $query .= " AND e.hm_dior <= '".$_POST['hm_dior_to']."'";
        }
        if (!empty($_POST['klados'])) {
            $klados = implode(",", array_map('intval', $_POST['klados']));
            $query .= " AND e.klados IN ($klados)";
        }

        $query .= " GROUP BY e.id ORDER BY 
            CASE 
                WHEN e.hm_dior >= '2020-01-01' THEN 1 
                WHEN e.hm_dior > '1950-01-01' THEN 2 
                ELSE 3 
            END ASC,
            CASE 
                WHEN e.hm_dior >= '2020-01-01' THEN e.hm_dior 
                ELSE NULL 
            END ASC,
            CASE 
                WHEN e.hm_dior < '2020-01-01' AND e.hm_dior > '1950-01-01' THEN e.hm_dior 
                ELSE NULL 
            END DESC,
            e.surname ASC, 
            e.name ASC";

        $result = mysqli_query($mysqlconnection, $query);

        $seen_table = [];
        $candidates_by_consultant = [];
        $consultant_is_pe60 = [];

        if ($result) {
            while ($row = mysqli_fetch_array($result)) {
                $emp_afm = trim($row['emp_afm']);
                if (isset($seen_table[$emp_afm]) || in_array($row['sch_id'], $symv_school_ids)) {
                    continue;
                }
                $seen_table[$emp_afm] = true;

                // Find consultant for this klados and school
                $consultants = $se_by_klados[$row['klados_id']] ?? [];
                $consultant_found = null;
                $default_consultant = null;

                foreach ($consultants as $c) {
                    if (!empty($c['sch_ids'])) {
                        $c_schools = explode(',', $c['sch_ids']);
                        if (in_array($row['sch_id'], $c_schools)) {
                            $consultant_found = $c;
                            break;
                        }
                    } else {
                        if (!$default_consultant) {
                            $default_consultant = $c;
                        }
                    }
                }

                $is_eae = (
                    in_array($row['klados'], ['ΠΕ60ΕΑΕ', 'ΠΕ70ΕΑΕ', 'ΠΕ61', 'ΠΕ71']) || 
                    in_array($row['klados_id'], [16, 17, 18, 19]) || 
                    ($row['type2'] == 2 && (in_array($row['klados'], ['ΠΕ60', 'ΠΕ70']) || in_array($row['klados_id'], [1, 2])))
                );

                if ($is_eae) {
                    // Για τους εκπ/κούς Ειδικής Αγωγής (ΠΕ60ΕΑΕ, ΠΕ70ΕΑΕ, ΠΕ61, ΠΕ71) ή εκπ/κούς γενικής που υπηρετούν σε ειδικό σχολείο (type2 = 2):
                    // Επιστημονική ευθύνη (Πεδίο Α1) έχει ο σύμβουλος Παιδαγωγικής ευθύνης του σχολείου
                    $symv_epist_afm = $row['symv_paid_afm'];
                    $symv_epist_surname = $row['symv_paid_surname'];
                    $symv_epist_name = $row['symv_paid_name'];

                    // Παιδαγωγική ευθύνη (Πεδίο Α2 & Β) έχει ο σύμβουλος Ειδικής Αγωγής (από τον πίνακα symvouloi_epist)
                    $eae_c = $consultant_found ?: $default_consultant;
                    if (!$eae_c && $row['type2'] == 2) {
                        $eae_kladoi = ($row['klados_id'] == 1 || $row['klados'] == 'ΠΕ60') ? [16, 17] : [18, 19];
                        foreach ($eae_kladoi as $ek) {
                            if (!empty($se_by_klados[$ek])) {
                                foreach ($se_by_klados[$ek] as $c) {
                                    if (!empty($c['sch_ids'])) {
                                        $c_schools = explode(',', $c['sch_ids']);
                                        if (in_array($row['sch_id'], $c_schools)) {
                                            $eae_c = $c;
                                            break 2;
                                        }
                                    } elseif (!$eae_c) {
                                        $eae_c = $c;
                                    }
                                }
                            }
                        }
                    }
                    if ($eae_c) {
                        $symv_paid_afm = $eae_c['afm'];
                        $symv_paid_surname = $eae_c['eponymo'];
                        $symv_paid_name = $eae_c['onoma'];
                    } else {
                        $symv_paid_afm = $row['symv_paid_afm'];
                        $symv_paid_surname = $row['symv_paid_surname'];
                        $symv_paid_name = $row['symv_paid_name'];
                    }
                } else {
                    if ($consultant_found) {
                        $symv_epist_afm = $consultant_found['afm'];
                        $symv_epist_surname = $consultant_found['eponymo'];
                        $symv_epist_name = $consultant_found['onoma'];
                    } elseif ($default_consultant) {
                        $symv_epist_afm = $default_consultant['afm'];
                        $symv_epist_surname = $default_consultant['eponymo'];
                        $symv_epist_name = $default_consultant['onoma'];
                    } elseif ($row['klados'] == 'ΠΕ60' || $row['klados'] == 'ΠΕ70') {
                        // Fallback to pedagogical consultant for PE60/PE70 if no specific scientific consultant
                        $symv_epist_afm = $row['symv_paid_afm'];
                        $symv_epist_surname = $row['symv_paid_surname'];
                        $symv_epist_name = $row['symv_paid_name'];
                    } else {
                        $symv_epist_afm = '';
                        $symv_epist_surname = '';
                        $symv_epist_name = '';
                    }

                    $symv_paid_afm = $row['symv_paid_afm'];
                    $symv_paid_surname = $row['symv_paid_surname'];
                    $symv_paid_name = $row['symv_paid_name'];
                }

                $taytish = (!empty($symv_epist_afm) && !empty($symv_paid_afm) && $symv_epist_afm === $symv_paid_afm);

                $row['resolved_symv_epist_afm'] = $symv_epist_afm;
                $row['resolved_symv_epist_surname'] = $symv_epist_surname;
                $row['resolved_symv_epist_name'] = $symv_epist_name;
                $row['resolved_symv_paid_afm'] = $symv_paid_afm;
                $row['resolved_symv_paid_surname'] = $symv_paid_surname;
                $row['resolved_symv_paid_name'] = $symv_paid_name;
                $row['resolved_taytish'] = $taytish;

                // Ομαδοποίηση βάσει συμβούλου επιστημονικής ευθύνης (Α1)
                $c_key = !empty($symv_epist_afm) ? trim($symv_epist_afm) : 'unassigned_' . $row['klados_id'];

                // Αν ο σύμβουλος έχει έδρα σε άλλο νομό, δώστου εκπαιδευτικούς μόνο από την ομάδα 1 (>= 2020)
                if (!empty($se_edra_allou[$c_key]) && (empty($row['hm_dior']) || $row['hm_dior'] < '2020-01-01')) {
                    continue;
                }
                if ($is_eae && !empty($symv_paid_afm) && !empty($se_edra_allou[trim($symv_paid_afm)]) && (empty($row['hm_dior']) || $row['hm_dior'] < '2020-01-01')) {
                    continue;
                }

                // Αν έχει οριστεί όριο ημερομηνίας αξιολόγησης (aksiologhsh_ews), επιλέγονται εκπαιδευτικοί 1ης ομάδας (>= 2020) έως την ημερομηνία αυτή
                if (!empty($se_aksiologhsh_ews[$c_key])) {
                    if (empty($row['hm_dior']) || $row['hm_dior'] < '2020-01-01' || $row['hm_dior'] > $se_aksiologhsh_ews[$c_key]) {
                        continue;
                    }
                }
                if ($is_eae && !empty($symv_paid_afm) && !empty($se_aksiologhsh_ews[trim($symv_paid_afm)])) {
                    if (empty($row['hm_dior']) || $row['hm_dior'] < '2020-01-01' || $row['hm_dior'] > $se_aksiologhsh_ews[trim($symv_paid_afm)]) {
                        continue;
                    }
                }

                $candidates_by_consultant[$c_key][] = $row;
                if (!isset($consultant_is_pe60[$c_key])) {
                    $consultant_is_pe60[$c_key] = (isset($pe60_consultant_afms[$c_key]) || $row['klados'] === 'ΠΕ60' || $row['klados_id'] == 1);
                }
            }
        }

        $eval_limit = (!empty($_POST['eval_per_consultant']) && (int)$_POST['eval_per_consultant'] > 0) ? (int)$_POST['eval_per_consultant'] : 0;

        $selected_rows = [];
        if ($eval_limit > 0) {
            foreach ($candidates_by_consultant as $c_key => $teacher_list) {
                $is_pe60 = !empty($consultant_is_pe60[$c_key]);
                $total_available = count($teacher_list);

                if ($is_pe60) {
                    // Για τους ΠΕ60: ελάχιστος αριθμός, δλδ τουλάχιστον Χ,
                    // συμπεριλαμβανομένων όλων των εκπαιδευτικών της ίδιας (τελευταίας) ημερομηνίας διορισμού
                    if ($total_available <= $eval_limit) {
                        $selected = $teacher_list;
                    } else {
                        $selected = array_slice($teacher_list, 0, $eval_limit);
                        $cutoff_date = $teacher_list[$eval_limit - 1]['hm_dior'];
                        if (!empty($cutoff_date) && $cutoff_date !== '0000-00-00') {
                            for ($idx = $eval_limit; $idx < $total_available; $idx++) {
                                if ($teacher_list[$idx]['hm_dior'] === $cutoff_date) {
                                    $selected[] = $teacher_list[$idx];
                                } else {
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    // Για τους υπόλοιπους συμβούλους: μέγιστο, δλδ. το πολύ Χ
                    $selected = array_slice($teacher_list, 0, $eval_limit);
                }

                foreach ($selected as $s) {
                    $selected_rows[] = $s;
                }
            }
        } else {
            foreach ($candidates_by_consultant as $teacher_list) {
                foreach ($teacher_list as $s) {
                    $selected_rows[] = $s;
                }
            }
        }

        // Ταξινόμηση επιλεγμένων εκπαιδευτικών κατά σύμβουλο και έπειτα κατά ημερομηνία διορισμού (αύξουσα από 2020+, φθίνουσα πριν το 2020)
        usort($selected_rows, function($a, $b) {
            $c_sur_a = trim($a['resolved_symv_epist_surname'] ?? '');
            $c_sur_b = trim($b['resolved_symv_epist_surname'] ?? '');

            if ($c_sur_a === '' && $c_sur_b !== '') return 1;
            if ($c_sur_a !== '' && $c_sur_b === '') return -1;
            if ($c_sur_a !== '' && $c_sur_b !== '') {
                $cmp_cs = strcmp($c_sur_a, $c_sur_b);
                if ($cmp_cs !== 0) return $cmp_cs;

                $c_name_a = trim($a['resolved_symv_epist_name'] ?? '');
                $c_name_b = trim($b['resolved_symv_epist_name'] ?? '');
                $cmp_cn = strcmp($c_name_a, $c_name_b);
                if ($cmp_cn !== 0) return $cmp_cn;
            }

            $d1 = $a['hm_dior'] ?? '';
            $d2 = $b['hm_dior'] ?? '';
            $g1 = ($d1 >= '2020-01-01') ? 1 : ((!empty($d1) && $d1 > '1950-01-01') ? 2 : 3);
            $g2 = ($d2 >= '2020-01-01') ? 1 : ((!empty($d2) && $d2 > '1950-01-01') ? 2 : 3);

            if ($g1 !== $g2) {
                $cmp = $g1 <=> $g2;
            } elseif ($g1 === 1) {
                $cmp = strcmp($d1, $d2); // Αύξουσα για >= 2020
            } elseif ($g1 === 2) {
                $cmp = strcmp($d2, $d1); // Φθίνουσα για < 2020
            } else {
                $cmp = strcmp($d1, $d2);
            }

            if ($cmp !== 0) return $cmp;
            $cmp_sur = strcmp($a['emp_surname'], $b['emp_surname']);
            if ($cmp_sur !== 0) return $cmp_sur;
            return strcmp($a['emp_name'], $b['emp_name']);
        });

        // Υπολογισμός αξιολογούμενων ανά σύμβουλο επιστημονικής ευθύνης
        $consultant_counts = [];
        $consultant_labels = [];
        foreach ($selected_rows as $r_item) {
            $c_key = !empty($r_item['resolved_symv_epist_afm']) ? trim($r_item['resolved_symv_epist_afm']) : ('unassigned_' . $r_item['klados']);
            $consultant_counts[$c_key] = ($consultant_counts[$c_key] ?? 0) + 1;
            if (!isset($consultant_labels[$c_key])) {
                $suffix_parts = [];
                if (!empty($se_edra_allou[$c_key])) {
                    $suffix_parts[] = 'Έδρα σε άλλο νομό';
                }
                if (!empty($se_aksiologhsh_ews[$c_key])) {
                    $suffix_parts[] = 'Έως ' . date('d/m/Y', strtotime($se_aksiologhsh_ews[$c_key]));
                }
                $c_suffix = !empty($suffix_parts) ? ' [' . implode(', ', $suffix_parts) . ']' : '';
                if (!empty($r_item['resolved_symv_epist_surname'])) {
                    $consultant_labels[$c_key] = trim($r_item['resolved_symv_epist_surname'] . ' ' . $r_item['resolved_symv_epist_name']) . ' (' . $r_item['klados'] . ')' . $c_suffix;
                } else {
                    $consultant_labels[$c_key] = 'Χωρίς σύμβουλο (' . $r_item['klados'] . ')';
                }
            }
        }
        arsort($consultant_counts);

        $tooltip_lines = ["Αριθμός αξιολογούμενων ανά σύμβουλο:"];
        foreach ($consultant_counts as $c_key => $cnt) {
            $tooltip_lines[] = "• " . $consultant_labels[$c_key] . ": " . $cnt;
        }
        $consultant_tooltip_text = implode("\n", $tooltip_lines);
        $consultant_tooltip_attr = htmlspecialchars($consultant_tooltip_text, ENT_QUOTES, 'UTF-8');

        ob_start();
        
        if (!empty($selected_rows)) {
            echo "<table id='dataTable' class='display'>";
            echo "<thead><tr>
                <th>Α/Α</th>
                <th>Επώνυμο</th>
                <th>Όνομα</th>
                <th>ΑΦΜ</th>
                <th>Σχολείο</th>
                <th>ΑΦΜ Συμβ.Επιστ.</th>
                <th>Επώνυμο Συμβ.Επιστ.</th>
                <th>Όνομα Συμβ.Επιστ.</th>
                <th>Συμβ.παιδ.επώνυμο</th>
                <th>Συμβ.παιδ.όνομα</th>
                <th>Συμβ.παιδ.ΑΦΜ</th>
                <th>Δ/ντής Επώνυμο</th>
                <th>Δ/ντής Όνομα</th>
                <th>Δ/ντής ΑΦΜ</th>
                <th>Ταύτιση</th>
                <th>Κλάδος</th>
                <th>Περιφέρεια</th>
                <th>Ημ. Διορ.</th>
                <th>Δντης/Πρνος</th>
            </tr></thead><tbody>";
            
            $count = 1;
            $evaluated_emp_ids = [];
            foreach ($selected_rows as $row) {
                $evaluated_emp_ids[] = (int)$row['emp_id'];

                $c_key = !empty($row['resolved_symv_epist_afm']) ? trim($row['resolved_symv_epist_afm']) : ('unassigned_' . $row['klados']);
                $c_count = $consultant_counts[$c_key] ?? 0;
                $c_title_attr = htmlspecialchars("Αξιολογούμενοι συμβούλου: " . $c_count, ENT_QUOTES, 'UTF-8');

                echo "<tr>";
                echo "<td>".$count++."</td>";
                echo "<td>".$row['emp_surname']."</td>";
                echo "<td>".$row['emp_name']."</td>";
                echo "<td>".$row['emp_afm']."</td>";
                echo "<td>".$row['sch_name']."</td>";
                echo "<td title=\"$c_title_attr\" style=\"cursor: help;\">".($row['resolved_symv_epist_afm'] ?: 'Δεν έχει οριστεί')."</td>";
                echo "<td title=\"$c_title_attr\" style=\"cursor: help; font-weight: 500;\">".($row['resolved_symv_epist_surname'] ?: 'Δεν έχει οριστεί')."</td>";
                echo "<td title=\"$c_title_attr\" style=\"cursor: help;\">".($row['resolved_symv_epist_name'] ?: 'Δεν έχει οριστεί')."</td>";
                echo "<td>".$row['resolved_symv_paid_surname']."</td>";
                echo "<td>".$row['resolved_symv_paid_name']."</td>";
                echo "<td>".$row['resolved_symv_paid_afm']."</td>";
                echo "<td>".$row['dnt_surname']."</td>";
                echo "<td>".$row['dnt_name']."</td>";
                echo "<td>".$row['dnt_afm']."</td>";
                echo "<td>".($row['resolved_taytish'] ? 'Ναι' : 'Όχι')."</td>";
                echo "<td>".$row['klados']."</td>";
                echo "<td>".$row['perif']."</td>";
                echo "<td>".$row['hm_dior']."</td>";
                echo "<td>".($row['thesi'] == 2 ? 'Ναι' : 'Όχι')."</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p style='margin-top: 15px;'>Δεν βρέθηκαν αποτελέσματα με τα επιλεγμένα κριτήρια.</p>";
        }
        $table_html = ob_get_clean();

        $unique_count = count($selected_rows);
        $_SESSION['eval_emp_ids'] = $evaluated_emp_ids;

        // Δημιουργία γραμμών για το popover panel
        $popover_rows_html = '';
        foreach ($consultant_counts as $c_key => $cnt) {
            $popover_rows_html .= "<tr>
                <td style='padding: 5px 8px; border-bottom: 1px solid #f1f5f9; text-align: left;'>" . htmlspecialchars($consultant_labels[$c_key], ENT_QUOTES, 'UTF-8') . "</td>
                <td style='padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #166534; text-align: right;'>" . $cnt . "</td>
            </tr>";
        }

        $count_display = "<div style='display: flex; justify-content: space-between; align-items: center; margin: 15px 0; flex-wrap: wrap; gap: 10px;'>
            <div style='display: inline-flex; align-items: center; gap: 12px;'>
                <div style='font-weight: bold; color: #1b5e20; font-size: 15px;'>
                    <span title=\"$consultant_tooltip_attr\" style='cursor: help; border-bottom: 1px dotted #1b5e20;'>$unique_count μοναδικοί εκπαιδευτικοί</span>
                </div>
                <div class='consultant-popover-wrapper' style='position: relative; display: inline-block;'>
                    <span class='badge-consultants' title=\"$consultant_tooltip_attr\" style='cursor: pointer; background: #e8f5e9; color: #1b5e20; border: 1px solid #a5d6a7; padding: 5px 12px; border-radius: 16px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);'>
                        👥 Αριθμός ανά σύμβουλο (" . count($consultant_counts) . ") ▾
                    </span>
                    <div class='consultant-popover-box' style='display: none; position: absolute; top: calc(100% + 5px); left: 0; z-index: 1050; min-width: 350px; max-height: 420px; overflow-y: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); padding: 12px;'>
                        <div style='font-weight: bold; font-size: 13px; color: #0f172a; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between;'>
                            <span>Σύμβουλος</span>
                            <span>Αξιολογούμενοι</span>
                        </div>
                        <table style='width: 100%; border-collapse: collapse; font-size: 12.5px;'>
                            <tbody>
                                $popover_rows_html
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div>
                <form method='post' action='aksiologhsh_topo.php' target='_blank' style='margin: 0; display: inline-block;'>
                    <input type='hidden' name='emp_ids' value='" . implode(',', $evaluated_emp_ids) . "'>
                    <button type='submit' class='btn btn-blue' style='cursor: pointer; padding: 7px 16px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-weight: bold; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.12);'>
                        🏫 Αναφορά Τοποθετήσεων Αξιολογούμενων ($unique_count)
                    </button>
                </form>
            </div>
        </div>";

        echo $count_display;
        echo $table_html;
    }
    ?>
</div>
</body>
</html>