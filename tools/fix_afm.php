<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	Require "../config.php";
	Require "../functions.php";
	
	// 01-03-2012
	// fix_afm.php
	// converts all 8-digit afm numbers to valid 9-digit by adding 0 in front.
		
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'greek'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
  
  $query = "SELECT id,afm from employee";
	$result = mysqli_query($mysqlconnection, $query);
  $num=mysqli_num_rows($result);
  
	while ($i<$num)	
	{
		$id = mysqli_result($result, $i, "id");
		$afm = mysqli_result($result, $i, "afm");
		
		$len = strlen($afm);
		if ($len==8)
		{
			$newafm = "0".$afm;
			echo "<br>$id: afm: $afm, length: $len - newafm: $newafm - ";
			
			$query1 = "UPDATE employee SET afm='$newafm' WHERE id='$id'";
			echo $query1;
			$result1 = mysqli_query($mysqlconnection, $query1);
			$count++;
		}
		$i++;
		
	}
	if (!$count)
		echo "Δε βρέθηκαν εγγραφές";
	else
		echo "<br><br>Διορθώθηκαν $count εγγραφές";	
?>