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
$src = new drasticSrcMySQL($server, $user, $pw, $db, 'symvouloi');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <?php 
    $root_path = '../';
    $page_title = 'Σύμβουλοι - Ενότητες';
    require '../etc/head.php'; 
    ?>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
<link rel="stylesheet" type="text/css" href="../tools/grid/css/grid_default.css"/>
<LINK href="../css/style.css" rel="stylesheet" type="text/css">
</head><body>
<?php require '../etc/menu.php'; ?>
    <h2>Σύμβουλοι - Ενότητες</h2>
<script type="text/javascript" src="../tools/grid/js/mootools-1.2-core.js"></script>
<script type="text/javascript" src="../tools/grid/js/mootools-1.2-more.js"></script>
<script type="text/javascript" src="../tools/grid/js/drasticGrid.js"></script>

<div id="grid1"></div>
<script type="text/javascript">
var thegrid = new drasticGrid('grid1', {
  pathimg: "../tools/grid/img/",
  columns: [
      {name: 'perif', displayname:'Ενότητα', width: 100},
      {name: 'emp_id', displayname:'Α/Α συμβούλου', width: 200},
  ]
});
</script>

<form>
<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
</form>

</body></html>
