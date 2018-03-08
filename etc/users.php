<?php
header('Content-type: text/html; charset=iso8859-7'); 
include("../tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false){
    header("Location: ../tools/login_check.php");
  }

 // check if super-user
  if ($_SESSION['userlevel']<>0)
     header("Location: ../index.php");
 
define("PATHDRASTICTOOLS", "../tools/grid/");
include(PATHDRASTICTOOLS."conf.php");
include(PATHDRASTICTOOLS."drasticSrcMySQL.class.php");
$src = new drasticSrcMySQL($server, $user, $pw, $db, $table_log);
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
    <tr><th colspan="2">Userlevels</th></tr>
    <tr><td>Userlevel</td><td>Δικαιώματα</td></tr>
    <tr><td>0</td><td>Διαχειριστής</td></tr>
    <tr><td>1</td><td>Προσθήκη / Διαγραφή / Επεξεργασία Υπαλλήλων & Άδειών</td></tr>
    <tr><td>2</td><td>Επεξεργασία Υπαλλήλων & Άδειών</td></tr>
    <tr><td>3</td><td>Μόνο Προβολή Υπαλλήλων & Άδειών</td></tr>
</table>
<form>
    <br>
<INPUT TYPE='button' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
</form>

</body></html>