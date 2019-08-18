<?php
// Workaround for the missing mysqli_result function
// Ideal for the transition to mysqli
// taken from https://mariolurig.com/coding/mysqli_result-function-to-match-mysqli_result/
function mysqli_result($res,$row=0,$col=0)
{ 
    if (!$res) return false;
    $numrows = mysqli_num_rows($res); 
    if ($numrows && $row <= ($numrows-1) && $row >=0) {
        mysqli_data_seek($res, $row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])) {
            return $resrow[$col];
        }
    }
    return false;
}
function getKlados($id,$conn)
{
    $query = "SELECT perigrafh from klados where id=".$id;
    $result = mysqli_query($conn, $query);
    //if (!$result) 
    //    die('Could not query:' . mysqli_error());
    return mysqli_result($result, 0);
}
    
function getSchool($id,$conn)
{
    $query = "SELECT name from school where id=".$id;
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        return;
    }
    //    die('Could not query:' . mysqli_error());
               //else
       return mysqli_result($result, 0);    
}
    
function getSchoolID($name,$conn)
{
    $query = "SELECT id from school where name='".$name."'";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        return false;
    } else {
        return mysqli_result($result, 0);
    }    
}
function getSchoolFromCode($code, $conn)
{
    $query = "SELECT id from school where code = '$code'";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return false;
    }
    //die('Could not query:' . mysqli_error());
      
    else {
        return mysqli_result($result, 0);
    }    
}
        
    //The function returns the no. of business days between two dates and it skips the holidays
function getWorkingDays($startDate,$endDate,$holidays)
{
    // do strtotime calculations just once
    $endDate = strtotime($endDate);
    $startDate = strtotime($startDate);

    //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
    //We add one to inlude both dates in the interval.
    $days = ($endDate - $startDate) / 86400 + 1;

    $no_full_weeks = floor($days / 7);
    $no_remaining_days = fmod($days, 7);

    //It will return 1 if it's Monday,.. ,7 for Sunday
    $the_first_day_of_week = date("N", $startDate);
    $the_last_day_of_week = date("N", $endDate);

    //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
    //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
    if ($the_first_day_of_week <= $the_last_day_of_week) {
        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) { $no_remaining_days--;
        }
        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) { $no_remaining_days--;
        }
    }
    else {
        // (edit by Tokes to fix an edge case where the start day was a Sunday
        // and the end day was NOT a Saturday)

        // the day of the week for start is later than the day of the week for end
        if ($the_first_day_of_week == 7) {
            // if the start date is a Sunday, then we definitely subtract 1 day
            $no_remaining_days--;

            if ($the_last_day_of_week == 6) {
                // if the end date is a Saturday, then we subtract another day
                $no_remaining_days--;
            }
        }
        else {
            // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
            // so we skip an entire weekend and subtract 2 days
            $no_remaining_days -= 2;
        }
    }

        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
    //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
    $workingDays = $no_full_weeks * 5;
    if ($no_remaining_days > 0 ) {
        $workingDays += $no_remaining_days;
    }

        //We subtract the holidays
    foreach($holidays as $holiday){
        $time_stamp=strtotime($holiday);
        //If the holiday doesn't fall in weekend
        if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N", $time_stamp) != 6 && date("N", $time_stamp) != 7) {
            $workingDays--;
        }
    }

        return $workingDays;
}
        
    // 23-12-2013: compute ypoloipo adeiwn - if yphrethsh = foreas adeies = 25 days
function ypoloipo_adeiwn($id, $sql)
{
    $qry2 = "SELECT sx_yphrethshs,thesi FROM employee WHERE id=$id";
    $res2 = mysqli_query($sql, $qry2);
    $sx_yphr = mysqli_result($res2, 0, "sx_yphrethshs");
    $thesi = mysqli_result($res2, 0, "thesi");
    // if apospasmenoi / dioikhtikoi
    if ($sx_yphr == 389 || $sx_yphr == 398 || $thesi == 4) {
        $cur_yr = date("Y");
        $prev_yr = $cur_yr - 1;
        $qry = "SELECT sum(days) as rem FROM adeia WHERE TYPE = 2 AND year(START) = $cur_yr AND year(FINISH) = $cur_yr AND emp_id = $id";
        $res = mysqli_query($sql, $qry);
        $cur_kan = mysqli_result($res, 0, "rem");
        $rem = 25 - $cur_kan;
        
        $qry1 = "SELECT sum(days) as rem FROM adeia WHERE TYPE = 2 AND year(START) = $prev_yr AND year(FINISH) = $prev_yr AND emp_id = $id";
        $res1 = mysqli_query($sql, $qry1);
        $prev_kan = mysqli_result($res1, 0, "rem");
        $prev_rem = 25 - $prev_kan;
            
        // xmas adeies
        $pre = $after = 0;
        $qry0 = "SELECT start, finish FROM adeia WHERE type=2 AND YEAR(start) = $prev_yr AND YEAR(finish) = $cur_yr AND emp_id = $id";
        $res0 = mysqli_query($sql, $qry0);
        if (mysqli_num_rows($res0)>0) {
            $start = mysqli_result($res0, 0, "start");
            $finish = mysqli_result($res0, 0, "finish");
            $holidays=array("$prev_yr-12-25","$prev_yr-12-26","$cur_yr-01-01","$cur_yr-01-06");
            $pre = getWorkingDays($start, "$prev_yr-12-31", $holidays);
            $after = getWorkingDays("$cur_yr-01-01", $finish, $holidays);
            //echo "pre: $pre, after: $after<br>";
        }
        $cur_kan += $after;
        $rem -= $after;
        $prev_rem -= $pre;
            
            
        // prev xmas adeies
        $pre = $after = 0;
        $preprev = $prev_yr-1;
        $qry0 = "SELECT start, finish FROM adeia WHERE type=2 AND YEAR(start) = $preprev AND YEAR(finish) = $prev_yr AND emp_id = $id";
        $res0 = mysqli_query($sql, $qry0);
        if (mysqli_num_rows($res0)>0) {
            $start = mysqli_result($res0, 0, "start");
            $finish = mysqli_result($res0, 0, "finish");
            $holidays=array("$preprev-12-25","$preprev-12-26","$prev_yr-01-01","$prev_yr-01-06");
            $pr_pre = getWorkingDays($start, "$preprev-12-31", $holidays);
            $pr_after = getWorkingDays("$prev_yr-01-01", $finish, $holidays);
            //echo "pre: $pr_pre, after: $pr_after<br>";
        }
        //$cur_kan += $after;
        //$rem -= $after;
        //$prev_rem -= $pre;
        $prev_kan += $pr_after;
        $prev_rem -= $pr_after;
            
        //echo "<small>Υπολ.$cur_yr: $rem, Yπολ.$prev_yr: $prev_rem / Κανονικές $cur_yr: $cur_kan, Κανονικές $prev_yr: $prev_kan</small><br>";
        $ret[2] = $prev_yr;
        $ret[3] = $prev_rem - $cur_kan;
        if ($ret[3]<0) {
            $ret[3] = 0;
        }
            
        $ret[0] = $cur_yr;
        $ret[1] = $rem + $prev_rem;
    }
    // if ekpaideytikoi
    else
    {
        $cur_yr = date("Y");
        $prev_yr = $cur_yr - 1;
        $qry = "SELECT sum(days) as rem FROM adeia WHERE TYPE = 2 AND year(START) = $cur_yr AND emp_id = $id";
        $res = mysqli_query($sql, $qry);
        $rem = mysqli_result($res, 0, "rem");
        
        $ret[0] = $cur_yr;
        $ret[1] = 10 - $rem;
        $ret[2] = 0;
    }
    return $ret;
}
    
