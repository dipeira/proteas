<?php

function getKlados($id,$conn,$full = false)
{
    $query = "SELECT perigrafh,onoma from klados where id=".$id;
    $result = mysqli_query($conn, $query);
    //if (!$result) 
    //    die('Could not query:' . mysqli_error($conn));
    $row = mysqli_fetch_assoc($result);
    return $full ? $row['perigrafh'] . ' (' . $row['onoma'] . ')' :
      $row['perigrafh'];
      //"<span title='".$row['onoma']."'>".$row['perigrafh']."</span>";
}
    
function getSchool($id,$conn)
{
    $query = "SELECT name from school where id=".$id;
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        return;
    }
    //    die('Could not query:' . mysqli_error($conn));
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
    //die('Could not query:' . mysqli_error($conn));
    else {
        return mysqli_result($result, 0);
    }    
}
function getSchoolNameFromCode($code, $conn)
{
    $query = "SELECT name from school where code = '$code'";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return false;
    }
    //die('Could not query:' . mysqli_error($conn));
    else {
        return mysqli_result($result, 0);
    }    
}

function get_school_type($id, $conn) {
  $query = "SELECT type,type2 from school where id = $id";
  $result = mysqli_query($conn, $query);
  $row = mysqli_fetch_array($result);
  switch ($row['type2']) {
    case 0:
      $sch_type2 = 'Δημόσιο';
      break;
    case 1:
      $sch_type2 = 'Ιδιωτικό';
      break;
    case 2:
      $sch_type2 = 'Ειδικό';
      break;
  }
  switch ($row['type']) {
    case 0:
      $sch_type = 'ς Φορέας/Περιοχή κλπ';
      break;
    case 1:
      $sch_type = ' Δημοτικό Σχολείο';
      break;
    case 2:
      $sch_type = ' Νηπιαγωγείο';
      break;
  }
  return $sch_type2.$sch_type;
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

function get_type($typeid,$conn)
{
    $query = "SELECT * from ektaktoi_types WHERE id=$typeid";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
      return;
        //die('Could not query:' . mysqli_error($conn));
    }
    $typos=mysqli_result($result, $i, "type");
    return $typos;
}
function getDimos($id,$conn)
{
    $query = "SELECT name from dimos where id=".$id;
    $result = mysqli_query($conn, $query);
    //if (!$result) 
    //    die('Could not query:' . mysqli_error($conn));
    //else
    $dimos = mysqli_result($result, 0);
    if (!$dimos) {
        return "Άγνωστος";
    } else {
        return $dimos;
    }
}
function getSchDimos($id,$conn)
{
    $query = "SELECT d.name from school s JOIN dimos d ON s.dimos = d.id where s.id=".$id;
    //echo $query;
    $result = mysqli_query($conn, $query);
    //if (!$result) 
    //    die('Could not query:' . mysqli_error($conn));
    //else
    $dimos = mysqli_result($result, 0);
    if (!$dimos) {
        return "Άγνωστος";
    } else {
        return $dimos;
    }
}

function get_anatr($id, $mysqlconnection) 
{
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

function getNamefromTbl($conn, $tbl, $id)
{
    $query = "SELECT * from $tbl WHERE id=$id";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
    return;
        //die('Could not query:' . mysqli_error($conn));
    }
    $name=mysqli_result($result, 0, "name");
    return $name;
}
function getIDfromTbl($conn, $tbl, $name)
{
    $query = "SELECT * from $tbl WHERE name=$name";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        die('Could not query:' . mysqli_error($conn));
    }
    $id=mysqli_result($result, 0, "id");
    return $id;
}

// returns school category
function getCategory($cat)
{
    switch ($cat){
    case 0:
        return "Άγνωστο";
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

// get_orgs
// returns number of teachers who belong organically per expertise
function get_orgs($id, $mysqlconnection, $eidiko = false)
{
    // organika, not @ entaksis
    $query = "SELECT k.perigrafh as klname, count(*) as plithos 
    FROM employee e join klados k on e.klados = k.id 
    WHERE e.sx_organikhs='$id' AND status IN (1,3,5) AND thesi IN (0,1,2) AND org_ent = 0
    GROUP BY klados";
    $result = mysqli_query($mysqlconnection, $query);
    // initialize array
    if ($eidiko) {
        $ret = array(
            'ΠΕ70' => 0,
            'ΠΕ11' => 0, 
            'ΠΕ06' => 0, 
            'ΠΕ79' => 0, 
            'ΠΕ05' => 0, 
            'ΠΕ07' => 0, 
            'ΠΕ08' => 0, 
            'ΠΕ86' => 0, 
            'ΠΕ91' =>0,
            'ΠΕ21' =>0,
            'ΠΕ23' =>0,
            'ΠΕ25' =>0,
            'ΠΕ26' =>0,
            'ΠΕ28' =>0,
            'ΠΕ29' =>0,
            'ΠΕ30' =>0,
            'ΔΕ1ΕΒΠ' =>0,
            'ent' => 0
        );
    } else {
        $ret = array('ΠΕ70' => 0,'ΠΕ11' => 0, 'ΠΕ06' => 0, 'ΠΕ79' => 0, 'ΠΕ05' => 0, 'ΠΕ07' => 0, 'ΠΕ08' => 0, 'ΠΕ86' => 0, 'ΠΕ91' =>0, 'ent' => 0);
    }
    
    while ($row = mysqli_fetch_array($result)){
      $plithos = strval($row['plithos']);
      $kl = $row['klname'];
      $ret[$kl] += $plithos;
    }
    // @ entaksis
    $query = "SELECT count(*) as plithos FROM employee e 
    WHERE e.sx_organikhs='$id' AND status IN (1,3,5) AND org_ent=1";
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

?>