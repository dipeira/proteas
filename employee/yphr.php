<?php
    header('Content-type: text/html; charset=iso8859-7'); 
    require_once"../config.php";
  require_once"../tools/functions.php";
  
function date2days($d)
{
    $d = strtotime($d);
    return date('d', $d) + date('m', $d)*30 + date('Y', $d)*360;
}
    
    $met_did = $_POST['met_did'];         
  $anatr= $_POST['anatr'];
  $proyp_not= $_POST['proyp_not'];

  // compute days of service
  $d1 = strtotime($_POST['yphr']);
  $result = (date('d', $d1) + date('m', $d1)*30 + date('Y', $d1)*360) - $anatr;
if ($result<=0) {
    die("Λάθος ημερομηνία");
}
  $ymd = days2ymd($result);    
  echo "<b>Συνολικός Χρόνος Υπηρεσίας:</b><br>Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2]<br>";

  
  
  ///////////////////
  // compute MK time
  // compute subtracted MK days
  $asked = date('Y-m-d', strtotime($_POST['yphr']));
  $start = date('Y-m-d', strtotime('2016-01-01'));
  $end = date('Y-m-d', strtotime('2017-12-31'));
  // if diorismos after 2016-01-01
if ($anatr > date2days($start)) {
    $subtract = $anatr - date2days($start);
    // if asked date > 2017-12-31
} elseif ($asked > $end) {
    $subtract = 720;
    // if asked between start & end
} elseif ($asked > $start && $asked < $end) {
    $subtract = date2days($asked) - date2days($start);
} else {
    $subtract = 0;
}
  // echo "subt: $subtract";
  
  // MSc / Phd
  // met: 4y, did: 12y, m+d: 12y
if ($met_did==1) {
        $anatr = $_POST['anatr'] - 1440;
} else if ($met_did==2) {
        $anatr = $_POST['anatr'] - 4320;
} else if ($met_did==3) {
        $anatr = $_POST['anatr'] - 4320;
} else {
        $anatr = $_POST['anatr'];
}
  // days for MK
  $result = date2days($_POST['yphr']) - $anatr - $subtract;

  $mk = mk16($result);
  //$res = mk16_plus($result);
  //$mk = $res[0];
  /*
  // Days to next MK: Left for later...
  $ymd = days2ymd($res[1]);
  $v99 = "-$tmp[1] day"; //may need fixing...
  $vdate = strtotime ( $v99 , $d1 );
  $vdate = date ( 'd-m-Y' , $vdate );
  echo "<br>MK: $mk <small>(από $vdate)</small>";
  */
  $ymd = days2ymd($result);    
  echo "<br><b>Χρόνος για Μ.Κ.:</b><br>Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2]";
  echo "&nbsp;(M.K.: $mk)<br>";

  // compute days of educational service for teaching hour reduction
  // find last day of year
  $year = substr($sxol_etos, 0, 4);
  $lastday = $year . '-12-31';
  $d1 = strtotime($lastday);
  
  
  $result = (date('d', $d1) + date('m', $d1)*30 + date('Y', $d1)*360) - $anatr - $proyp_not;
if ($result<=0) {
    die("Λάθος ημερομηνία");
}
  $ymd = days2ymd($result);
  $hours = get_wres($result);
  echo "<br><b>Χρόνος υπηρεσίας για μείωση ωραρίου:<br><small>(έως 31/12/$year)</small></b><br>Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2] &nbsp;($hours ώρες)<br>";
?>