function kladosCombo($klados,$conn)
{
       $query = "SELECT * from klados ORDER BY perigrafh";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        die('Could not query:' . mysqli_error());
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
        die('Could not query:' . mysqli_error());
    }
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
        die('Could not query:' . mysqli_error());
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
        die('Could not query:' . mysqli_error());
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
    case 3:
        $th = "Τμήμα Ένταξης";
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
function thesiselectcmb($thesi)
{
    echo "<tr><td>Θέση</td><td>";
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
    if ($thesi == 3) {
        echo "<option value='3' selected=\"selected\">Τμήμα Ένταξης</option>";    
    } else {
        echo "<option value='3'>Τμήμα Ένταξης</option>";
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
    case 2:
        $th = "Τμήμα Ένταξης";
        break;
    case 3:
        $th = "Παράλληλη στήριξη";
        break;
    }
    return $th;
}
function thesianaplselectcmb($thesi)
{
    echo "<tr><td>Θέση</td><td>";
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
    if ($thesi == 2) {
        echo "<option value='2' selected=\"selected\">Τμήμα Ένταξης</option>";    
    } else {
        echo "<option value='2'>Τμήμα Ένταξης</option>";
    }
    if ($thesi == 3) {
        echo "<option value='3' selected=\"selected\">Παράλληλη στήριξη</option>";    
    } else {
        echo "<option value='3'>Παράλληλη στήριξη</option>";
    }
}
    
    
function schoolCombo($schid,$conn)
{
    $query = "SELECT * from school";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        die('Could not query:' . mysqli_error());
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
        die('Could not query:' . mysqli_error());
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
function get_type($typeid,$conn)
{
    $query = "SELECT * from ektaktoi_types WHERE id=$typeid";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
      return;
        //die('Could not query:' . mysqli_error());
    }
    $typos=mysqli_result($result, $i, "type");
    return $typos;
}
function getDimos($id,$conn)
{
    $query = "SELECT name from dimos where id=".$id;
    $result = mysqli_query($conn, $query);
    //if (!$result) 
    //    die('Could not query:' . mysqli_error());
    //else
    $dimos = mysqli_result($result, 0);
    if (!$dimos) {
        return "¶γνωστος";
    } else {
        return $dimos;
    }
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
        echo "<option value=\"3\" selected>¶δεια</option>";
    } else {
        echo "<option value=\"3\">¶δεια</option>";
    }
    if ($v==4) {
        echo "<option value=\"4\" selected>Διαθεσιμότητα</option>";
    } else {
        echo "<option value=\"4\">Διαθεσιμότητα</option>";
    }
    echo "</select>";
}
    
function adeiaCmb($inp,$conn,$ekt = 0)
{
    $query = $ekt ? "SELECT * from adeia_ekt_type ORDER BY type" : "SELECT * from adeia_type ORDER BY type";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        die('Could not query:' . mysqli_error());
    }
    $num=mysqli_num_rows($result);
    echo "<select id='type' name=\"type\" >";
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
        
function days2ymd($input)
{
    $ret[0] = floor($input/360);
    $ret[1] = floor(($input%360)/30);
    $ret[2] = floor(($input%360)%30);
    if ($ret[0] < 0 || $ret[1] < 0 || $ret[2] < 0) {
        return Array(0,0,0);
    }
    return $ret;
}

function days2date($input)
{
    $ret[0] = floor($input/360);
    $ret[1] = floor(($input%360)/30);
    $ret[2] = floor(($input%360)%30);
    if ($ret[2]==0 && $ret[1]==0) {
        $ret[2]=30;
        $ret[1]=12;
        $ret[0]-=1;
    }
    else
        {
        if ($ret[2]==0) {
            $ret[2]=30;
            if ($ret[1]<=1) {
                $ret[1]=12;
                $ret[0]-=1;
            }
            else {
                $ret[1]-=1;
            }
        }
        if ($ret[1]==0) {
            $ret[1]=12;
            $ret[0]-=1;
        }
    }
        return $ret;
}
    // vathmos -> ret[0]: vathmos, ret[1]: days (pleonazwn sto vathmo)
function vathmos($days)
{
    // =IF(R3<1080;"ΣΤ";IF(R3<3240;"Ε";IF(R3<5400;"Δ";IF(R3<7560;"Γ";"Β"))))
    switch ($days)
    {
    case ($days<1080):
        $ret[0] = "ΣΤ";
        break;
    case ($days>=1080 && $days<3240):
        $ret[0] = "Ε";
        $ret[1] = $days-1080;
        break;
    case ($days>=3240 && $days<5400):
        $ret[0] = "Δ";
        $ret[1] = $days-3240;
        break;
    case ($days>=5400 && $days<7560):
        $ret[0] = "Γ";
        $ret[1] = $days-5400;
        break;
    default:
        $ret[0] = "Β";
        $ret[1] = $days-7560;
        break;
    }
    return $ret;
}
function mk_kat($days,$vathmos)         // mk katatakshs
{
    switch ($vathmos[0])
    {
    case ("ΣΤ"):    // ΣΤ'
        $mk = 0;
        break;
    case ("Ε"):    // Ε'
        switch ($days)
        {
        case ($days>=1080 && $days<1800):
            $mk = 0;
            break;
        case ($days>=1800 && $days<2520):
            $mk = 1;
            break;
        case ($days>=2520):
            $mk = 2;
            break;
        }
        break;
    case ("Δ"):  // Δ'
        switch ($days)
        {
        case ($days>=3240 && $days<3960):
            $mk = 0;
            break;
        case ($days>=3960 && $days<4680):
            $mk = 1;
            break;
        case ($days>=4680):
            $mk = 2;
            break;
        }
        break;
    case ("Γ"):    // Γ'
        switch ($days)
        {
        case ($days>=5400 && $days<6120):
            $mk = 0;
            break;
        case ($days>=6120 && $days<6840):
            $mk = 1;
            break;
        case ($days>=6840):
            $mk = 2;
            break;
        }
        break;
    case ("Β"):    // Β'
        switch ($days)
        {
        case ($days>=7560 && $days<8640):
            $mk = 0;
            break;
        case ($days>=8640 && $days<9720):
            $mk = 1;
            break;
        case ($days>=9720 && $days<10800):
            $mk = 2;
            break;
        case ($days>=10800 && $days<11880):
            $mk = 3;
            break;
        case ($days>=11880 && $days<12960):
            $mk = 4;
            break;
        case ($days>=12960 && $days<14040):
            $mk = 5;
            break;
        case ($days>=14040):
            $mk = 6;
            break;
        }
        break;
    }
    return $mk;
                
}
    // 16-12-2013 added Γ4
function mk($days,$vathmos)
{
    switch ($vathmos[0])
    {
    case ("ΣΤ"):    // ΣΤ'
        $mk = 0;
        break;
    case ("Ε"):    // Ε'
        switch ($days)
        {
        case ($days>=1080 && $days<1800):
            $mk = 0;
            break;
        case ($days>=1800 && $days<2520):
            $mk = 1;
            break;
        case ($days>=2520):
            $mk = 2;
            break;
        }
        break;
    case ("Δ"):  // Δ'
        switch ($days)
        {
        case ($days>=3240 && $days<3960):
            $mk = 0;
            break;
        case ($days>=3960 && $days<4680):
            $mk = 1;
            break;
        case ($days>=4680 && $days<5400):
            $mk = 2;
            break;
        case ($days>=5400):
            $mk = 3;
            break;
        }
        break;
    case ("Γ"):    // Γ'
        switch ($days)
        {
        case ($days>=5400 && $days<6120):
            $mk = 0;
            break;
        case ($days>=6120 && $days<6840):
            $mk = 1;
            break;
        case ($days>=6840 && $days<7560):
            $mk = 2;
            break;
        case ($days>=7560 && $days<8280):
            $mk = 3;
            break;
        case ($days>=8280):
            $mk = 4;
            break;
        }
        break;

                                
    case ("Β"):    // Β'
        switch ($days)
        {
        case ($days>=7560 && $days<8640):
            $mk = 0;
            break;
        case ($days>=8640 && $days<9720):
            $mk = 1;
            break;
        case ($days>=9720 && $days<10800):
            $mk = 2;
            break;
        case ($days>=10800 && $days<11880):
            $mk = 3;
            break;
        case ($days>=11880 && $days<12960):
            $mk = 4;
            break;
        case ($days>=12960 && $days<14040):
            $mk = 5;
            break;
        case ($days>=14040):
            $mk = 6;
            break;
        }
        break;
    }
    return $mk;        
}
        
    // mk_plus: returns mk[0]: mk & mk[1]:days in mk
    // 16-12-2013 added Γ4
