<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	Require "../config.php";
	Require "../functions.php";
	
?>
<html>
<head>      
        <script type="text/javascript" src="../js/jquery.js"></script>
        <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
        <script type="text/javascript">   
            $(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		});
        </script>
</head>
<body>
<?php
		
		$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $mysqlconnection);
		mysql_query("SET NAMES 'greek'", $mysqlconnection);
		mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
		
                $query = "SELECT * from employee";
                
                echo $query;
		$result = mysql_query($query, $mysqlconnection);
		$num=mysql_num_rows($result);
                
                echo "<table id=\"mytbl\" class=\"imagetable\" border='1'>";
                echo "<thead><tr><th>id</th><th>surname, name</th><th>hm_dior</th><th>proyp</th><th>anatr_excel</th><th>ALLAGH</th></tr></thead>";
                echo "<tbody>";
                
                
	while ($i<$num)	
	{
		$id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
		$hm_dior = mysql_result($result, $i, "hm_dior");
		$proyp = mysql_result($result, $i, "proyp");
		$met_did = mysql_result($result, $i, "met_did");
                $anatr_db = mysql_result($result, $i, "anatr_excel");
		
		
                $d1 = strtotime($hm_dior);
		
                // Met/Did MONO gia katataksi...
		 if ($met_did==1)
			$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 720;
		else if ($met_did==2)
			$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 2160;
		else if ($met_did==3)
			$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 2520;
		else
                
			$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp;
		
                		
                if ($anatr != $anatr_db)
                {
                    $anatr_days = days2date($anatr_db);
                    echo "<tr><td>id: $id</td><td>$surname, $name</td><td>$hm_dior</td><td>$proyp</td><td>$anatr_days[0]-$anatr_days[1]-$anatr_days[2]</td><td>Allagh</td>";
                }
		
		if ((strcmp($_GET['id'],'fixall')==0) && ($anatr!=$anatr_db))
		{
			$query = "UPDATE employee SET anatr='$anatr' WHERE ID=$id";
			echo "&nbsp;&nbsp;$query";
			mysql_query($query, $mysqlconnection);
			$fix+=1;
		}
		//echo "<br>";
		$i++;
		
	}
                echo "</tbody></table>";
		if ($fix)
			echo "<br>Fixed $fix record(s)";
                
		mysql_close();
?>
</body>
</html>