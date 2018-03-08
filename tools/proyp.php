<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	Require "../config.php";
	Require "../functions.php";
?>
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
		
                //$query = "SELECT * from employee";
                $query = "SELECT * from my_test";
                
                echo $query;
		$result = mysql_query($query, $mysqlconnection);
		$num=mysql_num_rows($result);
                
                echo "<table id=\"mytbl\" class=\"imagetable\" border='1'>";
                echo "<thead><tr><th>id</th><th>surname, name</th><th>hm_dior</th><th>proyp_misth</th><th>proyp</th><th>met_did</th><th>anatr_excel</th><th>ALLAGH</th><th>diafora</th></tr></thead>";
                echo "<tbody>";
	while ($i<$num)	
	{
		$id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
		$hm_dior = mysql_result($result, $i, "hm_dior");
		$proyp_old = mysql_result($result, $i, "proyp_misth");
		$met_did = mysql_result($result, $i, "met_did");
                $anatr = mysql_result($result, $i, "excel_anatr");
		$ymd = days2date($anatr);
		
                $d1 = strtotime($hm_dior);
		
                // Met/Did MONO gia katataksi...
		if ($met_did==1)
			//$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 720;
                        $proyp = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $anatr - 720;
		else if ($met_did==2)
			//$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 2160;
                        $proyp = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $anatr - 2160;
		else if ($met_did==3)
			//$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 2520;
                        $proyp = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $anatr - 2520;
		else
			//$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp;
                        $proyp = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $anatr;
		
                

                //if (($proyp != $proyp_old))
                //if (($proyp < $proyp_old) && ($proyp>=0))
                if (!$_GET_['id'] && ($proyp != $proyp_old))
                {
                    $diaf = abs($proyp-$proyp_old);
                    echo "<tr><td>$id</td><td>$surname, $name</td><td>$hm_dior</td><td>$proyp_old</td><td>$proyp</td><td>$met_did</td><td>$ymd[0]-$ymd[1]-$ymd[2]</td><td>ALLAGH</td><td>$diaf</tr>";
                    $cnt+=1;
                }
		
		//if ((strcmp($_GET['id'],'fixall')==0) && ($proyp != $proyp_old))
                if ((strcmp($_GET['id'],'fixall')==0))
		{
			$query = "UPDATE employee SET proyp='$proyp',anatr_excel='$anatr' WHERE ID=$id";
			echo "&nbsp;&nbsp;$query";
                        echo "<br>";
			mysql_query($query, $mysqlconnection);
			$fix+=1;
		}
		if ((strcmp($_GET['id'],'vmk')==0))
		{
			$vathmos = mysql_result($result, $i, "exc_v");
                        $mk = mysql_result($result, $i, "ex_mk");
                        $query = "UPDATE employee SET vathm='$vathmos',mk='$mk' WHERE ID=$id";
			echo "&nbsp;&nbsp;$query";
                        echo "<br>";
			mysql_query($query, $mysqlconnection);
			$fix+=1;
		}
		$i++;
		
	}
                echo "</tbody></table>";
		if ($fix)
			echo "<br>Fixed $fix record(s)";
                if ($cnt)
                        echo "<br>$cnt allages";
                
		mysql_close();
?>
</body>
</html>