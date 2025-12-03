<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once "../include/functions.php";
  require_once"../include/functions_controls.php";
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  // Demand authorization                
  require "../tools/class.login.php";
  $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
}
?>
<html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Επεξεργασία σχολείου';
    require '../etc/head.php'; 
    ?>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <style type="text/css">
      /* School Edit Form Styling */
      .edit-section {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
        max-width: 1000px;
      }
      
      .edit-section:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
      }
      
      .edit-section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #ffffff;
        padding: 16px 24px;
        font-weight: 600;
        font-size: 1.1rem;
        margin: 0;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
      }
      
      .edit-section-content {
        padding: 24px;
      }
      
      .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
      }
      
      .form-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
      }
      
      .form-label {
        font-size: 0.875rem;
        color: #374151;
        font-weight: 500;
        margin-bottom: 4px;
      }
      
      .form-label small {
        color: #6b7280;
        font-weight: 400;
      }
      
      .form-input {
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.9375rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        font-family: inherit;
      }
      
      .form-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      }
      
      .form-input[type="text"] {
        width: 100%;
        box-sizing: border-box;
      }
      
      .form-input[size] {
        width: auto;
        min-width: 60px;
      }
      
      .form-textarea {
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.9375rem;
        font-family: inherit;
        resize: vertical;
        min-height: 100px;
        width: 100%;
        box-sizing: border-box;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
      }
      
      .form-textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      }
      
      .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 8px;
      }
      
      .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        transition: background 0.2s ease;
      }
      
      .checkbox-item:hover {
        background: #f3f4f6;
      }
      
      .checkbox-item input[type="checkbox"] {
        margin: 0;
        cursor: pointer;
        width: 18px;
        height: 18px;
      }
      
      .checkbox-item label {
        margin: 0;
        font-size: 0.9375rem;
        color: #374151;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
      }
      
      .organikes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
        margin-top: 12px;
      }
      
      .organikes-item {
        display: flex;
        align-items: center;
        gap: 8px;
      }
      
      .organikes-item label {
        font-size: 0.875rem;
        color: #374151;
        min-width: 100px;
        font-weight: 500;
      }
      
      .organikes-item input {
        padding: 6px 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        width: 60px;
        text-align: center;
      }
      
      .action-buttons {
        margin-top: 32px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        padding-top: 24px;
        border-top: 2px solid #e5e7eb;
      }
      
      .action-buttons input[type="submit"],
      .action-buttons input[type="button"] {
        padding: 12px 28px;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.9375rem;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
      }
      
      .action-buttons input[type="submit"] {
        background: #667eea;
        color: #ffffff;
      }
      
      .action-buttons input[type="submit"]:hover {
        background: #764ba2;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
      }
      
      .action-buttons input[type="button"] {
        background: #6b7280;
        color: #ffffff;
      }
      
      .action-buttons input[type="button"]:hover {
        background: #4b5563;
        transform: translateY(-1px);
      }
      
      .form-row {
        display: flex;
        gap: 12px;
        align-items: flex-end;
      }
      
      .form-row .form-item {
        flex: 1;
      }
      
      .info-hint {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #6b7280;
        font-size: 0.875rem;
        margin-left: 8px;
      }
      
      .info-hint img {
        width: 16px;
        height: 16px;
        opacity: 0.6;
      }
      
      .edit-section table.imagetable {
        border-collapse: collapse;
        margin-top: 12px;
      }
      
      .edit-section table.imagetable th {
        background: #f9fafb;
        padding: 12px;
        text-align: center;
        font-weight: 600;
        color: #374151;
        border: 1px solid #e5e7eb;
      }
      
      .edit-section table.imagetable td {
        padding: 10px;
        text-align: center;
        border: 1px solid #e5e7eb;
        background: #ffffff;
      }
      
      .edit-section table.imagetable input[type="text"] {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 6px 8px;
        text-align: center;
        transition: border-color 0.2s ease;
      }
      
      .edit-section table.imagetable input[type="text"]:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
      }
      
      .edit-section h4 {
        color: #374151;
        font-size: 1.125rem;
        font-weight: 600;
        margin-top: 24px;
        margin-bottom: 12px;
      }
      
      @media (max-width: 768px) {
        .form-grid {
          grid-template-columns: 1fr;
        }
        
        .edit-section-content {
          padding: 16px;
        }
        
        .organikes-grid {
          grid-template-columns: 1fr;
        }
      }
    </style>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <LINK href="../css/jquery-ui.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../js/datepicker-gr.js"></script>
    <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
    <script type="text/javascript" src="../js/jquery_notification_v.1.js"></script>
    <link href="../css/jquery_notification.css" type="text/css" rel="stylesheet"/>
    <link href="../css/select2.min.css" rel="stylesheet" />
    <script src="../js/select2.min.js"></script>
    <script type="text/javascript">    
        $().ready(function() {
            $("#org").autocomplete("../employee/get_school.php", {
                width: 260,
                matchContains: true,
                selectFirst: false
            });
        });
        function valueChanged() {
              if($('#vivliothiki').is(":checked"))   
                $("#workerdiv").show();
              else
                $("#workerdiv").hide();
        };
        $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
            $("#mytbl2").tablesorter({widgets: ['zebra']});
            $("#mytbl3").tablesorter({widgets: ['zebra']});
            $("#mytbl4").tablesorter({widgets: ['zebra']});
            $("#mytbl5").tablesorter({widgets: ['zebra']});
            $("#mytbl6").tablesorter({widgets: ['zebra']});
            $("#proinizoni").select2();
        });
        $(document).ready(function(){
          $('#updatefrm').on('submit', function(e){
            //Stop the form from submitting itself to the server.
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: 'school_update.php',
                data: $("#updatefrm").serialize(),
                success: function(data){
                  $('#results').html(data);
                }
            });
          });
        });
    </script>
  </head>
  <body>
    <?php require '../etc/menu.php'; ?>
    <center>
        <h2>Επεξεργασία σχολείου</h2>
        <?php     
        if ($_SESSION['userlevel'] == 3){
            die('Σφάλμα: Δεν επιτρέπεται η πρόσβαση...');
        }
      
        echo "<div id=\"content\">";
        echo "<form id='searchfrm' name='searchfrm' action='' method='POST' autocomplete='off'>";
        echo "<table class=\"imagetable stable\" border='1'>";
        echo "<td>Σχολείο</td><td></td><td><input type=\"text\" name=\"org\" id=\"org\" /></td></tr>";                
        echo "	</table>";
        echo "	<input type='submit' value='Αναζήτηση'>";
        //echo "  &nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value=\"Επαναφορά\" onClick=\"window.location.reload()\">";
        echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
        echo "	</form>";
        echo "</div>";
        
        if (isset($_POST['org']) || isset($_GET['org'])) {
            $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
            mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
            mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
                    
            if (isset($_POST['org'])) {
                    $str1 = $_POST['org'];
                    $sch = getSchoolID($str1, $mysqlconnection);
            }
            else
            {
                $sch = $_GET['org'];
                $str1 = getSchool($sch, $mysqlconnection);
            }
                    
            echo "<h1>$str1</h1>";
            //disp_school($sch, $mysqlconnection);
            $query = "SELECT * from school where id=$sch";
            $result = mysqli_query($mysqlconnection, $query);

            $titlos = mysqli_result($result, 0, "titlos");
            $address = mysqli_result($result, 0, "address");
            $tk = mysqli_result($result, 0, "tk");
            $tel = mysqli_result($result, 0, "tel");
            $tel2 = mysqli_result($result, 0, "tel2");
            $fax = mysqli_result($result, 0, "fax");
            $email = mysqli_result($result, 0, "email");
            $email2 = mysqli_result($result, 0, "email2");
            $type = mysqli_result($result, 0, "type");
            $type2 = mysqli_result($result, 0, "type2");
            $organikothta = mysqli_result($result, 0, "organikothta");
            $leitoyrg = get_leitoyrgikothta($sch, $mysqlconnection);
            $anenergo = mysqli_result($result, 0, "anenergo");
            $thiteia = mysqli_result($result, 0, "thiteia");
            $thiteia_apo = mysqli_result($result, 0, "thiteia_apo");
            $thiteia_ews = mysqli_result($result, 0, "thiteia_ews");
                    
            // if dimotiko
            if ($type == 1) {
                $students = mysqli_result($result, 0, "students");
                $classes = explode(",", $students);
                $frontistiriako = mysqli_result($result, 0, "frontistiriako");
                $ted = mysqli_result($result, 0, "ted");
                $oloimero_stud = mysqli_result($result, 0, "oloimero_stud");
                $tmimata = mysqli_result($result, 0, "tmimata");
                $tmimata_exp = explode(",", $tmimata);
                $oloimero_tea = mysqli_result($result, 0, "oloimero_tea");
                $ekp_ee = mysqli_result($result, 0, "ekp_ee");
                $ekp_ee_exp = explode(",", $ekp_ee);
                $vivliothiki = mysqli_result($result, 0, "vivliothiki");
                // fill array blanks with zeroes
                foreach($classes as &$val) {
                    if(empty($val)) { $val = 0; }
                }        
                $synolo = $classes[0]+$classes[1]+$classes[2]+$classes[3]+$classes[4]+$classes[5];
                foreach($tmimata_exp as &$val) {
                    if(empty($val)) { $val = 0; }
                }
                $synolo_tmim = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
            }
            // if nipiagwgeio
            else if ($type == 2) {
                $klasiko = mysqli_result($result, 0, "klasiko");
                $klasiko_exp = explode(",", $klasiko);
                $oloimero_nip = mysqli_result($result, 0, "oloimero_nip");
                $oloimero_nip_exp = explode(",", $oloimero_nip);
                $nip = mysqli_result($result, 0, "nip");
                $nip_exp = explode(",", $nip);
                        
                $klasiko_synolo = array_sum($klasiko_exp);
                $oloimero_synolo = array_sum($oloimero_nip_exp);
            }
            $oloimero = mysqli_result($result, 0, "oloimero");
            $entaksis = explode(",", mysqli_result($result, 0, "entaksis"));
            $ypodoxis = mysqli_result($result, 0, "ypodoxis");                    
            $comments = mysqli_result($result, 0, "comments");
            $pe0507 = explode('|', mysqli_result($result, 0, "pe0507"));
            // organikes - added 05-10-2012
            $organikes = unserialize(mysqli_result($result, 0, "organikes"));

            echo "<form id='updatefrm' name='update' action='school_update.php' method='POST'>";
            
            // Basic Information Section
            echo "<div class='edit-section'>";
            echo "<div class='edit-section-header'>Βασικές Πληροφορίες</div>";
            echo "<div class='edit-section-content'>";
            echo "<div class='form-grid'>";
            echo "<div class='form-item' style='grid-column: 1 / -1;'>";
            echo "<label class='form-label'>Τίτλος (αναλυτικά)</label>";
            echo "<input type='text' name='titlos' value='$titlos' class='form-input' style='width: 100%;'/>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            
            // Address Information Section
            echo "<div class='edit-section'>";
            echo "<div class='edit-section-header'>Διεύθυνση</div>";
            echo "<div class='edit-section-content'>";
            echo "<div class='form-grid'>";
            echo "<div class='form-item'>";
            echo "<label class='form-label'>Διεύθυνση</label>";
            echo "<input type='text' name='address' value='$address' class='form-input'/>";
            echo "</div>";
            echo "<div class='form-item'>";
            echo "<label class='form-label'>Ταχυδρομικός Κώδικας</label>";
            echo "<input type='text' name='tk' value='$tk' size='5' class='form-input'/>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            
            // Contact Information Section
            echo "<div class='edit-section'>";
            echo "<div class='edit-section-header'>Στοιχεία Επικοινωνίας</div>";
            echo "<div class='edit-section-content'>";
            echo "<div class='form-grid'>";
            echo "<div class='form-item'>";
            echo "<label class='form-label'>Τηλέφωνο</label>";
            echo "<input type='text' name='tel' value='$tel' class='form-input'/>";
            echo "</div>";
            echo "<div class='form-item'>";
            echo "<label class='form-label'>Τηλέφωνο 2</label>";
            echo "<input type='text' name='tel2' value='$tel2' class='form-input'/>";
            echo "</div>";
            echo "<div class='form-item'>";
            echo "<label class='form-label'>Fax</label>";
            echo "<input type='text' name='fax' value='$fax' class='form-input'/>";
            echo "</div>";
            echo "<div class='form-item'>";
            echo "<label class='form-label'>Email</label>";
            echo "<input type='text' name='email' value='$email' class='form-input'/>";
            echo "</div>";
            echo "<div class='form-item'>";
            echo "<label class='form-label'>Email 2</label>";
            echo "<input type='text' name='email2' value='$email2' class='form-input'/>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            
            // Disable organikothta & organikes when not admin
            // $disabled = $_SESSION['userlevel'] > 1 ? 'disabled' : '';
            
            if ($type == 1 || $type == 2){
                // Organizational Information Section
                echo "<div class='edit-section'>";
                echo "<div class='edit-section-header'>Οργανωτικά Στοιχεία</div>";
                echo "<div class='edit-section-content'>";
                echo "<div class='form-grid'>";
                echo "<div class='form-item'>";
                echo "<label class='form-label'>Οργανικότητα</label>";
                echo "<input type='text' name='organ' value='$organikothta' size='2' class='form-input'/>";
                echo "</div>";
                echo "<div class='form-item'>";
                echo "<label class='form-label'>Λειτουργικότητα</label>";
                echo "<div style='padding: 10px 12px; background: #f9fafb; border-radius: 8px; color: #374151;'>$leitoyrg</div>";
                echo "</div>";
                echo "</div>";
                
                // Organikes
                echo "<div style='margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb;'>";
                echo "<label class='form-label' style='margin-bottom: 12px; font-size: 1rem;'>Οργανικές Θέσεις</label>";
                echo "<div class='organikes-grid'>";
                
                // if Dimotiko
                if ($type == 1) {
                    if ($type2 != 2) {
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ70:</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[0]' size='2' />";
                        echo "</div>";
                    } else {
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ70 EAE:</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[0]' size='2' />";
                        echo "</div>";
                    }
                    echo "<div class='organikes-item'>";
                    echo "<label>ΠΕ11:</label>";
                    echo "<input type='text' name='organikes[]' value='$organikes[1]' size='2' />";
                    echo "</div>";
                    echo "<div class='organikes-item'>";
                    echo "<label>ΠΕ06:</label>";
                    echo "<input type='text' name='organikes[]' value='$organikes[2]' size='2' />";
                    echo "</div>";
                    echo "<div class='organikes-item'>";
                    echo "<label>ΠΕ79:</label>";
                    echo "<input type='text' name='organikes[]' value='$organikes[3]' size='2' />";
                    echo "</div>";
                    echo "<div class='organikes-item'>";
                    echo "<label>ΠΕ05:</label>";
                    echo "<input type='text' name='organikes[]' value='$organikes[4]' size='2' />";
                    echo "</div>";
                    echo "<div class='organikes-item'>";
                    echo "<label>ΠΕ07:</label>";
                    echo "<input type='text' name='organikes[]' value='$organikes[5]' size='2' />";
                    echo "</div>";
                    echo "<div class='organikes-item'>";
                    echo "<label>ΠΕ08:</label>";
                    echo "<input type='text' name='organikes[]' value='$organikes[6]' size='2' />";
                    echo "</div>";
                    echo "<div class='organikes-item'>";
                    echo "<label>ΠΕ86:</label>";
                    echo "<input type='text' name='organikes[]' value='$organikes[7]' size='2' />";
                    echo "</div>";
                    echo "<div class='organikes-item'>";
                    echo "<label>ΠΕ91:</label>";
                    echo "<input type='text' name='organikes[]' value='$organikes[8]' size='2' />";
                    echo "</div>";
                    if ($type2 == 2) {
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ21 (Λογοθεραπευτών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[9]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ23 (Ψυχολόγων):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[10]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ25 (Σχ.Νοσηλευτών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[11]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ26 (Λογοθεραπευτών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[12]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ28 (Φυσικοθεραπευτών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[13]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ29 (Εργοθεραπευτών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[14]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ30 (Κοιν.Λειτουργών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[15]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΔΕ1ΕΒΠ:</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[16]' size='2' />";
                        echo "</div>";
                    }
                }
                // if Nip
                else if ($type == 2) {
                    if ($type2 != 2){
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ60:</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[0]' size='2' />";
                        echo "</div>";
                    } else {
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ60 EAE:</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[0]' size='2' />";
                        echo "</div>";
                    }
                    if ($type2 == 2) {
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ21 (Λογοθεραπευτών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[1]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ23 (Ψυχολόγων):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[2]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ25 (Σχ.Νοσηλευτών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[3]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ26 (Λογοθεραπευτών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[4]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ28 (Φυσικοθεραπευτών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[5]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ29 (Εργοθεραπευτών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[6]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΠΕ30 (Κοιν.Λειτουργών):</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[7]' size='2' />";
                        echo "</div>";
                        echo "<div class='organikes-item'>";
                        echo "<label>ΔΕ1ΕΒΠ:</label>";
                        echo "<input type='text' name='organikes[]' value='$organikes[8]' size='2' />";
                        echo "</div>";
                    }
                }
                echo "</div>"; // organikes-grid
                echo "</div>"; // organikes wrapper
                echo "</div>"; // edit-section-content
                echo "</div>"; // edit-section
            }
            // PE05/PE07 Hours Section (only for Dimotiko)
            if ($type == 1 ){
                echo "<div class='edit-section'>";
                echo "<div class='edit-section-header'>Ώρες Ειδικών Μαθημάτων</div>";
                echo "<div class='edit-section-content'>";
                echo "<div class='form-grid'>";
                echo "<div class='form-item'>";
                echo "<label class='form-label'>Ώρες ΠΕ05<span class='info-hint'><img src='../images/info.png' title='Αναλυτικά οι ώρες, π.χ.10 ώρες -> 4-4-2 στην ανάλυση'></span></label>";
                echo "<input type='text' name='tmpe05' size='2' value='$pe0507[0]' class='form-input'/>";
                echo "</div>";
                echo "<div class='form-item'>";
                echo "<label class='form-label'>Ανάλυση ΠΕ05</label>";
                echo "<input type='text' name='tmpe05b' size='6' value='$pe0507[1]' class='form-input'/>";
                echo "</div>";
                echo "<div class='form-item'>";
                echo "<label class='form-label'>Ώρες ΠΕ07<span class='info-hint'><img src='../images/info.png' title='Αναλυτικά οι ώρες, π.χ.10 ώρες -> 4-4-2 στην ανάλυση'></span></label>";
                echo "<input type='text' name='tmpe07' size='2' value='$pe0507[2]' class='form-input'/>";
                echo "</div>";
                echo "<div class='form-item'>";
                echo "<label class='form-label'>Ανάλυση ΠΕ07</label>";
                echo "<input type='text' name='tmpe07b' size='6' value='$pe0507[3]' class='form-input'/>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
            
            // Special Programs/Features Section
            if ($type == 1 || $type == 2){
                echo "<div class='edit-section'>";
                echo "<div class='edit-section-header'>Ειδικά Προγράμματα & Χαρακτηριστικά</div>";
                echo "<div class='edit-section-content'>";
                echo "<div class='checkbox-group'>";
                
                if ($entaksis[0]) {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" name='entaksis' checked id='entaksis'/>";
                    echo "<label for='entaksis'>Τμήμα Ένταξης / Μαθητές: <input type='text' name='entaksis_math' value='$entaksis[1]' size='2' style='margin-left: 8px; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; width: 40px;'/></label>";
                    echo "</div>";
                } else {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" name='entaksis' id='entaksis'/>";
                    echo "<label for='entaksis'>Τμήμα Ένταξης / Μαθητές: <input type='text' name='entaksis_math' value='$entaksis[1]' size='2' style='margin-left: 8px; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; width: 40px;'/></label>";
                    echo "</div>";
                }
                
                if ($ypodoxis) {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" name='ypodoxis' checked id='ypodoxis'/>";
                    echo "<label for='ypodoxis'>Τμήμα Υποδοχής</label>";
                    echo "</div>";
                } else {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" name='ypodoxis' id='ypodoxis'/>";
                    echo "<label for='ypodoxis'>Τμήμα Υποδοχής</label>";
                    echo "</div>";
                }
                
                echo "</div>"; // checkbox-group
                
                if ($entaksis[0] || $ypodoxis) {
                    echo "<div class='form-grid' style='margin-top: 16px;'>";
                    if ($entaksis[0]) {
                        echo "<div class='form-item'>";
                        echo "<label class='form-label'>Εκπ/κοί Τμ.Ένταξης</label>";
                        echo "<input type='text' name='ekp_te' size='1' value='$ekp_ee_exp[0]' class='form-input'/>";
                        echo "</div>";
                    }
                    if ($ypodoxis) {
                        echo "<div class='form-item'>";
                        echo "<label class='form-label'>Εκπ/κοί Τμ.Υποδοχής</label>";
                        echo "<input type='text' name='ekp_ty' size='1' value='$ekp_ee_exp[1]' class='form-input'/>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
                echo "</div>";
                echo "</div>";
            }
                    
            // Additional Features for Dimotiko
            if ($type == 1) {
                // Wrap in a section
                echo "<div class='edit-section'>";
                echo "<div class='edit-section-header'>Επιπλέον Χαρακτηριστικά</div>";
                echo "<div class='edit-section-content'>";
                echo "<div class='checkbox-group'>";
                echo $frontistiriako ? 
                    "<div class='checkbox-item'><input type=\"checkbox\" name='frontistiriako' checked id='frontistiriako'/><label for='frontistiriako'>Φροντιστηριακό Τμήμα</label></div>" :
                    "<div class='checkbox-item'><input type=\"checkbox\" name='frontistiriako' id='frontistiriako'/><label for='frontistiriako'>Φροντιστηριακό Τμήμα</label></div>";
                echo $oloimero ? 
                    "<div class='checkbox-item'><input type=\"checkbox\" name='oloimero' checked id='oloimero'/><label for='oloimero'>Όλοήμερο</label></div>" : 
                    "<div class='checkbox-item'><input type=\"checkbox\" name='oloimero' id='oloimero'/><label for='oloimero'>Όλοήμερο</label></div>";
                echo $ted ? 
                    "<div class='checkbox-item'><input type=\"checkbox\" name='ted' checked id='ted'/><label for='ted'>Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</label></div>" :
                    "<div class='checkbox-item'><input type=\"checkbox\" name='ted' id='ted'/><label for='ted'>Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</label></div>";
                echo $anenergo ? 
                    "<div class='checkbox-item'><input type=\"checkbox\" name='anenergo' checked id='anenergo'/><label for='anenergo'>Ανενεργό</label></div>" :
                    "<div class='checkbox-item'><input type=\"checkbox\" name='anenergo' id='anenergo'/><label for='anenergo'>Ανενεργό</label></div>";
                echo "</div>";
                
                // Library section
                echo "<div class='form-item' style='margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;'>";
                echo "<div class='checkbox-item' style='margin-bottom: 12px;'>";
                echo "<input type=\"checkbox\" name='vivliothiki' id='vivliothiki'";
                echo $vivliothiki ? 'checked' : '';
                echo " onchange='valueChanged()'/>";
                echo "<label for='vivliothiki'>Σχολική βιβλιοθήκη</label>";
                echo "</div>";
                echo "<div id='workerdiv'";
                echo $vivliothiki ? '' : " style='display:none;'";
                echo ">";
                echo "<label class='form-label'>Υπεύθυνος/-η:</label>";
                workersCmb($vivliothiki, $sch, $mysqlconnection);
                echo "</div>";
                echo "</div>";
                
                // Proini Zoni
                echo "<div class='form-item' style='margin-top: 16px;'>";
                echo "<label class='form-label'>Πρωινή Ζώνη:</label>";
                $proinizoni = unserialize(mysqli_result($result, 0, "proinizoni"));
                workersMultiCmb($proinizoni, $sch, $mysqlconnection,'proinizoni');
                echo "</div>";
                
                echo "</div>"; // edit-section-content
                echo "</div>"; // edit-section
                
                // Additional Information Section
                echo "<div class='edit-section'>";
                echo "<div class='edit-section-header'>Επιπλέον Πληροφορίες</div>";
                echo "<div class='edit-section-content'>";
                echo "<div class='form-grid'>";
                
                echo "<div class='form-item' style='grid-column: 1 / -1;'>";
                echo "<label class='form-label'>Σχόλια</label>";
                echo "<textarea rows='4' cols='80' name='comments' class='form-textarea'>$comments</textarea>";
                echo "</div>";
                
                // Θητεία Δ/ντή
                echo "<div class='checkbox-item' style='grid-column: 1 / -1; margin-top: 8px;'>";
                echo $thiteia ? 
                        "<input type=\"checkbox\" name='thiteia' checked id='thiteia'/>" :
                        "<input type=\"checkbox\" name='thiteia' id='thiteia'/>";
                echo "<label for='thiteia'>Δ/ντής σε θητεία</label>";
                echo "</div>";
                
                if ($thiteia) {
                    echo "<div class='form-item'>";
                    echo "<label class='form-label'>Από</label>";
                    echo "<div>";
                    modern_datepicker('thiteia_apo', $thiteia_apo, array(
                        'minDate' => '2000-01-01',
                        'maxDate' => '2050-12-31',
                    ));
                    echo "</div>";
                    echo "</div>";
                    echo "<div class='form-item'>";
                    echo "<label class='form-label'>Έως</label>";
                    echo "<div>";
                    modern_datepicker('thiteia_ews', $thiteia_ews, array(
                        'minDate' => '2000-01-01',
                        'maxDate' => '2050-12-31',
                    ));
                    echo "</div>";
                    echo "</div>";
                } else {
                    echo "<div class='form-item'>";
                    echo "<label class='form-label'>Από</label>";
                    echo "<div>";
                    modern_datepicker('thiteia_apo', $thiteia_apo, array(
                        'minDate' => '2000-01-01',
                        'maxDate' => '2050-12-31',
                    ));
                    echo "</div>";
                    echo "</div>";
                    echo "<div class='form-item'>";
                    echo "<label class='form-label'>Έως</label>";
                    echo "<div>";
                    modern_datepicker('thiteia_ews', $thiteia_ews, array(
                        'minDate' => '2000-01-01',
                        'maxDate' => '2050-12-31',
                    ));
                    echo "</div>";
                    echo "</div>";
                }
                
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "<br>";
                        
                //if ($oloimero) - Afairethike gia taxythterh kataxwrhsh...
                //{
                /*
                    echo "<table class=\"imagetable\" border='1'>";
                    echo "<tr><td>Μαθητές Ολοημέρου: <input type='text' name='oloimero_stud' value='$oloimero_stud' size='2'/></td>";
                    echo "<td>Εκπ/κοί Ολοημέρου: <input type='text' name='oloimero_tea' value='$oloimero_tea' size='2'/><td></tr>";
                    echo "</table>";
                */
                //}
                // Students/Classes Section for Dimotiko
                echo "<div class='edit-section'>";
                echo "<div class='edit-section-header'>Μαθητικό Δυναμικό</div>";
                echo "<div class='edit-section-content'>";
                echo "<table class=\"imagetable\" border='1' style='width: auto; max-width: 900px; margin: 0 auto;'>";
                echo "<tr><td colspan=8><strong>Σύνολο Μαθητών Πρωινού: $synolo</strong></td></tr>";
                echo "<tr><td>Α'</td><td>Β'</td><td>Γ'</td><td>Δ'</td><td>Ε'</td><td>ΣΤ'</td><td>Ολ.<small>(15.00/16.00)</small></td><td>ΠΖ</td></tr>";
                if ($synolo>0) {
                    echo "<tr><td><input type='text' name='a' size='1' value=$classes[0] class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='b' size='1' value=$classes[1] class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='c' size='1' value=$classes[2] class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='d' size='1' value=$classes[3] class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='e' size='1' value=$classes[4] class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='f' size='1' value=$classes[5] class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='g' size='3' value=$classes[6] class='form-input' style='width: 80px; text-align: center;'/></td><td><input type='text' name='h' size='1' value=$classes[7] class='form-input' style='width: 50px; text-align: center;'/></td></tr>";
                } else {
                    echo "<tr><td><input type='text' name='a' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='b' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='c' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='d' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='e' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='f' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='g' size='3' value='0' class='form-input' style='width: 80px; text-align: center;'/></td><td><input type='text' name='h' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td></tr>";
                }
                echo "<tr><td colspan=8><strong>Τμήματα (Εκπαιδευτικοί) ανά τάξη<br>Σύνολο Τμημάτων Πρωινού: $synolo_tmim</strong></td></tr>";
                if ($synolo>0) {
                    echo "<tr><td><input type='text' name='ta' size='1' value=$tmimata_exp[0] class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='tb' size='1' value=$tmimata_exp[1] class='form-input' style='width: 50px; text-align: center;'/></td>";
                    echo "<td><input type='text' name='tc' size='1' value=$tmimata_exp[2] class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='td' size='1' value=$tmimata_exp[3] class='form-input' style='width: 50px; text-align: center;'/></td>";
                    echo "<td><input type='text' name='te' size='1' value=$tmimata_exp[4] class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='tf' size='1' value=$tmimata_exp[5] class='form-input' style='width: 50px; text-align: center;'/></td>";
                    echo "<td>15.00&nbsp;<input type='text' name='tg' size='1' value=$tmimata_exp[6] class='form-input' style='width: 40px; text-align: center;'/>16.00&nbsp;<input type='text' name='th' size='1' value=$tmimata_exp[7] class='form-input' style='width: 40px; text-align: center;'/>17.30&nbsp;<input type='text' name='tj' size='1' value=$tmimata_exp[9] class='form-input' style='width: 40px; text-align: center;'/></td><td><input type='text' name='ti' size='1' value=$tmimata_exp[8] class='form-input' style='width: 50px; text-align: center;'/></td></tr>";
                } else {
                    echo "<tr><td><input type='text' name='ta' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='tb' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td>";
                    echo "<td><input type='text' name='tc' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='td' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td>";
                    echo "<td><input type='text' name='te' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='tf' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td>";
                    echo "<td><input type='text' name='tg' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td><td><input type='text' name='th' size='1' value='0' class='form-input' style='width: 50px; text-align: center;'/></td></tr>";
                }
                echo '<tr><td colspan=8><small>ΣΗΜ. Για 4/θέσια συμπληρώνουμε τμήματα στις τάξεις Α,Β,Γ,Ε & για 5/θέσια Α,Β,Γ,Ε,ΣΤ</small></td></tr>';
                echo "</table>";
                echo "</div>";
                echo "</div>";
            }
            // if nip
            else if ($type == 2) {
                // Additional Features for Nipiagogeio
                echo "<div class='edit-section'>";
                echo "<div class='edit-section-header'>Επιπλέον Χαρακτηριστικά</div>";
                echo "<div class='edit-section-content'>";
                echo "<div class='checkbox-group'>";
                echo $oloimero ? 
                    "<div class='checkbox-item'><input type=\"checkbox\" name='oloimero' checked id='oloimero'/><label for='oloimero'>Όλοήμερο</label></div>" :
                    "<div class='checkbox-item'><input type=\"checkbox\" name='oloimero' id='oloimero'/><label for='oloimero'>Όλοήμερο</label></div>";
                echo $anenergo ? 
                    "<div class='checkbox-item'><input type=\"checkbox\" name='anenergo' checked id='anenergo'/><label for='anenergo'>Ανενεργό</label></div>" :
                    "<div class='checkbox-item'><input type=\"checkbox\" name='anenergo' id='anenergo'/><label for='anenergo'>Ανενεργό</label></div>";
                echo "</div>";
                
                echo "<div class='form-item' style='margin-top: 16px;'>";
                echo "<label class='form-label'>Μαθητές Πρωινής Ζώνης</label>";
                echo "<input type='text' name='pz' size='1' value=$klasiko_exp[6] class='form-input' style='width: 60px;'/>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                
                // Additional Information Section for Nipiagogeio
                echo "<div class='edit-section'>";
                echo "<div class='edit-section-header'>Επιπλέον Πληροφορίες</div>";
                echo "<div class='edit-section-content'>";
                echo "<div class='form-grid'>";
                
                echo "<div class='form-item' style='grid-column: 1 / -1;'>";
                echo "<label class='form-label'>Σχόλια</label>";
                echo "<textarea rows='4' cols='80' name='comments' class='form-textarea'>$comments</textarea>";
                echo "</div>";
                
                // Θητεία Δ/ντή
                echo "<div class='checkbox-item' style='grid-column: 1 / -1; margin-top: 8px;'>";
                echo $thiteia ? 
                        "<input type=\"checkbox\" name='thiteia' checked id='thiteia'/>" :
                        "<input type=\"checkbox\" name='thiteia' id='thiteia'/>";
                echo "<label for='thiteia'>Δ/ντής σε θητεία</label>";
                echo "</div>";
                
                echo "<div class='form-item'>";
                echo "<label class='form-label'>Από</label>";
                echo "<div>";
                modern_datepicker('thiteia_apo', $thiteia_apo, array(
                    'minDate' => '2000-01-01',
                    'maxDate' => '2050-12-31',
                ));
                echo "</div>";
                echo "</div>";
                echo "<div class='form-item'>";
                echo "<label class='form-label'>Έως</label>";
                echo "<div>";
                modern_datepicker('thiteia_ews', $thiteia_ews, array(
                    'minDate' => '2000-01-01',
                    'maxDate' => '2050-12-31',
                ));
                echo "</div>";
                echo "</div>";
                
                echo "</div>";
                echo "</div>";
                echo "</div>";
                
                // Students Section for Nipiagogeio
                echo "<div class='edit-section'>";
                echo "<div class='edit-section-header'>Μαθητές</div>";
                echo "<div class='edit-section-content'>";
                echo "<table class=\"imagetable\" border='1' style='width: auto; max-width: 650px; margin: 0 auto;'>";
                echo "<tr><td rowspan=2>Τμήμα</td><td colspan=2>Κλασικό</td><td colspan=2>Ολοήμερο</td></tr>";
                echo "<tr><td>Νήπια</td><td>Προνήπια</td><td>Νήπια</td><td>Προνήπια</td></tr>";
                // t1
                echo "<tr><td>Τμ.1</td><td><input type='text' name='k1a' size='1' value=$klasiko_exp[0] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='k1b' size='1' value=$klasiko_exp[1] class='form-input' style='width: 50px; text-align: center;'>";
                echo "</td><td><input type='text' name='o1a' size='1' value=$oloimero_nip_exp[0] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='o1b' size='1' value=$oloimero_nip_exp[1] class='form-input' style='width: 50px; text-align: center;'></td></tr>";
                // t2
                echo "<tr><td>Τμ.2</td><td><input type='text' name='k2a' size='1' value=$klasiko_exp[2] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='k2b' size='1' value=$klasiko_exp[3] class='form-input' style='width: 50px; text-align: center;'>";
                echo "</td><td><input type='text' name='o2a' size='1' value=$oloimero_nip_exp[2] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='o2b' size='1' value=$oloimero_nip_exp[3] class='form-input' style='width: 50px; text-align: center;'></td></tr>";
                // t3
                echo "<tr><td>Τμ.3</td><td><input type='text' name='k3a' size='1' value=$klasiko_exp[4] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='k3b' size='1' value=$klasiko_exp[5] class='form-input' style='width: 50px; text-align: center;'>";
                echo "</td><td><input type='text' name='o3a' size='1' value=$oloimero_nip_exp[4] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='o3b' size='1' value=$oloimero_nip_exp[5] class='form-input' style='width: 50px; text-align: center;'></td></tr>";
                // t4
                echo "<tr><td>Τμ.4</td><td><input type='text' name='k4a' size='1' value=$klasiko_exp[7] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='k4b' size='1' value=$klasiko_exp[8] class='form-input' style='width: 50px; text-align: center;'>";
                echo "</td><td><input type='text' name='o4a' size='1' value=$oloimero_nip_exp[6] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='o4b' size='1' value=$oloimero_nip_exp[7] class='form-input' style='width: 50px; text-align: center;'></td></tr>";
                // t5
                echo "<tr><td>Τμ.5</td><td><input type='text' name='k5a' size='1' value=$klasiko_exp[9] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='k5b' size='1' value=$klasiko_exp[10] class='form-input' style='width: 50px; text-align: center;'>";
                echo "</td><td><input type='text' name='o5a' size='1' value=$oloimero_nip_exp[8] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='o5b' size='1' value=$oloimero_nip_exp[9] class='form-input' style='width: 50px; text-align: center;'></td></tr>";
                // t6
                echo "<tr><td>Τμ.6</td><td><input type='text' name='k6a' size='1' value=$klasiko_exp[11] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='k6b' size='1' value=$klasiko_exp[12] class='form-input' style='width: 50px; text-align: center;'>";
                echo "</td><td><input type='text' name='o6a' size='1' value=$oloimero_nip_exp[10] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='o6b' size='1' value=$oloimero_nip_exp[11] class='form-input' style='width: 50px; text-align: center;'></td></tr>";
                // t7
                echo "<tr><td>Τμ.7</td><td><input type='text' name='k7a' size='1' value=$klasiko_exp[13] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='k7b' size='1' value=$klasiko_exp[14] class='form-input' style='width: 50px; text-align: center;'>";
                echo "</td><td><input type='text' name='o7a' size='1' value=$oloimero_nip_exp[12] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='o7b' size='1' value=$oloimero_nip_exp[13] class='form-input' style='width: 50px; text-align: center;'></td></tr>";
                // t8
                echo "<tr><td>Τμ.8</td><td><input type='text' name='k8a' size='1' value=$klasiko_exp[15] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='k8b' size='1' value=$klasiko_exp[16] class='form-input' style='width: 50px; text-align: center;'>";
                echo "</td><td><input type='text' name='o8a' size='1' value=$oloimero_nip_exp[14] class='form-input' style='width: 50px; text-align: center;'></td><td><input type='text' name='o8b' size='1' value=$oloimero_nip_exp[15] class='form-input' style='width: 50px; text-align: center;'></td></tr>";
                echo "</table>";
                
                // Oloimero dieyrymenoy (16-17.30)
                echo "<div style='margin-top: 24px;'>";
                echo "<h4 style='margin-bottom: 12px;'>Ολοήμερο διευρυμένου προγράμματος (16.00-17.30)</h4>";
                echo "<table class=\"imagetable\" border='1' style='width: auto; max-width: 350px; margin: 0 auto;'>";
                echo "<thead><tr><th>Τμήματα</th><th>Μαθητές</th></tr></thead>";
                echo "<tr><td><input type='text' name='o9a' size='3' value=$oloimero_nip_exp[16] class='form-input' style='width: 80px; text-align: center;'/></td><td><input type='text' name='o9b' size='3' value=$oloimero_nip_exp[17] class='form-input' style='width: 80px; text-align: center;'/></td></tr>";
                echo "</table>";
                echo "</div>";

                echo "<div style='margin-top: 24px;'>";
                echo "<h4 style='margin-bottom: 12px;'>Νηπιαγωγοί</h4>";
                echo "<table class=\"imagetable\" border='1' style='width: auto; max-width: 450px; margin: 0 auto;'>";
                echo "<thead><tr><th>Κλασικό</th><th>Ολοήμερο</th><th>Τμ.Ένταξης</th></tr></thead>";
                echo "<tr><td><input type='text' name='ekp_kl' size='1' value=$nip_exp[0] class='form-input' style='width: 60px; text-align: center;'/></td><td><input type='text' name='ekp_ol' size='1' value=$nip_exp[1] class='form-input' style='width: 60px; text-align: center;'/></td><td><input type='text' name='ekp_te' size='1' value=$nip_exp[2] class='form-input' style='width: 60px; text-align: center;'/></td></tr>";
                echo "</table>";
                echo "</div>";
                
                echo "</div>"; // edit-section-content
                echo "</div>"; // edit-section
            }
                    
            echo "	<input type='hidden' name = 'sch' value='$sch'>";
            echo "	<input type='hidden' name = 'name' value='$str1'>";
            
            // Action Buttons
            echo "<div class='action-buttons'>";
            echo "<input type='submit' value='Αποθήκευση'>";
            $schLink = "school_status.php?org=$sch";
            echo "<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='$schLink'\">";
            echo "</div>";
            echo "</form>";
                
        }
        ?>
        </center>
        <div id='results'></div>
        </body>
        </html>
