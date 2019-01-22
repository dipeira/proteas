<?php
  //require_once('../config.php');
  require_once('functions.php');
  // getParam1: Διαβάζει παραμέτρους από τη βάση
  function getParam1($name,$conn)
  {
    $query = "SELECT value from params WHERE name='$name'";
    $result = mysqli_query($conn, $query);
    if (!$result) 
        die('Could not query:' . mysqli_error());
    return mysqli_result($result, 0, "value");
  }
  $myconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  // workaround for init.php
  // if (!$db_selected){
  //   return;
  // }
  mysqli_query($myconn, "SET NAMES 'greek'");
  mysqli_query($myconn, "SET CHARACTER SET 'greek'");
  
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
  
  define('SITE_ROOT', '/'.explode('/',$_SERVER['REQUEST_URI'])[1]); 
?>