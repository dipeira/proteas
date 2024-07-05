<?php

function kladosCombo($klados,$conn)
{
    $query = "SELECT * from klados ORDER BY perigrafh";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        echo "Δε βρέθηκαν αποτελέσματα...";
        return;
    }
    $num=mysqli_num_rows($result);
    echo "<select name=\"klados\" id=\"klados\">";
    echo "<option value='' selected>(Επιλογή:)</option>";
    while ($i < $num) 
    {
        $id=mysqli_result($result, $i, "id");
        $per=mysqli_result($result, $i, "perigrafh");
        if (strcmp($klados, $id)==0) {
            echo "<option value=\"".$id."\" selected=\"selected\">".$per."</option>";
        } else {
            echo "<option value=\"".$id."\">".$per."</option>";
        }
        $i++;
    }
    echo "</select>";
}

function kladosCmb($conn)
{
    $query = "SELECT * from klados ORDER BY perigrafh";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        echo "Δε βρέθηκαν αποτελέσματα...";
        return;
    }
    $i = 0;
    $num=mysqli_num_rows($result);
    echo "<select style='max-width: 97px;' name=\"klados\" id=\"klados\">";
    echo "<option value='' selected>(Επιλογή:)</option>";
    while ($i < $num) 
    {
        $id=mysqli_result($result, $i, "id");
        $per=mysqli_result($result, $i, "perigrafh");
        $onoma=mysqli_result($result, $i, "onoma");
        echo "<option value=\"".$id."\">".$per.", ".$onoma."</option>";
        $i++;
    }
    echo "</select>";
}

function typeCmb($conn)
{
    $query = "SELECT * from ektaktoi_types";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        echo "Δε βρέθηκαν αποτελέσματα...";
        return;
    }
    $num=mysqli_num_rows($result);
    echo "<select name=\"type\" id=\"type\">";
    echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
    while ($i < $num) 
    {
        $id=mysqli_result($result, $i, "id");
        $type=mysqli_result($result, $i, "type");
        echo "<option value=\"".$id."\">".$type."</option>";
        $i++;
    }
    echo "</select>";
}

function typeCmb1($typeinp,$conn)
{
    $query = "SELECT * from ektaktoi_types";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        echo "Δε βρέθηκαν αποτελέσματα...";
        return;
    }
    $num=mysqli_num_rows($result);
            $type1 = get_type($typeinp, $conn);
    echo "<select name=\"type\" id=\"type\">";
    echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
    while ($i < $num) 
    {
        $id=mysqli_result($result, $i, "id");
        $type=mysqli_result($result, $i, "type");
        if ($type1 == $type) {
            echo "<option value=\"$id\" selected>".$type."</option>";
        } else {
                echo "<option value=\"".$id."\">".$type."</option>";
        }
        $i++;
    }
    echo "</select>";
}

function vathmosCmb($conn)
{
    echo "<select name=\"vathm\">";
    echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
    echo "<option value=\"Α\">Α</option>";
    echo "<option value=\"Β\">Β</option>";
    echo "<option value=\"Γ\">Γ</option>";
    echo "<option value=\"Δ\">Δ</option>";
    echo "</select>";
}

function vathmosCmb1($v, $conn)
{
    echo "<select name=\"vathm\">";
    if (strcmp($v, 'Α')==0) {
      echo "<option value=\"Α\" selected>Α</option>";
    } else {
        echo "<option value=\"Α\">Α</option>";
    }
    if (strcmp($v, 'Β')==0) {
      echo "<option value=\"Β\" selected>Β</option>";
    } else {
        echo "<option value=\"Β\">Β</option>";
    }
    if (strcmp($v, 'Γ')==0) {
      echo "<option value=\"Γ\" selected>Γ</option>";
    } else {
        echo "<option value=\"Γ\">Γ</option>";
    }
    if (strcmp($v, 'Δ')==0) {
      echo "<option value=\"Δ\" selected>Δ</option>";
    } else {
        echo "<option value=\"Δ\">Δ</option>";
    }
    echo "</select>";
}

function taksiCmb()
{
    echo "<select name=\"taksi\">";
    echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
    echo "<option value=\"1\">Α</option>";
    echo "<option value=\"2\">Β</option>";
    echo "<option value=\"3\">Γ</option>";
    echo "<option value=\"4\">Δ</option>";
    echo "<option value=\"5\">Ε</option>";
    echo "<option value=\"6\">ΣΤ</option>";
    echo "</select>";
}

