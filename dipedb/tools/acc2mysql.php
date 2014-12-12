<?php
	header('Content-type: text/html; charset=iso8859-7'); 
        session_start();
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Access 2 MySQL</title>
  </head>
  <body> 
<?php
  
  Require "../config.php";
  Require "../functions.php";
  Require "access.php";
  
  set_time_limit(600);  
	
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
  $i = $count = 0;
  $query1 = "SELECT * from employee_tmp";
  $result1 = mysql_query($query1, $mysqlconnection);
  $num = mysql_num_rows($result1);
  
  while ($i < $num)
  {
    $afm = mysql_result($result1, $i, "afm");
    $amka = mysql_result($result1, $i, "amka");
    if (empty($amka))
        $res = misth_elements($afm);

    if ($res != NULL)
            {
                $tel = $res['telef'];
                $address = $res['street']." ".$res['numStr'].", ".$res['city'];
                $idnum = $res['idNum'];
                $amka = $res['AMKA'];

                $query_upd = "UPDATE employee_tmp SET tel='$tel', address='$address', idnum='$idnum', amka='$amka' WHERE afm='$afm'";
                echo "<br>$query_upd";
                $count++;
				mysql_query($query_upd,$mysqlconnection);
            }
     $i++;
  }
  
  
  mysql_close();
echo "<br><br>$count updates...";

echo "</body>";
echo "</html>";

?>