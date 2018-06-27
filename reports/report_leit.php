<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Πίνακας λειτουργικών κενών</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="../js/stickytable.js"></script>
	<script type="text/javascript">	
        $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
            $("#mytbl").stickyTableHeaders();
        });
	</script>
        <style>
            table.imagetable th {
                padding: 0px;
                font-weight: normal;
                font-size: 12px;
            }
        </style>
  </head>

<?php
    function hours_to_teachers($hours) {
        return round($hours/23,0);
    }
    function tdc1($val){
        return $val >= 0 ? 
            "<td style='background:none;background-color:rgba(0, 255, 0, 0.37)'><span title='".hours_to_teachers($val)."'>$val</span></td>" : 
            "<td style='background:none;background-color:rgba(255, 0, 0, 0.45)'><span title='".hours_to_teachers($val)."'>$val</span></td>";
    }
    
    require_once"../config.php";
    require_once"../tools/functions.php";
    session_start();

    $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
    mysql_select_db($db_name, $mysqlconnection);
    mysql_query("SET NAMES 'greek'", $mysqlconnection);
    mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
	
    // only dhmosia (type2 = 0)
    $query = "SELECT * from school WHERE type2 = 0 AND type = 1 AND anenergo=0";
    $result = mysql_query($query, $mysqlconnection);
    $num = mysql_num_rows($result);

    echo "<body>";
    include('../etc/menu.php');
    echo "<h3>Πίνακας λειτουργικών κενών</h3>";
    echo "<center>";
    $i=0;
    ob_start();
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
    echo "<thead>";
    echo "<tr><th rowspan=2>Κωδ.</th>";
    echo "<th rowspan=2>Ονομασία</th>";
    echo "<th rowspan=2>Οργ.</th>";
    echo "<th rowspan=2>Λειτ.</th>";
    echo "<th rowspan=2>Τοπ.<br>ΠΕ70</th>";
    // new
    echo "<th rowspan=2>Ωρ. Πρ.</th>";
    echo "<th rowspan=2>Ωρ. Ολ.</th>";
    echo "<th rowspan=2>Συν. Ωρ.</th>";
    echo "<th rowspan=2>Yπαρ. Ωρ.06,<br>11,79</th>";
    echo "<th rowspan=2>+/- 05-07,<br>06,86</th>";
    echo "<th rowspan=2>+/- 08,11,<br>79,91</th>";
    //
    echo "<th colspan=8>Υπάρχουν +/- <small>(με Δ/ντή, σε ώρες)</small></th>";
    echo "<th colspan=11>Λειτουργικά Κενά +/- <small>(σε ώρες)</small></th>";
    echo "</tr>";
    echo "<th>05-07</th><th>06</th><th>08</th><th>11</th><th>79</th><th>91</th><th>86</th><th>70</th>";
    echo "<th>05-07</th><th>06</th><th>08</th><th>11</th><th>79</th><th>91</th><th>86</th><th>70</th><th>70-(Ολ+ΠΖ) <strong>(A)</strong></th>";
    echo "<th>+/- 08,11,<br>79,91 <strong>(B)</strong></th><th>A+B</th>";
    echo "</tr>";
    echo "</thead>\n<tbody>\n";
    while ($i < $num)
    //while ($i < 4) // for testing
    {		
        $sch = mysql_result($result, $i, "id");
        $name = getSchool($sch, $mysqlconnection);
        $code = mysql_result($result, $i, "code");
        $organikothta = mysql_result($result, $i, "organikothta");
        
        // check if leit < 4. If yes, skip
        $tmimata_exp = explode(",",mysql_result($result, $i, "tmimata"));
        $leit = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
        // if oligothesio, skip
        if ($leit < 4) {
            $i++;
            continue;
        }
        
        // call ektimhseis_wrwn function
        $results = ektimhseis_wrwn($sch, $mysqlconnection, $sxol_etos);
        // required
        $req = $results['required'];
        // available hours
        $av = $results['available'];
        // hour diffs (+/-)
        $df = $results['diff'];
        if (!$df) {
            $i++;
            continue;
        }
        // count pe70
        /*
        $count70 = 0;
        $qry = "SELECT k.perigrafh as klados, count(k.perigrafh) as count FROM employee e join yphrethsh y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status=1 AND e.thesi in (0,1) AND e.klados=2";
        $res = mysql_query($qry, $mysqlconnection);
        while ($row = mysql_fetch_array($res)){
            $count70 = $row['count'];
        }
        */
        // τοποθετηθέντες ΠΕ70
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_yphrethshs = $sch AND klados=2 AND status=1 AND thesi IN (0,1,2)";
        $rs = mysql_query($qry, $mysqlconnection);
        $top70 = mysql_result($rs, 0, "cnt");
        $syntop70 += $top70;
        //
        echo "<tr>";
        echo "<td>$code</td>";
        echo "<td><a href='../school/school_status.php?org=$sch' target='_blank'>$name</a></td>";
        echo "<td>$organikothta</td>";
        echo "<td>".$results['leit']."</td>";
        echo "<td>$top70</td>";
        // new
        echo "<td>".($results['leit']*30)."</td>"; //wres pr.
        $OP = $req['O'] + $req['P'];
        echo "<td>".$OP."</td>"; // olohm + PZ
        //$diffOP = $df['70']-$df['OP'];
        //echo "<td>".$diffOP."</td>"; // olohm
        echo "<td>".($results['leit']*30+$OP)."</td>"; //synolo wrwn
        echo "<td>".($av['06']+$av['11']+$av['79'])."</td>"; // yparx. 08,11,79
        echo "<td>".($df['05-07']+$df['06']+$df['86'])."</td>"; // apait. 05-07,06,86
        echo "<td>".($df['08']+$df['11']+$df['79']+$df['91'])."</td>"; // apait. 08,11,79,91
        //
        $telPE70 = $df['70']-$OP;
        echo "<td>".(int)$av['05-07']."</td><td>".(int)$av['06']."</td><td>".(int)$av['08']."</td><td>".(int)$av['11']."</td><td>".(int)$av['79']."</td><td>".(int)$av['91']."</td><td>".(int)$av['86']."</td><td>".(int)$av['70']."</td>";
        echo tdc1($df['05-07']).tdc1($df['06']).tdc1($df['08']).tdc1($df['11']).tdc1($df['79']).tdc1($df['91']).tdc1($df['86']).tdc1($df['70']).tdc1($telPE70);
        $koines = $df['08']+$df['11']+$df['79']+$df['91'];
        echo tdc1($koines); // apait. 08,11,79,91
        echo tdc1($telPE70+$koines);
        echo "</tr>\n";


        $par_sum['05-07'] += $av['05-07'];
        $par_sum['06'] += $av['06'];
        $par_sum['08'] += $av['08'];
        $par_sum['11'] += $av['11'];
        $par_sum['79'] += $av['79'];
        $par_sum['91'] += $av['91'];
        $par_sum['86'] += $av['86'];
        $par_sum['70'] += $av['70'];
        
        $df_sum['05-07'] += $df['05-07'];
        $kena_sum['05-07'] += $df['05-07'] < 0 ? $df['05-07'] : 0;
        
        $df_sum['06'] += $df['06'];
        $kena_sum['06'] += $df['06'] < 0 ? $df['06'] : 0;
        
        $df_sum['08'] += $df['08'];
        $kena_sum['08'] += $df['08'] < 0 ? $df['08'] : 0;
        
        $df_sum['11'] += $df['11'];
        $kena_sum['11'] += $df['11'] < 0 ? $df['11'] : 0;
        
        $df_sum['79'] += $df['79'];
        $kena_sum['79'] += $df['79'] < 0 ? $df['79'] : 0;
        
        $df_sum['91'] += $df['91'];
        $kena_sum['91'] += $df['91'] < 0 ? $df['91'] : 0;
        
        $df_sum['86'] += $df['86'];
        $kena_sum['86'] += $df['86'] < 0 ? $df['86'] : 0;
        
        $df_sum['70'] += $df['70'];
        $kena_sum['70'] += $df['70'] < 0 ? $df['70'] : 0;
        
        $df_sum['OP'] += $df['OP'];

        $i++;                        
    } // of while

    // convert hour sums to teachers (23 hours each)
    $par_sum_t = array_map(hours_to_teachers, $par_sum);
    $df_sum_t = array_map(hours_to_teachers, $df_sum);
    $kena_sum_t = array_map(hours_to_teachers, $kena_sum);
    
    echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td colspan=3>ΣΥΝΟΛΑ</td>";
    echo "<td>".$par_sum['05-07']."</td><td>".$par_sum['06']."</td><td>".$par_sum['08']."</td><td>".$par_sum['11']."</td><td>".$par_sum['79']."</td><td>".$par_sum['91']."</td><td>".$par_sum['86']."</td><td>".$par_sum['70']."</td>\n";
    echo "<td>".$df_sum['05-07']."</td><td>".$df_sum['06']."</td><td>".$df_sum['08']."</td><td>".$df_sum['11']."</td><td>".$df_sum['79']."</td><td>".$df_sum['91']."</td><td>".$df_sum['86']."</td><td>".$df_sum['70']."</td><td>".$df_sum['OP']."</td><td></td><td></td>\n";
    
    echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td colspan=3>ΣΥΝΟΛΑ (εκπ)</td>";
    echo "<td>".$par_sum_t['05-07']."</td><td>".$par_sum_t['06']."</td><td>".$par_sum_t['08']."</td><td>".$par_sum_t['11']."</td><td>".$par_sum_t['79']."</td><td>".$par_sum_t['91']."</td><td>".$par_sum_t['86']."</td><td>".$par_sum_t['70']."</td>\n";
    echo "<td>".$df_sum_t['05-07']."</td><td>".$df_sum_t['06']."</td><td>".$df_sum_t['08']."</td><td>".$df_sum_t['11']."</td><td>".$df_sum_t['79']."</td><td>".$df_sum_t['91']."</td><td>".$df_sum_t['86']."</td><td>".$df_sum_t['70']."</td><td>".$df_sum_t['OP']."</td><td></td><td></td>\n";
    
    echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td>";
    echo "<td></td><td></td><td colspan=3>MONO KENA</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
    echo "<td>".$kena_sum['05-07']."</td><td>".$kena_sum['06']."</td><td>".$kena_sum['08']."</td><td>".$kena_sum['11']."</td><td>".$kena_sum['79']."</td><td>".$kena_sum['91']."</td><td>".$kena_sum['86']."</td><td>".$kena_sum['70']."</td><td></td><td></td><td></td>\n";

    echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td>";
    echo "<td></td><td></td><td colspan=3>MONO KENA (εκπ)</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
    echo "<td>".$kena_sum_t['05-07']."</td><td>".$kena_sum_t['06']."</td><td>".$kena_sum_t['08']."</td><td>".$kena_sum_t['11']."</td><td>".$kena_sum_t['79']."</td><td>".$kena_sum_t['91']."</td><td>".$kena_sum_t['86']."</td><td>".$kena_sum_t['70']."</td><td></td><td></td><td></td>\n";
    
    echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td>";
    echo "<td></td><td></td><td></td><td></td><td></td>";
    echo "<td><i>05-07</i></td><td><i>06</i></td><td><i>08</i></td><td><i>11</i></td><td><i>79</i></td><td><i>91</i></td><td><i>86</i></td><td><i>70</i></td>";
    echo "<td><i>05-07</i></td><td><i>06</i></td><td><i>08</i></td><td><i>11</i></td><td><i>79</i></td><td><i>91</i></td><td><i>86</i></td><td><i>70</i></td><td><i>70-(Ολ+ΠΖ)</i></td><td></td><td></td></i>";
    echo "</tr>";
    echo "</tbody></table>";
    echo "<br>";

    $page = ob_get_contents(); 
    $_SESSION['page'] = $page;
    ob_end_flush();

    echo "<form action='../tools/2excel_ses.php' method='post'>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
    echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "</form>";
    //ob_end_clean();
?>
		</body>
		</html>
                