<?php
header('Content-type: text/html; charset=utf-8'); 
require_once"../config.php";
require_once"../include/functions.php";

require "../tools/class.login.php";
$log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
  header("Location: ../tools/login.php");
}

require_once '../tools/paginator.php';
 
$conn       = new mysqli( $db_host, $db_user, $db_password, $db_name );

$limit      = ( isset( $_GET['limit'] ) ) ? $_GET['limit'] : 25;
$page       = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1;
$links      = ( isset( $_GET['links'] ) ) ? $_GET['links'] : 7;

// request status
// use $_SESSION in case the user clicks a link from pagination buttons
if ( isset( $_GET['type'] ) ) {
  $type = $_SESSION['type'] = $_GET['type'] == 0 ? null : $_GET['type'];
} else {
  $type = $_SESSION['type'];
}
// if ($type == 2)
//   $status = 0;
// elseif ($stat == 1) {
//   $status = 1;
// }
// $stat_radio = $stat == 2 ? $stat_radio = 2 : $stat;

$query      = "SELECT id, code, name, tel, email, organikothta, type, type2 FROM school WHERE anenergo = 0 ";
$query      .= isset ($type) ? "AND type = $type" : '';
$query      .= " ORDER BY type,name ASC";

$Paginator  = new Paginator( $conn, $query );

$results    = $Paginator->getData( $limit, $page );

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
    <script>
      $(document).ready(function() { 
        $("#mytbl").tablesorter();
      });
    
      // $().ready(function() {
      //   $("#org").autocomplete("../employee/get_school.php", {
      //     dataType: "json",
      //     extraParams: {getids: true},
      //     width: 260,
      //     matchContains: true,
      //     selectFirst: false
      //   });
      // });
    </script>

  <title>Λίστα Σχολείων</title>
  <style>
    ul.pagination {
        display: inline-block;
        padding: 0;
        margin: 0;
    }

    ul.pagination li {display: inline;}

    ul.pagination li a {
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
    }

    ul.pagination li a.active {
        background-color: #4CAF50;
        color: white;
    }

    ul.pagination li a:hover:not(.active) {background-color: #ddd;}
  </style>
</head>
<body>
<?php require '../etc/menu.php'; ?>
<h2>Λίστα Σχολείων</h2>
<?php

    // echo "<form id='searchfrm' name='searchfrm' action='school_status.php' method='GET' autocomplete='off'>";
    // echo "Σχολείο&nbsp;";
    // echo "<input type=\"text\" name=\"org\" id=\"org\" style='width:250px;'/>";                
    // echo "	<input type='submit' value='Αναζήτηση'>";
    // //echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    // echo "	</form>";

    ?>
<p>Τύπος Σχολείου: </p>
<form id="request_status">
    <input type="radio" name="type" value="0" <?= $type == 0 ? 'checked' : ''?> onchange="this.form.submit()"> Όλα
    <input type="radio" name="type" value="1" <?= $type == 1 ? 'checked' : ''?> onchange="this.form.submit()"> Δημοτικό Σχολέιο
    <input type="radio" name="type" value="2" <?= $type == 2 ? 'checked' : ''?> onchange="this.form.submit()"> Νηπιαγωγείο
</form>
<br>
<?php
  echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
  echo "<thead><tr><th>A/A</th><th>Κωδ.Υπουργείο</th><th>Όνομα</th><th>Τηλ.</th><th>email</th><th>Οργανικότητα</th><th>Δ.Σ./Νηπ.</th><th>Τύπος</th></tr></thead><tbody>";
  for( $i = 0; $i < count( $results->data ); $i++ ){
        echo "<tr>";
        echo "<td>".$results->data[$i]['id']."</a></td>";
        echo "<td>".$results->data[$i]['code']."</a></td>";
        echo "<td><a href='school_status.php?org=".$results->data[$i]['id']."'>".$results->data[$i]['name']."</td>";
        echo "<td>".$results->data[$i]['tel']."</td>";
        echo "<td><a href='mailto:".$results->data[$i]['email']."'>".$results->data[$i]['email']."</td>";
        echo "<td>".$results->data[$i]['organikothta']."</td>";
        echo "<td>";
        switch ($results->data[$i]['type']) {
          case '0':
            echo "Λοιπά";
            break;
          case '1':
            echo "Δ.Σ.";
            break;
          case '2':
            echo "Νηπ.";
            break;
        }
        echo "</td>";
        echo "<td>";
        switch ($results->data[$i]['type2']) {
          case '0':
            echo "Δημόσιο";
            break;
          case '1':
            echo "Ιδιωτικό";
            break;
          case '2':
            echo "Ειδικό";
            break; 
        }
        echo "</td>";
        echo "</tr>";
  }
  echo "</tbody></table>";
  
  echo $Paginator->createLinks( $links, 'pagination');

?>
<form>
<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
</form>

</body></html>

