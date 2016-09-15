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
    
    // wres anaplhrwtwn
    $anapl70 = [
        '9170039' => '6',
        '9170270' => '0',
        '9170017' => '3',
        '9521091' => '0',
        '9170012' => '1',
        '9170041' => '2',
        '9170006' => '2',
        '9170270' => '0',
        '9170200' => '0',
        '9521412' => '1',
        '9170132' => '2',
        '9170144' => '6',
        '9170561' => '2',
        '9170097' => '6',
        '9170549' => '0',
        '9170068' => '11',
        '9170009' => '5',
        '9170309' => '4',
        '9170120' => '0',
        '9170279' => '4',
        '9170580' => '1',
        '9170327' => '2',
        '9170018' => '4',
        '9170560' => '0',
        '9521411' => '0',
        '9170282' => '5',
        '9170002' => '3',
        '9170219' => '0',
        '9170205' => '0',
        '9170061' => '0',
        '9170222' => '0',
        '9170224' => '4',
        '9170285' => '6',
        '9170107' => '1',
        '9170423' => '0',
        '9170102' => '0',
        '9170254' => '0',
        '9170259' => '0',
        '9170058' => '1',
        '9170190' => '1',
        '9170191' => '0',
        '9170192' => '0',
        '9521093' => '0',
        '9170197' => '0',
        '9170194' => '0',
        '9170055' => '0',
        '9170425' => '0',
        '9521092' => '1',
        '9170056' => '0',
        '9170428' => '2',
        '9170057' => '0',
        '9170431' => '1',
        '9170512' => '1',
        '9170514' => '0',
        '9170517' => '0',
        '9170515' => '5',
        '9170516' => '0',
        '9170525' => '0',
        '9170532' => '0',
        '9170533' => '0',
        '9170534' => '0',
        '9170575' => '0',
        '9170345' => '1',
        '9170228' => '1',
        '9170020' => '1',
        '9170495' => '4',
        '9170235' => '5',
        '9170206' => '0',
        '9170091' => '0',
        '9170027' => '1',
        '9170028' => '4',
        '9170239' => '7',
        '9521015' => '2',
        '9170242' => '5',
        '9521014' => '2',
        '9170314' => '4',
        '9170079' => '6',
        '9170267' => '5',
        '9170268' => '2',
        '9170244' => '6',
        '9170264' => '0',
        '9170262' => '1',
        '9170520' => '',
        '9170263' => '4',
        '9170427' => '1',
        '9170336' => '4',
        '9170300' => '4',
        '9170126' => '0',
        '9170319' => '3',
        '9170034' => '1',
        '9170036' => '1',
        '9170086' => '4',
        '9170037' => '0',
        '9170338' => '3',
        '9170339' => '3',
        '9170536' => '7',
        '9170326' => '5',
        '9170095' => '0',
        // oligo
        '9170117' => '2',
        '9170129' => '1',
        '9170274' => '3',
        '9170130' => '0',
        '9170008' => '2',
        '9170284' => '2',
        '9170173' => '2',
        '9170340' => '1',
        '9170330' => '3',
        '9170123' => '2',
        '9170290' => '1',
        '9170030' => '1',
        '9170289' => '3',
        '9170246' => '1',
        '9170307' => '2',
        '9170170' => '2',
        '9170304' => '2',
        '9170085' => '2',
        '9170325' => '2',
        '9170067' => '3',
        '9170038' => '2',
        '9170383' => '2'
    ];
    //print_r($anapl70);
    // only dhmosia (type2 = 0)
    $query = "SELECT * from school WHERE type2 = 0 AND type = 1 AND anenergo=0";
    $result = mysql_query($query, $mysqlconnection);
    $num = mysql_num_rows($result);

        echo "<body>";
        echo "<h3>Λειτουργικά κενά <strong>(Μόνο για 09/09/2016 - με τοποθετημένους αναπληρωτές α' φάσης)</strong></h3>";
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
            echo "<th rowspan=2>Πρ.</th>";
            echo "<th rowspan=2>Ολ.+ΠΖ</th>";
            echo "<th rowspan=2>Συν. Ωρ.</th>";
            echo "<th rowspan=2>Yπαρ. Ωρ.06,<br>11,16</th>";
            //echo "<th rowspan=2>+/- 05-07,<br>06,19-20</th>";
            //
            echo "<th colspan=8>Υπάρχουν +/- <small>(με Δ/ντή, σε ώρες)</small></th>";
            echo "<th colspan=11>Λειτουργικά Κενά +/- <small>(με Δ/ντή, σε ώρες)</small></th>";
            echo "</tr>";
            echo "<th>05-07</th><th>06</th><th>08</th><th>11</th><th>16</th><th>32</th><th>19-20</th><th>70</th>";
            echo "<th>05-07</th><th>06</th><th>08</th><th>11</th><th>16</th><th>32</th><th>19-20</th><th>70</th><th>70-(Ολ+ΠΖ)</th>";
            echo "<th>+/- 08,11,<br>16,32</th>";
            echo "</tr>";
            echo "</thead>\n<tbody>\n";
        while ($i < $num)
        //while ($i < 10)
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
                /*$count70 = 0;
                $qry = "SELECT k.perigrafh as klados, count(k.perigrafh) as count FROM employee e join yphrethsh y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status=1 AND e.thesi in (0,1) AND e.klados=2";
                $res = mysql_query($qry, $mysqlconnection);
                while ($row = mysql_fetch_array($res)){
                    $count70 = $row['count'];
                }
                //*/
                $df['70'] += ($anapl70[$code]*24);
                                
                echo "<tr>";
                echo "<td>$code</td>";
                echo "<td><a href='school_status.php?org=$sch' target='_blank'>$name</a></td>";
                echo "<td>$organikothta</td>";
                echo "<td>".$results['leit']."</td>";
                // new
                echo "<td>".($results['leit']*30)."</td>"; //wres pr.
                $OP = $req['O'] + $req['P'];
                echo "<td>".$OP."</td>"; // olohm + PZ
                echo "<td>".($results['leit']*30+$OP)."</td>"; //synolo wrwn
                echo "<td>".($av['06']+$av['11']+$av['16'])."</td>"; // yparx. 08,11,16
                //echo "<td>".($df['05-07']+$df['06']+$df['19-20'])."</td>"; // apait. 05-07,06,19-20
                //
                echo "<td>".(int)$av['05-07']."</td><td>".(int)$av['06']."</td><td>".(int)$av['08']."</td><td>".(int)$av['11']."</td><td>".(int)$av['16']."</td><td>".(int)$av['32']."</td><td>".(int)$av['19-20']."</td><td>".(int)$av['70'];
                echo $anapl70[$code] ? "<strong> +$anapl70[$code]</strong>" : "";
                echo "</td>";
                $dnthrs = wres_dnth($results['leit']);
                echo tdc($df['05-07']).tdc($df['06']).tdc($df['08']).tdc($df['11']).tdc($df['16']).tdc($df['32']).tdc($df['19-20']).tdc($df['70']).tdc($df['70']-$OP);
                echo tdc($df['08']+$df['11']+$df['16']+$df['32']); // apait. 08,11,16,32
                echo "</tr>\n";

                $par_sum['05-07'] += $av['05-07'];
                $par_sum['06'] += $av['06'];
                $par_sum['08'] += $av['08'];
                $par_sum['11'] += $av['11'];
                $par_sum['16'] += $av['16'];
                $par_sum['32'] += $av['32'];
                $par_sum['19-20'] += $av['19-20'];
                $par_sum['70'] += $av['70'];
                
                $df_sum['05-07'] += $df['05-07'];
                $df_sum['06'] += $df['06'];
                $df_sum['08'] += $df['08'];
                $df_sum['11'] += $df['11'];
                $df_sum['16'] += $df['16'];
                $df_sum['32'] += $df['32'];
                $df_sum['19-20'] += $df['19-20'];
                $df_sum['70'] += $df['70'];
                //$df_sum['70d'] += ($df['70']+$dnthrs);
                $df_sum['OP'] += $df['OP'];

                $i++;                        
        }
        echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td colspan=2>ΣΥΝΟΛΑ</td>";
        echo "<td>".$par_sum['05-07']."</td><td>".$par_sum['06']."</td><td>".$par_sum['08']."</td><td>".$par_sum['11']."</td><td>".$par_sum['16']."</td><td>".$par_sum['32']."</td><td>".$par_sum['19-20']."</td><td>".$par_sum['70']."</td>\n";
        echo "<td>".$df_sum['05-07']."</td><td>".$df_sum['06']."</td><td>".$df_sum['08']."</td><td>".$df_sum['11']."</td><td>".$df_sum['16']."</td><td>".$df_sum['32']."</td><td>".$df_sum['19-20']."</td><td>".$df_sum['70']."</td><td>".$df_sum['OP']."</td><td></td>\n";
        echo "<tr><td></td><td></td><td></td><td></td>";
        //
        echo "<td></td><td></td><td></td><td></td>";
        //
        echo "<td><i>05-07</i></td><td><i>06</i></td><td><i>08</i></td><td><i>11</i></td><td><i>16</i></td><td><i>32</i></td><td><i>19-20</i></td><td><i>70</i></td>";
        echo "<td><i>05-07</i></td><td><i>06</i></td><td><i>08</i></td><td><i>11</i></td><td><i>16</i></td><td><i>32</i></td><td><i>19-20</i></td><td><i>70</i></td><td><i>70-(Ολ+ΠΖ)</i></td></i><td></td>";
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
                