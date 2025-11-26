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
$idiwtikoi = true;
?>
<html>
  <head>
  <?php 
    $root_path = '../';
    $page_title = 'Ιδιωτικοί εκπαιδευτικοί';
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
            $("#org").autocomplete("get_school.php", {
                width: 260,
                matchContains: true,
                selectFirst: false
            });
        });
        $().ready(function() {
            $("#yphr").autocomplete("get_school.php", {
                width: 260,
                matchContains: true,
                selectFirst: false
            });
        });
        $().ready(function() {
            $("#surname").autocomplete("get_name.php", {
                extraParams: {idiwtikoi: <?php echo $idiwtikoi; ?>},
                width: 260,
                matchContains: true,
                selectFirst: false
            });
        });
        
        $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
        }); 
    </script>
    
  </head>
  <body> 
    <?php require '../etc/menu.php'; ?>
    
<div>
<?php
        $usrlvl = $_SESSION['userlevel'];

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

        //$query = "SELECT * FROM employee ORDER BY surname ";
        $query = "SELECT * FROM employee ";
          
        $klpost = 0;
        $orgpost = 0;
        $yppost = 0;
        if (($_POST['klados']>0) || (strlen($_POST['org'])>0) || (strlen($_POST['yphr'])>0) || (strlen($_POST['surname'])>0)) {
            $posted=1;
            $curpg=1;
        }
        if (($_POST['klados']>0) || ($_GET['klados']>0)) {
            if ($_POST['klados']>0) {
                $klpost = $_POST['klados'];
            } else {
                $klpost = $_GET['klados'];
            }
            $query .= "WHERE klados = $klpost ";
            $whflag=1;
        }
        if ((strlen($_POST['org'])>0) || ($_GET['org']>0)) {
            if (strlen($_POST['org'])>0) {
                $orgpost = getSchoolID($_POST['org'], $mysqlconnection);
            }
            if ($_GET['org']>0) {
                $orgpost = $_GET['org'];
            }
            if ($whflag) {
                $query .= "AND sx_organikhs = $orgpost ";
            } else
            {
                $query .= "WHERE sx_organikhs = $orgpost ";
                $whflag=1;
            }    
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
              $surpost = explode(' ', $_POST['surname'])[0];
          } else {
              $surpost = $_GET['surname'];
          }
            
            if ($whflag) {
                $query .= "AND surname LIKE '%$surpost%' ";
            } else
            {
                $query .= "WHERE surname LIKE '%$surpost%' ";
                $whflag=1;
            }
        }

        // include inactive employees
        if (!isset($_REQUEST['inactive'])) {
          $text = " status IN (1,3)";
          if ($whflag) {
            $query .= " AND $text";
          } else {
            $query .= " WHERE $text";
            $whflag = 1;
          }
        }
                        
        // Mono idiwtikoi
        if ($whflag) {
            $query .= " AND thesi in (5,6) ";
        } else {
            $query .= " WHERE thesi in (5,6) ";
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
    
        echo "<center>";  
        echo "<h2>Ιδιωτικοί εκπαιδευτικοί</h2>";
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead>";
        echo "<tr><th>Ενέργεια</th>\n";
        echo "<th>Επώνυμο</th>\n";
        echo "<th>Όνομα</th>\n";
        echo "<th>Ειδικότητα</th>\n";
        echo "<th>Σχ.Οργανικής</th>\n";
        echo "<th>Σχ.Υπηρέτησης</th>\n";
        echo "</tr>";
        echo "<tr><form id='src' name='src' action='' method='POST'>\n";
        if ($posted || ($_GET['klados']>0) || ($_GET['org']>0) || ($_GET['yphr']>0)) {
            echo "<td><INPUT TYPE='submit' VALUE='Επαναφορά'></td><td>\n";
        } else {    
            echo "<td><INPUT TYPE='submit' VALUE='Αναζήτηση'></td><td>\n";
        }
        echo "<input type='text' name='surname' id='surname''/>\n";
        echo "<td></td></td><td>\n";
        kladosCmb($mysqlconnection);
        echo "</td>\n";
        echo "<div id=\"content\">";
        echo "<form autocomplete=\"off\">";
        echo "<td><input type=\"text\" name=\"org\" id=\"org\" /></td>";
        echo "<td><input type=\"text\" name=\"yphr\" id=\"yphr\" /></td>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        $has_inactive = isset($_REQUEST['inactive']) ? 'checked' : '';
        echo "<td colspan=6><input type='checkbox' name = 'inactive' $has_inactive><small>Εμφάνιση και όσων δεν εργάζονται (λύση σχέσης)</small></small></td>";
        echo "</form></tr>\n";
        
        echo "</thead>\n";
    
        echo "<tbody>\n";
    
        while ($i < $num)
        {
    
            $id = mysqli_result($result, $i, "id");
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            $sx_organ_id = mysqli_result($result, $i, "sx_organikhs");
            $sx_organikhs = getSchool($sx_organ_id, $mysqlconnection);
            $sx_yphrethshs_id = mysqli_result($result, $i, "sx_yphrethshs");
            $sx_yphrethshs = getSchool($sx_yphrethshs_id, $mysqlconnection);
            $sx_organikhs_url = "<a href=\"../school/school_status.php?org=$sx_organ_id\">$sx_organikhs</a>";
            $sx_yphrethshs_url = "<a href=\"../school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a>";
            // check if multiple schools
            $qry = "select * from yphrethsh where emp_id=$id and sxol_etos=$sxol_etos";
            $res = mysqli_query($mysqlconnection, $qry);
            if (mysqli_num_rows($res) > 0) {
                $sx_yphrethshs .= "*";
            }
                                
            echo "<tr><td>";
            echo "<span title=\"Προβολή\"><a href=\"employee.php?id=$id&op=view\" class=\"action-icon view\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M10 12a2 2 0 100-4 2 2 0 000 4z\"></path><path fill-rule=\"evenodd\" d=\"M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z\" clip-rule=\"evenodd\"></path></svg></a></span>";
            if ($usrlvl < 3) {
                    echo "<span title=\"Επεξεργασία\"><a href=\"employee.php?id=$id&op=edit\" class=\"action-icon edit\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z\"></path></svg></a></span>";
            }
            if ($usrlvl < 2) {
                    echo "<span title=\"Διαγραφή\"><a href=\"javascript:confirmDelete('employee.php?id=$id&op=delete')\" class=\"action-icon delete\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path fill-rule=\"evenodd\" d=\"M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z\" clip-rule=\"evenodd\"></path></svg></a></span>";
            }
            echo "</td>";
            echo "<td><a href=\"employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>".$sx_organikhs_url."</td><td>".$sx_yphrethshs_url."</td>\n";
            echo "</tr>";

            $i++;
        }   
        echo "</tbody>\n";
        if ($usrlvl < 2) {
            echo "<tr><td colspan=7><span title=\"Προσθήκη\"><a href=\"employee.php?id=$id&op=add\"><img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/>Προσθήκη εκπαιδευτικού</a></span>";
        }        
        echo "<tr><td colspan=7 align=center>";
        $prevpg = $curpg-1;
        if ($lastpg == 0) {
            $curpg = 0;
        }
        echo "Σελίδα $curpg από $lastpg ($num_record1 εγγραφές)<br>";
        $inactive = isset($_REQUEST['inactive']) ? '&inactive=1' : '';
        if ($curpg!=1) {
            echo "  <a href=idiwtikoi.php?page=1&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost&inactive>Πρώτη</a>";
            echo "&nbsp;&nbsp;  <a href=idiwtikoi.php?page=$prevpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost$inactive>Προηγ/νη</a>";
        }
        else {
            echo "  Πρώτη &nbsp;&nbsp; Προηγ/νη";
        }
        if ($curpg != $lastpg) {
            $nextpg = $curpg+1;
            echo "&nbsp;&nbsp;  <a href=idiwtikoi.php?page=$nextpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost$inactive>Επόμενη</a>";
            echo "&nbsp;&nbsp;  <a href=idiwtikoi.php?page=$lastpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost$inactive>Τελευταία</a>";
        }
        else { 
            echo "  Επόμενη &nbsp;&nbsp; Τελευταία";
        }
        echo "<FORM METHOD=\"POST\" ACTION=\"idiwtikoi.php\">";
        echo " Μετάβαση στη σελ.  <input type=\"text\" name=\"page\" size=1 />";
        echo "<input type=\"submit\" value=\"Μετάβαση\">";
                echo "<br>";
                echo "   Εγγρ./σελ.    <input type=\"text\" name=\"rpp\" value=\"$rpp\" size=1 />";
        echo "<input type=\"submit\" value=\"Ορισμός\">";
        echo "</FORM>";
        echo "</td></tr>";
                echo "<tr><td colspan=6><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\"></td></tr>";
        echo "</table>\n";
        ?>
      
      <br><br>
     
    </center>
</div>
  </body>
</html>
<?php
    mysqli_close($mysqlconnection);
?>
