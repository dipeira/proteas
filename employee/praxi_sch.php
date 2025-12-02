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
    $page_title = 'Εκπαιδευτικοί και Σχολεία ανά Πράξη';
    require '../etc/head.php'; 
    ?>      
    
    <link href="../css/select2.min.css" rel="stylesheet" />
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script src="../js/select2.min.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script>
    <style>
        /* Page layout */
        .praxi-sch-container {
            margin: 0 auto;
            padding: 20px;
            max-width: 1400px;
        }
        
        /* Search form styling */
        .search-form-table {
            margin: 20px auto;
            width: 100%;
            max-width: 800px;
        }
        
        /* Select2 styling */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
            min-height: 42px !important;
            padding: 4px 8px;
        }
        
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #4FC5D6 !important;
            box-shadow: 0 0 0 3px rgba(79, 197, 214, 0.2) !important;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%) !important;
            border: none !important;
            color: white !important;
            border-radius: 4px !important;
            padding: 4px 8px !important;
            margin: 2px !important;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white !important;
            margin-right: 6px !important;
        }
        
        /* Radio button styling */
        input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #4FC5D6;
            margin-right: 8px;
            margin-left: 12px;
        }
        
        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .radio-group label {
            display: flex;
            align-items: center;
            font-weight: 500;
            color: #1f2937;
            cursor: pointer;
        }
        
        /* Submit button styling */
        .imagetable input[type="submit"] {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%) !important;
            color: white !important;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(79, 197, 214, 0.3);
        }
        
        .imagetable input[type="submit"]:hover {
            background: linear-gradient(135deg, #3BA8B8 0%, #2A8B9A 100%) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(79, 197, 214, 0.4);
        }
        
        /* Results area */
        .results-area {
            margin-top: 30px;
        }
        
        .results-header {
            text-align: center;
            margin: 20px 0;
        }
        
        /* Action buttons area */
        .export-buttons-area {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: center;
            margin: 30px 0;
            padding: 15px;
            flex-wrap: wrap;
        }
        
        .export-buttons-area form {
            margin: 0;
        }
        
        .results-info {
            text-align: center;
            margin: 15px 0;
            font-style: italic;
            color: #6b7280;
            font-size: 0.875rem;
        }
    </style>
    <script type="text/javascript">   
      $(document).ready(function() { 
			  $("#mytbl").tablesorter({widgets: ['zebra']}); 
        $(".praxi_select").select2({
            placeholder: "Επιλέξτε πράξη(-εις)...",
            width: '100%'
        });
		  });    
    </script>
  </head>
  <body>
    <?php include('../etc/menu.php'); ?>
    <div class="praxi-sch-container">
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
        
    echo "<div class='page-header'>";
    echo "<h2>Εκπαιδευτικοί και Σχολεία ανά Πράξη</h2>";
    echo "</div>";
    
    echo "<div style='display: flex; justify-content: center;'>";
    echo "<table class=\"imagetable search-form-table\" border='1'>";
    echo "<form action='' method='POST' autocomplete='off'>";
    
    $sql = "select * from praxi";
    $result = mysqli_query($mysqlconnection, $sql);
    echo "<tr><td style='width: 200px;'><strong>Επιλογή Πράξεων:</strong></td><td>";
    $cmb = "<select name=\"praxi[]\" class=\"praxi_select\" multiple=\"multiple\" style='width: 100%;'>";
    while ($row = mysqli_fetch_array($result)){
      if (isset($_POST['praxi']) && in_array($row['id'],$_POST['praxi']))
          $cmb .= "<option value=\"".$row['id']."\" selected>".$row['name']."</option>";
      else
          $cmb .= "<option value=\"".$row['id']."\">".$row['name']."</option>";
    }
    $cmb .= "</select>";
    echo $cmb;
    
    echo "</td></tr>";
    
    echo "<tr><td style='width: 200px;'><strong>Επιλογή:</strong></td><td>";
    echo "<div class='radio-group'>";
    echo "<label><input type='radio' name='type' value='0' checked> Εμφάνιση μόνο Σχολείων</label>";
    echo "<label><input type='radio' name='type' value='1'> Εμφάνιση Εκπ/κών & Σχολείων</label>";
    echo "</div>";
    echo "</td></tr>";
    
    echo "<tr><td colspan=2 style='text-align: center; padding: 15px;'>";
    echo "<input type='submit' value='🔍 Αναζήτηση'>";
    echo "&nbsp;&nbsp;&nbsp;";
    echo "<INPUT TYPE='button' VALUE='← Επιστροφή' class='btn-red' onClick=\"parent.location='ektaktoi_list.php'\">";
    echo "</td></tr>";
    echo "</table></form>";
    echo "</div>";

	if(isset($_POST['praxi']))
	{
    foreach ($_REQUEST['praxi'] as $pr)
        $praxeis[] = $pr;
    $i = 0;
    
    $all = $_POST['type'];
    
    foreach ($praxeis as $pr)
        $praxinm[] = getNamefromTbl($mysqlconnection, praxi, $pr);

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
		if ($all)
      $query = "select s.id as schid, e.afm, e.hm_anal, e.id, e.surname, e.name, s.name as sch, p.name as praxi, code, y.hours from ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id 
        join school s on s.id = y.yphrethsh join praxi p on p.id = e.praxi where y.sxol_etos=$sxol_etos AND e.praxi in (" . implode(',',$_POST['praxi']) . ") ORDER BY SURNAME,NAME ASC";
    else
      $query = "select distinct s.name as sch, s.id as schid, code, s.email from ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id 
        join school s on s.id = y.yphrethsh where y.sxol_etos=$sxol_etos AND e.praxi in (" . implode(',',$_POST['praxi']) . ") ORDER BY sch ASC";
    //echo $query;
		$result = mysqli_query($mysqlconnection, $query);
		
    ob_start();
    echo "<div class='results-area'>";
    echo "<div class='results-header'>";
    echo "<h2>Πράξη(-εις): ". implode(', ', $praxinm)."</h2>";
    echo "</div>";
    echo "<div style='display: flex; justify-content: center;'>";
		echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
    if ($all)
        echo "<thead><tr><th>ΑΦΜ</th><th>Ονοματεπώνυμο</th><th>Κωδ.Σχολείου</th><th>Σχολείο</th><th>Ώρες</th><th>Ημ.Ανάληψης</th><th>Πράξη</th></tr></thead><tbody>";
    else
        echo "<thead><tr><th>Κωδ.Σχολείου</th><th>Σχολείο</th><th>email Σχολείου</th></tr></thead><tbody>";
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
      $afm = $row['afm'];
      $anal = date( 'd/m/Y', strtotime($row['hm_anal']));
      $sch = $row['sch'];
      $hours = $row['hours'];
      $schid = $row['schid'];
      $praxi = $row['praxi'];
      $code = $row['code'];
		                
      if ($all)
          echo "<tr><td>$afm</td><td><a href=\"ektaktoi.php?id=$id&op=view\">$surname $name</a></td><td>$code</td><td><a href=\"../school/school_status.php?org=$schid\">$sch</a></td><td>$hours</td><td>$anal</td><td>$praxi</td></tr>";
      else
          echo "<tr><td>$code</td><td><a href=\"../school/school_status.php?org=$schid\">$sch</a></td><td><a href=\"mailto:".$row['email']."\">".$row['email']."</a></td></tr>";
      $i++;
    }
		echo "</tbody></table>";
    echo "</div>";
    echo "<div class='results-info'>";
    echo $all ? "<i>$employees εκπ/κοί, $i τοποθετήσεις</i>" : "<i>$i εγγραφές</i>";
    echo "</div>";

    mysqli_close($mysqlconnection);
                
    $page = ob_get_contents(); 
    $page = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\2", $page);
		ob_end_flush();
			
		echo "<div class='export-buttons-area'>";
		echo "<form action='../tools/2excel.php' method='post' style='display: inline-block;'>";
		echo "<input type='hidden' name = 'data' value='".  htmlspecialchars($page, ENT_QUOTES)."'>";
    echo "<button type='submit' class='btn btn-excel' style='display: inline-flex; align-items: center; gap: 8px;'>";
    echo "<img src='../images/excel.png' alt='Excel' style='width: 20px; height: 20px; filter: brightness(0) invert(1);'>";
    echo "📊 Εξαγωγή στο Excel";
    echo "</button>";
		echo "</form>";
    echo "<INPUT TYPE='button' class='btn-red' VALUE='← Επιστροφή' onClick=\"parent.location='ektaktoi_list.php'\">";
    echo "</div>";
    echo "</div>"; // Close results-area
	}
?>
    </div>
</body>
</html>