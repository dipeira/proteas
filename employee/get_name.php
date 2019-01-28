<?php
header('Content-type: text/html; charset=iso8859-7');
require_once "../config.php";
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($conn, "SET NAMES 'greek'");
mysqli_query($conn, "SET CHARACTER SET 'greek'");

//$q = strtolower($_GET["q"]);
$q = mb_strtolower($_GET["q"],'utf-8');
if (strlen($q) < 2) {
  return;
} 

//$sql = "select DISTINCT surname from employee where surname LIKE '%$q%' LIMIT 0, 10";
// changed 13-11-2014: it now searches monimoi & ektaktoi and returns surname & 0 for mon, 1 for ekt
$sql = "select 0 as t_nm,surname from employee where surname LIKE '%$q%' LIMIT 0, 10 UNION select DISTINCT 1 as t_nm,surname from ektaktoi where surname LIKE '%$q%' LIMIT 0, 10";
//workaround for greek chars
$sql = mb_convert_encoding($sql, "iso-8859-7", "utf-8");

$rsd = mysqli_query($conn,$sql);
while($rs = mysqli_fetch_array($rsd)) {
  $postfix = $rs['t_nm'] ? ' (Αν)' : ' (Μον)';
  $cname = $rs['surname'].$postfix;
  $ctbl = $rs['t_nm'];
	$out =  "$cname|$ctbl\n";
  echo $out;
  //echo json_encode($out);
}
?>