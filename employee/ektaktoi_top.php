<?php
    header('Content-type: text/html; charset=utf-8'); 
    require_once"../config.php";
    require_once"../include/functions.php";  

    session_start();
?>    
  <html>
  <head>    
  <?php require '../etc/menu.php'; ?>  
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <title>Τοποθέτηση αναπληρωτών εκπαιδευτικών</title>
    <link href="../css/select2.min.css" rel="stylesheet" />
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script src="../js/select2.min.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type="text/javascript">
      $(document).ready(function() { 
        $("#mytbl").tablesorter({widgets: ['zebra']}); 
          $(".select2").select2({
            placeholder: 'Παρακαλώ επιλέξτε...'
          });
      });
    </script>
<?php            
    // init array
    if (!isset($_POST['ektaktos'])) {
        $_SESSION['topothetiseis'] = Array();
    }
    require "../tools/class.login.php";
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false) {
        header("Location: ../tools/login.php");
    }
    $usrlvl = $_SESSION['userlevel'];

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
    
    echo "<html><head><h2>Τοποθέτηση αναπληρωτών εκπαιδευτικών</h2></head><body>";
    
    echo "<form action='' id='hirefrm' method='POST' autocomplete='off'>";
    echo "<table class=\"imagetable\" border='1'>";
    
    $sql = "select e.id,e.surname,e.name,e.patrwnymo,k.perigrafh from ektaktoi e join klados k on e.klados=k.id";
    $result = mysqli_query($mysqlconnection, $sql);
    echo "<tr><td>Εκπαιδευτικός:</td><td>";
    $cmb = "<select name=\"ektaktos\" class=\"select2\" >";
    $cmd .= "<option value=''>Παρακαλώ επιλέξτε σχολείο...</option>";
    while ($row = mysqli_fetch_array($result)){
      $cmb .= "<option value=\"".$row['id']."\"";
      $cmb .= $_POST['ektaktos'] == $row['id'] ? "selected" : "";
      $cmb .= ">".$row['surname'].' '.$row['name']." (τ.".substr($row['patrwnymo'], 0, 6).") (".$row['perigrafh'].")</option>";
    }
    $cmb .= "</select>";
    echo $cmb;
    
    echo "</td></tr>";
    $sql = "select id,name from school";
    $result = mysqli_query($mysqlconnection, $sql);
    echo "<tr><td>Σχολείο:</td><td>";
    $cmb = "<select name=\"school\" class=\"select2\" >";
    while ($row = mysqli_fetch_array($result)){
        $cmb .= "<option value='".$row['id']."'";
        $cmb .= $_POST['school'] == $row['id'] ? "selected" : '';
        $cmb .= ">".$row['name']."</option>";
    }
    $cmb .= "</select>";
    echo $cmb;
    echo "</td></tr>";
    echo "<tr><td>Ώρες:</td><td colspan=2><input type='text' value='24' name='hours' />";
    echo "</td></tr>";
    echo "<input type='hidden' name = 'id' value='".$_POST['ektaktos']."'>";
    echo "<input type='hidden' name = 'schid' value='".$_POST['school']."'>";
    echo "<tr><td colspan=2><input type='submit' value='Προσθήκη Τοποθέτησης'>";
    echo "&nbsp;&nbsp;&nbsp;";
    echo "<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='ektaktoi_list.php'\"></td></tr>";
    echo "</table></form>";

    // submitted: now prompt user to confirm
    if (isset($_POST['ektaktos'])) {
      $top = Array('id' => $_POST['ektaktos'], 'school' => $_POST['school'], 'hours' => $_POST['hours']);
      
      array_push($_SESSION['topothetiseis'],$top);

      echo "<br><br><h2>Επιβεβαίωση προσλήψεων</h2>";
      echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
      echo "<thead><tr><th>Επώνυμο</th><th>Όνομα</th><th>Πατρώνυμο</th><th>Κλάδος</th><th>Σχολείο</th><th>Ώρες</th></tr></thead><tbody>";
      foreach ($_SESSION['topothetiseis'] as $rowar) {
        $sql_ekt = "select e.id,e.surname,e.name,e.patrwnymo,k.perigrafh from ektaktoi e join klados k on e.klados=k.id where e.id=".$rowar['id'];
        $res_ekt = mysqli_query($mysqlconnection, $sql_ekt);
        $row_ekt = mysqli_fetch_array($res_ekt);

        $sql_sch = "select name from school where id=".$rowar['school'];
        $res_sch = mysqli_query($mysqlconnection, $sql_sch);
        $row_sch = mysqli_fetch_array($res_sch);
        
        echo "<tr><td>".$row_ekt['surname']."</td><td>".$row_ekt['name']."</td><td>".$row_ekt['patrwnymo']."</td><td>".$row_ekt['perigrafh']."</td>";
        echo "<td>".$row_sch['name']."</td><td>".$rowar['hours']."</td></tr>";
      }
      echo "<form action='' method='POST' autocomplete='off'>";
      echo "<input type='hidden' name = 'topothetiseis' value='".serialize($_SESSION['topothetiseis'])."'>";
      echo "<tr><td colspan=6><input type='submit' name='finalize' value='Ολοκλήρωση τοποθετήσεων'>";
      echo "</table>";
      echo "</form>";
    }
    //confirmed, now proceed to insertion
    if (isset($_POST['finalize'])) {
      // check if at least one is already inserted
      $topothetiseis = unserialize($_POST['topothetiseis']);
      $i=0;
      $errors = 0;
      foreach ($topothetiseis as $row) {
        // check if 1st yphrethsh in order to update sx_yphrethshs
        $query = "select id from yphrethsh_ekt where emp_id = ".$row['id']." and sxol_etos = $sxol_etos";
        $result = mysqli_query($mysqlconnection, $query);
        if(!mysqli_num_rows($result)){
          $query = "update ektaktoi set sx_yphrethshs = ".$row['school']." where id = ".$row['id'];
          $result = mysqli_query($mysqlconnection, $query);  
        }
        // check if yphrethsh exists
        $query = "SELECT id FROM yphrethsh_ekt WHERE emp_id = ".$row['id']." AND yphrethsh = ".$row['school']." AND sxol_etos = $sxol_etos";
        $result = mysqli_query($mysqlconnection, $query);  
        if(mysqli_num_rows($result) > 0){
          $errors++;
          continue;
        } else {
          $query = "INSERT INTO yphrethsh_ekt (emp_id, yphrethsh, hours, sxol_etos) VALUES (".$row['id'].", '".$row['school']."', '".$row['hours']."', $sxol_etos)";
          $result = mysqli_query($mysqlconnection, $query);
          $i++;
        }
      }
      echo "<h3>Επιτυχής καταχώρηση $i τοποθετήσεων!</h3>";
      if ($errors){
        echo "<h4>$errors σφάλματα...</h4>";
      }
      echo "<INPUT TYPE='button' VALUE='Επιστροφή' class='btn-red' onClick=\"parent.location='ektaktoi_list.php'\"></td></tr>";
    }

mysqli_close($mysqlconnection);
?>
<br><br>
</html>
