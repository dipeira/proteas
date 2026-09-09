<?php
require_once "../config.php";
require_once "../include/functions.php";
require_once "../tools/class.login.php";

$log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {   
  header("Location: login.php");
  exit;
}
else {
  $logged = 1;
}

$usrlvl = $_SESSION['userlevel'];
if ($usrlvl > 1) {
  echo "<h3>Σφάλμα: Αυτή η ενέργεια μπορεί να γίνει μόνο από προϊστάμενο ή διαχειριστή...</h3>";
  echo "<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
  die();
}

$root_path = '../';
$page_title = 'Εισαγωγή εκπαιδευτικών από αρχείο excel';
?>
<html>
  <head>
    <?php require '../etc/head.php'; ?>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <style>
      .import-container {
        max-width: 950px;
        margin: 25px auto 40px auto;
        padding: 30px 35px;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
      }
      .import-header {
        margin-bottom: 25px;
        padding-bottom: 18px;
        border-bottom: 2px solid #f1f5f9;
      }
      .import-header h2 {
        color: #1e293b;
        font-size: 23px;
        font-weight: 700;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 10px;
      }
      .import-header p {
        color: #64748b;
        font-size: 14.5px;
        margin: 0;
        line-height: 1.5;
      }
      .info-badge-panel {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
      }
      .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
      }
      .guide-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 18px;
        margin-bottom: 25px;
      }
      .guide-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px 20px;
      }
      .guide-card h4 {
        margin: 0 0 10px 0;
        font-size: 15px;
        font-weight: 600;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .guide-card ul {
        margin: 0;
        padding-left: 20px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.6;
      }
      .upload-box {
        background: #f0f9ff;
        border: 2px dashed #38bdf8;
        border-radius: 12px;
        padding: 20px 22px;
        margin-bottom: 18px;
        transition: all 0.2s ease;
      }
      .upload-box:hover, .upload-box:focus-within {
        border-color: #0284c7;
        background: #e0f2fe;
      }
      .upload-box-title {
        font-weight: 600;
        font-size: 15px;
        color: #0369a1;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
      }
      .upload-box-desc {
        color: #64748b;
        font-size: 13px;
        margin-bottom: 10px;
      }
      .upload-box input[type="file"] {
        width: 100%;
        padding: 10px 14px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13.5px;
        cursor: pointer;
        box-sizing: border-box;
      }
      .upload-file-status {
        margin-top: 8px;
        font-size: 13px;
        color: #64748b;
      }
      .alert-box {
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        padding: 14px 18px;
        border-radius: 8px;
        margin: 20px 0;
        color: #92400e;
        font-size: 13.5px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        line-height: 1.5;
      }
      .actions-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
        flex-wrap: wrap;
      }
      .btn-submit-import {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff;
        border: none;
        padding: 12px 26px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
      }
      .btn-submit-import:hover {
        background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
        box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
        transform: translateY(-1px);
      }
      .btn-submit-import:active {
        transform: translateY(0);
      }
      .btn-back {
        background: #dc2626;
        color: #ffffff;
        border: none;
        padding: 11px 22px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
      }
      .btn-back:hover {
        background: #b91c1c;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
      }
      .link-other-import {
        margin-left: auto;
        color: #0284c7;
        font-size: 13.5px;
        text-decoration: none;
        font-weight: 500;
      }
      .link-other-import:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
<?php
require_once '../etc/menu.php';

$showRows = 10;

