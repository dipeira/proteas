<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once"../config.php";
	require_once"../tools/functions.php";
?>	
        
<?php        
	// metakinhsh_ekpkoy

  include("../tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false)
      header("Location: ../tools/login.php");
  $usrlvl = $_SESSION['userlevel'];
  
  echo "<html>";
  echo "<head>";
  echo "<link rel='stylesheet' type='text/css' href='../css/style.css' />";
  ?>
  <script type="text/javascript" src="../js/jquery.js"></script>
  <script type="text/javascript" src="../js/jquery.table.addrow.js"></script>
  <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
  <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
  <script type="text/javascript">        
      $().ready(function() {
        $(".addRow").btnAddRow(function(row){
          row.find(".yphrow").autocomplete("get_school.php", {
              width: 260,
              matchContains: true,
              selectFirst: false
          })
        });
        $(".delRow").btnDelRow();
        $(".yphrow").autocomplete("get_school.php", {
          width: 260,
          matchContains: true,
          selectFirst: false
        });
      });         
    </script>
  <?php
  echo "</head>";
  echo "<body>";
  include('../etc/menu.php');
  
  if (!isset($_POST['yphr'])){
    // check if employee data are sent to the script
    if (!isset($_REQUEST['id']) || !isset($_REQUEST['type'])){
      echo "<h3>Σφάλμα: Δεν έχει επιλεγεί υπάλληλος...</h3>";
      echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
      die();
    }
    $emp_data = urlencode(serialize($_POST));
    
    echo "<h2>Μετακίνηση εκπαιδευτικού με ον/μο: ".$_POST['surname'].' '.$_POST['name']."</h2>";
    echo "<form action='' method='POST'>";
    echo "<table class='imagetable stable' border='1'>";
    echo "<tr><td>Σχολείο μετακίνησης</td>";
    echo "<td><input type='text' name='yphr[]' class='yphrow' />";
    echo "&nbsp;&nbsp;<input type='text' name='hours[]' size=1 />";
    echo "&nbsp;<input class='addRow' type='button' value='Προσθήκη' />";
    echo "<input class='delRow' type='button' value='Αφαίρεση' /></td></tr>";
    echo "<tr><td>Αρ.Πρωτοκόλλου</td><td><input type='text' name='prot' />";
    echo "<tr><td>Ημ/νία Πρωτοκόλλου<br><small>(ΗΗ/MM/ΕΕΕΕ)</small></td><td><input type='text' name='hmprot' />";
    echo "<tr><td>Μετακίνηση από<br><small>(ΗΗ/MM/ΕΕΕΕ)</small></td><td><input type='text' name='datefrom' />";
    echo "<tr><td>Σχόλια</td><td><textarea rows=4 cols=50 name='comment'></textarea></td></tr>";
    echo "<tr><td colspan=2><input type='submit' value='Υποβολή'>";
    echo "<input type='hidden' name='data' value=$emp_data>";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "</td></tr>";
    echo "</table></form>";
  } else {
  // if user has submitted data
  // if(isset($_POST['yphr']))
  // {   
    // check if data submitted
    if (strlen($_POST['yphr'][0]) == 0)
        exit('<h3>Παρακαλώ εισάγετε σχολείο/-α...</h3>');
    
    $emp_data = unserialize(urldecode($_POST['data']));

    $is_monimos = $emp_data['type'] === "mon" ? true : false;
    // print_r($_POST);
    // print_r($emp_data);
    // find employee type
    if (!$is_monimos){
      if ($emp_data['type'] == 2){
        $is_kratikos = true;
      } else {
        $is_kratikos = false;
      }
    }
            
    // include PHPWord
    require_once '../vendor/autoload.php';

    $PHPWord = new PHPWord();
    if ($is_monimos)
    {
      $document = $PHPWord->loadTemplate('../word/tmpl/tmpl_metak_mon.docx');
    } else {
      if ($is_kratikos)
        $document = $PHPWord->loadTemplate('../word/tmpl/tmpl_metak_krat.docx');
      else
        $document = $PHPWord->loadTemplate('../word/tmpl/tmpl_metak_espa.docx');
    }
    
    // head_title & head_name
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'greek'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
    
    // gather document data
    $data = array();
    $data['head_title'] = getParam('head_title', $mysqlconnection);
    $data['head_name'] = getParam('head_name', $mysqlconnection);
    $data['endofyear'] = getParam('endofyear', $mysqlconnection);
    $data['prot'] = $_POST['prot'];
    $data['hmprot'] = $_POST['hmprot'];
    $data['datefrom'] = $_POST['datefrom'];
    $data['comment'] = $_POST['comment'];
    $data['klados'] = $emp_data['klados'];
    $data['surname'] = $emp_data['surname'];
    $data['name'] = $emp_data['name'];
    $data['patrwnymo'] = $emp_data['patrwnymo'];
    if ($is_monimos){
      $data['am'] = $emp_data['am'];
    } else {
      $data['afm'] = $emp_data['afm'];
      $data['ya'] = $emp_data['ya'];
      $data['ada'] = $emp_data['ada'];
      $data['apofasi'] = $emp_data['apofasi'];
      $thema = get_diavgeia_subject($data['ada']);
      $data['thema'] = $thema;
    }
    $data['yphrethsh'] = $emp_data['yphrethsh'];
    // metakinhsh
    $metakinhsh = array();
    $i = 0;
    foreach ($_POST['yphr'] as $value) {
      $metakinhsh[] =  "$value (".$_POST['hours'][$i]." ώρες)";
      $i++;
    }
    $data['metakinhsh'] = implode(', ',$metakinhsh);

    // convert all values to utf8
    array_walk(
      $data,
      function (&$entry) {
          $entry = mb_convert_encoding($entry, "utf-8", "iso-8859-7");
      }
    );
    // replace in document
    foreach ($data as $key => $value) {
      $document->setValue($key, $value);
    }
    
    $output1 = "../word/apof_metak".$_SESSION['userid'].".docx";
    $document->save($output1);
    echo "<h3>To έγγραφό σας δημιουργήθηκε με επιτυχία!</h3>";
    echo "<p><a href=$output1>¶νοιγμα εγγράφου</a></p>";
    $url = $is_monimos ? 
      "employee.php?id=".$emp_data['id']."&op=view" : 
      "ektaktoi.php?id=".$emp_data['id']."&op=view";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='$url'\">";
    
    mysqli_close($mysqlconnection);  
  }
?>
</body>
</html>
