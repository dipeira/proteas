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
  $usrlvl = $_SESSION['userlevel'];
  
  if (isset($_POST['save'])){
      $ch_date = date('Y-m-d',strtotime($_POST['ch_date']));
      $query = "INSERT INTO changelog (ch_date,change_txt) VALUES ('".$ch_date."','".$_POST['change']."')";
      $result = mysql_query($query, $mysqlconnection);
      echo "Επιτυχής καταχώρηση!";
      //echo $query;
      
  }
  if (isset($_POST['submit'])){
        require('calendar/tc_calendar.php');
        echo "<form id='changefrm' action='' method='POST'>";
        echo "<table class=\"imagetable\" border='1'>";
        echo "<tr><td>Ημ/νία αλλαγής</td><td>";
        $myCal = new tc_calendar("ch_date", true);
        $myCal->setIcon("calendar/images/iconCalendar.gif");
        //$myCal->setDate(date('d',strtotime($hm_mk)),date('m',strtotime($hm_mk)),date('Y',strtotime($hm_mk)));
        $myCal->setDate(date("d"), date("m"), date("Y"));
        $myCal->setPath("calendar/");
        $myCal->setYearInterval(1970, date("Y"));
        $myCal->dateAllow("1970-01-01", date("Y-m-d"));
        $myCal->setAlignment("left", "bottom");
        $myCal->disabledDay("sun,sat");
        $myCal->writeScript();
        echo "</td></tr>";
        echo "<tr><td>Αλλαγή</td><td><textarea rows='4' columns='120' name='change'></textarea></td></tr>";
        echo "<tr><td><input type='submit' name='save' value='Αποθήκευση'></td></tr>";
        echo "</table>";
        echo "</form>";
      
  }
  $query = "SELECT * FROM changelog";
  $result = mysql_query($query, $mysqlconnection);
?>
<html>
    <head>
        <title>Αλλαγές (changelog)</title>
    </head>
    <body>
        <h3>Πρωτέας: Αλλαγές <small>(changelog)</small></h3>

    <table id="mytbl" class="imagetable tablesorter" border="2">
        <thead>
            <tr>
                <th>Ημερομηνία</th>
                <th>Αλλαγή</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysql_fetch_array($result, MYSQL_BOTH))
                echo "<tr><td>".date("d-m-Y", strtotime($row['ch_date']))."</td><td>".$row['change_txt']."</td></tr>";
                ?>
        </tbody>
    </table>
    
        <br><br>
    <?php
        if (!$usrlvl)
            echo "<form method='POST'><BUTTON TYPE='submit' name='submit'>Προσθήκη αλλαγής</BUTTON></form>"
    ?>
    <a href="index.php">Επιστροφή στην Αρχική σελίδα</a>
    </body>
</html>