<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once "../config.php";
  require_once "../tools/functions.php";
  //define("L_LANG", "el_GR"); Needs fixing
  require('../tools/calendar/tc_calendar.php');
  
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
  // Demand authorization                
  include("../tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false){
    header("Location: ../tools/login.php");
  }
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Καρτέλα σχολείου</title>
	
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery.validate.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
	<script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
	<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
	<script type="text/javascript">
	
	$().ready(function() {
        $("#org").autocomplete("../employee/get_school.php", {
            width: 260,
            matchContains: true,
            selectFirst: false
        });
    });
	$(document).ready(function() { 
        $("#mytbl").tablesorter({widgets: ['zebra']}); 
        $("#mytbl2").tablesorter({widgets: ['zebra']});
        $("#mytbl3").tablesorter({widgets: ['zebra']});
        $("#mytbl4").tablesorter({widgets: ['zebra']});
        $("#mytbl5").tablesorter({widgets: ['zebra']});
        $("#mytbl6").tablesorter({widgets: ['zebra']});
        $('#toggleBtn').click(function(){
            event.preventDefault();
            $("#analysis").slideToggle();
        });
        $('#toggleSystegBtn').click(function(){
            event.preventDefault();
            $("#systeg").slideToggle();
        });
    });	
	</script>
  </head>
  <body> 
  <?php include('../etc/menu.php'); ?>
    <center>
        <h2>Καρτέλα σχολείου</h2>
    <?php
    function disp_school ($sch,$sxol_etos,$conn)
	{
		$query = "SELECT * from school where id=$sch";
		$result = mysql_query($query, $conn);
                
        $titlos = mysql_result($result, 0, "titlos");
        $address = mysql_result($result, 0, "address");
        $tk = mysql_result($result, 0, "tk");
        $dimos = mysql_result($result, 0, "dimos");
        $dimos = getDimos($dimos, $conn);
        $cat = getCategory(mysql_result($result, 0, "category"));
        $tel = mysql_result($result, 0, "tel");
        $fax = mysql_result($result, 0, "fax");
        $email = mysql_result($result, 0, "email");
        $type = mysql_result($result, 0, "type");
        $organikothta = mysql_result($result, 0, "organikothta");
        $leitoyrg = mysql_result($result, 0, "leitoyrg");
        // organikes - added 05-10-2012
        $organikes = unserialize(mysql_result($result, 0, "organikes"));
        // kena_org, kena_leit - added 19-06-2013
        $kena_org = unserialize(mysql_result($result, 0, "kena_org"));
        $kena_leit = unserialize(mysql_result($result, 0, "kena_leit"));
        $code = mysql_result($result, 0, "code");
        $updated = mysql_result($result, 0, "updated");
        $perif = mysql_result($result, 0, "perif");
        $systeg = mysql_result($result, 0, "systeg");
        $anenergo = mysql_result($result, 0, "anenergo");
        if ($systeg){
            $systegName = getSchool($systeg, $conn);
        }
                        
        // if dimotiko
        if ($type == 1)
        {
            $students = mysql_result($result, 0, "students");
            $classes = explode(",",$students);
            $frontistiriako = mysql_result($result, 0, "frontistiriako");
            $ted = mysql_result($result, 0, "ted");
            //$oloimero_stud = mysql_result($result, 0, "oloimero_stud");
            $tmimata = mysql_result($result, 0, "tmimata");
            $tmimata_exp = explode(",",$tmimata);
            //$oloimero_tea = mysql_result($result, 0, "oloimero_tea");
            $ekp_ee = mysql_result($result, 0, "ekp_ee");
            $ekp_ee_exp = explode(",",$ekp_ee);
            
            $synolo = array_sum($classes);
            //$synolo_tmim = array_sum($tmimata_exp);
            $vivliothiki = mysql_result($result, 0, "vivliothiki");
        }
        //if nipiagwgeio
        if ($type == 2)
        {
            $klasiko = mysql_result($result, 0, "klasiko");
            $klasiko_exp = explode(",",$klasiko);
            $oloimero_nip = mysql_result($result, 0, "oloimero_nip");
            $oloimero_nip_exp = explode(",",$oloimero_nip);
            $nip = mysql_result($result, 0, "nip");
            $nip_exp = explode(",",$nip);
        }
        // entaksis (varchar): on/off, no. of students
        $entaksis = explode(",",mysql_result($result, 0, "entaksis"));
        $ypodoxis = mysql_result($result, 0, "ypodoxis");
        //$frontistiriako = mysql_result($result, 0, "frontistiriako");
        $oloimero = mysql_result($result, 0, "oloimero");
        $comments = mysql_result($result, 0, "comments");
        
        echo "<table class=\"imagetable\" border='1'>";
        echo "<tr><td colspan=3>Τίτλος (αναλυτικά): $titlos</td></tr>";
        echo "<tr><td>Δ/νση: $address - Τ.Κ. $tk - Δήμος: $dimos</td><td>Τηλ.: $tel</td></tr>";
        echo "<tr><td>email: <a href=\"mailto:$email\">$email</a></td><td>Fax: $fax</td></tr>";
        echo "<tr><td>Οργανικότητα: $organikothta</td><td>Λειτουργικότητα: $leitoyrg</td></tr>";
        
        // οργανικά τοποθετηθέντες
        $klados_qry = ($type == 1) ? 2 : 1;
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados= $klados_qry AND status IN (1,3) AND thesi IN (0,1,2)";
        $rs = mysql_query($qry, $conn);
        $orgtop = mysql_result($rs, 0, "cnt");
        echo "<tr><td>Οργανικά τοποθετηθέντες (πλην Τ.Ε.): $orgtop</td><td colspan=3>Κατηγορία: $cat</td></tr>";
        
        // 05-10-2012 - organikes
        for ($i=0; $i<count($organikes); $i++)
            if (!$organikes[$i])
                $organikes[$i]=0;
        if ($type == 1)
            echo "<tr><td colspan=2>Οργανικές: ΠΕ70: $organikes[0] /";
        else
            echo "<tr><td colspan=2>Οργανικές: ΠΕ60: $organikes[0] /";
        echo "&nbsp;&nbsp;Φυσ. Αγωγής: $organikes[1] /";
        echo "&nbsp;&nbsp;Αγγλικών: $organikes[2] /";
        echo "&nbsp;&nbsp;Μουσικής: $organikes[3]";
        echo "</td></tr>";
        // 05-10-2012 - kena_leit, kena_org
        for ($i=0; $i<count($kena_org); $i++)
            if (!$kena_org[$i])
                $kena_org[$i]=0;
        if ($type == 1)
            echo "<tr><td colspan=2>Οργ. Κενά: ΠΕ70: $kena_org[0] /";
        else
            echo "<tr><td colspan=2>Οργ. Κενά: ΠΕ60: $kena_org[0] /";
        echo "&nbsp;&nbsp;Φυσ. Αγωγής: $kena_org[1] /";
        echo "&nbsp;&nbsp;Αγγλικών: $kena_org[2] /";
        echo "&nbsp;&nbsp;Μουσικής: $kena_org[3]";
        echo "</td></tr>";
        for ($i=0; $i<count($kena_leit); $i++)
            if (!$kena_leit[$i])
                $kena_leit[$i]=0;
        if ($type == 1)
            echo "<tr><td colspan=2>Λειτ. Κενά: ΠΕ70: $kena_leit[0] /";
        else
            echo "<tr><td colspan=2>Λειτ. Κενά: ΠΕ60: $kena_leit[0] /";
        echo "&nbsp;&nbsp;Φυσ. Αγωγής: $kena_leit[1] /";
        echo "&nbsp;&nbsp;Αγγλικών: $kena_leit[2] /";
        echo "&nbsp;&nbsp;Μουσικής: $kena_leit[3]";
        echo "</td></tr>";
        // end of leit,org
        //echo "<tr><td colspan=2>Μαθητές: $synolo</td></tr>";
        //echo "<tr><td colspan=2>Α': $classes[0], Β': $classes[1], Γ': $classes[2], Δ': $classes[3], Ε': $classes[4], ΣΤ': $classes[5]</td></tr>";
        echo "<tr>";
        if ($entaksis[0])
            echo "<td><input type=\"checkbox\" checked disabled>Τμήμα Ένταξης / Μαθητές: $entaksis[1]</td>";
        else
            echo "<td><input type=\"checkbox\" disabled>Τμήμα Ένταξης</td>";
        if ($ypodoxis)
            echo "<td><input type=\"checkbox\" checked disabled>Τμήμα Υποδοχής</td>";
        else
            echo "<td><input type=\"checkbox\" disabled>Τμήμα Υποδοχής</td>";
        echo "</tr>";
        if ($entaksis[0] || $ypodoxis)
            echo "<tr><td>Εκπ/κοί Τμ.Ένταξης: $ekp_ee_exp[0]</td><td>Εκπ/κοί Τμ.Υποδοχής: $ekp_ee_exp[1]</td></tr>";

        echo "<tr>";
        if ($type == 1)
        {
            if ($frontistiriako)
                echo "<td><input type=\"checkbox\" checked disabled>Φροντιστηριακό Τμήμα</td>";
            else
                echo "<td><input type=\"checkbox\" disabled>Φροντιστηριακό Τμήμα</td>";
        }
        // if nip print Proini Zoni (klasiko[6])
        else {
            if ($klasiko_exp[6])
                echo "<td><input type=\"checkbox\" checked disabled>Πρωινή Ζώνη / Μαθητές: $klasiko_exp[6]</td>";
            else
                echo "<td><input type=\"checkbox\" disabled>Πρωινή Ζώνη</td>";
        }
                                            
        if ($oloimero)
        {
            if ($type == 1)
            {
            echo "<td><input type=\"checkbox\" checked disabled>Όλοήμερο</td></tr>";
            //echo "<tr><td>Μαθητές Ολοημέρου: $oloimero_stud</td>";
            //echo "<td>Εκπ/κοί Ολοημέρου: $oloimero_tea</td></tr>";
            }
            else
                echo "<td><input type=\"checkbox\" checked disabled>Όλοήμερο</td></tr>";
        }
        else
            echo "<td><input type=\"checkbox\" disabled>Όλοήμερο</td></tr>";
        
        if ($type == 1)
        {
            echo "<tr>";
            if ($ted)
                echo "<td><input type=\"checkbox\" checked disabled>Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</td>";
            else
                echo "<td><input type=\"checkbox\" disabled>Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</td>";
            if ($vivliothiki)
                echo "<td><input type=\"checkbox\" checked disabled>Σχολική βιβλιοθήκη</td>";
            else
                echo "<td><input type=\"checkbox\" disabled>Σχολική βιβλιοθήκη</td>";
            echo "</tr>";
            echo "<tr><td>Περιφέρεια Σχολικών Συμβούλων: ".$perif."η</td>";
            echo $anenergo ? "<td>Κατάσταση: Σε αναστολή</td>" : "<td>Κατάσταση: Ενεργό</td>";
            echo "</tr>";
        }
        
        echo "<tr><td>Σχόλια: $comments</td><td>Κωδικός ΥΠΑΙΘ: $code</td></tr>";
        if ($systeg){
            echo "<tr><td colspan=2>Συστεγαζόμενη σχολική μονάδα: <a href='school_status.php?org=$systeg' target='_blank'>$systegName</td></tr>";    
        }
        if ($updated>0)
            echo "<tr><td colspan=2 align=right><small>Τελ.ενημέρωση: ".date("d-m-Y H:i",strtotime($updated))."<small></td></tr>";
        echo "</table>";
        echo "<br>";
        
        
        if ($type == 1)
        {
            if ($synolo>0)
            {
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td></td><td>Α'</td><td>Β'</td><td>Γ'</td><td>Δ'</td><td>Ε'</td><td>ΣΤ'</td><td class='tdnone'><i>Ολ</i></td><td class='tdnone'><i>ΠΖ</i></td></tr>";
                $synolo_pr = $classes[0]+$classes[1]+$classes[2]+$classes[3]+$classes[4]+$classes[5];
                echo "<tr><td>Μαθ.Πρωινού<br>Σύνολο: $synolo_pr</td><td>$classes[0]</td><td>$classes[1]</td><td>$classes[2]</td><td>$classes[3]</td><td>$classes[4]</td><td>$classes[5]</td><td class='tdnone'><i>$classes[6]</i></td><td class='tdnone'><i>$classes[7]</i></td></tr>";
                $synolo_pr = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
                echo "<tr><td>Τμ./τάξη Πρωινού<br>Σύνολο: $synolo_pr</td><td>$tmimata_exp[0]</td><td>$tmimata_exp[1]</td><td>$tmimata_exp[2]</td><td>$tmimata_exp[3]</td><td>$tmimata_exp[4]</td><td>$tmimata_exp[5]</td><td class='tdnone'><i>$tmimata_exp[6]<small> (14-15)</small><br>$tmimata_exp[7]<small> (15-16)</small></i></td><td class='tdnone'><i>$tmimata_exp[8]</i></td></tr>";
                echo "</table>";
                echo "<br>";
            }
            else 
            {
                echo "Δεν έχει καταχωρηθεί πλήθος μαθητών";
                echo "<br><br>";
            }
        }
        else if ($type == 2)
        {
            // klasiko_nip/pro: klasiko
            // klasiko pos 0-5: 0,1 t1n,p / 2,3 t2n,p / 4,5 t3n,p
            // prwinh zvnh @ pos 7 -> klasiko[6]
            // oloimero_syn_nip/pro: oloimero
            $klasiko_nip = $klasiko_exp[0] + $klasiko_exp[2] + $klasiko_exp[4];
            $klasiko_pro = $klasiko_exp[1] + $klasiko_exp[3] + $klasiko_exp[5];
            $oloimero_syn_nip = $oloimero_nip_exp[0] + $oloimero_nip_exp[2] + $oloimero_nip_exp[4] + $oloimero_nip_exp[6];
            $oloimero_syn_pro = $oloimero_nip_exp[1] + $oloimero_nip_exp[3] + $oloimero_nip_exp[5] + $oloimero_nip_exp[7];
            $meikto_nip = $klasiko_exp[6] + $klasiko_exp[8];
            $meikto_pro = $klasiko_exp[7] + $klasiko_exp[9];
            // Μαθητές
            echo "<h3>Μαθητές</h3>";
            echo "<table class=\"imagetable\" border='1'>";
            $ola = $klasiko_nip + $klasiko_pro;
            $olola = $oloimero_syn_nip + $oloimero_syn_pro;
            echo "<tr><td rowspan=2>Τμήμα</td><td colspan=3>Κλασικό</small></td><td colspan=3>Ολοήμερο</td></tr>";
            echo "<tr><td>Νήπια</td><td>Προνήπια</td><td>Σύνολο</td><td>Νήπια</td><td>Προνήπια</td><td>Σύνολο</td></tr>";
            // t1
            $syn = $klasiko_exp[0]+$klasiko_exp[1];
            $tmimata_nip = 1;
            $tmimata_nip_ol = 0;
            echo "<tr><td>Τμ.1</td><td>$klasiko_exp[0]</td><td>$klasiko_exp[1]</td><td>$syn</td>";
            $syn_ol = $oloimero_nip_exp[0]+$oloimero_nip_exp[1];
            if ($syn_ol > 0){
                $tmimata_nip_ol += 1;
            }
            echo "<td>$oloimero_nip_exp[0]</td><td>$oloimero_nip_exp[1]</td><td>$syn_ol</td></tr>";
            // print t2 + t3 only if they have students
            // t2
            $syn2 = $klasiko_exp[2]+$klasiko_exp[3];
            $syn_ol2 = $oloimero_nip_exp[2]+$oloimero_nip_exp[3];
            if (($syn2+$syn_ol2) > 0){
                $tmimata_nip += 1;
                if ($syn_ol2 > 0){
                    $tmimata_nip_ol += 1;
                }
                echo "<tr><td>Τμ.2</td><td>$klasiko_exp[2]</td><td>$klasiko_exp[3]</td><td>$syn2</td>";
                echo "<td>$oloimero_nip_exp[2]</td><td>$oloimero_nip_exp[3]</td><td>$syn_ol2</td></tr>";
            }
            // t3
            $syn3 = $klasiko_exp[4]+$klasiko_exp[5];
            $syn_ol3 = $oloimero_nip_exp[4]+$oloimero_nip_exp[5];
            if (($syn3+$syn_ol3) > 0){
                $tmimata_nip += 1;
                if ($syn_ol3 > 0){
                    $tmimata_nip_ol += 1;
                }
                echo "<tr><td>Τμ.3</td><td>$klasiko_exp[4]</td><td>$klasiko_exp[5]</td><td>$syn3</td>";
                echo "<td>$oloimero_nip_exp[4]</td><td>$oloimero_nip_exp[5]</td><td>$syn_ol3</td></tr>";
            }
            // totals (if more than one tmima)
            if (($syn2 + $syn_ol2 + $syn3 + $syn_ol3) > 0){
                echo "<tr><td><strong>Σύνολα</strong></td><td>$klasiko_nip<td>$klasiko_pro</td><td>$ola</td>";
                echo "<td>$oloimero_syn_nip<td>$oloimero_syn_pro</td><td>$olola</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<br>";
            
            $has_entaxi = strlen($entaksis[0])>1 ? 1 : 0; 
            // τοποθετημένοι εκπ/κοί
            $top60 = $top60m = $top60ana = 0;
            $qry = "SELECT count(*) as pe60 FROM employee WHERE sx_yphrethshs = $sch AND klados=1 AND status=1";
            $res = mysql_query($qry, $conn);
            $top60m = mysql_result($res, 0, 'pe60');
            $qry = "SELECT count(*) as pe60 FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados=1 AND status=1";
            $res = mysql_query($qry, $conn);
            $top60ana = mysql_result($res, 0, 'pe60');
            $top60 = $top60m+$top60ana;
            
            $syn_apait = $tmimata_nip+$tmimata_nip_ol+$has_entaxi;

            echo "<h3>Λειτουργικά κενά</h3>";
            echo "<table class=\"imagetable stable\" border='1'>";
            echo "<thead><th></th><th>Αριθμός</th><th>Κλασικό</th><th>Ολοήμερο</th><th>Τμ.Ένταξης</th></thead><tbody>";

            echo "<tr><td>Απαιτούμενοι Νηπιαγωγοί</td>";
            echo "<td>$syn_apait</td>";
            echo "<td>$tmimata_nip</td><td>$tmimata_nip_ol</td><td>$has_entaxi</td></tr>";

            echo "<tr><td>Υπάρχοντες Νηπιαγωγοί</td><td>$top60</td><td></td><td></td><td></td></tr>";
            $k_pl = $top60-$syn_apait;
            $k_pl_class = $k_pl >= 0 ? 
                "'background:none;background-color:rgba(0, 255, 0, 0.37)'" : 
                "'background:none;background-color:rgba(255, 0, 0, 0.45)'";
            echo "<tr><td>+ / -</td><td style=$k_pl_class>$k_pl</td><td></td><td></td><td></td></tr>";

            echo "</tbody></table>";
            echo "<br>";
        }
        echo "<INPUT TYPE='button' VALUE='Επεξεργασία' onClick=\"parent.location='school_edit.php?org=$sch'\">";
        echo "&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Εκδρομές' onClick=\"parent.location='ekdromi.php?sch=$sch&op=list'\">";
        echo "<br><br>";
        // if dimotiko & leitoyrg >= 4
        if ($type == 1 && array_sum($tmimata_exp)>3){
            ektimhseis_wrwn($sch, $conn, $sxol_etos, TRUE);
        }
        // if systegazomeno
        if ($systeg) {
            echo "<a id='toggleSystegBtn' href='#'>Συστεγαζόμενο: $systegName</a>";
            echo "<div id='systeg' style='display: none;'>";
            ektimhseis_wrwn($systeg, $conn, $sxol_etos, TRUE);
            echo "</div>";
            echo "<br><br>";
        }
	} // of disp_school
      
    echo "<div id=\"content\">";
    echo "<form id='searchfrm' name='searchfrm' action='' method='POST' autocomplete='off'>";
    echo "<table class=\"imagetable stable\" border='1'>";
    echo "<td>Σχολείο</td><td></td><td><input type=\"text\" name=\"org\" id=\"org\" /></td></tr>";				
    echo "	</table>";
    echo "	<input type='submit' value='Αναζήτηση'>";
    //echo "  &nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value=\"Επαναφορά\" onClick=\"window.location.reload()\">";
    echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "	</form>";
    echo "</div>";
    
    if (isset($_POST['org']) || isset($_GET['org']))
    {
        $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
        mysql_select_db($db_name, $mysqlconnection);
        mysql_query("SET NAMES 'greek'", $mysqlconnection);
        mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
        
        if (isset($_POST['org'])){
            $str1 = $_POST['org'];
            $sch = getSchoolID($_POST['org'],$mysqlconnection);
            if (!$sch){
                die('Το σχολείο δε βρέθηκε...');
            }
        }
        elseif (isset($_GET['org'])){
            $str1 = getSchool ($_GET['org'],$mysqlconnection);
            $sch = $_GET['org'];
            if (!$str1){
                die('Το σχολείο δε βρέθηκε...');
            }
        }
        
        echo "<h1>$str1</h1>";
        if (!$sch && !$str){
            die('Το σχολείο δε βρέθηκε...');
        }
        if (isset($_GET['sxoletos'])){
            $sxol_etos = $_GET['sxoletos'];
        }
        disp_school($sch, $sxol_etos, $mysqlconnection);
        
        //Υπηρετούν με θητεία
        $query = "SELECT * from employee WHERE sx_yphrethshs='$sch' AND status=1 AND thesi in (1,2,6)";
        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_num_rows($result);
        if ($num)
        {
            echo "<h3>Υπηρετούν με θητεία</h3><br>";
            
            $i=0;
            echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Θέση</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {            
                $id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
                $klados_id = mysql_result($result, $i, "klados");
                $klados = getKlados($klados_id,$mysqlconnection);
                $thesi = mysql_result($result, $i, "thesi");
                $th = thesicmb($thesi);
                $comments = mysql_result($result, $i, "comments");

                echo "<tr>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$th</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }                   
        //Ανήκουν οργανικά και υπηρετούν (ΠΕ60-70)
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0";
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0 ORDER BY klados";
        $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,5) AND (klados=2 OR klados=1)";
        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_num_rows($result);
        if ($num)
        {
            echo "<h3>Ανήκουν οργανικά και υπηρετούν (ΠΕ60/ΠΕ70)</h3>";
            $i=0;
            echo "<table id=\"mytbl2\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {            
                $id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
                $klados_id = mysql_result($result, $i, "klados");
                $klados = getKlados($klados_id,$mysqlconnection);
                $comments = mysql_result($result, $i, "comments");
                echo "<tr>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        //Ανήκουν οργανικά και υπηρετούν (Ειδικότητες)
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0";
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0 ORDER BY klados";
        $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,5) AND klados!=2 AND klados!=1";
        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_num_rows($result);
        if ($num)
        {
            echo "<h3>Ανήκουν οργανικά και υπηρετούν (Ειδικότητες)</h3>";
            $i=0;
            echo "<table id=\"mytbl2\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {            
                $id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
                $klados_id = mysql_result($result, $i, "klados");
                $klados = getKlados($klados_id,$mysqlconnection);
                $comments = mysql_result($result, $i, "comments");

                echo "<tr>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        
        
        // Οργανική αλλού και υπηρετούν
        $query = "SELECT * from employee WHERE sx_organikhs!='$sch' AND sx_yphrethshs='$sch' AND thesi in (0,5) AND status=1 ORDER BY klados";
        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_num_rows($result);
        if ($num)
        {
            echo "<h3>Με οργανική σε άλλο σχολείο και υπηρετούν</h3>";
            $i=0;
            echo "<table id=\"mytbl3\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχολείο Οργανικής</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {
                $id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
                $klados_id = mysql_result($result, $i, "klados");
                $klados = getKlados($klados_id,$mysqlconnection);
                $sx_organ_id = mysql_result($result, $i, "sx_organikhs");
                $sx_organikhs = getSchool ($sx_organ_id, $mysqlconnection);
                $comments = mysql_result($result, $i, "comments");
                
                echo "<tr>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        
        // Οργανική αλλού και δευτερεύουσα υπηρέτηση
        //$query = "SELECT * from employee WHERE sx_organikhs!='$sch' AND (sx_yphrethshs='$sch' AND thesi=0";
        $query = "SELECT * FROM employee e join yphrethsh y on e.id = y.emp_id where y.yphrethsh=$sch and e.sx_yphrethshs!=$sch AND y.sxol_etos = $sxol_etos";
        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_num_rows($result);
        if ($num)
        {
            echo "<h3>Με οργανική και κύρια υπηρέτηση σε άλλο σχολείο, που υπηρετούν με διάθεση</h3>";
            $i=0;
            echo "<table id=\"mytbl3\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχολείο Οργανικής</th>";
            echo "<th>Ώρες</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {
                $id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
                $klados_id = mysql_result($result, $i, "klados");
                $klados = getKlados($klados_id,$mysqlconnection);
                $sx_organ_id = mysql_result($result, $i, "sx_organikhs");
                $sx_organikhs = getSchool ($sx_organ_id, $mysqlconnection);
                $comments = mysql_result($result, $i, "comments");
                $hours = mysql_result($result, $i, "hours");
                
                echo "<tr>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$hours</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        //Υπηρετούν σε τμήμα ένταξης
        $query = "SELECT * from employee WHERE sx_yphrethshs='$sch' AND status=1 AND thesi=3";
        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_num_rows($result);
        if ($num)
        {
            echo "<h3>Υπηρετούν σε τμήμα ένταξης</h3>";
            $i=0;
            echo "<table id=\"mytbl2\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχολείο Οργανικής</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {            
                $id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
                $klados_id = mysql_result($result, $i, "klados");
                $klados = getKlados($klados_id,$mysqlconnection);
                $sx_organ_id = mysql_result($result, $i, "sx_organikhs");
                $sx_organikhs = getSchool ($sx_organ_id, $mysqlconnection);
                $comments = mysql_result($result, $i, "comments");

                echo "<tr>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        
        //Έκτακτο Προσωπικό
        //$query = "SELECT * FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos)";
        $query = "SELECT * FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 1)";
        //echo $query;
        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_num_rows($result);
        $sx_yphrethshs = mysql_result($result, 0, "sx_yphrethshs");
        if ($num)
        {
            echo "<h3>Έκτακτο Προσωπικό</h3>";
            $i=0;
            echo "<table id=\"mytbl4\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Τύπος Απασχόλησης</th>";
            echo "<th>Ώρες</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {
                $id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
                $klados_id = mysql_result($result, $i, "klados");
                $klados = getKlados($klados_id,$mysqlconnection);
                $typos = mysql_result($result, $i, "type");
                $type = get_type($typos,$mysqlconnection);
                $comments = mysql_result($result, $i, "comments");
                $wres = mysql_result($result, $i, "hours");
                
                echo "<tr>";
                echo "<td><a href=\"../employee/ektaktoi.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$wres</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        
        //Απουσιάζουν: Ανήκουν οργανικά και υπηρετούν αλλού
        $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs!='$sch' order by klados";
        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_num_rows($result);
        if ($num)
        {
            echo "<h2>Απουσιάζουν</h2>";
            echo "<h3>Ανήκουν οργανικά και υπηρετούν αλλού</h3>";
            $i=0;
            echo "<table id=\"mytbl5\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχολείο/Φορέας Υπηρέτησης</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {       
                $id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
                $klados_id = mysql_result($result, $i, "klados");
                $klados = getKlados($klados_id,$mysqlconnection);
                $sx_yphrethshs_id = mysql_result($result, $i, "sx_yphrethshs");
                $sx_yphrethshs = getSchool ($sx_yphrethshs_id, $mysqlconnection);
                $comments = mysql_result($result, $i, "comments");
                
                echo "<tr>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_yphrethshs</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        
        //Σε άδεια
        //old queries:
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND status=3";
        //$query0 = "SELECT * from adeia WHERE emp_id='$id' AND start<'$today' AND finish>'$today'";
        $today = date("Y-m-d");
        //$query = "SELECT * FROM adeia ad JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND start<'$today' AND finish>'$today'";
        //$query = "SELECT * FROM adeia ad JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND start<'$today' AND finish>'$today' AND status=3";
        //$query = "SELECT * FROM adeia ad RIGHT JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND ((start<'$today' AND finish>'$today') OR status=3)";
        //$query = "SELECT * FROM adeia ad RIGHT JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND ((start<'$today' AND finish>'$today') OR status=3) ORDER BY finish DESC";
        $query = "SELECT * FROM adeia ad RIGHT JOIN employee emp ON ad.emp_id = emp.id WHERE (sx_organikhs='$sch' OR sx_yphrethshs='$sch') AND ((start<'$today' AND finish>'$today') OR status=3) ORDER BY finish DESC";
        //echo $query;
        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_num_rows($result);
        if ($num)
        {
            echo "<h2>Σε Άδεια</h2>";
            echo "<h3>Μόνιμοι</h3>";
            $i=0;
            echo "<table id=\"mytbl6\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Τύπος</th>";
            echo "<th>Ημ/νία Επιστροφής</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            $apontes = array();
            while ($i < $num)
            {
                $flag = $absent = 0;		
                $id = mysql_result($result, $i, "emp_id");
                $adeia_id = mysql_result($result, $i, "id");
                $type = mysql_result($result, $i, "type");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
                $klados_id = mysql_result($result, $i, "klados");
                $klados = getKlados($klados_id,$mysqlconnection);
                $comments = mysql_result($result, $i, "comments");
                $comm = $comments;
                $today = date("Y-m-d");
                $return = mysql_result($result, $i, "finish");
                $start = mysql_result($result, $i, "start");
                $status = mysql_result($result, $i, "status");
                // if return date exists, check if absent and print - else continue.
                if ($return)
                {
                    if ($start<$today && $return>$today)
                    {
                        $flag = $absent = 1;
                        $apontes[] = $id;
                    }
                    else
                    {
                            //$flag=1;
                            if (!in_array($id,$apontes))
                                    $flag = 1;
                            $apontes[] = $id;
                            $comments = "Δεν απουσιάζει.<br>Έχει δηλωθεί κατάσταση \"Σε άδεια\"<br>";
                    }
                    $ret = date("d-m-Y", strtotime($return));
                }
                else
                {
                    $ret="";
                    $id = mysql_result($result, $i, "emp.id");
                    $flag=1;
                    $comments = "Δεν απουσιάζει.<br>Έχει δηλωθεί κατάσταση \"Σε άδεια\"<br>";
                }
                if ($flag)
                {
                    $query1 = "select type from adeia_type where id=$type";
                    $result1 = mysql_query($query1, $mysqlconnection);
                    $typewrd = mysql_result($result1, 0, "type");
                    if ($absent && $status<>3)
                        $comments = "<blink>Παρακαλώ αλλάξτε την κατάσταση του <br>εκπ/κού σε \"Σε Άδεια\"</blink><br>$comm";

                    echo "<tr>";
                    echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$typewrd</td><td><a href='../employee/adeia.php?adeia=$adeia_id&op=view'>$ret</a></td><td>$comments</td>\n";
                    echo "</tr>";
                }
                $i++;
            }
            echo "</tbody></table>";
        }
        //Έκτακτο Προσωπικό σε άδεια
        $query = "SELECT * FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 3)";
        //echo $query;
        $result = mysql_query($query, $mysqlconnection);
        $num = mysql_num_rows($result);
        $sx_yphrethshs = mysql_result($result, 0, "sx_yphrethshs");
        if ($num)
        {
            echo "<h3>Έκτακτο Προσωπικό</h3>";
            $i=0;
            echo "<table id=\"mytbl4\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Τύπος Απασχόλησης</th>";
            echo "<th>Ώρες</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {
                $id = mysql_result($result, $i, "id");
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
                $klados_id = mysql_result($result, $i, "klados");
                $klados = getKlados($klados_id,$mysqlconnection);
                $typos = mysql_result($result, $i, "type");
                $type = get_type($typos,$mysqlconnection);
                $comments = mysql_result($result, $i, "comments");
                $wres = mysql_result($result, $i, "hours");
                
                echo "<tr>";
                echo "<td><a href=\"../employee/ektaktoi.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$wres</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
    } // of school status
?>
</center>
<div id='results'></div>
</body>
</html>
