<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"config.php";
  require_once"functions.php";
  require_once"tools/access.php";
  //define("L_LANG", "el_GR"); Needs fixing
  require('calendar/tc_calendar.php');
  
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
  // Demand authorization                
  include("tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false){
    header("Location: tools/login_check.php");
  }
  $klados_type = 0;
	
?>
<html>
  <head>
	<LINK href="style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Έκτακτο Προσωπικό</title>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>
	<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
        <script type="text/javascript" src="js/jquery.table.addrow.js"></script>
	<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
	<script type="text/javascript">
           
        $(document).ready(function(){
		$("#wordfrm").validate({
			debug: false,
			rules: {
			//	name: "required",
			},
			messages: {
			//	name: "Please let us know who you are."
			},
			submitHandler: function(form) {
				// do other stuff for a valid form
				$.post('vev_yphr.php', $("#wordfrm").serialize(), function(data) {
					$('#word').html(data);
				});
			}
		});
	});
        var mylink = "<small>Παρακαλώ δώστε έγκυρη πράξη ή δημιουργήστε μία: </small><a target=\"_blank\" href=\"praxi.php\">Πράξεις</a>";
        
                $(document).ready(function(){
		$("#updatefrm").validate({
			debug: false,
                        rules: {
				name: "required", surname: "required", afm: "required", klados: "required", praxi: {"required": true, min:2 }, type: "required"
			},
			messages: {
				name: "Παρακαλώ δώστε όνομα", surname: "Παρακαλώ δώστε επώνυμο", afm: "Παρακαλώ δώστε έγκυρo ΑΦΜ",
                                klados: "Παρακαλώ δώστε έγκυρη τιμή", praxi: mylink, type: "Παρακαλώ δώστε έγκυρη τιμή"
			},
			submitHandler: function(form) {
				// do other stuff for a valid form
				$.post('update_ekt.php', $("#updatefrm").serialize(), function(data) {
					$('#results').html(data);
				});
			}
		});
	});
                $().ready(function(){
                        $(".slidingDiv").hide();
                        $(".show_hide").show();
 
                        $('.show_hide').click(function(){
                            $(".slidingDiv").slideToggle();
                        });
                });
    
