<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once"../include/functions.php";
  
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  // Demand authorization                
  include("../tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false){
    header("Location: ../tools/login.php");
  }
  $klados_type = 0;
	
?>
<html>
  <head>
  <?php 
    $root_path = '../';
    $page_title = 'Καρτέλα Αναπληρωτή';
    require '../etc/head.php'; 
  ?>
	<LINK href="../css/jquery-ui.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../js/jquery.validate.js"></script>
	<script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
	<script type="text/javascript" src="../js/jquery.table.addrow.js"></script>
	<script type="text/javascript" src="../js/datepicker-gr.js"></script>
        <script type="text/javascript" src="../js/jquery_notification_v.1.js"></script>
        <link href="../css/jquery_notification.css" type="text/css" rel="stylesheet"/> 
	<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
	<style>
        /* Employee View Page Styling - Same as employee.php */
        body {
            padding: 20px;
        }
        
        /* Main header styling */
        .imagetable th[colspan] {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 50%, #2A8B9A 100%) !important;
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
            padding: 18px 20px;
            text-transform: none;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }
        
        /* Table row styling with section grouping */
        .imagetable tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .imagetable tbody tr:hover {
            background-color: #f8fafc;
        }
        
        /* Label cells styling - first and third columns */
        .imagetable td:first-child,
        .imagetable td:nth-child(3) {
            background: linear-gradient(90deg, #f0f9ff 0%, #e0f2fe 100%);
            font-weight: 600;
            color: #1e40af;
            padding: 12px 16px;
            border-right: 2px solid #bae6fd;
            width: 25%;
            vertical-align: top;
        }
        
        /* Data cells styling - second and fourth columns */
        .imagetable td:nth-child(2),
        .imagetable td:nth-child(4) {
            padding: 12px 16px;
            color: #374151;
            vertical-align: top;
            background: #ffffff;
        }
        
        /* Alternate row styling for visual separation */
        .imagetable tbody tr:nth-child(even) td:first-child,
        .imagetable tbody tr:nth-child(even) td:nth-child(3) {
            background: linear-gradient(90deg, #e0f7fa 0%, #b2ebf2 100%);
        }
        
        /* Hover effect for rows */
        .imagetable tbody tr:hover td:nth-child(2),
        .imagetable tbody tr:hover td:nth-child(4) {
            background-color: #f8fafc;
        }
        
        /* Section separator - visual grouping */
        .imagetable tbody tr td[colspan] {
            background: #f0f9ff !important;
            border-top: 2px solid #4FC5D6;
            border-bottom: 1px solid #bae6fd;
            padding: 10px 16px !important;
            font-weight: 600;
            color: #1e40af;
        }
        
        /* Expandable sections styling */
        .show_hide, .show_hide2, .show_hide3, #archive-toggle {
            color: #4FC5D6;
            font-weight: 600;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-block;
            transition: all 0.2s;
            background: linear-gradient(90deg, #e0f7fa 0%, #b2ebf2 100%);
            border: 1px solid #4FC5D6;
        }
        
        .show_hide:hover, .show_hide2:hover, .show_hide3:hover, #archive-toggle:hover {
            background: linear-gradient(90deg, #b2ebf2 0%, #80deea 100%);
            color: #2A8B9A;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(79, 197, 214, 0.3);
        }
        
        /* Expandable content styling */
        .slidingDiv, .slidingDiv2, .slidingDiv3 {
            background: #f9fafb;
            padding: 14px 16px;
            border-radius: 8px;
            border-left: 4px solid #4FC5D6;
            margin-top: 8px;
            line-height: 1.8;
            color: #374151;
        }
        
        /* Important data highlighting */
        .imagetable td strong {
            color: #dc2626;
            font-weight: 700;
        }
        
        /* Link styling */
        .imagetable a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .imagetable a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        
        /* Email links */
        .imagetable a[href^="mailto:"] {
            color: #4FC5D6;
        }
        
        .imagetable a[href^="mailto:"]:hover {
            color: #3BA8B8;
        }
        
        /* Small text styling */
        .imagetable small {
            color: #6b7280;
            font-size: 0.8125rem;
        }
        
        /* Table cell text wrapping */
        .imagetable td {
            word-wrap: break-word;
        }
        
        /* Form inputs in view mode */
        .imagetable input[type="text"],
        .imagetable input[type="submit"],
        .imagetable textarea,
        .imagetable select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .imagetable input[type="text"]:focus,
        .imagetable textarea:focus,
        .imagetable select:focus {
            outline: none;
            border-color: #4FC5D6;
            box-shadow: 0 0 0 3px rgba(79, 197, 214, 0.1);
        }
        
        /* Buttons styling */
        .imagetable input[type="submit"],
        .imagetable input[type="button"] {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(79, 197, 214, 0.3);
        }
        
        .imagetable input[type="submit"]:hover,
        .imagetable input[type="button"]:hover {
            background: linear-gradient(135deg, #3BA8B8 0%, #2A8B9A 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(79, 197, 214, 0.4);
        }
        
        /* Button navigation */
        INPUT[type="button"][value="<<"],
        INPUT[type="button"][value=">>"] {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
            font-size: 1.25rem;
            padding: 8px 16px;
        }
        
        /* Print button */
        input[value="Εκτύπωση"] {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
        }
        
        /* Archive toggle */
        #archive-toggle {
            margin: 8px 0;
        }
        
        /* Lists in expandable sections */
        .slidingDiv ul, .slidingDiv2 ul, .slidingDiv3 ul {
            list-style: none;
            padding-left: 0;
            margin: 8px 0;
        }
        
        .slidingDiv li, .slidingDiv2 li, .slidingDiv3 li {
            padding: 8px 12px;
            margin: 4px 0;
            background: white;
            border-left: 3px solid #4FC5D6;
            border-radius: 4px;
        }
        
        /* Checkbox styling */
        .imagetable input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #4FC5D6;
            cursor: pointer;
        }
        
        /* Service time form styling */
        #wordfrm {
            background: #f0fdf4;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #86efac;
            margin: 12px 0;
        }
        
        /* Error highlighting - will be applied via inline styles if needed */
        .error-highlight {
            background-color: #fee2e2 !important;
            color: #dc2626 !important;
            font-weight: 700;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .imagetable td:first-child,
            .imagetable td:nth-child(3) {
                width: 30%;
            }
            
            .imagetable th[colspan] {
                font-size: 1rem;
                padding: 14px 16px;
            }
        }
        
        /* Last update info */
        .imagetable tr:last-child td {
            background: #f9fafb;
            color: #6b7280;
            font-size: 0.8125rem;
            padding: 8px 16px;
            border-top: 2px solid #e5e7eb;
        }
        
        /* Modal dialog styling */
        .ui-dialog {
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .ui-dialog-titlebar {
            background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 50%, #2A8B9A 100%);
            color: white;
            border: none;
            border-radius: 8px 8px 0 0;
            padding: 12px 20px;
            font-weight: 600;
        }
        
        .ui-dialog-titlebar-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            padding: 4px 8px;
            transition: background 0.2s;
        }
        
        .ui-dialog-titlebar-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .ui-dialog-titlebar-close .ui-icon {
            background-image: none;
            text-indent: 0;
            overflow: visible;
        }
        
        .ui-dialog-titlebar-close .ui-icon:before {
            content: "×";
            font-size: 20px;
            line-height: 1;
        }
        
        .ui-widget-overlay {
            background: rgba(0, 0, 0, 0.5);
            opacity: 1;
        }
    </style>
	<script type="text/javascript">
           
    $(document).ready(function(){
      $("#wordfrm").validate({
        debug: false,
        submitHandler: function(form) {
          // do other stuff for a valid form
          $.post('vev_yphr_anapl_pw.php', $("#wordfrm").serialize(), function(data) {
            $('#word').html(data);
          });
        }
      });
    });
    var mylink = "<small>Παρακαλώ δώστε έγκυρη πράξη ή δημιουργήστε μία: </small><a target=\"_blank\" href=\"praxi.php\">Πράξεις</a>";
        
    $(document).ready(function(){
      $("#updatefrm").validate({
        debug: false,
                          rules: {
          name: "required", surname: "required", afm: "required", klados: "required", praxi: {"required": true, min:2 }, type: "required"
        },
        messages: {
          name: "Παρακαλώ δώστε όνομα", surname: "Παρακαλώ δώστε επώνυμο", afm: "Παρακαλώ δώστε έγκυρo ΑΦΜ",
                                  klados: "Παρακαλώ δώστε έγκυρη τιμή", praxi: mylink, type: "Παρακαλώ δώστε έγκυρη τιμή"
        },
        submitHandler: function(form) {
          // do other stuff for a valid form
          $.post('update_ekt.php', $("#updatefrm").serialize(), function(data) {
            $('#results').html(data);
          });
        }
      });
    });
    $().ready(function(){
      $(".slidingDiv").hide();
      $(".show_hide").show();

      $('.show_hide').click(function(e){
        e.preventDefault();
        $(".slidingDiv").slideToggle();
      });
      $("#archive-toggle").click(function(e) {
        e.preventDefault();
        $("#yphrethsh-archive").slideToggle();
      });
    });
  </script>

  </head>
  <body> 
  <?php include('../etc/menu.php'); ?>
    <center>
      <?php
        $usrlvl = $_SESSION['userlevel'];
       if ($_GET['op']!="add")
       {
           if ($_GET['sxoletos']) {
               $sxol_etos = $_GET['sxoletos'];
               $sxoletos = $_GET['sxoletos'];
               $query = "SELECT * FROM ektaktoi_old e join yphrethsh_ekt y on e.id = y.emp_id where e.id = ".$_GET['id']." AND y.sxol_etos = $sxol_etos AND e.sxoletos=$sxoletos";
           }
           else {
                $query = "SELECT * FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where e.id = ".$_GET['id']." AND y.sxol_etos = $sxol_etos";
           }

          $result = mysqli_query($mysqlconnection, $query);
          $num=mysqli_num_rows($result);
          
          if ($num > 0)
          {
              $multi = 1;
              for ($i=0; $i<$num; $i++)
              {
                  $yphr_id_arr[$i] = mysqli_result($result, $i, "yphrethsh");
                  $yphr_arr[$i] = getSchool (mysqli_result($result, $i, "yphrethsh"), $mysqlconnection);
                  $hours_arr[$i] = mysqli_result($result, $i, "hours");
              }            
          }
          else
          {
              if ($_GET['sxoletos']) {
                $query = "SELECT * FROM ektaktoi_old where id=".$_GET['id']." AND sxoletos=".$_GET['sxoletos'];
              } else {
                $query = "SELECT * from ektaktoi where id=".$_GET['id'];
              }
              $result = mysqli_query($mysqlconnection, $query);
              $num=mysqli_num_rows($result);
              $sx_yphrethshs_id = mysqli_result($result, 0, "sx_yphrethshs");
              $sx_yphrethshs = getSchool ($sx_yphrethshs_id, $mysqlconnection);
          }
            //}
          $id = mysqli_result($result, 0, 0);
          $name = mysqli_result($result, 0, "name");
          $type = mysqli_result($result, 0, "type");
          $surname = mysqli_result($result, 0, "surname");
          $klados_id = mysqli_result($result, 0, "klados");
          $klados = getKlados($klados_id,$mysqlconnection);
          // if nip or nip eidikhs
          if ($klados_id == 1 || $klados_id == 16 || $klados_id == 17)
              $klados_type = 2;
          // if ebp or sx.nosileytes
          elseif (in_array($klados_id, [12, 25, 26, 8, 21, 9, 10, 27, 11])) {
              $klados_type = 0;
          }
          else
              $klados_type = 1;
          $metakinhsh = stripslashes(mysqli_result($result, 0, "metakinhsh"));
          $patrwnymo = mysqli_result($result, 0, "patrwnymo");
          $mhtrwnymo = mysqli_result($result, 0, "mhtrwnymo");
          $afm = mysqli_result($result, 0, "afm");
          $vathm = mysqli_result($result, 0, "vathm");
          $mk = mysqli_result($result, 0, "mk");
          $hm_mk = mysqli_result($result, 0, "hm_mk");
		      $analipsi = mysqli_result($result, 0, "analipsi");
          $hm_anal = mysqli_result($result, 0, "hm_anal");
          $hm_apox = mysqli_result($result, 0, "hm_apox");
          $met_did = mysqli_result($result, 0, "met_did");
          //$ya = mysqli_result($result, 0, "ya");
          //$apofasi = mysqli_result($result, 0, "apofasi");
          $comments = mysqli_result($result, 0, "comments");
          $comments = str_replace(" ", "&nbsp;", $comments);
          $stathero = mysqli_result($result, 0, "stathero");
          $kinhto = mysqli_result($result, 0, "kinhto");
          $praxi = mysqli_result($result, 0, "praxi");
          $updated= mysqli_result($result, 0, "updated");
          $thesi = mysqli_result($result, 0, "thesi");
          $entty = mysqli_result($result, 0, "ent_ty");
          $wres = mysqli_result($result, 0, "wres");
          $email = mysqli_result($result, 0, "email");
          $email_psd = mysqli_result($result, 0, "email_psd");
          
          $kat = mysqli_result($result, 0, "status");
          switch ($kat)
          {   
              case 1:
                  $katast = "Εργάζεται";
                  break;
              case 2:
                  $katast = "Λύση Σχέσης - Παραίτηση";
                  break;
              case 3:
                  $katast = "Άδεια";
                  break;
              case 4:
                  $katast = "Διαθεσιμότητα";
                  break;
              case 5:
                  $katast = "Απουσία COVID-19";
                  break;
          }
       }
                ?>
        <script type="text/javascript">
        $().ready(function() {
		$("#yphr").autocomplete("get_school.php", {
                               extraParams: {type: <?php echo $klados_type; ?>},
				width: 260,
				matchContains: true,
				selectFirst: false
			});
	});
                
        $().ready(function() {
                // fix to allow PE06 bypass school_type
                let e_params = '<?= $klados; ?>' == 'ΠΕ06' ? {} : {type: <?php echo $klados_type; ?>};
		$(".addRow").btnAddRow(function(row){
                        row.find(".yphrow").autocomplete("get_school.php", {
                                extraParams: e_params,
				width: 260,
				matchContains: true,
				selectFirst: false
                        })
                });
                $(".delRow").btnDelRow();
                        $(".yphrow").autocomplete("get_school.php", {
                                extraParams: e_params,
				width: 260,
				matchContains: true,
				selectFirst: false
                        });
                });
        </script>
        <?php