function taksiCmb1($t)
{
    echo "<select name=\"taksi\">";
    if ($t == 1) {
        echo "<option value=\"1\" selected>Α</option>";
    } else {
        echo "<option value=\"1\">Α</option>";
    }
    if ($t == 2) {
        echo "<option value=\"2\" selected>Β</option>";
    } else {
        echo "<option value=\"2\">Β</option>";
    }
    if ($t == 3) {
        echo "<option value=\"3\" selected>Γ</option>";
    } else {
        echo "<option value=\"3\">Γ</option>";
    }
    if ($t == 4) {
        echo "<option value=\"4\" selected>Δ</option>";
    } else {
        echo "<option value=\"4\">Δ</option>";
    }
    if ($t == 5) {
        echo "<option value=\"5\" selected>Ε</option>";
    } else {
        echo "<option value=\"5\">Ε</option>";
    }
    if ($t == 6) {
        echo "<option value=\"6\" selected>ΣΤ</option>";
    } else {
        echo "<option value=\"6\">ΣΤ</option>";
    }
    echo "</select>";
}

function metdidCombo($met_did = 0)
{
    echo "<select name=\"met_did\">";
    if ($met_did == 0) {
        echo "<option value='0' selected=\"selected\">Όχι</option>";
        echo "<option value='1'>Μεταπτυχιακό</option>";
        echo "<option value='2'>Διδακτορικό</option>";            
        echo "<option value='3'>Μετ. & Διδ.</option>";
    }
    elseif ($met_did == 1) {
        echo "<option value='0'></option>";
        echo "<option value='1' selected=\"selected\">Μεταπτυχιακό</option>";
        echo "<option value='2'>Διδακτορικό</option>";
        echo "<option value='3'>Μετ. & Διδ.</option>";
    }
    elseif ($met_did == 2) {
        echo "<option value='0'></option>";
        echo "<option value='1'>Μεταπτυχιακό</option>";
        echo "<option value='2' selected=\"selected\">Διδακτορικό</option>";            
        echo "<option value='3'>Μετ. & Διδ.</option>";
    }
    elseif ($met_did == 3) {
        echo "<option value='0'></option>";
        echo "<option value='1'>Μεταπτυχιακό</option>";
        echo "<option value='2'>Διδακτορικό</option>";            
        echo "<option value='3' selected=\"selected\">Μετ. & Διδ.</option>";
    }
    echo "</select>";
}

function opsel()
{
    echo "<select name=\"op\">";
    echo "<option value=\"=\" selected>=</option>";
    echo "<option value=\">\" >></option>";
    echo "<option value=\"<\" ><</option>";
    echo "</select>";
}

function thesicmb($thesi)
{
    switch ($thesi)
    {
    case 0:
        $th = "Εκπαιδευτικός";
        break;
    case 1:
        $th = "Υποδιευθυντής";
        break;
    case 2:
        $th = "Διευθυντής/Προϊστάμενος";
        break;
    case 4:
        $th = "Διοικητικός";
        break;
    case 5:
        $th = "Ιδιωτικός";
        break;
    case 6:
        $th = "Δ/ντής-Πρ/νος Ιδιωτικού Σχ.";
        break;
    }
    return $th;
}

function thesiselectcmb($thesi,$hasblank = false)
{
    echo "<td>";
    echo "<div class='tooltip'>Θέση";
    echo "<span class='tooltiptext'>Επιλέξτε ένα από: Εκπαιδευτικός, Υποδιευθυντής, Διευθυντής/Προϊστάμενος, Διοικητικός, Ιδιωτικός, Δ/ντής-Πρ/νος Ιδιωτικού Σχ.</span>";
    echo "</div>";
    echo "</td><td>";
    echo $hasblank ? "</td><td>" : '';
    echo "<select name=\"thesi\">";
    if ($thesi == 0) {
        echo "<option value='0' selected=\"selected\">Εκπαιδευτικός</option>";
    } else {
        echo "<option value='0'>Εκπαιδευτικός</option>";
    }
    if ($thesi == 1) {
        echo "<option value='1' selected=\"selected\">Υποδιευθυντής</option>";
    } else {
        echo "<option value='1'>Υποδιευθυντής</option>";
    }
    if ($thesi == 2) {
        echo "<option value='2' selected=\"selected\">Διευθυντής/Προϊστάμενος</option>";    
    } else {
        echo "<option value='2'>Διευθυντής/Προϊστάμενος</option>";
    }
    if ($thesi == 4) {
        echo "<option value='4' selected=\"selected\">Διοικητικός</option>";    
    } else {
        echo "<option value='4'>Διοικητικός</option>";
    }
    if ($thesi == 5) {
        echo "<option value='5' selected=\"selected\">Ιδιωτικός</option>";    
    } else {
        echo "<option value='5'>Ιδιωτικός</option>";
    }
    if ($thesi == 6) {
        echo "<option value='6' selected=\"selected\">Δ/ντής-Πρ/νος Ιδιωτικού Σχ.</option>";    
    } else {
        echo "<option value='6'>Δ/ντής-Πρ/νος Ιδιωτικού Σχ.</option>";
    }
    echo "</td>";
}

