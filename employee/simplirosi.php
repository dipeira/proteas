<?php
	header('Content-type: text/html; charset=utf-8'); 
	require_once"../config.php";
  require_once"../include/functions.php";
  
  session_start();
?>	
  <html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Συμπλήρωση ωραρίου εκπαιδευτικών';
    require '../etc/head.php'; 
    ?>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <!-- <link href="../css/select2.min.css" rel="stylesheet" /> -->
    <script type="text/javascript" src="../js/jquery.js"></script>
    <!-- <script src="../js/select2.min.js"></script> -->
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type="text/javascript">   
      $(document).ready(function() { 
			  $("#mytbl").tablesorter({widgets: ['zebra']}); 
        // $(".klados_select").select2();
		  });    
    </script>
  </head>
  <body>
    <?php include('../etc/menu.php'); ?>
<?php        
	// praxi_sch: Displays schools or Schools & ektaktoi for the selected praxi

    include("../tools/class.login.php");
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false){
        header("Location: ../tools/login.php");
    }
    $usrlvl = $_SESSION['userlevel'];
    echo "<h2>Συμπλήρωση ωραρίου εκπαιδευτικών</h2>";
    if ($_SESSION['userlevel'] == 3){
      echo "Σφάλμα: Δεν επιτρέπεται η πρόσβαση...";
      echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
      die();
    }

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
        
    
    echo "<table class=\"imagetable\" border='1'>";
    echo "<form action='' method='POST' autocomplete='off'>";
    
    $sql = "select * from klados";
    $result = mysqli_query($mysqlconnection, $sql);
 
    echo "<tr><td>Σχέση υπηρέτησης:</td>";
    echo "<td><select class='block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body' name='sxesi' class='sxesi_select'>";
    if (isset($_POST['sxesi']) && $_POST['sxesi'] == 'mon') {
        echo "<option value='mon' selected>Μόνιμος</option>";
        echo "<option value='ana'>Αναπληρωτής</option>";
    } else if(isset($_POST['sxesi']) && $_POST['sxesi'] == 'ana'){
        echo "<option value='mon'>Μόνιμος</option>";
        echo "<option value='ana' selected>Αναπληρωτής</option>";
    } else {
      echo "<option value='mon'>Μόνιμος</option>";
      echo "<option value='ana'>Αναπληρωτής</option>";
    }
    echo "</td></tr>";

    echo "<tr><td>Επιλογή κλάδου:</td><td>";
    $cmb = "<select class='block w-full px-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body' name='klados' class='klados_select'>";
    while ($row = mysqli_fetch_array($result)) {
      if (isset($_POST['klados']) && $row['id'] == $_POST['klados'])
          $cmb .= "<option value=\"".$row['id']."\" selected>".$row['perigrafh']."</option>";
      else
          $cmb .= "<option value=\"".$row['id']."\">".$row['perigrafh']."</option>";
    }
    $cmb .= "</select>";
    echo $cmb;
    
    echo "</td></tr>";
    $is_checked = isset($_POST['showchange']) ? 'checked' : '';
    echo "<tr><td>Προβολή τελ. αλλαγής υπηρέτησης (για μόνιμους)</td><td><input type='checkbox' name='showchange' $is_checked /><br /></td></tr>";
    
    
    echo "<tr><td colspan=2><input type='submit' value='Αναζήτηση'>";
    echo "&nbsp;&nbsp;&nbsp;";
    echo "<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='ektaktoi_list.php'\"></td></tr>";
    echo "</table></form>";

	if(isset($_POST['klados']) && isset($_POST['sxesi']))
	{
    $has_changes = $_POST['showchange'] && $_POST['sxesi'] == 'mon';
    $i = 0;
    $topo = 0;
    if ($_POST['sxesi'] == 'mon'){
      $emp_tbl = 'employee';
      $yphr_tbl = 'yphrethsh';
    } else {
      $emp_tbl = 'ektaktoi';
      $yphr_tbl = 'yphrethsh_ekt';
    }
    
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
		
    $query = "select count(y.id) as plithos,e.id,e.surname,e.name,e.wres,s.name as sch, s.id as schid, code, s.email from $emp_tbl e join $yphr_tbl y on e.id = y.emp_id 
      join school s on s.id = y.yphrethsh where y.sxol_etos=$sxol_etos AND e.klados=" . $_POST['klados'] . " GROUP BY y.emp_id HAVING count(y.id) > 1 ORDER BY e.surname ";
    
    // echo $query;
		$result = mysqli_query($mysqlconnection, $query);
		
    // Store all data in array for both display and export
    $table_data = array();
    $previd = $employees = 0;
    
    while ($row = mysqli_fetch_array($result))	
    {
      $id = $row['id'];
      if ($previd <> $id) {
        $employees++;
        $previd = $id;
      }
		  $name = $row['name'];
      $surname = $row['surname'];
      $wrario = $row['wres'];
      $plithos = $row['plithos'];
      
      $yphr_qry = "select s.id,s.name,y.hours from $yphr_tbl y join school s on y.yphrethsh = s.id where emp_id=$id and sxol_etos=$sxol_etos";
      $res_yphr = mysqli_query($mysqlconnection, $yphr_qry);
      $schools_list = array();
      $schools_html = '<ul>';
      $anathesi = 0;
      while ($row_yphr = mysqli_fetch_array($res_yphr)) {
        $schools_list[] = $row_yphr['name'] . " (" . $row_yphr['hours'] . " ώρες)";
        $schools_html .= "<li><a class='underline' href=\"../school/school_status.php?org=".$row_yphr['id']."\">".$row_yphr['name']." (" .$row_yphr['hours']. ' ώρες)</li>';
        $anathesi += $row_yphr['hours'];
        $topo++;
      }
      $schools_html .= '</ul>';
      $schools_text = implode(', ', $schools_list);
      
      // if monimos, examine employee_log for latest change in sx_yphreshsh
      $yphr_latest = '';
      if ($has_changes){
        $log_qry = "SELECT max(timestamp) as max_ts FROM employee_log WHERE emp_id = $id AND query LIKE '%sx_yphrethshs%' ";
        $res_log = mysqli_query($mysqlconnection, $log_qry);
        if ($log_row = mysqli_fetch_array($res_log)) {
          $yphr_latest = $log_row['max_ts'] ? $log_row['max_ts'] : '';
        }
      }

      $table_data[] = array(
        'id' => $id,
        'surname' => $surname,
        'name' => $name,
        'wrario' => $wrario,
        'anathesi' => $anathesi,
        'plithos' => $plithos,
        'schools_text' => $schools_text,
        'schools_html' => $schools_html,
        'yphr_latest' => $yphr_latest
      );
      $i++;
    }
    
    // Build Excel table HTML
    $excel_table = "<table border='1'>";
    $excel_table .= "<thead><tr><th>Ονοματεπώνυμο</th><th>Υποχρ.ωράριο</th><th>Σύνολο ωρών ανάθεσης</th><th>Πλήθος υπηρετήσεων</th><th>Σχολεία</th>";
    if ($has_changes) {
      $excel_table .= '<th>Τελ.αλλαγή σχ.υπηρέτησης</th>';
    }
    $excel_table .= "</tr></thead><tbody>";
    
    foreach ($table_data as $data) {
      $excel_table .= "<tr><td>".$data['surname']." ".$data['name']."</td><td>".$data['wrario']."</td><td>".$data['anathesi']."</td><td>".$data['plithos']."</td><td>".$data['schools_text']."</td>";
      if ($has_changes) {
        $excel_table .= "<td>".$data['yphr_latest']."</td>";
      }
      $excel_table .= "</tr>";
    }
    $excel_table .= "</tbody></table>";
    
    // Display table with links
    echo "<br>";
		echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
    echo "<thead><tr><th>Ονοματεπώνυμο</th><th>Υποχρ.ωράριο</th><th>Σύνολο ωρών<br> ανάθεσης</th><th>Πλήθος υπηρετήσεων</th><th>Σχολεία</th>";
    echo $has_changes ? '<th>Τελ.αλλαγή σχ.υπηρέτησης</th>' : '';
    echo "</tr></thead><tbody>";
    
    foreach ($table_data as $data) {
      echo "<tr><td><a class='underline' href=\"$emp_tbl.php?id=".$data['id']."&op=view\">".$data['surname']." ".$data['name']."</a></td><td>".$data['wrario']."</td><td>".$data['anathesi']."</td><td>".$data['plithos']."</td><td>".$data['schools_html']."</td>";
      if ($has_changes) {
        echo "<td>".$data['yphr_latest']."</td>";
      }
      echo "</tr>";
    }
		echo "</tbody></table>";
    echo "<small><i>$i εκπ/κοί, $topo τοποθετήσεις</i></small>";
    echo "<br><br>";

    mysqli_close($mysqlconnection);
			
		echo "<form action='../tools/2excel.php' method='post'>";
		echo "<input type='hidden' name='data' value='".htmlspecialchars($excel_table, ENT_QUOTES, 'UTF-8')."'>";
    echo "<BUTTON TYPE='submit' class='btn'><IMG SRC='../images/excel.png' ALIGN='absmiddle' style='display: inline-block; vertical-align: middle;'>Εξαγωγή στο excel</BUTTON>";
    echo "&nbsp;&nbsp;&nbsp;";
    echo "<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='ektaktoi_list.php'\">";
    echo "</form>";
	}
?>
<br><br>
</body>
</html>