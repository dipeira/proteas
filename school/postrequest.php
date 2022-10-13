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
  // if request is done, send email
  if ($_POST['done'] == 1) {
    // get initial school request
    $query = "SELECT * FROM school_requests WHERE id=".$_POST['id'];
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $req = str_replace(['\'', '"'], "", $row['request']);

    $emailTo = getEmail($_POST['school'],$conn);
    $subject = 'Διεκπεραίωση αιτήματος στο σύστημα Πρωτέας';
    $body = 'Σας ενημερώνουμε ότι το αίτημα που είχατε υποβάλει με Α/Α: ' . $_POST['id'] . ' στο σύστημα Πρωτέας, διεκπεραιώθηκε.<br><br>';
    $body .= "Αίτημα που είχατε υποβάλει: <br>". $req."<br><br>";
    $body .= "Απάντηση Διεύθυνσης: <br>$text<br><br><br>Με εκτίμηση, ".getParam('foreas',$conn);
    sendEmail($emailTo, $subject, $body); // add true for debug
  }
  $query = "UPDATE school_requests SET comment = '$text',done = ".$_POST['done']." WHERE id=".$_POST['id'];
  $result = mysqli_query($conn, $query);
  $ret = $result ? 'Επιτυχής ενημέρωση αιτήματος!' : 'Παρουσιάστηκε σφάλμα κατά την ενημέρωση';
  echo $ret;
}
?>