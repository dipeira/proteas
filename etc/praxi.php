<?php
header('Content-type: text/html; charset=iso8859-7'); 
require "tools/class.login.php";
  $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
}

 // check if super-user
// if ($_SESSION['userlevel']<>0)
//     header("Location: index.php");
 
define("PATHDRASTICTOOLS", "grid/");
require PATHDRASTICTOOLS."conf.php";
require PATHDRASTICTOOLS."drasticSrcMySQL.class.php";
$src = new drasticSrcMySQL($server, $user, $pw, $db, $table_pr);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <LINK href="style.css" rel="stylesheet" type="text/css">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />   
<link rel="stylesheet" type="text/css" href="grid/css/grid_default.css"/>
<title>Διαχείριση Πράξεων</title>
</head>
<body>
<script type="text/javascript" src="grid/js/mootools-1.2-core.js"></script>
<script type="text/javascript" src="grid/js/mootools-1.2-more.js"></script>
<script type="text/javascript" src="grid/js/drasticGrid.js"></script>

<div id="grid1"></div>
<script type="text/javascript">
var thegrid = new drasticGrid('grid1', {
    pathimg: "grid/img/",
    colwidth: "300"
    });
</script>

<table class="imagetable" border="1">
    <tr><th colspan="2">Επεξήγηση</th></tr>
    <tr><td><strong>Πεδίο</strong></td><td><strong>Περιγραφή</strong></td></tr>
    <tr><td>id</td><td>A/A (δε μεταβάλλεται)</td></tr>
    <tr><td>name</td><td>Όνομα πράξης - <small>Να είναι περιγραφικό & σύντομο π.χ. Ολοήμερο Β', Παράλληλη Γ' κλπ.<br>
                    Επίσης να περιέχει μία από τις λέξεις ολοήμερο, παράλληλη, εξειδικευμένη, ΕΑΕΠ, Ε.Α.Ε.Π., ΕΣΠΑ, <br>εξατομικευμένη, ειδική αγωγή, ΝΕΟ ΣΧΟΛΕΙΟ, ΕΚΟ ανάλογα με τη πράξη ΕΣΠΑ<br>
                            ή μία από τις λέξεις κρατικού, ΠΔΕ.</small></td></tr>
    <tr><td>ya</td><td>Υπουργική Απόφαση</td></tr>
    <tr><td>ada</td><td>ΑΔΑ<br><small>Να ξεκινάει με 'ΑΔΑ: '</small></td></tr>
    <tr><td>apofasi</td><td>Απόφαση τοποθέτησης <small>(Απόφαση Δ/ντη)</small></td></tr>
</table>
<form>
    <br>
<INPUT TYPE='button' VALUE='Επιστροφή' onClick="parent.location='ektaktoi_list.php'">
</form>

</body></html>
