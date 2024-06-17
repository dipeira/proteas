<?php
  header('Content-type: text/html; charset=utf-8');
  require_once "../config.php";
  require_once "../include/functions.php";
  // Removed unnecessary library tc_calendar.php

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
    <title>Μεταπτυχιακοί τίτλοι</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="../js/common.js"></script>
    <script type="text/javascript">
        $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
        });         
    </script>
  </head>
  <body> 
    <?php require '../etc/menu.php'; ?>
    <center>
        <?php
      
        function read_postgrad($afm, $mysqlconnection)
        {
            $query = "SELECT * from postgrad where afm=".$afm;
            //echo $query;
            $result = mysqli_query($mysqlconnection, $query);
            $rec['afm'] = mysqli_result($result, 0, "afm");
            $rec['category'] = mysqli_result($result, 0, "category");
            $rec['titlos'] = mysqli_result($result, 0, "titlos");
            $rec['idryma'] = mysqli_result($result, 0, "idryma");
            $rec['anagnwrish'] = mysqli_result($result, 0, "anagnwrish");
            $rec['updated'] = mysqli_result($result, 0, "updated");
            $rec['anagnwrish_date'] = mysqli_result($result, 0, "anagnwrish_date");
            $rec['gnhsiothta'] = mysqli_result($result, 0, "gnhsiothta");
            $rec['prot_gnhsiothta'] = mysqli_result($result, 0, "prot_gnhsiothta");
            return $rec;
        }
      
        if ($_GET['op']=="list") {
                $i = 0;
                $query = "SELECT * from postgrad";
                //echo $query;
                $result = mysqli_query($mysqlconnection, $query);
                $num=mysqli_num_rows($result);
                if (!$num) {
                    echo "<br><br><big>Δε βρέθηκαν εγγραφές</big>";
                    echo "<br><span title=\"Προσθήκη εγγραφής\"><a href=\"postgrad.php?op=add\"><big>Προσθήκη εγγραφής</big><img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/></a></span>";
                }
                else
                {
                echo "<br>";
                echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border='1'>";    
                echo "<thead><tr>";
                echo "<th>ΑΦΜ</th><th>Κατηγορία</th><th>Τίτλος</th><th>Ίδρυμα</th><th>Αναγνωριστικό</th><th>Ημερομηνία Ενημέρωσης</th>";
                echo "<th>Ημερομηνία Αναγνώρισης</th><th>Γνήσιοτητα</th><th>Πρωτ. Γνησιότητας</th>";
                echo "</tr></thead>";
                echo "<tbody>";
                while ($i<$num)
                {
                    $id = mysqli_result($result, $i, "id");
                    $afm = mysqli_result($result, $i, "afm");
                    $category = mysqli_result($result, $i, "category");
                    $titlos = mysqli_result($result, $i, "titlos");
                    $idryma = mysqli_result($result, $i, "idryma");
                    $anagnwrish = mysqli_result($result, $i, "anagnwrish");
                    $updated = mysqli_result($result, $i, "updated");
                    $anagnwrish_date = mysqli_result($result, $i, "anagnwrish_date");
                    $gnhsiothta = mysqli_result($result, $i, "gnhsiothta");
                    $prot_gnhsiothta = mysqli_result($result, $i, "prot_gnhsiothta");
                    
                    // Display data in table rows
                    echo "<tr>";
                    echo "<td>".$afm."</td>";
                    echo "<td>".$category."</td>";
                    echo "<td>".$titlos."</td>";
                    echo "<td>".$idryma."</td>";
                    echo "<td>".$anagnwrish."</td>";
                    echo "<td>".$updated."</td>";
                    echo "<td>".$anagnwrish_date."</td>";
                    echo "<td>".($gnhsiothta == 1 ? 'Ναι' : 'Όχι')."</td>"; // Convert boolean value to text
                    echo "<td>".$prot_gnhsiothta."</td>";
                    echo "</tr>";
                    
                    $i++;
                }

                echo "</tbody>";
                // if ($usrlvl < 2) // Assuming this restricts adding entries based on user level
                    echo "<tr><td colspan=10><span title=\"Προσθήκη εγγραφής\"><a href=\"ekdromi.php?op=add\">Προσθήκη εγγραφής<img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/></a></span>";        
                echo "</table>";
            }
                echo "<br><br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        }
        elseif ($_GET['op']=="view") {
                $postgrad = read_postgrad($_GET['id'], $mysqlconnection);    
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td>ΑΦΜ</td><td>".$postgrad['afm']."</td></tr>";
                echo "<tr><td>Κατηγορία</td><td>".$postgrad['category']."</td></tr>";
                echo "<tr><td>Τίτλος</td><td>".$postgrad['titlos']."</td></tr>";
                echo "<tr><td>Ίδρυμα</td><td>".$postgrad['idryma']."</td></tr>";
                echo "<tr><td>Αναγνωριστικό</td><td>".$postgrad['anagnwrish']."</td></tr>";
                echo "<tr><td>Ημερομηνία Ενημέρωσης</td><td>".$postgrad['updated']."</td></tr>";
                echo "<tr><td>Ημερομηνία Αναγνώρισης</td><td>".$postgrad['anagnwrish_date']."</td></tr>";
                echo "<tr><td>Γνήσιοτητα</td><td>".($postgrad['gnhsiothta'] == 1 ? 'Ναι' : 'Όχι')."</td></tr>";
                echo "<tr><td>Πρωτ. Γνησιότητας</td><td>".$postgrad['prot_gnhsiothta']."</td></tr>";
            echo "	</table>";
                echo "<br><br><INPUT TYPE='button' VALUE='Λίστα εγγραφών' onClick=\"parent.location='ekdromi.php?op=list'\">";
                echo "<br><br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        }
        elseif ($_GET['op']=="edit") {
                $postgrad = read_postgrad($_GET['afm'], $mysqlconnection);
                echo "<form id='update_ekdr' name='update' action='ekdromi.php' method='POST'>";
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td>ΑΦΜ</td><td><input type='text' name='afm' value=".$postgrad['afm'].
                $postgrad = read_postgrad($_GET['afm'], $mysqlconnection);
                echo "<form id='update_ekdr' name='update' action='ekdromi.php' method='POST'>";
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td>ΑΦΜ</td><td><input type='text' name='afm' value=".$postgrad['afm']." required></td></tr>";
                echo "<tr><td>Κατηγορία</td><td><input type='text' name='category' value=".$postgrad['category']." required></td></tr>";
                echo "<tr><td>Τίτλος</td><td><input type='text' name='titlos' value=".$postgrad['titlos']." required></td></tr>";
                echo "<tr><td>Ίδρυμα</td><td><input type='text' name='idryma' value=".$postgrad['idryma']." required></td></tr>";
                echo "<tr><td>Αναγνωριστικό</td><td><input type='text' name='anagnwrish' value=".$postgrad['anagnwrish']." required></td></tr>";
                // Assuming 'updated' is a system generated field, not editable by user
                // echo "<tr><td>Ημερομηνία Ενημέρωσης</td><td><input type='text' name='updated' value=".$postgrad['updated']." required></td></tr>";
                echo "<tr><td>Ημερομηνία Αναγνώρισης</td><td><input type='date' name='anagnwrish_date' value=".$postgrad['anagnwrish_date']."></td></tr>"; // Assuming 'date' input type is appropriate
                echo "<tr><td>Γνήσιοτητα</td><td><select name='gnhsiothta'><option value='0'>Όχι</option><option value='1' ".($postgrad['gnhsiothta'] == 1 ? 'selected' : '').">Ναι</option></select></td></tr>";
                echo "<tr><td>Πρωτ. Γνησιότητας</td><td><input type='text' name='prot_gnhsiothta' value=".$postgrad['prot_gnhsiothta']." required></td></tr>";
            echo "	</table>";
                // update: update=2
                echo "<input type='hidden' name = 'update' value='1'>";
                echo "<input type='hidden' name = 'afm' value=".$_GET['afm'].">";
                echo "<input type='submit' value='Επεξεργασία'>";
                echo "</form>";
                echo "<INPUT TYPE='button' VALUE='Λίστα εγγραφών' onClick=\"parent.location='ekdromi.php?op=list'\">";
                echo "<br><br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        }
        elseif ($_GET['op']=="add") {
                echo "<form id='add_ekdr' name='add' action='ekdromi.php' method='POST'>";
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td>ΑΦΜ</td><td><input type='text' name='afm' required></td></tr>";
                echo "<tr><td>Κατηγορία</td><td><input type='text' name='category' required></td></tr>";
                echo "<tr><td>Τίτλος</td><td><input type='text' name='titlos' required></td></tr>";
                echo "<tr><td>Ίδρυμα</td><td><input type='text' name='idryma' required></td></tr>";
                echo "<tr><td>Αναγνωριστικό</td><td><input type='text' name='anagnwrish' required></td></tr>";
                // Assuming 'updated' is a system generated field, not set manually
                // echo "<tr><td>Ημερομηνία Ενημέρωσης</td><td><input type='text' name='updated' required></td></tr>";
                echo "<tr><td>Ημερομηνία Αναγνώρισης</td><td><input type='date' name='anagnwrish_date'></td></tr>"; // Assuming 'date' input type is appropriate
                echo "<tr><td>Γνήσιοτητα</td><td><select name='gnhsiothta'><option value='0'>Όχι</option><option value='1'>Ναι</option></select></td></tr>";
                echo "<tr><td>Πρωτ. Γνησιότητας</td><td><input type='text' name='prot'></td></tr>";
                echo "</table>";
                // Add: update=1
                echo "<input type='hidden' name = 'update' value='0'>"; 
                echo "<input type='submit' value='Προσθήκη'>";
                echo "</form>";
                echo "<INPUT TYPE='button' VALUE='Λίστα εγγραφών' onClick=\"parent.location='ekdromi.php?op=list'\">";
                echo "<br><br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        }
        else { // Implicitly handles cases where 'op' is not set or invalid
                echo "Επιλογή δεν είναι διαθέσιμη";
        }
        
        // Assuming the following logic is for processing form submissions
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $update = $_POST['update'];
            
            if ($update == 1) { // Edit existing record
                $id = $_POST['id'];
                $afm = $_POST['afm'];
                $category = $_POST['category'];
                $titlos = $_POST['titlos'];
                $idryma = $_POST['idryma'];
                $anagnwrish = $_POST['anagnwrish'];
                $anagnwrish_date = $_POST['anagnwrish_date'];
                $gnhsiothta = $_POST['gnhsiothta'];
                $prot_gnhsiothta = $_POST['prot_gnhsiothta'];
                
                $query = "UPDATE postgrad SET afm='$afm', category='$category', titlos='$titlos', idryma='$idryma', anagnwrish='$anagnwrish', anagnwrish_date='$anagnwrish_date', gnhsiothta='$gnhsiothta', prot_gnhsiothta='$prot_gnhsiothta' WHERE id='$id'";
                //echo $query;
                mysqli_query($mysqlconnection, $query);
                echo "<br><br>Εγγραφή ενημερώθηκε";
            } elseif ($update == 0) { // Add new record
                $afm = $_POST['afm'];
                $category = $_POST['category'];
                $titlos = $_POST['titlos'];
                $idryma = $_POST['idryma'];
                $anagnwrish = $_POST['anagnwrish'];
                $anagnwrish_date = $_POST['anagnwrish_date'];
                $gnhsiothta = $_POST['gnhsiothta'];
                $prot_gnhsiothta = $_POST['prot_gnhsiothta'];
                
                $query = "INSERT INTO postgrad (afm, category, titlos, idryma, anagnwrish, anagnwrish_date, gnhsiothta, prot_gnhsiothta) VALUES ('$afm','$category','$titlos','$idryma','$anagnwrish','$anagnwrish_date','$gnhsiothta','$prot_gnhsiothta')";
                mysqli_query($mysqlconnection, $query);
                echo "<br><br>Εγγραφή προστέθηκε";
            }
        }
        
        mysqli_close($mysqlconnection);
        ?>
    </center>
  </body>
</html>
                    
