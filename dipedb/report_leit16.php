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
    $query = "SELECT * from school WHERE type2 = 0 AND type = 1";
    $result = mysql_query($query, $mysqlconnection);
    $num = mysql_num_rows($result);

        echo "<body>";
        echo "<center>";
        $i=0;
        ob_start();
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
            echo "<thead>";
            echo "<tr><th rowspan=2>���.</th>";
            echo "<th rowspan=2>��������</th>";
            echo "<th rowspan=2>���.</th>";
            echo "<th rowspan=2>����.</th>";
            echo "<th colspan=9>����������� ����</th>";
            echo "</tr>";
            echo "<th>05-07</th><th>06</th><th>08</th><th>11</th><th>16</th><th>32</th><th>19-20</th><th>70</th><th>70-(��+��)</th>";
            echo "</tr>";
            echo "</thead>\n<tbody>\n";

        while ($i < $num)
        {		
                $sch = mysql_result($result, $i, "id");
                $name = getSchool($sch, $mysqlconnection);
                $code = mysql_result($result, $i, "code");
                $organikothta = mysql_result($result, $i, "organikothta");
                $results = ektimhseis1617($sch, $mysqlconnection, $sxol_etos);
                $df = $results['diff'];
                if (!$df) {
                    $i++;
                    continue;
                }
                
                echo "<tr>";
                echo "<td>$code</td>";
                echo "<td><a href='school_status.php?org=$sch' target='_blank'>$name</a></td>";
                echo "<td>$organikothta</td>";
                echo "<td>".$results['leit']."</td>";
                echo tdc($df['05-07']).tdc($df['06']).tdc($df['08']).tdc($df['11']).tdc($df['16']).tdc($df['32']).tdc($df['19-20']).tdc($df['70']).tdc($df['OP']);
                echo "</tr>\n";

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
        echo "<tr><td></td><td></td><td></td><td>������</td>";
        echo "<td>".$df_sum['05-07']."</td><td>".$df_sum['06']."</td><td>".$df_sum['08']."</td><td>".$df_sum['11']."</td><td>".$df_sum['16']."</td><td>".$df_sum['32']."</td><td>".$df_sum['19-20']."</td><td>".$df_sum['70']."</td><td>".$df_sum['OP']."</td>\n";
        echo "<tr><td></td><td></td><td></td><td></td>";
        echo "<td>05-07</td><td>06</td><td>08</td><td>11</td><td>16</td><td>32</td><td>19-20</td><td>70</td><td>70-(��+��)</td>";
        echo "</tr>";
        echo "</tbody></table>";
        echo "<br>";

        $page = ob_get_contents(); 
        $_SESSION['page'] = $page;
        ob_end_flush();

        echo "<form action='2excel_ses.php' method='post'>";
        //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
        echo "<BUTTON TYPE='submit'><IMG SRC='images/excel.png' ALIGN='absmiddle'>������� ��� excel</BUTTON>";
        echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='���������' onClick=\"parent.location='index.php'\">";
        echo "</form>";
        //ob_end_clean();
                       
	
        
?>
                       
  
		</body>
		</html>
                