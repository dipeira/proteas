<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	Require "../config.php";
	Require "../functions.php";
	
	// 01-03-2012
	// fix_afm.php
	// converts all 8-digit afm numbers to valid 9-digit by adding 0 in front.
		
		$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $mysqlconnection);
		mysql_query("SET NAMES 'greek'", $mysqlconnection);
		mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
			$query = "SELECT id,afm from employee";
		$result = mysql_query($query, $mysqlconnection);
		$num=mysql_num_rows($result);
	while ($i<$num)	
	{
		$id = mysql_result($result, $i, "id");
		$afm = mysql_result($result, $i, "afm");
		
		$len = strlen($afm);
		if ($len==8)
		{
			$newafm = "0".$afm;
			echo "<br>$id: afm: $afm, length: $len - newafm: $newafm - ";
			
			$query1 = "UPDATE employee SET afm='$newafm' WHERE id='$id'";
			echo $query1;
			$result1 = mysql_query($query1, $mysqlconnection);
			$count++;
		}
		$i++;
		
	}
	if (!$count)
		echo "Δε βρέθηκαν εγγραφές";
	else
		echo "<br><br>Διορθώθηκαν $count εγγραφές";	
?>