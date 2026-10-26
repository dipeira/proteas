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
        'directors' => ['file' => 'directors.csv', 'mime' => 'text/csv; charset=utf-8'],
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

if (!function_exists('clean_str')) {
    function clean_str($val) {
        if ($val === null) return "";
        $s = trim((string)$val);
        $s = str_replace(["\xC2\xA0", "\xEF\xBB\xBF"], [' ', ''], $s);
        return trim($s);
    }
}

if (!function_exists('normalize_afm')) {
    function normalize_afm($afm) {
        $afm_clean = clean_str($afm);
        if (ctype_digit($afm_clean) && strlen($afm_clean) <= 9) {
            return str_pad($afm_clean, 9, '0', STR_PAD_LEFT);
        }
        return $afm_clean;
    }
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

    $teacher_afm_col_idx = null;
    try {
        $teacher_afm_col_idx = find_column_index($headers, ["ΑΦΜ", "Αξιολογούμενος-ΑΦΜ", "Αξιολογούμενος ΑΦΜ"]);
    } catch (Exception $e) {
        $teacher_afm_col_idx = null;
    }

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
            if ($val_id === '' && ($teacher_afm_col_idx === null || clean_str($row[$teacher_afm_col_idx] ?? '') === '')) continue;

            if ($val_id !== '') {
                $afm_key = extract_afms($val_id);
                if (!empty($afm_key)) {
                    $key_str = implode(':', $afm_key);
                    if (!isset($status_map[$key_str])) {
                        $status_map[$key_str] = [];
                    }
                    $status_map[$key_str][] = $val_status;
                }
            }

            $val_status_norm = normalize_greek_text($val_status);
            foreach ($excluded_norms as $ex_norm) {
                if (mb_strpos($val_status_norm, $ex_norm) !== false) {
                    $t_afm = extract_teacher_afm($val_id, $cat_name);
                    if ($t_afm !== '') {
                        $completed_by_category[$cat_name][$t_afm] = true;
                        $completed_all[$t_afm] = true;
                    }
                    if ($teacher_afm_col_idx !== null) {
                        $col_afm = normalize_afm($row[$teacher_afm_col_idx] ?? '');
                        if ($col_afm !== '') {
                            $completed_by_category[$cat_name][$col_afm] = true;
                            $completed_all[$col_afm] = true;
                        }
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

function process_uploaded_csv($csv_path, $output_path, $pending_keys, $completed_teachers, $suffix, $category, array $excluded_a2_afms = [], array $consultant_afms = [], array $director_afms = [], array &$excluded_directors = []) {
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

        // Exclude education consultants if they appear as evaluated
        if (!empty($consultant_afms)) {
            $is_consultant = ($teacher_afm !== '' && isset($consultant_afms[$teacher_afm])) ||
                             ($id_teacher_afm !== '' && isset($consultant_afms[$id_teacher_afm]));
            if ($is_consultant) {
                $excluded_rows++;
                continue;
            }
        }

        if ($category === 'A2') {
            $is_excluded_branch = ($teacher_afm !== '' && isset($excluded_a2_afms[$teacher_afm])) ||
                                  ($id_teacher_afm !== '' && isset($excluded_a2_afms[$id_teacher_afm]));
            if ($is_excluded_branch) {
                $excluded_rows++;
                continue;
            }
        }

        // New rule: Directors/Heads are completely excluded from all CSV files (A1, A2, B)
        if (!empty($director_afms)) {
            $is_dir = ($teacher_afm !== '' && isset($director_afms[$teacher_afm])) ||
                      ($id_teacher_afm !== '' && isset($director_afms[$id_teacher_afm]));
            if ($is_dir) {
                $excluded_rows++;
                $dir_afm = ($teacher_afm !== '') ? $teacher_afm : $id_teacher_afm;
                $excluded_directors[$dir_afm] = true;
                continue;
            }
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
        .consultant-popover-wrapper:hover .consultant-popover-box {
            display: block !important;
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

        if ($('#directors_table').length) {
            $('#directors_table').DataTable({
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Όλοι"]],
                language: {
                    url: '../js/datatables/greek.json'
                },
                order: [[3, 'asc'], [4, 'asc']]
            });
        }
    });
    </script>
</head>
<body>
    <?php require '../etc/menu.php'; ?>
<div id="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <h1 style="margin: 0;">Δημιουργία Αρχείων CSV Αξιολόγησης</h1>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="report_eval_symv.php" class="btn btn-primary" style="text-decoration: none; padding: 7px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; background: #059669;">
                👥 Υποχρεώσεις ανά Σύμβουλο (Excel / Email)
            </a>
            <a href="eval_emp_csv.php" class="btn btn-primary" style="text-decoration: none; padding: 7px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; background: #0284c7;">
                🔍 Χειροκίνητη Εισαγωγή Γραμμών Εκπ/κού (a1, a2, b)
            </a>
            <a href="aksiologhsh_report.php" class="btn btn-primary" style="text-decoration: none; padding: 7px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px;">
                📊 Προβολή Αναφοράς Αξιολόγησης
            </a>
        </div>
    </div>

    <p style="color: #475569; font-size: 14px; line-height: 1.5; margin-bottom: 20px;">
        Η παρούσα διαδικασία επεξεργάζεται τα δεδομένα εκπαιδευτικών σε συνδυασμό με το αρχείο αξιολόγησης του Υπουργείου (Excel ή CSV),
        εξαιρεί από το αντίστοιχο αρχείο (A1, A2, B) όσους εκπαιδευτικούς έχουν ήδη ολοκληρώσει την αξιολόγηση στο συγκεκριμένο πεδίο και ενημερώνει με επίθημα (suffix) τις εκκρεμότητες στα αρχεία
        <code>a1.csv</code>, <code>a2.csv</code> και <code>b.csv</code> (μονομερή).
    </p>

    <!-- Έλεγχος & Προβολή Τελευταίας Εξαγωγής Αρχείων CSV στο φάκελο word -->
    <?php
    $word_dir_path = __DIR__ . '/../word';
    $csv_file_configs = [
        'a1' => ['file' => 'a1.csv', 'label' => 'a1.csv'],
        'a2' => ['file' => 'a2.csv', 'label' => 'a2.csv'],
        'b'  => ['file' => 'b.csv',  'label' => 'b.csv'],
        'directors' => ['file' => 'directors.csv', 'label' => 'directors.csv'],
    ];
    $last_csv_export_timestamp = null;
    $available_csv_files = [];
    foreach ($csv_file_configs as $key => $cfg) {
        $full_p = $word_dir_path . '/' . $cfg['file'];
        if (file_exists($full_p)) {
            $mtime = filemtime($full_p);
            if ($last_csv_export_timestamp === null || $mtime > $last_csv_export_timestamp) {
                $last_csv_export_timestamp = $mtime;
            }
            $available_csv_files[$key] = [
                'file' => $cfg['file'],
                'label' => $cfg['label'],
                'mtime' => $mtime,
                'size' => filesize($full_p)
            ];
        }
    }
    $has_zip_file = file_exists($word_dir_path . '/aksiologhsh_csv.zip');
    ?>

    <div class="card-box" style="background: #ffffff; border: 1px solid <?= ($last_csv_export_timestamp !== null) ? '#0284c7' : '#cbd5e1' ?>; border-left: 6px solid <?= ($last_csv_export_timestamp !== null) ? '#0284c7' : '#94a3b8' ?>; padding: 16px 20px; margin-bottom: 24px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="background: <?= ($last_csv_export_timestamp !== null) ? '#e0f2fe' : '#f1f5f9' ?>; width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <?= ($last_csv_export_timestamp !== null) ? '📁' : 'ℹ️' ?>
                </div>
                <div>
                    <div style="font-size: 15px; color: #0f172a;">
                        <strong>Ημερομηνία τελευταίας εξαγωγής αρχείων csv:</strong>
                        <?php if ($last_csv_export_timestamp !== null): ?>
                            <span style="display: inline-block; background: #0284c7; color: #ffffff; font-weight: 700; font-size: 14px; padding: 3px 10px; border-radius: 5px; margin-left: 6px; letter-spacing: 0.3px;">
                                <?= date('d/m/Y H:i', $last_csv_export_timestamp) ?>
                            </span>
                        <?php else: ?>
                            <span style="color: #64748b; font-style: italic; margin-left: 6px;">
                                Δεν έχει πραγματοποιηθεί προηγούμενη εξαγωγή (δεν βρέθηκαν αρχεία στο φάκελο word).
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($last_csv_export_timestamp !== null): ?>
                        <div style="font-size: 13px; color: #64748b; margin-top: 4px;">
                            Τα αρχεία βρίσκονται αποθηκευμένα στο φάκελο <code>word</code> (ακριβής καταγραφή: <?= date('d/m/Y H:i:s', $last_csv_export_timestamp) ?>).
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($available_csv_files)): ?>
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    <span style="font-size: 13px; color: #475569; font-weight: 600;">Άμεση λήψη αρχείων:</span>
                    <?php foreach ($available_csv_files as $k => $f): ?>
                        <a href="?download=<?= $k ?>" class="btn" style="text-decoration: none; padding: 6px 11px; font-size: 13px; border-radius: 4px; background: #f8fafc; border: 1px solid #cbd5e1; color: #0f172a; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" title="Λήψη <?= $f['file'] ?> (<?= round($f['size'] / 1024, 1) ?> KB)">
                            📥 <?= $f['file'] ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($has_zip_file): ?>
                        <a href="?download=zip" class="btn" style="text-decoration: none; padding: 6px 13px; font-size: 13px; border-radius: 4px; background: #059669; color: #ffffff; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" title="Λήψη όλων σε ενιαίο ZIP">
                            📦 ZIP
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

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
                        <option value="per_category" <?php echo (($_POST['exclusion_mode'] ?? 'per_category') === 'per_category') ? 'selected' : ''; ?>>
                            Εξαίρεση ανά συγκεκριμένο πεδίο (Α1, Α2, Β) - Προτεινόμενο
                        </option>
                        <option value="all" <?php echo (($_POST['exclusion_mode'] ?? '') === 'all') ? 'selected' : ''; ?>>
                            Εξαίρεση εάν ολοκλήρωσε σε οποιοδήποτε πεδίο (καθολική)
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
                    <?php if ($last_csv_export_timestamp !== null): ?>
                        <span style="font-weight: 600; font-size: 12px; color: #0284c7; margin-left: 6px;">(τελευταία δημιουργία: <?= date('d/m/Y H:i', $last_csv_export_timestamp) ?>)</span>
                    <?php endif; ?>
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
                        <label>Αριθμός αξιολογούμενων ανά σύμβουλο:</label>
                        <input type="number" name="eval_per_consultant" min="1" class="form-control" style="width: 140px;" value="<?php echo htmlspecialchars($_POST['eval_per_consultant'] ?? ''); ?>" placeholder="π.χ. 25">
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
        $per_category = (($_POST['exclusion_mode'] ?? 'per_category') === 'per_category');
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

                    // Pre-load AFMs for branches excluded from A2 (ΔΕ1ΕΒΠ, ΤΕ01)
                    $excluded_a2_afms = [];
                    $res_ex = mysqli_query($mysqlconnection, "SELECT afm FROM employee WHERE klados IN (12, 31) UNION SELECT afm FROM ektaktoi WHERE klados IN (12, 31)");
                    if ($res_ex) {
                        while ($r_ex = mysqli_fetch_assoc($res_ex)) {
                            if (!empty($r_ex['afm'])) {
                                $excluded_a2_afms[normalize_afm($r_ex['afm'])] = true;
                            }
                        }
                    }

                    // Pre-load consultant AFMs (teachers whose school is 'Σύμβουλος Εκπαίδευσης' or who are consultants)
                    $consultant_afms = [];
                    $sxol_symv_upload = getSchoolID('Σύμβουλος Εκπαίδευσης', $mysqlconnection);
                    if (!$sxol_symv_upload) {
                        $sxol_symv_upload = getSchoolID('Σύμβουλος εκπαίδευσης', $mysqlconnection);
                    }
                    $symv_ids_upload = $sxol_symv_upload ? [(int)$sxol_symv_upload] : [397];
                    $res_symv_u = mysqli_query($mysqlconnection, "SELECT id FROM school WHERE name LIKE '%Σύμβουλος Εκπαίδευσης%' OR name LIKE '%Σύμβουλος εκπαίδευσης%'");
                    if ($res_symv_u) {
                        while ($r_su = mysqli_fetch_assoc($res_symv_u)) {
                            $symv_ids_upload[] = (int)$r_su['id'];
                        }
                    }
                    $symv_ids_upload = array_unique($symv_ids_upload);
                    $symv_ids_upload_str = implode(',', $symv_ids_upload);

                    $q_symv_afms = "SELECT DISTINCT e.afm 
                        FROM employee e 
                        LEFT JOIN yphrethsh y ON e.id = y.emp_id 
                        WHERE e.sx_yphrethshs IN ($symv_ids_upload_str) 
                           OR e.sx_organikhs IN ($symv_ids_upload_str) 
                           OR y.yphrethsh IN ($symv_ids_upload_str)";
                    $res_symv_afms = mysqli_query($mysqlconnection, $q_symv_afms);
                    if ($res_symv_afms) {
                        while ($r_sa = mysqli_fetch_assoc($res_symv_afms)) {
                            if (!empty($r_sa['afm'])) {
                                $consultant_afms[normalize_afm($r_sa['afm'])] = true;
                            }
                        }
                    }

                    // Pre-load director AFMs
                    $director_afms = [];
                    $res_dir = mysqli_query($mysqlconnection, "SELECT afm FROM employee WHERE thesi = 2 AND status IN (1,3)");
                    if ($res_dir) {
                        while ($r_dir = mysqli_fetch_assoc($res_dir)) {
                            if (!empty($r_dir['afm'])) {
                                $director_afms[normalize_afm($r_dir['afm'])] = true;
                            }
                        }
                    }
                    $excluded_directors_csv = [];

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
                                $cat,
                                $excluded_a2_afms,
                                $consultant_afms,
                                $director_afms,
                                $excluded_directors_csv
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

                    // Build directors report from excluded directors in uploaded CSV (ONLY hm_dior >= 2020-01-01)
                    $directors_report_rows = [];
                    if (!empty($excluded_directors_csv)) {
                        $csv_afms_quoted = [];
                        foreach (array_keys($excluded_directors_csv) as $daf) {
                            $daf_clean = mysqli_real_escape_string($mysqlconnection, $daf);
                            $csv_afms_quoted[] = "'$daf_clean'";
                        }
                        $csv_afms_in = implode(',', $csv_afms_quoted);
                        $q_dir_info = "SELECT e.id as emp_id, e.am as emp_am, e.afm as emp_afm, e.surname as emp_surname, e.name as emp_name, s.name as sch_name, e.hm_dior 
                                       FROM employee e 
                                       LEFT JOIN school s ON e.sx_yphrethshs = s.id 
                                       WHERE e.afm IN ($csv_afms_in)
                                         AND e.hm_dior >= '2020-01-01'
                                       ORDER BY e.surname ASC, e.name ASC";
                        $res_dir_info = mysqli_query($mysqlconnection, $q_dir_info);
                        if ($res_dir_info) {
                            while ($r_di = mysqli_fetch_assoc($res_dir_info)) {
                                $directors_report_rows[] = [
                                    'emp_id' => $r_di['emp_id'],
                                    'am' => clean_str($r_di['emp_am'] ?? ''),
                                    'afm' => normalize_afm($r_di['emp_afm']),
                                    'surname' => trim($r_di['emp_surname']),
                                    'name' => trim($r_di['emp_name']),
                                    'sch_name' => clean_str($r_di['sch_name'] ?? ''),
                                    'hm_dior' => (!empty($r_di['hm_dior']) && $r_di['hm_dior'] !== '0000-00-00') ? date('d/m/Y', strtotime($r_di['hm_dior'])) : ''
                                ];
                            }
                        }
                    }

                    // Write directors.csv with UTF-8 BOM
                    $f_dir = fopen($word_dir . '/directors.csv', 'w');
                    fwrite($f_dir, "\xEF\xBB\xBF");
                    fwrite($f_dir, format_csv_row(["ΑΜ", "ΑΦΜ", "Επώνυμο", "Όνομα", "Σχολείο Υπηρέτησης", "Ημερομηνία Διορισμού"]) . "\r\n");
                    foreach ($directors_report_rows as $dr) {
                        fwrite($f_dir, format_csv_row([
                            $dr['am'],
                            $dr['afm'],
                            $dr['surname'],
                            $dr['name'],
                            $dr['sch_name'],
                            $dr['hm_dior']
                        ]) . "\r\n");
                    }
                    fclose($f_dir);
                } else {
                    // Process directly from Database
                    $sxol_etos = getParam('sxol_etos', $mysqlconnection);
                    $excluded_info = get_eval_excluded_schools($mysqlconnection);
                    $symv_school_ids = $excluded_info['symv_school_ids'];

                    $lookups = get_eval_lookups($mysqlconnection);
                    $se_by_klados = $lookups['se_by_klados'];
                    $se_edra_allou = $lookups['se_edra_allou'];
                    $se_aksiologhsh_ews = $lookups['se_aksiologhsh_ews'];
                    $pe60_consultant_afms = $lookups['pe60_consultant_afms'];
                    $all_director_afms = $lookups['all_director_afms'];

                    $query = build_eval_evaluees_query($mysqlconnection, $sxol_etos, $_POST, $excluded_info);
                    $db_result = mysqli_query($mysqlconnection, $query);

                    $seen_table = [];
                    $candidates_by_consultant = [];
                    $consultant_is_pe60 = [];
                    $directors_report_rows = [];
                    $seen_directors = [];
                    $excluded_directors_count = 0;

                    if ($db_result) {
                        while ($row = mysqli_fetch_array($db_result)) {
                            $emp_afm_raw = trim($row['emp_afm']);
                            if (isset($seen_table[$emp_afm_raw]) || in_array($row['sch_id'], $symv_school_ids)) {
                                continue;
                            }
                            $seen_table[$emp_afm_raw] = true;

                            // Exclude directors/principals completely from CSVs; collect only hm_dior >= 2020-01-01
                            $is_director = is_eval_director($row, $all_director_afms);
                            if ($is_director) {
                                $excluded_directors_count++;
                                $d_hm = $row['hm_dior'] ?? '';
                                if (!empty($d_hm) && $d_hm !== '0000-00-00' && $d_hm >= '2020-01-01') {
                                    $d_afm = normalize_afm($emp_afm_raw);
                                    if (!isset($seen_directors[$d_afm])) {
                                        $seen_directors[$d_afm] = true;
                                        $directors_report_rows[] = [
                                            'emp_id' => $row['emp_id'],
                                            'am' => clean_str($row['emp_am'] ?? ''),
                                            'afm' => $d_afm,
                                            'surname' => trim($row['emp_surname']),
                                            'name' => trim($row['emp_name']),
                                            'sch_name' => clean_str($row['sch_name'] ?? ''),
                                            'hm_dior' => date('d/m/Y', strtotime($d_hm))
                                        ];
                                    }
                                }
                                continue;
                            }

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

                    $evaluated_emp_ids = [];
                    foreach ($selected_rows as $r) {
                        $evaluated_emp_ids[] = (int)$r['emp_id'];
                    }
                    $_SESSION['eval_emp_ids'] = $evaluated_emp_ids;

                    $a1_header = ["Αναγνωριστικό-Εκκρεμότητας", "Αξιολογούμενος-Όνομα", "Αξιολογούμενος-Επίθετο", "Αξιολογούμενος-ΑΦΜ", "Αξιολογητής-Όνομα", "Αξιολογητής-Επίθετο", "Αξιολογητής-ΑΦΜ"];
                    $a2_header = ["Αναγνωριστικό-Εκκρεμότητας", "Αξιολογούμενος-Όνομα", "Αξιολογούμενος-Επίθετο", "Αξιολογούμενος-ΑΦΜ", "Αξιολογητής-Όνομα", "Αξιολογητής-Επίθετο", "Αξιολογητής-ΑΦΜ"];
                    $b_header = ["Αναγνωριστικό-Εκκρεμότητας", "Αξιολογούμενος-Όνομα", "Αξιολογούμενος-Επίθετο", "Αξιολογούμενος-ΑΦΜ", "Αξιολογητής-1-Όνομα", "Αξιολογητής-1-Επίθετο", "Αξιολογητής-1-ΑΦΜ", "Αξιολογητής-2-Όνομα", "Αξιολογητής-2-Επίθετο", "Αξιολογητής-2-ΑΦΜ"];

                    $a1_rows = [];
                    $a2_rows = [];
                    $b_rows = [];

                    $seen_a1 = [];
                    $seen_a2 = [];
                    $seen_b = [];

                    $stats = [
                        'A1' => ['file' => 'a1.csv', 'total' => 0, 'excluded' => 0, 'matched' => 0, 'final' => 0],
                        'A2' => ['file' => 'a2.csv', 'total' => 0, 'excluded' => 0, 'matched' => 0, 'final' => 0],
                        'B'  => ['file' => 'b.csv',  'total' => 0, 'excluded' => 0, 'matched' => 0, 'final' => 0, 'excluded_directors' => 0],
                    ];

                    $exclude_a1 = $per_category ? $completed_by_cat['A1'] : $completed_all;
                    $exclude_a2 = $per_category ? $completed_by_cat['A2'] : $completed_all;
                    $exclude_b  = $per_category ? $completed_by_cat['B']  : $completed_all;

                    foreach ($selected_rows as $row) {
                        $emp_afm = normalize_afm($row['emp_afm']);
                        $emp_name = trim($row['emp_name']);
                        $emp_surname = trim($row['emp_surname']);

                        $se_afm = normalize_afm($row['resolved_symv_epist_afm']);
                        $se_name = trim($row['resolved_symv_epist_name']);
                        $se_surname = trim($row['resolved_symv_epist_surname']);

                        $sp_afm = normalize_afm($row['resolved_symv_paid_afm']);
                        $sp_name = trim($row['resolved_symv_paid_name']);
                        $sp_surname = trim($row['resolved_symv_paid_surname']);

                        $dnt_afm = normalize_afm($row['dnt_afm']);
                        $dnt_name = trim($row['dnt_name']);
                        $dnt_surname = trim($row['dnt_surname']);

                        // Defensive check: ensure no director enters CSVs
                        if (is_eval_director($row, $all_director_afms)) {
                            continue;
                        }

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
                        $excluded_a2_kladoi = ['ΔΕ1ΕΒΠ', 'ΤΕ01'];
                        $excluded_a2_ids = [12, 31];
                        $is_excluded_from_a2 = in_array(trim($row['klados'] ?? ''), $excluded_a2_kladoi) || in_array((int)($row['klados_id'] ?? 0), $excluded_a2_ids);

                        if (!$is_excluded_from_a2 && !isset($seen_a2[$emp_afm]) && !empty($emp_afm)) {
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
                                if (empty($dnt_afm)) {
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
                    $stats['A1']['final'] = count($a1_rows);
                    $stats['A2']['final'] = count($a2_rows);
                    $stats['B']['final'] = count($b_rows);
                    $stats['B']['excluded_directors'] = $excluded_directors_count;

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

                    // Sort directors by surname, name
                    usort($directors_report_rows, function($a, $b) {
                        $cmp = strcmp($a['surname'], $b['surname']);
                        if ($cmp !== 0) return $cmp;
                        return strcmp($a['name'], $b['name']);
                    });

                    // Write directors.csv with UTF-8 BOM
                    $f_dir = fopen($word_dir . '/directors.csv', 'w');
                    fwrite($f_dir, "\xEF\xBB\xBF");
                    fwrite($f_dir, format_csv_row(["ΑΜ", "ΑΦΜ", "Επώνυμο", "Όνομα", "Σχολείο Υπηρέτησης", "Ημερομηνία Διορισμού"]) . "\r\n");
                    foreach ($directors_report_rows as $dr) {
                        fwrite($f_dir, format_csv_row([
                            $dr['am'],
                            $dr['afm'],
                            $dr['surname'],
                            $dr['name'],
                            $dr['sch_name'],
                            $dr['hm_dior']
                        ]) . "\r\n");
                    }
                    fclose($f_dir);
                }

                // Create ZIP file
                $zipname = $word_dir . '/aksiologhsh_csv.zip';
                if (class_exists('ZipArchive')) {
                    $zip = new ZipArchive();
                    if ($zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                        if (file_exists($word_dir . '/a1.csv')) $zip->addFile($word_dir . '/a1.csv', 'a1.csv');
                        if (file_exists($word_dir . '/a2.csv')) $zip->addFile($word_dir . '/a2.csv', 'a2.csv');
                        if (file_exists($word_dir . '/b.csv')) $zip->addFile($word_dir . '/b.csv', 'b.csv');
                        if (file_exists($word_dir . '/directors.csv')) $zip->addFile($word_dir . '/directors.csv', 'directors.csv');
                        $zip->close();
                    }
                }

                // Display Results
                $db_summary_html = '';
                if ($data_source === 'db' && !empty($selected_rows)) {
                    $unique_count = count($selected_rows);
                    $popover_rows_html = '';
                    foreach ($consultant_counts as $c_key => $cnt) {
                        $popover_rows_html .= "<tr>
                            <td style='padding: 5px 8px; border-bottom: 1px solid #f1f5f9; text-align: left;'>" . htmlspecialchars($consultant_labels[$c_key] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                            <td style='padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #166534; text-align: right;'>" . $cnt . "</td>
                        </tr>";
                    }
                    $db_summary_html = "
                    <div style='display: flex; justify-content: space-between; align-items: center; margin: 15px 0 20px 0; flex-wrap: wrap; gap: 10px; background: #ffffff; padding: 12px 16px; border-radius: 6px; border: 1px solid #bbf7d0;'>
                        <div style='display: inline-flex; align-items: center; gap: 12px;'>
                            <div style='font-weight: bold; color: #1b5e20; font-size: 15px;'>
                                <span title=\"$consultant_tooltip_attr\" style='cursor: help; border-bottom: 1px dotted #1b5e20;'>$unique_count μοναδικοί εκπαιδευτικοί από Βάση Δεδομένων</span>
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
                        <div style='display: flex; gap: 8px; flex-wrap: wrap;'>
                            <form method='post' action='aksiologhsh_directors.php' target='_blank' style='margin: 0; display: inline-block;'>
                                <input type='hidden' name='dir_data' value='" . htmlspecialchars(json_encode($directors_report_rows, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . "'>
                                <button type='submit' class='btn' style='cursor: pointer; padding: 7px 16px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-weight: bold; background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); color: white; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.12);'>
                                    👔 Αναφορά Δ/ντών - Πρ/νων (" . count($directors_report_rows) . ")
                                </button>
                            </form>
                            <form method='post' action='aksiologhsh_topo.php' target='_blank' style='margin: 0; display: inline-block;'>
                                <input type='hidden' name='emp_ids' value='" . implode(',', $evaluated_emp_ids) . "'>
                                <button type='submit' class='btn btn-blue' style='cursor: pointer; padding: 7px 16px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-weight: bold; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.12);'>
                                    🏫 Αναφορά Τοποθετήσεων Αξιολογούμενων ($unique_count)
                                </button>
                            </form>
                        </div>
                    </div>";
                }

                $dir_count = count($directors_report_rows);

                echo "
                <div class='card-box' style='background: #f0fdf4; border: 1px solid #86efac;'>
                    <div style='display: flex; align-items: center; margin-bottom: 12px;'>
                        <span style='font-size: 24px; color: #16a34a; margin-right: 10px;'>✓</span>
                        <h2 style='margin: 0; color: #14532d; font-size: 20px;'>Η επεξεργασία ολοκληρώθηκε με επιτυχία!</h2>
                    </div>
                    <p style='color: #166534; margin: 0 0 16px 0; font-size: 14px;'>
                        Αναλύθηκε το αρχείο <strong>".htmlspecialchars($excel_name)."</strong> και εφαρμόστηκε επίθημα <code>:".htmlspecialchars($suffix)."</code> στις εκκρεμότητες (Ημερομηνία εξαγωγής: <strong>".date('d/m/Y H:i')."</strong>).
                    </p>
                    $db_summary_html

                    <div class='stats-grid'>
                        " . (($data_source === 'db' && !empty($selected_rows)) ? "
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #0369a1;'>" . count($selected_rows) . "</div>
                            <div class='stat-label'>Επιλεγμένοι Εκπ/κοί</div>
                        </div>
                        " : "") . "
                        " . ($per_category ? "
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #166534;'>" . count($completed_by_cat['A1']) . "</div>
                            <div class='stat-label'>Ολοκλήρωσαν Α1</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #166534;'>" . count($completed_by_cat['A2']) . "</div>
                            <div class='stat-label'>Ολοκλήρωσαν Α2</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #166534;'>" . count($completed_by_cat['B']) . "</div>
                            <div class='stat-label'>Ολοκλήρωσαν Β</div>
                        </div>
                        " : "
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #166534;'>" . count($completed_all) . "</div>
                            <div class='stat-label'>Ολοκλήρωσαν αξιολόγηση</div>
                        </div>
                        ") . "
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #0f766e;'>$dir_count</div>
                            <div class='stat-label'>Δ/ντές - Πρ/νοι (Εκτός Β)</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #b45309;'>" . count($pending_dict['A1']) . "</div>
                            <div class='stat-label'>Εκκρεμότητες Α1 (Αρχείο)</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #b45309;'>" . count($pending_dict['A2']) . "</div>
                            <div class='stat-label'>Εκκρεμότητες Α2 (Αρχείο)</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-value' style='color: #b45309;'>" . count($pending_dict['B']) . "</div>
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
                                $dir_note = ($cat === 'B' && !empty($st['excluded_directors'])) 
                                    ? "<br><small style='color: #991b1b; font-weight: 600;'>-{$st['excluded_directors']} δ/ντές (εκτός CSV)</small>" 
                                    : "";
                                echo "<tr>
                                    <td><strong>$cat ({$st['file']})</strong></td>
                                    <td style='text-align: center;'>{$st['total']}</td>
                                    <td style='text-align: center;'><span class='badge-completed'>-{$st['excluded']}</span>$dir_note</td>
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
                        echo "
                        <tr style='background: #f0fdfa;'>
                            <td><strong>👔 Αναφορά Δ/ντών - Πρ/νων (directors.csv)</strong><br><small style='color: #0f766e;'>Διορισμός από 01/01/2020 (για χειροκίνητη εισαγωγή)</small></td>
                            <td style='text-align: center;'>-</td>
                            <td style='text-align: center;'><span class='badge-completed' style='background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;'>Εξαιρέθηκαν από CSV</span></td>
                            <td style='text-align: center;'>-</td>
                            <td style='text-align: center; font-weight: bold; color: #0f766e;'>$dir_count</td>
                            <td style='text-align: center;'>
                                <a href='?download=directors' class='btn btn-primary' style='text-decoration: none; padding: 4px 10px; font-size: 13px; border-radius: 4px; background: #0d9488;'>
                                    📥 Λήψη directors.csv
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <div style='margin-top: 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;'>
                        <a href='?download=zip' class='btn btn-green' style='text-decoration: none; padding: 10px 18px; font-size: 15px; font-weight: bold; border-radius: 5px; display: inline-flex; align-items: center; gap: 8px;'>
                            📦 Λήψη όλων σε ενιαίο ZIP (aksiologhsh_csv.zip)
                        </a>
                        <a href='?download=directors' class='btn btn-primary' style='text-decoration: none; padding: 10px 18px; font-size: 15px; font-weight: bold; border-radius: 5px; display: inline-flex; align-items: center; gap: 8px; background: #0d9488;'>
                            📥 Λήψη directors.csv ($dir_count)
                        </a>
                    </div>
                </div>";

                // Directors / Heads Detailed Report Card
                if (!empty($directors_report_rows)) {
                    $dir_rows_html = '';
                    $dir_idx = 1;
                    foreach ($directors_report_rows as $dr) {
                        $dr_am = htmlspecialchars($dr['am'] ?: '-');
                        $dr_afm = htmlspecialchars($dr['afm'] ?: '-');
                        $dr_surname = htmlspecialchars($dr['surname']);
                        $dr_name = htmlspecialchars($dr['name']);
                        $dr_sch = htmlspecialchars($dr['sch_name'] ?: '-');
                        $dr_hm = htmlspecialchars($dr['hm_dior'] ?: '-');
                        $dr_id = !empty($dr['emp_id']) ? (int)$dr['emp_id'] : null;

                        $name_link = $dr_id 
                            ? "<a href='../employee/employee.php?id=$dr_id&op=view' target='_blank' style='color:#0f172a; font-weight:600; text-decoration:none;' title='Προβολή καρτέλας εκπαιδευτικού'>$dr_surname</a>" 
                            : "<strong style='color:#0f172a;'>$dr_surname</strong>";

                        $dir_rows_html .= "<tr>
                            <td style='text-align: center; color: #64748b;'>$dir_idx</td>
                            <td style='text-align: center; font-weight: 500;'>$dr_am</td>
                            <td style='text-align: center; font-weight: 500;'>$dr_afm</td>
                            <td>$name_link</td>
                            <td>$dr_name</td>
                            <td>$dr_sch</td>
                            <td style='text-align: center;'>$dr_hm</td>
                        </tr>";
                        $dir_idx++;
                    }

                    echo "
                    <div class='card-box' style='background: #ffffff; border: 1px solid #cbd5e1; margin-top: 24px;'>
                        <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;'>
                            <h3 style='margin: 0; color: #0f766e; border-bottom: none; padding-bottom: 0;'>
                                👔 Αναφορά Διευθυντών / Προϊσταμένων ($dir_count)
                            </h3>
                            <div style='display: flex; gap: 8px;'>
                                <a href='?download=directors' class='btn btn-primary' style='text-decoration: none; padding: 6px 14px; font-size: 13px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; background: #0d9488;'>
                                    📥 Λήψη directors.csv
                                </a>
                                <form method='post' action='aksiologhsh_directors.php' target='_blank' style='margin: 0; display: inline-block;'>
                                    <input type='hidden' name='dir_data' value='" . htmlspecialchars(json_encode($directors_report_rows, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . "'>
                                    <button type='submit' class='btn' style='padding: 6px 14px; font-size: 13px; border-radius: 4px; background: #f1f5f9; border: 1px solid #cbd5e1; cursor: pointer;'>
                                        🖨️ Εκτύπωση / Πλήρης Προβολή
                                    </button>
                                </form>
                            </div>
                        </div>
                        <p style='color: #475569; font-size: 13.5px; line-height: 1.5; margin-bottom: 16px;'>
                            Σύμφωνα με τους κανόνες αξιολόγησης, οι διευθυντές και προϊστάμενοι σχολικών μονάδων <strong>εξαιρούνται πλήρως από τα αρχεία CSV (a1.csv, a2.csv, b.csv)</strong>.
                            Στην παρακάτω αναφορά (και στο αρχείο <code>directors.csv</code>) περιλαμβάνονται <strong>μόνο όσοι έχουν ημερομηνία διορισμού από 01/01/2020 και μετά</strong>, προκειμένου να εισαχθούν χειροκίνητα (manually).
                        </p>
                        <div style='overflow-x: auto;'>
                            <table id='directors_table' class='results-table' style='width: 100%; font-size: 13.5px;'>
                                <thead>
                                    <tr>
                                        <th style='width: 40px; text-align: center;'>#</th>
                                        <th style='width: 90px; text-align: center;'>ΑΜ</th>
                                        <th style='width: 100px; text-align: center;'>ΑΦΜ</th>
                                        <th>Επώνυμο</th>
                                        <th>Όνομα</th>
                                        <th>Σχολείο Υπηρέτησης</th>
                                        <th style='width: 130px; text-align: center;'>Ημ/νία Διορισμού</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    $dir_rows_html
                                </tbody>
                            </table>
                        </div>
                    </div>";
                }

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