if (isset($_POST['submit']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
    require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
    $count = $inserted = 0;
    
    // Check if all 2 files are uploaded
    if (!empty($_FILES['proslipsi']['tmp_name']) && 
        !empty($_FILES['analipsi']['tmp_name']))  {
        
        // Load the files
        $proslipsi_path = $_FILES['proslipsi']['tmp_name'];
        $analipsi_path = $_FILES['analipsi']['tmp_name'];
        
        // Load Excel files
        $proslipsi_excel = PHPExcel_IOFactory::load($proslipsi_path);
        $analipsi_excel = PHPExcel_IOFactory::load($analipsi_path);
        
        // Get first worksheets
        $proslipsi_sheet = $proslipsi_excel->getSheet(0);
        $analipsi_sheet = $analipsi_excel->getSheet(0);

        // Helper functions for header mapping and Greek text normalization
        if (!function_exists('cleanExcelHeader')) {
            function cleanExcelHeader($str) {
                $str = str_replace(array("\xc2\xa0", "\t", "\r", "\n"), ' ', (string)$str);
                $str = trim(preg_replace('/\s+/u', ' ', $str));
                return mb_strtoupper($str, 'UTF-8');
            }
        }
        if (!function_exists('normalizeGreekStr')) {
            function normalizeGreekStr($str) {
                $str = str_replace(array("\xc2\xa0", "\t", "\r", "\n"), ' ', (string)$str);
                $str = preg_replace('/\s*\(\s*/u', ' (', $str);
                $str = preg_replace('/\s*\)\s*/u', ') ', $str);
                $str = trim(preg_replace('/\s+/u', ' ', $str));
                $str = mb_strtoupper($str, 'UTF-8');
                $accents = array(
                    'Ά' => 'Α', 'Έ' => 'Ε', 'Ή' => 'Η', 'Ί' => 'Ι', 'Ϊ' => 'Ι', 'ΐ' => 'Ι',
                    'Ό' => 'Ο', 'Ύ' => 'Υ', 'Ϋ' => 'Υ', 'ΰ' => 'Υ', 'Ώ' => 'Ω'
                );
                return strtr($str, $accents);
            }
        }
        if (!function_exists('findHeaderCol')) {
            function findHeaderCol($headers, $candidates) {
                foreach ($candidates as $cand) {
                    $cleaned = cleanExcelHeader($cand);
                    if (isset($headers[$cleaned])) {
                        return $headers[$cleaned];
                    }
                }
                foreach ($headers as $h_name => $col_idx) {
                    foreach ($candidates as $cand) {
                        $cleaned = cleanExcelHeader($cand);
                        if (mb_strpos($h_name, $cleaned) !== false) {
                            return $col_idx;
                        }
                    }
                }
                return null;
            }
        }

        // Read headers from row 1 for proslipsi
        $prosl_headers = array();
        $prosl_highestColumn = $proslipsi_sheet->getHighestColumn();
        $prosl_highestColumnIndex = PHPExcel_Cell::columnIndexFromString($prosl_highestColumn);
        for ($col = 0; $col < $prosl_highestColumnIndex; $col++) {
            $h_clean = cleanExcelHeader($proslipsi_sheet->getCellByColumnAndRow($col, 1)->getValue());
            if ($h_clean !== '') {
                $prosl_headers[$h_clean] = $col;
            }
        }

        // Read headers from row 1 for analipsi
        $anal_headers = array();
        $anal_highestColumn = $analipsi_sheet->getHighestColumn();
        $anal_highestColumnIndex = PHPExcel_Cell::columnIndexFromString($anal_highestColumn);
        for ($col = 0; $col < $anal_highestColumnIndex; $col++) {
            $h_clean = cleanExcelHeader($analipsi_sheet->getCellByColumnAndRow($col, 1)->getValue());
            if ($h_clean !== '') {
                $anal_headers[$h_clean] = $col;
            }
        }
        
        // Validate headers
        $prosl_wrong_columns = !isset($prosl_headers['Α/Α ΡΟΗΣ']) && 
            (trim((string)$proslipsi_sheet->getCellByColumnAndRow(1, 1)->getValue()) != 'Α/Α ΡΟΗΣ');
        $anal_wrong_columns = !isset($anal_headers['ΗΜ. ΑΝΑΛΗΨΗΣ']) && 
            (trim((string)$analipsi_sheet->getCellByColumnAndRow(9, 1)->getValue()) != 'ΗΜ. ΑΝΑΛΗΨΗΣ');

        if ( $prosl_wrong_columns || $anal_wrong_columns ) {
            echo "<h3>Σφάλμα: Λάθος μορφή αρχείων. Ελέγξτε τις επικεφαλίδες.</h3>";
            echo $prosl_wrong_columns ? '<p style="color:red">Εσφαλμένο αρχείο προσλήψεων (δεν βρέθηκε η στήλη "Α/Α ΡΟΗΣ")</p>' : '';
            echo $anal_wrong_columns ? '<p style="color:red">Εσφαλμένο αρχείο αναλήψεων (δεν βρέθηκε η στήλη "ΗΜ. ΑΝΑΛΗΨΗΣ")</p>' : '';
            echo "<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='ektaktoi_import_minedu.php'\">";
            echo "</body></html>";
            exit;
        }

        // Detect column positions for proslipsi (supports both column layouts)
        $col_afm = findHeaderCol($prosl_headers, array('ΑΦΜ'));
        $col_name = findHeaderCol($prosl_headers, array('ΟΝΟΜΑ'));
        $col_surname = findHeaderCol($prosl_headers, array('ΕΠΩΝΥΜΟ'));
        $col_patrwnymo = findHeaderCol($prosl_headers, array('ΠΑΤΡΩΝΥΜΟ'));
        $col_mhtrwnymo = findHeaderCol($prosl_headers, array('ΜΗΤΡΩΝΥΜΟ'));
        $col_klados = findHeaderCol($prosl_headers, array('ΚΛΑΔΟΣ'));
        $col_perioxh = findHeaderCol($prosl_headers, array('ΠΕΡΙΟΧΗ ΠΡΟΣΛΗΨΗΣ', 'ΠΕΡΙΟΧΗ/ΔΟΜΗ ΠΡΟΣΛΗΨΗΣ', 'ΠΕΡΙΟΧΗ'));
        $col_stathero = findHeaderCol($prosl_headers, array('ΤΗΛΕΦΩΝΟ', 'ΣΤΑΘΕΡΟ'));
        $col_kinhto = findHeaderCol($prosl_headers, array('ΚΙΝΗΤΟ'));
        $col_email = findHeaderCol($prosl_headers, array('E-MAIL', 'EMAIL', 'E - MAIL'));
        $col_hours = isset($prosl_headers['ΩΡΑΡΙΟ']) ? $prosl_headers['ΩΡΑΡΙΟ'] : null;
        $col_typos_kenou = isset($prosl_headers['ΤΥΠΟΣ ΚΕΝΟΥ']) ? $prosl_headers['ΤΥΠΟΣ ΚΕΝΟΥ'] : null;
        $col_funding1 = isset($prosl_headers['ΤΥΠΟΣ']) ? $prosl_headers['ΤΥΠΟΣ'] : null;
        $col_funding2 = isset($prosl_headers['ΤΥΠΟΣ ΠΡΟΣΛΗΨΗΣ']) ? $prosl_headers['ΤΥΠΟΣ ΠΡΟΣΛΗΨΗΣ'] : null;

        if ($col_afm === null || $col_perioxh === null) {
            echo "<h3>Σφάλμα: Δεν βρέθηκαν οι απαιτούμενες στήλες (ΑΦΜ ή Περιοχή Πρόσληψης) στο αρχείο προσλήψεων.</h3>";
            echo "<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='ektaktoi_import_minedu.php'\">";
            echo "</body></html>";
            exit;
        }

        // Detect column positions for analipsi
        $col_anal_afm = findHeaderCol($anal_headers, array('ΑΦΜ'));
        if ($col_anal_afm === null) {
            $col_anal_afm = 4;
        }
        $col_anal_hm = findHeaderCol($anal_headers, array('ΗΜ. ΑΝΑΛΗΨΗΣ', 'ΗΜΕΡΟΜΗΝΙΑ ΑΝΑΛΗΨΗΣ', 'ΗΜ/ΝΙΑ ΑΝΑΛΗΨΗΣ'));
        if ($col_anal_hm === null) {
            $col_anal_hm = 9;
        }

        // Process files and create arrays
        $new_anaplirotes = array();
        $analipseis = array();
        $log_messages = array();

        // Prepare connection
        $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
        mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
        mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

        $perioxh = getParam('perioxh',$mysqlconnection);
        if (!$perioxh) {
            echo "<h3>Σφάλμα: Πρέπει να οριστεί το όνομα περιοχής στις παραμέτρους (παράμετρος 'perioxh').</h3>";
            echo "<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='ektaktoi_import_minedu.php'\">";
            echo "</body></html>";
            exit;
        }

        $normPerioxh = normalizeGreekStr($perioxh);

        // Process proslipsi file
        $highestRow = $proslipsi_sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $cellValue = trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_perioxh, $row)->getValue());
            $normCellValue = normalizeGreekStr($cellValue);
            // Match records starting with perioxh (e.g. 'ΗΡΑΚΛΕΙΟΥ (Π.Ε.) - Μειωμένου Ωραρίου')
            if (mb_strpos($normCellValue, $normPerioxh) !== 0) {
                continue;
            }
            $afm = trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_afm, $row)->getValue());
            if (empty($afm)) {
                continue;
            }

            // Determine hours (from 'ΩΡΑΡΙΟ' or 'ΤΥΠΟΣ ΚΕΝΟΥ' or Region description)
            $hours_raw = '';
            if ($col_hours !== null) {
                $hours_raw = trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_hours, $row)->getValue());
            }
            if ($hours_raw === '' && $col_typos_kenou !== null) {
                $hours_raw = trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_typos_kenou, $row)->getValue());
            }
            if ($hours_raw !== '') {
                $hours = (mb_strpos($hours_raw, 'ΑΠΩ') !== false || $hours_raw === '24') ? 24 : 15;
            } else {
                // If column not found/empty, check if region specifies reduced hours (e.g. Μειωμένου Ωραρίου)
                $hours = (mb_strpos($normCellValue, 'ΜΕΙΩΜΕΝΟΥ') !== false) ? 15 : 24;
            }

            // Determine funding / type (3 = ΕΣΠΑ, 2 = Τακτικός)
            $funding_val = '';
            if ($col_funding1 !== null) {
                $funding_val .= ' ' . trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_funding1, $row)->getValue());
            }
            if ($col_funding2 !== null) {
                $funding_val .= ' ' . trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_funding2, $row)->getValue());
            }
            if ($col_funding1 !== null || $col_funding2 !== null) {
                $xrhmatodothsh = (mb_strpos($funding_val, 'ΕΣΠΑ') !== false) ? 3 : 2;
            } else {
                // Default to 3 (ΕΣΠΑ) for formats without explicit funding column
                $xrhmatodothsh = 3;
            }

            if (isset($new_anaplirotes[$afm])) {
                $log_messages[] = "[ΠΡΟΕΙΔΟΠΟΙΗΣΗ] Διπλότυπο ΑΦΜ $afm στο αρχείο προσλήψεων (γραμμή $row). Ενημερώθηκαν τα στοιχεία.";
            }

            $new_anaplirotes[$afm] = array(
                'name' => $col_name !== null ? trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_name, $row)->getValue()) : '',
                'surname' => $col_surname !== null ? trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_surname, $row)->getValue()) : '',
                'patrwnymo' => $col_patrwnymo !== null ? trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_patrwnymo, $row)->getValue()) : '',
                'mhtrwnymo' => $col_mhtrwnymo !== null ? trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_mhtrwnymo, $row)->getValue()) : '',
                'klados' => $col_klados !== null ? trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_klados, $row)->getValue()) : '',
                'stathero' => $col_stathero !== null ? trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_stathero, $row)->getValue()) : '',
                'kinhto' => $col_kinhto !== null ? trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_kinhto, $row)->getValue()) : '',
                'email' => $col_email !== null ? trim((string)$proslipsi_sheet->getCellByColumnAndRow($col_email, $row)->getValue()) : '',
                'hours' => $hours,
                'xrhmatodothsh' => $xrhmatodothsh
            );
        }
        
        // Process analipsi file
        $highestRowAnal = $analipsi_sheet->getHighestRow();
        for ($row = 2; $row <= $highestRowAnal; $row++) {
            $afm = trim((string)$analipsi_sheet->getCellByColumnAndRow($col_anal_afm, $row)->getValue());
            if (empty($afm)) {
                continue;
            }
            $hm_anal = $analipsi_sheet->getCellByColumnAndRow($col_anal_hm, $row)->getValue();
            if (is_numeric($hm_anal)) {
                $analipseis[$afm] = PHPExcel_Style_NumberFormat::toFormattedString($hm_anal, 'YYYY-MM-DD');
            } else {
                $hm_clean = trim((string)$hm_anal);
                $d = DateTime::createFromFormat('d/m/Y', $hm_clean) ?: DateTime::createFromFormat('Y-m-d', $hm_clean);
                $analipseis[$afm] = $d ? $d->format('Y-m-d') : $hm_clean;
            }
        }
        
        // Get parameters
        $hm_apox = date('Y-m-d',strtotime(getParam('endofyear2',$mysqlconnection)));

        // Process and insert data with verbose logging
        $count = 0;                      // Successfully inserted
        $inserted_with_analipsi = 0;     // Inserted with analipsi date
        $inserted_without_analipsi = 0;  // Inserted without analipsi date (empty date)
        $already_in_db = 0;              // Already exist in DB
        $errors = 0;                     // Insertion errors

        foreach ($new_anaplirotes as $afm => $data) {
            $fullname = trim($data['surname'] . ' ' . $data['name']);
            $klados = $data['klados'];

            $has_analipsi = isset($analipseis[$afm]) && !empty($analipseis[$afm]);
            if ($has_analipsi) {
                $esc_anal = mysqli_real_escape_string($mysqlconnection, $analipseis[$afm]);
                $hm_anal_sql = "'$esc_anal'";
            } else {
                $hm_anal_sql = "NULL";
            }

            // Check if already exists in DB
            $esc_afm = mysqli_real_escape_string($mysqlconnection, $afm);
            $sql1 = "SELECT afm FROM ektaktoi WHERE afm='$esc_afm'";
            $result1 = mysqli_query($mysqlconnection, $sql1);
            
            if (mysqli_num_rows($result1) > 0) {
                $already_in_db++;
                $log_messages[] = "[ΠΑΡΑΚΑΜΨΗ - ΥΠΑΡΧΕΙ ΗΔΗ] ΑΦΜ: $afm, $fullname, Κλάδος: $klados - Υπάρχει ήδη καταχωρημένος/η στη βάση δεδομένων.";
            } else {
                $esc_name = mysqli_real_escape_string($mysqlconnection, $data['name']);
                $esc_surname = mysqli_real_escape_string($mysqlconnection, $data['surname']);
                $esc_patrwnymo = mysqli_real_escape_string($mysqlconnection, $data['patrwnymo']);
                $esc_mhtrwnymo = mysqli_real_escape_string($mysqlconnection, $data['mhtrwnymo']);
                $esc_stathero = mysqli_real_escape_string($mysqlconnection, $data['stathero']);
                $esc_kinhto = mysqli_real_escape_string($mysqlconnection, $data['kinhto']);
                $esc_email = mysqli_real_escape_string($mysqlconnection, $data['email']);
                $klados_id = getKladosFromDescription($data['klados'], $mysqlconnection);

                $sql = "INSERT INTO ektaktoi(
                    hm_apox, name, surname, patrwnymo, mhtrwnymo, 
                    klados, hm_anal, afm, type, stathero, 
                    kinhto, email, praxi, wres, status, ent_ty, sx_yphrethshs
                ) VALUES (
                    '$hm_apox',
                    '$esc_name',
                    '$esc_surname',
                    '$esc_patrwnymo',
                    '$esc_mhtrwnymo',
                    '$klados_id',
                    $hm_anal_sql,
                    '$esc_afm',
                    '{$data['xrhmatodothsh']}',
                    '$esc_stathero',
                    '$esc_kinhto',
                    '$esc_email',
                    '1',
                    '{$data['hours']}',
                    1,
                    0,
                    ".getSchoolID('Διάθεση ΠΥΣΠΕ', $mysqlconnection)."
                )";

                if (mysqli_query($mysqlconnection, $sql)) {
                    $count++;
                    $funding_label = ($data['xrhmatodothsh'] == 3) ? 'ΕΣΠΑ' : 'Τακτικός';
                    if ($has_analipsi) {
                        $inserted_with_analipsi++;
                        $log_messages[] = "[ΠΡΟΣΘΗΚΗ] Προσθήκη εκπ/κού: $afm, {$data['surname']} {$data['name']}, {$data['klados']} (Ώρες: {$data['hours']}, Τύπος: $funding_label, Ημ. Ανάληψης: {$analipseis[$afm]})";
                    } else {
                        $inserted_without_analipsi++;
                        $log_messages[] = "[ΠΡΟΣΘΗΚΗ - ΧΩΡΙΣ ΑΝΑΛΗΨΗ] Προσθήκη εκπ/κού: $afm, {$data['surname']} {$data['name']}, {$data['klados']} (Ώρες: {$data['hours']}, Τύπος: $funding_label, Ημ. Ανάληψης: - [Κενή])";
                    }
                } else {
                    $errors++;
                    $db_err = mysqli_error($mysqlconnection);
                    $log_messages[] = "[ΣΦΑΛΜΑ] Αποτυχία προσθήκης εκπ/κού: ΑΦΜ $afm, $fullname, Κλάδος: $klados - Σφάλμα βάσης: $db_err";
                }
            }
        }
        
        // Assemble complete log
        $log_header = array();
        $log_header[] = "================================================================================";
        $log_header[] = "ΑΡΧΕΙΟ ΚΑΤΑΓΡΑΦΗΣ ΕΙΣΑΓΩΓΗΣ ΑΝΑΠΛΗΡΩΤΩΝ (ΥΠΑΙΘΑ / MINEDU)";
        $log_header[] = "Ημερομηνία/Ώρα:                     " . date('Y-m-d H:i:s');
        $log_header[] = "Αρχείο προσλήψεων:                  " . (isset($_FILES['proslipsi']['name']) ? $_FILES['proslipsi']['name'] : '-');
        $log_header[] = "Αρχείο αναλήψεων:                   " . (isset($_FILES['analipsi']['name']) ? $_FILES['analipsi']['name'] : '-');
        $log_header[] = "Περιοχή πρόσληψης (παράμετρος):     " . $perioxh;
        $log_header[] = "Σύνολο γραμμών αρχείου προσλήψεων:  " . max(0, $highestRow - 1);
        $log_header[] = "Εγγραφές που αντιστοιχούν περιοχή:  " . count($new_anaplirotes);
        $log_header[] = "Σύνολο εγγραφών αρχείου αναλήψεων:  " . count($analipseis);
        $log_header[] = "================================================================================";
        $log_header[] = "";

        $log_footer = array();
        $log_footer[] = "";
        $log_footer[] = "================================================================================";
        $log_footer[] = "ΣΥΝΟΨΗ ΑΠΟΤΕΛΕΣΜΑΤΩΝ";
        $log_footer[] = "================================================================================";
        $log_footer[] = "Επιτυχείς νέες καταχωρήσεις:        $count";
        $log_footer[] = "  - Με ημερομηνία ανάληψης:         $inserted_with_analipsi";
        $log_footer[] = "  - Χωρίς ημερομηνία ανάληψης:      $inserted_without_analipsi";
        $log_footer[] = "Υπήρχαν ήδη στη βάση δεδομένων:     $already_in_db";
        $log_footer[] = "Σφάλματα εισαγωγής:                 $errors";
        $log_footer[] = "Συνολικές εγγραφές περιοχής:        " . count($new_anaplirotes);
        $log_footer[] = "================================================================================";
        $log_footer[] = "Ολοκλήρωση: " . date('Y-m-d H:i:s');

        $full_log = implode("\r\n", array_merge($log_header, $log_messages, $log_footer));

        echo "<div class='import-container'>";
        echo "<div class='import-header'>";
        echo "<h2>📊 Αποτέλεσμα Εισαγωγής Αναπληρωτών</h2>";
        echo "<p>Ολοκληρώθηκε η επεξεργασία των αρχείων Excel του Υπουργείου Παιδείας.</p>";
        echo "</div>";

        if (!$count) {
            echo "<div style='background: #fef3c7; border-left: 4px solid #f59e0b; padding: 14px 18px; border-radius: 8px; margin-bottom: 18px; color: #92400e;'>";
            echo "<strong style='font-size: 15px;'>Δεν έγινε εισαγωγή νέων εγγραφών.</strong>";
            if ($already_in_db) echo "<br><i>$already_in_db εγγραφές υπάρχουν ήδη καταχωρημένες στη βάση δεδομένων.</i>";
            echo "</div>";
        } else {
            echo "<div style='background: #f0fdf4; border-left: 4px solid #16a34a; padding: 14px 18px; border-radius: 8px; margin-bottom: 18px; color: #166534;'>";
            echo "<strong style='font-size: 16px;'>✓ Επιτυχής καταχώρηση $count νέων εγγραφών!</strong>";
            if ($inserted_without_analipsi > 0) echo "<br><i>($inserted_without_analipsi εγγραφές καταχωρήθηκαν με κενή ημερομηνία ανάληψης)</i>";
            if ($already_in_db) echo "<br><i>$already_in_db εγγραφές υπήρχαν ήδη καταχωρημένες και παρακάμφθηκαν.</i>";
            if ($errors > 0) echo "<br><span style='color: #dc2626;'><i>$errors σφάλματα κατά την εισαγωγή.</i></span>";
            echo "</div>";
        }
