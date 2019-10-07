<?php
	header('Content-type: text/html; charset=iso8859-7'); 
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Update</title>
  </head>
  <body> 
<?php
  
  require_once"../config.php";
  require_once"../tools/functions.php";
    
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'greek'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
  

  $id = $_POST['id'];
  $emp_id = $_POST["emp_id"];  
  $type = $_POST['type']; 
  $prot =$_POST['prot']; 
  $hm_prot = date('Y-m-d',strtotime($_POST['hm_prot']));
  $prot_apof = $_POST['prot_apof'];
  $hm_apof = date('Y-m-d',strtotime($_POST['hm_apof']));
  $date = date('Y-m-d',strtotime($_POST['date']));
  $vev_dil =$_POST['vev_dil']; 
  $days =$_POST['days']; 
  $start = date('Y-m-d',strtotime($_POST['start']));
  $finish = date('Y-m-d',strtotime($_POST['finish']));
  $logos =$_POST['logos']; 
  $comments =$_POST['comments']; 
  $sxoletos = $_POST['sxoletos']; 
  //$logos = mb_convert_encoding($_POST['logos'], "iso-8859-7", "utf-8");
  //$comments = mb_convert_encoding($_POST['comments'], "iso-8859-7", "utf-8");
  
// Elegxoi:
  // if (!$prot)
  //     die('Παρακαλώ εισάγετε αριθμό πρωτοκόλλου');
  
  // if ($prot)
  // {
  //   $qry = "SELECT prot FROM adeia WHERE prot = $prot";
  //   $res = mysqli_query($mysqlconnection, $qry);
  //   if (mysqli_num_rows($res)>0)
  //       die('Η άδεια με αυτόν τον αρ.πρωτ. έχει ήδη καταχωρηθεί...');
  // }
  if (!$days)
      die('Σφάλμα: Παρακαλώ εισάγετε αριθμό ημερών');
  if ($start > $finish)
      die('Σφάλμα: Η ημερομηνία λήξης πρέπει να είναι ίση ή μεταγενέστερη της ημερομηνίας έναρξης');      
  
// $_POST['action']=1 for adding records  
  if (isset($_POST['action']))
  {
	$query0 = "INSERT INTO adeia_ekt (emp_id, type, prot, hm_prot, prot_apof, hm_apof, date, vev_dil, days, start, finish, logos, comments, sxoletos) ";
		 $query1 = "VALUES ('$emp_id','$type','$prot','$hm_prot','$prot_apof','$hm_apof','$date','$vev_dil','$days','$start', '$finish', '$logos','$comments', '$sxoletos')";
	$query = $query0.$query1;
  }
  else
  {
	  $query1 = "UPDATE adeia_ekt SET emp_id='$emp_id', type='$type', prot='$prot', hm_prot='$hm_prot', hm_apof='$hm_apof', prot_apof='$prot_apof', date='$date', vev_dil='$vev_dil', days='$days', start='$start', finish='$finish', logos='$logos', comments='$comments'";
	  $query2 = " WHERE id='$id'";
	  $query = $query1.$query2;
  }
  $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
  
  // for debugging...
  // echo "<br>".$query;
  
  mysqli_query($mysqlconnection, $query);
  
  //echo "Επιτυχής καταχώρηση!";
  mysqli_close($mysqlconnection);
?>
<br>
<h2 align="center">Επιτυχής καταχώρηση!</h2>
</body>
</html>