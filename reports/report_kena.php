<?php
    header('Content-type: text/html; charset=iso8859-7'); 
    //require_once "../tools/functions.php";
?>
<html>
  <head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <!--
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
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
    require_once"../tools/functions.php";
    session_start();

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'greek'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");

    echo "<body>";
    require '../etc/menu.php';
    echo "<h3>Οργανικά κενά</h3>";
    echo "<p>Παρακαλώ επιλέξτε τύπο σχολείου:</p>";
    echo "<a href='report_kena.php?type=1'>Δημοτικά Σχολεία</a><br>";
    echo "<a href='report_kena.php?type=2'>Νηπιαγωγεία</a><br>";
    echo "<a href='report_kena.php?type=3'>Ειδικά Σχολεία</a><br>";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";

if ($_GET['type'] == 1 || $_GET['type'] == 3) {
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
    echo "<th colspan=9>Οργανικές</th>";
    echo "<th colspan=9>Οργανικά Κενά</th>";
    echo "</tr>";
    echo "<tr><th>ΠΕ70</th><th>ΠΕ11</th><th>ΠΕ06</th><th>ΠΕ79</th>";
    echo "<th>ΠΕ05</th><th>ΠΕ07</th><th>ΠΕ08</th><th>ΠΕ86</th><th>ΠΕ91</th>";
    echo "<th>ΠΕ70</th><th>ΠΕ11</th><th>ΠΕ06</th><th>ΠΕ79</th>";
    echo "<th>ΠΕ05</th><th>ΠΕ07</th><th>ΠΕ08</th><th>ΠΕ86</th><th>ΠΕ91</th>";
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
        $organikes = unserialize(mysqli_result($result, $i, "organikes"));
        $orgs = get_orgs($sch,$mysqlconnection);
        // οργανικά τοποθετηθέντες
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados=2 AND status IN (1,3) AND thesi IN (0,1,2)";
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
            echo "<td>".$organikes[$j]."</td>";
        }
        echo "<td>".($organikes[0] - $orgs['ΠΕ70'])."</td>";
        echo "<td>".($organikes[1] - $orgs['ΠΕ11'])."</td>";
        echo "<td>".($organikes[2] - $orgs['ΠΕ06'])."</td>";
        echo "<td>".($organikes[3] - $orgs['ΠΕ79'])."</td>";
        echo "<td>".($organikes[4] - $orgs['ΠΕ05'])."</td>";
        echo "<td>".($organikes[5] - $orgs['ΠΕ07'])."</td>";
        echo "<td>".($organikes[6] - $orgs['ΠΕ08'])."</td>";
        echo "<td>".($organikes[7] - $orgs['ΠΕ86'])."</td>";
        echo "<td>".($organikes[8] - $orgs['ΠΕ91'])."</td>";
        echo "</tr>\n";

        for ($j=0; $j<9; $j++){
            $organikes_sum[$j] += $organikes[$j];
        }
        
        $kena_org_sum[0] += $organikes[0] - $orgs['ΠΕ70'];
        $kena_org_sum[1] += $organikes[1] - $orgs['ΠΕ11'];
        $kena_org_sum[2] += $organikes[2] - $orgs['ΠΕ06'];
        $kena_org_sum[3] += $organikes[3] - $orgs['ΠΕ79'];
        $kena_org_sum[4] += $organikes[4] - $orgs['ΠΕ05'];
        $kena_org_sum[5] += $organikes[5] - $orgs['ΠΕ07'];
        $kena_org_sum[6] += $organikes[6] - $orgs['ΠΕ08'];
        $kena_org_sum[7] += $organikes[7] - $orgs['ΠΕ86'];
        $kena_org_sum[8] += $organikes[8] - $orgs['ΠΕ91'];


        $i++;                        
    }

    echo "<tr><td></td><td></td><td>ΣΥΝΟΛΑ</td>";
    echo "<td>$synorgan</td><td>$synorgtop</td>";
    for ($j=0; $j<9; $j++){
        echo "<td>".$organikes_sum[$j]."</td>";
    }
    for ($j=0; $j<9; $j++){
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
else if ($_GET['type'] == 2) {
    //nipiagogeia
    $type = 2;
    $synorgtop = 0;
    // only dhmosia kai eidika (type2 = 0 or 2)
    $query = "SELECT * from school WHERE type2 in (0,2) AND type = $type";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);

    echo "<body>";
    echo "<center>";
    $i=0;
    ob_start();
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
    echo "<thead>";
    echo "<tr><th rowspan=2>Κωδ.</th>";
    echo "<th rowspan=2>Ονομασία</th>";
    echo "<th rowspan=2>Κατ.</th>";
    echo "<th>Οργανικές</th>";
    echo "<th>Οργ.Τοπ.</th>";
    echo "<th>Οργανικά Κενά</th>";
    echo "</tr>";
    echo "</thead>\n<tbody>\n";

    while ($i < $num)
    {        
        $sch = mysqli_result($result, $i, "id");
        $name = getSchool($sch, $mysqlconnection);
        $code = mysqli_result($result, $i, "code");
        $cat = getCategory(mysqli_result($result, $i, "category"));
        $students = mysqli_result($result, $i, "students");
        $organikes = unserialize(mysqli_result($result, $i, "organikes"));
        $kena_org = unserialize(mysqli_result($result, $i, "kena_org"));
        // οργανικά τοποθετηθέντες
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados=1 AND status IN (1,3)";
        $rs = mysqli_query($mysqlconnection, $qry);
        $orgtop = mysqli_result($rs, 0, "cnt");
        $synorgtop += $orgtop;

        echo "<tr>";
        echo "<td>$code</td>";
        echo "<td><a href='../school/school_status.php?org=$sch' target='_blank'>$name</a></td>";
        echo "<td>$cat</td>";
        echo "<td>$organikes[0]</td>";
        echo "<td>$orgtop</td>";
        echo "<td>$kena_org[0]</td>";
        echo "</tr>\n";

        $organikes_sum[0] += $organikes[0];

        $kena_org_sum[0] += $kena_org[0];


        $i++;                        
    }
    echo "<tr><td></td><td>ΣΥΝΟΛΑ</td><td></td><td>$organikes_sum[0]</td><td>$synorgtop</td><td>$kena_org_sum[0]</td></tr>";
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
}
?>
</body>
</html>
                
