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
    
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
  $ip = $_SERVER['REMOTE_ADDR'];

  $id = $_POST['id'];
  $name = $_POST["name"];  
  $surname = $_POST['surname']; 
  $klados =$_POST['klados']; 
  
  if ($_POST['org'] == "")
      $org = 387;
  else
  {
    $organ = mb_convert_encoding($_POST['org'], "iso-8859-7", "utf-8");
    $org = getSchoolID($organ,$mysqlconnection);  
  }
  // yphr array
  $count = count($_POST['yphr']);
  // if multiple
  //if ($count > 1)
  //{ 
        $multi = 1;
        for ($i=0; $i<$count; $i++)
        {
            if ($_POST['yphr'][$i] == "")
                $yp_tmp = 387;
            else
                $yp_tmp = $_POST['yphr'][$i];
            $yphret[$i] = mb_convert_encoding($yp_tmp, "iso-8859-7", "utf-8");
            $yphr_arr[$i] = getSchoolID($yphret[$i],$mysqlconnection);
            $hours_arr[$i] = $_POST['hours'][$i];
        }
        $yphret = $_POST['yphr'][0];
        $yphret = mb_convert_encoding($yphret, "iso-8859-7", "utf-8");
        $yphr = getSchoolID($yphret,$mysqlconnection);  

  //}
  //else
//  {
//      if ($_POST['yphr'][0] == "")
//        $yphr = 387;
//      else
//      {
//        $yphret = $_POST['yphr'][0];
//        $yphret = mb_convert_encoding($yphret, "iso-8859-7", "utf-8");
//        $yphr = getSchoolID($yphret,$mysqlconnection);  
//      }
//      if (count($_POST['hours'])==1 && $_POST['hours'][0]>0)
//      {
//          $single = 1;
//          $yphr_arr[0] = $yphr;
//          $hours_arr[0] = $_POST['hours'][0];
//      }
//  }
  
  $patrwnymo = $_POST['patrwnymo'];
  $mhtrwnymo = $_POST['mhtrwnymo'];
  $afm = $_POST['afm'];
  $tel = $_POST['tel'];
  $address = $_POST['address'];
  $idnum = $_POST['idnum'];
  $amka = $_POST['amka'];
  $am = $_POST['am'];
  $wres = $_POST['wres'];
  $vathm = $_POST['vathm'];
  $mk = $_POST['mk'];
  $hm_mk = date('Y-m-d',strtotime($_POST['hm_mk']));
  $fek_dior = $_POST['fek_dior'];
  $hm_dior = date('Y-m-d',strtotime($_POST['hm_dior']));
  $analipsi = $_POST['analipsi'];
  //date('d-m-Y',strtotime($hm_dior))
  $hm_anal = date('Y-m-d',strtotime($_POST['hm_anal']));
  $met_did = $_POST['met_did'];
  //$proyp = $_POST['proyp'];
  $proyp = $_POST['pyears']*360 + $_POST['pmonths']*30 + $_POST['pdays'];
  //$anatr = $_POST['anatr'];
  //$anatr = $_POST['ayears']*360 + $_POST['amonths']*30 + $_POST['adays'];
  $comments = $_POST['comments'];
  $katast = $_POST['status'];
  $thesi = $_POST['thesi'];
  // aney 27-02-2014
  if ($_POST['aney'])
      $aney = 1;
  $aney_apo = date('Y-m-d',strtotime($_POST['aney_apo']));
  $aney_ews = date('Y-m-d',strtotime($_POST['aney_ews']));
  $aney_xr = $_POST['aney_y']*360 + $_POST['aney_m']*30 + $_POST['aney_d'];
  // idiwtiko 07-11-2014
  if ($_POST['idiwtiko']) $idiwtiko=1;
  $idiwtiko_liksi = date('Y-m-d',strtotime($_POST['idiwtiko_liksi']));
  
