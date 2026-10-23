<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('memory_limit', '512M');
set_time_limit(300);

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../include/functions.php";
require_once __DIR__ . "/../vendor/autoload.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

require_once __DIR__ . "/../tools/class.login.php";
$log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
    exit();
}

// User level check
$is_admin = isset($_SESSION['userlevel']) && (int)$_SESSION['userlevel'] === 0;

// -------------------------------------------------------------
// Helper Functions
// -------------------------------------------------------------
if (!function_exists('normalize_afm')) {
    function normalize_afm($afm) {
        $afm = trim((string)$afm);
        $afm = str_replace(["\xC2\xA0", "\xEF\xBB\xBF", '"', "'"], '', $afm);
        if (ctype_digit($afm) && strlen($afm) <= 9) {
            return str_pad($afm, 9, '0', STR_PAD_LEFT);
        }
        return $afm;
    }
}

// Calculate target date (31/8 of the school year)
$se = getParam('sxol_etos', $mysqlconnection);
if ($se && strlen($se) >= 4) {
    $end_year = (int)substr($se, 0, 4) + 1;
    $sx_etos = substr($se, 0, 4) . '-' . substr($se, 4, 2);
} else {
    $curr_month = (int)date('n');
    $end_year = $curr_month >= 9 ? ((int)date('Y') + 1) : (int)date('Y');
    $sx_etos = ($end_year - 1) . '-' . substr((string)$end_year, -2);
}
$target_date = $end_year . "-08-31";
$sxol_etos_int = $se ? (int)$se : (int)(($end_year - 1) . substr((string)$end_year, -2));

// Email template and directorate parameter setup
$foreas = getParam('foreas', $mysqlconnection) ?: "Δ/νση Π.Ε. Ηρακλείου";
$email_symvouloi_param = getParam('email_symvouloi', $mysqlconnection);
if (empty($email_symvouloi_param)) {
    $email_symvouloi_param = "Αξιότιμε/η κ. {ΣΥΜΒΟΥΛΟΣ},\n\nΕπισυνάπτονται τα στοιχεία των αξιολογούμενων της αξιολογικής περιόδου {ΣΧΟΛ_ΕΤΟΣ} που σας αφορούν, στα πεδία Α1, Α2, Β.\n\nΣύνολο αξιολογούμενων: {ΠΛΗΘΟΣ}\n\nΜε εκτίμηση,\n{ΦΟΡΕΑΣ}";
}
$default_email_subject = ($foreas ? "$foreas: " : "") . "Στοιχεία αξιολογούμενων εκπαιδευτικών αξιολογικής περιόδου $sx_etos";
$default_email_body = str_replace(
    ['{ΣΧΟΛ_ΕΤΟΣ}', '{ΦΟΡΕΑΣ}'],
    [$sx_etos, $foreas],
    $email_symvouloi_param
);

