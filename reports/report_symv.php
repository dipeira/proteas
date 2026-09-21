<?php
    require_once "../config.php";
    require_once "../include/functions.php";
    session_start();

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

    $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ', $mysqlconnection);
    $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ', $mysqlconnection);
    $exclude_pyspe_arr = array_filter([(int)$allo_pyspe, (int)$allo_pysde]);
    $exclude_pyspe_cond = !empty($exclude_pyspe_arr) ? " AND e.sx_yphrethshs NOT IN (" . implode(',', $exclude_pyspe_arr) . ")" : "";

    // Handle Excel export
    if (isset($_GET['export']) && $_GET['export'] == 'excel' && !empty($_GET['enothta'])) {
        $perif = (int)$_GET['enothta'];

        // Get consultant details
        $symv_sql = "SELECT s.perif, e.surname, e.name, e.klados FROM symvouloi s JOIN employee e ON s.emp_id = e.id WHERE s.perif = $perif";
        $symv_res = mysqli_query($mysqlconnection, $symv_sql);
        $symv_row = mysqli_fetch_assoc($symv_res);
        $symv_title = $symv_row ? ($symv_row['surname'] . ' ' . $symv_row['name']) : "Περιφέρεια $perif";

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
        $query_mon = "SELECT s.id as sid, s.code, s.name AS sname, e.* FROM school s JOIN employee e ON s.id = e.sx_yphrethshs 
                      WHERE e.status IN (1,3) AND s.perif = $perif $exclude_pyspe_cond ORDER BY s.name, e.surname, e.name";
        $res_mon = mysqli_query($mysqlconnection, $query_mon);

        // Query substitute teachers
        $query_anapl = "SELECT s.id as sid, s.code, s.name AS sname, e.* FROM school s JOIN ektaktoi e ON s.id = e.sx_yphrethshs 
                        WHERE e.status IN (1,3) AND s.perif = $perif $exclude_pyspe_cond ORDER BY s.name, e.surname, e.name";
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

        $filename = "ekpaideytikoi_symv_" . $perif . "_" . date('Ymd_His') . ".xls";
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
<h3>Αναφορά Εκπαιδευτικών - Σύμβουλος: <?=htmlspecialchars($symv_title)?> (Σχολικό Έτος: <?=$sx_etos?>)</h3>
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
    $page_title = 'Αναφορά Εκπαιδευτικών ανά περιφέρεια συμβούλου εκπαίδευσης';
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
            min-width: 300px;
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


    function get_symv_select($conn) {
      $sql = "SELECT s.perif, e.surname, e.name, e.klados FROM symvouloi s JOIN employee e ON s.emp_id = e.id";
      $result = mysqli_query($conn, $sql);
  
      if (mysqli_num_rows($result) == 0) {
          echo "Δε βρέθηκαν στοιχεία";
          return;
      }
  
      // Get the selected value from $_GET if it exists
      $selected_perif = isset($_GET['enothta']) ? $_GET['enothta'] : '';
  
      echo "<select name='symvoulos' onchange='location = this.value;'>";
      echo "<option value=0>Επιλέξτε σύμβουλο εκπαίδευσης</option>";
      while ($row = mysqli_fetch_assoc($result)) {
          $klados = $row['klados'] == 1 ? 'ΠΕ60' : 'ΠΕ70';
          $perif = $row['perif'];
          
          // Check if the current option should be selected
          $selected = ($perif == $selected_perif) ? 'selected' : '';
  
          // Pass s.perif as a GET parameter in the URL and apply the selected attribute if it matches $_GET['enothta']
          echo "<option value='?enothta=" . $perif . "' $selected>" . $row['surname'] . ' ' . $row['name'] . ' - ' . $klados . "</option>";
      }
      echo "</select>";
    }
  
    echo "<center>";

    echo "<h2>Σύμβουλοι εκπαίδευσης ΠΕ60/ΠΕ70</h2>";
    echo "<p><small>Λίστα εκπ/κών που υπηρετούν στα σχολεία της ενότητας του επιλεγμένου συμβούλου εκπαίδευσης.</small></p>";
    echo "<p><small>ΣΗΜ: Η εξαγωγή σε excel περιλαμβάνει επιπλέον στοιχεία, όπως συν.υπηρεσία, λοιπά σχολεία τοποθέτησης κλπ.</small></p>";
    get_symv_select($mysqlconnection);

    $perif = isset($_GET['enothta']) && !empty($_GET['enothta']) ? $_GET['enothta'] : null;
    if ($perif) {
      echo "<input type='button' class='btn-excel' VALUE='Εξαγωγή σε excel' onClick=\"location.href='report_symv.php?enothta=$perif&export=excel'\">&nbsp;&nbsp;";
    }
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    
    function print_table($result, $num, $mysqlconnection, $mon = true){
      $i=0;
      echo "<table id=\"mytbl\" class=\"dttable imagetable\" border=\"1\" style='width:90%'>\n";
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

      while ($i < $num)
      {        
          $sid = mysqli_result($result, $i, "sid");
          $sname = mysqli_result($result, $i, "sname");
          $code = mysqli_result($result, $i, "code");
          $id = mysqli_result($result, $i, "id");
          $name = mysqli_result($result, $i, "name");
          $surname = mysqli_result($result, $i, "surname");
          $klados = getKlados(mysqli_result($result, $i, "klados"),$mysqlconnection);
          $thesi = mysqli_result($result, $i, "thesi") == 2 ? 'Δ/ντής/ντρια' : 'Εκπ/κός';
          $status = mysqli_result($result, $i, "status") == 1 ? 'Εργάζεται' : 'Αδεια';
          if ($mon) {
            $hm_dior_dt = mysqli_result($result, $i, "hm_dior");
            $hm_dior = date('d-m-Y',strtotime($hm_dior_dt));
            $monimopoihsh = mysqli_result($result,$i,'monimopoihsh') == 1 ? 'Ναι' : 'Όχι';
            $aksiologhsh = mysqli_result($result,$i,'aksiologhsh') == 1 ? 'Ναι' : 'Όχι';
            $tel = mysqli_result($result, $i, "tel");
          } else {
            $hm_anal_dt = mysqli_result($result, $i, "hm_anal");
            $hm_anal = date('d-m-Y',strtotime($hm_anal_dt));
            $stathero = trim((string)mysqli_result($result, $i, "stathero"));
            $kinhto = trim((string)mysqli_result($result, $i, "kinhto"));
            $tel = implode(' / ', array_filter([$stathero, $kinhto]));
          }
          $email = mysqli_result($result, $i, "email");
          

          echo "<tr>";
          echo "<td>$code</td>";
          echo "<td><a class='underline' href='../school/school_status.php?org=$sid' target='_blank'>$sname</a></td>";
          $link = $mon ? "../employee/employee.php?id=$id&op=view" : "../employee/ektaktoi.php?id=$id&op=view";
          echo "<td><a class='underline' href=$link target='_blank'>$surname</a></td>";
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


    if ($perif) {
      // Statistics
      $stat_query0 = "SELECT count(*) from school where perif=$perif and anenergo = 0";
      $stat_query1 = "SELECT count(*) from school s JOIN employee e ON s.id = e.sx_yphrethshs 
      WHERE e.status IN (1,3) AND s.perif=$perif $exclude_pyspe_cond";
      $stat_query2 = "SELECT count(*) from school s JOIN ektaktoi e ON s.id = e.sx_yphrethshs 
      WHERE e.status IN (1,3) AND s.perif=$perif $exclude_pyspe_cond";
      // Teachers per specialty queries
      $stat_query_klados_mon = "SELECT k.perigrafh, count(*) from employee e JOIN school s ON s.id = e.sx_yphrethshs JOIN klados k ON e.klados = k.id
      WHERE e.status IN (1,3) AND s.perif=$perif $exclude_pyspe_cond GROUP BY klados";
      $stat_query_klados_anapl = "SELECT k.perigrafh, count(*) from ektaktoi e JOIN school s ON s.id = e.sx_yphrethshs JOIN klados k ON e.klados = k.id
      WHERE e.status IN (1,3) AND s.perif=$perif $exclude_pyspe_cond GROUP BY klados";
      
      $result0 = mysqli_query($mysqlconnection, $stat_query0);
      $row0 = mysqli_fetch_row($result0);
      $result1 = mysqli_query($mysqlconnection, $stat_query1);
      $row1 = mysqli_fetch_row($result1);
      $result2 = mysqli_query($mysqlconnection, $stat_query2);
      $row2 = mysqli_fetch_row($result2);
      
      // Teachers per specialty
      $klados_mon = $klados_anapl = array();
      $result_klados_mon = mysqli_query($mysqlconnection, $stat_query_klados_mon);
      while ($row = mysqli_fetch_row($result_klados_mon)) { $klados_mon[$row[0]] = $row[1]; }
      $result_klados_anapl = mysqli_query($mysqlconnection, $stat_query_klados_anapl);
      while ($row = mysqli_fetch_row($result_klados_anapl)) { $klados_anapl[$row[0]] = $row[1]; }
      
      echo "<table class='imagetable' style='width:50%'><thead><th>Κατηγορία</th><th>Πλήθος</th>";
      echo "<tr><td>Σχολεία</td><td>" . $row0[0] . '</td></tr>';
      echo "<tr><td>Μόνιμοι</td><td>" . $row1[0] . '</td></tr>';
      echo "<tr><td>Αναπληρωτές</td><td>" . $row2[0] . '</td></tr>';
      echo "<tr><td>Εκπ/κοί ανά κλάδο&nbsp;";
      echo "<a href='#' class='show_hide'><small>Εμφάνιση/Απόκρυψη</small></a>";
      echo "</td><td><div id='analysis' style='display:none;'>";
      echo "<p><b>Μόνιμοι</b></p>";
      foreach ($klados_mon as $key => $value) { 
        echo $key . ": ".$value."<br>";
      }
      if (!empty($klados_anapl)) { 
        echo "<p><b>Αναπληρωτές</b></p>";
        foreach ($klados_anapl as $key => $value) { 
          echo $key . ": ".$value."<br>";
        }
      }
      echo "</div></td></tr>";
      echo "</table>";

      // Gather table data
      $query = "SELECT s.id as sid, s.code,s.name AS sname, e.* from school s JOIN employee e ON s.id = e.sx_yphrethshs 
      WHERE e.status IN (1,3) AND s.perif=$perif $exclude_pyspe_cond";
      $query2 = "SELECT s.id as sid, s.code,s.name AS sname, e.* from school s JOIN ektaktoi e ON s.id = e.sx_yphrethshs 
      WHERE e.status IN (1,3) AND s.perif=$perif $exclude_pyspe_cond";

      $result = mysqli_query($mysqlconnection, $query);
      $num = mysqli_num_rows($result);

      echo "<h2>Μόνιμοι</h2>";
      print_table($result, $num, $mysqlconnection);
      
      $result = mysqli_query($mysqlconnection, $query2);
      $num = mysqli_num_rows($result);
      if ($num){
        echo "<h2>Αναπληρωτές</h2>";
        print_table($result, $num, $mysqlconnection, false);
      }

      echo "<input type='button' class='btn-excel' VALUE='Εξαγωγή σε excel' onClick=\"location.href='report_symv.php?enothta=$perif&export=excel'\">&nbsp;&nbsp;";
      echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    }
    
?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.print.min.js"></script>

    <script type="text/javascript">  
    // Custom sorting function for date in DD-MM-YYYY format
    $.fn.dataTable.ext.type.order['date-dd-mm-yyyy-pre'] = function(date) {
        var parts = date.split('-');
        return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
    };  

    $(document).ready(function() { 
        var table = $(".dttable").DataTable({
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
                { type: 'date-dd-mm-yyyy', targets: [7] } // Set to your date column index if 7 is correct
            ]
        });
        $("#analysis").hide();

        $('.show_hide').click(function(){
            $("#analysis").slideToggle();
        });
    });
</script>
</body>
</html>
                
