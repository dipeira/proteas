<?php
    header('Content-type: text/html; charset=iso8859-7'); 
    require_once"../config.php";
    require_once"../tools/functions.php";
        require '../tools/calendar/tc_calendar.php';  

        session_start();
?>    
  <html>
  <head>      
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <title>Πρόσληψη έκτακτων εκπαιδευτικών προηγούμενου έτους</title>
        <link href="../css/select2.min.css" rel="stylesheet" />
           <script type="text/javascript" src="../js/1.7.2.jquery.min.js"></script>
        <script type="text/javascript" src="../js/jquery.validate.js"></script>
        <script src="../js/select2.min.js"></script>
        <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
        <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
        <script type="text/javascript">
            $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
                        $(".ektaktoi_select").select2();
        });
                
            $(document).ready(function(){
        $("#hirefrm").validate({
            debug: false,
                        rules: {
                ektaktoi: "required", type: "required", praxi: "required"
            },
            messages: {
                ektaktoi: "Παρακαλώ επιλέξτε εκπ/κούς!", type: "Παρακαλώ επιλέξτε τύπο πρόσληψης!", praxi: "Παρακαλώ δώστε πράξη!"
            }
        });
            });
            
        </script>
        
<?php        
    // ektaktoi_hire: Re-hires last year's employees

        require "../tools/class.login.php";
        $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
}
        $usrlvl = $_SESSION['userlevel'];

        $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
        mysqli_query($mysqlconnection, "SET NAMES 'greek'");
        mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
        
        echo "<html><head><h2>Πρόσληψη έκτακτων εκπαιδευτικών προηγούμενου έτους</h2></head><body>";
        
        echo "<form action='' id='hirefrm' method='POST' autocomplete='off'>";
        echo "<table class=\"imagetable\" border='1'>";
        
        $sql = "select * from ektaktoi_".$_SESSION['sxoletos'];
        $result = mysqli_query($mysqlconnection, $sql);
        echo "<tr><td>Επιλογή εκπ/κών:</td><td>";
        $cmb = "<select name=\"ektaktoi[]\" class=\"ektaktoi_select\" multiple=\"multiple\">";
while ($row = mysqli_fetch_array($result)){
    if (in_array($row['id'], $_POST['ektaktoi'])) {
        $cmb .= "<option value=\"".$row['id']."\" selected>".$row['surname'].' '.$row['name']."</option>";
    } else {
        $cmb .= "<option value=\"".$row['id']."\">".$row['surname'].' '.$row['name']." (τ.".substr($row['patrwnymo'], 0, 3).")</option>";
    }
}
        $cmb .= "</select>";
        echo $cmb;
        
        echo "</td></tr>";
        
        echo "<tr><td>Ημερομηνία πρόσληψης:</td><td>";
        $myCal = new tc_calendar("date", true);
        $myCal->setIcon("../tools/calendar/images/iconCalendar.gif");
        $myCal->setDate(date('d'), date('m'), date('Y'));
        $myCal->setPath("../tools/calendar/");
        $myCal->setYearInterval(1970, 2030);
        $myCal->dateAllow("1970-01-01", date("2030-01-01"));
        $myCal->setAlignment("left", "bottom");
        $myCal->writeScript();
        echo "</td></tr>";
        echo "<tr><td>Τύπος πρόσληψης</td><td>";
        typeCmb($mysqlconnection);
        echo "</td></tr>";
        echo "<tr><td>Πράξη</td><td>";
        tblCmb($mysqlconnection, 'praxi');
        echo "  <small><a target=\"_blank\" href=\"praxi.php\">Πράξεις</a></small>";
        echo "</td></tr>";
        
        echo "<tr><td colspan=2><input type='submit' value='Πρόσληψη'>";
        echo "&nbsp;&nbsp;&nbsp;";
        echo "<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='ektaktoi_prev.php?sxoletos=".$_SESSION['sxoletos']."'\"></td></tr>";
        echo "</table></form>";

    // submitted: now prompt user to confirm
