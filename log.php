<?php
	header('Content-type: text/html; charset=iso8859-7'); 
        include("tools/class.login.php");
        $log = new logmein();
        if($log->logincheck($_SESSION['loggedin']) == false){
            header("Location: tools/login_check.php");
        }

 // check if super-user
  if ($_SESSION['userlevel']<>0)
     header("Location: index.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=iso8859-7" />
		
		<title>Employee Log Viewer</title>
		<style type="text/css" title="currentStyle">
			@import "css/demo_page.css";
			@import "css/demo_table.css";
		</style>
		<script type="text/javascript" language="javascript" src="js/jquery.js"></script>
		<script type="text/javascript" language="javascript" src="js/datatables/jquery.dataTables.js"></script>
		<script type="text/javascript" charset="iso8859-7">
			(function($) {
                        /*
                        * Function: fnGetColumnData
                        * Purpose:  Return an array of table values from a particular column.
                        * Returns:  array string: 1d data array
                        * Inputs:   object:oSettings - dataTable settings object. This is always the last argument past to the function
                        *           int:iColumn - the id of the column to extract the data from
                        *           bool:bUnique - optional - if set to false duplicated values are not filtered out
                        *           bool:bFiltered - optional - if set to false all the table data is used (not only the filtered)
                        *           bool:bIgnoreEmpty - optional - if set to false empty values are not filtered from the result array
                        * Author:   Benedikt Forchhammer <b.forchhammer /AT\ mind2.de>
                        */
                        $.fn.dataTableExt.oApi.fnGetColumnData = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
                            // check that we have a column id
                            if ( typeof iColumn == "undefined" ) return new Array();

                            // by default we only want unique data
                            if ( typeof bUnique == "undefined" ) bUnique = true;

                            // by default we do want to only look at filtered data
                            if ( typeof bFiltered == "undefined" ) bFiltered = true;

                            // by default we do not want to include empty values
                            if ( typeof bIgnoreEmpty == "undefined" ) bIgnoreEmpty = true;

                            // list of rows which we're going to loop through
                            var aiRows;

                            // use only filtered rows
                            if (bFiltered == true) aiRows = oSettings.aiDisplay;
                            // use all rows
                            else aiRows = oSettings.aiDisplayMaster; // all row numbers

                            // set up data array   
                            var asResultData = new Array();

                            for (var i=0,c=aiRows.length; i<c; i++) {
                                iRow = aiRows[i];
                                var aData = this.fnGetData(iRow);
                                var sValue = aData[iColumn];

                                // ignore empty values?
                                if (bIgnoreEmpty == true && sValue.length == 0) continue;

                                // ignore unique values?
                                else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;

                                // else push the value onto the result data array
                                else asResultData.push(sValue);
                            }

                            return asResultData;
                        }}(jQuery));


                        function fnCreateSelect( aData )
                        {
                            var r='<select><option value=""></option>', i, iLen=aData.length;
                            for ( i=0 ; i<iLen ; i++ )
                            {
                                r += '<option value="'+aData[i]+'">'+aData[i]+'</option>';
                            }
                            return r+'</select>';
                        }


                        $(document).ready(function() {
                            /* Initialise the DataTable */
                            var oTable = $('#example').dataTable( {
                                "oLanguage": {
                                    "sSearch": "Search all columns:"
                                }
                            } );

                            /* Add a select menu for each TH element in the table footer */
                            $("tfoot th").each( function ( i ) {
                                this.innerHTML = fnCreateSelect( oTable.fnGetColumnData(i) );
                                $('select', this).change( function () {
                                    oTable.fnFilter( $(this).val(), i );
                                } );
                            } );
                        } );
		</script>
	</head>
        <h1>Employee Log Viewer</h1>
	<body id="dt_example">
		<div id="container">
<?php
		require_once"config.php";
		//require_once"functions.php";
		
		$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
		mysql_select_db($db_name, $mysqlconnection);
		mysql_query("SET NAMES 'greek'", $mysqlconnection);
		mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
		//$query = "SELECT * from employee_log";
