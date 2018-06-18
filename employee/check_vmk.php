<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once"../config.php";
	require_once"../tools/functions.php";
	require('../tools/calendar/tc_calendar.php');  
         
?>	
  <html>
  <head>      
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
        <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
        <script type="text/javascript">   
            $(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		});
        </script>
   </head>
   <body>
   <?php include('../etc/menu.php'); ?>
<?php        
	// check_vmk: Checks vathmos & mk of all employee records for the date provided
                

        include("../tools/class.login.php");
        $log = new logmein();
        if($log->logincheck($_SESSION['loggedin']) == false){
            header("Location: ../tools/login.php");
        }
        $usrlvl = $_SESSION['userlevel'];

        $idiwtikoi = 0;
        if ($_GET['id']==1)
            $idiwtikoi = 1;
        if ($idiwtikoi)
            echo "<h2>M.K. Ιδιωτικών Εκπ/κών</h2>";
        else
            echo "<h2>M.K. Μονίμων Εκπ/κών</h2>";
        echo "<table class=\"imagetable stable\" border='1'>";
        echo "<form action='' method='POST' autocomplete='off'>";
        echo "<tr><td>Ημερομηνία αναζήτησης:</td><td>";
        $myCalendar = new tc_calendar("date", true);
        $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
        if((int)$_POST['date'])
                $myCalendar->setDate(date('d',strtotime($_POST['date'])),date('m',strtotime($_POST['date'])),date('Y',strtotime($_POST['date'])));
        $myCalendar->setPath("../tools/calendar/");        
        $myCalendar->setYearInterval(1970, 2030);
        $myCalendar->dateAllow("1970-01-01", date("2020-01-01"));
        $myCalendar->setAlignment("left", "bottom");
        //$myCalendar->disabledDay("sun,sat");
        $myCalendar->writeScript();
        echo "</td></tr>";
        echo "<tr><td colspan=2>";
        echo "<input type='radio' name='type' value='0'checked >Αλλαγή ΜΚ<br>";
        echo "<input type='radio' name='type' value='1' disabled>Αλλαγή Βαθμών<br>";
        echo "<input type='radio' name='type' value='2' disabled>Αλλαγή ΜΚ & Βαθμών<br>";
        if ($usrlvl==0)
            echo "<input type='checkbox' name='editvmk' value='1' />Τροποποίηση ΜΚ στη ΒΔ<br />";
        echo "</td></tr>";
        echo "<tr><td colspan=2><input type='submit' value='Αναζήτηση'>";
        echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
        echo "</td></tr>";
        echo "</table></form>";
        if ($idiwtikoi)
            echo "<a href='check_vmk.php'>M.K. Μονίμων Εκπ/κών</a>";
        else
            echo "<a href='check_vmk.php?id=1'>M.K. Ιδιωτικών Εκπ/κών</a>";
		
	if((int)$_POST['date'])
	{
		
		$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $mysqlconnection);
		mysql_query("SET NAMES 'greek'", $mysqlconnection);
		mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
		//$query = "SELECT * from employee";
                //$query = "SELECT * from employee WHERE status NOT IN (2,4)";
                // 07-08-2013
                //$query = "SELECT * from employee WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24)";
		//$query = "SELECT * from employee WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24) AND NOT aney";
                if ($idiwtikoi)
                    $query = "SELECT *,k.perigrafh from employee e JOIN klados k ON e.klados = k.id WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24) AND NOT aney AND thesi=5";
                else
                    $query = "SELECT *,k.perigrafh from employee e JOIN klados k ON e.klados = k.id WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24) AND NOT aney AND thesi!=5";
		$result = mysql_query($query, $mysqlconnection);
		$num=mysql_num_rows($result);
		$dt = $_POST['date'];
                $type=$_POST['type'];
                $editvmk = $_POST['editvmk'];
		echo "<br>Ημερομηνία αναζήτησης: $dt<br>";
                ob_start();
		echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
                echo "<thead><tr><th>id</th><th>ΑΜ</th><th>Ονοματεπώνυμο</th><th>Πατρώνυμο</th><th>Κλάδος</th><th>Βαθμός</th><th>ΜΚ Πριν</th><th>ΜΚ Μετά</th><th>Ημ/νία</th><th>Έτη</th><th>Πλεονάζων χρόνος</th></tr></thead><tbody>";
                $aa = 1;
                $problems = 0;
            while ($i<$num)	
            {
		$id = mysql_result($result, $i, "id");
		$name = mysql_result($result, $i, "name");
		$surname = mysql_result($result, $i, "surname");
                $am = mysql_result($result, $i, "am");
		$vathm = mysql_result($result, $i, "vathm");
		$mk = mysql_result($result, $i, "mk");
		$metdid = mysql_result($result, $i, "met_did");
		$hm_dior = mysql_result($result, $i, "hm_dior");
                $hm_anal = mysql_result($result, $i, "hm_anal");
		$proyp = mysql_result($result, $i, "proyp");
                $klados = mysql_result($result, $i, "perigrafh");
                $patrwnymo = mysql_result($result, $i, "patrwnymo");
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

                if ($metdid==1)
			$days = $days1 + 720;
		else if ($metdid==2)
			$days = $days1 + 2160;
		else if ($metdid==3)
			$days = $days1 + 2520;
		else
			$days = $days1;
                
		if ($type==1)
                    $vath = vathmos($days);
                else
                    $vath[0] = $vathm;
		$mk1 = mk_plus($days,$vath[0]);
				
		if (($type==1 || $type==2) && (strcmp($vathm,$vath[0])!=0))
		{
			$ymd = days2ymd($vath[1]);
			$v99 = "-$vath[1] day"; //may need fixing...
			$vdate = strtotime ( $v99 , $day1 );
			$vdate = date ( 'd-m-Y' , $vdate );
			
			echo "<tr><td>$id</td><td>$am</td><td><a href=\"employee.php?id=$id&op=view\">$surname $name</a></td><td>Αλλαγή βαθμού από $vathm σε <b>$vath[0]</b></td><td>$vdate</td><td>Πλεον.Χρόνος στο Βαθμό: $ymd[0] έτη, $ymd[1] μήνες, $ymd[2] ημέρες</td></tr>";
			$notvathmos=1;
                        $vathmchange += 1;
		}
		if (($type==2) && ($mk!=$mk1[0]) && (strcmp($vathm,'ΣΤ')!=0))
		{
			$ymd = days2ymd($mk1[1]);
			$v99 = "-$mk1[1] day"; //may need fixing...
			$vdate = strtotime ( $v99 , $day1 );
			$vdate = date ( 'd-m-Y' , $vdate );
                        echo "<tr><td>$id</td><td>$am</td><td><a href=\"employee.php?id=$id&op=view\">$surname $name</a></td><td>Αλλαγή ΜΚ από $mk σε <b>$mk1[0]</b>&nbsp;&nbsp;(Βαθμ.:$vath[0])</td><td>$vdate</td><td>Πλεον.Χρόνος στο ΜΚ: $ymd[0] έτη, $ymd[1] μήνες, $ymd[2] ημέρες</td></tr>";
			$notmk=1;
                        $mkchange += 1;
		}
                // allagh MK
                if (($type==0) && ($mk!=$mk1[0]) && (strcmp($vathm,$vath[0])==0) && (strcmp($vathm,'ΣΤ')!=0))
		{
                        $ymd = days2ymd($mk1[1]);
			$v99 = "-$mk1[1] day"; //may need fixing...
			$vdate = strtotime ( $v99 , $day1 );
			$vdate = date ( 'd-m-Y' , $vdate );
                        $eth = intval($days/360);
                        if ($mk1[0]=='' || $mk1[0]<$mk)
                        {
                            echo "<tr><td>$aa</td><td>$am</td><td><a href=\"employee.php?id=$id&op=view\">$surname $name</a></td><td colspan=8>Παρουσιάστηκε πρόβλημα. Παρακαλώ ελέγξτε...<small>(Βαθ: $vathm, ΜΚ: $mk, ΜΚ Νέο: $mk1[0])</small></td></tr>";
                            $problem=1;
                            $problems++;
                        }
			else
                            //echo "<tr><td>$aa</td><td><a href=\"employee.php?id=$id&op=view\">$surname $name</a></td><td>Αλλαγή ΜΚ από $mk σε <b>$mk1[0]</b>&nbsp;&nbsp;(Βαθμ.:$vath[0])</td><td>$vdate</td><td>Πλεον.Χρόνος στο ΜΚ: $ymd[0] έτη, $ymd[1] μήνες, $ymd[2] ημέρες ($days)</td></tr>";
                            //echo "<thead><tr><th>id</th><th>Ονοματεπώνυμο</th><th>Κλάδος</th><th>Βαθμός</th><th>ΜΚ Πριν</th><th>ΜΚ Μετά</th><th>Ημ/νία</th><th>Έτη Υπηρεσίας</th><th>Πλεονάζων χρόνος</th></tr></thead><tbody>";
                            echo "<tr><td>$aa</td><td>$am</td><td><a href=\"employee.php?id=$id&op=view\">$surname $name</a></td><td>$patrwnymo</td><td>$klados</td><td>$vath[0]</td><td>$mk</td><td>$mk1[0]</td><td>$vdate</td><td>$eth</td><td>$ymd[0] έτη, $ymd[1] μήνες, $ymd[2] ημέρες ($days)</td></tr>";
                        // for word
                        $row = array($am,$surname,$name,$patrwnymo,$klados,$vath[0],$mk1[0],$vdate,$eth);
                        $emp[] = $row;
                        // end of word
			$notmk=1;
                        $mkchange += 1;
                        $aa++;
                        if ($editvmk && !$problem)
                        {
                            $mkdate = date ('Y-m-d', strtotime($vdate));
                            $query1 = "UPDATE employee SET mk='$mk1[0]', hm_mk='$mkdate' WHERE ID=$id";
                            //echo "<br>$query1";
                            mysql_query($query1, $mysqlconnection);                            
                            $updates+=1;
                        }
		}
		
		$i++;
		$notmk=$notvathmos=0;
                $problem=0;
            }
		echo "</tbody></table>";
                echo "<br>";
                if ($vathmchange)
                    echo "<br>$vathmchange αλλαγές βαθμών";
                if ($mkchange){
                    if ($problems){
                        $mkchange -= $problems;
                        echo "<br>$mkchange αλλαγές ΜΚ";
                    }
                    else
                        echo "<br>$mkchange αλλαγές ΜΚ";
		}
                if ($problems)
                    echo "<br>$problems προβλήματα";
                if ($updates)
                    echo "<br>$updates τροποποιήσεις ΜΚ στη ΒΔ";
		mysql_close();
                
                $page = ob_get_contents(); 
		ob_end_flush();
			
		echo "<form action='../tools/2excel.php' method='post'>";
		echo "<input type='hidden' name = 'data' value='".$page."'>";
                echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
                echo "</form>";
                echo "&nbsp;&nbsp;&nbsp;";
                // serialize & write to temp file
                $emp_ser = serialize($emp);
                $fname = "word/tmp.txt";
                file_put_contents($fname, $emp_ser);
                
                echo "<form id='wordfrm' name='wordfrm' action='' method='POST'>";
                if ($problems)
                    echo "<INPUT name='btnSubmit' TYPE='submit' VALUE='Εκτύπωση απόφασης Μ.Κ.' disabled>";
                else
                    echo "<INPUT name='btnSubmit' TYPE='submit' VALUE='Εκτύπωση απόφασης Μ.Κ.'>";
                echo "</form>";
	}
        // create word document
        if (isset($_POST['btnSubmit']))
        {
            require_once '../tools/PHPWord.php';
            $PHPWord = new PHPWord();
            
            // set max execution time 
            set_time_limit (180);
            
            // read from file
            $fname = "word/tmp.txt";
            $data = unserialize(file_get_contents($fname));
            
            $document = $PHPWord->loadTemplate('word/apof/tmpl_apof_mk.docx');
                                   
            foreach ($data as $ar)
            {
                $data1 = mb_convert_encoding($ar[1], "utf-8", "iso-8859-7");
                $data2 = mb_convert_encoding($ar[2], "utf-8", "iso-8859-7");
                $data = $data1." ".$data2;
                $onmo[] = $data;
                
                $data = mb_convert_encoding($ar[3], "utf-8", "iso-8859-7");
                $patr[] = $data;

                $data = mb_convert_encoding($ar[4], "utf-8", "iso-8859-7");
                $klados[] = $data;

                $data = mb_convert_encoding($ar[5], "utf-8", "iso-8859-7");
                $vathm[] = $data;

                $mk[] = $ar[6];
                $hmnia[] = $ar[7];
                $eth[] = $ar[8];
            }
            
            $mydata = array('onmo' => $onmo,'patr' => $patr,'klados' => $klados,'vathm' => $vathm,'mk'=> $mk,'hmnia' => $hmnia, 'eth' => $eth);
            $document->cloneRow2('TBL1', $mydata);
                        
            $data = mb_convert_encoding($head_title, "utf-8", "iso-8859-7");
            $document->setValue("headtitle", $data);
            $data = mb_convert_encoding($head_name, "utf-8", "iso-8859-7");
            $document->setValue("headname", $data);

            $output1 = "word/apof/apof_mk_".$_SESSION['userid'].".docx";
            $document->save($output1);
            echo "<html>";
            echo "<p><a href=$output1>Ανοιγμα εγγράφου</a></p>";
        }
?>
</html>