<?php
header('Content-type: text/html; charset=iso8859-7');
require_once "../config.php";
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($conn, "SET NAMES 'greek'");
mysqli_query($conn, "SET CHARACTER SET 'greek'");

$q = strtolower($_GET["q"]);
$type = $_GET['type'];
$title = $_GET['title'];
//$q = mb_strtolower($_GET["q"],'utf-8');
if (!$q) return;

if ($type > 0) {
  $sql = "select DISTINCT name from school where name LIKE '%$q%' AND type IN (0,$type)";
} elseif ($title) {
  $sql = "select DISTINCT titlos from school where titlos LIKE '%$q%'";
} else {
  $sql = "select DISTINCT name from school where name LIKE '%$q%'";
}
$sql = mb_convert_encoding($sql, "iso-8859-7", "utf-8");

$rsd = mysqli_query($conn,$sql);
while($rs = mysqli_fetch_array($rsd)) {
	$cname = $title ? $rs['titlos'] : $rs['name'];
	echo "$cname\n";
}
?>