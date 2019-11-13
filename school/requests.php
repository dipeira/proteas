<?php
header('Content-type: text/html; charset=utf-8'); 
require_once"../config.php";
require_once"../tools/functions.php";

require "../tools/class.login.php";
$log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
  header("Location: ../tools/login.php");
}

if (!$_SESSION['requests']) {
  echo "<h3>Σφάλμα: Δεν έχετε δικαίωμα προβολής αιτημάτων σχολείων...</h3>";
  die("<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">");
}

require_once '../tools/paginator.php';
 
$conn       = new mysqli( $db_host, $db_user, $db_password, $db_name );

$limit      = ( isset( $_GET['limit'] ) ) ? $_GET['limit'] : 25;
$page       = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1;
$links      = ( isset( $_GET['links'] ) ) ? $_GET['links'] : 7;

// request status
// use $_SESSION in case the user clicks a link from pagination buttons
if ( isset( $_GET['status'] ) ) {
  $stat = $_GET['status'];
  $_SESSION['status'] = $_GET['status'];
} else {
  $stat = $_SESSION['status'];
}
if ($stat == 2)
  $status = 0;
elseif ($stat == 1) {
  $status = 1;
}
$stat_radio = $stat == 2 ? $stat_radio = 2 : $stat;

$query      = "SELECT id, school, school_name, request, comment, done, submitted, sxol_etos FROM school_requests WHERE hidden = 0 ";
$query      .= isset ($status) ? "AND done = $status" : '';
$query      .= " ORDER BY submitted DESC";

$Paginator  = new Paginator( $conn, $query );

$results    = $Paginator->getData( $limit, $page );

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script>
    $(document).ready(function() { 
      $("#mytbl").tablesorter();
    });
    </script>

  <title>Διαχείριση Αιτημάτων</title>
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
<h2>Διαχείριση Αιτημάτων Σχολείων</h2>

<p>Εμφάνιση αιτημάτων: </p>
<form id="request_status">
    <input type="radio" name="status" value="0" <?= $stat_radio == 0 ? 'checked' : ''?> onchange="this.form.submit()"> Όλα
    <input type="radio" name="status" value="1" <?= $stat_radio == 1 ? 'checked' : ''?> onchange="this.form.submit()"> Διεκπεραιωμένα
    <input type="radio" name="status" value="2" <?= $stat_radio == 2 ? 'checked' : ''?> onchange="this.form.submit()"> Μη Διεκπεραιωμένα
</form>
<br>
<?php
  echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">";
  echo "<thead><tr><th>A/A</th><th>Σχολείο</th><th>Αίτημα</th><th>Σχόλιο Δ/νσης</th><th>Διεκπεραίωση</th><th>Ημ/νία υποβολής</th></tr></thead><tbody>";
  for( $i = 0; $i < count( $results->data ); $i++ ){
        echo "<tr>";
        echo "<td>".$results->data[$i]['id']."</a></td>";
        echo "<td><a href='school_status.php?org=".$results->data[$i]['school']."' target='_blank'>".$results->data[$i]['school_name']."</td>";
        echo "<td>".nl2br($results->data[$i]['request'])."</td>";
        echo "<td>".nl2br($results->data[$i]['comment'])."</td>";
        echo "<td>";
        echo $results->data[$i]['done'] ? 'Ναι' : 'Όχι';
        echo "</td>";
        echo "<td>".$results->data[$i]['submitted']."</td>";
        //echo "<td>".$results->data[$i]['sxol_etos']."</td>";
        echo "</tr>";
  }
  echo "</tbody></table>";
  
  echo $Paginator->createLinks( $links, 'pagination');

?>
<form>
<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
</form>

</body></html>