</script>

  </head>
  <body> 
    <center>
      <?php
                $usrlvl = $_SESSION['userlevel'];
       if ($_GET['op']!="add")
       {
           if ($_GET['sxoletos']) {
               $sx = $_GET['sxoletos'];
               $query = "SELECT * FROM ektaktoi_$sx e join yphrethsh_ekt y on e.id = y.emp_id where e.id = ".$_GET['id']." AND y.sxol_etos = $sx";
           }
           else{
                $query = "SELECT * FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where e.id = ".$_GET['id']." AND y.sxol_etos = $sxol_etos";
           }

		$result = mysql_query($query, $mysqlconnection);
		$num=mysql_numrows($result);
		
                if ($num > 0)
                {
                    $multi = 1;
                    for ($i=0; $i<$num; $i++)
                    {
                        $yphr_id_arr[$i] = mysql_result($result, $i, "yphrethsh");
                        $yphr_arr[$i] = getSchool (mysql_result($result, $i, "yphrethsh"), $mysqlconnection);
                        $hours_arr[$i] = mysql_result($result, $i, "hours");
                    }            
                }
                else
                {
                    $query = "SELECT * from ektaktoi where id=".$_GET['id'];
                    $result = mysql_query($query, $mysqlconnection);
                    $num=mysql_numrows($result);
                    $sx_yphrethshs_id = mysql_result($result, 0, "sx_yphrethshs");
                    $sx_yphrethshs = getSchool ($sx_yphrethshs_id, $mysqlconnection);
                }
       //}
                $id = mysql_result($result, 0, "id");
		$name = mysql_result($result, 0, "name");
                $type = mysql_result($result, 0, "type");
		$surname = mysql_result($result, 0, "surname");
		$klados_id = mysql_result($result, 0, "klados");
		$klados = getKlados($klados_id,$mysqlconnection);
                // if nip or nip eidikhs
                if ($klados_id == 1 || $klados_id == 16 || $klados_id == 17)
                    $klados_type = 2;
                // if ebp or sx.nosileytes
                elseif ($klados_id == 12 || $klados_id == 25) {
                    $klados_type = 0;
                }
                else
                    $klados_type = 1;
                $metakinhsh = stripslashes(mysql_result($result, 0, "metakinhsh"));
		$patrwnymo = mysql_result($result, 0, "patrwnymo");
		$mhtrwnymo = mysql_result($result, 0, "mhtrwnymo");
		$afm = mysql_result($result, 0, "afm");
		$vathm = mysql_result($result, 0, "vathm");
		$mk = mysql_result($result, 0, "mk");
                $hm_mk = mysql_result($result, 0, "hm_mk");
		$analipsi = mysql_result($result, 0, "analipsi");
		$hm_anal = mysql_result($result, 0, "hm_anal");
		$met_did = mysql_result($result, 0, "met_did");
		//$ya = mysql_result($result, 0, "ya");
		//$apofasi = mysql_result($result, 0, "apofasi");
		$comments = mysql_result($result, 0, "comments");
                $comments = str_replace(" ", "&nbsp;", $comments);
                $type = mysql_result($result, 0, "type");
                $stathero = mysql_result($result, 0, "stathero");
                $kinhto = mysql_result($result, 0, "kinhto");
                $praxi = mysql_result($result, 0, "praxi");
                $updated= mysql_result($result, 0, "updated");
                
                $kat = mysql_result($result, 0, "status");
                switch ($kat)
                {   
                    case 1:
                        $katast = "Εργάζεται";
                        break;
                    case 2:
                        $katast = "Λύση Σχέσης - Παραίτηση";
                        break;
                    case 3:
                        $katast = "Άδεια";
                        break;
                    case 4:
                        $katast = "Διαθεσιμότητα";
                        break;
                }
       }
                ?>
        	<script type="text/javascript">

        $().ready(function() {
		$("#yphr").autocomplete("get_school.php", {
                               extraParams: {type: <?php echo $klados_type; ?>},
				width: 260,
				matchContains: true,
				selectFirst: false
			});
	});
                
        $().ready(function() {
		$(".addRow").btnAddRow(function(row){
                        row.find(".yphrow").autocomplete("get_school.php", {
                                extraParams: {type: <?php echo $klados_type; ?>},
				width: 260,
				matchContains: true,
				selectFirst: false
                        })
                });
                $(".delRow").btnDelRow();
                        $(".yphrow").autocomplete("get_school.php", {
                                extraParams: {type: <?php echo $klados_type; ?>},
				width: 260,
				matchContains: true,
				selectFirst: false
                        });
                });
    </script>
        <?php
