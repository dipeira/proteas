<?php
	header('Content-type: text/html; charset=utf-8'); 
	require_once "config.php";
	require_once "functions.php";
	require('calendar/tc_calendar.php');  
         
?>	
  <html>
  <head>      
        <LINK href="style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
        <script type="text/javascript">   
            $(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		});
        </script>
        <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
        
<?php        
	
        include("tools/class.login.php");
        $log = new logmein();
        if($log->logincheck($_SESSION['loggedin']) == false){
            header("Location: ../tools/login.php");
        }
        $usrlvl = $_SESSION['userlevel'];
        if ($usrlvl)
            die('Insufficient privileges');
        
        // set max execution time 
        set_time_limit (180);
            
		$updates = $fails = 0;
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
		//$query = "SELECT * from employee";
                $query = "select e.surname,e.name,e.wres, y.hours, y.id from employee e join yphrethsh y on e.id = y.emp_id where sxol_etos = 201415";
		$result = mysqli_query($mysqlconnection, $query);
		$num=mysqli_num_rows($result);
                		
                $synolo = $num;
                
            while ($i<$num)	
            {
		$name = mysqli_result($result, $i, "name");
		$surname = mysqli_result($result, $i, "surname");
    $ypoxr_wres = mysqli_result($result, $i, "wres");
    
    $yphr_hours = mysqli_result($result, $i, "hours");
    $yphr_id = mysqli_result($result, $i, "id");
    
    if ($ypoxr_wres && ($yphr_hours > $ypoxr_wres))
    {
        $updates++;
        $qry = "update yphrethsh set hours=$ypoxr_wres where id=$yphr_id";
        $res = mysqli_query($mysqlconnection, $qry);
        if (!res)
        {
            die ('Error: '.mysqli_error ($mysqlconnection));
            $fails++;
        }
        echo "$surname\tYpoxr:$ypoxr_wres,Yphr:$yphr_hours ".$qry."<br>";
    }
                
		$i++;
            }
		
                echo "<br>";
                
    mysqli_close($mysqlconnection);
                
                echo " $updates   .. (  $synolo )";
                if ($fails)
                    echo "<br>$fails .";
                

	//}
?>
<br><br>
<a href="index.php"></a>
</html>