// Handle Quick Email Save via AJAX/POST
if (isset($_POST['action']) && $_POST['action'] === 'save_consultant_email') {
    header('Content-Type: application/json; charset=utf-8');
    if (!$is_admin) {
        echo json_encode(['success' => false, 'message' => 'Δεν έχετε δικαίωμα επεξεργασίας.']);
        exit;
    }
    $c_afm = normalize_afm($_POST['consultant_afm'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    if (empty($c_afm)) {
        echo json_encode(['success' => false, 'message' => 'Μη έγκυρο ΑΦΜ συμβούλου.']);
        exit;
    }

    $c_afm_esc = mysqli_real_escape_string($mysqlconnection, $c_afm);
    $new_email_esc = mysqli_real_escape_string($mysqlconnection, $new_email);

    // Check if in symvouloi_epist
    $res_se = mysqli_query($mysqlconnection, "SELECT id FROM symvouloi_epist WHERE afm = '$c_afm_esc'");
    if (mysqli_num_rows($res_se) > 0) {
        mysqli_query($mysqlconnection, "UPDATE symvouloi_epist SET email = '$new_email_esc' WHERE afm = '$c_afm_esc'");
    }

    // Also update employee if exists
    $res_emp = mysqli_query($mysqlconnection, "SELECT id FROM employee WHERE afm = '$c_afm_esc'");
    if (mysqli_num_rows($res_emp) > 0) {
        mysqli_query($mysqlconnection, "UPDATE employee SET email = '$new_email_esc' WHERE afm = '$c_afm_esc'");
    }

    echo json_encode(['success' => true, 'email' => $new_email]);
    exit;
}

// -------------------------------------------------------------
// Core Data Loader: Loads Consultants and Evaluation Obligations
// -------------------------------------------------------------
function load_evaluation_dataset($conn, $sxol_etos_int, $target_date, $end_year, $source = 'auto', array $db_filters = []) {
    // 1. Load All Consultants (General & Specialty)
    $consultants = [];

    // Pedagogical Consultants (symvouloi)
    $res_symv = mysqli_query($conn, "SELECT s.id, s.perif, s.emp_id, e.afm, e.surname, e.name, e.email, e.email_psd, e.klados, se.email as epist_email 
                                      FROM symvouloi s 
                                      JOIN employee e ON s.emp_id = e.id 
                                      LEFT JOIN symvouloi_epist se ON (se.emp_id = e.id OR se.afm = e.afm)
                                      ORDER BY s.perif ASC");
    if ($res_symv) {
        while ($r = mysqli_fetch_assoc($res_symv)) {
            $afm = normalize_afm($r['afm']);
            $klados_txt = ($r['klados'] == 1) ? 'ΠΕ60' : 'ΠΕ70';
            // Priority: 1. symvouloi_epist.email, 2. employee.email, 3. employee.email_psd
            $p_email = trim($r['epist_email'] ?: ($r['email'] ?: $r['email_psd']));
            $consultants[$afm] = [
                'afm' => $afm,
                'surname' => trim($r['surname']),
                'name' => trim($r['name']),
                'fullname' => trim($r['surname'] . ' ' . $r['name']),
                'type' => 'pedagogikos',
                'perif' => (int)$r['perif'],
                'klados' => $klados_txt,
                'label' => trim($r['surname'] . ' ' . $r['name']) . " ({$r['perif']}η Ενότητα - $klados_txt)",
                'email' => $p_email,
                'email_psd' => trim($r['email_psd'] ?? ''),
                'email_alt' => trim($r['email'] ?? ''),
                'teachers' => []
            ];
        }
    }

    // Specialty / EAE Consultants (symvouloi_epist)
    $res_symv_e = mysqli_query($conn, "SELECT se.*, k.perigrafh as klados_txt, e.email as emp_email, e.email_psd as emp_email_psd 
                                        FROM symvouloi_epist se 
                                        LEFT JOIN klados k ON se.klados = k.id 
                                        LEFT JOIN employee e ON (se.emp_id = e.id OR se.afm = e.afm) 
                                        ORDER BY se.eponymo, se.onoma");
    if ($res_symv_e) {
        while ($r = mysqli_fetch_assoc($res_symv_e)) {
            $afm = normalize_afm($r['afm']);
            $kt = $r['klados_txt'] ?: 'Ειδικότητα';
            // Priority: 1. symvouloi_epist.email, 2. employee.email, 3. employee.email_psd
            $c_email = trim($r['email'] ?: ($r['emp_email'] ?: $r['emp_email_psd']));
            if (!isset($consultants[$afm])) {
                $consultants[$afm] = [
                    'afm' => $afm,
                    'surname' => trim($r['eponymo']),
                    'name' => trim($r['onoma']),
                    'fullname' => trim($r['eponymo'] . ' ' . $r['onoma']),
                    'type' => 'eidikothta',
                    'perif' => null,
                    'klados' => $kt,
                    'label' => trim($r['eponymo'] . ' ' . $r['onoma']) . " ($kt)",
                    'email' => $c_email,
                    'email_psd' => trim($r['emp_email_psd'] ?? ''),
                    'email_alt' => trim($r['emp_email'] ?? ''),
                    'teachers' => []
                ];
            } else {
                // If explicit email is set in symvouloi_epist, it takes priority
                if (!empty($r['email'])) {
                    $consultants[$afm]['email'] = trim($r['email']);
                } elseif (empty($consultants[$afm]['email']) && !empty($c_email)) {
                    $consultants[$afm]['email'] = $c_email;
                }
            }
        }
    }

    // 2. Determine Obligations (Source CSV vs DB)
    $a1_file = __DIR__ . '/../word/a1.csv';
    $a2_file = __DIR__ . '/../word/a2.csv';
    $b_file  = __DIR__ . '/../word/b.csv';
    $csvs_exist = file_exists($a1_file) && file_exists($b_file);

    $actual_source = ($source === 'db' || (!$csvs_exist && $source === 'auto')) ? 'db' : 'csv';

    $teacher_afm_set = [];

    if ($actual_source === 'csv') {
        // --- Parse A1 ---
        if (file_exists($a1_file)) {
            $lines = file($a1_file);
            foreach (array_slice($lines, 1) as $l) {
                $p = str_getcsv($l, ';');
                if (count($p) >= 7) {
                    $cafm = normalize_afm($p[6]);
                    $tafm = normalize_afm($p[3]);
                    if (isset($consultants[$cafm]) && !empty($tafm)) {
                        $consultants[$cafm]['teachers'][$tafm]['fields']['A1'] = true;
                        $teacher_afm_set[$tafm] = true;
                    }
                }
            }
        }

        // --- Parse A2 ---
        if (file_exists($a2_file)) {
            $lines = file($a2_file);
            foreach (array_slice($lines, 1) as $l) {
                $p = str_getcsv($l, ';');
                if (count($p) >= 7) {
                    $cafm = normalize_afm($p[6]);
                    $tafm = normalize_afm($p[3]);
                    if (isset($consultants[$cafm]) && !empty($tafm)) {
                        $consultants[$cafm]['teachers'][$tafm]['fields']['A2'] = true;
                        $teacher_afm_set[$tafm] = true;
                    }
                }
            }
        }

        // --- Parse B ---
        if (file_exists($b_file)) {
            $lines = file($b_file);
            foreach (array_slice($lines, 1) as $l) {
                $p = str_getcsv($l, ';');
                if (count($p) >= 10) {
                    $cafm1 = normalize_afm($p[6]);
                    $cafm2 = normalize_afm($p[9]);
                    $tafm  = normalize_afm($p[3]);
                    if (!empty($tafm)) {
                        if (isset($consultants[$cafm1])) {
                            $consultants[$cafm1]['teachers'][$tafm]['fields']['B'] = true;
                            $teacher_afm_set[$tafm] = true;
                        }
                        if (!empty($cafm2) && isset($consultants[$cafm2])) {
                            $consultants[$cafm2]['teachers'][$tafm]['fields']['B'] = true;
                            $teacher_afm_set[$tafm] = true;
                        }
                    }
                }
            }
        }
    } else {
        // --- Calculate from Database ---
        $excluded_info = get_eval_excluded_schools($conn);
        $symv_school_ids = $excluded_info['symv_school_ids'];

        $lookups = get_eval_lookups($conn);
        $se_by_klados = $lookups['se_by_klados'];
        $se_edra_allou = $lookups['se_edra_allou'];
        $se_aksiologhsh_ews = $lookups['se_aksiologhsh_ews'];
        $pe60_consultant_afms = $lookups['pe60_consultant_afms'];
        $all_director_afms = $lookups['all_director_afms'];

        $query = build_eval_evaluees_query($conn, $sxol_etos_int, $db_filters, $excluded_info);
        $db_result = mysqli_query($conn, $query);
        $seen_table = [];
        $candidates_by_consultant = [];
        $consultant_is_pe60 = [];

        if ($db_result) {
            while ($row = mysqli_fetch_assoc($db_result)) {
                $emp_afm_raw = trim($row['emp_afm']);
                if (isset($seen_table[$emp_afm_raw]) || in_array($row['sch_id'], $symv_school_ids)) continue;
                $seen_table[$emp_afm_raw] = true;

                $eval_info = resolve_evaluee_consultants($row, $se_by_klados);
                $is_eae = $eval_info['is_eae'];
                $is_in_ent_ty = $eval_info['is_in_ent_ty'];
                $symv_epist_afm = $eval_info['symv_epist_afm'];
                $symv_epist_surname = $eval_info['symv_epist_surname'];
                $symv_epist_name = $eval_info['symv_epist_name'];
                $symv_paid_afm = $eval_info['symv_paid_afm'];
                $symv_paid_surname = $eval_info['symv_paid_surname'];
                $symv_paid_name = $eval_info['symv_paid_name'];
                $taytish = $eval_info['taytish'];

                $row['resolved_symv_epist_afm'] = $symv_epist_afm;
                $row['resolved_symv_paid_afm'] = $symv_paid_afm;

                $c_key = !empty($symv_epist_afm) ? trim($symv_epist_afm) : ('unassigned_' . $row['klados_id']);
                if (!empty($se_edra_allou[$c_key]) && (empty($row['hm_dior']) || $row['hm_dior'] < '2020-01-01')) continue;
                if ($is_eae && !empty($symv_paid_afm) && !empty($se_edra_allou[trim($symv_paid_afm)]) && (empty($row['hm_dior']) || $row['hm_dior'] < '2020-01-01')) continue;

                if (!empty($se_aksiologhsh_ews[$c_key])) {
                    if (empty($row['hm_dior']) || $row['hm_dior'] < '2020-01-01' || $row['hm_dior'] > $se_aksiologhsh_ews[$c_key]) continue;
                }
                if ($is_eae && !empty($symv_paid_afm) && !empty($se_aksiologhsh_ews[trim($symv_paid_afm)])) {
                    if (empty($row['hm_dior']) || $row['hm_dior'] < '2020-01-01' || $row['hm_dior'] > $se_aksiologhsh_ews[trim($symv_paid_afm)]) continue;
                }

                $candidates_by_consultant[$c_key][] = $row;
                if (!isset($consultant_is_pe60[$c_key])) {
                    $consultant_is_pe60[$c_key] = (isset($pe60_consultant_afms[$c_key]) || $row['klados'] === 'ΠΕ60' || $row['klados_id'] == 1);
                }
            }
        }

        $eval_limit = (!empty($db_filters['eval_per_consultant']) && (int)$db_filters['eval_per_consultant'] > 0) ? (int)$db_filters['eval_per_consultant'] : 0;
        $selected_rows = [];
        if ($eval_limit > 0) {
            foreach ($candidates_by_consultant as $c_key => $teacher_list) {
                $is_pe60 = !empty($consultant_is_pe60[$c_key]);
                $total_available = count($teacher_list);
                if ($is_pe60) {
                    if ($total_available <= $eval_limit) {
                        $selected = $teacher_list;
                    } else {
                        $selected = array_slice($teacher_list, 0, $eval_limit);
                        $cutoff_date = $teacher_list[$eval_limit - 1]['hm_dior'];
                        if (!empty($cutoff_date) && $cutoff_date !== '0000-00-00') {
                            for ($idx = $eval_limit; $idx < $total_available; $idx++) {
                                if ($teacher_list[$idx]['hm_dior'] === $cutoff_date) {
                                    $selected[] = $teacher_list[$idx];
                                } else break;
                            }
                        }
                    }
                } else {
                    $selected = array_slice($teacher_list, 0, $eval_limit);
                }
                foreach ($selected as $s) $selected_rows[] = $s;
            }
        } else {
            foreach ($candidates_by_consultant as $teacher_list) {
                foreach ($teacher_list as $s) $selected_rows[] = $s;
            }
        }

        $excluded_a2_ids = [12, 31];
        foreach ($selected_rows as $row) {
            $emp_afm = normalize_afm($row['emp_afm']);
            $se_afm = normalize_afm($row['resolved_symv_epist_afm']);
            $sp_afm = normalize_afm($row['resolved_symv_paid_afm']);
            $dnt_afm = normalize_afm($row['dnt_afm']);
            $is_director = ($row['thesi'] == 2 || (!empty($dnt_afm) && $dnt_afm === $emp_afm) || isset($all_director_afms[$emp_afm]));

            // Field A1
            if (!empty($se_afm) && isset($consultants[$se_afm])) {
                $consultants[$se_afm]['teachers'][$emp_afm]['fields']['A1'] = true;
                $teacher_afm_set[$emp_afm] = true;
            }

            // Field A2
            $is_excluded_from_a2 = in_array((int)($row['klados_id'] ?? 0), $excluded_a2_ids);
            if (!$is_excluded_from_a2) {
                $evaluator_afm = ($is_director || empty($dnt_afm)) ? $sp_afm : $dnt_afm;
                if (!empty($evaluator_afm) && isset($consultants[$evaluator_afm])) {
                    $consultants[$evaluator_afm]['teachers'][$emp_afm]['fields']['A2'] = true;
                    $teacher_afm_set[$emp_afm] = true;
                }
            }

            // Field B (Directors excluded)
            if (!$is_director && !empty($sp_afm) && isset($consultants[$sp_afm])) {
                $consultants[$sp_afm]['teachers'][$emp_afm]['fields']['B'] = true;
                $teacher_afm_set[$emp_afm] = true;
            }
        }
    }

    // 3. Batch Load Detailed Teacher & School Data
    $teacher_details = [];
    $teacher_placements = [];

    if (!empty($teacher_afm_set)) {
        $tafms_escaped = array_map(function($a) use ($conn) {
            return "'" . mysqli_real_escape_string($conn, $a) . "'";
        }, array_keys($teacher_afm_set));

        $chunks = array_chunk($tafms_escaped, 400);
        foreach ($chunks as $chunk) {
            $in_clause = implode(',', $chunk);
            $q_emp = "SELECT e.*, s.id as sid, s.code as scode, s.name as sname, k.perigrafh as klados_name 
                      FROM employee e 
                      LEFT JOIN school s ON e.sx_yphrethshs = s.id 
                      LEFT JOIN klados k ON e.klados = k.id 
                      WHERE e.afm IN ($in_clause)";
            $res_emp = mysqli_query($conn, $q_emp);
            if ($res_emp) {
                while ($r = mysqli_fetch_assoc($res_emp)) {
                    $tafm = normalize_afm($r['afm']);
                    $teacher_details[$tafm] = $r;
                }
            }
        }

        // Load placements from yphrethsh
        $emp_ids = [];
        foreach ($teacher_details as $td) {
            if (!empty($td['id'])) $emp_ids[] = (int)$td['id'];
        }
        if (!empty($emp_ids)) {
            $id_chunks = array_chunk($emp_ids, 500);
            foreach ($id_chunks as $ichunk) {
                $in_ids = implode(',', $ichunk);
                $q_yp = "SELECT y.emp_id, y.yphrethsh, y.hours, s.name as school_name 
                         FROM yphrethsh y 
                         JOIN school s ON y.yphrethsh = s.id 
                         WHERE y.emp_id IN ($in_ids) AND y.sxol_etos = $sxol_etos_int";
                $res_yp = mysqli_query($conn, $q_yp);
                if ($res_yp) {
                    while ($ry = mysqli_fetch_assoc($res_yp)) {
                        $teacher_placements[$ry['emp_id']][] = $ry;
                    }
                }
            }
        }
    }

    // 4. Enrich Each Consultant's Teachers
    foreach ($consultants as $cafm => &$c) {
        $enriched = [];
        foreach ($c['teachers'] as $tafm => $tdata) {
            $info = $teacher_details[$tafm] ?? null;
            if (!$info) continue;

            $fields_arr = array_keys($tdata['fields'] ?? []);
            // Sort fields order: A1, A2, B
            usort($fields_arr, function($a, $b) {
                $order = ['A1' => 1, 'Α1' => 1, 'A2' => 2, 'Α2' => 2, 'B' => 3, 'Β' => 3];
                return ($order[$a] ?? 9) <=> ($order[$b] ?? 9);
            });
            $fields_str = implode(', ', $fields_arr);

            // Additional schools calculation
            $loipa_sxoleia = '';
            $emp_id = $info['id'];
            if (isset($teacher_placements[$emp_id]) && count($teacher_placements[$emp_id]) > 1) {
                $other_schools = [];
                foreach ($teacher_placements[$emp_id] as $yp) {
                    if ((int)$yp['yphrethsh'] !== (int)$info['sid']) {
                        $hrs_str = (!empty($yp['hours']) && $yp['hours'] > 0) ? " (" . $yp['hours'] . " ώρες)" : "";
                        $other_schools[] = "• " . $yp['school_name'] . $hrs_str;
                    }
                }
                if (!empty($other_schools)) {
                    $loipa_sxoleia = implode(', ', $other_schools);
                }
            }

            // Total service calculation
            $synolikh = '-';
            if (!empty($info['hm_dior']) && $info['hm_dior'] != '0000-00-00') {
                $total_days = date2days($target_date) - date2days($info['hm_dior']) + (int)$info['proyp'];
                if ($total_days > 0) {
                    $ymd = days2ymd($total_days);
                    $synolikh = "{$ymd[0]} έτη, {$ymd[1]} μήνες, {$ymd[2]} ημέρες";
                }
            }

            $enriched[$tafm] = [
                'emp_id' => $info['id'],
                'am' => !empty($info['am']) ? trim((string)$info['am']) : '-',
                'afm' => $tafm,
                'sch_id' => !empty($info['sid']) ? $info['sid'] : $info['id'],
                'code' => $info['scode'] ?: '-',
                'sname' => $info['sname'] ?: '-',
                'loipa_sxoleia' => $loipa_sxoleia,
                'surname' => trim($info['surname']),
                'name' => trim($info['name']),
                'type' => 'Μόνιμος',
                'thesi' => ($info['thesi'] == 2) ? 'Δ/ντής/ντρια' : 'Εκπ/κός',
                'klados' => $info['klados_name'] ?: getKlados($info['klados'], $conn),
                'status' => ($info['status'] == 1) ? 'Εργάζεται' : 'Άδεια',
                'hm_dior' => (!empty($info['hm_dior']) && $info['hm_dior'] != '0000-00-00') ? date('d-m-Y', strtotime($info['hm_dior'])) : '-',
                'hm_dior_raw' => (!empty($info['hm_dior']) && $info['hm_dior'] != '0000-00-00') ? $info['hm_dior'] : '',
                'hm_anal' => (!empty($info['hm_anal']) && $info['hm_anal'] != '0000-00-00') ? date('d-m-Y', strtotime($info['hm_anal'])) : '-',
                'monimopoihsh' => ($info['monimopoihsh'] == 1) ? 'Ναι' : 'Όχι',
                'aksiologhsh' => ($info['aksiologhsh'] == 1) ? 'Ναι' : 'Όχι',
                'pedio' => $fields_str,
                'fields_arr' => $fields_arr,
                'synolikh' => $synolikh,
                'tel' => trim((string)$info['tel']),
                'email' => trim((string)$info['email']),
                'email_psd' => trim((string)$info['email_psd']),
            ];
        }

        // Sort teachers by Evaluation Field (A1 first), then descending appointment date, then surname/name
        uasort($enriched, function($a, $b) {
            $getFieldRank = function($item) {
                $f = $item['fields_arr'] ?? [];
                if (in_array('A1', $f) || in_array('Α1', $f)) {
                    return 1;
                }
                if (in_array('A2', $f) || in_array('Α2', $f)) {
                    return 2;
                }
                if (in_array('B', $f) || in_array('Β', $f)) {
                    return 3;
                }
                return 4;
            };

            $rank_a = $getFieldRank($a);
            $rank_b = $getFieldRank($b);
            if ($rank_a !== $rank_b) {
                return $rank_a <=> $rank_b;
            }

            // Descending appointment date (newest first)
            $dior_a = (!empty($a['hm_dior_raw']) && $a['hm_dior_raw'] !== '0000-00-00') ? $a['hm_dior_raw'] : '';
            $dior_b = (!empty($b['hm_dior_raw']) && $b['hm_dior_raw'] !== '0000-00-00') ? $b['hm_dior_raw'] : '';
            if ($dior_a !== $dior_b) {
                return strcmp($dior_b, $dior_a);
            }

            $cmp = strcmp($a['surname'], $b['surname']);
            return ($cmp !== 0) ? $cmp : strcmp($a['name'], $b['name']);
        });

        $c['teachers'] = $enriched;
        $c['total_count'] = count($enriched);
    }
    unset($c);

    // Sort consultants: Pedagogical first by perif, then Specialty alphabetically
    uasort($consultants, function($a, $b) {
        if ($a['type'] !== $b['type']) {
            return ($a['type'] === 'pedagogikos') ? -1 : 1;
        }
        if ($a['type'] === 'pedagogikos') {
            return ($a['perif'] <=> $b['perif']);
        }
        return strcmp($a['fullname'], $b['fullname']);
    });

    return [$consultants, $actual_source, count($teacher_afm_set)];
}

// -------------------------------------------------------------
// Excel Output Generator Functions
// -------------------------------------------------------------
function generate_consultant_excel_html($consultant, $sx_etos, $end_year) {
    ob_start();
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
    table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 13px; }
    th { background-color: #10b981; color: #ffffff; border: 1px solid #059669; padding: 6px 8px; text-align: left; }
    td { border: 1px solid #d1d5db; padding: 5px 8px; }
    tr:nth-child(even) { background-color: #f9fafb; }
    .text { mso-number-format: "\@"; }
    .center { text-align: center; }
    .badge-field { font-weight: bold; color: #047857; text-align: center; }
</style>
</head>
<body>
<h3>Αναφορά Υποχρεώσεων Αξιολόγησης - Σύμβουλος: <?=htmlspecialchars($consultant['label'])?> (Σχολικό Έτος: <?=$sx_etos?>)</h3>
<p>Συνολικός αριθμός αξιολογούμενων: <strong><?=count($consultant['teachers'])?></strong></p>
<table border="1">
<thead>
    <tr>
        <th>ΑΜ</th>
        <th>ΑΦΜ</th>
        <th>Επώνυμο</th>
        <th>Όνομα</th>
        <th>Κλάδος</th>
        <th style="background-color: #047857;">Πεδίο (Α1, Α2, Β)</th>
        <th>Θέση</th>
        <th>Τύπος</th>
        <th>Κατάσταση</th>
        <th>Κωδ. Σχολείου</th>
        <th>Ονομασία Σχολείου</th>
        <th>Λοιπά Σχολεία υπηρέτησης</th>
        <th>Ημ. Διορισμού</th>
        <th>Ημ. Ανάληψης</th>
        <th>Μονιμοποίηση</th>
        <th>Αξιολόγηση</th>
        <th>Συνολική Υπηρεσία (έως 31/08/<?=$end_year?>)</th>
        <th>Τηλέφωνο</th>
        <th>email</th>
        <th>email ΠΣΔ</th>
    </tr>
</thead>
<tbody>
<?php
    foreach ($consultant['teachers'] as $t) {
        echo "<tr>";
        echo "<td class='text center'>{$t['am']}</td>";
        echo "<td class='text center'>{$t['afm']}</td>";
        echo "<td>{$t['surname']}</td>";
        echo "<td>{$t['name']}</td>";
        echo "<td>{$t['klados']}</td>";
        echo "<td class='badge-field'>{$t['pedio']}</td>";
        echo "<td>{$t['thesi']}</td>";
        echo "<td class='center'>{$t['type']}</td>";
        echo "<td>{$t['status']}</td>";
        echo "<td class='text center'>{$t['code']}</td>";
        echo "<td>{$t['sname']}</td>";
        echo "<td>{$t['loipa_sxoleia']}</td>";
        echo "<td class='text center'>{$t['hm_dior']}</td>";
        echo "<td class='text center'>{$t['hm_anal']}</td>";
        echo "<td class='center'>{$t['monimopoihsh']}</td>";
        echo "<td class='center'>{$t['aksiologhsh']}</td>";
        echo "<td>{$t['synolikh']}</td>";
        echo "<td class='text'>{$t['tel']}</td>";
        echo "<td>{$t['email']}</td>";
        echo "<td>{$t['email_psd']}</td>";
        echo "</tr>\n";
    }
?>
</tbody>
</table>
</body>
</html>
    <?php
    return ob_get_clean();
}

function generate_all_consultants_excel_html($consultants, $sx_etos, $end_year) {
    ob_start();
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
    table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 13px; }
    th { background-color: #2563eb; color: #ffffff; border: 1px solid #1d4ed8; padding: 6px 8px; text-align: left; }
    td { border: 1px solid #d1d5db; padding: 5px 8px; }
    tr:nth-child(even) { background-color: #f9fafb; }
    .text { mso-number-format: "\@"; }
    .center { text-align: center; }
    .badge-field { font-weight: bold; color: #1e40af; text-align: center; }
</style>
</head>
<body>
<h3>Συγκεντρωτική Αναφορά Υποχρεώσεων Αξιολόγησης ανά Σύμβουλο (Σχολικό Έτος: <?=$sx_etos?>)</h3>
<table border="1">
<thead>
    <tr>
        <th>Σύμβουλος</th>
        <th>ΑΦΜ Συμβούλου</th>
        <th>ΑΜ</th>
        <th>ΑΦΜ</th>
        <th>Επώνυμο</th>
        <th>Όνομα</th>
        <th>Κλάδος</th>
        <th style="background-color: #1e3a8a;">Πεδίο (Α1, Α2, Β)</th>
        <th>Θέση</th>
        <th>Τύπος</th>
        <th>Κατάσταση</th>
        <th>Κωδ. Σχολείου</th>
        <th>Ονομασία Σχολείου</th>
        <th>Λοιπά Σχολεία υπηρέτησης</th>
        <th>Ημ. Διορισμού</th>
        <th>Ημ. Ανάληψης</th>
        <th>Μονιμοποίηση</th>
        <th>Αξιολόγηση</th>
        <th>Συνολική Υπηρεσία (έως 31/08/<?=$end_year?>)</th>
        <th>Τηλέφωνο</th>
        <th>email</th>
        <th>email ΠΣΔ</th>
    </tr>
</thead>
<tbody>
<?php
    foreach ($consultants as $c) {
        $c_label = $c['label'];
        $c_afm = $c['afm'];
        foreach ($c['teachers'] as $t) {
            echo "<tr>";
            echo "<td><b>{$c_label}</b></td>";
            echo "<td class='text center'>{$c_afm}</td>";
            echo "<td class='text center'>{$t['am']}</td>";
            echo "<td class='text center'>{$t['afm']}</td>";
            echo "<td>{$t['surname']}</td>";
            echo "<td>{$t['name']}</td>";
            echo "<td>{$t['klados']}</td>";
            echo "<td class='badge-field'>{$t['pedio']}</td>";
            echo "<td>{$t['thesi']}</td>";
            echo "<td class='center'>{$t['type']}</td>";
            echo "<td>{$t['status']}</td>";
            echo "<td class='text center'>{$t['code']}</td>";
            echo "<td>{$t['sname']}</td>";
            echo "<td>{$t['loipa_sxoleia']}</td>";
            echo "<td class='text center'>{$t['hm_dior']}</td>";
            echo "<td class='text center'>{$t['hm_anal']}</td>";
            echo "<td class='center'>{$t['monimopoihsh']}</td>";
            echo "<td class='center'>{$t['aksiologhsh']}</td>";
            echo "<td>{$t['synolikh']}</td>";
            echo "<td class='text'>{$t['tel']}</td>";
            echo "<td>{$t['email']}</td>";
            echo "<td>{$t['email_psd']}</td>";
            echo "</tr>\n";
        }
    }
?>
</tbody>
</table>
</body>
</html>
    <?php
    return ob_get_clean();
}

// -------------------------------------------------------------
// Parameters & State Initialization
// -------------------------------------------------------------
$req_source = $_POST['data_source'] ?? ($_GET['source'] ?? 'auto');
$filter_hm_from = $_POST['hm_dior_from'] ?? ($_GET['hm_dior_from'] ?? '');
$filter_hm_to = $_POST['hm_dior_to'] ?? ($_GET['hm_dior_to'] ?? '');
$filter_klados = $_POST['klados'] ?? ($_GET['klados'] ?? []);
$filter_eval_limit = $_POST['eval_per_consultant'] ?? ($_GET['eval_per_consultant'] ?? '');

$db_filters = [
    'hm_dior_from' => $filter_hm_from,
    'hm_dior_to' => $filter_hm_to,
    'klados' => $filter_klados,
    'eval_per_consultant' => $filter_eval_limit
];

// Load dataset
list($consultants, $actual_source, $total_teachers_count) = load_evaluation_dataset(
    $mysqlconnection,
    $sxol_etos_int,
    $target_date,
    $end_year,
    $req_source,
    $db_filters
);

// Determine selected consultant (default to first active or by AFM)
$selected_afm = isset($_GET['afm']) ? normalize_afm($_GET['afm']) : '';
if (empty($selected_afm) && !empty($consultants)) {
    // Select first consultant that has teachers
    foreach ($consultants as $cafm => $cdata) {
        if (!empty($cdata['teachers'])) {
            $selected_afm = $cafm;
            break;
        }
    }
    if (empty($selected_afm)) {
        $first_key = array_key_first($consultants);
        $selected_afm = $first_key ? $consultants[$first_key]['afm'] : '';
    }
}
$selected_consultant = $consultants[$selected_afm] ?? null;

// -------------------------------------------------------------
// Handle Excel Exports (Single, All Consolidated, All in ZIP)
// -------------------------------------------------------------
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];

    // 1. Export Single Consultant
    if ($export_type === 'excel' && !empty($selected_afm) && $selected_consultant) {
        $filename = "aksiologisi_" . $selected_afm . "_" . date('Ymd_His') . ".xls";
        $html = generate_consultant_excel_html($selected_consultant, $sx_etos, $end_year);

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Cache-Control: max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $html;
        exit;
    }

    // 2. Export All Consolidated in One Excel File
    if ($export_type === 'all_excel') {
        $filename = "aksiologisi_oloi_symvouloi_" . date('Ymd_His') . ".xls";
        $html = generate_all_consultants_excel_html($consultants, $sx_etos, $end_year);

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Cache-Control: max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $html;
        exit;
    }

    // 3. Export All Individual Files in ZIP
    if ($export_type === 'all_zip') {
        $zip_filename = "aksiologisi_symvouloi_excel_" . date('Ymd_His') . ".zip";
        $zip_filepath = sys_get_temp_dir() . '/' . $zip_filename;

        $zip = new ZipArchive();
        if ($zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($consultants as $c) {
                if (empty($c['teachers'])) continue;
                $clean_name = preg_replace('/[^a-zA-Z0-9_\x{0370}-\x{03FF}]/u', '_', $c['fullname']);
                $f_name = "aksiologisi_{$clean_name}_{$c['afm']}.xls";
                $html_content = generate_consultant_excel_html($c, $sx_etos, $end_year);
                $zip->addFromString($f_name, $html_content);
            }
            $zip->close();

            if (file_exists($zip_filepath)) {
                header("Content-Type: application/zip");
                header("Content-Disposition: attachment; filename=\"$zip_filename\"");
                header("Content-Length: " . filesize($zip_filepath));
                header("Cache-Control: max-age=0");
                readfile($zip_filepath);
                @unlink($zip_filepath);
                exit;
            }
        }
    }
}

// -------------------------------------------------------------
// Handle Email Sending (Bulk or Single, with Testing Mode)
// -------------------------------------------------------------
$email_results = null;

if ($is_admin && isset($_POST['send_emails'])) {
    $is_testing = !empty($_POST['testing']);
    $test_email = trim($_POST['test_email'] ?? 'it@dipe.ira.sch.gr');
    $target_scope = $_POST['email_scope'] ?? 'all'; // 'all' or 'selected'
    $custom_subject = trim($_POST['email_subject'] ?? '');
    $custom_body = trim($_POST['email_body'] ?? '');

    if (empty($custom_subject)) {
        $custom_subject = $default_email_subject;
    }
    if (empty($custom_body)) {
        $custom_body = $default_email_body;
    }

    // Set up SwiftMailer
    global $smtp_password, $smtp_username, $smtp_server, $smtp_port;
    $transport = Swift_SmtpTransport::newInstance($smtp_server, $smtp_port, 'ssl')
        ->setUsername($smtp_username)
        ->setPassword($smtp_password);
    $mailer = Swift_Mailer::newInstance($transport);
    $logger = new Swift_Plugins_Loggers_ArrayLogger();
    $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));

    $from_email = getParam('email', $mysqlconnection) ?: $smtp_username;
    $from_name = $foreas;

    // Determine target list of consultants (only those with evaluation records)
    $target_consultants = [];
    if ($target_scope === 'selected' && !empty($selected_consultant)) {
        if (!empty($selected_consultant['teachers'])) {
            $target_consultants[$selected_afm] = $selected_consultant;
        }
    } else {
        foreach ($consultants as $cafm => $cdata) {
            if (!empty($cdata['teachers'])) {
                $target_consultants[$cafm] = $cdata;
            }
        }
    }

    if (empty($target_consultants)) {
        $email_results = [
            'is_testing' => $is_testing,
            'test_email' => $test_email,
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 1,
            'logs' => [
                [
                    'afm' => $selected_afm ?? '-',
                    'name' => !empty($selected_consultant) ? $selected_consultant['label'] : 'Σύμβουλοι',
                    'intended_email' => !empty($selected_consultant['email']) ? $selected_consultant['email'] : '-',
                    'actual_email' => '-',
                    'teacher_count' => 0,
                    'status' => 'skipped',
                    'message' => 'Δεν υπάρχουν εγγραφές αξιολογούμενων. Το email δεν απεστάλη.'
                ]
            ]
        ];
    } else {
        $email_results = [
            'is_testing' => $is_testing,
            'test_email' => $test_email,
            'total' => count($target_consultants),
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'logs' => []
        ];

        foreach ($target_consultants as $cafm => $c) {
            $c_name = $c['fullname'];
            $c_label = $c['label'];
            $t_count = count($c['teachers'] ?? []);
            $real_email = trim($c['email'] ?? '');

            // Skip sending if no evaluation records exist for this consultant
            if ($t_count === 0) {
                $email_results['skipped']++;
                $email_results['logs'][] = [
                    'afm' => $cafm,
                    'name' => $c_label,
                    'intended_email' => $real_email ?: '-',
                    'actual_email' => '-',
                    'teacher_count' => 0,
                    'status' => 'skipped',
                    'message' => 'Δεν υπάρχουν εγγραφές αξιολογούμενων (παράλειψη αποστολής).'
                ];
                continue;
            }

            // Check if real email exists (unless in testing mode where test_email is used)
            if (!$is_testing && empty($real_email)) {
                $email_results['skipped']++;
                $email_results['logs'][] = [
                    'afm' => $cafm,
                    'name' => $c_label,
                    'intended_email' => '-',
                    'actual_email' => '-',
                    'teacher_count' => $t_count,
                    'status' => 'skipped',
                    'message' => 'Δεν έχει καταχωρηθεί email για τον σύμβουλο.'
                ];
                continue;
            }

            $recipient_email = $is_testing ? $test_email : $real_email;
            if (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
                $email_results['failed']++;
                $email_results['logs'][] = [
                    'afm' => $cafm,
                    'name' => $c_label,
                    'intended_email' => $real_email,
                    'actual_email' => $recipient_email,
                    'teacher_count' => $t_count,
                    'status' => 'failed',
                    'message' => 'Μη έγκυρη διεύθυνση email: ' . htmlspecialchars($recipient_email)
                ];
                continue;
            }

            // Generate Excel Attachment
            $excel_html = generate_consultant_excel_html($c, $sx_etos, $end_year);
            $clean_cname = preg_replace('/[^a-zA-Z0-9_\x{0370}-\x{03FF}]/u', '_', $c['fullname']);
            $att_filename = "aksiologisi_{$sx_etos}_{$clean_cname}_{$cafm}.xls";

            // Build Email Body & Subject
            $body_text = str_replace(
                ['{ΣΥΜΒΟΥΛΟΣ}', '{ΠΛΗΘΟΣ}', '{ΣΧΟΛ_ΕΤΟΣ}', '{ΦΟΡΕΑΣ}'],
                [$c_name, $t_count, $sx_etos, $foreas],
                $custom_body
            );

            $subj = $custom_subject;
            if ($is_testing) {
                $subj = "[TESTING] " . $subj . " (Προορισμός: $c_name)";
                $body_text = "⚠️ [ΔΟΚΙΜΑΣΤΙΚΗ ΑΠΟΣΤΟΛΗ / TESTING MODE]\nΤο email αυτό προοριζόταν για τον σύμβουλο: $c_name ($real_email)\n--------------------------------------------------\n\n" . $body_text;
            }

            try {
                $message = Swift_Message::newInstance($subj)
                    ->setFrom([$from_email => $from_name])
                    ->setTo([$recipient_email => $c_name])
                    ->setBody($body_text);

                // Attach Excel file
                $attachment = Swift_Attachment::newInstance($excel_html, $att_filename, 'application/vnd.ms-excel');
                $message->attach($attachment);

                $send_res = $mailer->send($message);
                if ($send_res > 0) {
                    $email_results['success']++;
                    $email_results['logs'][] = [
                        'afm' => $cafm,
                        'name' => $c_label,
                        'intended_email' => $real_email ?: '-',
                        'actual_email' => $recipient_email,
                        'attachment' => $att_filename,
                        'teacher_count' => $t_count,
                        'status' => 'success',
                        'message' => 'Απεστάλη επιτυχώς.'
                    ];
                } else {
                    $email_results['failed']++;
                    $email_results['logs'][] = [
                        'afm' => $cafm,
                        'name' => $c_label,
                        'intended_email' => $real_email ?: '-',
                        'actual_email' => $recipient_email,
                        'attachment' => $att_filename,
                        'teacher_count' => $t_count,
                        'status' => 'failed',
                        'message' => 'Αποτυχία αποστολής από τον διακομιστή.'
                    ];
                }
            } catch (Exception $e) {
                $email_results['failed']++;
                $email_results['logs'][] = [
                    'afm' => $cafm,
                    'name' => $c_label,
                    'intended_email' => $real_email ?: '-',
                    'actual_email' => $recipient_email,
                    'attachment' => $att_filename,
                    'teacher_count' => $t_count,
                    'status' => 'failed',
                    'message' => 'Σφάλμα: ' . $e->getMessage()
                ];
            }

            // Anti-flood pause
            usleep(150000); // 150ms
        }
    }

    // Write to tools/mail.log
    $log_dump = $logger->dump();
    if (!empty($log_dump)) {
        @file_put_contents(__DIR__ . '/../tools/mail.log', "[" . date('Y-m-d H:i:s') . "] EVALUATION ADVISOR EMAILS:\n" . $log_dump . "\n", FILE_APPEND);
    }
}

// Calculate Statistics
$total_active_consultants = 0;
$total_a1_count = 0;
$total_a2_count = 0;
$total_b_count = 0;
$consultants_with_email = 0;

foreach ($consultants as $c) {
    if (!empty($c['teachers'])) {
        $total_active_consultants++;
        if (!empty($c['email'])) $consultants_with_email++;
        foreach ($c['teachers'] as $t) {
            if (!empty($t['fields_arr'])) {
                if (in_array('A1', $t['fields_arr']) || in_array('Α1', $t['fields_arr'])) $total_a1_count++;
                if (in_array('A2', $t['fields_arr']) || in_array('Α2', $t['fields_arr'])) $total_a2_count++;
                if (in_array('B', $t['fields_arr']) || in_array('Β', $t['fields_arr'])) $total_b_count++;
            }
        }
    }
}

header('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Υποχρεώσεις Αξιολόγησης ανά Σύμβουλο</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <?php 
      $root_path = '../';
      $page_title = 'Υποχρεώσεις Αξιολόγησης ανά Σύμβουλο';
      require __DIR__ . '/../etc/head.php'; 
      require_once __DIR__ . '/../js/datatables/includes.html';
    ?>
    <script type="text/javascript" src="../js/select2.min.js"></script>
    <link href="../css/select2.min.css" rel="stylesheet" />
    <style>
        .page-container {
            max-width: 1350px;
            margin: 20px auto;
            padding: 0 15px;
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .kpi-val {
            font-size: 22px;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.2;
        }
        .kpi-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .action-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px 22px;
            margin-bottom: 22px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .action-card h3 {
            margin: 0 0 14px 0;
            color: #1e293b;
            font-size: 16px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .badge-a1 {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        .badge-a2 {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        .badge-b {
            background-color: #f3e8ff;
            color: #6b21a8;
            border: 1px solid #d8b4fe;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        .btn-modern {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 13.5px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-excel-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white !important;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.25);
        }
        .btn-excel-green:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        .btn-mail-blue {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white !important;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
        }
        .btn-mail-blue:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        }
        .btn-zip-amber {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white !important;
            box-shadow: 0 2px 4px rgba(217, 119, 6, 0.25);
        }
        .btn-zip-amber:hover {
            background: linear-gradient(135deg, #b45309 0%, #92400e 100%);
        }
        .testing-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 12px 16px;
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .select2-container .select2-selection--single {
            height: 38px !important;
            border-color: #cbd5e1 !important;
            display: flex !important;
            align-items: center !important;
        }
        .table-wrapper {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-top: 20px;
            overflow-x: auto;
        }
        #evalTeachersTable {
            width: 100% !important;
        }
        #evalTeachersTable th, #evalTeachersTable td {
            white-space: nowrap;
        }
        td.dt-control {
            text-align: center;
            cursor: pointer;
            vertical-align: middle;
            width: 36px !important;
            max-width: 36px !important;
        }
        .dt-expand-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 4px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #2563eb;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            line-height: 1;
            padding: 0;
        }
        .dt-expand-btn:hover {
            background: #e0f2fe;
            border-color: #3b82f6;
            color: #1d4ed8;
        }
        tr.shown .dt-expand-btn {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #dc2626;
        }
        .child-details-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 6px 0;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
            text-align: left;
        }
        .child-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 12px;
        }
        .child-section {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
        }
        .child-section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 6px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .child-field-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
            margin-bottom: 5px;
            gap: 8px;
        }
        .child-field-row:last-child {
            margin-bottom: 0;
        }
        .child-field-label {
            color: #64748b;
            white-space: nowrap;
        }
        .child-field-val {
            font-weight: 600;
            color: #1e293b;
            text-align: right;
            word-break: break-word;
        }
        tr.shown > td {
            background-color: #f0fdf4 !important;
        }
        tr.child-row-expanded > td {
            padding: 0 !important;
            background-color: #f8fafc !important;
            white-space: normal !important;
        }
    </style>
    <script type="text/javascript">
    $(document).ready(function() {
        $('#symvoulos_select').select2({
            placeholder: "Επιλέξτε Σύμβουλο Εκπαίδευσης",
            width: '100%'
        }).on('change', function() {
            var val = $(this).val();
            if (val) {
                var url = new URL(window.location.href);
                url.searchParams.set('afm', val);
                window.location.href = url.toString();
            }
        });

        if ($('#evalTeachersTable').length) {
            var evalTable = $('#evalTeachersTable').DataTable({
                pageLength: 50,
                autoWidth: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Όλοι"]],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        text: '📋 Αντιγραφή',
                        exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] }
                    },
                    {
                        text: '📊 Excel (όλες οι στήλες)',
                        className: 'btn-excel-export',
                        action: function (e, dt, node, config) {
                            window.location.href = 'report_eval_symv.php?export=excel&afm=<?=$selected_afm?>&source=<?=$actual_source?>';
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '📄 PDF',
                        exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] }
                    }
                ],
                order: [[3, 'asc'], [7, 'desc']],
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false, width: '36px' },
                    { targets: [3, 7], className: 'dt-center', orderable: true }
                ],
                language: {
                    url: '../js/datatables/greek.json'
                }
            });

            // Expand/Collapse single row
            $('#evalTeachersTable tbody').on('click', 'td.dt-control', function (e) {
                e.stopPropagation();
                var tr = $(this).closest('tr');
                var row = evalTable.row(tr);
                var childId = tr.data('child-id');

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    $(this).find('.dt-expand-btn').html('➕').attr('title', 'Ανάπτυξη λεπτομερειών');
                } else {
                    var childHtml = $('#child_tpl_' + childId).html();
                    row.child(childHtml, 'child-row-expanded').show();
                    tr.addClass('shown');
                    $(this).find('.dt-expand-btn').html('➖').attr('title', 'Σύμπτυξη λεπτομερειών');
                }
            });

            // Optional click on row toggles child (unless clicking an anchor or button)
            $('#evalTeachersTable tbody').on('click', 'tr', function (e) {
                if ($(e.target).closest('a, button, input, td.dt-control').length) {
                    return;
                }
                $(this).find('td.dt-control').trigger('click');
            });

            // Expand/Collapse All button
            $('#toggle_all_child_rows').click(function () {
                var btn = $(this);
                var isExpanded = btn.data('expanded') === true;

                evalTable.rows().every(function () {
                    var tr = $(this.node());
                    var childId = tr.data('child-id');
                    if (isExpanded) {
                        if (this.child.isShown()) {
                            this.child.hide();
                            tr.removeClass('shown');
                            tr.find('.dt-expand-btn').html('➕').attr('title', 'Ανάπτυξη λεπτομερειών');
                        }
                    } else {
                        var childHtml = $('#child_tpl_' + childId).html();
                        this.child(childHtml, 'child-row-expanded').show();
                        tr.addClass('shown');
                        tr.find('.dt-expand-btn').html('➖').attr('title', 'Σύμπτυξη λεπτομερειών');
                    }
                });

                if (isExpanded) {
                    btn.data('expanded', false).html('➕ Ανάπτυξη όλων');
                } else {
                    btn.data('expanded', true).html('➖ Σύμπτυξη όλων');
                }
            });
        }

        // Toggle email options panel
        $('#toggle_email_panel').click(function() {
            $('#email_panel').slideToggle();
        });

        // AJAX update consultant email
        $('.save-email-btn').click(function() {
            var btn = $(this);
            var afm = btn.data('afm');
            var emailVal = $('#email_input_' + afm).val();
            btn.prop('disabled', true).text('Αποθήκευση...');
            $.post('report_eval_symv.php', {
                action: 'save_consultant_email',
                consultant_afm: afm,
                email: emailVal
            }, function(resp) {
                btn.prop('disabled', false).text('💾 Αποθήκευση');
                if (resp && resp.success) {
                    $('#email_status_' + afm).html('<span style="color:#059669; font-weight:bold; font-size:12px;">✅ Αποθηκεύτηκε</span>').fadeIn().delay(2000).fadeOut();
                } else {
                    alert('Σφάλμα κατά την αποθήκευση email.');
                }
            }, 'json').fail(function() {
                btn.prop('disabled', false).text('💾 Αποθήκευση');
                alert('Σφάλμα επικοινωνίας με τον διακομιστή.');
            });
        });
    });
    </script>
