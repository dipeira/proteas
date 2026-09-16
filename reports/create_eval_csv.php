<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('memory_limit', '512M');
set_time_limit(300);

require_once "../config.php";
require_once "../include/functions.php";
require_once "../vendor/autoload.php";

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
if ($_SESSION['userlevel'] <> 0) {
    echo 'Σφάλμα: Δεν επιτρέπεται η πρόσβαση σε αυτή τη σελίδα.';
    echo '<br><br><a href="../index.php">Επιστροφή στην αρχική σελίδα</a>';
    exit();
}

// handle CSV or ZIP download
if (isset($_GET['download'])) {
    $type = $_GET['download'];
    $files = [
        'a1' => ['file' => 'a1.csv', 'mime' => 'text/csv; charset=utf-8'],
        'a2' => ['file' => 'a2.csv', 'mime' => 'text/csv; charset=utf-8'],
        'b' => ['file' => 'b.csv', 'mime' => 'text/csv; charset=utf-8'],
        'zip' => ['file' => 'aksiologhsh_csv.zip', 'mime' => 'application/zip'],
    ];
    if (isset($files[$type])) {
        $filepath = __DIR__ . '/../word/' . $files[$type]['file'];
        if (file_exists($filepath)) {
            header('Content-Type: ' . $files[$type]['mime']);
            header('Content-Disposition: attachment; filename="' . $files[$type]['file'] . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit();
        }
    }
}

const DEFAULT_SUFFIX = "2026-27";
const TARGET_STATUS = "Δεν αξιολογήθηκε με υπαιτιότητα του αξιολογούμενου";
const EXCLUDED_STATUSES = ["εξαιρετικός", "ικανοποιητικός", "πολύ καλός"];

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

function extract_afms($id_str) {
    if (!$id_str) return [];
    $parts = explode(':', (string)$id_str);
    $afms = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if (ctype_digit($p) && strlen($p) >= 8 && strlen($p) <= 10) {
            $afms[] = normalize_afm($p);
        }
    }
    return $afms;
}

function extract_teacher_afm($id_str, $category) {
    $afms = extract_afms($id_str);
    $cat = strtoupper($category);
    if (strpos($cat, 'A1') !== false || mb_strpos($cat, 'Α1') !== false) {
        return (count($afms) >= 2) ? $afms[1] : "";
    } elseif (strpos($cat, 'A2') !== false || mb_strpos($cat, 'Α2') !== false) {
        return (count($afms) >= 2) ? $afms[1] : "";
    } elseif (strpos($cat, 'B') !== false || mb_strpos($cat, 'Β') !== false) {
        if (count($afms) >= 3) {
            return $afms[2];
        } elseif (count($afms) == 2) {
            return $afms[1];
        }
    }
    return "";
}

function normalize_greek_text($str) {
    $str = mb_strtolower(clean_str($str), 'UTF-8');
    $accents = ['ά'=>'α', 'έ'=>'ε', 'ή'=>'η', 'ί'=>'ι', 'ό'=>'ο', 'ύ'=>'υ', 'ώ'=>'ω', 'ϊ'=>'ι', 'ϋ'=>'υ', 'ΐ'=>'ι', 'ΰ'=>'υ'];
    return strtr($str, $accents);
}

function find_column_index(array $headers, array $candidate_names) {
    $norm_headers = array_map('normalize_greek_text', $headers);
    foreach ($candidate_names as $cand) {
        $cand_norm = normalize_greek_text($cand);
        foreach ($norm_headers as $idx => $h) {
            if ($cand_norm === $h || mb_strpos($h, $cand_norm) !== false) {
                return $idx;
            }
        }
    }
    throw new Exception("Δεν βρέθηκε στήλη που να αντιστοιχεί σε μία από τις επιλογές: " . implode(', ', $candidate_names));
}

function read_csv_rows($csv_path) {
    $content = file_get_contents($csv_path);
    if ($content === false || strlen(trim($content)) === 0) {
        throw new Exception("Το αρχείο CSV είναι κενό.");
    }

    if (substr($content, 0, 2) === "\xFF\xFE") {
        $content = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16LE');
    } elseif (substr($content, 0, 2) === "\xFE\xFF") {
        $content = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16BE');
    } elseif (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
    }

    if (!mb_check_encoding($content, 'UTF-8')) {
        $converted = false;
        if (function_exists('iconv')) {
            $converted = @iconv('WINDOWS-1253', 'UTF-8//IGNORE', $content);
        }
        if ($converted === false || !mb_check_encoding($converted, 'UTF-8')) {
            $converted = @mb_convert_encoding($content, 'UTF-8', 'ISO-8859-7');
        }
        if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
            $content = $converted;
        }
    }

    $delimiters = [';', ',', "\t", '|'];
    $first_line = strtok($content, "\r\n");
    $delimiter = ';';
    if ($first_line !== false) {
        $counts = [];
        foreach ($delimiters as $d) {
            $counts[$d] = substr_count($first_line, $d);
        }
        arsort($counts);
        $firstKey = key($counts);
        if ($counts[$firstKey] > 0) {
            $delimiter = $firstKey;
        }
    }

    $fp = fopen('php://temp', 'r+');
    fwrite($fp, $content);
    rewind($fp);

    $rows = [];
    while (($row = fgetcsv($fp, 0, $delimiter)) !== false) {
        if (count($row) === 1 && clean_str($row[0]) === '') {
            continue;
        }
        $rows[] = $row;
    }
    fclose($fp);

    if (empty($rows)) {
        throw new Exception("Το αρχείο CSV είναι κενό.");
    }
    return $rows;
}

