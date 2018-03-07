<?php
	//header('Content-type: text/html; charset=iso8859-7'); 
	//require_once "../tools/functions.php";
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    
        <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
        <title>Λειτουργικά Κενά Ειδικοτήτων</title>
	
        <script type="text/javascript" src="../js/jquery-1.2.1.pack.js"></script>
        <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
	<script type="text/javascript">	
	$(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		}); 
	
	</script>
  </head>

<?php
	require_once"../config.php";
	require_once"../tools/functions.php";
        session_start();
        
 
	$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
	mysql_select_db($db_name, $mysqlconnection);
	mysql_query("SET NAMES 'greek'", $mysqlconnection);
	mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
	
	if ($_GET['klados']>0)
        {
            $klados = $_GET['klados'];
            $type = 1;
            $syn_wres_apait = 0;
            $query = "SELECT * from school WHERE type = $type and leitoyrg >= 4 AND eaep=0";
            
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);
        
                echo "<body>";
                if ($klados == 5)
                    echo "<a href=\"report_kena_eid.php?klados=3\"><h4>Για Κενά ΠΕ06 - Αγγλικών πιέστε εδώ</h4></a>";
                else
                    echo "<a href=\"report_kena_eid.php?klados=5\"><h4>Για Κενά ΠΕ11 - Φυσικής Αγωγής πιέστε εδώ</h4></a>";
                echo "<center>";
                $i=0;
                ob_start();
                
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                    echo "<thead>";
                    switch ($klados){
                        case 3:
                            echo "<tr><th colspan=5>ΠΕ06 - Αγγλικών</td></tr>";
                            break;
                        case 5:
                            echo "<tr><th colspan=5>ΠΕ11 - Φυσ. Αγωγής</td></tr>";
                            break;
//                        case 6:
//                            echo "ΠΕ16 - Μουσικής";
//                            break;
                    }
                    echo "<tr><th rowspan=2>Ονομασία</th>";
                    echo "<th colspan=4>Λειτουργικά Κενά</th>";
                    echo "</tr>";
                    
                    echo "<tr></tr>";
                    echo "<tr><small><th></th><th>Οργαν.</th><th>Απαιτ.</th><th>Διαθέσιμες</th></small>";
                    echo "<th>+/-</th>";
                    echo "</tr>";
                    echo "</thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        $students = mysql_result($result, $i, "students");
                        $kena_org = unserialize(mysql_result($result, $i, "kena_org"));
                        $kena_leit = unserialize(mysql_result($result, $i, "kena_leit"));
                        $organikes = unserialize(mysql_result($result, $i, "organikes"));
                        //$organikes11 = 1 / $organikes06 = 2
                        $pe06org = $organikes[2];
                        $pe11org = $organikes[1];
                        if ($klados == 3)
                            $organikes = $pe06org;
                        elseif ($klados == 5)
                            $organikes = $pe11org;
                        
                        $tm = explode(',',mysql_result($result, $i, "tmimata"));
                        $pe06wr = ($tm[2]+$tm[3]+$tm[4]+$tm[5])*3;
                        $pe11wr = array_sum($tm)*2;
                        $kladosqry = "SELECT count(*) as a FROM yphrethsh y JOIN employee e ON e.id=y.emp_id WHERE sxol_etos=".$sxol_etos." AND yphrethsh = ".$sch." AND e.klados = ".$klados." AND e.status = 1";

                        $res = mysql_query($kladosqry, $mysqlconnection);
                        $yphret = mysql_result($res, 0);
                        
                        $ekp_name = "";
                        $has_wres = $mon_wres = 0;
                        if ($yphret > 0){
                            $pe_ekp = "SELECT e.wres,e.id,e.surname,e.name,e.hm_dior,e.proyp,y.hours FROM yphrethsh y JOIN employee e ON e.id=y.emp_id WHERE sxol_etos=".$sxol_etos." AND yphrethsh = ".$sch." AND e.klados = ".$klados." AND e.status = 1";
                            $res1 = mysql_query($pe_ekp, $mysqlconnection);
                                                        
                            while ($row = mysql_fetch_array($res1, MYSQL_BOTH)){
                                //$has_wres = $row['wres'];
                                $has_wres = $row['hours'];
                                $ekp_name .= "$has_wres (<small><a href=\"employee.php?id=".  $row['id'] ."&op=view\">". $row['surname'] ." ". $row['name'] .")</a></small><br>";
                                $mon_wres += $has_wres;
                            }

                            //$has_wres = mysql_result($res1, 0,"hours");
                            //$ekp_name = "$has_wres (<small><a href=\"employee.php?id=".  mysql_result($res1,0,"id")."&op=view\">".mysql_result($res1, 0, "surname")." ".mysql_result($res1, 0, "name").")</a></small>";
                        }
                        if ($klados == 3)
                            $pewr = $pe06wr;
                        elseif ($klados == 5)
                            $pewr = $pe11wr;
                        $wres_apait = $mon_wres - $pewr;

                        echo "<tr>";
                        echo "<td><a href='../school/school_status.php?org=$sch'>$name</a></td>";
                        //echo "<td>$organikes<td>$pewr</td><td>$ekp_name</td><td>$wres_apait</td>\n";
                        if ($wres_apait >= 0)
                            echo "<td>$organikes</td><td>$pewr</td><td>$ekp_name</td><td style='background:none;background-color:#00FF00'>$wres_apait</td>\n";
                        else
                            echo "<td>$organikes</td><td>$pewr</td><td>$ekp_name</td><td style='background:none;background-color:#FF0000'>$wres_apait</td>\n";
                        $syn_wres_apait += $wres_apait;
                        echo "</tr>\n";
                                            
                        $i++;                        
                }
                echo "<tr><td colspan=4><strong>ΣΥΝΟΛΟ</strong></td><td>$syn_wres_apait</td></tr>";
                echo "</tbody></table>";

                $page = ob_get_contents(); 
                $_SESSION['page'] = $page;
                ob_end_flush();

                echo "<form action='../tools/2excel_ses.php' method='post'>";
                //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
                echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
                echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
                echo "</form>";
                //ob_end_clean();
	}
?>
                  
		</body>
		</html>
                