</head>
<body>
    <?php require __DIR__ . '/../etc/menu.php'; ?>
<div class="page-container">

    <!-- Header Navigation & Actions -->
    <div class="header-bar">
        <div>
            <h1 style="margin: 0; font-size: 24px; color: #0f172a;">Υποχρεώσεις Αξιολόγησης ανά Σύμβουλο</h1>
            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 13.5px;">
                Σχολικό Έτος: <strong><?=$sx_etos?></strong> | Πηγή δεδομένων: 
                <span style="font-weight: bold; color: <?=$actual_source === 'csv' ? '#059669' : '#2563eb'?>;">
                    <?=$actual_source === 'csv' ? '📁 Αρχεία CSV αξιολόγησης (word/a1, a2, b)' : '🗄️ Βάση Δεδομένων Proteas'?>
                </span>
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <?php if ($is_admin): ?>
            <a href="create_eval_csv.php" class="btn btn-primary" style="text-decoration: none; padding: 7px 12px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
                ⚙️ Δημιουργία CSV
            </a>
            <a href="aksiologhsh_report.php" class="btn btn-primary" style="text-decoration: none; padding: 7px 12px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
                📊 Αναφορά Αξιολόγησης
            </a>
            <?php endif; ?>
            <a href="report_symv.php" class="btn btn-secondary" style="text-decoration: none; padding: 7px 12px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
                👥 Σύμβουλοι ΠΕ60/70
            </a>
            <a href="report_symv_eid.php" class="btn btn-secondary" style="text-decoration: none; padding: 7px 12px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
                👥 Σύμβουλοι Ειδικοτήτων
            </a>
        </div>
    </div>

    <!-- Email Results Alert (if form was submitted) -->
    <?php if ($is_admin && $email_results !== null): ?>
    <div class="action-card" style="border-left: 5px solid <?=$email_results['failed'] > 0 ? '#ef4444' : '#10b981'?>; background: <?=$email_results['is_testing'] ? '#fffbeb' : '#f8fafc'?>;">
        <h3 style="color: <?=$email_results['failed'] > 0 ? '#b91c1c' : '#065f46'?>; margin-bottom: 8px;">
            <?=$email_results['is_testing'] ? '🧪 Αποτέλεσμα Δοκιμαστικής Αποστολής (Testing Mode)' : '✉️ Αποτέλεσμα Αποστολής Email'?>
        </h3>
        <p style="margin: 0 0 12px 0; font-size: 14px;">
            Σύνολο: <strong><?=$email_results['total']?></strong> | 
            Επιτυχείς: <strong style="color: #059669;"><?=$email_results['success']?></strong> | 
            Αποτυχίες: <strong style="color: #dc2626;"><?=$email_results['failed']?></strong> | 
            Παραλείφθηκαν (χωρίς email): <strong style="color: #d97706;"><?=$email_results['skipped']?></strong>
            <?php if ($email_results['is_testing']): ?>
                <br><span style="color: #b45309; font-weight: bold;">Όλα τα δοκιμαστικά μηνύματα απεστάλησαν αποκλειστικά στη διεύθυνση δοκιμής: <code><?=htmlspecialchars($email_results['test_email'])?></code></span>
            <?php endif; ?>
        </p>
        <div style="max-height: 250px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                <thead style="background: #f1f5f9; position: sticky; top: 0;">
                    <tr>
                        <th style="padding: 6px 10px; border-bottom: 1px solid #cbd5e1; text-align: left;">Σύμβουλος</th>
                        <th style="padding: 6px 10px; border-bottom: 1px solid #cbd5e1; text-align: left;">Πραγματικό Email</th>
                        <th style="padding: 6px 10px; border-bottom: 1px solid #cbd5e1; text-align: left;">Αποστολή σε</th>
                        <th style="padding: 6px 10px; border-bottom: 1px solid #cbd5e1; text-align: center;">Εκπ/κοί</th>
                        <th style="padding: 6px 10px; border-bottom: 1px solid #cbd5e1; text-align: left;">Κατάσταση</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($email_results['logs'] as $log_item): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 6px 10px;"><?=htmlspecialchars($log_item['name'])?></td>
                        <td style="padding: 6px 10px;"><code><?=htmlspecialchars($log_item['intended_email'])?></code></td>
                        <td style="padding: 6px 10px;"><code><?=htmlspecialchars($log_item['actual_email'])?></code></td>
                        <td style="padding: 6px 10px; text-align: center; font-weight: bold;"><?=$log_item['teacher_count']?></td>
                        <td style="padding: 6px 10px;">
                            <?php if ($log_item['status'] === 'success'): ?>
                                <span style="color: #059669; font-weight: bold;">✅ Επιτυχία</span>
                            <?php elseif ($log_item['status'] === 'skipped'): ?>
                                <span style="color: #d97706; font-weight: bold;">⚠️ Παράλειψη (Χωρίς email)</span>
                            <?php else: ?>
                                <span style="color: #dc2626; font-weight: bold;">❌ <?=htmlspecialchars($log_item['message'])?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- KPI Summary Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: #e0f2fe; color: #0284c7;">👥</div>
            <div>
                <div class="kpi-val"><?=$total_active_consultants?></div>
                <div class="kpi-label">Ενεργοί Σύμβουλοι</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: #dcfce7; color: #16a34a;">📋</div>
            <div>
                <div class="kpi-val"><?=$total_teachers_count?></div>
                <div class="kpi-label">Μοναδικοί Εκπ/κοί</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: #ecfdf5; color: #059669;">Α1</div>
            <div>
                <div class="kpi-val"><?=$total_a1_count?></div>
                <div class="kpi-label">Υποχρεώσεις Πεδίου Α1</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: #eff6ff; color: #2563eb;">Α2</div>
            <div>
                <div class="kpi-val"><?=$total_a2_count?></div>
                <div class="kpi-label">Υποχρεώσεις Πεδίου Α2</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: #faf5ff; color: #7c3aed;">Β</div>
            <div>
                <div class="kpi-val"><?=$total_b_count?></div>
                <div class="kpi-label">Υποχρεώσεις Πεδίου Β</div>
            </div>
        </div>
    </div>

    <!-- Main Actions & Controls Card (Admin only) -->
    <?php if ($is_admin): ?>
    <div class="action-card">
        <h3>⚡ Ενέργειες Εξαγωγής & Αποστολής Email</h3>
        
        <form method="POST" id="mainActionForm" onsubmit="return validateEmailForm();">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <!-- 1. Export All in Excel -->
                    <a href="report_eval_symv.php?export=all_excel&source=<?=$actual_source?>" class="btn-modern btn-excel-green" title="Εξαγωγή όλων των συμβούλων σε ένα ενιαίο αρχείο Excel">
                        📊 Εξαγωγή όλων σε Excel
                    </a>

                    <!-- 2. Export All in ZIP -->
                    <a href="report_eval_symv.php?export=all_zip&source=<?=$actual_source?>" class="btn-modern btn-zip-amber" title="Λήψη αρχείου ZIP με ξεχωριστό Excel ανά σύμβουλο">
                        📦 Λήψη όλων σε ZIP
                    </a>

                    <!-- 3. Bulk Send Email Button -->
                    <button type="submit" name="send_emails" value="1" class="btn-modern btn-mail-blue" onclick="document.getElementById('email_scope').value='all';">
                        ✉️ Αποστολή με email (σε όλους)
                    </button>

                    <!-- Toggle Email Settings -->
                    <button type="button" id="toggle_email_panel" class="btn btn-secondary" style="font-size: 13px; padding: 7px 12px; border-radius: 6px; cursor: pointer;">
                        ⚙️ Ρυθμίσεις / Κείμενο Email ▾
                    </button>
                </div>

                <!-- Testing Mode Checkbox -->
                <div class="testing-box">
                    <label style="font-weight: bold; color: #92400e; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; margin: 0;">
                        <input type="checkbox" name="testing" id="testing_checkbox" value="1" checked style="width: 18px; height: 18px; cursor: pointer;">
                        🧪 Δοκιμαστική αποστολή (Testing mode)
                    </label>
                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="font-size: 12.5px; color: #78350f;">Email δοκιμής:</span>
                        <input type="email" name="test_email" id="test_email" value="it@dipe.ira.sch.gr" class="form-control" style="padding: 4px 8px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; width: 200px;" required>
                    </div>
                </div>
            </div>

            <input type="hidden" name="email_scope" id="email_scope" value="all">
            <input type="hidden" name="send_emails" value="1">

            <!-- Collapsible Email Settings Panel -->
            <div id="email_panel" style="display: none; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 16px; margin-top: 14px;">
                <p style="margin: 0 0 10px 0; font-size: 13px; color: #475569; font-weight: bold;">
                    📝 Προσαρμογή Θέματος & Σώματος Email (Υποστηρίζει placeholders: <code>{ΣΥΜΒΟΥΛΟΣ}</code>, <code>{ΠΛΗΘΟΣ}</code>, <code>{ΣΧΟΛ_ΕΤΟΣ}</code>, <code>{ΦΟΡΕΑΣ}</code>):
                </p>
                <div style="margin-bottom: 10px;">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;">Θέμα Email:</label>
                    <input type="text" name="email_subject" class="form-control" style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13.5px;" value="<?=htmlspecialchars($default_email_subject)?>">
                </div>
                <div>
                    <label style="font-weight: 600; font-size: 13px; color: #334155;">Σώμα Email:</label>
                    <textarea name="email_body" rows="6" class="form-control" style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-family: inherit; font-size: 13.5px;"><?=htmlspecialchars($default_email_body)?></textarea>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Consultant Selector Card -->
    <div class="action-card" style="margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="flex: 1; min-width: 320px;">
                <label style="font-weight: bold; color: #1e293b; font-size: 14px; margin-bottom: 6px; display: block;">
                    👤 Επιλογή Συμβούλου Εκπαίδευσης:
                </label>
                <select id="symvoulos_select" name="symvoulos">
                    <option value="">-- Επιλέξτε Σύμβουλο --</option>
                    <?php foreach ($consultants as $cafm => $cdata): 
                        $tcnt = count($cdata['teachers']);
                        $sel = ($cafm === $selected_afm) ? 'selected' : '';
                        $email_flag = empty($cdata['email']) ? ' ⚠️ [Χωρίς email]' : '';
                    ?>
                        <option value="<?=$cafm?>" <?=$sel?>>
                            <?=htmlspecialchars($cdata['label'])?> — (<?=$tcnt?> αξιολογούμενοι)<?=$email_flag?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($selected_consultant): ?>
            <!-- Quick Actions for Selected Consultant -->
            <div style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap;">
                <!-- Export Selected to Excel -->
                <a href="report_eval_symv.php?export=excel&afm=<?=$selected_afm?>&source=<?=$actual_source?>" class="btn-modern btn-excel-green" style="font-size: 13px; padding: 7px 14px;" title="Εξαγωγή αρχείου Excel για τον επιλεγμένο σύμβουλο">
                    📥 Εξαγωγή σε Excel (επιλεγμένου)
                </a>

                <!-- Send Email to Selected (Admin only) -->
                <?php if ($is_admin): ?>
                <?php $sel_tcount = !empty($selected_consultant['teachers']) ? count($selected_consultant['teachers']) : 0; ?>
                <button type="button" class="btn-modern btn-mail-blue" style="font-size: 13px; padding: 7px 14px; <?=$sel_tcount === 0 ? 'opacity: 0.6;' : ''?>" onclick="sendSingleConsultantEmail(<?=$sel_tcount?>);" title="<?=$sel_tcount === 0 ? 'Δεν υπάρχουν εγγραφές αξιολογούμενων' : 'Αποστολή email μόνο στον επιλεγμένο σύμβουλο'?>">
                    ✉️ Αποστολή στον επιλεγμένο
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($selected_consultant): 
            $c_tcount = count($selected_consultant['teachers']);
            $ca1 = $ca2 = $cb = 0;
            foreach ($selected_consultant['teachers'] as $t) {
                if (!empty($t['fields_arr'])) {
                    if (in_array('A1', $t['fields_arr']) || in_array('Α1', $t['fields_arr'])) $ca1++;
                    if (in_array('A2', $t['fields_arr']) || in_array('Α2', $t['fields_arr'])) $ca2++;
                    if (in_array('B', $t['fields_arr']) || in_array('Β', $t['fields_arr'])) $cb++;
                }
            }
        ?>
        <!-- Consultant Details Header -->
        <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h2 style="margin: 0; font-size: 17px; color: #1e293b;">
                    <?=htmlspecialchars($selected_consultant['fullname'])?>
                    <span style="font-weight: normal; font-size: 14px; color: #64748b;">(ΑΦΜ: <?=htmlspecialchars($selected_consultant['afm'])?>, <?=htmlspecialchars($selected_consultant['klados'])?>)</span>
                </h2>
                <div style="margin-top: 6px; font-size: 13px; color: #475569; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span>Email παραλήπτη:</span>
                    <?php if ($is_admin): ?>
                        <input type="email" id="email_input_<?=$selected_afm?>" value="<?=htmlspecialchars($selected_consultant['email'])?>" style="padding: 3px 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; width: 230px;" placeholder="Συμπληρώστε email...">
                        <button type="button" class="save-email-btn btn btn-secondary" data-afm="<?=$selected_afm?>" style="padding: 3px 10px; font-size: 12px; border-radius: 4px;">💾 Αποθήκευση</button>
                        <span id="email_status_<?=$selected_afm?>"></span>
                    <?php else: ?>
                        <strong><?=htmlspecialchars($selected_consultant['email'] ?: 'Δεν έχει καταχωρηθεί')?></strong>
                    <?php endif; ?>
                    <?php if (!empty($selected_consultant['email_psd']) && $selected_consultant['email_psd'] !== $selected_consultant['email']): ?>
                        <small style="color: #64748b;">(ΠΣΔ: <?=htmlspecialchars($selected_consultant['email_psd'])?>)</small>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <span class="badge-a1">Πεδίο Α1: <?=$ca1?></span>
                <span class="badge-a2">Πεδίο Α2: <?=$ca2?></span>
                <span class="badge-b">Πεδίο Β: <?=$cb?></span>
                <span style="background: #e2e8f0; color: #334155; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">Σύνολο: <?=$c_tcount?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Data Table of Teachers for Selected Consultant -->
    <div class="table-wrapper">
        <?php if ($selected_consultant && !empty($selected_consultant['teachers'])): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
            <div style="color: #64748b; font-size: 13px;">
                💡 <em>Κάντε κλικ στο <strong>➕</strong> ή σε οποιαδήποτε γραμμή για εμφάνιση των αναλυτικών στοιχείων (πεδία αξιολόγησης, επικοινωνία, λοιπά σχολεία κ.ά.).</em>
            </div>
            <button type="button" id="toggle_all_child_rows" class="btn btn-secondary" style="font-size: 12.5px; padding: 4px 10px; border-radius: 4px; cursor: pointer;">
                ➕ Ανάπτυξη όλων
            </button>
        </div>

        <table id="evalTeachersTable" class="display" style="width: 100%; font-size: 13px;">
            <thead>
                <tr>
                    <th style="width: 36px; max-width: 36px; text-align: center;"></th>
                    <th>Επώνυμο</th>
                    <th>Όνομα</th>
                    <th style="text-align: center;">Πεδίο Αξιολόγησης</th>
                    <th>ΑΜ</th>
                    <th>ΑΦΜ</th>
                    <th>Σχολείο</th>
                    <th>Ημ/νία Διορισμού</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($selected_consultant['teachers'] as $idx => $t): ?>
                <tr data-child-id="<?=$idx?>">
                    <td class="dt-control">
                        <button type="button" class="dt-expand-btn" title="Ανάπτυξη λεπτομερειών">➕</button>
                    </td>
                    <td><a href="../employee/employee.php?id=<?=$t['emp_id']?>&op=view" target="_blank" style="font-weight: 600; color: #1e293b; text-decoration: underline;"><?=htmlspecialchars($t['surname'])?></a></td>
                    <td><?=htmlspecialchars($t['name'])?></td>
                    <?php 
                    $field_rank = 4;
                    if (in_array('A1', $t['fields_arr']) || in_array('Α1', $t['fields_arr'])) {
                        $field_rank = 1;
                    } elseif (in_array('A2', $t['fields_arr']) || in_array('Α2', $t['fields_arr'])) {
                        $field_rank = 2;
                    } elseif (in_array('B', $t['fields_arr']) || in_array('Β', $t['fields_arr'])) {
                        $field_rank = 3;
                    }
                    ?>
                    <td data-order="<?=$field_rank?>" style="text-align: center; white-space: nowrap;">
                        <?php 
                        foreach ($t['fields_arr'] as $fld) {
                            if ($fld === 'A1' || $fld === 'Α1') echo "<span class='badge-a1'>Α1</span> ";
                            elseif ($fld === 'A2' || $fld === 'Α2') echo "<span class='badge-a2'>Α2</span> ";
                            elseif ($fld === 'B' || $fld === 'Β') echo "<span class='badge-b'>Β</span> ";
                        }
                        ?>
                    </td>
                    <td><code><?=htmlspecialchars($t['am'])?></code></td>
                    <td><code><?=htmlspecialchars($t['afm'])?></code></td>
                    <td><a href="../school/school_status.php?org=<?=$t['sch_id'] ?: $t['emp_id']?>" target="_blank" style="color: #2563eb; text-decoration: underline;"><?=htmlspecialchars($t['sname'])?></a></td>
                    <td data-order="<?=htmlspecialchars($t['hm_dior_raw'] ?? '')?>"><?=htmlspecialchars($t['hm_dior'])?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Hidden child row templates outside the table -->
        <div id="childRowTemplates" style="display: none;">
            <?php foreach ($selected_consultant['teachers'] as $idx => $t): ?>
            <div id="child_tpl_<?=$idx?>">
                <div class="child-details-card">
                    <div class="child-grid">
                        <!-- Στοιχεία Αξιολόγησης -->
                        <div class="child-section">
                            <div class="child-section-title">
                                <span>📋</span> Στοιχεία Αξιολόγησης
                            </div>
                            <div class="child-field-row">
                                <span class="child-field-label">Κλάδος:</span>
                                <span class="child-field-val"><?=htmlspecialchars($t['klados'])?></span>
                            </div>
                            <div class="child-field-row">
                                <span class="child-field-label">Θέση:</span>
                                <span class="child-field-val"><?=htmlspecialchars($t['thesi'])?></span>
                            </div>
                            <div class="child-field-row">
                                <span class="child-field-label">Κατάσταση:</span>
                                <span class="child-field-val"><?=htmlspecialchars($t['status'])?></span>
                            </div>
                            <div class="child-field-row">
                                <span class="child-field-label">Τύπος:</span>
                                <span class="child-field-val"><?=htmlspecialchars($t['type'])?></span>
                            </div>
                        </div>

                        <!-- Υπηρεσιακή Κατάσταση -->
                        <div class="child-section">
                            <div class="child-section-title">
                                <span>📅</span> Υπηρεσιακή Κατάσταση
                            </div>
                            <div class="child-field-row">
                                <span class="child-field-label">Ημ. Ανάληψης:</span>
                                <span class="child-field-val"><?=htmlspecialchars($t['hm_anal'])?></span>
                            </div>
                            <div class="child-field-row">
                                <span class="child-field-label">Μονιμοποίηση:</span>
                                <span class="child-field-val"><?=htmlspecialchars($t['monimopoihsh'])?></span>
                            </div>
                            <div class="child-field-row">
                                <span class="child-field-label">Αξιολόγηση:</span>
                                <span class="child-field-val"><?=htmlspecialchars($t['aksiologhsh'])?></span>
                            </div>
                            <div class="child-field-row" style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #e2e8f0;">
                                <span class="child-field-label">Συνολική Υπηρεσία:</span>
                                <span class="child-field-val" style="color: #0369a1;"><?=htmlspecialchars($t['synolikh'])?></span>
                            </div>
                        </div>

                        <!-- Σχολείο & Επικοινωνία -->
                        <div class="child-section">
                            <div class="child-section-title">
                                <span>📞</span> Σχολείο & Επικοινωνία
                            </div>
                            <div class="child-field-row">
                                <span class="child-field-label">Κωδικός Σχολείου:</span>
                                <span class="child-field-val"><code><?=htmlspecialchars($t['code'])?></code></span>
                            </div>
                            <div class="child-field-row" style="align-items: flex-start;">
                                <span class="child-field-label">Λοιπά Σχολεία:</span>
                                <span class="child-field-val" style="font-weight: normal; font-size: 11.5px;"><?=htmlspecialchars($t['loipa_sxoleia'] ?: '-')?></span>
                            </div>
                            <div class="child-field-row">
                                <span class="child-field-label">Τηλέφωνο:</span>
                                <span class="child-field-val"><?=htmlspecialchars($t['tel'] ?: '-')?></span>
                            </div>
                            <div class="child-field-row">
                                <span class="child-field-label">Email:</span>
                                <span class="child-field-val">
                                    <?=$t['email'] ? "<a href='mailto:".htmlspecialchars($t['email'])."' style='color:#2563eb;'>".htmlspecialchars($t['email'])."</a>" : '-'?>
                                </span>
                            </div>
                            <div class="child-field-row">
                                <span class="child-field-label">Email ΠΣΔ:</span>
                                <span class="child-field-val">
                                    <?=$t['email_psd'] ? "<a href='mailto:".htmlspecialchars($t['email_psd'])."' style='color:#2563eb;'>".htmlspecialchars($t['email_psd'])."</a>" : '-'?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php elseif ($selected_consultant): ?>
            <p style="text-align: center; color: #64748b; padding: 30px; font-size: 15px;">
                Δεν υπάρχουν εκπαιδευτικοί με υποχρεώσεις αξιολόγησης για τον επιλεγμένο σύμβουλο.
            </p>
        <?php else: ?>
            <p style="text-align: center; color: #64748b; padding: 30px; font-size: 15px;">
                Παρακαλώ επιλέξτε σύμβουλο εκπαίδευσης από το παραπάνω μενού.
            </p>
        <?php endif; ?>
    </div>

