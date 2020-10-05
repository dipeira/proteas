<?php
    header('Content-type: text/html; charset=utf-8'); 
    Require_once "config.php";
    Require_once "include/functions.php";
        
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

  require "tools/class.login.php";
  $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {   
    header("Location: tools/login.php");
}
else {
    $logged = 1;
}        
  $usrlvl = $_SESSION['userlevel'];
?>
<html>
  <head>
    <LINK href="css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Πρωτέας</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.validate.js"></script>
    <script type='text/javascript' src='js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
    <script type="text/javascript" src="js/jquery_notification_v.1.js"></script>
    <link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
    <script type="text/javascript" src="js/common.js"></script>
    <link href="css/jquery_notification.css" type="text/css" rel="stylesheet"/> 
    <script type="text/javascript">        
      $().ready(function() {
          $("#org").autocomplete("employee/get_school.php", {
            width: 260,
            matchContains: true,
            selectFirst: false
          });
          $("#yphr").autocomplete("employee/get_school.php", {
            width: 260,
            matchContains: true,
            selectFirst: false
          });
          $("#surname").autocomplete("employee/get_name.php", {
            width: 260,
            matchContains: true,
            //mustMatch: true,
            selectFirst: false
          });
          $("#surname").result(function(event, data, formatted) {
            if (data){
              $("#pinakas").val(data[1]);
            }
          });
          $("#mytbl").tablesorter({widgets: ['zebra']}); 
      });         
    </script>
    
  </head>
  <body> 
    <?php require 'etc/menu.php'; ?>
    <div>
<?php
  // notify admin to delete init.php if it exists
if ($usrlvl == 0) {
    if (file_exists('init.php')) {
        notify("ΠΡΟΣΟΧΗ: Παρακαλώ διαγράψτε το αρχείο <b>init.php</b> για λόγους ασφαλείας!</p>", 'error');
    }
}
if (isset($_POST['clearall'])) {
  $_POST = array();
  echo "<script>window.location = 'index.php'</script>";
}
    //rpp = results per page
if (isset($_POST['rpp'])) {
    $rpp = $_POST['rpp'];
} elseif (isset($_GET['rpp'])) {
    $rpp = $_GET['rpp'];
} else {
    $rpp= 20;
}

if (isset($_POST['page']) && $_POST['page']!=0) {
    $curpg = $_POST['page'];
} elseif (isset($_GET['page'])) { 
    $curpg = $_GET['page'];
} else {
    $curpg = 1;
}
    //limit in the query thing
    $limitQ = ' LIMIT ' .($curpg - 1) * $rpp .',' .$rpp;

    $query = "";
    
    $klpost = 0;
    $orgpost = 0;
    $yppost = 0;
    $whflag = 0;
    $posted = 0;
    $surpost = '';
if (isset($_POST['klados']) && ($_POST['klados']>0) || (isset($_POST['org']) && strlen($_POST['org'])>0) || 
(isset($_POST['yphr']) && strlen($_POST['yphr'])>0) || (isset($_POST['surname']) && strlen($_POST['surname'])>0)) {
    $posted=1;
    $curpg=1;
}
if ((isset($_POST['klados']) && $_POST['klados']>0) || (isset($_GET['klados']) && $_GET['klados']>0)) {
    if ($_POST['klados']>0) {
        $klpost = $_POST['klados'];
    } else {
        $klpost = $_GET['klados'];
    }
    $query .= "WHERE klados = $klpost ";
    $whflag=1;
}
if (isset($_REQUEST['org']) && ((strlen($_POST['org'])>0) || ($_GET['org']>0))) {
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
if (isset($_REQUEST['yphr']) && ((strlen($_POST['yphr'])>0) || ($_GET['yphr']>0))) {
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
        $whflag = 1;
    }
}
    // if ektaktos
