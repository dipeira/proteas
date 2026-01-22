<?php
  require_once "../config.php";
  require_once '../include/functions.php';
  header('Content-type: text/html; charset=utf-8'); 
?>
<html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Εισαγωγή δεδομένων από αρχείο';
    require '../etc/head.php'; 
    ?>
	  <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <style>
      .import-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 30px;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      }
      
      .import-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
      }
      
      .import-section {
        margin: 30px 0;
        padding: 25px;
        background: #f9fafb;
        border-radius: 12px;
        border-left: 4px solid #4FC5D6;
      }
      
      .import-section h4 {
        margin-top: 0;
        color: #1f2937;
        font-weight: 600;
      }
      
      #mytbl.import-table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin: 20px 0;
        table-layout: fixed;
      }
      
      #mytbl.import-table thead th {
        background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
        color: #ffffff;
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
        border: none;
      }
      
      #mytbl.import-table thead th:first-child {
        width: 200px;
      }
      
      #mytbl.import-table thead th:last-child {
        width: auto;
      }
      
      #mytbl.import-table tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
        word-wrap: break-word;
      }
      
      #mytbl.import-table tbody tr:last-child td {
        border-bottom: none;
      }
      
      #mytbl.import-table tbody tr:hover {
        background: #f0f9ff;
      }
      
      #mytbl.import-table tbody td:first-child {
        font-weight: 600;
        color: #1f2937;
        background: #f3f4f6;
        width: 200px;
        vertical-align: middle;
        text-align: center;
        padding: 20px;
      }
      
      #mytbl.import-table tbody td.rowspan-cell {
        font-weight: 600;
        color: #1f2937;
        background: #f3f4f6;
        vertical-align: middle;
        text-align: center;
        padding: 20px;
        border-right: 2px solid #e5e7eb;
      }
      
      #mytbl.import-table tbody td:last-child {
        color: #374151;
        vertical-align: middle;
        padding-left: 20px;
      }
      
      .import-choice {
        margin-right: 12px;
        cursor: pointer;
        width: 18px;
        height: 18px;
        accent-color: #4FC5D6;
      }
      
      .import-choice-label {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 6px;
        transition: all 0.2s ease;
        cursor: pointer;
        width: 100%;
        margin: 4px 0;
      }
      
      .import-choice-label:hover {
        background: #e0f7fa;
      }
      
      .import-choice-label input[type="radio"] {
        margin-right: 10px;
        flex-shrink: 0;
      }
      
      .file-upload-section {
        margin: 30px 0;
        padding: 25px;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f7fa 100%);
        border-radius: 12px;
        border: 2px dashed #4FC5D6;
      }
      
      .file-input-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
        margin: 15px 0;
      }
      
      input[type="file"] {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: #ffffff;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
      }
      
      input[type="file"]:hover {
        border-color: #4FC5D6;
        box-shadow: 0 0 0 3px rgba(79, 197, 214, 0.1);
      }
      
      .warning-box {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 15px 20px;
        border-radius: 8px;
        margin: 20px 0;
        color: #92400e;
      }
      
      .info-box {
        background: #dbeafe;
        border-left: 4px solid #3b82f6;
        padding: 15px 20px;
        border-radius: 8px;
        margin: 20px 0;
        color: #1e40af;
        font-size: 13px;
        line-height: 1.6;
      }
      
      .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #e5e7eb;
      }
      
      .link-sample {
        color: #4FC5D6;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
      }
      
      .link-sample:hover {
        color: #3BA8B8;
        text-decoration: underline;
      }
      
      .result-message {
        padding: 20px;
        border-radius: 12px;
        margin: 20px 0;
      }
      
      .result-success {
        background: #d1fae5;
        border-left: 4px solid #10b981;
        color: #065f46;
      }
      
      .result-error {
        background: #fee2e2;
        border-left: 4px solid #ef4444;
        color: #991b1b;
      }
      
      .result-warning {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        color: #92400e;
      }
    </style>
  </head>
  <body>

