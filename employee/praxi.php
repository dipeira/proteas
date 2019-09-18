<?php
header('Content-type: text/html; charset=iso8859-7'); 
require_once"../config.php";
require "../tools/class.login.php";
  $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
}
 
define("PATHDRASTICTOOLS", "../tools/grid/");
require PATHDRASTICTOOLS."conf.php";
require PATHDRASTICTOOLS."drasticSrcMySQL.class.php";
$src = new drasticSrcMySQL($server, $user, $pw, $db, $table_pr);

// Prepare $anapl_praxeis keys/values for DrasticGrid
$pr_values = $pr_labels = Array();
array_walk_recursive(
    $anapl_praxeis, function ($item, $key) use (&$pr_values, &$pr_labels) {
        $pr_values[] = '"'.$key.'"';
        $pr_labels[] = '"'.$item.'"';
    }
);
$pr_values = implode($pr_values, ',');
$pr_labels = implode($pr_labels, ',');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />   
<link rel="stylesheet" type="text/css" href="../tools/grid/css/grid_default.css"/>
<title>Διαχείριση Πράξεων</title>
</head>
<body>
<?php require '../etc/menu.php'; ?>
<h2>Διαχείριση Πράξεων</h2>
<script type="text/javascript" src="../tools/grid/js/mootools-1.2-core.js"></script>
<script type="text/javascript" src="../tools/grid/js/mootools-1.2-more.js"></script>
<script type="text/javascript" src="../tools/grid/js/drasticGrid.js"></script>

<div id="grid1"></div>
<script type="text/javascript">
var thegrid = new drasticGrid('grid1', {
    pathimg: "../tools/grid/img/",
    //colwidth: "300",
    pagelength:25,
    columns: [
      {name: 'id', displayname:'Α/Α', width: 30},
      {name: 'name', displayname:'Όνομα', width: 300},
      {name: 'ya', displayname:'Υπουργική Απόφαση', width: 150},
      {name: 'ada', displayname:'Α.Δ.Α. Υ.Α.', width: 150},
      {name: 'apofasi', displayname:'Απόφαση Δ/ντή', width: 150},
      {name: 'ada_apof', displayname:'Α.Δ.Α. Απόφασης', width: 150},
      {name: 'sxolio', displayname:'Σχόλια', width: 150},
      {name: 'type', displayname:'Τύπος',
        type: DDTYPEKEY, 
        values: [<?php echo $pr_values; ?>],
        labels:  [<?php echo $pr_labels; ?>],
        width: 150
      }
    ]//,
    // onUpdateStart: function(id, colname, value) {
    //     if (id == 0 ) {
    //         alert('Σφάλμα: Η πρώτη γραμμή δεν μπορεί να μεταβληθεί...');
    //         this.do_update = false;
    //     }
    //     else this.do_update = true;
    // }
});
</script>

<table class="imagetable stable" border="1">
    <tr><th colspan="2">Επεξήγηση</th></tr>
    <tr><td><strong>Πεδίο</strong></td><td><strong>Περιγραφή</strong></td></tr>
    <tr><td>Όνομα πράξης</td><td>Να είναι περιγραφικό & σύντομο π.χ. Ολοήμερο Β', Παράλληλη Γ' κλπ.</td></tr>
    <tr><td>Υπουργική Απόφαση</td><td></td></tr>
    <tr><td>ΑΔΑ Υ.Α. </td><td>Να αναγράφεται μόνο ο ΑΔΑ της Υ.Α., π.χ. 6ΠΜΦ4653ΠΣ-4ΝΠ</td></tr>
    <tr><td>Απόφαση Δ/ντη</td><td>Απόφαση τοποθέτησης</td></tr>
    <tr><td>ΑΔΑ Απόφασης</td><td>Να αναγράφεται μόνο ο ΑΔΑ απόφασης τοποθέτησης, π.χ. 6ΠΜΦ4653ΠΣ-4ΝΠ</td></tr>
    <tr><td>Τύπος πράξης</td><td>(Επιλέξτε από τη λίστα)</td></tr>
</table>
<p>ΠΡΟΣΟΧΗ: Η πρώτη γραμμή να μη διαγράφεται!</p>
<form>
<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='ektaktoi_list.php'">
</form>

</body></html>
