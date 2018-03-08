<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once "../config.php";
	require_once "../tools/functions.php";
?>	
    <html>
    <head>      
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
<?php   
    include("../tools/class.login.php");
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false){
        header("Location: ../tools/login_check.php");
    }
    $usrlvl = $_SESSION['userlevel'];
    if ($usrlvl)
        die('Δεν έχετε τα προνόμια να εκτελέσετε αυτήν την ενέργεια...');

    if (empty($_POST)){
        echo "<h2>Επιδιόρθωση διδακτικού ωραρίου εκπ/κών</h2>";
        echo "<h4>ΠΡΟΣΟΧΗ: Η ακόλουθη ενέργεια προκαλεί μεταβολές στη Βάση Δεδομένων.<br>Προχωρήστε μόνο αν είστε σίγουροι...</h4>";
        echo "<form action='' method='POST' autocomplete='off'>";
        echo "<input type='submit' value='Μεταβολή'></td></tr>";
        echo "<input type='hidden' name = 'placeholder' value='1'>";
        echo "</form>";
    }
    else {
        // set max execution time 
        set_time_limit (180);
            
		$updates = $fails = 0;
		$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $mysqlconnection);
		mysql_query("SET NAMES 'greek'", $mysqlconnection);
		mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
        $query = "select e.surname,e.name,e.wres, y.hours, y.id from employee e join yphrethsh y on e.id = y.emp_id where sxol_etos = $sxol_etos";
		$result = mysql_query($query, $mysqlconnection);
		$num=mysql_num_rows($result);
                		
        $synolo = $num;
                
        while ($i<$num)	
        {
		    $name = mysql_result($result, $i, "name");
		    $surname = mysql_result($result, $i, "surname");
            $ypoxr_wres = mysql_result($result, $i, "wres");
            
            $yphr_hours = mysql_result($result, $i, "hours");
            $yphr_id = mysql_result($result, $i, "id");
            
            if ($ypoxr_wres && ($yphr_hours > $ypoxr_wres))
            {
                $updates++;
                $qry = "update yphrethsh set hours=$ypoxr_wres where id=$yphr_id";
                $res = mysql_query($qry, $mysqlconnection);
                if (!res)
                {
                    die ('Error: '.mysql_error ());
                    $fails++;
                }
                echo "$surname\tYpoxr:$ypoxr_wres,Yphr:$yphr_hours ".$qry."<br>";
            }   
		    $i++;
        }
        echo "<br>";       
		mysql_close();

        echo "Πραγματοποιήθηκαν $updates ενημερώσεις στη Β.Δ. (σε πλήθος $synolo υπαλλήλων)";
        if ($fails)
            echo "<br>$fails αποτυχίες.";
    }
?>
<br><br>
<a href="../index.php">Επιστροφή</a>
</html>