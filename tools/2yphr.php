<?php
	header('Content-type: text/html; charset=iso8859-7'); 
        session_start();
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>������� ������ �����������</title>
  </head>
  <body> 
<?php
  
  Require_once "../config.php";
  //Require_once "../functions.php";
  
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
    
  set_time_limit(1200);  
  
  echo "<h2>��������� �������� ��� ������� ������ ����������� �� ��������� �����������</h2>";
  echo "<br>���. ������ �� ���������� �� ���� ������ �������� �����. �� ���������� �������� ��� ��� ����� ��� ����������� �� ��������.";
  echo "<br><strong>�������:</strong>� ���������� ������� ������ �����.";
  if ($usrlvl > 0)
    {
        echo "<br><br><h3>��� ����� �������� ��� ��� �������������� ����� ��� ���������. ������������� �� �� ����������� ���.</h3>";
        echo "<br><a href=\"index.php\">���������</a>";
        mysql_close();
        exit;
    }
    echo "<form action='' method='POST' autocomplete='off'>";
    echo "<input type='submit' name='submit' value='��������������'>";
    echo "<br><br><input type='button' onclick=\"parent.location='../index.php'\" value=\"������ ������\">";
    echo "</form>";
  if (isset($_POST['submit']))
    do2yphr($mysqlconnection,1);

echo "</body>";
echo "</html>";

?>