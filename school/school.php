<?php
	header('Content-type: text/html; charset=iso8859-7'); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=iso8859-7" />
		
		<title>�������</title>
		<style type="text/css" title="currentStyle">
			@import "../css/demo_page.css";
			@import "../css/demo_table.css";
		</style>
		<script type="text/javascript" language="javascript" src="../js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="../js/jquery.jeditable.js"></script>
		<script type="text/javascript" language="javascript" src="../js/datatables/jquery.dataTables.js"></script>
		<script type="text/javascript" charset="iso8859-7">
			$(document).ready(function() {
				/* Init DataTables */
				var oTable = $('#example').dataTable();
				
				/* Apply the jEditable handlers to the table */
				oTable.$('td').editable( '../tools/editable_ajax.php', {
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
        <h1>� ������ ����� ��� ���������...</h1>
	<body id="dt_example">
		<div id="container">
<?php
		require_once"../config.php";
		//require_once"../tools/functions.php";
		
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'greek'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
    
		$query = "SELECT * from school";
		$result = mysqli_query($mysqlconnection, $query);
		$num=mysqli_num_rows($result);
		$i=0;
?>
			<div id="demo">
<table cellpadding="0" cellspacing="0" border="1" class="display" id="example">
	<thead>
		<tr>
			<th>id</th>
			<th>�����</th>
                        <th style="min-width:200px">������</th>
			<th>���������</th>
                        <th>T.K.</th>
			<th>��������</th>
			<th>Fax</th>
			<th>e-mail</th>
                        <th>������������</th>
                        <th>���������������</th>
<!--                    <th>�������</th>
                        <th>��.�������</th>
                        <th>��.��������</th>
                        <th>��������.�����</th>
                        <th>��������</th>
-->
                        <th>������</th>
		</tr>
	</thead>
	<tbody>
<?php
		while ($i<$num)
		{
			$id = mysqli_result($result, $i, "id");
			$name = mysqli_result($result, $i, "name");
                        $titlos = mysqli_result($result, $i, "titlos");
			$address = mysqli_result($result, $i, "address");
                        $tk = mysqli_result($result, $i, "tk");
			$tel = mysqli_result($result, $i, "tel");
			$fax = mysqli_result($result, $i, "fax");
			$email = mysqli_result($result, $i, "email");
                        $organ = mysqli_result($result, $i, "organikothta");
                        $leitoyrg = mysqli_result($result, $i, "leitoyrg");
                        //$students = mysqli_result($result, $i, "students");
                        //$entaksis = mysqli_result($result, $i, "entaksis");
                        //$ypodoxis = mysqli_result($result, $i, "ypodoxis");
                        //$front = mysqli_result($result, $i, "frontistiriako");
                        //$olo = mysqli_result($result, $i, "oloimero");
                        $comm = mysqli_result($result, $i, "comments");
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
<INPUT TYPE='button' VALUE='���������' onClick="parent.location='../index.php'">
	</body>
</html>