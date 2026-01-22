<?php
  require_once "../config.php";
  require_once '../include/functions.php';
  header('Content-type: text/html; charset=utf-8'); 
?>
<html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Εισαγωγή δεδομένων Μεταπτυχιακών';
    require '../etc/head.php'; 
    ?>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript">
      $(document).ready(function() {
        $('input[name="import_type"]').change(function() {
          if ($(this).val() === 'postgrad') {
            $('#postgrad-section').show();
            $('#comments-section').hide();
          } else {
            $('#postgrad-section').hide();
            $('#comments-section').show();
          }
        });
      });
    </script>
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
      
      #mytbl.import-table tbody td:last-child {
        color: #374151;
        vertical-align: middle;
        padding-left: 20px;
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
      
      .columns-table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        margin: 15px 0;
      }
      
      .columns-table th {
        background: #f3f4f6;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #e5e7eb;
      }
      
      .columns-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e5e7eb;
      }
      
      .columns-table tr:hover {
        background: #f9fafb;
      }
    </style>
  </head>
  <body>

<?php
  include_once("class.login.php");
  $log = new logmein();
  
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
    echo "<h2>Εισαγωγή δεδομένων Μεταπτυχιακών Σπουδών</h2>";
    echo "</div>";
    
    echo "<form enctype='multipart/form-data' action='import_postgrad.php' method='post'>";
    
    echo "<div class='import-section'>";
    echo "<h4>Επιλογή τύπου εισαγωγής</h4>";
    echo "<label style='margin-right: 30px;'>";
    echo "<input type='radio' name='import_type' value='postgrad' checked> Εισαγωγή Μεταπτυχιακών Σπουδών";
    echo "</label>";
    echo "<label>";
    echo "<input type='radio' name='import_type' value='comments'> Εισαγωγή Σχολίων";
    echo "</label>";
    echo "</div>";
    
    echo "<div class='import-section'>";
    echo "<h4>Οδηγίες</h4>";
    echo "<p>Κατεβάστε το δείγμα των δεδομένων που θέλετε να εισάγετε και αφού του προσθέσετε δεδομένα εισάγετε το.</p>";
    echo "<p>Το αρχείο θα πρέπει να είναι σε μορφή CSV (χωρισμένο με ερωτηματικό ;) ή Excel.</p>";
    echo "</div>";
    
    echo "<div class='import-section' id='postgrad-section'>";
    echo "<h4>Δομή αρχείου - Μεταπτυχιακές Σπουδές</h4>";
    echo "<p>Το αρχείο θα πρέπει να περιέχει τις ακόλουθες στήλες με αυτή τη σειρά:</p>";
    echo "<table class='columns-table'>";
    echo "<thead><tr>";
    echo "<th>Σειρά</th>";
    echo "<th>Πεδίο</th>";
    echo "<th>Τύπος</th>";
    echo "<th>Περιγραφή</th>";
    echo "</tr></thead>";
    echo "<tbody>";
    echo "<tr><td>1</td><td>afm</td><td>Αριθμός</td><td>Αριθμός Φορολογικού Μητρώου</td></tr>";
    echo "<tr><td>2</td><td>category</td><td>Κατηγορία</td><td>Ένα από τα: Μεταπτυχιακό / Διδακτορικό / Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master) (δεκτά και με κεφαλαία)</td></tr>";
    echo "<tr><td>3</td><td>title</td><td>Κείμενο</td><td>Τίτλος Σπουδών</td></tr>";
    echo "<tr><td>4</td><td>idryma</td><td>Κείμενο</td><td>Ίδρυμα</td></tr>";
    echo "<tr><td>5</td><td>aitisi_protocol</td><td>Αριθμός</td><td>Αριθμός Πρωτοκόλλου Αίτησης</td></tr>";
    echo "<tr><td>6</td><td>praxi</td><td>Κείμενο</td><td>Πράξη Αναγνώρισης Συνάφειας</td></tr>";
    echo "<tr><td>7</td><td>synafeia</td><td>0/1 ή ΝΑΙ/NAI/ΟΧΙ/OXI</td><td>Συνάφεια (0 ή ΟΧΙ=Όχι, 1 ή ΝΑΙ=Ναι)</td></tr>";
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    
    echo "<div class='import-section' id='comments-section' style='display:none;'>";
    echo "<h4>Δομή αρχείου - Σχόλια</h4>";
    echo "<p>Το αρχείο θα πρέπει να περιέχει τις ακόλουθες στήλες με αυτή τη σειρά:</p>";
    echo "<table class='columns-table'>";
    echo "<thead><tr>";
    echo "<th>Σειρά</th>";
    echo "<th>Πεδίο</th>";
    echo "<th>Τύπος</th>";
    echo "<th>Περιγραφή</th>";
    echo "</tr></thead>";
    echo "<tbody>";
    echo "<tr><td>1</td><td>afm</td><td>Αριθμός</td><td>Αριθμός Φορολογικού Μητρώου (θα αναζητηθεί στον πίνακα εκπ/κών, μόνιμος ή αναπληρωτής)</td></tr>";
    echo "<tr><td>2</td><td>category</td><td>Κατηγορία</td><td>Ένα από τα: Μεταπτυχιακό / Διδακτορικό / Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)</td></tr>";
    echo "<tr><td>3</td><td>comment</td><td>Κείμενο</td><td>Σχόλιο που θα προστεθεί</td></tr>";
    echo "</tbody>";
    echo "</table>";
    echo "<p><strong>Σημ.:</strong> Το σχόλιο θα προστεθεί στο τέλος του υπάρχοντος και met_did θα ενημερωθεί ανάλογα με την κατηγορία.</p>";
    echo "</div>";
    
    echo "<div class='file-upload-section'>";
    echo "<h4>Υποβολή συμπληρωμένου αρχείου προς εισαγωγή</h4>";
    echo "<div class='file-input-wrapper'>";
    echo "<input type='file' name='filename' accept='.csv,.xlsx,.xls' required>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='info-box'>";
    echo "<strong>ΣΗΜ.:</strong> Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν έχετε ολοκληρωθεί.";
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
  
  // Check import type
  $import_type = isset($_POST['import_type']) ? $_POST['import_type'] : 'postgrad';
  
  //Upload File
  if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
      require '../etc/menu.php';
      echo "<div class='import-container'>";
      echo "<div class='result-message result-success'>";
      echo "<p><strong>Το αρχείο ". htmlspecialchars($_FILES['filename']['name']) ." ανέβηκε με επιτυχία.</strong></p>";
      echo "</div>";

      //Import uploaded file to Database
      $handle = fopen($_FILES['filename']['tmp_name'], "r");
      
      $num = 0;
      $saves = 0;
      $checked = 0;
      $headers = 1;
      $error = false;
      $warnings = 0;
      $warn_msg = '';
      $er_msg = '';
      $employee_updated = 0;
      $ektaktoi_updated = 0;
      $log_entries = array();
      
      // set max execution time (for large files)
      set_time_limit(480);

      // initialize arrays for queries
      $update_queries = array();
      
      // Determine number of columns based on import type
      $expected_cols = ($import_type === 'comments') ? 3 : 7;
      
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

          if ($csvcols != $expected_cols)
          {
            echo "<div class='result-message result-error'>";
            echo "<h3>Σφάλμα: Λάθος αρχείο (Στήλες αρχείου: $csvcols <> απαιτούμενες στήλες: $expected_cols)</h3>";
            echo "<a href='import_postgrad.php' class='btn btn-primary'>Επιστροφή</a>";
            echo "</div>";
            echo "</div>";
            die();
          }
          else
            $checked = 1;
        }
        
        // trim whitespace
        $data = array_map('trim', $data);
        
        // comment importing
        if ($import_type === 'comments') {
          // COMMENTS IMPORT LOGIC
          $afm = intval($data[0]);
          $category = $data[1];
          $comment = $data[2];
          $log_entries[] = "Γραμμή " . ($num+2) . ": ΑΦΜ=$afm, Κατηγορία=$category";
          
          // validate afm
          if ($afm <= 0) {
            $error = true;
            $er_msg = "Σφάλμα: Άκυρο ΑΦΜ στη γραμμή ".($num+2);
            break;
          }
          
          // validate category
          $valid_categories = array('Μεταπτυχιακό', 'Διδακτορικό', 'Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)',
          'ΜΕΤΑΠΤΥΧΙΑΚΟ', 'ΔΙΔΑΚΤΟΡΙΚΟ', 'ΕΝΙΑΙΟΣ ΚΑΙ ΑΔΙΑΣΠΑΣΤΟΣ ΤΙΤΛΟΣ ΣΠΟΥΔΩΝ ΜΕΤΑΠΤΥΧΙΑΚΟΥ ΕΠΙΠΕΔΟΥ (INTEGRATED MASTER)');
          if (!in_array($category, $valid_categories)) {
            $error = true;
            $er_msg = "Σφάλμα: Άκυρη κατηγορία '$category' στη γραμμή ".($num+2);
            break;
          }
          
          // Convert uppercase to proper case
          if ($category == 'ΜΕΤΑΠΤΥΧΙΑΚΟ') {
            $category = 'Μεταπτυχιακό';
          } else if ($category == 'ΔΙΔΑΚΤΟΡΙΚΟ') {
            $category = 'Διδακτορικό';
          } else if ($category == 'ΕΝΙΑΙΟΣ ΚΑΙ ΑΔΙΑΣΠΑΣΤΟΣ ΤΙΤΛΟΣ ΣΠΟΥΔΩΝ ΜΕΤΑΠΤΥΧΙΑΚΟΥ ΕΠΙΠΕΔΟΥ (INTEGRATED MASTER)') {
            $category = 'Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)';
          }
          
          // validate comment
          if (empty($comment)) {
            $error = true;
            $er_msg = "Σφάλμα: Σχόλιο κενό στη γραμμή ".($num+2);
            break;
          }
          
          // Determine met_did value based on category
          $met_did = 0;
          if ($category == 'Μεταπτυχιακό') {
            $met_did = 1;
          } else if ($category == 'Διδακτορικό') {
            $met_did = 2;
          } else if ($category == 'Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)') {
            $met_did = 3;
          }
          
          // Search in employee table first
          $qry_emp = "SELECT id, comments FROM employee WHERE afm = $afm";
          $result_emp = mysqli_query($mysqlconnection, $qry_emp);
          
          if (mysqli_num_rows($result_emp) > 0) {
            // Found in employee table
            $row_emp = mysqli_fetch_assoc($result_emp);
            $emp_id = $row_emp['id'];
            $existing_comment = $row_emp['comments'];
            
            // Check if comment already exists
            if (!empty($existing_comment) && stripos($existing_comment, $comment) !== false) {
              // Comment already exists
              $warnings++;
              $warn_msg .= "Προειδοποίηση: Το σχόλιο για ΑΦΜ $afm υπάρχει ήδη και δεν θα ξαναπροστεθεί (γραμμή ".($num+2).")<br>";
            } else {
              // Append new comment
              $new_comment = empty($existing_comment) ? $comment : $existing_comment . "\n" . $comment;
              $new_comment = mysqli_real_escape_string($mysqlconnection, $new_comment);
              
              $update_query = "UPDATE employee SET comments = '$new_comment', met_did = $met_did WHERE id = $emp_id";
              $update_queries[] = array('table' => 'employee', 'query' => $update_query);
              $saves++;
            }
          } else {
            // Search in ektaktoi table
            $qry_ekt = "SELECT id, comments FROM ektaktoi WHERE afm = $afm";
            $result_ekt = mysqli_query($mysqlconnection, $qry_ekt);
            
            if (mysqli_num_rows($result_ekt) > 0) {
              // Found in ektaktoi table
              $row_ekt = mysqli_fetch_assoc($result_ekt);
              $ekt_id = $row_ekt['id'];
              $existing_comment = $row_ekt['comments'];
              
              // Check if comment already exists
              if (!empty($existing_comment) && stripos($existing_comment, $comment) !== false) {
                // Comment already exists
                $warnings++;
                $warn_msg .= "Προειδοποίηση: Το σχόλιο για ΑΦΜ $afm υπάρχει ήδη και δεν θα ξαναπροστεθεί (γραμμή ".($num+2).")<br>";
              } else {
                // Append new comment
                $new_comment = empty($existing_comment) ? $comment : $existing_comment . "\n" . $comment;
                $new_comment = mysqli_real_escape_string($mysqlconnection, $new_comment);
                
                $update_query = "UPDATE ektaktoi SET comments = '$new_comment' WHERE id = $ekt_id";
                $update_queries[] = array('table' => 'ektaktoi', 'query' => $update_query);
                $saves++;
              }
            } else {
              // Teacher not found
              $warnings++;
              $warn_msg .= "Προειδοποίηση: ΑΦΜ $afm δεν βρέθηκε ούτε στους μονίμους ούτε στους αναπληρωτές (γραμμή ".($num+2).")<br>";
            }
          }
          
        // postgrad detail importing
         } else {
           // POSTGRAD IMPORT LOGIC
           $afm = intval($data[0]);
           $category = $data[1];
           $title = $data[2];
           $idryma = $data[3];
           $aitisi_protocol = intval($data[4]);
           $praxi = $data[5];
           $synafeia_raw = $data[6]; // Keep original value before conversion
           $log_entries[] = "Γραμμή " . ($num+2) . ": ΑΦΜ=$afm, Κατηγορία=$category, Τίτλος=$title";
           
           // check if afm is valid
          if ($afm <= 0) {
            $error = true;
            $er_msg = "Σφάλμα: Άκυρο ΑΦΜ στη γραμμή ".($num+2);
            break;
          }
          
          // validate category
          $valid_categories = array('Μεταπτυχιακό', 'Διδακτορικό', 'Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)',
          'ΜΕΤΑΠΤΥΧΙΑΚΟ','ΔΙΔΑΚΤΟΡΙΚΟ', 'ΕΝΙΑΙΟΣ ΚΑΙ ΑΔΙΑΣΠΑΣΤΟΣ ΤΙΤΛΟΣ ΣΠΟΥΔΩΝ ΜΕΤΑΠΤΥΧΙΑΚΟΥ ΕΠΙΠΕΔΟΥ (INTEGRATED MASTER)');
          if (!in_array($category, $valid_categories)) {
            $error = true;
            $er_msg = "Σφάλμα: Άκυρη κατηγορία '$category' στη γραμμή ".($num+2);
            break;
          }
          // Normalize uppercase to proper case
          if ($category == 'ΜΕΤΑΠΤΥΧΙΑΚΟ') {
            $category = 'Μεταπτυχιακό';
          } else if ($category == 'ΔΙΔΑΚΤΟΡΙΚΟ') {
            $category = 'Διδακτορικό';
          } else if ($category == 'ΕΝΙΑΙΟΣ ΚΑΙ ΑΔΙΑΣΠΑΣΤΟΣ ΤΙΤΛΟΣ ΣΠΟΥΔΩΝ ΜΕΤΑΠΤΥΧΙΑΚΟΥ ΕΠΙΠΕΔΟΥ (INTEGRATED MASTER)') {
            $category = 'Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)';
          }
          
          // check if title and institution are not empty
          if (empty($title) || empty($idryma)) {
            $error = true;
            $er_msg = "Σφάλμα: Τίτλος ή Ίδρυμα κενά στη γραμμή ".($num+2);
            break;
          }
          
          // check if protocol number is valid
          if ($aitisi_protocol <= 0) {
            $error = true;
            $er_msg = "Σφάλμα: Άκυρος αριθμός πρωτοκόλλου στη γραμμή ".($num+2);
            break;
          }
          
          // validate synafeia (0 or 1 or ΝΑΙ/NAI or ΟΧΙ/OXI)
          $synafeia = strtoupper(trim($synafeia_raw));
          
          // Convert text values to 0/1
          if ($synafeia == 'ΟΧΙ' || $synafeia == 'OXI') {
            $synafeia = 0;
          } else if ($synafeia == 'ΝΑΙ' || $synafeia == 'NAI') {
            $synafeia = 1;
          } else {
            $synafeia = intval($synafeia);
          }
          
          // Validate the final synafeia value
          if ($synafeia !== 0 && $synafeia !== 1) {
            $error = true;
            $er_msg = "Σφάλμα: Συνάφεια πρέπει να είναι 0, 1, ΝΑΙ, NAI, ΟΧΙ ή OXI στη γραμμή ".($num+2);
            break;
          }
          
          // check if afm already exists in postgrad
          $qry = "SELECT id FROM postgrad WHERE afm = $afm";
          if (mysqli_num_rows(mysqli_query($mysqlconnection, $qry)) > 0) {
            $warnings++;
            $warn_msg .= "Προειδοποίηση: ΑΦΜ $afm υπάρχει ήδη (γραμμή ".($num+2).")<br>";
            $num++;
            continue;
          }
          
          // escape strings for SQL
          $title = mysqli_real_escape_string($mysqlconnection, $title);
          $idryma = mysqli_real_escape_string($mysqlconnection, $idryma);
          $praxi = mysqli_real_escape_string($mysqlconnection, $praxi);
          
          // prepare insert query
          $import = "INSERT INTO postgrad (afm, category, title, idryma, aitisi_protocol, praxi, synafeia, anagnwrish, gnhsiothta)
                     VALUES ('$afm', '$category', '$title', '$idryma', '$aitisi_protocol', '$praxi', '$synafeia', 0, 0)";
          
          $update_queries[] = array('table' => 'postgrad', 'query' => $import);
          $saves++;
        }
        
        $num++;
      }
      
      fclose($handle);
      
      // if errors detected
      if ($error) {
        echo "<div class='result-message result-error'>";
        echo "<h3>$er_msg</h3>";
        echo "<a href='import_postgrad.php' class='btn btn-primary'>Επιστροφή</a>";
        echo "</div>";
        echo "</div>";
        die();
      }
      
      // execute queries
      $successful = 0;
      $failed = 0;
      
      foreach($update_queries as $q_data) {
        // Handle both formats: array(table, query) and string (old format)
        if (is_array($q_data)) {
          $table = $q_data['table'];
          $q_str = $q_data['query'];
        } else {
          $q_str = $q_data;
          $table = 'postgrad';
        }
        
        if (mysqli_query($mysqlconnection, $q_str)) {
          $successful++;
          if ($table === 'employee') {
            $employee_updated++;
          } else if ($table === 'ektaktoi') {
            $ektaktoi_updated++;
          }
        } else {
          $failed++;
          echo "<div class='result-message result-error'>";
          echo "<p>Σφάλμα εισαγωγής: " . mysqli_error($mysqlconnection) . "</p>";
          echo "</div>";
        }
      }
      
      echo "<div class='result-message result-success'>";
      echo "<h3>Εισαγωγή ολοκληρώθηκε!</h3>";
      
      if ($import_type === 'comments') {
        // Display statistics for comments import
        echo "<p><strong>Στατιστικά Εισαγωγής Σχολίων:</strong></p>";
        echo "<ul>";
        echo "<li>Ενημερωμένοι Μόνιμοι: <strong>$employee_updated</strong></li>";
        echo "<li>Ενημερωμένοι Αναπληρωτές: <strong>$ektaktoi_updated</strong></li>";
        echo "<li>Σύνολο Ενημερωμένων: <strong>$successful</strong></li>";
        echo "</ul>";
      } else {
        // Display statistics for postgrad import
        echo "<p><strong>Εγγραφές που εισήχθησαν: $successful</strong></p>";
      }
      
      if ($failed > 0) {
        echo "<p><strong>Αποτυχίες: $failed</strong></p>";
      }
      if ($warnings > 0) {
        echo "<p><strong>Προειδοποιήσεις:</strong><br>$warn_msg</p>";
      }
      echo "</div>";
      
      // Generate log content
      $log_timestamp = date('Y-m-d H:i:s');
      $log_type = ($import_type === 'comments') ? 'Σχολίων' : 'Μεταπτυχιακών Σπουδών';
      
      $log_content = "=====================================\n";
      $log_content .= "ΑΡΧΕΊΟ ΚΑΤΑΓΡΑΦΗΣ ΕΙΣΑΓΩΓΗΣ ΔΕΔΟΜΈΝΩΝ\n";
      $log_content .= "=====================================\n\n";
      $log_content .= "Τύπος Εισαγωγής: $log_type\n";
      $log_content .= "Ημερομηνία/Ώρα: $log_timestamp\n";
      $log_content .= "Αρχείο: " . htmlspecialchars($_FILES['filename']['name']) . "\n\n";
      
      $log_content .= "ΣΤΑΤΙΣΤΙΚΑ\n";
      $log_content .= "----------\n";
      if ($import_type === 'comments') {
        $log_content .= "Ενημερωμένοι Μόνιμοι: $employee_updated\n";
        $log_content .= "Ενημερωμένοι Αναπληρωτές: $ektaktoi_updated\n";
        $log_content .= "Σύνολο Ενημερωμένων: $successful\n";
      } else {
        $log_content .= "Εγγραφές που εισήχθησαν: $successful\n";
      }
      $log_content .= "Αποτυχίες: $failed\n";
      $log_content .= "Προειδοποιήσεις: $warnings\n\n";
      
      if ($warnings > 0) {
        $log_content .= "ΠΡΟΕΙΔΟΠΟΙΗΣΕΙΣ\n";
        $log_content .= "---------------\n";
        $log_content .= strip_tags($warn_msg) . "\n\n";
      }
      
      $log_content .= "ΛΕΠΤΟΜΕΡΕΙΕΣ ΕΓΓΡΑΦΩΝ\n";
      $log_content .= "---------------------\n";
      foreach ($log_entries as $entry) {
        $log_content .= $entry . "\n";
      }
      
      // Store log in session for download
      $_SESSION['import_log_content'] = $log_content;
      
      echo "<div class='form-actions'>";
      echo "<a href='import_postgrad.php' class='btn btn-primary'>Νέα εισαγωγή</a>";
      echo "<a href='download_log.php' class='btn btn-primary' style='background-color: #4FC5D6;'>Λήψη καταγραφής αρχείου</a>";
      echo "<INPUT TYPE='button' class='btn btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
      echo "</div>";
      
  } else {
    require '../etc/menu.php';
    echo "<div class='import-container'>";
    echo "<div class='result-message result-error'>";
    echo "<h3>Σφάλμα: Δεν ανέβηκε αρχείο.</h3>";
    echo "<a href='import_postgrad.php' class='btn btn-primary'>Επιστροφή</a>";
    echo "</div>";
    echo "</div>";
  }
  
  mysqli_close($mysqlconnection);
?>

  </body>
</html>
