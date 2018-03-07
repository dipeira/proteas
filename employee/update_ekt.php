<?php
	header('Content-type: text/html; charset=iso8859-7'); 
        session_start();
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <script type="text/javascript" src="../js/jquery_notification_v.1.js"></script>
    <link href="../css/jquery_notification.css" type="text/css" rel="stylesheet"/> 
    <title>Update</title>
  </head>
  <body> 
<?php
  
  require_once"../config.php";
  require_once"../tools/functions.php";
  error_reporting(0);
  
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  

  $id = $_POST['id'];
  $name = $_POST["name"];  
  $surname = $_POST['surname']; 
  $klados =$_POST['klados']; 
  $stathero = $_POST['stathero'];
  $kinhto = $_POST['kinhto'];
  
  $ip = $_SERVER['REMOTE_ADDR'];
  
  // yphr array
  $count = count($_POST['yphr']);
  // if multiple
  if ($count > 1)
  { 
        $multi = 1;
        for ($i=0; $i<$count; $i++)
        {
            $yphret[$i] = mb_convert_encoding($_POST['yphr'][$i], "iso-8859-7", "utf-8");
            $yphr_arr[$i] = getSchoolID($yphret[$i],$mysqlconnection);
            $hours_arr[$i] = $_POST['hours'][$i];
        }
  }
  else
  {
      if ($_POST['yphr'][0] == "")
        $yphr = 387;
      else
      {
        $yphret = $_POST['yphr'][0];
        $yphret = mb_convert_encoding($yphret, "iso-8859-7", "utf-8");
        $yphr = getSchoolID($yphret,$mysqlconnection);  
      }
      if (count($_POST['hours'])==1 && $_POST['hours'][0]>0)
      {
          $single = 1;
          $yphr_arr[0] = $yphr;
          $hours_arr[0] = $_POST['hours'][0];
      }
      if (!$_POST['hours'][0])
      {
          $single = 1;
          $yphr_arr[0] = $yphr;
          $hours_arr[0] = 24;
      }
  }
  
  $patrwnymo = $_POST['patrwnymo'];
  $mhtrwnymo = $_POST['mhtrwnymo'];
  $afm = $_POST['afm'];
  //$vathm = $_POST['vathm'];
  //$mk = $_POST['mk'];

  $analipsi = $_POST['analipsi'];
  $hm_anal = date('Y-m-d',strtotime($_POST['hm_anal']));
  //$met_did = $_POST['met_did'];

  //$ya = $_POST['ya'];
  $type = $_POST['type'];
  //$apofasi = $_POST['apofasi'];
  $comments = $_POST['comments'];
  $katast = $_POST['status'];
  $metakinhsh = addslashes($_POST['metakinhsh']);
  $praxi = $_POST['praxi'];
    
