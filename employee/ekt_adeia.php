<?php
  header('Content-type: text/html; charset=utf-8');
  require_once"../config.php";
  require_once"../include/functions.php";
  require_once"../include/functions_controls.php";
  require_once '../tools/num2word.php';

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
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <LINK href="../css/jquery-ui.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Employee</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.js"></script>
    <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src="../js/datepicker-gr.js"></script>
    <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
    <script type="text/javascript">
      // $(document).ready(function(){
      //   $("#wordfrm").validate({
      //       debug: false,
      //       rules: {
      //       //    name: "required",
      //       },
      //       messages: {
      //       //    name: "Please let us know who you are."
      //       },
      //       submitHandler: function(form) {
      //           // do other stuff for a valid form
      //           $.post('ekt_adeia_print.php', $("#wordfrm").serialize(), function(data) {
      //               $('#word').html(data);
      //           });
      //       }
      //   });
      // });

      $(document).ready(function(){
        $("#updatefrm").validate({
            debug: false,
            rules: {
            //    name: "required",
            },
            messages: {
            //    name: "Please let us know who you are."
            },
            submitHandler: function(form) {
                // do other stuff for a valid form
                $.post('ekt_update_adeia.php', $("#updatefrm").serialize(), function(data) {
                    $('#results').html(data);
                });
            }
        });
        
        // When hm_prot date is selected, copy it to date field
        $('#hm_prot').on('change', function() {
            var selectedDate = $(this).val();
            if (selectedDate) {
                $('#date').val(selectedDate);
                // Also update the hidden field if it exists
                if ($('#hm_prot_hidden').length && $('#date_hidden').length) {
                    $('#date_hidden').val($('#hm_prot_hidden').val());
                }
            }
        });
      });                
    </script>
  </head>
  <body>
    <center>
        <?php
        switch ($_GET['op']) {
          case 'add':
            echo "<h2>Προσθήκη άδειας αναπληρωτή</h2>";
            break;
          case 'view':
            echo "<h2>Προβολή άδειας αναπληρωτή</h2>";
            break;
          case 'edit':
            echo "<h2>Επεξεργασία άδειας αναπληρωτή</h2>";
            break;
        }
        if (!isset($_GET['sxol_etos'])) {
            echo "<h2>Σφάλμα: Παρακαλώ δώστε σχολικό έτος.</h2>";
            echo "<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"history.back()\">";
            die();
        }
        $usrlvl = $_SESSION['userlevel'];
        $prev_year = false;
        if (isset($_GET['sxol_etos']) && $_GET['sxol_etos'] != $sxol_etos) {
            $sxol_etos = $_GET['sxol_etos'];
            $prev_year = true;
        }
                
        if (!isset($_GET['emp'])) {
            $query = "SELECT * from adeia_ekt where id=".$_GET['adeia']." AND sxoletos=$sxol_etos";
            $result = mysqli_query($mysqlconnection, $query);

            $id = mysqli_result($result, 0, "id");
            $emp_id = mysqli_result($result, 0, "emp_id");
            $type = mysqli_result($result, 0, "type");
            $prot = mysqli_result($result, 0, "prot");
            $prot_apof = mysqli_result($result, 0, "prot_apof");
            $hm_apof = mysqli_result($result, 0, "hm_apof");
            $hm_prot = mysqli_result($result, 0, "hm_prot");
            $date = mysqli_result($result, 0, "date");
            $vev_dil = mysqli_result($result, 0, "vev_dil");
            $days = mysqli_result($result, 0, "days");
            $start = mysqli_result($result, 0, "start");
            $finish = mysqli_result($result, 0, "finish");
            $logos = mysqli_result($result, 0, "logos");
            $logos = str_replace(" ", "&nbsp;", $logos);
            $comments = mysqli_result($result, 0, "comments");
            $comments = str_replace(" ", "&nbsp;", $comments);
            $created = mysqli_result($result, 0, "created");
        }

        if ($_GET['op']=="edit") {
            if ($prev_year) {
                echo "<h2>Σφάλμα: Δεν μπορείτε να επεξεργαστείτε άδειες προηγούμενου έτους</h2>";
                echo "<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"history.back()\">";
                die();
            }
                echo "<form id='updatefrm' name='update' action='ekt_update_adeia.php' method='POST'>";
            echo "<table class=\"imagetable\" border='1'>";

            echo "<tr>";
            echo "<td>ID</td><td>$id</td>";
            echo "</tr>";
            $query1 = "select * from ektaktoi where id=$emp_id";
            $result1 = mysqli_query($mysqlconnection, $query1);
            $name = mysqli_result($result1, 0, "name");
            $surname = mysqli_result($result1, 0, "surname");
            $kl1 = mysqli_result($result1, 0, "klados");
            $q2 = "select * from klados where id=$kl1";
            $res2 = mysqli_query($mysqlconnection, $q2);
            $klados = mysqli_result($res2, 0, "perigrafh");
            echo "<tr><td>Όνομα</td><td>$name</td></tr>";
            echo "<tr><td>Επώνυμο</td><td>$surname</td></tr>";
            echo "<tr><td>Κλάδος</td><td>$klados</td></tr>";

            echo "<tr><td>type</td><td>";
            adeiaCmb($type, $mysqlconnection, 1);
            echo "</td></tr>";

            echo "<tr><td>Αρ.Πρωτοκόλου απόφασης</td><td><input type='text' name='prot_apof' value=$prot_apof /></td></tr>";
            echo "<tr><td>Ημ/νία Πρωτοκόλου απόφασης</td><td>";
            modern_datepicker("hm_apof", $hm_apof, array(
                'minDate' => '2011-01-01',
                'maxDate' => '2030-12-31',
                'disabledDays' => array('sun', 'sat')
            ));
            echo "</td></tr>";
                
            echo "<tr><td>Αρ.Πρωτοκόλου αίτησης</td><td><input type='text' name='prot' value=$prot /></td></tr>";
                
            echo "<tr><td>Ημ/νία Πρωτοκόλου αίτησης</td><td>";
            modern_datepicker("hm_prot", $hm_prot, array(
                'minDate' => '2011-01-01',
                'maxDate' => '2030-12-31',
                'disabledDays' => array('sun', 'sat')
            ));
            echo "</td></tr>";
                
            echo "<tr><td>Ημ/νία αίτησης</td><td>";
            modern_datepicker("date", $date, array(
                'minDate' => '2011-01-01',
                'maxDate' => '2030-12-31',
                'disabledDays' => array('sun', 'sat')
            ));
            echo "</td></tr>";

            echo "<tr><td>Βεβαίωση / Δήλωση</td><td>";
            echo "<select name='vev_dil'>";
              if ($vev_dil==0) {
                echo "<option value=\"0\" selected>Όχι</option>";
              } else {
                echo "<option value=\"0\">Όχι</option>";
              }
            if ($vev_dil==1) {
              echo "<option value=\"1\" selected>Βεβαίωση</option>";
            } else {
              echo "<option value=\"1\">Βεβαίωση</option>";
            }
            if ($vev_dil==2) {
              echo "<option value=\"2\" selected>Υπεύθυνη Δήλωση</option>";
            } else {
              echo "<option value=\"2\">Υπεύθυνη Δήλωση</option>";
            }
            echo "</select>";
            echo "</td></tr>";

            echo "<tr><td>Ημέρες</td><td><input type='text' name='days' value=$days /></td></tr>";
            echo "<tr><td>Ημ/νία έναρξης</td><td>";
            modern_datepicker("start", $start, array(
                'minDate' => '2011-01-01',
                'maxDate' => '2030-12-31'
            ));
            echo "</td></tr>";

                echo "<tr><td>Ημ/νία λήξης</td><td>";
            modern_datepicker("finish", $finish, array(
                'minDate' => '2011-01-01',
                'maxDate' => '2030-12-31'
            ));
            echo "</td></tr>";

            echo "<tr><td>Λόγος</td><td><input type='text' name='logos' value=$logos /></td></tr>";

            echo "<tr><td>Σχόλια</td><td><input type='text' name='comments' value=$comments /></td></tr>";

            echo "	</table>";
            echo "	<input type='hidden' name = 'id' value='$id'>";
                echo "	<input type='hidden' name = 'emp_id' value='$emp_id'>";
            echo "	<input type='submit' value='Επεξεργασία'>";
            //echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='ekt_adeia.php?adeia=$id&op=view'\">";
            echo "    <INPUT TYPE='button' VALUE='Επιστροφή' onClick='history.go(-1);return true;'>";
            echo "	</form>";
            echo "    </center>";
            echo "</body>";
            ?>
        <div id='results'>   </div>
            <?php
            echo "</html>";
        }
        elseif ($_GET['op']=="view") {
            echo "<table class=\"imagetable\" border='1'>";
            echo "<tr>";
            echo "<td>ID</td><td>$id</td>";
            echo "</tr>";
            // check if current sxoliko etos
            $query1 = $sxol_etos != getParam('sxol_etos', $mysqlconnection) ? 
            "select * from ektaktoi_old where sxoletos=$sxol_etos AND id=$emp_id" :
            "select * from ektaktoi where id=$emp_id";
            
                $result1 = mysqli_query($mysqlconnection, $query1);
            $name = mysqli_result($result1, 0, "name");
                $surname = mysqli_result($result1, 0, "surname");
                $kl1 = mysqli_result($result1, 0, "klados");
                $q2 = "select * from klados where id=$kl1";
                $res2 = mysqli_query($mysqlconnection, $q2);
                $klados = mysqli_result($res2, 0, "perigrafh");
            echo "<tr><td>Όνομα</td><td>$name</td></tr>";
            echo "<tr><td>Επώνυμο</td><td>$surname</td></tr>";
                echo "<tr><td>Κλάδος</td><td>$klados</td></tr>";

            $query1 = "select type from adeia_ekt_type where id=$type";
                $result1 = mysqli_query($mysqlconnection, $query1);
            $typewrd = mysqli_result($result1, 0, "type");
                echo "<tr><td>Τύπος</td><td>$typewrd</td></tr>";
            if (!$prot_apof) {
                $prot_apof = '';
            }
            echo "<tr><td>Αρ.Πρωτοκόλου απόφασης</td><td>$prot_apof</td></tr>";
            if (!(int)$hm_apof) {
                $hm_apof = '';
            } else {
                    $hm_apof = date('d-m-Y', strtotime($hm_apof));
            }
                echo "<tr><td>Ημ/νία Πρωτοκόλου απόφασης</td><td>$hm_apof</td></tr>";
                
            echo "<tr><td>Αρ.Πρωτοκόλου αίτησης</td><td>$prot</td></tr>";
                $hm_prot = date('d-m-Y', strtotime($hm_prot));
                echo "<tr><td>Ημ/νία Πρωτοκόλου αίτησης</td><td>$hm_prot</td></tr>";
                $date = date('d-m-Y', strtotime($date));
                echo "<tr><td>Ημ/νία αίτησης</td><td>$date</td></tr>";
            if ($type==1) {
                    echo "<tr><td>Βεβαίωση / Δήλωση</td><td>";
                    switch ($vev_dil)
                        {
                case 0:
                    echo "Όχι";
                    break;
                case 1:
                    echo "Βεβαίωση";
                    break;
                case 2:
                    echo "Υπεύθυνη Δήλωση";
                    break;
                    }
                    echo "</td></tr>";
            }
                $daysfull = convertNumber($days);
            echo "<tr><td>Ημέρες</td><td>$days ($daysfull)</td></tr>";
                $start = date('d-m-Y', strtotime($start));
            echo "<tr><td>Ημ/νία Έναρξης</td><td>$start</td></tr>";
                $finish = date('d-m-Y', strtotime($finish));
                echo "<tr><td>Ημ/νία Λήξης</td><td>$finish</td></tr>";
            echo "<tr><td>Λόγος</td><td>$logos</td></tr>";
            echo "<tr><td>Σχόλια</td><td>$comments</td></tr>";
            if ($created != 0) {
                $created= date('d-m-Y, g:i a', strtotime($created));
                echo "<tr><td colspan=2 align='right'><small>Τελευταία τροποποίηση:<br> $created</small></td></tr>";
            }

            echo "</td></tr>";
            // if ($_SESSION['adeia']) {
            //     echo "<tr><td colspan=2 align='center'>";
            //     //Form gia Bebaiwsh
            //     echo "<form id='wordfrm' name='wordfrm' action='ekt_adeia_print.php' method='POST'>";
            //     echo "<input type='hidden' name=arr[] value=$emp_id>";
            //     echo "<input type='hidden' name=arr[] value=$type>";
            //     echo "<input type='hidden' name=arr[] value=$prot>";
            //     echo "<input type='hidden' name=arr[] value=$hm_prot>";
            //     echo "<input type='hidden' name=arr[] value=$date>";
            //     echo "<input type='hidden' name=arr[] value=$vev_dil>";
            //     echo "<input type='hidden' name=arr[] value=$days>";
            //     echo "<input type='hidden' name=arr[] value=$start>";
            //     echo "<input type='hidden' name=arr[] value=$finish>";
            //     echo "<input type='hidden' name=arr[] value=$logos>";

            //     echo "<INPUT TYPE='submit' VALUE='Εκτύπωση άδειας'>";
            //     echo "</form>";
            //         echo "</td></tr>";
            // }
            echo "	</table>";

            // Find prev - next row id
            $qprev = "SELECT id FROM adeia_ekt WHERE id < $id AND emp_id = $emp_id ORDER BY id DESC LIMIT 1";
            $res1 = mysqli_query($mysqlconnection, $qprev);
            if (mysqli_num_rows($res1)) {
                $previd = mysqli_result($res1, 0, "id");
            }
            $qnext = "SELECT id FROM adeia_ekt WHERE id > $id AND emp_id = $emp_id ORDER BY id ASC LIMIT 1";
            $res1 = mysqli_query($mysqlconnection, $qnext);
            if (mysqli_num_rows($res1)) {
                $nextid = mysqli_result($res1, 0, "id");
            }

            if ($previd) {
                echo "	<INPUT TYPE='button' VALUE='<<' onClick=\"parent.location='ekt_adeia.php?id=$emp_id&adeia=$previd&op=view'\">";
            }
            if ($usrlvl < 3 && $sxol_etos != $sxoletos) {
                echo "	<INPUT TYPE='button' VALUE='Επεξεργασία' onClick=\"parent.location='ekt_adeia.php?id=$emp_id&adeia=$id&op=edit&sxol_etos=$sxol_etos'\">";
            }
                echo "	<INPUT TYPE='button' VALUE='Επιστροφή στην καρτέλα εκπ/κού' onClick=\"history.back()\">";
            if ($nextid) {
                echo "	<INPUT TYPE='button' VALUE='>>' onClick=\"parent.location='ekt_adeia.php?id=$emp_id&adeia=$nextid&op=view'\">";
            }
                echo "<br><br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
            echo "    </center>";

            echo "</body>";
            echo "</html>";
        }
        if ($_GET['op']=="delete") {
            if ($prev_year) {
                echo "<h2>Σφάλμα: Δεν μπορείτε να διαγράψετε άδειες προηγούμενου έτους</h2>";
                echo "<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"history.back()\">";
                die();
            }
            // Copies the to-be-deleted row to adeia_deleted table for backup purposes.Also adds a row to adeia_del_log for logging purposes...
            $query1 = "INSERT INTO adeia_ekt_deleted SELECT e.* FROM adeia_ekt e WHERE id =".$_GET['adeia'];
            $result1 = mysqli_query($mysqlconnection, $query1);
                $query1 = "INSERT INTO adeia_del_log (adeia_id, userid, ektaktos) VALUES (".$_GET['adeia'].", ".$_SESSION['userid'].", '1')";
            $result1 = mysqli_query($mysqlconnection, $query1);
                $query = "DELETE from adeia_ekt where id=".$_GET['adeia'];
            $result = mysqli_query($mysqlconnection, $query);
            // Copies the deleted row to employee)deleted

            if ($result) {
                echo "Η εγγραφή με κωδικό $id διαγράφηκε με επιτυχία.<br>";
            } else {
                echo "Η διαγραφή απέτυχε...";
            }
            echo "	<INPUT TYPE='button' VALUE='Επιστροφή στην καρτέλα εκπ/κού' onClick=\"parent.location='ektaktoi.php?id=$emp_id&op=view'\">";
        }
        if ($_GET['op']=="add") {
            if ($prev_year) {
                echo "<h2>Σφάλμα: Δεν μπορείτε να προσθέσετε άδειες σε προηγούμενο έτος</h2>";
                echo "<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"history.back()\">";
                die();
            }
            echo "<form id='updatefrm' name='updatefrm' action='ekt_update_adeia.php' method='POST'>";
            echo "<table class=\"imagetable\" border='1'>";
            $emp_id = $_GET['emp'];
            
            $query1 = "select * from ektaktoi where id=$emp_id";
            $result1 = mysqli_query($mysqlconnection, $query1);
            $name = mysqli_result($result1, 0, "name");
            $surname = mysqli_result($result1, 0, "surname");
            $kl1 = mysqli_result($result1, 0, "klados");
            $q2 = "select * from klados where id=$kl1";
            $res2 = mysqli_query($mysqlconnection, $q2);
            $klados = mysqli_result($res2, 0, "perigrafh");
            echo "<tr><td>Όνομα</td><td>$name</td></tr>";
            echo "<tr><td>Επώνυμο</td><td>$surname</td></tr>";
            echo "<tr><td>Κλάδος</td><td>$klados</td></tr>";

            echo "<tr><td>Τύπος</td><td>";
            adeiaCmb($type, $mysqlconnection, 1);
            echo "</td></tr>";

            echo "<tr id='vevdil'><td>Βεβαίωση / Δήλωση<br>(για αναρρωτικές)</td><td>";
            echo "<select name='vev_dil'>";
            echo "<option value=\"0\" selected>Όχι</option>";
            echo "<option value=\"1\">Βεβαίωση</option>";
            echo "<option value=\"2\">Υπεύθυνη Δήλωση</option>";
            echo "</select>";
            echo "</td></tr>";
                
            echo "<tr><td>Αρ.Πρωτοκόλου απόφασης</td><td><input type='text' name='prot_apof' /></td></tr>";
            echo "<tr><td>Ημ/νία Πρωτοκόλου απόφασης</td><td>";
            modern_datepicker("hm_apof", date('Y-m-d'), array(
                'minDate' => '2011-01-01',
                'maxDate' => '2030-12-31',
                'disabledDays' => array('sun', 'sat')
            ));
            echo "</td></tr>";

            echo "<tr><td>Αρ.Πρωτοκόλου αίτησης</td><td><input type='text' name='prot' /></td></tr>";
                
            echo "<tr><td>Ημ/νία Πρωτοκόλου αίτησης</td><td>";
            modern_datepicker("hm_prot", date('Y-m-d'), array(
                'minDate' => '2011-01-01',
                'maxDate' => '2030-12-31',
                'disabledDays' => array('sun', 'sat')
            ));
            echo "</td></tr>";
                
            echo "<tr><td>Ημ/νία αίτησης</td><td>";
            modern_datepicker("date", date('Y-m-d'), array(
                'minDate' => '2011-01-01',
                'maxDate' => '2030-12-31',
                'disabledDays' => array('sun', 'sat')
            ));
            echo "</td></tr>";

            

            echo "<tr><td>Ημέρες</td><td><input type='text' name='days' /></td></tr>";
            echo "<tr><td>Ημ/νία έναρξης</td><td>";
            modern_datepicker("start", date('Y-m-d'), array(
                'minDate' => '2011-01-01',
                'maxDate' => '2030-12-31'
            ));
            ?>                
            <script language="javascript">
                // Show/hide vevdil row based on adeia type selection
                document.addEventListener('DOMContentLoaded', function() {
                    var typeSelect = document.getElementById('type');
                    if (typeSelect) {
                        typeSelect.addEventListener('change', function() {
                            var vevdilRow = document.getElementById('vevdil');
                            if (this.value == '1') {
                                // Type 1 = αναρρωτική (sick leave)
                                vevdilRow.style.display = '';
                            } else {
                                vevdilRow.style.display = 'none';
                            }
                        });
                    }
                });
                
                function addDays2Date(){
					// Get the start date from the hidden field (Y-m-d format)
					var startDateStr = document.getElementById('start_hidden').value;
					var days = parseInt(document.updatefrm.days.value);
					
					if (!startDateStr || !days || isNaN(days)) {
						alert('Παρακαλώ συμπληρώστε την ημ/νία έναρξης και τις ημέρες');
						return;
					}
					
					// Parse the date (Y-m-d format)
					var parts = startDateStr.split('-');
					if (parts.length !== 3) {
						alert('Μη έγκυρη ημ/νία έναρξης');
						return;
					}
					
					var year = parseInt(parts[0]);
					var month = parseInt(parts[1]) - 1; // JavaScript months are 0-based
					var day = parseInt(parts[2]);
					
					// Create date object
					var startDate = new Date(year, month, day);
					
					// Add days (subtract 1 because start date counts as day 1)
					var finishDate = new Date(startDate);
					finishDate.setDate(finishDate.getDate() + days - 1);
					
					// Format the dates
					var dd = String(finishDate.getDate()).padStart(2, '0');
					var mm = String(finishDate.getMonth() + 1).padStart(2, '0');
					var yyyy = finishDate.getFullYear();
					var displayDate = dd + '-' + mm + '-' + yyyy;
					var hiddenDate = yyyy + '-' + mm + '-' + dd;
					
					// Update both the display field and the hidden field
					document.getElementById('finish').value = displayDate;
					document.getElementById('finish_hidden').value = hiddenDate;
			    }
            </script>
                <a href="javascript:addDays2Date();"><small>Υπολογισμός<br>Ημ.Λήξης</small></a>
            <?php              
                echo "</td></tr>";
                echo "<tr><td>Ημ/νία λήξης</td><td>";
            modern_datepicker("finish", date('Y-m-d'), array(
                'minDate' => '2011-01-01',
                'maxDate' => '2030-12-31'
            ));
            echo "</td></tr>";

            echo "<tr><td>Λόγος</td><td><input type='text' name='logos' /></td></tr>";

            echo "<tr><td>Σχόλια</td><td><input type='text' name='comments' /></td></tr>";

            echo "	</table>";
            echo "	<input type='hidden' name = 'id' value='$id'>";
                echo "	<input type='hidden' name = 'emp_id' value='$emp_id'>";
            // action = 1 gia prosthiki
            echo "  <input type='hidden' name = 'action' value='1'>";
                echo "  <input type='hidden' name = 'sxoletos' value=$sxol_etos>";
            echo "	<input type='submit' value='Προσθήκη'>";
            //echo "    <INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
                echo "	<INPUT TYPE='button' VALUE='Επιστροφή στην καρτέλα εκπ/κού' onClick=\"parent.location='ektaktoi.php?id=$emp_id&op=view'\">";
            echo "	</form>";
            ?>
        <div id='results'></div>
            <?php
                        echo "    </center>";
                        echo "</body>";
                        echo "</html>";
        }
                mysqli_close($mysqlconnection);
        ?>
    </html>
