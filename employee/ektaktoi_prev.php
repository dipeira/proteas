<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once"../include/functions.php";
    
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
    require "../tools/class.login.php";
    $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {   
    header("Location: ../tools/login.php");
}
else {
        $logged = 1;
}
    
    // Get previous sxol_etos or find
if (strlen($_REQUEST['sxoletos'])>0) {
    $sxoletos = $_REQUEST['sxoletos'];
}
else
{
    $sxoletos = find_prev_year($sxol_etos);
}
     $_SESSION['sxoletos'] = $sxoletos;
          
?>
<html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Αναπληρωτές';
    require '../etc/head.php'; 
    ?>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.js"></script>
    <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
    <script type="text/javascript" src="../js/common.js"></script>
    <script type="text/javascript">        
        $().ready(function() {
            $("#yphr").autocomplete("get_school.php", {
                width: 260,
                matchContains: true,
                selectFirst: false
            });
        });
        $().ready(function() {
            $("#surname").autocomplete("get_name_prev.php", {
                width: 260,
                matchContains: true,
                selectFirst: false
            });
        });
        
        $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
        });

        function changeYear(){
            var selYr = $('#year-change').find(":selected").val();
            parent.location='ektaktoi_prev.php?sxoletos='+selYr;
        } 
    </script>
    <style>
        /* Fix width for 'Ονομα' column (3rd column) */
        #mytbl th:nth-child(3),
        #mytbl td:nth-child(3) {
            min-width: 150px;
            width: auto;
        }
        
        /* Style select dropdowns - klados, type, praxi_old */
        .imagetable select,
        .imagetable select[name="klados"],
        .imagetable select[name="type"],
        .imagetable select[name="praxi_old"],
        select#klados,
        select#type,
        select#praxi_old {
            width: 100% !important;
            max-width: 100% !important;
            padding: 8px 12px !important;
            padding-right: 35px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
            background: #ffffff !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234b5563' d='M6 9L1 4h10z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 10px center !important;
            background-size: 12px !important;
            color: #1f2937 !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            font-family: "Inter", "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
        }
        
        .imagetable select:hover,
        select#klados:hover,
        select#type:hover,
        select#praxi_old:hover {
            border-color: #4FC5D6;
            box-shadow: 0 0 0 3px rgba(79, 197, 214, 0.1);
        }
        
        .imagetable select:focus,
        select#klados:focus,
        select#type:focus,
        select#praxi_old:focus {
            outline: none;
            border-color: #4FC5D6;
            box-shadow: 0 0 0 3px rgba(79, 197, 214, 0.2);
        }
        
        .imagetable select option {
            padding: 10px 12px;
            background: #ffffff;
            color: #1f2937;
        }
        
        .imagetable select option:hover {
            background: #e0f7fa;
        }
        
        .imagetable select option:checked {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
            color: white;
        }
        
        /* Year change select styling */
        select#year-change {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #ffffff;
            color: #1f2937;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234b5563' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
            padding-right: 35px;
            margin-left: 8px;
        }
        
        select#year-change:hover {
            border-color: #4FC5D6;
            box-shadow: 0 0 0 3px rgba(79, 197, 214, 0.1);
        }
        
        select#year-change:focus {
            outline: none;
            border-color: #4FC5D6;
            box-shadow: 0 0 0 3px rgba(79, 197, 214, 0.2);
        }
    </style>
    
  </head>
  <body> 
    <?php require '../etc/menu.php'; ?>
      
