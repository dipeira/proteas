<?php
	header('Content-type: text/html; charset=utf-8'); 
	require_once"../config.php";
	require_once"../include/functions.php";
	require('../tools/calendar/tc_calendar.php');  
         
?>	
  <html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Αλλαγές Μ.Κ.';
    require '../etc/head.php'; 
    ?>
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="../js/jquery.js"></script>
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
	// check_vmk: Checks (& updates if asked) MK of all employee records for the date provided
    include("../tools/class.login.php");
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false){
        header("Location: ../tools/login.php");
    }
    $usrlvl = $_SESSION['userlevel'];

    echo "<h2>M.K. Μόνιμων Εκπ/κών</h2>";
    echo "<table class=\"imagetable stable\" border='1'>";
    echo "<form action='' method='POST' autocomplete='off'>";
    echo "<tr><td>Ημερομηνία αναζήτησης:</td><td>";
    $myCalendar = new tc_calendar("date", true);
    $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
    if((int)$_POST['date'])
            $myCalendar->setDate(date('d',strtotime($_POST['date'])),date('m',strtotime($_POST['date'])),date('Y',strtotime($_POST['date'])));
    $myCalendar->setPath("../tools/calendar/");        
    $myCalendar->setYearInterval(1970, 2030);
    $myCalendar->dateAllow("1970-01-01", date("2030-01-01"));
    $myCalendar->setAlignment("left", "bottom");
    $myCalendar->writeScript();
    echo "</td></tr>";
    echo "<tr><td colspan=2>";
    // echo "<input type='radio' name='type' value='0'checked >Αλλαγή ΜΚ<br>";

    if ($usrlvl==0)
        echo "<input type='checkbox' name='editvmk' value='1' />Τροποποίηση ΜΚ στη ΒΔ<br />";
    echo "</td></tr>";
    echo "<tr><td colspan=2><input type='submit' value='Αναζήτηση'>";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "</td></tr>";
    echo "</table></form>";
		
	if((int)$_POST['date'])
	{
        $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
        mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
        mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

        $query = "SELECT *,k.perigrafh,e.id as id from employee e JOIN klados k ON e.klados = k.id WHERE status NOT IN (2,4) AND klados NOT IN (22,23,24) AND NOT aney AND thesi!=5";
		$result = mysqli_query($mysqlconnection, $query);
		$num=mysqli_num_rows($result);
		$dt = $_POST['date'];
        $editvmk = $_POST['editvmk'];
		echo "<br>Ημερομηνία αναζήτησης: $dt<br>";
        ob_start();
		echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
        echo "<thead><tr><th>ΑΜ</th><th>Ονοματεπώνυμο</th><th>Πατρώνυμο</th><th>Κλάδος</th><th>ΜΚ Πριν</th><th>ΜΚ Μετά</th><th>Ημ/νία</th><th>Χρόνος για ΜΚ</th></tr></thead><tbody>";
        $aa = 1;
        $problems = 0;
        while ($i<$num)	
        {
            $id = mysqli_result($result, $i, "id");
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $am = mysqli_result($result, $i, "am");
            $mk = mysqli_result($result, $i, "mk");
            $metdid = mysqli_result($result, $i, "met_did");
            $hm_dior = mysqli_result($result, $i, "hm_dior");
            $hm_anal = mysqli_result($result, $i, "hm_anal");
            $proyp = mysqli_result($result, $i, "proyp");
            $klados = mysqli_result($result, $i, "perigrafh");
            $patrwnymo = mysqli_result($result, $i, "patrwnymo");
            $aney_xr = mysqli_result($result, $i, "aney_xr");
        
            // 29-10-2012 - Skip employees from elsewhere (organikh = allo pyspe or allo pysde).
            $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ',$mysqlconnection);
            $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ',$mysqlconnection);
            $organ = mysqli_result($result, $i, "sx_organikhs");
            if ($organ == $allo_pysde || $organ == $allo_pyspe)
            {
                $i++;
                continue;
            }
		
            $mk1 = get_mk($id, $mysqlconnection, $_POST['date']);  
            
            // allagh MK
            if ( $mk!=$mk1['mk'] )
            {
                $ymd = $mk1['ymd'];
                
                $mkdate = $mk1['hm_mk'];
                $eth = intval($days/360);
                if ($mk1['mk']=='' || $mk1['mk']<$mk)
                {
                    echo "<tr><td>$am</td><td><a href=\"employee.php?id=$id&op=view\">$surname $name</a></td><td colspan=6>Παρουσιάστηκε πρόβλημα. Παρακαλώ ελέγξτε...<small>(ΜΚ: $mk, ΜΚ Νέο: ".$mk1['mk'].")</small></td></tr>";
                    $problem=1;
                    $problems++;
                }
                else {
                    echo "<tr><td>$am</td><td><a href=\"employee.php?id=$id&op=view\">$surname $name</a></td><td>$patrwnymo</td><td>$klados</td><td>$mk</td><td>".$mk1['mk']."</td><td>$mkdate</td><td>$ymd[0] έτη, $ymd[1] μήνες, $ymd[2] ημέρες</td></tr>";
                }
                // for word
                $row = array($am,$surname,$name,$patrwnymo,$klados,$mk1['mk'],$mkdate,$eth);
                $emp[] = $row;
                // end of word
                $notmk=1;
                $mkchange += 1;
                $aa++;
                if ($editvmk && !$problem)
                {
                    $mkdate = date ('Y-m-d', strtotime($mk1['hm_mk']));
                    $query1 = "UPDATE employee SET mk='".$mk1['mk']."', hm_mk='$mkdate' WHERE ID=$id";
                    //echo "<br>$query1";
                    mysqli_query($mysqlconnection, $query1);                         
                    $updates+=1;
                }
		    }
		
		    $i++;
		    $notmk=0;
            $problem=0;
        }
		echo "</tbody></table>";
        echo "<br>";

        if ($mkchange) {
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
        mysqli_close($mysqlconnection);
                
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
                
        // echo "<form id='wordfrm' name='wordfrm' action='' method='POST'>";
        // if ($problems)
        //     echo "<INPUT name='btnSubmit' TYPE='submit' VALUE='Εκτύπωση απόφασης Μ.Κ.' disabled>";
        // else
        //     echo "<INPUT name='btnSubmit' TYPE='submit' VALUE='Εκτύπωση απόφασης Μ.Κ.'>";
        // echo "</form>";
	}
        // // create word document
        // if (isset($_POST['btnSubmit']))
        // {
        //     require_once '../vendor/phpoffice/phpword/Classes/PHPWord.php';
        //     $PHPWord = new PHPWord();
            
        //     // set max execution time 
        //     set_time_limit (180);
            
        //     // read from file
        //     $fname = "word/tmp.txt";
        //     $data = unserialize(file_get_contents($fname));
            
        //     $document = $PHPWord->loadTemplate('word/tmpl_apof/tmpl_apof_mk.docx');
                                   
        //     foreach ($data as $ar)
        //     {
        //         $data = $ar[1]." ".$ar[2];
        //         $onmo[] = $data;
                
        //         $patr[] = $data;

        //         $klados[] = $data;

        //         $mk[] = $ar[6];
        //         $hmnia[] = $ar[7];
        //         $eth[] = $ar[8];
        //     }
            
        //     $mydata = array('onmo' => $onmo,'patr' => $patr,'klados' => $klados,'mk'=> $mk,'hmnia' => $hmnia, 'eth' => $eth);
        //     $document->cloneRow2('TBL1', $mydata);
                        
        //     $document->setValue("headtitle", getParam('head_title', $mysqlconnection));
        //     $document->setValue("headname", getParam('head_name', $mysqlconnection));

        //     $output1 = "word/apof_mk_".$_SESSION['userid'].".docx";
        //     $document->save($output1);
        //     echo "<html>";
        //     echo "<p><a href=$output1>Ανοιγμα εγγράφου</a></p>";
        // }
?>
</html>