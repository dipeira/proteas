<?php
header('Content-type: text/html; charset=iso8859-7'); 
require_once"../config.php";

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
$options = array(
  "add_allowed" => false,       
  "delete_allowed" => false
);
$src = new drasticSrcMySQL($server, $user, $pw, $db, $table_slog, $options);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />   
<link rel="stylesheet" type="text/css" href="../tools/grid/css/grid_default.css"/>
<title>Αρχείο καταγραφής πρόσβασης σχολείων</title>
</head>
<body>
<?php require '../etc/menu.php'; ?>
<h2>Αρχείο καταγραφής πρόσβασης σχολείων</h2>
<script type="text/javascript" src="../tools/grid/js/mootools-1.2-core.js"></script>
<script type="text/javascript" src="../tools/grid/js/mootools-1.2-more.js"></script>
<script type="text/javascript" src="../tools/grid/js/drasticGrid.js"></script>

<div id="grid1"></div>
<script type="text/javascript">
var thegrid = new drasticGrid('grid1', {
    pathimg: "../tools/grid/img/",
    pagelength: 25,
    columns: [
      {name: 'id', displayname:'A/A', width: 30},
      {name: 'school_id', displayname:'Κωδ.Σχολείου', width: 100, editable: false},
      {name: 'school', displayname:'Όνομα Σχολείου', width: 350, editable: false},
      {name: 'action', displayname:'Ενέργεια', width: 250, editable: false},
      {name: 'datetime', displayname:'Ημ/νία - Ώρα', width: 150, editable: false},
    ]
});
</script>

<form>
<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
</form>

</body></html>
