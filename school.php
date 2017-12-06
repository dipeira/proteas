<?php
	header('Content-type: text/html; charset=iso8859-7'); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=iso8859-7" />
		
		<title>Σχολείο</title>
		<style type="text/css" title="currentStyle">
			@import "css/demo_page.css";
			@import "css/demo_table.css";
		</style>
		<script type="text/javascript" language="javascript" src="js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="js/jquery.jeditable.js"></script>
		<script type="text/javascript" language="javascript" src="js/datatables/jquery.dataTables.js"></script>
		<script type="text/javascript" charset="iso8859-7">
			$(document).ready(function() {
				/* Init DataTables */
				var oTable = $('#example').dataTable();
				
				/* Apply the jEditable handlers to the table */
				oTable.$('td').editable( 'tools/editable_ajax.php', {
					"callback": function( sValue, y ) {
						var aPos = oTable.fnGetPosition( this );
						oTable.fnUpdate( sValue, aPos[0], aPos[1] );
					},
					"submitdata": function ( value, settings ) {
						return {
							"row_id": this.parentNode.getAttribute('id'),
							"column": oTable.fnGetPosition( this )[2]
						};
					},
					"height": "14px",
					"width": "100%"
				} );
			} );
		</script>
	</head>
        <h1>Η σελίδα είναι υπό κατασκευή...</h1>
	<body id="dt_example">
		<div id="container">
<?php
		require_once"config.php";
		//require_once"functions.php";
		
		$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $mysqlconnection);
		mysql_query("SET NAMES 'greek'", $mysqlconnection);
		mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
		$query = "SELECT * from school";
		$result = mysql_query($query, $mysqlconnection);
		$num=mysql_numrows($result);
		$i=0;
?>
			<div id="demo">
<table cellpadding="0" cellspacing="0" border="1" class="display" id="example">
	<thead>
		<tr>
			<th>id</th>
			<th>Όνομα</th>
                        <th style="min-width:200px">Τίτλος</th>
			<th>Διεύθυνση</th>
                        <th>T.K.</th>
			<th>Τηλέφωνο</th>
			<th>Fax</th>
			<th>e-mail</th>
                        <th>Οργανικότητα</th>
                        <th>Λειτουργικότητα</th>
<!--                    <th>Μαθητές</th>
                        <th>Τμ.Ένταξης</th>
                        <th>Τμ.Υποδοχής</th>
                        <th>Φροντιστ.Τμήμα</th>
                        <th>Ολοήμερο</th>
-->
                        <th>Σχόλια</th>
		</tr>
	</thead>
	<tbody>
<?php
		while ($i<$num)
		{
			$id = mysql_result($result, $i, "id");
			$name = mysql_result($result, $i, "name");
                        $titlos = mysql_result($result, $i, "titlos");
			$address = mysql_result($result, $i, "address");
                        $tk = mysql_result($result, $i, "tk");
			$tel = mysql_result($result, $i, "tel");
			$fax = mysql_result($result, $i, "fax");
			$email = mysql_result($result, $i, "email");
                        $organ = mysql_result($result, $i, "organikothta");
                        $leitoyrg = mysql_result($result, $i, "leitoyrg");
                        //$students = mysql_result($result, $i, "students");
                        //$entaksis = mysql_result($result, $i, "entaksis");
                        //$ypodoxis = mysql_result($result, $i, "ypodoxis");
                        //$front = mysql_result($result, $i, "frontistiriako");
                        //$olo = mysql_result($result, $i, "oloimero");
                        $comm = mysql_result($result, $i, "comments");
			echo "<tr id='$id' class='gradeA'>\n<td>$id</td>";
			echo "\n<td>$name</td>";
                        echo "\n<td>$titlos</td>";
			echo "\n<td>$address</td>";
                        echo "\n<td>$tk</td>";
			echo "\n<td>$tel</td>";
			echo "\n<td>$fax</td>";
			echo "\n<td>$email</td>";
                        echo "\n<td>$organ</td>";
                        echo "\n<td>$leitoyrg</td>";
                        //echo "\n<td>$students</td>";
                        //echo "\n<td>$entaksis</td>";
                        //echo "\n<td>$ypodoxis</td>";
                        //echo "\n<td>$front</td>";
                        //echo "\n<td>$olo</td>";
                        echo "\n<td>$comm</td>";
                        echo "\n</tr>";
			$i++;
		}
		//<tr id="3" class="gradeA"> <tr id="2" class="gradeC"><tr id="1" class="gradeX">
?>
		
	</tbody>
	
</table>
			</div>
			<div class="spacer"></div>
			

		</div>
<INPUT TYPE='button' VALUE='Επιστροφή' onClick="parent.location='index.php'">
	</body>
</html>