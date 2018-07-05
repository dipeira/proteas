<?php
	//header('Content-type: text/html; charset=iso8859-7'); 
	//require_once "../tools/functions.php";
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <title>Μαθητές & Εκπαιδευτικοί</title>
    <!--
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    -->
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
  <body>
<?php
	require_once"../config.php";
	require_once"../tools/functions.php";
        session_start();
        
        include('../etc/menu.php');
        echo "<h3>Μαθητές & Εκπαιδευτικοί</h3>";
        echo "<table class=\"imagetable\" border='1'>";
        echo "<form action='' method='POST' autocomplete='off'>";
        echo "<tr><td colspan>";
        echo "<input type='radio' name='type' value='1' checked >Δημόσια Δημοτικά (όχι ειδικά)<br>";
        echo "<input type='radio' name='type' value='2' >Ιδιωτικά Δημοτικά<br>";
        echo "<input type='radio' name='type' value='3' >Ειδικά Δημοτικά<br>";
        echo "<input type='radio' name='type' value='7' >Ολιγοθέσια Δημοτικά<br>";
        echo "<input type='radio' name='type' value='4' >Δημόσια Νηπιαγωγεία (όχι ειδικά) (και λειτουργικά κενά)<br>";
        echo "<input type='radio' name='type' value='5' >Ιδιωτικά Νηπιαγωγεία<br>";
        echo "<input type='radio' name='type' value='6' >Ειδικά Νηπιαγωγεία<br>";
        echo "</td></tr>";
        echo "<tr><td colspan><input type='submit' value='Προβολή'>";
        echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
        echo "</td></tr>";
        echo "</table></form>";
        echo "<br>";
                
	$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
	mysql_select_db($db_name, $mysqlconnection);
	mysql_query("SET NAMES 'greek'", $mysqlconnection);
	mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
	
    if ($_POST['type']){
        $dim_ar = array('1', '2', '3', '7');
        $nip_ar = array('4', '5', '6');       
        //if ($_GET['type'] == 1)
        if (in_array($_POST['type'],$dim_ar))
        {
            $type = 1;
            //type2: 0 δημόσιο, 1 ιδιωτικό, 2 ειδικό
            if ($_POST['type'] == 7){
                $query = "SELECT * from school WHERE type = $type AND type2=0 AND anenergo=0 AND leitoyrg <4";
            } else {
                $type2 = $_POST['type'] - 1;
                $query = "SELECT * from school WHERE type = $type AND type2=$type2 AND anenergo=0";
            }
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);
        
                echo "<center>";
                $i=0;
                ob_start();
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                    echo "<thead><tr><th>Ονομασία</th>";
                    echo "<th>Οργ.</th>";
                    echo "<th>Λειτ.</th>";
                    echo "<th>Α'</th>";
                    echo "<th>Β'</th>";
                    echo "<th>Γ'</th>";
                    echo "<th>Δ'</th>";
                    echo "<th>Ε'</th>";
                    echo "<th>ΣΤ'</th>";
                    echo "<th>Σύν.</th>";
                    echo "<th>Τμ. Α'</th>";
                    echo "<th>Τμ. Β'</th>";
                    echo "<th>Τμ. Γ'</th>";
                    echo "<th>Τμ. Δ'</th>";
                    echo "<th>Τμ. Ε'</th>";
                    echo "<th>Τμ. ΣΤ'</th>";
                    echo "<th>Σύν. Τμ.</th>";
                    echo "<th>ΠΕ70</th>";
                    echo "<th>ΠΕ06</th>";
                    echo "<th>ΠΕ11</th>";
                    echo "<th>ΠΕ79</th>";
                    echo "<th>Τμ. Ολ.</th>";
                    echo "<th>Μαθ. Ολ.</th>";
                    //echo "<th>Εκπ. T.E.</th>";
                    //echo "<th>Εκπ. T.Y.</th>";
                    echo "</tr></thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $organikothta = mysql_result($result, $i, "organikothta");
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        $students = mysql_result($result, $i, "students");
                        $classes = explode(",",$students);
                        //$frontistiriako = mysql_result($result, $i, "frontistiriako");
                        $tmimata = mysql_result($result, $i, "tmimata");
                        $tmimata_exp = explode(",",$tmimata);

                        $oloimero_stud = $classes[6];
                        $oloimero_tea = $tmimata_exp[6];
                        //$ekp_ee = mysql_result($result, $i, "ekp_ee");
                        //$ekp_ee_exp = explode(",",$ekp_ee);

                        $synolo = $classes[0] + $classes[1] + $classes[2] + $classes[3] + $classes[4] + $classes[5];
                        $leitoyrg = $synolo_tmim = $tmimata_exp[0] + $tmimata_exp[1] + $tmimata_exp[2] + $tmimata_exp[3] + $tmimata_exp[4] + $tmimata_exp[5];
                        
                        // count employees per specialty
                        $ekp_ar = [];
                        $qry = "SELECT k.perigrafh as klados, count(k.perigrafh) as count FROM employee e join yphrethsh y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status=1 AND e.thesi in (0,1) GROUP BY e.klados";
                        $res = mysql_query($qry, $mysqlconnection);
                        while ($row = mysql_fetch_array($res)){
                            $ekp_ar[$row['klados']] = $row['count'];
                        }
                        $qry = "SELECT k.perigrafh as klados, count(k.perigrafh) as count FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status=1 GROUP BY e.klados";
                        $res = mysql_query($qry, $mysqlconnection);
                        while ($row = mysql_fetch_array($res)){
                            $ekp_ar[$row['klados']] += $row['count'];
                        }

                        echo "<tr>";
                        echo "<td><a href='../school/school_status.php?org=$sch' target='_blank'>$name</a></td><td>$organikothta</td><td>$leitoyrg</td><td>$classes[0]</td><td>$classes[1]</td><td>$classes[2]</td><td>$classes[3]</td><td>$classes[4]</td><td>$classes[5]</td><td>$synolo</td>\n";
                        echo "<td>$tmimata_exp[0]</td><td>$tmimata_exp[1]</td><td>$tmimata_exp[2]</td><td>$tmimata_exp[3]</td><td>$tmimata_exp[4]</td><td>$tmimata_exp[5]</td><td>$synolo_tmim</td>\n";
                        echo "<td>".$ekp_ar['ΠΕ70']."</td><td>".$ekp_ar['ΠΕ06']."</td><td>".$ekp_ar['ΠΕ11']."</td><td>".$ekp_ar['ΠΕ79']."</td>";
                        echo "<td>$oloimero_tea</td><td>$oloimero_stud</td>";//<td>$ekp_ee_exp[0]</td><td>$ekp_ee_exp[1]</td>";
                        echo "</tr>\n";

                        $sums[0] += $classes[0];
                        $sums[1] += $classes[1];
                        $sums[2] += $classes[2];
                        $sums[3] += $classes[3];
                        $sums[4] += $classes[4];
                        $sums[5] += $classes[5];
                        $sumt[0] += $tmimata_exp[0];
                        $sumt[1] += $tmimata_exp[1];
                        $sumt[2] += $tmimata_exp[2];
                        $sumt[3] += $tmimata_exp[3];
                        $sumt[4] += $tmimata_exp[4];
                        $sumt[5] += $tmimata_exp[5];
                        $sumol += $oloimero_tea;
                        $sumolstud += $oloimero_stud;
                        //$sumee[0] += $ekp_ee_exp[0];
                        //$sumee[1] += $ekp_ee_exp[1];
                        $sum70 += $ekp_ar['ΠΕ70'];
                        $sum06 += $ekp_ar['ΠΕ06'];
                        $sum11 += $ekp_ar['ΠΕ11'];
                        $sum16 += $ekp_ar['ΠΕ79'];
                        
                        $i++;                        
                }
        //}	
                $synolo_stud = array_sum($sums);
                $synolo_teach =  array_sum($sumt);
                echo "<tr><td>Σύνολα</td><td></td><td></td><td>$sums[0]</td><td>$sums[1]</td><td>$sums[2]</td><td>$sums[3]</td><td>$sums[4]</td><td>$sums[5]</td><td>$synolo_stud</td>";
                echo "<td>$sumt[0]</td><td>$sumt[1]</td><td>$sumt[2]</td><td>$sumt[3]</td><td>$sumt[4]</td><td>$sumt[5]</td><td>$synolo_teach</td><td>$sum70</td><td>$sum06</td><td>$sum11</td><td>$sum16</td>";
                echo "<td>$sumol</td><td>$sumolstud</td></tr>";//<td>$sumee[0]</td><td>$sumee[1]</td></tr>";
                echo "<tr><td></td><td></td><td></td><td>Α'</td><td>Β'</td><td>Γ'</td><td>Δ'</td><td>Ε'</td><td>ΣΤ'</td><td>Σύν.</td>";
                echo "<td>Τμ.Α'</td><td>Τμ.Β'</td><td>Τμ.Γ'</td><td>Τμ.Δ'</td><td>Τμ.Ε'</td><td>Τμ.ΣΤ'</td><td>Σύν.Τμ.</td><td>ΠΕ70</td><td>ΠΕ06</td><td>ΠΕ11</td><td>ΠΕ79</td><td>Τμ. Ολ.</td><td>Μαθ. Ολ.</td>";//<td>Εκπ. T.E.</td><td>Εκπ. T.Y.</td>";
                echo "</tr>";
                echo "</tbody></table>";

                $page = ob_get_contents(); 
                $_SESSION['page'] = $page;
                ob_end_flush();

                echo "<form action='../tools/2excel_ses.php' method='post'>";
                //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
                echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
                echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
                echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
                echo "</form>";
                //ob_end_clean();
                       
	}
        //else if ($_GET['type'] == 2)
        else if (in_array($_POST['type'],$nip_ar))
        {
//          //nipiagogeia
            $type = 2;
            //type2: 0 δημόσιο, 1 ιδιωτικό, 2 ειδικό
            $type2 = $_POST['type'] - 4;
            
            $query = "SELECT * from school WHERE type = $type AND type2=$type2 AND anenergo=0 ORDER BY name";
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);
            
                echo "<center>";
                $i=0;
                ob_start();
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                    echo "<thead><tr><th>Ονομασία</th>";
                    echo "<th>Οργ.</th>";
                    echo "<th>Λειτ.</th>";
                    
                    echo "<th>Τμήματα<br>Πρωινού</th>";
                    echo "<th>Νήπια<br>Πρωινού</th>";
                    echo "<th>Προνήπια<br>Πρωινού</th>";
                    
                    echo "<th>Τμήματα<br>Ολοήμερου</th>";
                    echo "<th>Νήπια<br>Ολοήμερου</th>";
                    echo "<th>Προνήπια<br>Ολοήμερου</th>";

                    echo "<th>Τ.Ε.</th>";
                    echo "<th>Μαθ Τ.Ε.</th>";
                    
                    echo "<th>Απαιτούμενοι Εκπ/κοί</th>";
                    echo "<th>Τοπ/νοι<br>Εκπ/κοί</th>";
                    echo "<th>+ / -</th>";
                    echo "</tr></thead>\n<tbody>\n";

                while ($i < $num)
                {		 
                    $sch = mysql_result($result, $i, "id");
                    $organikothta = mysql_result($result, $i, "organikothta");
                    $leitoyrg = mysql_result($result, $i, "leitoyrg");
                    $name = getSchool($sch, $mysqlconnection);
                    $entaksis = explode(',', mysql_result($result, $i, "entaksis"));
                    $klasiko = mysql_result($result, $i, "klasiko");
                    $klasiko_exp = explode(",",$klasiko);
                    $oloimero_nip = mysql_result($result, $i, "oloimero_nip");
                    $oloimero_nip_exp = explode(",",$oloimero_nip);

                    $klasiko_tm = $oloimero_tm = 0;
                    $klasiko_tm += $klasiko_exp[0]+$klasiko_exp[1]>0 ? 1:0;
                    $klasiko_tm += $klasiko_exp[2]+$klasiko_exp[3] >0 ? 1:0;
                    $klasiko_tm += $klasiko_exp[4]+$klasiko_exp[5]>0 ? 1:0;
                    $oloimero_tm += $oloimero_nip_exp[0]+$oloimero_nip_exp[1]>0 ? 1:0;
                    $oloimero_tm += $oloimero_nip_exp[2]+$oloimero_nip_exp[3]>0 ? 1:0;
                    $oloimero_tm += $oloimero_nip_exp[4]+$oloimero_nip_exp[5]>0 ? 1:0;

                    echo "<tr>";
                    echo "<td><a href='../school/school_status.php?org=$sch'>$name</a></td><td>$organikothta</td><td>$leitoyrg</td>";
                    echo "<td><strong>$klasiko_tm</strong></td>";

                    $klasiko_nip = $klasiko_exp[0] + $klasiko_exp[2] + $klasiko_exp[4];// + $klasiko_exp[6];
                    $klasiko_pro = $klasiko_exp[1] + $klasiko_exp[3] + $klasiko_exp[5];// + $klasiko_exp[7];
                    echo "<td>$klasiko_nip</td><td>$klasiko_pro</td>";
                    
                    $oloimero_syn_nip = $oloimero_nip_exp[0] + $oloimero_nip_exp[2] + $oloimero_nip_exp[4] + $oloimero_nip_exp[6];
                    $oloimero_syn_pro = $oloimero_nip_exp[1] + $oloimero_nip_exp[3] + $oloimero_nip_exp[5] + $oloimero_nip_exp[7];
                    echo "<td><strong>$oloimero_tm</strong></td>";
                    echo "<td>$oloimero_syn_nip</td><td>$oloimero_syn_pro</td>";
                    
                    // apaitoymenoi
                    $has_entaxi = strlen($entaksis[0])>1 ? 1 : 0; 
                    $apait = $klasiko_tm + $oloimero_tm + $has_entaxi;
                    
                    echo $has_entaxi ? "<td>Ναι</td>" : "<td>Όχι</td>";
                    echo $has_entaxi ? "<td>$entaksis[1]</td>" : "<td>0</td>";
                    echo "<td>$apait</td>";

                    // τοποθετημένοι εκπ/κοί
                    $top60 = $top60m = $top60ana = 0;
                    // exclude ekp/koys@tmima entaksis
                    $qry = "SELECT count(*) as pe60 FROM employee WHERE sx_yphrethshs = $sch AND klados=1 AND status=1 and thesi != 3";
                    $res = mysql_query($qry, $mysqlconnection);
                    $top60m = mysql_result($res, 0, 'pe60');
                    $qry = "SELECT count(*) as pe60 FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados=1 AND status=1";
                    $res = mysql_query($qry, $mysqlconnection);
                    $top60ana = mysql_result($res, 0, 'pe60');
                    $top60 = $top60m+$top60ana;
                    echo "<td>$top60</td>";

                    $k_pl = $top60-$apait;
                    // >0: yellow, 0: green, <0: red
                    if ($k_pl == 0) {
                        $k_pl_class = "'background:none;background-color:rgba(0, 255, 0, 0.37)'";
                    } elseif ($k_pl < 0 ){
                        $k_pl_class = "'background:none;background-color:rgba(255, 0, 0, 0.45)'";
                    } else {
                        $k_pl_class = "'background:none;background-color:rgba(255,255,0,0.3)'";
                    }

                    echo "<td style=$k_pl_class>$k_pl</td>";
                    echo "</tr>\n";

                    $synolo_tm_klas += $klasiko_tm;
                    $synolo_tm_olo += $oloimero_tm;
                    $synolo_nip += $klasiko_nip;
                    $synolo_pro += $klasiko_pro;
                    $synolo_ol_nip += $oloimero_syn_nip;
                    $synolo_ol_pro += $oloimero_syn_pro;
                    $synolo_apait += $apait;
                    $synolo_k_pl += $k_pl;
                    $synolo_nipiag_top += $top60;
                   
                    $i++;
                }
                
                echo "<tr>";
                echo "<td>Σύνολα</td><td></td><td></td>";
                echo "<td>$synolo_tm_klas</td>";
                echo "<td>$synolo_nip</td>";
                echo "<td>$synolo_tm_olo</td>";
                echo "<td>$synolo_pro</td>";
                echo "<td>$synolo_ol_nip</td>";
                echo "<td>$synolo_ol_pro</td><td></td><td></td>";
                echo "<td>$synolo_apait</td>";
                echo "<td>$synolo_nipiag_top</td>";
                echo "<td>$synolo_k_pl</td>";
                echo "</tr>";
                echo "</tbody></table>";

                $page = ob_get_contents(); 
                $_SESSION['page'] = $page;
                ob_end_flush();

                echo "<form action='../tools/2excel_ses.php' method='post'>";
                //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
                echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
                echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
                echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
                echo "</form>";
        }
   }
?>  
</body>
</html>
