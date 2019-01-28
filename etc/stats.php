<?php
header('Content-type: text/html; charset=iso8859-7');
require_once"../config.php";
require_once"../tools/functions.php";
?>    
<html>
    <head>      
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="../js/jquery.js"></script>
        <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
        <script type="text/javascript">   
            $().ready(function() { 
                $(".tablesorter").tablesorter({widgets: ['zebra']}); 
            });
        </script>
        <title>���������� ������� / �����������</title>
    </head>

    <?php
    require "../tools/class.login.php";
    $log = new logmein();
    if ($log->logincheck($_SESSION['loggedin']) == false) {
        header("Location: ../tools/login.php");
    }
    $usrlvl = $_SESSION['userlevel'];
    // status: 1 ���������, 2 ���� ������-���������, 3 �����, 4 �������������
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'greek'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
    
    $query = "SELECT count( * ) FROM employee WHERE status!=2 AND thesi=5";
    $result = mysqli_query($mysqlconnection, $query);
    $idiwtikoi = mysqli_result($result, 0);
    
    $query = "SELECT count( * ) FROM employee WHERE status!=2 AND sx_organikhs NOT IN (388,394) AND thesi!=5";
    $result = mysqli_query($mysqlconnection, $query);
    $monimoi_her_total = mysqli_result($result, 0);
    
    $query = "SELECT count( * ) FROM ektaktoi";
    $result = mysqli_query($mysqlconnection, $query);
    $anapl_total = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE sx_organikhs=1 AND status!=2";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_diath = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE sx_organikhs=388 AND status!=2 AND sx_yphrethshs NOT IN (388,394)";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_apoallopispe = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE sx_organikhs=394 AND status!=2 AND sx_yphrethshs NOT IN (388,394)";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_apoallopisde = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE sx_yphrethshs=388 AND status!=2 AND sx_organikhs NOT IN (388,394)";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_seallopispe = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE sx_yphrethshs=389 AND status!=2 AND sx_organikhs NOT IN (388,394)";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_seforea = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE STATUS=3";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_seadeia = mysqli_result($result, 0);
    
    $query = "SELECT count(*) FROM employee WHERE status!=2 AND (sx_organikhs=388 OR sx_organikhs=394) AND sx_yphrethshs NOT IN (388,394)";
    $result = mysqli_query($mysqlconnection, $query);
    $mon_alloy = mysqli_result($result, 0);

    $query = "SELECT COUNT( * ) , k.perigrafh, k.onoma FROM employee e 
                JOIN klados k 
                ON k.id = e.klados 
                WHERE status!=2 AND sx_organikhs NOT IN (388,394) AND thesi!=5
                GROUP BY klados";
    $result_mon = mysqli_query($mysqlconnection, $query);

    $query = "SELECT COUNT( * ) , k.perigrafh, k.onoma, ek.type
                FROM ektaktoi e
                JOIN klados k ON k.id = e.klados
                JOIN ektaktoi_types ek ON e.type = ek.id
                GROUP BY klados, e.type";
    $result_anapl = mysqli_query($mysqlconnection, $query);

    // sxoleia
    $sx_arr = array();
    $query = "SELECT count(*) FROM school WHERE type = 1";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['�������� (������)'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 0 AND type2 = 0";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['���. ������'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 1 AND type2 = 0";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['���. ��������'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 0 AND type2 = 2";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['���. ������'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 1 AND anenergo = 0 AND type2 = 1";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['���. ��������'] = $res;

    $query = "SELECT count(*) FROM school WHERE type = 2";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['����������� (������)'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 0 AND type2 = 0";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['�����������'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 1 AND type2 = 0";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['���. ��������'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 0 AND type2 = 2";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['���. ������'] = $res;
    $query = "SELECT count(*) FROM school WHERE type = 2 AND anenergo = 0 AND type2 = 1";
    $res = mysqli_query($mysqlconnection, $query);
    $res = mysqli_result($res, 0);
    $sx_arr['���. ��������'] = $res;

    //
    echo "<body>";
    require '../etc/menu.php';
    echo "<h2>����������</h2>";
    echo "<table id='mytbl' class=\"imagetable tablesorter\" border='1'>";
    echo "<h3>������� ������������� (�� �������� ��� �/��� ".getParam('dnsh', $mysqlconnection)."):&nbsp;$monimoi_her_total</h3>";
    echo "<thead><th><b>������</b></th><th colspan=3><b>�������</b></th></thead><tbody>";
    while ($row = mysqli_fetch_array($result_mon, MYSQLI_NUM)) {
        echo "<tr><td>$row[1] ($row[2])</td><td colspan=2>$row[0]</td></tr>";
    }
    echo "<tr><td><strong>��������� ���/���</strong></td><td>$idiwtikoi</td></tr>";
    echo "</tbody></table>";
    echo "<br>";
    echo "<table class=\"imagetable\" border='1'>";
    echo "<tr><td>��������� ��� ����� ��������� ��� <br>����� �������� �� ���� �����/�����</td><td>$mon_alloy</td></tr>";
    echo "<tr><td>������������ ��� ���� �����</td><td>$mon_apoallopispe</td></tr>";
    echo "<tr><td>������������/�� ������� ��� ���� �����</td><td>$mon_apoallopisde</td></tr>";
    echo "<tr><td>������� �����</td><td>$mon_diath</td></tr>";
    echo "<tr><td>�� �������� �� ���� �����</td><td>$mon_seallopispe</td></tr>";
    echo "<tr><td>�� �������� �� �����</td><td>$mon_seforea</td></tr>";
    echo "<tr><td>�� �����</td><td>$mon_seadeia</td></tr>";
    echo "</table>";
    echo "<br>";
    echo "<table id='mytbl' class=\"imagetable tablesorter\" border='1'>";
    echo "<h3>����������� / ���������� �������������:&nbsp;$anapl_total</h3>";
    echo "<thead><th>�����</th><th>������</th><th>������</th></thead><tbody>";
    while ($row = mysqli_fetch_array($result_anapl, MYSQLI_NUM)) {
        echo "<tr><td>$row[3]<td>$row[1] ($row[2])</td><td>$row[0]</td></tr>";
    }
    echo "</tbody></table>";
    echo "<br>";

    echo "<table class=\"imagetable tablesorter\" border='1'>";
    echo "<h3>�������� �������</h3>";
    echo "<thead><th>�����</th><th>�������</th></thead><tbody>";
    foreach ($sx_arr as $k => $v) {
        echo "<tr><td>$k</td><td>$v</td>";
    }

    echo "</tbody></table>";

    echo "<INPUT TYPE='button' VALUE='���������' class='btn-red' onClick=\"parent.location='../index.php'\">";
    echo "</body>";
    echo "</html>";

    mysqli_close($mysqlconnection);
    ?>
