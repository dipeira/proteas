<?php
    header('Content-type: text/html; charset=utf-8'); 
    require_once"../config.php";
    require_once"../include/functions.php";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <?php 
        $root_path = '../';
        $page_title = 'Σχολείο';
        require '../etc/head.php'; 
        ?>
        <style type="text/css" title="currentStyle">
            /* @import "../js/datatables/datatables.css.min"; */
            @import "../js/datatables/datatables.css.min";
            .dataTables_wrapper {
              position: initial;
            }
        </style>
        <script type="text/javascript" language="javascript" src="../js/jquery.js"></script>
        <!-- <script type="text/javascript" language="javascript" src="../js/datatables/jquery.dataTables.js"></script> -->
        <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.tailwindcss.css">
        <script src="https://cdn.datatables.net/2.3.5/js/dataTables.tailwindcss.js"></script>
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
    <center>
      <h2>Σχολεία</h2>
    </center>
        <div id="container">
<?php
    require_once"../config.php";
    require_once('../js/datatables/includes.html');
    //require_once"../include/functions.php";
        
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
    
    $query = "SELECT * from school ORDER BY type,name ASC";
    $result = mysqli_query($mysqlconnection, $query);
    $num=mysqli_num_rows($result);
    //$i=0;
?>
<div id="sch-table">
<table cellpadding="0" cellspacing="0" border="1" id="school-table" class='display' style='width:90%'>
    <thead>
      <tr>
        <th style="max-width:50px">Ενέργεια</th>
        <th style="max-width:200px">Όνομα</th>
        <th>Κωδ.Υπουργείου</th>
        <th>Τηλέφωνο</th>
        <th>e-mail</th>
        <th>Οργαν.</th>
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
    echo "<span title=\"Προβολή\"><a href=\"school_status.php?org=".$row['id']."\"><img style=\"border: 0pt none;\" src=\"../images/view_action.png\"/></a></span>&nbsp;&nbsp;";
    if ($usrlvl < 3) {
          echo "<span title=\"Επεξεργασία\"><a href=\"school_edit.php?org=".$row['id']."\"><img style=\"border: 0pt none;\" src=\"../images/edit_action.png\"/></a></span>&nbsp;&nbsp;";
    }
    echo "</td>";
    
    echo "\n<td><a href='school_status.php?org=".$row['id']."'>".$row['name']."</a></td>";
    echo "\n<td>".$row['code']."</td>";
    echo "\n<td>".preg_replace('/\s+/', '', $row['tel'])."</td>";
    echo "\n<td><a href='mailto:".$row['email']."'>".$row['email']."</a></td>";
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
    echo $row['anenergo'] == 1 ? 'Χ' : '&#10003;';
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
<div class="spacer"></div>
            

</div>
<INPUT TYPE='button' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
    </body>
</html>
