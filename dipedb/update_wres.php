<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once"config.php";
	require_once"functions.php";
	require('calendar/tc_calendar.php');  
         
?>	
  <html>
  <head>      
        <LINK href="style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
        <script type="text/javascript">   
            $(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		});
        </script>
        
<?php        
	// update_wres: Updates wres of all employees
        function get_wres($days)
        {
            // 0-10: 24 wres, 11-15: 23 wres, 15-20: 22 wres, >20: 21 wres
            // 10y = 3600 days, 15y = 5400 days, 20y = 7200 days
            if ($days <= 3600)
                return 24;
            elseif ($days > 3600 && $days <= 5400)
                return 23;
            elseif ($days > 5400 && $days <= 7200)
                return 22;
            elseif ($days >7200)
                return 21;
        }        

        include("tools/class.login.php");
        $log = new logmein();
        if($log->logincheck($_SESSION['loggedin']) == false){
            header("Location: tools/login_check.php");
        }
        $usrlvl = $_SESSION['userlevel'];

        
        echo "<table class=\"imagetable\" border='1'>";
        echo "<form action='' method='POST' autocomplete='off'>";
        echo "<tr><td>Ημερομηνία αναζήτησης:</td><td>";
        $myCalendar = new tc_calendar("date", true);
        $myCalendar->setIcon("calendar/images/iconCalendar.gif");
        if((int)$_POST['date'])
                $myCalendar->setDate(date('d',strtotime($_POST['date'])),date('m',strtotime($_POST['date'])),date('Y',strtotime($_POST['date'])));
        $myCalendar->setPath("calendar/");
        $myCalendar->setYearInterval(1970, 2030);
        $myCalendar->dateAllow("1970-01-01", date("2020-01-01"));
        $myCalendar->setAlignment("left", "bottom");
        //$myCalendar->disabledDay("sun,sat");
        $myCalendar->writeScript();
        echo "</td></tr>";
        echo "<tr><td colspan=2>";
        echo "<input type='radio' name='type' value='0'checked >Αλλαγή ωρών<br>";
        
        if ($usrlvl==0)
            echo "<input type='checkbox' name='editvmk' value='1' />Τροποποίηση ωρών στη ΒΔ<br />";
        echo "</td></tr>";
        echo "<tr><td colspan=2><input type='submit' value='Αναζήτηση'></td></tr>";
        echo "</table></form>";
		
	if((int)$_POST['date'])
	{
		$updates = 0;
                $need_update = 0;
		$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $mysqlconnection);
		mysql_query("SET NAMES 'greek'", $mysqlconnection);
		mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
		$query = "SELECT * from employee";
                //$query = "SELECT * from employee WHERE status NOT IN (2,4)";
                // 07-08-2013
                //$query = "SELECT * from employee WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24)";
		//$query = "SELECT * from employee WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24) AND NOT aney";
                //if ($idiwtikoi)
                //    $query = "SELECT *,k.perigrafh from employee e JOIN klados k ON e.klados = k.id WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24) AND NOT aney AND thesi=5";
                //else
                //    $query = "SELECT *,k.perigrafh from employee e JOIN klados k ON e.klados = k.id WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24) AND NOT aney AND thesi!=5";
		$result = mysql_query($query, $mysqlconnection);
		$num=mysql_numrows($result);
		$dt = $_POST['date'];
                $type=$_POST['type'];
                $editvmk = $_POST['editvmk'];
		echo "<br>Ημερομηνία αναζήτησης: $dt<br>";
                ob_start();
		echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
                echo "<thead><tr><th>id</th><th>ΑΜ</th><th>Ονοματεπώνυμο</th><th>Ώρες</th><th>Ημέρες</th><th>Υ - Μ - D</th></tr></thead><tbody>";
                $aa = 1;
                $problems = 0;
            while ($i<$num)	
            {
		$id = mysql_result($result, $i, "id");
		$name = mysql_result($result, $i, "name");
		$surname = mysql_result($result, $i, "surname");
                $am = mysql_result($result, $i, "am");
                
                $old_wres = mysql_result($result, $i, "wres");
		
                $vathm = mysql_result($result, $i, "vathm");
		$mk = mysql_result($result, $i, "mk");
		$metdid = mysql_result($result, $i, "met_did");
		$hm_dior = mysql_result($result, $i, "hm_dior");
                $hm_anal = mysql_result($result, $i, "hm_anal");
		$proyp = mysql_result($result, $i, "proyp");
                $aney_xr = mysql_result($result, $i, "aney_xr");
                
                // 29-10-2012 - Skip employees from elsewhere (organikh = 388 (allo pyspe) or 394 (allo pysde)) or vathmos = A.
                $organ = mysql_result($result, $i, "sx_organikhs");
                if ($organ == 388 || $organ == 394 || strcmp($vathm,'Α')==0)
                {
                    $i++;
                    continue;
                }
		
                // 11-10-2013 - hm_dior changed to hm_anal if diafora > 30 days
                // $hm_dior1 = strtotime($hm_dior);
		$dt1 = strtotime($hm_anal);
                $dt2 = strtotime($hm_dior);
		$diafora = abs($dt1 - $dt2);
		$diafora = $diafora/86400;
		//echo $diafora;
		if ($diafora > 30)
			$hm_dior1 = strtotime($hm_anal);
		else
			$hm_dior1 = strtotime($hm_dior);
                
		$dior = (date('d',$hm_dior1) + date('m',$hm_dior1)*30 + date('Y',$hm_dior1)*360);
		$day1 = strtotime($_POST['date']);
		$cur_day = (date('d',$day1) + date('m',$day1)*30 + date('Y',$day1)*360);                
		// fixed 21-02-2012 - fixed 6-3-2012 with metdid
		// met 2 yrs (720), did 6 yrs (2160), both 7 yrs (2520)
                // - aney 27-02-2013
		//$days1 = $cur_day - $dior + $proyp;
                $days1 = $cur_day - $dior + $proyp - $aney_xr;

		// Metdid not used to compute hours!
		/*
        if ($metdid==1)
			$days = $days1 + 720;
		else if ($metdid==2)
			$days = $days1 + 2160;
		else if ($metdid==3)
			$days = $days1 + 2520;
		else
		*/
			$days = $days1;
                
		$wres = get_wres($days);
                $ymd = days2ymd($days);
				if ($wres <> $old_wres){
					echo "<tr><td>$id</td><td>$am</td><td><a href=\"employee.php?id=$id&op=view\">$surname $name</a></td><td>$wres</td><td>$days</td><td>$ymd[0] y, $ymd[1] m, $ymd[2] d</tr>";
                    $need_update++;
				}
                // allagh wrwn
                if ($editvmk && $wres <> $old_wres)
                {
                    $mkdate = date ('Y-m-d', strtotime($vdate));
                    $query1 = "UPDATE employee SET wres=$wres WHERE ID=$id";
                    mysql_query($query1, $mysqlconnection);                            
                    $updates+=1;
                }
		
		$i++;
            }
		echo "</tbody></table>";
                echo "<br>";
                
		mysql_close();
                
                if ($editvmk)
                    echo "Πραγματοποιήθηκαν $updates ενημερώσεις στη Β.Δ.";
                else
                    echo "Απαιτούνται $need_update ενημερώσεις στη Β.Δ.";
                $page = ob_get_contents(); 
		ob_end_flush();
			
		echo "<form action='2excel.php' method='post'>";
		echo "<input type='hidden' name = 'data' value='".$page."'>";
                echo "<BUTTON TYPE='submit'><IMG SRC='images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
                echo "</form>";
                echo "&nbsp;&nbsp;&nbsp;";
                // serialize & write to temp file
                $emp_ser = serialize($emp);
                $fname = "word/tmp.txt";
                file_put_contents($fname, $emp_ser);
	}
?>
<br><br>
<a href="end_of_year.php">Επιστροφή</a>
</html>