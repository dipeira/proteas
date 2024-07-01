<?php
    header('Content-type: text/html; charset=utf-8'); 
    //require_once "../include/functions.php";
?>
<html>
  <head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type="text/javascript">    
    $(document).ready(function() { 
        $("#mytbl").tablesorter({widgets: ['zebra']}); 
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

    $op = " AND";
if (!$_POST['mon_anapl']) {
    $query = "SELECT * from adeia WHERE";
} else {
    $query = "SELECT * from adeia_ekt WHERE";
}
if (!$_POST['date_from']){
    $query .= " start >= '".$_POST['hm_from']."'";
    $query .= " AND start <= '".$_POST['hm_to']."'";
} else {
    $query .= " finish >= '".$_POST['hm_from']."'";
    $query .= " AND finish <= '".$_POST['hm_to']."'";
}
        
if (strlen($_POST['type'])>0) {
    if ($_POST['type'] != 0) {
        $query .= $op;
        $query .= " type like '".$_POST['type']."'";
    }
}
        
        $i=0;
        //    echo $query; // for debugging...
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);

if ($num==0) {
    echo "<BR><h4>Κανένα αποτέλεσμα...</h4>";
} else
{
    //echo "<p>Πλήθος εγγραφών που βρέθηκαν: $num<p>";
    $num1=$num;
    $num2=$num;
    $synolo_ola = $synolo_ews = 0;
    echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
    echo "<body>";
    echo "<center>";
    ob_start();
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
    echo "<thead><tr>";
    echo "<th>ΑΦΜ</th>";
    echo "<th>Επώνυμο, Όνομα</th>";
    echo "<th>Ειδικότητα</th>";    
    echo "<th>Είδος</th>";
      echo "<th>Έναρξη</th>";
      echo "<th>Λήξη</th>";
      echo "<th>Ημ.αδ<br><small>Ημ.έως</small></th>";
      echo "<th>Αρ.Πρωτοκόλλου</th>";
      echo "<th>Αρ.Απόφασης</th>";
      echo !$_POST['mon_anapl'] ? '<th>Σχ.Οργανικής</th>' : '';
      echo "<th>Σχ.Υπηρέτησης</th>";
    echo "</th>\n";
                    
    echo "</tr></thead>\n<tbody>";
    while ($i < $num)
    {                      
        $id = mysqli_result($result, $i, "id");
        $emp_id = mysqli_result($result, $i, "emp_id");
                
        if (!$_POST['mon_anapl']) {
            $query0 = "select e.name,surname,afm,s.name as schname,klados,sx_yphrethshs from employee e JOIN school s ON e.sx_organikhs = s.id where e.id=$emp_id";
        } else {
            $query0 = $_POST['mon_anapl'] == 1 ? 
                "select name,surname,afm,klados,sx_yphrethshs from ektaktoi where id=$emp_id" :
                "select name,surname,afm,klados,sx_yphrethshs from ektaktoi_old where id=$emp_id" ;
        }
        $result0 = mysqli_query($mysqlconnection, $query0);
        $test = mysqli_num_rows($result0);
        //Skip deleted employees
        if ($test == 0) {
            $del++;
            $i++;
            continue;
        }
        else
        {
            $name = mysqli_result($result0, 0, "name");
            $surname = mysqli_result($result0, 0, "surname");
            $afm = mysqli_result($result0, 0, "afm");
            $start = mysqli_result($result, $i, "start");
            $finish = mysqli_result($result, $i, "finish");
            $days = mysqli_result($result, $i, "days");
            $start = date("d-m-Y", strtotime($start));
            $finish = date("d-m-Y", strtotime($finish));
            
            $klados_id = mysqli_result($result0, 0, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            $sch_id = mysqli_result($result0, 0, "sx_yphrethshs");
            $school = getSchool($sch_id, $mysqlconnection);

            $organ = !$_POST['mon_anapl'] ? mysqli_result($result0, 0, 'schname') : '';
            // add days
            $synolo_ola += $days;
            $days_to_end = 0;
            if (strtotime($finish) > strtotime($_POST['hm_to'])) {
                $date1=date_create($start);
                $date2=date_create($_POST['hm_to']);
                $diff=date_diff($date1, $date2);
                $days_to_end = $diff->format("%a");
            } else {
                $days_to_end = $days;
            }
            $synolo_ews += $days_to_end;
                    
            $ar_prot = mysqli_result($result, $i, "prot");
            $hm_prot = mysqli_result($result, $i, "hm_prot");
            $apof = mysqli_result($result, $i, "prot_apof");
            $hm_apof = mysqli_result($result, $i, "hm_apof");
            if ($apof>0) {
                $apof_all = $apof."/".date("d-m-Y", strtotime($hm_apof));
            } else {
                $apof_all = "";
            }

            $type = mysqli_result($result, $i, "type");
            $query1 = "select type from adeia_type where id=$type";
            $result1 = mysqli_query($mysqlconnection, $query1);
            $typewrd = mysqli_result($result1, 0, "type");
                    
            $i++;
                                                                            
            echo "<tr><td>$afm</td><td>";
            if (!$_POST['mon_anapl']) {
                $tmpl = "adeia";
                $tmpl1 = "employee";
            }
            else{
                $tmpl = "ekt_adeia";
                $tmpl1 = "ektaktoi";
            }
            echo "<a href=\"".$tmpl1.".php?id=$emp_id&op=view\">$surname, $name</a></td><td>$klados</td><td><a href=\"".$tmpl.".php?adeia=$id&op=view\">$typewrd</a></td>
                    <td>$start</td><td>$finish</td><td>$days <small><i>($days_to_end)</i></small></td><td>$ar_prot/".date("d-m-Y",  strtotime($hm_prot))."</td><td>$apof_all\n";
            echo !$_POST['mon_anapl'] ? "<td>$organ</td>" : '';
            echo "<td>$school</td>";
            echo "</tr>";
        }
    }
    echo "</tbody></table>";

    $page = ob_get_contents(); 
    ob_end_flush();
            
    $num -= $del;
    $qry = str_replace("'", "", $query);
    echo "<span title='$qry'><p>Πλήθος εγγραφών που βρέθηκαν: <strong>$num</strong><p></span>";
    echo "<p>Σύνολο ημερών αδειών: <strong>$synolo_ola</strong><p>";
    echo "<p>Σύνολο ημερών απουσίας έως ".date("d-m-Y", strtotime($_POST['hm_to'])).": <strong>$synolo_ews</strong><p>";
    echo "<form action='../tools/2excel.php' method='post'>";
    echo "<input type='hidden' name = 'data' value='$page'>";
    echo "<BUTTON TYPE='submit'><IMG SRC='../images/excel.png' ALIGN='absmiddle'>Εξαγωγή στο excel</BUTTON>";
    //ob_end_clean();
}
        echo "</center>";
        echo "</body>";
        echo "</html>";
                
            
?>