?>
        <div style="margin: 20px 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                <label for="import_log" style="font-weight: bold; font-size: 14px; color: #374151;">
                    Αναλυτικό αρχείο καταγραφής (Log):
                </label>
                <div>
                    <button type="button" id="btn-copy-log" onclick="copyLog()" class="btn" style="padding: 6px 14px; margin-right: 8px; cursor: pointer; background-color: #f3f4f6; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;">
                        📋 Αντιγραφή
                    </button>
                    <button type="button" id="btn-download-log" onclick="downloadLog()" class="btn" style="padding: 6px 14px; cursor: pointer; background-color: #0284c7; color: #ffffff; border: 1px solid #0369a1; border-radius: 6px; font-size: 13px; font-weight: 500;">
                        💾 Λήψη Log (.txt)
                    </button>
                </div>
            </div>
            <textarea id="import_log" name="import_log" rows="18" readonly style="width: 100%; box-sizing: border-box; font-family: Consolas, 'Courier New', monospace; font-size: 12.5px; line-height: 1.5; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; background-color: #f8fafc; color: #1e293b; white-space: pre;"><?php echo htmlspecialchars($full_log, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <script type="text/javascript">
        function downloadLog() {
            var logEl = document.getElementById('import_log');
            if (!logEl) return;
            var text = logEl.value;
            var blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            var now = new Date();
            var pad = function(n) { return (n < 10 ? '0' : '') + n; };
            var timestamp = now.getFullYear() + '' + pad(now.getMonth() + 1) + '' + pad(now.getDate()) + '_' + 
                            pad(now.getHours()) + '' + pad(now.getMinutes()) + '' + pad(now.getSeconds());
            a.href = url;
            a.download = 'import_minedu_log_' + timestamp + '.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(function() { URL.revokeObjectURL(url); }, 1000);
        }

        function copyLog() {
            var logEl = document.getElementById('import_log');
            if (!logEl) return;
            logEl.select();
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(logEl.value).then(function() {
                    updateCopyBtn();
                }).catch(function() {
                    fallbackCopy();
                });
            } else {
                fallbackCopy();
            }
        }

        function fallbackCopy() {
            var logEl = document.getElementById('import_log');
            logEl.select();
            document.execCommand('copy');
            updateCopyBtn();
        }

        function updateCopyBtn() {
            var btn = document.getElementById('btn-copy-log');
            if (btn) {
                var orig = btn.innerText;
                btn.innerText = '✓ Αντιγράφηκε!';
                setTimeout(function() { btn.innerText = orig; }, 2000);
            }
        }
        </script>

        <div class="actions-row">
            <a href="ektaktoi_import_minedu.php" class="btn-submit-import" style="text-decoration: none;">
                🔄 Εισαγωγή περισσότερων
            </a>
            <a href="../employee/ektaktoi_list.php" class="btn-back">
                ← Επιστροφή
            </a>
        </div>
    </div>
