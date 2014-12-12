<?php
        header('Content-type: text/html; charset=iso8859-7'); 
        session_start();
        //require_once "config.php";

function misth_elements($afm)
{
    $dbName = $_SERVER["DOCUMENT_ROOT"] . "/access/DTnetNproto.mdb";
    
    if (!file_exists($dbName)) {
        die("Could not find database file.");
    }
    $db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=$dbName; Uid=; Pwd=;");

    $query = "SELECT * FROM elements WHERE AFM = '$afm'";
    //$query = "SELECT Lname FROM elements WHERE cod = '000_0001'";
    //echo $query;
    $result = $db->query($query);
    
    $count = count($db->query($query)->fetchAll()); 

    //$count = $result->columnCount();
    //echo "<br>count: $count";
    if ($count > 0)
    {
        if ($count > 1)
        {
            $row = $result->fetchAll();
            $res['telef'] = $row[0]['telef']."  ".$row[1]['telef'];
            if (strlen($row[0]['city']) >= strlen($row[1]['city']))
              $res['city'] = $row[0]['city'];
            else
              $res['city'] = $row[1]['city'];
            if (strlen($row[0]['street']) >= strlen($row[1]['street']))
              $res['street'] = $row[0]['street'];
            else
              $res['street'] = $row[1]['street'];
            if (strlen($row[0]['numStr']) >= strlen($row[1]['numStr']))
              $res['numStr'] = $row[0]['numStr'];
            else
              $res['TK'] = $row[1]['TK'];
            if (strlen($row[0]['TK']) >= strlen($row[1]['TK']))
              $res['TK'] = $row[0]['TK'];
            else
              $res['TK'] = $row[1]['TK'];
            if (strlen($row[0]['idNum']) >= strlen($row[1]['idNum']))
              $res['idNum'] = $row[0]['idNum'];
            else
              $res['idNum'] = $row[1]['idNum'];
            if (strlen($row[0]['AMKA']) >= strlen($row[1]['AMKA']))
              $res['AMKA'] = $row[0]['AMKA'];
            else
              $res['AMKA'] = $row[1]['AMKA'];
        }
        else
        {
            $row = $result->fetch();
            $res['city'] = $row['city'];
            $res['street'] = $row['street'];
            $res['numStr'] = $row['numStr'];
            $res['TK'] = $row['TK'];
            $res['telef'] = $row['telef'];
            $res['idNum'] = $row['idNum'];
            $res['AMKA'] = $row['AMKA'];
        }
    }
    return $res;
}

function misth_elements_mysql($afm)
{
    // show more personal data
    //misth: city, street, num, tk
    $query0 = "SELECT * from misth where afm1=".$afm;
    $result0 = mysql_query($query0, $mysqlconnection);
    $city = mysql_result($result0, 0, "city");
    $street = mysql_result($result0, 0, "street");
    $numbr = mysql_result($result0, 0, "num");
    $tk = mysql_result($result0, 0, "tk");

    //misth2: id, tel, amka
    $query0 = "SELECT * from misth2 where afm=".$afm;
    $result0 = mysql_query($query0, $mysqlconnection);
    $num2 = mysql_num_rows($result0);
    $z=0;
    if ($num2>1)
    {
        $tel = mysql_result($result0, 0, "tel")." ".mysql_result($result0, 1, "tel");

        $idnum1 = mysql_result($result0, 0, "idnum");
        $idnum2 = mysql_result($result0, 1, "idnum");
        if (strlen($idnum1) >= strlen($idnum2))
            $idnum = $idnum1;
        else
            $idnum = $idnum2;

        $amka1 = mysql_result($result0, 0, "amka");
        $amka2 = mysql_result($result0, 1, "amka");
        if (strlen($amka1) >= strlen($amka2))
            $amka = $amka1;
        else
            $amka = $amka2;
    }
    else
    {
        $tel = mysql_result($result0, 0, "tel");
        $idnum = mysql_result($result0, 0, "idnum");
        $amka = mysql_result($result0, 0, "amka");
    }
}
//$afm = "023154986"; //1 res
//$afm = "017710481"; //3 res
//$afm = "032977000";
//$res = misth_elements($afm);
//print_r($res);
?>
