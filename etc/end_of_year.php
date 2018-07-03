<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once"../config.php";
	require_once"../tools/functions.php";
    $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
    mysql_select_db($db_name, $mysqlconnection);
?>	
<html>
    <head>
        <title>Τέλος Διδακτικού/Σχολικού έτους - Ενέργειες</title>
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
    <?php include('../etc/menu.php'); ?>
<?php       
	// end_of_year: Includes end-of-year actions: Deletes ektakto personnel, returns personnel from other pispe etc...
    include("../tools/class.login.php");
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false){
        header("Location: ../tools/login.php");
    }
    $usrlvl = $_SESSION['userlevel'];
            
    $sxol_etos = getParam('sxol_etos', $mysqlconnection);
    $tbl_bkp_mon = "employee_bkp_$sxol_etos";
    echo "<html><head><h2>Τέλος Διδακτικού/Σχολικού έτους - Ενέργειες</h2></head><body>";
    echo "<h3><blink>Προσοχή: Μη αναστρέψιμες ενέργειες</blink></h3>";
    echo "<tr><td><p>Επιβάλλεται η εκτέλεση με τη σειρά του αριθμού που αναγράφεται.</p>";
    echo "<table class=\"imagetable\" border='1'>";
    echo "<form action='' method='POST' autocomplete='off'>";
    echo "<tr><td>Ενέργεια</td><td>";
    echo "<input type='radio' name='type' value='2'>1. Διαγραφή Αναπληρωτών / Ωρομισθίων από βάση δεδομένων (να γίνει πριν την αλλαγή Σχ.έτους)<br>";
    echo "<input type='radio' name='type' value='8'>2. Αλλαγή σχολικού έτους (τρέχον σχολικό έτος: $sxol_etos)<br>";
    echo "<input type='radio' name='type' value='10'>3. Αλλαγή ωραριου εκπ/κων<br>";
    echo "<input type='radio' name='type' value='11'>4. Πλήρωση πίνακα υπηρετήσεων<br>";
    //echo "<input type='radio' name='type' value='6' disabled>3. Επιστροφή αποσπασμένων εκπαιδευτικών του ΠΥΣΠΕ Ηρακλείου στην οργανική τους<br>";
    //echo "<input type='radio' name='type' value='3' disabled>4. Επιστροφή αποσπασμένων εκπαιδευτικών από άλλα ΠΥΣΠΕ<br>";
    //echo "<input type='radio' name='type' value='5' disabled>5. Διαγραφή αποσπασμένων από άλλα ΠΥΣΠΕ / ΠΥΣΔΕ από βάση δεδομένων<br>";
    echo "<br>";
    echo "<input type='radio' name='type' value='4'>Επιστροφή αποσπασμένων εκπαιδευτικών από φορείς (για 31/08)<br>";
    echo "<br>";
    echo "<input type='radio' name='type' value='1'>Εκτύπωση βεβαιώσεων Αναπληρωτών Κρατικού Προυπολογισμού<br>";
    echo "<input type='radio' name='type' value='7'>Εκτύπωση βεβαιώσεων Αναπληρωτών ΕΣΠΑ<br>";
    echo "</td></tr>";
    if ($usrlvl > 0)
        echo "<tr><td colspan=2><input type='submit' value='Πραγματοποίηση' disabled>";
    else
        echo "<tr><td colspan=2><input type='submit' value='Πραγματοποίηση'>";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "</td></tr>";
    echo "</form></table>";
    if ($usrlvl > 0)
    {
        echo "<br><br><h3>Δεν έχετε δικαίωμα για την πραγματοποίηση αυτών των ενεργειών. Επικοινωνήστε με το διαχειριστή σας.</h3>";
        mysql_close();
        exit;
    }
    // Allagh sxolikoy etoys
    if (isset($_POST['sxoletos']))
    {
        //do2yphr($mysqlconnection);
        $curSxoletos = getParam('sxol_etos', $mysqlconnection);
        if ($curSxoletos == $_POST['sxoletos']){
            echo "<br><br>";
            die('Σφάλμα: Το έτος έχει ήδη αλλάξει...');
        }
        setParam('sxol_etos', $_POST['sxoletos'], $mysqlconnection);
        // more...
        echo "<h3>Επιστροφή αποσπασμένων εκπαιδευτικών του ΠΥΣΠΕ Ηρακλείου στην οργανική τους</h3>";
        $query = "CREATE TABLE $tbl_bkp_mon SELECT * FROM employee";
        $result = mysql_query($query, $mysqlconnection);
        $query = "DROP TABLE employee_moved";
        $result = mysql_query($query, $mysqlconnection);
        // 388: allo pyspe, 389: apospasi se forea, 397: sxol. symvoulos, 399: Apospash ekswteriko
        // thesi 2: d/nths, 4: dioikhtikos
        $query = "CREATE TABLE employee_moved SELECT * FROM employee WHERE sx_yphrethshs NOT IN (388,389,397,399) AND thesi NOT IN (2,4)";
        $result = mysql_query($query, $mysqlconnection);
        $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs NOT IN (388,389,397,399) AND thesi NOT IN (2,4)";
        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_affected_rows($mysqlconnection);
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
        $num = mysql_affected_rows($mysqlconnection);
        //echo $query;
        if ($result)
            echo "Επιτυχής μεταβολή $num εγγραφών.";
        else 
            echo "Πρόβλημα στη διαγραφή...";
        
        // echo "<h3>Διαγραφή αποσπασμένων από άλλα ΠΥΣΠΕ / ΠΥΣΔΕ από βάση δεδομένων</h3>";
        // $query = "DROP TABLE employee_deleted";
        // $result = mysql_query($query, $mysqlconnection);
        // // 388: Άλλο ΠΥΣΠΕ, 394: Άλλο ΠΥΣΔΕ
        // $query = "CREATE TABLE employee_deleted SELECT * FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397,389)";
        // $result = mysql_query($query, $mysqlconnection);
        // $query = "DELETE FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397,389)";
        // $result = mysql_query($query, $mysqlconnection);
        // $num = mysql_affected_rows();
        // //echo $query;
        // if ($result)
        //     echo "Επιτυχής μεταβολή $num εγγραφών.";
        // else 
        //     echo "Πρόβλημα στη διαγραφή...";
        //
        //do2yphr($mysqlconnection);
        echo "<br><br>H διαδικασία ολοκληρώθηκε...";
    }
    ////////////////////////////////////////
    // vevaiwseis proyphresias anaplhrwtwn
    ////////////////////////////////////////
    if($_POST['type'] == 1 || $_POST['type'] == 7)
    {
        if ($_POST['type'] == 1)
            $kratikoy = 1;
        $sxol_etos = getParam('sxol_etos', $mysqlconnection);
        mysql_query("SET NAMES 'greek'", $mysqlconnection);
        mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
        // kratikoy or ESPA 
        if ($kratikoy)
            $query = "SELECT e.id,e.name,e.surname,e.patrwnymo,e.klados,p.name as praksi,p.ya,p.ada,p.apofasi,p.type,e.hm_anal,e.hm_apox,e.metakinhsh,e.afm,e.type as typos from ektaktoi e JOIN praxi p ON e.praxi = p.id WHERE e.type IN (1,2) AND p.type ='ΚΡΑΤ'";
        else
            $query = "SELECT e.id,e.name,e.surname,e.patrwnymo,e.klados,p.name as praksi,p.ya,p.ada,p.apofasi,p.type,e.hm_anal,e.hm_apox,e.metakinhsh,e.afm,e.type as typos from ektaktoi e JOIN praxi p ON e.praxi = p.id WHERE e.type IN (1,3,4,5,6) AND p.type !='ΚΡΑΤ'";

        $result = mysql_query($query, $mysqlconnection);
        $num=mysql_num_rows($result);

        echo "<h3>Εκτύπωση βεβαιώσεων Αναπληρωτών ";
        if ($kratikoy)
            echo "κρατικού προύπολογισμού";
        else
            echo "ΕΣΠΑ";
        echo "</h3>";
        if ($num == 0){
            echo "<p>Δε βρέθηκαν εγγραφές!</p>";
            die();
        }
        echo "<form name='anaplfrm' action=\"../employee/vev_yphr_anapl.php\" method='POST'>";
        

        $i=$cnt=0;
        // ***********************
        while ($i < $num)
        //while ($i < 20) // for testing
        // ***********************
        {
            $id = mysql_result($result, $i, "id");
            $name = mysql_result($result, $i, "name");
            $surname = mysql_result($result, $i, "surname");
            $patrwnymo = mysql_result($result, $i, "patrwnymo");
            $klados = mysql_result($result, $i, "klados");
            $ya = mysql_result($result, $i, "ya");
            $ada = mysql_result($result, $i, "ada");
            $apof = mysql_result($result, $i, "apofasi");
            $hmpros = mysql_result($result, $i, "hm_anal");
            $hmapox = mysql_result($result, $i, "hm_apox");
            $metakinhsh = mysql_result($result, $i, "metakinhsh");
            $last_afm = substr (mysql_result($result, $i, "afm"), -3);
            $ptype = mysql_result($result, $i, "type");
            $praksi = mysql_result($result, $i, "praksi");
            $typos = mysql_result($result, $i, "typos");

            if ($typos == 1)
                $meiwmeno = true;
            else 
                $meiwmeno = false;

            // get yphrethseis
            unset($sx_yphrethshs);
            $sx_yphrethshs[] = array();
            $qry = "select yphrethsh, hours from yphrethsh_ekt where emp_id = $id AND sxol_etos = $sxol_etos";
            $res1 = mysql_query($qry, $mysqlconnection);
            while ($arr = mysql_fetch_array($res1))
            {
                $sx_yphrethshs[] = array('sch' => $arr[0],'hours' => $arr[1]);
            }
            // Proteas has now prefix to distinguish workers.
            $prefix = '';
            $eepebp = 0;

            if (!$kratikoy)
            {
                if (strlen($ptype) > 0)
                    $prefix = greek_to_greeklish($ptype).'_';
            }
                        
            if (strpos($prefix,'PEP') !== false)
                $eepebp = 2;
            elseif (strpos($prefix,'YPOS') !== false || strpos($praksi,'ΕΕΠ') !== false || strpos($praksi,'ΕΒΠ') !== false) 
                $eepebp = 1;

            $cnt++;
            
            // get (and subtract) Adeies
            $adeies = get_adeies($id, $mysqlconnection);

            $metakinhsh = str_replace("'", "", $metakinhsh);
            $emp_arr = array(
                'id'=>$id,'name'=>$name,'surname'=>$surname,'patrwnymo'=>$patrwnymo,
                'klados'=>$klados,'sx_yphrethshs'=>$sx_yphrethshs,
                'ya'=>$ya,'ada'=>$ada,'apof'=>$apof,'hmpros'=>$hmpros,'hmapox'=>$hmapox,'metakinhsh'=>$metakinhsh,
                'last_afm'=>$last_afm,'prefix'=>$prefix,'eepebp'=>$eepebp,
                'adeies'=>$adeies, 'meiwmeno'=>$meiwmeno
            );

            $submit_array[] = $emp_arr;
            $i++;
        }
        echo "<input type='hidden' name='emp_arr' value='". serialize($submit_array) ."'>";
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
        
        $tbl_prx = "praxi_$sxol_etos";
        $query = "CREATE TABLE $tbl_prx SELECT * FROM praxi";
        $result = mysql_query($query, $mysqlconnection);
        $query = "TRUNCATE table praxi";
        $result = mysql_query($query, $mysqlconnection);
        if ($result)
            echo "Επιτυχής Διαγραφή. <br><small>Οι εκπ/κοί μεταφέρθηκαν στον πίνακα $tbl_ekt</small>";
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
    // Αλλαγή ωραριου εκπ/κων
    elseif ($_POST['type'] == 10)
    {
        echo "<br><br><a href='../employee/update_wres.php'>Αλλαγή ωραριου εκπ/κων</a><br>";
        echo "(Παρακαλώ βεβαιωθείτε ότι επιλέγετε 31/12 του τρέχοντος έτους και έχετε επιλέξει Τροποποίηση ωρών στη ΒΔ)";
    }
    elseif ($_POST['type'] == 11)
    {
        echo "<br><br><small>ΣΗΜ: Η διαδικασία διαρκεί αρκετή ώρα. Υπομονή...</small>";
        do2yphr($mysqlconnection);
    }
    mysql_close();

    // epistrofh ekp/kwn Hrakleioy sthn organikh toys
    /*
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
        // 388: Άλλο ΠΥΣΠΕ, 394: Άλλο ΠΥΣΔΕ
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
    */
?>
</body>
</html>