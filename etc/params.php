<?php
header('Content-type: text/html; charset=iso8859-7'); 
include('../config.php');

include("../tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false){
    header("Location: ../tools/login.php");
  }

 // check if super-user
// if ($_SESSION['userlevel']<>0)
//     header("Location: index.php");

define("PATHDRASTICTOOLS", "../tools/grid/");
include(PATHDRASTICTOOLS."conf.php");
include(PATHDRASTICTOOLS."drasticSrcMySQL.class.php");
$src = new drasticSrcMySQL($server, $user, $pw, $db, $table_opt);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />   
<link rel="stylesheet" type="text/css" href="../tools/grid/css/grid_default.css"/>
<title>Διαχείριση Παραμέτρων</title>
</head>
<body>
<?php include('../etc/menu.php'); ?>
    <h2>Παράμετροι</h2>
<script type="text/javascript" src="../tools/grid/js/mootools-1.2-core.js"></script>
<script type="text/javascript" src="../tools/grid/js/mootools-1.2-more.js"></script>
<script type="text/javascript" src="../tools/grid/js/drasticGrid.js"></script>

<div id="grid1"></div>
<script type="text/javascript">
var thegrid = new drasticGrid('grid1', {
    pathimg: "../tools/grid/img/",
	colwidth: "300"
    });
</script>

<table class="imagetable" border="1">
    <tr><th colspan="2">Επεξήγηση</th></tr>
    <tr><td><strong>Πεδίο</strong></td><td><strong>Περιγραφή</strong></td></tr>
    <tr><td>id</td><td>A/A <small>(δε μεταβάλλεται)</small></td></tr>
    <tr><td>name</td><td>Όνομα παραμέτρου <small>(Προσοχή: Να μη μεταβάλλεται)</small></td></tr>
    <tr><td>value</td><td>Τιμή παραμέτρου <small>(Αλλάζει κάνοντας κλικ στο μολύβι δεξιά)</small></td></tr>
    <tr><td>descr</td><td>Περιγραφή παραμέτρου</td></tr>
</table>
<form>
    <br>
<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
</form>

</body></html>