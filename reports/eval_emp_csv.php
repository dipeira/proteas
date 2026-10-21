<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('memory_limit', '512M');

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../include/functions.php";

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

require __DIR__ . "/../tools/class.login.php";
$log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
    exit();
}

// Check if admin / super-user
if ($_SESSION['userlevel'] <> 0) {
    echo 'Σφάλμα: Δεν επιτρέπεται η πρόσβαση σε αυτή τη σελίδα (μόνο για διαχειριστές).';
    echo '<br><br><a href="../index.php">Επιστροφή στην αρχική σελίδα</a>';
    exit();
}

const DEFAULT_SUFFIX = "2026-27";

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

function append_suffix($identifier, $suffix) {
    $clean_id = clean_str($identifier);
    if (empty($suffix)) return $clean_id;
    $marker = ':' . $suffix;
    if (substr($clean_id, -strlen($marker)) === $marker) {
        return $clean_id;
    }
    return ($clean_id !== '') ? ($clean_id . $marker) : $suffix;
}

function format_csv_row(array $fields) {
    $escaped = [];
    foreach ($fields as $val) {
        $val = (string)$val;
        if (strpos($val, ';') !== false || strpos($val, '"') !== false || strpos($val, "\n") !== false || strpos($val, "\r") !== false) {
            $val = '"' . str_replace('"', '""', $val) . '"';
        }
        $escaped[] = $val;
    }
    return implode(';', $escaped);
}

