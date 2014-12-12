<?php
	header('Content-type: text/html; charset=iso8859-7'); 
        session_start();
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Update</title>
  </head>
  <body> 
<?php
  
  require_once"config.php";
  require_once"functions.php";
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
//      $surn = mb_convert_encoding($surname, "iso-8859-7", "utf-8");
//      $query = "select am,surname from employee WHERE am='$am' AND surname = '$surn'";
//      $result = mysql_query($query,$mysqlconnection);
//      if (!mysql_num_rows($result))
//      {
      //if multiple schools
//        if ($multi)
//        {
            $query0 = "INSERT INTO ektaktoi (name, surname, patrwnymo, mhtrwnymo, klados, sx_yphrethshs, analipsi, hm_anal, type, comments, afm, status, metakinhsh, praxi, stathero, kinhto) ";
            $query1 = "VALUES ('$name','$surname','$patrwnymo','$mhtrwnymo','$klados','$yphr_arr[0]','$analipsi','$hm_anal','$type','$comments', '$afm', '$katast', '$metakinhsh', '$praxi', '$stathero', '$kinhto')";

            $query = $query0.$query1;
            $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
            echo $query;
            mysql_query($query,$mysqlconnection);
            // insert into yphrethsh
            $id = mysql_insert_id();
            for ($i=0; $i<count($yphr_arr); $i++) 
            {
                    $query = "insert into yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[$i]', '$hours_arr[$i]', $sxol_etos)";
                    mysql_query($query,$mysqlconnection);
            }
//        }
//      }
  }
      
