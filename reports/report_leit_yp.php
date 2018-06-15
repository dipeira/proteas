<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
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
    echo "<center>";
    
    // get required hours
    // only dhmosia (type2 = 0) dhmotika (type = 1)
    $query = "SELECT * from school WHERE type2 = 0 AND type = 1 AND anenergo=0";
    $result = mysql_query($query, $mysqlconnection);
    $num = mysql_num_rows($result);
    $i=0;
    while ($i < $num)
    {		
        $sch = mysql_result($result, $i, "id");
        $leit = mysql_result($result, $i, "leitoyrg");
        // call ektimhseis1617 function
        $results = ektimhseis1617($sch, $mysqlconnection, $sxol_etos);
        // required hours
        $req = $results['required'];
        $req_sum['信05'] += floor($req['05-07']/2);
        $req_sum['信07'] += floor($req['05-07']/2);
        $req_sum['信06'] += $req['06'];
        $req_sum['信08'] += $req['08'];
        $req_sum['信11'] += $req['11'];
        $req_sum['信79'] += $req['79'];
        $req_sum['信32'] += $req['32'];
        $req_sum['信86'] += $req['86'];
        $req_sum['信70'] += $req['70'];
        // add oloimero teacher if leitoyrg < 4
        if ($leit < 4){
            $req_sum['信70'] += 24;
        }
        $i++;                        
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

    // convert requirement hours to teachers (23 hours each)
    function hours_to_teachers($n){
        return floor($n/23);
    }
    $req_sum_teachers = array_map('hours_to_teachers', $req_sum);

    // prepare array for table
    $eidikothtes = Array('信70','信60','信05','信06','信07','信08','信11','信79','信86','信91');
    $tbl_arr = [];
    foreach ($eidikothtes as $eid) {
        $tbl_tmp = [];
        $tbl_tmp['eidikothta'] = $eid;
        $tbl_tmp['organikes'] = $organikes[$eid];
        $tbl_tmp['required'] = $eid == '信60' ? $tmimata_nip_req : $req_sum_teachers[$eid];
        $tbl_tmp['thiteia'] = $thiteia[$eid];
        $tbl_tmp['ekswteriko'] = $ekswteriko[$eid];
        $tbl_tmp['kena'] = $tbl_tmp['organikes'] - $tbl_tmp['required'] - $tbl_tmp['thiteia'] - $tbl_tmp['ekswteriko'];
        $tbl_arr[] = $tbl_tmp;
    }
    
    // print table
    echo "<h2>羞磲赆� 脲轸秕胥殛 赍睨�</h2>";
    ob_start();
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
    echo "<thead>";
    echo "<tr><th>孰茕矧</th>";
    echo "<th>像汜黹贻�</th>";
    echo "<th>琉衢麸屙镩</th>";
    echo "<th>如翦哚</th>";
    echo "<th>朵彘弪</th>";
    echo "<th>嗅襻轸摅彘�</th>";
    echo "<th>琉矬疖箦轵<br>蓬羼殛�</th>";
    echo "<th>峨腼</th>";
    echo "<th>隋轸.叔碥</th>";
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
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>蓬徙� 篝� excel</BUTTON>";
    echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<input type='button' class='btn-red' VALUE='硼轶赳秭�' onClick=\"parent.location='../index.php'\">";
    echo "</form>";
    //ob_end_clean();
?>
</body>
</html>
