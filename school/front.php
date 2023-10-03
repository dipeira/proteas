<?php
  $apiEndpoint = 'http://localhost/proteas/school/api.php'; // Replace with your API endpoint
  $sch = $_GET['code'];

  // Function to fetch data from API
  function fetchDataFromAPI($apiEndpoint, $params = []) {
      $ch = curl_init($apiEndpoint . '?' . http_build_query($params));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($ch);
      curl_close($ch);
      return json_decode($response, true);
  }

  // Function to get school name from API
  function getSchoolNameFromAPI($sch) {
    $params = ['code' => $sch];
    $data = fetchDataFromAPI($apiEndpoint, $params);
    return $data['school_name'];
  }

  $schData = fetchDataFromAPI($apiEndpoint, ['code' => $sch]);
      
  
  // If in production, login using sch.gr's CAS server
  // (To be able to login via sch.gr's CAS, the app must be whitelisted from their admins)
  $prDebug = 1;
  if (!$prDebug)
  {
    // phpCAS simple client, import phpCAS lib (downloaded with composer)
    require_once('../vendor/jasig/phpcas/CAS.php');
    //initialize phpCAS using SAML
    phpCAS::client(SAML_VERSION_1_1,'sso-test.sch.gr',443,'');
    // if logout
    if (isset($_POST['logout']))
    {
      session_unset();
      session_destroy(); 
      phpCAS::logout();
      die('Πραγματοποιήθηκε έξοδος...<br>Ευχαριστούμε για το χρόνο σας!');
    }
    
    // no SSL validation for the CAS server, only for testing environments
    phpCAS::setNoCasServerValidation();
    // handle backend logout requests from CAS server
    phpCAS::handleLogoutRequests(array('sso-test.sch.gr'));
    // force CAS authentication
    if (!phpCAS::checkAuthentication())
      phpCAS::forceAuthentication();
    // at this step, the user has been authenticated by the CAS server and the user's login name can be read with phpCAS::getUser().
    $_SESSION['loggedin'] = 1;
    $sch_code = phpCAS::getAttribute('edupersonorgunitdn:gsnunitcode');
    $sch = getSchoolFromCode($sch_code, $mysqlconnection);
    
    if (!isset($_POST['type'])){
      $schname = getSchoolNameFromCode($sch_code, $mysqlconnection);
      log2db($mysqlconnection, $sch_code, $schname);
    }
  }
  else {
    if (!$_GET['code']){
      die('Σφάλμα: Δεν έχει επιλεγεί σχολείο...');
    }
  }
  
