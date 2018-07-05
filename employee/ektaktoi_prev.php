<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"../config.php";
  require_once"../tools/functions.php";
    
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
    include("../tools/class.login.php");
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false)
    {   
        header("Location: ../tools/login.php");
    }
    else
        $logged = 1;
    
    // Get previous sxol_etos or find
    if (strlen($_REQUEST['sxoletos'])>0)
    {
        $sxoletos = $_REQUEST['sxoletos'];
    }
    else
    {
        $sxoletos = find_prev_year($sxol_etos);
	 }
	 $_SESSION['sxoletos'] = $sxoletos;
          
?>
<html>
  <head>
	
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Έκτακτο Προσωπικό</title>
	
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery.validate.js"></script>
	<script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
	<script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
	<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
	<script type="text/javascript">		
		$().ready(function() {
			$("#yphr").autocomplete("get_school.php", {
				width: 260,
				matchContains: true,
				selectFirst: false
			});
		});
		$().ready(function() {
			$("#surname").autocomplete("get_name_prev.php", {
				width: 260,
				matchContains: true,
				selectFirst: false
			});
		});
		
		$(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		});

		function changeYear(){
			var selYr = $('#year-change').find(":selected").val();
			parent.location='ektaktoi_prev.php?sxoletos='+selYr;
		} 
	</script>
	
  </head>
  <body> 
  <?php include('../etc/menu.php'); ?>
  	
