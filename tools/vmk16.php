<?php
    header('Content-type: text/html; charset=utf-8'); 
    Require "../config.php";
    Require "../functions.php";
        session_start();
        ini_set('max_execution_time', 300);  //300 seconds = 5 minutes
    
    echo "vmk16: Checks & fixes vathmos & mk of employee records for 01-01-2016 (N.4354/2015)";
        echo "<br>vmk.php?id=			number: checks specified id";
    echo "<br>				all: checks all records";
    echo "<br>date=         		all the above for the given date<br><br>";
        
        // if not admin, exit...
if ($_SESSION['userlevel'] > 0) {
    die('Not Permitted...');
}
        
        $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
        mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
        mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

if ((isset($_GET['id'])) && is_numeric($_GET['id'])) {
    $query = "SELECT * from employee where id=".$_GET['id'];
} else {
    $query = "SELECT * from employee";
}

        echo $query;
        $result = mysqli_query($mysqlconnection, $query);
        $num=mysqli_num_rows($result);
        
while ($i<$num)    
{
           $id = mysqli_result($result, $i, "id");
           $hm_dior = mysqli_result($result, $i, "hm_dior");
           $proyp = mysqli_result($result, $i, "proyp");
           $metdid = mysqli_result($result, $i, "met_did");

           $hm_dior1 = strtotime($hm_dior);
           $dior = (date('d', $hm_dior1) + date('m', $hm_dior1)*30 + date('Y', $hm_dior1)*360);

    if (isset($_GET['date'])) {
        $tmp = strtotime($_GET['date']);
        $day1 = (date('d', $tmp) + date('m', $tmp)*30 + date('Y', $tmp)*360);
    }
    else {
        // 01-01-2016 in days...
        $day1 = 725791;
    }

           // met: 4y, did: 12y, m+d: 12y
    if ($metdid==1) {
        $days = $day1 - $dior + $proyp + 1440;
    } else if ($metdid==2) {
        $days = $day1 - $dior + $proyp + 4320;
    } else if ($metdid==3) {
        $days = $day1 - $dior + $proyp + 4320;
    } else {
               $days = $day1 - $dior + $proyp;
    }

           // call mk16
           $mk16 = mk16($days);

           echo "<br>id: $id,&nbsp;&nbsp;hmdior (days): $hm_dior ($dior)";
           echo "&nbsp;&nbsp;proyp: $proyp";
           echo "&nbsp;&nbsp;days: $days";
           echo "&nbsp;&nbsp;MK: $mk16";

           $query = "UPDATE employee SET mk='$mk16' WHERE ID=$id";
           echo "&nbsp;&nbsp;$query";
           mysqli_query($mysqlconnection, $query);
           $fix+=1;
           $i++;
}
if ($fix) {
            echo "<br>Updated $fix record(s)";
}
                
    mysqli_close($mysqlconnection);
?>
