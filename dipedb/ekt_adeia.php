<?php
  header('Content-type: text/html; charset=iso8859-7');
  require_once"config.php";
  require_once"functions.php";
  require_once 'tools/num2word.php';
  //define("L_LANG", "el_GR"); Needs fixing
  require('calendar/tc_calendar.php');

  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);

  // Demand authorization                
  include("tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false)
    header("Location: tools/login_check.php");

?>
<html>
  <head>
	<LINK href="style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Employee</title>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>
	<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
	<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
	<script type="text/javascript">
        $(document).ready(function(){
		$("#wordfrm").validate({
			debug: false,
			rules: {
			//	name: "required",
			},
			messages: {
			//	name: "Please let us know who you are."
			},
			submitHandler: function(form) {
				// do other stuff for a valid form
				$.post('ekt_adeia_print.php', $("#wordfrm").serialize(), function(data) {
					$('#word').html(data);
				});
			}
		});
	});


                $(document).ready(function(){
		$("#updatefrm").validate({
			debug: false,
			rules: {
			//	name: "required",
			},
			messages: {
			//	name: "Please let us know who you are."
			},
			submitHandler: function(form) {
				// do other stuff for a valid form
				$.post('ekt_update_adeia.php', $("#updatefrm").serialize(), function(data) {
					$('#results').html(data);
				});
			}
		});
	});
                
	</script>
  </head>
  <body>
    <center>
      <?php
		//if (!isset($_GET['emp']) && ($_GET['op']!="delete"))
                $usrlvl = $_SESSION['userlevel'];
                
                if (!isset($_GET['emp']))
                {
                    $query = "SELECT * from adeia_ekt where id=".$_GET['adeia'];
                    $result = mysql_query($query, $mysqlconnection);

                    $id = mysql_result($result, 0, "id");
                    $emp_id = mysql_result($result, 0, "emp_id");
                    $type = mysql_result($result, 0, "type");
                    $prot = mysql_result($result, 0, "prot");
                    $prot_apof = mysql_result($result, 0, "prot_apof");
                    $hm_apof = mysql_result($result, 0, "hm_apof");
                    $hm_prot = mysql_result($result, 0, "hm_prot");
                    $date = mysql_result($result, 0, "date");
                    $vev_dil = mysql_result($result, 0, "vev_dil");
                    $days = mysql_result($result, 0, "days");
                    $start = mysql_result($result, 0, "start");
                    $finish = mysql_result($result, 0, "finish");
                    $logos = mysql_result($result, 0, "logos");
                    $logos = str_replace(" ", "&nbsp;", $logos);
                    $comments = mysql_result($result, 0, "comments");
                    $comments = str_replace(" ", "&nbsp;", $comments);
                    $created = mysql_result($result, 0, "created");
                }

if ($_GET['op']=="edit")
	{
                echo "<form id='updatefrm' name='update' action='ekt_update_adeia.php' method='POST'>";
		echo "<table class=\"imagetable\" border='1'>";

		echo "<tr>";
		echo "<td>ID</td><td>$id</td>";
		echo "</tr>";
                $query1 = "select * from ektaktoi where id=$emp_id";
                $result1 = mysql_query($query1, $mysqlconnection);
		$name = mysql_result($result1, 0, "name");
                $surname = mysql_result($result1, 0, "surname");
                $kl1 = mysql_result($result1, 0, "klados");
                $q2 = "select * from klados where id=$kl1";
                $res2 = mysql_query($q2, $mysqlconnection);
                $klados = mysql_result($res2, 0, "perigrafh");
		echo "<tr><td>Όνομα</td><td>$name</td></tr>";
		echo "<tr><td>Επώνυμο</td><td>$surname</td></tr>";
                echo "<tr><td>Κλάδος</td><td>$klados</td></tr>";

		echo "<tr><td>type</td><td>";
                adeiaCmb($type,$mysqlconnection,1);
                echo "</td></tr>";

                echo "<tr><td>Αρ.Πρωτοκόλου απόφασης</td><td><input type='text' name='prot_apof' value=$prot_apof /></td></tr>";
                echo "<tr><td>Ημ/νία Πρωτοκόλου απόφασης</td><td>";
		$myCalendar = new tc_calendar("hm_apof", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setDate(date('d',strtotime($hm_apof)),date('m',strtotime($hm_apof)),date('Y',strtotime($hm_apof)));
		$myCalendar->setPath("calendar/");
		$myCalendar->dateAllow("2011-01-01", "2030-12-31");
		$myCalendar->setAlignment("left", "bottom");
		//$myCalendar->setSpecificDate(array("2011-04-01", "2011-04-14", "2010-12-25"), 0, "year");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";
                
		echo "<tr><td>Αρ.Πρωτοκόλου αίτησης</td><td><input type='text' name='prot' value=$prot /></td></tr>";
                
                echo "<tr><td>Ημ/νία Πρωτοκόλου αίτησης</td><td>";
		$myCalendar = new tc_calendar("hm_prot", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setDate(date('d',strtotime($hm_prot)),date('m',strtotime($hm_prot)),date('Y',strtotime($hm_prot)));
		$myCalendar->setPath("calendar/");
		$myCalendar->dateAllow("2011-01-01", "2030-12-31");
		$myCalendar->setAlignment("left", "bottom");
		//$myCalendar->setSpecificDate(array("2011-04-01", "2011-04-14", "2010-12-25"), 0, "year");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";
                
                echo "<tr><td>Ημ/νία αίτησης</td><td>";
		$myCalendar = new tc_calendar("date", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date('d',strtotime($date)),date('m',strtotime($date)),date('Y',strtotime($date)));
		$myCalendar->setPath("calendar/");
		$myCalendar->dateAllow("2011-01-01", "2030-12-31");
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";

		echo "<tr><td>Βεβαίωση / Δήλωση</td><td>";
                //<input type='text' name='vev_dil' value=$vev_dil /></td></tr>";
                echo "<select name='vev_dil'>";
		if ($vev_dil==0)
                    echo "<option value=\"0\" selected>Όχι</option>";
                else
                    echo "<option value=\"0\">Όχι</option>";
                if ($vev_dil==1)
                    echo "<option value=\"1\" selected>Βεβαίωση</option>";
                else
                    echo "<option value=\"1\">Βεβαίωση</option>";
                if ($vev_dil==2)
                    echo "<option value=\"2\" selected>Υπεύθυνη Δήλωση</option>";
                else
                    echo "<option value=\"2\">Υπεύθυνη Δήλωση</option>";
                echo "</select>";
                echo "</td></tr>";


                echo "<tr><td>Ημέρες</td><td><input type='text' name='days' value=$days /></td></tr>";

                echo "<tr><td>Ημ/νία έναρξης</td><td>";
		$myCalendar = new tc_calendar("start", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		//$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setDate(date('d',strtotime($start)),date('m',strtotime($start)),date('Y',strtotime($start)));
		$myCalendar->setPath("calendar/");
		$myCalendar->dateAllow("2011-01-01", "2030-12-31");
		$myCalendar->setAlignment("left", "bottom");
		//$myCalendar->setSpecificDate(array("2011-04-01", "2011-04-14", "2010-12-25"), 0, "year");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";

                echo "<tr><td>Ημ/νία λήξης</td><td>";
		$myCalendar = new tc_calendar("finish", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		//$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setDate(date('d',strtotime($finish)),date('m',strtotime($finish)),date('Y',strtotime($finish)));
		$myCalendar->setPath("calendar/");
		$myCalendar->dateAllow("2011-01-01", "2030-12-31");
		$myCalendar->setAlignment("left", "bottom");
		//$myCalendar->setSpecificDate(array("2011-04-01", "2011-04-14", "2010-12-25"), 0, "year");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";

		echo "<tr><td>Λόγος</td><td><input type='text' name='logos' value=$logos /></td></tr>";

		echo "<tr><td>Σχόλια</td><td><input type='text' name='comments' value=$comments /></td></tr>";

		echo "	</table>";
		echo "	<input type='hidden' name = 'id' value='$id'>";
                echo "	<input type='hidden' name = 'emp_id' value='$emp_id'>";
		echo "	<input type='submit' value='Επεξεργασία'>";
		echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='ekt_adeia.php?adeia=$id&op=view'\">";
		//echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick='history.go(-1);return true;'>";
		echo "	</form>";
		echo "    </center>";
		echo "</body>";
?>
        <div id='results'>   </div>
<?php
		echo "</html>";
    }
	elseif ($_GET['op']=="view")
	{
		echo "<table class=\"imagetable\" border='1'>";
		echo "<tr>";
		echo "<td>ID</td><td>$id</td>";
		echo "</tr>";
                $query1 = "select * from ektaktoi where id=$emp_id";
                $result1 = mysql_query($query1, $mysqlconnection);
		$name = mysql_result($result1, 0, "name");
                $surname = mysql_result($result1, 0, "surname");
                $kl1 = mysql_result($result1, 0, "klados");
                $q2 = "select * from klados where id=$kl1";
                $res2 = mysql_query($q2, $mysqlconnection);
                $klados = mysql_result($res2, 0, "perigrafh");
		echo "<tr><td>Όνομα</td><td>$name</td></tr>";
		echo "<tr><td>Επώνυμο</td><td>$surname</td></tr>";
                echo "<tr><td>Κλάδος</td><td>$klados</td></tr>";

		$query1 = "select type from adeia_ekt_type where id=$type";
                $result1 = mysql_query($query1, $mysqlconnection);
		$typewrd = mysql_result($result1, 0, "type");
                echo "<tr><td>Τύπος</td><td>$typewrd</td></tr>";
                if (!$prot_apof)
                    $prot_apof = '';
		echo "<tr><td>Αρ.Πρωτοκόλου απόφασης</td><td>$prot_apof</td></tr>";
                if (!(int)$hm_apof)
                    $hm_apof = '';
                else
                    $hm_apof = date('d-m-Y',strtotime($hm_apof));
                echo "<tr><td>Ημ/νία Πρωτοκόλου απόφασης</td><td>$hm_apof</td></tr>";
                
		echo "<tr><td>Αρ.Πρωτοκόλου αίτησης</td><td>$prot</td></tr>";
                $hm_prot = date('d-m-Y',strtotime($hm_prot));
                echo "<tr><td>Ημ/νία Πρωτοκόλου αίτησης</td><td>$hm_prot</td></tr>";
                $date = date('d-m-Y',strtotime($date));
                echo "<tr><td>Ημ/νία αίτησης</td><td>$date</td></tr>";
		if ($type==1)
                {
                    echo "<tr><td>Βεβαίωση / Δήλωση</td><td>";
                    switch ($vev_dil)
                        {
                        case 0:
                            echo "Όχι";
                            break;
                        case 1:
                            echo "Βεβαίωση";
                            break;
                        case 2:
                            echo "Υπεύθυνη Δήλωση";
                            break;
                        }
                    echo "</td></tr>";
                }
                $daysfull = convertNumber($days);
		echo "<tr><td>Ημέρες</td><td>$days ($daysfull)</td></tr>";
                $start = date('d-m-Y',strtotime($start));
		echo "<tr><td>Ημ/νία Έναρξης</td><td>$start</td></tr>";
                $finish = date('d-m-Y',strtotime($finish));
                echo "<tr><td>Ημ/νία Λήξης</td><td>$finish</td></tr>";
		echo "<tr><td>Λόγος</td><td>$logos</td></tr>";
		echo "<tr><td>Σχόλια</td><td>$comments</td></tr>";
                if ($created != 0)
                {
                    $created= date('d-m-Y, g:i a',strtotime($created));
                    echo "<tr><td colspan=2 align='right'><small>Τελευταία τροποποίηση:<br> $created</small></td></tr>";
                }

		echo "</td></tr>";
                if ($_SESSION['adeia'])
                {
                    echo "<tr><td colspan=2 align='center'>";
                    //Form gia Bebaiwsh
                    echo "<form id='wordfrm' name='wordfrm' action='ekt_adeia_print.php' method='POST'>";
                    echo "<input type='hidden' name=arr[] value=$emp_id>";
                    echo "<input type='hidden' name=arr[] value=$type>";
                    echo "<input type='hidden' name=arr[] value=$prot>";
                    echo "<input type='hidden' name=arr[] value=$hm_prot>";
                    echo "<input type='hidden' name=arr[] value=$date>";
                    echo "<input type='hidden' name=arr[] value=$vev_dil>";
                    echo "<input type='hidden' name=arr[] value=$days>";
                    echo "<input type='hidden' name=arr[] value=$start>";
                    echo "<input type='hidden' name=arr[] value=$finish>";
                    echo "<input type='hidden' name=arr[] value=$logos>";

                    echo "<INPUT TYPE='submit' VALUE='Εκτύπωση άδειας'>";
                    echo "</form>";
                    ?>
                    <div id="word"></div>
                    <?php
                    echo "</td></tr>";
                }
		echo "	</table>";

		// Find prev - next row id
		$qprev = "SELECT id FROM adeia_ekt WHERE id < $id AND emp_id = $emp_id ORDER BY id DESC LIMIT 1";
		$res1 = mysql_query($qprev, $mysqlconnection);
		 if (mysql_numrows($res1))
			$previd = mysql_result($res1, 0, "id");
		$qnext = "SELECT id FROM adeia_ekt WHERE id > $id AND emp_id = $emp_id ORDER BY id ASC LIMIT 1";
		$res1 = mysql_query($qnext, $mysqlconnection);
		 if (mysql_numrows($res1))
			$nextid = mysql_result($res1, 0, "id");

		if ($previd)
			echo "	<INPUT TYPE='button' VALUE='<<' onClick=\"parent.location='ekt_adeia.php?id=$emp_id&adeia=$previd&op=view'\">";
                if ($usrlvl < 3)
                    echo "	<INPUT TYPE='button' VALUE='Επεξεργασία' onClick=\"parent.location='ekt_adeia.php?id=$emp_id&adeia=$id&op=edit'\">";
                echo "	<INPUT TYPE='button' VALUE='Επιστροφή στην καρτέλα εκπ/κού' onClick=\"parent.location='ektaktoi.php?id=$emp_id&op=view'\">";
		if ($nextid)
			echo "	<INPUT TYPE='button' VALUE='>>' onClick=\"parent.location='ekt_adeia.php?id=$emp_id&adeia=$nextid&op=view'\">";
                echo "<br><br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='index.php'\">";
		echo "    </center>";

		echo "</body>";
		echo "</html>";
	}
	if ($_GET['op']=="delete")
	{
		// Copies the to-be-deleted row to adeia_deleted table for backup purposes.Also adds a row to adeia_del_log for logging purposes...
		$query1 = "INSERT INTO adeia_ekt_deleted SELECT e.* FROM adeia_ekt e WHERE id =".$_GET['adeia'];
		$result1 = mysql_query($query1, $mysqlconnection);
                $query1 = "INSERT INTO adeia_del_log (adeia_id, userid, ektaktos) VALUES (".$_GET['adeia'].", ".$_SESSION['userid'].", '1')";
                echo $query1;
		$result1 = mysql_query($query1, $mysqlconnection);
                $query = "DELETE from adeia_ekt where id=".$_GET['adeia'];
		$result = mysql_query($query, $mysqlconnection);
		// Copies the deleted row to employee)deleted

		if ($result)
			echo "Η εγγραφή με κωδικό $id διαγράφηκε με επιτυχία.";
		else
			echo "Η διαγραφή απέτυχε...";
		echo "	<INPUT TYPE='button' VALUE='Επιστροφή στην καρτέλα εκπ/κού' onClick=\"parent.location='ektaktoi.php?id=$emp_id&op=view'\">";
	}
	if ($_GET['op']=="add")
	{
		echo "<form id='updatefrm' name='updatefrm' action='ekt_update_adeia.php' method='POST'>";
		echo "<table class=\"imagetable\" border='1'>";
		$emp_id = $_GET['emp'];
		//echo "<tr>";
		//echo "<td>ID</td><td>$id</td>";
		//echo "</tr>";
                $query1 = "select * from ektaktoi where id=$emp_id";
                $result1 = mysql_query($query1, $mysqlconnection);
		$name = mysql_result($result1, 0, "name");
                $surname = mysql_result($result1, 0, "surname");
                $kl1 = mysql_result($result1, 0, "klados");
                $q2 = "select * from klados where id=$kl1";
                $res2 = mysql_query($q2, $mysqlconnection);
                $klados = mysql_result($res2, 0, "perigrafh");
		echo "<tr><td>Όνομα</td><td>$name</td></tr>";
		echo "<tr><td>Επώνυμο</td><td>$surname</td></tr>";
                echo "<tr><td>Κλάδος</td><td>$klados</td></tr>";

		echo "<tr><td>Τύπος</td><td>";
                adeiaCmb($type,$mysqlconnection,1);
                echo "</td></tr>";
                
                echo "<tr><td>Αρ.Πρωτοκόλου απόφασης</td><td><input type='text' name='prot_apof' /></td></tr>";
                echo "<tr><td>Ημ/νία Πρωτοκόλου απόφασης</td><td>";
		$myCalendar = new tc_calendar("hm_apof", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setPath("calendar/");
		$myCalendar->dateAllow("2011-01-01", "2030-12-31");
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";

		echo "<tr><td>Αρ.Πρωτοκόλου αίτησης</td><td><input type='text' name='prot' /></td></tr>";
                
                echo "<tr><td>Ημ/νία Πρωτοκόλου αίτησης</td><td>";
		$myCalendar = new tc_calendar("hm_prot", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setPath("calendar/");
		$myCalendar->dateAllow("2011-01-01", "2030-12-31");
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";
                
                echo "<tr><td>Ημ/νία αίτησης</td><td>";
		$myCalendar = new tc_calendar("date", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
                $myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setPath("calendar/");
		$myCalendar->dateAllow("2011-01-01", "2030-12-31");
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";

		//echo "<tr><td>Βεβαίωση / Δήλωση</td><td><input type='text' name='vev_dil' /></td></tr>";
                // Need to check - hide-unhide depending on adeia type...
                echo "<div id='vevdil' style='display:none'><tr><td>Βεβαίωση / Δήλωση<br>(για αναρρωτικές)</td><td>";
                
                //<input type='text' name='vev_dil' value=$vev_dil /></td></tr>";
                echo "<select name='vev_dil'>";
		echo "<option value=\"0\" selected>Όχι</option>";
                echo "<option value=\"1\">Βεβαίωση</option>";
                echo "<option value=\"2\">Υπεύθυνη Δήλωση</option>";
                echo "</select>";
                echo "</td></tr></div>";

                echo "<tr><td>Ημέρες</td><td><input type='text' name='days' /></td></tr>";
                echo "<tr><td>Ημ/νία έναρξης</td><td>";
		$myCalendar = new tc_calendar("start", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setPath("calendar/");
		$myCalendar->dateAllow("2011-01-01", "2030-12-31");
		$myCalendar->setAlignment("left", "bottom");
		//$myCalendar->setSpecificDate(array("2011-04-01", "2011-04-14", "2010-12-25"), 0, "year");
                //$myCalendar->setOnChange("myChanged('test')");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
                
?>                
                <script language="javascript">
                function addDays2Date(){
                    var d = new Date (document.updatefrm.start.value);
                    temp1 = document.updatefrm.days.value - 1;
                
                    tmp = new Date(d.getTime() + temp1*24*60*60*1000)
                    alert (tmp.format("d/m/Y"));                
                    //document.updatefrm.finish.value = tmp;
                }
                </script>
                <a href="javascript:addDays2Date();"><small>Υπολογισμός<br>Ημ.Λήξης</small></a>
  <?php              
                echo "</td></tr>";
                echo "<tr><td>Ημ/νία λήξης</td><td>";
		$myCalendar = new tc_calendar("finish", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setPath("calendar/");
		$myCalendar->dateAllow("2011-01-01", "2030-12-31");
		$myCalendar->setAlignment("left", "bottom");
		//$myCalendar->setSpecificDate(array("2011-04-01", "2011-04-14", "2010-12-25"), 0, "year");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";

		echo "<tr><td>Λόγος</td><td><input type='text' name='logos' /></td></tr>";

		echo "<tr><td>Σχόλια</td><td><input type='text' name='comments' /></td></tr>";

		echo "	</table>";
		echo "	<input type='hidden' name = 'id' value='$id'>";
                echo "	<input type='hidden' name = 'emp_id' value='$emp_id'>";
		// action = 1 gia prosthiki
		echo "  <input type='hidden' name = 'action' value='1'>";
                echo "  <input type='hidden' name = 'sxoletos' value=$sxol_etos>";
		echo "	<input type='submit' value='Προσθήκη'>";
		//echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='index.php'\">";
                echo "	<INPUT TYPE='button' VALUE='Επιστροφή στην καρτέλα εκπ/κού' onClick=\"parent.location='ektaktoi.php?id=$emp_id&op=view'\">";
		echo "	</form>";
        ?>
        <div id='results'></div>
        <?php
                        echo "    </center>";
                        echo "</body>";
                        echo "</html>";
                }
                mysql_close();
        ?>
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