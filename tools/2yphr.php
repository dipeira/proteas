<?php
    header('Content-type: text/html; charset=iso8859-7'); 
        session_start();
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Πλήρωση πίνακα υπηρετήσεων</title>
  </head>
  <body> 
<?php
  
  Require_once "../config.php";
  //Require_once "../functions.php";
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'greek'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
    
  set_time_limit(1200);  
  
  echo "<h2>Βοηθητικό Εργαλείο για γέμισμα πίνακα υπηρετήσεων με τρέχουσες υπηρετήσεις</h2>";
  echo "<br>ΣΗΜ. Πρέπει να εκτελείται με κάθε αλλαγή σχολικού έτους. Αν εκτελεστεί παραπάνω από μία φορές δεν καταστρέφει τα δεδομένα.";
  echo "<br><strong>ΠΡΟΣΟΧΗ:</strong>Η διαδικασία διαρκεί αρκετά λεπτά.";
if ($usrlvl > 0) {
      echo "<br><br><h3>Δεν έχετε δικαίωμα για την πραγματοποίηση αυτών των ενεργειών. Επικοινωνήστε με το διαχειριστή σας.</h3>";
      echo "<br><a href=\"index.php\">Επιστροφή</a>";
      mysqli_close($mysqlconnection);
      exit;
}
    echo "<form action='' method='POST' autocomplete='off'>";
    echo "<input type='submit' name='submit' value='Πραγματοποίηση'>";
    echo "<br><br><input type='button' onclick=\"parent.location='../index.php'\" value=\"Αρχική σελίδα\">";
    echo "</form>";
if (isset($_POST['submit'])) {
    do2yphr($mysqlconnection, 1);
}

echo "</body>";
echo "</html>";

?>