function ent_ty_cmb($entty)
{
    switch ($entty)
    {
    case 0:
        $th = "Καμία";
        break;
    case 1:
        $th = "Τμήμα Ένταξης";
        break;
    case 2:
        $th = "Τάξη Υποδοχής";
        break;
    case 3:
        $th = "Παράλληλη στήριξη";
        break;
    }
    return $th;
}

function ent_ty_selectcmb($entty,$hasblank = false, $isanapl = false, $has_space = false, $has_all = false)
{
    echo "<td>";
    echo "<div class='tooltip'>Υπηρέτηση σε Τμήμα Ένταξης <br>/ Τάξη Υποδοχής";
    echo $isanapl ? " / Παράλληλη στήριξη" : '';
    echo "<span class='tooltiptext'>Επιλέξτε ένα από: Καμία, Τμήμα Ένταξης, Τάξη Υποδοχής</span>";
    echo "</div>";
    echo $has_space ? "<td></td>" : '';
    echo "</td><td>";
    echo $hasblank ? "</td><td>" : '';
    echo "<select name=\"entty\">";
    echo $has_all ? "<option value='-1' selected=\"selected\">Όλοι</option>" : '';
        
    if ($entty == 0 && !$has_all) {
        echo "<option value='0' selected=\"selected\">Γενική αγωγή</option>";
    } else {
        echo "<option value='0'>Γενική αγωγή</option>";
    }
    if ($entty == 1) {
        echo "<option value='1' selected=\"selected\">Τμήμα Ένταξης</option>";
    } else {
        echo "<option value='1'>Τμήμα Ένταξης</option>";
    }
    if ($entty == 2) {
        echo "<option value='2' selected=\"selected\">Τάξη Υποδοχής</option>";    
    } else {
        echo "<option value='2'>Τάξη Υποδοχής</option>";
    }
    if ($isanapl){
        if ($entty == 3) {
            echo "<option value='3' selected=\"selected\">Παράλληλη στήριξη</option>";    
        } else {
            echo "<option value='3'>Παράλληλη στήριξη</option>";
        }
    }
    echo "</td>";
}

function thesianaplcmb($thesi)
{
    switch ($thesi)
    {
    case 0:
        $th = "Εκπαιδευτικός";
        break;
    case 1:
        $th = "Διευθυντής/Προϊστάμενος";
        break;
    }
    return $th;
}

function thesianaplselectcmb($thesi)
{
    echo "<tr><td>";
    echo "<div class='tooltip'>Θέση";
    echo "<span class='tooltiptext'>Επιλέξτε ένα από: Εκπαιδευτικός, Διευθυντής/Προϊστάμενος, Τμήμα Ένταξης, Τάξη Υποδοχής, Παράλληλη στήριξη</span>";
    echo "</div>";
    echo "</td><td>";
    echo "<select name=\"thesi\">";
    if ($thesi == 0) {
        echo "<option value='0' selected=\"selected\">Εκπαιδευτικός</option>";
    } else {
        echo "<option value='0'>Εκπαιδευτικός</option>";
    }
    if ($thesi == 1) {
        echo "<option value='1' selected=\"selected\">Διευθυντής/Προϊστάμενος</option>";    
    } else {
        echo "<option value='1'>Διευθυντής/Προϊστάμενος</option>";
    }
    echo "</td></tr>";
}

function schoolCombo($schid,$conn)
{
    $query = "SELECT * from school";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        echo "Δε βρέθηκαν αποτελέσματα...";
        return;
    }
    $num=mysqli_num_rows($result);
    echo "<select name=\"school\">";
    while ($i < $num) 
    {
        $id=mysqli_result($result, $i, "id");
        $name=mysqli_result($result, $i, "name");
        if (strcmp($schid, $id)==0) {
            echo "<option value=\"".$id."\" selected=\"selected\">".$name."</option>";
        } else {
            echo "<option value=\"".$id."\">".$name."</option>";
        }
        $i++;
    }
    echo "</select>";
}

function schCombo($name1,$conn)
{
    $query = "SELECT * from school";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        echo "Δε βρέθηκαν αποτελέσματα...";
        return;
    }
    $num=mysqli_num_rows($result);
    echo "<select name='$name1'>";
    echo "<option value=\"\" selected>(Παρακαλώ επιλέξτε:)</option>";
    while ($i < $num) 
    {
        $id=mysqli_result($result, $i, "id");
        $name=mysqli_result($result, $i, "name");
        echo "<option value=\"".$id."\">".$name."</option>";
        $i++;
    }
    echo "</select>";
}