if (isset($_POST['ektaktoi'])) {
        $ektaktoi_csv = implode(', ', $_POST['ektaktoi']);
                
        $query = "SELECT surname,name,patrwnymo FROM ektaktoi_".$_SESSION['sxoletos']." WHERE id IN (".$ektaktoi_csv.")";
        $result = mysqli_query($mysqlconnection, $query);

        echo "<br><br><h2>Επιβεβαίωση προσλήψεων</h2>";
        echo "<h5>Ημερομηνία:".date('d-m-Y', strtotime($_POST['date']))."</h5>";
        echo "<h5>Πράξη:". getNamefromTbl($mysqlconnection, 'praxi', $_POST['praxi']). "</h5>";
        echo "<h5>Τύπος απασχόλησης:". get_type($_POST['type'], $mysqlconnection). "</h5>";
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
        echo "<thead><tr><th>Επώνυμο</th><th>Όνομα</th><th>Πατρώνυμο</th></tr></thead>";
    while ($row = mysqli_fetch_array($result))
        {
                    echo "<tr><td>".$row['surname']."</td><td>".$row['name']."</td><td>".$row['patrwnymo']."</td></tr>";
    }
        echo "<tr><td colspan=3><small>Αριθμός εγγραφών: ".mysqli_num_rows($result)."</small></td></tr>";
                
                
        echo "<form action='' method='POST' autocomplete='off'>";
        echo "<input type='hidden' name = 'ektaktoi2' value='".$ektaktoi_csv."'>";
        echo "<input type='hidden' name = 'date2' value='".$_POST['date']."'>";
        echo "<input type='hidden' name = 'type2' value='".$_POST['type']."'>";
        echo "<input type='hidden' name = 'praxi2' value='".$_POST['praxi']."'>";

        echo "<tr><td colspan=3><input type='submit' name='confirm' value='Ολοκλήρωση Πρόσληψης'>";
        echo "</table>";
        echo "</form>";
}
        //confirmed, now proceed to insertion
if (isset($_POST['ektaktoi2'])) {
    // check if at least one is already inserted
            
    $query = "SELECT surname, name, patrwnymo, afm FROM ektaktoi WHERE afm IN (select afm from ektaktoi_". $_SESSION['sxoletos'] . " where id in (".$_POST['ektaktoi2']."))";
    //"SELECT surname, name, patrwnymo FROM ektaktoi_". $_SESSION['sxoletos'] . " WHERE id IN (".$_POST['ektaktoi2'].")";
    //echo $query;
    $result = mysqli_query($mysqlconnection, $query);
    if (mysqli_num_rows($result) > 0) {
        echo '<br><strong>Σφάλμα:</strong> Οι παρακάτω εκπ/κοί έχουν ήδη προσληφθεί: <br>';
        echo "<ul>";
        while ($row = mysqli_fetch_array($result)) {
            echo '<li>'.$row['surname'] . ' ' . $row['name'] . ' (πατρ. ' . $row['patrwnymo'] . '), ΑΦΜ: ' . $row['afm'] .'</li>';
        }
        echo "</ul>";
        echo "<br><strong>Δεν πραγματοποιήθηκε καμία εισαγωγή.</strong>";
        exit();
    }
    // proceed
    $query = "INSERT INTO ektaktoi (name,surname,patrwnymo,mhtrwnymo,klados,met_did,status,afm,stathero,kinhto,type,praxi,hm_anal)"
            . " SELECT name,surname,patrwnymo,mhtrwnymo,klados,met_did,status,afm,stathero,kinhto,". $_POST['type2'].",". $_POST['praxi2'].",'". date('Y-m-d', strtotime($_POST['date2']))."' "
            . "FROM ektaktoi_".$_SESSION['sxoletos']." WHERE id in (".$_POST['ektaktoi2'].")";
    //echo $query;
    $result = mysqli_query($mysqlconnection, $query);
    if ($result) {
        echo "Έγινε επιτυχής εισαγωγή " . mysqli_affected_rows() . " εκπαιδευτικών.";
    }

}

        mysqli_close($mysqlconnection);
?>
<br><br>
</html>
