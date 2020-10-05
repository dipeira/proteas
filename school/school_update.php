<?php
  header('Content-type: text/html; charset=utf-8'); 
  
  require_once"../config.php";
  require_once"../include/functions.php";
    
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
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
  $anenergo = $_POST['anenergo'] == 'on' ? 1 : 0;
  // 05-10-2012
  $organikes = serialize($_POST['organikes']);
  // 19-06-2013
  $kena_leit = serialize($_POST['kena_leit']);
  $kena_org = serialize($_POST['kena_org']);
    
  $entaksis = $_POST['entaksis'] == 'on' ? 1 : 0;
  
  $entaksis = $_POST['entaksis'] . ',' . $_POST['entaksis_math'];
  
  $ypodoxis = $_POST['ypodoxis'] == 'on' ? 1 : 0;
  
  $frontistiriako = $_POST['frontistiriako'] == 'on' ? 1 : 0;
  
  $ted = $_POST['ted'] == 'on' ? 1 : 0;
  
  $oloimero = $_POST['oloimero'] == 'on' ? 1 : 0;
  
  $vivliothiki = $_POST['vivliothiki'] == 'on' ? $_POST['workercmb'] : 0;
  
  $comments = $_POST['comments'];
  $students = $_POST['a'].",".$_POST['b'].",".$_POST['c'].",".$_POST['d'].",".$_POST['e'].",".$_POST['f'].",".$_POST['g'].",".$_POST['h'];
   
  //29-6-2012
  $oloimero_tea = $_POST['oloimero_tea'];
  $oloimero_stud = $_POST['oloimero_stud'];
  $tmimata = $_POST['ta'].",".$_POST['tb'].",".$_POST['tc'].",".$_POST['td'].",".$_POST['te'].",".$_POST['tf'].",".$_POST['tg'].",".$_POST['th'].",".$_POST['ti'];
  $ekp_ee = $_POST['ekp_te'].",".$_POST['ekp_ty'];
  
  $klasiko = $_POST['k1a'].",".$_POST['k1b'].",".$_POST['k2a'].",".$_POST['k2b'].",".$_POST['k3a'].",".$_POST['k3b'];  
  // PZ
  $klasiko .= ",".$_POST['pz'];
  // tm4
  $klasiko .=",".$_POST['k4a'].",".$_POST['k4b'];
  // tm5
  $klasiko .=",".$_POST['k5a'].",".$_POST['k5b'];
  // tm6
  $klasiko .=",".$_POST['k6a'].",".$_POST['k6b'];
  // oloimero nip
  $oloimero_nip = $_POST['o1a'].",".$_POST['o1b'].",".$_POST['o2a'].",".$_POST['o2b'].",".$_POST['o3a'].",".$_POST['o3b'].",".$_POST['o4a'].",".$_POST['o4b'].",".$_POST['o5a'].",".$_POST['o5b'].",".$_POST['o6a'].",".$_POST['o6b'];
  $nip = $_POST['ekp_kl'].",".$_POST['ekp_ol'].",".$_POST['ekp_te'];
  
  $query0 = "UPDATE school SET name = '$name', address = '$address', tel='$tel', fax='$fax', email='$email', organikothta='$organ', leitoyrg='$leitoyrg', organikes='$organikes', students='$students', entaksis='$entaksis', ypodoxis='$ypodoxis', frontistiriako='$frontistiriako', ted='$ted', oloimero='$oloimero', comments='$comments'";
  $query1 = ", oloimero_tea = '$oloimero_tea', oloimero_stud = '$oloimero_stud', tmimata = '$tmimata', ekp_ee='$ekp_ee'";
  $query2 = ", klasiko = '$klasiko', oloimero_nip = '$oloimero_nip', nip = '$nip', kena_org = '$kena_org', kena_leit = '$kena_leit', titlos = '$titlos', tk = '$tk'";
  $query3 = ", anenergo = '$anenergo', vivliothiki = '$vivliothiki' WHERE id=$sch";
  $query = $query0.$query1.$query2.$query3;
  //echo $query;
  mysqli_query($mysqlconnection, $query);
  mysqli_close($mysqlconnection);
  notify('Επιτυχής καταχώρηση!',0);
?>
