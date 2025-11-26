<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once"../include/functions.php";
    
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  session_start();
  require_once "../tools/class.login.php";
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false) {   
    header("Location: ../tools/login.php");
  }
  else {
    $logged = 1;
  }
      
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
            $("#surname").autocomplete("get_name_ekt.php", {
                width: 260,
                matchContains: true,
                selectFirst: false
            });
        });
        
        $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
        });
    </script>
    <style>
        .page-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Fix width for 'Ονομα' column (3rd column) */
        #mytbl th:nth-child(3),
        #mytbl td:nth-child(3) {
            min-width: 150px;
            width: auto;
        }
        .table-scroll {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 16px;
        }
        #mytbl {
            border-collapse: collapse;
            min-width: 720px;
            width: auto;
        }
    </style>
    
  </head>
    
  <body> 
  <?php require '../etc/menu.php'; ?>
<div>
<?php
    $usrlvl = $_SESSION['userlevel'];
    //$query = "SELECT * FROM employee";
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

    $query = "SELECT * FROM ektaktoi ";
      
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
        if ($whflag) {
            $query .= "AND klados = $klpost ";
        } else {
                        $query .= "WHERE klados = $klpost ";
        }
        $whflag=1;
    }
    if (($_POST['type']>0) || ($_GET['type']>0)) {
        if ($_POST['type']>0) {
            $typepost = $_POST['type'];
        } else {
            $typepost = $_GET['type'];
        }
        if ($whflag) {
            $query .= "AND type = $typepost ";
        } else {
                $query .= "WHERE type = $typepost ";
        }
        $whflag=1;
    }
    if ((strlen($_POST['yphr'])>0) || ($_GET['yphr']>0)) {
        if (strlen($_POST['yphr'])>0) {
            $yppost = getSchoolID($_POST['yphr'], $mysqlconnection);
        }
        if ($_GET['yphr']>0) {
            $yppost = $_GET['yphr'];
        }
        if ($whflag) {
            $query .= "AND sx_yphrethshs = $yppost ";
        } else {
            $query .= "WHERE sx_yphrethshs = $yppost ";
        }
    }
    if (strlen($_POST['surname'])>0 || strlen($_GET['surname'])>0) {
        if (strlen($_POST['surname'])>0) {
            $surpost = $_POST['surname'];
        } else{
            $surpost = urldecode($_GET['surname']);
        }
                        
        if ($whflag) {
            $query .= "AND surname LIKE '%$surpost%' ";
        } else
        {
            $query .= "WHERE surname LIKE '%$surpost%' ";
            $whflag=1;
        }
    }
    if ((strlen($_POST['praxi'])>0) || ($_GET['praxi']>0)) {
        if ($_GET['praxi']>0) {
            $praxipost = $_GET['praxi'];
        } else {
                $praxipost = $_POST['praxi'];
        }
        if ($whflag) {
            $query .= "AND praxi = $praxipost ";
        } else {
            $query .= "WHERE praxi = $praxipost ";
        }
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
            
            // added 07-02-2013 - when 1 result, redirect to that employee page
    if ($num_record == 1) {
        $id = mysqli_result($result, 0, "id");
        $url = "ektaktoi.php?id=$id&op=view";
        echo "<script>window.location = '$url'</script>";
    }
    echo "<div style='margin: 0 auto; padding: 0 20px;'>";
    echo "<div class='page-container'>";
    echo "<div class='page-header'>";
    echo "<h2>Αναπληρωτές</h2>";
    echo "</div>";
    echo "<div class='table-scroll'>";
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
    echo "<thead>";
    echo "<tr><th style='min-width: 50px;'>Ενεργεια</th>\n";
    echo "<th>Επωνυμο</th>\n";
    echo "<th style='min-width: 150px;'>Ονομα</th>\n";
    echo "<th>Ειδικοτητα</th>\n";
    echo "<th>Σχ.Υπηρετησης</th>\n";
    // echo "<th>Τύπος Απασχολησης</th>\n";
    echo "<th>Πραξη</th>\n";
    echo "</tr>";
    echo "<tr><form id='src' name='src' action='ektaktoi_list.php' method='POST'>\n";    
?>
    <script type="text/javascript">
        $().ready(function(){
            $('#resetBtn').click(function() {
                $('#surname').val("");
                $('#klados').val("");
                $('#yphr').val("");
                $('#type').val("");
                $('#praxi').val("");
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
        // echo "<div id=\"content\">";
        echo "<form autocomplete=\"off\">";
        echo "<td><input type=\"text\" name=\"yphr\" id=\"yphr\" value='".getSchool($yppost, $mysqlconnection)."'/></td>";
        echo "</div>";
        // echo "<td>";
        //echo "</td>";
        // echo $typepost ? typeCmb1($typepost, $mysqlconnection) : typeCmb($mysqlconnection);
        // echo "</td>";
        echo "<td>";
        tblCmb($mysqlconnection, 'praxi', $praxipost, 'praxi', 'name');
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
    
                $id = mysqli_result($result, $i, 0);
                $name = mysqli_result($result, $i, "name");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $sx_yphrethshs_id = mysqli_result($result, $i, "sx_yphrethshs");
                $sx_yphrethshs = getSchool($sx_yphrethshs_id, $mysqlconnection);
                $sx_yphrethshs_url = "<a class='underline' href=\"../school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a>";
                // $type = mysqli_result($result, $i, "type");
                $praxi = mysqli_result($result, $i, "praxi");
                $praxi = getNamefromTbl($mysqlconnection, "praxi", $praxi);
                  
                echo "<tr><td>";
                echo "<span title=\"Προβολή\"><a href=\"ektaktoi.php?id=$id&op=view\" class=\"action-icon view\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M10 12a2 2 0 100-4 2 2 0 000 4z\"></path><path fill-rule=\"evenodd\" d=\"M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z\" clip-rule=\"evenodd\"></path></svg></a></span>";
                if ($usrlvl < 3) {
                      echo "<span title=\"Επεξεργασία\"><a href=\"ektaktoi.php?id=$id&op=edit\" class=\"action-icon edit\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z\"></path></svg></a></span>";
                }
                if ($usrlvl < 2) {
                      echo "<span title=\"Διαγραφή\"><a href=\"javascript:confirmDelete('ektaktoi.php?id=$id&op=delete')\" class=\"action-icon delete\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path fill-rule=\"evenodd\" d=\"M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z\" clip-rule=\"evenodd\"></path></svg></a></span>";
                } else {
                    echo "<span title=\"Η διαγραφή μπορεί να γίνει μόνο από προϊστάμενο ή διαχειριστή\"><span class=\"action-icon delete disabled\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path fill-rule=\"evenodd\" d=\"M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z\" clip-rule=\"evenodd\"></path></svg></span></span>";
                }
                echo "</td>";
                //   $typos = get_type($type, $mysqlconnection);
                echo "<td><a class='underline' href=\"ektaktoi.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>".$sx_yphrethshs_url."</td>";//<td>$typos</td>
                echo "<td>$praxi</td>\n";
                echo "</tr>";

                $i++;
            }   
        }
        echo "</tbody>\n";
        if ($usrlvl < 2) {
            echo "<tr><td colspan=7 style='text-align: center; padding: 16px; background: linear-gradient(90deg, #f0fdf4 0%, #dcfce7 100%); border-top: 2px solid #22c55e;'><a href=\"ektaktoi.php?id=0&op=add\" style='color: #16a34a; font-weight: 600; font-size: 0.9375rem; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;'><img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/>➕ Προσθήκη αναπληρωτή εκπαιδευτικού</a></td></tr>";
        } else {
            echo "<tr><td colspan=7 style='text-align: center; padding: 16px; background: #f9fafb; color: #9ca3af;'><span title=\"Η προσθήκη μπορεί να γίνει μόνο από προϊστάμενο ή διαχειριστή\"><img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/>Προσθήκη αναπληρωτή εκπαιδευτικού</span></td></tr>";
        }
        echo "<tr><td colspan=7 class='pagination-row'>";
        $prevpg = $curpg-1;
        if ($lastpg == 0) {
            $curpg = 0;
        }
        echo "<div class='pagination-info'>Σελίδα <strong>$curpg</strong> από <strong>$lastpg</strong> (<strong>$num_record1</strong> εγγραφές)</div>";
        
        // Build query string for pagination links
        $getstring = "&rpp=$rpp";
        $getstring .= $klpost ? "&klados=$klpost" : '';
        $getstring .= $yppost ? "&yphr=$yppost" : '';
        $getstring .= $surpost ? "&surname=".urlencode($surpost) : '';
        $getstring .= $typepost ? "&type=$typepost" : '';
        $getstring .= $praxipost ? "&praxi=$praxipost" : '';
        
        echo "<div class='pagination-links'>";
        if ($curpg!=1) {
            echo "<a href='ektaktoi_list.php?page=1$getstring'>⏮️ Πρώτη</a>";
            echo "<a href='ektaktoi_list.php?page=$prevpg$getstring'>◀️ Προηγούμενη</a>";
        }
        else {
            echo "<span>⏮️ Πρώτη</span>";
            echo "<span>◀️ Προηγούμενη</span>";
        }
        if ($curpg != $lastpg) {
            $nextpg = $curpg+1;
            echo "<a href='ektaktoi_list.php?page=$nextpg$getstring'>Επόμενη ▶️</a>";
            echo "<a href='ektaktoi_list.php?page=$lastpg$getstring'>Τελευταία ⏭️</a>";
        }
        else { 
            echo "<span>Επόμενη ▶️</span>";
            echo "<span>Τελευταία ⏭️</span>";
        }
        echo "</div>";
        echo "<div class='pagination-form'>";
        echo "<FORM METHOD='POST' ACTION='ektaktoi_list.php?".$_SERVER['QUERY_STRING']."' style='display: flex; gap: 8px; align-items: center;'>";
        echo "<label style='font-weight: 500; color: #374151;'>Μετάβαση στη σελ.:</label>";
        echo "<input type=\"text\" name=\"page\" size=3 />";
        echo "<input type=\"submit\" value=\"➡️ Μετάβαση\" />";
        echo "</FORM>";
        echo "<FORM METHOD='POST' ACTION='ektaktoi_list.php?".$_SERVER['QUERY_STRING']."' style='display: flex; gap: 8px; align-items: center;'>";
        echo "<label style='font-weight: 500; color: #374151;'>Εγγρ./σελ.:</label>";
        echo "<input type=\"text\" name=\"rpp\" value=\"$rpp\" size=3 />";
        echo "<input type=\"submit\" value=\"✅ Ορισμός\" />";
        echo "</FORM>";
        echo "</div>";
        echo "</td></tr>";
        echo "<tr><td colspan=7 class='action-buttons-row'>";
        echo "<INPUT TYPE='button' VALUE='📝 Επεξεργασία Πράξεων' onClick=\"parent.location='praxi.php'\">";
        echo "<INPUT TYPE='button' VALUE='📊 Εκπαιδευτικοί & Σχολεία ανά Πράξη' onClick=\"parent.location='praxi_sch.php'\">";
        echo "</td></tr>";
        echo "<tr><td colspan=7 class='action-buttons-row'>";
        echo "<INPUT TYPE='button' VALUE='📅 Αναπληρωτές προηγούμενου έτους' onClick=\"parent.location='ektaktoi_prev.php'\">";
        echo "</td></tr>";
        echo "<tr><td colspan=7 class='action-buttons-row'>";
        echo "<INPUT TYPE='button' class='btn-red' VALUE='🏠 Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        echo "</td></tr>";
        echo "</table>\n";
        ?>  
        </div>
      </div>
</div>
  </body>
</html>
<?php
    mysqli_close($mysqlconnection);
?>
