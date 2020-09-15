<html>
<head>
  <LINK href="../css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once "../config.php";
  require_once "../include/functions.php";
  
  echo "<h2>Επιδιόρθωση λειτουργικότητας</h2>";
  echo "<h4>fix_leitoyrg: Utility που επιδιορθώνει τη λειτουργικότητα στον πίνακα σχολείων.<br>";
  echo "Διορθώνει τη στήλη 'Λειτουργικότητα' σε περίπτωση που δε συμφωνεί με τον αριθμό τμημάτων.</h4>";
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
        
  $query = "SELECT * from school";
  $result = mysqli_query($mysqlconnection, $query);
  
  $fix = $count = 0;
  while ($row = mysqli_fetch_assoc($result))
  {
      $leitoyrg = get_leitoyrgikothta($row['id'], $mysqlconnection);
      if ($leitoyrg > 0 && $row['leitoyrg'] <> $leitoyrg) {
        $query_upd = "UPDATE school SET leitoyrg = $leitoyrg WHERE id=".$row['id'];
        mysqli_query($mysqlconnection, $query_upd);
        $fix+=1;
      }
      $count++;    
  }
  echo "<br>";
  echo "<br>Επιδιορθώθηκαν $fix εγγραφές από σύνολο $count.";
  echo "<br><br>";
  echo "<form action='../tools/2excel_ses.php' method='post'>";
  echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
  echo "</form>";
                
  mysqli_close($mysqlconnection);
?>
</body>
</html>