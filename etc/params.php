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
<title>���������� ����������</title>
</head>
<body>
<?php include('../etc/menu.php'); ?>
    <h2>����������</h2>
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
    <tr><th colspan="2">���������</th></tr>
    <tr><td><strong>�����</strong></td><td><strong>���������</strong></td></tr>
    <tr><td>id</td><td>A/A <small>(�� ������������)</small></td></tr>
    <tr><td>name</td><td>����� ���������� <small>(�������: �� �� ������������)</small></td></tr>
    <tr><td>value</td><td>���� ���������� <small>(������� �������� ���� ��� ������ �����)</small></td></tr>
    <tr><td>descr</td><td>��������� ����������</td></tr>
</table>
<form>
    <br>
<INPUT TYPE='button' class='btn-red' VALUE='���������' onClick="parent.location='../index.php'">
</form>

</body></html>