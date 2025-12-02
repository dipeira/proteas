<?php
    header('Content-type: text/html; charset=utf-8'); 
    require_once "../config.php";
    require_once "../include/functions.php";
    
    // Demand authorization                
    require "../tools/class.login.php";
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false) {
        header("Location: ../tools/login.php");
    }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <script type="text/javascript" language="javascript" src='../js/jquery.js'></script>
        <?php 
        $root_path = '../';
        $page_title = 'Σχολεία';
        require '../etc/head.php'; 
        require_once '../js/datatables/includes.html';
        ?>
        <style type="text/css" title="currentStyle">
            .dataTables_wrapper {
              position: initial;
            }
            
            /* DataTables button styling */
            .dataTables_wrapper .dt-buttons {
                margin-bottom: 20px;
            }
            
            .dataTables_wrapper .dt-buttons .dt-button {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                border: none;
                border-radius: 8px;
                padding: 8px 16px;
                font-weight: 500;
                margin-right: 8px;
                transition: all 0.2s ease;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .dataTables_wrapper .dt-buttons .dt-button:hover {
                background: linear-gradient(135deg, #059669 0%, #047857 100%);
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }
            
            /* DataTables filter and length controls */
            .dataTables_wrapper .dataTables_filter input,
            .dataTables_wrapper .dataTables_length select {
                padding: 8px 12px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 14px;
                transition: all 0.2s ease;
            }
            
            .dataTables_wrapper .dataTables_filter input:focus,
            .dataTables_wrapper .dataTables_length select:focus {
                outline: none;
                border-color: #10b981;
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            }
            
            /* DataTables pagination */
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 6px 12px;
                margin: 0 2px;
                border-radius: 6px;
                border: 1px solid #e5e7eb;
                background: #ffffff;
                color: #374151;
                transition: all 0.2s ease;
            }
            
            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: #f3f4f6;
                border-color: #10b981;
                color: #059669;
            }
            
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                border-color: #10b981;
            }
            
            /* Modern page container */
            #container {
                /* max-width: 98%; */
                margin: 0 auto;
                /* padding: 20px; */
            }
            
            /* Page header styling */
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
            
            /* Table container with modern card styling */
            #sch-table {
                background: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                padding: 20px;
                margin-bottom: 20px;
                overflow-x: auto;
            }
            
            /* DataTable styling to match imagetable */
            #school-table {
                width: 100% !important;
                border-collapse: separate;
                border-spacing: 0;
            }
            
            #school-table thead th {
                background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 50%, #2A8B9A 100%);
                border: none !important;
                padding: 14px 16px;
                text-align: left;
                color: white;
                font-weight: 600;
                letter-spacing: 0.025em;
                text-transform: uppercase;
                font-size: 13px;
                word-wrap: break-word;
                overflow-wrap: break-word;
                word-break: break-word;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
            }
            
            #school-table thead th:first-child {
                border-top-left-radius: 12px;
            }
            
            #school-table thead th:last-child {
                border-top-right-radius: 12px;
            }
            
            #school-table tbody td {
                background: #ffffff;
                border: none !important;
                border-bottom: 1px solid #e5e7eb;
                padding: 12px 16px;
                transition: background-color 0.2s ease;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            
            #school-table tbody tr:nth-child(even) td {
                background-color: #f9fafb;
            }
            
            #school-table tbody tr:hover td {
                background-color: #f0fdf4;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }
            
            /* Action buttons styling */
            #school-table tbody td:first-child {
                white-space: nowrap;
            }
            
            #school-table tbody td:first-child a {
                display: inline-block;
                margin-right: 8px;
                transition: transform 0.2s ease;
            }
            
            #school-table tbody td:first-child a:hover {
                transform: scale(1.1);
            }
            
            /* Link styling */
            #school-table tbody td a {
                color: #667eea;
                text-decoration: none;
                transition: color 0.2s ease;
                font-weight: 500;
            }
            
            #school-table tbody td a:hover {
                color: #764ba2;
                text-decoration: underline;
            }
            
            /* Status indicators */
            .status-active {
                color: #059669;
                font-weight: 600;
                font-size: 1.1em;
            }
            
            .status-inactive {
                color: #dc2626;
                font-weight: 600;
            }
            
            /* Action buttons container */
            .action-buttons {
                margin-top: 20px;
                text-align: center;
            }
            
            /* Responsive adjustments */
            @media screen and (max-width: 768px) {
                #container {
                    padding: 10px;
                }
                
                #sch-table {
                    padding: 10px;
                }
                
                #school-table thead th,
                #school-table tbody td {
                    padding: 8px 10px;
                    font-size: 12px;
                }
            }
        </style>
        
        <!-- <script type="text/javascript" language="javascript" src="../js/datatables/jquery.dataTables.js"></script> -->
        
        <script type="text/javascript">
          $(document).ready(function() {
              /* Init DataTables */
              $('#school-table').DataTable({
                language: {
                  url: '../js/datatables/greek.json'
                },
                pageLength: 20,
                lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "Όλες"]],
                dom: 'Bfrtlp',
                buttons: [
                    {
                        extend: 'copy',
                        text: 'Αντιγραφή',
                    },
                    {
                        extend: 'excel',
                        text: 'Εξαγωγή σε excel',
                        filename: 'export'
                    },
                    {
                        extend: 'print',
                        text: 'Εκτύπωση',
                    }
                ]
              });
          } );
        </script>
    </head>
    
    <body id="dt_example">
    <?php require '../etc/menu.php'; ?>
    
    <div id="container">
        <div class="page-header">
            <h2>Σχολεία</h2>
        </div>
        
