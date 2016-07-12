<?php
	header('Content-type: text/html; charset=iso8859-7'); 
        session_start();
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Επεξεργασία σχολείου</title>
  </head>
  <body> 
<?php
  
  require_once"config.php";
  require_once"functions.php";
    
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  

  $titlos = $_POST['titlos'];
  $sch = $_POST['sch'];
  $name = $_POST['name'];
  $address = $_POST['address'];
  $tk = $_POST['tk'];
  $tel = $_POST['tel']; 
  $email = $_POST['email']; 
  $fax = $_POST['fax'];
  $organ = $_POST['organ'];
  $leitoyrg = $_POST['leitoyrg'];
  // 05-10-2012
  $organikes = serialize($_POST['organikes']);
  // 19-06-2013
  $kena_leit = serialize($_POST['kena_leit']);
  $kena_org = serialize($_POST['kena_org']);
    
  $entaksis = $_POST['entaksis'];
  if ($entaksis == 'on')
      $entaksis = 1;
  else
      $entaksis = 0;
  $entaksis = $_POST['entaksis'] . ',' . $_POST['entaksis_math'];
  $ypodoxis = $_POST['ypodoxis'];
  if ($ypodoxis == 'on')
      $ypodoxis = 1;
  else
      $ypodoxis = 0;
  $frontistiriako = $_POST['frontistiriako'];
  if ($frontistiriako == 'on')
      $frontistiriako = 1;
  else
      $frontistiriako = 0;
  $ted = $_POST['ted'];
  if ($ted == 'on')
      $ted = 1;
  else
      $ted = 0;
  $oloimero = $_POST['oloimero'];
  if ($oloimero == 'on')
      $oloimero = 1;
  else
      $oloimero = 0;
  
  $comments = $_POST['comments'];
  $students = $_POST['a'].",".$_POST['b'].",".$_POST['c'].",".$_POST['d'].",".$_POST['e'].",".$_POST['f'].",".$_POST['g'].",".$_POST['h'];
   
  //29-6-2012
  $oloimero_tea = $_POST['oloimero_tea'];
  $oloimero_stud = $_POST['oloimero_stud'];
  $tmimata = $_POST['ta'].",".$_POST['tb'].",".$_POST['tc'].",".$_POST['td'].",".$_POST['te'].",".$_POST['tf'].",".$_POST['tg'].",".$_POST['th'];
  $ekp_ee = $_POST['ekp_te'].",".$_POST['ekp_ty'];
  
  $klasiko = $_POST['k1a'].",".$_POST['k1b'].",".$_POST['k2a'].",".$_POST['k2b'].",".$_POST['k3a'].",".$_POST['k3b'];  
  // meikto
  $klasiko .= ",".$_POST['k4a'].",".$_POST['k4b'].",".$_POST['k4c'].",".$_POST['k4d'].",".$_POST['k5a'].",".$_POST['k5b'].",".$_POST['k5c'].",".$_POST['k5d'];
  // oloimero nip
  $oloimero_nip = $_POST['o1a'].",".$_POST['o1b'].",".$_POST['o2a'].",".$_POST['o2b'].",".$_POST['o3a'].",".$_POST['o3b'].",".$_POST['o4a'].",".$_POST['o4b'];
  $nip = $_POST['ekp_kl'].",".$_POST['ekp_ol'].",".$_POST['ekp_te'];
  
  $query0 = "UPDATE school SET name = '$name', address = '$address', tel='$tel', fax='$fax', email='$email', organikothta='$organ', leitoyrg='$leitoyrg', organikes='$organikes', students='$students', entaksis='$entaksis', ypodoxis='$ypodoxis', frontistiriako='$frontistiriako', ted='$ted', oloimero='$oloimero', comments='$comments'";
  $query1 = ", oloimero_tea = '$oloimero_tea', oloimero_stud = '$oloimero_stud', tmimata = '$tmimata', ekp_ee='$ekp_ee'";
  $query2 = ", klasiko = '$klasiko', oloimero_nip = '$oloimero_nip', nip = '$nip', kena_org = '$kena_org', kena_leit = '$kena_leit', titlos = '$titlos', tk = '$tk'";
  $query3 = " WHERE id=$sch";
  $query = $query0.$query1.$query2.$query3;
  //$query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
  //echo $query;
  mysql_query($query,$mysqlconnection);

  //echo "Επιτυχής καταχώρηση!";
  mysql_close();
?>
<br>
  <center>
<h3>Επιτυχής καταχώρηση!</h3>
<br>
<meta http-equiv="refresh" content="2; URL=school_status.php?org=<?php echo $sch;?>">
</center>
</body>
</html>