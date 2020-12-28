<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once"../include/functions.php";
  //require('../tools/calendar/tc_calendar.php');
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  session_start();
  $usrlvl = $_SESSION['userlevel'];
	
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
  <LINK href="../css/jquery-ui.css" rel="stylesheet" type="text/css">
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>Employee</title>
	<script type="text/javascript" src="../js/jquery.js"></script>
  <script type="text/javascript" src="../js/jquery-ui.min.js"></script>
  <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
  <script type="text/javascript" src="../js/common.js"></script>
	<script type="text/javascript">
        $(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		});     
        

        $(function() {
            $( "#tabs" ).tabs();
        });

	</script>
  </head>
  <body> 
    <center>
      <?php
        $ypol = ypoloipo_adeiwn($_GET['id'], $mysqlconnection);
        if ($ypol[2] == 0)
            echo "Υπόλοιπο κανονικών αδειών για το $ypol[0]: <strong>$ypol[1] ημέρες</strong>";
        else
            echo "Υπόλοιπο κανονικών αδειών για το $ypol[0]: <strong>$ypol[1] ημέρες</strong> ($ypol[3] ημέρες από το $ypol[2])";
        
        $query = "SELECT * from adeia where emp_id=".$_GET['id']." ORDER BY start";
        $result = mysqli_query($mysqlconnection, $query);
        $num=mysqli_num_rows($result);
        if (!$num)
        {
            echo "<br><br><big>Δε βρέθηκαν άδειες</big>";
            $emp_id = $_GET['id'];
            if ($usrlvl < 2)
                echo "<br><span title=\"Προσθήκη Άδειας\"><a href=\"adeia.php?emp=$emp_id&op=add\"><big>Προσθήκη Άδειας</big><img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/></a></span>";
            exit;
        }

        // get adeia years
        $query = "SELECT DISTINCT YEAR(start) FROM adeia where emp_id=".$_GET['id'].' ORDER BY year(start) DESC';
        $result = mysqli_query($mysqlconnection, $query);
        while ($year = mysqli_fetch_array($result, MYSQLI_NUM))
            $year_arr[] = $year[0];
        echo "<br>";
        // tabs
        echo "<div id='container' style='width: 900px; padding: 10px;'>";
        echo "<div id='tabs'>";
        echo "<ul>";
        $tab_i = 1;
        foreach ($year_arr as $yr){
            echo "<li><a href='#tabs-$tab_i'>$yr</a></li>";
            $tab_i++;
        }
        echo "</ul>";
        
        $tab_i = 1;
        // year tab
        foreach ($year_arr as $yr)
        {		
            $i = $synolo_days = 0;
            echo "<div id='tabs-$tab_i'>";
            
            // year table
            echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border='1'>";	
            echo "<thead><tr>";
            echo "<th>ID</th><th>Τύπος</th><th>Αρ.Πρωτ.</th><th>Ημ.Αίτησης</th><th>Ημέρες</th><th>Ημ.Έναρξης</th><th>Ημ.Λήξης</th>";
            echo "</tr></thead>";
            echo "<tbody>";
            
            $query = "SELECT * from adeia where year(start) = $yr AND emp_id=".$_GET['id']." ORDER BY start";
            $result = mysqli_query($mysqlconnection, $query);
            $num=mysqli_num_rows($result);
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

                $query1 = "select type from adeia_type where id=$type";
                $result1 = mysqli_query($mysqlconnection, $query1);
                $typewrd = mysqli_result($result1, 0, "type");
                if ($usrlvl < 2)
                    echo "<tr><td>$id<span title=\"Διαγραφή\"><a href=\"javascript:confirmDelete('adeia.php?adeia=$id&op=delete')\"><img style=\"border: 0pt none;\" src=\"../images/delete_action.png\"/></a></span></td><td><a href='adeia.php?adeia=$id&op=view'>$typewrd</a></td><td>$prot</td><td>".date('d-m-Y',strtotime($date))."</td><td>$days</td><td>".date('d-m-Y',strtotime($start))."</td><td>".date('d-m-Y',strtotime($finish))."</td></tr>";
                else
                    echo "<tr><td>$id</td><td><a href='adeia.php?adeia=$id&op=view'>$typewrd</a></td><td>$prot</td><td>".date('d-m-Y',strtotime($date))."</td><td>$days</td><td>".date('d-m-Y',strtotime($start))."</td><td>".date('d-m-Y',strtotime($finish))."</td></tr>";
                $i++;
                $synolo_days += $days;
            }

            echo "</tbody>";
            if ($usrlvl < 2)
                echo "<tr><td colspan=4><span title=\"Προσθήκη Άδειας\"><a href=\"adeia.php?emp=$emp_id&op=add\">Προσθήκη Άδειας<img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/></a></span></td>";
            else
                echo "<tr><td colspan=4></td>";
            echo "<td colspan=4>Σύνολο αδειών έτους: $synolo_days</td></tr>";
            echo "</table>";
            
            
            $tab_i++;
            echo "</div>"; //year tab div
        }
        echo "</div>"; //all tabs div
        echo "</div>"; //tabs container
                
		echo "</body>";
		echo "</html>";	


	mysqli_close($mysqlconnection);
?>
