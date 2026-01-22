<?php
session_start();
header('Content-type: text/plain; charset=utf-8');
$timestamp = date('Y-m-d_H-i-s');
header("Content-Disposition: attachment; filename=import_log_$timestamp.txt");

if (isset($_SESSION['import_log_content'])) {
  echo $_SESSION['import_log_content'];
  unset($_SESSION['import_log_content']);
} else {
  echo "Λάθος: Δεν υπάρχει αρχείο καταγραφής για λήψη.";
}
?>
