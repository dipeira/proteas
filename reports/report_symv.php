<?php
    header('Content-type: text/html; charset=utf-8'); 
?>
<html>
  <head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <title>Αναφορά Εκπαιδευτικών ανά περιφέρεια συμβούλου εκπαίδευσης</title>
    
    <script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>
	  <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.min.js"></script>
  </head>

<?php
    require_once"../config.php";
    require_once"../include/functions.php";
    session_start();

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

    echo "<body>";
    require '../etc/menu.php';


    function get_symv_select($conn) {
      $sql = "SELECT s.perif, e.surname, e.name, e.klados FROM symvouloi s JOIN employee e ON s.emp_id = e.id";
      $result = mysqli_query($conn, $sql);
  
      if (mysqli_num_rows($result) == 0) {
          echo "Δε βρέθηκαν στοιχεία";
          return;
      }
  
      // Get the selected value from $_GET if it exists
      $selected_perif = isset($_GET['enothta']) ? $_GET['enothta'] : '';
  
      echo "<select name='symvoulos' onchange='location = this.value;'>";
      echo "<option value=0>Επιλέξτε σύμβουλο εκπαίδευσης</option>";
      while ($row = mysqli_fetch_assoc($result)) {
          $klados = $row['klados'] == 1 ? 'ΠΕ60' : 'ΠΕ70';
          $perif = $row['perif'];
          
          // Check if the current option should be selected
          $selected = ($perif == $selected_perif) ? 'selected' : '';
  
          // Pass s.perif as a GET parameter in the URL and apply the selected attribute if it matches $_GET['enothta']
          echo "<option value='?enothta=" . $perif . "' $selected>" . $row['surname'] . ' ' . $row['name'] . ' - ' . $klados . "</option>";
      }
      echo "</select>";
    }
  
    echo "<center>";

    echo "<h2>Στατιστικά συμβούλων εκπαίδευσης</h2>";
    get_symv_select($mysqlconnection);

    
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";

    $perif = $_GET['enothta'] ? $_GET['enothta'] : null;
    
    function print_table($result, $num, $mysqlconnection, $mon = true){
      $i=0;
      echo "<table id=\"mytbl\" class=\"dttable imagetable\" border=\"1\" style='width:90%'>\n";
      echo "<thead>";
      echo "<tr><th>Κωδ.</th>";
      echo "<th>Ονομασία</th>";
      echo "<th>Επώνυμο</th>";
      echo "<th>Όνομα</th>";
      echo "<th>Θέση</th>";
      echo "<th>Κλάδος</th>";
      echo "<th>Κατάσταση</th>";
      echo $mon ? "<th>Ημ.Διορισμού</th><th>Μονιμοποίηση</th><th>Αξιολόγηση</th>" : '<th>Ημ.Ανάληψης</th>';
      echo "</tr>";
      echo "</thead>\n<tbody>\n";

      while ($i < $num)
      {        
          $sid = mysqli_result($result, $i, "sid");
          $sname = mysqli_result($result, $i, "sname");
          $code = mysqli_result($result, $i, "code");
          $id = mysqli_result($result, $i, "id");
          $name = mysqli_result($result, $i, "name");
          $surname = mysqli_result($result, $i, "surname");
          $klados = getKlados(mysqli_result($result, $i, "klados"),$mysqlconnection);
          $thesi = mysqli_result($result, $i, "thesi") == 2 ? 'Δ/ντής/ντρια' : 'Εκπ/κός';
          $status = mysqli_result($result, $i, "status") == 1 ? 'Εργάζεται' : 'Αδεια';
          if ($mon) {
            $hm_dior_dt = mysqli_result($result, $i, "hm_dior");
            $hm_dior = date('d-m-Y',strtotime($hm_dior_dt));
            $monimopoihsh = mysqli_result($result,$i,'monimopoihsh') == 1 ? 'Ναι' : 'Όχι';
            $aksiologhsh = mysqli_result($result,$i,'aksiologhsh') == 1 ? 'Ναι' : 'Όχι';
          } else {
            $hm_anal_dt = mysqli_result($result, $i, "hm_anal");
            $hm_anal = date('d-m-Y',strtotime($hm_anal_dt));
          }
          

          echo "<tr>";
          echo "<td>$code</td>";
          echo "<td><a href='../school/school_status.php?org=$sid' target='_blank'>$sname</a></td>";
          $link = $mon ? "../employee/employee.php?id=$id&op=view" : "../employee/ektaktoi.php?id=$id&op=view";
          echo "<td><a href=$link target='_blank'>$surname</td>";
          echo "<td>$name</td>";
          echo "<td>$thesi</td>";
          echo "<td>$klados</td>";
          echo "<td>$status</td>";
          echo $mon ? "<td>$hm_dior</td><td>$monimopoihsh</td><td>$aksiologhsh</td>" : "<td>$hm_anal</td>";
          echo "</tr>\n";
          $i++;                        
      }
      echo "</tbody></table>";
    }


    if ($perif) {
      // Statistics
      $stat_query0 = "SELECT count(*) from school where perif=$perif and anenergo = 0";
      $stat_query1 = "SELECT count(*) from school s JOIN employee e ON s.id = e.sx_yphrethshs 
      WHERE status IN (1,3,5) AND s.perif=$perif";
      $stat_query2 = "SELECT count(*) from school s JOIN ektaktoi e ON s.id = e.sx_yphrethshs 
      WHERE status IN (1,3,5) AND s.perif=$perif";
      // Teachers per specialty queries
      $stat_query_klados_mon = "SELECT k.perigrafh, count(*) from employee e JOIN school s ON s.id = e.sx_yphrethshs JOIN klados k ON e.klados = k.id
      WHERE e.status IN (1,3,5) AND s.perif=$perif GROUP BY klados";
      $stat_query_klados_anapl = "SELECT k.perigrafh, count(*) from ektaktoi e JOIN school s ON s.id = e.sx_yphrethshs JOIN klados k ON e.klados = k.id
      WHERE e.status IN (1,3,5) AND s.perif=$perif GROUP BY klados";
      
      $result0 = mysqli_query($mysqlconnection, $stat_query0);
      $row0 = mysqli_fetch_row($result0);
      $result1 = mysqli_query($mysqlconnection, $stat_query1);
      $row1 = mysqli_fetch_row($result1);
      $result2 = mysqli_query($mysqlconnection, $stat_query2);
      $row2 = mysqli_fetch_row($result2);
      
      // Teachers per specialty
      $klados_mon = $klados_anapl = array();
      $result_klados_mon = mysqli_query($mysqlconnection, $stat_query_klados_mon);
      while ($row = mysqli_fetch_row($result_klados_mon)) { $klados_mon[$row[0]] = $row[1]; }
      $result_klados_anapl = mysqli_query($mysqlconnection, $stat_query_klados_anapl);
      while ($row = mysqli_fetch_row($result_klados_anapl)) { $klados_anapl[$row[0]] = $row[1]; }
      
      echo "<table class='imagetable' style='width:50%'><thead><th>Κατηγορία</th><th>Πλήθος</th>";
      echo "<tr><td>Σχολεία</td><td>" . $row0[0] . '</td></tr>';
      echo "<tr><td>Μόνιμοι</td><td>" . $row1[0] . '</td></tr>';
      echo "<tr><td>Αναπληρωτές</td><td>" . $row2[0] . '</td></tr>';
      echo "<tr><td>Εκπ/κοί ανά κλάδο&nbsp;";
      echo "<a href='#' class='show_hide'><small>Εμφάνιση/Απόκρυψη</small></a>";
      echo "</td><td><div id='analysis' style='display:none;'>";
      echo "<p><b>Μόνιμοι</b></p>";
      foreach ($klados_mon as $key => $value) { 
        echo $key . ": ".$value."<br>";
      }
      if (!empty($klados_anapl)) { 
        echo "<p><b>Αναπληρωτές</b></p>";
        foreach ($klados_anapl as $key => $value) { 
          echo $key . ": ".$value."<br>";
        }
      }
      echo "</div></td></tr>";
      echo "</table>";

      // Gather table data
      $query = "SELECT s.id as sid, s.code,s.name AS sname, e.* from school s JOIN employee e ON s.id = e.sx_yphrethshs 
      WHERE status IN (1,3,5) AND s.perif=$perif";
      $query2 = "SELECT s.id as sid, s.code,s.name AS sname, e.* from school s JOIN ektaktoi e ON s.id = e.sx_yphrethshs 
      WHERE status IN (1,3,5) AND s.perif=$perif";

      $result = mysqli_query($mysqlconnection, $query);
      $num = mysqli_num_rows($result);

      echo "<h2>Μόνιμοι</h2>";
      print_table($result, $num, $mysqlconnection);
      
      $result = mysqli_query($mysqlconnection, $query2);
      $num = mysqli_num_rows($result);
      if ($num){
        echo "<h2>Αναπληρωτές</h2>";
        print_table($result, $num, $mysqlconnection, false);
      }

      echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    }
    
?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.print.min.js"></script>

    <script type="text/javascript">  
    // Custom sorting function for date in DD-MM-YYYY format
    $.fn.dataTable.ext.type.order['date-dd-mm-yyyy-pre'] = function(date) {
        var parts = date.split('-');
        return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
    };  

    $(document).ready(function() { 
        var table = $(".dttable").DataTable({
            lengthMenu: [
                [20, 50, -1],
                [20, 50, 'Όλοι']
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/el.json'
            },
            dom: 'Bfrtlip',
            buttons: [
                'excel', 'pdf', 'print'
            ],
            columnDefs: [
                { type: 'date-dd-mm-yyyy', targets: [7] } // Set to your date column index if 7 is correct
            ]
        });
        $("#analysis").hide();

        $('.show_hide').click(function(){
            $("#analysis").slideToggle();
        });
    });
</script>
</body>
</html>
                
