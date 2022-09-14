<?php
	header('Content-type: text/html; charset=utf-8'); 
	require_once"../config.php";
  require_once"../include/functions.php";
  
  session_start();
?>	
  <html>
  <head>      
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <title>Συμπλήρωση ωραρίου εκπαιδευτικών</title>
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

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
        
    echo "<h2>Συμπλήρωση ωραρίου εκπαιδευτικών</h2>";
    echo "<table class=\"imagetable\" border='1'>";
    echo "<form action='' method='POST' autocomplete='off'>";
    
    $sql = "select * from klados";
    $result = mysqli_query($mysqlconnection, $sql);

    echo "<tr><td>Σχέση υπηρέτησης:</td>";
    echo "<td><select name='sxesi' class='sxesi_select'>";
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
    $cmb = "<select name='klados' class='klados_select'>";
    while ($row = mysqli_fetch_array($result)) {
      if (isset($_POST['klados']) && $row['id'] == $_POST['klados'])
          $cmb .= "<option value=\"".$row['id']."\" selected>".$row['perigrafh']."</option>";
      else
          $cmb .= "<option value=\"".$row['id']."\">".$row['perigrafh']."</option>";
    }
    $cmb .= "</select>";
    echo $cmb;
    
    echo "</td></tr>";
    
    
    echo "<tr><td colspan=2><input type='submit' value='Αναζήτηση'>";
    echo "&nbsp;&nbsp;&nbsp;";
    echo "<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='ektaktoi_list.php'\"></td></tr>";
    echo "</table></form>";

	if(isset($_POST['klados']) && isset($_POST['sxesi']))
	{
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
		
    ob_start();
    echo "<br>";
    // echo "<h2>Συμπλήρωση ωραρίου εκπ/κών κλάδου: ". implode(', ', $praxinm)."</h2>";
		echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
    echo "<thead><tr><th>Ονοματεπώνυμο</th><th>Υποχρ.ωράριο</th><th>Σύνολο ωρών<br> ανάθεσης</th><th>Πλήθος υπηρετήσεων</th><th>Σχολεία</th></tr></thead><tbody>";
    
    $previd = $employees = 0;
    while ($row = mysqli_fetch_array($result))	
    {
      $id = $row['id'];
      if ($previd <> $id) {
        $employees++;
        $previd = $id;
      }
      $employees += ($previd <> $id) ? 1 : 0;
		  $name = $row['name'];
      $surname = $row['surname'];
      $wrario = $row['wres'];
      $plithos = $row['plithos'];
      
      $yphr_qry = "select s.id,s.name,y.hours from $yphr_tbl y join school s on y.yphrethsh = s.id where emp_id=$id and sxol_etos=$sxol_etos";
      $res_yphr = mysqli_query($mysqlconnection, $yphr_qry);
      $schools = '<ul>';
      $anathesi = 0;
      while ($row = mysqli_fetch_array($res_yphr)) {
        
        $schools .= "<li><a href=\"../school/school_status.php?org=".$row['id']."\">".$row['name']." (" .$row['hours']. ' ώρες)</li>';
        $anathesi += $row['hours'];
        $topo++;
      }
      $schools .= '</ul>';
      echo "<tr><td><a href=\"$emp_tbl.php?id=$id&op=view\">$surname $name</a></td><td>$wrario</td><td>$anathesi</td><td>$plithos</td><td>$schools</td></tr>";
      $i++;
    }
		echo "</tbody></table>";
    echo "<small><i>$i εκπ/κοί, $topo τοποθετήσεις</i></small>";
    echo "<br><br>";

    mysqli_close($mysqlconnection);
                
    $page = ob_get_contents(); 
    $page = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\2", $page);
		ob_end_flush();
			
		echo "<form action='../tools/2excel.php' method='post'>";
		echo "<input type='hidden' name = 'data' value='".  $page."'>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
    echo "&nbsp;&nbsp;&nbsp;";
    echo "<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='ektaktoi_list.php'\">";
    echo "</form>";
	}
?>
<br><br>
</body>
</html>