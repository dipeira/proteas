<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once "../include/functions.php";
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  // Demand authorization                
  require "../tools/class.login.php";
  $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
}
?>
<html>
  <head>
    <?php 
    $root_path = '../';
    $page_title = 'Εκπ/κοί που βρίσκονται σε άδεια';
    require '../etc/head.php'; 
    ?>
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>
    <script type="text/javascript">
        // Custom sorting function for date in DD-MM-YYYY format
        $.fn.dataTable.ext.type.order['date-dd-mm-yyyy-pre'] = function(date) {
            if (!date || date === '') return 0;
            var parts = date.split('-');
            if (parts.length !== 3) return 0;
            return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
        };
        
        $(document).ready(function() { 
            $("#mytbl").DataTable({
                lengthMenu: [
                    [20, 50, -1],
                    [20, 50, 'Όλοι']
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/el.json'
                },
                dom: 'Bfrtlip',
                buttons: [
                    {
                        extend: 'excel',
                        text: 'Εξαγωγή σε Excel',
                        className: 'btn-green',
                        title: 'Εκπαιδευτικοί σε άδεια'
                    }
                ],
                columnDefs: [
                    { type: 'date-dd-mm-yyyy', targets: [4] }
                ],
                order: [[4, 'desc']]
            });
        });
    </script>
  </head>
  <body> 
    <?php require '../etc/menu.php'; ?>
    <center>
        <h2>Εκπ/κοί που βρίσκονται σε άδεια</h2>
        <?php
            if ($_SESSION['userlevel'] == 3){
                echo "Σφάλμα: Δεν επιτρέπεται η πρόσβαση...";
                echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
                die();
            }

            // set timeout to 90 secs
            set_time_limit(90);
            //Σε άδεια
            //old queries:
            //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND status=3";
            //$query0 = "SELECT * from adeia WHERE emp_id='$id' AND start<'$today' AND finish>'$today'";
            $today = date("Y-m-d");
            //$query = "SELECT * FROM adeia ad JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND start<'$today' AND finish>'$today'";
            //$query = "SELECT * FROM adeia ad JOIN employee emp ON ad.emp_id = emp.id WHERE sx_organikhs='$sch' AND start<'$today' AND finish>'$today' AND status=3";
            $query = "SELECT * FROM adeia ad RIGHT JOIN employee emp ON ad.emp_id = emp.id WHERE ((start<'$today' AND finish>'$today') OR status=3) ORDER BY finish DESC";
            //echo $query;
            $result = mysqli_query($mysqlconnection, $query);
            $num = mysqli_num_rows($result);
        if ($num) {
            echo "<table id=\"mytbl\" class=\"imagetable\" border=\"2\" style=\"width:95%\">\n";
            echo "<thead><tr>";
            echo "<th style='min-width: 180px;'>Επώνυμο</th>";
            echo "<th>Όνομα</th>";
            echo "<th>Κλάδος</th>";
            echo "<th>Τύπος</th>";
            echo "<th>Ημ/νία Επιστροφής</th>";
            echo "<th>Σχόλια</th>";
            echo "</tr></thead>\n<tbody>";
            $apontes = array();
            while ($row = mysqli_fetch_array($result))
            {
                $flag = $absent = 0;

                $id = $row['emp_id'];
                $adeia_id = $row['id'];
                $type = $row['type'];
                $name = $row['name'];
                $surname = $row['surname'];
                $klados_id = $row['klados'];
                $klados = getKlados($klados_id, $mysqlconnection);
                $comments = shorten_text($row['comments']);
                $comm = $comments;
                $today = date("Y-m-d");
                $return = $row['finish'];
                $start = $row['start'];
                $status = $row['status'];
                // if return date exists, check if absent and print - else continue.

                if ($start<$today && $return>$today) {
                    $flag = $absent = 1;
                    $apontes[] = $id;
                }
                else
                {
                    //$flag=1;
                    $ret="";
                    if (!in_array($id, $apontes)) {
                            $flag = 1;
                    }
                    $apontes[] = $id;
                    $comments = "Δεν απουσιάζει.<br>Έχει δηλωθεί κατάσταση \"Σε άδεια\"<br>";
                }
                $ret = date("d-m-Y", strtotime($return));


                // echo "OK: $i - $start>$today>$return - fl:$flag<br>";
                if ($flag) {
                    $query1 = "select type from adeia_type where id=$type";
                    $result1 = mysqli_query($mysqlconnection, $query1);
                    $rs = mysqli_fetch_array($result1);
                    $typewrd = $rs['type'];
                    if ($absent && $status<>3) {
                        $comments = "<blink>Παρακαλώ αλλάξτε την κατάσταση του <br>εκπ/κού σε \"Σε Άδεια\"</blink><br>$comm";
                    }
                    //if ($absent)
                    //    continue;
                    echo "<tr>";
                    echo "<td><a href=\"employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$typewrd</td><td><a href='adeia.php?adeia=$adeia_id&op=view'>$ret</a></td><td>$comments</td>\n";
                    echo "</tr>";
                }

            }
            echo "</tbody></table>";
            echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
        }
                    

        ?>
        </center>
        </body>
        </html>
