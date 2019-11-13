<?php
    header('Content-type: text/html; charset=utf-8'); 
    Require "../config.php";
    Require "../functions.php";
    
    
        
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
        
                $query = "SELECT * from employee";
                
                echo $query;
        $result = mysqli_query($mysqlconnection, $query);
        $num=mysqli_num_rows($result);
while ($i<$num)    
{
    $id = mysqli_result($result, $i, "id");
               $name = mysqli_result($result, $i, "name");
               $surname = mysqli_result($result, $i, "surname");
    $hm_dior = mysqli_result($result, $i, "hm_dior");
    $proyp = mysqli_result($result, $i, "proyp");
    $met_did = mysqli_result($result, $i, "met_did");
               $anatr_db = mysqli_result($result, $i, "anatr");
        
        
               $d1 = strtotime($hm_dior);
        
               // Met/Did MONO gia katataksi...
    /* if ($met_did==1)
    $anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 720;
    else if ($met_did==2)
    $anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 2160;
    else if ($met_did==3)
    $anatr = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $proyp - 2520;
    else
               */
    $anatr = (date('d', $d1) + date('m', $d1)*30 + date('Y', $d1)*360) - $proyp;
        
                
    echo "<br>id: $id,&nbsp;&nbsp;on/mo: $surname, $name &nbsp;&nbsp;hmdior: $hm_dior";
    echo "&nbsp;&nbsp;proyp: $proyp";
    echo "&nbsp;&nbsp;anatr: $anatr";
    if ($anatr != $anatr_db) {
        echo "&nbsp;&nbsp;ALLAGH";
    }
        
        
    if ((strcmp($_GET['id'], 'fixall')==0) && ($anatr!=$anatr_db)) {
        $query = "UPDATE employee SET anatr='$anatr' WHERE ID=$id";
        echo "&nbsp;&nbsp;$query";
        mysqli_query($mysqlconnection, $query);
        $fix+=1;
    }
    //echo "<br>";
    $i++;
        
}
                
if ($fix) {
    echo "<br>Fixed $fix record(s)";
}
                
    mysqli_close($mysqlconnection);
?>
