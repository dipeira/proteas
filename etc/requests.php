<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"config.php";
  require_once "functions.php";
  
  //define("L_LANG", "el_GR"); Needs fixing
  
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
  // Demand authorization                
  include("tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false){
    header("Location: tools/login_check.php");
  }
  
  if (isset($_POST['save'])){
      $req_date = date('Y-m-d',strtotime($_POST['req_date']));
      $user = $_SESSION['user'];
      $req_name = $_POST['req_name'];
      $query = "INSERT INTO requests (user_id,req_name,req_date,req_txt) VALUES ('".$user."','".$req_name."','".$req_date."','".$_POST['req_txt']."')";
      $result = mysql_query($query, $mysqlconnection);
      echo "�������� ����������!";
      //echo $query;   
  }
  
  if (isset($_POST['submit'])){
        require('calendar/tc_calendar.php');
        echo "<form id='reqfrm' action='' method='POST'>";
        echo "<table class=\"imagetable\" border='1'>";
        echo "<tr><td>��/��� ��������</td><td>";
        $myCal = new tc_calendar("req_date", true);
        $myCal->setIcon("calendar/images/iconCalendar.gif");
        $myCal->setDate(date("d"), date("m"), date("Y"));
        $myCal->setPath("calendar/");
        $myCal->setYearInterval(1970, date("Y"));
        $myCal->dateAllow("1970-01-01", date("Y-m-d"));
        $myCal->setAlignment("left", "bottom");
        $myCal->disabledDay("sun,sat");
        $myCal->writeScript();
        echo "</td></tr>";
        echo "<tr><td>��/��</td><td><input type='text' name='req_name'></td></tr>";
        echo "<tr><td>������</td><td><textarea rows='4' columns='120' name='req_txt'></textarea></td></tr>";
        echo "<tr><td><input type='submit' name='save' value='����������'></td></tr>";
        echo "</table>";
        echo "</form>";
  }
  $query = "SELECT * FROM requests";
  $result = mysql_query($query, $mysqlconnection);
  $num = mysql_num_rows($result);
?>
<html>
    <head>
        <title>�������� ������� (user requests)</title>
    </head>
    <body>
        <h3>�������: �������� ������� <small>(user requests)</small></h3>
<?php if (!$num){
    echo "<h3>�� �������� ��������.</h3>";
    echo "<form method='POST'><BUTTON TYPE='submit' name='submit'>�������� ���������</BUTTON></form>";
    echo "<a href='../index.php'>��������� ���� ������ ������</a>";
    exit();
}
?>
    <table id="mytbl" class="imagetable tablesorter" border="2">
        <thead>
            <tr>
                <th>����������</th>
                <th>��.������</th>
                <th>��/��</th>
                <th>������</th>
                <th>������������ - ��/���</th>
                <th>������</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysql_fetch_array($result, MYSQL_BOTH)){
                if ($row['req_done']=='0000-00-00')
                    $done = "���";
                else
                    $done = $row['req_done'];
                if (strlen($row['req_comment'])>0)
                    $comm = $row['req_comment'];
                else
                    $comm = "&nbsp;";
                echo "<tr><td>".date("d-m-Y", strtotime($row['req_date']))."</td><td>".$row['user_id']."</td><td>".$row['req_name']."</td><td>".$row['req_txt']."</td><td>$done</td><td>$comm</td></tr>";
            }
                ?>
        </tbody>
    </table>
    
        <br><br>
    <?php
            echo "<form method='POST'><BUTTON TYPE='submit' name='submit'>�������� ���������</BUTTON></form>"
    ?>
    <a href="../index.php">��������� ���� ������ ������</a>
    </body>
</html>