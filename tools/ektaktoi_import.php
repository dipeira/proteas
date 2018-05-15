<?php
header('Content-type: text/html; charset=iso8859-7');

require_once 'PHPExcel.php';
require_once 'PHPExcel/IOFactory.php';
require_once "../config.php";
require_once "./functions.php";

$showRows = 10;

if (isset($_POST['submit'])) {
    if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
        echo "<h3>" . "To αρχείο ". $_FILES['filename']['name'] ." ανέβηκε με επιτυχία." . "</h3>";

        //Import uploaded file to Database
        $path = $_FILES['filename']['tmp_name'];

        $objPHPExcel = PHPExcel_IOFactory::load($path);

            // get only 1st worksheet
            $worksheet = $objPHPExcel->getSheet(0);
            $worksheetTitle = $worksheet->getTitle();
            $worksheetTitle = mb_convert_encoding($worksheetTitle, "iso-8859-7", "utf-8");
            if (!strcmp($worksheetTitle,'Κλάδοι'))
                continue;
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            if ($highestRow > $showRows)
                $highestRowprint = $showRows;
            else
                $highestRowprint = $highestRow;
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

            $nrColumns = ord($highestColumn) - 64;
            
            echo "<br>Το φύλλο '".$worksheetTitle."' έχει: ";
            echo $nrColumns . ' στήλες';
            echo ' και ' . $highestRow . ' γραμμές (<strong>' . ($highestRow-1) . ' εγγραφές</strong>).';
            if ($highestRow == 1)
                continue;
            echo '<br>Δείγμα Δεδομένων (πρώτες '. $showRows . ' γραμμές): <table width="100%" cellpadding="1" cellspacing="0" border="1">';
            for ($row = 1; $row <= $highestRowprint; ++ $row) {
                echo '<tr>';
                for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    $row_arr = array(1,2,3,4,5,12,17,18,19,20,21,23);
                    if (!in_array($col, $row_arr))
                        continue;
                    if($row === 1){
                        $val = mb_convert_encoding($val, "iso-8859-7", "utf-8");
                        echo '<td style="background:#000; color:#fff;">' . $val . '</td>';
                    }
                    else
                    {
                        $val = mb_convert_encoding($val, "iso-8859-7", "utf-8");
                        echo '<td>' . $val . '</td>';
                    }
                }
                echo '</tr>';
            }
            echo '</table>';
            echo "<br>κλπ.<br>";

            for ($row = 2; $row <= $highestRow; ++ $row) {
                $val=array();
                for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val[] = $cell->getValue();
                }
                $val[12] = ExcelToPHP($val[12]);
                $val[12] = date ("Y-m-d", $val[12]);
                $sql="insert into ektaktoi(name, surname, patrwnymo, mhtrwnymo, klados, hm_anal, ya, apofasi, comments, status, afm, type, stathero, kinhto, praxi)
                values('".$val[1] . "','" . $val[2] . "','" . $val[3]. "','" . $val[4]. "','" . $val[5]. "','" . $val[12]. "','" . $val[13]. "','" . $val[14]. "','" . $val[16]. "','" . $val[17]. "','" . $val[18]. "','" . $val[19]. "','" . $val[20]. "','" . $val[21]. "','" . $val[23]. "')";

                //Run your mysql_query
                $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
                mysql_select_db($db_name, $mysqlconnection);
                mysql_query("SET NAMES 'greek'", $mysqlconnection);
                mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
                $sql = mb_convert_encoding($sql, "iso-8859-7", "utf-8");
                // check if already inserted...
                $sql1 = "select afm from ektaktoi where afm=$val[18]";
                $result1 = mysql_query($sql1, $mysqlconnection);
                if (mysql_num_rows($result1)==0)
                {    
                    $result = mysql_query($sql, $mysqlconnection);
                    if ($result)
                        $count++;
                }
                else
                    $inserted++;
                //echo $sql. "<br>";
            }
        if (!$count)
            echo "<h3>Δεν έγινε εισαγωγή εγγραφών...</h3><i>($inserted εγγραφές έχουν ήδη καταχωρηθεί)</i><br><br>";
        else
            echo "<br><h3>Επιτυχής καταχώρηση $count εγγραφών...<h3><br><br>";

        echo "	<INPUT TYPE='button' VALUE='Εισαγωγή περισσότερων' onClick=\"parent.location='ektaktoi_import.php'\">";
        echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='../ektaktoi_list.php'\">";
    }
}
else{
    echo "<h2>Εισαγωγή εκπαιδευτικών από αρχείο excel</h2>";
    print "Πραγματοποιήστε μαζική εισαγωγή έκτακτων εκπ/κών (αναπληρωτών) στο σύστημα.<br><br>\n";
    print 'Χρησιμοποιήστε το <a href="import_sample.xls">πρότυπο βιβλίο excel</a>, ακολουθώντας τις οδηγίες που βρίσκονται σε αυτό <small>(ως σχόλια στα κελιά των κεφαλίδων)</small>.<br><br>';
    print "<form enctype='multipart/form-data' action='' method='post'>";
    print "Aρχείο προς εισαγωγή:<br />\n";
    print "<input size='50' type='file' name='filename'><br />\n";
    print "<input type='submit' name='submit' value='Μεταφόρτωση'></form>";
    echo "<small>ΣΗΜ.: Η εισαγωγή ενδέχεται να διαρκέσει μερικά λεπτά, ειδικά για μεγάλα αρχεία.<br>Μη φύγετε από τη σελίδα αν δεν πάρετε κάποιο μήνυμα.</small><br><br>";

    echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='../employee/ektaktoi_list.php'\">";
    exit;
}
?>
