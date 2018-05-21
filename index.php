<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  Require_once "config.php";
  Require_once "tools/functions.php";
    
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
    include("tools/class.login.php");
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false)
    {   
        header("Location: tools/login_check.php");
    }
    else
		  $logged = 1;
	
  $usrlvl = $_SESSION['userlevel'];
      
?>
<html>
  <head>
	<a href="/"><IMG src="images/logo.png" class="applogo"></a>
	<LINK href="css/style.css" rel="stylesheet" type="text/css">
	<meta http-equiv="content-type" content="text/html; charset=iso8859-7">
	<title>Πρωτέας</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<link rel="icon" href="favicon.ico" type="image/x-icon">
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>
	<!--<script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>-->
	<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
	<script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
  <script type="text/javascript" src="js/jquery_notification_v.1.js"></script>
	<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
  <link href="css/jquery_notification.css" type="text/css" rel="stylesheet"/> 
	<script type="text/javascript">		
		$().ready(function() {
			$("#org").autocomplete("employee/get_school.php", {
				width: 260,
				matchContains: true,
				selectFirst: false
			});
			$("#yphr").autocomplete("employee/get_school.php", {
				width: 260,
				matchContains: true,
				selectFirst: false
			});
			$("#surname").autocomplete("employee/get_name.php", {
				width: 260,
				matchContains: true,
				//mustMatch: true,
				selectFirst: false
			});
			$("#surname").result(function(event, data, formatted) {
				if (data){
						$("#pinakas").val(data[1]);
				}
			});
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		});         
	</script>
	
  </head>
  <body> 
    
  <?php include('etc/menu.php'); ?>

