<?php
    require_once "../config.php";
    require_once "../include/functions.php";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

    $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ', $mysqlconnection);
    $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ', $mysqlconnection);
    $exclude_pyspe_arr = array_filter([(int)$allo_pyspe, (int)$allo_pysde]);
    $exclude_pyspe_cond = !empty($exclude_pyspe_arr) ? " AND e.sx_yphrethshs NOT IN (" . implode(',', $exclude_pyspe_arr) . ")" : "";

    // Determine consultant AFM (supports ?afm= and ?id=)
    $afm = isset($_GET['afm']) ? trim($_GET['afm']) : '';
    if (empty($afm) && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $res = mysqli_query($mysqlconnection, "SELECT afm FROM symvouloi_epist WHERE id = $id");
        if ($r = mysqli_fetch_assoc($res)) {
            $afm = trim($r['afm']);
        }
    }
    $afm = mysqli_real_escape_string($mysqlconnection, $afm);

    $symv_rows = [];
    $symv_name = '';
    $symv_kladoi = [];
    $symv_conditions = [];
    $all_assigned_sch_ids = [];
    $has_unrestricted_klados = false;

    if (!empty($afm)) {
        $symv_sql = "SELECT se.*, k.perigrafh, k.onoma as klados_name 
                     FROM symvouloi_epist se 
                     LEFT JOIN klados k ON se.klados = k.id 
                     WHERE se.afm = '$afm' 
                       AND (k.perigrafh NOT IN ('ΠΕ60', 'ΠΕ70') OR k.perigrafh IS NULL) 
                       AND se.klados NOT IN ('1', '2', 'ΠΕ60', 'ΠΕ70')";
        $symv_res = mysqli_query($mysqlconnection, $symv_sql);
        while ($row = mysqli_fetch_assoc($symv_res)) {
            $symv_rows[] = $row;
            $symv_name = $row['eponymo'] . ' ' . $row['onoma'];
            if (!empty($row['perigrafh'])) {
                $symv_kladoi[] = $row['perigrafh'];
            }
            $kid = (int)$row['klados'];
            
            $sids = array_filter(array_map('intval', explode(',', $row['sch_ids'] ?? '')));
            if (!empty($sids)) {
                $symv_conditions[] = "(e.klados = $kid AND s.id IN (" . implode(',', $sids) . "))";
                $all_assigned_sch_ids = array_merge($all_assigned_sch_ids, $sids);
            } else {
                $symv_conditions[] = "(e.klados = $kid)";
                $has_unrestricted_klados = true;
            }
        }
        $all_assigned_sch_ids = array_unique($all_assigned_sch_ids);
        $symv_kladoi = array_unique($symv_kladoi);
    }

    $where_symv = !empty($symv_conditions) ? "(" . implode(" OR ", $symv_conditions) . ") AND e.klados NOT IN (1, 2)" : "";

    // Handle Excel export
    if (isset($_GET['export']) && $_GET['export'] == 'excel' && !empty($where_symv)) {
        $symv_title = $symv_name . ' (' . implode(', ', $symv_kladoi) . ')';

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
        $sxol_etos = $se ? (int)$se : (int)(($end_year - 1) . substr((string)$end_year, -2));

        // Query permanent teachers
        $query_mon = "SELECT s.id as sid, s.code, s.name AS sname, e.* 
                      FROM school s 
                      JOIN employee e ON s.id = e.sx_yphrethshs 
                      WHERE e.status IN (1,3) AND $where_symv $exclude_pyspe_cond 
                      ORDER BY s.name, e.surname, e.name";
        $res_mon = mysqli_query($mysqlconnection, $query_mon);

        // Query substitute teachers
        $query_anapl = "SELECT s.id as sid, s.code, s.name AS sname, e.* 
                        FROM school s 
                        JOIN ektaktoi e ON s.id = e.sx_yphrethshs 
                        WHERE e.status IN (1,3) AND $where_symv $exclude_pyspe_cond 
                        ORDER BY s.name, e.surname, e.name";
        $res_anapl = mysqli_query($mysqlconnection, $query_anapl);

        // Fetch all placements for permanent and substitute teachers for the school year
        $mon_yphr_map = [];
        $mon_yphr_res = mysqli_query($mysqlconnection, "SELECT y.emp_id, y.yphrethsh, y.hours, s.name as school_name 
                                                         FROM yphrethsh y 
                                                         JOIN school s ON y.yphrethsh = s.id 
                                                         WHERE y.sxol_etos = $sxol_etos");
        if ($mon_yphr_res) {
            while ($y_row = mysqli_fetch_assoc($mon_yphr_res)) {
                $mon_yphr_map[$y_row['emp_id']][] = $y_row;
            }
        }

        $anapl_yphr_map = [];
        $anapl_yphr_res = mysqli_query($mysqlconnection, "SELECT y.emp_id, y.yphrethsh, y.hours, s.name as school_name 
                                                           FROM yphrethsh_ekt y 
                                                           JOIN school s ON y.yphrethsh = s.id 
                                                           WHERE y.sxol_etos = $sxol_etos");
        if ($anapl_yphr_res) {
            while ($y_row = mysqli_fetch_assoc($anapl_yphr_res)) {
                $anapl_yphr_map[$y_row['emp_id']][] = $y_row;
            }
        }

        $filename = "ekpaideytikoi_symv_eid_" . $afm . "_" . date('Ymd_His') . ".xls";
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Cache-Control: max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");

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
</style>
</head>
<body>
<h3>Αναφορά Εκπαιδευτικών Ειδικοτήτων - Σύμβουλος: <?=htmlspecialchars($symv_title)?> (Σχολικό Έτος: <?=$sx_etos?>)</h3>
<table border="1">
<thead>
    <tr>
        <th>Κωδ.</th>
        <th>Ονομασία Σχολείου</th>
        <th>Λοιπά Σχολεία υπηρέτησης</th>
        <th>Επώνυμο</th>
        <th>Όνομα</th>
        <th>Τύπος</th>
        <th>Θέση</th>
        <th>Κλάδος</th>
        <th>Κατάσταση</th>
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
        // Output permanent teachers
        while ($row = mysqli_fetch_assoc($res_mon)) {
            $code = $row['code'];
            $sname = $row['sname'];
            $surname = $row['surname'];
            $name = $row['name'];
            $thesi = $row['thesi'] == 2 ? 'Δ/ντής/ντρια' : 'Εκπ/κός';
            $klados = getKlados($row['klados'], $mysqlconnection);
            $status = $row['status'] == 1 ? 'Εργάζεται' : 'Άδεια';
            $hm_dior = (!empty($row['hm_dior']) && $row['hm_dior'] != '0000-00-00') ? date('d-m-Y', strtotime($row['hm_dior'])) : '-';
            $hm_anal = (!empty($row['hm_anal']) && $row['hm_anal'] != '0000-00-00') ? date('d-m-Y', strtotime($row['hm_anal'])) : '-';
            $monimopoihsh = $row['monimopoihsh'] == 1 ? 'Ναι' : 'Όχι';
            $aksiologhsh = $row['aksiologhsh'] == 1 ? 'Ναι' : 'Όχι';
            if (!empty($row['hm_dior']) && $row['hm_dior'] != '0000-00-00') {
                $total_days = date2days($target_date) - date2days($row['hm_dior']) + (int)$row['proyp'];
                if ($total_days > 0) {
                    $ymd = days2ymd($total_days);
                    $synolikh = "{$ymd[0]} έτη, {$ymd[1]} μήνες, {$ymd[2]} ημέρες";
                } else {
                    $synolikh = "-";
                }
            } else {
                $synolikh = "-";
            }
            $tel = trim((string)$row['tel']);
            $email = trim((string)$row['email']);
            $email_psd = trim((string)$row['email_psd']);

            $loipa_sxoleia = '';
            if (isset($mon_yphr_map[$row['id']]) && count($mon_yphr_map[$row['id']]) > 1) {
                $other_schools = [];
                foreach ($mon_yphr_map[$row['id']] as $yp) {
                    if ((int)$yp['yphrethsh'] !== (int)$row['sid']) {
                        $hrs_str = (!empty($yp['hours']) && $yp['hours'] > 0) ? " (" . $yp['hours'] . " ώρες)" : "";
                        $other_schools[] = "• " . $yp['school_name'] . $hrs_str;
                    }
                }
                if (!empty($other_schools)) {
                    $loipa_sxoleia = implode(', ', $other_schools);
                }
            }

            echo "<tr>";
            echo "<td class='text'>$code</td>";
            echo "<td>$sname</td>";
            echo "<td>$loipa_sxoleia</td>";
            echo "<td>$surname</td>";
            echo "<td>$name</td>";
            echo "<td class='center'>Μόνιμος</td>";
            echo "<td>$thesi</td>";
            echo "<td>$klados</td>";
            echo "<td>$status</td>";
            echo "<td class='text center'>$hm_dior</td>";
            echo "<td class='text center'>$hm_anal</td>";
            echo "<td class='center'>$monimopoihsh</td>";
            echo "<td class='center'>$aksiologhsh</td>";
            echo "<td>$synolikh</td>";
            echo "<td class='text'>$tel</td>";
            echo "<td>$email</td>";
            echo "<td>$email_psd</td>";
            echo "</tr>\n";
        }

        // Output substitute teachers
        while ($row = mysqli_fetch_assoc($res_anapl)) {
            $code = $row['code'];
            $sname = $row['sname'];
            $surname = $row['surname'];
            $name = $row['name'];
            $thesi = $row['thesi'] == 2 ? 'Δ/ντής/ντρια' : 'Εκπ/κός';
            $klados = getKlados($row['klados'], $mysqlconnection);
            $status = $row['status'] == 1 ? 'Εργάζεται' : 'Άδεια';
            $hm_dior = '-';
            $hm_anal = (!empty($row['hm_anal']) && $row['hm_anal'] != '0000-00-00') ? date('d-m-Y', strtotime($row['hm_anal'])) : '-';
            $monimopoihsh = '-';
            $aksiologhsh = '-';
            $synolikh = '-';
            $stathero = trim((string)$row['stathero']);
            $kinhto = trim((string)$row['kinhto']);
            $tel = implode(' / ', array_filter([$stathero, $kinhto]));
            $email = trim((string)$row['email']);
            $email_psd = trim((string)$row['email_psd']);

            $loipa_sxoleia = '';
            if (isset($anapl_yphr_map[$row['id']]) && count($anapl_yphr_map[$row['id']]) > 1) {
                $other_schools = [];
                foreach ($anapl_yphr_map[$row['id']] as $yp) {
                    if ((int)$yp['yphrethsh'] !== (int)$row['sid']) {
                        $hrs_str = (!empty($yp['hours']) && $yp['hours'] > 0) ? " (" . $yp['hours'] . " ώρες)" : "";
                        $other_schools[] = "• " . $yp['school_name'] . $hrs_str;
                    }
                }
                if (!empty($other_schools)) {
                    $loipa_sxoleia = implode(', ', $other_schools);
                }
            }

            echo "<tr>";
            echo "<td class='text'>$code</td>";
            echo "<td>$sname</td>";
            echo "<td>$loipa_sxoleia</td>";
            echo "<td>$surname</td>";
            echo "<td>$name</td>";
            echo "<td class='center'>Αναπληρωτής</td>";
            echo "<td>$thesi</td>";
            echo "<td>$klados</td>";
            echo "<td>$status</td>";
            echo "<td class='text center'>$hm_dior</td>";
            echo "<td class='text center'>$hm_anal</td>";
            echo "<td class='center'>$monimopoihsh</td>";
            echo "<td class='center'>$aksiologhsh</td>";
            echo "<td class='center'>$synolikh</td>";
            echo "<td class='text'>$tel</td>";
            echo "<td>$email</td>";
            echo "<td>$email_psd</td>";
            echo "</tr>\n";
        }
?>
</tbody>
</table>
</body>
</html>
<?php
        exit;
    }

    header('Content-type: text/html; charset=utf-8'); 
?>
<html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Σύμβουλοι εκπαίδευσης ειδικοτήτων';
    require '../etc/head.php'; 
    ?>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.min.js"></script>
    <style>
        /* Styled select dropdown */
        select[name='symvoulos'] {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            margin: 1rem 0;
            font-size: 1rem;
            font-weight: 500;
            color: #1f2937;
            background: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%231f2937' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 12px;
            padding-right: 3rem;
            min-width: 380px;
            max-width: 90%;
            font-family: "Inter", "Open Sans", Helvetica, Arial, sans-serif;
        }
        
        select[name='symvoulos']:hover {
            border-color: #10b981;
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.15);
        }
        
        select[name='symvoulos']:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
        
        select[name='symvoulos'] option {
            padding: 0.5rem;
            background: #ffffff;
            color: #1f2937;
        }
        
        select[name='symvoulos'] option:first-child {
            color: #9ca3af;
            font-style: italic;
        }
    </style>
  </head>

<?php
    echo "<body>";
    require '../etc/menu.php';

    function get_symv_select($conn, $selected_afm = '') {
        $sql = "SELECT se.afm, se.eponymo, se.onoma, 
                       GROUP_CONCAT(DISTINCT k.perigrafh ORDER BY k.perigrafh SEPARATOR ', ') as kladoi,
                       GROUP_CONCAT(DISTINCT se.sch_ids SEPARATOR ',') as combined_sch_ids,
                       COUNT(DISTINCT CASE WHEN se.sch_ids IS NULL OR TRIM(se.sch_ids) = '' THEN 1 END) as has_empty_sch
                FROM symvouloi_epist se 
                LEFT JOIN klados k ON se.klados = k.id 
                WHERE (k.perigrafh NOT IN ('ΠΕ60', 'ΠΕ70') OR k.perigrafh IS NULL) 
                  AND se.klados NOT IN ('1', '2', 'ΠΕ60', 'ΠΕ70') 
                GROUP BY se.afm, se.eponymo, se.onoma 
                ORDER BY se.eponymo, se.onoma";
        $result = mysqli_query($conn, $sql);

        if (!$result || mysqli_num_rows($result) == 0) {
            echo "Δε βρέθηκαν στοιχεία συμβούλων";
            return;
        }

        echo "<select name='symvoulos' onchange='location = this.value;'>";
        echo "<option value='report_symv_eid.php'>Επιλέξτε σύμβουλο εκπαίδευσης</option>";
        while ($row = mysqli_fetch_assoc($result)) {
            $cur_afm = $row['afm'];
            $selected = ($cur_afm === $selected_afm) ? 'selected' : '';
            
            $kladoi = $row['kladoi'];
            $sch_label = "";
            if ($row['has_empty_sch'] == 0) {
                $sch_ids_arr = array_filter(array_unique(array_map('trim', explode(',', $row['combined_sch_ids']))));
                $sch_count = count($sch_ids_arr);
                if ($sch_count > 0) {
                    $sch_label = " ($sch_count σχολεία)";
                }
            }
            
            $label = $row['eponymo'] . ' ' . $row['onoma'] . ' - ' . $kladoi . $sch_label;
            echo "<option value='?afm=" . htmlspecialchars($cur_afm) . "' $selected>" . htmlspecialchars($label) . "</option>";
        }
        echo "</select>";
    }
  
    echo "<center>";
    echo "<h2>Σύμβουλοι εκπαίδευσης ειδικοτήτων</h2>";
    echo "<p><small>Λίστα εκπ/κών του κλάδου του επιλεγμένου συμβούλου εκπαίδευσης.</small></p>";
    echo "<p><small>ΣΗΜ: Η εξαγωγή σε excel περιλαμβάνει επιπλέον στοιχεία, όπως συν.υπηρεσία, λοιπά σχολεία τοποθέτησης κλπ.</small></p>";
    get_symv_select($mysqlconnection, $afm);

    if (!empty($where_symv)) {
        echo "<input type='button' class='btn-excel' VALUE='Εξαγωγή σε excel' onClick=\"location.href='report_symv_eid.php?afm=$afm&export=excel'\">&nbsp;&nbsp;";
    }
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    
    function print_table($result, $num, $mysqlconnection, $mon = true, $tbl_id = 'mytbl') {
        $i = 0;
        echo "<table id=\"$tbl_id\" class=\"dttable imagetable\" border=\"1\" style='width:90%'>\n";
        echo "<thead>";
        echo "<tr><th>Κωδ.</th>";
        echo "<th>Ονομασία</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Θέση</th>";
        echo "<th>Κλάδος</th>";
        echo "<th>Κατάσταση</th>";
        echo $mon ? "<th>Ημ.Διορισμού</th><th>Μονιμοποίηση</th><th>Αξιολόγηση</th>" : '<th>Ημ.Ανάληψης</th>';
        echo "<th>Τηλέφωνο</th>";
        echo "<th>email</th>";
        echo "</tr>";
        echo "</thead>\n<tbody>\n";

        while ($i < $num) {        
            $sid = mysqli_result($result, $i, "sid");
            $sname = mysqli_result($result, $i, "sname");
            $code = mysqli_result($result, $i, "code");
            $id = mysqli_result($result, $i, "id");
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados = getKlados(mysqli_result($result, $i, "klados"), $mysqlconnection);
            $thesi = mysqli_result($result, $i, "thesi") == 2 ? 'Δ/ντής/ντρια' : 'Εκπ/κός';
            $status = mysqli_result($result, $i, "status") == 1 ? 'Εργάζεται' : 'Άδεια';
            if ($mon) {
                $hm_dior_dt = mysqli_result($result, $i, "hm_dior");
                $hm_dior = (!empty($hm_dior_dt) && $hm_dior_dt != '0000-00-00') ? date('d-m-Y', strtotime($hm_dior_dt)) : '-';
                $monimopoihsh = mysqli_result($result, $i, 'monimopoihsh') == 1 ? 'Ναι' : 'Όχι';
                $aksiologhsh = mysqli_result($result, $i, 'aksiologhsh') == 1 ? 'Ναι' : 'Όχι';
                $tel = mysqli_result($result, $i, "tel");
            } else {
                $hm_anal_dt = mysqli_result($result, $i, "hm_anal");
                $hm_anal = (!empty($hm_anal_dt) && $hm_anal_dt != '0000-00-00') ? date('d-m-Y', strtotime($hm_anal_dt)) : '-';
                $stathero = trim((string)mysqli_result($result, $i, "stathero"));
                $kinhto = trim((string)mysqli_result($result, $i, "kinhto"));
                $tel = implode(' / ', array_filter([$stathero, $kinhto]));
            }
            $email = mysqli_result($result, $i, "email");

            echo "<tr>";
            echo "<td>$code</td>";
            echo "<td><a class='underline' href='../school/school_status.php?org=$sid' target='_blank'>$sname</a></td>";
            $link = $mon ? "../employee/employee.php?id=$id&op=view" : "../employee/ektaktoi.php?id=$id&op=view";
            echo "<td><a class='underline' href='$link' target='_blank'>$surname</a></td>";
            echo "<td>$name</td>";
            echo "<td>$thesi</td>";
            echo "<td>$klados</td>";
            echo "<td>$status</td>";
            echo $mon ? "<td>$hm_dior</td><td>$monimopoihsh</td><td>$aksiologhsh</td>" : "<td>$hm_anal</td>";
            echo "<td>$tel</td>";
            echo "<td>$email</td>";
            echo "</tr>\n";
            $i++;                        
        }
        echo "</tbody></table>";
    }

    if (!empty($where_symv)) {
        // Statistics queries
        if (!$has_unrestricted_klados && !empty($all_assigned_sch_ids)) {
            $stat_query0 = "SELECT count(*) FROM school WHERE id IN (" . implode(',', $all_assigned_sch_ids) . ") AND anenergo = 0";
            $result0 = mysqli_query($mysqlconnection, $stat_query0);
            $row0 = mysqli_fetch_row($result0);
            $schools_display = $row0[0];
        } else {
            $exclude_sch_str = !empty($exclude_pyspe_arr) ? " AND id NOT IN (" . implode(',', $exclude_pyspe_arr) . ")" : "";
            $stat_query0 = "SELECT count(*) FROM school WHERE anenergo = 0 $exclude_sch_str";
            $result0 = mysqli_query($mysqlconnection, $stat_query0);
            $row0 = mysqli_fetch_row($result0);
            $schools_display = $row0[0] . " (Όλα τα σχολεία)";
        }

        $stat_query1 = "SELECT count(*) FROM school s JOIN employee e ON s.id = e.sx_yphrethshs WHERE e.status IN (1,3) AND $where_symv $exclude_pyspe_cond";
        $stat_query2 = "SELECT count(*) FROM school s JOIN ektaktoi e ON s.id = e.sx_yphrethshs WHERE e.status IN (1,3) AND $where_symv $exclude_pyspe_cond";
        $stat_query_klados_mon = "SELECT k.perigrafh, count(*) 
                                  FROM employee e 
                                  JOIN school s ON s.id = e.sx_yphrethshs 
                                  JOIN klados k ON e.klados = k.id 
                                  WHERE e.status IN (1,3) AND $where_symv $exclude_pyspe_cond 
                                  GROUP BY k.perigrafh ORDER BY k.perigrafh";
        $stat_query_klados_anapl = "SELECT k.perigrafh, count(*) 
                                    FROM ektaktoi e 
                                    JOIN school s ON s.id = e.sx_yphrethshs 
                                    JOIN klados k ON e.klados = k.id 
                                    WHERE e.status IN (1,3) AND $where_symv $exclude_pyspe_cond 
                                    GROUP BY k.perigrafh ORDER BY k.perigrafh";
        
        $result1 = mysqli_query($mysqlconnection, $stat_query1);
        $row1 = mysqli_fetch_row($result1);
        $result2 = mysqli_query($mysqlconnection, $stat_query2);
        $row2 = mysqli_fetch_row($result2);
        
        // Teachers per specialty
        $klados_mon = $klados_anapl = [];
        $result_klados_mon = mysqli_query($mysqlconnection, $stat_query_klados_mon);
        while ($row = mysqli_fetch_row($result_klados_mon)) { $klados_mon[$row[0]] = $row[1]; }
        $result_klados_anapl = mysqli_query($mysqlconnection, $stat_query_klados_anapl);
        while ($row = mysqli_fetch_row($result_klados_anapl)) { $klados_anapl[$row[0]] = $row[1]; }
        
        echo "<table class='imagetable' style='width:50%; margin-top: 15px;'><thead><th>Κατηγορία</th><th>Πλήθος</th></thead>";
        echo "<tr><td>Σχολεία</td><td>" . $schools_display . '</td></tr>';
        echo "<tr><td>Μόνιμοι</td><td>" . $row1[0] . '</td></tr>';
        echo "<tr><td>Αναπληρωτές</td><td>" . $row2[0] . '</td></tr>';
        echo "<tr><td>Εκπ/κοί ανά κλάδο&nbsp;";
        echo "<a href='#' class='show_hide'><small>Εμφάνιση/Απόκρυψη</small></a>";
        echo "</td><td><div id='analysis' style='display:none;'>";
        if (!empty($klados_mon)) {
            echo "<p><b>Μόνιμοι</b></p>";
            foreach ($klados_mon as $key => $value) { 
                echo htmlspecialchars($key) . ": " . $value . "<br>";
            }
        }
        if (!empty($klados_anapl)) { 
            echo "<p><b>Αναπληρωτές</b></p>";
            foreach ($klados_anapl as $key => $value) { 
                echo htmlspecialchars($key) . ": " . $value . "<br>";
            }
        }
        echo "</div></td></tr>";
        echo "</table>";

        // Gather table data
        $query = "SELECT s.id as sid, s.code, s.name AS sname, e.* 
                  FROM school s 
                  JOIN employee e ON s.id = e.sx_yphrethshs 
                  WHERE e.status IN (1,3) AND $where_symv $exclude_pyspe_cond 
                  ORDER BY s.name, e.surname, e.name";
        $query2 = "SELECT s.id as sid, s.code, s.name AS sname, e.* 
                   FROM school s 
                   JOIN ektaktoi e ON s.id = e.sx_yphrethshs 
                   WHERE e.status IN (1,3) AND $where_symv $exclude_pyspe_cond 
                   ORDER BY s.name, e.surname, e.name";

        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);

        echo "<h2 style='margin-top: 25px;'>Μόνιμοι (" . $num . ")</h2>";
        print_table($result, $num, $mysqlconnection, true, 'tbl_mon');
        
        $result2 = mysqli_query($mysqlconnection, $query2);
        $num2 = mysqli_num_rows($result2);
        if ($num2 > 0) {
            echo "<h2 style='margin-top: 25px;'>Αναπληρωτές (" . $num2 . ")</h2>";
            print_table($result2, $num2, $mysqlconnection, false, 'tbl_anapl');
        }

        echo "<br>";
        echo "<input type='button' class='btn-excel' VALUE='Εξαγωγή σε excel' onClick=\"location.href='report_symv_eid.php?afm=$afm&export=excel'\">&nbsp;&nbsp;";
        echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    }
?>
    </center>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.print.min.js"></script>

    <script type="text/javascript">  
    // Custom sorting function for date in DD-MM-YYYY format
    $.fn.dataTable.ext.type.order['date-dd-mm-yyyy-pre'] = function(date) {
        if (!date || date === '-') return 0;
        var parts = date.split('-');
        if (parts.length === 3) {
            return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
        }
        return 0;
    };  

    $(document).ready(function() { 
        $(".dttable").DataTable({
            lengthMenu: [
                [20, 50, -1],
                [20, 50, 'Όλοι']
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/el.json'
            },
            dom: 'Bfrtlip',
            buttons: [
                'excel', 'pdf', 'print'
            ],
            columnDefs: [
                { type: 'date-dd-mm-yyyy', targets: [7] }
            ]
        });
        $("#analysis").hide();

        $('.show_hide').click(function(e){
            e.preventDefault();
            $("#analysis").slideToggle();
        });
    });
    </script>
</body>
</html>
