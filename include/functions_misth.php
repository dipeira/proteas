<?php

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
    return $ret;
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
    $mk_date = days2date($anatr + $subtract + $mk*720);
    $formatted_time = $mk_date[2].'/'.$mk_date[1].'/'.$mk_date[0];
    return array(
      'mk' => $mk,
      'ymd' => days2ymd($result),
      'hm_mk' => $formatted_time
    );
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

?>