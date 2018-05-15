<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"../config.php";
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
    header("Location: ../tools/login_check.php");
  }
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Επεξεργασία σχολείου</title>
	
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery.validate.js"></script>
        <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
	<script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
	<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
    <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
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
        });
	</script>
  </head>
  <body>
  <?php include('../etc/menu.php'); ?>
    <center>
        <h2>Επεξεργασία σχολείου</h2>
      <?php     
      
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
                    
                    if (isset($_POST['org']))
                    {
                            $str1 = $_POST['org'];
                            //$str1 = mb_convert_encoding($_POST['org'], "iso-8859-7", "utf-8");
                            $sch = getSchoolID($str1,$mysqlconnection);
                    }
                    else
                    {
                        $sch = $_GET['org'];
                        $str1 = getSchool($sch, $mysqlconnection);
                    }
                    
                    echo "<h1>$str1</h1>";
                    //disp_school($sch, $mysqlconnection);
                    $query = "SELECT * from school where id=$sch";
                    $result = mysql_query($query, $mysqlconnection);

                    $titlos = mysql_result($result, 0, "titlos");
                    $address = mysql_result($result, 0, "address");
                    $tk = mysql_result($result, 0, "tk");
                    $tel = mysql_result($result, 0, "tel");
                    $fax = mysql_result($result, 0, "fax");
                    $email = mysql_result($result, 0, "email");
                    $type = mysql_result($result, 0, "type");
                    $organikothta = mysql_result($result, 0, "organikothta");
                    $leitoyrg = mysql_result($result, 0, "leitoyrg");
                    
                    // if dimotiko
                    if ($type == 1)
                    {
                        $students = mysql_result($result, 0, "students");
                        $classes = explode(",",$students);
                        $frontistiriako = mysql_result($result, 0, "frontistiriako");
                        $ted = mysql_result($result, 0, "ted");
                        $oloimero_stud = mysql_result($result, 0, "oloimero_stud");
                        $tmimata = mysql_result($result, 0, "tmimata");
                        $tmimata_exp = explode(",",$tmimata);
                        $oloimero_tea = mysql_result($result, 0, "oloimero_tea");
                        $ekp_ee = mysql_result($result, 0, "ekp_ee");
                        $ekp_ee_exp = explode(",",$ekp_ee);
                        
                        $synolo = $classes[0]+$classes[1]+$classes[2]+$classes[3]+$classes[4]+$classes[5];
                        $synolo_tmim = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
                    }
                    // if nipiagwgeio
                    else if ($type == 2)
                    {
                        $klasiko = mysql_result($result, 0, "klasiko");
                        $klasiko_exp = explode(",",$klasiko);
                        $oloimero_nip = mysql_result($result, 0, "oloimero_nip");
                        $oloimero_nip_exp = explode(",",$oloimero_nip);
                        $nip = mysql_result($result, 0, "nip");
                        $nip_exp = explode(",",$nip);
                        
                        $klasiko_synolo = array_sum($klasiko_exp);
                        $oloimero_synolo = array_sum($oloimero_nip_exp);
                    }
                    $oloimero = mysql_result($result, 0, "oloimero");
                    $entaksis = explode(",",mysql_result($result, 0, "entaksis"));
                    $ypodoxis = mysql_result($result, 0, "ypodoxis");                    
                    $comments = mysql_result($result, 0, "comments");
                    // organikes - added 05-10-2012
                    $organikes = unserialize(mysql_result($result, 0, "organikes"));
                    // kena_leit, kena_org - added 19-06-2013
                    $kena_org = unserialize(mysql_result($result, 0, "kena_org"));
                    $kena_leit = unserialize(mysql_result($result, 0, "kena_leit"));

                    echo "<table class=\"imagetable\" border='1'>";
                    echo "<form id='updatefrm' name='update' action='school_update.php' method='POST'>";
                    echo "<tr><td colspan=3>Τίτλος (αναλυτικά): <input type='text' name='titlos' value='$titlos' size='80'/></td></tr>";
                    echo "<tr><td>Δ/νση: <input type='text' name='address' value='$address' /> T.K.: <input size='5' type='text' name='tk' value='$tk' /></td><td>Τηλ.: <input type='text' name='tel' value='$tel' /></td></tr>";
                    echo "<tr><td>email: <input type='text' name='email' value='$email' size='30'/></a></td><td>Fax: <input type='text' name='fax' value='$fax' /></td></tr>";
                    echo "<tr><td>Οργανικότητα: <input type='text' name='organ' value='$organikothta' size='2'/><td>Λειτουργικότητα: <input type='text' name='leitoyrg' value='$leitoyrg' size='2'/></td></td></tr>";
                    // 05-10-2012 - organikes
                    if ($type == 1)
                        echo "<tr><td colspan=2>Οργανικές: ΠΕ70: <input type='text' name='organikes[]' value='$organikes[0]' size='2'/>";
                    else
                        echo "<tr><td colspan=2>Οργανικές: ΠΕ60: <input type='text' name='organikes[]' value='$organikes[0]' size='2'/>";
                    echo "&nbsp;&nbsp;Φυσ. Αγωγής: <input type='text' name='organikes[]' value='$organikes[1]' size='2'/>";
                    echo "&nbsp;&nbsp;Αγγλικών: <input type='text' name='organikes[]' value='$organikes[2]' size='2'/>";
                    echo "&nbsp;&nbsp;Μουσικής: <input type='text' name='organikes[]' value='$organikes[3]' size='2'/>";
                    echo "</td></tr>";
                    // 19-06-2013 - kena_org, kena_leit
                    if ($type == 1)
                        echo "<tr><td colspan=2>Οργ. Κενά: ΠΕ70: <input type='text' name='kena_org[]' value='$kena_org[0]' size='2'/>";
                    else
                        echo "<tr><td colspan=2>Οργ. Κενά: ΠΕ60: <input type='text' name='kena_org[]' value='$kena_org[0]' size='2'/>";
                    echo "&nbsp;&nbsp;Φυσ. Αγωγής: <input type='text' name='kena_org[]' value='$kena_org[1]' size='2'/>";
                    echo "&nbsp;&nbsp;Αγγλικών: <input type='text' name='kena_org[]' value='$kena_org[2]' size='2'/>";
                    echo "&nbsp;&nbsp;Μουσικής: <input type='text' name='kena_org[]' value='$kena_org[3]' size='2'/>";
                    echo "</td></tr>";
                    if ($type == 1)
                        echo "<tr><td colspan=2>Λειτ. Κενά: ΠΕ70: <input type='text' name='kena_leit[]' value='$kena_leit[0]' size='2'/>";
                    else
                        echo "<tr><td colspan=2>Λειτ. Κενά: ΠΕ60: <input type='text' name='kena_leit[]' value='$kena_leit[0]' size='2'/>";
                    echo "&nbsp;&nbsp;Φυσ. Αγωγής: <input type='text' name='kena_leit[]' value='$kena_leit[1]' size='2'/>";
                    echo "&nbsp;&nbsp;Αγγλικών: <input type='text' name='kena_leit[]' value='$kena_leit[2]' size='2'/>";
                    echo "&nbsp;&nbsp;Μουσικής: <input type='text' name='kena_leit[]' value='$kena_leit[3]' size='2'/>";
                    echo "</td></tr>";
                    //
                    echo "<tr>";
                    if ($entaksis[0])
                        echo "<td><input type=\"checkbox\" name='entaksis' checked >Τμήμα Ένταξης / Μαθητές: <input type='text' name='entaksis_math' value='$entaksis[1]' size='2'/></td>";
                    else
                        echo "<td><input type=\"checkbox\" name='entaksis'>Τμήμα Ένταξης / Μαθητές: <input type='text' name='entaksis_math' value='$entaksis[1]' size='2'/></td>";
                    if ($ypodoxis)
                        echo "<td><input type=\"checkbox\" name='ypodoxis' checked >Τμήμα Υποδοχής</td>";
                    else
                        echo "<td><input type=\"checkbox\" name='ypodoxis' >Τμήμα Υποδοχής</td>";
                    echo "</tr>";
                    echo "<tr><td>Εκπ/κοί Τμ.Ένταξης: <input type='text' name='ekp_te' size='1' value='$ekp_ee_exp[0]' /></td><td colspan=3>Εκπ/κοί Τμ.Υποδοχής: <input type='text' name='ekp_ty' size='1' value='$ekp_ee_exp[1]' /></td></tr>";
                    echo "<tr>";
                    
                    
                    if ($type == 1)
                    {
                        if ($frontistiriako)
                            echo "<td><input type=\"checkbox\" name='frontistiriako' checked >Φροντιστηριακό Τμήμα</td>";
                        else
                            echo "<td><input type=\"checkbox\" name='frontistiriako' >Φροντιστηριακό Τμήμα</td>";
                        if ($oloimero)
                            echo "<td><input type=\"checkbox\" name='oloimero' checked >Ολοήμερο</td>";
                        else
                            echo "<td><input type=\"checkbox\" name='oloimero' >Όλοήμερο</td>";
                        echo "</tr>";
                        
                        echo "<tr>";
                        if ($ted)
                            echo "<td><input type=\"checkbox\" name='ted' checked >Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</td>";
                        else
                            echo "<td><input type=\"checkbox\" name='ted' >Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</td>";
                        echo "<td></td>";
                        echo "</tr>";
                        
                        echo "<tr><td colspan=2>Σχόλια: <textarea rows='4' cols='80' name='comments'>$comments</textarea></td></tr>";
                        echo "</table>";
                        echo "<br>";
                        
                        //if ($oloimero) - Afairethike gia taxythterh kataxwrhsh...
                        //{
                        /*
                            echo "<table class=\"imagetable\" border='1'>";
                            echo "<tr><td>Μαθητές Ολοημέρου: <input type='text' name='oloimero_stud' value='$oloimero_stud' size='2'/></td>";
                            echo "<td>Εκπ/κοί Ολοημέρου: <input type='text' name='oloimero_tea' value='$oloimero_tea' size='2'/><td></tr>";
                            echo "</table>";
                        */
                        //}
                        echo "<br>";

                        echo "<table class=\"imagetable\" border='1'>";
                        echo "<tr><td colspan=8>Σύνολο Μαθητών Πρ.: $synolo</td></tr>";
                        echo "<tr><td>Α'</td><td>Β'</td><td>Γ'</td><td>Δ'</td><td>Ε'</td><td>ΣΤ'</td><td>Ολ.<small>(15.00/16.00)</small></td><td>ΠΖ</td></tr>";
                        if ($synolo>0)
                            echo "<tr><td><input type='text' name='a' size='1' value=$classes[0] /></td><td><input type='text' name='b' size='1' value=$classes[1] /></td><td><input type='text' name='c' size='1' value=$classes[2] /></td><td><input type='text' name='d' size='1' value=$classes[3] /></td><td><input type='text' name='e' size='1' value=$classes[4] /></td><td><input type='text' name='f' size='1' value=$classes[5] /></td><td><input type='text' name='g' size='1' value=$classes[6] /></td><td><input type='text' name='h' size='1' value=$classes[7] /></td></tr>";
                        else
                            echo "<tr><td><input type='text' name='a' size='1' value='0' /></td><td><input type='text' name='b' size='1' value='0' /></td><td><input type='text' name='c' size='1' value='0' /></td><td><input type='text' name='d' size='1' value='0' /></td><td><input type='text' name='e' size='1' value='0' /></td><td><input type='text' name='f' size='1' value='0' /></td><td><input type='text' name='g' size='1' value='0' /></td><td><input type='text' name='h' size='1' value='0' /></td></tr>";
                        echo "<tr><td colspan=8>Τμήματα (Εκπαιδευτικοί) ανά τάξη<br>Σύνολο Πρ: $synolo_tmim</td></tr>";
                        if ($synolo>0)
                            echo "<tr><td><input type='text' name='ta' size='1' value=$tmimata_exp[0] /></td><td><input type='text' name='tb' size='1' value=$tmimata_exp[1] /></td><td><input type='text' name='tc' size='1' value=$tmimata_exp[2] /></td><td><input type='text' name='td' size='1' value=$tmimata_exp[3] /></td><td><input type='text' name='te' size='1' value=$tmimata_exp[4] /></td><td><input type='text' name='tf' size='1' value=$tmimata_exp[5] /></td><td><input type='text' name='tg' size='1' value=$tmimata_exp[6] /><input type='text' name='th' size='1' value=$tmimata_exp[7] /></td><td><input type='text' name='ti' size='1' value=$tmimata_exp[8] /></td></tr>";
                        else
                            echo "<tr><td><input type='text' name='ta' size='1' value='0' /></td><td><input type='text' name='tb' size='1' value='0' /></td><td><input type='text' name='tc' size='1' value='0' /></td><td><input type='text' name='td' size='1' value='0' /></td><td><input type='text' name='te' size='1' value='0' /></td><td><input type='text' name='tf' size='1' value='0' /></td><td><input type='text' name='tg' size='1' value='0' /></td><td><input type='text' name='th' size='1' value='0' /></td></tr>";
                    }
                    // if nip
                    else if ($type == 2)
                    {
                        if ($oloimero)
                            echo "<td><input type=\"checkbox\" name='oloimero' checked >Ολοήμερο</td>";
                        else
                            echo "<td><input type=\"checkbox\" name='oloimero'>Όλοήμερο</td>";
                        echo "<td>Μαθητές Πρωινής Ζώνης&nbsp;&nbsp;<input type='text' name='pz' size='1' value=$klasiko_exp[6]></td></tr>";
                        echo "<tr><td colspan=2>Σχόλια: <textarea rows='4' cols='80' name='comments'>$comments</textarea></td></tr>";
                        echo "</table>";
                        echo "<br>";
                        /////////    
                        echo "<h3>Μαθητές</h3><br>";
                        echo "<table class=\"imagetable\" border='1'>";
                        echo "<tr><td rowspan=2>Τμήμα</td><td colspan=2>Κλασικό</td><td colspan=2>Ολοήμερο</td></tr>";
                        echo "<tr><td>Νήπια</td><td>Προνήπια</td><td>Νήπια</td><td>Προνήπια</td></tr>";
                        // t1
                        echo "<tr><td>Τμ.1</td><td><input type='text' name='k1a' size='1' value=$klasiko_exp[0]></td><td><input type='text' name='k1b' size='1' value=$klasiko_exp[1]>";
                        echo "<td><input type='text' name='o1a' size='1' value=$oloimero_nip_exp[0]></td><td><input type='text' name='o1b' size='1' value=$oloimero_nip_exp[1]></td></tr>";
                        // t2
                        echo "<tr><td>Τμ.2</td><td><input type='text' name='k2a' size='1' value=$klasiko_exp[2]></td><td><input type='text' name='k2b' size='1' value=$klasiko_exp[3]>";
                        echo "<td><input type='text' name='o2a' size='1' value=$oloimero_nip_exp[2]></td><td><input type='text' name='o2b' size='1' value=$oloimero_nip_exp[3]></td></tr>";
                        // t3
                        echo "<tr><td>Τμ.3</td><td><input type='text' name='k3a' size='1' value=$klasiko_exp[4]></td><td><input type='text' name='k3b' size='1' value=$klasiko_exp[5]>";
                        echo "<td><input type='text' name='o3a' size='1' value=$oloimero_nip_exp[4]></td><td><input type='text' name='o3b' size='1' value=$oloimero_nip_exp[5]></td></tr>";
                        echo "</table>";
                        echo "<br>";
                        echo "<table class=\"imagetable\" border='1'>";
                        echo "<tr><td colspan=3>Νηπιαγωγοί</td></tr>";
                        echo "<tr><td>Κλασικό</td><td>Ολοήμερο</td><td>Τμ.Ένταξης</td></tr>";
                        echo "<tr><td><input type='text' name='ekp_kl' size='1' value=$nip_exp[0]></td><td><input type='text' name='ekp_ol' size='1' value=$nip_exp[1]></td><td><input type='text' name='ekp_te' size='1' value=$nip_exp[2]></td></tr>";
                        echo "</table>";
                    }
                    
                    echo "</table>";
                    echo "<br>";
                    
                    echo "	<input type='hidden' name = 'sch' value='$sch'>";
                    echo "	<input type='hidden' name = 'name' value='$str1'>";
                    
                    echo "<input type='submit' value='Αποθήκευση'>";
                    echo "</form>";
                    $schLink = "school_status.php?org=$sch";
                    echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Καρτέλα' onClick=\"parent.location='$schLink'\">";
                
                }
?>
		</center>
		<div id='results'></div>
		</body>
		</html>