if ($_GET['op']=="add")
{
        if ($usrlvl == 3){
                echo "Δεν επιτρέπεται η πρόσβαση...";
                echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
                die();
        }
        echo "<h3>Προσθήκη αναπληρωτή εκπαιδευτικού</h3>";
        echo "<form id='updatefrm' action='update_ekt.php' method='POST'>";
        echo "<table class=\"imagetable\" border='1'>";
        
        echo "<tr><td>Επώνυμο</td><td><input type='text' name='surname' /></td></tr>";
        echo "<tr><td>Όνομα</td><td><input type='text' name='name' /></td></tr>";
        echo "<tr><td>Πατρώνυμο</td><td><input type='text' name='patrwnymo' /></td></tr>";
        echo "<tr><td>Μητρώνυμο</td><td><input type='text' name='mhtrwnymo' /></td></tr>";
        echo "<tr><td>Α.Φ.Μ.</td><td><input type='text' name='afm' /></td></tr>";
        echo "<tr><td>Σταθερό</td><td><input type='text' name='stathero' /></td></tr>";
        echo "<tr><td>Κινητό</td><td><input type='text' name='kinhto' /></td></tr>";
        echo "<tr><td>Κλάδος</td><td>";
        kladosCmb($mysqlconnection);
        echo "</td></tr>";
        //echo "<tr><td>Βαθμός</td><td><input type='text' name='vathm' /></td></tr>";
        //echo "<tr><td>Μ.Κ.</td><td><input type='text' name='mk' /></td></tr>";
        
        //echo "<tr><td>Ανάληψη υπηρεσίας</td><td><input type='text' name='analipsi' /></td></tr>";
        echo "<tr><td>Ημ/νία ανάληψης</td><td>";
        modern_datepicker("hm_anal", date('Y-m-d'), array(
            'minDate' => '2020-01-01',
            'maxDate' => date('Y-m-d'),
            'disabledDays' => array('sun', 'sat'),
            'yearRange' => '2020:' . date('Y')
        ));
        echo "</td></tr>";		
                        
        echo "<tr><td>Μεταπτυχιακό/Διδακτορικό</td><td>";
        metdidCombo(0);		
        
        echo "<tr><td>Τύπος Απασχόλησης</td><td>";
        typeCmb($mysqlconnection);
        echo "</td></tr>";
        echo "<tr><td>Σχόλια</td><td><textarea rows=4 cols=80 name='comments' ></textarea></td></tr>";
        //echo "<tr><td>Υπουργική Απόφαση</td><td><input type='text' name='ya' /></td></tr>";
        //echo "<tr><td>Απόφαση Δ/ντή</td><td><input type='text' name='apofasi' /></td></tr>";
        echo "<tr><td>Πράξη:</td><td>";
        tblCmb($mysqlconnection, "praxi",$praxi);
        echo "</td></tr>"; 
        echo "<div id=\"content\">";
        echo "<form autocomplete=\"off\">";
        echo "<tr><td>Σχολείο(-α) Υπηρέτησης";
        echo "<a href=\"\" onclick=\"window.open('../help/help.html#school_ekt','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"../images/help.gif\"/></a>";
        //echo "</td><td><input type=\"text\" name=\"yphr\" id=\"yphr\" size=50/>";
        echo "</td><td><input type=\"text\" name=\"yphr[]\" class=\"yphrow\" id=\"yphrow\" />";
        echo "&nbsp;&nbsp;<input type=\"text\" name=\"hours[]\" size=1 />";
        echo "&nbsp;<input class=\"addRow\" type=\"button\" value=\"Προσθήκη\" />";
        echo "<input class=\"delRow\" type=\"button\" value=\"Αφαίρεση\" />";
        //echo "</form>";
        echo "</div>";
        thesianaplselectcmb(0);
        echo "	</table>";
        echo "	<input type='hidden' name = 'id' value='$id'>";
        // action = 1 gia prosthiki
        echo "  <input type='hidden' name = 'status' value='1'>";
        echo "  <input type='hidden' name = 'action' value='1'>";
        echo "<br>";
        echo "	<input type='submit' value='Καταχώρηση'>";
        echo "	<INPUT TYPE='button' VALUE='Επιστροφή στη λίστα αναπληρωτών' onClick=\"parent.location='ektaktoi_list.php'\">";
        echo "<br>";
        echo "	<br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική Σελίδα' onClick=\"parent.location='../index.php'\">";
        echo "	</form>";
?>
<div id='results'></div>
<?php
        echo "    </center>";
        echo "</body>";
        echo "</html>";
}

