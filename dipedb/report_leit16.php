<html>
  <head>
	<LINK href="style.css" rel="stylesheet" type="text/css">
    <!--
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    -->
	<script type="text/javascript" src="js/jquery-1.2.1.pack.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.js"></script>
	<script type="text/javascript">	
	$().ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
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
    function tdc($val){
        return $val >= 0 ? "<td style='background:none;background-color:rgba(0, 255, 0, 0.37)'>$val</td>" : "<td style='background:none;background-color:rgba(255, 0, 0, 0.45)'>$val</td>";
    }
    require_once"config.php";
    require_once"functions.php";
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
        echo "<center>";
        $i=0;
        ob_start();
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
            echo "<thead>";
            echo "<tr><th rowspan=2>Κωδ.</th>";
            echo "<th rowspan=2>Ονομασία</th>";
            echo "<th rowspan=2>Οργ.</th>";
            echo "<th rowspan=2>Λειτ.</th>";
            // new
            echo "<th rowspan=2>Ωρ. Πρ.</th>";
            echo "<th rowspan=2>Ωρ. Ολ.</th>";
            echo "<th rowspan=2>Συν. Ωρ.</th>";
            echo "<th rowspan=2>Yπαρ. Ωρ.06,<br>11,16</th>";
            echo "<th rowspan=2>+/- 05-07,<br>06,19-20</th>";
            echo "<th rowspan=2>+/- 08,11,<br>16,32</th>";
            //
            echo "<th colspan=8>Υπάρχουν +/- <small>(με Δ/ντή, σε ώρες)</small></th>";
            echo "<th colspan=11>Λειτουργικά Κενά +/- <small>(σε ώρες)</small></th>";
            echo "</tr>";
            echo "<th>05-07</th><th>06</th><th>08</th><th>11</th><th>16</th><th>32</th><th>19-20</th><th>70</th>";
            echo "<th>05-07</th><th>06</th><th>08</th><th>11</th><th>16</th><th>32</th><th>19-20</th><th>70</th><th>70-(Ολ+ΠΖ) <strong>(A)</strong></th>";
            echo "<th>+/- 08,11,<br>16,32 <strong>(B)</strong></th><th>A+B</th>";
            echo "</tr>";
            echo "</thead>\n<tbody>\n";
        while ($i < $num)
        //while ($i < 4)
        {		
                $sch = mysql_result($result, $i, "id");
                $name = getSchool($sch, $mysqlconnection);
                $code = mysql_result($result, $i, "code");
                $organikothta = mysql_result($result, $i, "organikothta");
                $results = ektimhseis1617($sch, $mysqlconnection, $sxol_etos);
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
                $count70 = 0;
                $qry = "SELECT k.perigrafh as klados, count(k.perigrafh) as count FROM employee e join yphrethsh y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status=1 AND e.thesi in (0,1) AND e.klados=2";
                $res = mysql_query($qry, $mysqlconnection);
                while ($row = mysql_fetch_array($res)){
                    $count70 = $row['count'];
                }
                //
                echo "<tr>";
                echo "<td>$code</td>";
                echo "<td><a href='school_status.php?org=$sch' target='_blank'>$name</a></td>";
                echo "<td>$organikothta</td>";
                echo "<td>".$results['leit']."</td>";
                // new
                echo "<td>".($results['leit']*30)."</td>"; //wres pr.
                $OP = $req['O'] + $req['P'];
                echo "<td>".$OP."</td>"; // olohm + PZ
                //$diffOP = $df['70']-$df['OP'];
                //echo "<td>".$diffOP."</td>"; // olohm
                echo "<td>".($results['leit']*30+$OP)."</td>"; //synolo wrwn
                echo "<td>".($av['06']+$av['11']+$av['16'])."</td>"; // yparx. 08,11,16
                echo "<td>".($df['05-07']+$df['06']+$df['19-20'])."</td>"; // apait. 05-07,06,19-20
                echo "<td>".($df['08']+$df['11']+$df['16']+$df['32'])."</td>"; // apait. 08,11,16,32
                //
                $telPE70 = $df['70']-$OP;
                echo "<td>".(int)$av['05-07']."</td><td>".(int)$av['06']."</td><td>".(int)$av['08']."</td><td>".(int)$av['11']."</td><td>".(int)$av['16']."</td><td>".(int)$av['32']."</td><td>".(int)$av['19-20']."</td><td>".(int)$av['70']."</td>";
                echo tdc($df['05-07']).tdc($df['06']).tdc($df['08']).tdc($df['11']).tdc($df['16']).tdc($df['32']).tdc($df['19-20']).tdc($df['70']).tdc($telPE70);
                $koines = $df['08']+$df['11']+$df['16']+$df['32'];
                echo tdc($koines); // apait. 08,11,16,32
                echo tdc($telPE70+$koines);
                echo "</tr>\n";

                $par_sum['05-07'] += $av['05-07'];
                $par_sum['06'] += $av['06'];
                $par_sum['08'] += $av['08'];
                $par_sum['11'] += $av['11'];
                $par_sum['16'] += $av['16'];
                $par_sum['32'] += $av['32'];
                $par_sum['19-20'] += $av['1920'];
                $par_sum['70'] += $av['70'];
                
                $df_sum['05-07'] += $df['05-07'];
                $df_sum['06'] += $df['06'];
                $df_sum['08'] += $df['08'];
                $df_sum['11'] += $df['11'];
                $df_sum['16'] += $df['16'];
                $df_sum['32'] += $df['32'];
                $df_sum['19-20'] += $df['19-20'];
                $df_sum['70'] += $df['70'];
                $df_sum['OP'] += $df['OP'];

                $i++;                        
        }
        echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td colspan=2>ΣΥΝΟΛΑ</td>";
        echo "<td>".$par_sum['05-07']."</td><td>".$par_sum['06']."</td><td>".$par_sum['08']."</td><td>".$par_sum['11']."</td><td>".$par_sum['16']."</td><td>".$par_sum['32']."</td><td>".$par_sum['19-20']."</td><td>".$par_sum['70']."</td>\n";
        echo "<td>".$df_sum['05-07']."</td><td>".$df_sum['06']."</td><td>".$df_sum['08']."</td><td>".$df_sum['11']."</td><td>".$df_sum['16']."</td><td>".$df_sum['32']."</td><td>".$df_sum['19-20']."</td><td>".$df_sum['70']."</td><td>".$df_sum['OP']."</td><td></td><td></td>\n";
        echo "<tr><td></td><td></td><td></td><td></td><td></td>";
        //
        echo "<td></td><td></td><td></td><td></td><td></td>";
        //
        echo "<td><i>05-07</i></td><td><i>06</i></td><td><i>08</i></td><td><i>11</i></td><td><i>16</i></td><td><i>32</i></td><td><i>19-20</i></td><td><i>70</i></td>";
        echo "<td><i>05-07</i></td><td><i>06</i></td><td><i>08</i></td><td><i>11</i></td><td><i>16</i></td><td><i>32</i></td><td><i>19-20</i></td><td><i>70</i></td><td><i>70-(Ολ+ΠΖ)</i></td><td></td><td></td></i>";
        echo "</tr>";
        echo "</tbody></table>";
        echo "<br>";

        $page = ob_get_contents(); 
        $_SESSION['page'] = $page;
        ob_end_flush();

        echo "<form action='2excel_ses.php' method='post'>";
        echo "<BUTTON TYPE='submit'><IMG SRC='images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
        echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='index.php'\">";
        echo "</form>";
        //ob_end_clean();
?>
		</body>
		</html>
                