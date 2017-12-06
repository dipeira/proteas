<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	//require_once "functions.php";
?>
<html>
  <head>
	<LINK href="style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
	
	<script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
	<script type="text/javascript">	
	$(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		}); 
	
	</script>
  </head>

<?php
	require_once"config.php";
	require_once"functions.php";
        session_start();
        $usrlvl = $_SESSION['userlevel'];
        
        $onlysynol=0;
        $synol_find=0;
	
	$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
	mysql_select_db($db_name, $mysqlconnection);
	mysql_query("SET NAMES 'greek'", $mysqlconnection);
	mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
	$flag=0;
	if (isset($_POST['and']))
		$op = " AND";
	else
		$op = " OR";

	//old: $query = "SELECT * from employee WHERE";
        // changed to include employee teaching to multiple schools (table yphrethsh)
        $query = "SELECT * FROM employee e LEFT JOIN yphrethsh y ON e.id = y.emp_id WHERE sxol_etos=$sxol_etos AND ";
	if (strlen($_POST['name'])>0)
	{
		$query .= " name like '".$_POST['name']."'";
		$flag=1;
	}
	if (strlen($_POST['surname'])>0)
	{
		if ($flag)
			$query .= $op;
		$query .= " surname like '".$_POST['surname']."'";
		$flag=1;
	}
	if (strlen($_POST['patrwnymo'])>0)
	{
		if ($flag)
			$query .= $op;
		$query .= " patrwnymo like '".$_POST['patrwnymo']."'";
		$flag=1;
	}
	if (strlen($_POST['klados'])>0)
	{
		if ($flag)
			$query .= $op;
		$query .= " klados = '".$_POST['klados']."'";
		$flag=1;
	}
	if (strlen($_POST['am'])>0)
	{
		if ($flag)
			$query .= $op;
		$query .= " am like '".$_POST['am']."'";
		$flag=1;
	}
        if (strlen($_POST['afm'])>0)
	{
		if ($flag)
			$query .= $op;
		$query .= " afm like '".$_POST['afm']."'";
		$flag=1;
	}
        if (strlen($_POST['katast'])>0)
	{
		if ($flag)
			$query .= $op;
		$query .= " status like '".$_POST['katast']."'";
		$flag=1;
	}
	if((int)$_POST['hm_dior'])
	{
		if ($flag)
			$query .= $op;
		$query .= " hm_dior like '".$_POST['hm_dior']."'";
		$flag=1;
	}
	
	// problem if vathm=E -> cause: Unknown...
	if (strlen($_POST['vathm'])>0)
	{
		if ($flag)
			$query .= $op;
		$query .= " vathm = '".$_POST['vathm']."'";
		$flag=1;
	}
	if (strlen($_POST['mk'])>0)
	{
		if ($flag)
			$query .= $op;
		$query .= " mk like '".$_POST['mk']."'";
		$flag=1;
	}
	
	if((int)$_POST['hm_anal'])
//	if (strlen($_POST['hm_anal'])>0)
	{
		if ($flag)
			$query .= $op;
		$query .= " hm_anal like '".$_POST['hm_anal']."'";
		$flag=1;
	}
	if (strlen($_POST['met_did'])>0)
	{
		if ($flag)
			$query .= $op;
		$query .= " met_did like '".$_POST['met_did']."'";
		$flag=1;
	}
	if (strlen($_POST['pyears'])>0 || strlen($_POST['pmonths'])>0 || strlen($_POST['pdays'])>0)
	{
		if ($flag)
			$query .= $op;
		$days = $_POST['pyears']*360 + $_POST['pmonths']*30 + $_POST['pdays'];
		$query .= " proyp ".$_POST['opp']." '$days'";
		$flag=1;
	}