</div>

<script type="text/javascript">
function validateEmailForm() {
    var scope = $('#email_scope').val();
    if (scope === 'all' && <?= (int)$total_teachers_count ?> === 0) {
        alert('Δεν υπάρχουν εγγραφές αξιολογούμενων προς αποστολή.');
        return false;
    }

    var isTesting = $('#testing_checkbox').is(':checked');
    var testEmail = $('#test_email').val().trim();

    if (isTesting && !testEmail) {
        alert('Παρακαλώ εισάγετε μια έγκυρη διεύθυνση email δοκιμής (test email).');
        $('#test_email').focus();
        return false;
    }

    if (!isTesting) {
        return confirm('⚠️ ΠΡΟΣΟΧΗ:\nΗ επιλογή testing ΔΕΝ είναι ενεργοποιημένη!\n\nΘα αποσταλούν ΠΡΑΓΜΑΤΙΚΑ emails σε όλους τους συμβούλους.\nΕίστε βέβαιοι ότι θέλετε να προχωρήσετε;');
    } else {
        return confirm('🧪 Δοκιμαστική Αποστολή:\nΌλα τα emails θα σταλούν αποκλειστικά στο: ' + testEmail + '\n\nΘέλετε να προχωρήσετε;');
    }
}

function sendSingleConsultantEmail(tCount) {
    if (typeof tCount !== 'undefined' && tCount <= 0) {
        alert('Δεν υπάρχουν εγγραφές αξιολογούμενων για τον επιλεγμένο σύμβουλο. Το email δεν θα αποσταλεί.');
        return;
    }

    var isTesting = $('#testing_checkbox').is(':checked');
    var testEmail = $('#test_email').val().trim();

    if (isTesting && !testEmail) {
        alert('Παρακαλώ εισάγετε μια έγκυρη διεύθυνση email δοκιμής.');
        return;
    }

    var msg = isTesting 
        ? '🧪 Δοκιμαστική αποστολή για τον επιλεγμένο σύμβουλο στο email: ' + testEmail + ';\n\nΘέλετε να προχωρήσετε;'
        : '⚠️ ΠΡΟΣΟΧΗ: Θα αποσταλεί πραγματικό email στον επιλεγμένο σύμβουλο.\n\nΘέλετε να προχωρήσετε;';

    if (confirm(msg)) {
        $('#email_scope').val('selected');
        $('#mainActionForm').submit();
    }
}
</script>
</body>
</html>
