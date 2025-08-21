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
    $count = $inserted = 0;
    
    // Check if all 3 files are uploaded
    if (!empty($_FILES['proslipsi']['tmp_name']) && 
        !empty($_FILES['analipsi']['tmp_name']))  {
        
        // Load the files
        $proslipsi_path = $_FILES['proslipsi']['tmp_name'];
        $analipsi_path = $_FILES['analipsi']['tmp_name'];
        
        // Validate files by checking headers
        $proslipsi_excel = PHPExcel_IOFactory::load($proslipsi_path);
        $analipsi_excel = PHPExcel_IOFactory::load($analipsi_path);
        
        // Get first worksheets
        $proslipsi_sheet = $proslipsi_excel->getSheet(0);
        $analipsi_sheet = $analipsi_excel->getSheet(0);
        
        // Validate headers
        if ($proslipsi_sheet->getCellByColumnAndRow(1, 1)->getValue() != 'Α/Α ΡΟΗΣ' ||
            $analipsi_sheet->getCellByColumnAndRow(9, 1)->getValue() != 'ΗΜ. ΑΝΑΛΗΨΗΣ') {
            die("Σφάλμα: Λάθος μορφή αρχείων. Ελέγξτε τις επικεφαλίδες.");
        }

        // Process files and create arrays
        $new_anaplirotes = array();
        $analipseis = array();

        // Prepare connection
        $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
        mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
        mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

        $perioxh = getParam('perioxh',$mysqlconnection);
        // Process proslipsi file
        $highestRow = $proslipsi_sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            if (trim($proslipsi_sheet->getCellByColumnAndRow(15, $row)->getValue()) != $perioxh) {
              continue;
            }
            $afm = $proslipsi_sheet->getCellByColumnAndRow(3, $row)->getValue();
            $new_anaplirotes[$afm] = array(
                'name' => $proslipsi_sheet->getCellByColumnAndRow(5, $row)->getValue(),
                'surname' => $proslipsi_sheet->getCellByColumnAndRow(4, $row)->getValue(),
                'patrwnymo' => $proslipsi_sheet->getCellByColumnAndRow(6, $row)->getValue(),
                'mhtrwnymo' => $proslipsi_sheet->getCellByColumnAndRow(7, $row)->getValue(),
                'klados' => $proslipsi_sheet->getCellByColumnAndRow(8, $row)->getValue(),
                'stathero' => $proslipsi_sheet->getCellByColumnAndRow(20, $row)->getValue(),
                'kinhto' => $proslipsi_sheet->getCellByColumnAndRow(21, $row)->getValue(),
                'email' => $proslipsi_sheet->getCellByColumnAndRow(22, $row)->getValue(),
                'hours' => $proslipsi_sheet->getCellByColumnAndRow(14, $row)->getValue() =='ΑΠΩ' ?  24 : 15
            );
        }
        
        // Process analipsi file
        $highestRow = $analipsi_sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $afm = $analipsi_sheet->getCellByColumnAndRow(4, $row)->getValue();
            $hm_anal = $analipsi_sheet->getCellByColumnAndRow(9, $row)->getValue();
            $analipseis[$afm] = PHPExcel_Style_NumberFormat::toFormattedString($hm_anal, 'YYYY-MM-DD');
        }
        
        // Get parameters
        $hm_apox = date('Y-m-d',strtotime(getParam('endofyear2',$mysqlconnection)));

        // Process and insert data
        foreach ($new_anaplirotes as $afm => $data) {
            if (isset($analipseis[$afm])) {
                // Check if already exists
                $sql1 = "SELECT afm FROM ektaktoi WHERE afm='$afm'";
                $result1 = mysqli_query($mysqlconnection, $sql1);
                
                if (mysqli_num_rows($result1) == 0) {
                    $sql = "INSERT INTO ektaktoi(
                        hm_apox, name, surname, patrwnymo, mhtrwnymo, 
                        klados, hm_anal, afm, type, stathero, 
                        kinhto, email, praxi, wres, status, ent_ty, sx_yphrethshs
                    ) VALUES (
                        '$hm_apox',
                        '{$data['name']}',
                        '{$data['surname']}',
                        '{$data['patrwnymo']}',
                        '{$data['mhtrwnymo']}',
                        '".getKladosFromDescription($data['klados'], $mysqlconnection)."',
                        '{$analipseis[$afm]}',
                        '$afm',
                        '3',
                        '{$data['stathero']}',
                        '{$data['kinhto']}',
                        '{$data['email']}',
                        '1',
                        '{$data['hours']}',
                        1,
                        0,
                        ".getSchoolID('Διάθεση ΠΥΣΠΕ', $mysqlconnection)."
                    )";
                    // echo $sql;
                    // $count++;
                    if (mysqli_query($mysqlconnection, $sql)) {
                       $count++;
                    } else {
                       echo "<br>Σφάλμα για ΑΦΜ $afm: " . mysqli_error($mysqlconnection) . "<br>";
                    }
                } else {
                    $inserted++;
                }
            }
        }
        
        echo "<h2><u>Αποτέλεσμα</u></h2>";
        if (!$count) {
            echo "<h3>Δεν έγινε εισαγωγή εγγραφών...</h3>";
            echo $inserted ? "<i>($inserted εγγραφές έχουν ήδη καταχωρηθεί)</i><br><br>" : '';
        } else {
            echo "<h3>Επιτυχής καταχώρηση $count εγγραφών...</h3><br><br>";
        }
    } else {
        echo "<h3>Σφάλμα: Πρέπει να ανεβάσετε και τα 3 αρχεία.</h3>";
    }
    
    echo "<INPUT TYPE='button' VALUE='Εισαγωγή περισσότερων' onClick=\"parent.location='ektaktoi_import_minedu.php'\">";
    echo "<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../employee/ektaktoi_list.php'\">";
} else {
    // Display upload form
?>
    <h2>Εισαγωγή αναπληρωτών εκπαιδευτικών από αρχεία excel</h2>
    <p>Πραγματοποιήστε μαζική εισαγωγή αναπληρωτών εκπ/κών στο σύστημα.</p>
    <p>Απαιτούνται 3 αρχεία excel:</p>
    <ul>
        <li>Αρχείο προσλήψεων (με στήλη "Α/Α ΡΟΗΣ")</li>
        <li>Αρχείο αναλήψεων (με στήλη "ΗΜ. ΑΝΑΛΗΨΗΣ")</li>
    </ul>
    
    <form enctype='multipart/form-data' action='' method='post'>
        <p>Αρχείο προσλήψεων:<br>
        <input size='50' type='file' name='proslipsi'></p>
        
        <p>Αρχείο αναλήψεων:<br>
        <input size='50' type='file' name='analipsi'></p>
        
        <input type='submit' name='submit' value='Μεταφόρτωση'>
    </form>
    
    <small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά.<br>
    Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small><br><br>
    
    <a href='import.php'>Εισαγωγή μονίμων και σχολείων</a><br>
    <INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../employee/ektaktoi_list.php'">
<?php
}
?>
</body>
</html>
