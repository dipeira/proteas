<?php
	header('Content-type: text/html; charset=iso8859-7'); 
//	require_once"../config.php";
	
			
	//if (isset($_POST['yphr']))
	$d1 = strtotime($_POST['start']);
        $days = $_POST['days'];
	$result = (date('d',$d1)+$days + date('m',$d1) + date('Y',$d1));
	/*
        if ($result<=0)
		echo "Λάθος ημερομηνία";
	else
	{
		$ymd=days2ymd($result);	
		echo "Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2]";
		$vath = vathmos($result);
		$mk = mk($result);
		echo "<br>Βαθμός: $vath[0]";
		echo "<br>MK: $mk";
	}
	*/
        echo $result;
		
?>