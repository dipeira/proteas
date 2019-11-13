<?php
    header('Content-type: text/html; charset=utf-8'); 
    require_once"../config.php";
    require "../tools/class.login.php";
    $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
}

 // check if super-user
if ($_SESSION['userlevel']<>0) {
    header("Location: ../index.php");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        
        <title>Employee Log Viewer</title>
        <style type="text/css" title="currentStyle">
            @import "../css/demo_page.css";
            @import "../css/demo_table.css";
        </style>
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" language="javascript" src="../js/jquery.js"></script>
        <script type="text/javascript" language="javascript" src="../js/datatables/jquery.dataTables.js"></script>
        <script type="text/javascript">
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
    <body id="dt_example">
    <?php require '../etc/menu.php'; ?>
    <h1>Αρχείο Συμβάντων</h1>
        <div id="container">
<?php
        
        //require_once"functions.php";
        
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
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
        $result = mysqli_query($mysqlconnection, $query);
        $num=mysqli_num_rows($result);
        $i=0;
                
function action($action)
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
    $username = mysqli_result($result, $i, "username");
    $ip = mysqli_result($result, $i, "ip");
                      // gethostbyaddr was slooow and was removed
                      //if (strlen($ip)>0)
                      //    $ip = gethostbyaddr($ip);
    $timestamp = mysqli_result($result, $i, "timestamp");
    $id = mysqli_result($result, $i, "emp_id");
              $action = mysqli_result($result, $i, "action");
              $change = mysqli_result($result, $i, "query");
              $am = mysqli_result($result, $i, "am");
              $surname = mysqli_result($result, $i, "surname");
    if ($action==2) {
        $qry = "SELECT surname,am FROM employee_deleted WHERE id=$id";
        $res = mysqli_query($mysqlconnection, $qry);
        if (mysqli_num_rows($res)>0) {
                      $surname = mysqli_result($res, 0, "surname");
                      $am = mysqli_result($res, 0, "am");
        }
    }
        //                        else
        //                        {
        //                            $qry1 = "SELECT surname,am FROM employee WHERE id=$id";
        //                            $res1 = mysqli_query($mysqlconnection, $qry1)
        //                            if (mysqli_num_rows($res1)==0)
        //                            {
        //                                $qry1 = "SELECT surname,am FROM employee_deleted WHERE id=$id";
        //                                $res1 = mysqli_query($mysqlconnection, $qry1)
        //                            }
        //                            $surname = mysqli_result($res1, 0, "surname");
        //                            $am = mysqli_result($res1, 0, "am");
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
<INPUT TYPE='button' class='btn-red' VALUE='Επιστροφή' onClick="parent.location='../index.php'">
    </body>
</html>
