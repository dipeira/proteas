<?php
    header('Content-type: text/html; charset=utf-8'); 
    Require_once "config.php";
    Require_once "include/functions.php";

    require "tools/class.login.php";
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false) {   
        header("Location: tools/login.php");
    }
    else {
        $logged = 1;
    }        
    $usrlvl = $_SESSION['userlevel'];
?>

<head>
    <LINK href="css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Πρωτέας</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="js/jquery.js"></script>
    <!-- datatables -->
    <link rel="stylesheet" type="text/css" href="js/datatables/jquery.dataTables.min.css">
    <script type="text/javascript" language="javascript" src="js/datatables/jquery.dataTables.js"></script>
    <!-- buttons -->
    <link rel="stylesheet" type="text/css" href="js/datatables/buttons/buttons.dataTables.css">
    <script type="text/javascript" language="javascript" src="js/datatables/buttons/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/datatables/buttons/buttons.flash.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/datatables/buttons/jszip.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/datatables/buttons/buttons.html5.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/datatables/buttons/buttons.print.min.js"></script>
    <!-- fixedheader -->
    <link rel="stylesheet" type="text/css" href="js/datatables/fixedheader/fixedHeader.dataTables.css">
    <script type="text/javascript" language="javascript" src="js/datatables/fixedheader/dataTables.fixedHeader.js"></script>
    <script type="text/javascript" language="javascript" src="js/datatables/fixedheader/fixedHeader.dataTables.js"></script>

    <script type="text/javascript">   
    $(document).ready(function() {
        $('#example').DataTable( {
            "processing": true,
            "serverSide": true,
            "ajax": "js/datatables/ssp_index.php",
            fixedHeader: true,
            language: {
                url: 'js/datatables/greek.json'
            },
            pageLength: 15,
            lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "Όλες"]],
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'copy',
                    text: 'Αντιγραφή',
                },
                {
                    extend: 'excel',
                    text: 'Εξαγωγή σε excel',
                    filename: 'export'
                },
                {
                    extend: 'print',
                    text: 'Εκτύπωση',
                }
            ],
            "order": [[ 1, "asc" ]]
        } );
    } );
    </script>

</head>
<body> 
    <?php require 'etc/menu.php'; ?>


<table id="example" class="display imagetable" style="width:100%">
        <thead>
            <tr>
                <th>A/A</th>
                <th>Επώνυμο</th>
                <th>Όνομα</th>
                <th>Κλάδος</th>
                <th>Σχολείο Οργανικής</th>
                <th>Σχολείο Υπηρέτησης</th>
            </tr>
        </thead>
        
    </table>
    </body>
</html>