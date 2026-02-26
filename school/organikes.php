<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once "../config.php";
  require_once "../include/functions.php";

  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

  // Demand authorization                
  require "../tools/class.login.php";
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false) {
      header("Location: ../tools/login.php");
  }

  // Get school id
  if (isset($_GET['id'])) {
      $sch = intval($_GET['id']);
  } else {
      die('No school specified.');
  }

  // Get school name (only if id != 0)
  if ($sch == 0) {
      $school_name = 'Όλα τα Σχολεία';
  } else {
      $school_name = getSchool($sch, $mysqlconnection);
  }

  // Get action parameter
  $action = isset($_GET['action']) ? $_GET['action'] : 'list';

  // Handle POST requests
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $post_action = isset($_POST['action']) ? $_POST['action'] : '';
      
      if ($post_action === 'add' || $post_action === 'edit') {
          $klados_id = intval($_POST['klados_id']);
          $organikes = intval($_POST['organikes']);
          $fek = mysqli_real_escape_string($mysqlconnection, $_POST['fek']);
          $comments = mysqli_real_escape_string($mysqlconnection, $_POST['comments']);
          
          if ($post_action === 'add') {
              $query = "INSERT INTO organikes (school_id, klados_id, organikes, fek, comments) 
                        VALUES ($sch, $klados_id, $organikes, '$fek', '$comments')";
              mysqli_query($mysqlconnection, $query);
          } elseif ($post_action === 'edit') {
              $record_id = intval($_POST['record_id']);
              $query = "UPDATE organikes SET klados_id = $klados_id, organikes = $organikes, 
                        fek = '$fek', comments = '$comments' WHERE id = $record_id";
              mysqli_query($mysqlconnection, $query);
          }
          
          header("Location: organikes.php?id=$sch&action=list");
          exit;
      }
  }

  // Handle delete action
  if ($action === 'delete' && isset($_GET['record_id'])) {
      $record_id = intval($_GET['record_id']);
      $query = "DELETE FROM organikes WHERE id = $record_id";
      mysqli_query($mysqlconnection, $query);
      header("Location: organikes.php?id=$sch&action=list");
      exit;
  }

  // Get all klados for dropdown
  $klados_query = "SELECT * FROM klados ORDER BY perigrafh";
  $klados_result = mysqli_query($mysqlconnection, $klados_query);
  $klados_options = '';
  while ($klados_row = mysqli_fetch_assoc($klados_result)) {
      $klados_options .= "<option value='{$klados_row['id']}'>{$klados_row['perigrafh']} - {$klados_row['onoma']}</option>";
  }
