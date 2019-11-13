<?php
  header('Content-type: text/html; charset=utf-8'); 
  session_start();
  require_once "config.php";
  $_SESSION['auth']=null;
  $_SESSION['inserted']=null;
?>
<html>
<head>
  <LINK href="css/style.css" rel="stylesheet" type="text/css">
  <title>Πρωτέας: Αρχικοποίηση βάσης δεδομένων</title>
</head>

  <body>
<?php
  if (!isset($_POST['pass']) && !isset($_SESSION['auth']))
  {
    echo "<IMG src='images/logo.png' class='applogo'></a>";
    echo "<h1>Πρωτέας</h1>";
    echo "<h2> Αρχικοποίηση βάσης δεδομένων </h2>";
    echo "<h3>Δημιουργία βάσης δεδομένων</h3>";
    echo "<p>ΣΗΜ.: To script αυτό δημιουργεί τη βάση με όνομα <strong>$db_name</strong>.<br><br>";
    echo "ΠΡΟΣΟΧΗ: Πριν προχωρήσετε, πρέπει να ρυθμίσετε τις παραμέτρους στο αρχείο <strong><i>config.php</i></strong><br>";
    echo "Αφού αρχικοποιήσετε, μην ξεχάσετε να <b>διαγράψετε</b> το αρχείο init.php<br><br></p>";
    echo "<strong>ΠΡΟΕΙΔΟΠΟΙΗΣΗ: Η ενέργεια αυτή δεν είναι αναστρέψιμη...</strong><br><br>";
    echo "<form action='init.php' method='POST'>";
    echo "Δώστε κωδικό ασφαλείας για αρχικοποίηση <small>(βλ. config.php)</small>:&nbsp;&nbsp;&nbsp;<input type='password' id='pass' name='pass'><br><input type='submit' value='Αρχικοποίηση'></form>";
    exit;
  }
if (($_POST['pass'] == $db_init_pass) && !isset($_SESSION['auth'])) {
    $_SESSION['auth'] = 1;
}
elseif (!isset ($_SESSION['auth'])) {
  echo '<h3>Λάθος κωδικός!</h3>';
  echo "<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='init.php'\">";
  die();
}

if ($_SESSION['auth'])
{
  echo "<IMG src='images/logo.png' class='applogo'></a>";
  echo "<h1>Πρωτέας</h1>";
  if (!$_SESSION['inserted'])
  {
    echo "<h3>Αρχικοποίηση βάσης δεδομένων...</h3>";      
    
    // create database
    # MySQL with PDO_MYSQL  
    $db = new PDO("mysql:host=$db_host", $db_user, $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $stmt = $db->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =:dbname");
    $stmt->execute(array(":dbname"=>$db_name));
    $row=$stmt->fetch(PDO::FETCH_ASSOC);

    if($stmt->rowCount() == 1)
    {
        echo '<p>Η δημιουργία απέτυχε: Η βάση δεδομένων υπάρχει ήδη...</p>';
        echo "<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='init.php'\">";
        die();
    }
    else {
      // set higher time limit to avoid timeouts
      set_time_limit(180);
      
      $sql = "CREATE DATABASE IF NOT EXISTS $db_name DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
      $db->exec($sql);
      
      $query = "USE $db_name; " . file_get_contents('dipedb.sql');

      try {
        $db->exec($query);
      }
      catch (PDOException $e){
        echo "H αρχικοποίηση της Βάσης Δεδομένων απέτυχε...";
        echo $e->getMessage();
        echo "<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='init.php'\">";
        die();
      }
      $db = NULL;
      echo "<h3>H αρχικοποίηση της Βάσης Δεδομένων ήταν επιτυχής!</h3>";
    }
    $_SESSION['inserted']=1;
  }
  echo "<h3>Για λόγους ασφαλείας, παρακαλώ διαγράψτε το αρχείο init.php αφου τελειώσετε...</h3><br>";
  echo "<INPUT TYPE='button' VALUE='Είσοδος' onClick=\"parent.location='index.php'\">";
}	
	
?>

</body>
</html>
	