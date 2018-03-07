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
	jQuery(document).ready(function($) { 
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
            //$query = "SELECT * from school WHERE type = $type AND oloimero = 1 AND type2=0";
            $query = "SELECT * from school s JOIN dimos d ON d.id=s.dimos WHERE type = $type AND oloimero = 1 AND type2=0";
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
                    echo "<tr><th rowspan=2>Δήμος</th>";
                    //echo "<th colspan=4>Οργανικά Κενά</th>";
                    echo "<th colspan=6>Μαθητές Ολοημέρου</th>";
                    echo "</tr>";
                    //echo "<th>ΠΕ60/70</th><th>ΠΕ11</th><th>ΠΕ06</th><th>ΠΕ16</th>";
                    //echo "<th colspan=2>ΠΕ11</th><th colspan=2>ΠΕ06</th><th colspan=2>ΠΕ16</th>";
                    //echo "<th>ΠΕ60/70</th>";
                    //echo "<th>KENA</th><th>Διαφορα</th><th>org&anhk&energoi</th><th>tmimata (dntis)</th>";
                    //echo "</tr>";
                    echo "</thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        $dimos = mysql_result($result, $i, "d.name");
                        //$students = mysql_result($result, $i, "students");
                        $oloimero_stud = mysql_result($result, $i, "oloimero_stud");
                        
                        
                        // >6 tmhmata && pe6 or pe11 or pe16 >0
                        //if ($tmimata >= 6 && (($organikes[1]+$organikes[2]+$organikes[3])>0))
                            echo "<tr>";
                            echo "<td><a href='../school/school_status.php?org=$sch'>$name</a></td>";
                            echo "<td>$dimos</td>";
                            //echo "<td>$organikes[0]</td><td>$organikes[1]</td><td>$organikes[2]</td><td>$organikes[3]</td>\n";
                            //echo "<td>$organikes[1]</td><td>$organikes[2]</td><td>$organikes[3]</td>\n";
                            echo "<td>$oloimero_stud</td>\n";
                            echo "</tr>\n";
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
            //$query = "SELECT * from school WHERE type = $type";
            //$query = "SELECT * from school WHERE type = $type AND oloimero = 1 AND type2=0";
            $query = "SELECT * from school s JOIN dimos d ON d.id=s.dimos WHERE type = $type AND oloimero = 1 AND type2=0";
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);

                echo "<body>";
                echo "<center>";
                $i=0;
                ob_start();
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                    echo "<thead>";
                    echo "<tr><th rowspan=2>Ονομασία</th>";
                    echo "<th>Δήμος</th>";
                    echo "<th>Μαθητές Ολοημέρου</th>";
                    echo "</tr>";
                    echo "</thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        $oloimero_nip = mysql_result($result, $i, "oloimero_nip");
                        $oloimero_nip_arr = explode(",", $oloimero_nip);
                        $oloimero_nip_sum = array_sum($oloimero_nip_arr);
                        $dimos = mysql_result($result, $i, "d.name");
                        
                        echo "<tr>";
                        echo "<td><a href='../school/school_edit.php?org=$sch'>$name</a></td>";
                        echo "<td>$dimos</td>\n";
                        echo "<td>$oloimero_nip_sum</td>\n";
                        echo "</tr>\n";
                        
                        $i++;                        
                }
                //echo "<tr><td>ΣΥΝΟΛΑ</td><td>$kena_org_sum[0]</td><td>$kena_leit_sum[0]</td></tr>";
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
                