function mk_plus($days,$vathmos)
{
    switch ($vathmos)
    {
    case ("ΣΤ"):    // ΣΤ'
        $mk[0] = 0;
        break;
    case ("Ε"):    // Ε'
        switch ($days)
        {
        case ($days>=1080 && $days<1800):
            $mk[0] = 0;
                      $mk[1] = $days - 1080;
            break;
        case ($days>=1800 && $days<2520):
            $mk[0] = 1;
                       $mk[1] = $days - 1800;
            break;
        case ($days>=2520):
            $mk[0] = 2;
                       $mk[1] = $days - 2520;
            break;
        }
        break;
                        
    case ("Δ"):  // Δ'
        switch ($days)
        {
        case ($days>=3240 && $days<3960):
            $mk[0] = 0;
                      $mk[1] = $days - 3240;
            break;
        case ($days>=3960 && $days<4680):
            $mk[0] = 1;
                       $mk[1] = $days - 3960;
            break;
        case ($days>=4680 && $days<5400):
            $mk[0] = 2;
                       $mk[1] = $days - 4680;
            break;
        case ($days>=5400):
            $mk[0] = 3;
            $mk[1] = $days - 5400;
            break;
        }
        break;
                        
    case ("Γ"):    // Γ'
        switch ($days)
        {
        case ($days>=5400 && $days<6120):
            $mk[0] = 0;
                      $mk[1] = $days - 5400;
            break;
        case ($days>=6120 && $days<6840):
            $mk[0] = 1;
                       $mk[1] = $days - 6120;
            break;
        case ($days>=6840 && $days<7560):
            $mk[0] = 2;
            $mk[1] = $days - 6840;
            break;
        case ($days>=7560 && $days<8280):
            $mk[0] = 3;
                       $mk[1] = $days - 7560;
            break;
        case ($days>=8280):
            $mk[0] = 4;
            $mk[1] = $days - 8280;
            break;
        }
        break;
                        
    case ("Β"):    // Β'
        switch ($days)
        {
        case ($days>=7560 && $days<8640):
            $mk[0] = "0";
                      $mk[1] = $days - 7560;
            break;
        case ($days>=8640 && $days<9720):
            $mk[0] = 1;
                       $mk[1] = $days - 8640;
            break;
        case ($days>=9720 && $days<10800):
            $mk[0] = 2;
                       $mk[1] = $days - 9720;
            break;
        case ($days>=10800 && $days<11880):
            $mk[0] = 3;
                       $mk[1] = $days - 10800;
            break;
        case ($days>=11880 && $days<12960):
            $mk[0] = 4;
                       $mk[1] = $days - 11880;
            break;
        case ($days>=12960 && $days<14040):
            $mk[0] = 5;
                       $mk[1] = $days - 12960;
            break;
        case ($days>=14040):
            $mk[0] = 6;
                       $mk[1] = $days - 14040;
            break;
        }
        break;
    case ("Α"):
        $mk[0]=0;
        $mk[1]=0;
        break;
    }
    return $mk;
}
    
    // mk16: Function for N.4354/2015
    // returns new MK
function mk16($days)
{
    if ($days <= 0) {
        return 1;
    }
    // @excel: =INT(Q2/2)+1
    $years = floor($days/360);
    $mk = floor($years/2) + 1;
    return $mk > 19 ? 19 : $mk;
}
    // mk16_plus: Function for N.4354/2015
    // returns new MK and days since last MK (pleonazwn)
function mk16_plus($days)
{
    // @excel: =INT(Q2/2)+1
    $years = floor($days/360);
    $mk = floor($years/2) + 1;
    $ret[0] = $mk > 19 ? 19 : $mk;
    $ret[1] = $days - (($mk * 2) - 2);
    //print_r($ret);
    return ret;
}
function date2days($d)
{
    $d = strtotime($d);
    return date('d', $d) + date('m', $d)*30 + date('Y', $d)*360;
}
function get_anatr($id, $mysqlconnection){
  $query = "SELECT * from employee WHERE id=$id";
  $result = mysqli_query($mysqlconnection, $query);
  $row = mysqli_fetch_assoc($result);
  
  $dt1 = strtotime($row['hm_anal']);
  $dt2 = strtotime($row['hm_dior']);
  $diafora = abs($dt1 - $dt2);
  
  $diafora = $diafora/86400;
  $d1 = $diafora > 30 ? strtotime($row['hm_anal']) : strtotime($row['hm_dior']);
  $anatr = (date('d', $d1) + date('m', $d1)*30 + date('Y', $d1)*360) - $row['proyp'] + $row['aney_xr'];
  return $anatr;
}
// get_mk: Function for ΜΚ computation
// returns MK (mk -> int) and total days to compute MK (ymd -> array)
function get_mk($id, $mysqlconnection, $date = null) {
  $asked_date = $date ? $date : date("Y-m-d");
  $subtract = 0;

  $query = "SELECT * from employee WHERE id=$id";
  $result = mysqli_query($mysqlconnection, $query);
  $row = mysqli_fetch_assoc($result);
  // compute anatr
  $anatr = get_anatr($id, $mysqlconnection);
  
  ///////////////////
  // compute MK time
  // compute subtracted MK days
  $asked = date('Y-m-d', strtotime($asked_date));
  $start = date('Y-m-d', strtotime('2016-01-01'));
  $end = date('Y-m-d', strtotime('2017-12-31'));
  // if diorismos after 2016-01-01
  if ($anatr > date2days($start)) {
    $subtract = $anatr - date2days($start);
    // if asked date > 2017-12-31
  } elseif ($asked > $end) {
    $subtract = 720;
    // if asked between start & end
  } elseif ($asked > $start && $asked < $end) {
    $subtract = date2days($asked) - date2days($start);
  } else {
    $subtract = 0;
  }
  // MSc / Phd
  // met: 4y, did: 12y, m+d: 12y
  if ($row['met_did']==1) {
        $anatr -= 1440;
  } else if ($row['met_did']==2) {
        $anatr -= 4320;
  } else if ($row['met_did']==3) {
        $anatr -= 4320;
  }
  // days for MK
  $result = date2days($asked_date) - $anatr - $subtract;
  $mk = mk16($result);
  //$res = mk16_plus($result);
  //$mk = $res[0];
  /*
  // Days to next MK: Left for later...
  $ymd = days2ymd($res[1]);
  $v99 = "-$tmp[1] day"; //may need fixing...
  $vdate = strtotime ( $v99 , $d1 );
  $vdate = date ( 'd-m-Y' , $vdate );
  echo "<br>MK: $mk <small>(από $vdate)</small>";
  */
  return array(
    'mk' => $mk,
    'ymd' => days2ymd($result)
  );
}
        
function exp2excel($data)
{
    $filename ="export.xls";
    header('Content-type: application/ms-excel');
    header('Content-Disposition: attachment; filename='.$filename);
    echo $data;
}
    
function ypol_yphr($yphr,$anatr)
{
    $d1 = strtotime($yphr);
    $result = (date('d', $d1) + date('m', $d1)*30 + date('Y', $d1)*360) - $anatr;
    if ($result<=0) {
        echo "Λάθος ημερομηνία";
    } else
    {
        $ymd=days2ymd($result);    
        //return $ymd;
        $ret = "Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2]";
        return $ret;
    }
}
        
