<?php
	header('Content-type: text/html; charset=utf-8'); 
	require_once"../config.php";
	require_once"../include/functions.php";
  require('../tools/calendar/tc_calendar.php');
  
  // update_wres: Updates wres of all employees
  include("../tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false){
      header("Location: ../tools/login.php");
  }
  $usrlvl = $_SESSION['userlevel'];
?>	
  <html>
  <head>    
        <title>Αλλαγή ωραριου εκπ/κων</title>
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
        <script type="text/javascript">   
            $(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		});
        </script>
        <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
        </head>
        <body>
        <?php include('../etc/menu.php'); ?>
        <h2>Αλλαγή ωραριου εκπ/κων</h2>
<?php        
  echo "<table class=\"imagetable stable\" border='1'>";
  echo "<form action='' method='POST' autocomplete='off'>";
  echo "<tr><td>Ημερομηνία αναζήτησης:</td><td>";
  $myCalendar = new tc_calendar("date", true);
  $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
  if((int)$_POST['date']) {
    $myCalendar->setDate(date('d',strtotime($_POST['date'])),date('m',strtotime($_POST['date'])),date('Y',strtotime($_POST['date'])));
  } else {
    $myCalendar->setDate(31,12,date('Y'));
  }
  
  $myCalendar->setPath("../tools/calendar/");
  $myCalendar->setYearInterval(1970, 2030);
  $myCalendar->dateAllow("1970-01-01", date("2030-01-01"));
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
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
		$query = "SELECT e.*,k.perigrafh as kname from employee e JOIN klados k ON k.id = e.klados WHERE klados <> 1";
    // $query = "SELECT * from employee WHERE status NOT IN (2,4)";
    // 07-08-2013
    // $query = "SELECT * from employee WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24)";
		// $query = "SELECT * from employee WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24) AND NOT aney";
    //if ($idiwtikoi)
    //    $query = "SELECT *,k.perigrafh from employee e JOIN klados k ON e.klados = k.id WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24) AND NOT aney AND thesi=5";
    //else
    //    $query = "SELECT *,k.perigrafh from employee e JOIN klados k ON e.klados = k.id WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24) AND NOT aney AND thesi!=5";
		$result = mysqli_query($mysqlconnection, $query);
		$num=mysqli_num_rows($result);
		$dt = $_POST['date'];
    $type=$_POST['type'];
    $editvmk = $_POST['editvmk'];
		echo "<br>Ημερομηνία αναζήτησης: $dt<br>";
    ob_start();
		echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
    echo "<thead><tr><th>ΑΜ</th><th>Ονοματεπώνυμο</th><th>Κλάδος</th><th>Ώρες</th><th>Ημέρες</th><th>Υ - Μ - D</th></tr></thead><tbody>";
    $aa = 1;
    $problems = 0;
    while ($i<$num)	
    {
      $id = mysqli_result($result, $i, "id");
      $name = mysqli_result($result, $i, "name");
      $surname = mysqli_result($result, $i, "surname");
      $am = mysqli_result($result, $i, "am");
                
      $old_wres = mysqli_result($result, $i, "wres");

      $vathm = mysqli_result($result, $i, "vathm");
		  $mk = mysqli_result($result, $i, "mk");
		  $metdid = mysqli_result($result, $i, "met_did");
		  $hm_dior = mysqli_result($result, $i, "hm_dior");
      $hm_anal = mysqli_result($result, $i, "hm_anal");
		  $proyp = mysqli_result($result, $i, "proyp");
      $aney_xr = mysqli_result($result, $i, "aney_xr");
      $kname = mysqli_result($result, $i, "kname");
                
      // 29-10-2012 - Skip employees from elsewhere (organikh = 3 (allo pyspe) or 5 (allo pysde)).
      $organ = mysqli_result($result, $i, "sx_organikhs");
      $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ',$mysqlconnection);
      $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ',$mysqlconnection);
      if ($organ == $allo_pyspe || $organ == $allo_pyspe)
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
				echo "<tr><td>$am</td><td><a href=\"employee.php?id=$id&op=view\">$surname $name</a></td><td>$kname</td><td>$wres</td><td>$days</td><td>$ymd[0] y, $ymd[1] m, $ymd[2] d</tr>";
        $need_update++;
			}
      // allagh wrwn
      if ($editvmk && $wres <> $old_wres)
      {
          $mkdate = date ('Y-m-d', strtotime($vdate));
          $query1 = "UPDATE employee SET wres=$wres WHERE ID=$id";
          mysqli_query($mysqlconnection, $query1);                          
          $updates+=1;
      }
		
		  $i++;
    }
		echo "</tbody></table>";
    echo "<br>";
                
		mysqli_close($mysqlconnection);
                
    if ($editvmk)
        echo "Πραγματοποιήθηκαν $updates ενημερώσεις στη Β.Δ.";
    else
        echo "Απαιτούνται $need_update ενημερώσεις στη Β.Δ.";
    $page = ob_get_contents(); 
		ob_end_flush();
			
		echo "<form action='../tools/2excel.php' method='post'>";
		echo "<input type='hidden' name = 'data' value='".$page."'>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
    echo "</form>";
	}
?>
<br>
<input type='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
</body>
</html>