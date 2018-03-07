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
    header("Location: tools/login_check.php");
  }
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
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
				//mustMatch: true,
				//minChars: 0,
				//multiple: true,
				//highlight: false,
				//multipleSeparator: ",",
				selectFirst: false
			});
		});
		$().ready(function() {
			$("#yphr").autocomplete("get_school.php", {
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
		$().ready(function() {
			$("#surname").autocomplete("get_name.php", {
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
	</script>
  </head>
  <body> 
    <center>
      <?php
		echo "<div id=\"content\">";
		echo "<form id='searchfrm' name='searchfrm' action='' method='POST' autocomplete='off'>";
		echo "<table class=\"imagetable\" border='1'>";
		echo "<tr><td>Όνομα</td><td></td><td><input type='text' id='name' name='name' /></td><td>Ημ/νία Διορισμού</td><td><input type='checkbox' name = 'dsphm_dior'></td><td>";
		$myCalendar = new tc_calendar("hm_dior", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		//$myCalendar->setDate(date("d"), date("m"), date("Y"));
		//$myCalendar->setDate(date('d',strtotime($hm_dior)),date('m',strtotime($hm_dior)),date('Y',strtotime($hm_dior)));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
		//$myCalendar->setSpecificDate(array("2011-04-01", "2011-04-14", "2010-12-25"), 0, "year");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";
		
		echo "<tr><td>Επώνυμο</td><td></td><td><input type='text' name='surname' id='surname'/></td><td>Ημ/νία Ανάληψης</td><td></td><td>";
		$myCalendar = new tc_calendar("hm_anal", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		//$myCalendar->setDate(date('d',strtotime($hm_anal)),date('m',strtotime($hm_anal)),date('Y',strtotime($hm_anal)));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
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
		echo "Έτη<input type='text' name='pyears' size=1 />Μήνες<input type='text' name='pmonths' size=1 />Ημέρες<input type='text' name='pdays' size=1 /></td></tr>";
		
                echo "<tr><td>Α.Φ.Μ.</td><td><input type='checkbox' name = 'dspafm'></td><td><input type='text' name='afm' /></td>";
                echo "<td></td><td></td><td></tr>";
                
		echo "<tr><td>Κλάδος</td><td></td><td>";
		kladosCmb($mysqlconnection);
		echo "</td>";
		echo "<td>Σχολείο Οργανικής</td><td></td><td><input type=\"text\" name=\"org\" id=\"org\" /></td></tr>";
				
		echo "<tr><td>Βαθμός</td><td><input type='checkbox' name = 'dspvathmos'></td><td>";
		
		
		//vathmosCmb($mysqlconnection);
		echo "<select name=\"vathm\">";
		echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
		echo "<option value=\"ΣΤ\">ΣΤ</option>";
		echo "<option value=\"Ε\">Ε</option>";
		echo "<option value=\"Δ\">Δ</option>";
		echo "<option value=\"Γ\">Γ</option>";
		echo "<option value=\"Β\">Β</option>";
		echo "<option value=\"Α\">Α</option>";
		echo "</select>";
		
		
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
		echo "</select>";
                //echo "</td><td></td><td></td><td></td>";
                echo "</td><td>Συνολική Υπηρεσία <br><small>(από διορισμό + προϋπηρεσία)</small></td><td><input type='checkbox' name = 'dspsynol'></td><td>";
                echo "<select name=\"ops\">";
		echo "<option value=\"=\" selected>=</option>";
		echo "<option value=\">\" >></option>";
		echo "<option value=\"<\" ><</option>";
		echo "</select>";
		echo "Έτη<input type='text' name='syears' size=1 />Μήνες<input type='text' name='smonths' size=1 />Ημέρες<input type='text' name='sdays' size=1 /><br>";
                echo "<small>Συνολική Υπηρεσία έως:</small><br>";
                $myCalendar = new tc_calendar("hm_synol", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		//$myCalendar->setDate(date('d',strtotime($hm_anal)),date('m',strtotime($hm_anal)),date('Y',strtotime($hm_anal)));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", "2020-12-31");
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
                echo "</td></tr>";

		echo "<tr><td colspan=6><input type='checkbox' name = 'and'>Να ισχύουν ΟΛΑ τα παραπάνω κριτήρια ΤΑΥΤΟΧΡΟΝΑ;</td></tr>";	
		
		echo "	</table>";
		echo "	<input type='hidden' name = 'set' value='$set'>";
		echo "	<input type='submit' value='Αναζήτηση'>";
		echo "  &nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value=\"Επαναφορά\" onClick=\"window.location.reload()\">";
                echo "  &nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value=\"Βοήθεια\" onclick=\"window.open('help/help.html#search','', 'width=450, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=yes, resizable=no'); return false\">";
		echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
		echo "	</form>";
		echo "</div>";
		
		//mysql_close();
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