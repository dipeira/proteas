<?php
header('Content-type: text/html; charset=utf-8');
require_once "../config.php";
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");

//$q = strtolower($_GET["q"]);
$idiwtikoi = $_GET['idiwtikoi'];
$q = mb_strtolower($_GET["q"],'utf-8');
if (strlen($q) < 2) {
  return;
} 

//$sql = "select DISTINCT surname from employee where surname LIKE '%$q%' LIMIT 0, 10";
// changed 13-11-2014: it now searches monimoi & ektaktoi and returns surname & 0 for mon, 1 for ekt
if ($idiwtikoi){
  $sql = "select 0 as t_nm,surname from employee where thesi in (5,6) AND surname LIKE '%$q%' LIMIT 0, 10";
} else {
  $sql = "(select 0 as t_nm,surname from employee where thesi NOT IN (5,6) AND surname LIKE '%$q%' LIMIT 0, 10) UNION (select DISTINCT 1 as t_nm,surname from ektaktoi where surname LIKE '%$q%' LIMIT 0, 10)";
}


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