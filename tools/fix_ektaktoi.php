<html>
<head>
  <LINK href="../css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once "../config.php";
  require_once "functions.php";
  
  echo "<h2>Επιδιόρθωση υπηρετήσεων αναπληρωτών</h2>";
    
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
        
  $query = "SELECT emp_id,yphrethsh,hours,COUNT(*) occurrences FROM yphrethsh_ekt WHERE sxol_etos=$sxol_etos GROUP BY emp_id,yphrethsh HAVING COUNT(*) > 1";
  $result = mysqli_query($mysqlconnection, $query);
  
  $count = 0;
  while ($row = mysqli_fetch_assoc($result))
  {
      $query = "delete from yphrethsh_ekt where emp_id=".$row['emp_id']." AND yphrethsh=".$row['yphrethsh']." AND sxol_etos=$sxol_etos;";
      $result = mysqli_query($mysqlconnection, $query);
      //echo $query;
      $hours = $row['hours'] * 2;
      $query_ins = "insert into yphrethsh_ekt (id,emp_id,yphrethsh,hours,sxol_etos) VALUES (null,".$row['emp_id'].",".$row['yphrethsh'].",$hours,$sxol_etos);";
      $result = mysqli_query($mysqlconnection, $query_ins);
      //echo "<br>".$query_ins."<br><br>";
      $count++;    
  }
  echo "<br>";
  echo "<br>Επιδιορθώθηκαν $count εγγραφές.";
  echo "<br><br>";
  echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
                
  mysqli_close($mysqlconnection);
?>
</body>
</html>