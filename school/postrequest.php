<?php
require_once "../config.php";
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_query($conn, "SET NAMES 'greek'");
mysqli_query($conn, "SET CHARACTER SET 'greek'");
if ($_POST['type'] == 'insert'){
  $text = mb_convert_encoding($_POST['request'], "iso-8859-7", "utf-8");
  $sname = getSchool($_POST['school'], $conn);
  $query = "INSERT INTO school_requests (request, school, school_name, submitted, sxol_etos) VALUES ('$text', '". $_POST['school']."','".$sname."', NOW(), $sxol_etos)";
  $result = mysqli_query($conn, $query);
  $ret = $result ? 'Επιτυχής καταχώρηση αιτήματος!' : 'Παρουσιάστηκε σφάλμα κατά την καταχώρηση';
  echo mb_convert_encoding($ret, "utf-8", "iso-8859-7");
} elseif ($_POST['type'] == 'delete') {
  $query = "UPDATE school_requests SET hidden = 1 WHERE id=".$_POST['id'];
  $result = mysqli_query($conn, $query);
  $ret = $result ? 'Επιτυχής διαγραφή αιτήματος!' : 'Παρουσιάστηκε σφάλμα κατά την ενημέρωση';
  echo mb_convert_encoding($ret, "utf-8", "iso-8859-7");
} else {
  $text = mb_convert_encoding($_POST['comment'], "iso-8859-7", "utf-8");
  $query = "UPDATE school_requests SET comment = '$text',done = ".$_POST['done']." WHERE id=".$_POST['id'];
  $result = mysqli_query($conn, $query);
  $ret = $result ? 'Επιτυχής ενημέρωση αιτήματος!' : 'Παρουσιάστηκε σφάλμα κατά την ενημέρωση';
  echo mb_convert_encoding($ret, "utf-8", "iso-8859-7");
}
?>