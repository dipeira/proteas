<?php
define("PATHDRASTICTOOLS", "grid/");
include(PATHDRASTICTOOLS."conf.php");
include(PATHDRASTICTOOLS."drasticSrcMySQL.class.php");
$src = new drasticSrcMySQL($server, $user, $pw, $db, $table_emp);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
<link rel="stylesheet" type="text/css" href="grid/css/grid_default.css"/>
<title>Σχολικές Μονάδες</title>
</head><body>
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

<form>
<INPUT TYPE='button' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
</form>

</body></html>