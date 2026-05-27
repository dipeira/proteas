<?php
header('Content-type: text/html; charset=utf-8');
require_once "../config.php";
require_once "../include/functions.php";
require_once "../tools/class.login.php";

session_start();

$log = new logmein();
if ($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
    exit;
}

$usrlvl = $_SESSION['userlevel'];
if ($usrlvl > 1) {
    die("<h3>Σφάλμα: Δεν επιτρέπεται η πρόσβαση...</h3>");
}

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

$sxol_etos = getParam('sxol_etos', $mysqlconnection);
$sxol_etos_display = substr($sxol_etos, 0, 4) . '-' . substr($sxol_etos, 4, 2);

// Handle POST Request for document generation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    require_once '../vendor/phpoffice/phpword/Classes/PHPWord.php';

    set_time_limit(600);

    $debug_flag = isset($_POST['debug_flag']) && $_POST['debug_flag'] == 1 ? 1 : 0;

    $endofyear = getParam('endofyear', $mysqlconnection);
    $endofyear2 = getParam('endofyear2', $mysqlconnection);
    $protapol = getParam('protapol', $mysqlconnection);

    // Create output folder if not exists
    if (!file_exists('../word/anapl')) {
        mkdir('../word/anapl', 0777, true);
    }

    // Query teachers of interest (Kratikoy)
    $query = "SELECT e.*, p.name as praksi, p.ya, p.ada, p.apofasi, p.type as ptype 
              FROM ektaktoi e 
              JOIN praxi p ON e.praxi = p.id 
              WHERE e.type IN (1,2) AND p.type = 'ΚΡΑΤ'";

    if ($debug_flag) {
        $query .= " LIMIT 5";
    }

    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);

    if ($num == 0) {
        $error_msg = "Δε βρέθηκαν εγγραφές αναπληρωτών κρατικού προϋπολογισμού!";
    } else {
        $filenames = [];
        $PHPWord = new PHPWord();

        $template_src = '../word/tmpl_anapl/tmpl_vev_anapl_2026.docx';

        while ($teacher = mysqli_fetch_assoc($result)) {
            $id = $teacher['id'];
            $afm = trim($teacher['afm']);
            $surname = trim($teacher['surname']);
            $name = trim($teacher['name']);
            $patrwnymo = trim($teacher['patrwnymo']);
            $mhtrwnymo = trim($teacher['mhtrwnymo']);
            $klados = $teacher['klados'];
            $hmpros = $teacher['hm_anal'];
            $hmapox = $teacher['hm_apox'];
            $ya = $teacher['ya'];
            $ada = $teacher['ada'];

            // Get placements from yphrethsh_ext table
            $afm_esc = mysqli_real_escape_string($mysqlconnection, $afm);
            $yphr_query = "SELECT y.*, s.name as school_name 
                           FROM yphrethsh_ext y 
                           LEFT JOIN school s ON y.sch_id = s.id 
                           WHERE y.afm = '$afm_esc' AND y.sxol_etos = '$sxol_etos' 
                           ORDER BY y.date_from ASC";
            $yphr_res = mysqli_query($mysqlconnection, $yphr_query);

            $placements = [];
            $placements_raw = [];
            $hour_sum = 0;
            while ($row_yphr = mysqli_fetch_assoc($yphr_res)) {
                $sch_name = $row_yphr['school_name'] ?: ($row_yphr['sch_name'] ?: 'Άγνωστο');
                $date_from_f = ($row_yphr['date_from'] && $row_yphr['date_from'] != '0000-00-00') ? date('d/m/Y', strtotime($row_yphr['date_from'])) : '-';
                $date_to_f = ($row_yphr['date_to'] && $row_yphr['date_to'] != '0000-00-00') ? date('d/m/Y', strtotime($row_yphr['date_to'])) : '-';
                $hours_val = intval($row_yphr['hours']);

                $hour_sum += $hours_val;

                $placements[] = [
                    'sch_name' => $sch_name,
                    'date_from' => $date_from_f,
                    'date_to' => $date_to_f,
                    'hours' => $hours_val
                ];
                $placements_raw[] = [
                    'date_from' => $row_yphr['date_from'],
                    'date_to' => $row_yphr['date_to'],
                    'hours' => $hours_val
                ];
            }

            // Get specialisation description
            $qry_kl = "SELECT perigrafh, onoma FROM klados WHERE id = $klados";
            $res_kl = mysqli_query($mysqlconnection, $qry_kl);
            $kl1 = '';
            $kl2 = '';
            if ($res_kl && mysqli_num_rows($res_kl) > 0) {
                $row_kl = mysqli_fetch_assoc($res_kl);
                $kl1 = $row_kl['perigrafh'];
                $kl2 = $row_kl['onoma'];
            }
            $klados_full = $kl2 . " (" . $kl1 . ")";

            // Get employment type description
            $type_id = $teacher['type'];
            $qry_type = "SELECT type FROM ektaktoi_types WHERE id = $type_id";
            $res_type = mysqli_query($mysqlconnection, $qry_type);
            $type_desc = '';
            if ($res_type && mysqli_num_rows($res_type) > 0) {
                $type_desc = mysqli_result($res_type, 0, "type");
            }

            // Format dates
            $hmpros_f = ($hmpros && $hmpros != '0000-00-00') ? date("d-m-Y", strtotime($hmpros)) : '-';
            $hmapox_f = ($hmapox && $hmapox != '0000-00-00') ? date("d-m-Y", strtotime($hmapox)) : '-';

            // Format wrario
            $ypoxr = get_ypoxrewtiko_wrario($id, $mysqlconnection);
            $meiwmeno = ($teacher['type'] == 1);

            // Calculate actual weekly hours (handling sequential vs concurrent placements)
            $actual_weekly_hours = 0;
            if (!empty($placements_raw)) {
                $dates = [];
                foreach ($placements_raw as $p) {
                    if ($p['date_from'] && $p['date_from'] != '0000-00-00') {
                        $dates[] = $p['date_from'];
                    }
                    if ($p['date_to'] && $p['date_to'] != '0000-00-00') {
                        $dates[] = $p['date_to'];
                    }
                }
                $dates = array_unique($dates);

                if (empty($dates)) {
                    $actual_weekly_hours = $hour_sum;
                } else {
                    $max_h = 0;
                    foreach ($dates as $d) {
                        $t_d = strtotime($d);
                        $curr_h = 0;
                        foreach ($placements_raw as $p) {
                            $t_from = strtotime($p['date_from']);
                            $t_to = strtotime($p['date_to']);
                            if ($t_d >= $t_from && $t_d <= $t_to) {
                                $curr_h += $p['hours'];
                            }
                        }
                        if ($curr_h > $max_h) {
                            $max_h = $curr_h;
                        }
                    }
                    $actual_weekly_hours = $max_h;
                }
            }
            if ($actual_weekly_hours > $ypoxr) {
                $actual_weekly_hours = $ypoxr;
            }
            if ($actual_weekly_hours == 0) {
                $actual_weekly_hours = $ypoxr;
            }

            $wrario_text = $meiwmeno ?
                "μειωμένο ωράριο $actual_weekly_hours ώρες/εβδομάδα (πλήρες υποχρ.ωράριο $ypoxr ώρες/εβδ.)" :
                "πλήρες ωράριο ($ypoxr ώρες/εβδομάδα)";

            // Get (and subtract) Adeies
            $adeies = get_adeies($id, $mysqlconnection);

            // Calculate service duration
            $apol = substr($hmapox, 8, 2) + substr($hmapox, 5, 2) * 30 + substr($hmapox, 0, 4) * 360;

            // hm/nia ya or apofasi perif/khs
            $tempya = strlen($teacher['ya']) > 0 ? $teacher['ya'] : $teacher['apofasi'];
            $temp = explode('/', $tempya);
            $temp = explode('-', $temp[2]);
            $hm_ya = intval($temp[0]) + intval($temp[1]) * 30 + intval($temp[2]) * 360;

            // hm proslhpshs
            $pros = substr($hmpros, 0, 4) * 360 + substr($hmpros, 5, 2) * 30 + substr($hmpros, 8, 2);

            // days: misthologikh - days_ya: ekpaideytikh
            $days = $apol - $pros + 1;
            $days_ya = $apol - $hm_ya + 1;

            // subtract subtracted
            $days_ya -= $adeies['subtracted'];

            if ($meiwmeno) {
                $yp_wr = getParam('yp_wr', $mysqlconnection);
                $days = compute_meiwmeno($days, $actual_weekly_hours, $yp_wr);
                $days_ya = compute_meiwmeno($days_ya, $actual_weekly_hours, $yp_wr);
            }
            $ymd = days2ymd($days);
            $ymd_ya = days2ymd($days_ya);

            $y_anal = $ymd[0];
            $m_anal = $ymd[1];
            $d_anal = $ymd[2];
            // Set everything under 'ΑΠΟ ΗΜ/ΝΙΑ ΑΠΟΦΑΣΗΣ ΠΡΟΣΛΗΨΗΣ' to zero
            $y_pros = 0;
            $m_pros = 0;
            $d_pros = 0;

            // Build temporary path
            $fname = greek_to_greeklish($surname);
            $temp_path = '../word/anapl/temp_' . $fname . '_' . substr($afm, -3) . '.docx';
            copy($template_src, $temp_path);

            $zip = new ZipArchive();
            if ($zip->open($temp_path) === TRUE) {
                $xml = $zip->getFromName('word/document.xml');

                // Clean up split endofyear2 placeholder in XML
                $xml = preg_replace('/\$\s*<\/w:t>.*?\{\s*<\/w:t>.*?endofyear\s*<\/w:t>.*?2\s*<\/w:t>.*?\}\s*<\/w:t>/s', '${endofyear2}</w:t>', $xml);

                // Replace "24/24" fraction with dynamic "$actual_weekly_hours/ypoxr"
                $fraction = $actual_weekly_hours . '/' . $ypoxr;
                $xml = str_replace('24/24', $fraction, $xml);

                // Reconstruct Table 1 (placements table) with bottom border on all cells
                if (preg_match('/<w:tbl\b[^>]*>(?:(?!<\/w:tbl>).)*?Σχολική Μονάδα\/Υπηρεσία.*?<\/w:tbl>/s', $xml, $tbl_matches)) {
                    $table1 = $tbl_matches[0];
                    $tr_end_pos = strpos($table1, '</w:tr>');
                    if ($tr_end_pos !== false) {
                        $header_part = substr($table1, 0, $tr_end_pos + 7);

                        $dynamic_rows = '';
                        foreach ($placements as $p) {
                            $sch_name_esc = htmlspecialchars($p['sch_name'], ENT_XML1, 'UTF-8');
                            $date_from_esc = htmlspecialchars($p['date_from'], ENT_XML1, 'UTF-8');
                            $date_to_esc = htmlspecialchars($p['date_to'], ENT_XML1, 'UTF-8');
                            $hours_esc = htmlspecialchars($p['hours'], ENT_XML1, 'UTF-8');

                            $dynamic_rows .= '<w:tr w:rsidR="004221D9">
  <w:trPr><w:trHeight w:hRule="exact" w:val="334"/><w:jc w:val="center"/></w:trPr>
  <w:tc>
    <w:tcPr><w:tcW w:w="3976" w:type="dxa"/><w:tcBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/></w:tcBorders><w:vAlign w:val="center"/></w:tcPr>
    <w:p><w:pPr><w:pStyle w:val="a7"/><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr>
      <w:r><w:rPr><w:rFonts w:eastAsia="Tahoma"/></w:rPr><w:t>' . $sch_name_esc . '</w:t></w:r>
    </w:p>
  </w:tc>
  <w:tc>
    <w:tcPr><w:tcW w:w="2267" w:type="dxa"/><w:tcBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/></w:tcBorders><w:vAlign w:val="center"/></w:tcPr>
    <w:p><w:pPr><w:pStyle w:val="a7"/><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/></w:pPr>
      <w:r><w:rPr><w:rFonts w:eastAsia="Tahoma"/></w:rPr><w:t>' . $date_from_esc . '</w:t></w:r>
    </w:p>
  </w:tc>
  <w:tc>
    <w:tcPr><w:tcW w:w="2267" w:type="dxa"/><w:tcBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/></w:tcBorders><w:vAlign w:val="center"/></w:tcPr>
    <w:p><w:pPr><w:pStyle w:val="a7"/><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/></w:pPr>
      <w:r><w:rPr><w:rFonts w:eastAsia="Tahoma"/></w:rPr><w:t>' . $date_to_esc . '</w:t></w:r>
    </w:p>
  </w:tc>
  <w:tc>
    <w:tcPr><w:tcW w:w="2274" w:type="dxa"/><w:tcBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/></w:tcBorders><w:vAlign w:val="center"/></w:tcPr>
    <w:p><w:pPr><w:pStyle w:val="a7"/><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/></w:pPr>
      <w:r><w:rPr><w:rFonts w:eastAsia="Tahoma"/></w:rPr><w:t>' . $hours_esc . '</w:t></w:r>
    </w:p>
  </w:tc>
</w:tr>';
                        }

                        $new_table1 = $header_part . $dynamic_rows . '</w:tbl>';
                        $xml = str_replace($table1, $new_table1, $xml);
                    }
                }

                // Reconstruct Table 2 (duration summary Row #3)
                if (preg_match('/<w:tbl\b[^>]*>(?:(?!<\/w:tbl>).)*?ΣΥΝΟΛΙΚΟΣ ΧΡΟΝΟΣ ΠΡΟΫΠΗΡΕΣΙΑΣ.*?<\/w:tbl>/s', $xml, $tbl_matches)) {
                    $table2 = $tbl_matches[0];
                    preg_match_all('/<w:tr\b[^>]*>.*?<\/w:tr>/s', $table2, $tr_matches);
                    if (count($tr_matches[0]) >= 4) {
                        $row3 = $tr_matches[0][3];

                        $placeholders = [$y_anal, $m_anal, $d_anal, $y_pros, $m_pros, $d_pros];
                        $new_row3 = $row3;
                        $new_row3 = preg_replace_callback('/<w:t>([^<]+)<\/w:t>/', function ($m) use (&$placeholders) {
                            return '<w:t>' . array_shift($placeholders) . '</w:t>';
                        }, $new_row3);

                        $xml = str_replace($row3, $new_row3, $xml);
                    }
                }

                $zip->addFromString('word/document.xml', $xml);
                $zip->close();
            }

            // Now load in PHPWord as template to replace single placeholders
            $document = $PHPWord->loadTemplate($temp_path);

            $document->setValue('surname', $surname);
            $document->setValue('name', $name);
            $document->setValue('patrwnymo', $patrwnymo);
            $document->setValue('mhtrwnymo', $mhtrwnymo);
            $document->setValue('afm', $afm);
            $document->setValue('kladosfull', $klados_full);
            $document->setValue('type', $type_desc);
            $document->setValue('hmpros', $hmpros_f);
            $document->setValue('endofyear2', $hmapox_f);
            $document->setValue('wrario', $wrario_text);
            $document->setValue('ya', $ya);
            $document->setValue('ada', str_replace(array('(', ')'), "", $ada));
            $document->setValue('endofyear', $endofyear);
            $document->setValue('protapol', $protapol);

            $output_file = '../word/anapl/vev_' . $fname . '_' . $afm . '.docx';
            $document->save($output_file);
            $filenames[] = $output_file;

            // Remove temp file
            unlink($temp_path);
        }

        // Create Zip Archive
        $zipname = '../word/anapl/vev_kratikou.zip';
        if (file_exists($zipname)) {
            unlink($zipname);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipname, ZipArchive::CREATE) === TRUE) {
            foreach ($filenames as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        // Delete individual docx files
        foreach ($filenames as $file) {
            unlink($file);
        }

        $success_msg = "Η εξαγωγή ολοκληρώθηκε επιτυχώς! <br>Εκδόθηκαν <strong>" . count($filenames) . "</strong> βεβαιώσεις.";
    }
}