//	if (strlen($_POST['ayears'])>0 || strlen($_POST['amonths'])>0 || strlen($_POST['adays'])>0)
//	{
//		if ($flag)
//			$query .= $op;
//		$days = $_POST['ayears']*360 + $_POST['amonths']*30 + $_POST['adays'];
//		//$query .= " anatr = '$days'";
//		$query .= " anatr ".$_POST['opa']." '$days'";
//		$flag=1;
//	}
	if (strlen($_POST['org'])>0)
	{
		if ($flag)
			$query .= $op;
		$str1 = mb_convert_encoding($_POST['org'], "iso-8859-7", "utf-8");
		$org = getSchoolID($str1,$mysqlconnection);
		$query .= " sx_organikhs = '$org'";
		$flag=1;
	}
	if (strlen($_POST['yphr'])>0)
	{
		if ($flag)
			$query .= $op;
		$str1 = mb_convert_encoding($_POST['yphr'], "iso-8859-7", "utf-8");
		$yphr = getSchoolID($str1,$mysqlconnection);
		//$query .= " sx_yphrethshs = '$yphr'";
                $query .= " (sx_yphrethshs = '$yphr' OR yphrethsh = '$yphr')";
		$flag=1;
	}
        
        
        if (strlen($_POST['syears'])>0 || strlen($_POST['smonths'])>0 || strlen($_POST['sdays'])>0)
	{
		
		$synol_find = $_POST['syears']*360 + $_POST['smonths']*30 + $_POST['sdays'];
                if (!$flag)
                    $onlysynol=1;
                $flag=1;
	}
        if ((int)$_POST['hm_synol'])
                    $d1 = strtotime($_POST['hm_synol']);
        else
                    $d1 = strtotime ("now");
	
        // comments: added 02-08-2013
        if (strlen($_POST['comments'])>0)
	{
		if ($flag)
			$query .= $op;
		$query .= " comments like '".$_POST['comments']."'";
		$flag=1;
	}
        
        
	if (!$flag)
		echo "<p>Παρακαλώ επιλέξτε κάποιο κριτήριο αναζήτησης...</p>";
        if ($flag && $onlysynol)
                $query = "SELECT * from employee";
	if ($flag)
	{
		$i=0;
		$query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
			//echo $query; // for debugging...
		$result = mysql_query($query, $mysqlconnection);
		$num = mysql_num_rows($result);
		
		if ($num==0)
			echo "<BR><p>Κανένα αποτέλεσμα...</p>";
		else
		{
			echo "<p>Πλήθος εγγραφών που βρέθηκαν: $num<p>";
                        $num1=$num;
                        $num2=$num;
			echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=iso8859-7\">";
			echo "<body>";
			echo "<center>";
			ob_start();
			echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
			echo "<thead><tr><th>Ενέργεια</th>";
			echo "<th>Επώνυμο</th>";
			echo "<th>Όνομα</th>";
			echo "<th>Κλάδος</th>";
			echo "<th>Σχ.Οργανικής</th>";
			echo "<th>Σχ.Υπηρέτησης</td></th>\n";
		
			if (isset($_POST['dsppatr']))
				echo "<th>Πατρώνυμο</th>\n";
			if (isset($_POST['dsphm_dior']))
				echo "<th>Ημ/νία Διορισμού</th>\n";
			if (isset($_POST['dspmetdid']))
				echo "<th>Μεταπτ./Διδακτ.</th>\n";
			if (isset($_POST['dspam']))
				echo "<th>A.M.</th>\n";
                        if (isset($_POST['dspafm']))
				echo "<th>A.Φ.M.</th>\n";
			if (isset($_POST['dspproyhp']))
				echo "<th>Προϋπηρεσία</th>\n";
			if (isset($_POST['dspvathmos']))
				echo "<th>Βαθμός</th>\n";
			if (isset($_POST['dspmk']))
				echo "<th>M.K.</th>\n";
                        if (isset($_POST['dspkatast']))
				echo "<th>Κατάσταση</th>\n";
                        if (isset($_POST['dspsynol']))
				echo "<th>Συνολική Υπηρεσία</th>\n";
			echo "</tr></thead>\n<tbody>";
			while ($i < $num)
			{
                                // fix to avoid double records display
                                $oldid = $id;
				$id = mysql_result($result, $i, "id");
                                if ($oldid == $id)
                                {
                                    $i++;
                                    continue;
                                }
				$name = mysql_result($result, $i, "name");
				$surname = mysql_result($result, $i, "surname");
				$klados_id = mysql_result($result, $i, "klados");
				$klados = getKlados($klados_id,$mysqlconnection);
				$sx_organ_id = mysql_result($result, $i, "sx_organikhs");
				$sx_organikhs = getSchool ($sx_organ_id, $mysqlconnection);
				$sx_yphrethshs_id = mysql_result($result, $i, "sx_yphrethshs");
				$sx_yphrethshs = getSchool ($sx_yphrethshs_id, $mysqlconnection);
                                // check if multiple schools
                                $qry = "select * from yphrethsh where emp_id=$id and sxol_etos=$sxol_etos";
                                $res = mysql_query($qry,$mysqlconnection);
                                if (mysql_num_rows($res) > 0)
                                $sx_yphrethshs .= "*";
				
				$patrwnymo = mysql_result($result, $i, "patrwnymo");
				$am = mysql_result($result, $i, "am");
                                $afm = mysql_result($result, $i, "afm");
				$vathm = mysql_result($result, $i, "vathm");
				$mk = mysql_result($result, $i, "mk");
				$hm_dior = mysql_result($result, $i, "hm_dior");
				$met_did = mysql_result($result, $i, "met_did");
				$proyp = mysql_result($result, $i, "proyp");
                                $katast = mysql_result($result, $i, "status");
                                $i++;
                                if (isset($_POST['dspsynol']))
                                {
                                    $hm_dior1 = strtotime($hm_dior);
                                    $temp = (date('d',$hm_dior1) + date('m',$hm_dior1)*30 + date('Y',$hm_dior1)*360);
                                    $res1 = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $temp + $proyp;
                                    
                                    if ($synol_find)    
                                    {
                                        if ((strcmp($_POST['ops'],"<")==0) && ($res1 >= $synol_find))
                                        {
                                                $num1-=1;
                                                continue;
                                        }
                                        if ((strcmp($_POST['ops'],"=")==0) && ($res1 <> $synol_find))
                                        {
                                                $num1-=1;
                                                continue;
                                        }
                                        if ((strcmp($_POST['ops'],">")==0) && ($res1 <= $synol_find))
                                        {
                                                $num1-=1;
                                                continue;
                                        }
                                    }
                                     
                                }
										
				echo "<tr><td>";
				echo "<span title=\"Προβολή\"><a href=\"employee.php?id=$id&op=view\"><img style=\"border: 0pt none;\" src=\"images/view_action.png\"/></a></span>";
				if ($usrlvl < 3)
                                    echo "<span title=\"Επεξεργασία\"><a href=\"employee.php?id=$id&op=edit\"><img style=\"border: 0pt none;\" src=\"images/edit_action.png\"/></a></span>";
				echo "</td>";
				echo "<td><a href=\"employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>".$sx_organikhs."</td><td>".$sx_yphrethshs."</td>\n";
				if (isset($_POST['dsppatr']))
					echo "<td>$patrwnymo</td>\n";
				if (isset($_POST['dsphm_dior']))
					echo "<td>".date('d-m-Y',strtotime($hm_dior))."</td>\n";
				if (isset($_POST['dspmetdid']))
				{
					switch ($met_did)
					{
						case 0:
							echo "<td>Όχι</td>\n";
							break;
						case 1:
							echo "<td>Μεταπτυχιακό</td>\n";
							break;
						case 2:
							echo "<td>Διδακτορικό</td>\n";
							break;
					}
				}
				if (isset($_POST['dspam']))
					echo "<td>$am</td>\n";
                                if (isset($_POST['dspafm']))
					echo "<td>$afm</td>\n";
				if (isset($_POST['dspproyhp']))
				{
					$ymd=days2ymd($proyp);
					echo "<td>$ymd[0] Έτη, $ymd[1] Μήνες, $ymd[2] Ημέρες</td>\n";
				}
				if (isset($_POST['dspvathmos']))
					echo "<td>$vathm</td>\n";
				if (isset($_POST['dspmk']))
					echo "<td>$mk</td>\n";
                                if (isset($_POST['dspkatast']))
				{
					switch ($katast)
					{
						case 1:
							echo "<td>Εργάζεται</td>\n";
							break;
						case 2:
							echo "<td>Λύση Σχέσης-Παραίτηση</td>\n";
							break;
						case 3:
							echo "<td>Άδεια</td>\n";
							break;
						case 4:
							echo "<td>Διαθεσιμότητα</td>\n";
							break;
					}
				}
                                if (isset($_POST['dspsynol']))
				{
                                    $ymd=days2ymd($res1);	
                                    echo "<td>Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2]</td>";
				}
				echo "</tr>";

				
			}
		//}			
			echo "</tbody></table>";
                        
                        if ($synol_find)
                        {
                            $asd = $num1;
                            echo "Εγγραφές που πληρούν τα κριτήρια: $asd";
                        }
			$page = ob_get_contents(); 
			ob_end_flush();
			
			echo "<form action='2excel.php' method='post'>";
			echo "<input type='hidden' name = 'data' value='$page'>";
			//echo "<INPUT TYPE='submit' VALUE='Εξαγωγή στο excel'></form>";
			echo "<BUTTON TYPE='submit'><IMG SRC='images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
			//ob_end_clean();
		}
		echo "</center>";
		echo "</body>";
		echo "</html>";
				
		
	}	
?>