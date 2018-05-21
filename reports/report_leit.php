<?php
	//header('Content-type: text/html; charset=iso8859-7'); 
	//require_once "../tools/functions.php";
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <title>Υπολογισμός Κενών</title>
    <!--
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    -->
	
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
        
        echo "<body>";
        echo "<table class=\"imagetable\" border='1'>";
        echo "<form action='' method='POST' autocomplete='off'>";
        echo "<tr><td colspan=2>";
        echo "<input type='radio' name='type' value='1' checked >Δημόσια Δημοτικά (όχι ειδικά)<br>";
        echo "<input type='radio' name='type' value='2' >Ιδιωτικά Δημοτικά<br>";
        echo "<input type='radio' name='type' value='3' >Ειδικά Δημοτικά<br>";
        echo "<input type='radio' name='type' value='4' >Δημόσια Νηπιαγωγεία (όχι ειδικά)<br>";
        echo "<input type='radio' name='type' value='5' >Ιδιωτικά Νηπιαγωγεία<br>";
        echo "<input type='radio' name='type' value='6' >Ειδικά Νηπιαγωγεία<br>";
        echo "</td></tr>";
        echo "<tr><td colspan=2><input type='submit' value='Προβολή'></td></tr>";
        echo "</table></form></head>";
         
	$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
	mysql_select_db($db_name, $mysqlconnection);
	mysql_query("SET NAMES 'greek'", $mysqlconnection);
	mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);

    if ($_POST['type']){
        $dim_ar = array('1', '2', '3');
        $nip_ar = array('4', '5', '6');
	//if ($_GET['type'] == 1)
        if (in_array($_POST['type'],$dim_ar))
        {
            $type = 1;
            //type2: 0 δημόσιο, 1 ιδιωτικό, 2 ειδικό
            $type2 = $_POST['type'] - 1;
            
            $query = "SELECT * from school WHERE type = $type AND type2=$type2";
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);
        
                echo "<center>";
                $i=0;
                ob_start();
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                    echo "<thead>";
//                    echo "<tr><td>aaa</td></tr>";
                    echo "<tr><th rowspan=2>Ονομασία</th>";
                    //echo "<th colspan=4>Οργανικά Κενά</th>";
                    echo "<th colspan=4>Λειτουργικά Κενά</th>";
                    echo "</tr>";
                    echo "<th>ΠΕ60/70</th>";
                    //echo "<th>KENA</th><th>Διαφορα</th><th>org&anhk&energoi</th><th>tmimata (dntis) - tm.ent</th>";
                    echo "<th>KENA (από υπολογισμό)</th><th>Διαφορα</th>";
                    echo "</tr>";
                    echo "</thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        $students = mysql_result($result, $i, "students");
                        $oloimero = mysql_result($result, $i, "oloimero");
                        $oloimero_tea = mysql_result($result, $i, "oloimero_tea");
                        $kena_leit = unserialize(mysql_result($result, $i, "kena_leit"));
                        $tmim = mysql_result($result, $i, "tmimata");
                        $tmimata_exp = explode(",",$tmim);
                        $tmimata = array_sum($tmimata_exp);
                        $ty = mysql_result($result, $i, "ypodoxis");
                        $ekp_ee = mysql_result($result, $i, "ekp_ee");
                        $ekp_ee_exp = explode(",",$ekp_ee);
                        $ekp_ty = $ekp_ee_exp[1];
                        
                                                
                        $qry = "SELECT * FROM employee WHERE sx_organikhs = $sch AND sx_yphrethshs = $sch AND klados = 2 AND status = 1 AND thesi IN (0,1)";
                        $res = mysql_query($qry, $mysqlconnection);
                        $organhk =  mysql_num_rows($res);
                        // anaplhrwtes
                        $qry2 = "SELECT * FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados = 2 AND type = 2";
                        $res2 = mysql_query($qry2, $mysqlconnection);
                        $anapl =  mysql_num_rows($res2);
                        // apo alla pyspe h alla sxoleia
                        $qry3 = "SELECT * FROM employee WHERE sx_organikhs != $sch AND sx_yphrethshs = $sch AND klados = 2 AND status = 1 AND thesi IN (0,1)";
                        $res3 = mysql_query($qry3, $mysqlconnection);
                        $aposp =  mysql_num_rows($res3);
                        // + D/nths
                        if ($tmimata == 4 || $tmimata == 5)
                        {
                            $qry1 = "SELECT * FROM employee WHERE sx_yphrethshs = $sch AND klados = 2 AND status = 1 AND thesi = 2";
                            $res1 = mysql_query($qry1, $mysqlconnection);
                            $dntis =  mysql_num_rows($res1);
                            if ($dntis > 0)
                                $organhk += 1;
                        }
                        // + Olohmero
                        if ($oloimero)
                            $tmimata += $oloimero_tea;
                        // + Ypodoxis
                        if ($ty)
                            $tmimata += $ekp_ty;
                        //$kena = $tmimata - $organhk;
                        //$kena = $organhk - $tmimata;
                        //$kena_abs = abs($tmimata - $organhk);
                        $kena_abs = abs($tmimata - $organhk - $anapl - $aposp);
                        $kena = $organhk + $anapl +$aposp - $tmimata;
                        $diafora = '';
                        if ($kena_abs != $kena_leit[0])
                            $diafora = "Διαφορά";
                        
                        echo "<tr>";
                        echo "<td><a href='../school/school_status.php?org=$sch'>$name</a></td>";
                        //echo "<td>$kena_leit[0]</td><td>$kena_leit[1]</td><td>$kena_leit[2]</td><td>$kena_leit[3]</td>\n";
                        echo "<td>$kena_leit[0]</td>\n";
                        //echo "<td>$kena</td><td>$diafora</td><td>$organhk</td><td>$tmimata ($dntis) - $ty</td>\n";
                        echo "<td>$kena</td><td>$diafora";
                        if ($diafora)
                            echo "  (Τμ:$tmimata, Οργ.Ανηκ:$organhk, Αναπλ:$anapl, Αποσπ:$aposp)";
                        echo "</td>\n";
                        echo "</tr>\n";
                        
                        $kena_leit_sum += $kena;
                        $synolo_anapl += $anapl;
                        $i++;                        
                }
        //}	
                echo "<tr><td>ΣΥΝΟΛΑ</td>";
                echo "<td></td><td>$kena_leit_sum</td><td>$synolo_anapl</td></tr>";
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
        //else if ($_GET['type'] == 2)
        else if (in_array($_POST['type'],$nip_ar))
        {
            //nipiagogeia
            $type = 2;
            //type2: 0 δημόσιο, 1 ιδιωτικό, 2 ειδικό
            $type2 = $_POST['type'] - 4;
            
            $query = "SELECT * from school WHERE type = $type AND type2=$type2";
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);
        
                echo "<center>";
                $i=0;
                ob_start();
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                    echo "<thead>";
                    echo "<tr><th rowspan=2>Ονομασία</th>";
                    echo "<th colspan=4>Λειτουργικά Κενά</th>";
                    echo "</tr>";
                    echo "<th>ΠΕ60</th>";
                    //echo "<th>KENA</th><th>Διαφορα</th><th>org&anhk&energoi</th><th>tmimata (dntis) - tm.ent</th>";
                    echo "<th>KENA (από υπολογισμό)</th><th>Διαφορά</th>";
                    echo "</tr>";
                    echo "</thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        //$students = mysql_result($result, $i, "students");
                        //$oloimero = mysql_result($result, $i, "oloimero");
                        //$oloimero_tea = mysql_result($result, $i, "oloimero_tea");
                        $kena_leit = unserialize(mysql_result($result, $i, "kena_leit"));
                        $nip = mysql_result($result, $i, "nip");
                        $nip_exp = explode(",",$nip);
                        $tmimata = $nip_exp[0]+$nip_exp[1];
                        
                                                
                        $qry = "SELECT * FROM employee WHERE sx_organikhs = $sch AND sx_yphrethshs = $sch AND klados = 1 AND status = 1 AND thesi IN (0,1)";
                        $res = mysql_query($qry, $mysqlconnection);
                        $organhk =  mysql_num_rows($res);
                        // anaplhrwtes
                        $qry2 = "SELECT * FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados = 1 AND type = 2";
                        $res2 = mysql_query($qry2, $mysqlconnection);
                        $anapl =  mysql_num_rows($res2);
                        // apo alla pyspe h alla sxoleia
                        $qry3 = "SELECT * FROM employee WHERE sx_organikhs != $sch AND sx_yphrethshs = $sch AND klados = 1 AND status = 1 AND thesi IN (0,1)";
                        $res3 = mysql_query($qry3, $mysqlconnection);
                        $aposp =  mysql_num_rows($res3);
                        //
                        //$kena_abs = abs($tmimata - $organhk);
                        $kena_abs = abs($tmimata - $organhk - $anapl - $aposp);
                        //$kena = $tmimata - $organhk;
                        //$kena = $organhk - $tmimata;
                        $kena = $organhk + $anapl + $aposp - $tmimata;
                        $diafora = '';
                        if ($kena_abs != $kena_leit[0])
                            $diafora = "Διαφορά";
                        
                        echo "<tr>";
                        echo "<td><a href='../school/school_status.php?org=$sch'>$name</a></td>";
                        //echo "<td>$kena_leit[0]</td><td>$kena_leit[1]</td><td>$kena_leit[2]</td><td>$kena_leit[3]</td>\n";
                        echo "<td>$kena_leit[0]</td>\n";
                        //echo "<td>$kena</td><td>$diafora</td><td>$organhk</td><td>$tmimata ($dntis) - $ty</td>\n";
                        echo "<td>$kena</td><td>$diafora</td>\n";
                        echo "</tr>\n";
                        
                        $kena_leit_sum += $kena;
                        
                        $i++;                        
                }
        //}	
                echo "<tr><td>ΣΥΝΟΛΑ</td>";
                echo "<td></td><td>$kena_leit_sum</td><td></td></tr>";
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
    }
?>
                       
  
		</body>
		</html>
                