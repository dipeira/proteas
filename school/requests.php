<?php
header('Content-type: text/html; charset=iso8859-7'); 
require_once"../config.php";

require "../tools/class.login.php";
$log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
  header("Location: ../tools/login.php");
}

if (!$_SESSION['requests']) {
  echo "<h3>Σφάλμα: Δεν έχετε δικαίωμα προβολής αιτημάτων σχολείων...</h3>";
  die("<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">");
}

define("PATHDRASTICTOOLS", "../tools/grid/");
require PATHDRASTICTOOLS."conf.php";
require PATHDRASTICTOOLS."drasticSrcMySQL.class.php";
$src = new drasticSrcMySQL($server, $user, $pw, $db, $table_req);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />   
<link rel="stylesheet" type="text/css" href="../tools/grid/css/grid_default.css"/>
<title>Διαχείριση Αιτημάτων</title>
</head>
<body>
<?php require '../etc/menu.php'; ?>
<h2>Διαχείριση Αιτημάτων Σχολείων</h2>
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
      {name: 'id', displayname:'A/A', width: 15},
      {name: 'school_name', displayname:'Σχολείο', width: 150, editable: false},
      {name: 'request', displayname:'Αίτημα', width: 300, editable: false},
      {name: 'comment', displayname:'Σχόλιο Δ/νσης', width: 300},
      {name: 'done', displayname:'Διεκπεραίωση', width: 110,type: DDTYPEKEY, 
         values: [0,1],
         labels:  ['Όχι', 'Ναι']
      },
      {name: 'submitted', displayname:'Ημ/νία Υποβολής', width: 150, editable: false},
      {name: 'sxol_etos', displayname:'Σχ.Έτος', width: 60, editable: false}
    ]
});
</script>

<form>
<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
</form>

</body></html>
