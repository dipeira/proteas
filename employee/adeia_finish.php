<?php
	header('Content-type: text/html; charset=iso8859-7'); 
//	require_once"../config.php";
	
			
	//if (isset($_POST['yphr']))
	$d1 = strtotime($_POST['start']);
        $days = $_POST['days'];
	$result = (date('d',$d1)+$days + date('m',$d1) + date('Y',$d1));
	/*
        if ($result<=0)
		echo "����� ����������";
	else
	{
		$ymd=days2ymd($result);	
		echo "���: $ymd[0] &nbsp; �����: $ymd[1] &nbsp; ������: $ymd[2]";
		$vath = vathmos($result);
		$mk = mk($result);
		echo "<br>������: $vath[0]";
		echo "<br>MK: $mk";
	}
	*/
        echo $result;
		
?>