if (isset($_REQUEST['surname']) && strlen($_REQUEST['surname'])>0 && $_POST['pinakas']==1) {
    $surn = explode(' ', $_POST['surname'])[0];
    $url = "employee/ektaktoi_list.php?surname=".urlencode($surn);
    echo "<script>window.location = '$url'</script>";
}
if (isset($_REQUEST['surname']) && (strlen($_POST['surname'])>0 || strlen($_GET['surname'])>0)) {
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
  // ΟΧΙ idiwtikoi
  if ($whflag) {
      $query .= " AND thesi NOT IN (5,6) ";
  } else {
      $query .= " WHERE thesi NOT IN (5,6) ";
      $whflag = 1;
  }

  // exclude employees that don't belong in d/nsh
  if (!isset($_REQUEST['outsiders'])) {
    $text = " NOT (sx_yphrethshs IN (388, 394) AND sx_organikhs IN (388,394))";
    if ($whflag) {
      $query .= " AND $text";
    } else {
      $query .= " WHERE $text";
      $whflag = 1;
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
    
    $query .= " ORDER BY surname ";
                    
    // Create queries...
    $q_main = "SELECT * FROM employee ". $query . $limitQ;
    $q_count = "SELECT count(*) as cnt FROM employee " . $query;

    /////////////// debug
    // echo $q_main;
    /////////////// 
    $result = mysqli_query($mysqlconnection, $q_main);
    $result1 = mysqli_query($mysqlconnection, $q_count);
    // Number of records found
if ($result) {
    $num_record = mysqli_num_rows($result);
}
if ($result1) {
    $num_record1 = mysqli_result($result1, 0, "cnt");
}

    $lastpg = ceil($num_record1 / $rpp);
            
if ($result) {
    $num=mysqli_num_rows($result);
}

    // added 24-01-2013 - when 1 result, redirect to that employee page
if ($num_record == 1 && $num_record1 > 1) {
    $id = mysqli_result($result, 0, "id");
    $url = "employee/employee.php?id=$id&op=view";
    echo "<script>window.location = '$url'</script>";
}
echo "<center><h2>Μόνιμοι Εκπαιδευτικοί</h2></center>";
if ($logged) {
  $se = getParam('sxol_etos', $mysqlconnection);
  $sx_etos = substr($se, 0, 4).'-'.substr($se, 4, 2);
  echo "<p class='userdata'>Ενεργός Χρήστης: ".$_SESSION['user']."&nbsp;&nbsp;-&nbsp;&nbsp;Σχολ.Έτος:&nbsp;$sx_etos</p>";
}
    echo "<center>";        
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
    echo "<thead>";
    echo "<tr><th>Ενέργεια</th>\n";
    echo "<th>Επώνυμο</th>\n";
    echo "<th>Όνομα</th>\n";
    echo "<th>Ειδικότητα</th>\n";
    echo "<th>Σχ.Οργανικής</th>\n";
    echo "<th>Σχ.Υπηρέτησης</th>\n";
    echo "</tr>\n\n";
   echo "<tr class='tablesorter-ignoreRow'><form id='src' name='src' action='index.php' method='POST'>\n";
if ($posted || 
      (isset($_REQUEST['klados']) && $_REQUEST['klados']>0) || 
      (isset($_REQUEST['org']) && $_REQUEST['org']>0) || 
      (isset($_REQUEST['yphr']) && $_REQUEST['yphr']>0) || 
      (isset($_REQUEST['surname']) && strlen($_REQUEST['surname'])>0) || 
      (isset($_REQUEST['outsiders'])) ||
      (isset($_REQUEST['inactive']))
    ) {
    echo "<input type='hidden' name='clearall' id='clearall' />";
    echo "<td rowspan=2><INPUT TYPE='submit' VALUE='Επαναφορά'></td><td>\n";
} else {    
    echo "<td rowspan=2><INPUT TYPE='submit' VALUE='Αναζήτηση'></td><td>\n";
}
    echo isset($_REQUEST['surname']) && strlen($_REQUEST['surname'])>0 ? "<input type='text' value='".$_REQUEST['surname']."' name='surname' id='surname''/>\n" : "<input type='text' name='surname' id='surname''/>\n";
    echo "<input type='hidden' name='pinakas' id='pinakas' />";
    echo "<td><span title='Ψάχνει σε μόνιμους & αναπληρωτές, εμφανίζοντας σε παρένθεση τη σχέση εργασίας'><small>(Σε μόνιμους<br> & αναπληρωτές)</small><img style=\"border: 0pt none;\" src=\"images/help.gif\" height='12' width='12'/></span></td></td><td>\n";
    echo $klpost > 0 ? kladosCombo($klpost, $mysqlconnection) : kladosCmb($mysqlconnection);
  //  kladosCmb($mysqlconnection);
    echo "</td>\n";
    echo "<div id=\"content\">";
    echo "<form autocomplete=\"off\">";
    echo "<td><input type=\"text\" name=\"org\" id=\"org\" /></td>";
    echo "<td><input type=\"text\" name=\"yphr\" id=\"yphr\" /></td>";
    echo "</div>";
    echo "</td>";
    echo "<tr>";
    $has_outsiders = isset($_REQUEST['outsiders']) ? 'checked' : '';
    echo "<td colspan=3><input type='checkbox' name = 'outsiders' $has_outsiders><small>Εμφάνιση και όσων δεν υπηρετούν και δεν ανήκουν στη Δ/νση</small></td>";	
    $has_inactive = isset($_REQUEST['inactive']) ? 'checked' : '';
    echo "<td colspan=2><input type='checkbox' name = 'inactive' $has_inactive><small>Εμφάνιση και όσων δεν εργάζονται (λύση σχέσης, διαθεσιμότητα)</small></small></td>";
    echo "</form></tr></thead>\n";
    
    echo "<tbody>\n";
if ($num == 0) {
    echo "<tr><td colspan=7><b><h3>Δε βρέθηκαν αποτελέσματα...</h3></b></td></tr>";
} else {
    $i = 0;
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
        $sx_organikhs_url = "<a href=\"school/school_status.php?org=$sx_organ_id\">$sx_organikhs</a>";
        $sx_yphrethshs_url = "<a href=\"school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a>";
        // check if multiple schools
        $qry = "select * from yphrethsh where emp_id=$id and sxol_etos=$sxol_etos";
        $res = mysqli_query($mysqlconnection, $qry);
        if (mysqli_num_rows($res) > 0) {
            $sx_yphrethshs .= "*";
        }
                  
        echo "<tr><td>";
        echo "<span title=\"Προβολή\"><a href=\"employee/employee.php?id=$id&op=view\"><img style=\"border: 0pt none;\" src=\"images/view_action.png\"/></a></span>&nbsp;&nbsp;";
        if ($usrlvl < 3) {
            echo "<span title=\"Επεξεργασία\"><a href=\"employee/employee.php?id=$id&op=edit\"><img style=\"border: 0pt none;\" src=\"images/edit_action.png\"/></a></span>&nbsp;&nbsp;";
        }
        if ($usrlvl < 2) {
            echo "<span title=\"Διαγραφή\"><a href=\"javascript:confirmDelete('employee/employee.php?id=$id&op=delete')\"><img style=\"border: 0pt none;\" src=\"images/delete_action.png\"/></a></span>";
        } else {
            echo "<span title=\"Η διαγραφή μπορεί να γίνει μόνο από προϊστάμενο ή διαχειριστή\"><img style=\"border: 0pt none;\" src=\"images/delete_action.png\"/></span>";
        }
        echo "</td>";
        echo "<td><a href=\"employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>".$sx_organikhs_url."</td><td>".$sx_yphrethshs_url."</td>\n";
        echo "</tr>";

        $i++;
    }  
} 
    echo "</tbody>\n";
    //echo "<tr><td colspan=7><input type='checkbox' name = 'outsiders'>Εμφάνιση και όσων δεν υπηρετούν ή ανήκουν στη Δ/νση;</td></tr>";
if ($usrlvl < 2) {
    echo "<tr><td colspan=7><span title=\"Προσθήκη\"><a href=\"employee/employee.php?op=add\"><img style=\"border: 0pt none;\" src=\"images/user_add.png\"/>Προσθήκη εκπαιδευτικού</a></span>";
} else {
    echo "<tr><td colspan=7><span title=\"Η προσθήκη μπορεί να γίνει μόνο από προϊστάμενο ή διαχειριστή\"><img style=\"border: 0pt none;\" src=\"images/user_add.png\"/>Προσθήκη εκπαιδευτικού</span></td></tr>";
}        
    echo "<tr><td colspan=7 align=center>";
    $prevpg = $curpg-1;
if ($lastpg == 0) {
    $curpg = 0;
}
    echo "Σελίδα $curpg από $lastpg ($num_record1 εγγραφές)<br>";
$outsiders = isset($_REQUEST['outsiders']) ? '&outsiders=1' : '';
$inactive = isset($_REQUEST['inactive']) ? '&inactive=1' : '';
if ($curpg!=1) {
    echo "  <a href=index.php?page=1&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost$outsiders$inactive>Πρώτη</a>";
    echo "&nbsp;&nbsp;  <a href=index.php?page=$prevpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost$outsiders$inactive>Προηγ/νη</a>";
}
else {
        echo "  Πρώτη &nbsp;&nbsp; Προηγ/νη";
}
if ($curpg != $lastpg) {
    $nextpg = $curpg+1;
    echo "&nbsp;&nbsp;  <a href=index.php?page=$nextpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost$outsiders$inactive>Επόμενη</a>";
    echo "&nbsp;&nbsp;  <a href=index.php?page=$lastpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost$outsiders$inactive>Τελευταία</a>";
}
else { 
        echo "  Επόμενη &nbsp;&nbsp; Τελευταία";
}
    echo "<FORM METHOD='POST' ACTION='index.php?".$_SERVER['QUERY_STRING']."'>";
    echo " Μετάβαση στη σελ.  <input type=\"text\" name=\"page\" size=1 />";
    echo "<input type=\"submit\" value=\"Μετάβαση\">";
    echo "<br>";
    echo "   Εγγρ./σελ.    <input type=\"text\" name=\"rpp\" value=\"$rpp\" size=1 />";
    echo "<input type=\"submit\" value=\"Ορισμός\">";
    echo "</FORM>";
    echo "</td></tr>";
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
