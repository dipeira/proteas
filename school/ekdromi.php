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
    <title>Εκδρομές σχολείου</title>
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
      
        function taksi_switch($t)
        {
            switch ($t){
            case 1:
                return "A";
                        break;
            case 2:
                return "Β";
                        break;
            case 3:
                return "Γ";
                        break;
            case 4:
                return "Δ";
                        break;
            case 5:
                return "Ε";
                        break;
            case 6:
                return "ΣΤ";
                        break;
            }
        }
      
        if ($_GET['op']=="list") {
                $i = 0;
                $query = "SELECT * from ekdromi where sch=".$_GET['sch'];
                //echo $query;
                $school = getSchool($_GET['sch'], $mysqlconnection);
                echo "<h2>Λίστα εκδρομών: $school</h2>";
            $result = mysqli_query($mysqlconnection, $query);
            $num=mysqli_num_rows($result);
            if (!$num) {
                    echo "<br><br><big>Δε βρέθηκαν εκδρομές</big>";
                    $sch = $_GET['sch'];
                    echo "<br><span title=\"Προσθήκη εκδρομής\"><a href=\"ekdromi.php?sch=$sch&op=add\"><big>Προσθήκη Εκδρομής</big><img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/></a></span>";
            }
            else
                {
                echo "<br>";
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border='1'>";    
                echo "<thead><tr>";
                echo "<th>Αρ.Πρωτ.</th><th>Τάξη</th><th>Τμήμα</th><th>Προορισμός</th><th>Ημ/νία</th><th>Σχόλια</th>";
                echo "</tr></thead>";
                echo "<tbody>";
                while ($i<$num)
                {
                    $id = mysqli_result($result, $i, "id");
                    $sch = mysqli_result($result, $i, "sch");
                    $taksi = mysqli_result($result, $i, "taksi");
                    $tmima = mysqli_result($result, $i, "tmima");
                    $prot = mysqli_result($result, $i, "prot");
                    $date = mysqli_result($result, $i, "date");
                    $proorismos = mysqli_result($result, $i, "proorismos");
                    $comm = mysqli_result($result, $i, "comments");
                    $comments = substr($comm, 0, 30);
                    echo "<tr><td><a href='ekdromi.php?id=$id&op=view'>$prot</a><span title=\"Διαγραφή\"><a href=\"javascript:confirmDelete('ekdromi.php?id=$id&sch=$sch&op=delete')\"><img style=\"border: 0pt none;\" src=\"../images/delete_action.png\"/></a></span><span title=\"Επεξεργασία\"><a href=\"ekdromi.php?id=$id&sch=$sch&op=edit\"><img style=\"border: 0pt none;\" src=\"../images/edit_action.png\"/></a></span></td><td>".taksi_switch($taksi)."</td><td>$tmima</td><td>$proorismos</td><td>".date('d-m-Y', strtotime($date))."</td><td>$comments</td></tr>";
                    $i++;
                }

                echo "</tbody>";
                //if ($usrlvl < 2)
                    echo "<tr><td colspan=8><span title=\"Προσθήκη Εκδρομής\"><a href=\"ekdromi.php?sch=$sch&op=add\">Προσθήκη Εκδρομής<img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/></a></span>";        
                echo "</table>";
            }
                echo "<br><br><INPUT TYPE='button' VALUE='Καρτέλα Σχολείου' onClick=\"parent.location='school_status.php?org=$sch'\">";
                echo "<br><br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        }
        elseif ($_GET['op']=="view") {
                $ekdr = read_ekdromi($_GET['id'], $mysqlconnection);    
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td>Αρ.Πρωτ.</td><td>".$ekdr['prot']."</td></tr>";
                $school = getSchool($ekdr['sch'], $mysqlconnection);
                echo "<tr><td>Σχολείο</td><td>$school</td></tr>";
                echo "<tr><td>Τάξη</td><td>".taksi_switch($ekdr['taksi'])."</td></tr>";
                echo "<tr><td>Τμήμα</td><td>".$ekdr['tmima']."</td></tr>";
                echo "<tr><td>Προορισμός</td><td>".$ekdr['proorismos']."</td></tr>";
                echo "<tr><td>Ημ/νία</td><td>".$ekdr['date']."</td></tr>";
                echo "<tr><td>Σχόλια</td><td>".$ekdr['comments']."</td></tr>";
            echo "	</table>";
                echo "<br><br><INPUT TYPE='button' VALUE='Εκδρομές Σχολείου' onClick=\"parent.location='ekdromi.php?sch=".$ekdr['sch']."&op=list'\">";
                echo "<br><br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        }
        elseif ($_GET['op']=="edit") {
                $ekdr = read_ekdromi($_GET['id'], $mysqlconnection);
                echo "<form id='update_ekdr' name='update' action='ekdromi.php' method='POST'>";
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td>Αρ.Πρωτ.</td><td><input type='text' name='prot' value=".$ekdr['prot']."></td></tr>";
                //echo "<tr><td>Σχολείο</td><td><input type='text' name='sch' value=".$ekdr['sch']."></td></tr>";
                $school = getSchool($_GET['sch'], $mysqlconnection);
                echo "<input type='hidden' name='sch' value='".$_GET['sch']."' />";
                echo "<tr><td>Σχολείο</td><td><input type='text' name='tmp' value='$school' disabled size='35' /></td></tr>";
                echo "<tr><td>Τάξη</td><td>";
                taksiCmb1($ekdr['taksi']);
                echo "</td></tr>";
                echo "<tr><td>Τμήμα</td><td><input type='text' name='tmima' value=".$ekdr['tmima']."></td></tr>";
                echo "<tr><td>Προορισμός</td><td><input type='text' name='proorismos' value=".$ekdr['proorismos']." size='35'></td></tr>";
                $date = $ekdr['date'];
                echo "<tr><td>Ημ/νία</td><td>";
            $myCalendar = new tc_calendar("date", true);
            $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
            $myCalendar->setDate(date("d"), date("m"), date("Y"));
            $myCalendar->setDate(date('d', strtotime($date)), date('m', strtotime($date)), date('Y', strtotime($date)));
            $myCalendar->setPath("../tools/calendar/");
            $myCalendar->dateAllow("2011-01-01", "2030-12-31");
            $myCalendar->setAlignment("left", "bottom");
            $myCalendar->disabledDay("sun,sat");
            $myCalendar->writeScript();
            echo "</td></tr>";
                echo "<tr><td>Σχόλια</td><td><input type='text' name='comments' value=".$ekdr['comments']." size='35'></td></tr>";
            echo "</table>";
                // update: update=2
                echo "<input type='hidden' name = 'update' value='1'>";
                echo "<input type='hidden' name = 'id' value=".$_GET['id'].">";
                echo "<input type='submit' value='Επεξεργασία'>";
                echo "</form>";
                echo "<INPUT TYPE='button' VALUE='Εκδρομές Σχολείου' onClick=\"parent.location='ekdromi.php?sch=".$ekdr['sch']."&op=list'\">";
                echo "<br><br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        }
        elseif ($_GET['op']=="add") {
                echo "<form id='add_ekdr' name='add' action='ekdromi.php' method='POST'>";
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td>Αρ.Πρωτ.</td><td><input type='text' name='prot' /></td></tr>";
                $school = getSchool($_GET['sch'], $mysqlconnection);
                echo "<input type='hidden' name='sch' value='".$_GET['sch']."' />";
                echo "<tr><td>Σχολείο</td><td><input type='text' name='tmp' value='$school' disabled size='35' /></td></tr>";
                //echo "<tr><td>Τάξη</td><td><input type='text' name='taksi' /></td></tr>";
                echo "<tr><td>Τάξη</td><td>";
                taksiCmb();
                echo "</td></tr>";
                echo "<tr><td>Τμήμα</td><td><input type='text' name='tmima' /></td></tr>";
                echo "<tr><td>Προορισμός</td><td><input type='text' name='proorismos' size='35' /></td></tr>";
                //echo "<tr><td>Ημ/νία</td><td><input type='text' name='date' /></td></tr>";
                echo "<tr><td>Ημ/νία</td><td>";
            $myCalendar = new tc_calendar("date", true);
            $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
            $myCalendar->setDate(date("d"), date("m"), date("Y"));
            $myCalendar->setPath("../tools/calendar/");
            $myCalendar->dateAllow("2011-01-01", "2030-12-31");
            $myCalendar->setAlignment("left", "bottom");
            $myCalendar->disabledDay("sun,sat");
            $myCalendar->writeScript();
            echo "</td></tr>";
                echo "<tr><td>Σχόλια</td><td><input type='text' name='comments' size='35' /></td></tr>";
            echo "</table>";
                // add: update=2
                echo "<input type='hidden' name = 'update' value='2'>";
                echo "<input type='submit' value='Προσθήκη'>";
                echo "</form>";
                echo "<br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
                echo "&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Εκδρομές Σχολείου' onClick=\"parent.location='ekdromi.php?sch=".$_GET['sch']."&op=list'\">";
        }
        elseif ($_GET['op']=="delete") {
                $query = "DELETE from ekdromi where id=".$_GET['id'];
                //echo $query;
            $result = mysqli_query($mysqlconnection, $query);
            if ($result) {
                echo "Η εγγραφή με κωδικό ".$_GET['id']." διαγράφηκε με επιτυχία.";
            } else {
                        echo "Η διαγραφή απέτυχε...";
            }
            echo "<INPUT TYPE='button' VALUE='Επιστροφή στις εκδρομές' onClick=\"parent.location='ekdromi.php?sch=".$_GET['sch']."&op=list'\">";
                echo "<meta http-equiv=\"refresh\" content=\"2; URL=ekdromi.php?sch=".$_GET['sch']."&op=list\">";
        }
        
        // if POST...
        if (isset($_POST['update'])) {
            $id = $_POST['id'];
            $sch = $_POST['sch'];
            $taksi = $_POST['taksi'];
            $tmima = $_POST['tmima'];
            $proorismos = $_POST['proorismos'];
            $date = $_POST['date'];
            $comments = $_POST['comments'];
            $prot = $_POST['prot'];
            // if update
            if ($_POST['update'] == 1) {
                $query0 = "UPDATE ekdromi SET sch='$sch', taksi='$taksi', tmima='$tmima', proorismos='$proorismos', date='$date', comments='$comments', prot='$prot', sxol_etos='$sxol_etos'";
                $query1 = " WHERE id='$id'";
                $query = $query0.$query1;
            }
            // if add
            else
            {
                $query0 = "INSERT INTO ekdromi (sch, taksi, tmima, proorismos, date, comments, prot, sxol_etos)";
                $query1 = " VALUES ('$sch', '$taksi', '$tmima', '$proorismos', '$date', '$comments', $prot, $sxol_etos)";
                $query = $query0.$query1;
            }
            // for debugging...
            //echo "<br>".$query;
            mysqli_query($mysqlconnection, $query);
            echo "<INPUT TYPE='button' VALUE='Επιστροφή στις εκδρομές' onClick=\"parent.location='ekdromi.php?sch=".$sch."&op=list'\">";
            echo "<meta http-equiv=\"refresh\" content=\"2; URL=ekdromi.php?sch=".$sch."&op=list\">";
        }
        
        echo "</body>";
        echo "</html>";    


        mysqli_close($mysqlconnection);
        ?>
