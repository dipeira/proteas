<?php
    header('Content-type: text/html; charset=iso8859-7'); 
    require_once"../config.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Σχετικά</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="../css/style.css" />
</head>
<body>
    <?php require '../etc/menu.php'; ?>
    <h2>Σχετικά</h2>
    <p>
        Το project <b>Πρωτέας</b> σχεδιάστηκε και αναπτύσσεται από τον εκπαιδευτικό Πληροφορικής Δ.Ε. ΠΕ20 Βαγγέλη Ζαχαριουδάκη <br>
        από το σχολικό έτος 2011-2012 έως σήμερα, για λογαριασμό της Δ/νσης Πρωτοβάθμιας Εκπαίδευσης Ν.Ηρακλείου.
        <br><br>
        Η ανάπτυξη γίνεται εξ'ολοκλήρου με <a href="http://www.gnu.org/philosophy/free-sw.html">λογισμικό ανοιχτού κώδικα (open-source)</a>.<br>

        Τα εργαλεία που χρησιμοποιούνται είναι η γλώσσα <a href="http://www.php.net/">PHP</a>, η βάση δεδομένων <a href="http://www.mysql.com/">MySQL</a> και ο web server <a href="http://www.apache.org/">Apache</a>.
        <br><br><br>
        Βιβλιοθήκες - εργαλεία που χρησιμοποιούνται:
        <ul>
            <li><a href="https://jquery.com/">jQuery</a></li>
            <li><a href="https://datatables.net/">DataTables</a></li>
            <li><a href="http://www.triconsole.com/php/calendar_datepicker.php">PHP Calandar Date Picker</a></li>
            <li><a href="https://select2.org/">Select2</a></li>
            <li><a href="https://github.com/jmosbech/StickyTableHeaders">StickyTableHeaders</a></li>
            <li><a href="https://www.linux.com/news/quickly-put-data-mysql-web-drasticgrid">DrasticGrid</a></li>
            <li><a href="https://github.com/PHPOffice/PHPExcel">PHPExcel</a></li>
            <li><a href="https://github.com/PHPOffice/PHPWord">PHPWord</a></li>
            <li><a href="https://swiftmailer.symfony.com/">SwiftMailer</a></li>
            <li><a href="http://www.emirplicanic.com/php/simple-phpmysql-authentication-class">Simple PHP/MySQL authentication/login class</a></li>
            <li><a href="https://code.tutsplus.com/tutorials/how-to-paginate-data-with-php--net-2928">Paginator</a></li>            
        </ul>
        <br>
        <p>Επικοινωνία με το δημιουργό: sugarv@sch.gr</p>
        <p>Ο Πρωτέας στο GitHub: <a href='https://github.com/dipeira/proteas' target="_blank"><img style='width:32px;' src="../images/github.png" alt=""></a></p>
    </p>
    <INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
</body>
</html>