<?php
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
    
    $query = "SELECT * from school ORDER BY type,name ASC";
    $result = mysqli_query($mysqlconnection, $query);
    $num=mysqli_num_rows($result);
?>
<div id="sch-table">
<table cellpadding="0" cellspacing="0" border="0" id="school-table" class='display' style='width:100%'>
    <thead>
      <tr>
        <th style="max-width:75px">Ενεργ</th>
        <th style="max-width:200px">Όνομα</th>
        <th>Κωδ.ΥΠΑΙΘΑ</th>
        <th>Τηλέφωνο</th>
        <th>e-mail</th>
        <th>Οργ.</th>
        <th>Λειτ.</th>
        <th>Δ.Σ./Νηπ.</th>
        <th>Τύπος</th>
        <th>Ενεργό</th>
        <!-- <th>Τμ.Ένταξης</th>
        <th>Τμ.Υποδοχής</th>
        <th>Φροντιστ.Τμήμα</th>
        <th>Ολοήμερο</th> -->
      </tr>
    </thead>
    <tbody>
<?php
while ($row = mysqli_fetch_assoc($result))
{
    echo "<tr id='".$row['id']."' class='gradeA'>";  
    echo "<td>";
    echo "<span title=\"Προβολή\"><a href=\"school_status.php?org=".$row['id']."\" class=\"action-icon view\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M10 12a2 2 0 100-4 2 2 0 000 4z\"></path><path fill-rule=\"evenodd\" d=\"M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z\" clip-rule=\"evenodd\"></path></svg></a></span>";
    if ($usrlvl < 3) {
          echo "<span title=\"Επεξεργασία\"><a href=\"school_edit.php?org=".$row['id']."\" class=\"action-icon edit\"><svg fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z\"></path></svg></a></span>";
    }
    echo "</td>";
    
    echo "\n<td><a href='school_status.php?org=".$row['id']."'>".$row['name']."</a></td>";
    echo "\n<td>".$row['code']."</td>";
    echo "\n<td>".($row['tel'] ? preg_replace('/\s+/', '', $row['tel']) : '-')."</td>";
    echo "\n<td>".($row['email'] ? "<a href='mailto:".$row['email']."'>".$row['email']."</a>" : '-')."</td>";
    echo "\n<td>".$row['organikothta']."</td>";
    echo "\n<td>".get_leitoyrgikothta($row['id'], $mysqlconnection).'</td>';
    echo "<td>";
    switch ($row['type']) {
      case '0':
        echo "Λοιπά";
        break;
      case '1':
        echo "Δ.Σ.";
        break;
      case '2':
        echo "Νηπ.";
        break;
    }
    echo "</td>";
    echo "<td>";
    switch ($row['type2']) {
      case '0':
        echo "Δημόσιο";
        break;
      case '1':
        echo "Ιδιωτικό";
        break;
      case '2':
        echo "Ειδικό";
        break; 
    }
    echo "</td>";
    echo "<td>";
    if ($row['anenergo'] == 1) {
        echo "<span class='status-inactive'>Χ</span>";
    } else {
        echo "<span class='status-active'>&#10003;</span>";
    }
    echo "</td>";
    //echo "\n<td>".$row['entaksis']."</td>";
    //echo "\n<td>".$row['ypodoxis']."</td>";
    //echo "\n<td>".$row['front']."</td>";
    //echo "\n<td>".$row['olo']."</td>";
    echo "\n</tr>";
}
//<tr id="3" class="gradeA"> <tr id="2" class="gradeC"><tr id="1" class="gradeX">
?>
        
    </tbody>
    
</table>
</div>

<div class="action-buttons">
    <INPUT TYPE='button' VALUE='Επιστροφή' class="btn-red" onClick="parent.location='../index.php'">
</div>

</div>
    </body>
</html>
