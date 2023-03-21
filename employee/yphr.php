<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once"../include/functions.php";
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  $id = $_POST['id'];
  $anatr = get_anatr($id, $mysqlconnection);
  
  // compute days of service
  $d1 = strtotime($_POST['yphr']);
  $result = (date('d', $d1) + date('m', $d1)*30 + date('Y', $d1)*360) - $anatr;
  if ($result<=0) {
    die("Λάθος ημερομηνία");
  }
  $ymd = days2ymd($result);    
  echo "<b>Συνολικός Χρόνος Υπηρεσίας:</b><br>Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2]<br>";
  
  // print MK
  $mk = get_mk($id, $mysqlconnection, $_POST['yphr']);
  $ymd = $mk['ymd'];
  echo "<br><b>Χρόνος για Μ.Κ.:</b><br>Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2]";
  echo "&nbsp;(M.K.: ".$mk['mk'].")<br>";
  echo "Ημ/νία επόμενου Μ.Κ.: ".$mk['hm_mk'].'<br>';
  
  // compute days of educational service for teaching hour reduction
  // find last day of year
  $year = date('Y', strtotime($_POST['yphr']));
  $lastday = $year . '-12-31';
  $d1 = strtotime($lastday);
  $result = (date('d', $d1) + date('m', $d1)*30 + date('Y', $d1)*360) - $anatr - $_POST['proyp_not'];
  if ($result<=0) {
    die("Λάθος ημερομηνία");
  }
  $ymd = days2ymd($result);
  $hours = get_wres($result);
  echo "<br><b>Χρόνος υπηρεσίας για μείωση ωραρίου:<br><small>(έως 31/12/$year)</small></b><br>Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2] &nbsp;($hours ώρες)<br>";
?>