function katastCmb($v)
{
    echo "<select name=\"status\">";
    if ($v==1) {
        echo "<option value=\"1\" selected>Εργάζεται</option>";
    } else {
        echo "<option value=\"1\">Εργάζεται</option>";
    }
    if ($v==2) {
        echo "<option value=\"2\" selected>Λύση Σχέσης - Παραίτηση</option>";
    } else {
        echo "<option value=\"2\">Λύση Σχέσης - Παραίτηση</option>";
    }
    if ($v==3) {
        echo "<option value=\"3\" selected>Άδεια</option>";
    } else {
        echo "<option value=\"3\">Άδεια</option>";
    }
    if ($v==4) {
        echo "<option value=\"4\" selected>Διαθεσιμότητα</option>";
    } else {
        echo "<option value=\"4\">Διαθεσιμότητα</option>";
    }
    if ($v==5) {
        echo "<option value=\"5\" selected>Απουσία COVID-19</option>";
    } else {
        echo "<option value=\"5\">Απουσία COVID-19</option>";
    }
    echo "</select>";
}
    
function adeiaCmb($inp,$conn,$ekt = 0,$all = false)
{
    $query = $ekt ? "SELECT * from adeia_ekt_type ORDER BY type" : "SELECT * from adeia_type ORDER BY type";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        echo "Δε βρέθηκαν αποτελέσματα...";
        return;
    }
    $num=mysqli_num_rows($result);
    echo "<select id='type' name=\"type\" >";
    if ($all) {
        echo "<option value='0'>Όλες</option>";
    }
    while ($i < $num) 
    {
        $id=mysqli_result($result, $i, "id");
        $type=mysqli_result($result, $i, "type");   
        if (strcmp($id, $inp)==0) {
            echo "<option value=\"".$id."\" selected=\"selected\">".$type."</option>";
        } else {
            echo "<option value=\"".$id."\">".$type."</option>";
        }
        $i++;
    }
    echo "</select>";
}

function workersCmb($inp, $sch, $conn)
{
  $query = "SELECT e.id,surname,name,perigrafh from employee e JOIN klados k ON e.klados = k.id WHERE sx_yphrethshs='$sch' AND status=1 ORDER BY surname ASC";
  $result = mysqli_query($conn, $query);
  if (!$result) { 
    echo "Δε βρέθηκαν αποτελέσματα...";
    return;
  }
    echo "<select id='workercmb' name='workercmb' >";
    echo "<option value=0>Παρακαλώ επιλέξτε</option>";
    while ($row = mysqli_fetch_assoc($result)) 
    {
        if (strcmp($row['id'], $inp)==0) {
            echo "<option value='".$row['id']."' selected='selected'>".$row['surname'].' '.$row['name']."</option>";
        } else {
          echo "<option value='".$row['id']."'>".$row['surname'].' '.$row['name'].' ('.$row['perigrafh'].')'."</option>";
        }
    }
    echo "</select>";
}

// generic combo function
function tblCmb($conn, $tbl, $inp = 0, $fieldnm = null, $sortby = null, $query = null)
{
    $query = $query ? $query : "SELECT * from $tbl";
    $query .= $sortby ? " ORDER BY $sortby ASC" : '';
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        echo "Δε βρέθηκαν αποτελέσματα...";
        return;
    }
    $num=mysqli_num_rows($result);
    echo $fieldnm ? "<select id=\"$fieldnm\" name=\"$fieldnm\" >" : "<select id=\"$tbl\" name=\"$tbl\" >";
    //echo "<select id=\"$tbl\" name=\"$tbl\" onchange='replace()' >";
    echo "<option value=\"\"> </option>";
    while ($i < $num) 
    {
        $id=mysqli_result($result, $i, "id");
        $name=mysqli_result($result, $i, "name");
        if ($id==$inp) {
            echo "<option value=\"".$id."\" selected=\"selected\">".$name."</option>";
        } else {
            echo "<option value=\"".$id."\">".$name."</option>";
        }
        $i++;
    }
    echo "</select>";
}

function postgradCmb($v = null)
{
    echo "<select name=\"category\">";
    if ($v==1) {
        echo "<option value='Μεταπτυχιακό' selected>Μεταπτυχιακό</option>";
    } else {
        echo "<option value='Μεταπτυχιακό'>Μεταπτυχιακό</option>";
    }
    if ($v==2) {
        echo "<option value='Διδακτορικό' selected>Διδακτορικό</option>";
    } else {
        echo "<option value='Διδακτορικό'>Διδακτορικό</option>";
    }
    if ($v==3) {
        echo "<option value='Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)' selected>Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)</option>";
    } else {
        echo "<option value='Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)'>Ενιαίος και αδιάσπαστος τίτλος σπουδών μεταπτυχιακού επιπέδου (Integrated master)</option>";
    }
    echo "</select>";
}

?>