<?php
  //session_start();

 
  
  include_once("class.login.php");
  $log = new logmein();
  // if not logged in or not admin
  //if($log->logincheck($_SESSION['loggedin']) == false || $_SESSION['user'] != $av_admin)
  if($_SESSION['loggedin'] == false)
  {   
      header("Location: login.php");
  }
  else
      $loggedin = 1;

  // check if admin
  if ($_SESSION['userlevel'] > 0)
  {
    echo "<br><br><h3>Δεν έχετε δικαίωμα για την πραγματοποίηση αυτής της ενέργειας. Επικοινωνήστε με το διαχειριστή σας.</h3>";
    die();
  }
  
  if (!isset($_POST['submit']))
  {
    require '../etc/menu.php';
    echo "<div class='import-container'>";
    echo "<div class='import-header'>";
    echo "<h2>Εισαγωγή δεδομένων στη βάση δεδομένων</h2>";
    echo "</div>";
    
    echo "<form enctype='multipart/form-data' action='import.php' method='post'>";
    
    echo "<div class='import-section'>";
    echo "<h4>Οδηγίες</h4>";
    echo "<p>Κατεβάστε το δείγμα των δεδομένων που θέλετε να εισάγετε και αφού του προσθέσετε δεδομένα εισάγετε το.</p>";
    echo "</div>";
    
    echo "<div class='import-section'>";
    echo "<h4>Επιλογή τύπου δεδομένων & ενέργειας</h4>";
    echo "<table id=\"mytbl\" class=\"import-table imagetable tablesorter\">\n";
    echo "<thead><tr><th>Τύπος</th><th>Ενέργεια</th></tr></thead>\n";
    echo "<tbody>";
    echo "<tr>";
    echo "<td rowspan=\"4\" class=\"rowspan-cell\">Σχολεία/Μαθητές</td>";
    echo "<td><label class='import-choice-label'><input type='radio' class='import-choice' name='type' value='2'>Σχολεία&nbsp; (<a href='schools.csv' class='link-sample'>Δείγμα</a>)</label></td>";
    echo "</tr>";
    echo "<tr><td><label class='import-choice-label'><input type='radio' class='import-choice' name='type' value='22'>Σχολεία&nbsp; (από αναφορά MySchool 2.2. Εκτεταμένα Στοιχεία Σχολικών Μονάδων)</label></td></tr>";
    echo "<tr><td><label class='import-choice-label'><input type='radio' class='import-choice' name='type' value='3'>Μαθητές / Τμήματα Δ.Σ.&nbsp;(<a href='students_ds.csv' class='link-sample'>Δείγμα</a>)&nbsp;(<a href='import_check.php' class='link-sample'>Έλεγχος εισαγωγής</a>) *</label></td></tr>";
    echo "<tr><td><label class='import-choice-label'><input type='radio' class='import-choice' name='type' value='4'>Μαθητές / Τμήματα Νηπ.&nbsp;(<a href='students_nip.csv' class='link-sample'>Δείγμα</a>) *</label></td></tr>";
    echo "<tr>";
    echo "<td rowspan=\"4\" class=\"rowspan-cell\">Μόνιμοι</td>";
    echo "<td><label class='import-choice-label'><input type='radio' class='import-choice' name='type' value='1'>Μόνιμοι&nbsp; (<a href='employees.csv' class='link-sample'>Δείγμα</a>)</label></td>";
    echo "</tr>";
    echo "<tr><td><label class='import-choice-label'><input type='radio' class='import-choice' name='type' value='5'>Μαζικές τοποθετήσεις μονίμων εκπ/κών &nbsp;(<a href='topo.csv' class='link-sample'>Δείγμα</a>)</label></td></tr>";
    echo "<tr><td><label class='import-choice-label'><input type='radio' class='import-choice' name='type' value='6'>Μαζικές τοποθετήσεις μονίμων εκπ/κών με αντικατάσταση τοποθετήσεων &nbsp;(για αποσπάσεις - <a href='topo.csv' class='link-sample'>Δείγμα</a>)</label></td></tr>";
    echo "<tr><td><label class='import-choice-label'><input type='radio' class='import-choice' name='type' value='8'>Μαζική προσθήκη σχολίων&nbsp;(<a href='comments.csv' class='link-sample'>Δείγμα</a>)</label></td></tr>";
    echo "<tr>";
    echo "<td rowspan=\"5\" class=\"rowspan-cell\">Αναπληρωτές</td>";
    echo "<td><label class='import-choice-label'><input type='radio' class='import-choice' name='type' value='7'>Μαζικές τοποθετήσεις αναπληρωτών εκπ/κών&nbsp;(<a href='topo.csv' class='link-sample'>Δείγμα</a>)</label></td>";
    echo "</tr>";
    echo "<tr><td><label class='import-choice-label'><input type='radio' class='import-choice' name='type' value='9'>Μαζική ανάθεση αναπληρωτών σε πράξεις&nbsp;(<a href='praxi.csv' class='link-sample'>Δείγμα</a>)</label></td></tr>";
    echo "<tr><td><a href='ektaktoi_import.php' class='link-sample'>Εισαγωγή αναπληρωτών</a></td></tr>";
    echo "<tr><td><a href='ektaktoi_import_minedu.php' class='link-sample'>Εισαγωγή αναπληρωτών (από αρχεία υπουργείου)</a></td></tr>";
    echo "<tr><td><a href='import_postgrad.php' class='link-sample'>Εισαγωγή μεταπτυχιακών τίτλων</a></td></tr>";
    echo "</tbody></table>";
    echo "</div>";

    echo "<div class='warning-box'>";
    echo "<strong>* ΠΡΟΣΟΧΗ: </strong> Nα εισάγονται ΜΟΝΟ αφού αλλάξει το σχ. έτος.";
    echo "</div>";
    
    echo "<div class='file-upload-section'>";
    echo "<h4>Υποβολή συμπληρωμένου αρχείου προς εισαγωγή</h4>";
    echo "<div class='file-input-wrapper'>";
    echo "<input type='file' name='filename' accept='.csv' required>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='info-box'>";
    echo "<strong>ΣΗΜ.:</strong> Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.";
    echo "</div>";
    
    echo "<div class='form-actions'>";
    echo "<input type='submit' name='submit' value='Μεταφόρτωση' class='btn btn-primary'>";
    echo "<INPUT TYPE='button' class='btn btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "</div>";
    
    echo "</form>";
    echo "</div>";
    exit;
  }
		
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  if (!isset($_POST['type'])){
    require '../etc/menu.php';
    echo "<div class='import-container'>";
    echo "<div class='result-message result-error'>";
    echo "<h3>Σφάλμα: Δεν επιλέξατε τύπο δεδομένων.</h3>";
    echo "<br><a href='import.php' class='btn btn-primary'>Επιστροφή</a>";
    echo "</div>";
    echo "</div>";
    die();
  }
  //Upload File
  if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
      require '../etc/menu.php';
      echo "<div class='import-container'>";
      echo "<div class='result-message result-success'>";
      echo "<p><strong>Το αρχείο ". htmlspecialchars($_FILES['filename']['name']) ." ανέβηκε με επιτυχία.</strong></p>";
      echo "</div>";

      //Import uploaded file to Database
      $handle = fopen($_FILES['filename']['tmp_name'], "r");
      switch ($_POST['type'])
      {
          case 1:
          case 5:
          case 6:
          case 8:
            $tbl = 'employee';
            break;
          case 2:
          case 22:
          case 3:
          case 4:
            $tbl = 'school';
            break;
          case 7:
          case 9:
            $tbl = 'ektaktoi';
            break;
      }
      $num = 0;
      $saves = 0;
      $checked = 0;
      $headers = 1;
      $error = false;
      $warnings = 0;
      $warn_msg = '';
      $er_msg = '';
      $top_afm = null;
      
      // set max execution time (for large files)
      set_time_limit (480);

      // initialize update_queries table
      $update_queries = array();
      $update_yphrethseis = array();
      // read csv line by line
      while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
        // skip header line
        if ($headers){
            $headers = 0;
            continue;
        }
        // check if csv & table columns are equal
        if (!$checked)
        {
          $csvcols = count($data);
          if ($_POST['type'] == 1){
            $tblcols = 25;
          }
          else if ($_POST['type'] == 2){
            $tblcols = 12;
          }
          else if ($_POST['type'] == 3){
            $tblcols = 18;
          }
          else if ($_POST['type'] == 4){
            $tblcols = 13;
          }
          else if ($_POST['type'] == 5 || $_POST['type'] == 6 || $_POST['type'] == 7){
            $tblcols = 3;
          }
          else if ($_POST['type'] == 8 || $_POST['type'] == 9){
            $tblcols = 2;
          }
          else if ($_POST['type'] == 22){
            $tblcols = 73;
          }

          if ($csvcols <> $tblcols)
          {
            echo "<div class='result-message result-error'>";
            echo "<h3>Σφάλμα: Λάθος αρχείο (Στήλες αρχείου: $csvcols <> στήλες πίνακα: $tblcols)</h3>";
            echo "<a href='import.php' class='btn btn-primary'>Επιστροφή</a>";
            echo "</div>";
            echo "</div>";
            die();
          }
          else
            $checked = 1;
        }  

        switch ($_POST['type']){
          // employees
          case 1:
            // check school codes
            $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
            $sx_organ = getSchoolFromCode($data[23],$mysqlconn);
            $sx_yphr = getSchoolFromCode($data[24],$mysqlconn);
            if (!$sx_organ || !$sx_yphr){
              $error = true;
              $er_msg = 'Σφάλμα: Δε βρέθηκε ο 7ψήφιος κωδικός σχολείου: ';
              $er_msg .= !$sx_organ ? $data[23] : $data[24];
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            // check if am exists
            $qry = "SELECT * FROM employee WHERE am = $data[5]";
            if (mysqli_num_rows(mysqli_query($mysqlconnection, $qry)) ){
              $error = true;
              $er_msg ="Σφάλμα: Ο υπάλληλος με ΑΜ ".$data[5]." υπάρχει ήδη...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            // fix dates
            $data[7] = date ("Y-m-d", strtotime($data[7]));
            $data[10] = date ("Y-m-d", strtotime($data[10]));
            $data[11] = date ("Y-m-d", strtotime($data[11]));
            $status = 1;
            // proceed to import
            $import="INSERT into employee(name,surname,patrwnymo,mhtrwnymo,klados,am,thesi,fek_dior,hm_dior,
            vathm, mk, hm_mk, hm_anal, met_did, proyp, proyp_not, status,
            afm, tel, address, idnum, amka, email, wres, sx_organikhs, sx_yphrethshs)
            values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]',0,'$data[6]','$data[7]',
            '$data[8]','$data[9]','$data[10]','$data[11]','$data[12]','$data[13]','$data[14]','$status',
            '$data[16]','$data[17]','$data[18]','$data[19]','$data[20]','$data[21]','$data[22]', $sx_organ, $sx_yphr)";
            $imp_8 = mb_detect_encoding($string, $encodings, true) == 'cp1253' ? $imp_8 = iconv('cp1253','utf-8',$import) : $import;
            $update_queries[] = $imp_8;
            
            $saves++;
            // insert yphrethsh as well
            //$id = mysqli_insert_id($mysqlconnection);
            // use am instead of inserted id
            $query = "insert into yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) 
            values ('$data[5]', '$sx_yphr', '$data[22]', '$sx_organ', '$sxol_etos')";
            $update_yphrethseis[$data[5]] = $query;
            
            break;

          // schools - myschool
          case 22:
            // check if school already exists
            $code = trim($data[12],'=\"');
            $qry = "SELECT * FROM school WHERE code = $code";
            if (mysqli_num_rows(mysqli_query($mysqlconnection, $qry)) ) {
              $error = true;
              $er_msg ="Σφάλμα: Το σχολείο με κωδικό ".$code." υπάρχει ήδη...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            // columns:
            // Κατηγορία Μοριοδότησης (4),	Δήμος	(6), Είδος	(10) Κωδ. ΥΠΠΘ (12)	Ονομασία (13)	Λειτουργικότητα	(14) 
            // Οργανικότητα	(15) Τηλέφωνο	(17) ΦΑΞ	(18) e-mail	(19) Ταχ. Διεύθυνση	(21) ΤΚ	(22) Αναστολή	(46) 
            $eidos = iconv('cp1253','utf-8',$data[10]);
            $typos = iconv('cp1253','utf-8',$data[11]);
            if ($eidos == 'Νηπιαγωγεία') {
              $type2 = 0;
              $type = 0;
            } elseif ($eidos == 'Δημοτικά Σχολεία') {
              $type2 = 0;
              $type = 1;
            } elseif ($eidos == 'Ιδιωτικά Σχολεία') {
              $type2 = 1;
              $type = $typos == 'Ιδιωτικό Δημοτικό Σχολείο' ? 1 : 2;
            }
            if (strstr($typos, 'Ειδικής Αγωγής')) {
              $type2 = 2;
            }

            $dimos = getDimosId($data[6], $mysqlconnection, true);
            
            $import="INSERT into school(code,category,type,name,address,tk,tel,fax,email,organikothta,leitoyrg,type2,dimos) 
            values('$code','$data[4]',$type,'$data[13]','$data[21]','$data[22]','$data[17]','$data[18]','$data[19]','$data[15]','$data[14]','$type2',$dimos)";
            $imp_8 = iconv('cp1253','utf-8',$import);

            $update_queries[] = $imp_8;
            $saves++;
            
            break;

          // schools
          case 2:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            if (mysqli_num_rows(mysqli_query($mysqlconnection, $qry)) ){
              $error = true;
              $er_msg ="Σφάλμα: Το σχολείο με κωδικό ".$data[0]." υπάρχει ήδη...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            $import="INSERT into school(code,category,type,name,address,tk,tel,fax,email,organikothta,leitoyrg,type2) 
            values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]','$data[10]','$data[11]')";
            $imp_8 = iconv('cp1253','utf-8',$import);
            
            $update_queries[] = $imp_8;
            $saves++;
            
            break;

          // students ds
          case 3:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            $res = mysqli_query($mysqlconnection, $qry);
            if (!mysqli_num_rows($res) ){
              $error = true;
              $er_msg ="Σφάλμα: Το σχολείο με κωδικό ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            
            // update school set students='Α,Β,Γ,Δ,Ε,ΣΤ,ΟΛ,ΠΡ-Ζ',tmimata='Α,Β,Γ,Δ,Ε,ΣΤ,ΟΛ,ΟΛ16,ΠΡ-Ζ' WHERE code='9170117';
            $students = implode(',',Array($data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7],$data[8]));
            $tm_prz = ceil($data[8]/25);
            $tmimata = implode(',',Array($data[10],$data[11],$data[12],$data[13],$data[14],$data[15],$data[16],$data[17],$tm_prz));
            $entaksis = $data[9] > 0 ? 'on,'.$data[9] : ',';
            // archive 
            $archive = mysqli_result($res, 0, "archive");
            if (strlen($archive) > 0) {
              $archive_arr = unserialize($archive);
            } else $archive_arr = Array();
            $students_old = mysqli_result($res, 0, "students");
            $tmimata_old = mysqli_result($res, 0, "tmimata");
            $entaksis_old = mysqli_result($res, 0, "entaksis");
            $archive_data = $students_old . ',' . $tmimata_old . ',' . $entaksis_old;
            $sxoletos = find_prev_year($sxol_etos);
            // archive last year only once (in case of reinserting data)
            if (is_string($archive_arr[$sxoletos]) && strlen($archive_arr[$sxoletos]) > 0) {
            } else {
              $archive_arr[$sxoletos] = $archive_data;
            }
            $sql="UPDATE school SET archive = '". serialize($archive_arr) . "' WHERE code=".$data[0];
            $update_queries[] = $sql;
            
            // update school table
            if ($students <> $students_old || $tmimata <> $tmimata_old || $entaksis <> $entaksis_old){
              $sql="UPDATE school SET students='$students', tmimata='$tmimata', entaksis='$entaksis' WHERE code=".$data[0];
              
              $update_queries[] = $sql;
              $saves++;
            }
            break;

          // students nip
          case 4:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            $res = mysqli_query($mysqlconnection, $qry);
            if (!mysqli_num_rows($res) ){
              $error = true;
              $er_msg ="Σφάλμα: Το σχολείο με κωδικό ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }

            // update school set klasiko='1Π,1Ν,2Π,2Ν,3Π,3Ν,ΠΖ', oloimero_nip='ΟΛ1Π,ΟΛ1Ν,ΟΛ2Π,ΟΛ2Ν',entaksis='0,0' where code=9170040;
            $klasiko = implode(',',Array($data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7]));
            $oloimero_nip = implode(',',Array($data[8],$data[9],$data[10],$data[11]));
            $entaksis = $data[12] > 0 ? 'on,'.$data[12] : '0,0';
            // archive 
            $archive = mysqli_result($res, 0, "archive");
            if (strlen($archive) > 0) {
              $archive_arr = unserialize($archive);
            } else $archive_arr = Array();
            $klasiko_old = mysqli_result($res, 0, "klasiko");
            $oloimero_nip_old = mysqli_result($res, 0, "oloimero_nip");
            $entaksis_old = mysqli_result($res, 0, "entaksis");
            $archive_data = $klasiko_old . ',' . $oloimero_nip_old . ',' . $entaksis_old;
            $sxoletos = find_prev_year($sxol_etos);
            // archive last year only once (in case of reinserting data)
            if (is_string($archive_arr[$sxoletos]) && strlen($archive_arr[$sxoletos]) > 0) {
            } else {
              $archive_arr[$sxoletos] = $archive_data;
            }
            $sql="UPDATE school SET archive = '". serialize($archive_arr) . "' WHERE code=".$data[0];
            $update_queries[] = $sql;

            // update school table
            if ($klasiko <> $klasiko_old || $oloimero_nip <> $oloimero_nip_old || $entaksis <> $entaksis_old){
              $sql="UPDATE school SET klasiko='$klasiko', oloimero_nip='$oloimero_nip', entaksis='$entaksis' WHERE code=".$data[0];
              $update_queries[] = $sql;
              $saves++;
            }
            break;
          // topothetiseis
          // 5: 5) Τοποθετήσεις μονίμων εκπ/κών
          // 6: 6) Τοποθετήσεις μονίμων εκπ/κών με αντικατάσταση τοποθετήσεων  (για αποσπάσεις)
          // 7: 7) Τοποθετήσεις αναπληρωτών εκπ/κών
          case 5:
          case 6:
          case 7:
            // Decide if AM of AFM on 1st column
            $searchcol = strlen($data[0]) > 8 ? 'afm' : 'am';
            $searchcolname = strlen($data[0]) > 8 ? 'ΑΦΜ' : 'ΑΜ';
            // check if $data[0] has a length of 8 characters. If yes, add a leading zero:
            if (strlen($data[0]) == 8) $data[0] = '0'.$data[0];
            $is_mon = $_POST['type'] == 5 || $_POST['type'] == 6 ? true : false;

            // If anaplirotes & am in csv, abort with a message
            if (!$is_mon && $searchcol == 'am'){
              echo "<div class='result-message result-error'>";
              echo "<h3>ΣΦΑΛΜΑ: Δεν είναι δυνατή η εισαγωγή τοποθετήσεων αναπληρωτών με ΑΜ!</h3>";
              echo "<a href='import.php' class='btn btn-primary'>Επιστροφή</a>";
              echo "</div>";
              echo "</div>";
              die();
            }
            $delete_yphr = $_POST['type'] == 6 ? true : false;
            // csv: AM/ΑΦΜ εκπ/κού;Κωδικός ΥΠΑΙΘ σχολείου;Ώρες
            $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
            // check if am/afm exists @ monimoi & ektaktoi
            $emp_qry = $is_mon ? "SELECT * FROM employee WHERE $searchcol = '$data[0]'" : "SELECT * FROM ektaktoi WHERE $searchcol = '$data[0]'";
            $emp = mysqli_query($mysqlconnection, $emp_qry);
            
            if ( !mysqli_num_rows($emp) ) {
              $error = true;
              $er_msg ="Σφάλμα: Ο υπάλληλος με $searchcolname ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            
            // check school codes
            $sch_id = getSchoolFromCode($data[1],$mysqlconn);
            if (!$sch_id) {
              $error = true;
              $er_msg = 'Σφάλμα: Δε βρέθηκε το σχολείο με 7ψήφιο κωδικό: ' . $data[1];
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            // check hours
            if ($data[2] <= 0 || $data[2] > 30) {
              $error = true;
              $er_msg = 'Σφάλμα: Λάθος αριθμός ωρών: ' . $data[1];
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            
            // proceed to import
            $id = null;
            $emp_row = mysqli_fetch_assoc($emp);
            $id = $emp_row['id'];
            $yp_table = $is_mon ? 'yphrethsh' : 'yphrethsh_ekt';

            if ($delete_yphr) {
              // delete all yphrethseis of employee @ current school year
              $yphr_qry = "DELETE from $yp_table WHERE emp_id = $id AND sxol_etos = $sxol_etos";
              $yphr = mysqli_query($mysqlconnection,$yphr_qry);
            } else {
              // check if yphrethsh already inserted. If yes, skip record.
              $yphr_qry = "SELECT id FROM $yp_table WHERE emp_id = $id AND yphrethsh = $sch_id AND sxol_etos = $sxol_etos";
              $yphr = mysqli_query($mysqlconnection,$yphr_qry);
              if (mysqli_num_rows($yphr) > 0){
                $warnings ++;
                $warn_msg .= '<br>- Η τοποθέτηση υπάρχει ήδη: ';
                $warn_msg .= $emp_row['afm'] . ': '.$emp_row['surname'].' '.$emp_row['name'];
                $warn_msg .= " (γραμμή ".($num+1).")";
                continue 2;
              }
            }

            // insert yphrethsh @ employee table
            if (!$top_afm) {
              $top_afm = $data[0];
            }
            if ($top_afm != $data[0]){
              if ($is_mon) {
                $upd_qry = "UPDATE employee SET sx_yphrethshs = $sch_id WHERE id = $id";
              } else {
                $upd_qry = "UPDATE ektaktoi SET sx_yphrethshs = $sch_id WHERE id = $id";
              }
              $update_queries[] = $upd_qry;
              $top_afm = $data[0];
            } 

            if ($is_mon) {
              $sx_organ = $emp_row['sx_organikhs'];
              $query = "insert into yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) 
                values ($id, '$sch_id', '$data[2]', '$sx_organ', '$sxol_etos')";
            } else {
              $query = "insert into yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) 
                values ($id, '$sch_id', '$data[2]', '$sxol_etos')";
            }
            $update_queries[] = $query;
            $saves++;
            
            break;
          // Import comments 
          // 8) Μαζική προσθήκη σχολίων
          case 8:
            // csv: ΑΜ/ΑΦΜ εκπ/κού;Σχόλιο
            // Decide if AM of AFM on 1st column
            // > 8 cause it may be 8 characters long
            $searchcol = strlen($data[0]) > 8 ? 'afm' : 'am';
            $searchcolname = strlen($data[0]) > 8 ? 'ΑΦΜ' : 'ΑΜ';
            // check if $data[0] has a length of 8 characters. If yes, add a leading zero:
            if (strlen($data[0]) == 8) $data[0] = '0'.$data[0];
            
            $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
            // check if afm exists @ monimoi
            $emp_qry = "SELECT * FROM employee WHERE $searchcol = '$data[0]'";

            $emp = mysqli_query($mysqlconnection, $emp_qry);
            
            if ( !mysqli_num_rows($emp) ) {
              $error = true;
              $er_msg ="Σφάλμα: Ο υπάλληλος με $searchcolname ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            
            // proceed to import
            $id = null;
            $emp_row = mysqli_fetch_assoc($emp);
            $id = $emp_row['id'];
            
            // update employee table
            $upd_qry = "UPDATE employee set comments=concat(comments,'\n".$data[1]."') where $searchcol='".$data[0]."'";
            $saves++;
            $update_queries[] = $upd_qry;
             
            break;
          // Assign praxi to ektaktoi
          // 9) Μαζική ανάθεση αναπληρωτών σε πράξεις
          case 9:
            // csv: ΑΦΜ εκπ/κού;ID πράξης
            $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
            // check if afm exists @ ektaktoi
            $emp_qry = "SELECT * FROM ektaktoi WHERE afm = '$data[0]'";

            $emp = mysqli_query($mysqlconnection, $emp_qry);
            
            if ( !mysqli_num_rows($emp) ) {
              $error = true;
              $er_msg ="Σφάλμα: Ο υπάλληλος με ΑΦΜ ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            
            // proceed to import
            $id = null;
            $emp_row = mysqli_fetch_assoc($emp);
            $id = $emp_row['id'];
            
            // update employee table
            $upd_qry = "UPDATE ektaktoi set praxi=$data[1] where afm='".$data[0]."'";
            $saves++;
            $update_queries[] = $upd_qry;
             
            break;
        }
        if ($error){
          break;
        }
        
        $num++;
      }
      
      fclose($handle);

      if (!$error){
        // execute all update / insert queries
        $queries = implode(';', $update_queries);
        // echo "<br>Queries:<br>".$queries."<br><br>";

        //$ret = mysqli_multi_query($mysqlconnection, $queries);

        // display an image with the queries for debugging
        $qries = htmlspecialchars($queries, ENT_QUOTES, 'UTF-8');
        $infolink = "&nbsp;<img src='../images/info.png' width='20' height='20' title='Queries: $qries' />";

        mysqli_autocommit($mysqlconnection,FALSE);
        $errors = array();
        foreach ( $update_queries as $qry) {
          $res = mysqli_query($mysqlconnection, $qry);
          if (!$res) {
            $errors[$qry] = mysqli_error();
          }
        }
        if (!mysqli_commit($mysqlconnection)){
          echo "<div class='result-message result-error'>";
          echo "<h3>Σφάλμα κατά την εκτέλεση των ενημερώσεων στη βάση</h3>";
          foreach($errors as $k=>$v){
            echo "<br>".htmlspecialchars($k).": ".htmlspecialchars($v);
          }
          echo "<h4>Ελέγξτε το αρχείο ή επικοινωνήστε με το διαχειριστή.</h4>";
          echo "</div>";
          echo "</div>";
          die();
        }


        // if new employees, add their yphrethseis
        foreach( $update_yphrethseis as $key => $value ) {
          $query = "select * from employee where am = ".$key;
          $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
          $res = mysqli_query($mysqlconn,$query);
          $row = mysqli_fetch_assoc($res);
          $qry = str_replace($key, $row['id'], $value);
          $res = mysqli_query($mysqlconn, $qry);
        }
        // if (!$ret) {
        //   echo "Προέκυψε σφάλμα κατά την εκτέλεση των ενημερώσεων στη Β.Δ...";
        // }
        if ($warnings > 0){
          echo "<div class='result-message result-warning'>";
          echo "<h4>Παρατηρήσεις - προειδοποιήσεις:</h4>";
          echo $warn_msg;
          echo "</div>";
        }
        if ($saves > 0){ 
          echo "<div class='result-message result-success'>";
          echo "<h3>Η εισαγωγή πραγματοποιήθηκε με επιτυχία!</h3>";
          echo "<p>Έγινε εισαγωγή <strong>$saves</strong> εγγραφών στον πίνακα <strong>$tbl</strong>.$infolink</p>";
          echo "</div>";
        } else {
          echo "<div class='result-message result-warning'>";
          echo "<h3>Δεν έγινε καμία εισαγωγή στη βάση δεδομένων.$infolink</h3>";
          echo "</div>";
        }
      }
      else
      {
          echo "<div class='result-message result-error'>";
          echo "<h3>Παρουσιάστηκε σφάλμα κατά την εισαγωγή</h3>";
          
          echo mysqli_error($mysqlconnection) ? "<p><strong>Μήνυμα λάθους:</strong> ".htmlspecialchars(mysqli_error($mysqlconnection))."</p>" : '';
          echo $er_msg ? "<p><strong>$er_msg</strong></p>" : '';
          echo "<p>Ελέγξτε το αρχείο ή επικοινωνήστε με το διαχειριστή.</p>";
          echo "</div>";
      }
    }
    else {
        require '../etc/menu.php';
        echo "<div class='import-container'>";
        echo "<div class='result-message result-error'>";
        echo "<h3>Σφάλμα: Δεν επιλέξατε αρχείο</h3>";
        echo "</div>";
        echo "</div>";
    }
                
    echo "<div style='text-align: center; margin-top: 20px;'>";
    echo "<a href='import.php' class='btn btn-primary'>Επιστροφή</a>";
    echo "</div>";
    echo "</div>";
?>

</body>
</html>
	