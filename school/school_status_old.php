<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once "../config.php";
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
    <title>Καρτέλα σχολείου</title>
    
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
    <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
    <script type="text/javascript">
    
    $().ready(function() {
        $("#org").autocomplete("../employee/get_school.php", {
            width: 260,
            matchContains: true,
            selectFirst: false
        });
        $("#slidingDiv").hide();
        $("#slidingDiv2").hide();
        $('#show_hide').click(function(){
            $("#slidingDiv").slideToggle();
        });
        $('#show_hide2').click(function(){
            $("#slidingDiv2").slideToggle();
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
        $fax = mysqli_result($result, 0, "fax");
        $email = mysqli_result($result, 0, "email");
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
            $klasiko_exp = explode(",", $klasiko);
            $oloimero_nip = mysqli_result($result, 0, "oloimero_nip");
            $oloimero_nip_exp = explode(",", $oloimero_nip);
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
        
        echo "<table class=\"imagetable\" border='1'>";
        echo "<tr><td colspan=2>Τύπος: ".get_school_type($sch, $conn)."</td></tr>";
        echo "<tr><td colspan=3>Τίτλος (αναλυτικά): $titlos</td></tr>";
        echo "<tr><td>Δ/νση: $address - Τ.Κ. $tk - Δήμος: $dimos</td><td>Τηλ.: $tel</td></tr>";
        echo "<tr><td>email: <a href=\"mailto:$email\">$email</a></td><td>Fax: $fax</td></tr>";
        echo "<tr><td>Οργανικότητα: $organikothta</td><td>Λειτουργικότητα: $leitoyrg</td></tr>";
        
        // οργανικά τοποθετηθέντες
        $klados_qry = ($type == 1) ? 2 : 1;
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados= $klados_qry AND status IN (1,3,5) AND thesi IN (0,1,2)";
        $rs = mysqli_query($conn, $qry);
        $orgtop = mysqli_result($rs, 0, "cnt");
        echo "<tr><td>Οργανικά τοποθετηθέντες (πλην Τ.Ε.): $orgtop</td><td colspan=3>Κατηγορία: $cat</td></tr>";
        
        // 05-10-2012 - organikes
        for ($i=0; $i<count($organikes); $i++) {
            if (!$organikes[$i]) {
                $organikes[$i]=0;
            }
        }
        
        // if ds
        if ($type == 1) {
            echo "<tr><td colspan=2><a href='#' id='show_hide'>Οργανικές</a><br>";
            echo "<div id='slidingDiv'>";
            echo "<table>";
            echo "<thead><tr>";
            echo "<th>Κλάδος</th>";
            echo "<th><span title='Δασκάλων'>70</th>";
            echo "<th><span title='Φυσικής Αγωγής'>11</th>";
            echo "<th><span title='Αγγλικών'>06</th>";
            echo "<th><span title='Μουσικών'>79</th>";
            echo "<th><span title='Γαλλικών'>05</th>";
            echo "<th><span title='Γερμανικών'>07</th>";
            echo "<th><span title='Καλλιτεχνικών'>08</th>";
            echo "<th><span title='Πληροφορικής'>86</th>";
            echo "<th><span title='Θεατρικών Σπουδών'>91</th>";
            echo $org_ent ? "<th>Ένταξης</th>" : '';
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
        else {
            echo "<tr><td colspan=2>Οργανικές: ΠΕ60: $organikes[0]";
            if ($type2 == 2) {
              echo "<table>";
              echo "<thead><tr>";
                echo "<th>Κλάδος</th>";
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
                echo "<td>Οργανικές</td>";
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
                echo "<td>Οργ.ανήκοντες</td>";
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
                echo "<td>Οργ.Κενά</td>";
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
            }
        }
        
        echo "</td></tr>";
        // 05-10-2012 - kena_leit, kena_org
        for ($i=0; $i<count($kena_org); $i++) {
            if (!$kena_org[$i]) {
                $kena_org[$i]=0;
            }
        }
        if ($type == 1) {
            // echo "<tr>";
            // echo "<td>Οργανικά κενά</td>";
            // echo "<td>$kena_org[0]</td>";
            // echo "<td>$kena_org[1]</td>";
            // echo "<td>$kena_org[2]</td>";
            // echo "<td>$kena_org[3]</td>";
            // echo "<td>$kena_org[4]</td>";
            // echo "<td>$kena_org[5]</td>";
            // echo "<td>$kena_org[6]</td>";
            // echo "<td>$kena_org[7]</td>";
            // echo "<td>$kena_org[8]</td>";
            // echo "</tr>";
            ///////
            echo "<tr>";
            $orgs = get_orgs($sch,$conn);
            echo "<td>Οργανικά ανήκοντες</td>";
            echo "<td>".$orgs['ΠΕ70']."</td>";
            echo "<td>".$orgs['ΠΕ11']."</td>";
            echo "<td>".$orgs['ΠΕ06']."</td>";
            echo "<td>".$orgs['ΠΕ79']."</td>";
            echo "<td>".$orgs['ΠΕ05']."</td>";
            echo "<td>".$orgs['ΠΕ07']."</td>";
            echo "<td>".$orgs['ΠΕ08']."</td>";
            echo "<td>".$orgs['ΠΕ86']."</td>";
            echo "<td>".$orgs['ΠΕ91']."</td>";
            echo $org_ent ? "<td>".$orgs['ent']."</td>" : '';
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
            echo "<td>".($organikes[0] - $orgs['ΠΕ70'])."</td>";
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
            echo "</div>";
            // echo "&nbsp;&nbsp;&nbsp;ΠΕ11: ".($organikes[1] - $orgs['ΠΕ11']);
            // echo "&nbsp;&nbsp;ΠΕ06: ".($organikes[2] - $orgs['ΠΕ06']);
            // echo "&nbsp;&nbsp;ΠΕ79: ".($organikes[3] - $orgs['ΠΕ79']);
            // echo "&nbsp;&nbsp;ΠΕ05: ".($organikes[4] - $orgs['ΠΕ05']);
            // echo "&nbsp;&nbsp;ΠΕ07: ".($organikes[5] - $orgs['ΠΕ07']);
            // echo "&nbsp;&nbsp;ΠΕ08: ".($organikes[6] - $orgs['ΠΕ08']);
            // echo "&nbsp;&nbsp;ΠΕ86: ".($organikes[7] - $orgs['ΠΕ86']);
            // echo "&nbsp;&nbsp;ΠΕ91: ".($organikes[8] - $orgs['ΠΕ91']);
            // echo "</td></tr>";

        }
        else {
            echo "<tr><td colspan=2>Οργ. Κενά: ΠΕ60: $kena_org[0]";
        }
        echo "</td></tr>";

        if ($entaksis[0]) {
            echo "<td><input type=\"checkbox\" checked disabled>Τμήμα Ένταξης / Μαθητές: $entaksis[1]</td>";
        } else {
            echo "<td><input type=\"checkbox\" disabled>Τμήμα Ένταξης</td>";
        }
        if ($ypodoxis) {
            echo "<td><input type=\"checkbox\" checked disabled>Τμήμα Υποδοχής</td>";
        } else {
            echo "<td><input type=\"checkbox\" disabled>Τμήμα Υποδοχής</td>";
        }
        echo "</tr>";
        if ($entaksis[0] || $ypodoxis) {
            echo "<tr><td>Εκπ/κοί Τμ.Ένταξης: $ekp_ee_exp[0]</td><td>Εκπ/κοί Τμ.Υποδοχής: $ekp_ee_exp[1]</td></tr>";
        }

        echo "<tr>";
        if ($type == 1) {
            if ($frontistiriako) {
                echo "<td><input type=\"checkbox\" checked disabled>Φροντιστηριακό Τμήμα</td>";
            } else {
                echo "<td><input type=\"checkbox\" disabled>Φροντιστηριακό Τμήμα</td>";
            }
        }
        // if nip print Proini Zoni (klasiko[6])
        else {
            if ($klasiko_exp[6]) {
                echo "<td><input type=\"checkbox\" checked disabled>Πρωινή Ζώνη / Μαθητές: $klasiko_exp[6]</td>";
            } else {
                echo "<td><input type=\"checkbox\" disabled>Πρωινή Ζώνη</td>";
            }
        }
                                            
        if ($oloimero) {
            if ($type == 1) {
                echo "<td><input type=\"checkbox\" checked disabled>Όλοήμερο</td></tr>";
                //echo "<tr><td>Μαθητές Ολοημέρου: $oloimero_stud</td>";
                //echo "<td>Εκπ/κοί Ολοημέρου: $oloimero_tea</td></tr>";
            }
            else {
                echo "<td><input type=\"checkbox\" checked disabled>Όλοήμερο</td></tr>";
            }
        }
        else {
            echo "<td><input type=\"checkbox\" disabled>Όλοήμερο</td></tr>";
        }
        
        if ($type == 1) {
            echo "<tr>";
            if ($ted) {
                echo "<td><input type=\"checkbox\" checked disabled>Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</td>";
            } else {
                echo "<td><input type=\"checkbox\" disabled>Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</td>";
            }
            if ($vivliothiki) {
                echo "<td><input type=\"checkbox\" checked disabled>Σχολική βιβλιοθήκη";
                $qry1 = "SELECT surname,name,perigrafh from employee e JOIN klados k ON e.klados = k.id WHERE e.id=$vivliothiki";
                $res1 = mysqli_query($conn, $qry1);
                if ($row = mysqli_fetch_assoc($res1)){
                  echo "<i><small> (Υπευθυνος/-η: ".$row['surname'].' '.$row['name'].', '.$row['perigrafh'].')</small></i>';
                } else {
                  echo '<i><small> (Δεν έχει οριστεί υπεύθυνος βιβλιοθήκης)</small></i>';
                }
                echo '</td>';
            } else {
                echo "<td><input type=\"checkbox\" disabled>Σχολική βιβλιοθήκη</td>";
            }
            echo "</tr>";
            echo "<tr><td>Περιφέρεια Σχολικών Συμβούλων: ".$perif."η</td>";
            echo $anenergo ? "<td>Κατάσταση: Σε αναστολή</td>" : "<td>Κατάσταση: Ενεργό</td>";
            echo "</tr>";
        }
        echo $anenergo && $type == 2 ? "<tr><td>Κατάσταση: Σε αναστολή</td><td></td>" : "<td>Κατάσταση: Ενεργό</td><td></td></tr>";
        echo "<tr><td>Σχόλια: ".nl2br($comments)."</td><td>Κωδικός ΥΠΑΙΘ: $code</td></tr>";
        if ($systeg) {
            echo "<tr><td colspan=2>Συστεγαζόμενη σχολική μονάδα: <a href='school_status.php?org=$systeg' target='_blank'>$systegName</td></tr>";    
        }
        if ($updated>0) {
            echo "<tr><td colspan=2 align=right><small>Τελ.ενημέρωση: ".date("d-m-Y H:i", strtotime($updated))."<small></td></tr>";
        }
        echo "</table>";
        echo "<br>";
        
        
        if ($type == 1) {
            if ($synolo>0) {
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td></td><td>Α'</td><td>Β'</td><td>Γ'</td><td>Δ'</td><td>Ε'</td><td>ΣΤ'</td><td class='tdnone'><i>Ολ</i></td><td class='tdnone'><i>ΠΖ</i></td></tr>";
                $synolo_pr = $classes[0]+$classes[1]+$classes[2]+$classes[3]+$classes[4]+$classes[5];
                echo "<tr><td>Μαθ.Πρωινού<br>Σύνολο: $synolo_pr</td><td>$classes[0]</td><td>$classes[1]</td><td>$classes[2]</td><td>$classes[3]</td><td>$classes[4]</td><td>$classes[5]</td><td class='tdnone'><i>$classes[6]</i></td><td class='tdnone'><i>$classes[7]</i></td></tr>";
                $synolo_pr = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
                echo "<tr><td>Τμ./τάξη Πρωινού<br>Σύνολο: $synolo_pr</td><td>$tmimata_exp[0]</td><td>$tmimata_exp[1]</td><td>$tmimata_exp[2]</td><td>$tmimata_exp[3]</td><td>$tmimata_exp[4]</td><td>$tmimata_exp[5]</td><td class='tdnone'><i>$tmimata_exp[6]<small> (14-15)</small><br>$tmimata_exp[7]<small> (15-16)</small></i></td><td class='tdnone'><i>$tmimata_exp[8]</i></td></tr>";
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
            // klasiko pos 0-5: 0,1 t1n,p / 2,3 t2n,p / 4,5 t3n,p
            // prwinh zvnh @ pos 7 -> klasiko[6]
            // oloimero_syn_nip/pro: oloimero
            $klasiko_nip = $klasiko_exp[0] + $klasiko_exp[2] + $klasiko_exp[4];
            $klasiko_pro = $klasiko_exp[1] + $klasiko_exp[3] + $klasiko_exp[5];
            $oloimero_syn_nip = $oloimero_nip_exp[0] + $oloimero_nip_exp[2] + $oloimero_nip_exp[4] + $oloimero_nip_exp[6];
            $oloimero_syn_pro = $oloimero_nip_exp[1] + $oloimero_nip_exp[3] + $oloimero_nip_exp[5] + $oloimero_nip_exp[7];
            $meikto_nip = $klasiko_exp[6] + $klasiko_exp[8];
            $meikto_pro = $klasiko_exp[7] + $klasiko_exp[9];
            // Μαθητές
            echo "<h3>Μαθητές</h3>";
            echo "<table class=\"imagetable\" border='1'>";
            $ola = $klasiko_nip + $klasiko_pro;
            $olola = $oloimero_syn_nip + $oloimero_syn_pro;
            echo "<tr><td rowspan=2>Τμήμα</td><td colspan=3>Κλασικό</small></td><td colspan=3>Ολοήμερο</td></tr>";
            echo "<tr><td>Νήπια</td><td>Προνήπια</td><td>Σύνολο</td><td>Νήπια</td><td>Προνήπια</td><td>Σύνολο</td></tr>";
            // t1
            $syn = $klasiko_exp[0]+$klasiko_exp[1];
            $tmimata_nip = 1;
            $tmimata_nip_ol = 0;
            echo "<tr><td>Τμ.1</td><td>$klasiko_exp[0]</td><td>$klasiko_exp[1]</td><td>$syn</td>";
            $syn_ol = $oloimero_nip_exp[0]+$oloimero_nip_exp[1];
            if ($syn_ol > 0) {
                $tmimata_nip_ol += 1;
            }
            echo "<td>$oloimero_nip_exp[0]</td><td>$oloimero_nip_exp[1]</td><td>$syn_ol</td></tr>";
            // print t2 + t3 only if they have students
            // t2
            $syn2 = $klasiko_exp[2]+$klasiko_exp[3];
            $syn_ol2 = $oloimero_nip_exp[2]+$oloimero_nip_exp[3];
            if (($syn2+$syn_ol2) > 0) {
                $tmimata_nip += 1;
                if ($syn_ol2 > 0) {
                    $tmimata_nip_ol += 1;
                }
                echo "<tr><td>Τμ.2</td><td>$klasiko_exp[2]</td><td>$klasiko_exp[3]</td><td>$syn2</td>";
                echo "<td>$oloimero_nip_exp[2]</td><td>$oloimero_nip_exp[3]</td><td>$syn_ol2</td></tr>";
            }
            // t3
            $syn3 = $klasiko_exp[4]+$klasiko_exp[5];
            $syn_ol3 = $oloimero_nip_exp[4]+$oloimero_nip_exp[5];
            if (($syn3+$syn_ol3) > 0) {
                $tmimata_nip += 1;
                if ($syn_ol3 > 0) {
                    $tmimata_nip_ol += 1;
                }
                echo "<tr><td>Τμ.3</td><td>$klasiko_exp[4]</td><td>$klasiko_exp[5]</td><td>$syn3</td>";
                echo "<td>$oloimero_nip_exp[4]</td><td>$oloimero_nip_exp[5]</td><td>$syn_ol3</td></tr>";
            }
            // totals (if more than one tmima)
            if (($syn2 + $syn_ol2 + $syn3 + $syn_ol3) > 0) {
                echo "<tr><td><strong>Σύνολα</strong></td><td>$klasiko_nip<td>$klasiko_pro</td><td>$ola</td>";
                echo "<td>$oloimero_syn_nip<td>$oloimero_syn_pro</td><td>$olola</td>";
                echo "</tr>";
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
            
            $has_entaxi = strlen($entaksis[0])>1 ? 1 : 0; 
            // τοποθετημένοι εκπ/κοί
            $top60 = $top60m = $top60ana = 0;
            $qry = "SELECT count(*) as pe60 FROM employee WHERE sx_yphrethshs = $sch AND klados=1 AND status=1";
            $res = mysqli_query($conn, $qry);
            $top60m = mysqli_result($res, 0, 'pe60');
            $qry = "SELECT count(*) as pe60 FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados=1 AND status=1";
            $res = mysqli_query($conn, $qry);
            $top60ana = mysqli_result($res, 0, 'pe60');
            $top60 = $top60m+$top60ana;
            
            $syn_apait = $tmimata_nip+$tmimata_nip_ol+$has_entaxi;

            echo "<h3>Λειτουργικά κενά</h3>";
            echo "<table class=\"imagetable stable\" border='1'>";
            echo "<thead><th></th><th>Αριθμός</th><th>Κλασικό</th><th>Ολοήμερο</th><th>Τμ.Ένταξης</th></thead><tbody>";

            echo "<tr><td>Απαιτούμενοι Νηπιαγωγοί</td>";
            echo "<td>$syn_apait</td>";
            echo "<td>$tmimata_nip</td><td>$tmimata_nip_ol</td><td>$has_entaxi</td></tr>";

            echo "<tr><td>Υπάρχοντες Νηπιαγωγοί</td><td>$top60</td><td></td><td></td><td></td></tr>";
            $k_pl = $top60-$syn_apait;
            $k_pl_class = $k_pl >= 0 ? 
                "'background:none;background-color:rgba(0, 255, 0, 0.37)'" : 
                "'background:none;background-color:rgba(255, 0, 0, 0.45)'";
            echo "<tr><td>+ / -</td><td style=$k_pl_class>$k_pl</td><td></td><td></td><td></td></tr>";

            echo "</tbody></table>";
            echo "<br>";
        }
        echo "<INPUT TYPE='button' VALUE='Επεξεργασία' onClick=\"parent.location='school_edit.php?org=$sch'\">";
        echo "&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Εκδρομές' onClick=\"parent.location='ekdromi.php?sch=$sch&op=list'\">";
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
        if (!$sch && !$str) {
            die('Το σχολείο δε βρέθηκε...');
        }
        if (isset($_GET['sxoletos'])) {
            $sxol_etos = $_GET['sxoletos'];
        }
        disp_school($sch, $sxol_etos, $mysqlconnection);
        
        //Υπηρετούν με θητεία
        $query = "SELECT * from employee WHERE sx_yphrethshs='$sch' AND status=1 AND thesi in (1,2,6) ORDER BY thesi DESC";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Υπηρετούν με θητεία</h3><br>";
            
            $i=0;
            echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
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
                $comments = mysqli_result($result, $i, "comments");

                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$th</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }                   
        //Ανήκουν οργανικά και υπηρετούν (ΠΕ60-70)
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0";
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0 ORDER BY klados";
        $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,5) AND ent_ty=0 AND (klados=2 OR klados=1)";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Ανήκουν οργανικά και υπηρετούν (ΠΕ60/ΠΕ70)</h3>";
            $i=0;
            echo "<table id=\"mytbl2\" class=\"imagetable tablesorter\" border=\"2\">\n";
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
                $comments = mysqli_result($result, $i, "comments");
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        //Ανήκουν οργανικά και υπηρετούν (Ειδικότητες)
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0";
        //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0 ORDER BY klados";
        $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,5) AND klados!=2 AND klados!=1";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Ανήκουν οργανικά και υπηρετούν (Ειδικότητες)</h3>";
            $i=0;
            echo "<table id=\"mytbl2\" class=\"imagetable tablesorter\" border=\"2\">\n";
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
                $comments = mysqli_result($result, $i, "comments");

                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        
        
        // Οργανική αλλού και υπηρετούν
        $query = "SELECT * from employee WHERE sx_organikhs!='$sch' AND sx_yphrethshs='$sch' AND thesi in (0,5)  AND ent_ty=0 AND status=1 ORDER BY klados";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Με οργανική σε άλλο σχολείο και υπηρετούν</h3>";
            $i=0;
            echo "<table id=\"mytbl3\" class=\"imagetable tablesorter\" border=\"2\">\n";
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
                $comments = mysqli_result($result, $i, "comments");
                
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        
        // Οργανική αλλού και δευτερεύουσα υπηρέτηση
        //$query = "SELECT * from employee WHERE sx_organikhs!='$sch' AND (sx_yphrethshs='$sch' AND thesi=0";
        $query = "SELECT * FROM employee e join yphrethsh y on e.id = y.emp_id where y.yphrethsh=$sch and e.sx_yphrethshs!=$sch AND y.sxol_etos = $sxol_etos";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Με οργανική και κύρια υπηρέτηση σε άλλο σχολείο, που υπηρετούν με διάθεση</h3>";
            $i=0;
            echo "<table id=\"mytbl3\" class=\"imagetable tablesorter\" border=\"2\">\n";
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
                $comments = mysqli_result($result, $i, "comments");
                $hours = mysqli_result($result, $i, "hours");
                
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$hours</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        //Υπηρετούν σε τμήμα ένταξης
        $query = "SELECT * from employee WHERE sx_yphrethshs='$sch' AND status=1 AND ent_ty=1";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Υπηρετούν σε τμήμα ένταξης</h3>";
            $i=0;
            echo "<table id=\"mytbl2\" class=\"imagetable tablesorter\" border=\"2\">\n";
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
                $comments = mysqli_result($result, $i, "comments");

                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        //Υπηρετούν σε τάξη υποδοχής
        $query = "SELECT * from employee WHERE sx_yphrethshs='$sch' AND status=1 AND ent_ty=2";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h3>Υπηρετούν σε τάξη υποδοχής</h3>";
            $i=0;
            echo "<table id=\"mytbl2\" class=\"imagetable tablesorter\" border=\"2\">\n";
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
                $comments = mysqli_result($result, $i, "comments");

                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        
        //Αναπληρωτές
        //$query = "SELECT * FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos)";
        $query = "SELECT *,e.type as etype, e.name as ename, p.name as praxiname FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id join praxi p on e.praxi = p.id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 1)";
        //echo $query;
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        $sx_yphrethshs = mysqli_result($result, 0, "sx_yphrethshs");
        if ($num) {
            echo "<h3>Αναπληρωτές</h3>";
            $i=0;
            echo "<table id=\"mytbl4\" class=\"imagetable tablesorter\" border=\"2\">\n";
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
                $praxi = mysqli_result($result, $i, "praxiname");
                $type .= $thesi == 2 ? '<small> (Τμ.Ένταξης)</small>' : '';
                $type .= $thesi == 3 ? '<small> (Παράλληλη στήριξη)</small>' : '';

                $comments = mysqli_result($result, $i, "comments");
                $wres = mysqli_result($result, $i, "hours");
                
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a href=\"../employee/ektaktoi.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$praxi</td><td>$wres</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        
        //Απουσιάζουν: Ανήκουν οργανικά και υπηρετούν αλλού
        $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs!='$sch' order by klados";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h2>Απουσιάζουν</h2>";
            echo "<h3>Ανήκουν οργανικά και υπηρετούν αλλού</h3>";
            $i=0;
            echo "<table id=\"mytbl5\" class=\"imagetable tablesorter\" border=\"2\">\n";
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
                $comments = mysqli_result($result, $i, "comments");
                
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$sx_yphrethshs</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
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
        $query = "SELECT * FROM adeia ad RIGHT JOIN employee emp ON ad.emp_id = emp.id WHERE (sx_organikhs='$sch' OR sx_yphrethshs='$sch') AND ((start<'$today' AND finish>'$today') OR status=3) ORDER BY finish DESC";
        //echo $query;
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        if ($num) {
            echo "<h2>Σε Άδεια</h2>";
            echo "<h3>Μόνιμοι</h3>";
            $i=0;
            echo "<table id=\"mytbl6\" class=\"imagetable tablesorter\" border=\"2\">\n";
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
              $adeia_id = mysqli_result($result, $i, "id");
              $type = mysqli_result($result, $i, "type");
              $name = mysqli_result($result, $i, "name");
              $surname = mysqli_result($result, $i, "surname");
              $klados_id = mysqli_result($result, $i, "klados");
              $klados = getKlados($klados_id, $mysqlconnection);
              $comments = mysqli_result($result, $i, "comments");
              $comm = $comments;
              $today = date("Y-m-d");
              $return = mysqli_result($result, $i, "finish");
              $start = mysqli_result($result, $i, "start");
              $status = mysqli_result($result, $i, "status");
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
                          $comments = "Δεν απουσιάζει.<br>Έχει δηλωθεί κατάσταση \"Σε άδεια\"<br>";
                  }
                  $ret = date("d-m-Y", strtotime($return));
              }
              else
              {
                  $ret="";
                  $id = mysqli_result($result, $i, "emp.id");
                  $flag=1;
                  $comments = "Δεν απουσιάζει.<br>Έχει δηλωθεί κατάσταση \"Σε άδεια\"<br>";
              }
              if ($flag) {
                  $query1 = "select type from adeia_type where id=$type";
                  $result1 = mysqli_query($mysqlconnection, $query1);
                  $typewrd = mysqli_result($result1, 0, "type");
                  if ($absent && $status<>3) {
                      $comments = "<blink>Παρακαλώ αλλάξτε την κατάσταση του <br>εκπ/κού σε \"Σε Άδεια\"</blink><br>$comm";
                  }

                  echo "<tr>";
                  echo "<td>".($i+1)."</td>";
                  echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$typewrd</td><td><a href='../employee/adeia.php?adeia=$adeia_id&op=view'>$ret</a></td><td>$comments</td>\n";
                  echo "</tr>";
              }
              $i++;
            }
            echo "</tbody></table>";
        }
        //Αναπληρωτές σε άδεια
        $query = "SELECT * FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 3)";
        //echo $query;
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        $sx_yphrethshs = mysqli_result($result, 0, "sx_yphrethshs");
        if ($num) {
            echo "<h3>Αναπληρωτές</h3>";
            $i=0;
            echo "<table id=\"mytbl4\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr>";
            echo "<th>A/A</th>";
            echo "<th>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Τύπος Απασχόλησης</th>";
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
                $typos = mysqli_result($result, $i, "type");
                $type = get_type($typos, $mysqlconnection);
                $comments = mysqli_result($result, $i, "comments");
                $wres = mysqli_result($result, $i, "hours");
                
                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td><a href=\"../employee/ektaktoi.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$wres</td><td>$comments</td>\n";
                echo "</tr>";
                $i++;
            }
            echo "</tbody></table>";
            echo "<br>";
        }
        
        //Aπουσία COVID-19
        $query = "SELECT *,e.id as empid FROM employee e join yphrethsh y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 5)";
        $query_ekt = "SELECT *,e.id as empid FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 5)";
        //echo $query;
        $result = mysqli_query($mysqlconnection, $query);
        $result_ekt = mysqli_query($mysqlconnection, $query_ekt);
        $num = mysqli_num_rows($result);
        $num_ekt = mysqli_num_rows($result_ekt);
        //$sx_yphrethshs = mysqli_result($result, 0, "sx_yphrethshs");
        if (($num + $num_ekt) > 0){
            echo "<h2>Απουσία COVID-19</h2>";
            if ($num > 0){
                echo "<h3>Μόνιμοι</h3>";
                echo "<table id=\"mytbl4\" class=\"imagetable tablesorter\" border=\"2\">\n";
                echo "<thead><tr>";
                echo "<th>A/A</th>";
                echo "<th>Επώνυμο</th>";
                echo "<th>Όνομα</th>";
                echo "<th>Κλάδος</th>";
                echo "<th>Τύπος Απασχόλησης</th>";
                echo "<th>Ώρες</th>";
                echo "<th>Σχόλια</th>";
                echo "</tr></thead>\n<tbody>";
                while ($row = mysqli_fetch_assoc($result))
                {
                    $id = $row['empid'];
                    $name = $row['name'];
                    $surname = $row['surname'];
                    $klados_id = $row['klados'];
                    $klados = getKlados($klados_id, $mysqlconnection);
                    $typos = $row['type'];
                    $type = get_type($typos, $mysqlconnection);
                    $comments = $row['comments'];
                    $wres = $row['hours'];
                    
                    echo "<tr>";
                    echo "<td>".($i+1)."</td>";
                    echo "<td><a href=\"../employee/employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$wres</td><td>$comments</td>\n";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            }
            // ektaktoi
            if ($num_ekt > 0){
                echo "<h3>Αναπληρωτές</h3>";
                // $i=0;
                echo "<table id=\"mytbl4\" class=\"imagetable tablesorter\" border=\"2\">\n";
                echo "<thead><tr>";
                echo "<th>A/A</th>";
                echo "<th>Επώνυμο</th>";
                echo "<th>Όνομα</th>";
                echo "<th>Κλάδος</th>";
                echo "<th>Τύπος Απασχόλησης</th>";
                echo "<th>Ώρες</th>";
                echo "<th>Σχόλια</th>";
                echo "</tr></thead>\n<tbody>";
                while ($row = mysqli_fetch_assoc($result_ekt))
                {
                    $id = $row['empid'];
                    $name = $row['name'];
                    $surname = $row['surname'];
                    $klados_id = $row['klados'];
                    $klados = getKlados($klados_id, $mysqlconnection);
                    $typos = $row['type'];
                    $type = get_type($typos, $mysqlconnection);
                    $comments = $row['comments'];
                    $wres = $row['hours'];
                    
                    echo "<tr>";
                    echo "<td>".($i+1)."</td>";
                    echo "<td><a href=\"../employee/ektaktoi.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$wres</td><td>$comments</td>\n";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            }
        }
        if ($_SESSION['requests']) {
            display_school_requests($sch, $sxol_etos, $mysqlconnection, true);
        }
    } // of school status
    ?>
</center>
<div id='results'></div>
</body>
</html>