// Logic function to resolve evaluation CSV lines for an employee
function resolve_employee_eval_lines($emp_id, $conn, $suffix = '') {
    $emp_id = (int)$emp_id;
    if ($emp_id <= 0) {
        return ['error' => 'Μη έγκυρο αναγνωριστικό εκπαιδευτικού.'];
    }

    $sxol_etos = getParam('sxol_etos', $conn);

    // Pre-load consultant data from symvouloi_epist
    $se_by_klados = [];
    $se_res = mysqli_query($conn, "SELECT id, klados, afm, eponymo, onoma, emp_id, sch_ids FROM symvouloi_epist");
    if ($se_res) {
        while ($se_row = mysqli_fetch_assoc($se_res)) {
            $se_by_klados[$se_row['klados']][] = $se_row;
        }
    }

    $query = "SELECT
        e.id as emp_id,
        e.surname as emp_surname,
        e.name as emp_name,
        e.patrwnymo,
        e.am,
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
    WHERE e.id = $emp_id
    GROUP BY e.id";

    $res = mysqli_query($conn, $query);
    if (!$res || mysqli_num_rows($res) === 0) {
        return ['error' => 'Ο εκπαιδευτικός δεν βρέθηκε στη βάση δεδομένων.'];
    }

    $row = mysqli_fetch_assoc($res);

    // Consultant mapping (Scientific vs Pedagogical)
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
        $symv_epist_afm = $row['symv_paid_afm'];
        $symv_epist_surname = $row['symv_paid_surname'];
        $symv_epist_name = $row['symv_paid_name'];

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

    $emp_afm = normalize_afm($row['emp_afm']);
    $emp_name = trim($row['emp_name']);
    $emp_surname = trim($row['emp_surname']);

    $se_afm = normalize_afm($symv_epist_afm);
    $se_name = trim($symv_epist_name);
    $se_surname = trim($symv_epist_surname);

    $sp_afm = normalize_afm($symv_paid_afm);
    $sp_name = trim($symv_paid_name);
    $sp_surname = trim($symv_paid_surname);

    $dnt_afm = normalize_afm($row['dnt_afm']);
    $dnt_name = trim($row['dnt_name']);
    $dnt_surname = trim($row['dnt_surname']);

    $is_director = ($row['thesi'] == 2 || (!empty($dnt_afm) && $dnt_afm === $emp_afm));

    // Headers as in reports/create_eval_csv.php
    $a1_header = ["Αναγνωριστικό-Εκκρεμότητας", "Αξιολογούμενος-Όνομα", "Αξιολογούμενος-Επίθετο", "Αξιολογούμενος-ΑΦΜ", "Αξιολογητής-Όνομα", "Αξιολογητής-Επίθετο", "Αξιολογητής-ΑΦΜ"];
    $a2_header = ["Αναγνωριστικό-Εκκρεμότητας", "Αξιολογούμενος-Όνομα", "Αξιολογούμενος-Επίθετο", "Αξιολογούμενος-ΑΦΜ", "Αξιολογητής-Όνομα", "Αξιολογητής-Επίθετο", "Αξιολογητής-ΑΦΜ"];
    $b_header = ["Αναγνωριστικό-Εκκρεμότητας", "Αξιολογούμενος-Όνομα", "Αξιολογούμενος-Επίθετο", "Αξιολογούμενος-ΑΦΜ", "Αξιολογητής-1-Όνομα", "Αξιολογητής-1-Επίθετο", "Αξιολογητής-1-ΑΦΜ", "Αξιολογητής-2-Όνομα", "Αξιολογητής-2-Επίθετο", "Αξιολογητής-2-ΑΦΜ"];

    // 1. A1 Line
    $a1_data = null;
    if (!empty($se_afm) && !empty($emp_afm)) {
        $a1_id = $se_afm . ':' . $emp_afm;
        $a1_id_suffix = append_suffix($a1_id, $suffix);
        $a1_row = [$a1_id, $emp_name, $emp_surname, $emp_afm, $se_name, $se_surname, $se_afm];
        $a1_row_suffix = [$a1_id_suffix, $emp_name, $emp_surname, $emp_afm, $se_name, $se_surname, $se_afm];
        $a1_data = [
            'applicable' => true,
            'id' => $a1_id,
            'id_suffix' => $a1_id_suffix,
            'line' => format_csv_row($a1_row),
            'line_suffix' => format_csv_row($a1_row_suffix),
            'evaluator' => [
                'name' => $se_name,
                'surname' => $se_surname,
                'afm' => $se_afm,
                'role' => 'Σύμβουλος Επιστημονικής Ευθύνης'
            ]
        ];
    } else {
        $a1_data = [
            'applicable' => false,
            'reason' => 'Δεν εντοπίστηκε Σύμβουλος Εκπαίδευσης επιστημονικής ευθύνης για τον συγκεκριμένο κλάδο/σχολείο.'
        ];
    }

    // 2. A2 Line
    $excluded_a2_kladoi = ['ΔΕ1ΕΒΠ', 'ΠΕ25', 'ΠΕ23', 'ΠΕ26', 'ΠΕ28', 'ΠΕ30', 'ΤΕ01', 'ΠΕ21', 'ΠΕ29'];
    $excluded_a2_ids = [8, 9, 10, 11, 12, 21, 26, 27, 31];
    $is_excluded_from_a2 = in_array(trim($row['klados'] ?? ''), $excluded_a2_kladoi) || in_array((int)($row['klados_id'] ?? 0), $excluded_a2_ids);

    $a2_data = null;
    if ($is_excluded_from_a2) {
        $a2_data = [
            'applicable' => false,
            'reason' => 'Ο κλάδος (' . htmlspecialchars($row['klados']) . ') εξαιρείται από το πεδίο Α2 (ΕΕΠ/ΕΒΠ).'
        ];
    } else {
        $evaluator_afm = '';
        $evaluator_name = '';
        $evaluator_surname = '';
        $evaluator_role = '';

        if ($is_director || empty($dnt_afm)) {
            if (!empty($sp_afm)) {
                $evaluator_afm = $sp_afm;
                $evaluator_name = $sp_name;
                $evaluator_surname = $sp_surname;
                $evaluator_role = $is_director ? 'Σύμβουλος Παιδαγωγικής Ευθύνης (ο αξιολογούμενος είναι Διευθυντής)' : 'Σύμβουλος Παιδαγωγικής Ευθύνης (δεν ορίστηκε Διευθυντής)';
            }
        } else {
            if (!empty($dnt_afm)) {
                $evaluator_afm = $dnt_afm;
                $evaluator_name = $dnt_name;
                $evaluator_surname = $dnt_surname;
                $evaluator_role = 'Διευθυντής / Προϊστάμενος Σχολικής Μονάδας';
            }
        }

        if (!empty($evaluator_afm)) {
            $a2_id = $evaluator_afm . ':' . $emp_afm;
            $a2_id_suffix = append_suffix($a2_id, $suffix);
            $a2_row = [$a2_id, $emp_name, $emp_surname, $emp_afm, $evaluator_name, $evaluator_surname, $evaluator_afm];
            $a2_row_suffix = [$a2_id_suffix, $emp_name, $emp_surname, $emp_afm, $evaluator_name, $evaluator_surname, $evaluator_afm];
            $a2_data = [
                'applicable' => true,
                'id' => $a2_id,
                'id_suffix' => $a2_id_suffix,
                'line' => format_csv_row($a2_row),
                'line_suffix' => format_csv_row($a2_row_suffix),
                'evaluator' => [
                    'name' => $evaluator_name,
                    'surname' => $evaluator_surname,
                    'afm' => $evaluator_afm,
                    'role' => $evaluator_role
                ]
            ];
        } else {
            $a2_data = [
                'applicable' => false,
                'reason' => 'Δεν βρέθηκε αξιολογητής για το πεδίο Α2 (δεν έχει οριστεί Διευθυντής ούτε Σύμβουλος Παιδαγωγικής Ευθύνης).'
            ];
        }
    }

    // 3. B Line
    $b_data = null;
    if ($is_director) {
        $b_data = [
            'applicable' => false,
            'reason' => 'Ο αξιολογούμενος είναι Διευθυντής / Προϊστάμενος σχολικής μονάδας και εξαιρείται από το πεδίο Β (δεν αξιολογείται στο Β).'
        ];
    } elseif (empty($dnt_afm)) {
        if (!empty($sp_afm)) {
            $b_id = $sp_afm . ':' . $emp_afm;
            $b_id_suffix = append_suffix($b_id, $suffix);
            $b_row = [$b_id, $emp_name, $emp_surname, $emp_afm, $sp_name, $sp_surname, $sp_afm, '', '', ''];
            $b_row_suffix = [$b_id_suffix, $emp_name, $emp_surname, $emp_afm, $sp_name, $sp_surname, $sp_afm, '', '', ''];
            $b_data = [
                'applicable' => true,
                'id' => $b_id,
                'id_suffix' => $b_id_suffix,
                'line' => format_csv_row($b_row),
                'line_suffix' => format_csv_row($b_row_suffix),
                'evaluators' => [
                    'ev1' => [
                        'name' => $sp_name,
                        'surname' => $sp_surname,
                        'afm' => $sp_afm,
                        'role' => 'Σύμβουλος Παιδαγωγικής Ευθύνης (μοναδικός αξιολογητής)'
                    ],
                    'ev2' => null
                ]
            ];
        } else {
            $b_data = [
                'applicable' => false,
                'reason' => 'Δεν βρέθηκε Σύμβουλος Παιδαγωγικής Ευθύνης για το πεδίο Β.'
            ];
        }
    } else {
        if (!empty($dnt_afm)) {
            if (!empty($sp_afm)) {
                $b_id = $dnt_afm . ':' . $sp_afm . ':' . $emp_afm;
                $b_id_suffix = append_suffix($b_id, $suffix);
                $b_row = [$b_id, $emp_name, $emp_surname, $emp_afm, $dnt_name, $dnt_surname, $dnt_afm, $sp_name, $sp_surname, $sp_afm];
                $b_row_suffix = [$b_id_suffix, $emp_name, $emp_surname, $emp_afm, $dnt_name, $dnt_surname, $dnt_afm, $sp_name, $sp_surname, $sp_afm];
                $b_data = [
                    'applicable' => true,
                    'id' => $b_id,
                    'id_suffix' => $b_id_suffix,
                    'line' => format_csv_row($b_row),
                    'line_suffix' => format_csv_row($b_row_suffix),
                    'evaluators' => [
                        'ev1' => [
                            'name' => $dnt_name,
                            'surname' => $dnt_surname,
                            'afm' => $dnt_afm,
                            'role' => 'Αξιολογητής 1: Διευθυντής / Προϊστάμενος'
                        ],
                        'ev2' => [
                            'name' => $sp_name,
                            'surname' => $sp_surname,
                            'afm' => $sp_afm,
                            'role' => 'Αξιολογητής 2: Σύμβουλος Παιδαγωγικής Ευθύνης'
                        ]
                    ]
                ];
            } else {
                $b_id = $dnt_afm . ':' . $emp_afm;
                $b_id_suffix = append_suffix($b_id, $suffix);
                $b_row = [$b_id, $emp_name, $emp_surname, $emp_afm, $dnt_name, $dnt_surname, $dnt_afm, '', '', ''];
                $b_row_suffix = [$b_id_suffix, $emp_name, $emp_surname, $emp_afm, $dnt_name, $dnt_surname, $dnt_afm, '', '', ''];
                $b_data = [
                    'applicable' => true,
                    'id' => $b_id,
                    'id_suffix' => $b_id_suffix,
                    'line' => format_csv_row($b_row),
                    'line_suffix' => format_csv_row($b_row_suffix),
                    'evaluators' => [
                        'ev1' => [
                            'name' => $dnt_name,
                            'surname' => $dnt_surname,
                            'afm' => $dnt_afm,
                            'role' => 'Αξιολογητής 1: Διευθυντής / Προϊστάμενος'
                        ],
                        'ev2' => null
                    ]
                ];
            }
        } else {
            $b_data = [
                'applicable' => false,
                'reason' => 'Δεν βρέθηκε αξιολογητής (Διευθυντής ή Σύμβουλος) για το πεδίο Β.'
            ];
        }
    }

    return [
        'success' => true,
        'emp' => [
            'id' => $row['emp_id'],
            'name' => $emp_name,
            'surname' => $emp_surname,
            'patrwnymo' => trim($row['patrwnymo'] ?? ''),
            'am' => trim($row['am'] ?? ''),
            'afm' => $emp_afm,
            'klados' => $row['klados'],
            'sch_name' => $row['sch_name'] ?: 'Χωρίς σχολείο υπηρέτησης',
            'is_director' => $is_director,
            'thesi' => $row['thesi'],
            'hm_dior' => $row['hm_dior']
        ],
        'headers' => [
            'a1' => format_csv_row($a1_header),
            'a2' => format_csv_row($a2_header),
            'b'  => format_csv_row($b_header),
        ],
        'a1' => $a1_data,
        'a2' => $a2_data,
        'b' => $b_data
    ];
}