<div>
        <?php
        $usrlvl = $_SESSION['userlevel'];

        //rpp = results per page
        if (isset($_POST['rpp'])) {
            $rpp = $_POST['rpp'];
        } elseif (isset($_GET['rpp'])) {
            $rpp = $_GET['rpp'];
        } else {
            $rpp= 20;
        }
            
        if ($_POST['page']!=0) {
            $curpg = $_POST['page'];
        } elseif (isset($_GET['page'])) { 
            $curpg = $_GET['page'];
        } else {
                $curpg = 1;
        }
                        
        //limit in the query thing
        $limitQ = ' LIMIT ' .($curpg - 1) * $rpp .',' .$rpp;

                
        $query = "SELECT * FROM ektaktoi_old where sxoletos=" . $sxoletos.' ';
        
        $klpost = $yppost = $praxipost = $typepost = 0;
        $surpost = '';
        $whflag = 0;
        if (($_POST['klados']>0) || (strlen($_POST['yphr'])>0) || (strlen($_POST['surname'])>0) || (strlen($_POST['type'])>0)) {
            $posted=1;
            $curpg=1;
        }
        if (($_POST['klados']>0) || ($_GET['klados']>0)) {
            if ($_POST['klados']>0) {
                $klpost = $_POST['klados'];
            } else {
                $klpost = $_GET['klados'];
            }
            $query .= "AND klados = $klpost ";
        }
        if (($_POST['type']>0) || ($_GET['type']>0)) {
            if ($_POST['type']>0) {
                $typepost = $_POST['type'];
            } else {
                $typepost = $_GET['type'];
            }
            $query .= "AND type = $typepost ";
        }
        if ((strlen($_POST['yphr'])>0) || ($_GET['yphr']>0)) {
            if (strlen($_POST['yphr'])>0) {
                $yppost = getSchoolID($_POST['yphr'], $mysqlconnection);
            }
            if ($_GET['yphr']>0) {
                $yppost = $_GET['yphr'];
            }
            $query .= "AND sx_yphrethshs = $yppost ";
        }
        if (strlen($_POST['surname'])>0 || strlen($_GET['surname'])>0) {
            if (strlen($_POST['surname'])>0) {
                $surpost = $_POST['surname'];
            } else{
                $surpost = urldecode($_GET['surname']);
            }
            $query .= "AND surname LIKE '%$surpost%' ";
        }
        if ((strlen($_POST['praxi_old'])>0) || ($_GET['praxi_old']>0)) {
            if ($_GET['praxi_old']>0) {
                $praxipost = $_GET['praxi_old'];
            } else {
                $praxipost = $_POST['praxi_old'];
            }
            $query .= "AND praxi = $praxipost ";
        }
        $query_all = $query;
        $query .= " ORDER BY surname ";
                $query .= $limitQ;
                
        // Debugging...
        //echo $query;
        
        $result = mysqli_query($mysqlconnection, $query);
        $result1 = mysqli_query($mysqlconnection, $query_all);
        // Number of records found
        
        if ($result) {
            $num_record = mysqli_num_rows($result);
        }
        if ($result1) {
            $num_record1 = mysqli_num_rows($result1);
        }
        $lastpg = ceil($num_record1 / $rpp);
        
                
        if ($result) {
            $num=mysqli_num_rows($result);
        }
                
        // display prev year select
        $query_pr = "SELECT distinct sxoletos from praxi_old ORDER BY sxoletos DESC";
        $result_pr = mysqli_query($mysqlconnection, $query_pr);
        
        echo "<div style='margin: 0 auto; padding: 0 20px;'>";
        echo "<div class='page-header'>";
        if (isset($sxoletos)) {
            echo "<h2>Αναπληρωτές σχολικού έτους: " . substr($sxoletos, 0, 4) . '-' . substr($sxoletos, 4, 2) ."</h2>";
        } else {
            echo "<h2>Αναπληρωτές προηγούμενων ετών</h2>";
        }
        if (mysqli_num_rows($result_pr)) {
            echo "<div style='margin: 10px 0;'>Επιλέξτε έτος: ";
            echo "<select id='year-change' onchange='changeYear()' style='padding: 6px 10px; border-radius: 6px; border: 1px solid #d1d5db; width: 7em;'>";
            while ($row = mysqli_fetch_array($result_pr)){
                $setos = substr($row['sxoletos'], 0, 4).'-'.substr($row['sxoletos'], 4, 2);
                $selected = $sxoletos == $row['sxoletos'] ? 'selected' : '';
                echo "<option value='".$row['sxoletos']."' $selected>".$setos."</option>";
            }
            echo "</select></div>";
        }
        echo "</div>";
        echo "<div style='display: flex; justify-content: center;'>";
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead>";
        echo "<tr><th style='min-width: 50px;'>Ενεργεια</th>\n";
        echo "<th>Επωνυμο</th>\n";
        echo "<th style='min-width: 150px;'>Ονομα</th>\n";
        echo "<th>Ειδικοτητα</th>\n";
        echo "<th>Σχ.Υπηρετησης<br><small>(* περισσότερα από 1 σχολ.)</small></th>\n";
        echo "<th>Τύπος Απασχολησης</th>\n";
        echo "<th>Πραξη</th>\n";
        echo "</tr>";
        echo "<tr><form id='src' name='src' action='ektaktoi_prev.php?sxoletos=$sxoletos' method='POST'>\n";
    ?>
    <script type="text/javascript">
        $().ready(function(){
            $('#resetBtn').click(function() {
                $('#surname').val("");
                $('#klados').val("");
                $('#yphr').val("");
                $('#type').val("");
                $('#praxi_old').val("");
                $('#src').submit();
            });
        });
    </script>
        <?php
        echo "<td style='text-align: center;'><INPUT TYPE='submit' VALUE='🔍 Αναζήτηση' />"
            . "<br><button type='button' class='btn btn-yellow' id='resetBtn'><small>🔄 Επαναφορά</small></button>"
            . "</td><td>\n";
        echo "<input type='text' name='surname' id='surname' value='$surpost' />\n";
        echo "<td></td><td>\n";
        echo $klpost ? kladosCombo($klpost, $mysqlconnection) : kladosCmb($mysqlconnection);
        echo "</td>\n";
        echo "<div id=\"content\">";
        echo "<form autocomplete=\"off\">";
        echo "<td><input type=\"text\" name=\"yphr\" id=\"yphr\" value='".getSchool($yppost, $mysqlconnection)."'/></td>";
        echo "</div>";
        echo "<td>";
        echo $typepost ? typeCmb1($typepost, $mysqlconnection) : typeCmb($mysqlconnection);
        echo "</td>";
        echo "<td>";
        tblCmb($mysqlconnection, 'praxi_old', $praxipost, 'praxi_old', null, "SELECT * from praxi_old where sxoletos = $sxoletos");
        echo "</td>";
        echo "</form></tr>\n";
    
        echo "</thead>\n";
    
        echo "<tbody>\n";
        if ($num == 0) {
            echo "<tr><td colspan=7><h3>Δε βρέθηκαν αποτελέσματα...</h3></td></tr>";
        } else {
            $i = 0;
            while ($i < $num)
        {
    
            $id = mysqli_result($result, $i, "id");
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            // an parapanw apo 1 sxoleia, deixnei mono to 1o (kyriws) kai vazei * dipla toy.
            $sx_yphrethshs_id_str = mysqli_result($result, $i, "sx_yphrethshs");
            $sx_yphrethshs_id_arr = explode(",", $sx_yphrethshs_id_str);
            $sx_yphrethshs_id = trim($sx_yphrethshs_id_arr[0]);
            $sx_yphrethshs = getSchool($sx_yphrethshs_id, $mysqlconnection);
            $sx_yphrethshs_url = "<a class='underline' href=\"../school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a>";

            $type = mysqli_result($result, $i, "type");
            $praxi = mysqli_result($result, $i, "praxi");
            $query_p = "SELECT name from praxi_old WHERE id=$praxi AND sxoletos=$sxoletos";
            $result_p = mysqli_query($mysqlconnection, $query_p);
            $praxi = mysqli_result($result_p, 0, "name");
                                
            echo "<tr><td>";
            echo "<span title=\"Προβολή\"><a href=\"ektaktoi.php?op=view&sxoletos=".$sxoletos."&id=$id\"><img style=\"border: 0pt none;\" src=\"../images/view_action.png\"/></a></span>";
            echo "</td>";
            $typos = get_type($type, $mysqlconnection);
            echo "<td><a class='underline' href='ektaktoi.php?op=view&sxoletos=".$sxoletos."&id=$id'>".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>".$sx_yphrethshs_url."</td><td>$typos</td><td>$praxi</td>\n";
            echo "</tr>";

            $i++;
            }   
        }
        echo "</tbody>\n";
        echo "<tr><td colspan=7 class='pagination-row'>";
        $prevpg = $curpg-1;
        if ($lastpg == 0) {
            $curpg = 0;
        }
        echo "<div class='pagination-info'>Σελίδα <strong>$curpg</strong> από <strong>$lastpg</strong> (<strong>$num_record1</strong> εγγραφές)</div>";
        
        // Build query string for pagination links
        $getstring = "sxoletos=$sxoletos&rpp=$rpp";
        $getstring .= $klpost ? "&klados=$klpost" : '';
        $getstring .= $yppost ? "&yphr=$yppost" : '';
        $getstring .= $surpost ? "&surname=".urlencode($surpost) : '';
        $getstring .= $typepost ? "&type=$typepost" : '';
        $getstring .= $praxipost ? "&praxi_old=$praxipost" : '';
        
        echo "<div class='pagination-links'>";
        if ($curpg!=1) {
            echo "<a href='ektaktoi_prev.php?page=1&$getstring'>⏮️ Πρώτη</a>";
            echo "<a href='ektaktoi_prev.php?page=$prevpg&$getstring'>◀️ Προηγούμενη</a>";
        }
        else {
            echo "<span>⏮️ Πρώτη</span>";
            echo "<span>◀️ Προηγούμενη</span>";
        }
        if ($curpg != $lastpg) {
            $nextpg = $curpg+1;
            echo "<a href='ektaktoi_prev.php?page=$nextpg&$getstring'>Επόμενη ▶️</a>";
            echo "<a href='ektaktoi_prev.php?page=$lastpg&$getstring'>Τελευταία ⏭️</a>";
        }
        else { 
            echo "<span>Επόμενη ▶️</span>";
            echo "<span>Τελευταία ⏭️</span>";
        }
        echo "</div>";
        echo "<div class='pagination-form'>";
        echo "<FORM METHOD='POST' ACTION='ektaktoi_prev.php?$getstring' style='display: flex; gap: 8px; align-items: center;'>";
        echo "<label style='font-weight: 500; color: #374151;'>Μετάβαση στη σελ.:</label>";
        echo "<input type=\"text\" name=\"page\" size=3 />";
        echo "<input type=\"submit\" value=\"➡️ Μετάβαση\" />";
        echo "</FORM>";
        echo "<FORM METHOD='POST' ACTION='ektaktoi_prev.php?$getstring' style='display: flex; gap: 8px; align-items: center;'>";
        echo "<label style='font-weight: 500; color: #374151;'>Εγγρ./σελ.:</label>";
        echo "<input type=\"text\" name=\"rpp\" value=\"$rpp\" size=3 />";
        echo "<input type=\"submit\" value=\"✅ Ορισμός\" />";
        echo "</FORM>";
        echo "</div>";
        echo "</td></tr>";
        echo "<tr><td colspan=7 class='action-buttons-row'>";
        echo "<INPUT TYPE='button' VALUE='📝 Πρόσληψη αναπληρωτών' onClick=\"parent.location='ektaktoi_hire.php'\">";
        echo "<INPUT TYPE='button' VALUE='📊 Πράξεις έτους $sxoletos' onClick=\"parent.location='praxi_prev.php?sxoletos=$sxoletos'\">";
        echo "</td></tr>";
        echo "<tr><td colspan=7 class='action-buttons-row'>";
        echo "<INPUT TYPE='button' class='btn-red' VALUE='🏠 Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        echo "</td></tr>";
        echo "</table>\n";
        ?>
      </div>
</div>
  </body>
</html>
<?php
    mysqli_close($mysqlconnection);
?>
