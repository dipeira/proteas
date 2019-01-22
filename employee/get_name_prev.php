<?php
// Helper for searching via surname in ektaktoi_prev.php

session_start();
header('Content-type: text/html; charset=iso8859-7');
require_once "../config.php";
$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($mysqlconnection, "SET NAMES 'greek'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");

//$q = strtolower($_GET["q"]);
$q = mb_strtolower($_GET["q"],'utf-8');
if (!$q) return;

$sql = "select DISTINCT surname from ektaktoi_old where surname LIKE '%$q%' AND sxoletos=".$_SESSION['sxoletos'];

//workaround for greek chars
$sql = mb_convert_encoding($sql, "iso-8859-7", "utf-8");

$rsd = mysqli_query($conn, $sql);
while($rs = mysqli_fetch_array($rsd)) {
	$cname = $rs['surname'];
	echo "$cname\n";
}
?>