<div>
      <?php
		$usrlvl = $_SESSION['userlevel'];

		//rpp = results per page
		if (isset ($_POST['rpp']))
			$rpp = $_POST['rpp'];
		elseif (isset ($_GET['rpp']))
			$rpp = $_GET['rpp'];
		else
			$rpp= 20;
			
		if ($_POST['page']!=0)
			$curpg = $_POST['page'];
                elseif (isset($_GET['page'])) 
			$curpg = $_GET['page'];
		else
			$curpg = 1;
		                
		//limit in the query thing
		$limitQ = ' LIMIT ' .($curpg - 1) * $rpp .',' .$rpp;

                
		$query = "SELECT * FROM ektaktoi_old where sxoletos=" . $sxoletos.' ';
		

		if (isset($sxoletos))
				echo "<h3>Έκτακτο προσωπικό σχολικού έτους: " . substr($sxoletos,0,4) . '-' . substr($sxoletos,4,2) ."</h3>";
                
		$klpost = 0;
		$yppost = 0;
		if (($_POST['klados']>0) || (strlen($_POST['yphr'])>0) || (strlen($_POST['surname'])>0) || (strlen($_POST['type'])>0))
		{
			$posted=1;
			$curpg=1;
		}
		if (($_POST['klados']>0) || ($_GET['klados']>0))
		{
			if ($_POST['klados']>0)
				$klpost = $_POST['klados'];
			else
				$klpost = $_GET['klados'];
			$query .= "AND klados = $klpost ";
		}
                if (($_POST['type']>0) || ($_GET['type']>0))
		{
			if ($_POST['type']>0)
				$typepost = $_POST['type'];
			else
				$typepost = $_GET['type'];
			$query .= "AND type = $typepost ";
		}
		if ((strlen($_POST['yphr'])>0) || ($_GET['yphr']>0))
		{
			if (strlen($_POST['yphr'])>0)
				$yppost = getSchoolID ($_POST['yphr'], $mysqlconnection);
			if ($_GET['yphr']>0)
				$yppost = $_GET['yphr'];
				$query .= "AND sx_yphrethshs = $yppost ";
		}
		if (strlen($_POST['surname'])>0 || strlen($_GET['surname'])>0)
		{
			if (strlen($_POST['surname'])>0)
					$surpost = $_POST['surname'];
			else{
					$surpost = urldecode($_GET['surname']);
					//$surpost = mb_convert_encoding($surpost, "iso-8859-7", "utf-8");
			}
                            
			$query .= "AND surname LIKE '$surpost' ";
		}
      if ((strlen($_POST['praxi'])>0) || ($_GET['praxi']>0))
		{
			if ($_GET['praxi']>0)
				$yppost = $_GET['praxi'];
                        else
                            $yppost = $_POST['praxi'];
			
			$query .= "AND praxi = $yppost ";
		}
		$query_all = $query;
		$query .= " ORDER BY surname ";
                $query .= $limitQ;
                
		// Debugging...
		//echo $query;
		
		$result = mysql_query($query, $mysqlconnection);
		$result1 = mysql_query($query_all, $mysqlconnection);
		// Number of records found
		
		if ($result)
			$num_record = mysql_num_rows($result);
		if ($result1)
			$num_record1 = mysql_num_rows($result1);
		$lastpg = ceil($num_record1 / $rpp);
		
				
		if ($result)
			$num=mysql_num_rows($result);
                
	// display prev year select
	$query_pr = "SELECT distinct sxoletos from praxi_old ORDER BY sxoletos DESC";
	$result_pr = mysql_query($query_pr, $mysqlconnection);
	if (mysql_num_rows($result_pr)){
		echo "Επιλέξτε έτος: ";
		echo "<select id='year-change' onchange='changeYear()'>";
		while ($row = mysql_fetch_array($result_pr)){
			$setos = substr($row['sxoletos'],0,4).'-'.substr($row['sxoletos'],4,2);
			$selected = $sxoletos == $row['sxoletos'] ? 'selected' : '';
			echo "<option value='".$row['sxoletos']."' $selected>".$setos."</option>";
		}
		echo "</select><br><br>";
	}
	echo "<center>";
	
	echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
	echo "<thead><tr><form id='src' name='src' action='ektaktoi_prev.php?sxoletos=$sxoletos' method='POST'>\n";
	echo "<td colspan=2><input type='text' name='surname' id='surname''/>";
	if ($posted || ($_GET['klados']>0) || ($_GET['org']>0) || ($_GET['yphr']>0) || ($_GET['type']>0))
		echo "<INPUT TYPE='submit' VALUE='Επαναφορά'>";
	else	
		echo "<INPUT TYPE='submit' VALUE='Αναζήτηση'>";
	
	echo "<td>\n";
	kladosCmb($mysqlconnection);
	echo "</td>\n";
	echo "<div id=\"content\">";
	echo "<form autocomplete=\"off\">";
	echo "<td><input type=\"text\" name=\"yphr\" id=\"yphr\" /></td>";
	echo "</div>";
	echo "<td>";
	//echo "</td>";
	typeCmb($mysqlconnection);
	echo "</td>";
	echo "<td>";
	tblCmb($mysqlconnection, 'praxi_old', 0, 'praxi_old',NULL, "SELECT * from praxi_old where sxoletos = $sxoletos");
	echo "</td>";
	echo "</form></tr>\n";
	
	echo "<th>Επώνυμο</th>\n";
	echo "<th>Όνομα</th>\n";
	echo "<th>Ειδικότητα</th>\n";
	echo "<th>Σχ.Υπηρέτησης<br><small>(* περισσότερα από 1 σχολ.)</small></th>\n";
	echo "<th>Τύπος Απασχόλησης</th>\n";
	echo "<th>Πράξη</th>\n";
	echo "</tr>\n</thead>\n";
	
	echo "<tbody>\n";
	
	while ($i < $num)
	{
	
		$id = mysql_result($result, $i, "id");
		$name = mysql_result($result, $i, "name");
		$surname = mysql_result($result, $i, "surname");
		$klados_id = mysql_result($result, $i, "klados");
		$klados = getKlados($klados_id,$mysqlconnection);
		// an parapanw apo 1 sxoleia, deixnei mono to 1o (kyriws) kai vazei * dipla toy.
		$sx_yphrethshs_id_str = mysql_result($result, $i, "sx_yphrethshs");
		$sx_yphrethshs_id_arr = explode(",", $sx_yphrethshs_id_str);
		$sx_yphrethshs_id = trim($sx_yphrethshs_id_arr[0]);
		$sx_yphrethshs = getSchool ($sx_yphrethshs_id, $mysqlconnection);
		$sx_yphrethshs_url = "<a href=\"../school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a>";

		$type = mysql_result($result, $i, "type");
		$praxi = mysql_result($result, $i, "praxi");
		$query_p = "SELECT name from praxi_old WHERE id=$praxi AND sxoletos=$sxoletos";
		$result_p = mysql_query($query_p, $mysqlconnection);
		$praxi = mysql_result($result_p, 0, "name");
								
		echo "<tr>";
      $typos = get_type($type, $mysqlconnection);
		echo "<td><a href='ektaktoi.php?op=view&sxoletos=".$sxoletos."&id=$id'>".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>".$sx_yphrethshs_url."</td><td>$typos</td><td>$praxi</td>\n";
		echo "</tr>";

		$i++;
    }   
		echo "</tbody>\n";
                
		echo "<tr><td colspan=7 align=center>";
		$prevpg = $curpg-1;
		if ($lastpg == 0)
			$curpg = 0;
		echo "Σελίδα $curpg από $lastpg ($num_record1 εγγραφές)<br>";
		if ($curpg!=1)
		{
				echo "  <a href=ektaktoi_prev.php?sxoletos=$sxoletos&page=1&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost>Πρώτη</a>";
				echo "&nbsp;&nbsp;  <a href=ektaktoi_prev.php?sxoletos=$sxoletos&page=$prevpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost>Προηγ/νη</a>";
		}
		else
			echo "  Πρώτη &nbsp;&nbsp; Προηγ/νη";
		if ($curpg != $lastpg)
		{
				$nextpg = $curpg+1;
				echo "&nbsp;&nbsp;  <a href=ektaktoi_prev.php?sxoletos=$sxoletos&page=$nextpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost>Επόμενη</a>";
				echo "&nbsp;&nbsp;  <a href=ektaktoi_prev.php?sxoletos=$sxoletos&page=$lastpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost>Τελευταία</a>";
		}
		else 
			echo "  Επόμενη &nbsp;&nbsp; Τελευταία";
		echo "<FORM METHOD=\"POST\" ACTION=\"ektaktoi_prev.php\">";
		echo " Μετάβαση στη σελ.  <input type=\"text\" name=\"page\" size=1 />";
		echo "<input type=\"submit\" value=\"Μετάβαση\">";
                echo "<br>";
                echo "   Εγγρ./σελ.    <input type=\"text\" name=\"rpp\" value=\"$rpp\" size=1 />";
		echo "<input type=\"submit\" value=\"Ορισμός\">";
		echo "</FORM>";
		echo "</td></tr>";
		echo "<tr><td colspan=7><INPUT TYPE='button' VALUE='Πρόσληψη έκτακτου προσωπικού' onClick=\"parent.location='ektaktoi_hire.php'\">";
		echo "<tr><td colspan=7><INPUT TYPE='button' VALUE='Πράξεις έτους $sxoletos' onClick=\"parent.location='praxi_prev.php?sxoletos=$sxoletos'\">";
      echo "<tr><td colspan=7><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\"></td></tr>";
		echo "</table>\n";
      ?>
      
      <br><br>
     
    </center>
</div>
  </body>
</html>
<?php
	mysql_close();
?>
