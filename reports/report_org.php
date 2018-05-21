<?php
	//header('Content-type: text/html; charset=iso8859-7'); 
	//require_once "../tools/functions.php";
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
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
        
 
	$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
	mysql_select_db($db_name, $mysqlconnection);
	mysql_query("SET NAMES 'greek'", $mysqlconnection);
	mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
	
	if ($_GET['type'] == 1)
        {
            $type = 1;
            $query = "SELECT * from school WHERE type = $type AND type2=0";
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);
        
                echo "<body>";
                echo "<center>";
                $i=0;
                ob_start();
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                    echo "<thead>";
//                    echo "<tr><td>aaa</td></tr>";
                    echo "<tr><th rowspan=2>Ονομασία</th>";
                    //echo "<th colspan=4>Οργανικά Κενά</th>";
                    echo "<th colspan=6>Οργανικές - Κενά</th>";
                    echo "</tr>";
                    echo "<th colspan=2>ΠΕ11</th><th colspan=2>ΠΕ06</th><th colspan=2>ΠΕ79</th>";
                    //echo "<th>ΠΕ60/70</th>";
                    //echo "<th>KENA</th><th>Διαφορα</th><th>org&anhk&energoi</th><th>tmimata (dntis)</th>";
                    echo "</tr>";
                    echo "</thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        //$students = mysql_result($result, $i, "students");
                        //$oloimero = mysql_result($result, $i, "oloimero");
                        //$oloimero_tea = mysql_result($result, $i, "oloimero_tea");
                        $organikes = unserialize(mysql_result($result, $i, "organikes"));
                        $kena_org = unserialize(mysql_result($result, $i, "kena_org"));
                        $kena_leit = unserialize(mysql_result($result, $i, "kena_leit"));
                        $tmim = mysql_result($result, $i, "tmimata");
                        $tmimata_exp = explode(",",$tmim);
                        $tmimata = array_sum($tmimata_exp);                         
                        
                        // >6 tmhmata && pe6 or pe11 or pe16 >0
                        //if ($tmimata >= 6 && (($organikes[1]+$organikes[2]+$organikes[3])>0))
                        if ($tmimata >= 6)
                        {
                            echo "<tr>";
                            echo "<td><a href='../school/school_status.php?org=$sch'>$name</a></td>";
                            //echo "<td>$organikes[0]</td><td>$organikes[1]</td><td>$organikes[2]</td><td>$organikes[3]</td>\n";
                            //echo "<td>$organikes[1]</td><td>$organikes[2]</td><td>$organikes[3]</td>\n";
                            echo "<td>$organikes[1]</td><td>$kena_leit[1]</td><td>$organikes[2]</td><td>$kena_leit[2]</td><td>$organikes[3]</td><td>$kena_leit[3]</td>\n";
                            echo "</tr>\n";
                        }
                        $i++;
                }
                //echo "<tr><td>ΣΥΝΟΛΑ</td>";
                //echo "<td>$kena_leit_sum[0]</td><td>$kena_leit_sum[1]</td><td>$kena_leit_sum[2]</td><td>$kena_leit_sum[3]</td></tr>";
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
        else if ($_GET['type'] == 2)
        {
//            //nipiagogeia
            $type = 2;
            $query = "SELECT * from school WHERE type = $type";
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);

                echo "<body>";
                echo "<center>";
                $i=0;
                ob_start();
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                    echo "<thead>";
                    echo "<tr><th rowspan=2>Ονομασία</th>";
                    echo "<th>Οργανικά Κενά</th>";
                    echo "<th>Λειτουργικά Κενά</th>";
                    echo "</tr>";
                    echo "<tr><th>ΠΕ60</th><th>ΠΕ60</th>";
                    echo "</tr>";
                    echo "</thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        $students = mysql_result($result, $i, "students");
                        $kena_org = unserialize(mysql_result($result, $i, "kena_org"));
                        $kena_leit = unserialize(mysql_result($result, $i, "kena_leit"));

                        echo "<tr>";
                        echo "<td><a href='../school/school_edit.php?org=$sch'>$name</a></td>";
                        echo "<td>$kena_org[0]</td><td>$kena_leit[0]</td>\n";
                        echo "</tr>\n";
                        
                        $kena_org_sum[0] += $kena_org[0];
//                        $kena_org_sum[1] += $kena_org[1];
//                        $kena_org_sum[2] += $kena_org[2];
//                        $kena_org_sum[3] += $kena_org[3];
                        
                        $kena_leit_sum[0] += $kena_leit[0];
//                        $kena_leit_sum[1] += $kena_leit[1];
//                        $kena_leit_sum[2] += $kena_leit[2];
//                        $kena_leit_sum[3] += $kena_leit[3];
                        
                        $i++;                        
                }
                echo "<tr><td>ΣΥΝΟΛΑ</td><td>$kena_org_sum[0]</td><td>$kena_leit_sum[0]</td></tr>";
                echo "</tbody></table>";            

                $page = ob_get_contents(); 
                $_SESSION['page'] = $page;
                ob_end_flush();

                echo "<form action='../tools/2excel_ses.php' method='post'>";
                //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
                echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
                echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
                echo "</form>";
         
        }
?>
                       
  
		</body>
		</html>
                