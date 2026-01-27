<meta http-equiv="content-type" content="text/html; charset=utf-8">
<?php
  header('Content-type: text/html; charset=utf-8'); 
  Require_once "../config.php";
  Require_once "../include/functions.php";
  
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  // Demand authorization                
  require "../tools/class.login.php";
  $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
}
  $klados_type = 0;
?>
<html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Μόνιμο Προσωπικό';
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
        /* Employee View Page Styling */
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
        
        /* Data cells with colspan should have white background and normal font weight */
        .imagetable td:nth-child(2)[colspan],
        .imagetable td:nth-child(4)[colspan] {
            background: #ffffff !important;
            font-weight: normal !important;
            color: #374151 !important;
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
            background: linear-gradient(135deg, rgba(47,152,171,0.22) 0%, rgba(58,168,184,0.25) 48%, rgba(79,197,214,0.30) 100%) !important;
            border-top: 3px solid #2A8B9A;
            border-bottom: 2px solid #4FC5D6;
            padding: 12px 20px !important;
            font-weight: 700;
            color: #0f3f66;
            letter-spacing: 0.6px;
            text-transform: none;
        }

        .separator {
            text-transform: uppercase !important;  
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f3f66;
            letter-spacing: 0.8px;
            padding: 10px 16px !important;
            margin: 12px 0 !important;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, rgba(47,152,171,0.22) 0%, rgba(58,168,184,0.25) 48%, rgba(79,197,214,0.30) 100%) !important;
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
        
        /* Status information highlighting */
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
        #yphrfrm {
            background: #f0f9ff;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #bae6fd;
            margin: 12px 0;
        }
        
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
            .imagetable td:first-child {
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
            /* color: white; */
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
            $("#yphrfrm").validate({
                debug: false,
                submitHandler: function(form) {
                    // do other stuff for a valid form
                    $.post('yphr.php', $("#yphrfrm").serialize(), function(data) {
                        $('#yphr_res').html(data);
                    });
                }
            });
        });
            
        $(document).ready(function(){
          $("#wordfrm").validate({
              debug: false,
              submitHandler: function(form) {
                  // do other stuff for a valid form
                  $.post('vev_yphr_pw.php', $("#wordfrm").serialize(), function(data) {
                      $('#word').html(data);
                  });
              }
          });
        });
            
            
        $(document).ready(function(){
            $("#updatefrm").validate({
                debug: false,
                rules: {
                    name: "required", surname: "required", 
                    afm: {required: true, digits: true, minlength: 8}, 
                    klados: "required", vathm: "required", 
                    mk: {required: true, digits: true}, org: "required", yphr:"required"
                },
                messages: {
                    name: "Παρακαλώ δώστε όνομα", surname: "Παρακαλώ δώστε επώνυμο", afm: "Παρακαλώ δώστε έγκυρη τιμή", am: "Παρακαλώ δώστε έγκυρη τιμή", 
                    klados: "Παρακαλώ δώστε έγκυρη τιμή", vathm: "Παρακαλώ δώστε έγκυρη τιμή", mk: "Παρακαλώ δώστε έγκυρη τιμή", org: "Παρακαλώ επιλέξτε από την αναδυόμενη λίστα",yphr: "Παρακαλώ επιλέξτε από την αναδυόμενη λίστα"
                },
                submitHandler: function(form) {
                    // do other stuff for a valid form
                    $.post('update.php', $("#updatefrm").serialize(), function(data) {
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
        });
        $().ready(function(){
            $(".slidingDiv2").hide();
            $(".show_hide2").show();

            $('.show_hide2').click(function(e){
                e.preventDefault();
                $(".slidingDiv2").slideToggle();
            });
        });
        $().ready(function(){
            $(".slidingDiv3").hide();
            $(".show_hide3").show();

            $('.show_hide3').click(function(e){
                e.preventDefault();
                $(".slidingDiv3").slideToggle();
            });
            $("#archive-toggle").click(function(e) {
                e.preventDefault();
                $("#yphrethsh-archive").slideToggle();
            });
        });
</script>

  </head>
  <body> 
    <?php require '../etc/menu.php'; ?>
    <center>
        <?php
            $usrlvl = $_SESSION['userlevel'];
            
        if ($_GET['op'] != "add") {
            $id = $_GET['id'];
            //$query = "SELECT * FROM employee e join yphrethsh y on e.id = y.emp_id where e.id = ".$_GET['id'];
            $query = "SELECT * FROM employee e join yphrethsh y on e.id = y.emp_id where e.id = ".$_GET['id']." AND y.sxol_etos = $sxol_etos";
            $result = mysqli_query($mysqlconnection, $query);
            $num=mysqli_num_rows($result);
            if (!$num){
              echo "<h3>Ο υπάλληλος δε βρέθηκε...</h3>";
              echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
              die();
            }
                
            for ($i=0; $i<$num; $i++)
            {
                $yphr_id_arr[$i] = mysqli_result($result, $i, "yphrethsh");
                $yphr_arr[$i] = getSchool(mysqli_result($result, $i, "yphrethsh"), $mysqlconnection);
                $hours_arr[$i] = mysqli_result($result, $i, "hours");
            }
            $name = mysqli_result($result, 0, "name");
            $surname = mysqli_result($result, 0, "surname");
            $klados_id = mysqli_result($result, 0, "klados");
                // if nip
            if ($klados_id == 1) {
                $klados_type = 2;
            } else {
                    $klados_type = 1;
            }
            $klados = getKlados($klados_id, $mysqlconnection);
            $sx_organ_id = mysqli_result($result, 0, "sx_organikhs");
            $sx_organikhs = getSchool($sx_organ_id, $mysqlconnection);
            $thesi = mysqli_result($result, 0, "thesi");
            $entty = mysqli_result($result, 0, "ent_ty");
            $patrwnymo = mysqli_result($result, 0, "patrwnymo");
            $mhtrwnymo = mysqli_result($result, 0, "mhtrwnymo");
            $afm = mysqli_result($result, 0, "afm");
            $am = mysqli_result($result, 0, "am");
            //$etos_gen = mysqli_result($result, 0, "etos_gen");
            $vathm = mysqli_result($result, 0, "vathm");
            $mk = mysqli_result($result, 0, "mk");
            $hm_mk = mysqli_result($result, 0, "hm_mk");
            $fek_dior = mysqli_result($result, 0, "fek_dior");
            $hm_dior = mysqli_result($result, 0, "hm_dior");
            $analipsi = mysqli_result($result, 0, "analipsi");
            $hm_anal = mysqli_result($result, 0, "hm_anal");
            $met_did = mysqli_result($result, 0, "met_did");
            $proyp = mysqli_result($result, 0, "proyp");
            $proyp_not = mysqli_result($result, 0, "proyp_not");
            $proyp_wrario = mysqli_result($result, 0, "proyp_wrario");
            $anatr = mysqli_result($result, 0, "anatr");
            $comments = mysqli_result($result, 0, "comments");
            $comments = str_replace(" ", "&nbsp;", $comments);
            $wres = mysqli_result($result, 0, "wres");
                
            //new 16-05-2013
            $tel = mysqli_result($result, 0, "tel");
            $address = mysqli_result($result, 0, "address");
            $idnum = mysqli_result($result, 0, "idnum");
            $amka = mysqli_result($result, 0, "amka");
            
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
            // updated: 05-09-2013
            $updated = mysqli_result($result, 0, "updated");
            // aney apodoxwn: 27-02-2014
            $aney = mysqli_result($result, 0, "aney");
            $aney_xr = mysqli_result($result, 0, "aney_xr");
            $aney_apo = mysqli_result($result, 0, "aney_apo");
            $aney_ews = mysqli_result($result, 0, "aney_ews");
            // idiwtiko ergo se dhmosio forea
            $idiwtiko = mysqli_result($result, 0, "idiwtiko");
            $idiwtiko_liksi = mysqli_result($result, 0, "idiwtiko_liksi");
            $idiwtiko_enarxi = mysqli_result($result, 0, "idiwtiko_enarxi");
            // idiwtiko ergo se idiwtiko forea
            $idiwtiko_id = mysqli_result($result, 0, "idiwtiko_id");
            $idiwtiko_id_enarxi = mysqli_result($result, 0, "idiwtiko_id_enarxi");
            $idiwtiko_id_liksi = mysqli_result($result, 0, "idiwtiko_id_liksi");
            // kat'oikon
            $katoikon = mysqli_result($result, 0, "katoikon");
            $katoikon_apo = mysqli_result($result, 0, "katoikon_apo");
            $katoikon_ews = mysqli_result($result, 0, "katoikon_ews");
            $katoikon_comm = mysqli_result($result, 0, "katoikon_comm");
            $katoikon_comm = str_replace(" ", "&nbsp;", $katoikon_comm);
            $email = mysqli_result($result, 0, "email");
            $email_psd = mysqli_result($result, 0, "email_psd");
            $org_ent = mysqli_result($result, 0, 'org_ent');
            // monimopoihsh - aksiologhsh
            $monimopoihsh = mysqli_result($result, 0, 'monimopoihsh');
            $monimopoihsh_apof = mysqli_result($result, 0, 'monimopoihsh_apof');
            $aksiologhsh = mysqli_result($result, 0, 'aksiologhsh');
            $aksiologhsh_date = mysqli_result($result, 0, 'aksiologhsh_date');
                    
        } // of if not add
        ?>
        
<script type="text/javascript">
    $().ready(function() {
        $("#org").autocomplete("get_school.php", {
            extraParams: {type: <?php echo $klados_type; ?>},
            width: 260,
            matchContains: true,
            selectFirst: false
        });
    });
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
            
    $(document).ready(function() {
        // Check if jQuery UI Dialog is available
        if (typeof $.ui === 'undefined' || typeof $.ui.dialog === 'undefined') {
            return;
        }
        
        $("#adeia").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            var MyVar = <?php if (isset($id)) echo $id; else echo 0; ?>;
            
            // Clean up any existing modal and overlay
            if ($("#adeia-modal").length > 0) {
                if ($("#adeia-modal").hasClass('ui-dialog-content') || $("#adeia-modal").parent().hasClass('ui-dialog')) {
                    $("#adeia-modal").dialog('destroy');
                }
                $("#adeia-modal").remove();
            }
            $(".ui-widget-overlay").remove();
            $(".ui-dialog").filter(function() {
                return $(this).find('#adeia-modal').length > 0;
            }).remove();
            
            // Create new modal div (must be hidden to prevent flash of content)
            var $modalDiv = $('<div id="adeia-modal" style="display:none !important; visibility:hidden; position:absolute; top:-9999px;" title="Λίστα Αδειών"></div>');
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
                    // Ensure dialog is properly positioned and visible
                    $(this).css('display', 'block');
                },
                open: function(event, ui) {
                    // Ensure dialog is visible and positioned correctly
                    var $dialog = $(this);
                    var $dialogParent = $dialog.parent();
                    
                    // Ensure dialog wrapper is properly positioned
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
                    $dialogContent.load("adeia_list.php?id=" + MyVar + "&ajax=1", function(response, status, xhr) {
                        if (status == "error") {
                            $dialogContent.html('<div style="padding: 20px; text-align: center; color: red;"><p>Σφάλμα φόρτωσης δεδομένων</p></div>');
                        } else {
                            // Ensure content is within dialog and not elsewhere
                            $dialogContent.css({
                                'display': 'block',
                                'visibility': 'visible',
                                'position': 'relative',
                                'top': 'auto',
                                'left': 'auto'
                            });
                            
                            // Make sure dialog is still visible and positioned
                            $dialogParent.css({
                                'position': 'fixed',
                                'top': '50%',
                                'left': '50%',
                                'transform': 'translate(-50%, -50%)',
                                'z-index': 1000
                            });
                            
                            // Reinitialize tabs and tablesorter after content is loaded
                            setTimeout(function() {
                                var $tabs = $dialogContent.find("#tabs");
                                
                                // Initialize tabs
                                if ($tabs.length && typeof $.ui !== 'undefined' && $.ui.tabs) {
                                    try {
                                        // Destroy existing tabs if any
                                        if ($tabs.hasClass('ui-tabs')) {
                                            $tabs.tabs('destroy');
                                        }
                                        // Initialize tabs
                                        $tabs.tabs({
                                            active: 0,
                                            collapsible: false,
                                            heightStyle: "content"
                                        });
                                    } catch(e) {
                                        // Fallback: try simple initialization
                                        try {
                                            $tabs.tabs();
                                        } catch(e2) {
                                            // Silent fail
                                        }
                                    }
                                }
                                
                                // Initialize tablesorter for all tables in tab panels (each tab has its own table)
                                $dialogContent.find('.ui-tabs-panel table.tablesorter, table.tablesorter').each(function() {
                                    var $tbl = $(this);
                                    if (!$tbl.data('tablesorter-initialized')) {
                                        try {
                                            $tbl.tablesorter({widgets: ['zebra']});
                                            $tbl.data('tablesorter-initialized', true);
                                        } catch(e) {
                                            // Silent fail
                                        }
                                    }
                                });
                            }, 200);
                        }
                    });
                }
            });
        });
        
        // Postgrad modal handler
        $("#postgrad-link").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            var afmValue = $(this).data('afm');
            
            //console.log('Opening modal for postgrad with AFM:', afmValue);
            
            // Clean up any existing modal and overlay
            if ($("#postgrad-modal").length > 0) {
                if ($("#postgrad-modal").hasClass('ui-dialog-content') || $("#postgrad-modal").parent().hasClass('ui-dialog')) {
                    $("#postgrad-modal").dialog('destroy');
                }
                $("#postgrad-modal").remove();
            }
            $(".ui-widget-overlay").remove();
            $(".ui-dialog").filter(function() {
                return $(this).find('#postgrad-modal').length > 0;
            }).remove();
            
            // Create new modal div (must be hidden to prevent flash of content)
            var $modalDiv = $('<div id="postgrad-modal" style="display:none !important; visibility:hidden; position:absolute; top:-9999px;" title="Μεταπτυχιακοί Τίτλοι"></div>');
            $("body").append($modalDiv);
            
            // Show loading state
            $modalDiv.html('<div style="padding: 20px; text-align: center;"><p>Φόρτωση δεδομένων...</p></div>');
            
            // Initialize dialog
            $modalDiv.dialog({
                modal: true,
                width: 80%,
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
                    // Ensure dialog is properly positioned and visible
                    $(this).css('display', 'block');
                },
                open: function(event, ui) {
                    // Ensure dialog is visible and positioned correctly
                    var $dialog = $(this);
                    var $dialogParent = $dialog.parent();
                    
                    // Ensure dialog wrapper is properly positioned
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
                    $dialogContent.load("postgrad.php?afm=" + afmValue, function(response, status, xhr) {
                        if (status == "error") {
                            $dialogContent.html('<div style="padding: 20px; text-align: center; color: red;"><p>Σφάλμα φόρτωσης δεδομένων</p></dialog>');
                        } else {
                            // Ensure content is within dialog and not elsewhere
                            $dialogContent.css({
                                'display': 'block',
                                'visibility': 'visible',
                                'position': 'relative',
                                'top': 'auto',
                                'left': 'auto'
                            });
                            
                            // Make sure dialog is still visible and positioned
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
                                            // Silent fail
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
/////////////////////////////////
// ************ EDIT ************
/////////////////////////////////
if ($_GET['op']=="edit") {
    if ($usrlvl == 3){
        echo "Δεν επιτρέπεται η πρόσβαση...";
        echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        die();
    }
    echo "<form id='updatefrm' name='update' action='update.php' method='POST'>";
    echo "<table class=\"imagetable\" border='1'>";
    
    // ========== ΠΡΟΣΩΠΙΚΑ (Personal Information) ==========
    echo "<tr><td colspan=2>Προσωπικα</td></tr>";
    echo "<tr><td>Επώνυμο</td><td><input type='text' name='surname' value=$surname /></td></tr>";
    echo "<tr><td>Όνομα</td><td><input type='text' name='name' value=$name /></td></tr>";
    echo "<tr><td>Πατρώνυμο</td><td><input type='text' name='patrwnymo' value=$patrwnymo /></td></tr>";
    echo "<tr><td>Μητρώνυμο</td><td><input type='text' name='mhtrwnymo' value=$mhtrwnymo /></td></tr>";
    echo "<tr><td>Α.Φ.Μ.</td><td><input type='text' name='afm' value=$afm /></td></tr>";
    echo "<tr><td>email</td><td><input type='text' name='email' value='$email' /></td></tr>";
    echo "<tr><td>email (ΠΣΔ)</td><td><input type='text' name='email_psd' value='$email_psd' /></td></tr>";
    echo "<tr><td>Τηλέφωνο</td><td><input size='30' type='text' name='tel' value='$tel' /></td></tr>";
    echo "<tr><td>Διεύθυνση</td><td><input size='50' type='text' name='address' value='$address' /></td></tr>";
    echo "<tr><td>Α.Δ.Τ.</td><td><input type='text' name='idnum' value='$idnum' /></td></tr>";
    echo "<tr><td>Α.Μ.K.A.</td><td><input type='text' name='amka' value='$amka' /></td></tr>";
    
    // ========== ΥΠΗΡΕΣΙΑΚΑ (Service Information) ==========
    echo "<tr><td colspan=2>Υπηρεσιακα</td></tr>";
    echo "<tr><td>Α.Μ.</td><td><input type='text' name='am' value=$am /></td></tr>";
    echo "<tr><td>Κλάδος</td><td>";
    kladosCombo($klados_id, $mysqlconnection);
    echo "</td></tr>";
    echo "<tr><td>";
    show_tooltip('Κατάσταση','Επιλέξτε ένα από: Εργάζεται, Λύση Σχέσης-Παραίτηση, Άδεια, Διαθεσιμότητα');
    echo "</td><td>";
    katastCmb($kat);
    echo "</td></tr>";
    echo "<tr><td>Βαθμός</td><td>";
    vathmosCmb1($vathm, $mysqlconnection);
    echo "</td><tr>";
    
    // monimopoihsh - aksiologhsh
    echo "<tr><td>Μονιμοποίηση/<br>Απόφαση μονιμοποίησης</td>";
    echo $monimopoihsh ? 
        "<td><input type=\"checkbox\" name='monimopoihsh' checked >Μονιμοποίηση" :
        "<td><input type=\"checkbox\" name='monimopoihsh' >Μονιμοποίηση";
    echo "<br><input type=\"input\" name='monimopoihsh_apof' value=$monimopoihsh_apof></td>"; 
    echo "</tr>";

    echo "<tr><td>Αξιολόγηση/<br>Ημ/νία τελ.αξιολόγησης</td>";
    echo $aksiologhsh ? 
        "<td><input type=\"checkbox\" name='aksiologhsh' checked >Αξιολογήθηκε" :
        "<td><input type=\"checkbox\" name='aksiologhsh' >Αξιολογήθηκε";
    echo "<br>";
    modern_datepicker('aksiologhsh_date', $aksiologhsh_date, array(
        'minDate' => '2020-01-01',
        'disabledDays' => array('sun', 'sat'),
        'maxDate' => date('Y-m-d')
    ));
    echo "</td>";
    echo "</tr>";

    echo "<tr><td>Μ.Κ.</td><td><input type='text' name='mk' value=$mk /></td></tr>";
    echo "<tr><td>Ημ/νία M.K.</td><td>";
    modern_datepicker("hm_mk", $hm_mk, array(
        'minDate' => '1980-01-01',
        'maxDate' => date('Y-m-d'),
        'yearRange' => '1980:' . date('Y')
    ));
    echo "</td></tr>";        
                
    echo "<tr><td>ΦΕΚ Διορισμού</td><td><input type='text' name='fek_dior' value=$fek_dior /></td></tr>";
        
    echo "<tr><td>Ημ/νία Διορισμού</td><td>";
    modern_datepicker("hm_dior", $hm_dior, array(
        'minDate' => '1980-01-01',
        'maxDate' => date('Y-m-d'),
        'disabledDays' => array('sun', 'sat'),
        'yearRange' => '1980:' . date('Y')
    ));
    echo "</td></tr>";        
        
    echo "<tr><td>Ημ/νία ανάληψης</td><td>";
    modern_datepicker("hm_anal", $hm_anal, array(
        'minDate' => '1980-01-01',
        'maxDate' => date('Y-m-d'),
        'disabledDays' => array('sun', 'sat'),
        'yearRange' => '1980:' . date('Y')
    ));
    echo "</td></tr>";        
                
    echo "<tr><td>Μεταπτυχιακό/Διδακτορικό</td><td>";
    metdidCombo($met_did);
    echo "</td></tr>";
    
    echo "<tr><td>Ώρες Υποχρ.Ωρ.</td><td><input type='text' name='wres' value=$wres /></td></tr>";

    echo "<tr><td>";
    show_tooltip("Σχολείο Οργανικής","Επιλέξτε σχολείο αφού εισάγετε μερικούς χαρακτήρες (αυτόματη συμπλήρωση).");
        echo "<a href=\"\" onclick=\"window.open('../help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"../images/help.gif\"/></a></td>";
        echo "<td><input type=\"text\" name=\"org\" id=\"org\" value='$sx_organikhs' size='40' />";

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
    echo "</td></tr>";

    echo "<tr><td>Οργανική σε τμήμα ένταξης</td><td>";
    echo $org_ent ? "<input type='checkbox' name='org_ent' checked>" : "<input type='checkbox' name='org_ent'>";
    echo "</td></tr>";
    echo "<tr>".thesiselectcmb($thesi)."</tr>";
    echo "<tr>".ent_ty_selectcmb($entty)."</tr>";
    
    // ========== ΧΡΟΝΟΙ ΥΠΗΡΕΣΙΑΣ (Service Times) ==========
    echo "<tr><td colspan=2>Χρονοι υπηρεσιας</td></tr>";
    
    $ymd=days2ymd($proyp);
    echo "<tr><td>Συνολική δημόσια προϋπηρεσία</td><td>Έτη&nbsp;<input type='text' name='pyears' size=1 value=$ymd[0] />Μήνες&nbsp;<input type='text' name='pmonths' size=1 value=$ymd[1] />Ημέρες&nbsp;<input type='text' name='pdays' size=1 value=$ymd[2] />&nbsp;($proyp Ημέρες)</td></tr>";
    $ymdnot=days2ymd($proyp_not);
    echo "<tr><td>Προϋπηρεσία που δε λαμβάνεται<br> υπ'όψιν για μείωση ωραρίου</td><td>Έτη&nbsp;<input type='text' name='peyears' size=1 value=$ymdnot[0] />Μήνες&nbsp;<input type='text' name='pemonths' size=1 value=$ymdnot[1] />Ημέρες&nbsp;<input type='text' name='pedays' size=1 value=$ymdnot[2] /></td></tr>";
    $ymdwrario=days2ymd($proyp_wrario);
    echo "<tr><td>Προϋπηρεσία που λαμβάνεται<br> υπ'όψιν για μείωση ωραρίου (όχι στη συνολική)</td><td>Έτη&nbsp;<input type='text' name='pe1years' size=1 value=$ymdwrario[0] />Μήνες&nbsp;<input type='text' name='pe1months' size=1 value=$ymdwrario[1] />Ημέρες&nbsp;<input type='text' name='pe1days' size=1 value=$ymdwrario[2] /></td></tr>";
                
    // aney
        echo "<tr><td>Σε άδ.άνευ αποδοχών:</td><td>";
    if ($aney) {
        echo "<input type='checkbox' name='aney' checked>";
    } else {
            echo "<input type='checkbox' name='aney'>";
    }
        echo "</tr>";
        echo "<tr><td>Τρέχουσα άδεια<br>άνευ αποδοχών: (Από / Έως)</td><td>";
        modern_datepicker("aney_apo", $aney_apo, array(
            'minDate' => '1980-01-01',
            'maxDate' => date('Y-m-d')
        ));
        echo " / ";
        modern_datepicker("aney_ews", $aney_ews, array(
            'minDate' => '1980-01-01'
        ));
        echo "</td></tr>";
        $aney_ymd = days2ymd($aney_xr);
        echo "<tr><td>Χρόνος παλαιών αδειών<br>άνευ αποδοχών (<small>χωρίς την παραπάνω</small>):</td><td><input type='text' name='aney_y' size='3' value=$aney_ymd[0]> έτη&nbsp;";
        echo "<input type='text' name='aney_m' size='3' value=$aney_ymd[1]> μήνες&nbsp; <input type='text' name='aney_d' size='3' value=$aney_ymd[2]> ημέρες</td></tr>";
        
        // idiwtiko ergo 07-11-2014
    //     echo "<tr><td colspan=2>Ιδιωτικα έργα</td></tr>";
    //     echo "<tr><td>Ιδ.έργο σε δημ.φορέα</td><td>";
    // if ($idiwtiko) {
    //     echo "<input type='checkbox' name='idiwtiko' checked>";
    // } else {
    //         echo "<input type='checkbox' name='idiwtiko'>";
    // }
    //     echo "<tr><td>Ημ/νία έναρξης/λήξης Ιδ.Έργου σε δημ.φορέα</td><td>";
    //     modern_datepicker("idiwtiko_enarxi", $idiwtiko_enarxi, array(
    //         'minDate' => '1980-01-01',
    //         'maxDate' => '2050-01-01'
    //     ));
    //     echo " / ";
    //     modern_datepicker("idiwtiko_liksi", $idiwtiko_liksi, array(
    //         'minDate' => '1980-01-01',
    //         'maxDate' => '2050-01-01'
    //     ));
    //     // idiwtiko sympl
    //     echo "<tr><td>Ιδ.έργο σε ιδιωτ.φορέα</td><td>";
    // if ($idiwtiko_id) {
    //     echo "<input type='checkbox' name='idiwtiko_id' checked>";
    // } else {
    //         echo "<input type='checkbox' name='idiwtiko_id'>";
    // }
    //     echo "<tr><td>Ημ/νία έναρξης/λήξης Ιδ.Έργου σε ιδιωτ.φορέα</td><td>";
    //     modern_datepicker("idiwtiko_id_enarxi", $idiwtiko_id_enarxi, array(
    //         'minDate' => '1980-01-01',
    //         'maxDate' => '2050-01-01'
    //     ));
    //     echo " / ";
    //     modern_datepicker("idiwtiko_id_liksi", $idiwtiko_id_liksi, array(
    //         'minDate' => '1980-01-01',
    //         'maxDate' => '2050-01-01'
    //     ));
        // idiwtiko end
        // katoikon
    echo "<tr><td colspan=2>Κατ' οίκον διδασκαλία</td></tr>";
    echo "<tr><td>Κατ' οίκον διδασκαλία</td><td>";
    if ($katoikon) {
        echo "<input type='checkbox' name='katoikon' checked>";
    } else {
        echo "<input type='checkbox' name='katoikon'>";
    }
    echo "<tr><td>Έναρξη/λήξη κατ'οίκον διδασκαλίας</td><td>";
    modern_datepicker("katoikon_apo", $katoikon_apo, array(
        'minDate' => '1980-01-01',
        'maxDate' => '2050-01-01'
    ));
    echo " / ";
    modern_datepicker("katoikon_ews", $katoikon_ews, array(
        'minDate' => '1980-01-01',
        'maxDate' => '2050-01-01'
    ));
    echo "<tr><td>Σχόλια κατ'οίκον διδασκαλίας</td><td><input size=50 type='text' name='katoikon_comm' value=$katoikon_comm /></td></tr>";
    // katoikon_end
    echo "<tr><td colspan=2>Σχόλια</td></tr>";
    echo "<tr><td>Σχόλια</td><td><textarea rows=10 cols=95 name='comments' >$comments</textarea></td></tr>";
        
    //new 15-02-2012: implemented with jquery.autocomplete
    echo "<div id=\"content\">";
    // echo "<form autocomplete=\"off\">";
    

    
    echo "	</table>";
    echo "	<input type='hidden' name = 'id' value='$id'>";
    echo "	<input type='submit' value='Αποθήκευση'>";
                echo "	<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='employee.php?id=$id&op=view'\">";
    echo "	</form>";
    echo "    </center>";
    echo "</body>";
    ?>
        <div id='results'>   </div>
    <?php
    echo "</html>";
}
/////////////////////////////////
// ************ VIEW ************
/////////////////////////////////
elseif ($_GET['op']=="view") {    
       echo "<br>";
    echo "<table class=\"imagetable\" border='1'>";    
    echo "<tr>";
        
       echo "<th colspan=4 align=center>Καρτέλα μόνιμου εκπαιδευτικού</th>";
    echo "</tr>";
    
    // ========== ΠΡΟΣΩΠΙΚΑ (Personal Information) ==========
    echo "<tr><td colspan=4 class='separator'>Προσωπικα</td></tr>";
    echo "<tr><td>Επώνυμο</td><td>$surname</td><td>Όνομα</td><td>$name</td></tr>";
    echo "<tr><td>Πατρώνυμο</td><td>$patrwnymo</td><td>Μητρώνυμο</td><td>$mhtrwnymo</td></tr>";
    
    // Email fields
    echo "<tr><td>e-mail</td><td><a href=\"mailto:$email\">$email</a></td><td>e-mail (ΠΣΔ)</td><td><a href=\"mailto:$email_psd\">$email_psd</a></td></tr>";
    
    // AFM (only for user level < 3)
    if ($usrlvl < 3){
        echo "<tr><td>Α.Φ.Μ.</td><td>$afm</td><td></td><td></td></tr>";
    }
    
    // Additional personal data in expandable section
    if ($amka || $tel || $address || $idnum || $idiwtiko || $idiwtiko_id || $katoikon) {
        echo "<tr><td><a href=\"#\" class=\"show_hide\"><small>Εμφάνιση/Απόκρυψη<br>περισσοτέρων στοιχείων</small></a></td>";
        echo "<td colspan=3><div class=\"slidingDiv\">";
        echo "Τηλέφωνο: ".$tel."<br>";
        // only for user_level < 3
        if ($usrlvl < 3){
            echo "Διεύθυνση: ".$address."<br>";
            echo "ΑΔΤ: ".$idnum."<br>";
            echo "AMKA: ".$amka."<br>";
            if ($katoikon) {
                echo "Κατ'οίκον διδασκαλία<input type='checkbox' name='katoikon' checked disabled>";
            } else {
                echo "Κατ'οίκον διδασκαλία<input type='checkbox' name='katoikon' disabled>";
            }
            $sdate = strtotime($katoikon_apo)>0 ? date('d-m-Y', strtotime($katoikon_apo)) : '';
            $ldate = strtotime($katoikon_ews)>0 ? date('d-m-Y', strtotime($katoikon_ews)) : '';
            echo ($katoikon > 0 ? "&nbsp;&nbsp;Έναρξη:&nbsp;$sdate&nbsp;-&nbsp;Λήξη:&nbsp;$ldate<br>Σχόλια κατ'οίκον:&nbsp;".stripslashes($katoikon_comm) : "");
            echo "<br><br>";
            idiwtika_table("Μόνιμος", $id, $mysqlconnection);
        }
        
        echo "</div>";
        echo "</td></tr>";
    }
    else
    {
        echo "<tr><td><a href=\"#\" class=\"show_hide\"><small>Εμφάνιση/Απόκρυψη<br>περισσοτέρων στοιχείων</small></a></td>";
        echo "<td colspan=3><div class=\"slidingDiv\">";
        echo "Δε βρέθηκαν περισσότερα στοιχεία για τον/-ην υπάλληλο.<br>";
        echo "O/H υπάλληλος δε μισθοδοτείται από τη Δ/νση Ηρακλείου<br>";
        echo "ή δεν έχουν καταχωρηθεί στοιχεία.";
        echo "</div>";
        echo "</td></tr>";   
    }
    
    // ========== ΥΠΗΡΕΣΙΑΚΑ (Service Information) ==========
    echo "<tr><td colspan=4 class='separator'>Υπηρεσιακα</td></tr>";
    echo "<tr><td>Α.Μ.</td><td>$am</td><td>Κλάδος</td><td>".getKlados($klados_id, $mysqlconnection, true)."</td></tr>";
    echo "<tr><td>Κατάσταση</td><td>$katast</td><td>Βαθμός</td><td>$vathm</td></tr>";
    
    $hm_mk = date('d-m-Y', strtotime($hm_mk));
    if ($hm_mk > "01-01-1970") {
        echo "<tr><td>Μ.Κ.</td><td>$mk &nbsp;<small>(από $hm_mk)</small></td><td></td><td></td></tr>";
    } else {
        echo "<tr><td>Μ.Κ.</td><td>$mk</td><td></td><td></td></tr>";
    }

    // monimopoihsh - aksiologhsh
    echo "<tr><td>Μονιμοποίηση /<br>Απόφαση μονιμοποίησης</td>";
    echo $monimopoihsh ? 
        "<td>ΝΑΙ<br>$monimopoihsh_apof</td>" :
        "<td>ΟΧΙ</td>";

    echo "<td>Αξιολόγηση /<br>Ημ/νία τελ.αξιολόγησης</td>";
    if ($aksiologhsh) {
        echo "<td>ΝΑΙ<br>";
        echo $aksiologhsh_date>'2000-11-30'? date("d-m-Y", strtotime($aksiologhsh_date))."</td>" : "</td>";
    } else {
        echo "<td>ΟΧΙ</td>"; 
    }
    echo "</tr>";

    echo "<tr><td>ΦΕΚ Διορισμού</td><td>$fek_dior</td><td>Ημ/νία Διορισμού</td><td>".date('d-m-Y', strtotime($hm_dior))."</td></tr>";
    
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
        $met="Ενιαίος και αδιάσπαστος τίτλος σπουδών<br> μεταπτυχιακού επιπέδου (Integrated master)";
        break;
    }

    echo "<tr><td>Ημ/νία Ανάληψης</td><td>".date('d-m-Y', strtotime($hm_anal))."</td>";
    echo "<td>Μεταπτυχιακό/Διδακτορικό <small><a href='#' id='postgrad-link' data-afm='$afm'>(Λεπτομέρειες)</a></small></td><td>$met</td></tr>";
    
    echo "<tr><td>Ώρες υποχρ. ωραρίου</td><td>$wres</td><td></td><td></td></tr>";
    echo "<tr><td>Σχ.Οργανικής</td><td>&nbsp;<a href=\"../school/school_status.php?org=$sx_organ_id\">$sx_organikhs</a>";
    $count = count($yphr_arr);
    $sxoleia = '';
    $sxol_str = '';
    $counthrs = 0;
    for ($i=0; $i<$count; $i++)
    {
        $sxoleia .=  "<a href=\"../school/school_status.php?org=$yphr_id_arr[$i]\">$yphr_arr[$i]</a> ($hours_arr[$i] ώρες)<br>";
        $sxol_str .=  "$yphr_arr[$i] ($hours_arr[$i] ώρες) ";
        $counthrs += $hours_arr[$i];
    }
    if ($count>1) {
        if ($counthrs > $wres) {
            echo "<tr class='error-highlight'><td>Σχ.Υπηρέτησης</td><td colspan=3>$sxoleia<br><strong>$counthrs ώρες > $wres υποχρ.ωραρίου: ΣΦΑΛΜΑ! Παρακαλώ διορθώστε!!!</strong></td></tr>";
        }
        else {
            echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3>$sxoleia<br><small>($counthrs ώρες σε $count Σχολεία)</small></td></tr>";
        }
    }
    else {
        echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3>$sxoleia</td></tr>";
    }
    
    $th = thesicmb($thesi);
    echo "<tr><td>Θέση</td><td colspan=3>$th</td></tr>";

    
    echo $org_ent ? '&nbsp;(Οργανική σε Τ.Ε.)' : '';
    echo "</td><td></td><td></td></tr>";

    
    echo "<tr><td><a id='archive-toggle' href='#'>Ιστορικό αλλαγών υπηρετήσεων</a></td><td colspan=3>";
    display_yphrethsh_archive($mysqlconnection, $id, $sxol_etos, true);
    echo "</td></tr>";
    echo "<tr><td>Υπηρέτηση σε Τμήμα Ένταξης<br> / Τάξη υποδοχής</td><td colspan=3>".ent_ty_cmb($entty)."</td></tr>";
    
    // Calculate anatr for service times section
    $hm_dior_org = $hm_dior;
    $dt1 = strtotime($hm_anal);
    $dt2 = strtotime($hm_dior);
    $diafora = abs($dt1 - $dt2);
    $diafora = $diafora/86400;
    if ($diafora > 30) {
        $d1 = strtotime($hm_anal);
        $hm_dior = $hm_anal;
    }
    else {
        $d1 = strtotime($hm_dior);
    }
    
    $days_aney = 0;
    if ($aney && strtotime($aney_ews) > date("Y-m-d")) {
        $dtd = strtotime(date('Y-m-d'));
        $tod = date('d', $dtd) + date('m', $dtd)*30 + date('Y', $dtd)*360;
        $apo = date('d', strtotime($aney_apo)) + date('m', strtotime($aney_apo))*30 + date('Y', strtotime($aney_apo))*360;
        $days_aney = $tod - $apo;
        if ($days_aney > 30) {
          $days_aney -= 30;
        } else { 
          $days_aney = 0;
        }
    }
    $aney = $aney_xr + $days_aney;
    $anatr = (date('d', $d1) + date('m', $d1)*30 + date('Y', $d1)*360) - $proyp + $aney;
    $ymd = days2date($anatr);
    
    // ========== ΧΡΟΝΟΙ ΥΠΗΡΕΣΙΑΣ (Service Times) ==========
    echo "<tr><td colspan=4 class='separator'>Χρονοι υπηρεσιας</td></tr>";
    
    $ymd_proyp = days2ymd($proyp);
    echo "<tr><td>Συνολική δημόσια προϋπηρεσία</td><td>Έτη: $ymd_proyp[0] &nbsp; Μήνες: $ymd_proyp[1] &nbsp; Ημέρες: $ymd_proyp[2]</td>";
    echo "<td>Ανατρέχει</td><td>Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2]</td></tr>";
    
    $ymdnot = days2ymd($proyp_not);
    $ymdwrario = days2ymd($proyp_wrario);
    echo "<tr>";
    echo "<td>Προϋπηρεσία που δε λαμβάνεται υπ'όψιν για μείωση ωραρίου</td><td>Έτη: $ymdnot[0] &nbsp; Μήνες: $ymdnot[1] &nbsp; Ημέρες: $ymdnot[2]</td>";
    echo "<td>Προϋπηρεσία που λαμβάνεται υπ'όψιν για μείωση ωραρίου<br>(όχι στη συνολική)</td><td>Έτη: $ymdwrario[0] &nbsp; Μήνες: $ymdwrario[1] &nbsp; Ημέρες: $ymdwrario[2]</td>";
    echo "</tr>";
    
    // aney
    echo "<tr><td>Σε άδ.άνευ αποδοχών:</td><td>";
    if ($aney) {
        echo "<input type='checkbox' name='aney' checked disabled>";
    } else {
        echo "<input type='checkbox' name='aney' disabled>";
    }
    if ($aney && $aney_apo && $aney_ews) {
        echo "<small>(Από ".date('d-m-Y', strtotime($aney_apo))." έως ".date('d-m-Y', strtotime($aney_ews)).")</small>";
    }
    $aney_ymd = days2ymd($aney_xr);
    echo "</td><td>Χρόνος σε άδ.άνευ αποδοχών:</td><td>$aney_ymd[0] έτη, $aney_ymd[1] μήνες, $aney_ymd[2] ημέρες</td></tr>";
    echo "<tr><td>Σχόλια<br><br></td><td colspan='3'>".nl2br(stripslashes($comments))."</td></tr>"; 
    
    // history
    $hist_qry = "SELECT * FROM yphrethsh WHERE emp_id=$id AND sxol_etos<$sxol_etos";
    $hist_res = mysqli_query($mysqlconnection, $hist_qry);
    if (mysqli_num_rows($hist_res)) {
        echo "<tr><td><a href=\"#\" class=\"show_hide2\"><small>Εμφάνιση/Απόκρυψη<br>ιστορικού</small></a></td>";
        echo "<td colspan=3><div class=\"slidingDiv2\">";
        while ($row = mysqli_fetch_array($hist_res, MYSQLI_ASSOC)) {
            echo "Σχολ.έτος: ".$row['sxol_etos']." - Σχ.Υπηρέτησης : ".getSchool($row['yphrethsh'], $mysqlconnection)." (".$row['hours']." ώρες) - <small>Οργανική: ".getSchool($row['organikh'], $mysqlconnection)."</small><br>";
        }
              
        echo "</div>";
        echo "</td></tr>";  
    }
    // 
    // show changes for admin
    if ($updated > 0 && $usrlvl==0) {
        $update_qry = "SELECT l.*, u.username from employee_log l JOIN logon u ON u.userid = l.userid WHERE emp_id=$id ORDER BY timestamp DESC";
        $result_upd = mysqli_query($mysqlconnection, $update_qry);
                      
        echo "<tr><td><a href=\"#\" class=\"show_hide3\"><small>Εμφάνιση/Απόκρυψη<br>μεταβολών</small></a></td>";
        echo "<td colspan=3><div class=\"slidingDiv3\">";
        echo "<ul>";
        while ($row = mysqli_fetch_array($result_upd, MYSQLI_ASSOC)) {
              echo "<li><b>".date("d-m-Y H:i", strtotime($row['timestamp']))."</b>&nbsp; usr: ".$row['username']." - IP: ".$row['ip']." ".$row['query']."</li>";
        }
        echo "</ul>";
        echo "</div>";
        echo "</td></tr>";
    }
    echo "<tr><td colspan=4 align='right'><small>Τελευταία ενημέρωση: ".date("d-m-Y H:i", strtotime($updated))."</small></td></tr>";
    
    // Service time form
    echo "<form id='yphrfrm' name='yphrfrm' action='' method='POST'>";
    echo "<tr><td>Χρόνοι Υπηρεσίας</td><td>";
    modern_datepicker("yphr", date('Y-m-d'), array(
        'minDate' => $hm_dior,
        'maxDate' => '2050-01-01'
    ));
    echo "<br>";
    echo "<input type='hidden' name='id' value=$id>";
    echo "<input type='hidden' name='proyp_not' value=$proyp_not>";
    echo "<input type='hidden' name='proyp_wrario' value=$proyp_wrario>";
    echo "<INPUT TYPE='submit' VALUE='Υπολογισμός'>";
    echo "<br>";
    echo "</form>";
    ?>
        <div id='yphr_res'></div>
    <?php
        
    echo "</td>";
    echo "<td colspan=2 align='center'>";
    //Form gia Bebaiwsh
    echo "<form id='wordfrm' name='wordfrm' action='' method='POST'>";
    echo "<input type='hidden' name='surname' value=$surname>";
    echo "<input type='hidden' name='name' value=$name>";
    echo "<input type='hidden' name='patrwnymo' value=$patrwnymo>";
    echo "<input type='hidden' name='klados' value=$klados>";
    echo "<input type='hidden' name='am' value=$am>";
    echo "<input type='hidden' name='vathm' value=$vathm>";
    echo "<input type='hidden' name='id' value=$id>";
    echo "<input type='hidden' name='sx_organikhs' value='$sx_organikhs'>";
    echo "<input type='hidden' name='fek_dior' value=$fek_dior>";
    echo "<input type='hidden' name='hm_dior_org' value=$hm_dior_org>";
    echo "<input type='hidden' name='hm_anal' value=$hm_anal>";
    $ymd = ypol_yphr(date("Y/m/d"), $anatr);
    echo "<input type='hidden' name='ymd' value='$ymd'>";
    //echo "<input type='hidden' name='afm' value=$afm>";
    if ($usrlvl < 3) {
        echo "<INPUT TYPE='submit' name='yphr' VALUE='Βεβαίωση Υπηρ.Κατάστασης'>"; 
    }
    //echo "&nbsp;&nbsp;<INPUT TYPE='submit' name='anadr' VALUE='Βεβαίωση διεκδίκησης αναδρομικών'>"; 
    echo "</form>";
    //Form gia metakinhsh
    if ($_SESSION['user'] === 'pispe'){
      echo "<form id='metakfrm' name='metakfrm' action='metakinhsh.php' method='POST'>";
      echo "<input type='hidden' name='type' value='mon'>";
      echo "<input type='hidden' name='surname' value=$surname>";
      echo "<input type='hidden' name='name' value=$name>";
      echo "<input type='hidden' name='patrwnymo' value=$patrwnymo>";
      echo "<input type='hidden' name='klados' value=$klados>";
      echo "<input type='hidden' name='yphrethsh' value='$sxol_str'>";
      echo "<input type='hidden' name='am' value=$am>";
      echo "<input type='hidden' name='id' value=$id>";
      echo "<INPUT TYPE='submit' VALUE='Μετακίνηση'>";
      echo "</form>";
    }
    ?>
  <div id="word"></div>
    <?php
    echo "</td></tr>";
                                        
    echo "	</table>";
        
    // Find prev - next row id
    $qprev = "SELECT id FROM employee WHERE id < $id ORDER BY id DESC LIMIT 1";
    $res1 = mysqli_query($mysqlconnection, $qprev);
    if (mysqli_num_rows($res1)) {
        $previd = mysqli_result($res1, 0, "id");
    }
    $qnext = "SELECT id FROM employee WHERE id > $id ORDER BY id ASC LIMIT 1";
    $res1 = mysqli_query($mysqlconnection, $qnext);
    if (mysqli_num_rows($res1)) {
        $nextid = mysqli_result($res1, 0, "id");
    }
        
    //echo (existID($previd,$mysqlconnection));
    if ($previd) {
        echo "	<INPUT TYPE='button' VALUE='<<' onClick=\"parent.location='employee.php?id=$previd&op=view'\">";
    }
    if ($usrlvl < 3){
        echo "  <INPUT TYPE='button' id='adeia' VALUE='Άδειες'>";
    }
    if ($usrlvl < 3) {
        echo "	<INPUT TYPE='button' VALUE='Επεξεργασία' onClick=\"parent.location='employee.php?id=$id&op=edit'\">";
    }
     echo "  <input type='button' value='Εκτύπωση' onclick='javascript:window.print()' />";
     echo "	<INPUT TYPE='button' VALUE='Επιστροφή στο προηγούμενο' onClick='history.go(-1);return true;'>";
      

    if ($nextid) {
        echo "	<INPUT TYPE='button' VALUE='>>' onClick=\"parent.location='employee.php?id=$nextid&op=view'\">";
    }
     // if idiwtikoi
    if ($thesi == 5) {
        echo "<br><br><INPUT TYPE='button' VALUE='Σελίδα ιδιωτικών' onClick=\"parent.location='idiwtikoi.php'\">";
    }
     echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
    
    
    echo "    </center>";
    echo "</body>";
    echo "</html>";    
    // echo "</div>";
}
///////////////////////////////////
// ************ DELETE ************
///////////////////////////////////
if ($_GET['op']=="delete") {
    if ($usrlvl == 3){
        echo "Δεν επιτρέπεται η πρόσβαση...";
        echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        die();
    }
    $ip = $_SERVER['REMOTE_ADDR'];
    // Copies the to-be-deleted row to employee_deleted table for backup purposes.Also inserts a row on employee_del_log...
    $query1 = "INSERT INTO employee_deleted SELECT e.* FROM employee e WHERE id =".$_GET['id'];
    $result1 = mysqli_query($mysqlconnection, $query1);
    $query1 = "INSERT INTO employee_log (emp_id, ip, userid, action) VALUES (".$_GET['id'].",$ip,".$_SESSION['userid'].", 2)";
    $result1 = mysqli_query($mysqlconnection, $query1);
    $query = "DELETE from employee where id=".$_GET['id'];
    $result = mysqli_query($mysqlconnection, $query);
    // Copies the deleted row to employee)deleted
        
    if ($result) {
      echo "Η εγγραφή με κωδικό $id διαγράφηκε με επιτυχία.";
    } else {
      echo "Η διαγραφή απέτυχε...";
    }
    echo "	<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='../index.php'\">";
    echo "  <meta http-equiv=\"refresh\" content=\"2; URL=../index.php\">";
}
/////////////////////////////////
// ************ ADD  ************
/////////////////////////////////
if ($_GET['op']=="add") {
    if ($usrlvl == 3){
        echo "Δεν επιτρέπεται η πρόσβαση...";
        echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        die();
    }
    echo "<h3>Προσοχή: Παρακαλώ δώστε έγκυρα στοιχεία από τον προσωπικό φάκελο του εργαζομένου</h3><br>";
    echo "<form id='updatefrm' action='update.php' method='POST'>";
    echo "<table class=\"imagetable\" border='1'>";
                
               echo "<thead></thead><tbody>";
        
       echo "<tr><td>Επώνυμο</td><td><input type='text' name='surname' /></td></tr>";
       echo "<tr><td>Όνομα</td><td><input type='text' name='name' /></td></tr>";
    echo "<tr><td>Πατρώνυμο</td><td><input type='text' name='patrwnymo' /></td></tr>";
    echo "<tr><td>Μητρώνυμο</td><td><input type='text' name='mhtrwnymo' /></td></tr>";
    echo "<tr><td>Α.Φ.Μ.</td><td><input type='text' name='afm' /></td></tr>";
    echo "<tr><td>Α.Μ.</td><td><input type='text' name='am' /></td></tr>";
    echo "<tr><td>Κλάδος</td><td>";
    kladosCmb($mysqlconnection);
    echo "</td></tr>";
    echo "<tr><td>Βαθμός</td><td><input type='text' name='vathm' /></td></tr>";
    echo "<tr><td>Μ.Κ.</td><td><input type='text' name='mk' /></td></tr>";
    echo "<tr><td>ΦΕΚ Διορισμού</td><td><input type='text' name='fek_dior' /></td></tr>";
                
    echo "<tr><td>Ημ/νία Διορισμού</td><td>";
    modern_datepicker("hm_dior", date('Y-m-d'), array(
        'minDate' => '1980-01-01',
        'maxDate' => date('Y-m-d'),
        'disabledDays' => array('sun', 'sat'),
        'yearRange' => '1980:' . date('Y')
    ));
    echo "</td></tr>";        
        
    echo "<tr><td>Ημ/νία ανάληψης</td><td>";
    modern_datepicker("hm_anal", date('Y-m-d'), array(
        'minDate' => '1980-01-01',
        'maxDate' => date('Y-m-d'),
        'disabledDays' => array('sun', 'sat'),
        'yearRange' => '1980:' . date('Y')
    ));
    echo "</td></tr>";        
                
    echo "<tr><td>Μεταπτυχιακό/Διδακτορικό</td><td>";
    metdidCombo(0);
    echo "<tr><td>Συνολική δημόσια προϋπηρεσία</td><td>Έτη:&nbsp;<input type='text' name='pyears' size=1 />&nbsp;Μήνες:&nbsp;<input type='text' name='pmonths' size=1 />&nbsp;Ημέρες:&nbsp;<input type='text' name='pdays' size=1 /></td></tr>";
    echo "<tr><td>Προϋπηρεσία που δε λαμβάνεται<br> υπ'όψιν για μείωση ωραρίου</td><td>Έτη:&nbsp;<input type='text' name='peyears' size=1 />&nbsp;Μήνες:&nbsp;<input type='text' name='pemonths' size=1 />&nbsp;Ημέρες:&nbsp;<input type='text' name='pedays' size=1 /></td></tr>";
       echo "<tr><td>Σχόλια</td><td><textarea rows=4 cols=80 name='comments' ></textarea></td></tr>";
         
    echo "<div id=\"content\">";
    echo "<form autocomplete=\"off\">";
    echo "<tr><td>Σχολείο Οργανικής";
               echo "<a href=\"\" onclick=\"window.open('../help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"../images/help.gif\"/></a>";
               echo "</td><td><input type=\"text\" name=\"org\" id=\"org\" />";
    echo "<tr><td>Σχολείο Υπηρέτησης";
               echo "<a href=\"\" onclick=\"window.open('../help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"../images/help.gif\"/></a>";
               echo "</td><td><input type=\"text\" name=\"yphr[]\" class=\"yphrow\" id=\"yphrow\" />";
               echo "&nbsp;&nbsp;<input type=\"text\" name=\"hours[]\" size=1 />";
               echo "&nbsp;<input class=\"addRow\" type=\"button\" value=\"Προσθήκη\" />";
               echo "<input class=\"delRow\" type=\"button\" value=\"Αφαίρεση\" /><tr>";
               echo "<tr>".thesiselectcmb(0)."</tr>";
               echo "<tr>".ent_ty_selectcmb(0)."</tr>";
        
    echo "</div>";
    echo "</tbody>";
    echo "	</table>";
                
    //echo "	<input type='hidden' name = 'id' value='$id'>";
    // action = 1 gia prosthiki
    echo "  <input type='hidden' name = 'action' value='1'>";
               // status = 1 gia ergazetai
    echo "  <input type='hidden' name = 'status' value='1'>";
    echo "	<input type='submit' value='Αποθήκευση'>";
               echo "&nbsp;&nbsp;&nbsp;&nbsp;	<input type='submit' value='Αποθήκευση & εισαγωγή νέου' onClick=\"parent.location='employee.php?id=100&op=add'\">";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;	<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='../index.php'\">";
    echo "	</form>";
    ?>
<div id='results'></div>
    <?php
    echo "    </center>";
    echo "</body>";
    echo "</html>";
}
    mysqli_close($mysqlconnection);
?> 