function extract_evaluations_from_rows(array $rows) {
    if (empty($rows)) {
        throw new Exception("Το αρχείο αξιολόγησης είναι κενό.");
    }
    $headers = array_map('clean_str', $rows[0]);

    $a1_id_idx = find_column_index($headers, ["ΑΝΑΓΝΩΡΙΣΤΙΚΟ ΕΚΚΡΕΜΟΤΗΤΑΣ Α1/Α", "ΑΝΑΓΝΩΡΙΣΤΙΚΟ ΕΚΚΡΕΜΟΤΗΤΑΣ Α1"]);
    $a1_status_idx = find_column_index($headers, ["ΠΕΔΙΟ Α1/Α", "ΠΕΔΙΟ Α1"]);

    $a2_id_idx = find_column_index($headers, ["ΑΝΑΓΝΩΡΙΣΤΙΚΟ ΕΚΚΡΕΜΟΤΗΤΑΣ Α2"]);
    $a2_status_idx = find_column_index($headers, ["ΠΕΔΙΟ Α2"]);

    $b_id_idx = find_column_index($headers, ["ΑΝΑΓΝΩΡΙΣΤΙΚΟ ΕΚΚΡΕΜΟΤΗΤΑΣ Β", "ΑΝΑΓΝΩΡΙΣΤΙΚΟ ΕΚΚΡΕΜΟΤΗΤΑΣ B"]);
    $b_status_idx = find_column_index($headers, ["ΠΕΔΙΟ B", "ΠΕΔΙΟ Β"]);

    $categories = [
        ["A1", $a1_id_idx, $a1_status_idx],
        ["A2", $a2_id_idx, $a2_status_idx],
        ["B", $b_id_idx, $b_status_idx],
    ];

    $pending_results = [];
    $completed_by_category = ["A1" => [], "A2" => [], "B" => []];
    $completed_all = [];

    $target_norm = normalize_greek_text(TARGET_STATUS);
    $excluded_norms = array_map('normalize_greek_text', EXCLUDED_STATUSES);

    $rowCount = count($rows);
    foreach ($categories as $cat) {
        $cat_name = $cat[0];
        $id_idx = $cat[1];
        $status_idx = $cat[2];

        $status_map = [];

        for ($r = 1; $r < $rowCount; $r++) {
            $row = $rows[$r];
            $val_id = clean_str($row[$id_idx] ?? '');
            $val_status = clean_str($row[$status_idx] ?? '');
            if ($val_id === '') continue;

            $afm_key = extract_afms($val_id);
            if (empty($afm_key)) continue;

            $key_str = implode(':', $afm_key);
            if (!isset($status_map[$key_str])) {
                $status_map[$key_str] = [];
            }
            $status_map[$key_str][] = $val_status;

            $val_status_norm = normalize_greek_text($val_status);
            foreach ($excluded_norms as $ex_norm) {
                if (mb_strpos($val_status_norm, $ex_norm) !== false) {
                    $t_afm = extract_teacher_afm($val_id, $cat_name);
                    if ($t_afm !== '') {
                        $completed_by_category[$cat_name][$t_afm] = true;
                        $completed_all[$t_afm] = true;
                    }
                    break;
                }
            }
        }

        $pending_for_cat = [];
        foreach ($status_map as $key_str => $statuses) {
            $has_target = false;
            foreach ($statuses as $s) {
                if (normalize_greek_text($s) === $target_norm) {
                    $has_target = true;
                    break;
                }
            }
            if ($has_target) {
                $other_statuses = [];
                foreach ($statuses as $s) {
                    $c = clean_str($s);
                    if ($c !== '' && normalize_greek_text($c) !== $target_norm) {
                        $other_statuses[] = $c;
                    }
                }
                if (empty($other_statuses)) {
                    $pending_for_cat[$key_str] = true;
                }
            }
        }
        $pending_results[$cat_name] = $pending_for_cat;
    }

    return [$pending_results, $completed_all, $completed_by_category];
}

function extract_evaluations_from_file($file_path, $original_filename = '') {
    $filename_to_check = !empty($original_filename) ? $original_filename : $file_path;
    $ext = strtolower(pathinfo($filename_to_check, PATHINFO_EXTENSION));

    $rows = null;

    if ($ext === 'csv' || $ext === 'txt') {
        $rows = read_csv_rows($file_path);
    } elseif ($ext === 'xlsx' || $ext === 'xls') {
        try {
            $objPHPExcel = PHPExcel_IOFactory::load($file_path);
            $ws = $objPHPExcel->getActiveSheet();
            $rows = $ws->toArray(null, true, false, false);
        } catch (Exception $e) {
            try {
                $rows = read_csv_rows($file_path);
            } catch (Exception $e2) {
                throw new Exception("Αδυναμία ανάγνωσης αρχείου Excel: " . $e->getMessage());
            }
        }
    } else {
        try {
            $objPHPExcel = PHPExcel_IOFactory::load($file_path);
            $ws = $objPHPExcel->getActiveSheet();
            $rows = $ws->toArray(null, true, false, false);
        } catch (Exception $e) {
            $rows = read_csv_rows($file_path);
        }
    }

    if (empty($rows)) {
        throw new Exception("Το αρχείο αξιολόγησης είναι κενό.");
    }

    return extract_evaluations_from_rows($rows);
}

function extract_evaluations_from_excel($excel_path, $original_filename = '') {
    return extract_evaluations_from_file($excel_path, $original_filename);
}

