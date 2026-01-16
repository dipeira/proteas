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
    header("Location: ../index.php");
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
    <script type="text/javascript">
    $(document).ready(function() {
        // Initialize select2 for multiple klados selection
        $('#klados').select2({
            placeholder: "Επιλέξτε κλάδο/ους",
            allowClear: true
        });
        
        // Initialize DataTables
        var table = $('#dataTable').DataTable({
            "pageLength": 50,
            "scrollX": true,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Όλα"]],
            "dom": 'Bfrtip',
            "buttons": ['copy', 'excel', 'pdf'],
            "language": {
                "url": "../js/datatables/greek.json"
            }
        });
    });
    </script>
</head>
<body>
    <?php require '../etc/menu.php'; ?>
<div id="container">
    <h1>Αναφορά Αξιολόγησης Εκπαιδευτικών</h1>
    
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
        
        // Build the query based on filters
        $query = "SELECT
            e.surname as emp_surname,
            e.name as emp_name,
            e.afm as emp_afm,
            sp.surname as symv_paid_surname,
            sp.name as symv_paid_name,
            sp.afm as symv_paid_afm,
            d.surname as dnt_surname,
            d.name as dnt_name,
            d.afm as dnt_afm,
            se.afm as symv_epist_afm,
            se.eponymo as symv_epist_surname,
            se.onoma as symv_epist_name,
            se.sch_ids,
            d.thesi,
            CASE WHEN sp.id = se.emp_id THEN 1 ELSE 0 END as taytish,
            k.perigrafh as klados,
            e.klados as klados_id,
            s.name as sch_name,
            s.id as sch_id,
            s.perif,
            e.hm_dior,
            e.thesi
        FROM employee e
        LEFT JOIN klados k ON e.klados = k.id
        LEFT JOIN school s ON e.sx_yphrethshs = s.id
        LEFT JOIN symvouloi sm ON s.perif = sm.perif
        LEFT JOIN employee sp ON sm.emp_id = sp.id
        LEFT JOIN employee d ON (s.id = d.sx_yphrethshs AND d.thesi = 2)
        LEFT JOIN symvouloi_epist se ON (e.klados = se.klados)
        WHERE e.status = 1 
        AND e.sx_yphrethshs NOT IN ($allo_pysde, $allo_pyspe, $dipe, $foreas, $ekswteriko)
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
        if ($_POST['monimopoihsh'] !== '' && $_POST['monimopoihsh'] !== '0') {
            $query .= " AND e.monimopoihsh = ".$_POST['monimopoihsh'];
        }
        if ($_POST['aksiologhsh'] !== '' && $_POST['aksiologhsh'] !== '0') {
            $query .= " AND e.aksiologhsh = ".$_POST['aksiologhsh'];
        }
        if (!empty($_POST['aks_date_from'])) {
            $query .= " AND e.aksiologhsh_date >= '".$_POST['aks_date_from']."'";
        }
        if (!empty($_POST['aks_date_to'])) {
            $query .= " AND e.aksiologhsh_date <= '".$_POST['aks_date_to']."'";
        }

        $result = mysqli_query($mysqlconnection, $query);

        $num = mysqli_num_rows($result);
        echo "<span title='$query'>$num αποτελέσματα</span>";
        
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
            </tr></thead><tbody>";
            
            
            $count = 1;
            while ($row = mysqli_fetch_array($result)) {
                $sid_array = explode(',', $row['sch_ids']);
                // if PE60 or PE70 
                if (($row['klados'] == 'ΠΕ60' || $row['klados'] == 'ΠΕ70')) {
                    // if no specific schools, symboulos epist = symvoulos paidag
                    if (empty($row['sch_ids']) || !in_array($row['sch_id'], $sid_array)) {
                        $symv_epist_afm = $row['symv_paid_afm'];
                        $symv_epist_surname = $row['symv_paid_surname'];
                        $symv_epist_name = $row['symv_paid_name'];
                    // if has schools and current school is one of them
                    } else {
                        $symv_epist_afm = $row['symv_epist_afm'];
                        $symv_epist_surname = $row['symv_epist_surname'];
                        $symv_epist_name = $row['symv_epist_name'];
                    }
                // if other klados
                } else {
                    // Get all consultants for this klados to find the appropriate one
                    $klados_query = "SELECT se.afm, se.eponymo, se.onoma, se.sch_ids
                                   FROM symvouloi_epist se
                                   WHERE se.klados = '".$row['klados_id']."'";
                    $klados_result = mysqli_query($mysqlconnection, $klados_query);
                    
                    $consultant_found = false;
                    $default_consultant = null;
                    
                    // First pass: look for consultant who has this school in sch_ids
                    while ($consultant = mysqli_fetch_array($klados_result)) {
                        if (!empty($consultant['sch_ids'])) {
                            $consultant_schools = explode(',', $consultant['sch_ids']);
                            if (in_array($row['sch_id'], $consultant_schools)) {
                                // Found consultant responsible for this specific school
                                $symv_epist_afm = $consultant['afm'];
                                $symv_epist_surname = $consultant['eponymo'];
                                $symv_epist_name = $consultant['onoma'];
                                $consultant_found = true;
                                break;
                            }
                        } else {
                            // Store consultant with no specific schools as default
                            if (!$default_consultant) {
                                $default_consultant = $consultant;
                            }
                        }
                    }
                    
                    // If no specific consultant found, use default (consultant with empty sch_ids)
                    // or fallback to original data if no default consultant exists
                    if (!$consultant_found) {
                        if ($default_consultant) {
                            $symv_epist_afm = $default_consultant['afm'];
                            $symv_epist_surname = $default_consultant['eponymo'];
                            $symv_epist_name = $default_consultant['onoma'];
                        } else {
                            // Fallback to what was joined in the original query
                            $symv_epist_afm = $row['symv_epist_afm'];
                            $symv_epist_surname = $row['symv_epist_surname'];
                            $symv_epist_name = $row['symv_epist_name'];
                        }
                    }
                }
                echo "<tr>";
                echo "<td>".$count++."</td>";
                echo "<td>".$row['emp_surname']."</td>";
                echo "<td>".$row['emp_name']."</td>";
                echo "<td>".$row['emp_afm']."</td>";
                echo "<td>".$row['sch_name']."</td>";
                echo "<td>".$row['symv_paid_surname']."</td>";
                echo "<td>".$row['symv_paid_name']."</td>";
                echo "<td>".$row['symv_paid_afm']."</td>";
                echo "<td>".$row['dnt_surname']."</td>";
                echo "<td>".$row['dnt_name']."</td>";
                echo "<td>".$row['dnt_afm']."</td>";
                echo "<td>".($symv_epist_afm ?: 'Δεν έχει οριστεί')."</td>";
                echo "<td>".($symv_epist_surname ?: 'Δεν έχει οριστεί')."</td>";
                echo "<td>".($symv_epist_name ?: 'Δεν έχει οριστεί')."</td>";
                echo "<td>".($row['taytish'] ? 'Ναι' : 'Όχι')."</td>";
                echo "<td>".$row['klados']."</td>";
                echo "<td>".$row['perif']."</td>";
                echo "<td>".$row['hm_dior']."</td>";
                echo "<td>".($row['thesi'] == 2 ? 'Ναι' : 'Όχι')."</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
    }
    ?>
</div>
</body>
</html>