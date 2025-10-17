<?php
    header('Content-type: text/html; charset=utf-8'); 
    //require_once "../include/functions.php";
?>
<html>
  <head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <!--
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    -->
    <title>Αναφορά Διευθυντών / Προϊσταμένων</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type="text/javascript" src="../js/stickytable.js"></script>
    <script type="text/javascript">    
        $(document).ready(function() { 
            $(".tablesorter").tablesorter({widgets: ['zebra']}); 
            $(".tablesorter").stickyTableHeaders();
        });  
    </script>
  </head>

<?php
    require_once"../config.php";
    require_once"../include/functions.php";
    session_start();

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

    echo "<body>";
    require '../etc/menu.php';

    $req_type = $_GET['type'] ? $_GET['type'] : 0;
    echo "<h3>Αναφορά Διευθυντών / Προϊσταμένων</h3>";
    echo "<p>Παρακαλώ επιλέξτε τύπο σχολείου:</p>";
    echo $req_type == 0 ? "<b>Δημοτικά Σχολεία</b><br>" : "<a href='report_head.php?type=0'>Δημοτικά Σχολεία</a><br>";
    echo $req_type == 3 ? "<b>Δημοτικά Σχολεία (μόνο Δ/ντές)</b><br>" : "<a href='report_head.php?type=3'>Δημοτικά Σχολεία (μόνο Δ/ντές)</a><br>";
    echo $req_type == 1 ? "<b>Νηπιαγωγεία</b><br>" : "<a href='report_head.php?type=1'>Νηπιαγωγεία</a><br>";
    echo $req_type == 2 ? '<b>Ειδικά Σχολεία</b><br>' : "<a href='report_head.php?type=2'>Ειδικά Σχολεία</a><br>";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    
    function print_table($result, $num, $mysqlconnection, $mon = true){
      $i=0;
      echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
      echo "<thead>";
      echo "<tr><th>Κωδ.</th>";
      echo "<th>Ονομασία</th>";
      echo "<th>Λειτ.</th>";
      echo "<th>Θέση</th>";
      echo "<th>Επώνυμο</th>";
      echo "<th>Όνομα</th>";
      echo "<th>Κλάδος</th>";
      echo "<th>Τηλέφωνο</th>";
      echo "<th>email</th>";
      echo "<th>ΑΦΜ</th>";
      echo $mon ? "<th>ΑΜ</th>" : '';
      echo "</tr>";
      echo "</thead>\n<tbody>\n";

      while ($i < $num)
      {        
          $sid = mysqli_result($result, $i, "sid");
          $sname = mysqli_result($result, $i, "sname");
          $code = mysqli_result($result, $i, "code");
          $leitoyrg = get_leitoyrgikothta($sid, $mysqlconnection);
          $id = mysqli_result($result, $i, "id");
          $name = mysqli_result($result, $i, "name");
          $surname = mysqli_result($result, $i, "surname");
          $email = mysqli_result($result, $i, "email");
          $thesi = $leitoyrg > 3 ? 
            mysqli_result($result, $i, "thesi") == 2 ? 'Διευθυντής/-ντρια' : 'Υποδιευθυντής/-ντρια' :
            'Πρ/νος/-η';
          $klados = getKlados(mysqli_result($result, $i, "klados"),$mysqlconnection);
          $tel = $mon ? mysqli_result($result, $i, "tel") : mysqli_result($result, $i, "stathero") . ' / ' . mysqli_result($result, $i, "kinhto");
          $afm = mysqli_result($result, $i, "afm");
          $am = $mon ? mysqli_result($result, $i, "am") : 0;

          echo "<tr>";
          echo "<td>$code</td>";
          echo "<td><a href='../school/school_status.php?org=$sid' target='_blank'>$sname</a></td>";
          echo "<td>$leitoyrg</td>";
          echo "<td>$thesi</td>";
          $link = $mon ? "../employee/employee.php?id=$id&op=view" : "../employee/ektaktoi.php?id=$id&op=view";
          echo "<td><a href=$link target='_blank'>$surname</td>";
          echo "<td>$name</td>";
          echo "<td>$klados</td>";
          echo $_SESSION['userlevel'] < 3 ? "<td>$tel</td>" : "<td></td>";
          echo "<td><a href='mailto:$email'>$email</a></td>";
          echo "<td>$afm</td>";
          echo $mon ? "<td>$am</td>" : '';
          echo "</tr>\n";
          $i++;                        
      }
      echo "</tbody></table>";
    }
    if ($req_type == 1){
      $type = 2;
      $type2 = 0;
    } else {
      $type = 1;
      $type2 = $req_type;
    }
    if ($req_type == 3) {
      $thesi = "(2)";
      $type2 = 0;
    } else {
      $thesi = "(1,2)";
    }
    $query = "SELECT s.id as sid, s.code,s.name AS sname, e.* from school s JOIN employee e ON s.id = e.sx_yphrethshs 
    WHERE s.type2 = $type2 AND s.type = $type AND e.thesi IN $thesi AND status IN (1,3,5)";
    $query2 = "SELECT s.id as sid, s.code,s.name AS sname, e.* from school s JOIN ektaktoi e ON s.id = e.sx_yphrethshs 
    WHERE s.type2 = $type2 AND s.type = $type AND e.thesi = 1 AND status IN (1,3,5)";

    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);

    echo "<center>";
    
    ob_start();
    echo "<h2>Μόνιμοι</h2>";
    print_table($result, $num, $mysqlconnection);
    
    
    
    $result = mysqli_query($mysqlconnection, $query2);
    $num = mysqli_num_rows($result);
    if ($num){
      echo "<h2>Αναπληρωτές</h2>";
      print_table($result, $num, $mysqlconnection, false);
    }

    $page = ob_get_contents(); 
    $_SESSION['page'] = $page;
    ob_end_flush();

    echo "<form action='../tools/2excel_ses.php' method='post'>";
    //echo "<input type='hidden' name = 'data' value=\"$page\"></input>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
    echo "	&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    echo "</form>";
    //ob_end_clean();


?>
</body>
</html>
                
