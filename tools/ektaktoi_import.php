<html>
  <head>
	  <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Εισαγωγή εκπαιδευτικών από αρχείο excel</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
  </head>
  <body>
<?php

require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once "../config.php";
require_once "../include/functions.php";

$showRows = 10;


require_once "../tools/class.login.php";
$log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {   
  header("Location: login.php");
}
else {
  $logged = 1;
}
require_once '../etc/menu.php';

$usrlvl = $_SESSION['userlevel'];

if ($usrlvl > 1) {
  echo "<h3>Σφάλμα: Αυτή η ενέργεια μπορεί να γίνει μόνο από προϊστάμενο ή διαχειριστή...</h3>";
  echo "<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
  die();
}


if (isset($_POST['submit'])) {
  if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
    echo "<h3>" . "To αρχείο ". $_FILES['filename']['name'] ." ανέβηκε με επιτυχία." . "</h3>";

    //Import uploaded file to Database
    $path = $_FILES['filename']['tmp_name'];

    $objPHPExcel = PHPExcel_IOFactory::load($path);

    // get only 1st worksheet
    $worksheet = $objPHPExcel->getSheet(0);
    $worksheetTitle = $worksheet->getTitle();
    // if (!strcmp($worksheetTitle,'Κλάδοι'))
    //     continue;
    $highestRow = $worksheet->getHighestRow(); // e.g. 10
    if ($highestRow > $showRows)
        $highestRowprint = $showRows;
    else
        $highestRowprint = $highestRow;
    $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

    $nrColumns = ord($highestColumn) - 64;
    
    echo "<p>Το φύλλο '".$worksheetTitle."' έχει: ";
    echo $nrColumns . ' στήλες';
    echo ' και ' . $highestRow . ' γραμμές (<strong>' . ($highestRow-1) . ' εγγραφές</strong>).</p>';
    // if ($highestRow == 1)
    //     continue;
    echo '<h4>Δείγμα Δεδομένων (πρώτες '. $showRows . ' γραμμές):</h4> <table width="100%" cellpadding="1" cellspacing="0" border="1">';
    for ($row = 1; $row <= $highestRowprint; ++ $row) {
      echo '<tr>';
      for ($col = 0; $col < $highestColumnIndex; ++ $col) {
        $cell = $worksheet->getCellByColumnAndRow($col, $row);
        $val = $cell->getValue();
        $row_arr = array(0,1,2,3,4,5,6,7,8,9,10,11,12);
        if (!in_array($col, $row_arr))
            continue;
        if($row === 1){
            echo '<td style="background:#000; color:#fff;">' . $val . '</td>';
        }
        else
        {
            echo '<td>' . $val . '</td>';
        }
      }
      echo '</tr>';
    }
    echo '</table>';
    echo "<p>κλπ.</p>";

    // prepare connection
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

    // get the following variable values from parameters once
    // prepare hm/nia apoxwrhshs (endofyear)
    $hm_apox = date('Y-m-d',strtotime(getParam('endofyear2',$mysqlconnection)));
    // get hours
    $hours = getParam('yp_wr', $mysqlconnection);

    for ($row = 2; $row <= $highestRow; ++ $row) {
      $val=array();
      for ($col = 0; $col < $highestColumnIndex; ++ $col) {
        $cell = $worksheet->getCellByColumnAndRow($col, $row);
        $val[] = $cell->getValue();
      }
      $val[5] = ExcelToPHP($val[5]);
      $val[5] = date ("Y-m-d", $val[5]);

      // prepare query
      $sql="insert into ektaktoi(hm_apox, name, surname, patrwnymo, mhtrwnymo, klados, 
      hm_anal, afm, type, stathero, kinhto, email, praxi, 
      wres, status, ent_ty)
      values('$hm_apox','".$val[0] . "','" . $val[1] . "','" . $val[2]. "','" . $val[3]. "','" . $val[4]. 
      "','" . $val[5]. "','" . $val[6]. "','" . $val[7]. "','" . $val[8]. "','" . $val[9]. "','" . $val[10]. "','" .
       $val[11]. "', $hours, 1, '".$val[12]."')";

       //Run your mysqli_query
      // check if already inserted...
      $sql1 = "select afm from ektaktoi where afm=$val[6]";
      $result1 = mysqli_query($mysqlconnection, $sql1);
      if (mysqli_num_rows($result1)==0)
      {    
          $result = mysqli_query($mysqlconnection, $sql);
          if ($result)
              $count++;
          else echo "<br>".mysqli_error($mysqlconnection)."<br>";
      }
      else
          $inserted++;
      //echo $sql. "<br>";
    }
    echo "<h2><u>Αποτέλεσμα</u></h2>";
    if (!$count){
        echo "<h3>Δεν έγινε εισαγωγή εγγραφών...</h3>";
        echo $inserted ? "<i>($inserted εγγραφές έχουν ήδη καταχωρηθεί)</i><br><br>" : '';
    }
    else
        echo "<h3>Επιτυχής καταχώρηση $count εγγραφών...<h3><br><br>";

    echo "	<INPUT TYPE='button' VALUE='Εισαγωγή περισσότερων' onClick=\"parent.location='ektaktoi_import.php'\">";
    echo "	<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../employee/ektaktoi_list.php'\">";
  }
}
else {
  echo "<h2>Εισαγωγή αναπληρωτών εκπαιδευτικών από αρχείο excel</h2>";
  print "<p>Πραγματοποιήστε μαζική εισαγωγή αναπληρωτών εκπ/κών στο σύστημα.</p>\n";
  print '<p>Χρησιμοποιήστε το <a href="import_sample.xls">πρότυπο βιβλίο excel</a>, ακολουθώντας τις οδηγίες που βρίσκονται σε αυτό <small>(ως σχόλια στα κελιά των κεφαλίδων)</small>.</p>';
  print "<form enctype='multipart/form-data' action='' method='post'>";
  print "Aρχείο προς εισαγωγή:<br />\n";
  print "<input size='50' type='file' name='filename'><br />\n";
  print "<input type='submit' name='submit' value='Μεταφόρτωση'></form>";
  echo "<small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small><br><br>";
  echo "<a href='import.php'>Εισαγωγή μονίμων και σχολείων</a><br>";

  echo "	<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../employee/ektaktoi_list.php'\">";
  exit;
}
?>
</body>
</html>
