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
	
	$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
	mysql_select_db($db_name, $mysqlconnection);
	mysql_query("SET NAMES 'greek'", $mysqlconnection);
	mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);

	$op = " AND";
        if (!$_POST['mon_anapl'])
            $query = "SELECT * from adeia WHERE";
        else
            $query = "SELECT * from adeia_ekt WHERE";
	$query .= " start >= '".$_POST['hm_from']."'";
	$query .= " AND start <= '".$_POST['hm_to']."'";
		
	if (strlen($_POST['type'])>0)
	{
		$query .= $op;
		$query .= " type like '".$_POST['type']."'";
		
	}
		
		$i=0;
		$query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
		//	echo $query; // for debugging...
		$result = mysql_query($query, $mysqlconnection);
		$num = mysql_num_rows($result);
		
		if ($num==0)
			echo "<BR><p>Κανένα αποτέλεσμα...</p>";
		else
		{
			//echo "<p>Πλήθος εγγραφών που βρέθηκαν: $num<p>";
                        $num1=$num;
                        $num2=$num;
			echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=iso8859-7\">";
			echo "<body>";
			echo "<center>";
			ob_start();
			echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
			echo "<thead><tr>";
			echo "<th>Επώνυμο, Όνομα</th>";
			echo "<th>Είδος</th>";
                        echo "<th>Έναρξη</th>";
                        echo "<th>Λήξη</th>";
                        echo "<th>Αρ.Πρωτοκόλλου</th>";
                        echo "<th>Αρ.Απόφασης</th>";
			echo "</th>\n";
					
			echo "</tr></thead>\n<tbody>";
			while ($i < $num)
			{
                                
				$id = mysql_result($result, $i, "id");
                                $emp_id = mysql_result($result, $i, "emp_id");
                                
                                if (!$_POST['mon_anapl'])
                                    $query0 = "select name,surname from employee where id=$emp_id";
                                else
                                    $query0 = "select name,surname from ektaktoi where id=$emp_id";
                                $result0 = mysql_query($query0, $mysqlconnection);
                                $test = mysql_num_rows($result0);
                                //Skip deleted employees
                                if ($test == 0)
                                {
                                    $del++;
                                    $i++;
                                    continue;
                                }
                                else
                                {
				$name = mysql_result($result0, 0, "name");
				$surname = mysql_result($result0, 0, "surname");
                                                                
				$start = mysql_result($result, $i, "start");
				$finish = mysql_result($result, $i, "finish");
                                $start = date ("d-m-Y", strtotime($start));
                                $finish = date ("d-m-Y", strtotime($finish));
                                
                                $ar_prot = mysql_result($result, $i, "prot");
                                $hm_prot = mysql_result($result, $i, "hm_prot");
                                $apof = mysql_result($result, $i, "prot_apof");
                                $hm_apof = mysql_result($result, $i, "hm_apof");
                                if ($apof>0)
                                    $apof_all = $apof."/".date("d-m-Y", strtotime($hm_apof));
                                else
                                    $apof_all = "";
				
                                $type = mysql_result($result, $i, "type");
				$query1 = "select type from adeia_type where id=$type";
                                $result1 = mysql_query($query1, $mysqlconnection);
                                $typewrd = mysql_result($result1, 0, "type");
                                
                                $i++;
                                										
				echo "<tr><td>";
                                if (!$_POST['mon_anapl']){
                                    $tmpl = "adeia";
                                    $tmpl1 = "employee";
                                }
                                else{
                                    $tmpl = "ekt_adeia";
                                    $tmpl1 = "ektaktoi";
                                }
				echo "<a href=\"".$tmpl1.".php?id=$emp_id&op=view\">$surname, $name</a></td><td><a href=\"".$tmpl.".php?adeia=$id&op=view\">$typewrd</a></td>
                                <td>$start</td><td>$finish</td><td>$ar_prot/".date("d-m-Y",  strtotime($hm_prot))."</td><td>$apof_all\n";
				echo "</tr>";
                                }
				
			}
			echo "</tbody></table>";

			$page = ob_get_contents(); 
			ob_end_flush();
			
                        $num -= $del;
                        echo "<p>Πλήθος εγγραφών που βρέθηκαν: $num<p>";
			echo "<form action='2excel.php' method='post'>";
			echo "<input type='hidden' name = 'data' value='$page'>";
			echo "<BUTTON TYPE='submit'><IMG SRC='images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
			//ob_end_clean();
		}
		echo "</center>";
		echo "</body>";
		echo "</html>";
				
			
?>