<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once "../config.php";
	require_once "../tools/functions.php";
?>	
  <html>
  <head>      
      <LINK href="../css/style.css" rel="stylesheet" type="text/css">
      <title>Συμπλήρωση υποχρεωτικού ωραρίου</title>
      <script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
      <script type="text/javascript">   
         $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
         });
      </script>
   </head>
   <body>
   <?php include('../etc/menu.php'); ?>
<?php        
      include("../tools/class.login.php");
      
      $log = new logmein();
      if($log->logincheck($_SESSION['loggedin']) == false){
         header("Location: ../tools/login.php");
      }
      $usrlvl = $_SESSION['userlevel'];
      //if ($usrlvl)
      //    die('Insufficient privileges');
      
      // set max execution time 
      set_time_limit (180);
            
		$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $mysqlconnection);
		mysql_query("SET NAMES 'greek'", $mysqlconnection);
		mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);

      //$query = "select e.surname,e.name,e.wres, y.hours, y.id from employee e join yphrethsh y on e.id = y.emp_id where sxol_etos = $sxol_etos";
      echo "<h2>Μη συμπλήρωση υποχρεωτικού ωραρίου</h2>";
      // init vars
      $mon_diffs = Array();
      $ekt_diffs = Array();
      $has_mon_diffs = false;
      $has_ekt_diffs = false;

      // monimoi
      $query = "select e.id as empid,e.surname,e.name,e.wres, sum(y.hours) as hours from employee e join yphrethsh y on e.id = y.emp_id where sxol_etos = $sxol_etos GROUP BY y.emp_id ORDER BY e.surname ASC";
      $result = mysql_query($query, $mysqlconnection);

      while ($row = mysql_fetch_assoc($result)) {
         if ($row['hours'] == $row['wres']){
            continue;
         }
         $has_mon_diffs = true;
         $mon_diffs[] = $row;
      }
      // ektaktoi
      $query = "select e.id as empid,e.surname,e.name,e.wres, sum(y.hours) as hours from ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where sxol_etos = $sxol_etos GROUP BY y.emp_id ORDER BY e.surname ASC";
      $result = mysql_query($query, $mysqlconnection);
                		
      while ($row = mysql_fetch_assoc($result)) {
         if ($row['hours'] == $row['wres']){
            continue;
         }
         $has_ekt_diffs = true;
         $ekt_diffs[] = $row;
      }
      // print results
      if ($has_mon_diffs || $has_ekt_diffs){
         if ($has_mon_diffs){
            echo "<h3>Μόνιμοι</h3>";
            echo "<table class=\"imagetable\" border='1'>";
            echo "<thead><th>Επώνυμο</th><th>Όνομα</th><th>Ώρες Υπ.Ωραρίου</th><th>Ώρες Τοποθέτησης σε Σχ.Μονάδες</th><th>Διαφορά <small>(Υποχρ. - Τοποθ.)</small></th></thead>";
            echo "<tbody>";
            foreach ($mon_diffs as $row) {
               echo "<tr>";
               echo "<td><a href='../employee/employee.php?id=".$row['empid']."&op=view'>".$row['surname']."</td>";
               $df = $row['wres'] - $row['hours'];
               echo "<td>".$row['name']."</td><td>".$row['wres']."</td><td>".$row['hours']."</td><td>$df</td>";
               echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
         }
         if ($has_ekt_diffs){
            echo "<h3>Αναπληρωτές</h3>";
            echo "<table class=\"imagetable\" border='1'>";
            echo "<thead><th>Επώνυμο</th><th>Όνομα</th><th>Ώρες Υπ.Ωραρίου</th><th>Ώρες Τοποθέτησης σε Σχ.Μονάδες</th><th>Διαφορά <small>(Υποχρ. - Τοποθ.)</small></th></thead>";
            echo "<tbody>";
            foreach ($ekt_diffs as $row) {
               echo "<tr>";
               echo "<td><a href='../employee/ektaktoi.php?id=".$row['empid']."&op=view'>".$row['surname']."</td>";
               $df = $row['wres'] - $row['hours'];
               echo "<td>".$row['name']."</td><td>".$row['wres']."</td><td>".$row['hours']."</td><td>$df</td>";
               echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
         }
      } else {
         echo "<h3>Δε βρέθηκαν διαφορές</h3>";
      }

         
		mysql_close();
                

	//}
?>
   <br>
   <input type='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">

   </body>
</html>
