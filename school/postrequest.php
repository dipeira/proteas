<?php
require_once "../config.php";
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
if ($_POST['type'] == 'insert'){
  $text = $_POST['request'];
  $sname = getSchool($_POST['school'], $conn);
  $query = "INSERT INTO school_requests (request, school, school_name, submitted, sxol_etos) VALUES ('$text', '". $_POST['school']."','".$sname."', NOW(), $sxol_etos)";
  $result = mysqli_query($conn, $query);
  $ret = $result ? 'Επιτυχής καταχώρηση αιτήματος!' : 'Παρουσιάστηκε σφάλμα κατά την καταχώρηση';
  echo $ret;
} elseif ($_POST['type'] == 'delete') {
  $query = "UPDATE school_requests SET hidden = 1 WHERE id=".$_POST['id'];
  $result = mysqli_query($conn, $query);
  $ret = $result ? 'Επιτυχής διαγραφή αιτήματος!' : 'Παρουσιάστηκε σφάλμα κατά την ενημέρωση';
  echo $ret;
} else {
  $text = $_POST['comment'];
  $query = "UPDATE school_requests SET comment = '$text',done = ".$_POST['done']." WHERE id=".$_POST['id'];
  $result = mysqli_query($conn, $query);
  $ret = $result ? 'Επιτυχής ενημέρωση αιτήματος!' : 'Παρουσιάστηκε σφάλμα κατά την ενημέρωση';
  echo $ret;
}
?>