<?php
    } else {
        echo "<div class='import-container'>";
        echo "<div class='alert-box' style='background: #fef2f2; border-color: #ef4444; color: #991b1b;'>";
        echo "<div><strong>Σφάλμα:</strong> Πρέπει να ανεβάσετε και τα 2 αρχεία (Προσλήψεων και Αναλήψεων).</div>";
        echo "</div>";
        echo "<div class='actions-row'>";
        echo "<a href='ektaktoi_import_minedu.php' class='btn-submit-import' style='text-decoration: none;'>Δοκιμάστε ξανά</a>";
        echo "<a href='../employee/ektaktoi_list.php' class='btn-back'>Επιστροφή</a>";
        echo "</div>";
        echo "</div>";
    }
} else {
    // Prepare database connection to fetch active parameters for display
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
    $current_perioxh = getParam('perioxh', $mysqlconnection);
    $current_endofyear = getParam('endofyear2', $mysqlconnection);
?>
    <div class="import-container">
        <div class="import-header">
            <h2>📥 Εισαγωγή Αναπληρωτών Εκπαιδευτικών από Excel</h2>
            <p>Μαζική εισαγωγή και αυτόματη καταχώρηση στοιχείων εκπαιδευτικών από τα αρχεία του Υπουργείου (MINEDU / ΟΠΣΥΔ).</p>
            
            <?php if (!empty($current_perioxh)): ?>
            <div class="info-badge-panel">
                <span class="info-badge" title="Φιλτράρισμα βάσει της παραμέτρου περιοχής">
                    📍 Περιοχή Πρόσληψης: <strong><?php echo htmlspecialchars($current_perioxh); ?></strong> (και σχετικές υποκατηγορίες)
                </span>
                <?php if (!empty($current_endofyear)): ?>
                <span class="info-badge" style="background: #f0fdf4; color: #166534; border-color: #bbf7d0;">
                    📅 Ημ/νία Αποχώρησης: <strong><?php echo date('d/m/Y', strtotime($current_endofyear)); ?></strong>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="guide-grid">
            <div class="guide-card">
                <h4>📄 1. Αρχείο Προσλήψεων</h4>
                <ul>
                    <li>Απαραίτητη στήλη: <strong>Α/Α ΡΟΗΣ</strong></li>
                    <li>Στήλες στοιχείων: <strong>ΑΦΜ, ΕΠΩΝΥΜΟ, ΟΝΟΜΑ, ΚΛΑΔΟΣ</strong></li>
                    <li>Στήλη: <strong>ΠΕΡΙΟΧΗ ΠΡΟΣΛΗΨΗΣ</strong></li>
                    <li>Στήλες: <strong>ΩΡΑΡΙΟ / ΤΥΠΟΣ ΚΕΝΟΥ</strong> (πλήρες ή μειωμένο)</li>
                </ul>
            </div>
            <div class="guide-card">
                <h4>📄 2. Αρχείο Αναλήψεων</h4>
                <ul>
                    <li>Απαραίτητη στήλη: <strong>ΗΜ. ΑΝΑΛΗΨΗΣ</strong></li>
                    <li>Στήλη ταυτοποίησης: <strong>ΑΦΜ</strong></li>
                    <li>Καθορίζει την ημερομηνία ανάληψης υπηρεσίας</li>
                    <li>Εγγραφές χωρίς ανάληψη εισάγονται κανονικά (με κενή ημ/νία ανάληψης)</li>
                </ul>
            </div>
        </div>

        <form enctype='multipart/form-data' action='' method='post' id='importForm'>
            <input type='hidden' name='submit' value='1'>
            <div class="upload-box">
                <div class="upload-box-title">
                    <span>1. Αρχείο Προσλήψεων (Excel)</span>
                    <span style="font-size: 11px; background: #0284c7; color: #ffffff; padding: 3px 10px; border-radius: 12px; font-weight: 600;">Υποχρεωτικό</span>
                </div>
                <div class="upload-box-desc">Επιλέξτε το αρχείο excel αποφάσεων προσλήψεων με στήλες «Α/Α ΡΟΗΣ» και «ΠΕΡΙΟΧΗ ΠΡΟΣΛΗΨΗΣ»:</div>
                <input type='file' name='proslipsi' id='file_proslipsi' accept='.xlsx, .xls' required onchange="updateFileStatus(this, 'status_proslipsi')">
                <div id="status_proslipsi" class="upload-file-status">Δεν έχει επιλεγεί αρχείο (.xlsx, .xls)</div>
            </div>

            <div class="upload-box">
                <div class="upload-box-title">
                    <span>2. Αρχείο Αναλήψεων Υπηρεσίας (Excel)</span>
                    <span style="font-size: 11px; background: #0284c7; color: #ffffff; padding: 3px 10px; border-radius: 12px; font-weight: 600;">Υποχρεωτικό</span>
                </div>
                <div class="upload-box-desc">Επιλέξτε το αρχείο excel με τις αναλήψεις υπηρεσίας και τη στήλη «ΗΜ. ΑΝΑΛΗΨΗΣ»:</div>
                <input type='file' name='analipsi' id='file_analipsi' accept='.xlsx, .xls' required onchange="updateFileStatus(this, 'status_analipsi')">
                <div id="status_analipsi" class="upload-file-status">Δεν έχει επιλεγεί αρχείο (.xlsx, .xls)</div>
            </div>

            <div class="alert-box">
                <span style="font-size: 20px; line-height: 1;">ℹ️</span>
                <div>
                    <strong>Σημείωση:</strong> Η διαδικασία εισαγωγής ενδέχεται να διαρκέσει μερικά λεπτά αναλόγως του μεγέθους των αρχείων.<br>
                    Μην κλείσετε ή ανανεώσετε τη σελίδα μέχρι να ολοκληρωθεί η επεξεργασία και να προβληθεί το αναλυτικό log.
                </div>
            </div>

            <div id="formLoadingAlert" style="display: none; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 14px 18px; border-radius: 8px; margin: 15px 0; align-items: center; gap: 12px;">
                <span style="font-size: 20px;">⏳</span>
                <div><strong>Γίνεται μεταφόρτωση και επεξεργασία...</strong> Παρακαλούμε περιμένετε.</div>
            </div>

            <div class="actions-row">
                <button type='submit' name='submit' id='submitBtn' class='btn-submit-import'>
                    <span>🚀 Μεταφόρτωση &amp; Έναρξη Εισαγωγής</span>
                </button>
                <a href="../employee/ektaktoi_list.php" class="btn-back">
                    ← Επιστροφή
                </a>
                <a href='import.php' class='link-other-import'>
                    🔄 Εισαγωγή μονίμων και σχολείων
                </a>
            </div>
        </form>
    </div>

    <script type="text/javascript">
    function updateFileStatus(input, statusId) {
        var el = document.getElementById(statusId);
        if (!el) return;
        if (input.files && input.files[0]) {
            var file = input.files[0];
            var sizeKB = (file.size / 1024).toFixed(1);
            el.innerHTML = '<span style="color: #16a34a; font-weight: 600;">✓ ' + escapeHtml(file.name) + '</span> <span style="color: #64748b; font-size: 12px;">(' + sizeKB + ' KB)</span>';
        } else {
            el.innerHTML = '<span style="color: #94a3b8;">Δεν έχει επιλεγεί αρχείο (.xlsx, .xls)</span>';
        }
    }
    function escapeHtml(text) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    var formSubmitted = false;
    document.getElementById('importForm').addEventListener('submit', function(e) {
        if (formSubmitted) {
            e.preventDefault();
            return false;
        }
        formSubmitted = true;
        var btn = document.getElementById('submitBtn');
        btn.innerHTML = '⏳ Γίνεται επεξεργασία...';
        btn.style.opacity = '0.75';
        btn.style.cursor = 'wait';
        btn.style.pointerEvents = 'none';
        var loading = document.getElementById('formLoadingAlert');
        if (loading) loading.style.display = 'flex';
    });
    </script>
<?php
}
?>
</body>
</html>
