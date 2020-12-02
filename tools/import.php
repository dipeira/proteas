<?php
  header('Content-type: text/html; charset=utf-8'); 
?>
<html>
  <head>
	  <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Εισαγωγή δεδομένων από αρχείο</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
  </head>
  <body>

<?php
  session_start();

  require_once "../config.php";
  require_once '../include/functions.php';
  
  include_once("class.login.php");
  $log = new logmein();
  // if not logged in or not admin
  //if($log->logincheck($_SESSION['loggedin']) == false || $_SESSION['user'] != $av_admin)
  if($_SESSION['loggedin'] == false)
  {   
      header("Location: login.php");
  }
  else
      $loggedin = 1;

  // check if admin
  if ($_SESSION['userlevel'] > 0)
  {
    echo "<br><br><h3>Δεν έχετε δικαίωμα για την πραγματοποίηση αυτής της ενέργειας. Επικοινωνήστε με το διαχειριστή σας.</h3>";
    die();
  }
  
  if (!isset($_POST['submit']))
  {
    require '../etc/menu.php';
    echo "<h2> Εισαγωγή δεδομένων στη βάση δεδομένων </h2>";
    echo "<form enctype='multipart/form-data' action='import.php' method='post'>";
    echo "<b>Βήμα 1.</b> Επιλογή αρχείου προς συμπλήρωση:<br>";
    echo "<ul>";
    echo "<li><a href='schools.csv'>Σχολεία</a></li>";
    echo "<li><a href='employees.csv'>Μόνιμοι</a></li>";
    echo "<li><a href='students_ds.csv'>Μαθητές / Τμήματα Δ.Σ.</a></li>";
    echo "<li><a href='students_nip.csv'>Μαθητές / Τμήματα Νηπ.</a></li></ul>";
    echo "<b>Βήμα 2.</b> Επιλογή τύπου δεδομένων:<br>";
    echo "<input type='radio' name='type' value='2'>α) Σχολεία<br>";
    echo "<input type='radio' name='type' value='1'>β) Μόνιμοι<br>";
    echo "<input type='radio' name='type' value='3'>γ) Μαθητές / Τμήματα Δ.Σ.&nbsp;(<a href='import_check.php'>Έλεγχος εισαγωγής</a>)<br>";
    echo "<input type='radio' name='type' value='4'>δ) Μαθητές / Τμήματα Νηπ.<br>";
    echo "<br><b>ΠΡΟΣΟΧΗ: </b> Τα γ, δ να εισάγονται αφού αλλάξει το σχ. έτος.<br />\n";
    echo "<br><b>Βήμα 3.</b> Υποβολή συμπληρωμένου αρχείου προς εισαγωγή:<br />\n";
    echo "<input size='50' type='file' name='filename'><br />\n";
    print "<input type='submit' name='submit' value='Μεταφόρτωση'></form>";
    echo "<small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small>";
    echo "</form>";
    echo "<br><a href='ektaktoi_import.php'>Εισαγωγή αναπληρωτών</a>";
    echo "<br><br>";
    echo "<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    exit;
  }
		
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  if (!isset($_POST['type'])){
    echo "<h3>Σφάλμα: Δεν επιλέξατε τύπο δεδομένων.</h3>";
    echo "<br><a href='import.php'>Επιστροφή</a>";
    die();
  }
  //Upload File
  if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
      echo "<h3>" . "To αρχείο ". $_FILES['filename']['name'] ." ανέβηκε με επιτυχία." . "</h3>";

      //Import uploaded file to Database
      $handle = fopen($_FILES['filename']['tmp_name'], "r");
      switch ($_POST['type'])
      {
          case 1:
            $tbl = 'employee';
            break;
          case 2:
            $tbl = 'school';
            break;
          case 3:
          case 4:
            $tbl = 'school';
            break;
      }
      $num = 0;
      $saves = 0;
      $checked = 0;
      $headers = 1;
      $error = false;
      $er_msg = '';
      
      // set max execution time (for large files)
      set_time_limit (480);

      while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        // skip header line
        if ($headers){
            $headers = 0;
            continue;
        }
        // check if csv & table columns are equal
        if (!$checked)
        {
          $csvcols = count($data);
          if ($_POST['type'] == 1){
            $tblcols = 25;
          }
          else if ($_POST['type'] == 2){
            $tblcols = 12;
          }
          else if ($_POST['type'] == 3){
            $tblcols = 18;
          }
          else if ($_POST['type'] == 4){
            $tblcols = 13;
          }

          if ($csvcols <> $tblcols)
          {
            echo "<h3>Σφάλμα: Λάθος αρχείο (Στήλες αρχείου: $csvcols <> στήλες πίνακα: $tblcols)</h3>";
            echo "<a href='import.php'>Επιστροφή</a>";
            die();
          }
          else
            $checked = 1;
        }  

        switch ($_POST['type']){
          // employees
          case 1:
            // check school codes
            $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
            $sx_organ = getSchoolFromCode($data[23],$mysqlconn);
            $sx_yphr = getSchoolFromCode($data[24],$mysqlconn);
            if (!$sx_organ || !$sx_yphr){
              $error = true;
              $er_msg = 'Σφάλμα: Δε βρέθηκε ο 7ψήφιος κωδικός σχολείου: ';
              $er_msg .= !$sx_organ ? $data[23] : $data[24];
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            // check if am exists
            $qry = "SELECT * FROM employee WHERE am = $data[5]";
            if (mysqli_num_rows(mysqli_query($mysqlconnection, $qry)) ){
              $error = true;
              $er_msg ="Σφάλμα: Ο υπάλληλος με ΑΜ ".$data[5]." υπάρχει ήδη...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            // fix dates
            $data[7] = date ("Y-m-d", strtotime($data[7]));
            $data[10] = date ("Y-m-d", strtotime($data[10]));
            $data[11] = date ("Y-m-d", strtotime($data[11]));
            // proceed to import
            $import="INSERT into employee(name,surname,patrwnymo,mhtrwnymo,klados,am,thesi,fek_dior,hm_dior,
            vathm, mk, hm_mk, hm_anal, met_did, proyp, proyp_not, status,
            afm, tel, address, idnum, amka, email, wres, sx_organikhs, sx_yphrethshs)
            values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]',0,'$data[6]','$data[7]',
            '$data[8]','$data[9]','$data[10]','$data[11]','$data[12]','$data[13]','$data[14]','$data[15]',
            '$data[16]','$data[17]','$data[18]','$data[19]','$data[20]','$data[21]','$data[22]', $sx_organ, $sx_yphr)";
            $imp_8 = iconv('cp1253','utf-8',$import);
            $ret = mysqli_query($mysqlconnection, $imp_8);
            
            if (!$ret) {
              $error = true;
            } else {
              $saves++;
              // insert yphrethsh as well
              $id = mysqli_insert_id($mysqlconnection);
              $query = "insert into yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) 
              values ($id, '$sx_yphr', '$data[22]', '$sx_organ', '$sxol_etos')";
              mysqli_query($mysqlconnection,$query);
            }
            break;

          // schools
          case 2:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            if (mysqli_num_rows(mysqli_query($mysqlconnection, $qry)) ){
              $error = true;
              $er_msg ="Σφάλμα: Το σχολείο με κωδικό ".$data[0]." υπάρχει ήδη...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            $import="INSERT into school(code,category,type,name,address,tk,tel,fax,email,organikothta,leitoyrg,type2) 
            values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]','$data[10]','$data[11]')";
            $imp_8 = iconv('cp1253','utf-8',$import);
            $ret = mysqli_query($mysqlconnection, $imp_8);
            if (!$ret) {
              $error = true;
            } else {
              $saves++;
            }
            break;

          // students ds
          case 3:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            $res = mysqli_query($mysqlconnection, $qry);
            if (!mysqli_num_rows($res) ){
              $error = true;
              $er_msg ="Σφάλμα: Το σχολείο με κωδικό ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }
            
            // update school set students='Α,Β,Γ,Δ,Ε,ΣΤ,ΟΛ,ΠΡ-Ζ',tmimata='Α,Β,Γ,Δ,Ε,ΣΤ,ΟΛ,ΟΛ16,ΠΡ-Ζ' WHERE code='9170117';
            $students = implode(',',Array($data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7],$data[8]));
            $tm_prz = ceil($data[8]/25);
            $tmimata = implode(',',Array($data[10],$data[11],$data[12],$data[13],$data[14],$data[15],$data[16],$data[17],$tm_prz));
            $entaksis = $data[9] > 0 ? 'on,'.$data[9] : ',';
            // archive 
            $archive = mysqli_result($res, 0, "archive");
            if (strlen($archive) > 0) {
              $archive_arr = unserialize($archive);
            } else $archive_arr = Array();
            $students_old = mysqli_result($res, 0, "students");
            $tmimata_old = mysqli_result($res, 0, "tmimata");
            $entaksis_old = mysqli_result($res, 0, "entaksis");
            $archive_data = $students_old . ',' . $tmimata_old . ',' . $entaksis_old;
            $sxoletos = find_prev_year($sxol_etos);
            // archive last year only once (in case of reinserting data)
            if (is_string($archive_arr[$sxoletos]) && strlen($archive_arr[$sxoletos]) > 0) {
            } else {
              $archive_arr[$sxoletos] = $archive_data;
            }
            $sql="UPDATE school SET archive = '". serialize($archive_arr) . "' WHERE code=".$data[0];
            $ret = mysqli_query($mysqlconnection, $sql);
            // update school table
            if ($students <> $students_old || $tmimata <> $tmimata_old || $entaksis <> $entaksis_old){
              $sql="UPDATE school SET students='$students', tmimata='$tmimata', entaksis='$entaksis' WHERE code=".$data[0];
              $ret = mysqli_query($mysqlconnection, $sql);
              if (!$ret) {
                $error = true;
              } else {
                $saves++;
              }
            }
            break;

          // students nip
          case 4:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            $res = mysqli_query($mysqlconnection, $qry);
            if (!mysqli_num_rows($res) ){
              $error = true;
              $er_msg ="Σφάλμα: Το σχολείο με κωδικό ".$data[0]." δεν υπάρχει...";
              $er_msg .= " (γραμμή ".($num+1).")";
              break;
            }

            // update school set klasiko='1Π,1Ν,2Π,2Ν,3Π,3Ν,ΠΖ', oloimero_nip='ΟΛ1Π,ΟΛ1Ν,ΟΛ2Π,ΟΛ2Ν',entaksis='0,0' where code=9170040;
            $klasiko = implode(',',Array($data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7]));
            $oloimero_nip = implode(',',Array($data[8],$data[9],$data[10],$data[11]));
            $entaksis = $data[12] > 0 ? 'on,'.$data[12] : '0,0';
            // archive 
            $archive = mysqli_result($res, 0, "archive");
            if (strlen($archive) > 0) {
              $archive_arr = unserialize($archive);
            } else $archive_arr = Array();
            $klasiko_old = mysqli_result($res, 0, "klasiko");
            $oloimero_nip_old = mysqli_result($res, 0, "oloimero_nip");
            $entaksis_old = mysqli_result($res, 0, "entaksis");
            $archive_data = $klasiko_old . ',' . $oloimero_nip_old . ',' . $entaksis_old;
            $sxoletos = find_prev_year($sxol_etos);
            // archive last year only once (in case of reinserting data)
            if (is_string($archive_arr[$sxoletos]) && strlen($archive_arr[$sxoletos]) > 0) {
            } else {
              $archive_arr[$sxoletos] = $archive_data;
            }
            $sql="UPDATE school SET archive = '". serialize($archive_arr) . "' WHERE code=".$data[0];
            $ret = mysqli_query($mysqlconnection, $sql);
            // update school table
            if ($klasiko <> $klasiko_old || $oloimero_nip <> $oloimero_nip_old || $entaksis <> $entaksis_old){
              $sql="UPDATE school SET klasiko='$klasiko', oloimero_nip='$oloimero_nip', entaksis='$entaksis' WHERE code=".$data[0];
              $ret = mysqli_query($mysqlconnection, $sql);
              if (!$ret) {
                $error = true;
              } else {
                $saves++;
              }
            }
            break;
        }
        if ($error){
          break;
        }
        
        $num++;
      }

      fclose($handle);
      if (!$error){
        if ($saves > 0){ 
          print "<h3>Η εισαγωγή πραγματοποιήθηκε με επιτυχία!</h3>";
          echo "Έγινε εισαγωγή $saves εγγραφών στον πίνακα $tbl.<br>";
        } else {
          echo "Δεν έγινε καμία εισαγωγή στη βάση δεδομένων.<br>";
        }
      }
      else
      {
          echo "<h3>Παρουσιάστηκε σφάλμα κατά την εισαγωγή</h3>";
          
          echo mysqli_error($mysqlconnection) ? "Μήνυμα λάθους:".mysqli_error($mysqlconnection) : '';
          echo $er_msg ? "<h3>$er_msg</h3>" : '';
          echo "<h4>Ελέγξτε το αρχείο ή επικοινωνήστε με το διαχειριστή.</h4>";
      }
    }
    else {
        echo "<h3>Σφάλμα: Δεν επιλέξατε αρχείο</h3><br><br>";
    }
                
    echo "<a href='import.php'>Επιστροφή</a>";
?>

</body>
</html>
	