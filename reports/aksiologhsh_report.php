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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h1 style="margin: 0;">Αναφορά Αξιολόγησης Εκπαιδευτικών</h1>
        <a href="create_eval_csv.php" class="btn btn-green" style="text-decoration: none; padding: 7px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-weight: bold;">
            ⚙️ Δημιουργία CSV Αξιολόγησης
        </a>
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
            
            <label>Μονιμοποίηση:</label>
            <select name="monimopoihsh">
                <option value=""<?php echo (isset($_POST['monimopoihsh']) && $_POST['monimopoihsh'] === '') ? ' selected' : ''; ?>>Όλοι</option>
                <option value="1"<?php echo (isset($_POST['monimopoihsh']) && $_POST['monimopoihsh'] === '1') ? ' selected' : ''; ?>>Ναι</option>
                <option value="0"<?php echo (isset($_POST['monimopoihsh']) && $_POST['monimopoihsh'] === '0') ? ' selected' : ''; ?>>Όχι</option>
            </select>
            
            <label>Αξιολόγηση:</label>
            <select name="aksiologhsh">
                <option value=""<?php echo (isset($_POST['aksiologhsh']) && $_POST['aksiologhsh'] === '') ? ' selected' : ''; ?>>Όλοι</option>
                <option value="1"<?php echo (isset($_POST['aksiologhsh']) && $_POST['aksiologhsh'] === '1') ? ' selected' : ''; ?>>Ναι</option>
                <option value="0"<?php echo (isset($_POST['aksiologhsh']) && $_POST['aksiologhsh'] === '0') ? ' selected' : ''; ?>>Όχι</option>
            </select>
            <br>
            <label>Ημ/νία Αξιολόγησης από:</label>
            <input type="date" name="aks_date_from" value="<?php echo isset($_POST['aks_date_from']) ? $_POST['aks_date_from'] : ''; ?>">
            
            <label>έως:</label>
            <input type="date" name="aks_date_to" value="<?php echo isset($_POST['aks_date_to']) ? $_POST['aks_date_to'] : ''; ?>">
            
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
        $dipe = '398';
        
        // Pre-load symvouloi_epist once to avoid N+1 queries and duplicate join rows
        $se_by_klados = [];
        $se_res = mysqli_query($mysqlconnection, "SELECT id, klados, afm, eponymo, onoma, emp_id, sch_ids FROM symvouloi_epist");
        while ($se_row = mysqli_fetch_assoc($se_res)) {
            $se_by_klados[$se_row['klados']][] = $se_row;
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
            s.perif,
            e.hm_dior,
            e.thesi,
            e.monimopoihsh,
            e.aksiologhsh
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
        AND e.sx_yphrethshs NOT IN ($allo_pysde, $allo_pyspe, $dipe, $foreas, $ekswteriko)
        AND COALESCE(yp.primary_sch_id, e.sx_yphrethshs) NOT IN ($allo_pysde, $allo_pyspe, $dipe, $foreas, $ekswteriko)
        AND s.type2 = 0"; // dhmosio

        // Add filters
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

        $result = mysqli_query($mysqlconnection, $query);

        $seen_table = [];

        ob_start();
        
        if ($result) {
            echo "<table id='dataTable' class='display'>";
            echo "<thead><tr>
                <th>Α/Α</th>
                <th>Επώνυμο</th>
                <th>Όνομα</th>
                <th>ΑΦΜ</th>
                <th>Σχολείο</th>
                <th>Συμβ.παιδ.επώνυμο</th>
                <th>Συμβ.παιδ.όνομα</th>
                <th>Συμβ.παιδ.ΑΦΜ</th>
                <th>Δ/ντής Επώνυμο</th>
                <th>Δ/ντής Όνομα</th>
                <th>Δ/ντής ΑΦΜ</th>
                <th>ΑΦΜ Συμβ.Επιστ.</th>
                <th>Επώνυμο Συμβ.Επιστ.</th>
                <th>Όνομα συμβ.επιστ</th>
                <th>Ταύτιση</th>
                <th>Κλάδος</th>
                <th>Περιφέρεια</th>
                <th>Ημ. Διορ.</th>
                <th>Δντης/Πρνος</th>
                <th>Μονιμοποίηση</th>
                <th>Αξιολόγηση</th>
            </tr></thead><tbody>";
            
            $count = 1;
            while ($row = mysqli_fetch_array($result)) {
                $emp_afm = trim($row['emp_afm']);
                if (isset($seen_table[$emp_afm])) {
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

                $is_eae = (in_array($row['klados'], ['ΠΕ60ΕΑΕ', 'ΠΕ70ΕΑΕ', 'ΠΕ61', 'ΠΕ71']) || in_array($row['klados_id'], [16, 17, 18, 19]));

                if ($is_eae) {
                    // Για τους εκπ/κούς Ειδικής Αγωγής (ΠΕ60ΕΑΕ, ΠΕ70ΕΑΕ, ΠΕ61, ΠΕ71):
                    // Επιστημονική ευθύνη (Πεδίο Α1) έχει ο σύμβουλος Παιδαγωγικής ευθύνης του σχολείου
                    $symv_epist_afm = $row['symv_paid_afm'];
                    $symv_epist_surname = $row['symv_paid_surname'];
                    $symv_epist_name = $row['symv_paid_name'];

                    // Παιδαγωγική ευθύνη (Πεδίο Α2 & Β) έχει ο σύμβουλος Ειδικής Αγωγής (από τον πίνακα symvouloi_epist)
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

                echo "<tr>";
                echo "<td>".$count++."</td>";
                echo "<td>".$row['emp_surname']."</td>";
                echo "<td>".$row['emp_name']."</td>";
                echo "<td>".$row['emp_afm']."</td>";
                echo "<td>".$row['sch_name']."</td>";
                echo "<td>".$symv_paid_surname."</td>";
                echo "<td>".$symv_paid_name."</td>";
                echo "<td>".$symv_paid_afm."</td>";
                echo "<td>".$row['dnt_surname']."</td>";
                echo "<td>".$row['dnt_name']."</td>";
                echo "<td>".$row['dnt_afm']."</td>";
                echo "<td>".($symv_epist_afm ?: 'Δεν έχει οριστεί')."</td>";
                echo "<td>".($symv_epist_surname ?: 'Δεν έχει οριστεί')."</td>";
                echo "<td>".($symv_epist_name ?: 'Δεν έχει οριστεί')."</td>";
                echo "<td>".($taytish ? 'Ναι' : 'Όχι')."</td>";
                echo "<td>".$row['klados']."</td>";
                echo "<td>".$row['perif']."</td>";
                echo "<td>".$row['hm_dior']."</td>";
                echo "<td>".($row['thesi'] == 2 ? 'Ναι' : 'Όχι')."</td>";
                echo "<td>".($row['monimopoihsh'] == 1 ? 'Ναι' : 'Όχι')."</td>";
                echo "<td>".($row['aksiologhsh'] == 1 ? 'Ναι' : 'Όχι')."</td>";
                // echo "<td>".$row['aksiologhsh_date']."</td>"; // future use
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        $table_html = ob_get_clean();

        $unique_count = count($seen_table);
        $query_title = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
        $count_display = "<div style='margin: 10px 0; font-weight: bold; color: #1b5e20;'><span title=\"$query_title\">$unique_count μοναδικοί εκπαιδευτικοί</span></div>";

        echo $count_display;
        echo $table_html;
    }
    ?>
</div>
</body>
</html>