//	$query0 = "INSERT INTO ektaktoi (name, surname, patrwnymo, mhtrwnymo, klados, sx_yphrethshs, analipsi, hm_anal, type, ya, apofasi, comments, afm, status, metakinhsh, praxi) ";
//			 	 $query1 = "VALUES ('$name','$surname','$patrwnymo','$mhtrwnymo','$klados','$yphr','$analipsi','$hm_anal','$type','$ya','$apofasi','$comments', '$afm', '$katast', '$metakinhsh', '$praxi')";
//	$query = $query0.$query1;
//        $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
//        //echo $query;
//        mysql_query($query,$mysqlconnection);
        // insert 2 log
        //$query1 = "INSERT INTO employee_log (emp_id, userid, action) VALUES ('$id',".$_SESSION['userid'].", 0)";
        //mysql_query($query1, $mysqlconnection);
  else
  {
//	  $query1 = "UPDATE ektaktoi SET name='".$name."', surname='".$surname."', klados='".$klados."', sx_yphrethshs='$yphr',";
//	  $query2 = " patrwnymo='$patrwnymo', mhtrwnymo='$mhtrwnymo', analipsi='$analipsi',";
//	  $query3 = " hm_anal='$hm_anal', type= '$type', comments='$comments',afm='$afm', status='$katast', ya='$ya', apofasi='$apofasi', metakinhsh='$metakinhsh', praxi='$praxi' WHERE id='$id'";
//	  $query = $query1.$query2.$query3;
//          $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
          //echo $query;
//          mysql_query($query,$mysqlconnection);
          // insert 2 log
          //if (mysql_affected_rows()>0)
          //{
          //  $query1 = "INSERT INTO employee_log (emp_id, userid, action) VALUES ('$id',".$_SESSION['userid'].", 1)";
          //  mysql_query($query1, $mysqlconnection);
          //}
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
          mysql_query($query,$mysqlconnection);
          // insert 2 log
//          if (mysql_affected_rows()>0)
//          {
//            // find changes and write them to query field
//              $name = mb_convert_encoding($name, "iso-8859-7", "utf-8");
//              $surname = mb_convert_encoding($surname, "iso-8859-7", "utf-8");
//              $patrwnymo = mb_convert_encoding($patrwnymo, "iso-8859-7", "utf-8");
//              $mhtrwnymo = mb_convert_encoding($mhtrwnymo, "iso-8859-7", "utf-8");
//              $address = mb_convert_encoding($address, "iso-8859-7", "utf-8");
//              $idnum = mb_convert_encoding($idnum, "iso-8859-7", "utf-8");
//              $vathm = mb_convert_encoding($vathm, "iso-8859-7", "utf-8");
//              $comments = mb_convert_encoding($comments, "iso-8859-7", "utf-8");
//              $after = array($id,$name,$surname,$patrwnymo,$mhtrwnymo,$klados,$am,$org,$yphr,$thesi,$fek_dior,$hm_dior,$before[12],$vathm,$before[14],$before[15],$mk,$hm_mk,$analipsi,$hm_anal,$met_did,$before[21],$proyp,$before[23],$before[24],$before[25],$comments,$katast,$afm,$before[29],$tel,$address,$idnum,$amka,$aney,$aney_xr,$aney_apo,$aney_ews);
//              $ind=0;
//              foreach ($after as $aft){
//                  if (strcmp($aft,$before[$ind]) != 0){
//                          $field = mysql_fetch_field($res,$ind);
//                          $change .= $field->name.":".$before[$ind]."->".$aft." | ";
//                  }
//                  $ind++;
//              }
//            //
//            $query1 = "INSERT INTO employee_log (emp_id, userid, action, ip, query) VALUES ('$id',".$_SESSION['userid'].", 1, '$ip', '$change')";
//            mysql_query($query1, $mysqlconnection);
//          }
          // AND sxol_etos -> future use...
          $query = "DELETE FROM yphrethsh_ekt WHERE emp_id = $id AND sxol_etos=$sxol_etos";
          mysql_query($query,$mysqlconnection);
          for ($i=0; $i<count($yphr_arr); $i++) 
          {
                $query = "insert into yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[$i]', '$hours_arr[$i]', $sxol_etos)";
                mysql_query($query,$mysqlconnection);
          }
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
          //echo $query;
          mysql_query($query,$mysqlconnection);
          // insert 2 log
//          if (mysql_affected_rows()>0)
//          {
//            // find changes and write them to query field
//              $name = mb_convert_encoding($name, "iso-8859-7", "utf-8");
//              $surname = mb_convert_encoding($surname, "iso-8859-7", "utf-8");
//              $patrwnymo = mb_convert_encoding($patrwnymo, "iso-8859-7", "utf-8");
//              $mhtrwnymo = mb_convert_encoding($mhtrwnymo, "iso-8859-7", "utf-8");
//              $address = mb_convert_encoding($address, "iso-8859-7", "utf-8");
//              $idnum = mb_convert_encoding($idnum, "iso-8859-7", "utf-8");
//              $vathm = mb_convert_encoding($vathm, "iso-8859-7", "utf-8");
//              $comments = mb_convert_encoding($comments, "iso-8859-7", "utf-8");
//              $after = array($id,$name,$surname,$patrwnymo,$mhtrwnymo,$klados,$am,$org,$yphr,$thesi,$fek_dior,$hm_dior,$before[12],$vathm,$before[14],$before[15],$mk,$hm_mk,$analipsi,$hm_anal,$met_did,$before[21],$proyp,$before[23],$before[24],$before[25],$comments,$katast,$afm,$before[29],$tel,$address,$idnum,$amka,$aney,$aney_xr,$aney_apo,$aney_ews);
//              $ind=0;
//              foreach ($after as $aft){
//                  if (strcmp($aft,$before[$ind]) != 0){
//                          $field = mysql_fetch_field($res,$ind);
//                          $change .= $field->name.":".$before[$ind]."->".$aft." | ";
//                  }
//                  $ind++;
//              }
//            //
//            $query1 = "INSERT INTO employee_log (emp_id, userid, action, ip, query) VALUES ('$id',".$_SESSION['userid'].", 1, '$ip', '$change')";
//            mysql_query($query1, $mysqlconnection);
//          }
          // svhse tyxon >1 yphrethseis 
          $query = "DELETE FROM yphrethsh_ekt WHERE emp_id = $id AND sxol_etos = $sxol_etos";
          mysql_query($query,$mysqlconnection);
          if ($single)
          {
              $query = "insert into yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[0]', '$hours_arr[0]', $sxol_etos)";
              mysql_query($query,$mysqlconnection);
          }
      }
  }

  
  // for debugging...
  //echo "<br>".$query;
  
  // insert / update
  
  
  
  //echo "Επιτυχής καταχώρηση!";
  mysql_close();
?>
<br>
<h2 align="center">Επιτυχής καταχώρηση!</h2>
</body>
</html>