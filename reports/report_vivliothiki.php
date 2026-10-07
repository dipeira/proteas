<?php
header('Content-type: text/html; charset=utf-8');
require_once "../config.php";
require_once "../include/functions.php";
require_once "../tools/class.login.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$log = new logmein();
if ($log->logincheck($_SESSION['loggedin'] ?? '') == false) {
    header("Location: ../tools/login.php");
    exit();
}

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

$query = "SELECT 
            s.id AS sid,
            s.name AS school_name,
            s.code AS school_code,
            e.id AS eid,
            e.am,
            e.surname,
            e.name AS emp_name,
            k.perigrafh AS klados
          FROM school s
          JOIN employee e ON s.vivliothiki = e.id
          JOIN klados k ON e.klados = k.id
          WHERE s.vivliothiki > 0 AND s.vivliothiki IS NOT NULL AND (s.anenergo = 0 OR s.anenergo IS NULL)
          ORDER BY s.name ASC";

$result = mysqli_query($mysqlconnection, $query);
$num_rows = $result ? mysqli_num_rows($result) : 0;
?>
<!DOCTYPE html>
<html>
<head>
  <?php
  $root_path = '../';
  $page_title = 'Αναφορά Υπευθύνων Βιβλιοθήκης';
  require '../etc/head.php';
  ?>
  <link href="../css/style.css" rel="stylesheet" type="text/css">
  <script type="text/javascript" src="../js/jquery.js"></script>
  <?php require_once('../js/datatables/includes.html'); ?>
  <style>
    .page-container {
      max-width: 1400px;
      margin: 20px auto;
      padding: 0 15px;
    }
    .page-header {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 2px solid #e5e7eb;
    }
    .page-header h2 {
      margin: 0;
      color: #1f2937;
      font-size: 1.5rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .stat-badge {
      display: inline-flex;
      align-items: center;
      background: #e0f2fe;
      color: #0369a1;
      font-size: 0.95rem;
      font-weight: 600;
      padding: 6px 14px;
      border-radius: 9999px;
      border: 1px solid #bae6fd;
    }
    .table-container {
      background: #ffffff;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.08);
      padding: 16px;
      border: 1px solid #e5e7eb;
    }
    #library-table {
      width: 100% !important;
      border-collapse: separate;
      border-spacing: 0;
    }
    #library-table thead th {
      background: #f3f4f6;
      color: #374151;
      font-weight: 600;
      font-size: 0.875rem;
      padding: 10px 12px;
      border-bottom: 2px solid #d1d5db;
      white-space: nowrap;
    }
    #library-table tbody td {
      padding: 8px 12px;
      font-size: 0.875rem;
      border-bottom: 1px solid #e5e7eb;
      vertical-align: middle;
    }
    #library-table tbody tr:hover {
      background-color: #f0fdf4 !important;
    }
    .link-primary {
      color: #0284c7;
      text-decoration: underline;
      font-weight: 500;
    }
    .link-primary:hover {
      color: #0369a1;
    }
    .dt-buttons {
      margin-bottom: 12px;
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .dt-button {
      border-radius: 6px !important;
      padding: 6px 14px !important;
      font-weight: 500 !important;
      font-size: 0.85rem !important;
    }
    .btn-red {
      color: #ffffff !important;
      cursor: pointer;
    }
  </style>
  <script type="text/javascript">
    $(document).ready(function() {
      $('#library-table').DataTable({
        pageLength: 25,
        lengthMenu: [
          [10, 25, 50, 100, -1],
          [10, 25, 50, 100, 'Όλα']
        ],
        order: [[2, 'asc']], // Ταξινόμηση ανά Όνομα Σχολείου
        language: {
          url: '../js/datatables/greek.json'
        },
        dom: 'Bfrtlip',
        buttons: [
          {
            extend: 'excel',
            text: 'Εξαγωγή σε Excel',
            className: 'btn-excel',
            title: 'Υπεύθυνοι Βιβλιοθήκης',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6]
            }
          },
          {
            extend: 'print',
            text: 'Εκτύπωση',
            className: 'btn-primary',
            title: 'Υπεύθυνοι Σχολικών Βιβλιοθηκών',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6]
            }
          },
          {
            extend: 'copy',
            text: 'Αντιγραφή',
            className: 'btn-yellow',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6]
            }
          }
        ]
      });
    });
  </script>
</head>

<body>
  <?php require '../etc/menu.php'; ?>

  <div class="page-container">
    <div class="page-header">
      <h2>
        <span>📚</span> Αναφορά Υπευθύνων Σχολικής Βιβλιοθήκης
      </h2>
      <div class="stat-badge">
        Σύνολο σχολείων: <span style="margin-left: 6px; font-weight: 700;"><?= $num_rows ?></span>
      </div>
    </div>

    <div style="margin-bottom: 16px;">
      <input type="button" class="btn btn-red" value="Επιστροφή" onClick="parent.location='../index.php'">
    </div>

    <div class="table-container">
      <?php
      ob_start();
      ?>
      <table id="library-table" class="imagetable display" border="1">
        <thead>
          <tr>
            <th style="text-align: center; width: 45px;">Α/Α</th>
            <th style="text-align: center; width: 90px;">Κωδικός</th>
            <th style="text-align: left;">Όνομα Σχολείου</th>
            <th style="text-align: center; width: 90px;">ΑΜ</th>
            <th style="text-align: left;">Επώνυμο</th>
            <th style="text-align: left;">Όνομα</th>
            <th style="text-align: center; width: 90px;">Κλάδος</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($num_rows > 0) {
              $i = 0;
              while ($row = mysqli_fetch_assoc($result)) {
                  $i++;
                  $sid = htmlspecialchars($row['sid']);
                  $sname = htmlspecialchars($row['school_name']);
                  $code = htmlspecialchars($row['school_code']);
                  $eid = htmlspecialchars($row['eid']);
                  $am = htmlspecialchars($row['am']);
                  $surname = htmlspecialchars($row['surname']);
                  $emp_name = htmlspecialchars($row['emp_name']);
                  $klados = htmlspecialchars($row['klados']);

                  echo "<tr>";
                  echo "<td style='text-align: center;'>$i</td>";
                  echo "<td style='text-align: center;'>$code</td>";
                  echo "<td><a class='link-primary' href='../school/school_status.php?org=$sid' target='_blank'>$sname</a></td>";
                  echo "<td style='text-align: center;'>$am</td>";
                  echo "<td><a class='link-primary' href='../employee/employee.php?id=$eid&op=view' target='_blank'>$surname</a></td>";
                  echo "<td>$emp_name</td>";
                  echo "<td style='text-align: center;'><span class='badge' style='background: #f3f4f6; padding: 2px 8px; border-radius: 4px; font-weight: 600;'>$klados</span></td>";
                  echo "</tr>";
              }
          }
          ?>
        </tbody>
      </table>
      <?php
      $table_content = ob_get_contents();
      $_SESSION['page'] = $table_content;
      ob_end_flush();
      ?>
    </div>

    <div style="margin-top: 25px; margin-bottom: 25px; text-align: center;">
      <form action="../tools/2excel_ses.php" method="post" style="display: inline-block;">
        <button type="submit" class="btn btn-excel" style="display: inline-flex; align-items: center; gap: 6px; vertical-align: middle;">
          <img src="../images/excel.png" alt="Excel" style="width: 16px; height: 16px;">
          Εξαγωγή στο excel
        </button>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <input type="button" class="btn btn-red" value="Επιστροφή" onClick="parent.location='../index.php'">
      </form>
    </div>
  </div>
</body>
</html>
