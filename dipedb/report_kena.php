<?php
	//header('Content-type: text/html; charset=iso8859-7'); 
	//require_once "functions.php";
?>
<html>
  <head>
	<LINK href="style.css" rel="stylesheet" type="text/css">
    <!--
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    -->
	
        <script type="text/javascript" src="js/jquery-1.2.1.pack.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
	<script type="text/javascript">	
	$(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		}); 
	
	</script>
  </head>

<?php
	require_once"config.php";
	require_once"functions.php";
        session_start();
        
	$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
	mysql_select_db($db_name, $mysqlconnection);
	mysql_query("SET NAMES 'greek'", $mysqlconnection);
	mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
	
	if ($_GET['type'] == 1)
        {
            $type = 1;
            // only dhmosia kai eidika (type2 = 0 or 2)
            $query = "SELECT * from school WHERE type2 in (0,2) AND type = $type";
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
                    echo "<th rowspan=2>Κατ.</th>";
                    echo "<th rowspan=2>Οργ.</th>";
                    echo "<th colspan=4>Οργανικές</th>";
                    echo "<th colspan=4>Οργανικά Κενά</th>";
                    echo "<th colspan=4>Λειτουργικά Κενά</th>";
                    echo "</tr>";
                    echo "<tr><th>ΠΕ60/70</th><th>ΠΕ11</th><th>ΠΕ06</th><th>ΠΕ16</th>";
                    echo "<th>ΠΕ60/70</th><th>ΠΕ11</th><th>ΠΕ06</th><th>ΠΕ16</th>";
                    echo "<th>ΠΕ60/70</th><th>ΠΕ11</th><th>ΠΕ06</th><th>ΠΕ16</th>";
                    echo "</tr>";
                    echo "</thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        $code = mysql_result($result, $i, "code");
                        $cat = getCategory(mysql_result($result, $i, "category"));
                        $organikothta = mysql_result($result, $i, "organikothta");
                        $organikes = unserialize(mysql_result($result, $i, "organikes"));
                        $kena_org = unserialize(mysql_result($result, $i, "kena_org"));
                        $kena_leit = unserialize(mysql_result($result, $i, "kena_leit"));

                        echo "<tr>";
                        echo "<td>$code</td>";
                        echo "<td><a href='school_edit.php?org=$sch'>$name</a></td>";
                        echo "<td>$cat</td>";
                        echo "<td>$organikothta</td>";
                        echo "<td>$organikes[0]</td><td>$organikes[1]</td><td>$organikes[2]</td><td>$organikes[3]</td>\n";
                        echo "<td>$kena_org[0]</td><td>$kena_org[1]</td><td>$kena_org[2]</td><td>$kena_org[3]</td>\n";
                        echo "<td>$kena_leit[0]</td><td>$kena_leit[1]</td><td>$kena_leit[2]</td><td>$kena_leit[3]</td>\n";
                        echo "</tr>\n";
                        
                        $organikes_sum[0] += $organikes[0];
                        $organikes_sum[1] += $organikes[1];
                        $organikes_sum[2] += $organikes[2];
                        $organikes_sum[3] += $organikes[3];
                        
                        $kena_org_sum[0] += $kena_org[0];
                        $kena_org_sum[1] += $kena_org[1];
                        $kena_org_sum[2] += $kena_org[2];
                        $kena_org_sum[3] += $kena_org[3];
                        
                        $kena_leit_sum[0] += $kena_leit[0];
                        $kena_leit_sum[1] += $kena_leit[1];
                        $kena_leit_sum[2] += $kena_leit[2];
                        $kena_leit_sum[3] += $kena_leit[3];
                        
                        $i++;                        
                }
        //}	
                echo "<tr><td></td><td></td><td></td><td>ΣΥΝΟΛΑ</td>";
                echo "<td>$organikes_sum[0]</td><td>$organikes_sum[1]</td><td>$organikes_sum[2]</td><td>$organikes_sum[3]</td>";
                echo "<td>$kena_org_sum[0]</td><td>$kena_org_sum[1]</td><td>$kena_org_sum[2]</td><td>$kena_org_sum[3]</td>";
                echo "<td>$kena_leit_sum[0]</td><td>$kena_leit_sum[1]</td><td>$kena_leit_sum[2]</td><td>$kena_leit_sum[3]</td></tr>";
                echo "</tbody></table>";

                $page = ob_get_contents(); 
                $_SESSION['page'] = $page;
                ob_end_flush();

                echo "<form action='2excel_ses.php' method='post'>";
                //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
                echo "<BUTTON TYPE='submit'><IMG SRC='images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
                echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='index.php'\">";
                echo "</form>";
                //ob_end_clean();
                       
	}
        else if ($_GET['type'] == 99)
        {
            $type = 1;
            // only dhmosia kai eidika (type2 = 0 or 2)
            $query = "SELECT * from school WHERE type2 in (0,2) AND type = $type AND organikothta >= 4";
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);
        
                echo "<body>";
                echo "<center>";
                $i=0;
                ob_start();
                echo "<h3>4/θέσια και άνω</h3>";
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
                    echo "<thead>";
                    echo "<tr><th rowspan=2>Κωδ.</th>";
                    echo "<th rowspan=2>Ονομασία</th>";
                    echo "<th rowspan=2>Κατ.</th>";
                    echo "<th colspan=4>Οργανικές</th>";
                    echo "<th colspan=4>Οργανικά Κενά</th>";
                    echo "<th colspan=4>Λειτουργικά Κενά</th>";
                    echo "</tr>";
                    echo "<tr><th>ΠΕ60/70</th><th>ΠΕ11</th><th>ΠΕ06</th><th>ΠΕ16</th>";
                    echo "<th>ΠΕ60/70</th><th>ΠΕ11</th><th>ΠΕ06</th><th>ΠΕ16</th>";
                    echo "<th>ΠΕ60/70</th><th>ΠΕ11</th><th>ΠΕ06</th><th>ΠΕ16</th>";
                    echo "</tr>";
                    echo "</thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        $code = mysql_result($result, $i, "code");
                        $cat = getCategory(mysql_result($result, $i, "category"));
                        $students = mysql_result($result, $i, "students");
                        $organikes = unserialize(mysql_result($result, $i, "organikes"));
                        $kena_org = unserialize(mysql_result($result, $i, "kena_org"));
                        $kena_leit = unserialize(mysql_result($result, $i, "kena_leit"));
                        
                        echo "<tr>";
                        echo "<td>$code</td>";
                        echo "<td><a href='school_edit.php?org=$sch'>$name</a></td>";
                        echo "<td>$cat</td>";
                        echo "<td>$organikes[0]</td><td>$organikes[1]</td><td>$organikes[2]</td><td>$organikes[3]</td>\n";
                        echo "<td>$kena_org[0]</td><td>$kena_org[1]</td><td>$kena_org[2]</td><td>$kena_org[3]</td>\n";
                        echo "<td>$kena_leit[0]</td><td>$kena_leit[1]</td><td>$kena_leit[2]</td><td>$kena_leit[3]</td>\n";
                        echo "</tr>\n";
                        
                        $organikes_sum[0] += $organikes[0];
                        $organikes_sum[1] += $organikes[1];
                        $organikes_sum[2] += $organikes[2];
                        $organikes_sum[3] += $organikes[3];
                        
                        $kena_org_sum[0] += $kena_org[0];
                        $kena_org_sum[1] += $kena_org[1];
                        $kena_org_sum[2] += $kena_org[2];
                        $kena_org_sum[3] += $kena_org[3];
                        
                        $kena_leit_sum[0] += $kena_leit[0];
                        $kena_leit_sum[1] += $kena_leit[1];
                        $kena_leit_sum[2] += $kena_leit[2];
                        $kena_leit_sum[3] += $kena_leit[3];
                        
                        $i++;                        
                }
        //}	
                echo "<tr><td></td><td>ΣΥΝΟΛΑ</td><td></td>";
                echo "<td>$organikes_sum[0]</td><td>$organikes_sum[1]</td><td>$organikes_sum[2]</td><td>$organikes_sum[3]</td>";
                echo "<td>$kena_org_sum[0]</td><td>$kena_org_sum[1]</td><td>$kena_org_sum[2]</td><td>$kena_org_sum[3]</td>";
                echo "<td>$kena_leit_sum[0]</td><td>$kena_leit_sum[1]</td><td>$kena_leit_sum[2]</td><td>$kena_leit_sum[3]</td></tr>";
                echo "</tbody></table>";

                $page = ob_get_contents(); 
                $_SESSION['page'] = $page;
                ob_end_flush();

                echo "<form action='2excel_ses.php' method='post'>";
                //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
                echo "<BUTTON TYPE='submit'><IMG SRC='images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
                echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='index.php'\">";
                echo "</form>";
                //ob_end_clean();
                       
	}
        else if ($_GET['type'] == 2)
        {
//            //nipiagogeia
            $type = 2;
            // only dhmosia kai eidika (type2 = 0 or 2)
            $query = "SELECT * from school WHERE type2 in (0,2) AND type = $type";
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
                    echo "<th rowspan=2>Κατ.</th>";
                    echo "<th>Οργανικές</th>";
                    echo "<th>Οργανικά Κενά</th>";
                    echo "<th>Λειτουργικά Κενά</th>";
                    echo "</tr>";
                    echo "<tr><th>ΠΕ60</th><th>ΠΕ60</th><th>ΠΕ60</th>";
                    echo "</tr>";
                    echo "</thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        $code = mysql_result($result, $i, "code");
                        $cat = getCategory(mysql_result($result, $i, "category"));
                        $students = mysql_result($result, $i, "students");
                        $organikes = unserialize(mysql_result($result, $i, "organikes"));
                        $kena_org = unserialize(mysql_result($result, $i, "kena_org"));
                        $kena_leit = unserialize(mysql_result($result, $i, "kena_leit"));

                        echo "<tr>";
                        echo "<td>$code</td>";
                        echo "<td><a href='school_edit.php?org=$sch'>$name</a></td>";
                        echo "<td>$cat</td>";
                        echo "<td>$organikes[0]</td>";
                        echo "<td>$kena_org[0]</td><td>$kena_leit[0]</td>\n";
                        echo "</tr>\n";
                        
                        $organikes_sum[0] += $organikes[0];
                        
                        $kena_org_sum[0] += $kena_org[0];
                        
                        $kena_leit_sum[0] += $kena_leit[0];
                        
                        $i++;                        
                }
                echo "<tr><td></td><td>ΣΥΝΟΛΑ</td><td></td><td>$organikes_sum[0]</td><td>$kena_org_sum[0]</td><td>$kena_leit_sum[0]</td></tr>";
                echo "</tbody></table>";            

                $page = ob_get_contents(); 
                $_SESSION['page'] = $page;
                ob_end_flush();

                echo "<form action='2excel_ses.php' method='post'>";
                //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
                echo "<BUTTON TYPE='submit'><IMG SRC='images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
                echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='index.php'\">";
                echo "</form>";
         
        }
?>
                       
  
		</body>
		</html>
                