?>
<html>
  <head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Καρτέλα σχολείου</title>
  </head>
  <body> 
    <center>
      <IMG src="../images/logo.png" class="applogo">
      <h1>Πληροφοριακό σύστημα "Πρωτέας"</h1>
    </center>
    <div class="container">
    <?php
      echo "<h2>Καρτέλα Σχολείου: ".$schData['school_data']['title']."</h2>";
      echo "<br>";

      $sdt = $schData['school_data'];
      echo "<hr class='bg-danger border-3 border-top border-primary' />";
      echo "<h2 style='text-align:left;'>Α. Στοιχεία σχολικής μονάδας</h2>";
      echo "<table class='table table-striped'>";
      echo "<tr><td colspan=3>Τίτλος: ".$sdt['title']."</td></tr>";
      echo "<tr><td>Δ/νση: ".$sdt['address']." - Δήμος: ".$sdt['dimos']."</td><td>Τηλ.: ".$sdt['tel']."</td></tr>";
      echo "<tr><td>email: <a href='mailto:".$sdt['email']."'>".$sdt['email']."</a></td><td></td></tr>";
      echo "<tr><td>Οργανικότητα: ".$sdt['organikothta']."</td><td>Λειτουργικότητα: ".$sdt['leitoyrg']."</td></tr>";
      echo "<tr><td>Οργανικά τοποθετηθέντες (πλην Τ.Ε.): ".$sdt['orgtop']."</td><td colspan=3>Κατηγορία: ".$sdt['cat']."</td></tr>";
      echo "<tr><td colspan=3>Σχόλια: ".$sdt['comments']."</td></tr>";
      echo "</table>";   
      
      $organikes = $sdt['organikes'];
      echo "<h4>Οργανικές</h4>";
      // if dim
      if ($sdt['type'] == 'dim') {
        echo "<table class='table table-striped'>";
        echo "<thead><tr>";
        echo "<th>Κλάδος</th>";
        echo "<th><span title='Δασκάλων'>ΠΕ70</th>";
        echo "<th><span title='Φυσικής Αγωγής'>ΠΕ11</th>";
        echo "<th><span title='Αγγλικών'>ΠΕ06</th>";
        echo "<th><span title='Μουσικών'>ΠΕ79</th>";
        echo "<th><span title='Γαλλικών'>ΠΕ05</th>";
        echo "<th><span title='Γερμανικών'>ΠΕ07</th>";
        echo "<th><span title='Καλλιτεχνικών'>ΠΕ08</th>";
        echo "<th><span title='Πληροφορικής'>ΠΕ86</th>";
        echo "<th><span title='Θεατρικών Σπουδών'>ΠΕ91</th>";
        if ($sdt['type2'] == 2) {
          echo "<th><span title='Λογοθεραπευτών'>ΠΕ21</th>";
          echo "<th><span title='Ψυχολόγων'>ΠΕ23</th>";
          echo "<th><span title='Σχ.Νοσηλευτών'>ΠΕ25</th>";
          echo "<th><span title='Λογοθεραπευτών'>ΠΕ26</th>";
          echo "<th><span title='Φυσικοθεραπευτών'>ΠΕ28</th>";
          echo "<th><span title='Εργοθεραπευτών'>ΠΕ29</th>";
          echo "<th><span title='Κοιν.Λειτουργών'>ΠΕ30</th>";
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
        // echo $org_ent ? "<td>$org_ent</td>" : '';
        if ($sdt['type2'] == 2) {
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
        echo "<tr>";
        $orgs = $sdt['org_top'];
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
        // echo $org_ent ? "<td>".$orgs['ent']."</td>" : '';
        if ($sdt['type2'] == 2) {
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
        //$orgs = get_orgs($sch,$conn);
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
        // echo $org_ent ? "<td>".($org_ent - $orgs['ent'])."</td>" : '';
        if ($sdt['type2'] == 2) {
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
      } else {
        //if nip
        $organikes = $sdt['organikes'];
        echo "<table class='table table-striped'>";  
          echo "<thead><tr>";
          echo "<th>Κλάδος</th>";
          echo "<th>ΠΕ60</th>";
        if ($sdt['type2'] == 2) {
          echo "<th><span title='Λογοθεραπευτών'>ΠΕ21</th>";
          echo "<th><span title='Ψυχολόγων'>ΠΕ23</th>";
          echo "<th><span title='Σχ.Νοσηλευτών'>ΠΕ25</th>";
          echo "<th><span title='Λογοθεραπευτών'>ΠΕ26</th>";
          echo "<th><span title='Φυσικοθεραπευτών'>ΠΕ28</th>";
          echo "<th><span title='Εργοθεραπευτών'>ΠΕ29</th>";
          echo "<th><span title='Κοιν.Λειτουργών'>ΠΕ30</th>";
          echo "<th><span title='Βοηθ.Προσ.Ειδ.Αγ.'>ΔΕ1ΕΒΠ</th>";  
        }
        echo "</tr></thead><tbody>";
        echo "<tr>";
        echo "<td>Οργανικές</td>";
        echo "<td>$organikes[0]</td>";
        if ($sdt['type2'] == 2) {
          echo "<td>$organikes[1]</td>";
          echo "<td>$organikes[2]</td>";
          echo "<td>$organikes[3]</td>";
          echo "<td>$organikes[4]</td>";
          echo "<td>$organikes[5]</td>";
          echo "<td>$organikes[6]</td>";
          echo "<td>$organikes[7]</td>";
          echo "<td>$organikes[8]</td>";
        }
        echo "</tr>";
        echo "<tr>";
        $orgs = $sdt['org_top'];
        echo "<td>Οργ.ανήκοντες</td>";
        echo "<td>".$orgs['ΠΕ60']."</td>";
        if ($sdt['type2'] == 2) {
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
        echo "<tr>";
        echo "<td>Οργ.Κενά</td>";
        echo "<td>".($organikes[0] - $orgs['ΠΕ60'])."</td>";
        if ($sdt['type2'] == 2) {
          echo "<td>".($organikes[1] - $orgs['ΠΕ21'])."</td>";
          echo "<td>".($organikes[2] - $orgs['ΠΕ23'])."</td>";
          echo "<td>".($organikes[3] - $orgs['ΠΕ25'])."</td>";
          echo "<td>".($organikes[4] - $orgs['ΠΕ26'])."</td>";
          echo "<td>".($organikes[5] - $orgs['ΠΕ28'])."</td>";
          echo "<td>".($organikes[6] - $orgs['ΠΕ29'])."</td>";
          echo "<td>".($organikes[7] - $orgs['ΠΕ30'])."</td>";
          echo "<td>".($organikes[8] - $orgs['ΔΕ1ΕΒΠ'])."</td>";
        }
        echo "</tr>";
        echo "</tbody></table>";
      }

    //     if ($entaksis[0]) {
    //         echo "<td><input type=\"checkbox\" checked disabled>Τμήμα Ένταξης / Μαθητές: $entaksis[1]</td>";
    //     } else {
    //         echo "<td><input type=\"checkbox\" disabled>Τμήμα Ένταξης</td>";
    //     }
    //     if ($ypodoxis) {
    //         echo "<td><input type=\"checkbox\" checked disabled>Τμήμα Υποδοχής</td>";
    //     } else {
    //         echo "<td><input type=\"checkbox\" disabled>Τμήμα Υποδοχής</td>";
    //     }
    //     echo "</tr>";
    //     if ($entaksis[0] || $ypodoxis) {
    //         echo "<tr><td>Εκπ/κοί Τμ.Ένταξης: $ekp_ee_exp[0]</td><td>Εκπ/κοί Τμ.Υποδοχής: $ekp_ee_exp[1]</td></tr>";
    //     }

    //     echo "<tr>";
    //     if ($type == 1) {
    //         if ($frontistiriako) {
    //             echo "<td><input type=\"checkbox\" checked disabled>Φροντιστηριακό Τμήμα</td>";
    //         } else {
    //             echo "<td><input type=\"checkbox\" disabled>Φροντιστηριακό Τμήμα</td>";
    //         }
    //     }
    //     // if nip print Proini Zoni (klasiko[6])
    //     else {
    //         if ($klasiko_exp[6]) {
    //             echo "<td><input type=\"checkbox\" checked disabled>Πρωινή Ζώνη / Μαθητές: $klasiko_exp[6]</td>";
    //         } else {
    //             echo "<td><input type=\"checkbox\" disabled>Πρωινή Ζώνη</td>";
    //         }
    //     }
                                            
    //     if ($oloimero) {
    //         if ($type == 1) {
    //             echo "<td><input type=\"checkbox\" checked disabled>Όλοήμερο</td></tr>";
    //             //echo "<tr><td>Μαθητές Ολοημέρου: $oloimero_stud</td>";
    //             //echo "<td>Εκπ/κοί Ολοημέρου: $oloimero_tea</td></tr>";
    //         }
    //         else {
    //             echo "<td><input type=\"checkbox\" checked disabled>Όλοήμερο</td></tr>";
    //         }
    //     }
    //     else {
    //         echo "<td><input type=\"checkbox\" disabled>Όλοήμερο</td></tr>";
    //     }
        
    //     if ($type == 1) {
    //         echo "<tr>";
    //         if ($ted) {
    //             echo "<td><input type=\"checkbox\" checked disabled>Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</td>";
    //         } else {
    //             echo "<td><input type=\"checkbox\" disabled>Τμ.Ενισχ.Διδασκαλίας (Τ.Ε.Δ.)</td>";
    //         }
    //         if ($vivliothiki) {
    //           echo "<td><input type=\"checkbox\" checked disabled>Σχολική βιβλιοθήκη";
    //           $qry1 = "SELECT surname,name,perigrafh from employee e JOIN klados k ON e.klados = k.id WHERE e.id=$vivliothiki";
    //           $res1 = mysqli_query($conn, $qry1);
    //           if ($row = mysqli_fetch_assoc($res1)) {
    //             echo "<i><small> (Υπευθυνος/-η: ".$row['surname'].' '.$row['name'].', '.$row['perigrafh'].')</small></i>';
    //           } else {
    //             echo '<i><small> (Δεν έχει οριστεί υπεύθυνος βιβλιοθήκης)</small></i>';
    //           }
    //           echo '</td>';
    //         } else {
    //             echo "<td><input type=\"checkbox\" disabled>Σχολική βιβλιοθήκη</td>";
    //         }
    //         echo "</tr>";
    //         echo "<tr><td>Ενότητα Σχολικών Συμβούλων: ".$perif."η</td>";
    //         echo $anenergo ? "<td>Κατάσταση: Σε αναστολή</td>" : "<td>Κατάσταση: Ενεργό</td>";
    //         echo "</tr>";
    //     }
    //     echo $anenergo && $type == 2 ? "<tr><td>Κατάσταση: Σε αναστολή</td><td></td>" : "<td>Κατάσταση: Ενεργό</td><td></td></tr>";
    //     echo "<tr><td>Σχόλια: $comments</td><td>Κωδικός ΥΠΑΙΘ: $code</td></tr>";
    //     // if ($systeg) {
    //     //     echo "<tr><td colspan=2>Συστεγαζόμενη σχολική μονάδα: <a href='school_status.php?org=$systeg' target='_blank'>$systegName</td></tr>";    
    //     // }
    //     if ($updated>0) {
    //         echo "<tr><td colspan=2 align=right><small>Τελ.ενημέρωση: ".date("d-m-Y H:i", strtotime($updated))."<small></td></tr>";
    //     }
    //     echo "</table>";
    //     echo "<br>";
        
    // students - classes
    // if dim
    echo "<hr class='bg-danger border-3 border-top border-primary' />";
    if ($sdt['type'] == 'dim') {
      $classes = $sdt['classes'];
      $tmimata_exp = $sdt['tmimata'];
      echo "<h2 style='text-align:left;'>Β. Μαθητές - Τμήματα</h2>";
      echo "<table class='table table-striped'>";  
      echo "<tr><td></td><td>Α'</td><td>Β'</td><td>Γ'</td><td>Δ'</td><td>Ε'</td><td>ΣΤ'</td><td class='tdnone'><i>Ολ</i></td><td class='tdnone'><i>ΠΖ</i></td></tr>";
      echo "<tr><td>Μαθ.Πρωινού<br>Σύνολο: ".$sdt['synolo_mathiton']."</td><td>$classes[0]</td><td>$classes[1]</td><td>$classes[2]</td><td>$classes[3]</td><td>$classes[4]</td><td>$classes[5]</td><td class='tdnone'><i>$classes[6]</i></td><td class='tdnone'><i>$classes[7]</i></td></tr>";
      echo "<tr><td>Τμ./τάξη Πρωινού<br>Σύνολο: ".$sdt['sylono_tmimaton']."</td><td>$tmimata_exp[0]</td><td>$tmimata_exp[1]</td><td>$tmimata_exp[2]</td><td>$tmimata_exp[3]</td><td>$tmimata_exp[4]</td><td>$tmimata_exp[5]</td><td class='tdnone'><i>$tmimata_exp[6]<small> (14-15)</small><br>$tmimata_exp[7]<small> (15-16)</small></i></td><td class='tdnone'><i>$tmimata_exp[8]</i></td></tr>";
      echo "</table>";
    }
    // if nip
    // klasiko_nip/pro: klasiko
    // klasiko pos 0-5: 0,1 t1n,p / 2,3 t2n,p / 4,5 t3n,p
    // prwinh zvnh @ pos 7 -> klasiko[6]
    // oloimero_syn_nip/pro: oloimero
    // Μαθητές
    else {
      echo "<h2 style='text-align:left;'>Β. Μαθητές - Τμήματα</h2>";
      echo "<table class='table table-striped'>";  
      $classes = $sdt['classes'];
      $oloimero = $sdt['oloimero'];
      echo "<tr><td>Τμήμα</td><td>Κλασικό</td><td>Ολοήμερο</td></tr>";

      echo ($classes[0] + $classes[1]) > 0 ? "<tr><td>Τμήμα 1</td><td>".($classes[0] + $classes[1])."</td><td>".($oloimero[0] + $oloimero[1])."</td></tr>" : '';
      echo ($classes[2] + $classes[3]) > 0 ? "<tr><td>Τμήμα 2</td><td>".($classes[2] + $classes[3])."</td><td>".($oloimero[2] + $oloimero[3])."</td></tr>" : '';
      echo ($classes[4] + $classes[5]) > 0 ? "<tr><td>Τμήμα 3</td><td>".($classes[4] + $classes[5])."</td><td>".($oloimero[4] + $oloimero[5])."</td></tr>" : '';
      echo ($classes[4] + $classes[5]) > 0 ? "<tr><td>Τμήμα 3</td><td>".($classes[4] + $classes[5])."</td><td>".($oloimero[4] + $oloimero[5])."</td></tr>" : '';
      echo ($classes[7] + $classes[8]) > 0 ? "<tr><td>Τμήμα 4</td><td>".($classes[7] + $classes[8])."</td><td>".($oloimero[6] + $oloimero[7])."</td></tr>" : '';
      echo ($classes[9] + $classes[10]) > 0 ? "<tr><td>Τμήμα 5</td><td>".($classes[9] + $classes[10])."</td><td>".($oloimero[8] + $oloimero[9])."</td></tr>" : '';
      echo ($classes[11] + $classes[12]) > 0 ? "<tr><td>Τμήμα 6</td><td>".($classes[11] + $classes[12])."</td><td>".($oloimero[10] + $oloimero[11])."</td></tr>" : '';
      echo "<tr><td>Σύνολο</td><td>".$sdt['synolo_mathiton']."</td><td>".$sdt['synolo_oloimero']."</td></tr>";
      echo "</table>";
    }

    // kena - pleonasmata
    if ($sdt['type'] == 'dim') {
      $req = $sdt['kena_pleonasmata']['required'];
      $avl = $sdt['kena_pleonasmata']['available'];
      $df = $sdt['kena_pleonasmata']['diff'];
      echo "<h4>Λειτουργικά κενά - πλεονάσματα (ώρες)</h4>";
      echo "<table class='table table-striped'>";
        echo "<thead><th>Κλάδος</th><th>ΠΕ05-07</th><th>ΠΕ06</th><th>ΠΕ08</th><th>ΠΕ11</th><th>ΠΕ79</th><th>ΠΕ91</th><th>ΠΕ86</th><th>ΠΕ70</th><th>Ολοήμερο</th><th>Πρωινή Ζώνη</th></thead><tbody>";
        echo "<tr><td>Απαιτούμενες</td><td>".$req['05-07']."</td><td>".$req['06']."</td><td>".$req['08']."</td><td>".$req['11']."</td><td>".$req['79']."</td><td>".$req['91']."</td>";
        echo "<td>".$req['86']."</td><td>".$req['70']."</td><td>".$req['O']."</td><td>".$req['P']."</td>";
        echo "<tr><td>Διαθέσιμες</td><td>".$avl['05-07']."</td><td>".$avl['06']."</td><td>".$avl['08']."</td><td>".$avl['11']."</td><td>".$avl['79']."</td><td>".$avl['91']."</td>";
        echo "<td>".$avl['86']."</td><td>".$avl['70']."</td><td></td><td></td>";
        echo "<tr><td>+ / -</td><td>".$df['05-07']."</td><td>".$df['06']."</td><td>".$df['08']."</td><td>".$df['11']."</td><td>".$df['79']."</td><td>".$df['91']."</td>";
        echo "<td>".$df['86']."</td><td>".$df['70']."</td><td>".$df['OP']."</td><td></td>";
        echo "</tbody>";
      echo "</table>"; 
      
      echo "<a class='btn btn-primary' data-bs-toggle='collapse' href='#collapseExample' role='button' aria-expanded='false' aria-controls='collapseExample'>";
      echo "Αναλυτικά</a>";
      echo "<div class='collapse' id='collapseExample'>";
        echo "<p>";
        foreach ($sdt['kena_pleonasmata']['analytika_cnt'] as $key=>$value){
          echo "&nbsp;&nbsp;$key: <strong>$value</strong>";
        }
        echo "</p>";
        echo "<table class='table table-sm table-striped'>";
        echo "<tr><th>Ον/μο</th><th>Κλάδος</th><th>Ώρες</th></tr>";
        foreach ($sdt['kena_pleonasmata']['analytika'] as $row) {
          echo "<tr><td>".$row['fullname']."</td><td>".$row['klados']."</td><td>".$row['hours']."</td></tr>";
        }
        echo "</table>"; 
      echo "</div>";
    }
    else {
      $req = $sdt['kena_pleonasmata']['apaitoymenoi'];
      echo "<h4>Λειτουργικά κενά - πλεονάσματα</h4>";
      echo "<table class='table table-striped'>";
      echo "<thead><th></th><th>Σύνολο</th><th>Κλασικό</th><th>Ολοήμερο</th><th>Ένταξης</th></thead>";
      echo "<tr><td>Απαιτούμενοι</td><td>".$req['synolo']."</td><td>".$req['klasiko']."</td><td>".$req['oloimero']."</td><td>".$req['entaksis']."</td></tr>";
      echo "<tr><td>Υπάρχοντες</td><td>".$sdt['kena_pleonasmata']['yparxontes']."</td><td></td><td></td><td></td>";
      echo "<tr><td>+ / -</td><td>".$sdt['kena_pleonasmata']['kena_pleon']."</td><td></td><td></td><td></td>";
      echo "</table>";
    }
    //         $has_entaxi = strlen($entaksis[0])>1 ? 1 : 0; 
    //         // τοποθετημένοι εκπ/κοί
    //         $top60 = $top60m = $top60ana = 0;
    //         $qry = "SELECT count(*) as pe60 FROM employee WHERE sx_yphrethshs = $sch AND klados=1 AND status=1";
    //         $res = mysqli_query($conn, $qry);
    //         $top60m = mysqli_result($res, 0, 'pe60');
    //         $qry = "SELECT count(*) as pe60 FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados=1 AND status=1";
    //         $res = mysqli_query($conn, $qry);
    //         $top60ana = mysqli_result($res, 0, 'pe60');
    //         $top60 = $top60m+$top60ana;
            
    //         $syn_apait = $tmimata_nip+$tmimata_nip_ol+$has_entaxi;    
    //     // if systegazomeno
    //     if ($systeg) {
    //         echo "<a id='toggleSystegBtn' href='#'>Συστεγαζόμενο: $systegName</a>";
    //         echo "<div id='systeg' style='display: none;'>";
    //         ektimhseis_wrwn($systeg, $conn, $sxol_etos, true);
    //         echo "</div>";
    //         echo "<br><br>";
    //     }

    if (!$sch && !$str) {
        die('Το σχολείο δε βρέθηκε...');
    }
    if (isset($_GET['sxoletos'])) {
        $sxol_etos = $_GET['sxoletos'];
    }
    
    //Υπηρετούν με θητεία
    echo "<hr class='bg-danger border-3 border-top border-primary' />";
    echo "<h2 style='text-align:left;'>Γ. Προσωπικό</h2>";
    ?>
    <ul class="nav nav-tabs" id="myTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="thiteia-tab" data-bs-toggle="tab" href="#thiteia" role="tab" aria-controls="thiteia" aria-selected="true">Με θητεία</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="organika-tab" data-bs-toggle="tab" href="#organika" role="tab" aria-controls="organika" aria-selected="false">Ανήκουν οργανικά</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="organika_eid-tab" data-bs-toggle="tab" href="#organika_eid" role="tab" aria-controls="organika_eid" aria-selected="false">Ανήκουν οργανικά (ειδικότητες)</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="allou-tab" data-bs-toggle="tab" href="#allou" role="tab" aria-controls="allou" aria-selected="false">Ανήκουν οργανικά αλλού</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="diathesi-tab" data-bs-toggle="tab" href="#diathesi" role="tab" aria-controls="diathesi" aria-selected="false">Με διάθεση</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="entaksis-tab" data-bs-toggle="tab" href="#entaksis" role="tab" aria-controls="entaksis" aria-selected="false">Τμ.Ένταξης</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="ty-tab" data-bs-toggle="tab" href="#ty" role="tab" aria-controls="ty" aria-selected="false">Τ.Υποδοχής</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="anaplirotes-tab" data-bs-toggle="tab" href="#anaplirotes" role="tab" aria-controls="anaplirotes" aria-selected="false">Αναπληρωτές</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="apontes-tab" data-bs-toggle="tab" href="#apontes" role="tab" aria-controls="apontes" aria-selected="false">Απόντες</a>
        </li>
        
    </ul>
    <div class="tab-content" id="myTabsContent">
        <div class="tab-pane fade show active" id="thiteia" role="tabpanel" aria-labelledby="thiteia-tab">
    <?
        echo "<h3>Υπηρετούν με θητεία</h3>";
        
        $i=0;
        echo "<table id=\"mytbl\" class='table table-sm table-striped' border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Κλάδος</th>";
        echo "<th>Θέση</th>";
        //echo "<th>Σχόλια</th>";
        echo "</tr></thead>\n<tbody>";
        foreach($schData['thiteia'] as $i=>$row){
            $thesi = $row['thesi'] == 1 ? 'Υποδιευθυντής/ντρια' : 'Διευθυντής/ντρια';
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>".$row['surname']."</td><td>".$row['name']."</td><td>".$row['klados']."</td><td>".$thesi."</td>";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";           
        echo "</div>";
    //Ανήκουν οργανικά και υπηρετούν (ΠΕ60-70)
    ?>
    <div class="tab-pane fade" id="organika" role="tabpanel" aria-labelledby="organika-tab">
      <?php
        echo "<h3>Ανήκουν οργανικά και υπηρετούν (ΠΕ60/ΠΕ70)</h3>";
        $i=0;
        echo "<table id=\"mytbl2\" class='table table-sm table-striped' border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Κλάδος</th>";
        //echo "<th>Σχόλια</th>";
        echo "</tr></thead>\n<tbody>";
        foreach($schData['organika'] as $row)
        {            
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>".$row['surname']."</td><td>".$row['name']."</td><td>".$row['klados']."</td>";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    ?>
    </div>
    <div class="tab-pane fade" id="organika_eid" role="tabpanel" aria-labelledby="organika_eid-tab">
    <?php
    //Ανήκουν οργανικά και υπηρετούν (Ειδικότητες)
        echo "<h3>Ανήκουν οργανικά και υπηρετούν (Ειδικότητες)</h3>";
        $i=0;
        echo "<table id=\"mytbl2\" class='table table-sm table-striped' border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Κλάδος</th>";
        echo "</tr></thead>\n<tbody>";
        foreach($schData['organika_eid'] as $row)
        {
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>".$row['surname']."</td><td>".$row['name']."</td><td>".$row['klados']."</td>";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    ?>
    </div>
    <div class="tab-pane fade" id="allou" role="tabpanel" aria-labelledby="allou-tab">
    <?php
    // Οργανική αλλού και υπηρετούν
        echo "<h3>Με οργανική σε άλλο σχολείο και υπηρετούν</h3>";
        $i=0;
        echo "<table id=\"mytbl3\" class='table table-sm table-striped' border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Κλάδος</th>";
        echo "<th>Σχολείο Οργανικής</th>";
        echo "</tr></thead>\n<tbody>";
        foreach ($schData['organikh_allou'] as $row) {
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>".$row['surname']."</td><td>".$row['name']."</td><td>".$row['klados']."</td><td>".$row['sx_organikhs']."</td>";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
?>
      </div>
      <div class="tab-pane fade" id="diathesi" role="tabpanel" aria-labelledby="diathesi-tab">
    <?php
    // Οργανική αλλού και δευτερεύουσα υπηρέτηση
        echo "<h3>Με οργανική και κύρια υπηρέτηση σε άλλο σχολείο, που υπηρετούν με διάθεση</h3>";
        $i=0;
        echo "<table id=\"mytbl3\" class='table table-sm table-striped' border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Κλάδος</th>";
        echo "<th>Σχολείο Οργανικής</th>";
        echo "<th>Ώρες</th>";
        echo "</tr></thead>\n<tbody>";
        foreach ($schData['organikh_allou_deyt'] as $row) {
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>".row['surname']."</td><td>".$row['name']."</td><td>".$row['klados']."</td><td>".$row['organikh']."</td><td>".$row['hours']."</td>";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    ?>
    </div>
    <div class="tab-pane fade" id="entaksis" role="tabpanel" aria-labelledby="entaksis-tab">
    <?php
    //Υπηρετούν σε τμήμα ένταξης
        echo "<h3>Υπηρετούν σε τμήμα ένταξης</h3>";
        $i=0;
        echo "<table id=\"mytbl2\" class='table table-sm table-striped' border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Κλάδος</th>";
        echo "<th>Σχολείο Οργανικής</th>";
        echo "</tr></thead>\n<tbody>";
        foreach ($schData['entaksis'] as $row) {
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>".$row['surname']."</td><td>".$row['name']."</td><td>".$row['klados']."</td><td>".$row['organikh']."</td>";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    ?>
    </div>
    <div class="tab-pane fade" id="ty" role="tabpanel" aria-labelledby="ty-tab">
    <?php
    //Υπηρετούν σε τάξη υποδοχής
        echo "<h3>Υπηρετούν σε τάξη υποδοχής</h3>";
        $i=0;
        echo "<table id=\"mytbl2\" class='table table-sm table-striped' border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Κλάδος</th>";
        echo "<th>Σχολείο Οργανικής</th>";
        echo "</tr></thead>\n<tbody>";
        foreach ($schData['ty'] as $row) {
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>".$row['surname']."</td><td>".$row['name']."</td><td>".$row['klados']."</td><td>".$row['organikh']."</td>";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    ?>
    </div>
    <div class="tab-pane fade" id="anaplirotes" role="tabpanel" aria-labelledby="anaplirotes-tab">
    <?php
    
    //Αναπληρωτές
      echo "<h3>Αναπληρωτές</h3>";
        $i=0;
        echo "<table id=\"mytbl4\" class='table table-sm table-striped' border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Κλάδος</th>";
        echo "<th>Τύπος Απασχόλησης</th>";
        echo "<th>Πράξη</th>";
        echo "<th>Ώρες</th>";
        echo "</tr></thead>\n<tbody>";
        foreach ($schData['anapl'] as $row) {
          if ($row['thesi'] == 2) {
            $row['etype'] .=  '<small> (Τμ.Ένταξης)</small>';
          } else if ($row['thesi'] == 3) {
            $row['etype'] .=  '<small> (Παράλληλη στήριξη)</small>';
          }
            
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>".$row['surname']."</td><td>".$row['name']."</td><td>".$row['klados']."</td><td>".$row['etype']."</td><td>".$row['praxi']."</td><td>".$row['hours']."</td>";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    ?>
    </div>
    <div class="tab-pane fade" id="apontes" role="apontes" aria-labelledby="apontes-tab">
    <?php
    //Απουσιάζουν: Ανήκουν οργανικά και υπηρετούν αλλού
    if ($schData['apontes']) {
      echo "<h2>Απουσιάζουν</h2>";
        echo "<h3>Ανήκουν οργανικά και υπηρετούν αλλού</h3>";
        $i=0;
        echo "<table id=\"mytbl5\" class='table table-sm table-striped' border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Κλάδος</th>";
        echo "<th>Σχολείο/Φορέας Υπηρέτησης</th>";
        echo "<th>Σχόλια</th>";
        echo "</tr></thead>\n<tbody>";
        foreach ($schData['apontes'] as $row) {
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>".$row['surname']."</td><td>".$row['name']."</td><td>".$row['klados']."</td><td>".$row['yphrethsh']."</td><td>".$row['comments']."</td>\n";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    }
  
    //Σε άδεια
    if ($schData['adeia']) {
      echo "<h2>Σε Άδεια</h2>";
        echo "<h3>Μόνιμοι</h3>";
        $i=0;
        echo "<table id=\"mytbl6\" class='table table-sm table-striped' border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Κλάδος</th>";
        echo "<th>Τύπος</th>";
        echo "<th>Ημ/νία Επιστροφής</th>";
        echo "<th>Σχόλια</th>";
        echo "</tr></thead>\n<tbody>";
        foreach ($schData['adeia'] as $row) {           
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            $ret_dt = date("d/m/Y", strtotime($row['finish']));
            echo "<td>".$row['surname']."</td><td>".$row['name']."</td><td>".$row['klados']."</td><td>".$row['type']."</td><td>".$ret_dt."</td><td>".$row['comments']."</td>\n";
            echo "</tr>";
        }
        $i++;
        echo "</tbody></table>";
    }

    //Αναπληρωτές σε άδεια
    if ($schData['anapladeia']) {
        echo "<h3>Αναπληρωτές</h3>";
        $i=0;
        echo "<table id=\"mytbl4\" class='table table-sm table-striped' border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Επώνυμο</th>";
        echo "<th>Όνομα</th>";
        echo "<th>Κλάδος</th>";
        echo "<th>Τύπος Απασχόλησης</th>";
        echo "<th>Πράξη</th>";
        echo "</tr></thead>\n<tbody>";
        foreach ($schData['anapladeia'] as $row) {
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>".$row['surname']."</td><td>".$row['name']."</td><td>".$row['klados']."</td><td>".$row['etype']."</td><td>".$row['praxi']."</td>";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    }
    ?>
    </div>
  </div>
  <?php
    // requests
    // display_school_requests($sch, $sxol_etos, $mysqlconnection);
    
    // echo "<h4>Υποβολή αιτήματος</h4>";
    // echo "<p><i>ΣΗΜ: Σε περίπτωση που δεν εντοπίζετε κάποιο λάθος, δε χρειάζεται κάποια ενέργεια.</i></p>";
    // echo "<form id='requestfrm' action='' method='POST' autocomplete='off'>";
    // echo "<table class=\"imagetable stable\" border='1'>";
    // echo "<td>Αίτημα</td><td></td>";
    // echo "<td><textarea id='request' name='request' rows='10' cols='80'></textarea></td></tr>";
    // echo "</table>";
    // echo "<input type='hidden' name = 'school' value='$sch'>";
    // echo "<input type='hidden' name = 'type' value='insert'>";
    // echo "<br>";
    // echo "<input id='submit' type='submit' value='Υποβολή'>";
    // echo "</form>";

    //logout button
    echo "<form action='' method='POST'>";
    echo "<input type='submit' class='btn btn-danger' name='logout' value='Έξοδος'>";
    echo "</form>";

    ?>

  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@2.11.6/dist/umd/popper.min.js"></script>
</body>
</html>
