<?php
  require_once "../config.php";
  require_once '../include/functions.php';
  header('Content-type: text/html; charset=utf-8'); 
?>
<html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Εισαγωγή δεδομένων Οργανικών Θέσεων';
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
      
      .sample-box {
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: 8px;
        padding: 15px;
        margin: 15px 0;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        white-space: pre-wrap;
        word-break: break-all;
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
    echo "<h2>Εισαγωγή δεδομένων Οργανικών Θέσεων</h2>";
    echo "</div>";
    
    echo "<form enctype='multipart/form-data' action='import_organikes.php' method='post'>";
    
    echo "<div class='import-section'>";
    echo "<h4>Οδηγίες</h4>";
    echo "<p>Κατεβάστε το δείγμα των δεδομένων που θέλετε να εισάγετε και αφού του προσθέσετε δεδομένα εισάγετε το.</p>";
    echo "<p>Το αρχείο θα πρέπει να είναι σε μορφή CSV (χωρισμένο με ερωτηματικό ;).</p>";
    echo "</div>";
    
    echo "<div class='import-section'>";
    echo "<h4>Δομή αρχείου</h4>";
    echo "<p>Το αρχείο θα πρέπει να περιέχει τις ακόλουθες στήλες με αυτή τη σειρά:</p>";
    echo "<table class='columns-table'>";
    echo "<thead><tr>";
    echo "<th>Σειρά</th>";
    echo "<th>Πεδίο</th>";
    echo "<th>Τύπος</th>";
    echo "<th>Περιγραφή</th>";
    echo "</tr></thead>";
    echo "<tbody>";
    echo "<tr><td>1</td><td>code</td><td>Αριθμός</td><td>Κωδικός ΥΠΑΙΘ σχολείου (7 ψηφία)</td></tr>";
    echo "<tr><td>2</td><td>klados</td><td>Κείμενο</td><td>Κωδικός ή περιγραφή κλάδου (π.χ. ΠΕ70, ΔΕ01, ή ΠΕ60-Νηπιαγωγών)</td></tr>";
    echo "<tr><td>3</td><td>fek</td><td>Κείμενο</td><td>Αριθμός ΦΕΚ (π.χ. 2089/Β/04-06-2019)</td></tr>";
    echo "<tr><td>4</td><td>organikes</td><td>Αριθμός</td><td>Πλήθος οργανικών θέσεων</td></tr>";
    echo "<tr><td>5</td><td>comments</td><td>Κείμενο</td><td>Σχόλια (προαιρετικό)</td></tr>";
    echo "</tbody>";
    echo "</table>";
    
    echo "<div class='sample-box'>";
    echo "<strong>Δείγμα CSV:</strong><br>";
    echo "code;klados;fek;organikes;comments<br>";
    echo "9170527;ΔΕ01-ΕΒΠ;2089/Β/04-06-2019;1;<br>";
    echo "9170527;ΔΕ01-ΕΒΠ;4243/Β/19-07-2024;1;<br>";
    echo "9170527;ΠΕ70;1234/Α/01-01-2020;5;Πρόσθετες θέσεις";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='file-upload-section'>";
    echo "<h4>Υποβολή συμπληρωμένου αρχείου προς εισαγωγή</h4>";
    echo "<div class='file-input-wrapper'>";
    echo "<input type='file' name='filename' accept='.csv' required>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='info-box'>";
    echo "<strong>ΣΗΜ.:</strong> Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν έχετε ολοκληρωθεί.";
    echo "</div>";
    
    echo "<div class='warning-box'>";
    echo "<strong>ΠΡΟΣΟΧΗ:</strong> Αν υπάρχει ήδη εγγραφή για το ίδιο σχολείο και κλάδο, θα ενημερωθεί.";
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
      $updates = 0;
      $errors = 0;
      $headers = 1;
      $error = false;
      $warnings = 0;
      $warn_msg = '';
      $er_msg = '';
      $log_entries = array();
      
      // set max execution time (for large files)
      set_time_limit(480);

      // read csv line by line
      while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
        // skip header line
        if ($headers){
            $headers = 0;
            continue;
        }
        
        // check if csv has correct number of columns
        if (count($data) < 4) {
            $errors++;
            $er_msg .= "Σφάλμα στη γραμμή " . ($num + 1) . ": Λάθος αριθμός στηλών. Αναμένονται τουλάχιστον 4 στήλες.<br>";
            $num++;
            continue;
        }
        
        $num++;
        
        // Get data from CSV
        $code = trim($data[0]);
        $klados_input = trim($data[1]);
        $fek = isset($data[2]) ? trim($data[2]) : '';
        $organikes = isset($data[3]) ? intval(trim($data[3])) : 0;
        $comments = isset($data[4]) ? trim($data[4]) : '';
        
        // Find school by code
        $school_query = "SELECT id, name FROM school WHERE code = '$code'";
        $school_result = mysqli_query($mysqlconnection, $school_query);
        
        if (!$school_result || mysqli_num_rows($school_result) == 0) {
            $errors++;
            $er_msg .= "Σφάλμα στη γραμμή $num: Το σχολείο με κωδικό '$code' δεν βρέθηκε.<br>";
            continue;
        }
        
        $school_row = mysqli_fetch_assoc($school_result);
        $school_id = $school_row['id'];
        $school_name = $school_row['name'];
        
        // Find klados by code or description
        $klados_query = "SELECT id, perigrafh, onoma FROM klados WHERE perigrafh = '$klados_input' OR onoma = '$klados_input' OR CONCAT(perigrafh, '-', onoma) = '$klados_input'";
        $klados_result = mysqli_query($mysqlconnection, $klados_query);
        
        if (!$klados_result || mysqli_num_rows($klados_result) == 0) {
            $errors++;
            $er_msg .= "Σφάλμα στη γραμμή $num: Ο κλάδος '$klados_input' δεν βρέθηκε.<br>";
            continue;
        }
        
        $klados_row = mysqli_fetch_assoc($klados_result);
        $klados_id = $klados_row['id'];
        $klados_name = $klados_row['perigrafh'] . ' - ' . $klados_row['onoma'];
        
        // Check if record already exists
        $check_query = "SELECT id FROM organikes WHERE school_id = $school_id AND klados_id = $klados_id AND fek = '$fek'";
        $check_result = mysqli_query($mysqlconnection, $check_query);
        
        if ($check_result && mysqli_num_rows($check_result) > 0) {
            // Update existing record
            $existing_id = mysqli_result($check_result, 0, "id");
            $update_query = "UPDATE organikes SET organikes = $organikes, fek = '$fek', comments = '$comments' WHERE id = $existing_id";
            
            if (mysqli_query($mysqlconnection, $update_query)) {
                $updates++;
                $log_entries[] = "Ενημερώθηκε: $school_name - $klados_name ($organikes θέσεις)";
            } else {
                $errors++;
                $er_msg .= "Σφάλμα στη γραμμή $num: Απέτυχε η ενημέρωση της εγγραφής.<br>";
            }
        } else {
            // Insert new record
            $insert_query = "INSERT INTO organikes (school_id, klados_id, organikes, fek, comments) 
                            VALUES ($school_id, $klados_id, $organikes, '$fek', '$comments')";
            
            if (mysqli_query($mysqlconnection, $insert_query)) {
                $saves++;
                $log_entries[] = "Προστέθηκε: $school_name - $klados_name ($organikes θέσεις)";
            } else {
                $errors++;
                $er_msg .= "Σφάλμα στη γραμμή $num: Απέτυχε η εισαγωγή της εγγραφής.<br>";
            }
        }
      }
      
      fclose($handle);
      
      // Display results
      echo "<div class='result-message result-success'>";
      echo "<h3>Αποτελέσματα Εισαγωγής</h3>";
      echo "<p><strong>Συνολικές γραμμές:</strong> $num</p>";
      echo "<p><strong>Νέες εγγραφές:</strong> $saves</p>";
      echo "<p><strong>Ενημερώσεις:</strong> $updates</p>";
      echo "<p><strong>Σφάλματα:</strong> $errors</p>";
      echo "</div>";
      
      if ($errors > 0) {
          echo "<div class='result-message result-error'>";
          echo "<h4>Λεπτομέρειες Σφαλμάτων:</h4>";
          echo $er_msg;
          echo "</div>";
      }
      
      if (!empty($log_entries)) {
          echo "<div class='import-section'>";
          echo "<h4>Καταγραφή Εγγραφών:</h4>";
          echo "<ul>";
          foreach ($log_entries as $entry) {
              echo "<li>$entry</li>";
          }
          echo "</ul>";
          echo "</div>";
      }
      
      echo "<div class='form-actions'>";
      echo "<INPUT TYPE='button' class='btn btn-primary' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
      echo "</div>";
      
      echo "</div>";
  } else {
      require '../etc/menu.php';
      echo "<div class='import-container'>";
      echo "<div class='result-message result-error'>";
      echo "<h3>Σφάλμα Μεταφόρτωσης</h3>";
      echo "<p>Δεν επιλέχθηκε αρχείο για μεταφόρτωση.</p>";
      echo "</div>";
      echo "<div class='form-actions'>";
      echo "<INPUT TYPE='button' class='btn btn-primary' VALUE='Επιστροφή' onClick=\"parent.location='import_organikes.php'\">";
      echo "</div>";
      echo "</div>";
  }
  
  //require '../etc/footer.php';
?>
</body>
</html>