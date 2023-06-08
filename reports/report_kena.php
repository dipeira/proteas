<?php
    header('Content-type: text/html; charset=utf-8'); 
    //require_once "../include/functions.php";
?>
<html>
  <head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <!--
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    -->
    <title>Πίνακας οργανικών κενών</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type="text/javascript" src="../js/stickytable.js"></script>
    <script type="text/javascript">    
        $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
            $("#mytbl").stickyTableHeaders();
        });  
    </script>
  </head>

<?php
    require_once"../config.php";
    require_once"../include/functions.php";
    session_start();

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

    echo "<body>";
    require '../etc/menu.php';
    echo "<h3>Οργανικά κενά</h3>";
    echo "<p>Παρακαλώ επιλέξτε τύπο σχολείου:</p>";
    echo "<a href='report_kena.php?type=1'>Δημοτικά Σχολεία</a><br>";
    echo "<a href='report_kena.php?type=2'>Νηπιαγωγεία</a><br>";
    echo "<a href='report_kena.php?type=3'>Ειδικά Δημοτικά Σχολεία</a><br>";
    echo "<a href='report_kena.php?type=4'>Ειδικά Νηπιαγωγεία</a><br>";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";

// dhmotika
if ($_GET['type'] == 1) {
    $type = 1;
    if ($_GET['type'] == 1) {
        $query = "SELECT * from school WHERE type2 = 0 AND type = $type";
    }
    else {
        $query = "SELECT * from school WHERE type2 = 2 AND type = $type";
    }

    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
        
        
    echo "<center>";
    $i=0;
    ob_start();
    $synorgtop = $synorgan = 0;
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
    echo "<thead>";
    echo "<tr><th rowspan=2>Κωδ.</th>";
    echo "<th rowspan=2>Ονομασία</th>";
    echo "<th rowspan=2>Κατ.</th>";
    echo "<th rowspan=2>Οργ.</th>";
    echo "<th rowspan=2>ΠΕ70<br>Οργ.<br>Τοποθ.</th>";
    echo "<th colspan=10>Οργανικές</th>";
    echo "<th colspan=10>Οργανικά Κενά</th>";
    echo "</tr>";
    echo "<tr><th>ΠΕ70</th><th>ΠΕ11</th><th>ΠΕ06</th><th>ΠΕ79</th>";
    echo "<th>ΠΕ05</th><th>ΠΕ07</th><th>ΠΕ08</th><th>ΠΕ86</th><th>ΠΕ91</th><th>Τ.Ε.</th>";
    echo "<th>ΠΕ70</th><th>ΠΕ11</th><th>ΠΕ06</th><th>ΠΕ79</th>";
    echo "<th>ΠΕ05</th><th>ΠΕ07</th><th>ΠΕ08</th><th>ΠΕ86</th><th>ΠΕ91</th><th>Τ.Ε.</th>";
    echo "</tr>";
    echo "</thead>\n<tbody>\n";

    while ($i < $num)
    {        
        $sch = mysqli_result($result, $i, "id");
        $name = getSchool($sch, $mysqlconnection);
        $code = mysqli_result($result, $i, "code");
        $cat = getCategory(mysqli_result($result, $i, "category"));
        $organikothta = mysqli_result($result, $i, "organikothta");
        $synorgan += $organikothta;
        $entaksis = explode(",", mysqli_result($result, $i, "entaksis"));
        $org_ent = $entaksis[0] ? 1 : 0;
        $organikes = unserialize(mysqli_result($result, $i, "organikes"));
        if (!is_array($organikes) || array_sum($organikes) == 0) { $organikes= array(0,0,0,0,0,0,0,0);}
        $orgs = get_orgs($sch,$mysqlconnection);
        // οργανικά τοποθετηθέντες
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados=2 AND status IN (1,3,5) AND ent_ty = 0 AND thesi IN (0,1,2)";
        $rs = mysqli_query($mysqlconnection, $qry);
        $orgtop = mysqli_result($rs, 0, "cnt");
        $synorgtop += $orgtop;
        
        echo "<tr>";
        echo "<td>$code</td>";
        echo "<td><a href='../school/school_status.php?org=$sch' target='_blank'>$name</a></td>";
        echo "<td>$cat</td>";
        echo "<td>$organikothta</td>";
        echo "<td>$orgtop</td>";
        for ($j=0; $j<9; $j++){
            echo "<td>";
            echo $organikes[$j] > 0 ? $organikes[$j] : 0;
            echo "</td>";
        }
        echo "<td>$org_ent</td>";
        echo "<td>".($organikes[0] - $orgs['ΠΕ70'])."</td>";
        echo "<td>".($organikes[1] - $orgs['ΠΕ11'])."</td>";
        echo "<td>".($organikes[2] - $orgs['ΠΕ06'])."</td>";
        echo "<td>".($organikes[3] - $orgs['ΠΕ79'])."</td>";
        echo "<td>".($organikes[4] - $orgs['ΠΕ05'])."</td>";
        echo "<td>".($organikes[5] - $orgs['ΠΕ07'])."</td>";
        echo "<td>".($organikes[6] - $orgs['ΠΕ08'])."</td>";
        echo "<td>".($organikes[7] - $orgs['ΠΕ86'])."</td>";
        echo "<td>".($organikes[8] - $orgs['ΠΕ91'])."</td>";
        echo "<td>".($org_ent - $orgs['ent'])."</td>";
        echo "</tr>\n";

        for ($j=0; $j<9; $j++){
            $organikes_sum[$j] += $organikes[$j];
        }
        $organikes_sum[9] += $org_ent;
        
        $kena_org_sum[0] += $organikes[0] - $orgs['ΠΕ70'];
        $kena_org_sum[1] += $organikes[1] - $orgs['ΠΕ11'];
        $kena_org_sum[2] += $organikes[2] - $orgs['ΠΕ06'];
        $kena_org_sum[3] += $organikes[3] - $orgs['ΠΕ79'];
        $kena_org_sum[4] += $organikes[4] - $orgs['ΠΕ05'];
        $kena_org_sum[5] += $organikes[5] - $orgs['ΠΕ07'];
        $kena_org_sum[6] += $organikes[6] - $orgs['ΠΕ08'];
        $kena_org_sum[7] += $organikes[7] - $orgs['ΠΕ86'];
        $kena_org_sum[8] += $organikes[8] - $orgs['ΠΕ91'];
        $kena_org_sum[9] += $org_ent - $orgs['ent'];

        $i++;                        
    }

    echo "<tr class='synola'><td></td><td></td><td>ΣΥΝΟΛΑ</td>";
    echo "<td>$synorgan</td><td>$synorgtop</td>";
    for ($j=0; $j<10; $j++){
        echo "<td>".$organikes_sum[$j]."</td>";
    }
    for ($j=0; $j<10; $j++){
        echo "<td>".$kena_org_sum[$j]."</td>";
    }
    echo "</tbody></table>";
    echo "<br>";

    $page = ob_get_contents(); 
    $_SESSION['page'] = $page;
    ob_end_flush();

    echo "<form action='../tools/2excel_ses.php' method='post'>";
    //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
    echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "</form>";
    //ob_end_clean();
}
// eidika dhmotika
else if ($_GET['type'] == 3) {
    $type = 1;
    $query = "SELECT * from school WHERE type2 = 2 AND type = $type";

    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
        
    echo "<center>";
    $i=0;
    ob_start();
    $synorgtop = $synorgan = 0;
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
    echo "<thead>";
    echo "<tr><th rowspan=2>Κωδ.</th>";
    echo "<th rowspan=2>Ονομασία</th>";
    echo "<th rowspan=2>Κατ.</th>";
    echo "<th rowspan=2>Οργ.</th>";
    echo "<th rowspan=2>ΠΕ70 ΕΑΕ<br>Οργ.<br>Τοποθ.</th>";
    echo "<th colspan=14>Οργανικές</th>";
    echo "<th colspan=14>Οργανικά Κενά</th>";
    echo "</tr>";
    echo "<tr>";
    // organikes
    echo "<th>11</th><th>79</th>";
    echo "<th>08</th><th>86</th><th>91</th>";
    echo "<th><span title='Λογοθεραπευτών'>21</th>";
    echo "<th><span title='Ψυχολόγων'>23</th>";
    echo "<th><span title='Σχ.Νοσηλευτών'>25</th>";
    echo "<th><span title='Λογοθεραπευτών'>26</th>";
    echo "<th><span title='Φυσικοθεραπευτών'>28</th>";
    echo "<th><span title='Εργοθεραπευτών'>29</th>";
    echo "<th><span title='Κοιν.Λειτουργών'>30</th>";
    echo "<th><span title='Βοηθ.Προσ.Ειδ.Αγ.'>ΔΕ1<br>ΕΒΠ</th><th>70 ΕΑΕ</th>";
    // kena
    echo "<th>11</th><th>79</th>";
    echo "<th>08</th><th>86</th><th>91</th>";
    echo "<th><span title='Λογοθεραπευτών'>21</th>";
    echo "<th><span title='Ψυχολόγων'>23</th>";
    echo "<th><span title='Σχ.Νοσηλευτών'>25</th>";
    echo "<th><span title='Λογοθεραπευτών'>26</th>";
    echo "<th><span title='Φυσικοθεραπευτών'>28</th>";
    echo "<th><span title='Εργοθεραπευτών'>29</th>";
    echo "<th><span title='Κοιν.Λειτουργών'>30</th>";
    echo "<th><span title='Βοηθ.Προσ.Ειδ.Αγ.'>ΔΕ1<br>ΕΒΠ</th><th>70 ΕΑΕ</th>";
    echo "</tr>";
    echo "</thead>\n<tbody>\n";

    while ($i < $num)
    {        
        $sch = mysqli_result($result, $i, "id");
        $name = getSchool($sch, $mysqlconnection);
        $code = mysqli_result($result, $i, "code");
        $cat = getCategory(mysqli_result($result, $i, "category"));
        $organikothta = mysqli_result($result, $i, "organikothta");
        $synorgan += $organikothta;
        $entaksis = explode(",", mysqli_result($result, $i, "entaksis"));
        $org_ent = $entaksis[0] ? 1 : 0;
        $organikes = unserialize(mysqli_result($result, $i, "organikes"));
        if (!is_array($organikes) || array_sum($organikes) == 0) { $organikes= array(0,0,0,0,0,0,0,0);}
        $orgs = get_orgs($sch,$mysqlconnection,true);
        // οργανικά τοποθετηθέντες ΠΕ70ΕΑΕ & ΠΕ71
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados in (18,19) AND status IN (1,3,5) AND ent_ty = 0 AND thesi IN (0,1,2)";
        $rs = mysqli_query($mysqlconnection, $qry);
        $orgtop70eae = mysqli_result($rs, 0, "cnt");
        $synorgtop70eae += $orgtop70eae;
        
        echo "<tr>";
        echo "<td>$code</td>";
        echo "<td><a href='../school/school_status.php?org=$sch' target='_blank'>$name</a></td>";
        echo "<td>$cat</td>";
        echo "<td>$organikothta</td>";
        echo "<td>$orgtop70eae</td>";
        for ($j=1; $j<18; $j++){
            if ($j == 2 || $j == 4 || $j == 5){
                continue;
            }
            echo "<td>";
            echo $organikes[$j] > 0 ? $organikes[$j] : 0;
            echo "</td>";
        }
        // echo "<td>$org_ent</td>";
        echo "<td>".($organikes[1] - $orgs['ΠΕ11'])."</td>";
        echo "<td>".($organikes[3] - $orgs['ΠΕ79'])."</td>";
        echo "<td>".($organikes[6] - $orgs['ΠΕ08'])."</td>";
        echo "<td>".($organikes[7] - $orgs['ΠΕ86'])."</td>";
        echo "<td>".($organikes[8] - $orgs['ΠΕ91'])."</td>";
        echo "<td>".($organikes[9] - $orgs['ΠΕ21'])."</td>";
        echo "<td>".($organikes[10] - $orgs['ΠΕ23'])."</td>";
        echo "<td>".($organikes[11] - $orgs['ΠΕ25'])."</td>";
        echo "<td>".($organikes[12] - $orgs['ΠΕ26'])."</td>";
        echo "<td>".($organikes[13] - $orgs['ΠΕ28'])."</td>";
        echo "<td>".($organikes[14] - $orgs['ΠΕ29'])."</td>";
        echo "<td>".($organikes[15] - $orgs['ΠΕ30'])."</td>";
        echo "<td>".($organikes[16] - $orgs['ΔΕ1ΕΒΠ'])."</td>";
        echo "<td>".($organikes[17] - $orgs['ΠΕ70ΕΑΕ'] - $orgs['ΠΕ71'])."</td>";

        // echo "<td>".($org_ent - $orgs['ent'])."</td>";
        echo "</tr>\n";

        for ($j=1; $j<18; $j++){
            if ($j == 2 || $j == 4 || $j == 5){
                continue;
            }
            $organikes_sum[$j] += $organikes[$j];
        }
        $organikes_sum[17] += $org_ent;
        
        $kena_org_sum[0] += $organikes[0] - $orgs['ΠΕ70'];
        $kena_org_sum[1] += $organikes[1] - $orgs['ΠΕ11'];
        $kena_org_sum[3] += $organikes[3] - $orgs['ΠΕ79'];
        $kena_org_sum[6] += $organikes[6] - $orgs['ΠΕ08'];
        $kena_org_sum[7] += $organikes[7] - $orgs['ΠΕ86'];
        $kena_org_sum[8] += $organikes[8] - $orgs['ΠΕ91'];
        $kena_org_sum[9] += $organikes[9] - $orgs['ΠΕ21'];
        $kena_org_sum[10] += $organikes[10] - $orgs['ΠΕ23'];
        $kena_org_sum[11] += $organikes[11] - $orgs['ΠΕ25'];
        $kena_org_sum[12] += $organikes[12] - $orgs['ΠΕ26'];
        $kena_org_sum[13] += $organikes[13] - $orgs['ΠΕ28'];
        $kena_org_sum[14] += $organikes[14] - $orgs['ΠΕ29'];
        $kena_org_sum[15] += $organikes[15] - $orgs['ΠΕ30'];
        $kena_org_sum[16] += $organikes[16] - $orgs['ΔΕ1ΕΒΠ'];
        $kena_org_sum[17] += $organikes[17] - $orgs['ΠΕ70ΕΑΕ'] - $orgs['ΠΕ71'];
        // $kena_org_sum[17] += $org_ent - $orgs['ent'];

        $i++;                        
    }

    echo "<tr class='synola'><td></td><td></td><td>ΣΥΝΟΛΑ</td>";
    echo "<td>$synorgan</td><td>$synorgtop70eae</td>";
    for ($j=1; $j<18; $j++){
        if ($j == 2 || $j == 4 || $j == 5){
            continue;
        }
        echo "<td>".$organikes_sum[$j]."</td>";
    }
    for ($j=1; $j<18; $j++){
        if ($j == 2 || $j == 4 || $j == 5){
            continue;
        }
        echo "<td>".$kena_org_sum[$j]."</td>";
    }
    echo "</tbody></table>";
    echo "<br>";

    $page = ob_get_contents(); 
    $_SESSION['page'] = $page;
    ob_end_flush();

    echo "<form action='../tools/2excel_ses.php' method='post'>";
    //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
    echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "</form>";
    //ob_end_clean();
}
//nipiagogeia
else if ($_GET['type'] == 2) {
    $type = 2;
    $synorgtop = 0;
    // dhmosia or eidika (type2 = 0 or 2)
    $query = $_GET['type'] == 2 ? 
        "SELECT * from school WHERE type2 = 0 AND type = $type and anenergo = 0" :
        "SELECT * from school WHERE type2 = 2 AND type = $type and anenergo = 0";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);

    echo "<body>";
    echo "<small><p>ΣΗΜ: Στήλη 'Οργανικές ΠΕ60': Με κίτρινο χρώμα οι οργανικές που είναι περισσότερες από την οργανικότητα και<br>Στήλη 'Οργ.Τοπ.ΠΕ60': με κόκκινο οι οργανικά τοποθετημένοι που είναι περισσότεροι από την οργανικότητα.</p></small>";
    echo "<center>";
    $i=0;
    
    ob_start();
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
    echo "<thead>";
    echo "<tr><th rowspan=2>Κωδ.</th>";
    echo "<th rowspan=2>Ονομασία</th>";
    echo "<th rowspan=2>Κατ.</th>";
    echo "<th>Οργανικότητα</th>";
    echo "<th>Οργανικές ΠΕ60</th>";
    echo "<th>Οργ.Τοπ.ΠΕ60</th>";
    echo "<th>Οργανικά Κενά ΠΕ60</th>";
    echo "<th>T.E.</th>";
    echo "<th>Οργανικά Κενά T.E.</th>";
    echo "</tr>";
    echo "</thead>\n<tbody>\n";

    while ($i < $num)
    {        
        $sch = mysqli_result($result, $i, "id");
        $name = getSchool($sch, $mysqlconnection);
        $code = mysqli_result($result, $i, "code");
        $cat = getCategory(mysqli_result($result, $i, "category"));
        $students = mysqli_result($result, $i, "students");
        $organikothta = mysqli_result($result, $i, "organikothta");
        $organikes = unserialize(mysqli_result($result, $i, "organikes"));
        if (!is_array($organikes) || array_sum($organikes) == 0) { $organikes= array(0,0,0,0,0,0,0,0);}
        //if (!is_array($kena_org) || array_sum($kena_org) == 0) { $kena_org= array(0,0,0,0,0,0,0,0);}
        // οργανικά τοποθετηθέντες
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados=1 AND status IN (1,3,5) AND thesi IN (0,1,2)";
        $rs = mysqli_query($mysqlconnection, $qry);
        $orgtop = mysqli_result($rs, 0, "cnt");
        $kena_org = $organikes[0] - $orgtop;
        $synorgtop += $orgtop;
        // οργανικά τοποθετηθέντες @ T.E.
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND status IN (1,3,5) AND org_ent=1";
        $rs = mysqli_query($mysqlconnection, $qry);
        $orgte = mysqli_result($rs, 0, "cnt");
        $synorgte += $orgte;
        $entaksis = explode(",", mysqli_result($result, $i, "entaksis"));
        $org_entaksis = $entaksis[0] ? 1 : 0;
        $kena_te = $org_entaksis - $orgte;

        echo "<tr>";
        echo "<td>$code</td>";
        echo "<td><a href='../school/school_status.php?org=$sch' target='_blank'>$name</a></td>";
        echo "<td>$cat</td>";
        echo "<td>$organikothta</td>";
        echo $organikes[0] > $organikothta ? 
            "<td style='background:none;background-color:rgba(234, 238, 17, 0.3)'>$organikes[0]</td>" : 
            "<td>$organikes[0]</td>";
        echo $orgtop > $organikothta ? 
            "<td style='background:none;background-color:rgba(255, 0, 0, 0.45)'>$orgtop</td>" : 
            "<td>$orgtop</td>" ;
        //echo "<td>$kena_org</td>";
        echo tdc($kena_org);
        echo "<td>$org_entaksis</td>";
        echo "<td>$kena_te</td>";
        echo "</tr>\n";

        $organikes_sum[0] += $organikes[0];

        $kena_org_sum[0] += $kena_org;
        $org_te_sum += $org_entaksis;
        $kena_org_te += $kena_te;

        $i++;                        
    }
    echo "<tr class='synola'><td></td><td>ΣΥΝΟΛΑ</td><td><td></td></td><td>$organikes_sum[0]</td><td>$synorgtop</td><td>$kena_org_sum[0]</td>";
    echo "<td>$org_te_sum</td><td>$kena_org_te</td></tr>";
    echo "</tbody></table>";
    echo "<small><i>Σύνολο εγγραφών: ".$i."</i></small>";
    echo "<br>";

    $page = ob_get_contents(); 
    $_SESSION['page'] = $page;
    ob_end_flush();

    echo "<form action='../tools/2excel_ses.php' method='post'>";
    //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
    echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "</form>";
}
// eidika nip
else if ($_GET['type'] == 4) {
    function nb($value) {
        return !$value ? 0 : $value;
    }
    //nipiagogeia
    $type = 2;
    $synorgtop = [];
    // dhmosia or eidika (type2 = 0 or 2)
    $query = "SELECT * from school WHERE type2 = 2 AND type = $type and anenergo = 0";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);

    echo "<body>";
    echo "<small><p>ΣΗΜ: Στήλη 'Οργανικές ΠΕ60': Με κίτρινο χρώμα οι οργανικές που είναι περισσότερες από την οργανικότητα και<br>Στήλη 'Οργ.Τοπ.ΠΕ60': με κόκκινο οι οργανικά τοποθετημένοι που είναι περισσότεροι από την οργανικότητα.</p></small>";
    echo "<center>";
    $i=0;
    
    ob_start();
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
    echo "<thead>";
    echo "<tr><th rowspan=2>Κωδ.</th>";
    echo "<th rowspan=2>Ονομασία</th>";
    echo "<th rowspan=2>Κατ.</th>";
    echo "<th><span title='Οργανικότητα'>Οργαν.</th>";
    echo "<th><span title='Νηπιαγωγών'>60</th>";
    echo "<th><span title='Λογοθεραπευτών'>21</th>";
    echo "<th><span title='Ψυχολόγων'>23</th>";
    echo "<th><span title='Σχ.Νοσηλευτών'>25</th>";
    echo "<th><span title='Λογοθεραπευτών'>26</th>";
    echo "<th><span title='Φυσικοθεραπευτών'>28</th>";
    echo "<th><span title='Εργοθεραπευτών'>29</th>";
    echo "<th><span title='Κοιν.Λειτουργών'>30</th>";
    echo "<th><span title='Βοηθ.Προσ.Ειδ.Αγ.'>ΔΕ1ΕΒΠ</th>";
    echo "<th>Οργ.Τοπ.<br>ΠΕ60</th>";
    echo "<th>Κενά<br>ΠΕ60</th>";
    echo "<th>Οργανικές<br>ΠΕ60 EAE</th>";
    echo "<th>Οργ.Τοπ.<br>ΠΕ60 EAE</th>";
    echo "<th>Κενά<br>ΠΕ60 EAE</th>";
    // echo "<th>T.E.</th>";
    // echo "<th>Οργανικά Κενά T.E.</th>";
    echo "</tr>";
    echo "</thead>\n<tbody>\n";

    while ($i < $num)
    {        
        $sch = mysqli_result($result, $i, "id");
        $name = getSchool($sch, $mysqlconnection);
        $code = mysqli_result($result, $i, "code");
        $cat = getCategory(mysqli_result($result, $i, "category"));
        $students = mysqli_result($result, $i, "students");
        $organikothta = mysqli_result($result, $i, "organikothta");
        $organikes = unserialize(mysqli_result($result, $i, "organikes"));
        if (!is_array($organikes) || array_sum($organikes) == 0) { $organikes= array(0,0,0,0,0,0,0,0);}
        //if (!is_array($kena_org) || array_sum($kena_org) == 0) { $kena_org= array(0,0,0,0,0,0,0,0);}
        // οργανικά τοποθετηθέντες
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados=1 AND status IN (1,3,5) AND thesi IN (0,1,2)";
        $rs = mysqli_query($mysqlconnection, $qry);
        $orgtop = mysqli_result($rs, 0, "cnt");
        $kena_org = $organikes[0] - $orgtop;
        // οργανικά τοποθετηθέντες 60.50 & 61
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados in (16,17) AND status IN (1,3,5) AND thesi IN (0,1,2)";
        $rs = mysqli_query($mysqlconnection, $qry);
        $orgtop6050 = mysqli_result($rs, 0, "cnt");
        $kena_org6050 = $organikes[9] - $orgtop6050;
        
        // οργανικά τοποθετηθέντες @ T.E.
        // $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados=1 AND status IN (1,3,5) AND ent_ty = 1 AND org_ent=1";
        // $rs = mysqli_query($mysqlconnection, $qry);
        // $orgte = mysqli_result($rs, 0, "cnt");
        // $synorgte += $orgte;
        $entaksis = explode(",", mysqli_result($result, $i, "entaksis"));
        $org_entaksis = $entaksis[0] ? 1 : 0;
        $kena_te = $org_entaksis - $orgte;

        echo "<tr>";
        echo "<td>$code</td>";
        echo "<td><a href='../school/school_status.php?org=$sch' target='_blank'>$name</a></td>";
        echo "<td>$cat</td>";
        echo "<td>$organikothta</td>";
        echo $organikes[0] > $organikothta ? 
            "<td style='background:none;background-color:rgba(234, 238, 17, 0.3)'>$organikes[0]</td>" : 
            "<td>$organikes[0]</td>";
        echo "<td>".nb($organikes[1])."</td>";
        echo "<td>".nb($organikes[2])."</td>";
        echo "<td>".nb($organikes[3])."</td>";
        echo "<td>".nb($organikes[4])."</td>";
        echo "<td>".nb($organikes[5])."</td>";
        echo "<td>".nb($organikes[6])."</td>";
        echo "<td>".nb($organikes[7])."</td>";
        echo "<td>".nb($organikes[8])."</td>";

        echo $orgtop > $organikothta ? 
            "<td style='background:none;background-color:rgba(255, 0, 0, 0.45)'>$orgtop</td>" : 
            "<td>$orgtop</td>" ;
        //echo "<td>$kena_org</td>";
        
        echo tdc($kena_org);
        echo "<td>".nb($organikes[9])."</td>";
        echo "<td>$orgtop6050</td>";
        echo tdc($kena_org6050);
        //echo "<td>$org_entaksis</td>";
        //echo "<td>$kena_te</td>";
        echo "</tr>\n";

        for ($j=0; $j<9; $j++){
            $organikes_sum[$j] += $organikes[$j];
        }
        //$organikes_sum[0] += $organikes[0];
        $synorgtop[0] += $orgtop;
        $synorgtop[1] += $orgtop6050;

        $kena_org_sum[0] += $kena_org;
        $kena_org_sum[1] += $kena_org6050;
        $kena_org_sum[2] += $organikes[9];
        // $org_te_sum += $org_entaksis;
        // $kena_org_te += $kena_te;

        $i++;                        
    }
    echo "<tr class='synola'><td></td><td>ΣΥΝΟΛΑ</td><td><td></td></td>";
    for ($k=0; $k<9; $k++){
        echo "<td>$organikes_sum[$k]</td>";
    }
    echo "<td>$synorgtop[0]</td><td>$kena_org_sum[0]</td><td>$kena_org_sum[2]</td>";
    echo "<td>$synorgtop[1]</td><td>$kena_org_sum[1]</td>";
    
    // echo "<td>$org_te_sum</td><td>$kena_org_te</td></tr>";
    echo "</tbody></table>";
    echo "<small><i>Σύνολο εγγραφών: ".$i."</i></small>";
    echo "<br>";

    $page = ob_get_contents(); 
    $_SESSION['page'] = $page;
    ob_end_flush();

    echo "<form action='../tools/2excel_ses.php' method='post'>";
    //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
    echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "</form>";
}
?>
</body>
</html>
                
