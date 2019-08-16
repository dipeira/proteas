<?php
  require_once "../config.php";
  $conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  mysqli_query($conn, "SET NAMES 'greek'");
  mysqli_query($conn, "SET CHARACTER SET 'greek'");
  $text = mb_convert_encoding($_POST['request'], "iso-8859-7", "utf-8");
  $sname = getSchool($_POST['school'], $conn);
  $query = "INSERT INTO school_requests (request, school, school_name, submitted, sxol_etos) VALUES ('$text', '". $_POST['school']."','".$sname."', NOW(), $sxol_etos)";
  $result = mysqli_query($conn, $query);
  $ret = $result ? 'Επιτυχής καταχώρηση αιτήματος!' : 'Παρουσιάστηκε σφάλμα κατά την καταχώρηση';
  echo mb_convert_encoding($ret, "utf-8", "iso-8859-7");
?>