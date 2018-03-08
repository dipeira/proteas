<?php
header('Content-type: text/html; charset=iso8859-7');
require_once"../config.php";
require_once"../tools/functions.php";
?>	
<html>
    <head>      
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
        <script type="text/javascript">   
            $(document).ready(function() { 
                $("#mytbl").tablesorter({widgets: ['zebra']}); 
            });
            
            $().ready(function(){
                $(".slidingDiv").hide();
                $(".show_hide").show();
 
                $('.show_hide').click(function(){
                    $(".slidingDiv").slideToggle();
                });
            });
        </script>
        <title>Στατιστικά Μονίμων / Αναπληρωτών</title>
    </head>

    <?php
    include("../tools/class.login.php");
    $log = new logmein();
    if ($log->logincheck($_SESSION['loggedin']) == false) {
        header("Location: ../tools/login_check.php");
    }
    $usrlvl = $_SESSION['userlevel'];

    $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
    mysql_select_db($db_name, $mysqlconnection);
    mysql_query("SET NAMES 'greek'", $mysqlconnection);
    mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
    $query = "SELECT count( * ) FROM employee WHERE status!=2 AND thesi!=5";
    $result = mysql_query($query, $mysqlconnection);
    $monimoi_total = mysql_result($result, 0);
    $query = "SELECT count( * ) FROM employee WHERE status!=2 AND thesi=5";
    $result = mysql_query($query, $mysqlconnection);
    $idiwtikoi = mysql_result($result, 0);
    $query = "SELECT count( * ) FROM employee WHERE status!=2 AND sx_organikhs NOT IN (388,394) AND thesi!=5";
    $result = mysql_query($query, $mysqlconnection);
    $monimoi_her_total = mysql_result($result, 0);
    $query = "SELECT count( * ) FROM ektaktoi";
    $result = mysql_query($query, $mysqlconnection);
    $anapl_total = mysql_result($result, 0);
    $query = "SELECT count(*) FROM employee WHERE sx_organikhs=1 AND status!=2";
    $result = mysql_query($query, $mysqlconnection);
    $mon_diath = mysql_result($result, 0);
    $query = "SELECT count(*) FROM employee WHERE sx_organikhs=388 AND status!=2";
    $result = mysql_query($query, $mysqlconnection);
    $mon_apoallopispe = mysql_result($result, 0);
    $query = "SELECT count(*) FROM employee WHERE sx_organikhs=394 AND status!=2";
    $result = mysql_query($query, $mysqlconnection);
    $mon_apoallopisde = mysql_result($result, 0);
    $query = "SELECT count(*) FROM employee WHERE sx_yphrethshs=388 AND status!=2";
    $result = mysql_query($query, $mysqlconnection);
    $mon_seallopispe = mysql_result($result, 0);
    $query = "SELECT count(*) FROM employee WHERE sx_yphrethshs=389 AND status!=2";
    $result = mysql_query($query, $mysqlconnection);
    $mon_seforea = mysql_result($result, 0);
    $query = "SELECT count(*) FROM employee WHERE STATUS=3";
    $result = mysql_query($query, $mysqlconnection);
    $mon_seadeia = mysql_result($result, 0);
    $query = "SELECT count(*) FROM employee WHERE sx_organikhs=388 OR sx_organikhs=394";
    $result = mysql_query($query, $mysqlconnection);
    $mon_alloy = mysql_result($result, 0);
    //$mon_organ = $monimoi_total - $mon_alloy;


    $query = "SELECT COUNT( * ) , k.perigrafh, k.onoma FROM employee e 
                JOIN klados k 
                ON k.id = e.klados 
                WHERE status!=2 AND sx_organikhs NOT IN (388,394) AND thesi!=5
                GROUP BY klados";
    $result_mon = mysql_query($query, $mysqlconnection);
    //$num = mysql_fetch_array($result);
    $query = "SELECT COUNT( * ) , k.perigrafh, k.onoma, ek.type
                FROM ektaktoi e
                JOIN klados k ON k.id = e.klados
                JOIN ektaktoi_types ek ON e.type = ek.id
                GROUP BY klados, e.type";
    $result_anapl = mysql_query($query, $mysqlconnection);

    // sxoleia
    $sx_arr = array();
    $query = "SELECT count(*) FROM school WHERE type = 1";
    $res = mysql_result((mysql_query($query, $mysqlconnection)), 0);
    $sx_arr['Δημοτικά (Σύνολο)'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 0 AND type2 = 0";
    $res = mysql_result((mysql_query($query, $mysqlconnection)), 0);
    $sx_arr['Δημ. Ενεργά'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 1 AND type2 = 0";
    $res = mysql_result((mysql_query($query, $mysqlconnection)), 0);
    $sx_arr['Δημ. Ανενεργά'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 0 AND type2 = 2";
    $res = mysql_result((mysql_query($query, $mysqlconnection)), 0);
    $sx_arr['Δημ. Ειδικά'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 0 AND type2 = 1";
    $res = mysql_result((mysql_query($query, $mysqlconnection)), 0);
    $sx_arr['Δημ. Ιδιωτικά'] = $res;

    $query = "SELECT count(*) FROM school WHERE type = 2";
    $res = mysql_result((mysql_query($query, $mysqlconnection)), 0);
    $sx_arr['Νηπιαγωγεία (Σύνολο)'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 0 AND type2 = 0";
    $res = mysql_result((mysql_query($query, $mysqlconnection)), 0);
    $sx_arr['Νηπιαγωγεία'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 1 AND type2 = 0";
    $res = mysql_result((mysql_query($query, $mysqlconnection)), 0);
    $sx_arr['Νηπ. Ανενεργά'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 0 AND type2 = 2";
    $res = mysql_result((mysql_query($query, $mysqlconnection)), 0);
    $sx_arr['Νηπ. Ειδικά'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 0 AND type2 = 1";
    $res = mysql_result((mysql_query($query, $mysqlconnection)), 0);
    $sx_arr['Νηπ. Ιδιωτικά'] = $res;

    //
    echo "<body>";
    echo "<table class=\"imagetable\" border='1'>";
    echo "<tr><td colspan=3><strong>Μόνιμοι εκπαιδευτικοί (+ από άλλα ΠΥΣΠΕ/ΠΥΣΔΕ):&nbsp;$monimoi_total</strong></td></tr>";
    echo "</table>";
    echo "<br>";
    echo "<table class=\"imagetable\" border='1'>";
    echo "<tr><td colspan=3><strong>Μόνιμοι εκπαιδευτικοί (με οργανική στο Ηράκλειο):&nbsp;$monimoi_her_total</strong></td></tr>";
    echo "<tr><td>Κλάδος</td><td colspan=3>Πλήθος</td>";
    while ($row = mysql_fetch_array($result_mon, MYSQL_NUM)) {
        echo "<tr><td>$row[1] ($row[2])</td><td colspan=2>$row[0]</td></tr>";
    }
    echo "<tr><td><strong>Ιδιωτικοί εκπ/κοί</strong></td><td>$idiwtikoi</td></tr>";
    echo "</table>";
    echo "<br>";
    echo "<table class=\"imagetable\" border='1'>";
    //echo "<tr><td>Με οργανική στο ΠΥΣΠΕ</td><td>$mon_organ</td></tr>";
    echo "<tr><td>Υπηρετούν στο ΠΥΣΠΕ Ηρακλείου και <br>έχουν οργανική σε άλλο ΠΥΣΠΕ/ΠΥΣΔΕ</td><td>$mon_alloy</td></tr>";
    echo "<tr><td>Απόσπασμένοι από άλλο ΠΥΣΠΕ</td><td>$mon_apoallopispe</td></tr>";
    echo "<tr><td>Απόσπασμένοι/με διάθεση από άλλο ΠΥΣΔΕ</td><td>$mon_apoallopisde</td></tr>";
    echo "<tr><td>Διάθεση ΠΥΣΠΕ</td><td>$mon_diath</td></tr>";
    echo "<tr><td>Με απόσπαση σε άλλο ΠΥΣΠΕ</td><td>$mon_seallopispe</td></tr>";
    echo "<tr><td>Με απόσπαση σε φορέα</td><td>$mon_seforea</td></tr>";
    echo "<tr><td>Σε άδεια</td><td>$mon_seadeia</td></tr>";
    echo "</table>";
    echo "<br>";
    echo "<table class=\"imagetable\" border='1'>";
    echo "<tr><td colspan=3><strong>Αναπληρωτές / Ωρομίσθιοι εκπαιδευτικοί:&nbsp;$anapl_total</strong></td>";
    echo "<tr><td>Τύπος</td><td>Κλάδος</td><td>Πλήθος</td>";
    while ($row = mysql_fetch_array($result_anapl, MYSQL_NUM)) {
        echo "<tr><td>$row[3]<td>$row[1] ($row[2])</td><td>$row[0]</td></tr>";
    }
    echo "</table>";
    echo "<br>";

    echo "<table class=\"imagetable\" border='1'>";
    echo "<tr><td colspan=3><strong>Σχολεία</strong></td>";
    echo "<tr><td>Τύπος</td><td>Αριθμός</td>";
    foreach ($sx_arr as $k => $v)
        echo "<tr><td>$k</td><td>$v</td>";

    echo "</table>";

    echo "<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "</body>";
    echo "</html>";

    mysql_close();
    ?>
