<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	//require_once "../tools/functions.php";
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <!--
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    -->
    <title>������� ��������� �����</title>
	<script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type="text/javascript" src="../js/stickytable.js"></script>
	<script type="text/javascript">	
        $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
            $("#mytbl").stickyTableHeaders();
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

    echo "<body>";
    include('../etc/menu.php');
    echo "<h3>�������� ����</h3>";
    echo "<p>�������� �������� ���� ��������:</p>";
    echo "<a href='report_kena.php?type=1'>�������� �������</a><br>";
    echo "<a href='report_kena.php?type=2'>�����������</a><br>";
    echo "<a href='report_kena.php?type=3'>������ �������</a><br>";
    echo "<input type='button' class='btn-red' VALUE='���������' onClick=\"parent.location='../index.php'\">";

    if ($_GET['type'] == 1 || $_GET['type'] == 3)
    {
        $type = 1;
        if ($_GET['type'] == 1){
            $query = "SELECT * from school WHERE type2 = 0 AND type = $type";
        }
        else {
            $query = "SELECT * from school WHERE type2 = 2 AND type = $type";
        }

        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_num_rows($result);
        
        
        echo "<center>";
        $i=0;
        ob_start();
        $synorgtop = $synorgan = 0;
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
        echo "<thead>";
        echo "<tr><th rowspan=2>���.</th>";
        echo "<th rowspan=2>��������</th>";
        echo "<th rowspan=2>���.</th>";
        echo "<th rowspan=2>���.</th>";
        echo "<th rowspan=2>��70<br>���.<br>�����.</th>";
        echo "<th colspan=4>���������</th>";
        echo "<th colspan=4>�������� ����</th>";
        echo "</tr>";
        echo "<tr><th>��70</th><th>��11</th><th>��06</th><th>��79</th>";
        echo "<th>��70</th><th>��11</th><th>��06</th><th>��79</th>";
        echo "</tr>";
        echo "</thead>\n<tbody>\n";

        while ($i < $num)
        {		
            $sch = mysql_result($result, $i, "id");
            $name = getSchool($sch, $mysqlconnection);
            $code = mysql_result($result, $i, "code");
            $cat = getCategory(mysql_result($result, $i, "category"));
            $organikothta = mysql_result($result, $i, "organikothta");
            $synorgan += $organikothta;
            $organikes = unserialize(mysql_result($result, $i, "organikes"));
            $kena_org = unserialize(mysql_result($result, $i, "kena_org"));
            // �������� ��������������
            $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados=2 AND status IN (1,3) AND thesi IN (0,1,2)";
            $rs = mysql_query($qry, $mysqlconnection);
            $orgtop = mysql_result($rs, 0, "cnt");
            $synorgtop += $orgtop;

            echo "<tr>";
            echo "<td>$code</td>";
            echo "<td><a href='../school/school_status.php?org=$sch' target='_blank'>$name</a></td>";
            echo "<td>$cat</td>";
            echo "<td>$organikothta</td>";
            echo "<td>$orgtop</td>";
            echo "<td>$organikes[0]</td><td>$organikes[1]</td><td>$organikes[2]</td><td>$organikes[3]</td>\n";
            echo "<td>$kena_org[0]</td><td>$kena_org[1]</td><td>$kena_org[2]</td><td>$kena_org[3]</td>\n";
            echo "</tr>\n";

            $organikes_sum[0] += $organikes[0];
            $organikes_sum[1] += $organikes[1];
            $organikes_sum[2] += $organikes[2];
            $organikes_sum[3] += $organikes[3];

            $kena_org_sum[0] += $kena_org[0];
            $kena_org_sum[1] += $kena_org[1];
            $kena_org_sum[2] += $kena_org[2];
            $kena_org_sum[3] += $kena_org[3];

            $i++;                        
        }
    //}	
        echo "<tr><td></td><td></td><td>������</td>";
        echo "<td>$synorgan</td><td>$synorgtop</td><td>$organikes_sum[0]</td><td>$organikes_sum[1]</td><td>$organikes_sum[2]</td><td>$organikes_sum[3]</td>";
        echo "<td>$kena_org_sum[0]</td><td>$kena_org_sum[1]</td><td>$kena_org_sum[2]</td><td>$kena_org_sum[3]</td>";
        echo "<tr><td></td><td></td><td></td><td>������-<br>�������</td><td>��70<br>���.<br>�����.</td><td>��70</td><td>��11</td><td>��06</td><td>��79</td>";
        echo "<td>��70</td><td>��11</td><td>��06</td><td>��79</td>";
        echo "</tr>";
        echo "</tbody></table>";
        echo "<br>";

        $page = ob_get_contents(); 
        $_SESSION['page'] = $page;
        ob_end_flush();

        echo "<form action='../tools/2excel_ses.php' method='post'>";
        //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
        echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>������� ��� excel</BUTTON>";
        echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<input type='button' class='btn-red' VALUE='���������' onClick=\"parent.location='../index.php'\">";
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
        echo "<h3>4/����� ��� ���</h3>";
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
        echo "<thead>";
        echo "<tr><th rowspan=2>���.</th>";
        echo "<th rowspan=2>��������</th>";
        echo "<th rowspan=2>���.</th>";
        echo "<th colspan=4>���������</th>";
        echo "<th colspan=4>�������� ����</th>";
        echo "<th colspan=4>����������� ����</th>";
        echo "</tr>";
        echo "<tr><th>��60/70</th><th>��11</th><th>��06</th><th>��79</th>";
        echo "<th>��60/70</th><th>��11</th><th>��06</th><th>��79</th>";
        echo "<th>��60/70</th><th>��11</th><th>��06</th><th>��79</th>";
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
            echo "<td><a href='../school/school_edit.php?org=$sch'>$name</a></td>";
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
        echo "<tr><td></td><td>������</td><td></td>";
        echo "<td>$organikes_sum[0]</td><td>$organikes_sum[1]</td><td>$organikes_sum[2]</td><td>$organikes_sum[3]</td>";
        echo "<td>$kena_org_sum[0]</td><td>$kena_org_sum[1]</td><td>$kena_org_sum[2]</td><td>$kena_org_sum[3]</td>";
        echo "<td>$kena_leit_sum[0]</td><td>$kena_leit_sum[1]</td><td>$kena_leit_sum[2]</td><td>$kena_leit_sum[3]</td></tr>";
        echo "</tbody></table>";

        $page = ob_get_contents(); 
        $_SESSION['page'] = $page;
        ob_end_flush();

        echo "<form action='../tools/2excel_ses.php' method='post'>";
        //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
        echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>������� ��� excel</BUTTON>";
        echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<input type='button' class='btn-red' VALUE='���������' onClick=\"parent.location='../index.php'\">";
        echo "</form>";
        //ob_end_clean();
    }
    else if ($_GET['type'] == 2)
    {
        //nipiagogeia
        $type = 2;
        $synorgtop = 0;
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
        echo "<tr><th rowspan=2>���.</th>";
        echo "<th rowspan=2>��������</th>";
        echo "<th rowspan=2>���.</th>";
        echo "<th>���������</th>";
        echo "<th>���.���.</th>";
        echo "<th>�������� ����</th>";
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
            // �������� ��������������
            $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados=1 AND status IN (1,3)";
            $rs = mysql_query($qry, $mysqlconnection);
            $orgtop = mysql_result($rs, 0, "cnt");
            $synorgtop += $orgtop;

            echo "<tr>";
            echo "<td>$code</td>";
            echo "<td><a href='../school/school_status.php?org=$sch' target='_blank'>$name</a></td>";
            echo "<td>$cat</td>";
            echo "<td>$organikes[0]</td>";
            echo "<td>$orgtop</td>";
            echo "<td>$kena_org[0]</td>";
            echo "</tr>\n";

            $organikes_sum[0] += $organikes[0];

            $kena_org_sum[0] += $kena_org[0];


            $i++;                        
        }
        echo "<tr><td></td><td>������</td><td></td><td>$organikes_sum[0]</td><td>$synorgtop</td><td>$kena_org_sum[0]</td></tr>";
        echo "</tbody></table>";
        echo "<br>";

        $page = ob_get_contents(); 
        $_SESSION['page'] = $page;
        ob_end_flush();

        echo "<form action='../tools/2excel_ses.php' method='post'>";
        //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
        echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>������� ��� excel</BUTTON>";
        echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<input type='button' class='btn-red' VALUE='���������' onClick=\"parent.location='../index.php'\">";
        echo "</form>";
    }
?>
</body>
</html>
                