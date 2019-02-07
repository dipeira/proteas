<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"../config.php";
  require_once"../tools/functions.php";
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  $id = $_POST['id'];
  $anatr = get_anatr($id, $mysqlconnection);
  
  // compute days of service
  $d1 = strtotime($_POST['yphr']);
  $result = (date('d', $d1) + date('m', $d1)*30 + date('Y', $d1)*360) - $anatr;
  if ($result<=0) {
    die("����� ����������");
  }
  $ymd = days2ymd($result);    
  echo "<b>��������� ������ ���������:</b><br>���: $ymd[0] &nbsp; �����: $ymd[1] &nbsp; ������: $ymd[2]<br>";
  
  // print MK
  $mk = get_mk($id, $mysqlconnection, $yphr);
  $ymd = $mk['ymd'];
  echo "<br><b>������ ��� �.�.:</b><br>���: $ymd[0] &nbsp; �����: $ymd[1] &nbsp; ������: $ymd[2]";
  echo "&nbsp;(M.K.: ".$mk['mk'].")<br>";

  // compute days of educational service for teaching hour reduction
  // find last day of year
  $year = substr($sxol_etos, 0, 4);
  $lastday = $year . '-12-31';
  $d1 = strtotime($lastday);
  
  $result = (date('d', $d1) + date('m', $d1)*30 + date('Y', $d1)*360) - $anatr - $proyp_not;
  if ($result<=0) {
    die("����� ����������");
  }
  $ymd = days2ymd($result);
  $hours = get_wres($result);
  echo "<br><b>������ ��������� ��� ������ �������:<br><small>(��� 31/12/$year)</small></b><br>���: $ymd[0] &nbsp; �����: $ymd[1] &nbsp; ������: $ymd[2] &nbsp;($hours ����)<br>";
?>
