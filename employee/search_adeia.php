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
    <?php 
    $root_path = '../';
    $page_title = 'Αναζήτηση Αδειών';
    require '../etc/head.php'; 
    ?>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery.validate.js"></script>
	<script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
	<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
	<script type="text/javascript" src='../tools/calendar/calendar.js'></script>
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
			display: block;
			font-weight: 500;
			color: #374151;
			margin-bottom: 3px;
			font-size: 0.8125rem;
		}
		.form-input, .form-select {
			width: 100%;
			padding: 6px 10px;
			border: 1px solid #d1d5db;
			border-radius: 4px;
			font-size: 0.8125rem;
			transition: border-color 0.2s, box-shadow 0.2s;
		}
		.form-input:focus, .form-select:focus {
			outline: none;
			border-color: #3b82f6;
			box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
		}
		.form-section select {
			width: 100%;
			padding: 6px 10px;
			border: 1px solid #d1d5db;
			border-radius: 4px;
			font-size: 0.8125rem;
			transition: border-color 0.2s, box-shadow 0.2s;
			background-color: white;
		}
		.form-section select:focus {
			outline: none;
			border-color: #3b82f6;
			box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
		}
		.radio-group {
			display: flex;
			flex-direction: column;
			gap: 6px;
		}
		.radio-item {
			display: flex;
			align-items: center;
			gap: 6px;
		}
		.radio-item input[type="radio"] {
			width: 16px;
			height: 16px;
			cursor: pointer;
			margin: 0;
		}
		.radio-item label {
			font-size: 0.8125rem;
			color: #374151;
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
		.alert-message {
			background: #fef2f2;
			border: 1px solid #fecaca;
			color: #991b1b;
			padding: 12px 16px;
			border-radius: 6px;
			margin-bottom: 16px;
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
				$.post('results_adeia.php', $("#searchfrm").serialize(), function(data) {
					$('#results').html(data);
					$('html, body').animate({
						scrollTop: $('#results').offset().top - 100
					}, 500);
				});
			}
		});
	});
	</script>
  </head>
  <body class="p-4 md:p-6 lg:p-8"> 
  <?php include('../etc/menu.php'); ?>
    <div class="max-w-4xl mx-auto">
	 <h2 class="text-3xl font-bold text-gray-800 mb-2">Αναζήτηση αδειών</h2>
      <?php
        $usrlvl = $_SESSION['userlevel'];
        
         if ($usrlvl>2) {
            echo "<div class='alert-message'>";
            echo "<strong>Δεν έχετε δικαίωμα να αναζητήσετε στις άδειες.</strong><br>";
            echo "Πατήστε <a href='../index.php' class='text-blue-600 hover:underline'>εδώ</a> για επιστροφή...";
            echo "</div>";
         } else {
            echo "<div id=\"content\">";
            echo "<form id='searchfrm' name='searchfrm' action='' method='POST' autocomplete='off'>";
            
            // Date Range Section
            echo "<div class='form-section'>";
            echo "<h3>Ημερομηνίες</h3>";
            echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-3'>";
            
            echo "<div class='form-group'>";
            echo "<label class='form-label'>Ημ/νία από</label>";
            echo "<div class='date-range-item'>";
            $myCalendar = new tc_calendar("hm_from", true);
            $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
            $myCalendar->setDate(date("d"), date("m"), date("Y"));
            $myCalendar->setPath("../tools/calendar/");
            $myCalendar->setYearInterval(2011, date("Y"));
            $myCalendar->setAlignment("left", "bottom");
            $myCalendar->disabledDay("sun,sat");
            $myCalendar->writeScript();
            echo "</div>";
            echo "</div>";
            
            echo "<div class='form-group'>";
            echo "<label class='form-label'>Ημ/νία έως</label>";
            echo "<div class='date-range-item'>";
            $myCalendar = new tc_calendar("hm_to", true);
            $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
            $myCalendar->setDate(date("d"), date("m"), date("Y"));
            $myCalendar->setPath("../tools/calendar/");
            $myCalendar->setYearInterval(2011, date("Y")+1);
            $myCalendar->dateAllow("2011-01-01", "2030-12-31");
            $myCalendar->setAlignment("left", "bottom");
            $myCalendar->disabledDay("sun,sat");
            $myCalendar->writeScript();
            echo "</div>";
            echo "</div>";
            
            echo "</div>"; // grid
            echo "</div>"; // form-section
            
            // Search Options Section
            echo "<div class='form-section'>";
            echo "<h3>Επιλογές Αναζήτησης</h3>";
            echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3'>";
            
            echo "<div class='form-group'>";
            echo "<label class='form-label'>Ημ/νία αναφοράς</label>";
            echo "<div class='radio-group'>";
            echo "<div class='radio-item'>";
            echo "<input type='radio' name='date_from' value='0' id='date_from_0' checked />";
            echo "<label for='date_from_0'>Ημ/νία έναρξης</label>";
            echo "</div>";
            echo "<div class='radio-item'>";
            echo "<input type='radio' name='date_from' value='1' id='date_from_1' />";
            echo "<label for='date_from_1'>Ημ/νία λήξης</label>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            
            echo "<div class='form-group'>";
            echo "<label class='form-label'>Είδος άδειας</label>";
            adeiaCmb($type,$mysqlconnection,0,true);
            echo "</div>";
            
            echo "<div class='form-group'>";
            echo "<label class='form-label'>Τύπος προσωπικού</label>";
            echo "<div class='radio-group'>";
            echo "<div class='radio-item'>";
            echo "<input type='radio' name='mon_anapl' value='0' id='mon_anapl_0' checked />";
            echo "<label for='mon_anapl_0'>Μόνιμοι</label>";
            echo "</div>";
            echo "<div class='radio-item'>";
            echo "<input type='radio' name='mon_anapl' value='1' id='mon_anapl_1' />";
            echo "<label for='mon_anapl_1'>Αναπληρωτές</label>";
            echo "</div>";
            echo "<div class='radio-item'>";
            echo "<input type='radio' name='mon_anapl' value='2' id='mon_anapl_2' />";
            echo "<label for='mon_anapl_2'>Αναπληρωτές προηγ.ετών</label>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            
            echo "</div>"; // grid
            echo "</div>"; // form-section
            
            echo "<div class='button-group'>";
            echo "<button type='submit' class='btn-primary'>🔍 Αναζήτηση</button>";
            echo "<button type='button' class='btn-secondary' onClick=\"window.location.reload()\">🔄 Επαναφορά</button>";
            echo "<button type='button' class='btn-red' onClick=\"parent.location='../index.php'\">← Επιστροφή</button>";
            echo "</div>";
            
            echo "</form>";
            echo "</div>";
         }	
    mysqli_close($mysqlconnection);
?>
		</div>
		<div id='results' class='mt-8'></div>
		</body>
		</html>