?>
<html>
  <head>
    <?php
    $root_path = '../';
    $page_title = 'Οργανικές Θέσεις';
    require '../etc/head.php';
    ?>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Οργανικές Θέσεις</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="../js/datatables/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../js/datatables/datatables.css.min">
    <style type="text/css">
      .info-section {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
        overflow: hidden;
      }
      
      .info-section-header {
        background: linear-gradient(135deg, #4f8188 0%, #30b7cb 100%);
        color: #ffffff;
        padding: 16px 24px;
        font-weight: 600;
        font-size: 1.1rem;
      }
      
      .info-section-content {
        padding: 24px;
      }
      
      .organikes-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
      }
      
      .organikes-table th {
        background: #f9fafb;
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e7eb;
      }
      
      .organikes-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
      }
      
      .organikes-table tbody tr:hover {
        background: #f9fafb;
      }
      
      .action-buttons {
        margin-top: 24px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
      }
      
      .action-buttons input[type="button"],
      .action-buttons input[type="submit"] {
        padding: 10px 24px;
        background: #667eea;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.1s ease;
      }
      
      .action-buttons input[type="button"]:hover,
      .action-buttons input[type="submit"]:hover {
        background: #764ba2;
        transform: translateY(-1px);
      }
      
      .action-buttons input[type="button"].btn-secondary {
        background: #6b7280;
      }
      
      .action-buttons input[type="button"].btn-secondary:hover {
        background: #4b5563;
      }
      
      .action-buttons input[type="button"].btn-danger {
        background: #ef4444;
      }
      
      .action-buttons input[type="button"].btn-danger:hover {
        background: #dc2626;
      }
      
      .action-link {
        display: inline-block;
        padding: 6px 12px;
        margin-right: 4px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.2s ease;
      }
      
      .action-link.view {
        background: #dbeafe;
        color: #1d4ed8;
      }
      
      .action-link.view:hover {
        background: #bfdbfe;
      }
      
      .action-link.edit {
        background: #fef3c7;
        color: #d97706;
      }
      
      .action-link.edit:hover {
        background: #fde68a;
      }
      
      .action-link.delete {
        background: #fee2e2;
        color: #dc2626;
      }
      
      .action-link.delete:hover {
        background: #fecaca;
      }
      
      .form-group {
        margin-bottom: 20px;
      }
      
      .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #374151;
      }
      
      .form-group input[type="text"],
      .form-group input[type="number"],
      .form-group select,
      .form-group textarea {
        width: 100%;
        max-width: 400px;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.2s ease;
      }
      
      .form-group input:focus,
      .form-group select:focus,
      .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      }
      
      .form-group textarea {
        min-height: 100px;
        resize: vertical;
      }
      
      .view-details {
        background: #f9fafb;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
      }
      
      .view-details .detail-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #e5e7eb;
      }
      
      .view-details .detail-row:last-child {
        border-bottom: none;
      }
      
      .view-details .detail-label {
        width: 150px;
        font-weight: 600;
        color: #6b7280;
      }
      
      .view-details .detail-value {
        flex: 1;
        color: #111827;
      }
      
      .no-records {
        text-align: center;
        padding: 40px;
        color: #6b7280;
        font-style: italic;
      }
    </style>
  </head>
  <script src="../js/jquery.js"></script>
  <script src="../js/datatables/jquery.dataTables.min.js"></script>
  <body> 
    <?php require '../etc/menu.php'; ?>
    <center>
        <h2>Οργανικές Θέσεις - <?php echo $school_name; ?></h2>
        
        <div class="action-buttons" style="margin-bottom: 20px;">
            <input type="button" value="Επιστροφή" onclick="parent.location='../index.php'" class="btn-secondary">
            <?php if ($sch != 0): ?>
              <input type="button" value="Καρτέλα σχολείου" onclick="parent.location='school_status.php?org=<?php echo $sch; ?>'">
            <?php endif; ?>
        </div>
        
        <div id="content">
    <?php

  // LIST ACTION
  if ($action === 'list') {
      if ($sch == 0) {
          // Show all records with school name
          $query = "SELECT o.*, k.perigrafh, k.onoma, s.name as school_name
                    FROM organikes o
                    LEFT JOIN klados k ON o.klados_id = k.id
                    LEFT JOIN school s ON o.school_id = s.id
                    ORDER BY s.name, k.perigrafh";
      } else {
          // Show records for specific school
          $query = "SELECT o.*, k.perigrafh, k.onoma
                    FROM organikes o
                    LEFT JOIN klados k ON o.klados_id = k.id
                    WHERE o.school_id = $sch
                    ORDER BY k.perigrafh";
      }
      $result = mysqli_query($mysqlconnection, $query);
      $num = mysqli_num_rows($result);
      
      echo "<div class='info-section'>";
      echo "<div class='info-section-header'>Λίστα Οργανικών Θέσεων</div>";
      echo "<div class='info-section-content'>";
      
      if ($num > 0) {
          echo "<table id='organikesTable' class='organikes-table'>";
          echo "<thead><tr>";
          if ($sch == 0) {
              echo "<th>Σχολείο</th>";
          }
          echo "<th>Κλάδος</th>";
          echo "<th>Πλήθος Οργανικών</th>";
          echo "<th>ΦΕΚ</th>";
          echo "<th>Σχόλια</th>";
          echo "<th>Ενέργειες</th>";
          echo "</tr></thead>";
          echo "<tbody>";
          
          while ($row = mysqli_fetch_assoc($result)) {
              $id = $row['id'];
              $schid = $row['school_id'];
              $klados = $row['perigrafh'] . ' - ' . $row['onoma'];
              $organikes = $row['organikes'];
              $fek = htmlspecialchars($row['fek']);
              $comments = htmlspecialchars($row['comments']);
              
              echo "<tr>";
              if ($sch == 0) {
                  $school_name_display = htmlspecialchars($row['school_name']);
                  echo "<td><a href='school_status.php?org=$schid'>$school_name_display</a></td>";
              }
              echo "<td>$klados</td>";
              echo "<td>$organikes</td>";
              echo "<td>$fek</td>";
              echo "<td>".shorten_text($comments, 30)."</td>";
              echo "<td>";
              echo "<a href='organikes.php?id=$schid&action=view&record_id=$id' class='action-link view'>Προβολή</a>";
              echo "<a href='organikes.php?id=$schid&action=edit&record_id=$id' class='action-link edit'>Επεξεργασία</a>";
              echo "<a href='organikes.php?id=$schid&action=delete&record_id=$id' class='action-link delete' onclick='return confirm(\"Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτή την εγγραφή;\");'>Διαγραφή</a>";
              echo "</td>";
              echo "</tr>";
          }
          
          echo "</tbody>";
          echo "</table>";
      } else {
          echo "<div class='no-records'>Δεν υπάρχουν εγγραφές οργανικών θέσεων.</div>";
      }
      
      echo "<div class='action-buttons'>";
      if ($sch != 0) {
          echo "<input type='button' value='Προσθήκη Οργανικής Θέσης' onclick=\"parent.location='organikes.php?id=$sch&action=add'\">";
      }
      echo "</div>";
      
      echo "</div>"; // info-section-content
      echo "</div>"; // info-section
  }

  // VIEW ACTION
  elseif ($action === 'view' && isset($_GET['record_id'])) {
      $record_id = intval($_GET['record_id']);
      $query = "SELECT o.*, k.perigrafh, k.onoma 
                FROM organikes o 
                LEFT JOIN klados k ON o.klados_id = k.id 
                WHERE o.id = $record_id";
      $result = mysqli_query($mysqlconnection, $query);
      
      if ($row = mysqli_fetch_assoc($result)) {
          $klados = $row['perigrafh'] . ' - ' . $row['onoma'];
          $organikes = $row['organikes'];
          $fek = htmlspecialchars($row['fek']);
          $comments = htmlspecialchars($row['comments']);
          
          echo "<div class='info-section'>";
          echo "<div class='info-section-header'>Προβολή Οργανικής Θέσης</div>";
          echo "<div class='info-section-content'>";
          
          echo "<div class='view-details'>";
          echo "<div class='detail-row'>";
          echo "<span class='detail-label'>Κλάδος:</span>";
          echo "<span class='detail-value'>$klados</span>";
          echo "</div>";
          echo "<div class='detail-row'>";
          echo "<span class='detail-label'>Πλήθος Οργανικών:</span>";
          echo "<span class='detail-value'>$organikes</span>";
          echo "</div>";
          echo "<div class='detail-row'>";
          echo "<span class='detail-label'>ΦΕΚ:</span>";
          echo "<span class='detail-value'>$fek</span>";
          echo "</div>";
          echo "<div class='detail-row'>";
          echo "<span class='detail-label'>Σχόλια:</span>";
          echo "<span class='detail-value'>$comments</span>";
          echo "</div>";
          echo "</div>";
          
          echo "<div class='action-buttons'>";
          echo "<input type='button' value='Επιστροφή στη λίστα' onclick=\"parent.location='organikes.php?id=$sch&action=list'\">";
          echo "<input type='button' value='Επεξεργασία' onclick=\"parent.location='organikes.php?id=$sch&action=edit&record_id=$record_id'\">";
          echo "</div>";
          
          echo "</div>"; // info-section-content
          echo "</div>"; // info-section
      } else {
          echo "<div class='no-records'>Η εγγραφή δεν βρέθηκε.</div>";
      }
  }

  // ADD ACTION
  elseif ($action === 'add') {
      echo "<div class='info-section'>";
      echo "<div class='info-section-header'>Προσθήκη Οργανικής Θέσης</div>";
      echo "<div class='info-section-content'>";
      
      echo "<form method='POST' action='organikes.php?id=$sch&action=list'>";
      echo "<input type='hidden' name='action' value='add'>";
      
      echo "<div class='form-group'>";
      echo "<label for='klados_id'>Κλάδος:</label>";
      echo "<select name='klados_id' id='klados_id' required>";
      echo "<option value=''>-- Επιλέξτε κλάδο --</option>";
      echo $klados_options;
      echo "</select>";
      echo "</div>";
      
      echo "<div class='form-group'>";
      echo "<label for='organikes'>Πλήθος Οργανικών:</label>";
      echo "<input type='number' name='organikes' id='organikes' min='0' required>";
      echo "</div>";
      
      echo "<div class='form-group'>";
      echo "<label for='fek'>ΦΕΚ:</label>";
      echo "<input type='text' name='fek' id='fek'>";
      echo "</div>";
      
      echo "<div class='form-group'>";
      echo "<label for='comments'>Σχόλια:</label>";
      echo "<textarea name='comments' id='comments'></textarea>";
      echo "</div>";
      
      echo "<div class='action-buttons'>";
      echo "<input type='submit' value='Αποθήκευση'>";
      echo "<input type='button' value='Ακύρωση' onclick=\"parent.location='organikes.php?id=$sch&action=list'\" class='btn-secondary'>";
      echo "</div>";
      
      echo "</form>";
      
      echo "</div>"; // info-section-content
      echo "</div>"; // info-section
  }

  // EDIT ACTION
  elseif ($action === 'edit' && isset($_GET['record_id'])) {
      $record_id = intval($_GET['record_id']);
      $query = "SELECT * FROM organikes WHERE id = $record_id";
      $result = mysqli_query($mysqlconnection, $query);
      
      if ($row = mysqli_fetch_assoc($result)) {
          $klados_id = $row['klados_id'];
          $organikes = $row['organikes'];
          $fek = htmlspecialchars($row['fek']);
          $comments = htmlspecialchars($row['comments']);
          
          echo "<div class='info-section'>";
          echo "<div class='info-section-header'>Επεξεργασία Οργανικής Θέσης</div>";
          echo "<div class='info-section-content'>";
          
          echo "<form method='POST' action='organikes.php?id=$sch&action=list'>";
          echo "<input type='hidden' name='action' value='edit'>";
          echo "<input type='hidden' name='record_id' value='$record_id'>";
          
          echo "<div class='form-group'>";
          echo "<label for='klados_id'>Κλάδος:</label>";
          echo "<select name='klados_id' id='klados_id' required>";
          echo "<option value=''>-- Επιλέξτε κλάδο --</option>";
          // Build options with selected value
          $klados_result2 = mysqli_query($mysqlconnection, $klados_query);
          while ($klados_row = mysqli_fetch_assoc($klados_result2)) {
              $selected = ($klados_row['id'] == $klados_id) ? 'selected' : '';
              echo "<option value='{$klados_row['id']}' $selected>{$klados_row['perigrafh']} - {$klados_row['onoma']}</option>";
          }
          echo "</select>";
          echo "</div>";
          
          echo "<div class='form-group'>";
          echo "<label for='organikes'>Πλήθος Οργανικών:</label>";
          echo "<input type='number' name='organikes' id='organikes' value='$organikes' min='0' required>";
          echo "</div>";
          
          echo "<div class='form-group'>";
          echo "<label for='fek'>ΦΕΚ:</label>";
          echo "<input type='text' name='fek' id='fek' value='$fek'>";
          echo "</div>";
          
          echo "<div class='form-group'>";
          echo "<label for='comments'>Σχόλια:</label>";
          echo "<textarea name='comments' id='comments'>$comments</textarea>";
          echo "</div>";
          
          echo "<div class='action-buttons'>";
          echo "<input type='submit' value='Αποθήκευση'>";
          echo "<input type='button' value='Ακύρωση' onclick=\"parent.location='organikes.php?id=$sch&action=list'\" class='btn-secondary'>";
          echo "</div>";
          
          echo "</form>";
          
          echo "</div>"; // info-section-content
          echo "</div>"; // info-section
      } else {
          echo "<div class='no-records'>Η εγγραφή δεν βρέθηκε.</div>";
      }
  }

  // INVALID ACTION
  else {
      echo "<div class='no-records'>Μη έγκυρη ενέργεια.</div>";
  }

  echo "</div>"; // content
  echo "</center>";
  //require '../etc/footer.php';
?>
<script>
$(document).ready(function() {
    $('#organikesTable').DataTable({
        language: {
            url: "../js/datatables/greek.json"
        },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Όλα"]],
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });
});
</script>