function append_suffix($identifier, $suffix) {
    $clean_id = clean_str($identifier);
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

function detect_delimiter_from_file($path) {
    $handle = fopen($path, 'r');
    if (!$handle) return ';';
    $sample = fread($handle, 8192);
    fclose($handle);
    $delimiters = [';', ',', "\t", '|'];
    $counts = [];
    foreach ($delimiters as $d) {
        $counts[$d] = substr_count($sample, $d);
    }
    arsort($counts);
    $firstKey = key($counts);
    return ($counts[$firstKey] > 0) ? $firstKey : ';';
}

function process_uploaded_csv($csv_path, $output_path, $pending_keys, $completed_teachers, $suffix, $category) {
    $delimiter = detect_delimiter_from_file($csv_path);
    $handle = fopen($csv_path, 'r');
    if (!$handle) throw new Exception("Αδυναμία ανοίγματος του $csv_path");

    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }

    $header = fgetcsv($handle, 0, $delimiter);
    if ($header === false) throw new Exception("Το αρχείο CSV δεν έχει κεφαλίδα.");
    $fieldnames = array_map('clean_str', $header);

    $id_column = "Αναγνωριστικό-Εκκρεμότητας";
    $id_idx = false;
    foreach ($fieldnames as $idx => $fn) {
        if ($fn === $id_column) {
            $id_idx = $idx;
            break;
        }
    }
    if ($id_idx === false) {
        foreach ($fieldnames as $idx => $fn) {
            $norm = normalize_greek_text($fn);
            if (mb_strpos($norm, 'αναγνωριστικο') !== false && mb_strpos($norm, 'εκκρεμοτητ') !== false) {
                $id_idx = $idx;
                break;
            }
        }
    }

    $col_indices = array_flip($fieldnames);
    $afm_col_indices = [];
    foreach ($fieldnames as $idx => $fn) {
        if (mb_strpos(normalize_greek_text($fn), 'αφμ') !== false) {
            $afm_col_indices[] = $idx;
        }
    }

    $total_rows = 0;
    $matched_rows = 0;
    $excluded_rows = 0;
    $output_rows = [];

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        if (count($row) < count($fieldnames)) {
            $row = array_pad($row, count($fieldnames), '');
        }
        $total_rows++;
        $raw_id = clean_str($row[$id_idx] ?? '');
        $afms_from_id = extract_afms($raw_id);
        $key_from_id = implode(':', $afms_from_id);

        $teacher_col_idx = $col_indices['Αξιολογούμενος-ΑΦΜ'] ?? false;
        $teacher_afm = ($teacher_col_idx !== false) ? normalize_afm($row[$teacher_col_idx]) : '';
        $id_teacher_afm = extract_teacher_afm($raw_id, $category);

        $is_completed = ($teacher_afm !== '' && isset($completed_teachers[$teacher_afm])) ||
                        ($id_teacher_afm !== '' && isset($completed_teachers[$id_teacher_afm]));

        if ($is_completed) {
            $excluded_rows++;
            continue;
        }

        if ($category === 'A1' || $category === 'A2') {
            $ev_idx = $col_indices['Αξιολογητής-ΑΦΜ'] ?? false;
            $ed_idx = $col_indices['Αξιολογούμενος-ΑΦΜ'] ?? false;
            $ev = ($ev_idx !== false) ? normalize_afm($row[$ev_idx]) : '';
            $ed = ($ed_idx !== false) ? normalize_afm($row[$ed_idx]) : '';
            $key_from_cols = ($ev !== '' && $ed !== '') ? ($ev . ':' . $ed) : '';
        } else {
            $e1_idx = $col_indices['Αξιολογητής-1-ΑΦΜ'] ?? false;
            $e2_idx = $col_indices['Αξιολογητής-2-ΑΦΜ'] ?? false;
            $ed_idx = $col_indices['Αξιολογούμενος-ΑΦΜ'] ?? false;
            $e1 = ($e1_idx !== false) ? normalize_afm($row[$e1_idx]) : '';
            $e2 = ($e2_idx !== false) ? normalize_afm($row[$e2_idx]) : '';
            $ed = ($ed_idx !== false) ? normalize_afm($row[$ed_idx]) : '';
            if ($e2 !== '') {
                $key_from_cols = $e1 . ':' . $e2 . ':' . $ed;
            } elseif ($e1 !== '') {
                $key_from_cols = $e1 . ':' . $ed;
            } else {
                $key_from_cols = '';
            }
        }

        $is_matched = ($key_from_id !== '' && isset($pending_keys[$key_from_id])) ||
                      ($key_from_cols !== '' && isset($pending_keys[$key_from_cols]));

        foreach ($afm_col_indices as $a_idx) {
            $val = clean_str($row[$a_idx]);
            if ($val !== '' && ctype_digit($val) && strlen($val) <= 9) {
                $row[$a_idx] = normalize_afm($val);
            }
        }

        $parts = explode(':', $raw_id);
        $norm_parts = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '' && ctype_digit($p) && strlen($p) <= 9) {
                $norm_parts[] = normalize_afm($p);
            } else {
                $norm_parts[] = $p;
            }
        }
        $norm_id = implode(':', $norm_parts);

        if ($is_matched) {
            $row[$id_idx] = append_suffix($norm_id, $suffix);
            $matched_rows++;
        } else {
            $row[$id_idx] = norm_id;
        }

        $output_rows[] = $row;
    }
    fclose($handle);

    $out_dir = dirname($output_path);
    if (!is_dir($out_dir)) mkdir($out_dir, 0777, true);

    $out_handle = fopen($output_path, 'w');
    fwrite($out_handle, "\xEF\xBB\xBF");
    fwrite($out_handle, format_csv_row($fieldnames) . "\r\n");
    foreach ($output_rows as $or) {
        fwrite($out_handle, format_csv_row($or) . "\r\n");
    }
    fclose($out_handle);

    return [$total_rows, $matched_rows, $excluded_rows, count($output_rows)];
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Δημιουργία Αρχείων CSV Αξιολόγησης</title>
    <script type="text/javascript" language="javascript" src='../js/jquery.js'></script>

    <?php 
      $root_path = '../';
      $page_title = 'Δημιουργία Αρχείων CSV Αξιολόγησης';
      require '../etc/head.php'; 
      require_once '../js/datatables/includes.html';
    ?>
    <script type="text/javascript" src="../js/select2.min.js"></script>
    <link href="../css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container {
            min-width: 260px;
            vertical-align: middle;
        }
        .card-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px 24px;
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
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            margin-bottom: 14px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .form-group label {
            font-weight: 600;
            color: #475569;
            font-size: 13px;
        }
        .form-control {
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 14px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin: 16px 0;
        }
        .stat-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 14px 16px;
            text-align: center;
        }
        .stat-card .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
        }
        .stat-card .stat-label {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-completed {
            background: #dcfce7;
            color: #166534;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .results-table th, .results-table td {
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            text-align: left;
        }
        .results-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
        }
    </style>
    <script type="text/javascript">
    $(document).ready(function() {
        $('#klados').select2({
            placeholder: "Επιλέξτε κλάδο/ους",
            allowClear: true,
            width: 'resolve'
        });

        $('input[name="data_source"]').change(function() {
            if ($(this).val() === 'db') {
                $('#db_filters_section').slideDown();
                $('#csv_upload_section').slideUp();
            } else {
                $('#db_filters_section').slideUp();
                $('#csv_upload_section').slideDown();
            }
        });
    });
    </script>
