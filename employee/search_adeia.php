<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"../config.php";
  require_once "../tools/functions.php";
  //define("L_LANG", "el_GR"); Needs fixing
  require('../tools/calendar/tc_calendar.php');
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'greek'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
  
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
    <title>Αναζήτηση Αδειών</title>
	
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
				$.post('results_adeia.php', $("#searchfrm").serialize(), function(data) {
					$('#results').html(data);
				});
			}
		});
	});
	</script>
  </head>
  <body> 
  <?php include('../etc/menu.php'); ?>
    <center>
	 <h2>Αναζήτηση αδειών</h2>
	 <br>
      <?php
        //session_start();
        $usrlvl = $_SESSION['userlevel'];
        //echo $usrlvl;
         if ($usrlvl>2)
            {
                echo "Δεν έχετε δικαίωμα να αναζητήσετε στις άδειες.<br>";
                echo "Πατήστε <a href='../index.php'>εδώ</a> για επιστροφή...";
                //sleep (6);
                //header("Location: index.php");
                //header("Refresh: 6; url=index.php");  
            }
         else
         {
		echo "<div id=\"content\">";
		echo "<form id='searchfrm' name='searchfrm' action='' method='POST' autocomplete='off'>";
		echo "<table class=\"imagetable stable\" border='1'>";
		echo "<tr><td>Ημ/νία από: </td><td>";
		$myCalendar = new tc_calendar("hm_from", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		//$myCalendar->setDate(date('d',strtotime($hm_dior)),date('m',strtotime($hm_dior)),date('Y',strtotime($hm_dior)));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(2011, date("Y"));
		$myCalendar->dateAllow("2011-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
		//$myCalendar->setSpecificDate(array("2011-04-01", "2011-04-14", "2010-12-25"), 0, "year");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";
		
		echo "<tr><td>Ημ/νία έως</td><td>";
		$myCalendar = new tc_calendar("hm_to", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(2011, date("Y")+1);
		$myCalendar->dateAllow("2011-01-01", "2020-12-31");
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";	
		
                echo "<tr><td>Είδος</td><td>";
                adeiaCmb($type,$mysqlconnection);
                echo "</td></tr>";
                
                echo "<tr><td colspan=2>";
                echo "<input type='radio' name='mon_anapl' value='0' checked >Μόνιμοι<br>";
                echo "<input type='radio' name='mon_anapl' value='1'>Αναπληρωτές<br>";
                echo "</td></tr>";
                		
		echo "	</table>";
		echo "	<input type='submit' value='Αναζήτηση'>";
		echo "  &nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value=\"Επαναφορά\" onClick=\"window.location.reload()\">";
		echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
		echo "	</form>";
		echo "</div>";
         }	
    mysqli_close($mysqlconnection);
?>
		</center>
		<div id='results'></div>
		</body>
		</html>
