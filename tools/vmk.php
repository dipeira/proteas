<?php
    header('Content-type: text/html; charset=utf-8'); 
    Require "../config.php";
    Require "../include/functions.php";
    
    echo "vmk: Checks & fixes vathmos & mk of employee records for 01-11-2011";
        echo "<br>vmk.php?id=			number: checks specified id";
    echo "<br>				all: checks all records";
    echo "<br>				fixall: checks all & fixes wrong records (Careful!)";
    echo "<br>date=         		all the above for the given date<br><br>";
    
        
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
    $vathm = mysqli_result($result, $i, "vathm");
    $mk = mysqli_result($result, $i, "mk");
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
        $day1 = 724291;
    }
        
    if ($metdid==1) {
        $days = $day1 - $dior + $proyp + 720;
    } else if ($metdid==2) {
        $days = $day1 - $dior + $proyp + 2160;
    } else if ($metdid==3) {
        $days = $day1 - $dior + $proyp + 2520;
    } else {
        $days = $day1 - $dior + $proyp;
    }
        
    //$days = $day1 - $dior + $proyp;
    //$d1 = strtotime($hm_dior);
    //$anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp ;
        
    //$vath = vathmos($days);
               $vath[0] = $vathm;
    $mk1 = mk($days, $vath);
               
    echo "<br>id: $id,&nbsp;&nbsp;hmdior, days: $hm_dior, $dior";
    echo "&nbsp;&nbsp;proyp: $proyp";
    echo "&nbsp;&nbsp;vathm: $vathm";
    echo "&nbsp;&nbsp;mk (vash): $mk";
    echo "&nbsp;&nbsp;days: $days";
    echo "&nbsp;&nbsp;Βαθμός: $vath[0]";
    echo "&nbsp;&nbsp;MK: $mk1";
        
    if (strcmp($vathm, $vath[0])!=0) {
        echo "&nbsp;&nbsp;wrong vathmos";
        $notvathmos=1;
                        $wrongvathmos+=1;
    }
    else {
        echo "&nbsp;&nbsp;vathmos OK!";
    }
    if ($mk!=$mk1) {
        echo "&nbsp;&nbsp;wrong mk";
        $notmk=1;
                        $wrongmk+=1;
    }
    else {
        echo "&nbsp;&nbsp;mk OK!";
    }
        
    if ((strcmp($_GET['id'], 'fixall')==0) && ($notvathmos || $notmk)) {
        $query = "UPDATE employee SET vathm='$vath[0]', mk='$mk1' WHERE ID=$id";
        echo "&nbsp;&nbsp;$query";
        mysqli_query($mysqlconnection, $query);
        $fix+=1;
    }
    //echo "<br>";
    $i++;
    $notmk=$notvathmos=0;
}
if ($wrongmk || $wrongvathmos) {
    echo "<br><br>Λάθος Βαθμός: $wrongvathmos, Λάθος ΜΚ: $wrongmk";
} else {
    echo "<br><br>Όλες οι εγγραφές ($num) είναι έγκυρες...";
}
                
if ($fix) {
    echo "<br>Fixed $fix record(s)";
}
                
  mysqli_close($mysqlconnection);
?>
