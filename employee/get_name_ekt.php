<?php
header('Content-type: text/html; charset=iso8859-7');
require_once "../config.php";
$conn = mysql_connect($db_host, $db_user, $db_password) or die ('Error connecting to mysql');
mysql_select_db($db_name);
mysql_query("SET NAMES 'greek'", $conn);
mysql_query("SET CHARACTER SET 'greek'", $conn);

//$q = strtolower($_GET["q"]);
$q = mb_strtolower($_GET["q"],'utf-8');
if (!$q) return;

$sql = "select DISTINCT surname from ektaktoi where surname LIKE '%$q%'";
//workaround for greek chars
$sql = mb_convert_encoding($sql, "iso-8859-7", "utf-8");

$rsd = mysql_query($sql,$conn);
while($rs = mysql_fetch_array($rsd)) {
	$cname = $rs['surname'];
	echo "$cname\n";
}
?>