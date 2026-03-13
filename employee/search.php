<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once "../include/functions.php";
  require_once"../include/functions_controls.php";
  
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
    <?php 
    $root_path = '../';
    $page_title = 'Αναζήτηση';
    require '../etc/head.php'; 
    ?>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery.validate.js"></script>
	<LINK href="../css/jquery-ui.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../js/datepicker-gr.js"></script>
	<script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
	<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
	<style>
		.form-section {
			background: white;
			border-radius: 8px;
			padding: 12px;
			margin-bottom: 12px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.06);
		}
		.form-section h3 {
			margin-top: 0;
			margin-bottom: 10px;
			padding-bottom: 8px;
			border-bottom: 1px solid #e5e7eb;
			color: #1f2937;
			font-size: 1rem;
			font-weight: 600;
		}
		.form-group {
			margin-bottom: 8px;
		}
		.form-label {
			display: flex;
			align-items: center;
			justify-content: space-between;
			font-weight: 500;
			color: #374151;
			margin-bottom: 3px;
			font-size: 0.8125rem;
			min-height: 20px;
		}
		.form-label-text {
			flex: 1;
		}
		.display-checkbox-wrapper {
			display: flex;
			align-items: center;
			gap: 4px;
			margin-left: 8px;
		}
		.display-checkbox-wrapper input[type="checkbox"] {
			width: 14px;
			height: 14px;
			cursor: pointer;
			margin: 0;
		}
		.display-checkbox-wrapper label {
			font-size: 0.7rem;
			color: #6b7280;
			cursor: pointer;
			margin: 0;
			font-weight: 400;
		}
		.form-input, .form-select {
			width: 100%;
			padding: 10px 14px;
			border: 2px solid #e5e7eb;
			border-radius: 8px;
			font-size: 14px;
			transition: all 0.2s ease;
			background: #ffffff;
		}
		.form-input:focus, .form-select:focus {
			outline: none;
			border-color: #10b981;
			box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
		}
		.form-section select {
			width: 100%;
			padding: 10px 14px;
			border: 2px solid #e5e7eb;
			border-radius: 8px;
			font-size: 14px;
			transition: all 0.2s ease;
			background-color: white;
		}
		.form-section select:focus {
			outline: none;
			border-color: #10b981;
			box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
		}
		.checkbox-group {
			display: flex;
			align-items: center;
			gap: 5px;
			margin-bottom: 3px;
		}
		.checkbox-group input[type="checkbox"] {
			width: 16px;
			height: 16px;
			cursor: pointer;
			margin: 0;
		}
		.checkbox-label {
			font-size: 0.75rem;
			color: #6b7280;
			cursor: pointer;
		}
		.date-range-group {
			display: flex;
			gap: 8px;
			align-items: flex-start;
			flex-wrap: wrap;
		}
		.date-range-item {
			flex: 1;
			min-width: 150px;
		}
		.duration-group {
			display: flex;
			gap: 6px;
			align-items: center;
			flex-wrap: wrap;
		}
		.duration-input {
			width: 50px;
			padding: 6px 4px;
			border: 1px solid #d1d5db;
			border-radius: 4px;
			text-align: center;
			font-size: 0.8125rem;
		}
		.options-section {
			background: #f9fafb;
			border-left: 3px solid #3b82f6;
		}
		.btn-primary {
			background: #3b82f6;
			color: white;
			padding: 8px 20px;
			border: none;
			border-radius: 6px;
			font-weight: 600;
			font-size: 0.875rem;
			cursor: pointer;
			transition: background 0.2s, transform 0.1s;
		}
		.btn-primary:hover {
			background: #2563eb;
			transform: translateY(-1px);
		}
		.btn-secondary {
			background: #6b7280;
			color: white;
			padding: 8px 20px;
			border: none;
			border-radius: 6px;
			font-weight: 600;
			font-size: 0.875rem;
			cursor: pointer;
			transition: background 0.2s;
		}
		.btn-secondary:hover {
			background: #4b5563;
		}
		.btn-red {
			background: #ef4444;
			color: white;
			padding: 8px 20px;
			border: none;
			border-radius: 6px;
			font-weight: 600;
			font-size: 0.875rem;
			cursor: pointer;
			transition: background 0.2s;
		}
		.btn-red:hover {
			background: #dc2626;
		}
		.button-group {
			display: flex;
			gap: 8px;
			flex-wrap: wrap;
			margin-top: 12px;
		}
		@media (max-width: 768px) {
			.form-section {
				padding: 10px;
			}
			.date-range-group {
				flex-direction: column;
			}
			.date-range-item {
				width: 100%;
			}
		}
	</style>
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
					$('html, body').animate({
						scrollTop: $('#results').offset().top - 100
					}, 500);
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
	</script>
  </head>
  <body class="p-4 md:p-6 lg:p-8"> 
  <?php include('../etc/menu.php'); ?>
    <div class="max-w-7xl mx-auto">
	 <h2 class="text-3xl font-bold text-gray-800 mb-2">Αναζήτηση προσωπικού</h2>
   <h3 class="text-xl text-gray-600 mb-6">(μονίμων & αναπληρωτών)</h3>
      <?php
		echo "<div id=\"content\">";
		echo "<form id='searchfrm' name='searchfrm' action='' method='POST' autocomplete='off'>";
		
		// Personal Information Section
		echo "<div class='form-section'>";
		echo "<h3>Προσωπικά Στοιχεία</h3>";
		echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-3'>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>Επώνυμο</label>";
		echo "<input type='text' name='surname' id='surname' class='form-input' />";
		echo "</div>";

		echo "<div class='form-group'>";
		echo "<label class='form-label'>Όνομα</label>";
		echo "<input type='text' id='name' name='name' class='form-input' />";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Πατρώνυμο</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dsppatr' id='dsppatr' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dsppatr' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<input type='text' name='patrwnymo' class='form-input' />";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Α.Μ.</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dspam' id='dspam' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dspam' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<input type='text' name='am' class='form-input' />";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Α.Φ.Μ.</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dspafm' id='dspafm' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dspafm' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<input type='text' name='afm' class='form-input' />";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>Τηλέφωνο</label>";
		echo "<input type='text' name='tel' class='form-input' />";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Email</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dspemail' id='dspemail' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dspemail' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<input type='text' name='email' class='form-input' />";
		echo "</div>";
		
		echo "</div>"; // grid
		echo "</div>"; // form-section
		
		// Professional Information Section
		echo "<div class='form-section'>";
		echo "<h3>Επαγγελματικά Στοιχεία</h3>";
		echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-3'>";

		echo "<div class='form-group'>";
		echo "<label class='form-label'>Μόνιμος/Αναπληρωτής</label>";
		echo "<select name=\"emptype\" class='form-select'>";
		echo "<option value=\"1\">Μόνιμος</option>";
		echo "<option value=\"2\">Αναπληρωτής</option>";
		echo "<option value=\"3\">Ιδιωτικός</option>";
		echo "</select>";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>Κλάδος</label>";
		kladosCmb($mysqlconnection, true);
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Βαθμός</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dspvathmos' id='dspvathmos' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dspvathmos' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		vathmosCmb($mysqlconnection);
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Μ.Κ.</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dspmk' id='dspmk' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dspmk' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<input type='text' name='mk' class='form-input' />";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Κατάσταση</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dspkatast' id='dspkatast' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dspkatast' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<select name=\"katast[]\" class='form-select' multiple>";
		echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
		echo "<option value=\"1\">Εργάζεται</option>";
		echo "<option value=\"2\">Λύση Σχέσης-Παραίτηση</option>";
		echo "<option value=\"3\">Άδεια</option>";
		echo "<option value=\"4\">Διαθεσιμότητα</option>";
		echo "<option value=\"5\">Απουσία COVID-19</option>";
		echo "</select>";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>Θέση</label>";
		echo "<select name=\"thesi\" class='form-select'>";
		echo "<option value='0' selected>Εκπαιδευτικός</option>";
		echo "<option value='1'>Υποδιευθυντής</option>";
		echo "<option value='2'>Διευθυντής/Προϊστάμενος</option>";
		echo "<option value='4'>Διοικητικός</option>";
		echo "<option value='5'>Ιδιωτικός</option>";
		echo "<option value='6'>Δ/ντής-Πρ/νος Ιδιωτικού Σχ.</option>";
		echo "</select>";
		echo "</div>";
		
		echo "<div class='form-group md:col-span-2 lg:col-span-3'>";
		echo "<label class='form-label'>Υπηρέτηση σε Τμήμα Ένταξης / Τάξη Υποδοχής / Παράλληλη στήριξη</label>";
		echo "<select name=\"entty\" class='form-select'>";
		echo "<option value='-1' selected>Όλοι</option>";
		echo "<option value='0'>Γενική αγωγή</option>";
		echo "<option value='1'>Τμήμα Ένταξης</option>";
		echo "<option value='2'>Τάξη Υποδοχής</option>";
		echo "<option value='3'>Παράλληλη στήριξη</option>";
		echo "</select>";
		echo "</div>";
		
		echo "</div>"; // grid
		echo "</div>"; // form-section
		
		// School Information Section
		echo "<div class='form-section'>";
		echo "<h3>Σχολικά Στοιχεία</h3>";
		echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3'>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>Σχολείο Οργανικής</label>";
		echo "<input type=\"text\" name=\"org\" id=\"org\" class='form-input' />";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>Σχολείο Υπηρέτησης</label>";
		echo "<input type=\"text\" name=\"yphr\" id=\"yphr\" class='form-input' />";
		echo "</div>";
		
		echo "</div>"; // grid
		echo "</div>"; // form-section
		
		// Dates Section
		echo "<div class='form-section'>";
		echo "<h3>Ημερομηνίες</h3>";
		echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-3'>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Ημ/νία Διορισμού</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dsphm_dior' id='dsphm_dior' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dsphm_dior' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<div class='date-range-group'>";
		echo "<div class='date-range-item'>";
		echo "<label class='text-xs text-gray-600 mb-1 block'>Από:</label>";
		modern_datepicker("hm_dior_from", null, array(
			'minDate' => '1970-01-01',
			'maxDate' => date('Y-m-d'),
			'yearRange' => '1970:' . date('Y')
		));
		echo "</div>";
		echo "<div class='date-range-item'>";
		echo "<label class='text-xs text-gray-600 mb-1 block'>Έως:</label>";
		modern_datepicker("hm_dior_to", null, array(
			'minDate' => '1970-01-01',
			'maxDate' => date('Y-m-d'),
			'yearRange' => '1970:' . date('Y')
		));
		echo "</div>";
		echo "</div>"; // date-range-group
		echo "</div>"; // form-group
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Ημ/νία Ανάληψης</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dsphm_anal' id='dsphm_anal' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dsphm_anal' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<div class='date-range-group'>";
		echo "<div class='date-range-item'>";
		echo "<label class='text-xs text-gray-600 mb-1 block'>Από:</label>";
		modern_datepicker("hm_anal_from", null, array(
			'minDate' => '1970-01-01',
			'maxDate' => date('Y-m-d'),
			'yearRange' => '1970:' . date('Y')
		));
		echo "</div>";
		echo "<div class='date-range-item'>";
		echo "<label class='text-xs text-gray-600 mb-1 block'>Έως:</label>";
		modern_datepicker("hm_anal_to", null, array(
			'minDate' => '1970-01-01',
			'maxDate' => date('Y-m-d'),
			'yearRange' => '1970:' . date('Y')
		));
		echo "</div>";
		echo "</div>"; // date-range-group
		echo "</div>"; // form-group
		
		echo "</div>"; // grid
		echo "</div>"; // form-section
		
		// Education & Experience Section
		echo "<div class='form-section'>";
		echo "<h3>Εκπαίδευση & Προϋπηρεσία</h3>";
		echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3'>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Μεταπτυχιακό/Διδακτορικό</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dspmetdid' id='dspmetdid' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dspmetdid' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<select name=\"met_did\" class='form-select'>";
		echo "<option value=''></option>";
		echo "<option value='0'>Όχι</option>";
		echo "<option value='1'>Μεταπτυχιακό</option>";
		echo "<option value='2'>Διδακτορικό</option>";		
		echo "<option value='3'>Μεταπτυχιακό & Διδακτορικό</option>";
		echo "<option value='3'>Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)</option>";
		echo "</select>";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Προϋπηρεσία</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dspproyhp' id='dspproyhp' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dspproyhp' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<div class='duration-group'>";
		echo "<select name=\"opp\" class='form-select' style='width: auto; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;'>";
		echo "<option value=\"=\" selected>=</option>";
		echo "<option value=\">\" >></option>";
		echo "<option value=\"<\" ><</option>";
		echo "</select>";
		echo "<input type='text' name='pyears' placeholder='Έτη' class='duration-input' />";
		echo "<input type='text' name='pmonths' placeholder='Μήνες' class='duration-input' />";
		echo "<input type='text' name='pdays' placeholder='Ημέρες' class='duration-input' />";
		echo "</div>";
		echo "</div>";
		
		echo "<div class='form-group md:col-span-2'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Συνολική Υπηρεσία <small>(από διορισμό + προϋπηρεσία)</small></span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dspsynol' id='dspsynol' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dspsynol' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<div class='duration-group mb-2'>";
		echo "<select name=\"ops\" class='form-select' style='width: auto; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;'>";
		echo "<option value=\"=\" selected>=</option>";
		echo "<option value=\">\" >></option>";
		echo "<option value=\"<\" ><</option>";
		echo "</select>";
		echo "<input type='text' name='syears' placeholder='Έτη' class='duration-input' />";
		echo "<input type='text' name='smonths' placeholder='Μήνες' class='duration-input' />";
		echo "<input type='text' name='sdays' placeholder='Ημέρες' class='duration-input' />";
		echo "</div>";
		echo "<div>";
		echo "<label class='text-xs text-gray-600 mb-1 block'>Συνολική Υπηρεσία έως:</label>";
		modern_datepicker("hm_synol", null, array(
			'minDate' => '1970-01-01',
			'maxDate' => '2030-12-31',
			'yearRange' => '1970:' . date('Y')
		));
		echo "</div>";
		echo "</div>";
		
		echo "</div>"; // grid
		echo "</div>"; // form-section
		
		// Additional Information Section
		echo "<div class='form-section'>";
		echo "<h3>Επιπλέον Πληροφορίες</h3>";
		echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3'>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>Σχόλια</label>";
		echo "<input type='text' name='comments' class='form-input' />";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Μονιμοποίηση</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dspmon' id='dspmon' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dspmon' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<div class='checkbox-group'>";
		echo "<input type='checkbox' name='monimopoihsh' id='monimopoihsh' />";
		echo "<label for='monimopoihsh' class='checkbox-label'>Ναι</label>";
		echo "</div>";
		echo "</div>";
		
		echo "<div class='form-group'>";
		echo "<label class='form-label'>";
		echo "<span class='form-label-text'>Ολοκληρωμένη Αξιολόγηση</span>";
		echo "<span class='display-checkbox-wrapper'>";
		echo "<input type='checkbox' name='dspaks' id='dspaks' title='Εμφάνιση πεδίου στα αποτελέσματα' />";
		echo "<label for='dspaks' title='Εμφάνιση πεδίου στα αποτελέσματα'>Εμφάνιση</label>";
		echo "</span>";
		echo "</label>";
		echo "<div class='checkbox-group'>";
		echo "<input type='checkbox' name='aksiologhsh' id='aksiologhsh' />";
		echo "<label for='aksiologhsh' class='checkbox-label'>Ναι</label>";
		echo "</div>";
		echo "</div>";
		
		echo "</div>"; // grid
		echo "</div>"; // form-section
		
		// Search Options Section
		echo "<div class='form-section options-section'>";
		echo "<h3>Επιλογές Αναζήτησης</h3>";
		echo "<div class='space-y-2'>";
		echo "<div class='checkbox-group'>";
		echo "<input type='checkbox' name='smeae' id='smeae' />";
		echo "<label for='smeae' class='checkbox-label'>Οργανική σε Ειδικό Σχολείο</label>";
		echo "</div>";
		echo "<div class='checkbox-group'>";
		echo "<input type='checkbox' name='outsiders' id='outsiders' />";
		echo "<label for='outsiders' class='checkbox-label'>Εμφάνιση και όσων δεν ανήκουν στη Δ/νση</label>";
		echo "</div>";
		echo "<div class='checkbox-group'>";
		echo "<input type='checkbox' name='or' id='or' />";
		echo "<label for='or' class='checkbox-label'>Να ισχύει ΤΟΥΛΑΧΙΣΤΟΝ ΕΝΑ από τα παραπάνω κριτήρια (λογικό OR)</label>";
		echo "</div>";
		echo "</div>";
		echo "</div>"; // form-section
		
		echo "<input type='hidden' name='set' value='$set'>";
		
		echo "<div class='button-group'>";
		echo "<button type='submit' class='btn-primary'>🔍 Αναζήτηση</button>";
		echo "<button type='button' class='btn-secondary' onClick=\"window.location.reload()\">🔄 Επαναφορά</button>";
		echo "<button type='button' class='btn-secondary' onclick=\"window.open('../help/help.html#search','', 'width=450, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=yes, resizable=no'); return false\">❓ Βοήθεια</button>";
		echo "<button type='button' class='btn-red' onClick=\"parent.location='../index.php'\">← Επιστροφή</button>";
		echo "</div>";
		
		echo "</form>";
		echo "</div>";
		
		mysqli_close($mysqlconnection);
?>
		<div id='results' class='mt-8'></div>
		</div>
		</body>
		</html>
