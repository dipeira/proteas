<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once "../config.php";
  require_once "../include/functions.php";
  
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
    $page_title = 'Καρτέλα σχολείου';
    require '../etc/head.php'; 
    ?>
    <LINK href="../css/jquery-ui.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Καρτέλα σχολείου</title>
    <style type="text/css">
      /* General Information Tab Styling */
      #general {
        padding: 20px 0;
      }
      
      .info-section {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
      }
      
      .info-section:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
      }
      
      .info-section-header {
        background: linear-gradient(135deg, #4f8188 0%, #30b7cb 100%);
        color: #ffffff;
        padding: 16px 24px;
        font-weight: 600;
        font-size: 1.1rem;
        margin: 0;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
      }
      
      .info-section-content {
        padding: 24px;
      }
      
      .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
      }
      
      .info-item {
        display: flex;
        flex-direction: column;
        padding: 12px 0;
        border-bottom: 1px solid #e5e7eb;
      }
      
      .info-item:last-child {
        border-bottom: none;
      }
      
      .info-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
      
      .info-value {
        font-size: 1rem;
        color: #111827;
        font-weight: 500;
        word-break: break-word;
      }
      
      .info-value a {
        color: #667eea;
        text-decoration: none;
        transition: color 0.2s ease;
      }
      
      .info-value a:hover {
        color: #764ba2;
        text-decoration: underline;
      }
      
      .info-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        background: #f3f4f6;
        color: #374151;
      }
      
      .info-badge.active {
        background: #d1fae5;
        color: #065f46;
      }
      
      .info-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
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
        padding: 8px 12px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
      }
      
      .checkbox-item input[type="checkbox"] {
        margin: 0;
        cursor: not-allowed;
      }
      
      .checkbox-item label {
        margin: 0;
        font-size: 0.9375rem;
        color: #374151;
        cursor: not-allowed;
      }
      
      .action-buttons {
        margin-top: 24px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
      }
      
      .action-buttons input[type="button"] {
        padding: 10px 24px;
        background: #667eea;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.1s ease;
      }
      
      .action-buttons input[type="button"]:hover {
        background: #764ba2;
        transform: translateY(-1px);
      }
      
      .organikes-table-wrapper {
        margin-top: 16px;
        overflow-x: auto;
      }
      
      .organikes-table-wrapper table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
      }
      
      .organikes-table-wrapper th {
        background: #f9fafb;
        padding: 10px 8px;
        text-align: center;
        font-weight: 600;
        color: #374151;
        border: 1px solid #e5e7eb;
      }
      
      .organikes-table-wrapper td {
        padding: 10px 8px;
        text-align: center;
        border: 1px solid #e5e7eb;
        background: #ffffff;
      }
      
      .organikes-toggle {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        background: #f3f4f6;
        border-radius: 6px;
        transition: all 0.2s ease;
      }
      
      .organikes-toggle:hover {
        background: #e5e7eb;
        color: #764ba2;
      }
      
      @media (max-width: 768px) {
        .info-grid {
          grid-template-columns: 1fr;
        }
        
        .info-section-content {
          padding: 16px;
        }
      }

      /* Dynamiko table styling */
      #dynamiko-table {
        width: 100%;
        border-collapse: collapse;
        margin: 24px 0;
        font-size: 0.95rem;
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
      }

      #dynamiko-table th,
      #dynamiko-table td {
        padding: 14px 16px;
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        text-align: center;
      }

      #dynamiko-table thead {
        background: linear-gradient(135deg,rgb(113, 153, 209),rgb(89, 105, 140));
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.1em;
      }

      #dynamiko-table thead th {
        color: #ffffff;
        font-size: 0.9rem;
        text-shadow: 0 2px 6px rgba(15, 23, 42, 0.45);
      }

      #dynamiko-table tbody tr:nth-child(odd) {
        background: #f8fafc;
      }

      #dynamiko-table tbody tr:last-child td {
        border-bottom: none;
      }
      
      /* Personnel Tab Accordion Styling */
      #personnel-accordion {
        margin-top: 20px;
      }
      
      #personnel-accordion .ui-accordion-header {
        background: linear-gradient(135deg,rgb(108, 176, 185) 0%, #30b7cb 100%) !important;
        color: #ffffff;
        border: none;
        border-radius: 8px !important;
        margin-bottom: 8px;
        padding: 16px 20px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
      }
      
      #personnel-accordion .ui-accordion-header:hover {
        background: linear-gradient(135deg,rgb(120, 162, 75) 0%,rgb(137, 234, 102) 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
      }
      
      #personnel-accordion .ui-accordion-header-active {
        background: linear-gradient(135deg,rgb(127, 144, 146) 0%,rgb(142, 197, 205) 100%);
        border-radius: 8px 8px 0 0 !important;
      }
      
      #personnel-accordion .ui-accordion-header .ui-accordion-header-icon {
        float: right;
        margin-top: 0;
        margin-right: 0;
      }
      
      #personnel-accordion .ui-accordion-content {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-top: none;
        border-radius: 0 0 8px 8px;
        padding: 20px;
        margin-bottom: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      }
      
      #personnel-accordion .ui-accordion-content h3 {
        margin-top: 0;
        margin-bottom: 16px;
        color: #374151;
        font-size: 1.1rem;
        font-weight: 600;
      }
      
      #personnel-accordion .ui-accordion-content h2 {
        margin-top: 24px;
        margin-bottom: 16px;
        color: #374151;
        font-size: 1.25rem;
        font-weight: 600;
      }
      
      #personnel-accordion .ui-accordion-content h2:first-child {
        margin-top: 0;
      }
      
      #personnel-accordion .personnel-count {
        display: inline-block;
        background: rgba(255, 255, 255, 0.3);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.875rem;
        margin-left: 8px;
        font-weight: 500;
      }
    </style>
    
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
    <script type="text/javascript">
    $(function() {
        $( "#tabs" ).tabs();
        $( "#personnel-accordion" ).accordion({
            collapsible: true,
            heightStyle: "content",
            active: false,
            icons: {
                header: "ui-icon-triangle-1-e",
                activeHeader: "ui-icon-triangle-1-s"
            }
        });
    });
    $().ready(function() {
      $("#org").autocomplete("../employee/get_school.php", {
          width: 260,
          matchContains: true,
          selectFirst: false
      });
      $("#slidingDiv").hide();
      $("#slidingDiv2").hide();
      $('#show_hide').click(function(e){
          e.preventDefault();
          $("#slidingDiv").slideToggle();
          var $this = $(this);
          if ($this.text().indexOf('▼') !== -1) {
              $this.text($this.text().replace('▼', '▲'));
          } else {
              $this.text($this.text().replace('▲', '▼'));
          }
      });
      $('#show_hide2').click(function(e){
          e.preventDefault();
          $("#slidingDiv2").slideToggle();
      });
      
      // Expand/Collapse all accordion sections
      $('#expandAllBtn').click(function(e){
          e.preventDefault();
          var $accordion = $("#personnel-accordion");
          var $headers = $accordion.find(".ui-accordion-header");
          var $contents = $accordion.find(".ui-accordion-content");
          var isExpanded = $(this).data('expanded') || false;
          
          if (!isExpanded) {
              // Expand all
              $headers.removeClass('ui-corner-all').addClass('ui-accordion-header-active ui-state-active ui-corner-top');
              $headers.find('.ui-accordion-header-icon').removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
              $contents.show().addClass('ui-accordion-content-active');
              $(this).text('Σύμπτυξη όλων ▲').data('expanded', true);
          } else {
              // Collapse all
              $headers.removeClass('ui-accordion-header-active ui-state-active ui-corner-top').addClass('ui-corner-all');
              $headers.find('.ui-accordion-header-icon').removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e');
              $contents.hide().removeClass('ui-accordion-content-active');
              $(this).text('Ανάπτυξη όλων ▼').data('expanded', false);
          }
      });
    });
    $(document).ready(function() { 
      $(".tablesorter").tablesorter({widgets: ['zebra']}); 
      $('#toggleBtn').click(function(){
          event.preventDefault();
          $("#analysis").slideToggle();
      });
      $('#toggleSystegBtn').click(function(){
          event.preventDefault();
          $("#systeg").slideToggle();
      });
    });    
    </script>
  </head>
  <body> 
    <?php require '../etc/menu.php'; ?>
    <center>
        <h2>Καρτέλα σχολείου</h2>
    <?php
    function disp_school($sch,$sxol_etos,$conn)
    {
      $query = "SELECT * from school where id=$sch";
      $result = mysqli_query($conn, $query);
              
      $titlos = mysqli_result($result, 0, "titlos");
      $address = mysqli_result($result, 0, "address");
      $tk = mysqli_result($result, 0, "tk");
      $dimos = mysqli_result($result, 0, "dimos");
      $dimos = getDimos($dimos, $conn);
      $cat = getCategory(mysqli_result($result, 0, "category"));
      $tel = mysqli_result($result, 0, "tel");
      $tel2 = mysqli_result($result, 0, "tel2");
      $fax = mysqli_result($result, 0, "fax");
      $email = mysqli_result($result, 0, "email");
      $email2 = mysqli_result($result, 0, "email2");
      $type = mysqli_result($result, 0, "type");
      $type2 = mysqli_result($result, 0, "type2");
      $organikothta = mysqli_result($result, 0, "organikothta");
      $leitoyrg = get_leitoyrgikothta($sch, $conn);
      // organikes - added 05-10-2012
      $organikes = unserialize(mysqli_result($result, 0, "organikes"));
      // kena_org, kena_leit - added 19-06-2013
      $kena_org = unserialize(mysqli_result($result, 0, "kena_org"));
      $code = mysqli_result($result, 0, "code");
      $updated = mysqli_result($result, 0, "updated");
      $perif = mysqli_result($result, 0, "perif");
      $systeg = mysqli_result($result, 0, "systeg");
      $anenergo = mysqli_result($result, 0, "anenergo");
      if ($systeg) {
          $systegName = getSchool($systeg, $conn);
      }
      $archive = mysqli_result($result, 0, "archive");
      $thiteia = mysqli_result($result, 0, "thiteia");
      $thiteia_apo = mysqli_result($result, 0, "thiteia_apo");
      $thiteia_ews = mysqli_result($result, 0, "thiteia_ews");    
                      
      // if dimotiko
      if ($type == 1) {
          $students = mysqli_result($result, 0, "students");
          $classes = explode(",", $students);
          $frontistiriako = mysqli_result($result, 0, "frontistiriako");
          $ted = mysqli_result($result, 0, "ted");
          //$oloimero_stud = mysqli_result($result, 0, "oloimero_stud");
          $tmimata = mysqli_result($result, 0, "tmimata");
          $tmimata_exp = explode(",", $tmimata);
          //$oloimero_tea = mysqli_result($result, 0, "oloimero_tea");
          $ekp_ee = mysqli_result($result, 0, "ekp_ee");
          $ekp_ee_exp = explode(",", $ekp_ee);
          
          $synolo = array_sum($classes);
          //$synolo_tmim = array_sum($tmimata_exp);
          $vivliothiki = mysqli_result($result, 0, "vivliothiki");
      }
      //if nipiagwgeio
      if ($type == 2) {
          $klasiko = mysqli_result($result, 0, "klasiko");
          $klasiko_exp = strlen($klasiko) > 0 ? explode(",", $klasiko) : '';
          $oloimero_nip = mysqli_result($result, 0, "oloimero_nip");
          $oloimero_nip_exp = strlen($oloimero_nip) > 0 ? explode(",", $oloimero_nip) : '';
          $nip = mysqli_result($result, 0, "nip");
          $nip_exp = explode(",", $nip);
      }
      // entaksis (varchar): on/off, no. of students
      $entaksis = explode(",", mysqli_result($result, 0, "entaksis"));
      $org_ent = $entaksis[0] ? 1 : 0;
      $ypodoxis = mysqli_result($result, 0, "ypodoxis");
      //$frontistiriako = mysqli_result($result, 0, "frontistiriako");
      $oloimero = mysqli_result($result, 0, "oloimero");
      $comments = mysqli_result($result, 0, "comments");

      // general tab
      echo "<div id='general'>";
        
        // Basic Information Section
        echo "<div class='info-section'>";
        echo "<div class='info-section-header'>Βασικές Πληροφορίες</div>";
        echo "<div class='info-section-content'>";
        echo "<div class='info-grid'>";
        echo "<div class='info-item'>";
        echo "  <span class='info-label'>Τύπος Σχολείου</span>";
        echo "  <span class='info-value'>".get_school_type($sch, $conn)."</span>";
        echo "</div>";
        echo "<div class='info-item'>";
        echo "  <span class='info-label'>Κωδικός ΥΠΑΙΘ</span>";
        echo "  <span class='info-value'>$code</span>";
        echo "</div>";
        echo "<div class='info-item' style='grid-column: 1 / -1;'>";
        echo "  <span class='info-label'>Τίτλος (αναλυτικά)</span>";
        echo "  <span class='info-value'>$titlos</span>";
        echo "</div>";

        echo "<div class='info-item'>";
        echo "  <span class='info-label'>Διεύθυνση</span>";
        echo "  <span class='info-value'>$address</span>";
        echo "</div>";
        echo "<div class='info-item'>";
        echo "  <span class='info-label'>Ταχυδρομικός Κώδικας</span>";
        echo "  <span class='info-value'>$tk</span>";
        echo "</div>";
        echo "<div class='info-item'>";
        echo "  <span class='info-label'>Δήμος</span>";
        echo "  <span class='info-value'>$dimos</span>";
        echo "</div>";

        if ($tel) {
            echo "<div class='info-item'>";
            echo "  <span class='info-label'>Τηλέφωνο</span>";
            echo "  <span class='info-value'>".preg_replace('/\s+/', '', $tel)."</span>";
            echo "</div>";
          }
          if ($email) {
            echo "<div class='info-item'>";
            echo "  <span class='info-label'>Email</span>";
            echo "  <span class='info-value'><a href=\"mailto:$email\">$email</a></span>";
            echo "</div>";
          }
          if ($email2) {
            echo "<div class='info-item'>";
            echo "  <span class='info-label'>Email 2</span>";
            echo "  <span class='info-value'><a href=\"mailto:$email2\">$email2</a></span>";
            echo "</div>";
          }

        echo "</div>"; //info-grid
        echo "</div>"; //info-section-content
        echo "</div>"; //info-section
        
        
        if ($type == 1 || $type == 2) {
            // Organizational Information Section
            echo "<div class='info-section'>";
            echo "<div class='info-section-header'>Οργανικότητα - Λειτουργικότητα</div>";
            echo "<div class='info-section-content'>";
            echo "<div class='info-grid'>";
            echo "<div class='info-item'>";
            echo "  <span class='info-label'>Οργανικότητα</span>";
            echo "  <span class='info-value'>$organikothta</span>";
            echo "</div>";
            echo "<div class='info-item'>";
            echo "  <span class='info-label'>Λειτουργικότητα</span>";
            echo "  <span class='info-value'>$leitoyrg</span>";
            echo "</div>"   ;
            echo "<div class='info-item'>";
            echo "  <span class='info-label'>Κατηγορία</span>";
            echo "  <span class='info-value'>$cat</span>";
            echo "</div>";
            // οργανικά τοποθετηθέντες
            $klados_qry = ($type == 1) ? 2 : 1;
            $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados= $klados_qry AND status IN (1,3,5) AND thesi IN (0,1,2)";
            $rs = mysqli_query($conn, $qry);
            $orgtop = mysqli_result($rs, 0, "cnt");
            
            echo "<div class='info-item'>";
            echo "  <span class='info-label'>Οργανικά τοποθετηθέντες (πλην Τ.Ε.)</span>";
            echo "  <span class='info-value'>$orgtop</span>";
            echo "</div>";
            

            
            // 05-10-2012 - organikes
            $organikes = is_array($organikes) ? $organikes : [];
            for ($i=0; $i<count($organikes); $i++) {
                if (!$organikes[$i]) {
                    $organikes[$i]=0;
                }
            }
            
            // if ds
            if ($type == 1) {
                echo "<div style='margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;'>";
                echo "<a href='#' id='show_hide' class='organikes-toggle'>▼ Οργανικές Θέσεις</a>";
                echo "<div id='slidingDiv' style='display: none; margin-top: 16px;'>";
                echo "<div class='organikes-table-wrapper'>";
                echo "<table>";
                echo "<thead><tr>";
                echo "<th>Κλάδος</th>";
                echo $type2 != 2 ? "<th><span title='Δασκάλων'>70</th>" : "<th><span title='Δασκάλων EAE'>70 EAE</th>";
                echo "<th><span title='Φυσικής Αγωγής'>11</th>";
                echo "<th><span title='Αγγλικών'>06</th>";
                echo "<th><span title='Μουσικών'>79</th>";
                echo "<th><span title='Γαλλικών'>05</th>";
                echo "<th><span title='Γερμανικών'>07</th>";
                echo "<th><span title='Καλλιτεχνικών'>08</th>";
                echo "<th><span title='Πληροφορικής'>86</th>";
                echo "<th><span title='Θεατρικών Σπουδών'>91</th>";
                echo $org_ent ? "<th>Ένταξης</th>" : '';
                // if eidiko
                if ($type2 == 2) {
                    echo "<th><span title='Λογοθεραπευτών'>21</th>";
                    echo "<th><span title='Ψυχολόγων'>23</th>";
                    echo "<th><span title='Σχ.Νοσηλευτών'>25</th>";
                    echo "<th><span title='Λογοθεραπευτών'>26</th>";
                    echo "<th><span title='Φυσικοθεραπευτών'>28</th>";
                    echo "<th><span title='Εργοθεραπευτών'>29</th>";
                    echo "<th><span title='Κοιν.Λειτουργών'>30</th>";
                    echo "<th><span title='Βοηθ.Προσ.Ειδ.Αγ.'>ΔΕ1ΕΒΠ</th>";
                }
                echo "</tr></thead>";
                echo "<tbody><tr>";
                echo "<td>Οργανικές</td>";
                echo "<td>$organikes[0]</td>";
                echo "<td>$organikes[1]</td>";
                echo "<td>$organikes[2]</td>";
                echo "<td>$organikes[3]</td>";
                echo "<td>$organikes[4]</td>";
                echo "<td>$organikes[5]</td>";
                echo "<td>$organikes[6]</td>";
                echo "<td>$organikes[7]</td>";
                echo "<td>$organikes[8]</td>";
                echo $org_ent ? "<td>$org_ent</td>" : '';
                // if eidiko
                if ($type2 == 2) {
                    echo "<td>$organikes[9]</td>";
                    echo "<td>$organikes[10]</td>";
                    echo "<td>$organikes[11]</td>";
                    echo "<td>$organikes[12]</td>";
                    echo "<td>$organikes[13]</td>";
                    echo "<td>$organikes[14]</td>";
                    echo "<td>$organikes[15]</td>";
                    echo "<td>$organikes[16]</td>";
                }
                echo "</tr>";
            }
            // if nip
            else if ($type == 2) {
                echo "<div style='margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;'>";
                echo "<div class='info-item'>";
                echo "<span class='info-label'>Οργανικές ΠΕ60</span>";
                echo "<span class='info-value'>$organikes[0]</span>";
                echo "</div>";
                // if eidiko
                if ($type2 == 2) {
                    echo "<div class='organikes-table-wrapper' style='margin-top: 16px;'>";
                    echo "<table>";
                    echo "<thead><tr>";
                    echo "<th>Κλάδος</th>";
                    echo "<th><span title='ΠΕ60.50'>60 ΕΑΕ</th>";
                    echo "<th><span title='Λογοθεραπευτών'>21</th>";
                    echo "<th><span title='Ψυχολόγων'>23</th>";
                    echo "<th><span title='Σχ.Νοσηλευτών'>25</th>";
                    echo "<th><span title='Λογοθεραπευτών'>26</th>";
                    echo "<th><span title='Φυσικοθεραπευτών'>28</th>";
                    echo "<th><span title='Εργοθεραπευτών'>29</th>";
                    echo "<th><span title='Κοιν.Λειτουργών'>30</th>";
                    echo "<th><span title='Βοηθ.Προσ.Ειδ.Αγ.'>ΔΕ1ΕΒΠ</th>";
                    echo "</tr></thead><tbody>";
                    echo "<tr>";
                    echo "<td><strong>Οργανικές</strong></td>";
                    echo "<td>$organikes[0]</td>";
                    echo "<td>$organikes[1]</td>";
                    echo "<td>$organikes[2]</td>";
                    echo "<td>$organikes[3]</td>";
                    echo "<td>$organikes[4]</td>";
                    echo "<td>$organikes[5]</td>";
                    echo "<td>$organikes[6]</td>";
                    echo "<td>$organikes[7]</td>";
                    echo "<td>$organikes[8]</td>";
                    echo "</tr>";
                    echo "<tr>";
                    $orgs = get_orgs($sch,$conn);
                    echo "<td><strong>Οργ.ανήκοντες</strong></td>";
                    echo "<td>".($orgs['ΠΕ60ΕΑΕ']+$orgs['ΠΕ61'])."</td>";
                    echo "<td>".$orgs['ΠΕ21']."</td>";
                    echo "<td>".$orgs['ΠΕ23']."</td>";
                    echo "<td>".$orgs['ΠΕ25']."</td>";
                    echo "<td>".$orgs['ΠΕ26']."</td>";
                    echo "<td>".$orgs['ΠΕ28']."</td>";
                    echo "<td>".$orgs['ΠΕ29']."</td>";
                    echo "<td>".$orgs['ΠΕ30']."</td>";
                    echo "<td>".$orgs['ΔΕ1ΕΒΠ']."</td>";
                    echo "</tr>";
                    echo "<tr>";
                    $orgs = get_orgs($sch,$conn);
                    echo "<td><strong>Οργ.Κενά</strong></td>";
                    echo "<td>".($organikes[0] - $orgs['ΠΕ60ΕΑΕ'] - $orgs['ΠΕ61'])."</td>";
                    echo "<td>".($organikes[1] - $orgs['ΠΕ21'])."</td>";
                    echo "<td>".($organikes[2] - $orgs['ΠΕ23'])."</td>";
                    echo "<td>".($organikes[3] - $orgs['ΠΕ25'])."</td>";
                    echo "<td>".($organikes[4] - $orgs['ΠΕ26'])."</td>";
                    echo "<td>".($organikes[5] - $orgs['ΠΕ28'])."</td>";
                    echo "<td>".($organikes[6] - $orgs['ΠΕ29'])."</td>";
                    echo "<td>".($organikes[7] - $orgs['ΠΕ30'])."</td>";
                    echo "<td>".($organikes[8] - $orgs['ΔΕ1ΕΒΠ'])."</td>";
                    echo "</tr>";
                    echo "</tbody></table>";
                    echo "</div>";
                } else {
                    echo "<div class='info-item' style='margin-top: 12px;'>";
                    echo "<span class='info-label'>Οργανικά κενά ΠΕ60</span>";
                    echo "<span class='info-value'>".($organikes[0] - $orgtop)."</span>";
                    echo "</div>";
                }
                echo "</div>";
            }
            // 05-10-2012 - kena_leit, kena_org
            $kena_org_count = is_array($kena_org) ? count($kena_org) : 0;
            for ($i=0; $i<$kena_org_count; $i++) {
                if (!$kena_org[$i]) {
                    $kena_org[$i]=0;
                }
            }
            // if dim
            if ($type == 1) 
            {
                echo "<tr>";
                $orgs = get_orgs($sch,$conn);
                echo "<td>Οργανικά ανήκοντες</td>";
                echo $type2 != 2 ? "<td>".$orgs['ΠΕ70']."</td>" : "<td>".($orgs['ΠΕ70ΕΑΕ'] + $orgs['ΠΕ71'])."</td>";
                echo "<td>".$orgs['ΠΕ11']."</td>";
                echo "<td>".$orgs['ΠΕ06']."</td>";
                echo "<td>".$orgs['ΠΕ79']."</td>";
                echo "<td>".$orgs['ΠΕ05']."</td>";
                echo "<td>".$orgs['ΠΕ07']."</td>";
                echo "<td>".$orgs['ΠΕ08']."</td>";
                echo "<td>".$orgs['ΠΕ86']."</td>";
                echo "<td>".$orgs['ΠΕ91']."</td>";
                echo $org_ent ? "<td>".$orgs['ent']."</td>" : '';
                // if eidiko
                if ($type2 == 2) {
                    echo "<td>".$orgs['ΠΕ21']."</td>";
                    echo "<td>".$orgs['ΠΕ23']."</td>";
                    echo "<td>".$orgs['ΠΕ25']."</td>";
                    echo "<td>".$orgs['ΠΕ26']."</td>";
                    echo "<td>".$orgs['ΠΕ28']."</td>";
                    echo "<td>".$orgs['ΠΕ29']."</td>";
                    echo "<td>".$orgs['ΠΕ30']."</td>";
                    echo "<td>".$orgs['ΔΕ1ΕΒΠ']."</td>";
                }
                echo "</tr>";
                ///////
                echo "</tr>";
                echo "<tr>";
                $orgs = get_orgs($sch,$conn);
                echo "<td>Οργανικά κενά</td>";
                echo $type2 != 2 ? "<td>".($organikes[0] - $orgs['ΠΕ70'])."</td>" : "<td>".($organikes[0] - $orgs['ΠΕ70ΕΑΕ'] - $orgs['ΠΕ71'])."</td>";
                echo "<td>".($organikes[1] - $orgs['ΠΕ11'])."</td>";
                echo "<td>".($organikes[2] - $orgs['ΠΕ06'])."</td>";
                echo "<td>".($organikes[3] - $orgs['ΠΕ79'])."</td>";
                echo "<td>".($organikes[4] - $orgs['ΠΕ05'])."</td>";
                echo "<td>".($organikes[5] - $orgs['ΠΕ07'])."</td>";
                echo "<td>".($organikes[6] - $orgs['ΠΕ08'])."</td>";
                echo "<td>".($organikes[7] - $orgs['ΠΕ86'])."</td>";
                echo "<td>".($organikes[8] - $orgs['ΠΕ91'])."</td>";
                echo $org_ent ? "<td>".($org_ent - $orgs['ent'])."</td>" : '';
                if ($type2 == 2) {
                    echo "<td>".($organikes[9] - $orgs['ΠΕ21'])."</td>";
                    echo "<td>".($organikes[10] - $orgs['ΠΕ23'])."</td>";
                    echo "<td>".($organikes[11] - $orgs['ΠΕ25'])."</td>";
                    echo "<td>".($organikes[12] - $orgs['ΠΕ26'])."</td>";
                    echo "<td>".($organikes[13] - $orgs['ΠΕ28'])."</td>";
                    echo "<td>".($organikes[14] - $orgs['ΠΕ29'])."</td>";
                    echo "<td>".($organikes[15] - $orgs['ΠΕ30'])."</td>";
                    echo "<td>".($organikes[16] - $orgs['ΔΕ1ΕΒΠ'])."</td>";
                }
                echo "</tr>";
                echo "</table>";
                echo "</div>"; // organikes-table-wrapper
                echo "</div>"; // slidingDiv
                echo "</div>"; // organikes wrapper
            }
            
            echo "</div>"; // info-grid
            echo "</div>"; // info-section-content
            echo "</div>"; // info-section
            
            // Special Programs/Features Section
            echo "<div class='info-section'>";
            echo "<div class='info-section-header'>Ειδικά Προγράμματα & Χαρακτηριστικά</div>";
            echo "<div class='info-section-content'>";
            echo "<div class='checkbox-group'>";
            
            if ($entaksis[0]) {
                echo "<div class='checkbox-item'>";
                echo "<input type=\"checkbox\" checked disabled>";
                echo "<label>Τμήμα Ένταξης / Μαθητές: $entaksis[1]</label>";
                echo "</div>";
            } else {
                echo "<div class='checkbox-item'>";
                echo "<input type=\"checkbox\" disabled>";
                echo "<label>Τμήμα Ένταξης</label>";
                echo "</div>";
            }
            
            if ($ypodoxis) {
                echo "<div class='checkbox-item'>";
                echo "<input type=\"checkbox\" checked disabled>";
                echo "<label>Τμήμα Υποδοχής</label>";
                echo "</div>";
            } else {
                echo "<div class='checkbox-item'>";
                echo "<input type=\"checkbox\" disabled>";
                echo "<label>Τμήμα Υποδοχής</label>";
                echo "</div>";
            }
            
            if ($type == 1) {
                if ($frontistiriako) {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" checked disabled>";
                    echo "<label>Φροντιστηριακό Τμήμα</label>";
                    echo "</div>";
                } else {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" disabled>";
                    echo "<label>Φροντιστηριακό Τμήμα</label>";
                    echo "</div>";
                }
            }
            // if nip print Proini Zoni (klasiko[6])
            else if ($type == 2) {
                if ($klasiko_exp[6]) {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" checked disabled>";
                    echo "<label>Πρωινή Ζώνη / Μαθητές: $klasiko_exp[6]</label>";
                    echo "</div>";
                } else {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" disabled>";
                    echo "<label>Πρωινή Ζώνη</label>";
                    echo "</div>";
                }
            }
                                                
            if ($oloimero) {
                echo "<div class='checkbox-item'>";
                echo "<input type=\"checkbox\" checked disabled>";
                echo "<label>Όλοήμερο</label>";
                echo "</div>";
            } else {
                echo "<div class='checkbox-item'>";
                echo "<input type=\"checkbox\" disabled>";
                echo "<label>Όλοήμερο</label>";
                echo "</div>";
            }
            
            if ($type == 1) {
                if ($ted) {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" checked disabled>";
                    echo "<label>Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</label>";
                    echo "</div>";
                } else {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" disabled>";
                    echo "<label>Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</label>";
                    echo "</div>";
                }
                if ($vivliothiki) {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" checked disabled>";
                    echo "<label>Σχολική βιβλιοθήκη";
                    $qry1 = "SELECT surname,name,perigrafh from employee e JOIN klados k ON e.klados = k.id WHERE e.id=$vivliothiki";
                    $res1 = mysqli_query($conn, $qry1);
                    if ($row = mysqli_fetch_assoc($res1)){
                        echo "<i><small> (Υπευθυνος/-η: ".$row['surname'].' '.$row['name'].', '.$row['perigrafh'].')</small></i>';
                    } else {
                        echo '<i><small> (Δεν έχει οριστεί υπεύθυνος βιβλιοθήκης)</small></i>';
                    }
                    echo "</label>";
                    echo "</div>";
                } else {
                    echo "<div class='checkbox-item'>";
                    echo "<input type=\"checkbox\" disabled>";
                    echo "<label>Σχολική βιβλιοθήκη</label>";
                    echo "</div>";
                }
            }
            
            echo "</div>"; // checkbox-group
            
            if ($entaksis[0] || $ypodoxis) {
                echo "<div class='info-grid' style='margin-top: 16px;'>";
                if ($entaksis[0]) {
                    echo "<div class='info-item'>";
                    echo "<span class='info-label'>Εκπ/κοί Τμ.Ένταξης</span>";
                    echo "<span class='info-value'>$ekp_ee_exp[0]</span>";
                    echo "</div>";
                }
                if ($ypodoxis) {
                    echo "<div class='info-item'>";
                    echo "<span class='info-label'>Εκπ/κοί Τμ.Υποδοχής</span>";
                    echo "<span class='info-value'>$ekp_ee_exp[1]</span>";
                    echo "</div>";
                }
                echo "</div>";
            }
            
            echo "</div>"; // info-section-content
            echo "</div>"; // info-section
            
            // Additional Information Section
            echo "<div class='info-section'>";
            echo "<div class='info-section-header'>Επιπλέον Πληροφορίες</div>";
            echo "<div class='info-section-content'>";
            echo "<div class='info-grid'>";
            
            if ($type == 1) {
                echo "<div class='info-item'>";
                echo "<span class='info-label'>Ενότητα Σχολικών Συμβούλων</span>";
                echo "<span class='info-value'>".$perif."η</span>";
                echo "</div>";
            }
            
            echo "<div class='info-item'>";
            echo "<span class='info-label'>Κατάσταση</span>";
            echo "<span class='info-value'>";
            echo $anenergo ? "<span class='info-badge inactive'>Σε αναστολή</span>" : "<span class='info-badge active'>Ενεργό</span>";
            echo "</span>";
            echo "</div>";
            
            if ($thiteia) {
                echo "<div class='info-item'>";
                echo "<span class='info-label'>Διευθυντής με θητεία</span>";
                echo "<span class='info-value'>";
                if ($thiteia_apo){
                    echo "ΝΑΙ (Από ".date("d-m-Y", strtotime($thiteia_apo)). ' έως '.date("d-m-Y", strtotime($thiteia_ews)). ')';
                } else {
                    echo "ΝΑΙ (Έως ".date("d-m-Y", strtotime($thiteia_ews)). ")";
                }
                echo "</span>";
                echo "</div>";
            } else {
                echo "<div class='info-item'>";
                echo "<span class='info-label'>Διευθυντής με θητεία</span>";
                echo "<span class='info-value'>ΟΧΙ</span>";
                echo "</div>";
            }
            
            if ($systeg) {
                echo "<div class='info-item' style='grid-column: 1 / -1;'>";
                echo "<span class='info-label'>Συστεγαζόμενη σχολική μονάδα</span>";
                echo "<span class='info-value'><a href='school_status.php?org=$systeg' target='_blank'>$systegName</a></span>";
                echo "</div>";
            }
            
            if ($comments) {
                echo "<div class='info-item' style='grid-column: 1 / -1;'>";
                echo "<span class='info-label'>Σχόλια</span>";
                echo "<span class='info-value'>".nl2br($comments)."</span>";
                echo "</div>";
            }
            
            if ($updated>0) {
                echo "<div class='info-item' style='grid-column: 1 / -1; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 12px; margin-top: 8px;'>";
                echo "<span class='info-label' style='font-size: 0.75rem;'>Τελ.ενημέρωση</span>";
                echo "<span class='info-value' style='font-size: 0.875rem; color: #6b7280;'>".date("d-m-Y H:i", strtotime($updated))."</span>";
                echo "</div>";
            }
            
            echo "</div>"; //info-grid
            echo "</div>"; //info-section-content
            echo "</div>"; //info-section
            // echo "</div>";
        }
        
        // Action Buttons
        if ( $_SESSION['userlevel'] < 3){
            echo "<div class='action-buttons'>";
            echo "<INPUT TYPE='button' VALUE='Επεξεργασία' onClick=\"parent.location='school_edit.php?org=$sch'\">";
            if ($type == 1 || $type == 2){
                echo "<INPUT TYPE='button' VALUE='Εκδρομές' onClick=\"parent.location='ekdromi.php?sch=$sch&op=list'\">";
            }
            echo "</div>";
        }
      echo "</div>"; // of general tab
      
      if ($type == 1 || $type == 2){
        echo "<div id='leit'>"; // leit tab
            
        if ($type == 1) {
            if ($synolo>0) {
            echo "<h3>Μαθητικό Δυναμικό</h3>";
            echo "<table id='dynamiko-table' border='1'>";
            echo "<thead><tr><td></td><td>Α'</td><td>Β'</td><td>Γ'</td><td>Δ'</td><td>Ε'</td><td>ΣΤ'</td><td class='tdnone'><i>Ολοήμερο</i></td><td class='tdnone'><i>Πρωινή Ζώνη</i></td></tr></thead>";
            echo "<tbody>";
            $synolo_pr = $classes[0]+$classes[1]+$classes[2]+$classes[3]+$classes[4]+$classes[5];
            echo "<tr><td>Μαθ.Πρωινού<br><b>Σύνολο: $synolo_pr</b></td><td>$classes[0]</td><td>$classes[1]</td><td>$classes[2]</td><td>$classes[3]</td>";
            echo "<td>$classes[4]</td><td>$classes[5]</td><td class='tdnone'><i>$classes[6]</i></td><td class='tdnone'><i>$classes[7]</i></td></tr>";
            $synolo_pr = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
            echo "<tr><td>Τμ./τάξη Πρωινού<br><b>Σύνολο: $synolo_pr</b></td><td>$tmimata_exp[0]</td><td>$tmimata_exp[1]</td><td>$tmimata_exp[2]</td>";
            echo "<td>$tmimata_exp[3]</td><td>$tmimata_exp[4]</td><td>$tmimata_exp[5]</td><td class='tdnone'><i>$tmimata_exp[6]<small>&nbsp;&nbsp;&nbsp;(14-15)</small>";
            echo "<br>$tmimata_exp[7]<small>&nbsp;&nbsp;&nbsp;(15-16)</small>";
            echo $tmimata_exp[9] > 0 ? "<br>$tmimata_exp[9]<small>&nbsp;&nbsp;&nbsp;(16-17.30)</small>":"";
            // Get PZ teacher names
            $pznames = get_pz_names(mysqli_result($result, 0, "proinizoni"), $conn);
            echo "</i></td><td class='tdnone'><i>$tmimata_exp[8]</i>&nbsp;&nbsp;&nbsp;<span title='$pznames'><img style='border: 0pt none;' src='../images/info.png'</span></td></tr>";
            if (strlen($archive) > 0){
                // update school set students='Α,Β,Γ,Δ,Ε,ΣΤ,ΟΛ,ΠΡ-Ζ',
                // tmimata='Α,Β,Γ,Δ,Ε,ΣΤ,ΟΛ,ΟΛ16,ΠΡ-Ζ' WHERE code='9170117';
                echo "<tr><td colspan=9><a href='#' id='show_hide2'>Ιστορικό</a><br>";
                echo "<div id='slidingDiv2'>";
                echo "<table>";
                echo "<thead><tr>";
                echo "<th>Σχολικό έτος</th>";
                echo "<th>Α</th>";
                echo "<th>Β</th>";
                echo "<th>Γ</th>";
                echo "<th>Δ</th>";
                echo "<th>Ε</th>";
                echo "<th>ΣΤ</th>";
                echo "<th>Ολοήμερο</th>";
                echo "<th>Πρωινή Ζώνη</th>";
                echo "<th>Ένταξης</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                $archive_arr = unserialize($archive);
                foreach ($archive_arr as $key => $value) {
                echo '<tr>';
                echo "<td>". substr($key, 0, 4).'-'.substr($key, 4, 2) ."</td>";
                $data = explode(',',$value);
                echo "<td>$data[0] ($data[8])</td>";
                echo "<td>$data[1] ($data[9])</td>";
                echo "<td>$data[2] ($data[10])</td>";
                echo "<td>$data[3] ($data[11])</td>";
                echo "<td>$data[4] ($data[12])</td>";
                echo "<td>$data[5] ($data[13])</td>";
                echo "<td>$data[6] (14-15: $data[14], 15-16: $data[15])</td>";
                echo "<td>$data[7] ($data[16])</td>";
                echo $data[17] == 'on' ? "<td>ΝΑΙ ($data[18] μαθ.)</td>" : "<td></td>";
                }
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "<br>";
            }
            else 
            {
            echo "Δεν έχει καταχωρηθεί πλήθος μαθητών";
            echo "<br><br>";
            }
        }
        else if ($type == 2) {
            // klasiko_nip/pro: klasiko
            // klasiko pos: 0,1 t1n,p / 2,3 t2n,p / 4,5 t3n,p / 7,8 t4n,p / 9,10 t5n,p / 11,12 t6n,p
            // prwinh zvnh @ pos 7 -> klasiko[6]
            // oloimero_nip/pro: oloimero
            // oloimero pos: 0,1 t1n,p / 2,3 t2n,p / 4,5 t3n,p / 6,7 t4n,p / 8,9 t5n,p / 10,11 t6n,p
            // 12,13 t7n,p / 14,15 t8n,p / 16,17 dieyrymeno tmimata, dieyr stud
            if (is_array($klasiko_exp) && is_array($oloimero_nip_exp) ){
                // fill array blanks with zeroes
                foreach($klasiko_exp as &$val) {
                    if(empty($val)) { $val = 0; }
                }
                foreach($oloimero_nip_exp as &$val) {
                    if(empty($val)) { $val = 0; }
                }
                $klasiko_nip = $klasiko_exp[0] + $klasiko_exp[2] + $klasiko_exp[4] + $klasiko_exp[7] + $klasiko_exp[9] + $klasiko_exp[11] + $klasiko_exp[13] + $klasiko_exp[15];
                $klasiko_pro = $klasiko_exp[1] + $klasiko_exp[3] + $klasiko_exp[5] + $klasiko_exp[8] + $klasiko_exp[10] + $klasiko_exp[12] + $klasiko_exp[14] + $klasiko_exp[16];
                $oloimero_syn_nip = $oloimero_nip_exp[0] + $oloimero_nip_exp[2] + $oloimero_nip_exp[4] + $oloimero_nip_exp[6] + $oloimero_nip_exp[8] + $oloimero_nip_exp[10] + $oloimero_nip_exp[12] + $oloimero_nip_exp[14];
                $oloimero_syn_pro = $oloimero_nip_exp[1] + $oloimero_nip_exp[3] + $oloimero_nip_exp[5] + $oloimero_nip_exp[7] + $oloimero_nip_exp[9] + $oloimero_nip_exp[11] + $oloimero_nip_exp[13] + $oloimero_nip_exp[15];
                $has_dieyrymeno = $oloimero_nip_exp[16] > 0 ? true : false;

                // Μαθητές
                echo "<h3>Μαθητές</h3>";
                echo "<table id='dynamiko-table' border='1'>";
                $ola = $klasiko_nip + $klasiko_pro;
                $olola = $oloimero_syn_nip + $oloimero_syn_pro;
                echo "<thead><tr><td rowspan=2>Τμημα</td><td colspan=3>Κλασικο</small></td><td colspan=3>Ολοημερο</td></tr>";
                echo "<tr><td>Νηπια</td><td>Προνηπια</td><td>Συνολο</td><td>Νηπια</td><td>Προνηπια</td><td>Συνολο</td></tr>";
                echo "</thead>";
                echo "<tbody>";
                // t1
                $syn = $klasiko_exp[0]+$klasiko_exp[1];
                $tmimata_nip = 1;
                $tmimata_nip_ol = 0;
                echo "<tr><td>Τμ.1</td><td>$klasiko_exp[0]</td><td>$klasiko_exp[1]</td><td><b>$syn</b></td>";
                $syn_ol = $oloimero_nip_exp[0]+$oloimero_nip_exp[1];
                if ($syn_ol > 0) {
                    $tmimata_nip_ol += 1;
                }
                echo "<td>$oloimero_nip_exp[0]</td><td>$oloimero_nip_exp[1]</td><td><b>$syn_ol</b></td></tr>";
                // print t2 + t3 + t4 + t5 + t6 only if they have students
                // t2
                $syn2 = $klasiko_exp[2]+$klasiko_exp[3];
                $syn_ol2 = $oloimero_nip_exp[2]+$oloimero_nip_exp[3];
                if (($syn2+$syn_ol2) > 0) {
                    $tmimata_nip += 1;
                    if ($syn_ol2 > 0) {
                        $tmimata_nip_ol += 1;
                    }
                    echo "<tr><td>Τμ.2</td><td>$klasiko_exp[2]</td><td>$klasiko_exp[3]</td><td><b>$syn2</b></td>";
                    echo "<td>$oloimero_nip_exp[2]</td><td>$oloimero_nip_exp[3]</td><td><b>$syn_ol2</b></td></tr>";
                }
                // t3
                $syn3 = $klasiko_exp[4]+$klasiko_exp[5];
                $syn_ol3 = $oloimero_nip_exp[4]+$oloimero_nip_exp[5];
                if (($syn3+$syn_ol3) > 0) {
                    $tmimata_nip += 1;
                    if ($syn_ol3 > 0) {
                        $tmimata_nip_ol += 1;
                    }
                    echo "<tr><td>Τμ.3</td><td>$klasiko_exp[4]</td><td>$klasiko_exp[5]</td><td><b>$syn3</b></td>";
                    echo "<td>$oloimero_nip_exp[4]</td><td>$oloimero_nip_exp[5]</td><td><b>$syn_ol3</b></td></tr>";
                }
                // t4
                $syn4 = $klasiko_exp[7]+$klasiko_exp[8];
                $syn_ol4 = $oloimero_nip_exp[6]+$oloimero_nip_exp[7];
                if (($syn4+$syn_ol4) > 0) {
                    $tmimata_nip += 1;
                    if ($syn_ol4 > 0) {
                        $tmimata_nip_ol += 1;
                    }
                    echo "<tr><td>Τμ.4</td><td>$klasiko_exp[7]</td><td>$klasiko_exp[8]</td><td><b>$syn4</b></td>";
                    echo "<td>$oloimero_nip_exp[6]</td><td>$oloimero_nip_exp[7]</td><td><b>$syn_ol4</b></td></tr>";
                }
                // t5
                $syn5 = $klasiko_exp[9]+$klasiko_exp[10];
                $syn_ol5 = $oloimero_nip_exp[8]+$oloimero_nip_exp[9];
                if (($syn5+$syn_ol5) > 0) {
                    $tmimata_nip += 1;
                    if ($syn_ol5 > 0) {
                        $tmimata_nip_ol += 1;
                    }
                    echo "<tr><td>Τμ.5</td><td>$klasiko_exp[9]</td><td>$klasiko_exp[10]</td><td><b>$syn5</b></td>";
                    echo "<td>$oloimero_nip_exp[8]</td><td>$oloimero_nip_exp[9]</td><td><b>$syn_ol5</b></td></tr>";
                }
                // t6
                $syn6 = $klasiko_exp[11]+$klasiko_exp[12];
                $syn_ol6 = $oloimero_nip_exp[10]+$oloimero_nip_exp[11];
                if (($syn6+$syn_ol6) > 0) {
                    $tmimata_nip += 1;
                    if ($syn_ol6 > 0) {
                        $tmimata_nip_ol += 1;
                    }
                    echo "<tr><td>Τμ.6</td><td>$klasiko_exp[11]</td><td>$klasiko_exp[12]</td><td><b>$syn6</b></td>";
                    echo "<td>$oloimero_nip_exp[10]</td><td>$oloimero_nip_exp[11]</td><td><b>$syn_ol6</b></td></tr>";
                }
                // t7
                $syn7 = $klasiko_exp[13]+$klasiko_exp[14];
                $syn_ol7 = $oloimero_nip_exp[12]+$oloimero_nip_exp[13];
                if (($syn7+$syn_ol7) > 0) {
                    $tmimata_nip += 1;
                    if ($syn_ol7 > 0) {
                        $tmimata_nip_ol += 1;
                    }
                    echo "<tr><td>Τμ.7</td><td>$klasiko_exp[13]</td><td>$klasiko_exp[14]</td><td><b>$syn7</b></td>";
                    echo "<td>$oloimero_nip_exp[12]</td><td>$oloimero_nip_exp[13]</td><td><b>$syn_ol7</b></td></tr>";
                }
                // t8
                $syn8 = $klasiko_exp[15]+$klasiko_exp[16];
                $syn_ol8 = $oloimero_nip_exp[14]+$oloimero_nip_exp[15];
                if (($syn8+$syn_ol8) > 0) {
                    $tmimata_nip += 1;
                    if ($syn_ol8 > 0) {
                        $tmimata_nip_ol += 1;
                    }
                    echo "<tr><td>Τμ.8</td><td>$klasiko_exp[15]</td><td>$klasiko_exp[16]</td><td><b>$syn8</b></td>";
                    echo "<td>$oloimero_nip_exp[14]</td><td>$oloimero_nip_exp[15]</td><td><b>$syn_ol8</b></td></tr>";
                }
                // totals (if more than one tmima)
                if (($syn2 + $syn_ol2 + $syn3 + $syn_ol3) > 0) {
                    echo "<tr><td><strong>Σύνολα</strong></td><td><b>$klasiko_nip</b><td><b>$klasiko_pro</b></td><td><b>$ola</b></td>";
                    echo "<td><b>$oloimero_syn_nip</b><td><b>$oloimero_syn_pro</b></td><td><b>$olola</b></td>";
                    echo "</tr>";
                }
            }
            if (strlen($archive) > 0){
            // update school set klasiko='1Π,1Ν,2Π,2Ν,3Π,3Ν,ΠΖ', oloimero_nip='ΟΛ1Π,ΟΛ1Ν,ΟΛ2Π,ΟΛ2Ν',entaksis='0,0' where code=XXX;              
            echo "<tr><td colspan=9><a href='#' id='show_hide2'>Ιστορικό</a><br>";
            echo "<div id='slidingDiv2'>";
            echo "<table>";
            echo "<thead><tr>";
            echo "<th>Σχολικό έτος</th>";
            echo "<th>Πρ.1 Προνήπια</th>";
            echo "<th>Πρ.1 Νήπια</th>";
            echo "<th>Πρ.2 Προνήπια</th>";
            echo "<th>Πρ.2 Νήπια</th>";
            echo "<th>Πρ.3 Προνήπια</th>";
            echo "<th>Πρ.3 Νήπια</th>";
            echo "<th>Πρωινή Ζώνη</th>";
            echo "<th>Ολοήμ.1 Προνήπια</th>";
            echo "<th>Ολοήμ.1 Νήπια</th>";
            echo "<th>Ολοήμ.2 Προνήπια</th>";
            echo "<th>Ολοήμ.2 Νήπια</th>";
            echo "<th>Ένταξης</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            $archive_arr = unserialize($archive);
            foreach ($archive_arr as $key => $value) {
                echo '<tr>';
                echo "<td>". substr($key, 0, 4).'-'.substr($key, 4, 2) ."</td>";
                $data = explode(',',$value);
                echo "<td>$data[0]</td>";
                echo "<td>$data[1]</td>";
                echo "<td>$data[2]</td>";
                echo "<td>$data[3]</td>";
                echo "<td>$data[4]</td>";
                echo "<td>$data[5]</td>";
                echo "<td>$data[6]</td>";
                echo "<td>$data[7]</td>";
                echo "<td>$data[8]</td>";
                echo "<td>$data[9]</td>";
                echo "<td>$data[10]</td>";
                echo $data[11] == 'on' ? "<td>ΝΑΙ ($data[12] μαθ.)</td>" : "<td></td>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            }
            echo "</table>";
            echo "<br>";

            // dieyrymeno
            if ($has_dieyrymeno) {
                echo "<h5 style='margin-top:-10px'>Ολοήμερο διευρυμένου προγράμματος</h5>";
                echo "<table class='imagetable stable' style='margin-top:-10px'>";
                echo "<tr><td>Τμήματα</td><td>Μαθητές</td>";
                echo "<tr><td>$oloimero_nip_exp[16]</td><td>$oloimero_nip_exp[17]</td></tr>";
                echo "</table>";
            }
        
            $has_entaxi = strlen($entaksis[0])>1 ? 1 : 0; 
            // τοποθετημένοι εκπ/κοί ΠΕ60 (όχι Τ.Ε.)
            $top60 = $top60m = $top60ana = $top06m = $top06a = $topent = 0;
            $qry = "SELECT count(*) as pe60 FROM employee WHERE sx_yphrethshs = $sch AND klados IN (1,16) AND status=1 AND ent_ty = 0";
            $res = mysqli_query($conn, $qry);
            $top60m = mysqli_result($res, 0, 'pe60');
            $qry = "SELECT count(*) as pe60 FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados IN (1,16) AND status=1 AND ent_ty = 0";
            $res = mysqli_query($conn, $qry);
            $top60ana = mysqli_result($res, 0, 'pe60');
            $top60 = $top60m+$top60ana;
            // entaksis
            $qry = "SELECT count(*) as pe60 FROM employee WHERE sx_yphrethshs = $sch AND klados IN (1,16) AND status=1 AND ent_ty = 1";
            $res = mysqli_query($conn, $qry);
            $topent = mysqli_result($res, 0, 'pe60');
            $qry = "SELECT count(*) as pe60 FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados IN (1,16) AND status=1 AND ent_ty = 1";
            $res = mysqli_query($conn, $qry);
            $topentana = mysqli_result($res, 0, 'pe60');
            $topent = $topent + $topentana;

            // τοποθετημένοι εκπ/κοί ΠΕ06
            $top06 = top_pe06_nip($sch, $conn);

            $syn_apait = $tmimata_nip+$tmimata_nip_ol;
            $syn_apait += $has_dieyrymeno ? $oloimero_nip_exp[16] : 0;
            $apait06 = $tmimata_nip * WRES_PE06_NIP;

            echo "<h3>Λειτουργικά κενά</h3>";
            echo "<table class=\"imagetable stable\" border='1'>";
            echo "<thead><th></th><th>ΠΕ60</th><th>Κλασικό</th><th>Ολοήμερο</th>";
            echo $has_dieyrymeno ? "<th>Διευρυμένο</th>" : '';
            echo "<th>Τμ.Ένταξης</th><th>ΠΕ06 <small>(ώρες)</small></th></thead><tbody>";

            echo "<tr><td>Απαιτούμενοι</td>";
            echo "<td>$syn_apait</td>";
            echo "<td>$tmimata_nip</td><td>$tmimata_nip_ol</td>";
            echo $has_dieyrymeno ? "<td>$oloimero_nip_exp[16]</td>" : '';
            echo "<td>$has_entaxi</td><td>$apait06</td></tr>";

            echo "<tr><td>Υπάρχοντες </td><td>$top60</td><td></td><td></td><td>$topent</td>";
            echo $has_dieyrymeno ? "<td></td>" : '';
            echo "<td>$top06</td></tr>";
            //60
            $k_pl = $top60-$syn_apait;
            $k_pl_class = $k_pl >= 0 ? 
                "'background:none;background-color:rgba(0, 255, 0, 0.37)'" : 
                "'background:none;background-color:rgba(255, 0, 0, 0.45)'";
            //06
            $k_pl06 = $top06-$apait06;
            $k_plent = $topent - $has_entaxi;
            $k_pl_class06 = $k_pl06 >= 0 ? 
                "'background:none;background-color:rgba(0, 255, 0, 0.37)'" : 
                "'background:none;background-color:rgba(255, 0, 0, 0.45)'";
            echo "<tr><td>+ / -</td><td style=$k_pl_class>$k_pl</td><td></td><td></td><td>$k_plent</td>";
            echo $has_dieyrymeno ? "<td></td>" : '';
            echo "<td style=$k_pl_class06>$k_pl06</td></tr>";

            echo "</tbody></table>";
            echo "<br>";
        }
        if ( $_SESSION['userlevel'] < 3) {
            echo "<INPUT TYPE='button' VALUE='Επεξεργασία' onClick=\"parent.location='school_edit.php?org=$sch'\">";
        }
        echo "<br><br>";
        // if dimotiko & leitoyrg >= 4
        if ($type == 1 ) {//&& array_sum($tmimata_exp)>3){
            ektimhseis_wrwn($sch, $conn, $sxol_etos, true);
        }
        // if systegazomeno
        if ($systeg) {
            echo "<a id='toggleSystegBtn' href='#'>Συστεγαζόμενο: $systegName</a>";
            echo "<div id='systeg' style='display: none;'>";
            ektimhseis_wrwn($systeg, $conn, $sxol_etos, true);
            echo "</div>";
            echo "<br><br>";
        }
        echo "</div>"; // of leit tab
      }
    } // of disp_school
      
    echo "<div id=\"content\">";
    echo "<form id='searchfrm' name='searchfrm' action='' method='POST' autocomplete='off'>";
    echo "<table class=\"imagetable stable\" border='1'>";
    echo "<td>Σχολείο</td><td></td>";
    echo "<td><input type=\"text\" name=\"org\" id=\"org\" style='width:250px;'/></td></tr>";                
    echo "	</table>";
    echo "	<input type='submit' value='Αναζήτηση'>";
    //echo "  &nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value=\"Επαναφορά\" onClick=\"window.location.reload()\">";
    echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='school.php'\">";
    echo "	</form>";
    echo "</div>";
    
    if (isset($_POST['org']) || isset($_GET['org'])) {
      if (isset($_POST['org'])) {
          $str1 = $_POST['org'];
          $sch = getSchoolID($_POST['org'], $mysqlconnection);
          if (!$sch) {
              die('Το σχολείο δε βρέθηκε...');
          }
      }
      elseif (isset($_GET['org'])) {
          $str1 = getSchool($_GET['org'], $mysqlconnection);
          $sch = $_GET['org'];
          if (!$str1) {
              die('Το σχολείο δε βρέθηκε...');
          }
      }
      
      echo "<h1>$str1</h1>";
      echo "<div align='right'>";
    //   echo "<p style='margin-block-end: 1px; margin-block-start: -10px;'><small><a href='./school_status_old.php?org=".$sch."'>Μετάβαση στην παλιά προβολή</a></small></p>";
      echo "</div>";
      if (!$sch && !$str) {
          die('Το σχολείο δε βρέθηκε...');
      }
      if (isset($_GET['sxoletos'])) {
          $sxol_etos = $_GET['sxoletos'];
      }

      $query = "SELECT type from school where id=$sch";
      $result = mysqli_query($mysqlconnection, $query);
      $row = mysqli_fetch_assoc($result);
      $schooltype = $row['type'];

      // Tabs
      echo "<div id='container' style='width: 98%; padding: 8px;'>";
        echo "<div id='tabs'>";
          echo "<ul>";
          echo "<li><a href='#general'>Γενικά Στοιχεία</a></li>";
          // show tab only for schools
          if ($schooltype == 1 || $schooltype == 2){
            echo "<li><a href='#leit'>Μαθ.Δυναμικό - Κενά/Πλεονάσματα</a></li>";
          }
          echo "<li><a href='#personnel'>Προσωπικό</a></li>";
          echo $_SESSION['requests'] && $schooltype != 0 ? "<li><a href='#requests'>Αιτήματα</a></li>" : '';
          echo "</ul>";

         disp_school($sch, $sxol_etos, $mysqlconnection);

        

        // personnel tab
        echo "<div id='personnel'>";
        echo "<div style='margin-bottom: 15px; text-align: right;'>";
        echo "<button id='expandAllBtn' class='btn' style='padding: 8px 16px; cursor: pointer;'>Ανάπτυξη όλων ▼</button>";
        echo "</div>";
        echo "<div id='personnel-accordion'>";
        
        //Υπηρετούν με θητεία
        $query = "SELECT * from employee WHERE sx_yphrethshs='$sch' AND status=1 AND thesi in (1,2,6) ORDER BY thesi DESC";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Υπηρετούν με θητεία<span class='personnel-count'>$num</span></h3>";
            echo "<div>";
            
            $i=0;
            echo "<table id=\"mytbl\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Θέση</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {            
                $id = mysqli_result($result, $i, "id");
                $name = mysqli_result($result, $i, "name");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $thesi = mysqli_result($result, $i, "thesi");
                $th = thesicmb($thesi);
                $comments = shorten_text(mysqli_result($result, $i, "comments"));

                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a class='underline' href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$th</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "</div>";
        }                   
        //Ανήκουν οργανικά και υπηρετούν (ΠΕ60-70)
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0";
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0 ORDER BY klados";
        $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,5) AND ent_ty=0 AND (klados=2 OR klados=1)";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Ανήκουν οργανικά και υπηρετούν (ΠΕ60/ΠΕ70)<span class='personnel-count'>$num</span></h3>";
            echo "<div>";
            $i=0;
            echo "<table id=\"mytbl2\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {            
                $id = mysqli_result($result, $i, "id");
                $name = mysqli_result($result, $i, "name");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $comments = shorten_text(mysqli_result($result, $i, "comments"));
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a class='underline' href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "</div>";
        }
        //Ανήκουν οργανικά και υπηρετούν (Ειδικότητες)
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0";
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0 ORDER BY klados";
        $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,5) AND klados!=2 AND klados!=1";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Ανήκουν οργανικά και υπηρετούν (Ειδικότητες)<span class='personnel-count'>$num</span></h3>";
            echo "<div>";
            $i=0;
            echo "<table id=\"mytbl2\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {            
                $id = mysqli_result($result, $i, "id");
                $name = mysqli_result($result, $i, "name");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $comments = shorten_text(mysqli_result($result, $i, "comments"));

                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a class='underline' href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "</div>";
        }
        
        
        // Οργανική αλλού και υπηρετούν
        $query = "SELECT * from employee WHERE sx_organikhs!='$sch' AND sx_yphrethshs='$sch' AND thesi in (0,5)  AND ent_ty=0 AND status=1 ORDER BY klados";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Με οργανική σε άλλο σχολείο και υπηρετούν<span class='personnel-count'>$num</span></h3>";
            echo "<div>";
            $i=0;
            echo "<table id=\"mytbl3\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχολείο Οργανικής</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {
                $id = mysqli_result($result, $i, "id");
                $name = mysqli_result($result, $i, "name");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $sx_organ_id = mysqli_result($result, $i, "sx_organikhs");
                $sx_organikhs = getSchool($sx_organ_id, $mysqlconnection);
                $comments = shorten_text(mysqli_result($result, $i, "comments"));
                
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a class='underline' href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "</div>";
        }
        
        // Οργανική αλλού και δευτερεύουσα υπηρέτηση
        //$query = "SELECT * from employee WHERE sx_organikhs!='$sch' AND (sx_yphrethshs='$sch' AND thesi=0";
        $query = "SELECT * FROM employee e join yphrethsh y on e.id = y.emp_id where y.yphrethsh=$sch and e.sx_yphrethshs!=$sch AND y.sxol_etos = $sxol_etos AND status = 1";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Με οργανική και κύρια υπηρέτηση σε άλλο σχολείο, που υπηρετούν με διάθεση<span class='personnel-count'>$num</span></h3>";
            echo "<div>";
            $i=0;
            echo "<table id=\"mytbl3\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχολείο Οργανικής</th>";
            echo "<th>Ώρες</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {
                $id = mysqli_result($result, $i, 0);
                $name = mysqli_result($result, $i, "name");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $sx_organ_id = mysqli_result($result, $i, "sx_organikhs");
                $sx_organikhs = getSchool($sx_organ_id, $mysqlconnection);
                $comments = shorten_text(mysqli_result($result, $i, "comments"));
                $hours = mysqli_result($result, $i, "hours");
                
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a class='underline' href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$hours</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "</div>";
        }
        //Υπηρετούν σε τμήμα ένταξης
        $query = "SELECT * from employee WHERE sx_yphrethshs='$sch' AND status=1 AND ent_ty=1";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Υπηρετούν σε τμήμα ένταξης<span class='personnel-count'>$num</span></h3>";
            echo "<div>";
            $i=0;
            echo "<table id=\"mytbl2\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχολείο Οργανικής</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {            
                $id = mysqli_result($result, $i, "id");
                $name = mysqli_result($result, $i, "name");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $sx_organ_id = mysqli_result($result, $i, "sx_organikhs");
                $sx_organikhs = getSchool($sx_organ_id, $mysqlconnection);
                $comments = shorten_text(mysqli_result($result, $i, "comments"));

                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a class='underline' href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "</div>";
        }
        //Υπηρετούν σε τάξη υποδοχής
        $query = "SELECT * from employee WHERE sx_yphrethshs='$sch' AND status=1 AND ent_ty=2";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Υπηρετούν σε τάξη υποδοχής<span class='personnel-count'>$num</span></h3>";
            echo "<div>";
            $i=0;
            echo "<table id=\"mytbl2\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχολείο Οργανικής</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {            
                $id = mysqli_result($result, $i, "id");
                $name = mysqli_result($result, $i, "name");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $sx_organ_id = mysqli_result($result, $i, "sx_organikhs");
                $sx_organikhs = getSchool($sx_organ_id, $mysqlconnection);
                $comments = shorten_text(mysqli_result($result, $i, "comments"));

                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a class='underline' href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "</div>";
        }
        
        //Αναπληρωτές
        //$query = "SELECT * FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos)";
        $query = "SELECT *,e.type as etype, e.name as ename, p.name as praxiname FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id join praxi p on e.praxi = p.id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 1)";
        //echo $query;
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        $sx_yphrethshs = mysqli_result($result, 0, "sx_yphrethshs");
        if ($num) {
            echo "<h3>Αναπληρωτές <span class='personnel-count'>$num</span></h3>";
            echo "<div>";
            $i=0;
            echo "<table id=\"mytbl4\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Τύπος Απασχόλησης</th>";
            echo "<th>Πράξη</th>";
            echo "<th>Ώρες</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {
                $id = mysqli_result($result, $i, 0);
                $name = mysqli_result($result, $i, "ename");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $typos = mysqli_result($result, $i, "etype");
                $type = get_type($typos, $mysqlconnection);
                $thesi = mysqli_result($result, $i, "thesi");
                $entty = mysqli_result($result, $i, "ent_ty");
                $praxi = mysqli_result($result, $i, "praxiname");
                switch ($entty) {
                    case 1:
                        $type .= "<small> (Τμήμα ένταξης)</small>";
                        break;
                    case 2:
                        $type .= "<small> (Τάξη υποδοχής)</small>";
                        break;
                    case 3:
                        $type .= "<small> (Παράλληλη στήριξη)</small>";
                        break;
                    default:
                        break;
                }

                $comments = shorten_text(mysqli_result($result, $i, "comments"));
                $wres = mysqli_result($result, $i, "hours");
                
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a class='underline' href=\"../employee/ektaktoi.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$praxi</td><td>$wres</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "</div>";
        }
        
        //Απουσιάζουν: Ανήκουν οργανικά και υπηρετούν αλλού
        $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs!='$sch' AND status IN (1,3) order by klados";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Απουσιάζουν: Ανήκουν οργανικά και υπηρετούν αλλού<span class='personnel-count'>$num</span></h3>";
            echo "<div>";
            $i=0;
            echo "<table id=\"mytbl5\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Σχολείο/Φορέας Υπηρέτησης</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {       
                $id = mysqli_result($result, $i, "id");
                $name = mysqli_result($result, $i, "name");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $sx_yphrethshs_id = mysqli_result($result, $i, "sx_yphrethshs");
                $sx_yphrethshs = getSchool($sx_yphrethshs_id, $mysqlconnection);
                $comments = shorten_text(mysqli_result($result, $i, "comments"));
                
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a class='underline' href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_yphrethshs</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "</div>";
        }
        
        //Σε άδεια
        //old queries:
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND status=3";
        //$query0 = "SELECT * from adeia WHERE emp_id='$id' AND start<'$today' AND finish>'$today'";
        $today = date("Y-m-d");
        //$query = "SELECT * FROM adeia ad JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND start<'$today' AND finish>'$today'";
        //$query = "SELECT * FROM adeia ad JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND start<'$today' AND finish>'$today' AND status=3";
        //$query = "SELECT * FROM adeia ad RIGHT JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND ((start<'$today' AND finish>'$today') OR status=3)";
        //$query = "SELECT * FROM adeia ad RIGHT JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND ((start<'$today' AND finish>'$today') OR status=3) ORDER BY finish DESC";
        $query = "SELECT *,ad.id as adeia_id FROM adeia ad RIGHT JOIN employee emp ON ad.emp_id = emp.id JOIN yphrethsh yp ON emp.id = yp.emp_id
        WHERE yp.yphrethsh = $sch AND yp.sxol_etos=$sxol_etos AND ((start<'$today' AND finish>'$today') OR status=3) 
        ORDER BY finish DESC";
        // echo $query;
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Σε Άδεια - Μόνιμοι<span class='personnel-count'>$num</span></h3>";
            echo "<div>";
            $i=0;
            echo "<table id=\"mytbl6\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Τύπος</th>";
            echo "<th>Ημ/νία Επιστροφής</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            $apontes = array();
            while ($i < $num)
            {
              $flag = $absent = 0;        
              $id = mysqli_result($result, $i, "emp_id");
              $adeia_id = mysqli_result($result, $i, "adeia_id");
              $type = mysqli_result($result, $i, "type");
              $name = mysqli_result($result, $i, "name");
              $surname = mysqli_result($result, $i, "surname");
              $klados_id = mysqli_result($result, $i, "klados");
              $klados = getKlados($klados_id, $mysqlconnection);
              $comments = shorten_text(mysqli_result($result, $i, "comments"));
              $comm = $comments;
              $today = date("Y-m-d");
              $return = mysqli_result($result, $i, "finish");
              $start = mysqli_result($result, $i, "start");
              $status = mysqli_result($result, $i, "status");
              $comments = '';
              $organ = mysqli_result($result, $i, 'sx_organikhs');
              if ($organ != $sch) {
                  $comments .= "<i>Σχ. Οργανικής: <a href='../school/school_status.php?org=$organ'>".getSchool($organ, $mysqlconnection)."</a></i><br>";
              }
              // if return date exists, check if absent and print - else continue.
              if ($return) {
                  if ($start<$today && $return>$today) {
                      $flag = $absent = 1;
                      $apontes[] = $id;
                  }
                  else
                  {
                          //$flag=1;
                      if (!in_array($id, $apontes)) {
                              $flag = 1;
                      }
                          $apontes[] = $id;
                          $comments .= "Δεν απουσιάζει.<br>Έχει δηλωθεί κατάσταση \"Σε άδεια\"<br>";
                  }
                  $ret = date("d-m-Y", strtotime($return));
              }
              else
              {
                  $ret="";
                  $id = mysqli_result($result, $i, "emp_id");
                  $flag=1;
                  $comments .= "Δεν απουσιάζει.<br>Έχει δηλωθεί κατάσταση \"Σε άδεια\"<br>";
              }
              if ($flag) {
                  $query1 = "select type from adeia_type where id=$type";
                  $result1 = mysqli_query($mysqlconnection, $query1);
                  $typewrd = mysqli_result($result1, 0, "type");
                  if ($absent && $status<>3) {
                      $comments .= "<blink>Παρακαλώ αλλάξτε την κατάσταση του <br>εκπ/κού σε \"Σε Άδεια\"</blink><br>$comm";
                  }

                  echo "<tr>";
                  echo "<td>".($i+1)."</td>";
                  echo "<td><a class='underline' href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$typewrd</td><td><a href='../employee/adeia.php?adeia=$adeia_id&op=view'>$ret</a></td><td>$comments</td>\n";
                  echo "</tr>";
              }
              $i++;
            }
            echo "</tbody></table>";
            echo "</div>";
        }
        //Αναπληρωτές σε άδεια
        $query = "SELECT *,e.type as emptype, e.name as empname, p.name as praxiname FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id join praxi p on p.id=e.praxi where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 3)";
        //echo $query;
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        $sx_yphrethshs = mysqli_result($result, 0, "sx_yphrethshs");
        if ($num) {
            echo "<h3>Αναπληρωτές</h3>";
            $i=0;
            echo "<table id=\"mytbl4\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Τύπος Απασχόλησης</th>";
            echo "<th>Πράξη</th>";
            echo "<th>Ώρες</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            while ($i < $num)
            {
                $id = mysqli_result($result, $i, 0);
                $name = mysqli_result($result, $i, "empname");
                $surname = mysqli_result($result, $i, "surname");
                $klados_id = mysqli_result($result, $i, "klados");
                $klados = getKlados($klados_id, $mysqlconnection);
                $typos = mysqli_result($result, $i, "emptype");
                $praxiname = mysqli_result($result, $i, "praxiname");
                $type = get_type($typos, $mysqlconnection);
                $comments = shorten_text(mysqli_result($result, $i, "comments"));
                $wres = mysqli_result($result, $i, "hours");
                
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a class='underline' href=\"../employee/ektaktoi.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$praxiname</td><td>$wres</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "</div>";
          }

        //Aπουσία COVID-19
        // $query = "SELECT *,e.id as empid FROM employee e join yphrethsh y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 5)";
        // $query_ekt = "SELECT *,e.id as empid FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 5)";
        // //echo $query;
        // $result = mysqli_query($mysqlconnection, $query);
        // $result_ekt = mysqli_query($mysqlconnection, $query_ekt);
        // $num = mysqli_num_rows($result);
        // $num_ekt = mysqli_num_rows($result_ekt);
        // //$sx_yphrethshs = mysqli_result($result, 0, "sx_yphrethshs");
        // if (($num + $num_ekt) > 0){
        //     echo "<h3>Απουσία COVID-19<span class='personnel-count'>".($num + $num_ekt)."</span></h3>";
        //     echo "<div>";
        //     if ($num > 0){
        //         echo "<h4>Μόνιμοι</h4>";
        //         echo "<table id=\"mytbl4\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
        //         echo "<thead><tr>";
        //         echo "<th>A/A</th>";
        //         echo "<th>Επώνυμο</th>";
        //         echo "<th>Όνομα</th>";
        //         echo "<th>Κλάδος</th>";
        //         echo "<th>Τύπος Απασχόλησης</th>";
        //         echo "<th>Ώρες</th>";
        //         echo "<th>Σχόλια</th>";
        //         echo "</tr></thead>\n<tbody>";
        //         while ($row = mysqli_fetch_assoc($result))
        //         {
        //             $id = $row['empid'];
        //             $name = $row['name'];
        //             $surname = $row['surname'];
        //             $klados_id = $row['klados'];
        //             $klados = getKlados($klados_id, $mysqlconnection);
        //             $typos = $row['type'];
        //             $type = get_type($typos, $mysqlconnection);
        //             $comments = shorten_text($row['comments']);
        //             $wres = $row['hours'];
                    
        //             echo "<tr>";
        //             echo "<td>".($i+1)."</td>";
        //             echo "<td><a class='underline' href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$wres</td><td>$comments</td>\n";
        //             echo "</tr>";
        //         }
        //         echo "</tbody></table>";
        //     }
        //     // ektaktoi
        //     if ($num_ekt > 0){
        //         echo "<h4>Αναπληρωτές</h4>";
        //         // $i=0;
        //         echo "<table id=\"mytbl4\" class=\"imagetable schooltable tablesorter\" border=\"2\">\n";
        //         echo "<thead><tr>";
        //         echo "<th>A/A</th>";
        //         echo "<th>Επώνυμο</th>";
        //         echo "<th>Όνομα</th>";
        //         echo "<th>Κλάδος</th>";
        //         echo "<th>Τύπος Απασχόλησης</th>";
        //         echo "<th>Ώρες</th>";
        //         echo "<th>Σχόλια</th>";
        //         echo "</tr></thead>\n<tbody>";
        //         while ($row = mysqli_fetch_assoc($result_ekt))
        //         {
        //             $id = $row['empid'];
        //             $name = $row['name'];
        //             $surname = $row['surname'];
        //             $klados_id = $row['klados'];
        //             $klados = getKlados($klados_id, $mysqlconnection);
        //             $typos = $row['type'];
        //             $type = get_type($typos, $mysqlconnection);
        //             $comments = shorten_text($row['comments']);
        //             $wres = $row['hours'];
                    
        //             echo "<tr>";
        //             echo "<td>".($i+1)."</td>";
        //             echo "<td><a class='underline' href=\"../employee/ektaktoi.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$wres</td><td>$comments</td>\n";
        //             echo "</tr>";
        //         }
        //         echo "</tbody></table>";
        //     }
        //     echo "</div>";
        // }
        echo "</div>"; // of personnel-accordion
        echo "</div>"; // of personnel tab
          
          // requests tab
          if ($_SESSION['requests'] && $schooltype != 0) {
            echo "<div id='requests'>";
              display_school_requests($sch, $sxol_etos, $mysqlconnection, true);
            echo "</div>";
          }
        echo "</div>"; // of tabs
      echo "</div>"; // of container

    } // of school status
    ?>
</center>
<div id='results'></div>
</body>
</html>