function ExcelToPHP($dateValue = 0)
{
    $myExcelBaseDate = 25569;
    //  Adjust for the spurious 29-Feb-1900 (Day 60)
    if ($dateValue < 60) {
        --$myExcelBaseDate;
    }

    // Perform conversion
    if ($dateValue >= 1) {
        $utcDays = $dateValue - $myExcelBaseDate;
        $returnValue = round($utcDays * 86400);
        if (($returnValue <= PHP_INT_MAX) && ($returnValue >= -PHP_INT_MAX)) {
            $returnValue = (integer) $returnValue;
        }
    } else {
        $hours = round($dateValue * 24);
        $mins = round($dateValue * 1440) - round($hours * 60);
        $secs = round($dateValue * 86400) - round($hours * 3600) - round($mins * 60);
        $returnValue = (integer) gmmktime($hours, $mins, $secs);
    }

    // Return
    return $returnValue;
}   //  function ExcelToPHP()

    // source: http://code.loon.gr/snippet/php/%CE%BC%CE%B5%CF%84%CE%B1%CF%84%CF%81%CE%BF%CF%80%CE%AE-greek-%CF%83%CE%B5-greeklish
function greek_to_greeklish($string)
{
    return strtr(
        $string, array(
        'Α' => 'A', 'Β' => 'V', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'I', 'Θ' => 'TH', 'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L',
        'Μ' => 'M', 'Ν' => 'N', 'Ξ' => 'KS', 'Ο' => 'O', 'Π' => 'P', 'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F','Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'O',
        'α' => 'a', 'β' => 'v', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'i',
        'θ' => 'th', 'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'ks', 'ο' => 'o', 'π' => 'p', 'ρ' => 'r',
        'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'o', 'ς' => 's',
        'ά' => 'a', 'έ' => 'e', 'ή' => 'i', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ώ' => 'o',
        'ϊ' => 'i', 'ϋ' => 'y','Ϊ' => 'I', 'Ϋ' => 'Y','ΐ' => 'i', 'ΰ' => 'y'
        )
    );
}
        
    // generic combo function
function tblCmb($conn, $tbl, $inp = 0, $fieldnm = null, $sortby = null, $query = null)
{
    $query = $query ? $query : "SELECT * from $tbl";
    $query .= $sortby ? " ORDER BY $sortby ASC" : '';
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        die('Could not query:' . mysqli_error());
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
function getNamefromTbl($conn, $tbl, $id)
{
    $query = "SELECT * from $tbl WHERE id=$id";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
      return;
        //die('Could not query:' . mysqli_error());
    }
    $name=mysqli_result($result, 0, "name");
    return $name;
}
function getIDfromTbl($conn, $tbl, $name)
{
    $query = "SELECT * from $tbl WHERE name=$name";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        die('Could not query:' . mysqli_error());
    }
    $id=mysqli_result($result, 0, "id");
    return $id;
}
    //get parameter from param table
function getParam($name,$conn)
{
    $query = "SELECT value from params WHERE name='$name'";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        die('Could not query:' . mysqli_error($mysqlconnection));
    }
    return mysqli_result($result, 0, "value");
}
function setParam($name,$value,$conn)
{
    $query = "UPDATE params SET value='$value' WHERE name='$name'";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        die('Could not query:' . mysqli_error());
    }
}
    // creates a new record in yphrethsh table for each employee (if there isn't any) - used when changing sxoliko etos
    // disp: 0 - none, 1 - basic, 2 - extensive
function do2yphr($mysqlconnection, $disp = 1)
{
    if ($disp) {
        echo "<h3>Πλήρωση πίνακα υπηρετήσεων</h3>";
    }
    set_time_limit(1200);  
    $sxol_etos = getParam('sxol_etos', $mysqlconnection);
    $i = $ins_count = 0;
    $query0 = "SELECT * from employee";
    $result0 = mysqli_query($mysqlconnection, $query0);
    $num = mysqli_num_rows($result0);

    while ($i < $num)
    {
        $id = mysqli_result($result0, $i, "id");
        $sx_yphrethshs = mysqli_result($result0, $i, "sx_yphrethshs");
        $sx_organikhs = mysqli_result($result0, $i, "sx_organikhs");
        $hours = mysqli_result($result0, $i, "wres");
        //$query1 = "select * from yphrethsh WHERE emp_id=$id AND organikh=$sx_organikhs AND sxol_etos=$sxol_etos";
        $query1 = "select * from yphrethsh WHERE emp_id=$id AND sxol_etos=$sxol_etos";
        $result1 = mysqli_query($mysqlconnection, $query1);
        if (!mysqli_num_rows($result1)) {
            $ins_query = "INSERT INTO yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) VALUES ('$id', '$sx_yphrethshs', '$hours', '$sx_organikhs', '$sxol_etos')";
            $result2 = mysqli_query($mysqlconnection, $ins_query);
            if ( $result2 ) $ins_count++;
            if ($disp > 1) {
                echo "$id, ";
            }
        }
        $i++;
    }

    mysqli_close($mysqlconnection);
    if ($disp) {
        echo "<br>$i υπάλληλοι<br>$ins_count αλλαγές...<br>";
    }
}
    // returns school category
function getCategory($cat)
{
    switch ($cat){
    case 0:
        return "¶γνωστο";
            exit;
    case 1:
        return "Α' ($cat)";
            exit;
    case 2:
        return "Β' ($cat)";
            exit;
    case 3:
        return "Γ' ($cat)";
            exit;
    case 4:
        return "Δ' ($cat)";
            exit;
    case 5:
        return "Ε' ($cat)";
            exit;
    case 6:
        return "ΣΤ' ($cat)";
            exit;
    case 7:
        return "Ζ' ($cat)";
            exit;
    case 8:
        return "Η' ($cat)";
            exit;
    case 9:
        return "Θ' ($cat)";
            exit;
    }
}
    /* display notification
    * JQuery plugin: http://www.9lessons.info/2011/10/jquery-notification-plugin.html
    * type: 0: success, 1: error
    */        
function notify($msg, $type)
{
    $typewrd = $type ? 'error' : 'success';
    echo "<script type=\"text/javascript\">
            $(document).ready(function(){
            showNotification({
            message: '$msg',
            type: '$typewrd',
            autoClose: true,
            duration: 3
            });
        });
        </script>";
}
    /*
    * check for expired adeies idiwtikoy ergoy
    */
function check_idiwtiko($conn)
{
    $ret = "";
    $query = "SELECT id,surname,name,idiwtiko_liksi from employee WHERE idiwtiko=1 AND idiwtiko_liksi <= curdate()";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        die('Could not query:' . mysqli_error());
    }
    if (!mysqli_num_rows($result)) {
        return "";
    } else {
        $ret = "Οι παρακάτω εκπ/κοί έχουν άδεια ιδιωτικού έργου σε δημόσιο φορέα που έχει λήξει:<br>";
    }
    while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)){
        $ret .= "<small><a href=\"employee.php?id=".  $row['id'] ."&op=view\" target=\"_blank\">". $row['surname'] ." ". $row['name'] ."</a></small><br>";
    }
    return $ret;
}
    /*
    * Return previous school year
    */
function find_prev_year($sxoletos)
{
    $tmp = (int)(substr($sxoletos, 0, 4));
    $tmp = (string)($tmp - 1);
    $tmp = $tmp . substr($sxoletos, 2, 2);
    return $tmp;
}
    /*
    * anagkes_wrwn: Compute required hours based on oloimero schedule 2016-17
    * Returns required hours depending on number of classes
    * tmimata: 0: A, 1: B, 2: Γ, 3: Δ, 4: E, 5: ΣΤ
    * 6: Ολ. 15.00, 7: Ολ. 16:00, 8: ΠΖ
    */
