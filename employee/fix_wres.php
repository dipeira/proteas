<?php
	header('Content-type: text/html; charset=utf-8'); 
	require_once "../config.php";
	require_once "../include/functions.php";
?>	
    <html>
    <head>      
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
<?php   
    include("../tools/class.login.php");
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false){
        header("Location: ../tools/login.php");
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
    
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

    $query = "select e.surname,e.name,e.wres, y.hours, y.id from employee e join yphrethsh y on e.id = y.emp_id where sxol_etos = $sxol_etos";
		$result = mysqli_query($mysqlconnection, $query);
		$num=mysqli_num_rows($result);
                		
        $synolo = $num;
                
        while ($i<$num)	
        {
		    $name = mysqli_result($result, $i, "name");
		    $surname = mysqli_result($result, $i, "surname");
            $ypoxr_wres = mysqli_result($result, $i, "wres");
            
            $yphr_hours = mysqli_result($result, $i, "hours");
            $yphr_id = mysqli_result($result, $i, "id");
            
            if ($ypoxr_wres && ($yphr_hours > $ypoxr_wres))
            {
                $updates++;
                $qry = "update yphrethsh set hours=$ypoxr_wres where id=$yphr_id";
                $res = mysqli_query($mysqlconnection, $qry);
                if (!res)
                {
                    die ('Error: '.mysqli_error ($mysqlconnection));
                    $fails++;
                }
                echo "$surname\tYpoxr:$ypoxr_wres,Yphr:$yphr_hours ".$qry."<br>";
            }   
		    $i++;
        }
        echo "<br>";       
        mysqli_close($mysqlconnection);

        echo "Πραγματοποιήθηκαν $updates ενημερώσεις στη Β.Δ. (σε πλήθος $synolo υπαλλήλων)";
        if ($fails)
            echo "<br>$fails αποτυχίες.";
    }
?>
<br><br>
<a href="../index.php">Επιστροφή</a>
</html>