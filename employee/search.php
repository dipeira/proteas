<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once "../include/functions.php";
  require('../tools/calendar/tc_calendar.php');
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
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
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Αναζήτηση</title>
	
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery.validate.js"></script>
	<script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
	<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
	<script type="text/javascript" src='../tools/calendar/calendar.js'></script>
	<script type="text/javascript">
	$(document).ready(function(){
		$("#searchfrm").validate({
			debug: false,
			rules: {
			//	name: "required",
			},
			messages: {
			//	name: "Please let us know who you are."
			},
			submitHandler: function(form) {
				// do other stuff for a valid form
				$.post('results.php', $("#searchfrm").serialize(), function(data) {
					$('#results').html(data);
				});
			}
		});
	});
	$().ready(function() {
			$("#org").autocomplete("get_school.php", {
				width: 260,
				matchContains: true,
				selectFirst: false
			});
		});
		$().ready(function() {
			$("#yphr").autocomplete("get_school.php", {
				width: 260,
				matchContains: true,
				selectFirst: false
			});
		});
		// $().ready(function() {
		// 	$("#surname").autocomplete("get_name.php", {
		// 		width: 260,
		// 		matchContains: true,
		// 		selectFirst: false
		// 	});
		// });
	</script>
  </head>
  <body> 
  <?php include('../etc/menu.php'); ?>
    <center>
	 <h2>Αναζήτηση προσωπικού</h2>
   <h3>(μονίμων & αναπληρωτών)</h3>
      <?php
		echo "<div id=\"content\">";
		echo "<form id='searchfrm' name='searchfrm' action='' method='POST' autocomplete='off'>";
    echo "<table class=\"imagetable\" border='1'>";
    echo "<thead><th>Κριτήριο</th><th><span title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφ.</span></th><th>Τιμή</th>";
    echo "<th>Κριτήριο</th><th><span title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφ.</span></th><th>Τιμή</th></thead>";
		echo "<tr><td>Όνομα</td><td></td><td><input type='text' id='name' name='name' /></td><td>Ημ/νία Διορισμού</td><td><input type='checkbox' name = 'dsphm_dior'></td><td>";
    echo "Από:&nbsp;<br>";
    $myCalendar = new tc_calendar("hm_dior_from", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
    $myCalendar->disabledDay("sun,sat");
    $myCalendar->setDatePair('hm_dior_from', 'hm_dior_to');
    $myCalendar->writeScript();
    echo "&nbsp;έως:&nbsp;<br>";
    $myCalendar = new tc_calendar("hm_dior_to", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
    $myCalendar->disabledDay("sun,sat");
    $myCalendar->setDatePair('hm_dior_from', 'hm_dior_to');
		$myCalendar->writeScript();
	  echo "</td></tr>";
		
    echo "<tr><td>Επώνυμο</td><td></td><td><input type='text' name='surname' id='surname'/></td><td>Ημ/νία Ανάληψης</td><td><input type='checkbox' name = 'dsphm_anal'></td><td>";
    echo "Από:&nbsp;<br>";
		$myCalendar = new tc_calendar("hm_anal_from", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
    $myCalendar->disabledDay("sun,sat");
    $myCalendar->setDatePair('hm_anal_from', 'hm_anal_to');
    $myCalendar->writeScript();
    echo "&nbsp;έως:&nbsp;<br>";
    $myCalendar = new tc_calendar("hm_anal_to", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
    $myCalendar->disabledDay("sun,sat");
    $myCalendar->setDatePair('hm_anal_from', 'hm_anal_to');
    $myCalendar->writeScript();
	  echo "</td></tr>";	
		
		echo "<tr><td>Πατρώνυμο</td><td><input type='checkbox' name = 'dsppatr'></td><td><input type='text' name='patrwnymo' /></td>";
		echo "<td>Μεταπτυχιακό/Διδακτορικό</td><td><input type='checkbox' name = 'dspmetdid'></td><td>";
		echo "<select name=\"met_did\">";
		echo "<option value=''></option>";
		echo "<option value='0'>Όχι</option>";
		echo "<option value='1'>Μεταπτυχιακό</option>";
		echo "<option value='2'>Διδακτορικό</option>";		
		echo "</select></td></tr>";
		
		echo "<tr><td>Α.Μ.</td><td><input type='checkbox' name = 'dspam'></td><td><input type='text' name='am' /></td>";
		echo "<td>Προϋπηρεσία</td><td><input type='checkbox' name = 'dspproyhp'></td><td>";
		echo "<select name=\"opp\">";
		echo "<option value=\"=\" selected>=</option>";
		echo "<option value=\">\" >></option>";
		echo "<option value=\"<\" ><</option>";
		echo "</select>";
		echo "Έτη&nbsp;<input type='text' name='pyears' size=1 />&nbsp;Μήνες&nbsp;<input type='text' name='pmonths' size=1 />&nbsp;Ημέρες&nbsp;<input type='text' name='pdays' size=1 /></td></tr>";
		
    echo "<tr><td>Α.Φ.Μ.</td><td><input type='checkbox' name = 'dspafm'></td><td><input type='text' name='afm' /></td>";
    echo "<td>Τηλέφωνο</td><td></td><td><input type='text' name='tel' /></td>";
    echo "</tr>";
                
		echo "<tr><td>Κλάδος</td><td></td><td>";
		kladosCmb($mysqlconnection);
		echo "</td>";
		echo "<td>Σχολείο Οργανικής</td><td></td><td><input type=\"text\" name=\"org\" id=\"org\" /></td></tr>";
				
		echo "<tr><td>Βαθμός</td><td><input type='checkbox' name = 'dspvathmos'></td><td>";
		
		
		vathmosCmb($mysqlconnection);
		
		
		
		echo "<td>Σχολείο Υπηρέτησης</td><td></td><td><input type=\"text\" name=\"yphr\" id=\"yphr\" /></td></tr>";
		
		echo "<tr><td>Μ.Κ.</td><td><input type='checkbox' name = 'dspmk'></td><td><input type='text' name='mk' /></td>";
		echo "<td>Σχόλια</td><td></td><td><input type='text' name='comments' /></td></tr>";
                
    echo "<tr><td>Κατάσταση</td><td><input type='checkbox' name = 'dspkatast'></td><td>";
    echo "<select name=\"katast\">";
		echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
		echo "<option value=\"1\">Εργάζεται</option>";
		echo "<option value=\"2\">Λύση Σχέσης-Παραίτηση</option>";
		echo "<option value=\"3\">Άδεια</option>";
		echo "<option value=\"4\">Διαθεσιμότητα</option>";
		echo "<option value=\"5\">Απουσία COVID-19</option>";
		echo "</select>";
    //echo "</td><td></td><td></td><td></td>";
    echo "</td><td>Συνολική Υπηρεσία <br><small>(από διορισμό + προϋπηρεσία)</small></td><td><input type='checkbox' name = 'dspsynol'></td><td>";
    echo "<select name=\"ops\">";
		echo "<option value=\"=\" selected>=</option>";
		echo "<option value=\">\" >></option>";
		echo "<option value=\"<\" ><</option>";
		echo "</select>";
		echo "Έτη&nbsp;<input type='text' name='syears' size=1 />&nbsp;Μήνες&nbsp;<input type='text' name='smonths' size=1 />&nbsp;Ημέρες&nbsp;<input type='text' name='sdays' size=1 /><br>";
    echo "<small>Συνολική Υπηρεσία έως:</small><br>";
    $myCalendar = new tc_calendar("hm_synol", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		//$myCalendar->setDate(date('d',strtotime($hm_anal)),date('m',strtotime($hm_anal)),date('Y',strtotime($hm_anal)));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", "2030-12-31");
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
    echo "</td></tr>";
    echo "<tr><td>Μον./Αναπλ.</td><td></td><td>";
    echo "<select name=\"emptype\">";
		//echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
		echo "<option value=\"1\">Μόνιμος</option>";
		echo "<option value=\"2\">Αναπληρωτής</option>";
		//echo "<option value=\"3\">Διοικητικός</option>";
		//echo "<option value=\"4\">Ιδιωτικός</option>";
    echo "</select>";
		echo "</td>";
		thesiselectcmb(0,true);
		echo "</tr>";
		echo "<tr>";
		ent_ty_selectcmb(0, false, true, true);
		echo "</td><td colspan=3></td></tr>";

		echo "<tr><td colspan=6><input type='checkbox' name = 'smeae'>&nbsp;Οργανική σε Ειδικό Σχολείο;</td></tr>";	
    
    echo "<tr><td colspan=6><input type='checkbox' name = 'outsiders'>&nbsp;Εμφάνιση και όσων δεν ανήκουν στη Δ/νση;</td></tr>";	
		echo "<tr><td colspan=6><input type='checkbox' name = 'or'>&nbsp;Να ισχύει ΤΟΥΛΑΧΙΣΤΟΝ ΕΝΑ από τα παραπάνω κριτήρια (λογικό OR);</td></tr>";	
		
		echo "	</table>";
		echo "	<input type='hidden' name = 'set' value='$set'>";
		echo "	<input type='submit' value='Αναζήτηση'>";
		echo "  &nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value=\"Επαναφορά\" onClick=\"window.location.reload()\">";
    echo "  &nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value=\"Βοήθεια\" onclick=\"window.open('../help/help.html#search','', 'width=450, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=yes, resizable=no'); return false\">";
		echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
		echo "	</form>";
		echo "</div>";
		
		mysqli_close($mysqlconnection);
?>
		</center>
		<div id='results'></div>
		</body>
		</html>