function anagkes_wrwn($tm)
{
    $artm = $tm[0]+$tm[1]+$tm[2]+$tm[3]+$tm[4]+$tm[5];
    // oligothesia (<4/thesia)
    if ($artm < 4) {
        $hours = [];
        $hours['70'] = $artm * 30;
        // oloimero
        $hours['O'] = $tm[6]>0 ? $tm[6]*10 + $tm[7]*5 : 0;
        // PZ
        $hours['P'] = $tm[8]*5;
        return $hours;
    }
    // 4/thesia
    elseif ($artm == 4) {
        $hours = [];
        $hours['05-07'] = $tm[4]*1;
        $hours['06'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*3 + $tm[4]*3;
        $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[4]*1;
        $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[4]*2;
        $hours['79'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1;
        $hours['91'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1;
        $hours['86'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[4]*1;
        $hours['70'] = $tm[0]*21 + $tm[1]*21 + $tm[2]*20 + $tm[4]*22;
        // oloimero
        $hours['O'] = $tm[6]>0 ? $tm[6]*10 + $tm[7]*5 : 0;
        // PZ
        $hours['P'] = $tm[8]*5;
        return $hours;
    }
    // 5/thesia
    elseif ($artm == 5) {
        // ean opws fek dld synd/lia 3-4
        $hours = [];
        if ($tm[2] + $tm[3] == 1) {
            $hours['05-07'] = $tm[4]*2 + $tm[5]*2;
            $hours['06'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*3 + $tm[4]*3 + $tm[5]*3;
            $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[4]*1 + $tm[5]*1;
            $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[4]*2 + $tm[5]*2;
            $hours['79'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[4]*1 + $tm[5]*1;
            $hours['91'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1;
            $hours['86'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[4]*1 + $tm[5]*1;
            $hours['70'] = $tm[0]*21 + $tm[1]*21 + $tm[2]*20 + $tm[4]*20 + $tm[5]*20;
            // alliws ean synd/lia 5-6
        } else {
            $hours['05-07'] = $tm[4]*1;
            $hours['06'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*3 + $tm[3]*3 + $tm[4]*3;
            $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1;
            $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[3]*3 + $tm[4]*2;
            $hours['79'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1;
            $hours['91'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1;
            $hours['86'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1;
            $hours['70'] = $tm[0]*21 + $tm[1]*21 + $tm[2]*20 + $tm[3]*20 + $tm[4]*22;
        }
        // oloimero
        $hours['O'] = $tm[6]>0 ? $tm[6]*10 + $tm[7]*5 : 0;
        // PZ
        $hours['P'] = $tm[8]*5;
        return $hours;
    }
    // 6/thesia+anw
    else {
        $hours = [];
        $hours['05-07'] = $tm[4]*2 + $tm[5]*2;
        $hours['06'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*3 + $tm[3]*3 + $tm[4]*3 + $tm[5]*3;
        $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1 + $tm[5]*1;
        $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[3]*3 + $tm[4]*2 + $tm[5]*2;
        $hours['79'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1 + $tm[5]*1;
        $hours['91'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1;
        $hours['86'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1 + $tm[5]*1;
        $hours['70'] = $tm[0]*21 + $tm[1]*21 + $tm[2]*20 + $tm[3]*20 + $tm[4]*20 + $tm[5]*20;
        // oloimero
        $hours['O'] = $tm[6]>0 ? $tm[6]*10 + $tm[7]*5 : 0;
        // PZ
        $hours['P'] = $tm[8]*5;
        return $hours;
    }
}
    /*
    * Headmaster's hours depending on number of school classes
    * 4,5: 18, 6-9: 10, 10,11: 8, 12+: 6
    */
function wres_dnth($tm)
{
    if ($tm < 4) {
        return 25;
    } elseif ($tm == 4 || $tm == 5) {
        return 18;
    } elseif ($tm > 5 && $tm < 10) {
        return 10;
    } elseif ($tm == 10 || $tm == 11) {
        return 8;
    } elseif ($tm >= 12) {
        return 6;
    }
}
function hours_to_teachers($hours)
{
    return round($hours/23, 0);
}
function tdc($val,$colspan = null,$withspan = true)
{
    $cols = $colspan ? "colspan=$colspan" : '';
    $colval = $withspan ? 
        "<span title='".hours_to_teachers($val)."'>$val</span>" :
        $val;
    if ($val == 0) {
        return "<td $cols style='background:none;background-color:rgba(0, 255, 0, 0.37)'>$colval</td>";
    } elseif ($val < 0 ) {
        return "<td $cols style='background:none;background-color:rgba(255, 0, 0, 0.45)'>$colval</td>";
    } else {
        return "<td $cols style='background:none;background-color:rgba(255,255,0,0.3)'>$colval</td>";
    }
}
    /*
    * ektimhseis_wrwn
    * Function to compute required and available hours for oloimero schedule (since 2016-17)
    * uses anagkes_wrwn
    * sch: school code, $print: TRUE to print, false to return 3 arrays(required, available, diff)
    */
function ektimhseis_wrwn($sch, $mysqlconnection, $sxoletos, $print = false)
{
    set_time_limit(1200);
    $avhrs = [];
    $all = $allcnt = [];
    // init db
    mysqli_query($mysqlconnection, "SET NAMES 'greek'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
    // get tmimata
    $query = "SELECT students,tmimata,entaksis,leitoyrg,vivliothiki,type2 from school WHERE id='$sch'";
    $result = mysqli_query($mysqlconnection, $query);
    $tmimata_exp = explode(",", mysqli_result($result, 0, "tmimata"));
    $vivliothiki = mysqli_result($result, 0, "vivliothiki");
    $eidiko = mysqli_result($result, 0, "type2") == 2 ? true : false;
    $leit = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
    $oligothesio = $leit < 4 ? true : false;
    // entaksis
    $entaksis = explode(',', mysqli_result($result, 0, "entaksis"));
    $has_entaxi = strlen($entaksis[0])>1 ? 1 : 0;
    // synolo mathitwn (gia yp/ntes)
    $classes = explode(",", mysqli_result($result, 0, "students"));
    $synolo_pr = $classes[0]+$classes[1]+$classes[2]+$classes[3]+$classes[4]+$classes[5];
        
    // for PZ: at least 7 stud for leit < 9, at least 10 for leit >= 9
    if ($leit < 9 && $classes[7] < 7 || $leit >= 9 && $classes[7] < 10) {
        $tmimata_exp[8] = 0;
    }
    // Απαιτούμενες ώρες
    $reqhrs = anagkes_wrwn($tmimata_exp);
    // ώρες Δ/ντή
    $query = "SELECT * from employee e JOIN klados k ON e.klados = k.id WHERE sx_yphrethshs='$sch' AND status=1 AND thesi = 2";
    $result = mysqli_query($mysqlconnection, $query);
    if (mysqli_num_rows($result)) {
        $dnthrs = wres_dnth($leit);
        $klados = mysqli_result($result, 0, "klados");
        $klper = mysqli_result($result, 0, "perigrafh");
        $avhrs[$klados] = $dnthrs;
        // ώρες Δ/ντή στην ανάλυση
        $ar = Array(
            'name' => mysqli_result($result, 0, 1),
            'surname' => "<small>(Δ/ντής/-ντρια)</small> ".mysqli_result($result, 0, 2),
            'klados' =>  mysqli_result($result, 0, "perigrafh"), 
            'hours' => $dnthrs
        );
        $all[] = $ar;
        $allcnt[$klper]++;
    }
    // ώρες Υπ/ντή
    $meiwsh_ypnth = 0;
    $query_yp = "SELECT * from employee e JOIN klados k ON e.klados = k.id WHERE sx_yphrethshs='$sch' AND status=1 AND thesi = 1";
    $result_yp = mysqli_query($mysqlconnection, $query_yp);
    while ($row = mysqli_fetch_assoc($result_yp)){
        // reduce if students > 120 or eidiko with >30 students
        if ($synolo_pr > 120 || ($eidiko && $synolo_pr > 30)) {
            $meiwsh_ypnth = 2;
        }
        
        $klados = $row['klados'];
        $meiwsh_ypnth_klados = $row['perigrafh'];
        $avhrs[$klados] -= $meiwsh_ypnth;
        // ώρες Υπ/ντή στην ανάλυση
        $ar = Array(
            'name' => $row['name'],
            'surname' =>  '<small>(Υπ/ντής/-ντρια)</small> ' . $row['surname'],
            'klados' =>  $meiwsh_ypnth_klados, 
            'hours' => $row['wres'] - $meiwsh_ypnth
        );
        $all[] = $ar;
        $allcnt[$meiwsh_ypnth_klados]++;
    }
        
    // μείωση ωραρίου υπευθύνου βιβλιοθήκης (3 ώρες)
    $meiwsh_vivliothikis = 0;
    if ($vivliothiki > 0) {
        $meiwsh_vivliothikis = 3;
        $reqhrs['70'] += 3;
    }
    // ώρες υπηρετούντων (εκπ/κοί - υπ/ντές, εκτός Τ.Ε.)
    //$query = "SELECT klados, sum(wres) as wres from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,1) GROUP BY klados";
    if ($oligothesio) {
        $query = "SELECT e.klados, count(*) as plithos FROM employee e join yphrethsh y on e.id = y.emp_id WHERE y.yphrethsh='$sch' AND y.sxol_etos = $sxoletos AND e.status=1 AND e.thesi in (0,1) GROUP BY klados";
        $result = mysqli_query($mysqlconnection, $query);
        while ($row = mysqli_fetch_array($result)){
            $plithos = strval($row['plithos']);
            $kl = strval($row['klados']);
            $avhrs[$kl] += $plithos * 30;
        }
    } else {
        $query = "SELECT e.klados, sum(y.hours) as wres FROM employee e join yphrethsh y on e.id = y.emp_id WHERE y.yphrethsh='$sch' AND y.sxol_etos = $sxoletos AND e.status=1 AND e.thesi in (0,1) GROUP BY klados";
        $result = mysqli_query($mysqlconnection, $query);
        while ($row = mysqli_fetch_array($result)){
            $kl = strval($row['klados']);
            $avhrs[$kl] += $row['wres'];
        }
    }
    if ($print) {
        // αναλυτικά...
        $query = "SELECT e.name, e.surname,k.perigrafh, y.hours FROM employee e join yphrethsh y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh='$sch' AND y.sxol_etos = $sxoletos AND e.status=1 AND e.thesi in (0) ORDER BY e.klados";
        $result = mysqli_query($mysqlconnection, $query);
        while ($row = mysqli_fetch_array($result)){
            $ar = Array('name' => $row['name'], 'surname' => $row['surname'], 'klados' => $row['perigrafh'], 'hours' => $row['hours']);
            $all[] = $ar;
            $allcnt[$row['perigrafh']]++;
        }
    }
    // αναπληρωτές (εκτός ΖΕΠ / ΕΚΟ (type=6) & thesi 2,3 (ένταξης/παράλληλη) & type 4,5,6 (ΕΕΠ,ΕΒΠ,ΖΕΠ/ΕΚΟ))
    $query = "SELECT klados,sum(y.hours) as wres FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where y.yphrethsh=$sch AND y.sxol_etos = $sxoletos AND e.status = 1 AND e.type NOT IN (4,5,6) AND e.thesi NOT IN (2,3) GROUP BY klados";
    $result = mysqli_query($mysqlconnection, $query);
    while ($row = mysqli_fetch_array($result)){
        $kl = strval($row['klados']);
        $avhrs[$kl] += $row['wres'];
    }
    if ($print) {
        // αναλυτικά...(εκτός ΖΕΠ / ΕΚΟ (type=6))
        $query = "SELECT e.name, e.surname, e.thesi, k.perigrafh, y.hours FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id JOIN klados k ON e.klados=k.id where y.yphrethsh=$sch AND y.sxol_etos = $sxoletos AND e.status = 1 AND e.type != 6 ORDER BY e.klados";
        $result = mysqli_query($mysqlconnection, $query);
        while ($row = mysqli_fetch_array($result)){
            $srn = $row['surname'] . ' *';
            $srn .= $row['thesi'] == 2 ? '<small> (Τμ.Ένταξης)</small>' : '';
            $srn .= $row['thesi'] == 3 ? '<small> (Παράλληλη)</small>' : '';
            $ar = Array('name' => $row['name'], 'surname' => $srn, 'klados' => $row['perigrafh'], 'hours' => $row['hours']);
            $all[] = $ar;
            $allcnt[$row['perigrafh']]++;
        }
    }
    // PE70 entaksis
    if ($has_entaxi > 0) {
        $qry = "SELECT count(*) as pe70 FROM employee WHERE sx_yphrethshs = $sch AND klados=2 AND status=1 and thesi = 3";
        $res = mysqli_query($mysqlconnection, $qry);
        $top_ent = mysqli_result($res, 0, 'pe70');
        $avhrs['TE'] = $top_ent;
        $ret['TE'] = $top_ent - $has_entaxi;
        if ($print) {
            // αναλυτικά...
            $query = "SELECT e.name,e.surname,k.perigrafh, y.hours FROM employee e join yphrethsh y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh='$sch' AND y.sxol_etos = $sxoletos AND e.status=1 AND e.thesi = 3 ORDER BY e.klados";
            $result = mysqli_query($mysqlconnection, $query);
            while ($row = mysqli_fetch_array($result)){
                $ar = Array('name' => $row['name'],'surname' => $row['surname'] . ' (Τ.Ε.)', 'klados' => $row['perigrafh'], 'hours' => $row['hours']);
                $all[] = $ar;
                //$allcnt[$row['perigrafh']]++;
            }
        }
    }
    // replace kladoi @ array
    //kladoi: 2/70, 3/06, 4/08, 5/11, 6/79(ex 16), 15/86 (ex 19-20), 13/05-07, 14/05-07, 20/91
    $avar = $avhrs;
    $avar['70'] = $avar['2'];
    unset($avar['2']);
    $avar['06'] = $avar['3'];
    unset($avar['3']);
    $avar['08'] = $avar['4'];
    unset($avar['4']);
    $avar['11'] = $avar['5'];
    unset($avar['5']);
    $avar['79'] = $avar['6'];
    unset($avar['6']);
    $avar['86'] = $avar['15'];
    unset($avar['15']);
    $avar['05-07'] = $avar['13'] + $avar['14'];
    unset($avar['13']);
    unset($avar['14']);
    $avar['91'] = $avar['20'] + $avar['28'];
    unset($avar['20']);

    // subtract available from required
    foreach ($avar as $key => $value) {
        if(array_key_exists($key, $reqhrs) && array_key_exists($key, $avar)) {
            $ret[$key] = $avar[$key] - $reqhrs[$key];
        }
    }
    // subtract oloimero & pz from PE70
    $ret['OP'] = $ret['70'] - $reqhrs['O'] - $reqhrs['P'];
        
    if ($print) {
        echo "<h3>Λειτουργικά Κενά / Πλεονάσματα <em>(σε ώρες)</em></h3>";
        echo "<table class=\"imagetable\" border='1'>";
        echo "<thead>";
        echo "<th>Κλάδος</th>";
        echo "<th><span title='Γαλλικών-Γερμανικών'>05-07</span></th>";
        echo "<th><span title='Αγγλικών'>06</span></th>";
        echo "<th><span title='Καλλιτεχνικών'>08</span></th>";
        echo "<th><span title='Φυσικής αγωγής'>11</span></th>";
        echo "<th><span title='Μουσικής'>79</span></th>";
        echo "<th><span title='Θεατρικής Αγωγής'>91</span></th>";
        echo "<th><span title='Πληροφορικής'>86</span></th>";
        echo "<th><span title='Δασκάλων'>70</span></th>";
        echo "<th>Ολοήμερο</th><th>Πρωινή Ζώνη</th>";
        echo $has_entaxi ? '<th>T.E.<small> (αρ.εκπ)</small></th>' : '';
        echo "</thead>";
        echo "<tr><td>Απαιτούμενες</td><td>".$reqhrs['05-07']."</td><td>".$reqhrs['06']."</td><td>".$reqhrs['08']."</td><td>".$reqhrs['11']."</td><td>".$reqhrs['79']."</td><td>".$reqhrs['91']."</td><td>".$reqhrs['86']."</td><td>".$reqhrs['70']." ($leit)</td><td>".$reqhrs['O']."</td><td>".$reqhrs['P']."</td>";
        echo $has_entaxi ? '<td>1</td>' : '';
        echo "</tr>";
        echo "<tr><td>Διαθέσιμες</td><td>".$avar['05-07']."</td><td>".$avar['06']."</td><td>".$avar['08']."</td><td>".$avar['11']."</td><td>".$avar['79']."</td><td>".$avar['91']."</td><td>".$avar['86']."</td><td>".$avar['70']." (".$allcnt['ΠΕ70'].")</td><td colspan=2></td>";
        echo $has_entaxi ? '<td>'.$avhrs['TE'].'</td>' : '';
        echo "</tr>";
        echo "<tr><td>Διαφορά (+/-)</td>".tdc($ret['05-07']).tdc($ret['06']).tdc($ret['08']).tdc($ret['11']).tdc($ret['79']).tdc($ret['91']).tdc($ret['86']).tdc($ret['70']).tdc($ret['OP'], 2);
        echo $has_entaxi ? tdc($ret['TE']) : '';
        echo "</tr>";
        echo "</table>";
        // if meiwseis, print below table
        if (($meiwsh_ypnth + $meiwsh_vivliothikis) > 0) {
            echo "<p>Μειώσεις υπ.ωραρίου: ";
            echo $meiwsh_ypnth > 0 ? "Υποδιευθυντών ($meiwsh_ypnth_klados): ".$meiwsh_ypnth.' ώρες<br>' : '';
            echo $meiwsh_vivliothikis > 0 ? 'Υπευθύνου Βιβλιοθήκης (ΠΕ70): '.$meiwsh_vivliothikis.' ώρες<br>' : '';
            echo "</p>";
        }
        echo "<a id='toggleBtn' href='#' onClick=>Αναλυτικά</a>";
        echo "<div id='analysis' style='display: none;'>";
            echo "<table class=\"imagetable stable\" border='1'>";
            echo "<tr><td colspan=3><u>Σύνολα εκπ/κών:</u> ";
        foreach ($allcnt as $key=>$value){
            echo "&nbsp;&nbsp;$key: <strong>$value</strong>";
        }
            echo "</td></tr>";
            echo "<tr><td><b>Ον/μο</b></td><td><b>Κλάδος</b></td><td><b>Ώρες</b></td></tr>";
        foreach ($all as $row) {
            echo "<tr><td>".$row['surname']." ".substr($row['name'], 0, 3).".</td><td>".$row['klados']."</td><td>".$row['hours']."</td></tr>";
        }
            echo "</table>";
            echo "* Αναπληρωτής";
            echo "</div>";
            echo "<br><br>";
    }
    else {
        return ['required' => $reqhrs, 'available' => $avar, 'diff' => $ret, 'leit' => $leit];
    }
}

function organikes_per_klados($mysqlconnection)
{
    $query = "SELECT COUNT( * ) as total, k.perigrafh, k.onoma FROM employee e 
            JOIN klados k 
            ON k.id = e.klados 
            WHERE status!=2 AND sx_organikhs NOT IN (388,394) AND thesi NOT IN (4,5)
            GROUP BY klados";
    $result_mon = mysqli_query($mysqlconnection, $query);
    $ret = [];
    while($row = mysqli_fetch_array($result_mon, MYSQLI_BOTH)){
        $ret[$row['perigrafh']] = $row['total'];
    }
    return $ret;
}

function tmimata_nipiagwgeiwn($mysqlconnection)
{
    $query = "SELECT * from school WHERE type = 2 AND type2=0 AND anenergo=0";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
    $i=0;

    while ($i < $num)
    {         
        $sch = mysqli_result($result, $i, "id");
            
        $klasiko = mysqli_result($result, $i, "klasiko");
        $klasiko_exp = explode(",", $klasiko);

        $oloimero_nip = mysqli_result($result, $i, "oloimero_nip");
        $oloimero_nip_exp = explode(",", $oloimero_nip);
            
        $klasiko_tm = $oloimero_tm = 0;
        $klasiko_tm += $klasiko_exp[0]+$klasiko_exp[1]>0 ? 1:0;
        $klasiko_tm += $klasiko_exp[2]+$klasiko_exp[3] >0 ? 1:0;
        $klasiko_tm += $klasiko_exp[4]+$klasiko_exp[5]>0 ? 1:0;

        $oloimero_tm += $oloimero_nip_exp[0]+$oloimero_nip_exp[1]>0 ? 1:0;
        $oloimero_tm += $oloimero_nip_exp[2]+$oloimero_nip_exp[3]>0 ? 1:0;
        $oloimero_tm += $oloimero_nip_exp[4]+$oloimero_nip_exp[5]>0 ? 1:0;

        $synolo_tm_klas += $klasiko_tm;
        $synolo_tm_olo += $oloimero_tm;
            
        $i++;
    }
    return ['klasiko' => $synolo_tm_klas, 'oloimero' => $synolo_tm_olo];
}

function dntes_ana_klado($mysqlconnection, $tetrathesia_k_anw = false)
{
    if ($tetrathesia_k_anw) {
        $query = "SELECT k.perigrafh as eidikothta, count(*) as total
            FROM `employee` e 
            JOIN klados k ON e.klados = k.id 
            JOIN school s ON e.sx_yphrethshs = s.id 
            WHERE thesi = 2 AND leitoyrg > 3 AND klados != 1 
            group by klados";
    }
    else {
        $query = "SELECT k.perigrafh as eidikothta, count(*) as total
                FROM `employee` e 
                JOIN klados k ON e.klados = k.id 
                WHERE thesi = 2 group by klados";
    }
    $res = mysqli_query($mysqlconnection, $query);
    $ret = [];
    while($row = mysqli_fetch_array($res, MYSQLI_BOTH)){
        $ret[$row['eidikothta']] = $row['total'];
    }
    return $ret;
}
function apospasmenoi_ekswteriko($mysqlconnection)
{
    $query = "SELECT k.perigrafh as eidikothta, count(*) as total
            FROM `employee` e 
            JOIN klados k ON e.klados = k.id 
            WHERE e.sx_yphrethshs=399 group by klados";
        
    $res = mysqli_query($mysqlconnection, $query);
    $ret = [];
    while($row = mysqli_fetch_array($res, MYSQLI_BOTH)){
        $ret[$row['eidikothta']] = $row['total'];
    }
    return $ret;
}

    // Function to check for subtracted days of leave (for anaplirotes)
    // returns number of subtracted days
function get_adeies($id, $mysqlconnection)
{
    $has_kyhsh = $has_loxeia = $anar_days = $anar_days_subtr = $apergies = $aney = $subtract = 0;
    $sxol_etos = getParam('sxol_etos', $mysqlconnection);
    $qry_ad = "SELECT type,days FROM adeia_ekt WHERE emp_id = $id AND sxoletos=$sxol_etos";
    $res_ad = mysqli_query($mysqlconnection, $qry_ad);
    while ($arr_ad = mysqli_fetch_array($res_ad)) {
        // check adeia type
        switch ($arr_ad['type']) {
            // // kyhshs
            // case 6:
            //     $has_kyhsh = 1; break;
            // // loxeias
            // case 5:
            //     $has_loxeia = 1; break;
            // anarrwtikh or anarrwtikh (ygeionomiko)
        case 1:
        case 3:
            $anar_days += $arr_ad['days'];
            break;
            // aney or aney anatrofhs
        case 10:
        case 12:
            $aney += $arr_ad['days'];
            break;
            // apergia
        case 17:
            $apergies += $arr_ad['days'];
            break;
            // stash
        case 18:
            $apergies += ($arr_ad['days']*0.5);
            break;
        }
    }
    // if kyhsh or loxeia, subtract every anarrwtikh
    // if ($has_kyhsh || $has_loxeia) {
    //     $subtract += $anar_days;
    // } 
    // subtract anarrwtikes > 15
    if ($anar_days > 15) {
        $anar_days_subtr = $anar_days - 15;
        $subtract += $anar_days_subtr;
    }
    // subtract aney
    $subtract += $aney;
    // subtract (rounded down) apergies
    //$subtract += floor($apergies);

    $ret = Array(
        'subtracted'=>$subtract, 
        'anar_sub'=>$anar_days_subtr,
        'anar'=>$anar_days,
        'aney'=>$aney,
        'apergies'=>floor($apergies)
    );
    return $ret;
}

    // get ypoxrewtiko wrario
    // depending on klados, praxi etc.
function get_ypoxrewtiko_wrario($emp_id, $conn)
{
    $query = "SELECT e.id, e.klados, p.type, p.name as praxi FROM ektaktoi e
            JOIN praxi p ON e.praxi = p.id
            WHERE e.id = $emp_id";
    $res = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($res, MYSQLI_BOTH);
    // PE60 or EEP or Anaptixi Yp.Dom.
    if ($row['klados'] == 1 || strpos($row['praxi'], 'ΕΕΠ') !== false || $row['type'] == 'ΥΠΟΣ') {
        return 25;
        // EBP or PEP
    } elseif (strpos($row['praxi'], 'ΕΒΠ') !== false || $row['type'] == 'ΠΕΠ') {
        return 30;
    }
    return 24;
}

// compute_meiwmeno
// for teachers with reduced teaching hours
function compute_meiwmeno($days, $hours_per_week = 14, $ypoxr = 24)
{   
    return round($days * ($hours_per_week/$ypoxr));
}

function get_leitoyrgikothta($id, $mysqlconnection)
{
    $query = "SELECT tmimata,type,klasiko from school WHERE id='$id'";
    $result = mysqli_query($mysqlconnection, $query);
    $type = mysqli_result($result, 0, 'type');
    // if nip
    if ($type == 2) {
        $klasiko_exp = explode(",", mysqli_result($result, 0, "klasiko"));
        $klasiko_tm = 0;
        $klasiko_tm += $klasiko_exp[0]+$klasiko_exp[1]>0 ? 1:0;
        $klasiko_tm += $klasiko_exp[2]+$klasiko_exp[3] >0 ? 1:0;
        $klasiko_tm += $klasiko_exp[4]+$klasiko_exp[5]>0 ? 1:0;
        return $klasiko_tm;
    } else {
        $tmimata_exp = explode(",", mysqli_result($result, 0, "tmimata"));
        $leit = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
        return $leit;
    }
        
}

// get_orgs
// returns number of teachers who belong organically per expertise
function get_orgs($id, $mysqlconnection)
{
    // organika, not @ entaksis
    $query = "SELECT k.perigrafh as klname, count(*) as plithos 
    FROM employee e join klados k on e.klados = k.id 
    WHERE e.sx_organikhs='$id' AND status IN (1,3) AND thesi IN (0,1,2) AND org_ent = 0
    GROUP BY klados";
    $result = mysqli_query($mysqlconnection, $query);
    $ret = array();
    while ($row = mysqli_fetch_array($result)){
      $plithos = strval($row['plithos']);
      $kl = $row['klname'];
      $ret[$kl] += $plithos;
    }
    // @ entaksis
    $query = "SELECT count(*) as plithos FROM employee e 
    WHERE e.sx_organikhs='$id' AND status IN (1,3) AND org_ent=1";
    $result = mysqli_query($mysqlconnection, $query);
    while ($row = mysqli_fetch_array($result)){
      $plithos = strval($row['plithos']);
      $ret['ent'] = $plithos;
    }
    return $ret;
}

function get_wres($days)
{
    // 0-10: 24 wres, 11-15: 23 wres, 15-20: 22 wres, >20: 21 wres
    // 10y = 3600 days, 15y = 5400 days, 20y = 7200 days
    if ($days <= 3600) {
        return 24;
    } elseif ($days > 3600 && $days <= 5400) {
        return 23;
    } elseif ($days > 5400 && $days <= 7200) {
        return 22;
    } elseif ($days >7200) {
        return 21;
    }
}   
function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
} 
// $hmapox, $hmpros : YYYY-MM-DD strings
function yphresia_anaplhrwth($hmapox, $hmpros, $meiwmeno = false, $subtracted = 0, $yp_wrario = 24, $hour_sum = 0) {
  // ypologismos yphresias
  $apol = substr($hmapox, 8, 2) + substr($hmapox, 5, 2)*30 + substr($hmapox, 0, 4)*360;
  // hm proslhpshs
  $pros = substr($hmpros, 0, 4)*360 + substr($hmpros, 5, 2)*30 + substr($hmpros, 8, 2);
  // +1 για να περιληφθεί και η τελευταία μέρα
  $days = $apol - $pros + 1;
  // subtract subtracted
  $days -= $subtracted;
  // if meiwmeno, compute yphresia
  if ($meiwmeno) {
      $days = compute_meiwmeno($days, $hour_sum, $yp_wrario);
  }
  $ymd = days2ymd($days);
  $data = $ymd[1]." μήνες, ".$ymd[2]." ημέρες";
  return $data;
}

function get_diavgeia_subject($ada) {
  if (!$ada) {
    return;
  }
  //setup the request, you can also use CURLOPT_URL
  $ch = curl_init();
  $mystr = mb_convert_encoding('https://diavgeia.gov.gr/luminapi/opendata/decisions/'.$ada,'utf-8',"iso-8859-7");
  curl_setopt($ch, CURLOPT_URL, $mystr);
  // Returns the data/output as a string instead of raw data
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  //Set your auth headers
  // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  //     'Content-Type: application/json',
  //     'Authorization: Bearer ' . $TOKEN
  //     ));

  // get stringified data/output. See CURLOPT_RETURNTRANSFER
  $data = curl_exec($ch);
  
  // close curl resource to free up system resources 
  curl_close($ch);
  $dt = json_decode($data);

  return mb_convert_encoding($dt->subject, 'iso-8859-7', 'utf-8');
}

function display_school_requests($sch, $sxol_etos, $mysqlconnection, $guest = true){
    $query = "SELECT * from school_requests where school=$sch AND sxol_etos=$sxol_etos ORDER BY submitted DESC";
    $res = mysqli_query($mysqlconnection, $query);
    if (mysqli_num_rows($res) > 0) {
        echo $guest ? "<h1>Αιτήματα Σχολικής Μονάδας</h1>" :
        "<h1><a href='requests.php'>Αιτήματα Σχολικής Μονάδας</a></h1>";
        echo "<table id=\"mytbl4\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>Κείμενο αιτήματος</th>";
        echo "<th>Σχόλιο Δ/νσης</th>";
        echo "<th>Διεκπεραίωση</th>";
        echo "<th>Ημ/νία Υποβολής</th>";
        echo "<th>Ημ/νία Διεκπεραίωσης</th>";
        echo "</tr></thead>\n<tbody>";
        while ($row = mysqli_fetch_array($res)){
            echo "<tr>";
            echo "<td>".$row['id']."</td>";
            echo "<td>".$row['request']."</td>";
            echo "<td>".$row['comment']."</td>";
            echo "<td>";
            echo $row['done'] ? 'Ναι' : 'Όχι';
            echo "</td>";
            echo "<td>";
            echo date("d-m-Y H:m:s", strtotime($row['submitted']));
            echo "</td>";
            echo "<td>";
            echo $row['done'] ? date("d-m-Y H:m:s", strtotime($row['handled'])) : '';
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }
}
?>
