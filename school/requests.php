<?php
header('Content-type: text/html; charset=utf-8'); 
require_once"../config.php";
require_once"../include/functions.php";

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

$query      = "SELECT id, school, school_name, request, comment, done, submitted, sxol_etos FROM school_requests WHERE sxol_etos = $sxol_etos AND hidden = 0 ";
$query      .= isset ($status) ? "AND done = $status" : '';
$query      .= " ORDER BY submitted DESC";

$Paginator  = new Paginator( $conn, $query );

$results    = $Paginator->getData( $limit, $page );

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <?php 
    $root_path = '../';
    $page_title = 'Διαχείριση Αιτημάτων';
    require '../etc/head.php'; 
    ?>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script>
    $(document).ready(function() { 
      $("#mytbl").tablesorter();
    });
    </script>
  <style>
    body {
        padding: 20px;
    }
    
    .page-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .page-header {
        text-align: center;
        margin: 20px 0 30px 0;
    }
    
    .filter-form {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .filter-form p {
        margin: 0 0 12px 0;
        font-weight: 600;
        color: #374151;
        font-size: 0.9375rem;
    }
    
    .filter-form input[type="radio"] {
        margin-right: 8px;
        margin-left: 16px;
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #4FC5D6;
    }
    
    .filter-form input[type="radio"]:first-of-type {
        margin-left: 0;
    }
    
    .filter-form label {
        margin-right: 20px;
        cursor: pointer;
        font-size: 0.9375rem;
        color: #374151;
    }
    
    .table-container {
        display: flex;
        justify-content: center;
        margin: 24px 0;
    }
    
    ul.pagination {
        display: inline-block;
        padding: 0;
        margin: 20px 0;
        list-style: none;
    }

    ul.pagination li {
        display: inline;
    }

    ul.pagination li a {
        color: #4FC5D6;
        float: left;
        padding: 10px 16px;
        text-decoration: none;
        border-radius: 6px;
        margin: 0 4px;
        background: white;
        border: 1px solid #bae6fd;
        transition: all 0.2s;
        font-weight: 600;
    }

    ul.pagination li a.active {
        background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
        color: white;
        border-color: #4FC5D6;
    }

    ul.pagination li a:hover:not(.active) {
        background: linear-gradient(135deg, #4FC5D6 0%, #3BA8B8 100%);
        color: white;
        border-color: #4FC5D6;
        transform: translateY(-1px);
    }
    
    .no-results {
        text-align: center;
        padding: 40px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin: 24px 0;
    }
    
    .no-results h3 {
        color: #6b7280;
        font-size: 1.125rem;
        font-weight: 600;
    }
    
    .button-container {
        text-align: center;
        margin: 30px 0;
    }
    
    #mytbl th:nth-child(1),
    #mytbl td:nth-child(1) {
        min-width: 11px;
        white-space: nowrap;
    }

    #mytbl th:nth-child(2),
    #mytbl td:nth-child(2) {
        min-width: 250px;
        white-space: nowrap;
    }
    
    #mytbl th:nth-child(6),
    #mytbl td:nth-child(6) {
        min-width: 150px;
        white-space: nowrap;
    }

    #mytbl th:nth-child(5),
    #mytbl td:nth-child(5) {
        min-width: 20px;
        white-space: nowrap;
    }
  </style>
</head>
<body>
<?php require '../etc/menu.php'; ?>
<div class="page-container">
    <div class="page-header">
        <h2>Διαχείριση Αιτημάτων Σχολείων</h2>
    </div>
    
    <div class="filter-form">
        <form id="request_status" method="GET">
            <p>Εμφάνιση αιτημάτων:</p>
            <input type="radio" name="status" id="status_all" value="0" <?= $stat_radio == 0 ? 'checked' : ''?> onchange="this.form.submit()">
            <label for="status_all">Όλα</label>
            <input type="radio" name="status" id="status_done" value="1" <?= $stat_radio == 1 ? 'checked' : ''?> onchange="this.form.submit()">
            <label for="status_done">Διεκπεραιωμένα</label>
            <input type="radio" name="status" id="status_pending" value="2" <?= $stat_radio == 2 ? 'checked' : ''?> onchange="this.form.submit()">
            <label for="status_pending">Μη Διεκπεραιωμένα</label>
        </form>
    </div>
    
    <?php
    if (count($results->data)){
        echo "<div class=\"table-container\">";
        echo "<center>";
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\">";
        echo "<thead><tr><th>A/A</th><th>Σχολείο</th><th>Αίτημα</th><th>Σχόλιο Δ/νσης</th><th>Διεκπεραίωση</th><th>Ημ/νία υποβολής</th></tr></thead><tbody>";
        for( $i = 0; $i < count( $results->data ); $i++ ){
            echo "<tr>";
            echo "<td>".$results->data[$i]['id']."</td>";
            echo "<td><a href='school_status.php?org=".$results->data[$i]['school']."#requests' target='_blank'>".$results->data[$i]['school_name']."</a></td>";
            echo "<td>".nl2br($results->data[$i]['request'])."</td>";
            echo "<td>".nl2br($results->data[$i]['comment'])."</td>";
            echo "<td>";
            echo $results->data[$i]['done'] ? 'Ναι' : 'Όχι';
            echo "</td>";
            echo "<td>".$results->data[$i]['submitted']."</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "</center>";
        echo "</div>";
        echo "<div style=\"text-align: center; margin: 20px 0;\">";
        echo $Paginator->createLinks( $links, 'pagination');
        echo "</div>";
    } else {
        echo "<div class=\"no-results\">";
        echo "<h3>Δε βρέθηκαν αιτήματα</h3>";
        echo "</div>";
    }
    ?>
    
    <div class="button-container">
        <INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
    </div>
</div>

</body></html>

