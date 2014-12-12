<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	Require "../config.php";
	Require "../functions.php";
	
	
		
		$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $mysqlconnection);
		mysql_query("SET NAMES 'greek'", $mysqlconnection);
		mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
		
                $query = "SELECT * from employee";
                
                echo $query;
		$result = mysql_query($query, $mysqlconnection);
		$num=mysql_numrows($result);
	while ($i<$num)	
	{
		$id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
		$hm_dior = mysql_result($result, $i, "hm_dior");
		$proyp = mysql_result($result, $i, "proyp");
		$met_did = mysql_result($result, $i, "met_did");
                $anatr_db = mysql_result($result, $i, "anatr");
		
		
                $d1 = strtotime($hm_dior);
		
                // Met/Did MONO gia katataksi...
		/* if ($met_did==1)
			$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 720;
		else if ($met_did==2)
			$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 2160;
		else if ($met_did==3)
			$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 2520;
		else
                */
			$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp;
		
                
		echo "<br>id: $id,&nbsp;&nbsp;on/mo: $surname, $name &nbsp;&nbsp;hmdior: $hm_dior";
		echo "&nbsp;&nbsp;proyp: $proyp";
		echo "&nbsp;&nbsp;anatr: $anatr";
                if ($anatr != $anatr_db)
                    echo "&nbsp;&nbsp;ALLAGH";
		
		
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
                
		if ($fix)
			echo "<br>Fixed $fix record(s)";
                
		mysql_close();
?>