// Handle AJAX request
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json; charset=utf-8');
    $emp_id = (int)($_GET['emp_id'] ?? 0);
    $suffix = clean_str($_GET['suffix'] ?? DEFAULT_SUFFIX);
    $result = resolve_employee_eval_lines($emp_id, $mysqlconnection, $suffix);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit();
}

// Fetch list of permanent employees for Select2 dropdown
$allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ', $mysqlconnection);
$allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ', $mysqlconnection);
$exclude_pyspe_arr = array_filter([(int)$allo_pyspe, (int)$allo_pysde]);
$exclude_pyspe_str = implode(',', $exclude_pyspe_arr);

$emp_options_query = "SELECT 
    e.id, 
    e.surname, 
    e.name, 
    e.patrwnymo,
    e.am, 
    e.afm, 
    k.perigrafh as klados, 
    s.name as sch_name
FROM employee e
LEFT JOIN klados k ON e.klados = k.id
LEFT JOIN school s ON e.sx_yphrethshs = s.id
WHERE e.status IN (1, 3) 
  AND e.sx_yphrethshs NOT IN ($exclude_pyspe_str)
ORDER BY e.surname ASC, e.name ASC";

$emp_options_res = mysqli_query($mysqlconnection, $emp_options_query);

$initial_emp_id = isset($_GET['emp_id']) ? (int)$_GET['emp_id'] : 0;
$initial_suffix = isset($_GET['suffix']) ? clean_str($_GET['suffix']) : DEFAULT_SUFFIX;
$initial_data = null;
if ($initial_emp_id > 0) {
    $initial_data = resolve_employee_eval_lines($initial_emp_id, $mysqlconnection, $initial_suffix);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Χειροκίνητη Εισαγωγή Γραμμών CSV Αξιολόγησης (a1, a2, b)</title>
    <script type="text/javascript" language="javascript" src='../js/jquery.js'></script>

    <?php 
      $root_path = '../';
      $page_title = 'Χειροκίνητη Εισαγωγή Γραμμών CSV Αξιολόγησης';
      require '../etc/head.php'; 
    ?>
    <script type="text/javascript" src="../js/select2.min.js"></script>
    <link href="../css/select2.min.css" rel="stylesheet" />

    <style>
        .select2-container {
            width: 100% !important;
        }
        .select2-container .select2-selection--single {
            height: 44px;
            padding: 7px 12px;
            font-size: 15px;
            border-color: #cbd5e1;
            border-radius: 6px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
            right: 10px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
            color: #1e293b;
        }
        .card-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 22px 26px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .card-box h3 {
            margin-top: 0;
            margin-bottom: 16px;
            color: #1e293b;
            font-size: 17px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .csv-row-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 18px;
            transition: all 0.2s ease;
        }
        .csv-row-card:hover {
            border-color: #94a3b8;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .csv-row-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 8px;
        }
        .csv-row-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .code-box-container {
            position: relative;
            margin-top: 8px;
        }
        .code-box {
            background: #0f172a;
            color: #38bdf8;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 13.5px;
            padding: 14px 16px;
            border-radius: 6px;
            overflow-x: auto;
            white-space: pre;
            border: 1px solid #1e293b;
            line-height: 1.45;
        }
        .btn-copy {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s ease;
        }
        .btn-copy:hover {
            background: #1d4ed8;
            color: #ffffff;
        }
        .btn-copy.copied {
            background: #16a34a !important;
        }
        .badge-evaluator {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            padding: 3px 9px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-role {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .emp-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            background: #f1f5f9;
            padding: 14px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .emp-meta-item {
            font-size: 13px;
            color: #475569;
        }
        .emp-meta-item strong {
            color: #0f172a;
            display: block;
            font-size: 13.5px;
        }
        .alert-excluded {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .toggle-switch {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            color: #334155;
            user-select: none;
        }
    </style>
</head>
<body>
    <?php require '../etc/menu.php'; ?>
<div id="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h1 style="margin: 0;">Χειροκίνητη Εισαγωγή Γραμμών CSV Αξιολόγησης</h1>
            <p style="color: #64748b; font-size: 14px; margin: 5px 0 0 0;">
                Διαμόρφωση γραμμών προς χειροκίνητη προσθήκη στα αρχεία <code>a1.csv</code>, <code>a2.csv</code> και <code>b.csv</code> σύμφωνα με το <a href="create_eval_csv.php" style="color: #2563eb; text-decoration: underline;">reports/create_eval_csv.php</a>.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="create_eval_csv.php" class="btn btn-primary" style="text-decoration: none; padding: 7px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px;">
                📁 Μαζική Δημιουργία CSV
            </a>
            <a href="aksiologhsh_report.php" class="btn btn-primary" style="text-decoration: none; padding: 7px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px;">
                📊 Αναφορά Αξιολόγησης
            </a>
        </div>
    </div>

    <!-- Selection Card -->
    <div class="card-box">
        <h3>🔍 Επιλογή Εκπαιδευτικού & Παράμετροι</h3>

        <div style="margin-bottom: 18px;">
            <label for="emp_select" style="font-weight: 600; font-size: 14px; color: #334155; display: block; margin-bottom: 6px;">
                Μόνιμος Εκπαιδευτικός (Status 1 ή 3, που δεν υπηρετεί σε άλλο ΠΥΣΠΕ/ΠΥΣΔΕ):
            </label>
            <select id="emp_select" class="form-control" style="width: 100%;">
                <option value="">-- Επιλέξτε εκπαιδευτικό (πληκτρολογήστε όνομα, επώνυμο, ΑΦΜ ή σχολείο) --</option>
                <?php
                if ($emp_options_res) {
                    while ($emp = mysqli_fetch_assoc($emp_options_res)) {
                        $eid = (int)$emp['id'];
                        $selected_attr = ($eid === $initial_emp_id) ? ' selected="selected"' : '';
                        $patr_str = !empty($emp['patrwnymo']) ? ' του ' . clean_str($emp['patrwnymo']) : '';
                        $am_str = !empty($emp['am']) ? ', ΑΜ: ' . clean_str($emp['am']) : '';
                        $opt_text = clean_str($emp['surname']) . ' ' . clean_str($emp['name']) . $patr_str . 
                                   ' (ΑΦΜ: ' . normalize_afm($emp['afm']) . $am_str . ') - ' . 
                                   clean_str($emp['klados']) . ' - ' . 
                                   ($emp['sch_name'] ? clean_str($emp['sch_name']) : 'Χωρίς σχολείο');
                        echo "<option value='$eid'$selected_attr>" . htmlspecialchars($opt_text, ENT_QUOTES, 'UTF-8') . "</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center; background: #f8fafc; padding: 12px 18px; border-radius: 6px; border: 1px dashed #cbd5e1;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <label for="suffix_input" style="font-weight: 600; font-size: 13.5px; color: #475569; margin: 0;">
                    Επίθημα εκκρεμότητας (Suffix):
                </label>
                <input type="text" id="suffix_input" class="form-control" value="<?php echo htmlspecialchars($initial_suffix); ?>" style="width: 130px; padding: 6px 10px; font-size: 13.5px;" placeholder="π.χ. 2026-27">
            </div>

            <label class="toggle-switch">
                <input type="checkbox" id="use_suffix_toggle" checked style="width: 17px; height: 17px; cursor: pointer;">
                <span>Εφαρμογή επιθήματος στα αναγνωριστικά (π.χ. :<?php echo htmlspecialchars($initial_suffix); ?>)</span>
            </label>

            <div style="margin-left: auto;">
                <button type="button" id="copy_all_btn" class="btn btn-green" style="display: none; padding: 7px 16px; border-radius: 5px; font-weight: bold; cursor: pointer;">
                    📋 Αντιγραφή Όλων των Γραμμών (a1, a2, b)
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loading_spinner" style="display: none; text-align: center; padding: 30px 0;">
        <div style="display: inline-block; font-size: 16px; color: #2563eb; font-weight: bold;">
            ⏳ Υπολογισμός γραμμών αξιολόγησης εκπαιδευτικού...
        </div>
    </div>

    <!-- Error Box -->
    <div id="error_box" class="alert alert-danger" style="display: none; background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 14px 18px; border-radius: 6px; margin-bottom: 20px;">
    </div>

    <!-- Results Container -->
    <div id="results_container" style="display: none;">
        <!-- Employee Metadata Box -->
        <div class="card-box" style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                <h3 style="margin: 0; border: none; padding: 0;">
                    👤 <span id="meta_emp_fullname">-</span>
                </h3>
                <span id="meta_director_badge" class="badge-evaluator" style="background: #fef3c7; color: #92400e; border-color: #fde68a; display: none;">
                    ★ Διευθυντής / Προϊστάμενος
                </span>
            </div>
            
            <div class="emp-meta-grid">
                <div class="emp-meta-item">
                    <span>ΑΦΜ:</span>
                    <strong id="meta_emp_afm">-</strong>
                </div>
                <div class="emp-meta-item">
                    <span>Κλάδος:</span>
                    <strong id="meta_emp_klados">-</strong>
                </div>
                <div class="emp-meta-item">
                    <span>Σχολείο Υπηρέτησης:</span>
                    <strong id="meta_emp_school">-</strong>
                </div>
                <div class="emp-meta-item">
                    <span>Ημ/νία Διορισμού:</span>
                    <strong id="meta_emp_hm_dior">-</strong>
                </div>
            </div>
        </div>

        <!-- CSV Rows Sections -->
        <div class="card-box">
            <h3>📑 Γραμμές CSV προς Προσθήκη</h3>

            <!-- 1. a1.csv -->
            <div class="csv-row-card">
                <div class="csv-row-header">
                    <div class="csv-row-title">
                        <span style="background: #0284c7; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">a1.csv</span>
                        <span>Πεδίο Α1 (Γενική και ειδική διδακτική του γνωστικού αντικειμένου)</span>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <span id="a1_eval_badge" class="badge-evaluator">Αξιολογητής: -</span>
                        <button type="button" class="btn-copy" onclick="copyRow('a1_code', this)">
                            📋 Αντιγραφή γραμμής
                        </button>
                    </div>
                </div>
                <div id="a1_container">
                    <div class="code-box-container">
                        <div id="a1_code" class="code-box"></div>
                    </div>
                    <div style="margin-top: 6px; font-size: 11.5px; color: #64748b;">
                        Κεφαλίδα: <code>Αναγνωριστικό-Εκκρεμότητας;Αξιολογούμενος-Όνομα;Αξιολογούμενος-Επίθετο;Αξιολογούμενος-ΑΦΜ;Αξιολογητής-Όνομα;Αξιολογητής-Επίθετο;Αξιολογητής-ΑΦΜ</code>
                    </div>
                </div>
                <div id="a1_excluded" class="alert-excluded" style="display: none;">
                    ⚠️ <span id="a1_excluded_reason"></span>
                </div>
            </div>

            <!-- 2. a2.csv -->
            <div class="csv-row-card">
                <div class="csv-row-header">
                    <div class="csv-row-title">
                        <span style="background: #0d9488; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">a2.csv</span>
                        <span>Πεδίο Α2 (Παιδαγωγικό κλίμα και διαχείριση της τάξης)</span>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <span id="a2_eval_badge" class="badge-evaluator">Αξιολογητής: -</span>
                        <button type="button" id="a2_copy_btn" class="btn-copy" onclick="copyRow('a2_code', this)">
                            📋 Αντιγραφή γραμμής
                        </button>
                    </div>
                </div>
                <div id="a2_container">
                    <div class="code-box-container">
                        <div id="a2_code" class="code-box"></div>
                    </div>
                    <div style="margin-top: 6px; font-size: 11.5px; color: #64748b;">
                        Κεφαλίδα: <code>Αναγνωριστικό-Εκκρεμότητας;Αξιολογούμενος-Όνομα;Αξιολογούμενος-Επίθετο;Αξιολογούμενος-ΑΦΜ;Αξιολογητής-Όνομα;Αξιολογητής-Επίθετο;Αξιολογητής-ΑΦΜ</code>
                    </div>
                </div>
                <div id="a2_excluded" class="alert-excluded" style="display: none;">
                    ℹ️ <span id="a2_excluded_reason"></span>
                </div>
            </div>

            <!-- 3. b.csv -->
            <div class="csv-row-card">
                <div class="csv-row-header">
                    <div class="csv-row-title">
                        <span style="background: #7c3aed; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">b.csv</span>
                        <span>Πεδίο Β (Υπηρεσιακή συνέπεια και επάρκεια)</span>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        <span id="b_eval1_badge" class="badge-evaluator">Αξιολογητής 1: -</span>
                        <span id="b_eval2_badge" class="badge-evaluator" style="display: none;">Αξιολογητής 2: -</span>
                        <button type="button" class="btn-copy" onclick="copyRow('b_code', this)">
                            📋 Αντιγραφή γραμμής
                        </button>
                    </div>
                </div>
                <div id="b_container">
                    <div class="code-box-container">
                        <div id="b_code" class="code-box"></div>
                    </div>
                    <div style="margin-top: 6px; font-size: 11.5px; color: #64748b;">
                        Κεφαλίδα: <code>Αναγνωριστικό-Εκκρεμότητας;Αξιολογούμενος-Όνομα;Αξιολογούμενος-Επίθετο;Αξιολογούμενος-ΑΦΜ;Αξιολογητής-1-Όνομα;Αξιολογητής-1-Επίθετο;Αξιολογητής-1-ΑΦΜ;Αξιολογητής-2-Όνομα;Αξιολογητής-2-Επίθετο;Αξιολογητής-2-ΑΦΜ</code>
                    </div>
                </div>
                <div id="b_excluded" class="alert-excluded" style="display: none;">
                    ⚠️ <span id="b_excluded_reason"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var currentEvalData = null;

$(document).ready(function() {
    $('#emp_select').select2({
        placeholder: "-- Επιλέξτε εκπαιδευτικό --",
        allowClear: true,
        width: 'resolve'
    });

    $('#emp_select').on('change', function() {
        var empId = $(this).val();
        if (empId) {
            loadEmployeeLines(empId);
        } else {
            $('#results_container').slideUp();
            $('#copy_all_btn').hide();
            currentEvalData = null;
        }
    });

    $('#suffix_input').on('input', function() {
        if (currentEvalData) {
            renderData(currentEvalData);
        }
    });

    $('#use_suffix_toggle').on('change', function() {
        if (currentEvalData) {
            renderData(currentEvalData);
        }
    });

    $('#copy_all_btn').on('click', function() {
        copyAllRows(this);
    });

    <?php if ($initial_data && isset($initial_data['success'])): ?>
    currentEvalData = <?php echo json_encode($initial_data, JSON_UNESCAPED_UNICODE); ?>;
    renderData(currentEvalData);
    $('#results_container').show();
    $('#copy_all_btn').show();
    <?php endif; ?>
});

function loadEmployeeLines(empId) {
    $('#loading_spinner').show();
    $('#error_box').hide();
    $('#results_container').hide();
    $('#copy_all_btn').hide();

    var suffix = $('#suffix_input').val();

    $.ajax({
        url: 'eval_emp_csv.php',
        type: 'GET',
        dataType: 'json',
        data: {
            ajax: 1,
            emp_id: empId,
            suffix: suffix
        },
        success: function(resp) {
            $('#loading_spinner').hide();
            if (resp.error) {
                $('#error_box').text(resp.error).show();
            } else if (resp.success) {
                currentEvalData = resp;
                renderData(resp);
                $('#results_container').slideDown();
                $('#copy_all_btn').show();
            }
        },
        error: function(xhr, status, err) {
            $('#loading_spinner').hide();
            $('#error_box').text("Σφάλμα επικοινωνίας με το διακομιστή: " + err).show();
        }
    });
}

function renderData(data) {
    var emp = data.emp;
    var suffix = $('#suffix_input').val().trim();
    var useSuffix = $('#use_suffix_toggle').is(':checked');

    // Fill metadata
    var fullName = emp.surname + ' ' + emp.name;
    if (emp.patrwnymo) fullName += ' του ' + emp.patrwnymo;
    if (emp.am) fullName += ' (ΑΜ: ' + emp.am + ')';
    $('#meta_emp_fullname').text(fullName);
    $('#meta_emp_afm').text(emp.afm);
    $('#meta_emp_klados').text(emp.klados);
    $('#meta_emp_school').text(emp.sch_name);
    $('#meta_emp_hm_dior').text(emp.hm_dior ? emp.hm_dior : '-');

    if (emp.is_director) {
        $('#meta_director_badge').show();
    } else {
        $('#meta_director_badge').hide();
    }

    // Helper for suffix replacement
    function getLineWithActiveSuffix(originalLine, lineSuffix, id, idSuffix) {
        if (!useSuffix || !suffix) {
            return originalLine;
        }
        // Build dynamic suffix if user changed it in the input
        var customId = id + ':' + suffix;
        return customId + originalLine.substring(id.length);
    }

    // A1
    if (data.a1 && data.a1.applicable) {
        $('#a1_container').show();
        $('#a1_excluded').hide();
        var a1Line = getLineWithActiveSuffix(data.a1.line, data.a1.line_suffix, data.a1.id, data.a1.id_suffix);
        $('#a1_code').text(a1Line);
        var ev = data.a1.evaluator;
        $('#a1_eval_badge').text('Αξιολογητής: ' + ev.surname + ' ' + ev.name + ' (' + ev.afm + ') - ' + ev.role).show();
    } else {
        $('#a1_container').hide();
        $('#a1_excluded_reason').text(data.a1 ? data.a1.reason : 'Μη διαθέσιμο');
        $('#a1_excluded').show();
        $('#a1_eval_badge').hide();
    }

    // A2
    if (data.a2 && data.a2.applicable) {
        $('#a2_container').show();
        $('#a2_copy_btn').show();
        $('#a2_excluded').hide();
        var a2Line = getLineWithActiveSuffix(data.a2.line, data.a2.line_suffix, data.a2.id, data.a2.id_suffix);
        $('#a2_code').text(a2Line);
        var ev = data.a2.evaluator;
        $('#a2_eval_badge').text('Αξιολογητής: ' + ev.surname + ' ' + ev.name + ' (' + ev.afm + ') - ' + ev.role).show();
    } else {
        $('#a2_container').hide();
        $('#a2_copy_btn').hide();
        $('#a2_excluded_reason').text(data.a2 ? data.a2.reason : 'Μη διαθέσιμο');
        $('#a2_excluded').show();
        $('#a2_eval_badge').hide();
    }

    // B
    if (data.b && data.b.applicable) {
        $('#b_container').show();
        $('#b_excluded').hide();
        var bLine = getLineWithActiveSuffix(data.b.line, data.b.line_suffix, data.b.id, data.b.id_suffix);
        $('#b_code').text(bLine);

        var ev1 = data.b.evaluators.ev1;
        var ev2 = data.b.evaluators.ev2;
        if (ev1) {
            $('#b_eval1_badge').text(ev1.role + ': ' + ev1.surname + ' ' + ev1.name + ' (' + ev1.afm + ')').show();
        } else {
            $('#b_eval1_badge').hide();
        }
        if (ev2) {
            $('#b_eval2_badge').text(ev2.role + ': ' + ev2.surname + ' ' + ev2.name + ' (' + ev2.afm + ')').show();
        } else {
            $('#b_eval2_badge').hide();
        }
    } else {
        $('#b_container').hide();
        $('#b_excluded_reason').text(data.b ? data.b.reason : 'Μη διαθέσιμο');
        $('#b_excluded').show();
        $('#b_eval1_badge').hide();
        $('#b_eval2_badge').hide();
    }
}

function copyRow(elementId, btn) {
    var text = $('#' + elementId).text();
    if (!text) return;

    copyToClipboard(text, function() {
        var origText = $(btn).html();
        $(btn).addClass('copied').html('✓ Αντιγράφηκε!');
        setTimeout(function() {
            $(btn).removeClass('copied').html(origText);
        }, 1800);
    });
}

function copyAllRows(btn) {
    var lines = [];
    if ($('#a1_container').is(':visible')) {
        var a1 = $('#a1_code').text().trim();
        if (a1) lines.push(a1);
    }
    if ($('#a2_container').is(':visible')) {
        var a2 = $('#a2_code').text().trim();
        if (a2) lines.push(a2);
    }
    if ($('#b_container').is(':visible')) {
        var b = $('#b_code').text().trim();
        if (b) lines.push(b);
    }

    if (lines.length === 0) return;

    var fullText = lines.join("\r\n");
    copyToClipboard(fullText, function() {
        var origText = $(btn).html();
        $(btn).addClass('copied').html('✓ Αντιγράφηκαν όλες οι γραμμές!');
        setTimeout(function() {
            $(btn).removeClass('copied').html(origText);
        }, 2000);
    });
}

function copyToClipboard(text, onSuccess) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(onSuccess).catch(function(err) {
            fallbackCopy(text, onSuccess);
        });
    } else {
        fallbackCopy(text, onSuccess);
    }
}

function fallbackCopy(text, onSuccess) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.opacity = "0";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        var successful = document.execCommand('copy');
        if (successful && onSuccess) onSuccess();
    } catch (err) {
        console.error('Fallback copy failed', err);
    }
    document.body.removeChild(textArea);
}
</script>
</body>
</html>
