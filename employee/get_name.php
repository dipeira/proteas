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

//$sql = "select DISTINCT surname from employee where surname LIKE '%$q%' LIMIT 0, 10";
// changed 13-11-2014: it now searches monimoi & ektaktoi and returns surname & 0 for mon, 1 for ekt
$sql = "select 0 as t_nm,surname from employee where surname LIKE '%$q%' LIMIT 0, 10 UNION select DISTINCT 1 as t_nm,surname from ektaktoi where surname LIKE '%$q%' LIMIT 0, 10";
//workaround for greek chars
$sql = mb_convert_encoding($sql, "iso-8859-7", "utf-8");

$rsd = mysql_query($sql,$conn);
while($rs = mysql_fetch_array($rsd)) {
	$cname = $rs['surname'];
        $ctbl = $rs['t_nm'];
	$out =  "$cname|$ctbl\n";
        echo $out;
        //echo json_encode($out);
}
?>