// $_POST['action']=1 for adding records  
  if (isset($_POST['action']))
  {
      // check if record exists by checking am AND surname
      $surn = mb_convert_encoding($surname, "iso-8859-7", "utf-8");
      $query = "select afm,surname from ektaktoi WHERE afm='$afm' AND surname = '$surn'";
      $result = mysql_query($query,$mysqlconnection);
      if (!mysql_num_rows($result))
      {
            $query0 = "INSERT INTO ektaktoi (name, surname, patrwnymo, mhtrwnymo, klados, sx_yphrethshs, analipsi, hm_anal, type, comments, afm, status, metakinhsh, praxi, stathero, kinhto) ";
            $query1 = "VALUES ('$name','$surname','$patrwnymo','$mhtrwnymo','$klados','$yphr_arr[0]','$analipsi','$hm_anal','$type','$comments', '$afm', '$katast', '$metakinhsh', '$praxi', '$stathero', '$kinhto')";

            $query = $query0.$query1;
            $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
            //echo $query;
            mysql_query($query,$mysqlconnection);
            // insert into yphrethsh
            $id = mysql_insert_id();
            for ($i=0; $i<count($yphr_arr); $i++) 
            {
                    $query = "insert into yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[$i]', '$hours_arr[$i]', $sxol_etos)";
                    mysql_query($query,$mysqlconnection);
            }
      }
      // if already inserted display error
      else 
      {
        notify('Η εγγραφή έχει ήδη καταχωρηθεί...',1);
        $dupe = 1;
      }
  }
  else
  {
      if ($multi)
      {
          // get current row from db
          $qry = "SELECT * from ektaktoi WHERE id=$id";
          $res = mysql_query($qry,$mysqlconnection);
          $before = mysql_fetch_row($res);
          
          $query1 = "UPDATE ektaktoi SET name='".$name."', surname='".$surname."', klados='".$klados."', sx_yphrethshs='$yphr_arr[0]',";
	  $query2 = " patrwnymo='$patrwnymo', mhtrwnymo='$mhtrwnymo', analipsi='$analipsi',";
	  $query3 = " hm_anal='$hm_anal', type= '$type', comments='$comments',afm='$afm', status='$katast', metakinhsh='$metakinhsh', praxi='$praxi', stathero='$stathero', kinhto='$kinhto' WHERE id='$id'";
	  $query = $query1.$query2.$query3;
          $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
          $qlog .= $query;
          mysql_query($query,$mysqlconnection);
          $query = "DELETE FROM yphrethsh_ekt WHERE emp_id = $id AND sxol_etos=$sxol_etos";
          mysql_query($query,$mysqlconnection);
          for ($i=0; $i<count($yphr_arr); $i++) 
          {
                $query = "insert into yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[$i]', '$hours_arr[$i]', $sxol_etos)";
                mysql_query($query,$mysqlconnection);
                $qlog = $qlog . ' \n ' . $query;
          }
          // insert 2 log
          $qlog = addslashes($qlog);
          $query1 = "INSERT INTO ektaktoi_log (emp_id, userid, action, ip, query) VALUES ('$id',".$_SESSION['userid'].", 1, '$ip', '$qlog')";
          mysql_query($query1, $mysqlconnection);
      }
      else
      {
          // get current row from db
          $qry = "SELECT * from employee_ekt WHERE id=$id";
          $res = mysql_query($qry,$mysqlconnection);
          $before = mysql_fetch_row($res);
          
          $query1 = "UPDATE ektaktoi SET name='".$name."', surname='".$surname."', klados='".$klados."', sx_yphrethshs='$yphr',";
	  $query2 = " patrwnymo='$patrwnymo', mhtrwnymo='$mhtrwnymo', analipsi='$analipsi',";
	  $query3 = " hm_anal='$hm_anal', type= '$type', comments='$comments',afm='$afm', status='$katast', ya='$ya', apofasi='$apofasi', metakinhsh='$metakinhsh', praxi='$praxi', stathero='$stathero', kinhto='$kinhto' WHERE id='$id'";
	  $query = $query1.$query2.$query3;
          $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
          $qlog .= $query;
          //echo $query;
          mysql_query($query,$mysqlconnection);
          // svhse tyxon >1 yphrethseis 
          $query = "DELETE FROM yphrethsh_ekt WHERE emp_id = $id AND sxol_etos = $sxol_etos";
          mysql_query($query,$mysqlconnection);
          if ($single)
          {
              $query = "insert into yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[0]', '$hours_arr[0]', $sxol_etos)";
              mysql_query($query,$mysqlconnection);
              $qlog = $qlog . ' \n ' . $query;
          }
          // insert 2 log
          $qlog = addslashes($qlog);
          $query1 = "INSERT INTO ektaktoi_log (emp_id, userid, action, ip, query) VALUES ('$id',".$_SESSION['userid'].", 1, '$ip', '$qlog')";
          mysql_query($query1, $mysqlconnection);
      }
  }

  if (!$dupe)
    notify('Επιτυχής καταχώρηση!',0);
  mysql_close();
?>
<br>
</body>
</html>