if ($_GET['op']=="edit")
{
        if ($usrlvl == 3){
                echo "Δεν επιτρέπεται η πρόσβαση...";
                echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
                die();
        }
        echo "<h3>Επεξεργασία αναπληρωτή εκπαιδευτικού</h3>";
        echo "<form id='updatefrm' name='update' action='update_ekt.php' method='POST'>";
        echo "<table class=\"imagetable\" border='1'>";
        echo "<tr><td>Επώνυμο</td><td><input type='text' name='surname' value=$surname /></td></tr>";
        echo "<tr><td>Όνομα</td><td><input type='text' name='name' value=$name /></td></tr>";
        echo "<tr><td>Πατρώνυμο</td><td><input type='text' name='patrwnymo' value=$patrwnymo /></td></tr>";
        echo "<tr><td>Μητρώνυμο</td><td><input type='text' name='mhtrwnymo' value=$mhtrwnymo /></td></tr>";
        echo "<tr><td>Α.Φ.Μ.</td><td><input type='text' name='afm' value=$afm /></td></tr>";
        echo "<tr><td>Σταθερό</td><td><input type='text' name='stathero' value=$stathero /></td></tr>";
        echo "<tr><td>Κινητό</td><td><input type='text' name='kinhto' value=$kinhto /></td></tr>";
        echo "<tr><td>email<br>email (ΠΣΔ)</td><td><input type='text' name='email' value=$email /><br><input type='text' name='email_psd' value=$email_psd /></td></tr>";
        echo "<tr><td>Κλάδος</td><td>";
        kladosCombo($klados_id,$mysqlconnection);
        echo "</td></tr>";
        echo "<tr><td>";
        show_tooltip('Κατάσταση','Επιλέξτε ένα από: Εργάζεται, Λύση Σχέσης-Παραίτηση, Άδεια, Διαθεσιμότητα');
        echo "</td><td>";
        katastCmb($kat);
        echo "</td></tr>";
        //echo "<tr><td>Βαθμός</td><td>";
        //vathmosCmb1($vathm, $mysqlconnection);
        //echo "</td><tr>";
        //<input type='text' name='vathm' value=$vathm /></td></tr>";
        //echo "<tr><td>Μ.Κ.</td><td><input type='text' name='mk' value=$mk /></td></tr>";
        echo "<tr><td>";
        show_tooltip("Τύπος απασχόλησης","Επιλέξτε ένα από: Αναπληρωτής Μ.Ω., Αναπληρωτής, Αναπληρωτής ΕΣΠΑ, ΕΕΠ, ΕΒΠ, ΖΕΠ / ΕΚΟ");
        echo "</td><td>";
        
        typeCmb1($type, $mysqlconnection);
        echo "</td></tr>";
        //echo "<tr><td>Ανάληψη</td><td><input type='text' name='analipsi' value=$analipsi /></td></tr>";
        
        echo "<tr><td>Ημ/νία ανάληψης</td><td>";
        modern_datepicker("hm_anal", $hm_anal, array(
            'minDate' => '2020-01-01',
            'maxDate' => date('Y-m-d'),
            'disabledDays' => array('sun', 'sat')
        ));
        echo "</td></tr>";
        echo "<tr><td>Ημ/νία αποχώρησης</td><td>";
        modern_datepicker("hm_apox", $hm_apox, array(
            'minDate' => '2020-01-01'
        ));
        echo "</td></tr>";		
                        
        echo "<tr><td>Μεταπτυχιακό/Διδακτορικό</td><td>";
        metdidCombo($met_did);
        //echo "<tr><td>Υπουργική Απόφαση</td><td><input size=50 type='text' name='ya' value=$ya /></td></tr>";
        //echo "<tr><td>Απόφαση Δ/ντή</td><td><input size=50 type='text' name='apofasi' value=$apofasi /></td></tr>";
        echo "<tr><td>Πράξη:</td><td>";
        tblCmb($mysqlconnection, "praxi",$praxi, null, 'name');
        echo "</td></tr>";
        echo "<tr><td>Υποχρεωτικό ωράριο</td><td><input type='text' name='wres' value=$wres /></td></tr>";
        echo "<tr><td>Σχόλια</td><td><textarea rows=4 cols=80 name='comments' >$comments</textarea></td></tr>";
        
        //new 15-02-2012: implemented with jquery.autocomplete
        echo "<div id=\"content\">";
        echo "<form autocomplete=\"off\">";
        
        if ($multi)
        {
                $count = count($yphr_arr);
                for ($i=0; $i<$count; $i++)
                {
                    echo "<tr><td>";
                    show_tooltip("Σχολείο (-α) Υπηρέτησης","Επιλέξτε σχολείο αφού εισάγετε μερικούς χαρακτήρες (αυτόματη συμπλήρωση).<br>
                    Πατώντας 'Προσθήκη' προστίθεται μια επιπλέον υπηρέτηση σε άλλο σχολείο, με 'Αφαίρεση' διαγράφεται η υπηρέτηση αυτή.");
                    echo "<a href=\"\" onclick=\"window.open('../help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"../images/help.gif\"/></a>";
                    echo "</td><td><input type=\"text\" name=\"yphr[]\" value='$yphr_arr[$i]' class=\"yphrow\" id=\"yphrow\" size=40/>";
                    echo "&nbsp;&nbsp;<input type=\"text\" name=\"hours[]\" value='$hours_arr[$i]' size=1 />";
                    echo "&nbsp;<input class=\"addRow\" type=\"button\" value=\"Προσθήκη\" />";
                    echo "<input class=\"delRow\" type=\"button\" value=\"Αφαίρεση\" />";
                    echo "</tr>";
                }
                }
        else
        {
          echo "<tr><td>";
          show_tooltip("Σχολείο (-α) Υπηρέτησης","Επιλέξτε σχολείο αφού εισάγετε μερικούς χαρακτήρες (αυτόματη συμπλήρωση).<br>
          Πατώντας 'Προσθήκη' προστίθεται μια επιπλέον υπηρέτηση σε άλλο σχολείο, με 'Αφαίρεση' διαγράφεται η υπηρέτηση αυτή.");
          echo "<a href=\"\" onclick=\"window.open('../help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"../images/help.gif\"/></a>";
          echo "</td><td><input type=\"text\" name=\"yphr[]\" value='$sx_yphrethshs' class=\"yphrow\" id=\"yphrow\" size=40/>";
          echo "&nbsp;&nbsp;<input type=\"text\" name=\"hours[]\" size=1 />";
          echo "&nbsp;<input class=\"addRow\" type=\"button\" value=\"Προσθήκη\" />";
          echo "<input class=\"delRow\" type=\"button\" value=\"Αφαίρεση\" />";
          echo "</tr>";
        }
        
        echo "<tr><td>Μετακινήσεις<br><br><small><strong>ΠΡΟΣΟΧΗ:</strong> Συμπληρώστε ως εξής: \"Αρχικά τοποθετήθηκε στο ΧΧΧΧΧΧ και έπειτα με την ΧΧΧ απόφαση τοποθετήθηκε στο\"</small></td>";
        echo "<td><textarea rows=4 cols=50 name='metakinhsh'>$metakinhsh</textarea></td></tr>";
        echo "</form>";
        echo "</div>";
        thesianaplselectcmb($thesi);
        echo "<tr>".ent_ty_selectcmb($entty,false,true)."</tr>";
        echo "	</table>";
        
        echo "	<input type='hidden' name = 'id' value='$id'>";
        echo "	<input type='submit' value='Αποθήκευση'>";
        echo "	<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='ektaktoi.php?id=$id&op=view'\">";
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
        ?>
        <script type="text/javascript">
        $(document).ready(function() {
            // Check if jQuery UI Dialog is available
            if (typeof $.ui === 'undefined' || typeof $.ui.dialog === 'undefined') {
                console.error('jQuery UI Dialog is not loaded');
                return;
            }
            
            $("#adeia").click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                var MyVar = <?php echo $id; ?>;
                var sxEtos = <?php echo $sxol_etos; ?>;
                
                console.log('Opening modal for temporary employee ID:', MyVar, 'School year:', sxEtos);
                
                // Clean up any existing modal and overlay
                if ($("#ekt-adeia-modal").length > 0) {
                    if ($("#ekt-adeia-modal").hasClass('ui-dialog-content') || $("#ekt-adeia-modal").parent().hasClass('ui-dialog')) {
                        $("#ekt-adeia-modal").dialog('destroy');
                    }
                    $("#ekt-adeia-modal").remove();
                }
                $(".ui-widget-overlay").remove();
                $(".ui-dialog").filter(function() {
                    return $(this).find('#ekt-adeia-modal').length > 0;
                }).remove();
                
                // Create new modal div (must be hidden to prevent flash of content)
                var $modalDiv = $('<div id="ekt-adeia-modal" style="display:none !important; visibility:hidden; position:absolute; top:-9999px;" title="Λίστα Αδειών Αναπληρωτή"></div>');
                $("body").append($modalDiv);
                
                // Show loading state
                $modalDiv.html('<div style="padding: 20px; text-align: center;"><p>Φόρτωση δεδομένων...</p></div>');
                
                // Initialize dialog
                $modalDiv.dialog({
                    modal: true,
                    width: 950,
                    height: 600,
                    maxHeight: $(window).height() - 50,
                    resizable: true,
                    autoOpen: true,
                    position: {
                        my: "center",
                        at: "center",
                        of: window
                    },
                    show: {
                        effect: "fade",
                        duration: 300
                    },
                    hide: {
                        effect: "fade",
                        duration: 200
                    },
                    close: function() {
                        var $this = $(this);
                        $this.dialog('destroy');
                        $this.remove();
                        $(".ui-widget-overlay").remove();
                    },
                    create: function(event, ui) {
                        $(this).css('display', 'block');
                    },
                    open: function(event, ui) {
                        console.log('Dialog opened');
                        var $dialog = $(this);
                        var $dialogParent = $dialog.parent();
                        
                        $dialogParent.css({
                            'position': 'fixed',
                            'top': '50%',
                            'left': '50%',
                            'transform': 'translate(-50%, -50%)',
                            'z-index': 1000
                        });
                        
                        $dialog.css({
                            'display': 'block',
                            'visibility': 'visible',
                            'position': 'relative',
                            'top': 'auto',
                            'left': 'auto'
                        });
                        
                        // Load content after dialog is fully visible
                        var $dialogContent = $(this);
                        console.log('Loading content from ekt_adeia_list.php');
                        $dialogContent.load("ekt_adeia_list.php?id=" + MyVar + "&sxol_etos=" + sxEtos + "&ajax=1", function(response, status, xhr) {
                            console.log('Content loaded, status:', status);
                            if (status == "error") {
                                console.error('Error loading content:', xhr.status, xhr.statusText);
                                $dialogContent.html('<div style="padding: 20px; text-align: center; color: red;"><p>Σφάλμα φόρτωσης δεδομένων</p></div>');
                            } else {
                                console.log('Content loaded successfully');
                                $dialogContent.css({
                                    'display': 'block',
                                    'visibility': 'visible',
                                    'position': 'relative',
                                    'top': 'auto',
                                    'left': 'auto'
                                });
                                
                                $dialogParent.css({
                                    'position': 'fixed',
                                    'top': '50%',
                                    'left': '50%',
                                    'transform': 'translate(-50%, -50%)',
                                    'z-index': 1000
                                });
                                
                                // Initialize tablesorter after content is loaded
                                setTimeout(function() {
                                    $dialogContent.find('table.tablesorter').each(function() {
                                        var $tbl = $(this);
                                        if (!$tbl.data('tablesorter-initialized')) {
                                            try {
                                                $tbl.tablesorter({widgets: ['zebra']});
                                                $tbl.data('tablesorter-initialized', true);
                                            } catch(e) {
                                                console.error('Tablesorter error for table:', e);
                                            }
                                        }
                                    });
                                }, 200);
                            }
                        });
                    }
                });
            });
        });
        </script>
<?php
        echo "<br>";
        echo "<table class=\"imagetable\" border='1'>";	
        echo "<tr>";
        //echo "<td colspan=2>ID</td><td colspan=2>$id</td>";
        echo "<th colspan=4 align=center>Καρτέλα αναπληρωτή εκπαιδευτικού</th>";
        echo "</tr>";
        echo "<tr><td>Επώνυμο</td><td>$surname</td><td>Όνομα</td><td>$name</td></tr>";
        echo "<tr><td>Πατρώνυμο</td><td>$patrwnymo</td><td>Μητρώνυμο</td><td>$mhtrwnymo</td></tr>";
        if ($usrlvl < 3){
                echo "<tr><td>Α.Φ.Μ.</td><td>$afm</td><td></td><td></td></tr>";
        }
        echo "<tr><td>Κλάδος</td><td>".getKlados($klados_id,$mysqlconnection, true)."</td><td>Κατάσταση</td><td>$katast</td></tr>";
        if ($usrlvl < 3){
                echo "<tr><td><a href=\"#\" class=\"show_hide\"><small>Εμφάνιση/Απόκρυψη<br>περισσοτέρων στοιχείων</small></a></td>";
                echo "<td colspan=3><div class=\"slidingDiv\">";
                echo "Τηλ.: $stathero - $kinhto<br>";
                echo "email: <a href='mailto:$email'>$email</a><br>";
                echo "email (ΠΣΔ): <a href='mailto:$email_psd'>$email_psd</a><br>";
                idiwtika_table("Αναπληρωτής", $id, $mysqlconnection);
                echo "</div>";
                echo "</td></tr>";
        } else {
                echo "<tr><td><a href=\"#\" class=\"show_hide\"><small>Εμφάνιση/Απόκρυψη<br>περισσοτέρων στοιχείων</small></a></td>";
                echo "<td colspan=3><div class=\"slidingDiv\">";
                echo "email: <a href='mailto:$email'>$email</a><br>";
                echo "email (ΠΣΔ): <a href='mailto:$email_psd'>$email_psd</a><br>";
                echo "</div>";
                echo "</td></tr>";
        }
        
        //$hm_mk = date ('d-m-Y', strtotime($hm_mk));
        //echo "<tr><td>Βαθμός</td><td>$vathm</td><td>Μ.Κ.</td><td>$mk &nbsp;<small>(από $hm_mk)</small></td></tr>";
        switch ($met_did)
        {
                case 0:
                        $met="Όχι";
                        break;
                case 1:
                        $met="Μεταπτυχιακό";
                        break;
                case 2:
                        $met="Διδακτορικό";
                        break;
                case 3:
                        $met="Μεταπτυχιακό & Διδακτορικό";
                        break;
                case 4:
                        $met="Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)";
                        break;
        }
        // echo "<tr><td colspan>Μεταπτυχιακό/Διδακτορικό</td><td colspan=3>$met</td></tr>";
        echo "<td>Μεταπτυχιακό/Διδακτορικό <small><a href='postgrad.php?op=list&afm=$afm' onclick=\"window.open('postgrad.php?op=list&afm=$afm','newwindow','width=1000,height=500');return false;\">(Λεπτομέρειες)</a></small></td><td colspan=3>$met</td></tr>";
                        
        echo "<tr><td>Σχόλια<br><br></td><td colspan='3'>".nl2br($comments)."</td></tr>"; 
        echo "<tr><td>Υποχρεωτικό ωράριο</td><td colspan='3'>$wres</td></tr>";
        
        // check if multiple schools
        if ($multi)
        {
                $count = count($yphr_arr);
                for ($i=0; $i<$count; $i++)
                {
                $sxoleia .=  "<a href=\"../school/school_status.php?org=$yphr_id_arr[$i]\">$yphr_arr[$i]</a> ($hours_arr[$i] ώρες)<br>";
                $counthrs += $hours_arr[$i];
                }
                if ($count > 1)
                echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3>$sxoleia<br><small>($counthrs ώρες σε $count Σχολεία)</small></td></tr>";
                else
                echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3>$sxoleia</td></tr>";
        }
        else
        {
                echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3><a href=\"../school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a></td></tr>";
        }
        
        echo "<tr><td><a id='archive-toggle' href='#'>Ιστορικό αλλαγών υπηρετήσεων</a></td><td colspan=3>";
        display_yphrethsh_archive($mysqlconnection, $id, $sxol_etos, false);
        echo "</td></tr>";

        $typos = get_type($type,$mysqlconnection);
        echo "<tr><td>Ανάληψη υπηρεσίας</td><td colspan=3>$analipsi</td>";
        $date_anal = date ("d-m-Y",  strtotime($hm_anal));
        echo "<tr><td>Ημ/νία Ανάληψης</td><td colspan=3>$date_anal</td>";
        if ($kat == 2){
                $date_apox = date ("d-m-Y",  strtotime($hm_apox));
                echo "<tr><td>Ημ/νία Αποχώρησης</td><td colspan=3>$date_apox</td>";
        }
        echo "<tr><td>Μετακινήσεις</td><td colspan=3>".nl2br($metakinhsh)."</td></tr>";
        echo "<tr><td>Τύπος Απασχόλησης</td><td colspan=3>$typos</td>";
        echo "<tr><td>Πραξη</td><td colspan=3>";
        $qry = $sxoletos ? 
        "SELECT * FROM praxi_old WHERE id=$praxi AND sxoletos = $sxoletos" :
        "SELECT * FROM praxi WHERE id=$praxi";
        $res = mysqli_query($mysqlconnection, $qry);

        echo $sxoletos ? 
                "<a href='ektaktoi_prev.php?praxi_old=$praxi&sxoletos=$sxoletos'>".mysqli_result($res, 0, 'name')."</a>" :
                "<a href='ektaktoi_list.php?praxi=$praxi'>".getNamefromTbl($mysqlconnection, "praxi", $praxi)."</a>";
        echo "</td></tr>";
        
        $ya = mysqli_result($res, 0, 'ya');
        $apofasi = mysqli_result($res, 0, 'apofasi');
        $ada = mysqli_result($res, 0, 'ada');
        $ada_apof = mysqli_result($res, 0, 'ada_apof');
        echo "<tr><td>Υπουργική Απόφαση</td><td colspan=3>$ya</td></tr>";
        echo "<tr><td>Α.Δ.Α. Y.A.</td><td colspan=3><a href='https://diavgeia.gov.gr/decision/view/$ada' target='_blank'>$ada</a></td></tr>";
        echo "<tr><td>Απόφαση Δ/ντή</td><td colspan=3>$apofasi&nbsp;&nbsp;<small>(Α.Δ.Α.:&nbsp;<a href='https://diavgeia.gov.gr/decision/view/$ada_apof' target='_blank'>$ada_apof</a>)</small></td></tr>";
        echo "<tr><td>Θέση</td><td colspan=3>".thesianaplcmb($thesi)."</td></tr>";
        echo "<tr><td>Υπηρέτηση σε Τμήμα Ένταξης<br> / Τάξη υποδοχής / Παράλληλη στήριξη</td><td colspan=3>".ent_ty_cmb($entty)."</td></tr>";
        

        echo "<tr><td>Βεβαίωση υπηρεσίας έως: </td><td>";
        //stringify schools
        $hour_sum = 0;
        for ($i=0; $i < count($yphr_arr); $i++)
        {
          $schools .=  $yphr_arr[$i] ." (" . $hours_arr[$i] ." ώρες), ";
          $hour_sum += $hours_arr[$i];
        }
        $schools = substr($schools, 0, -2); 
        
        //Form gia Bebaiwsh
        echo "<form id='wordfrm' name='wordfrm' action='' method='POST'>";
        modern_datepicker("sel_date", date('Y-m-d'), array(
            'minDate' => '2020-01-01'
        ));
        echo "<br>";
        echo "<input type='hidden' name='surname' value=$surname>";
        echo "<input type='hidden' name='name' value=$name>";
        echo "<input type='hidden' name='patrwnymo' value=$patrwnymo>";
        echo "<input type='hidden' name='klados' value='$klados'>";
        //echo "<input type='hidden' name='afm' value=$afm>";
        echo "<input type='hidden' name='ada' value='$ada'>";
        $meiwmeno = $type == 1 ? true : false;
        echo "<input type='hidden' name='meiwmeno' value=$meiwmeno>";
        echo "<input type='hidden' name='hoursum' value=$hour_sum>";
        echo "<input type='hidden' name='date_anal' value=$date_anal>";
        echo "<input type='hidden' name='date_apox' value=$hm_apox>";
        echo "<input type='hidden' name='ya' value=$ya>";
        echo "<input type='hidden' name='apofasi' value=$apofasi>";
        echo "<input type='hidden' name='sxoletos' value=$sxol_etos>";
        echo "<input type='hidden' name='schools' value='$schools'>";
        if ($usrlvl < 3){
                echo "<INPUT TYPE='submit' value='Βεβαίωση υπηρεσίας'>"; 
        }
        echo "</form>";
        //Form gia metakinhsh (only for user pispe)
        if ($_SESSION['user'] === 'pispe'){
          echo "<form id='metakfrm' name='metakfrm' action='metakinhsh.php' method='POST'>";
          echo "<input type='hidden' name='type' value=$type>";
          echo "<input type='hidden' name='surname' value=$surname>";
          echo "<input type='hidden' name='name' value=$name>";
          echo "<input type='hidden' name='patrwnymo' value=$patrwnymo>";
          echo "<input type='hidden' name='klados' value='$klados'>";
          echo "<input type='hidden' name='afm' value=$afm>";
          echo "<input type='hidden' name='ada' value='$ada'>";
          echo "<input type='hidden' name='ya' value='$ya'>";
          echo "<input type='hidden' name='apofasi' value='$apofasi'>";
          echo "<input type='hidden' name='ada_apof' value='$ada_apof'>";
          echo "<input type='hidden' name='yphrethsh' value='$schools'>";
          echo "<input type='hidden' name='id' value=$id>";
          echo "<input type='hidden' name='praxi' value=$praxi>";
          echo "<INPUT TYPE='submit' value='Μετακίνηση'>"; 
          echo "</form>";
        }
        ?>
      <div id="word"></div>
        <?php
        echo "</td><td colspan=2></td></tr>";
        
        echo $updated > 0 ? "<tr><td colspan=4 align='right'><small>Τελευταία ενημέρωση: ".date("d-m-Y H:i", strtotime($updated))."</small></td></tr>" : null;
        echo "	</table>";
        
        echo "<br>";
        // echo "  <INPUT TYPE='submit' id='adeia' VALUE='Άδειες'>"; future use?
        if ($usrlvl < 3){
                $can_edit = $_GET['sxoletos'] ? 'disabled' : '';
                echo "	<INPUT TYPE='button' VALUE='Επεξεργασία' $can_edit onClick=\"parent.location='ektaktoi.php?id=$id&op=edit'\">";
        }
        echo "  <input type='button' value='Εκτύπωση' onclick='javascript:window.print()' />";
        if ($usrlvl < 3){
                echo "  <INPUT TYPE='button' id='adeia' VALUE='Άδειες'>";
        }
        echo $sxoletos ?
                "   <INPUT TYPE='button' VALUE='Επιστροφή στη λίστα αναπληρωτών' onClick=\"parent.location='ektaktoi_prev.php?sxoletos=$sxoletos'\">" :
                "   <INPUT TYPE='button' VALUE='Επιστροφή στη λίστα αναπληρωτών' onClick=\"parent.location='ektaktoi_list.php'\">";

        echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        echo "    </center>";
        echo "</body>";
        echo "</html>";	
}
if ($_GET['op']=="delete")
{
        if ($usrlvl == 3){
                echo "Δεν επιτρέπεται η πρόσβαση...";
                echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
                die();
        }
        // Copies the to-be-deleted row to employee_deleted table for backup purposes.Also inserts a row on employee_del_log...
        //$query1 = "INSERT INTO ektaktoi_deleted SELECT e.* FROM ektaktoi e WHERE id =".$_GET['id'];
        //$result1 = mysqli_query($mysqlconnection, $query1)
        //$query1 = "INSERT INTO ektaktoi_log (emp_id, userid, action) VALUES (".$_GET['id'].",".$_SESSION['userid'].", 2)";
        //$result1 = mysqli_query($mysqlconnection, $query1)
        $query = "DELETE from ektaktoi where id=".$_GET['id'];
        $result = mysqli_query($mysqlconnection, $query);
        // Copies the deleted row to employee)deleted
        
        if ($result)
                echo "Η εγγραφή με κωδικό $id διαγράφηκε με επιτυχία.";
        else
                echo "Η διαγραφή απέτυχε...";
        echo "	<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='ektaktoi_list.php'\">";
}

mysqli_close($mysqlconnection);
?> 
