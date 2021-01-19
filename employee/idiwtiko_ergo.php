<?php
  header('Content-type: text/html; charset=utf-8'); 
  require_once"../config.php";
  require_once"../include/functions.php";
  require '../tools/calendar/tc_calendar.php';
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
  
  session_start();
  $usrlvl = $_SESSION['userlevel'];
    
?>
<html>
  <head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Ιδιωτικό έργο</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="../js/common.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
        });
    </script>
    <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
  </head>
  <body> 
    <?php require '../etc/menu.php'; ?>
    <center>
        <?php
      
        function read_ekdromi($id, $mysqlconnection)
        {
            $query = "SELECT * from ekdromi where id=".$id;
            //echo $query;
            $result = mysqli_query($mysqlconnection, $query);
            $rec['sch'] = mysqli_result($result, 0, "sch");
            $rec['taksi'] = mysqli_result($result, 0, "taksi");
            $rec['tmima'] = mysqli_result($result, 0, "tmima");
            $rec['prot'] = mysqli_result($result, 0, "prot");
            $rec['date'] = mysqli_result($result, 0, "date");
            $proo = mysqli_result($result, 0, "proorismos");
            $rec['proorismos'] = str_replace(" ", "&nbsp;", $proo);
            $comm = mysqli_result($result, 0, "comments");
            $rec['comments'] = str_replace(" ", "&nbsp;", $comm);
            return $rec;
        }
      
        
        if ($_GET['op']=="edit") {
            $query = "SELECT * from idiwtiko where id=".$id;
            $result = mysqli_query($mysqlconnection, $query);
            $row = mysqli_fetch_assoc($result);

            $emp_table = $_GET['type'] == 'Μόνιμος' ? 'employee' : 'ektaktoi';
            $emp_query = "select name, surname from $emp_table where id = ".$_GET['emp_id'];
            $result = mysqli_query($mysqlconnection, $emp_query);
            // if employee not found
            if (!mysqli_num_rows($result)){
                echo "<h3>ΣΦΑΛΜΑ: Δε βρέθηκε ο υπάλληλος</h3>";
                echo "<br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
                die();
            }
            $row = mysqli_fetch_assoc($result);
            echo "<h2>Προσθήκη ιδωτικού έργου</h2>";
            echo "<h4>Ονοματεπώνυμο υπαλλήλου: ".$row['surname']. ' ' . $row['name']."</h4>";
            echo "<form id='add_idiwtiko' name='add' action='idiwtiko_ergo.php' method='POST'>";
                echo "<table class=\"imagetable\" border='1'>";
                    echo "<tr><td>Τύπος έργου</td><td><select name='type'><option value=''>Επιλέξτε τύπο</option><option value='Ιδιωτικό'>Ιδιωτικό</option><option value='Δημόσιο'>Δημόσιο</option></select></td></tr>";
                    echo "<tr><td>Αρ.Πρωτ.</td><td><input type='text' name='prot_no' /></td></tr>";
                    echo "<tr><td>Ημ/νία πρωτοκόλλου</td><td>";
                    $myCalendar = new tc_calendar("prot_date", true);
                    $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
                    $myCalendar->setDate(date("d"), date("m"), date("Y"));
                    $myCalendar->setPath("../tools/calendar/");
                    $myCalendar->dateAllow("2019-01-01", "2040-12-31");
                    $myCalendar->setAlignment("left", "bottom");
                    $myCalendar->disabledDay("sun,sat");
                    $myCalendar->writeScript();
                    echo "</td></tr>";
                    echo "<tr><td>Πράξη</td><td><input type='text' name='praxi' /></td></tr>";
                    echo "<tr><td>Α.Δ.Α.</td><td><input type='text' name='ada' /></td></tr>";
                    echo "<input type='hidden' name='emp_type' value='".$_GET['type']."' />";
                    echo "<input type='hidden' name='emp_id' value='".$_GET['id']."' />";
                    echo "<input type='hidden' name = 'update' value='2'>";
                echo "</table>";
                echo "<input type='submit' value='Προσθήκη'>";
            echo "</form>";
            echo "<br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
            echo "&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Σελίδα υπαλλήλου' onClick=\"parent.location='$emp_table.php?id=".$_GET['id']."&op=view'\">";

            //     echo "<form id='update_ekdr' name='update' action='ekdromi.php' method='POST'>";
            //     echo "<table class=\"imagetable\" border='1'>";
            //     echo "<tr><td>Αρ.Πρωτ.</td><td><input type='text' name='prot' value=".$ekdr['prot']."></td></tr>";
            //     //echo "<tr><td>Σχολείο</td><td><input type='text' name='sch' value=".$ekdr['sch']."></td></tr>";
            //     $school = getSchool($_GET['sch'], $mysqlconnection);
            //     echo "<input type='hidden' name='sch' value='".$_GET['sch']."' />";
            //     echo "<tr><td>Σχολείο</td><td><input type='text' name='tmp' value='$school' disabled size='35' /></td></tr>";
            //     echo "<tr><td>Τάξη</td><td>";
            //     taksiCmb1($ekdr['taksi']);
            //     echo "</td></tr>";
            //     echo "<tr><td>Τμήμα</td><td><input type='text' name='tmima' value=".$ekdr['tmima']."></td></tr>";
            //     echo "<tr><td>Προορισμός</td><td><input type='text' name='proorismos' value=".$ekdr['proorismos']." size='35'></td></tr>";
            //     $date = $ekdr['date'];
            //     echo "<tr><td>Ημ/νία</td><td>";
            // $myCalendar = new tc_calendar("date", true);
            // $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
            // $myCalendar->setDate(date("d"), date("m"), date("Y"));
            // $myCalendar->setDate(date('d', strtotime($date)), date('m', strtotime($date)), date('Y', strtotime($date)));
            // $myCalendar->setPath("../tools/calendar/");
            // $myCalendar->dateAllow("2011-01-01", "2030-12-31");
            // $myCalendar->setAlignment("left", "bottom");
            // $myCalendar->disabledDay("sun,sat");
            // $myCalendar->writeScript();
            // echo "</td></tr>";
            //     echo "<tr><td>Σχόλια</td><td><input type='text' name='comments' value=".$ekdr['comments']." size='35'></td></tr>";
            // echo "</table>";
            //     // update: update=1
            //     echo "<input type='hidden' name = 'update' value='1'>";
            //     echo "<input type='hidden' name = 'id' value=".$_GET['id'].">";
            //     echo "<input type='submit' value='Επεξεργασία'>";
            //     echo "</form>";
            //     echo "<INPUT TYPE='button' VALUE='Εκδρομές Σχολείου' onClick=\"parent.location='ekdromi.php?sch=".$ekdr['sch']."&op=list'\">";
            //     echo "<br><br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        }
        elseif ($_GET['op']=="add") {
            $emp_table = $_GET['type'] == 'Μόνιμος' ? 'employee' : 'ektaktoi';
            $emp_query = "select name, surname from $emp_table where id = ".$_GET['id'];
            $result = mysqli_query($mysqlconnection, $emp_query);
            // if employee not found
            if (!mysqli_num_rows($result)){
                echo "<h3>ΣΦΑΛΜΑ: Δε βρέθηκε ο υπάλληλος</h3>";
                echo "<br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
                die();
            }
            $row = mysqli_fetch_assoc($result);
            echo "<h2>Προσθήκη ιδωτικού έργου</h2>";
            echo "<h4>Ονοματεπώνυμο υπαλλήλου: ".$row['surname']. ' ' . $row['name']."</h4>";
            echo "<form id='add_idiwtiko' name='add' action='idiwtiko_ergo.php' method='POST'>";
                echo "<table class=\"imagetable\" border='1'>";
                    echo "<tr><td>Τύπος έργου</td><td><select name='type'><option value=''>Επιλέξτε τύπο</option><option value='Ιδιωτικό'>Ιδιωτικό</option><option value='Δημόσιο'>Δημόσιο</option></select></td></tr>";
                    echo "<tr><td>Αρ.Πρωτ.</td><td><input type='text' name='prot_no' /></td></tr>";
                    echo "<tr><td>Ημ/νία πρωτοκόλλου</td><td>";
                    $myCalendar = new tc_calendar("prot_date", true);
                    $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
                    $myCalendar->setDate(date("d"), date("m"), date("Y"));
                    $myCalendar->setPath("../tools/calendar/");
                    $myCalendar->dateAllow("2019-01-01", "2040-12-31");
                    $myCalendar->setAlignment("left", "bottom");
                    $myCalendar->disabledDay("sun,sat");
                    $myCalendar->writeScript();
                    echo "</td></tr>";
                    echo "<tr><td>Πράξη</td><td><input type='text' name='praxi' /></td></tr>";
                    echo "<tr><td>Α.Δ.Α.</td><td><input type='text' name='ada' /></td></tr>";
                    echo "<input type='hidden' name='emp_type' value='".$_GET['type']."' />";
                    echo "<input type='hidden' name='emp_id' value='".$_GET['id']."' />";
                    echo "<input type='hidden' name = 'update' value='2'>";
                echo "</table>";
                echo "<input type='submit' value='Προσθήκη'>";
            echo "</form>";
            echo "<br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
            echo "&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Σελίδα υπαλλήλου' onClick=\"parent.location='$emp_table.php?id=".$_GET['id']."&op=view'\">";
        }
        elseif ($_GET['op']=="delete") {
            $query = "DELETE from idiwtiko where id=".$_GET['id'];
            //echo $query;
            $result = mysqli_query($mysqlconnection, $query);
            if ($result) {
                echo "<h2>Η εγγραφή με κωδικό ".$_GET['id']." διαγράφηκε με επιτυχία.</h2>";
            } else {
                echo "<h2>Η διαγραφή απέτυχε...</h2>";
            }
            echo "<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='ekdromi.php?sch=".$_GET['sch']."&op=list'\">";
            //     echo "<meta http-equiv=\"refresh\" content=\"2; URL=ekdromi.php?sch=".$_GET['sch']."&op=list\">";
        }
        
        // if POST...
        if (isset($_POST['update'])) {
            $emp_type = $_POST['emp_type'];
            $emp_id = $_POST['emp_id'];
            $type = $_POST['type'];
            $prot_no = $_POST['prot_no'];
            $prot_date = $_POST['prot_date'];
            $praxi = $_POST['praxi'];
            $ada = $_POST['ada'];
            // if update
            if ($_POST['update'] == 1) {
                $id = $_POST['id'];
                $query0 = "UPDATE idiwtiko SET emp_type='$emp_type', emp_id='$emp_id', type='$type', prot_no='$prot_no', prot_date='$prot_date', praxi='$praxi', ada='$ada', sxol_etos='$sxol_etos'";
                $query1 = " WHERE id='$id'";
                $query = $query0.$query1;
            }
            // if add
            else
            {

                $query0 = "INSERT INTO idiwtiko (emp_type, emp_id, type, prot_no, prot_date, praxi, ada, sxol_etos)";
                $query1 = " VALUES ('$emp_type', '$emp_id', '$type', '$prot_no', '$prot_date', '$praxi', '$ada', $sxol_etos)";
                $query = $query0.$query1;
            }
            // for debugging...
            echo "<br>".$query;
            mysqli_query($mysqlconnection, $query);
            echo "<INPUT TYPE='button' VALUE='Επιστροφή στις εκδρομές' onClick=\"parent.location='ekdromi.php?sch=".$sch."&op=list'\">";
            echo "<meta http-equiv=\"refresh\" content=\"2; URL=ekdromi.php?sch=".$sch."&op=list\">";
        }
        
        echo "</body>";
        echo "</html>";    


        mysqli_close($mysqlconnection);
        ?>
