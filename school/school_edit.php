<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once "../include/functions.php";
  require '../tools/calendar/tc_calendar.php';
  
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
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Επεξεργασία σχολείου</title>
    
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
    <script type="text/javascript" src="../js/jquery_notification_v.1.js"></script>
    <link href="../css/jquery_notification.css" type="text/css" rel="stylesheet"/> 
    <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
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
            $fax = mysqli_result($result, 0, "fax");
            $email = mysqli_result($result, 0, "email");
            $type = mysqli_result($result, 0, "type");
            $type2 = mysqli_result($result, 0, "type2");
            $organikothta = mysqli_result($result, 0, "organikothta");
            $leitoyrg = get_leitoyrgikothta($sch, $mysqlconnection);
            $anenergo = mysqli_result($result, 0, "anenergo");
                    
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

            echo "<table class=\"imagetable\" border='1'>";
            echo "<form id='updatefrm' name='update' action='school_update.php' method='POST'>";
            echo "<tr><td colspan=3>Τίτλος (αναλυτικά): <input type='text' name='titlos' value='$titlos' size='80'/></td></tr>";
            echo "<tr><td>Δ/νση: <input type='text' name='address' value='$address' /> T.K.: <input size='5' type='text' name='tk' value='$tk' /></td><td>Τηλ.: <input type='text' name='tel' value='$tel' /></td></tr>";
            echo "<tr><td>email: <input type='text' name='email' value='$email' size='30'/></a></td><td>Fax: <input type='text' name='fax' value='$fax' /></td></tr>";
            
            // Disable organikothta & organikes when not admin
            // $disabled = $_SESSION['userlevel'] > 1 ? 'disabled' : '';
            
            if ($type == 1 || $type == 2){
                echo "<tr><td>Οργανικότητα: <input type='text' name='organ' value='$organikothta' size='2' /></td>";
                echo "<td>Λειτουργικότητα: $leitoyrg</td></tr>";
            }
            // 05-10-2012 - organikes
            /*
            **** DS ****
            ΠΕ70: $organikes[0], ΠΕ11: $organikes[1], ΠΕ06: $organikes[2], ΠΕ79: $organikes[3], ΠΕ05: $organikes[4]
            ΠΕ07: $organikes[5], ΠΕ08: $organikes[6], ΠΕ86: $organikes[7], ΠΕ91: $organikes[8], ΠΕ21: $organikes[9]
            ΠΕ23: $organikes[10], ΠΕ25: $organikes[11], ΠΕ26: $organikes[12], ΠΕ28: $organikes[13], ΠΕ29: $organikes[14]
            ΠΕ30: $organikes[15],  ΔΕ1ΕΒΠ: $organikes[16], ΠΕ70ΕΑΕ: $organikes[17], ΠΕ71: $organikes[18]
            **** Nip ****
            ΠΕ60 => 0, ΠΕ21 => 1, ΠΕ23 => 2, ΠΕ25 => 3, ΠΕ26 => 4, ΠΕ28 => 5, ΠΕ29 => 6, ΠΕ30 => 7, ΔΕ1ΕΒΠ => 8, ΠΕ60ΕΑΕ => 9, ΠΕ61 => 10
            */
            // if Dimotiko
            if ($type == 1) {
                echo "<tr><td colspan=2>Οργανικές:<br>";
                if ($type2 != 2) {
                    echo "ΠΕ70: <input type='text' name='organikes[]' value='$organikes[0]' size='2' /><br>";
                } else {
                    echo "ΠΕ70 EAE: <input type='text' name='organikes[]' value='$organikes[0]' size='2' /><br>";
                }
                echo "ΠΕ11: <input type='text' name='organikes[]' value='$organikes[1]' size='2' /><br>";
                echo "ΠΕ06: <input type='text' name='organikes[]' value='$organikes[2]' size='2' /><br>";
                echo "ΠΕ79: <input type='text' name='organikes[]' value='$organikes[3]' size='2' /><br>";
                echo "ΠΕ05: <input type='text' name='organikes[]' value='$organikes[4]' size='2' /><br>";
                echo "ΠΕ07: <input type='text' name='organikes[]' value='$organikes[5]' size='2' /><br>";
                echo "ΠΕ08: <input type='text' name='organikes[]' value='$organikes[6]' size='2' /><br>";
                echo "ΠΕ86: <input type='text' name='organikes[]' value='$organikes[7]' size='2' /><br>";
                echo "ΠΕ91: <input type='text' name='organikes[]' value='$organikes[8]' size='2' /><br>";
                if ($type2 == 2) {
                  echo "ΠΕ21 (Λογοθεραπευτών): <input type='text' name='organikes[]' value='$organikes[9]' size='2' /><br>";
                  echo "ΠΕ23 (Ψυχολόγων): <input type='text' name='organikes[]' value='$organikes[10]' size='2' /><br>";
                  echo "ΠΕ25 (Σχ.Νοσηλευτών): <input type='text' name='organikes[]' value='$organikes[11]' size='2' /><br>";
                  echo "ΠΕ26 (Λογοθεραπευτών): <input type='text' name='organikes[]' value='$organikes[12]' size='2' /><br>";
                  echo "ΠΕ28 (Φυσικοθεραπευτών): <input type='text' name='organikes[]' value='$organikes[13]' size='2' /><br>";
                  echo "ΠΕ29 (Εργοθεραπευτών): <input type='text' name='organikes[]' value='$organikes[14]' size='2' /><br>";
                  echo "ΠΕ30 (Κοιν.Λειτουργών): <input type='text' name='organikes[]' value='$organikes[15]' size='2' /><br>";
                  echo "ΔΕ1ΕΒΠ: <input type='text' name='organikes[]' value='$organikes[16]' size='2' /><br>";
                }
            }
            // if Nip
            else if ($type == 2) {
                if ($type2 != 2){
                    echo "<tr><td colspan=2>Οργανικές: ΠΕ60: <input type='text' name='organikes[]' value='$organikes[0]' size='2' />";
                } else {
                    echo "<tr><td colspan=2>Οργανικές: ΠΕ60 EAE: <input type='text' name='organikes[]' value='$organikes[0]' size='2' />";
                }
                if ($type2 == 2) {
                  echo "<br>ΠΕ21 (Λογοθεραπευτών): <input type='text' name='organikes[]' value='$organikes[1]' size='2' /><br>";
                  echo "ΠΕ23 (Ψυχολόγων): <input type='text' name='organikes[]' value='$organikes[2]' size='2' /><br>";
                  echo "ΠΕ25 (Σχ.Νοσηλευτών): <input type='text' name='organikes[]' value='$organikes[3]' size='2' /><br>";
                  echo "ΠΕ26 (Λογοθεραπευτών): <input type='text' name='organikes[]' value='$organikes[4]' size='2' /><br>";
                  echo "ΠΕ28 (Φυσικοθεραπευτών): <input type='text' name='organikes[]' value='$organikes[5]' size='2' /><br>";
                  echo "ΠΕ29 (Εργοθεραπευτών): <input type='text' name='organikes[]' value='$organikes[6]' size='2' /><br>";
                  echo "ΠΕ30 (Κοιν.Λειτουργών): <input type='text' name='organikes[]' value='$organikes[7]' size='2' /><br>";
                  echo "ΔΕ1ΕΒΠ: <input type='text' name='organikes[]' value='$organikes[8]' size='2' /><br>";
                }
            }
            echo "</td></tr>";
            // pe05 - pe07
            if ($type == 1 ){
                echo "<tr><td colspan=2>";
                echo "Ώρες ΠΕ05: <input type='text' name='tmpe05' size='2' value='$pe0507[0]' />&nbsp;Ανάλυση ΠΕ05: ";
                echo "<span title='Αναλυτικά οι ώρες, π.χ.10 ώρες -> 4-4-2 στην ανάλυση'><img style='border: 0pt none;' src='../images/info.png'></span>&nbsp;";
                echo "<input type='text' name='tmpe05b' size='6' value='$pe0507[1]' <br><br>";
                echo "Ώρες ΠΕ07: <input type='text' name='tmpe07' size='2' value='$pe0507[2]' />&nbsp;Ανάλυση ΠΕ07: ";
                echo "<span title='Αναλυτικά οι ώρες, π.χ.10 ώρες -> 4-4-2 στην ανάλυση'><img style='border: 0pt none;' src='../images/info.png'></span>&nbsp;";
                echo "<input type='text' name='tmpe07b' size='6' value='$pe0507[3]'";
                echo "</tr>";
            }
            if ($type == 1 || $type == 2){
                echo "<tr>";
                if ($entaksis[0]) {
                    echo "<td><input type=\"checkbox\" name='entaksis' checked >Τμήμα Ένταξης / Μαθητές: <input type='text' name='entaksis_math' value='$entaksis[1]' size='2'/></td>";
                } else {
                    echo "<td><input type=\"checkbox\" name='entaksis'>Τμήμα Ένταξης / Μαθητές: <input type='text' name='entaksis_math' value='$entaksis[1]' size='2'/></td>";
                }
                if ($ypodoxis) {
                    echo "<td><input type=\"checkbox\" name='ypodoxis' checked >Τμήμα Υποδοχής</td>";
                } else {
                    echo "<td><input type=\"checkbox\" name='ypodoxis' >Τμήμα Υποδοχής</td>";
                }
                echo "</tr>";
                echo "<tr><td>Εκπ/κοί Τμ.Ένταξης: <input type='text' name='ekp_te' size='1' value='$ekp_ee_exp[0]' /></td><td colspan=3>Εκπ/κοί Τμ.Υποδοχής: <input type='text' name='ekp_ty' size='1' value='$ekp_ee_exp[1]' /></td></tr>";
                echo "<tr>";
            }
                    
                    
            if ($type == 1) {
                echo $frontistiriako ? 
                    "<td><input type=\"checkbox\" name='frontistiriako' checked >Φροντιστηριακό Τμήμα</td>" :
                    "<td><input type=\"checkbox\" name='frontistiriako' >Φροντιστηριακό Τμήμα</td>";
                echo $oloimero ? 
                    "<td><input type=\"checkbox\" name='oloimero' checked >Ολοήμερο</td>" : 
                    "<td><input type=\"checkbox\" name='oloimero' >Όλοήμερο</td>";
                echo "</tr>";
                        
                echo "<tr>";
                echo $ted ? 
                    "<td><input type=\"checkbox\" name='ted' checked >Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</td>" :
                    "<td><input type=\"checkbox\" name='ted' >Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</td>";
                //echo "<td></td>";
                echo $anenergo ? 
                    "<td><input type=\"checkbox\" name='anenergo' checked >Ανενεργό</td>" :
                    "<td><input type=\"checkbox\" name='anenergo' >Ανενεργό</td>";
                echo "</tr>";
                
                echo "<td colspan=2><input type=\"checkbox\" name='vivliothiki' id='vivliothiki'";
                echo $vivliothiki ? 'checked' : '';
                echo " onchange='valueChanged()'>Σχολική βιβλιοθήκη&nbsp;&nbsp;";
                echo "<div id='workerdiv'";
                echo $vivliothiki ? '' : " style='display:none;'";
                echo ">";
                echo "Υπεύθυνος/-η: ";
                workersCmb($vivliothiki, $sch, $mysqlconnection);
                echo "</div>";
                echo "<tr>";
                echo "<td>Πρωινή Ζώνη:&nbsp;";
                $proinizoni = unserialize(mysqli_result($result, 0, "proinizoni"));
                workersMultiCmb($proinizoni, $sch, $mysqlconnection,'proinizoni');
                echo "</td><td>";
                echo "</td></tr>";
                echo "</td>";
                        
                echo "<tr><td colspan=2>Σχόλια: <textarea rows='4' cols='80' name='comments'>$comments</textarea></td></tr>";
                //echo $disabled ? "<tr><td colspan=2><small>ΣΗΜ.: Μόνο ο διαχειριστής μπορεί να αλλάξει τα απενεργοποιημένα πεδία.</small></td></tr>" : '';
                echo "</table>";
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
                echo "<br>";

                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td colspan=8>Σύνολο Μαθητών Πρ.: $synolo</td></tr>";
                echo "<tr><td>Α'</td><td>Β'</td><td>Γ'</td><td>Δ'</td><td>Ε'</td><td>ΣΤ'</td><td>Ολ.<small>(15.00/16.00)</small></td><td>ΠΖ</td></tr>";
                if ($synolo>0) {
                    echo "<tr><td><input type='text' name='a' size='1' value=$classes[0] /></td><td><input type='text' name='b' size='1' value=$classes[1] /></td><td><input type='text' name='c' size='1' value=$classes[2] /></td><td><input type='text' name='d' size='1' value=$classes[3] /></td><td><input type='text' name='e' size='1' value=$classes[4] /></td><td><input type='text' name='f' size='1' value=$classes[5] /></td><td><input type='text' name='g' size='3' value=$classes[6] /></td><td><input type='text' name='h' size='1' value=$classes[7] /></td></tr>";
                } else {
                    echo "<tr><td><input type='text' name='a' size='1' value='0' /></td><td><input type='text' name='b' size='1' value='0' /></td><td><input type='text' name='c' size='1' value='0' /></td><td><input type='text' name='d' size='1' value='0' /></td><td><input type='text' name='e' size='1' value='0' /></td><td><input type='text' name='f' size='1' value='0' /></td><td><input type='text' name='g' size='3' value='0' /></td><td><input type='text' name='h' size='1' value='0' /></td></tr>";
                }
                echo "<tr><td colspan=8>Τμήματα (Εκπαιδευτικοί) ανά τάξη<br>Σύνολο Τμημάτων Πρωινού: $synolo_tmim</td></tr>";
                if ($synolo>0) {
                    echo "<tr><td><input type='text' name='ta' size='1' value=$tmimata_exp[0] /></td><td><input type='text' name='tb' size='1' value=$tmimata_exp[1] /></td>";
                    echo "<td><input type='text' name='tc' size='1' value=$tmimata_exp[2] /></td><td><input type='text' name='td' size='1' value=$tmimata_exp[3] /></td>";
                    echo "<td><input type='text' name='te' size='1' value=$tmimata_exp[4] /></td><td><input type='text' name='tf' size='1' value=$tmimata_exp[5] /></td>";
                    echo "<td>15.00&nbsp;<input type='text' name='tg' size='1' value=$tmimata_exp[6] />16.00&nbsp;<input type='text' name='th' size='1' value=$tmimata_exp[7] />17.30&nbsp;<input type='text' name='tj' size='1' value=$tmimata_exp[9] /></td><td><input type='text' name='ti' size='1' value=$tmimata_exp[8] /></td></tr>";
                } else {
                    echo "<tr><td><input type='text' name='ta' size='1' value='0' /></td><td><input type='text' name='tb' size='1' value='0' /></td>";
                    echo "<td><input type='text' name='tc' size='1' value='0' /></td><td><input type='text' name='td' size='1' value='0' /></td>";
                    echo "<td><input type='text' name='te' size='1' value='0' /></td><td><input type='text' name='tf' size='1' value='0' /></td>";
                    echo "<td><input type='text' name='tg' size='1' value='0' /></td><td><input type='text' name='th' size='1' value='0' /></td></tr>";
                }
                echo '<tr><td colspan=8><small>ΣΗΜ. Για 4/θέσια συμπληρώνουμε τμήματα στις τάξεις Α,Β,Γ,Ε & για 5/θέσια Α,Β,Γ,Ε,ΣΤ</small></td></tr>';
            }
            // if nip
            else if ($type == 2) {
                if ($oloimero) {
                    echo "<td><input type=\"checkbox\" name='oloimero' checked >Ολοήμερο</td>";
                } else {
                    echo "<td><input type=\"checkbox\" name='oloimero'>Όλοήμερο</td>";
                }
                echo "<td>Μαθητές Πρωινής Ζώνης&nbsp;&nbsp;<input type='text' name='pz' size='1' value=$klasiko_exp[6]></td></tr>";
                echo "<tr><td>Σχόλια: <textarea rows='4' cols='80' name='comments'>$comments</textarea></td>";
                echo $anenergo ? 
                    "<td><input type=\"checkbox\" name='anenergo' checked >Ανενεργό</td>" :
                    "<td><input type=\"checkbox\" name='anenergo' >Ανενεργό</td>";
                echo "</tr>";
                echo  $disabled ? "<tr><td colspan=2><small>ΣΗΜ.: Μόνο ο διαχειριστής μπορεί να αλλάξει τα απενεργοποιημένα πεδία.</small></td></tr>" : '';
                echo "</table>";
                echo "<br>";
                /////////    
                echo "<h3>Μαθητές</h3><br>";
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td rowspan=2>Τμήμα</td><td colspan=2>Κλασικό</td><td colspan=2>Ολοήμερο</td></tr>";
                echo "<tr><td>Νήπια</td><td>Προνήπια</td><td>Νήπια</td><td>Προνήπια</td></tr>";
                // t1
                echo "<tr><td>Τμ.1</td><td><input type='text' name='k1a' size='1' value=$klasiko_exp[0]></td><td><input type='text' name='k1b' size='1' value=$klasiko_exp[1]>";
                echo "<td><input type='text' name='o1a' size='1' value=$oloimero_nip_exp[0]></td><td><input type='text' name='o1b' size='1' value=$oloimero_nip_exp[1]></td></tr>";
                // t2
                echo "<tr><td>Τμ.2</td><td><input type='text' name='k2a' size='1' value=$klasiko_exp[2]></td><td><input type='text' name='k2b' size='1' value=$klasiko_exp[3]>";
                echo "<td><input type='text' name='o2a' size='1' value=$oloimero_nip_exp[2]></td><td><input type='text' name='o2b' size='1' value=$oloimero_nip_exp[3]></td></tr>";
                // t3
                echo "<tr><td>Τμ.3</td><td><input type='text' name='k3a' size='1' value=$klasiko_exp[4]></td><td><input type='text' name='k3b' size='1' value=$klasiko_exp[5]>";
                echo "<td><input type='text' name='o3a' size='1' value=$oloimero_nip_exp[4]></td><td><input type='text' name='o3b' size='1' value=$oloimero_nip_exp[5]></td></tr>";
                // t4
                echo "<tr><td>Τμ.4</td><td><input type='text' name='k4a' size='1' value=$klasiko_exp[7]></td><td><input type='text' name='k4b' size='1' value=$klasiko_exp[8]>";
                echo "<td><input type='text' name='o4a' size='1' value=$oloimero_nip_exp[6]></td><td><input type='text' name='o4b' size='1' value=$oloimero_nip_exp[7]></td></tr>";
                // t5
                echo "<tr><td>Τμ.5</td><td><input type='text' name='k5a' size='1' value=$klasiko_exp[9]></td><td><input type='text' name='k5b' size='1' value=$klasiko_exp[10]>";
                echo "<td><input type='text' name='o5a' size='1' value=$oloimero_nip_exp[8]></td><td><input type='text' name='o5b' size='1' value=$oloimero_nip_exp[9]></td></tr>";
                // t6
                echo "<tr><td>Τμ.6</td><td><input type='text' name='k6a' size='1' value=$klasiko_exp[11]></td><td><input type='text' name='k6b' size='1' value=$klasiko_exp[12]>";
                echo "<td><input type='text' name='o6a' size='1' value=$oloimero_nip_exp[10]></td><td><input type='text' name='o6b' size='1' value=$oloimero_nip_exp[11]></td></tr>";
                // t7
                echo "<tr><td>Τμ.7</td><td><input type='text' name='k7a' size='1' value=$klasiko_exp[13]></td><td><input type='text' name='k7b' size='1' value=$klasiko_exp[14]>";
                echo "<td><input type='text' name='o7a' size='1' value=$oloimero_nip_exp[12]></td><td><input type='text' name='o7b' size='1' value=$oloimero_nip_exp[13]></td></tr>";
                // t8
                echo "<tr><td>Τμ.8</td><td><input type='text' name='k8a' size='1' value=$klasiko_exp[15]></td><td><input type='text' name='k8b' size='1' value=$klasiko_exp[16]>";
                echo "<td><input type='text' name='o8a' size='1' value=$oloimero_nip_exp[14]></td><td><input type='text' name='o8b' size='1' value=$oloimero_nip_exp[15]></td></tr>";
                echo "</table>";
                
                // Oloimero dieyrymenoy (16-17.30)
                // Tmimata: oloimero_nip_exp[16] / Mathites: oloimero_nip_exp[17]
                echo "<h4>Ολοήμερο διευρυμένου προγράμματος (16.00-17.30)</h4>";
                echo "<table class=\"imagetable\" border='1' style='width:20%;'>";
                echo "<thead><th>Τμήματα</th><th>Μαθητές</th></thead>";
                echo "<tr><td><input type='text' name='o9a' size='3' value=$oloimero_nip_exp[16]></td><td><input type='text' name='o9b' size='3' value=$oloimero_nip_exp[17]>";
                echo "</table>";
                echo "<br>";

                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td colspan=3>Νηπιαγωγοί</td></tr>";
                echo "<tr><td>Κλασικό</td><td>Ολοήμερο</td><td>Τμ.Ένταξης</td></tr>";
                echo "<tr><td><input type='text' name='ekp_kl' size='1' value=$nip_exp[0]></td><td><input type='text' name='ekp_ol' size='1' value=$nip_exp[1]></td><td><input type='text' name='ekp_te' size='1' value=$nip_exp[2]></td></tr>";
                echo "</table>";
            }
                    
            echo "</table>";
            echo "<br>";
                    
            echo "	<input type='hidden' name = 'sch' value='$sch'>";
            echo "	<input type='hidden' name = 'name' value='$str1'>";
                    
            echo "<input type='submit' value='Αποθήκευση'>";
            echo "</form>";
            $schLink = "school_status.php?org=$sch";
            echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='$schLink'\">";
                
        }
        ?>
        </center>
        <div id='results'></div>
        </body>
        </html>