</head>
<body>
    <?php require '../etc/menu.php'; ?>
<div id="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h1 style="margin: 0;">Δημιουργία Αρχείων CSV Αξιολόγησης</h1>
        <a href="aksiologhsh_report.php" class="btn btn-primary" style="text-decoration: none; padding: 7px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px;">
            📊 Προβολή Αναφοράς Αξιολόγησης
        </a>
    </div>

    <p style="color: #475569; font-size: 14px; line-height: 1.5; margin-bottom: 20px;">
        Η παρούσα διαδικασία επεξεργάζεται τα δεδομένα εκπαιδευτικών σε συνδυασμό με το αρχείο αξιολόγησης του Υπουργείου (Excel ή CSV),
        εξαιρεί όσους εκπαιδευτικούς έχουν ήδη ολοκληρώσει την αξιολόγηση και ενημερώνει με επίθημα (suffix) τις εκκρεμότητες στα αρχεία
        <code>a1.csv</code>, <code>a2.csv</code> και <code>b.csv</code> (μονομερή).
    </p>

    <form method="post" enctype="multipart/form-data">
        <!-- 1. Excel/CSV File and Processing Parameters -->
        <div class="card-box">
            <h3>📁 1. Αρχείο Αξιολόγησης (Excel / CSV) & Παράμετροι</h3>
            
            <div class="form-row">
                <div class="form-group" style="flex: 1; min-width: 300px;">
                    <label>Αρχείο Αξιολόγησης (.xlsx, .xls, .csv) <span style="color: red;">*</span>:</label>
                    <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls, .csv" required>
                    <small style="color: #64748b;">Το αρχείο εξαγωγής αξιολόγησης (π.χ. axiologisi-gov.xlsx ή axiologisi-gov.csv)</small>
                </div>

                <div class="form-group" style="width: 160px;">
                    <label>Επίθημα (Suffix):</label>
                    <input type="text" name="suffix" class="form-control" value="<?php echo htmlspecialchars($_POST['suffix'] ?? DEFAULT_SUFFIX); ?>" required>
                    <small style="color: #64748b;">π.χ. 2026-27</small>
                </div>

                <div class="form-group" style="flex: 1; min-width: 260px;">
                    <label>Κανόνας εξαίρεσης ολοκληρωμένων:</label>
                    <select name="exclusion_mode" class="form-control">
                        <option value="all" <?php echo (($_POST['exclusion_mode'] ?? 'all') === 'all') ? 'selected' : ''; ?>>
                            Εξαίρεση εάν ολοκλήρωσε σε οποιοδήποτε πεδίο (Α1, Α2, Β) - Προτεινόμενο
                        </option>
                        <option value="per_category" <?php echo (($_POST['exclusion_mode'] ?? '') === 'per_category') ? 'selected' : ''; ?>>
                            Εξαίρεση ανά συγκεκριμένο πεδίο (πεδίο προς πεδίο)
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Data Source Selection -->
        <div class="card-box">
            <h3>🗄️ 2. Πηγή Δεδομένων</h3>

            <div style="margin-bottom: 14px;">
                <label style="margin-right: 20px; font-weight: bold; cursor: pointer;">
                    <input type="radio" name="data_source" value="db" <?php echo (($_POST['data_source'] ?? 'db') === 'db') ? 'checked' : ''; ?>>
                    Αυτόματη άντληση εκπαιδευτικών από τη βάση δεδομένων Proteas (Προεπιλογή)
                </label>
                <label style="font-weight: bold; cursor: pointer;">
                    <input type="radio" name="data_source" value="csv" <?php echo (($_POST['data_source'] ?? '') === 'csv') ? 'checked' : ''; ?>>
                    Μεταφόρτωση έτοιμων αρχείων a1.csv, a2.csv, b.csv
                </label>
            </div>

            <!-- Database Filters Section -->
            <div id="db_filters_section" style="<?php echo (($_POST['data_source'] ?? 'db') === 'csv') ? 'display:none;' : ''; ?> background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px dashed #cbd5e1;">
                <p style="margin: 0 0 10px 0; font-size: 13px; color: #475569; font-weight: bold;">Φίλτρα άντλησης από τη βάση δεδομένων (προαιρετικά):</p>
                <div class="form-row">
                    <div class="form-group">
                        <label>Ημ/νία Διορισμού από:</label>
                        <input type="date" name="hm_dior_from" class="form-control" value="<?php echo htmlspecialchars($_POST['hm_dior_from'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>έως:</label>
                        <input type="date" name="hm_dior_to" class="form-control" value="<?php echo htmlspecialchars($_POST['hm_dior_to'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
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
                    </div>
                    <div class="form-group">
                        <label>Μονιμοποίηση:</label>
                        <select name="monimopoihsh" class="form-control">
                            <option value="" <?php echo (isset($_POST['monimopoihsh']) && $_POST['monimopoihsh'] === '') ? 'selected' : ''; ?>>Όλοι</option>
                            <option value="1" <?php echo (isset($_POST['monimopoihsh']) && $_POST['monimopoihsh'] === '1') ? 'selected' : ''; ?>>Ναι</option>
                            <option value="0" <?php echo (isset($_POST['monimopoihsh']) && $_POST['monimopoihsh'] === '0') ? 'selected' : ''; ?>>Όχι</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Αξιολόγηση:</label>
                        <select name="aksiologhsh" class="form-control">
                            <option value="" <?php echo (isset($_POST['aksiologhsh']) && $_POST['aksiologhsh'] === '') ? 'selected' : ''; ?>>Όλοι</option>
                            <option value="1" <?php echo (isset($_POST['aksiologhsh']) && $_POST['aksiologhsh'] === '1') ? 'selected' : ''; ?>>Ναι</option>
                            <option value="0" <?php echo (isset($_POST['aksiologhsh']) && $_POST['aksiologhsh'] === '0') ? 'selected' : ''; ?>>Όχι</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ημ/νία Αξιολόγησης από:</label>
                        <input type="date" name="aks_date_from" class="form-control" value="<?php echo htmlspecialchars($_POST['aks_date_from'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>έως:</label>
                        <input type="date" name="aks_date_to" class="form-control" value="<?php echo htmlspecialchars($_POST['aks_date_to'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Upload Existing CSVs Section -->
            <div id="csv_upload_section" style="<?php echo (($_POST['data_source'] ?? '') === 'csv') ? '' : 'display:none;'; ?> background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px dashed #cbd5e1;">
                <p style="margin: 0 0 10px 0; font-size: 13px; color: #475569; font-weight: bold;">Επιλογή υπαρχόντων αρχείων CSV (a1.csv, a2.csv, b.csv):</p>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label>a1.csv:</label>
                        <input type="file" name="csv_a1" class="form-control" accept=".csv">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>a2.csv:</label>
                        <input type="file" name="csv_a2" class="form-control" accept=".csv">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>b.csv:</label>
                        <input type="file" name="csv_b" class="form-control" accept=".csv">
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 15px; margin-bottom: 30px;">
            <input type="submit" name="process_eval" value="🚀 Επεξεργασία & Παραγωγή Αρχείων CSV" class="btn btn-green" style="font-size: 15px; font-weight: bold; padding: 10px 20px; cursor: pointer;">
        </div>
    </form>

    <?php
    if (isset($_POST['process_eval'])) {
        $suffix = clean_str($_POST['suffix'] ?? DEFAULT_SUFFIX);
        if ($suffix === '') $suffix = DEFAULT_SUFFIX;
        $per_category = (($_POST['exclusion_mode'] ?? 'all') === 'per_category');
        $data_source = $_POST['data_source'] ?? 'db';

        // 1. Verify File Upload
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            echo "<div class='alert alert-danger' style='background:#fee2e2; border:1px solid #ef4444; color:#b91c1c; padding:12px 16px; border-radius:6px; margin:15px 0;'>
                <strong>Σφάλμα:</strong> Παρακαλώ επιλέξτε ένα έγκυρο αρχείο αξιολόγησης (Excel ή CSV).
            </div>";
        } else {
            $excel_tmp = $_FILES['excel_file']['tmp_name'];
            $excel_name = $_FILES['excel_file']['name'];

            try {
                // Parse Excel or CSV
                list($pending_dict, $completed_all, $completed_by_cat) = extract_evaluations_from_file($excel_tmp, $excel_name);

                $word_dir = __DIR__ . '/../word';
                if (!is_dir($word_dir)) {
                    mkdir($word_dir, 0777, true);
                }

                $stats = [];

                if ($data_source === 'csv') {
                    // Process from uploaded CSV files
                    $uploaded_tasks = [
                        ['A1', 'csv_a1', 'a1.csv'],
                        ['A2', 'csv_a2', 'a2.csv'],
                        ['B', 'csv_b', 'b.csv'],
                    ];

                    foreach ($uploaded_tasks as $t) {
                        $cat = $t[0];
                        $input_name = $t[1];
                        $filename = $t[2];
                        $target_path = $word_dir . '/' . $filename;
                        $exclude_set = $per_category ? $completed_by_cat[$cat] : $completed_all;

                        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
                            $src_path = $_FILES[$input_name]['tmp_name'];
                            list($total, $matched, $excluded, $final_count) = process_uploaded_csv(
                                $src_path,
                                $target_path,
                                $pending_dict[$cat],
                                $exclude_set,
                                $suffix,
                                $cat
                            );
                            $stats[$cat] = [
                                'file' => $filename,
                                'total' => $total,
                                'excluded' => $excluded,
                                'matched' => $matched,
                                'final' => $final_count
                            ];
                        } else {
                            $stats[$cat] = [
                                'file' => $filename,
                                'error' => 'Δεν μεταφορτώθηκε αρχείο'
                            ];
                        }
                    }
                } else {
                    // Process directly from Database
                    $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ', $mysqlconnection);
                    $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ', $mysqlconnection);
                    $ekswteriko = getSchoolID('Απόσπαση στο εξωτερικό', $mysqlconnection);
                    $foreas = getSchoolID('Απόσπαση σε φορέα', $mysqlconnection);
                    $dipe = '398';

                    $se_by_klados = [];
                    $se_res = mysqli_query($mysqlconnection, "SELECT id, klados, afm, eponymo, onoma, emp_id, sch_ids FROM symvouloi_epist");
                    while ($se_row = mysqli_fetch_assoc($se_res)) {
                        $se_by_klados[$se_row['klados']][] = $se_row;
                    }

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
                        s.perif,
                        e.hm_dior,
                        e.thesi,
                        e.monimopoihsh,
                        e.aksiologhsh
                    FROM employee e
                    LEFT JOIN klados k ON e.klados = k.id
                    LEFT JOIN school s ON e.sx_yphrethshs = s.id
                    LEFT JOIN symvouloi sm ON s.perif = sm.perif
                    LEFT JOIN employee sp ON sm.emp_id = sp.id
                    LEFT JOIN employee d ON (s.id = d.sx_yphrethshs AND d.thesi = 2 AND d.status IN (1,3))
                    WHERE e.status = 1 
                    AND e.sx_yphrethshs NOT IN ($allo_pysde, $allo_pyspe, $dipe, $foreas, $ekswteriko)
                    AND s.type2 = 0";

                    if (!empty($_POST['hm_dior_from'])) {
                        $query .= " AND e.hm_dior >= '".$_POST['hm_dior_from']."'";
                    }
                    if (!empty($_POST['hm_dior_to'])) {
                        $query .= " AND e.hm_dior <= '".$_POST['hm_dior_to']."'";
                    }
                    if (!empty($_POST['klados'])) {
                        $klados = implode(",", $_POST['klados']);
                        $query .= " AND e.klados IN ($klados)";
                    }
                    if ($_POST['monimopoihsh'] !== '') {
                        $query .= " AND e.monimopoihsh = ".$_POST['monimopoihsh'];
                    }
                    if ($_POST['aksiologhsh'] !== '') {
                        $query .= " AND e.aksiologhsh = ".$_POST['aksiologhsh'];
                    }
                    if (!empty($_POST['aks_date_from'])) {
                        $query .= " AND e.aksiologhsh_date >= '".$_POST['aks_date_from']."'";
                    }
                    if (!empty($_POST['aks_date_to'])) {
                        $query .= " AND e.aksiologhsh_date <= '".$_POST['aks_date_to']."'";
                    }

                    $query .= " GROUP BY e.id";
                    $db_result = mysqli_query($mysqlconnection, $query);

                    $a1_header = ["Αναγνωριστικό-Εκκρεμότητας", "Αξιολογούμενος-Όνομα", "Αξιολογούμενος-Επίθετο", "Αξιολογούμενος-ΑΦΜ", "Αξιολογητής-Όνομα", "Αξιολογητής-Επίθετο", "Αξιολογητής-ΑΦΜ"];
                    $a2_header = ["Αναγνωριστικό-Εκκρεμότητας", "Αξιολογούμενος-Όνομα", "Αξιολογούμενος-Επίθετο", "Αξιολογούμενος-ΑΦΜ", "Αξιολογητής-Όνομα", "Αξιολογητής-Επίθετο", "Αξιολογητής-ΑΦΜ"];
                    $b_header = ["Αναγνωριστικό-Εκκρεμότητας", "Αξιολογούμενος-Όνομα", "Αξιολογούμενος-Επίθετο", "Αξιολογούμενος-ΑΦΜ", "Αξιολογητής-1-Όνομα", "Αξιολογητής-1-Επίθετο", "Αξιολογητής-1-ΑΦΜ", "Αξιολογητής-2-Όνομα", "Αξιολογητής-2-Επίθετο", "Αξιολογητής-2-ΑΦΜ"];

                    $a1_rows = [];
                    $a2_rows = [];
                    $b_rows = [];

                    $seen_table = [];
                    $seen_a1 = [];
                    $seen_a2 = [];
                    $seen_b = [];

                    $stats = [
                        'A1' => ['file' => 'a1.csv', 'total' => 0, 'excluded' => 0, 'matched' => 0, 'final' => 0],
                        'A2' => ['file' => 'a2.csv', 'total' => 0, 'excluded' => 0, 'matched' => 0, 'final' => 0],
                        'B'  => ['file' => 'b.csv',  'total' => 0, 'excluded' => 0, 'matched' => 0, 'final' => 0],
                    ];

                    $exclude_a1 = $per_category ? $completed_by_cat['A1'] : $completed_all;
                    $exclude_a2 = $per_category ? $completed_by_cat['A2'] : $completed_all;
                    $exclude_b  = $per_category ? $completed_by_cat['B']  : $completed_all;

                    while ($row = mysqli_fetch_array($db_result)) {
                        $emp_afm_raw = trim($row['emp_afm']);
                        if (isset($seen_table[$emp_afm_raw])) {
                            continue;
                        }
                        $seen_table[$emp_afm_raw] = true;

                        $emp_afm = normalize_afm($emp_afm_raw);
                        $emp_name = trim($row['emp_name']);
                        $emp_surname = trim($row['emp_surname']);

                        // Consultant mapping
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

                        $is_eae = (in_array($row['klados'], ['ΠΕ60ΕΑΕ', 'ΠΕ70ΕΑΕ', 'ΠΕ61', 'ΠΕ71']) || in_array($row['klados_id'], [16, 17, 18, 19]));

                        if ($is_eae) {
                            $symv_epist_afm = $row['symv_paid_afm'];
                            $symv_epist_surname = $row['symv_paid_surname'];
                            $symv_epist_name = $row['symv_paid_name'];

                            $eae_c = $consultant_found ?: $default_consultant;
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

                        // --- A1 Field ---
                        if (!empty($se_afm) && !empty($emp_afm) && !isset($seen_a1[$emp_afm])) {
                            $seen_a1[$emp_afm] = true;
                            $stats['A1']['total']++;

                            if (isset($exclude_a1[$emp_afm])) {
                                $stats['A1']['excluded']++;
                            } else {
                                $a1_id = $se_afm . ':' . $emp_afm;
                                $key_a1 = $se_afm . ':' . $emp_afm;
                                $is_pending = isset($pending_dict['A1'][$key_a1]);

                                if ($is_pending) {
                                    $a1_id = append_suffix($a1_id, $suffix);
                                    $stats['A1']['matched']++;
                                }
                                $a1_rows[] = [$a1_id, $emp_name, $emp_surname, $emp_afm, $se_name, $se_surname, $se_afm];
                            }
                        }

                        // --- A2 Field ---
                        if (!isset($seen_a2[$emp_afm]) && !empty($emp_afm)) {
                            $evaluator_afm = '';
                            $evaluator_name = '';
                            $evaluator_surname = '';

                            if ($is_director || empty($dnt_afm)) {
                                if (!empty($sp_afm)) {
                                    $evaluator_afm = $sp_afm;
                                    $evaluator_name = $sp_name;
                                    $evaluator_surname = $sp_surname;
                                }
                            } else {
                                if (!empty($dnt_afm)) {
                                    $evaluator_afm = $dnt_afm;
                                    $evaluator_name = $dnt_name;
                                    $evaluator_surname = $dnt_surname;
                                }
                            }

                            if (!empty($evaluator_afm)) {
                                $seen_a2[$emp_afm] = true;
                                $stats['A2']['total']++;

                                if (isset($exclude_a2[$emp_afm])) {
                                    $stats['A2']['excluded']++;
                                } else {
                                    $a2_id = $evaluator_afm . ':' . $emp_afm;
                                    $key_a2 = $evaluator_afm . ':' . $emp_afm;
                                    $is_pending = isset($pending_dict['A2'][$key_a2]);

                                    if ($is_pending) {
                                        $a2_id = append_suffix($a2_id, $suffix);
                                        $stats['A2']['matched']++;
                                    }
                                    $a2_rows[] = [$a2_id, $emp_name, $emp_surname, $emp_afm, $evaluator_name, $evaluator_surname, $evaluator_afm];
                                }
                            }
                        }

                        // --- B Field ---
                        if (!isset($seen_b[$emp_afm]) && !empty($emp_afm)) {
                            if ($is_director || empty($dnt_afm)) {
                                if (!empty($sp_afm)) {
                                    $seen_b[$emp_afm] = true;
                                    $stats['B']['total']++;

                                    if (isset($exclude_b[$emp_afm])) {
                                        $stats['B']['excluded']++;
                                    } else {
                                        $b_id = $sp_afm . ':' . $emp_afm;
                                        $b_key = $sp_afm . ':' . $emp_afm;
                                        $is_pending = isset($pending_dict['B'][$b_key]);

                                        if ($is_pending) {
                                            $b_id = append_suffix($b_id, $suffix);
                                            $stats['B']['matched']++;
                                        }
                                        $b_rows[] = [$b_id, $emp_name, $emp_surname, $emp_afm, $sp_name, $sp_surname, $sp_afm, '', '', ''];
                                    }
                                }
                            } else {
                                if (!empty($dnt_afm)) {
                                    $seen_b[$emp_afm] = true;
                                    $stats['B']['total']++;

                                    if (isset($exclude_b[$emp_afm])) {
                                        $stats['B']['excluded']++;
                                    } else {
                                        if (!empty($sp_afm)) {
                                            $b_id = $dnt_afm . ':' . $sp_afm . ':' . $emp_afm;
                                            $b_key = $dnt_afm . ':' . $sp_afm . ':' . $emp_afm;
                                            $is_pending = isset($pending_dict['B'][$b_key]);

                                            if ($is_pending) {
                                                $b_id = append_suffix($b_id, $suffix);
                                                $stats['B']['matched']++;
                                            }
                                            $b_rows[] = [$b_id, $emp_name, $emp_surname, $emp_afm, $dnt_name, $dnt_surname, $dnt_afm, $sp_name, $sp_surname, $sp_afm];
                                        } else {
                                            $b_id = $dnt_afm . ':' . $emp_afm;
                                            $b_key = $dnt_afm . ':' . $emp_afm;
                                            $is_pending = isset($pending_dict['B'][$b_key]);

                                            if ($is_pending) {
                                                $b_id = append_suffix($b_id, $suffix);
                                                $stats['B']['matched']++;
                                            }
                                            $b_rows[] = [$b_id, $emp_name, $emp_surname, $emp_afm, $dnt_name, $dnt_surname, $dnt_afm, '', '', ''];
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $stats['A1']['final'] = count($a1_rows);
                    $stats['A2']['final'] = count($a2_rows);
                    $stats['B']['final'] = count($b_rows);

                    // Write CSV files with UTF-8 BOM
                    $f_a1 = fopen($word_dir . '/a1.csv', 'w');
                    fwrite($f_a1, "\xEF\xBB\xBF");
                    fwrite($f_a1, format_csv_row($a1_header) . "\r\n");
                    foreach ($a1_rows as $r) {
                        fwrite($f_a1, format_csv_row($r) . "\r\n");
                    }
                    fclose($f_a1);

                    $f_a2 = fopen($word_dir . '/a2.csv', 'w');
                    fwrite($f_a2, "\xEF\xBB\xBF");
                    fwrite($f_a2, format_csv_row($a2_header) . "\r\n");
                    foreach ($a2_rows as $r) {
                        fwrite($f_a2, format_csv_row($r) . "\r\n");
                    }
                    fclose($f_a2);

                    $f_b = fopen($word_dir . '/b.csv', 'w');
                    fwrite($f_b, "\xEF\xBB\xBF");
                    fwrite($f_b, format_csv_row($b_header) . "\r\n");
                    foreach ($b_rows as $r) {
                        fwrite($f_b, format_csv_row($r) . "\r\n");
                    }
                    fclose($f_b);
                }

                // Create ZIP file
                $zipname = $word_dir . '/aksiologhsh_csv.zip';
                if (class_exists('ZipArchive')) {
                    $zip = new ZipArchive();
                    if ($zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                        if (file_exists($word_dir . '/a1.csv')) $zip->addFile($word_dir . '/a1.csv', 'a1.csv');
                        if (file_exists($word_dir . '/a2.csv')) $zip->addFile($word_dir . '/a2.csv', 'a2.csv');
                        if (file_exists($word_dir . '/b.csv')) $zip->addFile($word_dir . '/b.csv', 'b.csv');
                        $zip->close();
                    }
                }

                // Display Results
                echo "
                <div class='card-box' style='background: #f0fdf4; border: 1px solid #86efac;'>
                    <div style='display: flex; align-items: center; margin-bottom: 12px;'>
                        <span style='font-size: 24px; color: #16a34a; margin-right: 10px;'>✓</span>
                        <h2 style='margin: 0; color: #14532d; font-size: 20px;'>Η επεξεργασία ολοκληρώθηκε με επιτυχία!</h2>
                    </div>
                    <p style='color: #166534; margin: 0 0 16px 0; font-size: 14px;'>
                        Αναλύθηκε το αρχείο <strong>".htmlspecialchars($excel_name)."</strong> και εφαρμόστηκε επίθημα <code>:".htmlspecialchars($suffix)."</code> στις εκκρεμότητες.
                    </p>

                    <div class='stats-grid'>
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #166534;'>".count($completed_all)."</div>
                            <div class='stat-label'>Ολοκλήρωσαν αξιολόγηση</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #b45309;'>".count($pending_dict['A1'])."</div>
                            <div class='stat-label'>Εκκρεμότητες Α1 (Αρχείο)</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #b45309;'>".count($pending_dict['A2'])."</div>
                            <div class='stat-label'>Εκκρεμότητες Α2 (Αρχείο)</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #b45309;'>".count($pending_dict['B'])."</div>
                            <div class='stat-label'>Εκκρεμότητες Β (Αρχείο)</div>
                        </div>
                    </div>

                    <table class='results-table' style='background: white; border-radius: 6px; overflow: hidden;'>
                        <thead>
                            <tr>
                                <th>Αρχείο</th>
                                <th style='text-align: center;'>Αρχικές Εγγραφές</th>
                                <th style='text-align: center;'>Εξαιρέθηκαν (Ολοκλήρωσαν)</th>
                                <th style='text-align: center;'>Με Επίθημα (Suffix)</th>
                                <th style='text-align: center;'>Τελικές Εγγραφές</th>
                                <th style='text-align: center;'>Λήψη Αρχείου</th>
                            </tr>
                        </thead>
                        <tbody>";
                        foreach (['A1' => 'a1', 'A2' => 'a2', 'B' => 'b'] as $cat => $key) {
                            $st = $stats[$cat];
                            if (isset($st['error'])) {
                                echo "<tr>
                                    <td><strong>$cat ($key.csv)</strong></td>
                                    <td colspan='5' style='color: #b91c1c;'>{$st['error']}</td>
                                </tr>";
                            } else {
                                echo "<tr>
                                    <td><strong>$cat ({$st['file']})</strong></td>
                                    <td style='text-align: center;'>{$st['total']}</td>
                                    <td style='text-align: center;'><span class='badge-completed'>-{$st['excluded']}</span></td>
                                    <td style='text-align: center;'><span class='badge-pending'>+{$st['matched']}</span></td>
                                    <td style='text-align: center; font-weight: bold;'>{$st['final']}</td>
                                    <td style='text-align: center;'>
                                        <a href='?download=$key' class='btn btn-primary' style='text-decoration: none; padding: 4px 10px; font-size: 13px; border-radius: 4px;'>
                                            📥 Λήψη $key.csv
                                        </a>
                                    </td>
                                </tr>";
                            }
                        }
                        echo "</tbody>
                    </table>

                    <div style='margin-top: 20px; display: flex; gap: 12px; align-items: center;'>
                        <a href='?download=zip' class='btn btn-green' style='text-decoration: none; padding: 10px 18px; font-size: 15px; font-weight: bold; border-radius: 5px; display: inline-flex; align-items: center; gap: 8px;'>
                            📦 Λήψη όλων σε ενιαίο ZIP (aksiologhsh_csv.zip)
                        </a>
                    </div>
                </div>";

            } catch (Exception $e) {
                echo "<div class='alert alert-danger' style='background:#fee2e2; border:1px solid #ef4444; color:#b91c1c; padding:15px; border-radius:6px; margin:20px 0;'>
                    <strong>Σφάλμα κατά την επεξεργασία:</strong> ".htmlspecialchars($e->getMessage())."
                </div>";
            }
        }
    }
    ?>
</div>
</body>
</html>
