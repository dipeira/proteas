<?php
  header('Content-type: text/html; charset=utf-8'); 
?>
<html>
  <head>
	  <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Εισαγωγή δεδομένων από αρχείο</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
  </head>
  <body>

<?php
  session_start();

  require_once "../config.php";
  require_once '../include/functions.php';
  
  include_once("class.login.php");
  $log = new logmein();
  // if not logged in or not admin
  //if($log->logincheck($_SESSION['loggedin']) == false || $_SESSION['user'] != $av_admin)
  if($_SESSION['loggedin'] == false)
  {   
      header("Location: login.php");
  }
  else
      $loggedin = 1;

  // check if admin
  if ($_SESSION['userlevel'] > 0)
  {
    echo "<br><br><h3>Δεν έχετε δικαίωμα για την πραγματοποίηση αυτής της ενέργειας. Επικοινωνήστε με το διαχειριστή σας.</h3>";
    die();
  }
	echo "<h2>Έλεγχος εισαγωγής δεδομένων μαθητών - τμημάτων</h2>";
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

  $query = "SELECT * from school WHERE anenergo=0 ORDER BY type";
  $result = mysqli_query($mysqlconnection, $query);
  $num = mysqli_num_rows($result);
  $previous_year = find_prev_year($sxol_etos);
  $no_archive = $no_year = Array();

  while($row = mysqli_fetch_assoc($result)) {
    $archive = $row['archive'];
    if (strlen($archive) > 0) {
      $archive_unser = unserialize($archive);
      if (strlen($archive_unser[$previous_year])==0){
        $no_year[$row['code']] = $row['name'];  
      }
      // $archive_arr = explode(',',$archive_unser[$previous_year]);
      // if (count($archive_arr) == 0){
      //   $no_year[$row['code']] = $row['name'];  
      // }
    } else {
      $no_archive[$row['code']] = $row['name'];  
    }
  }
  
  echo '<h3>Χωρίς προηγούμενο έτος</h3>';
  echo "Πρέπει να είσαχθούν με αρχείο excel για να αρχειοθετηθεί η προηγούμενη χρονιά";
  echo "<ul>";
  foreach($no_year as $key=>$value){
    echo "<li>$key: $value</li>";
  }
  echo "</ul>";
  
  echo '<h3>Χωρίς αρχείο</h3>';
  echo "<ul>";
  foreach($no_archive as $key=>$value){
    echo "<li>$key: $value</li>";
  }
  echo "</ul>";
  
  echo "<br><br><a href='import.php'>Επιστροφή</a>";
?>

</body>
</html>
	