//                $query = "SELECT o.username,l.ip,l.timestamp,l.action,e.surname,e.am
//                            FROM employee_log l
//                            JOIN employee e ON e.id = l.emp_id
//                            JOIN logon o ON l.userid = o.userid";
//                $query = "SELECT l.emp_id,o.username,l.ip,l.timestamp,l.action
//                            FROM employee_log l
//                            JOIN logon o ON l.userid = o.userid";
                // changed 30-01-2013: limited to 100 last changes
//                $query = "SELECT l.emp_id, o.username, l.ip, l.timestamp, l.action
//                            FROM employee_log l
//                            JOIN logon o ON l.userid = o.userid
//                            ORDER BY l.timestamp DESC
//                            LIMIT 100 ";
                $query = "SELECT l.emp_id, e.am, e.surname, o.username, l.ip, l.timestamp, l.action,l.query
                            FROM employee_log l
                            JOIN logon o ON l.userid = o.userid
                            LEFT JOIN employee e ON e.id = l.emp_id
                            ORDER BY l.timestamp DESC
                            LIMIT 300 ";
		$result = mysql_query($query, $mysqlconnection);
		$num=mysql_numrows($result);
		$i=0;
                
                function action ($action)
                {
                    switch ($action){
                        case 0:
                            return "add";
                            break;
                        case 1:
                            return "edit";
                            break;
                        case 2:
                            return "delete";
                            break;
                    }
                }
?>
			<div id="demo">
<table cellpadding="0" cellspacing="0" border="1" class="display" id="example">
	<thead>
		<tr>
			<th>username</th>
			<th>hostname</th>
			<th>timestamp</th>
			<th>action</th>
                        <th>am</th>
                        <th>surname</th>
                        <th>change</th>                        
		</tr>
	</thead>
	<tbody>
<?php
		while ($i<$num)
		{
			$username = mysql_result($result, $i, "username");
			$ip = mysql_result($result, $i, "ip");
                        // gethostbyaddr was slooow and was removed
                        //if (strlen($ip)>0)
                        //    $ip = gethostbyaddr($ip);
			$timestamp = mysql_result($result, $i, "timestamp");
			$id = mysql_result($result, $i, "emp_id");
                        $action = mysql_result($result, $i, "action");
                        $change = mysql_result($result, $i, "query");
                        $am = mysql_result($result, $i, "am");
                        $surname = mysql_result($result, $i, "surname");
                        if ($action==2)
                        {
                            $qry = "SELECT surname,am FROM employee_deleted WHERE id=$id";
                            $res = mysql_query($qry, $mysqlconnection);
                            if (mysql_numrows($res)>0)
                            {
                                $surname = mysql_result($res, 0, "surname");
                                $am = mysql_result($res, 0, "am");
                            }
                        }
//                        else
//                        {
//                            $qry1 = "SELECT surname,am FROM employee WHERE id=$id";
//                            $res1 = mysql_query($qry1, $mysqlconnection);
//                            if (mysql_numrows($res1)==0)
//                            {
//                                $qry1 = "SELECT surname,am FROM employee_deleted WHERE id=$id";
//                                $res1 = mysql_query($qry1, $mysqlconnection);
//                            }
//                            $surname = mysql_result($res1, 0, "surname");
//                            $am = mysql_result($res1, 0, "am");
//                        }
			$act = action($action);
                        
			echo "\n<tr class='gradeA'>\n<td>$username</td>";
			echo "\n<td>$ip</td>";
			echo "\n<td>$timestamp</td>";
			echo "\n<td>$act</td>";
                        echo "\n<td>$am</td>";
                        echo "\n<td>$surname</td>";
                        echo "\n<td>$change</td>";
                        echo "\n</tr>";
			$i++;
		}
		//<tr id="3" class="gradeA"> <tr id="2" class="gradeC"><tr id="1" class="gradeX">
?>
		
	</tbody>
        <tfoot>
		<tr>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
                        <th></th>
		</tr>
	</tfoot>
	
</table>
			</div>
			<div class="spacer"></div>
			

		</div>
<INPUT TYPE='button' VALUE='Επιστροφή' onClick="parent.location='index.php'">
	</body>
</html>