if ($_GET['op']=="add")
	{
		echo "<form id='updatefrm' action='update_ekt.php' method='POST'>";
		echo "<table class=\"imagetable\" border='1'>";
		
		echo "<tr>";
		//echo "<td>ID</td><td>$id</td>";
		echo "</tr>";
		echo "<tr><td>Όνομα</td><td><input type='text' name='name' /></td></tr>";
		echo "<tr><td>Επώνυμο</td><td><input type='text' name='surname' /></td></tr>";
		echo "<tr><td>Πατρώνυμο</td><td><input type='text' name='patrwnymo' /></td></tr>";
		echo "<tr><td>Μητρώνυμο</td><td><input type='text' name='mhtrwnymo' /></td></tr>";
		echo "<tr><td>Α.Φ.Μ.</td><td><input type='text' name='afm' /></td></tr>";
                echo "<tr><td>Σταθερό</td><td><input type='text' name='stathero' /></td></tr>";
                echo "<tr><td>Κινητό</td><td><input type='text' name='kinhto' /></td></tr>";
		echo "<tr><td>Κλάδος</td><td>";
		kladosCmb($mysqlconnection);
		echo "</td></tr>";
		//echo "<tr><td>Βαθμός</td><td><input type='text' name='vathm' /></td></tr>";
		//echo "<tr><td>Μ.Κ.</td><td><input type='text' name='mk' /></td></tr>";
		
		//echo "<tr><td>Ανάληψη υπηρεσίας</td><td><input type='text' name='analipsi' /></td></tr>";
                echo "<tr><td>Ημ/νία ανάληψης</td><td>";
		$myCalendar = new tc_calendar("hm_anal", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setPath("calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";		
				
		//echo "<tr><td>Μεταπτυχιακό/Διδακτορικό</td><td>";
		//metdidCombo(0);		
                
                echo "<tr><td>Τύπος Απασχόλησης</td><td>";
                typeCmb($mysqlconnection);
                echo "</td></tr>";
		echo "<tr><td>Σχόλια</td><td><input type='text' name='comments' /></td></tr>";
                //echo "<tr><td>Υπουργική Απόφαση</td><td><input type='text' name='ya' /></td></tr>";
                //echo "<tr><td>Απόφαση Δ/ντή</td><td><input type='text' name='apofasi' /></td></tr>";
		echo "<tr><td>Πράξη:</td><td>";
                tblCmb($mysqlconnection, "praxi",$praxi);
                echo "</td></tr>"; 
		echo "<div id=\"content\">";
		echo "<form autocomplete=\"off\">";
		echo "<tr><td>Σχολείο(-α) Υπηρέτησης";
                echo "<a href=\"\" onclick=\"window.open('help/help.html#school_ekt','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"images/help.gif\"/></a>";
                //echo "</td><td><input type=\"text\" name=\"yphr\" id=\"yphr\" size=50/>";
                echo "</td><td><input type=\"text\" name=\"yphr[]\" class=\"yphrow\" id=\"yphrow\" />";
                echo "&nbsp;&nbsp;<input type=\"text\" name=\"hours[]\" size=1 />";
                echo "&nbsp;<input class=\"addRow\" type=\"button\" value=\"Προσθήκη\" />";
                echo "<input class=\"delRow\" type=\"button\" value=\"Αφαίρεση\" />";
                //echo "</form>";
                echo "</div>";
		
		echo "	</table>";
		echo "	<input type='hidden' name = 'id' value='$id'>";
		// action = 1 gia prosthiki
                echo "  <input type='hidden' name = 'status' value='1'>";
		echo "  <input type='hidden' name = 'action' value='1'>";
                echo "<br>";
		echo "	<input type='submit' value='Καταχώρηση'>";
                echo "	<INPUT TYPE='button' VALUE='Επιστροφή στη λίστα έκτακτου προσωπικού' onClick=\"parent.location='ektaktoi_list.php'\">";
                echo "<br>";
		echo "	<br><INPUT TYPE='button' VALUE='Αρχική Σελίδα' onClick=\"parent.location='index.php'\">";
		echo "	</form>";
?>
<div id='results'></div>
<?php
		echo "    </center>";
		echo "</body>";
		echo "</html>";
	}

if ($_GET['op']=="edit")
	{
            ?>
   <script type="text/javascript">
        $().ready(function() {
                $("#adeia").click(function() {
                    var MyVar = <?php echo $id; ?>;
                    $("#adeies").load("ekt_adeia_list.php?id="+ MyVar );
                    });
                });
        
	
    </script>
<?php
		echo "<form id='updatefrm' name='update' action='update_ekt.php' method='POST'>";
		echo "<table class=\"imagetable\" border='1'>";
		
		echo "<tr>";
		echo "<td>ID</td><td>$id</td>";
		echo "</tr>";
		echo "<tr><td>Όνομα</td><td><input type='text' name='name' value=$name /></td></tr>";
		echo "<tr><td>Επώνυμο</td><td><input type='text' name='surname' value=$surname /></td></tr>";
		echo "<tr><td>Πατρώνυμο</td><td><input type='text' name='patrwnymo' value=$patrwnymo /></td></tr>";
		echo "<tr><td>Μητρώνυμο</td><td><input type='text' name='mhtrwnymo' value=$mhtrwnymo /></td></tr>";
		echo "<tr><td>Α.Φ.Μ.</td><td><input type='text' name='afm' value=$afm /></td></tr>";
                echo "<tr><td>Σταθερό</td><td><input type='text' name='stathero' value=$stathero /></td></tr>";
                echo "<tr><td>Κινητό</td><td><input type='text' name='kinhto' value=$kinhto /></td></tr>";
		echo "<tr><td>Κλάδος</td><td>";
		kladosCombo($klados_id,$mysqlconnection);
                echo "</td></tr>";
                echo "<tr><td>Κατάσταση</td><td>";
                katastCmb($kat);
		echo "</td></tr>";
		//echo "<tr><td>Βαθμός</td><td>";
		//vathmosCmb1($vathm, $mysqlconnection);
		//echo "</td><tr>";
		//<input type='text' name='vathm' value=$vathm /></td></tr>";
		//echo "<tr><td>Μ.Κ.</td><td><input type='text' name='mk' value=$mk /></td></tr>";
		echo "<tr><td>Τύπος απασχόλησης</td><td>";
                typeCmb1($type, $mysqlconnection);
                echo "</td></tr>";
		echo "<tr><td>Ανάληψη</td><td><input type='text' name='analipsi' value=$analipsi /></td></tr>";
		
		echo "<tr><td>Ημ/νία ανάληψης</td><td>";
		$myCalendar = new tc_calendar("hm_anal", true);
		$myCalendar->setIcon("calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date('d',strtotime($hm_anal)),date('m',strtotime($hm_anal)),date('Y',strtotime($hm_anal)));
		$myCalendar->setPath("calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";		
				
		//echo "<tr><td>Μεταπτυχιακό/Διδακτορικό</td><td>";
		//<input type='text' name='met_did' value=$met_did /></td></tr>";
		//metdidCombo($met_did);
		//echo "<tr><td>Υπουργική Απόφαση</td><td><input size=50 type='text' name='ya' value=$ya /></td></tr>";
                //echo "<tr><td>Απόφαση Δ/ντή</td><td><input size=50 type='text' name='apofasi' value=$apofasi /></td></tr>";
		echo "<tr><td>Πράξη:</td><td>";
                tblCmb($mysqlconnection, "praxi",$praxi);
                echo "</td></tr>";
		echo "<tr><td>Σχόλια</td><td><input size=50 type='text' name='comments' value=$comments /></td></tr>";
		
		//new 15-02-2012: implemented with jquery.autocomplete
		echo "<div id=\"content\">";
		echo "<form autocomplete=\"off\">";
                
                if ($multi)
                {
                    $count = count($yphr_arr);
                        for ($i=0; $i<$count; $i++)
                        {
                            echo "<tr><td>Σχολείο (-α) Υπηρέτησης";
                            echo "<a href=\"\" onclick=\"window.open('help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"images/help.gif\"/></a>";
                            echo "</td><td><input type=\"text\" name=\"yphr[]\" value='$yphr_arr[$i]' class=\"yphrow\" id=\"yphrow\" size=40/>";
                            echo "&nbsp;&nbsp;<input type=\"text\" name=\"hours[]\" value='$hours_arr[$i]' size=1 />";
                            echo "&nbsp;<input class=\"addRow\" type=\"button\" value=\"Προσθήκη\" />";
                            echo "<input class=\"delRow\" type=\"button\" value=\"Αφαίρεση\" />";
                            echo "</tr>";
                        }
                    }
                else
                {
                    echo "<tr><td>Σχολείο (-α) Υπηρέτησης";
                    echo "<a href=\"\" onclick=\"window.open('help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"images/help.gif\"/></a>";
                    echo "</td><td><input type=\"text\" name=\"yphr[]\" value='$sx_yphrethshs' class=\"yphrow\" id=\"yphrow\" size=40/>";
                    echo "&nbsp;&nbsp;<input type=\"text\" name=\"hours[]\" size=1 />";
                    echo "&nbsp;<input class=\"addRow\" type=\"button\" value=\"Προσθήκη\" />";
                    echo "<input class=\"delRow\" type=\"button\" value=\"Αφαίρεση\" />";
                    echo "</tr>";
                }
                
                echo "<tr><td>Μετακινήσεις</td><td><textarea rows=4 cols=50 name='metakinhsh'>$metakinhsh</textarea></td></tr>";
		echo "</form>";
		echo "</div>";
		echo "	</table>";
                
		echo "	<input type='hidden' name = 'id' value='$id'>";
		echo "	<input type='submit' value='Επεξεργασία'>";
                echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='ektaktoi.php?id=$id&op=view'\">";
		echo "	</form>";
		echo "    </center>";
		echo "</body>";
?>
        <div id='results'>   </div>
<?php
		echo "</html>";
    }
	elseif ($_GET['op']=="view")
	{		
		echo "<table class=\"imagetable\" border='1'>";	
		echo "<tr>";
		//echo "<td colspan=2>ID</td><td colspan=2>$id</td>";
                echo "<th colspan=4 align=center>Καρτέλα Υπαλλήλου</th>";
		echo "</tr>";
		echo "<tr><td>Όνομα</td><td>$name</td><td>Επώνυμο</td><td>$surname</td></tr>";
		echo "<tr><td>Πατρώνυμο</td><td>$patrwnymo</td><td>Μητρώνυμο</td><td>$mhtrwnymo</td></tr>";

		echo "<tr><td>Α.Φ.Μ.</td><td>$afm</td><td></td><td></td></tr>";
		echo "<tr><td>Κλάδος</td><td>".getKlados($klados_id,$mysqlconnection)."</td><td>Κατάσταση</td><td>$katast</td></tr>";
                echo "<tr><td><a href=\"#\" class=\"show_hide\"><small>Εμφάνιση/Απόκρυψη<br>περισσοτέρων στοιχείων</small></a></td>";
                echo "<td colspan=3><div class=\"slidingDiv\">";
                echo "Τηλ.: $stathero - $kinhto<br>";
                
           if ($mdb)
           {
                $res = misth_elements($afm);
                if ($res!=NULL)
                {
                    echo "Διεύθυνση: ".$res['street']." ".$res['numStr'].", ".$res['city']."<br>";
                    echo "ΑΔΤ: ".$res['idNum']."<br>";
                    echo "AMKA: ".$res['AMKA']."<br>";
                }
                else
                {
                    echo "Περισσότερα στοιχεία δεν είναι διαθέσιμα.";   
                }
           }
               echo "</div>";
               echo "</td></tr>";
                
                //$hm_mk = date ('d-m-Y', strtotime($hm_mk));
		//echo "<tr><td>Βαθμός</td><td>$vathm</td><td>Μ.Κ.</td><td>$mk &nbsp;<small>(από $hm_mk)</small></td></tr>";
		switch ($met_did)
		{
			case 0:
				$met="Όχι";
				break;
			case 1:
				$met="Μεταπτυχιακό";
				break;
			case 2:
				$met="Διδακτορικό";
				break;
			case 3:
				$met="Μετ. + Διδ.";
				break;
		}
                		
		echo "<tr><td>Σχόλια<br><br></td><td colspan='3'>$comments</td></tr>"; 
                
                // check if multiple schools
                if ($multi)
                {
                    $count = count($yphr_arr);
                    for ($i=0; $i<$count; $i++)
                    {
                        $sxoleia .=  "<a href=\"school_status.php?org=$yphr_id_arr[$i]\">$yphr_arr[$i]</a> ($hours_arr[$i] ώρες)<br>";
                        $counthrs += $hours_arr[$i];
                    }
                    if ($count > 1)
                        echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3>$sxoleia<br><small>($counthrs ώρες σε $count Σχολεία)</small></td></tr>";
                    else
                        echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3>$sxoleia</td></tr>";
                }
                else
                {
                    echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3><a href=\"school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a></td></tr>";
                }
                $typos = get_type($type,$mysqlconnection);
                echo "<tr><td>Ανάληψη υπηρεσίας</td><td colspan=3>$analipsi</td>";
                $date_anal = date ("d-m-Y",  strtotime($hm_anal));
                echo "<tr><td>Ημ/νία Ανάληψης</td><td colspan=3>$date_anal</td>";
                echo "<tr><td>Μετακινήσεις</td><td colspan=3>$metakinhsh</td></tr>";
                echo "<tr><td>Τύπος Απασχόλησης</td><td colspan=3>$typos</td>";
                echo "<tr><td>Πραξη</td><td colspan=3>".getNamefromTbl($mysqlconnection, "praxi", $praxi)."</td></tr>";
                
                $qry = "SELECT * FROM praxi WHERE id=$praxi";
                $res = mysql_query($qry);
                $ya = mysql_result($res, 0, 'ya');
                $apofasi = mysql_result($res, 0, 'apofasi');
                $ada = mysql_result($res, 0, 'ada');
                echo "<tr><td>Υπουργική Απόφαση</td><td colspan=3>$ya</td></tr>";
                echo "<tr><td>Α.Δ.Α.</td><td colspan=3>$ada</td></tr>";
                echo "<tr><td>Απόφαση Δ/ντή</td><td colspan=3>$apofasi</td></tr>";
				
		/* Future use?
		echo "<td colspan=2 align='center'>";
		//Form gia Bebaiwsh
		echo "<form id='wordfrm' name='wordfrm' action='vev_yphr.php' method='POST'>";
		echo "<input type='hidden' name=arr[] value=$surname>";
		echo "<input type='hidden' name=arr[] value=$name>";
		echo "<input type='hidden' name=arr[] value=$patrwnymo>";
		echo "<input type='hidden' name=arr[] value=$klados>";
		echo "<input type='hidden' name=arr[] value=$am>";
		echo "<input type='hidden' name=arr[] value=$vathm>";
		echo "<input type='hidden' name=arr[] value=$mk>";
		echo "<input type='hidden' name=arr[] value='$sx_organikhs'>";
		echo "<input type='hidden' name=arr[] value=$fek_dior>";
		echo "<input type='hidden' name=arr[] value=$hm_dior_org>";
		echo "<input type='hidden' name=arr[] value=$hm_anal>";
		$ymd = ypol_yphr(date("Y/m/d"),$anatr);
		echo "<input type='hidden' name=arr[] value='$ymd'>";
		//echo "<input type='hidden' name=$arr[] value=$surname>";
		echo "<INPUT TYPE='submit' VALUE='Βεβαίωση Υπηρ.Κατάστασης'>"; 
		echo "</form>";
                ?>
                <div id="word"></div>
                <?php
		echo "</td></tr>";
                */              		
                echo $updated > 0 ? "<tr><td colspan=4 align='right'><small>Τελευταία ενημέρωση: ".date("d-m-Y H:i", strtotime($updated))."</small></td></tr>" : null;
		echo "	</table>";
		
                echo "<br>";
                // echo "  <INPUT TYPE='submit' id='adeia' VALUE='Άδειες'>"; future use?
                if ($usrlvl < 3)
                    echo "	<INPUT TYPE='button' VALUE='Επεξεργασία' onClick=\"parent.location='ektaktoi.php?id=$id&op=edit'\">";
                echo "  <input type='button' value='Εκτύπωση' onclick='javascript:window.print()' />";
                echo "  <INPUT TYPE='submit' id='adeia' VALUE='Άδειες'>";
                echo "	<INPUT TYPE='button' VALUE='Επιστροφή στη λίστα έκτακτου προσωπικού' onClick=\"parent.location='ektaktoi_list.php'\">";

                echo "<br><br><INPUT TYPE='button' VALUE='Αρχική σελίδα' onClick=\"parent.location='index.php'\">";
		?>
                <div id="adeies"></div>
                <?php
                
                echo "    </center>";
		echo "</body>";
		echo "</html>";	
	}
	if ($_GET['op']=="delete")
	{
		// Copies the to-be-deleted row to employee_deleted table for backup purposes.Also inserts a row on employee_del_log...
		//$query1 = "INSERT INTO ektaktoi_deleted SELECT e.* FROM ektaktoi e WHERE id =".$_GET['id'];
		//$result1 = mysql_query($query1, $mysqlconnection);
                //$query1 = "INSERT INTO ektaktoi_log (emp_id, userid, action) VALUES (".$_GET['id'].",".$_SESSION['userid'].", 2)";
		//$result1 = mysql_query($query1, $mysqlconnection);
                $query = "DELETE from ektaktoi where id=".$_GET['id'];
		$result = mysql_query($query, $mysqlconnection);
		// Copies the deleted row to employee)deleted
		
		if ($result)
			echo "Η εγγραφή με κωδικό $id διαγράφηκε με επιτυχία.";
		else
			echo "Η διαγραφή απέτυχε...";
		echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='ektaktoi_list.php'\">";
	}
	
	mysql_close();
?> 

<style type="text/css">
	body {
		font-family: Arial;
		font-size: 11px;
		color: #000;
	}
	
	h3 {
		margin: 0px;
		padding: 0px;	
	}

	.suggestionsBox {
		font-family: sans-serif;
		position: relative;
		left: 30px;
		margin: 10px 0px 0px 0px;
		width: 200px;
		background-color: #212427;
		-moz-border-radius: 7px;
		-webkit-border-radius: 7px;
		border: 2px solid #000;	
		color: #fff;
	}
	
	.suggestionList {
		margin: 0px;
		padding: 0px;
	}
	
	.suggestionList li {
		
		margin: 0px 0px 3px 0px;
		padding: 3px;
		cursor: pointer;
	}
	
	.suggestionList li:hover {
		background-color: #659CD8;
	}
</style>