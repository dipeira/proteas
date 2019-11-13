<?php
// Helper for searching via surname in ektaktoi_prev.php

session_start();
header('Content-type: text/html; charset=utf-8');
require_once "../config.php";
$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

//$q = strtolower($_GET["q"]);
$q = mb_strtolower($_GET["q"],'utf-8');
if (!$q) return;

$sql = "select DISTINCT surname from ektaktoi_old where surname LIKE '%$q%' AND sxoletos=".$_SESSION['sxoletos'];

$rsd = mysqli_query($mysqlconnection, $sql);
while($rs = mysqli_fetch_array($rsd)) {
	$cname = $rs['surname'];
	echo "$cname\n";
}
?>