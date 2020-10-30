<?php
header('Content-type: text/html; charset=utf-8'); 
require '../config.php';
require "../tools/class.login.php";
  $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
}

 // check if super-user
if ($_SESSION['userlevel']<>0) {
    header("Location: ../index.php");
}

define("PATHDRASTICTOOLS", "../tools/grid/");
require PATHDRASTICTOOLS."conf.php";
require PATHDRASTICTOOLS."drasticSrcMySQL.class.php";
$src = new drasticSrcMySQL($server, $user, $pw, $db, $table_log);

$showpass = isset($_GET['showpass']) ? 'true' :  'false';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />   
<link rel="stylesheet" type="text/css" href="../tools/grid/css/grid_default.css"/>
<title>Διαχείριση Χρηστών</title>
</head>
<body>
<?php require '../etc/menu.php'; ?>
    <h2>Διαχείριση Χρηστών</h2>
    <?php echo $showpass == 'true' ? "<p><small><a href='users.php'>Απόκρυψη κωδικών χρηστών</a></small></p>" : "<p><small><a href='users.php?showpass=1'>Εμφάνιση κωδικών χρηστών</a></small></p>"; ?>
<script type="text/javascript" src="../tools/grid/js/mootools-1.2-core.js"></script>
<script type="text/javascript" src="../tools/grid/js/mootools-1.2-more.js"></script>
<script type="text/javascript" src="../tools/grid/js/drasticGrid.js"></script>

<div id="grid1"></div>
<script type="text/javascript">
    var columns = [
        {name: 'userid', displayname:'A/A', width: 30},
        {name: 'username', displayname:'Ον.Χρήστη', width: 100},
        {name: 'useremail', displayname:'email', width: 130},
        {name: 'userlevel', displayname:'Ρόλος', width: 110,
            type: DDTYPEKEY, 
            values: [0,1,2,3],
            labels:  ['Διαχειριστής', 'Προϊστάμενος','Υπάλληλος','Χρήστης']
        },
        {name: 'requests', displayname:'Αιτήματα Σχολείων', width: 140},
        {name: 'adeia', displayname:'Άδειες', width: 60},
        {name: 'lastlogin', displayname:'Τελευταία Είσοδος', width: 150, editable: false}
        ];
    // if passwords should be displayed, add them to columns
    if (<?= $showpass?> == 1){
        columns.splice(2, 0, {name: 'password', displayname:'Κωδικός', width: 100});
    }
    var thegrid = new drasticGrid('grid1', {
        pathimg: "../tools/grid/img/",
        columns: columns
    });
</script>

<h3>Ρόλοι</h3>
<table class="imagetable stable" border="1">
    <tr><th>Ρόλος</th><th>Δικαιώματα</th></tr>
    <tr><td>Διαχειριστής</td><td>Πλήρης πρόσβαση σε όλα</td></tr>
    <tr><td>Προϊστάμενος</td><td>Προσθήκη / Διαγραφή / Επεξεργασία Υπαλλήλων & Άδειών</td></tr>
    <tr><td>Υπάλληλος</td><td>Επεξεργασία Υπαλλήλων & Άδειών</td></tr>
    <tr><td>Χρήστης</td><td>Μόνο Προβολή Υπαλλήλων & Άδειών</td></tr>
</table>
<form>
    <br>
<INPUT TYPE='button' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
</form>

</body></html>
