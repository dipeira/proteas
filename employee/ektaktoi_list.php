<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once"../tools/functions.php";
    
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
      
?>
<html>
  <head>
    
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Αναπληρωτές</title>
    
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
      
    $klpost = $yppost = $praxipost = 0;
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
    echo "<center>";
    echo "<h2>Αναπληρωτές</h2>";
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
    echo "<thead>";
    echo "<tr><th>Ενέργεια</th>\n";
    echo "<th>Επώνυμο</th>\n";
    echo "<th>Όνομα</th>\n";
    echo "<th>Ειδικότητα</th>\n";
    echo "<th>Σχ.Υπηρέτησης</th>\n";
    echo "<th>Τύπος Απασχόλησης</th>\n";
    echo "<th>Πράξη</th>\n";
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
        echo "<td><INPUT TYPE='submit' VALUE='Αναζήτηση' />"
            . "<br><center><button type='button' id='resetBtn' style='margin: 3px'><small>Επαναφορά</small></button></center>"
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
        //echo "</td>";
        echo $typepost ? typeCmb1($typepost, $mysqlconnection) : typeCmb($mysqlconnection);
        echo "</td>";
        echo "<td>";
        tblCmb($mysqlconnection, 'praxi', $praxipost, 'praxi', 'name');
        echo "</td>";
        echo "</form></tr>\n";
    
        echo "</thead>\n";
    
        echo "<tbody>\n";
        if ($num == 0) {
            echo "<tr><td colspan=7><b><h3>Δε βρέθηκαν αποτελέσματα...</h3></b></td></tr>";
        } else {
            while ($i < $num)
            {
    
                $id = mysqli_result($result, $i, 0);
                $name = mysqli_result($result, $i, "name");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $sx_yphrethshs_id = mysqli_result($result, $i, "sx_yphrethshs");
                $sx_yphrethshs = getSchool($sx_yphrethshs_id, $mysqlconnection);
                $sx_yphrethshs_url = "<a href=\"../school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a>";
                $type = mysqli_result($result, $i, "type");
                $praxi = mysqli_result($result, $i, "praxi");
                $praxi = getNamefromTbl($mysqlconnection, "praxi", $praxi);
                  
                echo "<tr><td>";
                echo "<span title=\"Προβολή\"><a href=\"ektaktoi.php?id=$id&op=view\"><img style=\"border: 0pt none;\" src=\"../images/view_action.png\"/></a></span>&nbsp;&nbsp;";
                if ($usrlvl < 3) {
                      echo "<span title=\"Επεξεργασία\"><a href=\"ektaktoi.php?id=$id&op=edit\"><img style=\"border: 0pt none;\" src=\"../images/edit_action.png\"/></a></span>&nbsp;&nbsp;";
                }
                if ($usrlvl < 2) {
                      echo "<span title=\"Διαγραφή\"><a href=\"javascript:confirmDelete('ektaktoi.php?id=$id&op=delete')\"><img style=\"border: 0pt none;\" src=\"../images/delete_action.png\"/></a></span>";
                }
                echo "</td>";
                  $typos = get_type($type, $mysqlconnection);
                echo "<td><a href=\"ektaktoi.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>".$sx_yphrethshs_url."</td><td>$typos</td><td>$praxi</td>\n";
                echo "</tr>";

                $i++;
            }   
        }
        echo "</tbody>\n";
        if ($usrlvl < 2) {
            echo "<tr><td colspan=7><span title=\"Προσθήκη\"><a href=\"ektaktoi.php?id=0&op=add\"><img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/>Προσθήκη αναπληρωτή εκπαιδευτικού</a></span>";
        }        
        if ($usrlvl == 0) {
            echo "<tr><td colspan=7><span title=\"Προσθήκη\"></span>";
        }
        echo "<tr><td colspan=7 align=center>";
        $prevpg = $curpg-1;
        if ($lastpg == 0) {
            $curpg = 0;
        }
        echo "Σελίδα $curpg από $lastpg ($num_record1 εγγραφές)<br>";
        if ($curpg!=1) {
            echo "  <a href=ektaktoi_list.php?page=1&rpp=$rpp&klados=$klpost&praxi=$praxipost&yphr=$yppost&klados=$klpost&type=$typepost&surname=$surpost>Πρώτη</a>";
            echo "&nbsp;&nbsp;  <a href=ektaktoi_list.php?page=$prevpg&rpp=$rpp&klados=$klpost&praxi=$praxipost&yphr=$yppost&klados=$klpost&type=$typepost&surname=$surpost>Προηγ/νη</a>";
        }
        else {
            echo "  Πρώτη &nbsp;&nbsp; Προηγ/νη";
        }
        if ($curpg != $lastpg) {
            $nextpg = $curpg+1;
            echo "&nbsp;&nbsp;  <a href=ektaktoi_list.php?page=$nextpg&rpp=$rpp&klados=$klpost&praxi=$praxipost&yphr=$yppost&klados=$klpost&type=$typepost&surname=$surpost>Επόμενη</a>";
            echo "&nbsp;&nbsp;  <a href=ektaktoi_list.php?page=$lastpg&rpp=$rpp&klados=$klpost&praxi=$praxipost&yphr=$yppost&klados=$klpost&type=$typepost&surname=$surpost>Τελευταία</a>";
        }
        else { 
            echo "  Επόμενη &nbsp;&nbsp; Τελευταία";
        }
        echo "<FORM METHOD='POST' ACTION='ektaktoi_list.php?".$_SERVER['QUERY_STRING']."'>";
        echo " Μετάβαση στη σελ.  <input type=\"text\" name=\"page\" size=1 />";
        echo "<input type=\"submit\" value=\"Μετάβαση\">";
        echo "<br>";
        echo "   Εγγρ./σελ.    <input type=\"text\" name=\"rpp\" value=\"$rpp\" size=1 />";
        echo "<input type=\"submit\" value=\"Ορισμός\">";
        echo "</FORM>";
        echo "</td></tr>";
        echo "<tr><td colspan=7><INPUT TYPE='button' VALUE='Επεξεργασία Πράξεων' onClick=\"parent.location='praxi.php'\">";
        echo "&nbsp;&nbsp;&nbsp;";
        echo "<INPUT TYPE='button' VALUE='Εκπαιδευτικοί & Σχολεία ανά Πράξη' onClick=\"parent.location='praxi_sch.php'\">";
        //echo "&nbsp;&nbsp;&nbsp;";
        //echo "<INPUT TYPE='button' VALUE='Πράξεις ανά Σχολείο' onClick=\"parent.location='praxi_sch2.php'\">";
        echo "</td></tr>";
        echo "<tr><td colspan=7><INPUT TYPE='button' VALUE='Αναπληρωτές προηγούμενου έτους' onClick=\"parent.location='ektaktoi_prev.php'\"></td></tr>";
        echo "<tr><td colspan=7><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\"></td></tr>";
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
