<?php
  //require_once('../config.php');
  require_once("functions.php");
  // getParam1: Διαβάζει παραμέτρους από τη βάση
  function getParam1($name,$conn)
  {
    $query = "SELECT value from params WHERE name='$name'";
    $result = mysqli_query($conn, $query);
    if (!$result) 
       die('Could not query:' . mysqli_error($conn));
    return mysqli_result($result, 0, "value");
  }

  // check if DB exists
  $db = new mysqli($db_host, $db_user, $db_password);
  $query="SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME=?";
  $stmt = $db->prepare($query);
  $stmt->bind_param('s',$db_name);
  $stmt->execute();
  $stmt->bind_result($data);
  // if exists, init params
  if($stmt->fetch()) {
    $myconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
    mysqli_query($myconn, "SET NAMES 'utf8'");
    mysqli_query($myconn, "SET CHARACTER SET 'utf8'");
    
    $sxol_etos = getParam1('sxol_etos',$myconn);
    
    // Δ/ντής-τρια
    $head_title = getParam1('head_title',$myconn);
    $head_name = getParam1('head_name',$myconn);
    
    // Για βεβαιώσεις αναπληρωτών στο τέλος της χρονιάς - endofyear: ημέρα έκδοσης βεβαίωσης, endofyear2: τελευταία ημέρα εργασίας
    $endofyear = getParam1('endofyear',$myconn);
    $endofyear2 = getParam1('endofyear2',$myconn);
    $protapol = getParam1('protapol',$myconn);

    // Report all errors except E_NOTICE
    // This is the default value set in php.ini  
    // to avoid notices on some configurations
    error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
    
    // set calendar language to el_GR
    define("L_LANG", "el_GR");
    // meiwsh ypeythinoy vivliothikis
    define('MEIWSH_VIVLIOTHIKIS',3);
    // meiwsh ypodieythinth
    define('MEIWSH_YPNTH',2);
  }
  // if not exists
  else {
    ?>
    <head>
      <LINK href="css/style.css" rel="stylesheet" type="text/css">
      <title>Πρωτέας</title>
    </head>
  <?php
    // if the executed script is not init.php alert the user and die...
    if (!endsWith($_SERVER['PHP_SELF'],'init.php')){
      echo "<IMG src='images/logo.png' class='applogo'></a>";
      echo "<h1>Πρωτέας</h1>";
      echo "<h2>Σφάλμα: Η βάση δεδομένων δεν υπάρχει.</h2>";
      echo "<h3>Δημιουργήστε την με το <a href='init.php'>init.php</a> ή επικοινωνήστε με το διαχειριστή.</h3>";
      die();
    }
  }
  $stmt->close();
?>
