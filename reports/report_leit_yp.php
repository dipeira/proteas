<html>
  <head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>������� ������������ ����� (��� ���������)</title>
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

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'greek'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
    
    echo "<body>";
    require '../etc/menu.php';
    echo "<center>";
    
    // get required hours
    // only dhmosia (type2 = 0) dhmotika (type = 1)
    $query = "SELECT * from school WHERE type2 = 0 AND type = 1 AND anenergo=0";
    
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
while ($row = mysqli_fetch_array($result))
{        
    $sch = $row['id'];
    $leit = get_leitoyrgikothta($sch, $mysqlconnection);
    // call ektimhseis_wrwn function
    $results = ektimhseis_wrwn($sch, $mysqlconnection, $sxol_etos);
    // required hours
    $req = $results['required'];
    $req_sum['��05'] += floor($req['05-07']/2);
    $req_sum['��07'] += floor($req['05-07']/2);
    $req_sum['��06'] += $req['06'];
    $req_sum['��08'] += $req['08'];
    $req_sum['��11'] += $req['11'];
    $req_sum['��79'] += $req['79'];
    $req_sum['��91'] += $req['91'];
    $req_sum['��86'] += $req['86'];
    $req_sum['��70'] += $req['70'];
    // add oloimero teacher if leitoyrg < 4
    if ($leit < 4) {
        $req_sum['��70'] += 24;
    }                       
}
    
    // get organikes per klados
    $organikes = organikes_per_klados($mysqlconnection);
    // get tmimata nipiagwgeiwn
    $tmimata_nip = tmimata_nipiagwgeiwn($mysqlconnection);
    $tmimata_nip_req = $tmimata_nip['klasiko'] + $tmimata_nip['oloimero'];
    // get dntes (>=4th)
    $thiteia = dntes_ana_klado($mysqlconnection, true);
    // get apospasmenoi @ ekswteriko
    $ekswteriko = apospasmenoi_ekswteriko($mysqlconnection);

    $req_sum_teachers = array_map('hours_to_teachers', $req_sum);

    // prepare array for table
    $eidikothtes = Array('��70','��60','��05','��06','��07','��08','��11','��79','��86','��91');
    $tbl_arr = [];
foreach ($eidikothtes as $eid) {
    $tbl_tmp = [];
    $tbl_tmp['eidikothta'] = $eid;
    $tbl_tmp['organikes'] = $organikes[$eid];
    $tbl_tmp['required'] = $eid == '��60' ? $tmimata_nip_req : $req_sum_teachers[$eid];
    $tbl_tmp['thiteia'] = $thiteia[$eid];
    $tbl_tmp['ekswteriko'] = $ekswteriko[$eid];
    $tbl_tmp['kena'] = $tbl_tmp['organikes'] - $tbl_tmp['required'] - $tbl_tmp['thiteia'] - $tbl_tmp['ekswteriko'];
    $tbl_arr[] = $tbl_tmp;
}
    
    // print table
    echo "<h2>������� ������������ �����</h2>";
    echo "<h4>(� ������� ����� ��� ���������)</h4>";
    ob_start();
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
    echo "<thead>";
    echo "<tr><th>������</th>";
    echo "<th>���������</th>";
    echo "<th>������������</th>";
    echo "<th>������</th>";
    echo "<th>������</th>";
    echo "<th>�����������</th>";
    echo "<th>����������<br>���������</th>";
    echo "<th>����</th>";
    echo "<th>����.����</th>";
    echo "</tr>";
    echo "</thead>\n<tbody>\n";
    
foreach ($tbl_arr as $row) {
    echo "<tr>";
    echo "<td>".$row['eidikothta']."</td>";
    echo "<td>".$row['organikes']."</td>";
    echo "<td>".$row['required']."</td>";
    echo "<td>".$row['thiteia']."</td>";
    echo "<td></td>";
    echo "<td></td>";
    echo "<td>".$row['ekswteriko']."</td>";
    echo "<td></td>";
    echo "<td>".$row['kena']."</td>";
    echo "</tr>";
}
    
    echo "</tbody>";
    echo "</table>";

    $page = ob_get_contents(); 
    $_SESSION['page'] = $page;
    ob_end_flush();
    
    echo "<form action='../tools/2excel_ses.php' method='post'>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>������� ��� excel</BUTTON>";
    echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<input type='button' class='btn-red' VALUE='���������' onClick=\"parent.location='../index.php'\">";
    echo "</form>";
    //ob_end_clean();
?>
</body>
</html>
