<?php
	//header('Content-type: text/html; charset=iso8859-7'); 
	//require_once "../tools/functions.php";
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    
        <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
        <title>����������� ���� ����������� ��� ������� ����</title>
	
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
            $query = "SELECT * from school WHERE type = $type and leitoyrg >= 4 AND eaep=1";
            
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);
        
                echo "<body>";
                echo "<a href=\"report_kena_eid_eaep.php?klados=3\">����/����������� ��06 - ��������</a><br>";
                echo "<a href=\"report_kena_eid_eaep.php?klados=4\">����/����������� ��08 - �������������</a><br>";
                echo "<a href=\"report_kena_eid_eaep.php?klados=5\">����/����������� ��11 - ������� ������</a><br>";
                echo "<a href=\"report_kena_eid_eaep.php?klados=6\">����/����������� ��16 - ��������</a><br>";
                echo "<a href=\"report_kena_eid_eaep.php?klados=13\">����/����������� ��05 - ��������</a><br>";
                echo "<a href=\"report_kena_eid_eaep.php?klados=14\">����/����������� ��07 - ����������</a><br>";
                echo "<a href=\"report_kena_eid_eaep.php?klados=15\">����/����������� ��19-20 - ������������</a><br>";
                echo "<a href=\"report_kena_eid_eaep.php?klados=20\">����/����������� ��32 - �����������</a><br>";
                echo "<center>";
                $i=0;
                ob_start();
                
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                    echo "<thead>";
                    switch ($klados){
                        case 3:
                            echo "<tr><th colspan=4>��06 - ��������</td></tr>";
                            break;
                        case 4:
                            echo "<tr><th colspan=4>��08 - �������������</td></tr>";
                            break;
                        case 5:
                            echo "<tr><th colspan=4>��11 - ���. ������</td></tr>";
                            break;
                        case 6:
                            echo "<tr><th colspan=4>��16 - ��������</td></tr>";
                            break;
                        case 13:
                            echo "<tr><th colspan=4>��05 - ��������</td></tr>";
                            break;
                        case 14:
                            echo "<tr><th colspan=4>��07 - ����������</td></tr>";
                            break;
                        case 15:
                            echo "<tr><th colspan=4>��19-20 - ������������</td></tr>";
                            break;
                        case 20:
                            echo "<tr><th colspan=4>��32 - �����������</td></tr>";
                            break;
                    }
                    echo "<tr><th rowspan=2>��������</th>";
                    echo "<th colspan=3>����������� ����</th>";
                    echo "</tr>";
                    
                    echo "<tr></tr>";
                    //echo "<tr><small><th></th><th>�����.</th><th>�����.</th><th>����������</th></small>";
                    echo "<tr><small><th></th><th>�����.</th><th>����������</th></small>";
                    echo "<th>+/-</th>";
                    echo "</tr>";
                    echo "</thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        //$students = mysql_result($result, $i, "students");
                        //$kena_org = unserialize(mysql_result($result, $i, "kena_org"));
                        //$kena_leit = unserialize(mysql_result($result, $i, "kena_leit"));
                        //$organikes = unserialize(mysql_result($result, $i, "organikes"));
                        //$organikes11 = 1 / $organikes06 = 2
                        //$pe06org = $organikes[2];
                        //$pe11org = $organikes[1];
                        //if ($klados == 3)
                        //    $organikes = $pe06org;
                        //elseif ($klados == 5)
                        //    $organikes = $pe11org;
                        
                        $tm = explode(',',mysql_result($result, $i, "tmimata"));
                        $tmimata = array_sum($tm);
                        
                        switch ($klados){
                        case 3:
                            // ��06
                            $pewr = (($tm[0]+$tm[1])*2) + (($tm[2]+$tm[3]+$tm[4]+$tm[5])*4);
                            break;
                        case 4:
                            // ��08
                            $pewr = (($tm[0]+$tm[1])*2) + (($tm[2]+$tm[3]+$tm[4]+$tm[5])*1);
                            break;
                        case 5:
                            // ��11
                            $pewr = (($tm[4]+$tm[5])*2) + (($tm[0]+$tm[1]+$tm[2]+$tm[3])*4);
                            break;
                        case 6:
                            // ��16
                            $pewr = (($tm[0]+$tm[1])*2) + (($tm[2]+$tm[3]+$tm[4]+$tm[5])*1);
                            break;
                        case 13:
                            // ��05
                            $pewr = (($tm[4]+$tm[5])*2);
                            break;
                        case 14:
                            // ��07
                            $pewr = (($tm[4]+$tm[5])*2);
                            break;
                        case 15:
                            // ��19-20
                            $pewr = (($tm[0]+$tm[1])*1) + (($tm[2]+$tm[3]+$tm[4]+$tm[5])*2);
                            break;
                        case 20:
                            // ��32
                            $pewr = $tmimata * 1;
                            break;
                        }
                        
                        $kladosqry = "SELECT count(*) as a FROM yphrethsh y JOIN employee e ON e.id=y.emp_id WHERE sxol_etos=".$sxol_etos." AND yphrethsh = ".$sch." AND e.klados = ".$klados." AND e.status = 1";
                        $res = mysql_query($kladosqry, $mysqlconnection);
                        $yphret = mysql_result($res, 0);
                        
                        $ektkladosqry = "SELECT count(*) as a FROM yphrethsh_ekt y JOIN ektaktoi e ON e.id=y.emp_id WHERE sxol_etos=".$sxol_etos." AND yphrethsh = ".$sch." AND e.klados = ".$klados;
                        $res = mysql_query($ektkladosqry, $mysqlconnection);
                        $ektyphret = mysql_result($res, 0);
                        
                        $ekp_name = "";
                        $has_wres = $has_wres_ekt = $mon_wres = $ekt_wres = 0;
                        if ($yphret > 0){
                            //$pe_ekp = "SELECT e.wres,e.id,e.surname,e.name,e.hm_dior,e.proyp FROM yphrethsh y JOIN employee e ON e.id=y.emp_id WHERE sxol_etos=".$sxol_etos." AND yphrethsh = ".$sch." AND e.klados = ".$klados." AND e.status = 1";
                            $pe_ekp = "SELECT y.hours,e.id,e.surname,e.name,e.hm_dior,e.proyp,e.wres FROM yphrethsh y JOIN employee e ON e.id=y.emp_id WHERE sxol_etos=".$sxol_etos." AND yphrethsh = ".$sch." AND e.klados = ".$klados." AND e.status = 1";
                            $res1 = mysql_query($pe_ekp, $mysqlconnection);
                            
                            while ($row = mysql_fetch_array($res1, MYSQL_BOTH)){
                                // check if emp is in multiple schools
                                $q1 = "select count(*) as cnt from yphrethsh where emp_id = '" . $row['id'] ."' and sxol_etos=".$sxol_etos;
                                $r1 = mysql_query($q1);
                                $count = mysql_result($r1, 0, 'cnt');
                                // if more than one schools, get hours from yphrethsh table, else from employee table
                                $count > 1 ? $has_wres = $row['hours'] : $has_wres = $row['wres'];
                                //$has_wres = $row['hours'];
                                $ekp_name .= "$has_wres (<small><a href=\"employee.php?id=".  $row['id'] ."&op=view\">". $row['surname'] ." ". $row['name'] .")</a></small><br>";
                                $mon_wres += $has_wres;
                            }
                        }
                        if ($ektyphret > 0){
                            $pe_ekp = "SELECT e.id,e.surname,e.name,y.hours FROM yphrethsh_ekt y JOIN ektaktoi e ON e.id=y.emp_id WHERE sxol_etos=".$sxol_etos." AND yphrethsh = ".$sch." AND e.klados = ".$klados."";
                            $res1 = mysql_query($pe_ekp, $mysqlconnection);
                            
                            while ($row = mysql_fetch_array($res1, MYSQL_BOTH)){
                                $has_wres_ekt = $row['hours'];
                                $ekp_name .= "<i><br> $has_wres_ekt (<small><a href=\"ektaktoi.php?id=".  $row['id'] ."&op=view\">". $row['surname'] ." ". $row['name'] .")</a></small></i>";
                                $ekt_wres += $has_wres_ekt;
                            }
                        }
                        $wres_apait = $mon_wres + $ekt_wres - $pewr;

                        echo "<tr>";
                        echo "<td><a href='../school/school_status.php?org=$sch'>$name</a></td>";
                        //echo "<td>$organikes<td>$pewr</td><td>$ekp_name</td><td>$wres_apait</td>\n";
                        if ($wres_apait >= 0)
                            echo "<td>$pewr</td><td>$ekp_name</td><td style='background:none;background-color:#00FF00'>$wres_apait</td>\n";
                        else
                            echo "<td>$pewr</td><td>$ekp_name</td><td style='background:none;background-color:#FF0000'>$wres_apait</td>\n";
                        $syn_wres_apait += $wres_apait;
                        echo "</tr>\n";
                                            
                        $i++;
                }
                echo "<tr><td colspan=3><strong>������</strong></td><td>$syn_wres_apait</td></tr>";
                echo "</tbody></table>";

                $page = ob_get_contents(); 
                $_SESSION['page'] = $page;
                ob_end_flush();

                echo "<form action='../tools/2excel_ses.php' method='post'>";
                //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
                echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>������� ��� excel</BUTTON>";
                echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='���������' onClick=\"parent.location='../index.php'\">";
                echo "</form>";
                //ob_end_clean();
	}
?>
                  
		</body>
		</html>
                