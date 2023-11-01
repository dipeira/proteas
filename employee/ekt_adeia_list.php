<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once"../include/functions.php";
  require('../tools/calendar/tc_calendar.php');
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  session_start();
  $usrlvl = $_SESSION['userlevel'];
	
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>Employee</title>
	<script type="text/javascript" src="../js/jquery.js"></script>
  <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
  <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
  <script type="text/javascript" src="../js/common.js"></script>
	<script type="text/javascript">
        $(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		});     
        
	</script>
  </head>
  <body> 
    <center>
      <?php
        $i = 0;
        if (isset($_GET['sxol_etos'])){
            $sxol_etos = $_GET['sxol_etos'];
        }
        $query = "SELECT * from adeia_ekt where emp_id=".$_GET['id']." AND sxoletos = $sxol_etos";
        $result = mysqli_query($mysqlconnection, $query);
        $num=mysqli_num_rows($result);
        if (!$num)
        {
          echo "<br><br><big>Δε βρέθηκαν άδειες</big>";
          $emp_id = $_GET['id'];
          if ($usrlvl < 2 || $_SESSION['adeia'])
              echo "<br><span title=\"Προσθήκη Άδειας\"><a href=\"ekt_adeia.php?emp=$emp_id&op=add&sxol_etos=$sxol_etos\"><big>Προσθήκη Άδειας</big><img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/></a></span>";
          exit;
        }
                    
        echo "<br>";
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border='1'>";	
        echo "<thead><tr>";
        echo "<th>Ενέργεια</th><th>Τύπος</th><th>Αρ.Πρωτ.</th><th>Ημ.Αίτησης</th><th>Ημέρες</th><th>Ημ.Έναρξης</th><th>Ημ.Λήξης</th>";
        echo "</tr></thead>";
        echo "<tbody>";
        
        while ($i<$num)
        {
          $id = mysqli_result($result, $i, "id");
          $emp_id = mysqli_result($result, $i, "emp_id");
          $type = mysqli_result($result, $i, "type");
          $prot = mysqli_result($result, $i, "prot");
          $date = mysqli_result($result, $i, "date");
          $days = mysqli_result($result, $i, "days");
          $start = mysqli_result($result, $i, "start");
          $finish = mysqli_result($result, $i, "finish");
          $comments = mysqli_result($result, $i, "comments");
          $sxol_etos = mysqli_result($result, $i, "sxoletos");
                              
          $query1 = "select type from adeia_ekt_type where id=$type";
          $result1 = mysqli_query($mysqlconnection, $query1);
          $typewrd = mysqli_result($result1, 0, "type");
          echo "<tr><td>";
          echo "<span title='Προβολή'><a href='ekt_adeia.php?adeia=$id&op=view&sxol_etos=$sxol_etos'><img style='border: 0pt none;' src='../images/view_action.png'></a></span>&nbsp;&nbsp;";
          echo "<span title='Επεξεργασία'><a href='ekt_adeia.php?adeia=$id&op=edit&sxol_etos=$sxol_etos'><img style='border: 0pt none;' src='../images/edit_action.png'></a></span>&nbsp;&nbsp;";
          if ($usrlvl < 2 || $_SESSION['adeia']) {
            echo "<span title=\"Διαγραφή\"><a href=\"javascript:confirmDelete('ekt_adeia.php?adeia=$id&op=delete&sxol_etos=$sxol_etos')\"><img style=\"border: 0pt none;\" src=\"../images/delete_action.png\"/></a></span>";
          }
          echo "</td><td><a href='ekt_adeia.php?adeia=$id&op=view&sxol_etos=$sxol_etos'>$typewrd</a></td><td>$prot</td><td>".date('d-m-Y',strtotime($date))."</td><td>$days</td><td>".date('d-m-Y',strtotime($start))."</td><td>".date('d-m-Y',strtotime($finish))."</td></tr>";
          $i++;
        }

        echo "</tbody>";
        // add absense only on current year
        if (($usrlvl < 2 || $_SESSION['adeia']) && $_GET['sxol_etos'] == getParam('sxol_etos',$mysqlconnection))
            echo "<tr><td colspan=8><span title=\"Προσθήκη Άδειας\"><a href=\"ekt_adeia.php?emp=$emp_id&op=add&sxol_etos=$sxol_etos\">Προσθήκη Άδειας<img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/></a></span>";		
        echo "</table>";
        
        echo "</body>";
        echo "</html>";	

        mysqli_close($mysqlconnection);
      ?>
  </body>
</html>
