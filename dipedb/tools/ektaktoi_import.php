<?php
header('Content-type: text/html; charset=iso8859-7');

require 'PHPExcel.php';
require_once 'PHPExcel/IOFactory.php';
require_once '../functions.php';

$path = "import.xls";


$objPHPExcel = PHPExcel_IOFactory::load($path);

foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
    $worksheetTitle = $worksheet->getTitle();
    $highestRow = $worksheet->getHighestRow(); // e.g. 10
    if ($highestRow > 10)
        $highestRowprint = 10;
    else
        $highestRowprint = $highestRow;
    $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

    $nrColumns = ord($highestColumn) - 64;

    echo "<br>Το φύλλο ".$worksheetTitle." έχει: ";
    echo $nrColumns . ' στήλες';
    echo ' και ' . $highestRow . ' γραμμές.';

    echo '<br>Δείγμα Δεδομένων (πρώτες γραμμές): <table width="100%" cellpadding="3" cellspacing="0">';
    for ($row = 1; $row <= $highestRowprint; ++ $row) {
        echo '<tr>';
        for ($col = 0; $col < $highestColumnIndex; ++ $col) {
            $cell = $worksheet->getCellByColumnAndRow($col, $row);
            $val = $cell->getValue();
            if($row === 1)
                echo '<td style="background:#000; color:#fff;">' . $val . '</td>';
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
        $sql="insert into ektaktoi(name, surname, patrwnymo, mhtrwnymo, klados, hm_anal, ya, apofasi, comments, status, afm, type, stathero, kinhto)
        values('".$val[1] . "','" . $val[2] . "','" . $val[3]. "','" . $val[4]. "','" . $val[5]. "','" . $val[12]. "','" . $val[13]. "','" . $val[14]. "','" . $val[16]. "','" . $val[17]. "','" . $val[18]. "','" . $val[19]. "','" . $val[20]. "','" . $val[21]. "')";
        
        //Run your mysql_query
        Require "../config.php";
        $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
        mysql_select_db($db_name, $mysqlconnection);
        mysql_query("SET NAMES 'greek'", $mysqlconnection);
        mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
        $sql = mb_convert_encoding($sql, "iso-8859-7", "utf-8");
        // check if already inserted...
        $sql1 = "select afm from ektaktoi where afm= $val[18]";
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
}
if (!$count)
    echo "<h3>Δεν έγινε εισαγωγή εγγραφών...</h3>($inserted εγγραφές έχουν ήδη καταχωρηθεί)<br>";
else
    echo "<br><h3>Επιτυχής καταχώρηση $count εγγραφών...<h3><br>";

echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='../ektaktoi_list.php'\">";
//echo "  <meta http-equiv=\"refresh\" content=\"10; URL=../ektaktoi_list.php\">";
?>
