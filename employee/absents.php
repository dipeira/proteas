<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"../config.php";
  require_once "../tools/functions.php";
  //define("L_LANG", "el_GR"); Needs fixing
  
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
  // Demand authorization                
  include("../tools/class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false){
    header("Location: tools/login_check.php");
  }
?>
<html>
  <head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>Εκπ/κοί που βρίσκονται σε άδεια</title>
	
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery.validate.js"></script>
        <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
	<script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
	<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
	<script type="text/javascript">
	
	$().ready(function() {
			$("#org").autocomplete("get_school.php", {
				width: 260,
				matchContains: true,
				//mustMatch: true,
				//minChars: 0,
				//multiple: true,
				//highlight: false,
				//multipleSeparator: ",",
				selectFirst: false
			});
		});
	$(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
                        $("#mytbl2").tablesorter({widgets: ['zebra']});
                        $("#mytbl3").tablesorter({widgets: ['zebra']});
                        $("#mytbl4").tablesorter({widgets: ['zebra']});
                        $("#mytbl5").tablesorter({widgets: ['zebra']});
                        $("#mytbl6").tablesorter({widgets: ['zebra']});
		});	

	</script>
  </head>
  <body> 
    <center>
        <h2>Εκπ/κοί που βρίσκονται σε άδεια</h2>
      <?php

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
            $result = mysql_query($query, $mysqlconnection);
            $num = mysql_num_rows($result);
            if ($num)
            {
            $i=0;
            echo "<table id=\"mytbl6\" class=\"imagetable tablesorter\" border=\"2\">\n";
                echo "<thead><tr>";
                echo "<th>Επώνυμο</th>";
                echo "<th>Όνομα</th>";
                echo "<th>Κλάδος</th>";
                echo "<th>Τύπος</th>";
                echo "<th>Ημ/νία Επιστροφής</th>";
                echo "<th>Σχόλια</th>";
                echo "</tr></thead>\n<tbody>";
                $apontes = array();
            while ($i < $num)
                {
                        $flag = $absent = 0;

                        $id = mysql_result($result, $i, "emp_id");
                        $adeia_id = mysql_result($result, $i, "id");
                        $type = mysql_result($result, $i, "type");
                        $name = mysql_result($result, $i, "name");
                        $surname = mysql_result($result, $i, "surname");
                        $klados_id = mysql_result($result, $i, "klados");
                        $klados = getKlados($klados_id,$mysqlconnection);
                        $comments = mysql_result($result, $i, "comments");
                        $comm = $comments;
                        $today = date("Y-m-d");
                        $return = mysql_result($result, $i, "finish");
                        $start = mysql_result($result, $i, "start");
                        $status = mysql_result($result, $i, "status");
                        // if return date exists, check if absent and print - else continue.

                        if ($start<$today && $return>$today)
                        {
                            $flag = $absent = 1;
                            $apontes[] = $id;
                        }
                        else
                        {
                                //$flag=1;
                                $ret="";
                                if (!in_array($id,$apontes))
                                        $flag = 1;
                                $apontes[] = $id;
                                $comments = "Δεν απουσιάζει.<br>Έχει δηλωθεί κατάσταση \"Σε άδεια\"<br>";
                        }
                            $ret = date("d-m-Y", strtotime($return));


                        //echo "OK: $i - $start>$today>$return - fl:$flag<br>";
                        if ($flag)
                        {
                            $query1 = "select type from adeia_type where id=$type";
                            $result1 = mysql_query($query1, $mysqlconnection);
                            $typewrd = mysql_result($result1, 0, "type");
                            if ($absent && $status<>3)
                                $comments = "<blink>Παρακαλώ αλλάξτε την κατάσταση του <br>εκπ/κού σε \"Σε Άδεια\"</blink><br>$comm";
                            //if ($absent)
                            //    continue;
                            echo "<tr>";
                            echo "<td><a href=\"employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>$typewrd</td><td><a href='adeia.php?adeia=$adeia_id&op=view'>$ret</a></td><td>$comments</td>\n";
                            echo "</tr>";
                        }
                        $i++;
                }
                echo "</tbody></table>";
                echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
            }
                    

?>
		</center>
		</body>
		</html>

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