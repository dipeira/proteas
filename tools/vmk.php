<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	Require "../config.php";
	Require "../functions.php";
	
	echo "vmk: Checks & fixes vathmos & mk of employee records for 01-11-2011";
        echo "<br>vmk.php?id=			number: checks specified id";
	echo "<br>				all: checks all records";
	echo "<br>				fixall: checks all & fixes wrong records (Careful!)";
	echo "<br>date=         		all the above for the given date<br><br>";
	
		
		$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $mysqlconnection);
		mysql_query("SET NAMES 'greek'", $mysqlconnection);
		mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
		
		if ((isset($_GET['id'])) && is_numeric($_GET['id']))
			$query = "SELECT * from employee where id=".$_GET['id'];
                else
                        $query = "SELECT * from employee";
                
                echo $query;
		$result = mysql_query($query, $mysqlconnection);
		$num=mysql_num_rows($result);
	while ($i<$num)	
	{
		$id = mysql_result($result, $i, "id");
		$vathm = mysql_result($result, $i, "vathm");
		$mk = mysql_result($result, $i, "mk");
		$hm_dior = mysql_result($result, $i, "hm_dior");
		$proyp = mysql_result($result, $i, "proyp");
		$metdid = mysql_result($result, $i, "met_did");
		
		$hm_dior1 = strtotime($hm_dior);
		$dior = (date('d',$hm_dior1) + date('m',$hm_dior1)*30 + date('Y',$hm_dior1)*360);
		
		if (isset($_GET['date']))
		{
			$tmp = strtotime($_GET['date']);
			$day1 = (date('d',$tmp) + date('m',$tmp)*30 + date('Y',$tmp)*360);
		}
		else
			$day1 = 724291;
		
		if ($metdid==1)
			$days = $day1 - $dior + $proyp + 720;
		else if ($metdid==2)
			$days = $day1 - $dior + $proyp + 2160;
		else if ($metdid==3)
			$days = $day1 - $dior + $proyp + 2520;
		else
			$days = $day1 - $dior + $proyp;
		
		//$days = $day1 - $dior + $proyp;
		//$d1 = strtotime($hm_dior);
		//$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp ;
		
		//$vath = vathmos($days);
                $vath[0] = $vathm;
		$mk1 = mk($days,$vath);
               
		echo "<br>id: $id,&nbsp;&nbsp;hmdior, days: $hm_dior, $dior";
		echo "&nbsp;&nbsp;proyp: $proyp";
		echo "&nbsp;&nbsp;vathm: $vathm";
		echo "&nbsp;&nbsp;mk (vash): $mk";
		echo "&nbsp;&nbsp;days: $days";
		echo "&nbsp;&nbsp;Βαθμός: $vath[0]";
		echo "&nbsp;&nbsp;MK: $mk1";
		
		if (strcmp($vathm,$vath[0])!=0)
		{
			echo "&nbsp;&nbsp;wrong vathmos";
			$notvathmos=1;
                        $wrongvathmos+=1;
		}
		else
			echo "&nbsp;&nbsp;vathmos OK!";
		if ($mk!=$mk1)
		{
			echo "&nbsp;&nbsp;wrong mk";
			$notmk=1;
                        $wrongmk+=1;
		}
		else
			echo "&nbsp;&nbsp;mk OK!";
		
		if ((strcmp($_GET['id'],'fixall')==0) && ($notvathmos || $notmk))
		{
			$query = "UPDATE employee SET vathm='$vath[0]', mk='$mk1' WHERE ID=$id";
			echo "&nbsp;&nbsp;$query";
			mysql_query($query, $mysqlconnection);
			$fix+=1;
		}
		//echo "<br>";
		$i++;
		$notmk=$notvathmos=0;
	}
                if ($wrongmk || $wrongvathmos)
                    echo "<br><br>Λάθος Βαθμός: $wrongvathmos, Λάθος ΜΚ: $wrongmk";
                else
                    echo "<br><br>Όλες οι εγγραφές ($num) είναι έγκυρες...";
                
		if ($fix)
			echo "<br>Fixed $fix record(s)";
                
		mysql_close();
?>