// $_POST['action']=1 for adding records  
  if (isset($_POST['action']))
  {
      // check if record exists by checking am AND surname
      $surn = mb_convert_encoding($surname, "iso-8859-7", "utf-8");
      $query = "select am,surname from employee WHERE am='$am' AND surname = '$surn'";
      $result = mysql_query($query,$mysqlconnection);
      if (!mysql_num_rows($result))
    {
      //if multiple schools
      if ($multi)
      {
          $query0 = "INSERT INTO employee (name, surname, patrwnymo, mhtrwnymo, klados, am, sx_organikhs, sx_yphrethshs, fek_dior, hm_dior, vathm, mk, hm_anal, met_did, proyp, comments, afm, thesi, status, wres) ";
	  $query1 = "VALUES ('$name','$surname','$patrwnymo','$mhtrwnymo','$klados','$am','$org','$yphr_arr[0]','$fek_dior','$hm_dior','$vathm','$mk','$hm_anal','$met_did','$proyp','$comments', '$afm', '$thesi', '$katast', '$wres')";
          $query = $query0.$query1;
          $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
          mysql_query($query,$mysqlconnection);
          // insert into yphrethsh
          $id = mysql_insert_id();
          for ($i=0; $i<count($yphr_arr); $i++) 
          {
                $query = "insert into yphrethsh (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[$i]', '$hours_arr[$i]', $sxol_etos)";
                mysql_query($query,$mysqlconnection);
          }
      } 
      // if one school
      else
      {
	$query0 = "INSERT INTO employee (name, surname, patrwnymo, mhtrwnymo, klados, am, sx_organikhs, sx_yphrethshs, fek_dior, hm_dior, vathm, mk, hm_anal, met_did, proyp, comments, afm, thesi, status, wres) ";
			 	 $query1 = "VALUES ('$name','$surname','$patrwnymo','$mhtrwnymo','$klados','$am','$org','$yphr','$fek_dior','$hm_dior','$vathm','$mk','$hm_anal','$met_did','$proyp','$comments', '$afm', '$thesi', '$katast', '$wres')";
	$query = $query0.$query1;
        $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
        mysql_query($query,$mysqlconnection);
        $id = mysql_insert_id();
        if ($single)
        {
            $query = "insert into yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) values ($id, '$yphr_arr[0]', '$hours_arr[0]', '$org', $sxol_etos)";
            mysql_query($query,$mysqlconnection);
        }
            
      }
      // insert 2 log
      $query1 = "INSERT INTO employee_log (emp_id, userid, action, ip) VALUES ('$id',".$_SESSION['userid'].", 0,'$ip')";
      mysql_query($query1, $mysqlconnection);
    }
    // if already inserted
    else 
    {
        echo "<h2 align=\"center\">Η εγγραφή έχει ήδη καταχωρηθεί...</h2>";
        $dupe = 1;
    }
  } //of if action
  
  // if update
  else
  {
      if ($multi)
      {
          // get current row from db
          $qry = "SELECT * from employee WHERE id=$id";
          $res = mysql_query($qry,$mysqlconnection);
          $before = mysql_fetch_row($res);
              
          $query1 = "UPDATE employee SET name='".$name."', surname='".$surname."', klados='".$klados."', sx_organikhs='".$org."', sx_yphrethshs='$yphr_arr[0]',";
	  $query2 = " patrwnymo='$patrwnymo', mhtrwnymo='$mhtrwnymo', am='$am', tel='$tel', address='$address', idnum='$idnum', amka='$amka', vathm='$vathm', mk='$mk', hm_mk='$hm_mk', fek_dior='$fek_dior', hm_dior='$hm_dior', analipsi='$analipsi',";
          $query3 = " aney='$aney', aney_xr='$aney_xr', aney_apo='$aney_apo', aney_ews='$aney_ews',idiwtiko='$idiwtiko',idiwtiko_liksi='$idiwtiko_liksi',";
	  $query4 = " hm_anal='$hm_anal', met_did='$met_did', proyp='$proyp', anatr='$anatr', comments='$comments',afm='$afm', status='$katast', thesi='$thesi', wres='$wres' WHERE id='$id'";
	  $query = $query1.$query2.$query3.$query4;
          $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
          //echo $query;
          mysql_query($query,$mysqlconnection);
          // insert 2 log
          if (mysql_affected_rows()>0)
          {
            // find changes and write them to query field
              $name = mb_convert_encoding($name, "iso-8859-7", "utf-8");
              $surname = mb_convert_encoding($surname, "iso-8859-7", "utf-8");
              $patrwnymo = mb_convert_encoding($patrwnymo, "iso-8859-7", "utf-8");
              $mhtrwnymo = mb_convert_encoding($mhtrwnymo, "iso-8859-7", "utf-8");
              $address = mb_convert_encoding($address, "iso-8859-7", "utf-8");
              $idnum = mb_convert_encoding($idnum, "iso-8859-7", "utf-8");
              $vathm = mb_convert_encoding($vathm, "iso-8859-7", "utf-8");
              $comments = mb_convert_encoding($comments, "iso-8859-7", "utf-8");
              $after = array($id,$name,$surname,$patrwnymo,$mhtrwnymo,$klados,$am,$org,$yphr,$thesi,$fek_dior,$hm_dior,$before[12],$vathm,$before[14],$before[15],$mk,$hm_mk,$analipsi,$hm_anal,$met_did,$before[21],$proyp,$before[23],$before[24],$before[25],$comments,$katast,$afm,$before[29],$tel,$address,$idnum,$amka,$aney,$aney_xr,$aney_apo,$aney_ews);
              $ind=0;
              foreach ($after as $aft){
                  if (strcmp($aft,$before[$ind]) != 0){
                          $field = mysql_fetch_field($res,$ind);
                          $change .= $field->name.":".$before[$ind]."->".$aft." | ";
                  }
                  $ind++;
              }
            //
            $query1 = "INSERT INTO employee_log (emp_id, userid, action, ip, query) VALUES ('$id',".$_SESSION['userid'].", 1, '$ip', '$change')";
            mysql_query($query1, $mysqlconnection);
          }
          // AND sxol_etos -> future use...
          $query = "DELETE FROM yphrethsh WHERE emp_id = $id AND sxol_etos=$sxol_etos";
          mysql_query($query,$mysqlconnection);
          for ($i=0; $i<count($yphr_arr); $i++) 
          {
                $query = "insert into yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) values ($id, '$yphr_arr[$i]', '$hours_arr[$i]', '$org',$sxol_etos)";
                mysql_query($query,$mysqlconnection);
          }
      }
      else
      {
          // get current row from db
          $qry = "SELECT * from employee WHERE id=$id";
          $res = mysql_query($qry,$mysqlconnection);
          $before = mysql_fetch_row($res);
              
	  $query1 = "UPDATE employee SET name='".$name."', surname='".$surname."', klados='".$klados."', sx_organikhs='".$org."', sx_yphrethshs='$yphr',";
	  $query2 = " patrwnymo='$patrwnymo', mhtrwnymo='$mhtrwnymo', am='$am', tel='$tel', address='$address', idnum='$idnum', amka='$amka', vathm='$vathm', mk='$mk', hm_mk='$hm_mk', fek_dior='$fek_dior', hm_dior='$hm_dior', analipsi='$analipsi',";
          $query3 = " aney='$aney', aney_xr='$aney_xr', aney_apo='$aney_apo', aney_ews='$aney_ews',idiwtiko='$idiwtiko',idiwtiko_liksi='$idiwtiko_liksi',";
	  $query4 = " hm_anal='$hm_anal', met_did='$met_did', proyp='$proyp', anatr='$anatr', comments='$comments',afm='$afm', status='$katast', thesi='$thesi', wres='$wres' WHERE id='$id'";
	  $query = $query1.$query2.$query3.$query4;
          $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
          //echo $query;
          mysql_query($query,$mysqlconnection);
          // insert 2 log
          if (mysql_affected_rows()>0)
          {
            // find changes and write them to query field
              $name = mb_convert_encoding($name, "iso-8859-7", "utf-8");
              $surname = mb_convert_encoding($surname, "iso-8859-7", "utf-8");
              $patrwnymo = mb_convert_encoding($patrwnymo, "iso-8859-7", "utf-8");
              $mhtrwnymo = mb_convert_encoding($mhtrwnymo, "iso-8859-7", "utf-8");
              $address = mb_convert_encoding($address, "iso-8859-7", "utf-8");
              $idnum = mb_convert_encoding($idnum, "iso-8859-7", "utf-8");
              $vathm = mb_convert_encoding($vathm, "iso-8859-7", "utf-8");
              $comments = mb_convert_encoding($comments, "iso-8859-7", "utf-8");
              $after = array($id,$name,$surname,$patrwnymo,$mhtrwnymo,$klados,$am,$org,$yphr,$thesi,$fek_dior,$hm_dior,$before[12],$vathm,$before[14],$before[15],$mk,$hm_mk,$analipsi,$hm_anal,$met_did,$before[21],$proyp,$before[23],$before[24],$before[25],$comments,$katast,$afm,$before[29],$tel,$address,$idnum,$amka,$aney,$aney_xr,$aney_apo,$aney_ews);
              $ind=0;
              foreach ($after as $aft){
                  if (strcmp($aft,$before[$ind]) != 0){
                          $field = mysql_fetch_field($res,$ind);
                          $change .= $field->name.":".$before[$ind]."->".$aft." | ";
                  }
                  $ind++;
              }
            //
            $query1 = "INSERT INTO employee_log (emp_id, userid, action, ip, query) VALUES ('$id',".$_SESSION['userid'].", 1, '$ip', '$change')";
            mysql_query($query1, $mysqlconnection);
          }
          // svhse tyxon >1 yphrethseis 
          $query = "DELETE FROM yphrethsh WHERE emp_id = $id AND sxol_etos = $sxol_etos";
          mysql_query($query,$mysqlconnection);
          if ($single)
          {
              $query = "insert into yphrethsh (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[0]', '$hours_arr[0]', $sxol_etos)";
              mysql_query($query,$mysqlconnection);
          }
      }
  }
  mysql_close();

echo "<br>";
if (!$dupe)
    echo "<h2 align=\"center\">Επιτυχής καταχώρηση!</h2>";
echo "</body>";
echo "</html>";

?>