// Query eligible employees for list view
$query = "SELECT e.*, p.name as praksi 
          FROM ektaktoi e 
          JOIN praxi p ON e.praxi = p.id 
          WHERE e.type IN (1,2) AND p.type = 'ΚΡΑΤ'
          ORDER BY e.surname ASC, e.name ASC";
$result = mysqli_query($mysqlconnection, $query);
$num_teachers = mysqli_num_rows($result);

// Query total placements for these teachers in the current school year
$total_placements = 0;
if ($num_teachers > 0) {
    $yphr_count_query = "SELECT COUNT(*) as total_placements 
                         FROM yphrethsh_ext y
                         WHERE y.sxol_etos = '$sxol_etos' 
                         AND TRIM(y.afm) IN (
                             SELECT TRIM(e.afm) 
                             FROM ektaktoi e 
                             JOIN praxi p ON e.praxi = p.id 
                             WHERE e.type IN (1,2) AND p.type = 'ΚΡΑΤ'
                         )";
    $yphr_count_res = mysqli_query($mysqlconnection, $yphr_count_query);
    if ($yphr_count_res) {
        $yphr_count_row = mysqli_fetch_assoc($yphr_count_res);
        $total_placements = $yphr_count_row['total_placements'];
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <?php
    $root_path = '../';
    $page_title = 'Βεβαιώσεις Κρατικού Προϋπολογισμού';
    require '../etc/head.php';
    ?>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <style>
        .krat-container {
            font-family: 'Outfit', 'Inter', system-ui, sans-serif;
            max-width: 1100px;
            margin: 0 auto;
        }

        .krat-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            border-top: 4px solid #3b82f6;
            padding: 24px;
            margin-bottom: 24px;
        }

        .krat-title {
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .krat-subtitle {
            color: #64748b;
            font-size: 0.95rem;
            margin: 0 0 20px 0;
        }

        .info-badge {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }

        .btn-krat {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 10px 24px;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.15);
        }

        .btn-krat:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(59, 130, 246, 0.25);
        }

        .btn-krat:disabled {
            background: #cbd5e1;
            color: #94a3b8;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-back {
            background: #ef4444;
            color: white;
            padding: 10px 24px;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-back:hover {
            background: #dc2626;
        }

        .krat-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .krat-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
        }

        .krat-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        .teacher-row:hover {
            background-color: #f8fafc;
        }

        .teacher-name {
            font-weight: 600;
            color: #0f172a;
        }

        .teacher-meta {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 4px;
        }

        .placement-item {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: #334155;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 12px;
            margin-bottom: 6px;
        }

        .placement-item:last-child {
            margin-bottom: 0;
        }

        .placement-school {
            font-weight: 600;
            color: #1e293b;
        }

        .placement-details {
            margin-left: auto;
            font-size: 0.8rem;
            color: #64748b;
        }

        .badge-count {
            background: #3b82f6;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .alert-krat {
            border-left: 4px solid #ef4444;
            background: #fef2f2;
            color: #991b1b;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .success-krat {
            border-left: 4px solid #10b981;
            background: #f0fdf4;
            color: #14532d;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 24px;
        }

        .debug-banner {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #78350f;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            margin-bottom: 15px;
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            border: 1px dashed #cbd5e1;
        }

        .form-checkbox-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .form-checkbox-container label {
            font-size: 0.9rem;
            color: #475569;
            cursor: pointer;
            font-weight: 500;
        }

        .no-placements {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            color: #b45309;
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-block;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen text-slate-800">
    <?php include('../etc/menu.php'); ?>
    <div class="krat-container px-4 py-8">

        <?php if (isset($error_msg)): ?>
            <div class="alert-krat">
                <strong>⚠️ Σφάλμα:</strong> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success_msg)): ?>
            <div class="success-krat">
                <h3 class="text-lg font-bold mb-2">🎉 Επιτυχής Έκδοση!</h3>
                <p class="mb-4"><?php echo $success_msg; ?></p>
                <div class="flex gap-4">
                    <a href="<?php echo $zipname; ?>" class="btn-krat" style="text-decoration: none;">📥 Λήψη αρχείου
                        ZIP</a>
                    <a href="../etc/end_of_year.php" class="btn-back" style="background:#475569;">Επιστροφή</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="krat-card">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="krat-title">Έκδοση Βεβαιώσεων Κρατικού</h2>
                    <p class="krat-subtitle">Προετοιμασία και μαζική παραγωγή εγγράφων Word (.docx) βάσει του προτύπου
                        <strong>tmpl_vev_anapl_2026.docx</strong></p>
                </div>
                <span class="info-badge">Σχ. Έτος: <?php echo htmlspecialchars($sxol_etos_display); ?></span>
            </div>

            <div
                style="background:#f8fafc; border-radius:8px; padding:16px; margin-bottom:20px; font-size:0.9rem; line-height:1.6; border: 1px solid #e2e8f0;">
                <strong>ℹ️ Πληροφορίες Διαδικασίας:</strong><br>
                • Η έκδοση αφορά αναπληρωτές (πλήρους & μειωμένου ωραρίου) κρατικού προϋπολογισμού.<br>
                • Τα σχολεία και οι ώρες υπηρετήσεων αντλούνται απευθείας από τον πίνακα <strong>yphrethsh_ext</strong>
                (MySchool).<br>
                • Οι άδειες και οι ημέρες απουσίας υπολογίζονται αυτόματα για κάθε εκπαιδευτικό.<br>
                • Μετά την ολοκλήρωση, θα παραχθεί ένα συμπιεσμένο αρχείο <strong>vev_kratikou.zip</strong> για λήψη.
            </div>

            <div class="flex gap-2">
                <div class="badge-count">Βρέθηκαν: <?php echo $num_teachers; ?> εκπαιδευτικοί με συνολικά <?php echo $total_placements; ?> υπηρετήσεις</div>
            </div>

            <form action="" method="POST">
                <input type="hidden" name="action" value="create">

                <div class="form-checkbox-container">
                    <input type="checkbox" name="debug_flag" value="1" id="debug_flag" />
                    <label for="debug_flag"><strong>Δοκιμαστική λειτουργία (Debug Mode)</strong> - Δημιουργία μόνο των 5
                        πρώτων εγγράφων για δοκιμή</label>
                </div>

                <div class="flex gap-3" style="margin-top:20px;">
                    <button type="submit" class="btn-krat" <?php if ($num_teachers == 0)
                        echo 'disabled'; ?>>⚙️ Δημιουργία
                        Βεβαιώσεων</button>
                    <a href="../etc/end_of_year.php" class="btn-back">← Ακύρωση</a>
                </div>
            </form>
        </div>

        <?php
        $submitted = ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create');
        ?>
        <div class="krat-card" style="padding:0; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid #e2e8f0;">
                <h3 style="margin:0; font-size:1.15rem; font-weight:700; color:#1e293b;">
                    <?php if ($submitted): ?>
                        Λίστα Εκπαιδευτικών &amp; Υπηρετήσεων (MySchool)
                    <?php else: ?>
                        Βρέθηκαν: <?php echo $num_teachers; ?> εκπαιδευτικοί με συνολικά <?php echo $total_placements; ?> υπηρετήσεις
                    <?php endif; ?>
                </h3>
            </div>
            <?php if ($submitted): ?>
                <div style="overflow-x:auto;">
                    <table class="krat-table">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Εκπαιδευτικός</th>
                                <th style="width: 60%;">Τρέχουσες Υπηρετήσεις (yphrethsh_ext)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($num_teachers == 0):
                                ?>
                                <tr>
                                    <td colspan="2" style="text-align:center; padding:30px; color:#64748b;">
                                        Δεν βρέθηκαν εκπαιδευτικοί κρατικού προϋπολογισμού.
                                    </td>
                                </tr>
                            <?php
                            else:
                                while ($row = mysqli_fetch_assoc($result)):
                                    $t_id = $row['id'];
                                    $t_afm = $row['afm'];
                                    $t_surname = $row['surname'];
                                    $t_name = $row['name'];
                                    $t_patr = $row['patrwnymo'];
                                    $t_mhtr = $row['mhtrwnymo'];
                                    $t_klados = $row['klados'];

                                    // Fetch specialisation code
                                    $qry_kl = "SELECT perigrafh FROM klados WHERE id = $t_klados";
                                    $res_kl = mysqli_query($mysqlconnection, $qry_kl);
                                    $kl_name = $res_kl && mysqli_num_rows($res_kl) > 0 ? mysqli_result($res_kl, 0, "perigrafh") : 'Άγνωστος';

                                    // Fetch yphrethsh_ext placements
                                    $t_afm_esc = mysqli_real_escape_string($mysqlconnection, $t_afm);
                                    $t_yphr_query = "SELECT y.*, s.name as school_name 
                                                   FROM yphrethsh_ext y 
                                                   LEFT JOIN school s ON y.sch_id = s.id 
                                                   WHERE y.afm = '$t_afm_esc' AND y.sxol_etos = '$sxol_etos' 
                                                   ORDER BY y.date_from ASC";
                                    $t_yphr_res = mysqli_query($mysqlconnection, $t_yphr_query);
                                    $t_num_placements = mysqli_num_rows($t_yphr_res);
                                    ?>
                                    <tr class="teacher-row">
                                        <td>
                                            <div class="teacher-name"><?php echo htmlspecialchars($t_surname . ' ' . $t_name); ?>
                                            </div>
                                            <div class="teacher-meta">
                                                Πατρώνυμο: <?php echo htmlspecialchars($t_patr); ?> | Μητρώνυμο:
                                                <?php echo htmlspecialchars($t_mhtr); ?><br>
                                                ΑΦΜ: <strong><?php echo htmlspecialchars($t_afm); ?></strong> | Κλάδος:
                                                <strong><?php echo htmlspecialchars($kl_name); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($t_num_placements == 0): ?>
                                                <span class="no-placements">⚠️ Δεν βρέθηκαν υπηρετήσεις στο MySchool</span>
                                            <?php else: ?>
                                                <?php
                                                while ($p = mysqli_fetch_assoc($t_yphr_res)):
                                                    $p_sch = $p['school_name'] ?: ($p['sch_name'] ?: 'Άγνωστο');
                                                    $p_from = ($p['date_from'] && $p['date_from'] != '0000-00-00') ? date('d-m-Y', strtotime($p['date_from'])) : '-';
                                                    $p_to = ($p['date_to'] && $p['date_to'] != '0000-00-00') ? date('d-m-Y', strtotime($p['date_to'])) : '-';
                                                    $p_hrs = $p['hours'];
                                                    ?>
                                                    <div class="placement-item">
                                                        <span class="placement-school">🏫 <?php echo htmlspecialchars($p_sch); ?></span>
                                                        <span class="placement-details">
                                                            📅 <strong><?php echo $p_from; ?></strong> έως
                                                            <strong><?php echo $p_to; ?></strong> | 🕒
                                                            <strong><?php echo $p_hrs; ?></strong> ώρες
                                                        </span>
                                                    </div>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            endif;
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>

</html>
<?php
mysqli_close($mysqlconnection);
?>