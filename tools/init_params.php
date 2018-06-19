<?php
  //require_once('../config.php');
  // getParam1: Διαβάζει παραμέτρους από τη βάση
  function getParam1($name,$conn)
  {
    $query = "SELECT value from params WHERE name='$name'";
    $result = mysql_query($query, $conn);
    if (!$result) 
        die('Could not query:' . mysql_error());
    return mysql_result($result, 0, "value");
  }
  $myconn = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $myconn);
  mysql_query("SET NAMES 'greek'", $myconn);
  mysql_query("SET CHARACTER SET 'greek'", $myconn);
  
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