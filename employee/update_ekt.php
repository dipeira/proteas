<?php
	header('Content-type: text/html; charset=utf-8'); 
        session_start();
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <script type="text/javascript" src="../js/jquery_notification_v.1.js"></script>
    <link href="../css/jquery_notification.css" type="text/css" rel="stylesheet"/> 
    <title>Update</title>
  </head>
  <body> 
<?php
  
  require_once"../config.php";
  require_once"../include/functions.php";
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  

  $id = $_POST['id'];
  $name = $_POST["name"];  
  $surname = $_POST['surname']; 
  $klados =$_POST['klados']; 
  $stathero = $_POST['stathero'];
  $kinhto = $_POST['kinhto'];
  $thesi = $_POST['thesi'];
  $entty = $_POST['entty'];
  $email = $_POST['email'];
  $email_psd = $_POST['email_psd'];
  
  $ip = $_SERVER['REMOTE_ADDR'];
  
  // yphr array
  $count = count($_POST['yphr']);
  // if multiple
  if ($count > 1)
  { 
        $multi = 1;
        // check if has duplicate schools
        if (count($_POST['yphr']) != count(array_unique($_POST['yphr']))){
          notify('Σφάλμα: διπλή καταχώρηση σχολείου υπηρέτησης!',1);
          mysqli_close($mysqlconnection);
          die();
        }

        for ($i=0; $i<$count; $i++)
        {
            $yphret[$i] = $_POST['yphr'][$i];
            $yphr_arr[$i] = getSchoolID($yphret[$i],$mysqlconnection);
            $hours_arr[$i] = $_POST['hours'][$i];
        }
  }
  else
  {
      if ($_POST['yphr'][0] == "")
        $yphr = getSchoolID('Άγνωστο',$mysqlconnection);
      else
      {
        $yphret = $_POST['yphr'][0];
        $yphret = $yphret;
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
  $hm_apox = date('Y-m-d',strtotime($_POST['hm_apox']));
  $met_did = $_POST['met_did'];

  //$ya = $_POST['ya'];
  $type = $_POST['type'];
  //$apofasi = $_POST['apofasi'];
  $comments = $_POST['comments'];
  $katast = $_POST['status'];
  $metakinhsh = addslashes($_POST['metakinhsh']);
  $praxi = $_POST['praxi'];
  $wres = $_POST['wres'];
    
// $_POST['action']=1 for adding records  
  if (isset($_POST['action']))
  {
      // check if record exists by checking am AND surname
      $surn = $surname;
      $query = "select afm,surname from ektaktoi WHERE afm='$afm' AND surname = '$surn'";
      $result = mysqli_query($mysqlconnection, $query);
      // hm/nia apoxwrhshs (from endofyear param)
      $hm_apox = date('Y-m-d',strtotime(getParam('endofyear2',$mysqlconnection)));

      if (!mysqli_num_rows($result))
      {
            $query0 = "INSERT INTO ektaktoi (name, surname, patrwnymo, mhtrwnymo, klados, sx_yphrethshs, analipsi, hm_anal, type, comments, afm, status, metakinhsh, praxi, stathero, kinhto, met_did, hm_apox, thesi, ent_ty,wres) ";
            $query1 = "VALUES ('$name','$surname','$patrwnymo','$mhtrwnymo','$klados','$yphr_arr[0]','$analipsi','$hm_anal','$type','$comments', '$afm', '$katast', '$metakinhsh', '$praxi', '$stathero', '$kinhto', '$met_did', '$hm_apox',$thesi,'$entty',24)";

            $query = $query0.$query1;
            //echo $query;
            mysqli_query($mysqlconnection, $query);
            // insert into yphrethsh
            $id = mysqli_insert_id($mysqlconnection);
            for ($i=0; $i<count($yphr_arr); $i++) 
            {
                    $query = "insert into yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[$i]', '$hours_arr[$i]', $sxol_etos)";
                    mysqli_query($mysqlconnection, $query);
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
          $res = mysqli_query($mysqlconnection, $qry);
          $before = mysqli_fetch_row($res);
          
          $query1 = "UPDATE ektaktoi SET name='".$name."', surname='".$surname."', klados='".$klados."', sx_yphrethshs='$yphr_arr[0]',";
          $query2 = " patrwnymo='$patrwnymo', mhtrwnymo='$mhtrwnymo', analipsi='$analipsi', met_did='$met_did',hm_apox='$hm_apox',thesi=$thesi,wres=$wres, ent_ty=$entty, ";
          $query3 = " hm_apox='$hm_apox', hm_anal='$hm_anal', type= '$type', comments='$comments',afm='$afm', status='$katast', metakinhsh='$metakinhsh', praxi='$praxi', stathero='$stathero', kinhto='$kinhto',email='$email',email_psd='$email_psd' WHERE id='$id'";
          $query = $query1.$query2.$query3;

          $qlog .= $query;
          mysqli_query($mysqlconnection, $query);
          $query = "DELETE FROM yphrethsh_ekt WHERE emp_id = $id AND sxol_etos=$sxol_etos";
          mysqli_query($mysqlconnection, $query);
          for ($i=0; $i<count($yphr_arr); $i++) 
          {
                $query = "insert into yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[$i]', '$hours_arr[$i]', $sxol_etos)";
                mysqli_query($mysqlconnection, $query);
                $qlog = $qlog . ' \n ' . $query;
          }
          // insert 2 log
          $qlog = addslashes($qlog);
          $query1 = "INSERT INTO ektaktoi_log (emp_id, userid, action, ip, query) VALUES ('$id',".$_SESSION['userid'].", 1, '$ip', '$qlog')";
          mysqli_query($mysqlconnection, $query1);
      }
      else
      {
          // get current row from db
          $qry = "SELECT * from ektaktoi WHERE id=$id";
          $res = mysqli_query($mysqlconnection, $qry);
          $before = mysqli_fetch_row($res);
          
          $query1 = "UPDATE ektaktoi SET name='".$name."', surname='".$surname."', klados='".$klados."', sx_yphrethshs='$yphr',";
          $query2 = " patrwnymo='$patrwnymo', mhtrwnymo='$mhtrwnymo', analipsi='$analipsi', met_did='$met_did',thesi=$thesi,ent_ty=$entty,wres=$wres,";
          $query3 = " hm_apox='$hm_apox', hm_anal='$hm_anal', type= '$type', comments='$comments',afm='$afm', status='$katast', ya='$ya', apofasi='$apofasi', metakinhsh='$metakinhsh', praxi='$praxi', stathero='$stathero', kinhto='$kinhto', email='$email', email_psd='$email_psd' WHERE id='$id'";
          $query = $query1.$query2.$query3;
          $qlog .= $query;
          //echo $query;
          mysqli_query($mysqlconnection, $query);
          // svhse tyxon >1 yphrethseis 
          $query = "DELETE FROM yphrethsh_ekt WHERE emp_id = $id AND sxol_etos = $sxol_etos";
          mysqli_query($mysqlconnection, $query);
          if ($single)
          {
              $query = "insert into yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[0]', '$hours_arr[0]', $sxol_etos)";
              mysqli_query($mysqlconnection, $query);
              $qlog = $qlog . ' \n ' . $query;
          }
          // insert 2 log
          $qlog = addslashes($qlog);
          $query1 = "INSERT INTO ektaktoi_log (emp_id, userid, action, ip, query) VALUES ('$id',".$_SESSION['userid'].", 1, '$ip', '$qlog')";
          mysqli_query($mysqlconnection, $query1);
      }
  }

  if (!$dupe)
    notify('Επιτυχής καταχώρηση!',0);
  mysqli_close($mysqlconnection);
?>
<br>
</body>
</html>