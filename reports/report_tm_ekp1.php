<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	//require_once "../tools/functions.php";
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <title>Μαθητές & Εκπαιδευτικοί</title>
    
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    	
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
        
        echo "<body>";
        echo "<table class=\"imagetable\" border='1'>";
        echo "<form action='' method='POST' autocomplete='off'>";
        echo "<tr><td colspan=2>";
        echo "<input type='radio' name='type' value='1' checked >Δημόσια Δημοτικά (όχι ειδικά)<br>";
        echo "<input type='radio' name='type' value='2' >Ιδιωτικά Δημοτικά<br>";
        echo "<input type='radio' name='type' value='3' >Ειδικά Δημοτικά<br>";
        echo "<input type='radio' name='type' value='4' >Δημόσια Νηπιαγωγεία (όχι ειδικά)<br>";
        echo "<input type='radio' name='type' value='5' >Ιδιωτικά Νηπιαγωγεία<br>";
        echo "<input type='radio' name='type' value='6' >Ειδικά Νηπιαγωγεία<br>";
        echo "</td></tr>";
        echo "<tr><td colspan=2><input type='submit' value='Προβολή'></td></tr>";
        echo "</table></form></head>";
        echo "<br>";
                
	$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
	mysql_select_db($db_name, $mysqlconnection);
	mysql_query("SET NAMES 'greek'", $mysqlconnection);
	mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
	
    if ($_POST['type']){
        $dim_ar = array('1', '2', '3');
        $nip_ar = array('4', '5', '6');       
        //if ($_GET['type'] == 1)
        if (in_array($_POST['type'],$dim_ar))
        {
            $type = 1;
            //type2: 0 δημόσιο, 1 ιδιωτικό, 2 ειδικό
            $type2 = $_POST['type'] - 1;
            
            $query = "SELECT * from school WHERE type = $type AND type2=$type2";
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);
        
                echo "<center>";
                $i=0;
                ob_start();
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                    echo "<thead><tr><th>Ονομασία</th>";
                    echo "<th>Κωδικός</th>";
                    echo "<th>ΕΑΕΠ</th>";
                    echo "<th>Οργανικ.</th>";
                    echo "<th>Λειτουργ.</th>";
                    echo "<th>Α'</th>";
                    echo "<th>Β'</th>";
                    echo "<th>Γ'</th>";
                    echo "<th>Δ'</th>";
                    echo "<th>Ε'</th>";
                    echo "<th>ΣΤ'</th>";
                    echo "<th>Σύνολο</th>";
                    echo "<th>Τμ. Α'</th>";
                    echo "<th>Τμ. Β'</th>";
                    echo "<th>Τμ. Γ'</th>";
                    echo "<th>Τμ. Δ'</th>";
                    echo "<th>Τμ. Ε'</th>";
                    echo "<th>Τμ. ΣΤ'</th>";
                    echo "<th>Σύνολο Τμ.</th>";
                    echo "<th>Εκπ/κοί Ολοημέρου</th>";
                    echo "<th>Μαθητές Ολοημέρου</th>";
                    echo "<th>Εκπ/κοί T.E.</th>";
                    echo "<th>Εκπ/κοί T.Y.</th>";
                    echo "<th>Παρόντες Εκπ/κοί ΠΕ70</th>";
                    echo "</tr></thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        $organikothta = mysql_result($result, $i, "organikothta");
                        $code = mysql_result($result, $i, "code");
                        $eaep_field = mysql_result($result, $i, "code");
                        if ($eaep_field)
                            $eaep = "NAI";
                        else
                            $eaep = "OXI";
                        $leitoyrg = mysql_result($result, $i, "leitoyrg");
                        $sch = mysql_result($result, $i, "id");
                        $name = getSchool($sch, $mysqlconnection);
                        $students = mysql_result($result, $i, "students");
                        $classes = explode(",",$students);
                        //$frontistiriako = mysql_result($result, $i, "frontistiriako");
                        $oloimero_stud = mysql_result($result, $i, "oloimero_stud");
                        $tmimata = mysql_result($result, $i, "tmimata");
                        $tmimata_exp = explode(",",$tmimata);
                        $oloimero_tea = mysql_result($result, $i, "oloimero_tea");
                        $ekp_ee = mysql_result($result, $i, "ekp_ee");
                        $ekp_ee_exp = explode(",",$ekp_ee);

                        $synolo = array_sum($classes);
                        $synolo_tmim = array_sum($tmimata_exp);
                        
                        
                        $ekpqry = "SELECT count(*) as a FROM yphrethsh y JOIN employee e ON e.id=y.emp_id WHERE sxol_etos=".$sxol_etos." AND yphrethsh = ".$sch." AND e.klados = 2 AND e.status = 1";
                        $res2 = mysql_query($ekpqry, $mysqlconnection);
                        $mon_ekpkoi = mysql_result($res2, 0);
                        $ekpqry = "SELECT count(*) FROM ektaktoi e WHERE sx_yphrethshs = ".$sch." AND e.klados = 2";
                        $res2 = mysql_query($ekpqry, $mysqlconnection);
                        $ekt_ekpkoi = mysql_result($res2, 0);
                        $ekpkoi = $mon_ekpkoi + $ekt_ekpkoi;
                        
                        

                        echo "<tr>";
                        echo "<td><a href='../school/school_status.php?org=$sch'>$name</a></td><td>$code</td><td>$eaep</td><td>$organikothta</td><td>$leitoyrg</td><td>$classes[0]</td><td>$classes[1]</td><td>$classes[2]</td><td>$classes[3]</td><td>$classes[4]</td><td>$classes[5]</td><td>$synolo</td>\n";
                        echo "<td>$tmimata_exp[0]</td><td>$tmimata_exp[1]</td><td>$tmimata_exp[2]</td><td>$tmimata_exp[3]</td><td>$tmimata_exp[4]</td><td>$tmimata_exp[5]</td><td>$synolo_tmim</td>\n";
                        echo "<td>$oloimero_tea</td><td>$oloimero_stud</td><td>$ekp_ee_exp[0]</td><td>$ekp_ee_exp[1]</td><td>$ekpkoi</td>";
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
                        $sumee[0] += $ekp_ee_exp[0];
                        $sumee[1] += $ekp_ee_exp[1];
                        
                        $i++;                        
                }
        //}	
                $synolo_stud = array_sum($sums);
                $synolo_teach =  array_sum($sumt);
                echo "<tr><td>Σύνολα</td><td></td><td></td><td>$sums[0]</td><td>$sums[1]</td><td>$sums[2]</td><td>$sums[3]</td><td>$sums[4]</td><td>$sums[5]</td><td>$synolo_stud</td>";
                echo "<td>$sumt[0]</td><td>$sumt[1]</td><td>$sumt[2]</td><td>$sumt[3]</td><td>$sumt[4]</td><td>$sumt[5]</td><td>$synolo_teach</td>";
                echo "<td>$sumol</td><td>$sumolstud</td><td>$sumee[0]</td><td>$sumee[1]</td></tr>";
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
        //else if ($_GET['type'] == 2)
        else if (in_array($_POST['type'],$nip_ar))
        {
//          //nipiagogeia
            $type = 2;
            //type2: 0 δημόσιο, 1 ιδιωτικό, 2 ειδικό
            $type2 = $_POST['type'] - 4;
            
            $query = "SELECT * from school WHERE type = $type AND type2=$type2";
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);
            
                echo "<center>";
                $i=0;
                ob_start();
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
                    echo "<thead><tr><th>Ονομασία</th>";
                    echo "<th>Οργ.</th>";
                    echo "<th>Λειτ.</th>";
                    echo "<th>1 Νηπ.</th>";
                    echo "<th>1 Προ.</th>";
                    /*
                    echo "<th>2 Νηπ.</th>";
                    echo "<th>2 Προ.</th>";
                    echo "<th>3 Νηπ.</th>";
                    echo "<th>3 Προ.</th>";
                    echo "<th>4 Νηπ.</th>";
                    echo "<th>4 Προ.</th>";
                     */
                    echo "<th>Σύν.Νηπ.</th>";
                    echo "<th>Σύν.Προ.</th>";
                    
                    echo "<th>Ολ.1 Νηπ.</th>";
                    echo "<th>Ολ.1 Προ.</th>";
                    /*
                    echo "<th>Ολ.2 Νηπ.</th>";
                    echo "<th>Ολ.2 Προ.</th>";
                    echo "<th>Ολ.3 Νηπ.</th>";
                    echo "<th>Ολ.3 Προ.</th>";
                    echo "<th>Ολ.4 Νηπ.</th>";
                    echo "<th>Ολ.4 Προ.</th>";
                     */
                    echo "<th>Σύν.Νηπ.</th>";
                    echo "<th>Σύν.Προ.</th>";
                    
                    echo "<th>Εκπ/κοί Κλασικού</th>";
                    echo "<th>Εκπ/κοί Ολοημέρου</th>";
                    echo "<th>Εκπ/κοί T.E.</th>";
                    echo "<th>Σύν.Εκπ/κών</th>";
                    echo "</tr></thead>\n<tbody>\n";

                while ($i < $num)
                {		
                        
                    $sch = mysql_result($result, $i, "id");
                    $organikothta = mysql_result($result, $i, "organikothta");
                    $leitoyrg = mysql_result($result, $i, "leitoyrg");
                    $name = getSchool($sch, $mysqlconnection);
                    $klasiko = mysql_result($result, $i, "klasiko");
                    $klasiko_exp = explode(",",$klasiko);
                    $oloimero_nip = mysql_result($result, $i, "oloimero_nip");
                    $oloimero_nip_exp = explode(",",$oloimero_nip);
                    $nip = mysql_result($result, $i, "nip");
                    $nip_exp = explode(",",$nip);

                    echo "<tr>";
                    echo "<td><a href='../school/school_status.php?org=$sch'>$name</a></td><td>$organikothta</td><td>$leitoyrg</td>";
                    echo "<td>$klasiko_exp[0]</td><td>$klasiko_exp[1]</td>";
                    //echo "<td>$klasiko_exp[2]</td><td>$klasiko_exp[3]</td><td>$klasiko_exp[4]</td><td>$klasiko_exp[5]</td><td>$klasiko_exp[6]</td><td>$klasiko_exp[7]</td>";
                    $klasiko_nip = $klasiko_exp[0] + $klasiko_exp[2] + $klasiko_exp[4] + $klasiko_exp[6];
                    $klasiko_pro = $klasiko_exp[1] + $klasiko_exp[3] + $klasiko_exp[5] + $klasiko_exp[7];
                    echo "<td>$klasiko_nip</td><td>$klasiko_pro</td>";
                    
                    echo "<td>$oloimero_nip_exp[0]</td><td>$oloimero_nip_exp[1]</td>";
                    //echo "<td>$oloimero_nip_exp[2]</td><td>$oloimero_nip_exp[3]</td>";
                    //echo "<td>$oloimero_nip_exp[4]</td><td>$oloimero_nip_exp[5]</td>";
                    //echo "<td>$oloimero_nip_exp[6]</td><td>$oloimero_nip_exp[7]</td>";
                    $oloimero_syn_nip = $oloimero_nip_exp[0] + $oloimero_nip_exp[2] + $oloimero_nip_exp[4] + $oloimero_nip_exp[6];
                    $oloimero_syn_pro = $oloimero_nip_exp[1] + $oloimero_nip_exp[3] + $oloimero_nip_exp[5] + $oloimero_nip_exp[7];
                    echo "<td>$oloimero_syn_nip</td><td>$oloimero_syn_pro</td>";
                    
                    echo "<td>$nip_exp[0]</td><td>$nip_exp[1]</td><td>$nip_exp[2]</td>";
                    $nip_syn = array_sum($nip_exp);
                    echo "<td>$nip_syn</td>";
                    echo "</tr>\n";

                    // sums
                    for ($c=0; $c<8; $c++)
                    {
                        $sumk[$c] += $klasiko_exp[$c];
                        $sumol[$c] += $oloimero_nip_exp[$c];
                    }
                    for ($c=0; $c<3; $c++)
                    {
                        $sumnip[$c] += $nip_exp[$c];
                    }
                   
                    $i++;
                }
                
                $synolo_nip = $sumk[0]+$sumk[2]+$sumk[4]+$sumk[6];
                $synolo_pro = $sumk[1]+$sumk[3]+$sumk[5]+$sumk[7];
                $synolo_ol_nip = $sumol[0]+$sumol[2]+$sumol[4]+$sumol[6];
                $synolo_ol_pro = $sumol[1]+$sumol[3]+$sumol[5]+$sumol[7];
                $synolo_nipiag = array_sum($sumnip);
                
                echo "<tr>";
                echo "<td>Σύνολα</td><td></td><td></td>";
                //for ($c=0; $c<8; $c++)
                //      echo "<td>$sumk[$c]</td>";
                echo "<td>$sumk[0]</td><td>$sumk[1]</td>";
                echo "<td>$synolo_nip</td>";
                echo "<td>$synolo_pro</td>";
                //for ($c=0; $c<8; $c++)
                //        echo "<td>$sumol[$c]</td>";
                echo "<td>$sumol[0]</td><td>$sumol[1]</td>";
                echo "<td>$synolo_ol_nip</td>";
                echo "<td>$synolo_ol_pro</td>";
                for ($c=0; $c<3; $c++)
                        echo "<td>$sumnip[$c]</td>";
                echo "<td>$synolo_nipiag</td>";
                echo "</tr>";
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
   }
?>
                       
  
		</body>
		</html>
                