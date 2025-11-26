<?php
    header('Content-type: text/html; charset=utf-8'); 
    Require_once "../config.php";
    Require_once "../include/functions.php";
        
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
  $usrlvl = $_SESSION['userlevel'];
  $sxol_etos = getParam('sxol_etos', $mysqlconnection);
?>
<html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Μόνιμοι Εκπαιδευτικοί';
    require '../etc/head.php'; 
    ?>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.js"></script>
    <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type="text/javascript" src="../js/jquery_notification_v.1.js"></script>
    <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
    <script type="text/javascript" src="../js/common.js"></script>
    <link href="../css/jquery_notification.css" type="text/css" rel="stylesheet"/>
    <style>
        /* Index Page Styling */
        body {
            padding: 20px;
        }
        
        .page-header {
            text-align: center;
            margin: 20px 0 30px 0;
        }
        
        .page-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }
        
        .user-info {
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
            padding: 10px 16px;
            border-radius: 8px;
            display: inline-block;
            margin: 12px 0;
            font-size: 0.875rem;
            color: #1e40af;
            font-weight: 500;
            border: 1px solid #4FC5D6;
        }
        
        /* Search form row styling */
        .tablesorter-ignoreRow {
            background: #f0f9ff !important;
        }
        
        .tablesorter-ignoreRow td {
            background: #f0f9ff !important;
            padding: 12px !important;
            border: 1px solid #bae6fd !important;
        }
        
        .tablesorter-ignoreRow input[type="text"],
        .tablesorter-ignoreRow select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .tablesorter-ignoreRow input[type="text"]:focus,
        .tablesorter-ignoreRow select:focus {
            outline: none;
            border-color: #4FC5D6;
            box-shadow: 0 0 0 3px rgba(79, 197, 214, 0.1);
        }
        
        .tablesorter-ignoreRow input[type="submit"] {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(79, 197, 214, 0.3);
            width: 100%;
        }
        
        .tablesorter-ignoreRow input[type="submit"]:hover {
            background: linear-gradient(135deg, #3BA8B8 0%, #2A8B9A 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(79, 197, 214, 0.4);
        }
        
        .tablesorter-ignoreRow input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #4FC5D6;
            cursor: pointer;
            margin-right: 8px;
        }
        
        .tablesorter-ignoreRow small {
            color: #6b7280;
            font-size: 0.8125rem;
            line-height: 1.5;
        }
        
        /* Table header styling */
        .imagetable thead th {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 50%, #2A8B9A 100%) !important;
            color: white !important;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 16px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        
        /* Table rows */
        .imagetable tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .imagetable tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .imagetable tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .imagetable tbody tr:nth-child(even):hover {
            background-color: #f3f4f6;
        }
        
        /* Action column styling */
        .imagetable tbody td:first-child {
            text-align: center;
            padding: 12px 8px;
            vertical-align: middle;
        }
        
        .imagetable tbody td:first-child a {
            display: inline-block;
            margin: 0 4px;
            transition: transform 0.2s;
        }
        
        .imagetable tbody td:first-child a:hover {
            transform: scale(1.15);
        }
        
        .imagetable tbody td:first-child img {
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
        }
        
        /* Data cells */
        .imagetable tbody td {
            padding: 12px 16px;
            color: #374151;
            vertical-align: middle;
        }
        
        .imagetable tbody td a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .imagetable tbody td a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        
        /* "No results" message */
        .imagetable tbody td[colspan] h3 {
            color: #6b7280;
            font-size: 1.125rem;
            font-weight: 600;
            padding: 20px;
            text-align: center;
        }
        
        /* Add employee row */
        .imagetable tbody tr:has(td[colspan]:contains("Προσθήκη")) {
            background: linear-gradient(90deg, #f0fdf4 0%, #dcfce7 100%);
            border-top: 2px solid #22c55e;
        }
        
        .imagetable tbody tr:has(td[colspan]:contains("Προσθήκη")) td {
            padding: 16px;
            text-align: center;
        }
        
        .imagetable tbody tr:has(td[colspan]:contains("Προσθήκη")) a {
            color: #16a34a;
            font-weight: 600;
            font-size: 0.9375rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.2s;
        }
        
        .imagetable tbody tr:has(td[colspan]:contains("Προσθήκη")) a:hover {
            color: #15803d;
        }
        
        /* Pagination row */
        .pagination-row {
            background: #f9fafb;
            padding: 16px;
            text-align: center;
            border-top: 2px solid #e5e7eb;
        }
        
        .pagination-info {
            margin-bottom: 12px;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .pagination-links {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin: 12px 0;
        }
        
        .pagination-links a {
            color: #4FC5D6;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 6px;
            background: white;
            border: 1px solid #bae6fd;
            transition: all 0.2s;
        }
        
        .pagination-links a:hover {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
            color: white;
            border-color: #4FC5D6;
            transform: translateY(-1px);
        }
        
        .pagination-links span {
            color: #9ca3af;
            padding: 8px 16px;
        }
        
        .pagination-form {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .pagination-form input[type="text"] {
            width: 60px;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            text-align: center;
        }
        
        .pagination-form input[type="submit"] {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .pagination-form input[type="submit"]:hover {
            background: linear-gradient(135deg, #3BA8B8 0%, #2A8B9A 100%);
            transform: translateY(-1px);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-header h2 {
                font-size: 1.5rem;
            }
            
            .pagination-links {
                flex-direction: column;
                gap: 8px;
            }
            
            .pagination-form {
                flex-direction: column;
            }
        }
    </style>
    <script type="text/javascript">        
      $().ready(function() {
          $("#org").autocomplete("get_school.php", {
            width: 260,
            matchContains: true,
            selectFirst: false
          });
          $("#yphr").autocomplete("get_school.php", {
            width: 260,
            matchContains: true,
            selectFirst: false
          });
          $("#surname").autocomplete("get_name.php", {
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
    <?php require '../etc/menu.php'; ?>
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
  echo "<script>window.location = 'monimoi_list.php'</script>";
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
if (isset($_REQUEST['klados']) && strlen($_REQUEST['klados'])) {
    if (isset($_POST['klados']) && $_POST['klados']>0) {
        $klpost = $_POST['klados'];
    }  
    if (isset($_GET['klados']) && $_GET['klados']>0){
        $klpost = $_GET['klados'];
    }
    $query .= "WHERE klados = $klpost ";
    $whflag=1;
    
}
if ( isset($_REQUEST['org']) && strlen($_REQUEST['org']) ) {
    if (isset ($_POST['org']) && strlen($_POST['org'])>0) {
        $orgpost = getSchoolID($_POST['org'], $mysqlconnection);
    }
    if (isset($_GET['org']) && $_GET['org']>0) {
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
if ( isset($_REQUEST['yphr']) && strlen($_REQUEST['yphr'])>0) {
    if (isset($_POST['yphr']) && strlen($_POST['yphr'])>0) {
        $yppost = getSchoolID($_POST['yphr'], $mysqlconnection);
    }
    if (isset($_GET['yphr']) && $_GET['yphr']>0) {
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
if (isset($_REQUEST['surname']) && strlen($_REQUEST['surname'])>0 && isset($_POST['pinakas']) && $_POST['pinakas']==1) {
    $surn = explode(' ', $_POST['surname'])[0];
    $url = "ektaktoi_list.php?surname=".urlencode($surn);
    echo "<script>window.location = '$url'</script>";
}
if ( isset($_REQUEST['surname']) && strlen($_REQUEST['surname'])>0) {
    if (isset($_POST['surname']) && strlen($_POST['surname'])>0) {
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
    $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ',$mysqlconnection);
    $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ',$mysqlconnection);
    $text = " NOT (sx_yphrethshs IN ($allo_pyspe, $allo_pysde) AND sx_organikhs IN ($allo_pyspe, $allo_pysde))";
    if ($whflag) {
      $query .= " AND $text";
    } else {
      $query .= " WHERE $text";
      $whflag = 1;
    }
  }
  // include inactive employees
  if (!isset($_REQUEST['inactive'])) {
    $text = " status IN (1,3,5)";
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
    $url = "employee.php?id=$id&op=view";
    echo "<script>window.location = '$url'</script>";
}
echo "<div style='margin: 0 auto; padding: 0 20px;'>";
echo "<div class='page-header'>";
echo "<h2>Μόνιμοι Εκπαιδευτικοί</h2>";

echo "</div>";
echo "<div style='display: flex; justify-content: center;'>";        
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
    echo "<thead>";
    echo "<tr><th>Ενεργεια</th>\n";
    echo "<th>Επωνυμο</th>\n";
    echo "<th>Ονομα</th>\n";
    echo "<th>Ειδικοτητα</th>\n";
    echo "<th>Σχ.Οργανικης</th>\n";
    echo "<th>Σχ.Υπηρετησης</th>\n";
    echo "</tr>\n\n";
   echo "<tr class='tablesorter-ignoreRow'><form id='src' name='src' action='monimoi_list.php' method='POST'>\n";
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
    echo "<td><span title='Ψάχνει σε μόνιμους & αναπληρωτές, εμφανίζοντας σε παρένθεση τη σχέση εργασίας'><small>(Σε μόνιμους<br> & αναπληρωτές)</small><img style=\"border: 0pt none;\" src=\"../images/help.gif\" height='12' width='12'/></span></td></td><td>\n";
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
    echo "<td colspan=3 style='padding: 10px 16px;'><label style='display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.875rem;'><input type='checkbox' name='outsiders' $has_outsiders style='width: 18px; height: 18px; accent-color: #4FC5D6; cursor: pointer;'><span>Εμφάνιση και όσων δεν υπηρετούν και δεν ανήκουν στη Δ/νση</span></label></td>";	
    $has_inactive = isset($_REQUEST['inactive']) ? 'checked' : '';
    echo "<td colspan=2 style='padding: 10px 16px;'><label style='display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.875rem;'><input type='checkbox' name='inactive' $has_inactive style='width: 18px; height: 18px; accent-color: #4FC5D6; cursor: pointer;'><span>Εμφάνιση και όσων δεν εργάζονται (λύση σχέσης, διαθεσιμότητα)</span></label></td>";
    echo "</form></tr></thead>\n";
    
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
        $sx_organ_id = mysqli_result($result, $i, "sx_organikhs");
        $sx_organikhs = getSchool($sx_organ_id, $mysqlconnection);
        $sx_yphrethshs_id = mysqli_result($result, $i, "sx_yphrethshs");
        $sx_yphrethshs = getSchool($sx_yphrethshs_id, $mysqlconnection);
        $sx_organikhs_url = "<a class='underline' href=\"../school/school_status.php?org=$sx_organ_id\">$sx_organikhs</a>";
        $sx_yphrethshs_url = "<a class='underline' href=\"../school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a>";
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
        } else {
            echo "<span title=\"Η διαγραφή μπορεί να γίνει μόνο από προϊστάμενο ή διαχειριστή\"><span class=\"action-icon delete disabled\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path fill-rule=\"evenodd\" d=\"M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z\" clip-rule=\"evenodd\"></path></svg></span></span>";
        }
        echo "</td>";
        echo "<td><a class='underline' href=\"employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>".$sx_organikhs_url."</td><td>".$sx_yphrethshs_url."</td>\n";
        echo "</tr>";

        $i++;
    }  
} 
    echo "</tbody>\n";
    //echo "<tr><td colspan=7><input type='checkbox' name = 'outsiders'>Εμφάνιση και όσων δεν υπηρετούν ή ανήκουν στη Δ/νση;</td></tr>";
if ($usrlvl < 2) {
    echo "<tr><td colspan=7 style='text-align: center; padding: 16px; background: linear-gradient(90deg, #f0fdf4 0%, #dcfce7 100%); border-top: 2px solid #22c55e;'><a href=\"employee.php?op=add\" style='color: #16a34a; font-weight: 600; font-size: 0.9375rem; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;'><img style=\"border: 0pt none;\" src=\"images/user_add.png\"/>➕ Προσθήκη εκπαιδευτικού</a></td></tr>";
} else {
    echo "<tr><td colspan=7 style='text-align: center; padding: 16px; background: #f9fafb; color: #9ca3af;'><span title=\"Η προσθήκη μπορεί να γίνει μόνο από προϊστάμενο ή διαχειριστή\"><img style=\"border: 0pt none;\" src=\"images/user_add.png\"/>Προσθήκη εκπαιδευτικού</span></td></tr>";
}        
    echo "<tr><td colspan=7 class='pagination-row'>";
    $prevpg = $curpg-1;
if ($lastpg == 0) {
    $curpg = 0;
}
    echo "<div class='pagination-info'>Σελίδα <strong>$curpg</strong> από <strong>$lastpg</strong> (<strong>$num_record1</strong> εγγραφές)</div>";
$outsiders = isset($_REQUEST['outsiders']) ? '&outsiders=1' : '';
$inactive = isset($_REQUEST['inactive']) ? '&inactive=1' : '';
$getstring = "&rpp=$rpp";
$getstring .= $klpost ? "&klados=$klpost" : '';
$getstring .= $yppost ? "&yphr=$yppost" : '';
$getstring .= $surpost ? "&surname=$surpost" : '';
$getstring .= strlen($outsiders)>0 ? $outsiders : '';
$getstring .= strlen($inactive)>0 ? $inactive : '';
echo "<div class='pagination-links'>";
if ($curpg!=1) {
    echo "<a href='monimoi_list.php?page=1$getstring'>⏮️ Πρώτη</a>";
    echo "<a href='monimoi_list.php?page=$prevpg$getstring'>◀️ Προηγούμενη</a>";
}
else {
        echo "<span>⏮️ Πρώτη</span>";
        echo "<span>◀️ Προηγούμενη</span>";
}
if ($curpg != $lastpg) {
    $nextpg = $curpg+1;
    echo "<a href='monimoi_list.php?page=$nextpg$getstring'>Επόμενη ▶️</a>";
    echo "<a href='monimoi_list.php?page=$lastpg$getstring'>Τελευταία ⏭️</a>";
}
else { 
        echo "<span>Επόμενη ▶️</span>";
        echo "<span>Τελευταία ⏭️</span>";
}
echo "</div>";
    echo "<div class='pagination-form'>";
    echo "<FORM METHOD='POST' ACTION='monimoi_list.php?".$_SERVER['QUERY_STRING']."' style='display: flex; gap: 8px; align-items: center;'>";
    echo "<label style='font-weight: 500; color: #374151;'>Μετάβαση στη σελ.:</label>";
    echo "<input type=\"text\" name=\"page\" size=3 />";
    echo "<input type=\"submit\" value=\"➡️ Μετάβαση\" />";
    echo "</FORM>";
    echo "<FORM METHOD='POST' ACTION='monimoi_list.php?".$_SERVER['QUERY_STRING']."' style='display: flex; gap: 8px; align-items: center;'>";
    echo "<label style='font-weight: 500; color: #374151;'>Εγγρ./σελ.:</label>";
    echo "<input type=\"text\" name=\"rpp\" value=\"$rpp\" size=3 />";
    echo "<input type=\"submit\" value=\"✅ Ορισμός\" />";
    echo "</FORM>";
    echo "</div>";
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

