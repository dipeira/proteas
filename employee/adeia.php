<?php
  header('Content-type: text/html; charset=utf-8');
  require_once"../config.php";
  require_once"../include/functions.php";
  require_once"../include/functions_controls.php";
  require_once '../tools/num2word.php';

  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

  // Demand authorization                
  include("../tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false)
    header("Location: ../tools/login.php");

?>
<html>
  <head>
  <?php 
    $root_path = '../';
    $page_title = 'Άδεια';
    require '../etc/head.php'; 
    ?>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
	<LINK href="../css/jquery-ui.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../js/jquery.validate.js"></script>
	<script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
	<script type="text/javascript" src="../js/datepicker-gr.js"></script>
	<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
	<style>
		/* Button container styling */
		.button-group {
			margin-top: 20px;
			margin-bottom: 20px;
			text-align: center;
			display: flex;
			justify-content: center;
			gap: 10px;
			flex-wrap: wrap;
		}
		
		/* Form table styling improvements */
		.imagetable.stable {
			margin: 20px auto;
		}
		
		.imagetable.stable td:first-child {
			font-weight: 600;
			color: #1e40af;
			background: linear-gradient(90deg, #f0f9ff 0%, #e0f2fe 100%);
			border-right: 2px solid #bae6fd;
			width: 30%;
		}
		
		.imagetable.stable td:nth-child(2) {
			background: #ffffff;
		}
		
		/* Input field styling */
		.imagetable.stable input[type="text"],
		.imagetable.stable input[type="number"] {
			width: 100%;
			max-width: 400px;
		}
		
		/* Select element styling - comprehensive */
		.imagetable.stable select,
		.imagetable select {
			width: 100%;
			max-width: 400px;
			padding: 10px 14px;
			padding-right: 40px;
			border: 2px solid #e5e7eb;
			border-radius: 8px;
			font-size: 14px;
			font-family: "Inter", "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			background: #ffffff;
			background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23374151' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
			background-repeat: no-repeat;
			background-position: right 14px center;
			background-size: 12px;
			appearance: none;
			-webkit-appearance: none;
			-moz-appearance: none;
			cursor: pointer;
			transition: all 0.2s ease;
			color: #374151;
			font-weight: 500;
		}
		
		.imagetable.stable select:hover,
		.imagetable select:hover {
			border-color: #4FC5D6;
			background-color: #f8fafc;
		}
		
		.imagetable.stable select:focus,
		.imagetable select:focus {
			outline: none;
			border-color: #10b981;
			box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
			background-color: #ffffff;
		}
		
		.imagetable.stable select:active,
		.imagetable select:active {
			border-color: #059669;
		}
		
		/* Select option styling */
		.imagetable.stable select option,
		.imagetable select option {
			padding: 10px 14px;
			background: #ffffff;
			color: #374151;
			font-size: 14px;
		}
		
		.imagetable.stable select option:hover,
		.imagetable select option:hover {
			background: #f0f9ff;
		}
		
		.imagetable.stable select option:checked,
		.imagetable select option:checked {
			background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
			color: #ffffff;
			font-weight: 600;
		}
		
		/* Results div styling */
		#results {
			margin-top: 20px;
			padding: 15px;
			background: #f0f9ff;
			border-radius: 8px;
			text-align: center;
		}
		
		#word {
			margin-top: 15px;
		}
	</style>
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
						$.post('adeia_print_pw.php', $("#wordfrm").serialize(), function(data) {
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
						$.post('update_adeia.php', $("#updatefrm").serialize(), function(data) {
							$('#results').html(data);
						});
					}
				});
			});
			function replace() {
				var select = document.getElementById("type");
				var value = select.options[select.selectedIndex].value;
				var textfield = document.getElementById("logos");
				if (value == 3)
						textfield.innerHTML = "Αρ.Γνωμάτευσης Α/θμιας<br>Υγειονομικής Επιτροπής";
				else
						textfield.innerHTML = "Λόγος";
			}        
	</script>
  </head>
  <body>
  <?php include('../etc/menu.php'); ?>
    <center>
    	<h2>Άδεια</h2>
      <?php
		//if (!isset($_GET['emp']) && ($_GET['op']!="delete"))
		$usrlvl = $_SESSION['userlevel'];
		
		if (!isset($_GET['emp']))
		{
			$query = "SELECT * from adeia where id=".$_GET['adeia'];
			$result = mysqli_query($mysqlconnection, $query);

			$id = mysqli_result($result, 0, "id");
			$emp_id = mysqli_result($result, 0, "emp_id");
			$type = mysqli_result($result, 0, "type");
			$prot = mysqli_result($result, 0, "prot");
			$prot_apof = mysqli_result($result, 0, "prot_apof");
			$hm_apof = mysqli_result($result, 0, "hm_apof");
			$hm_prot = mysqli_result($result, 0, "hm_prot");
			$date = mysqli_result($result, 0, "date");
			$vev_dil = mysqli_result($result, 0, "vev_dil");
			$days = mysqli_result($result, 0, "days");
			$start = mysqli_result($result, 0, "start");
			$finish = mysqli_result($result, 0, "finish");
			$logos = mysqli_result($result, 0, "logos");
			$logos = str_replace(" ", "&nbsp;", $logos);
			$comments = mysqli_result($result, 0, "comments");
			$comments = str_replace(" ", "&nbsp;", $comments);
			$created = mysqli_result($result, 0, "created");
		}

		if ($_GET['op']=="edit")
		{
			echo "<form id='updatefrm' name='update' action='update_adeia.php' method='POST'>";
			echo "<table class=\"imagetable stable\" border='1'>";
			$query1 = "select * from employee where id=$emp_id";
			$result1 = mysqli_query($mysqlconnection, $query1);
			$name = mysqli_result($result1, 0, "name");
			$surname = mysqli_result($result1, 0, "surname");
			$kl1 = mysqli_result($result1, 0, "klados");
			$q2 = "select * from klados where id=$kl1";
			$res2 = mysqli_query($mysqlconnection, $q2);
			$klados = mysqli_result($res2, 0, "perigrafh");
			echo "<tr><td>Όνομα</td><td>$name</td></tr>";
			echo "<tr><td>Επώνυμο</td><td>$surname</td></tr>";
			echo "<tr><td>Κλάδος</td><td>$klados</td></tr>";

			echo "<tr><td>Τύπος</td><td>";
			adeiaCmb($type,$mysqlconnection);
			echo "</td></tr>";

			echo "<tr><td>Αρ.Πρωτοκόλου απόφασης</td><td><input type='text' name='prot_apof' value=\"$prot_apof\" /></td></tr>";
			echo "<tr><td>Ημ/νία Πρωτοκόλου απόφασης</td><td>";
			modern_datepicker("hm_apof", $hm_apof, array(
				'minDate' => '2011-01-01',
				'maxDate' => '2030-12-31',
				'disabledDays' => array('sun', 'sat')
			));
			echo "</td></tr>";
									
			echo "<tr><td>Αρ.Πρωτοκόλου αίτησης</td><td><input type='text' name='prot' value=\"$prot\" /></td></tr>";
									
			echo "<tr><td>Ημ/νία Πρωτοκόλου</td><td>";
			modern_datepicker("hm_prot", $hm_prot, array(
				'minDate' => '2011-01-01',
				'maxDate' => '2030-12-31',
				'disabledDays' => array('sun', 'sat')
			));
			echo "</td></tr>";
									
			echo "<tr><td>Ημ/νία αίτησης</td><td>";
			modern_datepicker("date", $date, array(
				'minDate' => '2011-01-01',
				'maxDate' => '2030-12-31',
				'disabledDays' => array('sun', 'sat')
			));
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

			echo "<tr><td>Ημέρες</td><td><input type='text' name='days' value=\"$days\" /></td></tr>";

			echo "<tr><td>Ημ/νία έναρξης</td><td>";
			modern_datepicker("start", $start, array(
				'minDate' => '2011-01-01',
				'maxDate' => '2030-12-31',
				'disabledDays' => array('sun', 'sat')
			));
			echo "</td></tr>";

			echo "<tr><td>Ημ/νία λήξης</td><td>";
			modern_datepicker("finish", $finish, array(
				'minDate' => '2011-01-01',
				'maxDate' => '2030-12-31',
				'disabledDays' => array('sun', 'sat')
			));
			echo "</td></tr>";

			echo "<tr><td><div id='logos'>Λόγος</div></td><td><input type='text' name='logos' value=\"$logos\" /></td></tr>";

			echo "<tr><td>Σχόλια</td><td><input type='text' name='comments' value=\"$comments\" /></td></tr>";

			echo "	</table>";
			echo "	<input type='hidden' name = 'id' value='$id'>";
			echo "	<input type='hidden' name = 'emp_id' value='$emp_id'>";
			echo "	<div class='button-group'>";
			echo "	<input type='submit' value='Επεξεργασία' class='btn btn-primary'>";
			echo "	<INPUT TYPE='button' VALUE='Επιστροφή' class='btn' onClick=\"parent.location='adeia.php?adeia=$id&op=view'\">";
			echo "	</div>";
			//echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick='history.go(-1);return true;'>";
			echo "	</form>";
			echo "</div>";
			echo "    </center>";
			echo "</body>";
?>
			<div id='results'></div>
<?php
		echo "</html>";
    }
	elseif ($_GET['op']=="view")
	{
		echo "<div style='margin: 20px 0;'>";
		echo "<table class=\"imagetable stable\" border='1'>";
		$query1 = "select * from employee where id=$emp_id";
		$result1 = mysqli_query($mysqlconnection, $query1);
		$name = mysqli_result($result1, 0, "name");
		$surname = mysqli_result($result1, 0, "surname");
		$kl1 = mysqli_result($result1, 0, "klados");
		$q2 = "select * from klados where id=$kl1";
		$res2 = mysqli_query($mysqlconnection, $q2);
		$klados = mysqli_result($res2, 0, "perigrafh");
		echo "<tr><td>Επώνυμο</td><td><a href='employee.php?id=$emp_id&op=view'>$surname</a></td></tr>";
		echo "<tr><td>Όνομα</td><td>$name</td></tr>";
		echo "<tr><td>Κλάδος</td><td>$klados</td></tr>";

		$query1 = "select type from adeia_type where id=$type";
		$result1 = mysqli_query($mysqlconnection, $query1);
		$typewrd = mysqli_result($result1, 0, "type");
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
		if ($_SESSION['adeia'])
		{
				echo "<tr><td colspan=2 align='center'>";
				//Form gia Bebaiwsh
				echo "<form id='wordfrm' name='wordfrm' action='adeia_print.php' method='POST'>";
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

				echo "<INPUT TYPE='submit' VALUE='Εκτύπωση άδειας' class='btn btn-primary'>";
				echo "</form>";
				?>
				<div id="word"></div>
				<?php
				echo "</td></tr>";
		}
		echo "	</table>";
		echo "</div>";

		// Find prev - next row id
		$qprev = "SELECT id FROM adeia WHERE id < $id AND emp_id = $emp_id ORDER BY id DESC LIMIT 1";
		$res1 = mysqli_query($mysqlconnection, $qprev);
		if (mysqli_num_rows($res1))
			$previd = mysqli_result($res1, 0, "id");
		$qnext = "SELECT id FROM adeia WHERE id > $id AND emp_id = $emp_id ORDER BY id ASC LIMIT 1";
		$res1 = mysqli_query($mysqlconnection, $qnext);
		if (mysqli_num_rows($res1))
			$nextid = mysqli_result($res1, 0, "id");

		echo "<div class='button-group'>";
		if ($previd)
			echo "	<INPUT TYPE='button' VALUE='<<' class='btn' onClick=\"parent.location='adeia.php?id=$emp_id&adeia=$previd&op=view'\">";
		if ($usrlvl < 3)
			echo "	<INPUT TYPE='button' VALUE='Επεξεργασία' class='btn btn-primary' onClick=\"parent.location='adeia.php?id=$emp_id&adeia=$id&op=edit'\">";
		echo "	<INPUT TYPE='button' VALUE='Επιστροφή στην καρτέλα εκπ/κού' class='btn' onClick=\"parent.location='employee.php?id=$emp_id&op=view'\">";
		if ($nextid)
			echo "	<INPUT TYPE='button' VALUE='>>' class='btn' onClick=\"parent.location='adeia.php?id=$emp_id&adeia=$nextid&op=view'\">";
		echo "</div>";
		echo "<div class='button-group'>";
		echo "<INPUT TYPE='button' VALUE='Αρχική σελίδα' class='btn btn-red' onClick=\"parent.location='../index.php'\">";
		echo "</div>";
		echo "    </center>";

		echo "</body>";
		echo "</html>";
	}
	if ($_GET['op']=="delete")
	{
		// Copies the to-be-deleted row to adeia_deleted table for backup purposes.Also adds a row to adeia_del_log for logging purposes...
		$query1 = "INSERT INTO adeia_deleted SELECT e.* FROM adeia e WHERE id =".$_GET['adeia'];
		$result1 = mysqli_query($mysqlconnection, $query1);
		$query1 = "INSERT INTO adeia_del_log (adeia_id, userid) VALUES (".$_GET['adeia'].",".$_SESSION['userid'].")";
		$result1 = mysqli_query($mysqlconnection, $query1);
		$query = "DELETE from adeia where id=".$_GET['adeia'];
		$result = mysqli_query($mysqlconnection, $query);
		// Copies the deleted row to employee)deleted

		echo "<div style='margin: 20px; padding: 15px; background: #f0f9ff; border-radius: 8px; text-align: center;'>";
		if ($result)
			echo "<p style='color: #059669; font-weight: 600;'>Η εγγραφή με κωδικό $id διαγράφηκε με επιτυχία.</p>";
		else
			echo "<p style='color: #dc2626; font-weight: 600;'>Η διαγραφή απέτυχε...</p>";
		echo "<INPUT TYPE='button' VALUE='Επιστροφή στην καρτέλα εκπ/κού' class='btn' onClick=\"parent.location='employee.php?id=$emp_id&op=view'\">";
		echo "</div>";
	}
	if ($_GET['op']=="add")
	{
		echo "<div style='margin: 20px 0;'>";
		echo "<form id='updatefrm' name='updatefrm' action='update_adeia.php' method='POST'>";
		echo "<table class=\"imagetable stable\" border='1'>";
		$emp_id = $_GET['emp'];
		$query1 = "select * from employee where id=$emp_id";
		$result1 = mysqli_query($mysqlconnection, $query1);
		$name = mysqli_result($result1, 0, "name");
		$surname = mysqli_result($result1, 0, "surname");
		$kl1 = mysqli_result($result1, 0, "klados");
		$q2 = "select * from klados where id=$kl1";
		$res2 = mysqli_query($mysqlconnection, $q2);
		$klados = mysqli_result($res2, 0, "perigrafh");
		echo "<tr><td>Όνομα</td><td>$name</td></tr>";
		echo "<tr><td>Επώνυμο</td><td>$surname</td></tr>";
		echo "<tr><td>Κλάδος</td><td>$klados</td></tr>";

		echo "<tr><td>Τύπος</td><td>";
		adeiaCmb($type,$mysqlconnection);
		echo "</td></tr>";

		echo "<tr><td>Αρ.Πρωτοκόλου απόφασης</td><td><input type='text' name='prot_apof' /></td></tr>";
		echo "<tr><td>Ημ/νία Πρωτοκόλου απόφασης</td><td>";
		modern_datepicker("hm_apof", date('Y-m-d'), array(
			'minDate' => '2011-01-01',
			'maxDate' => '2030-12-31',
			'disabledDays' => array('sun', 'sat')
		));
		echo "</td></tr>";
                
		echo "<tr><td>Αρ.Πρωτοκόλου αίτησης</td><td><input type='text' name='prot' /></td></tr>";
		echo "<tr><td>Ημ/νία Πρωτοκόλου αίτησης</td><td>";
		modern_datepicker("hm_prot", date('Y-m-d'), array(
			'minDate' => '2011-01-01',
			'maxDate' => '2030-12-31',
			'disabledDays' => array('sun', 'sat')
		));
		echo "</td></tr>";
                
		echo "<tr><td>Ημ/νία αίτησης</td><td>";
		modern_datepicker("date", date('Y-m-d'), array(
			'minDate' => '2011-01-01',
			'maxDate' => '2030-12-31',
			'disabledDays' => array('sun', 'sat')
		));
		echo "</td></tr>";

		//echo "<tr><td>Βεβαίωση / Δήλωση</td><td><input type='text' name='vev_dil' /></td></tr>";
		// Need to check - hide-unhide depending on adeia type...
		echo "<tr id='vevdil' style='display:none'><td>Βεβαίωση / Δήλωση<br>(για αναρρωτικές)</td><td>";
		
		//<input type='text' name='vev_dil' value=$vev_dil /></td></tr>";
		echo "<select name='vev_dil'>";
		echo "<option value=\"0\" selected>Όχι</option>";
		echo "<option value=\"1\">Βεβαίωση</option>";
		echo "<option value=\"2\">Υπεύθυνη Δήλωση</option>";
		echo "</select>";
		echo "</td></tr>";

		echo "<tr><td>Ημέρες</td><td><input type='text' name='days' /></td></tr>";
		echo "<tr><td>Ημ/νία έναρξης</td><td>";
		modern_datepicker("start", date('Y-m-d'), array(
			'minDate' => '2011-01-01',
			'maxDate' => '2030-12-31',
			'disabledDays' => array('sun', 'sat')
		));
                
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
		modern_datepicker("finish", date('Y-m-d'), array(
			'minDate' => '2011-01-01',
			'maxDate' => '2030-12-31',
			'disabledDays' => array('sun', 'sat')
		));
		echo "</td></tr>";

		echo "<tr><td><div id='logos'>Λόγος</div></td><td><input type='text' name='logos' /></td></tr>";

		echo "<tr><td>Σχόλια</td><td><input type='text' name='comments' /></td></tr>";

		echo "	</table>";
		echo "	<input type='hidden' name = 'id' value='$id'>";
		echo "	<input type='hidden' name = 'emp_id' value='$emp_id'>";
		// action = 1 gia prosthiki
		echo "  <input type='hidden' name = 'action' value='1'>";
		echo "	<div class='button-group'>";
		echo "	<input type='submit' value='Προσθήκη' class='btn btn-primary'>";
		echo "	<INPUT TYPE='button' VALUE='Επιστροφή στην καρτέλα εκπ/κού' class='btn' onClick=\"parent.location='employee.php?id=$emp_id&op=view'\">";
		echo "	</div>";
		//echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
		echo "	</form>";
		echo "</div>";
        ?>
        <div id='results'></div>
<?php
		echo "    </center>";
		echo "</body>";
		echo "</html>";
	}
		mysqli_close($mysqlconnection);
	?>
</html>
