<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

require_once "../config.php";
require_once "../include/functions.php";
require_once "../tools/class.login.php";

$log = new logmein();
if ($_SESSION['loggedin'] == false) {   
  header("Location: login.php");
  exit;
}

$usrlvl = $_SESSION['userlevel'];
if ($usrlvl > 1) {
  echo "<h3>Σφάλμα: Αυτή η ενέργεια μπορεί να γίνει μόνο από προϊστάμενο ή διαχειριστή...</h3>";
  echo "<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
  die();
}

$root_path = '../';
$page_title = 'Εισαγωγή υπηρετήσεων από αρχείο excel';
?>
<html>
  <head>
    <?php require '../etc/head.php'; ?>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <style>
      .import-card {
        max-width: 1000px;
        margin: 40px auto;
        padding: 35px;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border-top: 5px solid #2563eb;
        font-family: 'Outfit', 'Inter', system-ui, sans-serif;
      }
      .import-card h2 {
        color: #1e293b;
        font-size: 24px;
        margin-bottom: 8px;
        font-weight: 700;
        text-align: center;
      }
      .import-card p.subtitle {
        color: #64748b;
        text-align: center;
        margin-bottom: 25px;
        font-size: 15px;
      }
      .file-dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 40px 20px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.2s ease;
        cursor: pointer;
        margin-bottom: 25px;
      }
      .file-dropzone:hover {
        border-color: #2563eb;
        background: #f0fdf4;
      }
      .file-dropzone input[type="file"] {
        display: none;
      }
      .file-dropzone-icon {
        font-size: 48px;
        margin-bottom: 12px;
        display: inline-block;
      }
      .file-dropzone-text {
        color: #475569;
        font-size: 16px;
        font-weight: 500;
      }
      .file-dropzone-hint {
        color: #94a3b8;
        font-size: 13px;
        margin-top: 6px;
      }
      .btn-submit {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
        border: none;
        padding: 12px 28px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        width: 100%;
        text-align: center;
      }
      .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.3);
      }
      .btn-secondary {
        background: #e2e8f0;
        color: #334155;
        border: none;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        margin-top: 15px;
      }
      .btn-secondary:hover {
        background: #cbd5e1;
      }
      .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        line-height: 1.5;
      }
      .alert-warning {
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        color: #78350f;
      }
      .alert-info {
        background: #f0f9ff;
        border-left: 4px solid #0284c7;
        color: #0c4a6e;
      }
      .results-container {
        margin-top: 30px;
      }
      .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
      }
      .stat-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 18px;
        text-align: center;
      }
      .stat-card-title {
        font-size: 13px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 6px;
      }
      .stat-card-value {
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
      }
      .stat-card.success { border-left: 4px solid #10b981; }
      .stat-card.success .stat-card-value { color: #10b981; }
      .stat-card.warning { border-left: 4px solid #f59e0b; }
      .stat-card.warning .stat-card-value { color: #f59e0b; }
      .stat-card.danger { border-left: 4px solid #ef4444; }
      .stat-card.danger .stat-card-value { color: #ef4444; }
      
      .warning-list {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-radius: 8px;
        padding: 20px;
        max-height: 300px;
        overflow-y: auto;
        margin-bottom: 25px;
      }
      .warning-list h4 {
        margin-top: 0;
        color: #b45309;
        font-size: 15px;
        margin-bottom: 10px;
        font-weight: 600;
      }
      .warning-item {
        font-size: 13.5px;
        color: #78350f;
        margin-bottom: 6px;
        padding-bottom: 6px;
        border-bottom: 1px solid #fef3c7;
        text-align: left;
      }
      .warning-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
      }
      
      .preview-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        margin-bottom: 25px;
        font-size: 13px;
      }
      .preview-table th {
        background: #f1f5f9;
        color: #475569;
        padding: 10px;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        text-align: left;
      }
      .preview-table td {
        padding: 10px;
        border: 1px solid #e2e8f0;
        color: #334155;
      }
      .preview-table tr:hover {
        background: #f8fafc;
      }
    </style>
  </head>
  <body>
    <?php require '../etc/menu.php'; ?>
    <div class="import-card">
      <?php
      if (isset($_POST['submit'])) {
          if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
              $path = $_FILES['filename']['tmp_name'];
              
              require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
              require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
              
              $objPHPExcel = PHPExcel_IOFactory::load($path);
              $worksheet = $objPHPExcel->getSheet(0);
              $highestRow = $worksheet->getHighestRow();
              $highestColumn = $worksheet->getHighestColumn();
              $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
              
              // Database connection
              $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
              mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
              mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
              
              $sxol_etos = getParam('sxol_etos', $mysqlconnection);
              
              $imported = 0;
              $skipped = 0;
              $warnings_list = [];
              $total_rows = 0;
              
              $preview_rows = [];
              
              set_time_limit(480);
              
              for ($row = 3; $row <= $highestRow; ++ $row) {
                  $val = array();
                  for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                      $cell = $worksheet->getCellByColumnAndRow($col, $row);
                      $val[] = $cell->getValue();
                  }
                  
                  $afm = isset($val[1]) ? trim($val[1]) : '';
                  $surname = isset($val[2]) ? trim($val[2]) : '';
                  $name = isset($val[3]) ? trim($val[3]) : '';
                  $sxesh = isset($val[5]) ? trim($val[5]) : '';
                  $sxesh_topo = isset($val[6]) ? trim($val[6]) : '';
                  $sch_code = isset($val[7]) ? trim($val[7]) : '';
                  $sch_name = isset($val[8]) ? trim($val[8]) : '';
                  $hours = isset($val[14]) ? (int)trim($val[14]) : 0;
                  
                  if (empty($afm)) {
                      continue;
                  }
                  
                  $total_rows++;
                  
                  $date_from_php = isset($val[15]) ? ExcelToPHP($val[15]) : null;
                  $date_from = $date_from_php ? date("Y-m-d", $date_from_php) : null;
                  
                  $date_to_php = isset($val[16]) ? ExcelToPHP($val[16]) : null;
                  $date_to = $date_to_php ? date("Y-m-d", $date_to_php) : null;
                  
                  $state = isset($val[17]) ? trim($val[17]) : '';
                  
                  $emp_id = null;
                  $mon_anapl = null;
                  
                  $sxesh_lower = mb_strtolower($sxesh, 'UTF-8');
                  $accents = ['ά'=>'α', 'έ'=>'ε', 'ή'=>'η', 'ί'=>'ι', 'ό'=>'ο', 'ύ'=>'υ', 'ώ'=>'ω', 'ϊ'=>'ι', 'ϋ'=>'υ', 'ΐ'=>'ι', 'ΰ'=>'υ'];
                  $sxesh_normalized = strtr($sxesh_lower, $accents);
                  
                  $is_monimos = ($sxesh_normalized === 'μονιμος' || mb_strpos($sxesh_normalized, 'ιδιωτικου δικαιου') === 0);
                  $is_anapl = (mb_strpos($sxesh_normalized, 'αναπληρωτης') === 0);
                  
                  if ($is_monimos) {
                      $mon_anapl = 'Μόνιμος';
                      $emp_query = "SELECT id, surname, name FROM employee WHERE afm = '$afm' LIMIT 1";
                      $emp_res = mysqli_query($mysqlconnection, $emp_query);
                      if ($emp_res && mysqli_num_rows($emp_res) > 0) {
                          $emp_row = mysqli_fetch_assoc($emp_res);
                          $emp_id = $emp_row['id'];
                      } else {
                          $warnings_list[] = "Γραμμή $row: Ο μόνιμος/ιδ. δικαίου εκπαιδευτικός με ΑΦΜ <strong>$afm</strong> ($surname $name) δεν βρέθηκε στον πίνακα employee.";
                      }
                  } elseif ($is_anapl) {
                      $mon_anapl = 'Αναπληρωτής';
                      $emp_query = "SELECT id, surname, name FROM ektaktoi WHERE afm = '$afm' LIMIT 1";
                      $emp_res = mysqli_query($mysqlconnection, $emp_query);
                      if ($emp_res && mysqli_num_rows($emp_res) > 0) {
                          $emp_row = mysqli_fetch_assoc($emp_res);
                          $emp_id = $emp_row['id'];
                      } else {
                          $warnings_list[] = "Γραμμή $row: Ο αναπληρωτής εκπαιδευτικός με ΑΦΜ <strong>$afm</strong> ($surname $name) δεν βρέθηκε στον πίνακα ektaktoi.";
                      }
                  } else {
                      $warnings_list[] = "Γραμμή $row: Άγνωστη σχέση εργασίας '<strong>$sxesh</strong>' για εκπαιδευτικό με ΑΦΜ $afm ($surname $name).";
                  }
                  
                  $sch_id = null;
                  if (!empty($sch_code)) {
                      $sch_query = "SELECT id, name FROM school WHERE code = '$sch_code' LIMIT 1";
                      $sch_res = mysqli_query($mysqlconnection, $sch_query);
                      if ($sch_res && mysqli_num_rows($sch_res) > 0) {
                          $sch_row = mysqli_fetch_assoc($sch_res);
                          $sch_id = $sch_row['id'];
                      } else {
                          $warnings_list[] = "Γραμμή $row: Το σχολείο με κωδικό <strong>$sch_code</strong> ($sch_name) δεν βρέθηκε στον πίνακα school.";
                      }
                  } else {
                      $warnings_list[] = "Γραμμή $row: Δεν καθορίστηκε κωδικός σχολείου για τον εκπαιδευτικό με ΑΦΜ $afm ($surname $name).";
                  }
                  
                  // Check if row already exists
                  $check_query = "SELECT id FROM yphrethsh_ext 
                                  WHERE afm = '$afm' 
                                    AND sch_code = '$sch_code' 
                                    AND date_from = " . ($date_from ? "'$date_from'" : "NULL") . " 
                                    AND date_to = " . ($date_to ? "'$date_to'" : "NULL") . "
                                    AND sxol_etos = '$sxol_etos'
                                  LIMIT 1";
                  $check_res = mysqli_query($mysqlconnection, $check_query);
                  
                  if ($check_res && mysqli_num_rows($check_res) > 0) {
                      $skipped++;
                      continue;
                  }
                  
                  // Insert record
                  $sql = "INSERT INTO yphrethsh_ext (afm, mon_anapl, emp_id, sxesh, sxesh_topo, sch_code, sch_id, date_from, date_to, hours, state, sxol_etos)
                          VALUES (
                            " . ($afm ? "'$afm'" : "NULL") . ",
                            " . ($mon_anapl ? "'$mon_anapl'" : "NULL") . ",
                            " . ($emp_id ? $emp_id : "NULL") . ",
                            " . ($sxesh ? "'$sxesh'" : "NULL") . ",
                            " . ($sxesh_topo ? "'$sxesh_topo'" : "NULL") . ",
                            " . ($sch_code ? "'$sch_code'" : "NULL") . ",
                            " . ($sch_id ? $sch_id : "NULL") . ",
                            " . ($date_from ? "'$date_from'" : "NULL") . ",
                            " . ($date_to ? "'$date_to'" : "NULL") . ",
                            " . $hours . ",
                            " . ($state ? "'$state'" : "NULL") . ",
                            " . ($sxol_etos ? "'$sxol_etos'" : "NULL") . "
                          )";
                  
                  if (mysqli_query($mysqlconnection, $sql)) {
                      $imported++;
                      if (count($preview_rows) < 5) {
                          $preview_rows[] = [
                              'afm' => $afm,
                              'name' => "$surname $name",
                              'sxesh' => $sxesh,
                              'sxesh_topo' => $sxesh_topo,
                              'school' => $sch_name . " ($sch_code)",
                              'date_from' => $date_from,
                              'date_to' => $date_to,
                              'hours' => $hours,
                              'state' => $state
                          ];
                      }
                  } else {
                      $warnings_list[] = "Γραμμή $row: Αποτυχία εισαγωγής για ΑΦΜ $afm: " . mysqli_error($mysqlconnection);
                  }
              }
              
              mysqli_close($mysqlconnection);
              
              ?>
              <h2>Αποτελέσματα Εισαγωγής</h2>
              <p class="subtitle">Σχολικό Έτος: <?php echo htmlspecialchars($sxol_etos); ?></p>
              
              <div class="results-container">
                <div class="stats-grid">
                  <div class="stat-card success">
                    <div class="stat-card-title">Εισήχθησαν</div>
                    <div class="stat-card-value"><?php echo $imported; ?></div>
                  </div>
                  <div class="stat-card warning">
                    <div class="stat-card-title">Παρακάμφθηκαν (Διπλότυπα)</div>
                    <div class="stat-card-value"><?php echo $skipped; ?></div>
                  </div>
                  <div class="stat-card danger">
                    <div class="stat-card-title">Προειδοποιήσεις</div>
                    <div class="stat-card-value"><?php echo count($warnings_list); ?></div>
                  </div>
                </div>
                
                <?php if (count($warnings_list) > 0): ?>
                  <div class="warning-list">
                    <h4>⚠️ Προειδοποιήσεις / Εκκρεμότητες</h4>
                    <?php foreach ($warnings_list as $warn): ?>
                      <div class="warning-item"><?php echo $warn; ?></div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                
                <?php if (count($preview_rows) > 0): ?>
                  <h3>Δείγμα Εγγραφών που Εισήχθησαν</h3>
                  <table class="preview-table">
                    <thead>
                      <tr>
                        <th>ΑΦΜ</th>
                        <th>Ονοματεπώνυμο</th>
                        <th>Σχέση Εργασίας</th>
                        <th>Σχέση Τοποθέτησης</th>
                        <th>Σχολείο</th>
                        <th>Από</th>
                        <th>Έως</th>
                        <th>Ώρες</th>
                        <th>Κατάσταση</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($preview_rows as $row): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($row['afm']); ?></td>
                          <td><?php echo htmlspecialchars($row['name']); ?></td>
                          <td><?php echo htmlspecialchars($row['sxesh']); ?></td>
                          <td><?php echo htmlspecialchars($row['sxesh_topo']); ?></td>
                          <td><?php echo htmlspecialchars($row['school']); ?></td>
                          <td><?php echo htmlspecialchars($row['date_from']); ?></td>
                          <td><?php echo htmlspecialchars($row['date_to']); ?></td>
                          <td><?php echo htmlspecialchars($row['hours']); ?></td>
                          <td><?php echo htmlspecialchars($row['state']); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php endif; ?>
                
                <div style="text-align: center; margin-top: 30px;">
                  <a href="import_yphrethsh.php" class="btn-submit" style="text-decoration: none; display: inline-block; width: auto; padding: 12px 35px;">Εισαγωγή νέου αρχείου</a>
                  <br>
                  <a href="import.php" class="btn-secondary">Επιστροφή στις Εισαγωγές</a>
                </div>
              </div>
              <?php
          } else {
              echo "<div class='alert alert-warning'>Σφάλμα: Δεν μεταφορτώθηκε αρχείο.</div>";
              echo "<a href='import_yphrethsh.php' class='btn-secondary'>Δοκιμάστε Ξανά</a>";
          }
      } else {
          // Display Upload Form
          ?>
          <h2>Εισαγωγή Υπηρετήσεων από Excel (.xls)</h2>
          <p class="subtitle">Εισαγωγή δεδομένων υπηρετήσεων στον πίνακα yphrethsh_ext</p>
          
          <div class="alert alert-info">
            <strong>Πληροφορίες αρχείου Excel:</strong><br>
            • Το αρχείο πρέπει να είναι μορφής Excel 97-2003 (<strong>.xls</strong>).<br>
            • Οι γραμμές 1 και 2 θεωρούνται κεφαλίδες. Τα δεδομένα πρέπει να ξεκινούν από τη <strong>γραμμή 3</strong>.<br>
            • Απαιτούμενες στήλες: <strong>Α.Φ.Μ.</strong> (στήλη B), <strong>Σχέση εργασίας</strong> (στήλη F), <strong>Κωδικός σχολείου</strong> (στήλη H), <strong>Ώρες</strong> (στήλη O), <strong>Από</strong> (στήλη P), <strong>Έως</strong> (στήλη Q), <strong>Κατάσταση</strong> (στήλη R).
          </div>
          
          <form enctype="multipart/form-data" action="import_yphrethsh.php" method="post">
            <div class="file-dropzone" onclick="document.getElementById('file-input').click();">
              <span class="file-dropzone-icon">📥</span>
              <div class="file-dropzone-text" id="file-text">Επιλέξτε ή σύρετε το αρχείο Excel (.xls) εδώ</div>
              <div class="file-dropzone-hint">Υποστηρίζεται μόνο μορφή .xls</div>
              <input type="file" id="file-input" name="filename" accept=".xls" onchange="updateFileName(this);" required>
            </div>
            
            <button type="submit" name="submit" class="btn-submit">Μεταφόρτωση και Εισαγωγή</button>
            
            <div style="text-align: center; margin-top: 15px;">
              <a href="import.php" class="btn-secondary">Επιστροφή</a>
            </div>
          </form>
          
          <script>
          function updateFileName(input) {
              var fileText = document.getElementById('file-text');
              if (input.files && input.files.length > 0) {
                  fileText.innerHTML = "<strong>Επιλεγμένο αρχείο:</strong> " + input.files[0].name;
                  fileText.style.color = "#10b981";
              } else {
                  fileText.innerHTML = "Επιλέξτε ή σύρετε το αρχείο Excel (.xls) εδώ";
                  fileText.style.color = "#475569";
              }
          }
          </script>
          <?php
      }
      ?>
    </div>
  </body>
</html>
