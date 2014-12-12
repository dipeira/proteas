<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"config.php";
  require_once "functions.php";
  //define("L_LANG", "el_GR"); Needs fixing
  require('calendar/tc_calendar.php');
  
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
  // Demand authorization                
  include("tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false){
    header("Location: tools/login_check.php");
  }
?>
<html>
  <head>
	<LINK href="style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Επεξεργασία σχολείου</title>
	
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
	<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
	<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
	<script type="text/javascript">
	
	$().ready(function() {
			$("#org").autocomplete("get_school.php", {
				width: 260,
				matchContains: true,
				//mustMatch: true,
				//minChars: 0,
				//multiple: true,
				//highlight: false,
				//multipleSeparator: ",",
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
    <center>
        <h2>Επεξεργασία σχολείου</h2>
      <?php     
      
		echo "<div id=\"content\">";
		echo "<form id='searchfrm' name='searchfrm' action='' method='POST' autocomplete='off'>";
		echo "<table class=\"imagetable\" border='1'>";
		echo "<td>Σχολείο</td><td></td><td><input type=\"text\" name=\"org\" id=\"org\" /></td></tr>";				
		echo "	</table>";
		echo "	<input type='submit' value='Αναζήτηση'>";
		//echo "  &nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value=\"Επαναφορά\" onClick=\"window.location.reload()\">";
		echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='index.php'\">";
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
                        $oloimero_stud = mysql_result($result, 0, "oloimero_stud");
                        $tmimata = mysql_result($result, 0, "tmimata");
                        $tmimata_exp = explode(",",$tmimata);
                        $oloimero_tea = mysql_result($result, 0, "oloimero_tea");
                        $ekp_ee = mysql_result($result, 0, "ekp_ee");
                        $ekp_ee_exp = explode(",",$ekp_ee);
                        
                        $synolo = array_sum($classes);
                        $synolo_tmim = array_sum($tmimata_exp);
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
                    $entaksis = mysql_result($result, 0, "entaksis");
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
                    if ($entaksis)
                        echo "<td><input type=\"checkbox\" name='entaksis' checked >Τμήμα Ένταξης</td>";
                    else
                        echo "<td><input type=\"checkbox\" name='entaksis' >Τμήμα Ένταξης</td>";
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
                        echo "<tr><td colspan=2>Σχόλια: <input type='text' name='comments' value='$comments' size='65' /></td></tr>";
                        echo "</table>";
                        echo "<br>";
                        
                        //if ($oloimero) - Afairethike gia taxythterh kataxwrhsh...
                        //{
                            echo "<table class=\"imagetable\" border='1'>";
                            echo "<tr><td>Μαθητές Ολοημέρου: <input type='text' name='oloimero_stud' value='$oloimero_stud' size='2'/></td>";
                            echo "<td>Εκπ/κοί Ολοημέρου: <input type='text' name='oloimero_tea' value='$oloimero_tea' size='2'/><td></tr>";
                            echo "</table>";
                        //}
                        echo "<br>";

                        echo "<table class=\"imagetable\" border='1'>";
                        echo "<tr><td colspan=6>Σύνολο Μαθητών: $synolo</td></tr>";
                        echo "<tr><td>Α'</td><td>Β'</td><td>Γ'</td><td>Δ'</td><td>Ε'</td><td>ΣΤ'</td></tr>";
                        if ($synolo>0)
                            echo "<tr><td><input type='text' name='a' size='1' value=$classes[0] /></td><td><input type='text' name='b' size='1' value=$classes[1] /></td><td><input type='text' name='c' size='1' value=$classes[2] /></td><td><input type='text' name='d' size='1' value=$classes[3] /></td><td><input type='text' name='e' size='1' value=$classes[4] /></td><td><input type='text' name='f' size='1' value=$classes[5] /></td></tr>";
                        else
                            echo "<tr><td><input type='text' name='a' size='1' value='0' /></td><td><input type='text' name='b' size='1' value='0' /></td><td><input type='text' name='c' size='1' value='0' /></td><td><input type='text' name='d' size='1' value='0' /></td><td><input type='text' name='e' size='1' value='0' /></td><td><input type='text' name='f' size='1' value='0' /></td></tr>";
                        echo "<tr><td colspan=6>Τμήματα (Εκπαιδευτικοί) ανά τάξη<br>Σύνολο: $synolo_tmim</td></tr>";
                        if ($synolo>0)
                            echo "<tr><td><input type='text' name='ta' size='1' value=$tmimata_exp[0] /></td><td><input type='text' name='tb' size='1' value=$tmimata_exp[1] /></td><td><input type='text' name='tc' size='1' value=$tmimata_exp[2] /></td><td><input type='text' name='td' size='1' value=$tmimata_exp[3] /></td><td><input type='text' name='te' size='1' value=$tmimata_exp[4] /></td><td><input type='text' name='tf' size='1' value=$tmimata_exp[5] /></td></tr>";
                        else
                            echo "<tr><td><input type='text' name='ta' size='1' value='0' /></td><td><input type='text' name='tb' size='1' value='0' /></td><td><input type='text' name='tc' size='1' value='0' /></td><td><input type='text' name='td' size='1' value='0' /></td><td><input type='text' name='te' size='1' value='0' /></td><td><input type='text' name='tf' size='1' value='0' /></td></tr>";
                        
                    }
                    else if ($type == 2)
                    {
                        if ($oloimero)
                            echo "<td><input type=\"checkbox\" name='oloimero' checked >Ολοήμερο</td><td></td></tr>";
                        else
                            echo "<td><input type=\"checkbox\" name='oloimero' >Όλοήμερο</td><td></td></tr>";
                        echo "<tr><td colspan=2>Σχόλια: <input type='text' name='comments' value='$comments' size='65' /></td></tr>";
                        echo "</table>";
                        echo "<br>";
                        
                        echo "<table class=\"imagetable\" border='1'>";
                        echo "<tr><td colspan=16>Μαθητές</td></tr>";
                        echo "<tr><td colspan=8>Κλασικό</td><td colspan=8>Ολοήμερο</td></tr>";
                        echo "<tr><td colspan=2>Τμήμα 1</td><td colspan=2>Τμήμα 2</td><td colspan=2>Τμήμα 3</td><td colspan=2>Τμήμα 4</td>";
                        echo "<td colspan=2>Τμήμα 1</td><td colspan=2>Τμήμα 2</td><td colspan=2>Τμήμα 3</td><td colspan=2>Τμήμα 4</td>";
                        echo "</tr>";
                        echo "<tr><td>Νηπ.</td><td>Προνηπ.</td><td>Νηπ.</td><td>Προνηπ.</td><td>Νηπ.</td><td>Προνηπ.</td><td>Νηπ.</td><td>Προνηπ.</td><td>Νηπ.</td><td>Προνηπ.</td><td>Νηπ.</td><td>Προνηπ.</td><td>Νηπ.</td><td>Προνηπ.</td><td>Νηπ.</td><td>Προνηπ.</td></tr>";
                        echo "<tr>";

                            echo "<tr>";
                            echo "<td><input type='text' name='k1a' size='1' value=$klasiko_exp[0]></td><td><input type='text' name='k1b' size='1' value=$klasiko_exp[1]></td>";
                            echo "<td><input type='text' name='k2a' size='1' value=$klasiko_exp[2]></td><td><input type='text' name='k2b' size='1' value=$klasiko_exp[3]></td>";
                            echo "<td><input type='text' name='k3a' size='1' value=$klasiko_exp[4]></td><td><input type='text' name='k3b' size='1' value=$klasiko_exp[5]></td>";
                            echo "<td><input type='text' name='k4a' size='1' value=$klasiko_exp[6]></td><td><input type='text' name='k4b' size='1' value=$klasiko_exp[7]></td>";
                            
                            echo "<td><input type='text' name='o1a' size='1' value=$oloimero_nip_exp[0]></td><td><input type='text' name='o1b' size='1' value=$oloimero_nip_exp[1]></td>";
                            echo "<td><input type='text' name='o2a' size='1' value=$oloimero_nip_exp[2]></td><td><input type='text' name='o2b' size='1' value=$oloimero_nip_exp[3]></td>";
                            echo "<td><input type='text' name='o3a' size='1' value=$oloimero_nip_exp[4]></td><td><input type='text' name='o3b' size='1' value=$oloimero_nip_exp[5]></td>";
                            echo "<td><input type='text' name='o4a' size='1' value=$oloimero_nip_exp[6]></td><td><input type='text' name='o4b' size='1' value=$oloimero_nip_exp[7]></td>";
                            echo "</tr>";
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
                    
                    
                    
                    echo "<input type='submit' value='Επεξεργασία'>";
                    echo "</form>";
                    echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='index.php'\">";
                
                }
?>
		</center>
		<div id='results'></div>
		</body>
		</html>

<style type="text/css">
	body {
		font-family: Arial;
		font-size: 11px;
		color: #000;
	}
	
	h3 {
		margin: 0px;
		padding: 0px;	
	}

	.suggestionsBox {
		font-family: sans-serif;
		position: relative;
		left: 30px;
		margin: 10px 0px 0px 0px;
		width: 200px;
		background-color: #212427;
		-moz-border-radius: 7px;
		-webkit-border-radius: 7px;
		border: 2px solid #000;	
		color: #fff;
	}
	
	.suggestionList {
		margin: 0px;
		padding: 0px;
	}
	
	.suggestionList li {
		
		margin: 0px 0px 3px 0px;
		padding: 3px;
		cursor: pointer;
	}
	
	.suggestionList li:hover {
		background-color: #659CD8;
	}
</style>