<div>
		<?php
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

		//$query = "SELECT * FROM employee ORDER BY surname ";
                $query = "SELECT * FROM employee ";
	  	
		$klpost = 0;
		$orgpost = 0;
		$yppost = 0;
		if (($_POST['klados']>0) || (strlen($_POST['org'])>0) || (strlen($_POST['yphr'])>0) || (strlen($_POST['surname'])>0))
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
			$query .= "WHERE klados = $klpost ";
			$whflag=1;
		}
		if ((strlen($_POST['org'])>0) || ($_GET['org']>0))
		{
			if (strlen($_POST['org'])>0)
				$orgpost = getSchoolID ($_POST['org'],$mysqlconnection);
			if ($_GET['org']>0)
				$orgpost = $_GET['org'];
			if ($whflag)
				$query .= "AND sx_organikhs = $orgpost ";
			else
			{
				$query .= "WHERE sx_organikhs = $orgpost ";
				$whflag=1;
			}	
		}
		if ((strlen($_POST['yphr'])>0) || ($_GET['yphr']>0))
		{
			if (strlen($_POST['yphr'])>0)
				$yppost = getSchoolID ($_POST['yphr'], $mysqlconnection);
			if ($_GET['yphr']>0)
				$yppost = $_GET['yphr'];
			if ($whflag)
				$query .= "AND sx_yphrethshs = $yppost ";
			else
				$query .= "WHERE sx_yphrethshs = $yppost ";
		}
                // if ektaktos
                if (strlen($_POST['surname'])>0 && $_POST['pinakas']==1)
                {
                    $surn = explode(' ',$_POST['surname'])[0];
                    $url = "employee/ektaktoi_list.php?surname=".urlencode($surn);
                    echo "<script>window.location = '$url'</script>";
                }
		if (strlen($_POST['surname'])>0 || strlen($_GET['surname'])>0)
		{
			if (strlen($_POST['surname'])>0)
                            $surpost = explode(' ',$_POST['surname'])[0];
                        else
                            $surpost = $_GET['surname'];
			if ($whflag)
				$query .= "AND surname LIKE '$surpost' ";
			else
			{
				$query .= "WHERE surname LIKE '$surpost' ";
				$whflag=1;
			}
		}
                // ΟΧΙ idiwtikoi
                if ($whflag)
                    $query .= " AND thesi!=5 ";
                else
                    $query .= " WHERE thesi!=5 ";
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
		
		// added 24-01-2013 - when 1 result, redirect to that employee page
		if ($num_record == 1)
		{
				$id = mysql_result($result, 0, "id");
				$url = "employee/employee.php?id=$id&op=view";
				echo "<script>window.location = '$url'</script>";
		}
	
	if ($logged)
		{
				echo "<p class='userdata'>Ενεργός Χρήστης: ".$_SESSION['user']."&nbsp;&nbsp;-&nbsp;&nbsp;Σχολ.Έτος:&nbsp;".getParam ('sxol_etos', $mysqlconnection)."</p>";
		}
  echo "<center>";        
	echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr><form id='src' name='src' action='index.php' method='POST'>\n";
	if ($posted || ($_GET['klados']>0) || ($_GET['org']>0) || ($_GET['yphr']>0))
		echo "<td><INPUT TYPE='submit' VALUE='Επαναφορά'></td><td>\n";
	else	
		echo "<td><INPUT TYPE='submit' VALUE='Αναζήτηση'></td><td>\n";
	echo "<input type='text' name='surname' id='surname''/>\n";
  echo "<input type='hidden' name='pinakas' id='pinakas' />";
	echo "<td><span title='Ψάχνει σε μόνιμους & αναπληρωτές, εμφανίζοντας σε παρένθεση τη σχέση εργασίας'><small>(Σε μόνιμους<br> & αναπληρωτές)</small><img style=\"border: 0pt none;\" src=\"images/help.gif\" height='12' width='12'/></span></td></td><td>\n";
	kladosCmb($mysqlconnection);
	echo "</td>\n";
		echo "<div id=\"content\">";
		echo "<form autocomplete=\"off\">";
		echo "<td><input type=\"text\" name=\"org\" id=\"org\" /></td>";
		echo "<td><input type=\"text\" name=\"yphr\" id=\"yphr\" /></td>";
		echo "</div>";
	echo "</td>";
	echo "</form></tr>\n";
        
	//echo "<thead>\n<tr><th>Ενέργεια</th>\n";
        echo "<tr><th>Ενέργεια</th>\n";
	//echo "<th>ID</th>\n";
	echo "<th>Επώνυμο</th>\n";
	echo "<th>Όνομα</th>\n";
	echo "<th>Ειδικότητα</th>\n";
	echo "<th>Σχ.Οργανικής</th>\n";
	echo "<th>Σχ.Υπηρέτησης</th>\n";
	echo "</tr>\n</thead>\n";
	
	echo "<tbody>\n";
	
	while ($i < $num)
	{
	
		$id = mysql_result($result, $i, "id");
		$name = mysql_result($result, $i, "name");
		$surname = mysql_result($result, $i, "surname");
		$klados_id = mysql_result($result, $i, "klados");
		$klados = getKlados($klados_id,$mysqlconnection);
		$sx_organ_id = mysql_result($result, $i, "sx_organikhs");
		$sx_organikhs = getSchool ($sx_organ_id, $mysqlconnection);
		$sx_yphrethshs_id = mysql_result($result, $i, "sx_yphrethshs");
		$sx_yphrethshs = getSchool ($sx_yphrethshs_id, $mysqlconnection);
                $sx_organikhs_url = "<a href=\"school/school_status.php?org=$sx_organ_id\">$sx_organikhs</a>";
                $sx_yphrethshs_url = "<a href=\"school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a>";
                // check if multiple schools
                $qry = "select * from yphrethsh where emp_id=$id and sxol_etos=$sxol_etos";
                $res = mysql_query($qry,$mysqlconnection);
                if (mysql_num_rows($res) > 0)
                    $sx_yphrethshs .= "*";
								
		echo "<tr><td>";
		echo "<span title=\"Προβολή\"><a href=\"employee/employee.php?id=$id&op=view\"><img style=\"border: 0pt none;\" src=\"images/view_action.png\"/></a></span>";
		if ($usrlvl < 3)
                    echo "<span title=\"Επεξεργασία\"><a href=\"employee/employee.php?id=$id&op=edit\"><img style=\"border: 0pt none;\" src=\"images/edit_action.png\"/></a></span>";
		if ($usrlvl < 2)
                    echo "<span title=\"Διαγραφή\"><a href=\"javascript:confirmDelete('employee/employee.php?id=$id&op=delete')\"><img style=\"border: 0pt none;\" src=\"images/delete_action.png\"/></a></span>";
		echo "</td>";
		echo "<td><a href=\"employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>".$sx_organikhs_url."</td><td>".$sx_yphrethshs_url."</td>\n";
		echo "</tr>";

		$i++;
    }   
		echo "</tbody>\n";
                if ($usrlvl < 2)
                    echo "<tr><td colspan=7><span title=\"Προσθήκη\"><a href=\"employee/employee.php?id=$id&op=add\"><img style=\"border: 0pt none;\" src=\"images/user_add.png\"/>Προσθήκη εκπαιδευτικού</a></span>";		
		echo "<tr><td colspan=7 align=center>";
		$prevpg = $curpg-1;
		if ($lastpg == 0)
			$curpg = 0;
		echo "Σελίδα $curpg από $lastpg ($num_record1 εγγραφές)<br>";
		if ($curpg!=1)
		{
				echo "  <a href=index.php?page=1&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost>Πρώτη</a>";
				echo "&nbsp;&nbsp;  <a href=index.php?page=$prevpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost>Προηγ/νη</a>";
		}
		else
			echo "  Πρώτη &nbsp;&nbsp; Προηγ/νη";
		if ($curpg != $lastpg)
		{
				$nextpg = $curpg+1;
				echo "&nbsp;&nbsp;  <a href=index.php?page=$nextpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost>Επόμενη</a>";
				echo "&nbsp;&nbsp;  <a href=index.php?page=$lastpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost>Τελευταία</a>";
		}
		else 
			echo "  Επόμενη &nbsp;&nbsp; Τελευταία";
		echo "<FORM METHOD=\"POST\" ACTION=\"index.php\">";
		echo " Μετάβαση στη σελ.  <input type=\"text\" name=\"page\" size=1 />";
		echo "<input type=\"submit\" value=\"Μετάβαση\">";
                echo "<br>";
                echo "   Εγγρ./σελ.    <input type=\"text\" name=\"rpp\" value=\"$rpp\" size=1 />";
		echo "<input type=\"submit\" value=\"Ορισμός\">";
		echo "</FORM>";
		echo "</td></tr>";
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
