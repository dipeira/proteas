<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once"config.php";
	require_once"functions.php";
        $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
        mysql_select_db($db_name, $mysqlconnection);
?>	
  <html>
      <head><title>Τέλος Διδακτικού/Σχολικού έτους - Ενέργειες</title></head>
  <body>
        
<?php        
	// end_of_year: Includes end-of-year actions: Deletes ektakto personnel, returns personnel from other pispe etc...

                include("tools/class.login.php");
                $log = new logmein();
                if($log->logincheck($_SESSION['loggedin']) == false){
                    header("Location: tools/login_check.php");
                }
                $usrlvl = $_SESSION['userlevel'];
                
		$sxol_etos = getParam('sxol_etos', $mysqlconnection);
                $tbl_bkp_mon = "employee_bkp_$sxol_etos";
                echo "<html><head><h2>Τέλος Διδακτικού/Σχολικού έτους - Ενέργειες</h2></head><body>";
                echo "<h3><blink>Προσοχή: Μη αναστρέψιμες ενέργειες</blink></h3>";
                echo "<tr><td>Προτείνεται η εκτέλεση με τη σειρά του αριθμού που αναγράφεται.<br><br>";
		echo "<table class=\"imagetable\" border='1'>";
		echo "<form action='' method='POST' autocomplete='off'>";
                echo "<tr><td>Ενέργεια</td><td>";
                echo "<input type='radio' name='type' value='8'>1. Αλλαγή σχολικού έτους (τρέχον σχολικό έτος: $sxol_etos)<br>";
                echo "<input type='radio' name='type' value='2'>2. Διαγραφή Αναπληρωτών / Ωρομισθίων από βάση δεδομένων<br>";
                echo "<input type='radio' name='type' value='6' disabled>3. Επιστροφή αποσπασμένων εκπαιδευτικών του ΠΥΣΠΕ Ηρακλείου στην οργανική τους<br>";
                echo "<input type='radio' name='type' value='3' disabled>4. Επιστροφή αποσπασμένων εκπαιδευτικών από άλλα ΠΥΣΠΕ<br>";
                echo "<input type='radio' name='type' value='5' disabled>5. Διαγραφή αποσπασμένων από άλλα ΠΥΣΠΕ / ΠΥΣΔΕ από βάση δεδομένων<br>";
                echo "<br>";
                echo "<input type='radio' name='type' value='4'>Επιστροφή αποσπασμένων εκπαιδευτικών από φορείς (για 31/08)<br>";
                echo "<br>";
                echo "<input type='radio' name='type' value='1'>Εκτύπωση βεβαιώσεων Αναπληρωτών Κρατικού Προυπολογισμού<br>";
                echo "<input type='radio' name='type' value='7'>Εκτύπωση βεβαιώσεων Αναπληρωτών ΕΣΠΑ<br>";
                echo "</td></tr>";
                if ($usrlvl > 0)
                    echo "<tr><td colspan=2><input type='submit' value='Πραγματοποίηση' disabled></td></tr>";
                else
                    echo "<tr><td colspan=2><input type='submit' value='Πραγματοποίηση'></td></tr>";
		echo "</form></table>";
                if ($usrlvl > 0)
                {
                    echo "<br><br><h3>Δεν έχετε δικαίωμα για την πραγματοποίηση αυτών των ενεργειών. Επικοινωνήστε με το διαχειριστή σας.</h3>";
                    echo "<br><a href=\"index.php\">Επιστροφή</a>";
                    mysql_close();
                    exit;
                }
                else
                    echo "<br><a href=\"index.php\">Επιστροφή</a>";
                // Allagh sxolikoy etoys
                if (isset($_POST['sxoletos']))
                {
                    do2yphr($mysqlconnection, 0);
                    setParam('sxol_etos', $_POST['sxoletos'], $mysqlconnection);
                    // more...
                    echo "<h3>Επιστροφή αποσπασμένων εκπαιδευτικών του ΠΥΣΠΕ Ηρακλείου στην οργανική τους</h3>";
                    $query = "CREATE TABLE $tbl_bkp_mon SELECT * FROM employee";
                    $result = mysql_query($query, $mysqlconnection);
                    $query = "DROP TABLE employee_moved";
                    $result = mysql_query($query, $mysqlconnection);
                    // 389: apospasi se forea, 397: sxol. symvoulos, 399: Apospash ekswteriko
                    // thesi 2: d/nths, 4: dioikhtikos
                    $query = "CREATE TABLE employee_moved SELECT * FROM employee WHERE sx_yphrethshs NOT IN (389,397,399) AND thesi NOT IN (2,4)";
                    $result = mysql_query($query, $mysqlconnection);
                    $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs NOT IN (389,397,399) AND thesi NOT IN (2,4)";
                    $result = mysql_query($query, $mysqlconnection);
                    $num = mysql_affected_rows();
                    //echo $query;
                    if ($result)
                        echo "Επιτυχής μεταβολή $num εγγραφών.";
                    else 
                        echo "Πρόβλημα στη διαγραφή...";
                    
                    echo "<h3>Επιστροφή αποσπασμένων εκπαιδευτικών από άλλα ΠΥΣΠΕ</h3>";
                    $query = "INSERT INTO employee_moved SELECT * FROM employee WHERE sx_yphrethshs = 388";
                    $result = mysql_query($query, $mysqlconnection);
                    $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs = 388";
                    $result = mysql_query($query, $mysqlconnection);
                    $num = mysql_affected_rows();
                    //echo $query;
                    if ($result)
                        echo "Επιτυχής μεταβολή $num εγγραφών.";
                    else 
                        echo "Πρόβλημα στη διαγραφή...";
                    
//                    echo "<h3>Διαγραφή αποσπασμένων από άλλα ΠΥΣΠΕ / ΠΥΣΔΕ από βάση δεδομένων</h3>";
//                    $query = "DROP TABLE employee_deleted";
//                    $result = mysql_query($query, $mysqlconnection);
//                    // 388: ¶λλο ΠΥΣΠΕ, 394: ¶λλο ΠΥΣΔΕ
//                    $query = "CREATE TABLE employee_deleted SELECT * FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397,389)";
//                    $result = mysql_query($query, $mysqlconnection);
//                    $query = "DELETE FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397,389)";
//                    $result = mysql_query($query, $mysqlconnection);
//                    $num = mysql_affected_rows();
//                    //echo $query;
//                    if ($result)
//                        echo "Επιτυχής μεταβολή $num εγγραφών.";
//                    else 
//                        echo "Πρόβλημα στη διαγραφή...";
                    //
                    do2yphr($mysqlconnection, 0);
                    echo "<br><br>H διαδικασία ολοκληρώθηκε...";
                }
                // vevaiwseis proyphresias anaplhrwtwn
                if($_POST['type'] == 1 || $_POST['type'] == 7)
                {
                        if ($_POST['type'] == 1)
                            $kratikoy = 1;
                        mysql_query("SET NAMES 'greek'", $mysqlconnection);
                        mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
                        // kratikoy or ESPA
                        if ($kratikoy)
                            $query = "SELECT * from ektaktoi WHERE type IN (2,4,5)";
                        else
                            $query = "SELECT * from ektaktoi WHERE type = 3";

                        $result = mysql_query($query, $mysqlconnection);
                        $num=mysql_numrows($result);

                        echo "<h3>Εκτύπωση βεβαιώσεων Αναπληρωτών ";
                        if ($kratikoy)
                            echo "κρατικού προύπολογισμού";
                        else
                            echo "ΕΣΠΑ";
                        echo "</h3>";
                        echo "<form name='anaplfrm' action=\"vev_yphr_anapl.php\" method='POST'>";

                        $i=0;
                        while ($i < $num)
                        {
                            $name = mysql_result($result, $i, "name");
                            $surname = mysql_result($result, $i, "surname");
                            $patrwnymo = mysql_result($result, $i, "patrwnymo");
                            $klados = mysql_result($result, $i, "klados");
                            $sx_yphrethshs = mysql_result($result, $i, "sx_yphrethshs");
                            $ya = mysql_result($result, $i, "ya");
                            $apof = mysql_result($result, $i, "apofasi");
                            $hmpros = mysql_result($result, $i, "hm_anal");
                            $metakinhsh = mysql_result($result, $i, "metakinhsh");
                            $last_afm = substr (mysql_result($result, $i, "afm"), -3);
							// find espa type & post prefix accordingly - worked only for 2013-14. Proteas now has praxi to distinguish workers.
							$prefix = '';
							if (!$kratikoy)
							{
								$comments = mysql_result($result, $i, "comments");
								if (strpos($comments,'ΕΑΕΠ') !== false || strpos($comments,'Ε.Α.Ε.Π.') !== false)
									$prefix = "EAEP_";
								elseif (strpos($comments,'ΠΑΡΑΛΛΗΛΗ') !== false || strpos($comments,'Παράλληλη') !== false)
									$prefix = "PARAL_";
								elseif (strpos($comments,'Εξατομ.') !== false || strpos($comments,'ΕΞΑΤΟΜΙΚΕΥΜΕΝΗ') !== false)
									$prefix = "EKSATOM_";
								elseif (strpos($comments,'ΟΛΟΗΜΕΡΟ') !== false || strpos($comments,'Ολοήμερο') !== false)
									$prefix = "OLOHM_";
								elseif (strpos($comments,'ΝΈΟ ΣΧΟΛΕΙΟ') !== false)
									$prefix = "NEO_";
								else
									$prefix = '';
								echo "<input type='hidden' name=arr[$i][prefix] value=$prefix>";
							}
                            echo "<input type='hidden' name=arr[$i][name] value=$name>";
                            echo "<input type='hidden' name=arr[$i][surname] value=$surname>";
                            echo "<input type='hidden' name=arr[$i][patrwnymo] value=$patrwnymo>";
                            echo "<input type='hidden' name=arr[$i][klados] value=$klados>";
                            echo "<input type='hidden' name=arr[$i][sx_yphrethshs] value=$sx_yphrethshs>";
                            echo "<input type='hidden' name=arr[$i][ya] value=$ya>";
                            echo "<input type='hidden' name=arr[$i][apof] value=$apof>";
                            echo "<input type='hidden' name=arr[$i][hmpros] value=$hmpros>";
                            echo "<input type='hidden' name=arr[$i][metakinhsh] value=\"$metakinhsh\">";
                            echo "<input type='hidden' name=arr[$i][last_afm] value=\"$last_afm\">";
                            $i++;
                        }

                        echo "<input type='hidden' name='kratikoy' value=$kratikoy>";
                        echo "<input type='hidden' name='plithos' value=$num>";
                        echo "<input type='submit' VALUE='Υποβολή αιτήματος'>"; 
                        echo "</form>";
                }
                elseif ($_POST['type'] == 8)
                {
                        echo "<h3>Αλλαγή Σχολικού έτους</h3>";
                        echo "Τρέχον σχολικό έτος: $sxol_etos<br>";
                        echo "Δώστε νέο σχολικό έτος (π.χ. για το σχολ.έτος 2014-15 εισάγετε <strong>201415</strong><br>";
                        echo "<form action='' method='POST'>";
                        echo "<input type='text' name='sxoletos'>";
                        echo "<input type='submit' value='Υποβολή'>";
                        echo "</form>";
                        echo "<small>ΣΗΜ: Η διαδικασία διαρκεί αρκετή ώρα. Υπομονή...</small>";
                }
                elseif ($_POST['type'] == 2)
                {
                        echo "<h3>Διαγραφή Αναπληρωτών / Ωρομισθίων από βάση δεδομένων</h3>";
                        // check if not empty
                        $query = "select id from ektaktoi";
                        $result = mysql_query($query, $mysqlconnection);
                        if (!mysql_num_rows($result))
                            exit('O πίνακας έκτακτου προσωπικού είναι κενός...');
                        //
                        //$query = "DROP TABLE ektaktoi_bkp";
                        //$result = mysql_query($query, $mysqlconnection);
                        $tbl_ekt = "ektaktoi_$sxol_etos";
                        $query = "CREATE TABLE $tbl_ekt SELECT * FROM ektaktoi";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "TRUNCATE table ektaktoi";
                        $result = mysql_query($query, $mysqlconnection);
                        //echo $query;
                        if ($result)
                            echo "Επιτυχής Διαγραφή. <br><small>Οι εκπ/κοί μεταφέρθηκαν στον πίνακα $tbl_ekt</small>";
                        else 
                            echo "Πρόβλημα στη διαγραφή...";
                }
                // epistrofh ekp/kwn Hrakleioy sthn organikh toys
                elseif ($_POST['type'] == 6)
                {
                        echo "<h3>Επιστροφή αποσπασμένων εκπαιδευτικών του ΠΥΣΠΕ Ηρακλείου στην οργανική τους</h3>";
                        // check...
                        $query = "SELECT * FROM employee WHERE sx_yphrethshs NOT IN (389,397,399) AND sx_yphrethshs != sx_organikhs AND thesi NOT IN (2,4)";
                        $result = mysql_query($query, $mysqlconnection);
                        if (!mysql_num_rows($result))
                            exit('Δεν υπάρχουν εκπαιδευτικοί γι\'αυτή την ενέργεια...');
                        //
                        //$query = "DROP TABLE employee_bkp";
                        //$result = mysql_query($query, $mysqlconnection);
                        $query = "CREATE TABLE $tbl_bkp_mon SELECT * FROM employee";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "DROP TABLE employee_moved";
                        $result = mysql_query($query, $mysqlconnection);
                        // 389: apospasi se forea, 397: sxol. symvoulos, 399: Apospash ekswteriko
                        // thesi 2: d/nths, 4: dioikhtikos
                        $query = "CREATE TABLE employee_moved SELECT * FROM employee WHERE sx_yphrethshs NOT IN (389,397,399) AND thesi NOT IN (2,4)";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs NOT IN (389,397,399) AND thesi NOT IN (2,4)";
                        $result = mysql_query($query, $mysqlconnection);
                        $num = mysql_affected_rows();
                        //echo $query;
                        if ($result)
                            echo "Επιτυχής μεταβολή $num εγγραφών.";
                        else 
                            echo "Πρόβλημα στη διαγραφή...";
                }
                elseif ($_POST['type'] == 3)
                {
                        echo "<h3>Επιστροφή αποσπασμένων εκπαιδευτικών από άλλα ΠΥΣΠΕ</h3>";
                        // check...
                        $query = "SELECT * FROM employee WHERE sx_yphrethshs = 388 AND sx_organikhs != 388";
                        $result = mysql_query($query, $mysqlconnection);
                        if (!mysql_num_rows($result))
                            exit('Δεν υπάρχουν εκπαιδευτικοί γι\'αυτή την ενέργεια...');
                        //
                        //$query = "DROP TABLE employee_bkp";
                        //$result = mysql_query($query, $mysqlconnection);
                        //$query = "CREATE TABLE employee_bkp SELECT * FROM employee";
                        //$result = mysql_query($query, $mysqlconnection);
                        $query = "INSERT INTO employee_moved SELECT * FROM employee WHERE sx_yphrethshs = 388";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs = 388";
                        $result = mysql_query($query, $mysqlconnection);
                        $num = mysql_affected_rows();
                        //echo $query;
                        if ($result)
                            echo "Επιτυχής μεταβολή $num εγγραφών.";
                        else 
                            echo "Πρόβλημα στη διαγραφή...";
                }
                // diagrafh ekpkwn apo alla pyspe/pysde
                elseif ($_POST['type'] == 5)
                {
                        echo "<h3>Διαγραφή αποσπασμένων από άλλα ΠΥΣΠΕ / ΠΥΣΔΕ από βάση δεδομένων</h3>";
                        // check...
                        $query = "SELECT * FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397)";
                        $result = mysql_query($query, $mysqlconnection);
                        if (!mysql_num_rows($result))
                            exit('Δεν υπάρχουν εκπαιδευτικοί γι\' αυτή την ενέργεια...');
                        //
                        //$query = "DROP TABLE employee_bkp";
                        //$result = mysql_query($query, $mysqlconnection);
                        //$query = "CREATE TABLE employee_bkp SELECT * FROM employee";
                        //$result = mysql_query($query, $mysqlconnection);
                        $query = "DROP TABLE employee_deleted";
                        $result = mysql_query($query, $mysqlconnection);
                        // 388: ¶λλο ΠΥΣΠΕ, 394: ¶λλο ΠΥΣΔΕ
                        $query = "CREATE TABLE employee_deleted SELECT * FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397)";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "DELETE FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397)";
                        $result = mysql_query($query, $mysqlconnection);
                        $num = mysql_affected_rows();
                        //echo $query;
                        if ($result)
                            echo "Επιτυχής μεταβολή $num εγγραφών.";
                        else 
                            echo "Πρόβλημα στη διαγραφή...";
                }
                elseif ($_POST['type'] == 4)
                {
                        echo "<h3>Επιστροφή αποσπασμένων εκπαιδευτικών από φορείς (για 31-08)</h3>";
                        // check...
                        $query = "SELECT * FROM employee WHERE sx_yphrethshs = 389";
                        $result = mysql_query($query, $mysqlconnection);
                        if (!mysql_num_rows($result))
                            exit('Δεν υπάρχουν εκπαιδευτικοί γι\'αυτή την ενέργεια...');
                        //
                        //$query = "DROP TABLE employee_bkp";
                        //$result = mysql_query($query, $mysqlconnection);
                        //$query = "CREATE TABLE employee_bkp SELECT * FROM employee";
                        //$result = mysql_query($query, $mysqlconnection);
                        $query = "INSERT INTO employee_moved SELECT * FROM employee WHERE sx_yphrethshs = 389";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs = 389";
                        $result = mysql_query($query, $mysqlconnection);
                        $num = mysql_affected_rows();
                        //echo $query;
                        if ($result)
                            echo "Επιτυχής μεταβολή $num εγγραφών.";
                        else 
                            echo "Πρόβλημα στη διαγραφή...";
                }
                
                
		mysql_close();

?>
<br><br>
  </body>
</html>