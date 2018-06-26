<meta http-equiv="content-type" content="text/html; charset=iso8859-7">
<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  Require_once "../config.php";
  Require_once "../tools/functions.php";
  //Require_once "../tools/access.php";
  //define("L_LANG", "el_GR"); Needs fixing
  require_once '../tools/calendar/tc_calendar.php';
  
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
  // Demand authorization                
  include("../tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false){
    header("Location: ../tools/login.php");
  }
  $klados_type = 0;
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Μόνιμο Προσωπικό</title>
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery.validate.js"></script>
	<script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <script type="text/javascript" src="../js/jquery.table.addrow.js"></script>
    <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
    <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
	<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
	<script type="text/javascript">
        $(document).ready(function(){
            $("#yphrfrm").validate({
                debug: false,
                rules: {
                //	name: "required",
                },
                messages: {
                //	name: "Please let us know who you are."
                },
                submitHandler: function(form) {
                    // do other stuff for a valid form
                    $.post('yphr.php', $("#yphrfrm").serialize(), function(data) {
                        $('#yphr_res').html(data);
                    });
                }
            });
        });
            
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
                    $.post('vev_yphr_pw.php', $("#wordfrm").serialize(), function(data) {
                        $('#word').html(data);
                    });
                }
            });
        });
            
            
        $(document).ready(function(){
            $("#updatefrm").validate({
                debug: false,
                rules: {
                    name: "required", surname: "required", afm: "required", am: "required", klados: "required", vathm: "required", mk: "required", org: "required"
                },
                messages: {
                    name: "Παρακαλώ δώστε όνομα", surname: "Παρακαλώ δώστε επώνυμο", afm: "Παρακαλώ δώστε έγκυρη τιμή", am: "Παρακαλώ δώστε έγκυρη τιμή", 
                                    klados: "Παρακαλώ δώστε έγκυρη τιμή", vathm: "Παρακαλώ δώστε έγκυρη τιμή", mk: "Παρακαλώ δώστε έγκυρη τιμή", org: "Παρακαλώ δώστε έγκυρη τιμή",
                },
                submitHandler: function(form) {
                    // do other stuff for a valid form
                    $.post('update.php', $("#updatefrm").serialize(), function(data) {
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
        $().ready(function(){
            $(".slidingDiv2").hide();
            $(".show_hide2").show();

            $('.show_hide2').click(function(){
                $(".slidingDiv2").slideToggle();
            });
        });
        $().ready(function(){
            $(".slidingDiv3").hide();
            $(".show_hide3").show();

            $('.show_hide3').click(function(){
                $(".slidingDiv3").slideToggle();
            });
        });
</script>

  </head>
  <body> 
  <?php include('../etc/menu.php'); ?>
    <center>
      <?php
            $usrlvl = $_SESSION['userlevel'];
            $id = $_GET['id'];
            if ($_GET['op']!="add")
            {
                //$query = "SELECT * FROM employee e join yphrethsh y on e.id = y.emp_id where e.id = ".$_GET['id'];
                $query = "SELECT * FROM employee e join yphrethsh y on e.id = y.emp_id where e.id = ".$_GET['id']." AND y.sxol_etos = $sxol_etos";
                $result = mysql_query($query, $mysqlconnection);
		        $num=mysql_num_rows($result);
                
                // if multiple schools
                //if ($num > 0)
                //{
                //    $multi = 1;
                    for ($i=0; $i<$num; $i++)
                    {
                        $yphr_id_arr[$i] = mysql_result($result, $i, "yphrethsh");
                        $yphr_arr[$i] = getSchool (mysql_result($result, $i, "yphrethsh"), $mysqlconnection);
                        $hours_arr[$i] = mysql_result($result, $i, "hours");
                    }
                //}
                // else one school
//                else
//                {
//                    $query = "SELECT * from employee where id=".$_GET['id'];
//                    $result = mysql_query($query, $mysqlconnection);
//                    $num=mysql_num_rows($result);
//                    $sx_yphrethshs_id = mysql_result($result, 0, "sx_yphrethshs");
//                    $sx_yphrethshs = getSchool ($sx_yphrethshs_id, $mysqlconnection);
//                }
                    $id = mysql_result($result, 0, "id");
                    $name = mysql_result($result, 0, "name");
                    $surname = mysql_result($result, 0, "surname");
                    $klados_id = mysql_result($result, 0, "klados");
                    // if nip
                    if ($klados_id == 1)
                        $klados_type = 2;
                    else
                        $klados_type = 1;
                    $klados = getKlados($klados_id,$mysqlconnection);
                    $sx_organ_id = mysql_result($result, 0, "sx_organikhs");
                    $sx_organikhs = getSchool ($sx_organ_id, $mysqlconnection);
                    $eidikh = mysql_result($result, 0, "eidikh");
                    $thesi = mysql_result($result, 0, "thesi");
                    $patrwnymo = mysql_result($result, 0, "patrwnymo");
                    $mhtrwnymo = mysql_result($result, 0, "mhtrwnymo");
                    $afm = mysql_result($result, 0, "afm");
                    $am = mysql_result($result, 0, "am");
                    //$etos_gen = mysql_result($result, 0, "etos_gen");
                    $vathm = mysql_result($result, 0, "vathm");
                    $mk = mysql_result($result, 0, "mk");
                    $hm_mk = mysql_result($result, 0, "hm_mk");
                    $fek_dior = mysql_result($result, 0, "fek_dior");
                    $hm_dior = mysql_result($result, 0, "hm_dior");
                    $analipsi = mysql_result($result, 0, "analipsi");
                    $hm_anal = mysql_result($result, 0, "hm_anal");
                    $met_did = mysql_result($result, 0, "met_did");
                    $proyp = mysql_result($result, 0, "proyp");
                    $anatr = mysql_result($result, 0, "anatr");
                    $comments = mysql_result($result, 0, "comments");
                    $comments = str_replace(" ", "&nbsp;", $comments);
                    $wres = mysql_result($result, 0, "wres");
                    
                    //new 16-05-2013
                    $tel = mysql_result($result, 0, "tel");
                    $address = mysql_result($result, 0, "address");
                    $idnum = mysql_result($result, 0, "idnum");
                    $amka = mysql_result($result, 0, "amka");
				
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
                    // updated: 05-09-2013
                    $updated = mysql_result($result, 0, "updated");
                    // aney apodoxwn: 27-02-2014
                    $aney = mysql_result($result, 0, "aney");
                    $aney_xr = mysql_result($result, 0, "aney_xr");
                    $aney_apo = mysql_result($result, 0, "aney_apo");
                    $aney_ews = mysql_result($result, 0, "aney_ews");
                    // idiwtiko ergo se dhmosio forea
                    $idiwtiko = mysql_result($result, 0, "idiwtiko");
                    $idiwtiko_liksi = mysql_result($result, 0, "idiwtiko_liksi");
                    $idiwtiko_enarxi = mysql_result($result, 0, "idiwtiko_enarxi");
                    // idiwtiko ergo se idiwtiko forea
                    $idiwtiko_id = mysql_result($result, 0, "idiwtiko_id");
                    $idiwtiko_id_enarxi = mysql_result($result, 0, "idiwtiko_id_enarxi");
                    $idiwtiko_id_liksi = mysql_result($result, 0, "idiwtiko_id_liksi");
                    // kat'oikon
                    $katoikon = mysql_result($result, 0, "katoikon");
                    $katoikon_apo = mysql_result($result, 0, "katoikon_apo");
                    $katoikon_ews = mysql_result($result, 0, "katoikon_ews");
                    $katoikon_comm = mysql_result($result, 0, "katoikon_comm");
                    $katoikon_comm = str_replace(" ", "&nbsp;", $katoikon_comm);
                    
            } // of if not add
?>
        
<script type="text/javascript">
    $().ready(function() {
        $("#org").autocomplete("get_school.php", {
                            extraParams: {type: <?php echo $klados_type; ?>},
            width: 260,
            matchContains: true,
            selectFirst: false
        });
    });
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
            
    $().ready(function() {
        $("#adeia").click(function() {
            var MyVar = <?php echo $id; ?>;
            $("#adeies").load("adeia_list.php?id="+ MyVar );
        });
    });
</script>
     
        
<?php
            
if ($_GET['op']=="edit")
	{
		echo "<form id='updatefrm' name='update' action='update.php' method='POST'>";
		echo "<table class=\"imagetable\" border='1'>";
		
        echo "<tr><td>Επώνυμο</td><td><input type='text' name='surname' value=$surname /></td></tr>";
        echo "<tr><td>Όνομα</td><td><input type='text' name='name' value=$name /></td></tr>";
		echo "<tr><td>Πατρώνυμο</td><td><input type='text' name='patrwnymo' value=$patrwnymo /></td></tr>";
		echo "<tr><td>Μητρώνυμο</td><td><input type='text' name='mhtrwnymo' value=$mhtrwnymo /></td></tr>";
		echo "<tr><td>Α.Φ.Μ.</td><td><input type='text' name='afm' value=$afm /></td></tr>";
        echo "<tr><td>Τηλέφωνο</td><td><input size='30' type='text' name='tel' value='$tel' /></td></tr>";
        echo "<tr><td>Διεύθυνση</td><td><input size='50' type='text' name='address' value='$address' /></td></tr>";
        echo "<tr><td>Α.Δ.Τ.</td><td><input type='text' name='idnum' value='$idnum' /></td></tr>";
        echo "<tr><td>Α.Μ.K.A.</td><td><input type='text' name='amka' value='$amka' /></td></tr>";
		echo "<tr><td>Α.Μ.</td><td><input type='text' name='am' value=$am /></td></tr>";
		echo "<tr><td>Κλάδος</td><td>";
		kladosCombo($klados_id,$mysqlconnection);
        echo "</td></tr>";
        echo "<tr><td>Ώρες Υποχρ.Ωρ.</td><td><input type='text' name='wres' value=$wres /></td></tr>";
        echo "<tr><td>Κατάσταση</td><td>";
        katastCmb($kat);
		echo "</td></tr>";
		echo "<tr><td>Βαθμός</td><td>";
		vathmosCmb1($vathm, $mysqlconnection);
		echo "</td><tr>";
		//<input type='text' name='vathm' value=$vathm /></td></tr>";
		echo "<tr><td>Μ.Κ.</td><td><input type='text' name='mk' value=$mk /></td></tr>";
        echo "<tr><td>Ημ/νία M.K.</td><td>";
		$myCalendar = new tc_calendar("hm_mk", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		//$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setDate(date('d',strtotime($hm_mk)),date('m',strtotime($hm_mk)),date('Y',strtotime($hm_mk)));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
		//$myCalendar->setSpecificDate(array("2011-04-01", "2011-04-14", "2010-12-25"), 0, "year");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";		
                
                
		echo "<tr><td>ΦΕΚ Διορισμού</td><td><input type='text' name='fek_dior' value=$fek_dior /></td></tr>";
		//echo "<tr><td>hm_dior</td><td><input type='text' name='hm_dior' value=".date('d-m-Y',strtotime($hm_dior))." /></td></tr>";
		
		echo "<tr><td>Ημ/νία Διορισμού</td><td>";
		$myCalendar = new tc_calendar("hm_dior", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		//$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setDate(date('d',strtotime($hm_dior)),date('m',strtotime($hm_dior)),date('Y',strtotime($hm_dior)));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
		//$myCalendar->setSpecificDate(array("2011-04-01", "2011-04-14", "2010-12-25"), 0, "year");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";		
		
		//echo "<tr><td>analipsi</td><td><input type='text' name='analipsi' value=$analipsi /></td></tr>";
		
		echo "<tr><td>Ημ/νία ανάληψης</td><td>";
		$myCalendar = new tc_calendar("hm_anal", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date('d',strtotime($hm_anal)),date('m',strtotime($hm_anal)),date('Y',strtotime($hm_anal)));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";		
				
		echo "<tr><td>Μεταπτυχιακό/Διδακτορικό</td><td>";
		//<input type='text' name='met_did' value=$met_did /></td></tr>";
		metdidCombo($met_did);
		$ymd=days2ymd($proyp);
		echo "<tr><td>Προϋπηρεσία</td><td>($proyp Ημέρες)&nbsp;Έτη<input type='text' name='pyears' size=1 value=$ymd[0] />Μήνες<input type='text' name='pmonths' size=1 value=$ymd[1] />Ημέρες<input type='text' name='pdays' size=1 value=$ymd[2] /></td></tr>";
		//$ymd=days2ymd($anatr);
		//echo "<tr><td>Ανατρέχει</td><td><input type='text' name='anatr' size='3' value=$anatr />&nbsp;Eth<input type='text' name='ayears' size=1 value=$ymd[0] />Mhnes<input type='text' name='amonths' size=1 value=$ymd[1] />Meres<input type='text' name='adays' size=1 value=$ymd[2] /></td></tr>";
                
		// aney
        echo "<tr><td>Σε άδ.άνευ αποδοχών:</td><td>";
        if ($aney)
            echo "<input type='checkbox' name='aney' checked>";
        else
            echo "<input type='checkbox' name='aney'>";
        echo "</tr>";
        echo "<tr><td>Τρέχουσα άδεια<br>άνευ αποδοχών: (Από / Έως)</td><td>";
                                
        $myCalendar = new tc_calendar("aney_apo", true,false);
        $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
        $myCalendar->setDate(date('d',strtotime($aney_apo)),date('m',strtotime($aney_apo)),date('Y',strtotime($aney_apo)));
        $myCalendar->setPath("../tools/calendar/");
        //$myCalendar->setYearInterval(1970, date("Y"));
        $myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
        $myCalendar->setAlignment("left", "bottom");
        $myCalendar->writeScript();

        $myCalendar = new tc_calendar("aney_ews", true,false);
        $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
        $myCalendar->setDate(date('d',strtotime($aney_ews)),date('m',strtotime($aney_ews)),date('Y',strtotime($aney_ews)));
        $myCalendar->setPath("../tools/calendar/");
        $myCalendar->setAlignment("left", "bottom");
        $myCalendar->writeScript();
        
        echo "</td></tr>";
        $aney_ymd = days2ymd($aney_xr);
        echo "<tr><td>Χρόνος παλαιών αδειών<br>άνευ αποδοχών (<small>χωρίς την παραπάνω</small>):</td><td><input type='text' name='aney_y' size='3' value=$aney_ymd[0]> έτη&nbsp;";
        echo "<input type='text' name='aney_m' size='3' value=$aney_ymd[1]> μήνες&nbsp; <input type='text' name='aney_d' size='3' value=$aney_ymd[2]> ημέρες</td></tr>";
        
        // idiwtiko ergo 07-11-2014
        echo "<tr><td>Ιδ.έργο σε δημ.φορέα</td><td>";
        if ($idiwtiko)
            echo "<input type='checkbox' name='idiwtiko' checked>";
        else
            echo "<input type='checkbox' name='idiwtiko'>";
        echo "<tr><td>Ημ/νία έναρξης/λήξης Ιδ.Έργου σε δημ.φορέα</td><td>";
        $myCalendar = new tc_calendar("idiwtiko_enarxi", true,false);
        $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
        $myCalendar->setDate(date('d',strtotime($idiwtiko_enarxi)),date('m',strtotime($idiwtiko_enarxi)),date('Y',strtotime($idiwtiko_enarxi)));
        $myCalendar->setPath("../tools/calendar/");
        $myCalendar->dateAllow("1970-01-01", '2050-01-01');
        $myCalendar->setAlignment("left", "bottom");
        $myCalendar->writeScript();
        $myCalendar = new tc_calendar("idiwtiko_liksi", true,false);
        $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
        $myCalendar->setDate(date('d',strtotime($idiwtiko_liksi)),date('m',strtotime($idiwtiko_liksi)),date('Y',strtotime($idiwtiko_liksi)));
        $myCalendar->setPath("../tools/calendar/");
        $myCalendar->dateAllow("1970-01-01", '2050-01-01');
        $myCalendar->setAlignment("left", "bottom");
        $myCalendar->writeScript();
        // idiwtiko sympl
        echo "<tr><td>Ιδ.έργο σε ιδιωτ.φορέα</td><td>";
        if ($idiwtiko_id)
            echo "<input type='checkbox' name='idiwtiko_id' checked>";
        else
            echo "<input type='checkbox' name='idiwtiko_id'>";
        echo "<tr><td>Ημ/νία έναρξης/λήξης Ιδ.Έργου σε ιδιωτ.φορέα</td><td>";
        $myCalendar = new tc_calendar("idiwtiko_id_enarxi", true,false);
        $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
        $myCalendar->setDate(date('d',strtotime($idiwtiko_id_enarxi)),date('m',strtotime($idiwtiko_id_enarxi)),date('Y',strtotime($idiwtiko_id_enarxi)));
        $myCalendar->setPath("../tools/calendar/");
        $myCalendar->dateAllow("1970-01-01", '2050-01-01');
        $myCalendar->setAlignment("left", "bottom");
        $myCalendar->writeScript();
        $myCalendar = new tc_calendar("idiwtiko_id_liksi", true,false);
        $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
        $myCalendar->setDate(date('d',strtotime($idiwtiko_id_liksi)),date('m',strtotime($idiwtiko_id_liksi)),date('Y',strtotime($idiwtiko_id_liksi)));
        $myCalendar->setPath("../tools/calendar/");
        $myCalendar->dateAllow("1970-01-01", '2050-01-01');
        $myCalendar->setAlignment("left", "bottom");
        $myCalendar->writeScript();
        // idiwtiko end
        // katoikon
        echo "<tr><td>Κατ' οίκον διδασκαλία</td><td>";
        if ($katoikon)
            echo "<input type='checkbox' name='katoikon' checked>";
        else
            echo "<input type='checkbox' name='katoikon'>";
        echo "<tr><td>Έναρξη/λήξη κατ'οίκον διδασκαλίας</td><td>";
        $myCalendar = new tc_calendar("katoikon_apo", true,false);
        $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
        $myCalendar->setDate(date('d',strtotime($katoikon_apo)),date('m',strtotime($katoikon_apo)),date('Y',strtotime($katoikon_apo)));
        $myCalendar->setPath("../tools/calendar/");
        $myCalendar->dateAllow("1970-01-01", '2050-01-01');
        $myCalendar->setAlignment("left", "bottom");
        $myCalendar->writeScript();
        $myCalendar = new tc_calendar("katoikon_ews", true,false);
        $myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
        $myCalendar->setDate(date('d',strtotime($katoikon_ews)),date('m',strtotime($katoikon_ews)),date('Y',strtotime($katoikon_ews)));
        $myCalendar->setPath("../tools/calendar/");
        $myCalendar->dateAllow("1970-01-01", '2050-01-01');
        $myCalendar->setAlignment("left", "bottom");
        $myCalendar->writeScript();
        echo "<tr><td>Σχόλια κατ'οίκον διδασκαλίας</td><td><input size=50 type='text' name='katoikon_comm' value=$katoikon_comm /></td></tr>";
        // katoikon_end
        
        echo "<tr><td>Σχόλια</td><td><textarea rows=4 cols=80 name='comments' >$comments</textarea></td></tr>";
		
		//new 15-02-2012: implemented with jquery.autocomplete
		echo "<div id=\"content\">";
		echo "<form autocomplete=\"off\">";
		echo "<tr><td>Σχολείο Οργανικής";
        echo "<a href=\"\" onclick=\"window.open('help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"../images/help.gif\"/></a></td>";
        echo "<td><input type=\"text\" name=\"org\" id=\"org\" value='$sx_organikhs' size='40' />";

        $count = count($yphr_arr);
        for ($i=0; $i<$count; $i++)
        {
            echo "<tr><td>Σχολείο (-α) Υπηρέτησης";
            echo "<a href=\"\" onclick=\"window.open('help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"../images/help.gif\"/></a>";
            echo "</td><td><input type=\"text\" name=\"yphr[]\" value='$yphr_arr[$i]' class=\"yphrow\" id=\"yphrow\" size=40/>";
            echo "&nbsp;&nbsp;<input type=\"text\" name=\"hours[]\" value='$hours_arr[$i]' size=1 />";
            echo "&nbsp;<input class=\"addRow\" type=\"button\" value=\"Προσθήκη\" />";
            echo "<input class=\"delRow\" type=\"button\" value=\"Αφαίρεση\" />";
            echo "</tr>";
        }       
		echo "</div>";
        
        thesiselectcmb($thesi); 
		
		echo "	</table>";
		echo "	<input type='hidden' name = 'id' value='$id'>";
		echo "	<input type='submit' value='Επεξεργασία'>";
                echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='employee.php?id=$id&op=view'\">";
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
        echo "<br>";
		echo "<table class=\"imagetable\" border='1'>";	
		echo "<tr>";
		
        echo "<th colspan=4 align=center>Καρτέλα Υπαλλήλου</th>";
		echo "</tr>";
		echo "<tr><td>Επώνυμο</td><td>$surname</td><td>Όνομα</td><td>$name</td></tr>";
		echo "<tr><td>Πατρώνυμο</td><td>$patrwnymo</td><td>Μητρώνυμο</td><td>$mhtrwnymo</td></tr>";
                
                // 16-05-2013 tel,address,amka,idnum moved to employee table
                    if ($amka || $tel || $address || $idnum || $idiwtiko || $idiwtiko_id || $katoikon)
                    {
                        echo "<tr><td><a href=\"#\" class=\"show_hide\"><small>Εμφάνιση/Απόκρυψη<br>περισσοτέρων στοιχείων</small></a></td>";
                        echo "<td colspan=3><div class=\"slidingDiv\">";
                        echo "Τηλέφωνο: ".$tel."<br>";
                        echo "Διεύθυνση: ".$address."<br>";
                        echo "ΑΔΤ: ".$idnum."<br>";
                        echo "AMKA: ".$amka."<br>";
                        if ($idiwtiko)
                            echo "Ιδ.έργο σε δημ.φορέα<input type='checkbox' name='idiwtiko' checked disabled>";
                        else
                            echo "Ιδ.έργο σε δημ.φορέα<input type='checkbox' name='idiwtiko' disabled>";
                        $sdate = strtotime($idiwtiko_enarxi)>0 ? date ('d-m-Y', strtotime($idiwtiko_enarxi)) : '';
                        $ldate = strtotime($idiwtiko_liksi)>0 ?date ('d-m-Y', strtotime($idiwtiko_liksi)) : '';
                        echo ($idiwtiko > 0 ? "&nbsp;&nbsp;Έναρξη:&nbsp;$sdate&nbsp;-&nbsp;Λήξη:&nbsp;$ldate" : "");
                        echo "<br>";
                        if ($idiwtiko_id)
                            echo "Ιδ.έργο σε ιδιωτ.φορέα<input type='checkbox' name='idiwtiko_id' checked disabled>";
                        else
                            echo "Ιδ.έργο σε ιδιωτ.φορέα<input type='checkbox' name='idiwtiko_id' disabled>";
                        $sdate = strtotime($idiwtiko_id_enarxi)>0 ? date ('d-m-Y', strtotime($idiwtiko_id_enarxi)): '';
                        $ldate = strtotime($idiwtiko_id_liksi)>0 ? date ('d-m-Y', strtotime($idiwtiko_id_liksi)): '';
                        echo ($idiwtiko_id > 0 ? "&nbsp;&nbsp;Έναρξη:&nbsp;$sdate&nbsp;-&nbsp;Λήξη:&nbsp;$ldate" : "");
                        echo "<br>";
                        if ($katoikon)
                            echo "Κατ'οίκον διδασκαλία<input type='checkbox' name='katoikon' checked disabled>";
                        else
                            echo "Κατ'οίκον διδασκαλία<input type='checkbox' name='katoikon' disabled>";
                        $sdate = strtotime($katoikon_apo)>0 ? date ('d-m-Y', strtotime($katoikon_apo)) : '';
                        $ldate = strtotime($katoikon_ews)>0 ? date ('d-m-Y', strtotime($katoikon_ews)) : '';
                        echo ($katoikon > 0 ? "&nbsp;&nbsp;Έναρξη:&nbsp;$sdate&nbsp;-&nbsp;Λήξη:&nbsp;$ldate<br>Σχόλια:&nbsp;".stripslashes($katoikon_comm) : "");
                        echo "</div>";
                        echo "</td></tr>";
                    }
                    else
                    {
                        echo "<tr><td><a href=\"#\" class=\"show_hide\"><small>Εμφάνιση/Απόκρυψη<br>περισσοτέρων στοιχείων</small></a></td>";
                        echo "<td colspan=3><div class=\"slidingDiv\">";
                        echo "Δε βρέθηκαν περισσότερα στοιχεία για τον/-ην υπάλληλο.<br>";
                        echo "O/H υπάλληλος δε μισθοδοτείται από τη Δ/νση Ηρακλείου<br>";
                        echo "ή δεν έχουν καταχωρηθεί στοιχεία.";
                        echo "</div>";
                        echo "</td></tr>";   
                    }
                    // more data ends

		echo "<tr><td>Α.Φ.Μ.</td><td>$afm</td><td>Α.Μ.</td><td>$am</td></tr>";
		echo "<tr><td>Κλάδος</td><td>".getKlados($klados_id,$mysqlconnection)."</td><td>Κατάσταση</td><td>$katast</td></tr>";
                $hm_mk = date ('d-m-Y', strtotime($hm_mk));
                if ($hm_mk > "01-01-1970")
                    echo "<tr><td>Βαθμός</td><td>$vathm</td><td>Μ.Κ.</td><td>$mk &nbsp;<small>(από $hm_mk)</small></td></tr>";
                else
                    echo "<tr><td>Βαθμός</td><td>$vathm</td><td>Μ.Κ.</td><td>$mk</td></tr>";
		echo "<tr><td>ΦΕΚ Διορισμού</td><td>$fek_dior</td><td>Ημ/νία Διορισμού</td><td>".date('d-m-Y',strtotime($hm_dior))."</td></tr>";
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
                
                // elegxos hm_anal me start_date apo misth
                /*
                $dt1 = strtotime($hm_anal);
                $dt2 = strtotime($start_misth);
                $diff = $dt1-$dt2;
                if (abs($diff) > 0)
                    echo "<tr><td>Ημ/νία Ανάληψης</td><td>".date('d-m-Y',strtotime($hm_anal))."<br><small>Μισθ.:".date('d-m-Y',strtotime($start_misth))."</small></td><td>Μεταπτυχιακό/Διδακτορικό</td><td>$met</td></tr>";
                else
                */
                    echo "<tr><td>Ημ/νία Ανάληψης</td><td>".date('d-m-Y',strtotime($hm_anal))."</td><td>Μεταπτυχιακό/Διδακτορικό</td><td>$met</td></tr>";
/*		
		// elegxos proyp eDC <-> Misth
                if (abs($proyp-$proyp_misth) > 15)
                {
                    $ymd=days2ymd($proyp);
                    $ymd1=  days2ymd($proyp_misth);
                    $temp = "<tr><td>Προϋπηρεσία</td><td>Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2]<br><small>Μισθ.: Έτη: $ymd1[0] &nbsp; Μήνες: $ymd1[1] &nbsp; Ημέρες: $ymd1[2] </small></td>";
                }
                else
                {
 */
                    $ymd=days2ymd($proyp);
                    $temp = "<tr><td>Προϋπηρεσία</td><td>Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2] </td>";
                //}
                $hm_dior_org = $hm_dior;
                // if hm_anal-hm_dior > 30
                //$dt1 = strtotime($hm_anal);
                //$dt2 = strtotime($hm_dior);
                //if (abs($dt1-$dt2) > 30)
                //    $hm_dior = $hm_anal;
                
		// 20-09-2013 - changed to hm_anal if diafora > 30 days
		$dt1 = strtotime($hm_anal);
                $dt2 = strtotime($hm_dior);
		$diafora = abs($dt1 - $dt2);
		$diafora = $diafora/86400;
		//echo $diafora;
		if ($diafora > 30)
			$d1 = strtotime($hm_anal);
		else
			$d1 = strtotime($hm_dior);
		
                // Met h/kai did MONO gia katataksi
	/*
                if ($met_did==1)
			$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 720;
		else if ($met_did==2)
			$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 2160;
		else if ($met_did==3)
			$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 2520;
		else
        */
                // 27-02-2014: add aney to anatr
		//$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp;
                $days_aney = 0;
                if ($aney && strtotime($aney_ews) > date("Y-m-d"))
                {
                    $dtd = strtotime(date('Y-m-d'));
                    $tod = date('d',$dtd) + date('m',$dtd)*30 + date('Y',$dtd)*360;
                    $apo = date('d',strtotime($aney_apo)) + date('m',strtotime($aney_apo))*30 + date('Y',strtotime($aney_apo))*360;
                    $days_aney = $tod - $apo;
                    if ($days_aney > 30)
                        $days_aney -= 30;
                    else $days_aney = 0;
                }
                $anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp + $aney_xr + $days_aney;
                
                //echo "anatr: $anatr // proyp: $proyp // aney_xr: $aney_xr // days_aney: $days_aney";
                
                // an eidikh periptwsh tote na parei anatrexei apo pinaka
                if ($eidikh)
                    $anatr = mysql_result($result, 0, "anatr_excel");

		//$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp ;
		$ymd = days2date($anatr);
		
		echo "$temp<td>Ανατρέχει</td><td>Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2] </td></tr>";
                
                // aney
                echo "<tr><td>Σε άδ.άνευ αποδοχών:</td><td>";
                if ($aney)
                    echo "<input type='checkbox' name='aney' checked disabled>";
                else
                    echo "<input type='checkbox' name='aney' disabled>";
                if ($aney && $aney_apo && $aney_ews)
                {
                    // date('d-m-Y',strtotime($hm_dior))
                    echo "<small>(Από ".date('d-m-Y',strtotime($aney_apo))." έως ".date('d-m-Y',strtotime($aney_ews)).")</small>";
                }
                $aney_ymd = days2ymd($aney_xr);
                echo "</td><td>Χρόνος σε άδ.άνευ αποδοχών:</td><td>$aney_ymd[0] έτη, $aney_ymd[1] μήνες, $aney_ymd[2] ημέρες</td></tr>";
                //
                echo "</td><td>Ώρες υποχρ. ωραρίου:</td><td colspan=3>$wres</td></tr>";
		echo "<tr><td>Σχόλια<br><br></td><td colspan='3'>".stripslashes($comments)."</td></tr>"; 
		echo "<tr><td>Σχ.Οργανικής</td><td><a href=\"../school/school_status.php?org=$sx_organ_id\">$sx_organikhs</a></td><td></td><td></td></tr>";
                
                // check if multiple schools
                //if ($multi)
                //{
                    $count = count($yphr_arr);
                    for ($i=0; $i<$count; $i++)
                    {
                        $sxoleia .=  "<a href=\"../school/school_status.php?org=$yphr_id_arr[$i]\">$yphr_arr[$i]</a> ($hours_arr[$i] ώρες)<br>";
                        $counthrs += $hours_arr[$i];
                    }
                    if ($count>1){
                        if ($counthrs > $wres){
                            echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3>$sxoleia<br><strong>$counthrs ώρες > $wres υποχρ.ωραρίου: ΣΦΑΛΜΑ! Παρακαλώ διορθώστε!!!</strong></td></tr>";
                        }
                        else {
                            echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3>$sxoleia<br><small>($counthrs ώρες σε $count Σχολεία)</small></td></tr>";
                        }
                    }
                    else {
                        echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3>$sxoleia</td></tr>";
                    }
                //}
                //else
                //{
                //    echo "<tr><td>Σχ.Υπηρέτησης</td><td colspan=3><a href=\"../school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a></td></tr>";
                //}
                
                $th = thesicmb($thesi);
                echo "<tr><td>Θέση</td><td colspan=3>$th</td></tr>";
                // history
                $hist_qry = "SELECT * FROM yphrethsh WHERE emp_id=$id AND sxol_etos<$sxol_etos";
                $hist_res = mysql_query($hist_qry, $mysqlconnection);
                if (mysql_num_rows($hist_res))
                {
                    echo "<tr><td><a href=\"#\" class=\"show_hide2\"><small>Εμφάνιση/Απόκρυψη<br>ιστορικού</small></a></td>";
                    echo "<td colspan=3><div class=\"slidingDiv2\">";
                    while ($row = mysql_fetch_array($hist_res, MYSQLI_ASSOC))
                        echo "Σχολ.έτος: ".$row['sxol_etos']." - Σχ.Υπηρέτησης : ".getSchool($row['yphrethsh'], $mysqlconnection)." (".$row['hours']." ώρες) - <small>Οργανική: ".getSchool($row['organikh'], $mysqlconnection)."</small><br>";
                            
                    echo "</div>";
                    echo "</td></tr>";
                        
                }
                // 
		// show changes for admin
                if ($updated > 0 && $usrlvl==0)
                {
                    $update_qry = "SELECT l.*, u.username from employee_log l JOIN logon u ON u.userid = l.userid WHERE emp_id=$id ORDER BY timestamp DESC";
                    $result_upd = mysql_query($update_qry, $mysqlconnection);
                                    
                    echo "<tr><td><a href=\"#\" class=\"show_hide3\"><small>Εμφάνιση/Απόκρυψη<br>μεταβολών</small></a></td>";
                    echo "<td colspan=3><div class=\"slidingDiv3\">";

                    while ($row = mysql_fetch_array($result_upd, MYSQL_ASSOC)) 
                            echo date("d-m-Y H:i", strtotime($row['timestamp']))." usr: ".$row['username']." - IP: ".$row['ip']." ".$row['query']."<br>";
                    
                    echo "</div>";
                    echo "</td></tr>";
                }
                echo "<tr><td colspan=4 align='right'><small>Τελευταία ενημέρωση: ".date("d-m-Y H:i", strtotime($updated))."</small></td></tr>";
		echo "<form id='yphrfrm' name='yphrfrm' action='' method='POST'>";
		echo "<tr><td>Χρόνος Υπηρεσίας έως</td><td>";
		$myCalendar = new tc_calendar("yphr", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date('d'), date('m'), date('Y'));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, 2030);
		$myCalendar->dateAllow("1970-01-01", date("2031-01-01"));
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
		echo "<br>";
		echo "<input type='hidden' name = 'anatr' value='$anatr'>";
                echo "<input type='hidden' name = 'vathm' value='$vathm'>";
                echo "<input type='hidden' name = 'mk' value='$mk'>";
                echo "<input type='hidden' name = 'met_did' value='$met_did'>";
		echo "<INPUT TYPE='submit' VALUE='Υπολογισμός'>";
		echo "<br>";
		echo "</form>";
		?>
		<div id='yphr_res'></div>
		<?php
		
		echo "</td>";
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
                                		
		echo "	</table>";
		
		// Find prev - next row id
		$qprev = "SELECT id FROM employee WHERE id < $id ORDER BY id DESC LIMIT 1";
		$res1 = mysql_query($qprev, $mysqlconnection);
		 if (mysql_num_rows($res1))
			$previd = mysql_result($res1, 0, "id");
		$qnext = "SELECT id FROM employee WHERE id > $id ORDER BY id ASC LIMIT 1";
		$res1 = mysql_query($qnext, $mysqlconnection);
		 if (mysql_num_rows($res1))
			$nextid = mysql_result($res1, 0, "id");
		
		
		//echo (existID($previd,$mysqlconnection));
		if ($previd)
			echo "	<INPUT TYPE='button' VALUE='<<' onClick=\"parent.location='employee.php?id=$previd&op=view'\">";
                echo "  <INPUT TYPE='submit' id='adeia' VALUE='Άδειες'>";
                if ($usrlvl < 3)
                    echo "	<INPUT TYPE='button' VALUE='Επεξεργασία' onClick=\"parent.location='employee.php?id=$id&op=edit'\">";
                echo "  <input type='button' value='Εκτύπωση' onclick='javascript:window.print()' />";
                echo "	<INPUT TYPE='button' VALUE='Επιστροφή στο προηγούμενο' onClick='history.go(-1);return true;'>";
                

		if ($nextid)
			echo "	<INPUT TYPE='button' VALUE='>>' onClick=\"parent.location='employee.php?id=$nextid&op=view'\">";
                // if idiwtikoi
                if ($thesi == 5)
                    echo "<br><br><INPUT TYPE='button' VALUE='Σελίδα ιδιωτικών' onClick=\"parent.location='idiwtikoi.php'\">";
                echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
		?>
                <div id="adeies"></div>
                <?php
                
                echo "    </center>";
		echo "</body>";
		echo "</html>";	
	}
	if ($_GET['op']=="delete")
	{
        $ip = $_SERVER['REMOTE_ADDR'];
		// Copies the to-be-deleted row to employee_deleted table for backup purposes.Also inserts a row on employee_del_log...
		$query1 = "INSERT INTO employee_deleted SELECT e.* FROM employee e WHERE id =".$_GET['id'];
		$result1 = mysql_query($query1, $mysqlconnection);
                $query1 = "INSERT INTO employee_log (emp_id, ip, userid, action) VALUES (".$_GET['id'].",$ip,".$_SESSION['userid'].", 2)";
		$result1 = mysql_query($query1, $mysqlconnection);
                $query = "DELETE from employee where id=".$_GET['id'];
		$result = mysql_query($query, $mysqlconnection);
		// Copies the deleted row to employee)deleted
		
		if ($result)
			echo "Η εγγραφή με κωδικό $id διαγράφηκε με επιτυχία.";
		else
			echo "Η διαγραφή απέτυχε...";
		echo "	<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
                echo "  <meta http-equiv=\"refresh\" content=\"2; URL=../index.php\">";
	}
	if ($_GET['op']=="add")
	{
        echo "<h3>Προσοχή: Παρακαλώ δώστε έγκυρα στοιχεία από τον προσωπικό φάκελο του εργαζομένου</h3><br>";
		echo "<form id='updatefrm' action='update.php' method='POST'>";
		echo "<table class=\"imagetable\" border='1'>";
                
                echo "<thead></thead><tbody>";
		
        echo "<tr><td>Επώνυμο</td><td><input type='text' name='surname' /></td></tr>";
        echo "<tr><td>Όνομα</td><td><input type='text' name='name' /></td></tr>";
		echo "<tr><td>Πατρώνυμο</td><td><input type='text' name='patrwnymo' /></td></tr>";
		echo "<tr><td>Μητρώνυμο</td><td><input type='text' name='mhtrwnymo' /></td></tr>";
		echo "<tr><td>Α.Φ.Μ.</td><td><input type='text' name='afm' /></td></tr>";
		echo "<tr><td>Α.Μ.</td><td><input type='text' name='am' /></td></tr>";
		echo "<tr><td>Κλάδος</td><td>";
		kladosCmb($mysqlconnection);
		echo "</td></tr>";
		echo "<tr><td>Βαθμός</td><td><input type='text' name='vathm' /></td></tr>";
		echo "<tr><td>Μ.Κ.</td><td><input type='text' name='mk' /></td></tr>";
		echo "<tr><td>ΦΕΚ Διορισμού</td><td><input type='text' name='fek_dior' /></td></tr>";
				
		echo "<tr><td>Ημ/νία Διορισμού</td><td>";
		$myCalendar = new tc_calendar("hm_dior", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";		
		
		echo "<tr><td>Ημ/νία ανάληψης</td><td>";
		$myCalendar = new tc_calendar("hm_anal", true);
		$myCalendar->setIcon("../tools/calendar/images/iconCalendar.gif");
		$myCalendar->setDate(date("d"), date("m"), date("Y"));
		$myCalendar->setPath("../tools/calendar/");
		$myCalendar->setYearInterval(1970, date("Y"));
		$myCalendar->dateAllow("1970-01-01", date("Y-m-d"));
		$myCalendar->setAlignment("left", "bottom");
		$myCalendar->disabledDay("sun,sat");
		$myCalendar->writeScript();
	  	echo "</td></tr>";		
				
		echo "<tr><td>Μεταπτυχιακό/Διδακτορικό</td><td>";
		metdidCombo(0);
		echo "<tr><td>Προϋπηρεσία</td><td><input type='text' name='proyp' size='3' />&nbsp;Έτη:<input type='text' name='pyears' size=1 />&nbsp;Μήνες:<input type='text' name='pmonths' size=1 />&nbsp;Ημέρες:<input type='text' name='pdays' size=1 /></td></tr>";
        echo "<tr><td>Σχόλια</td><td><textarea rows=4 cols=80 name='comments' ></textarea></td></tr>";
		 
		echo "<div id=\"content\">";
		echo "<form autocomplete=\"off\">";
		echo "<tr><td>Σχολείο Οργανικής";
                echo "<a href=\"\" onclick=\"window.open('help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"../images/help.gif\"/></a>";
                echo "</td><td><input type=\"text\" name=\"org\" id=\"org\" />";
		echo "<tr><td>Σχολείο Υπηρέτησης";
                //echo "<a href=\"\" onclick=\"window.open('help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"../images/help.gif\"/></a>";
                //echo "</td><td><input type=\"text\" name=\"yphr\" id=\"yphr\" />";
                echo "<a href=\"\" onclick=\"window.open('help/help.html#school','', 'width=400, height=250, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no'); return false\"><img style=\"border: 0pt none;\" src=\"../images/help.gif\"/></a>";
                echo "</td><td><input type=\"text\" name=\"yphr[]\" class=\"yphrow\" id=\"yphrow\" />";
                echo "&nbsp;&nbsp;<input type=\"text\" name=\"hours[]\" size=1 />";
                echo "&nbsp;<input class=\"addRow\" type=\"button\" value=\"Προσθήκη\" />";
                echo "<input class=\"delRow\" type=\"button\" value=\"Αφαίρεση\" />";
                
                thesiselectcmb(0);
		
		echo "</div>";
		echo "</tbody>";
		echo "	</table>";
                
		echo "	<input type='hidden' name = 'id' value='$id'>";
		// action = 1 gia prosthiki
		echo "  <input type='hidden' name = 'action' value='1'>";
                // status = 1 gia ergazetai
		echo "  <input type='hidden' name = 'status' value='1'>";
		echo "	<input type='submit' value='Αποθήκευση'>";
                echo "&nbsp;&nbsp;&nbsp;&nbsp;	<input type='submit' value='Αποθήκευση & εισαγωγή νέου' onClick=\"parent.location='employee.php?id=100&op=add'\">";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;	<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
		echo "	</form>";
?>
<div id='results'></div>
<?php
		echo "    </center>";
		echo "</body>";
		echo "</html>";
	}
	mysql_close();
?> 
