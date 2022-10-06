<?php

////////////////////
// school functions
////////////////////
/*
* anagkes_wrwn: Compute required hours based on oloimero schedule 2016-17
* Returns required weekly hours depending on number of classes
* tmimata: 0: A, 1: B, 2: Γ, 3: Δ, 4: E, 5: ΣΤ
* 6: Ολ. 15.00, 7: Ολ. 16:00, 8: ΠΖ
* 1/9/2022: added 10 weekly hours for tm[9]: 16.00-17.30
*/
function anagkes_wrwn($tm)
{
    if (!is_array($tm)){
        return [];
    }
    $artm = $tm[0]+$tm[1]+$tm[2]+$tm[3]+$tm[4]+$tm[5];
    // oligothesia (<4/thesia)
    if ($artm < 4) {
        $hours = [];
        $hours['70'] = $artm * 30;
        // oloimero
        $hours['O'] = $tm[6]>0 ? $tm[6]*10 + $tm[7]*5 + $tm[9]*10: 0;
        // PZ
        $hours['P'] = $tm[8]*5;
        return $hours;
    }
    // 4/thesia
    elseif ($artm == 4) {
        $hours = [];
        $hours['05-07'] = $tm[4]*1;
        $hours['06'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*3 + $tm[4]*3;
        $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[4]*1;
        $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[4]*2;
        $hours['79'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1;
        $hours['91'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1;
        $hours['86'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[4]*1;
        $hours['70'] = $tm[0]*20 + $tm[1]*20 + $tm[2]*20 + $tm[4]*22;
        // oloimero
        $hours['O'] = $tm[6]>0 ? $tm[6]*10 + $tm[7]*5 + $tm[9]*10 : 0;
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
            $hours['06'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*3 + $tm[4]*3 + $tm[5]*3;
            $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[4]*1 + $tm[5]*1;
            $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[4]*2 + $tm[5]*2;
            $hours['79'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[4]*1 + $tm[5]*1;
            $hours['91'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1;
            $hours['86'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[4]*1 + $tm[5]*1;
            $hours['70'] = $tm[0]*20 + $tm[1]*20 + $tm[2]*20 + $tm[4]*20 + $tm[5]*20;
            // alliws ean synd/lia 5-6
        } else {
            $hours['05-07'] = $tm[4]*1;
            $hours['06'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*3 + $tm[3]*3 + $tm[4]*3;
            $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1;
            $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[3]*3 + $tm[4]*2;
            $hours['79'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1;
            $hours['91'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1;
            $hours['86'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1;
            $hours['70'] = $tm[0]*20 + $tm[1]*20 + $tm[2]*20 + $tm[3]*20 + $tm[4]*22;
        }
        // oloimero
        $hours['O'] = $tm[6]>0 ? $tm[6]*10 + $tm[7]*5 + $tm[9]*10 : 0;
        // PZ
        $hours['P'] = $tm[8]*5;
        return $hours;
    }
    // 6/thesia+anw
    else {
        $hours = [];
        $hours['05-07'] = $tm[4]*2 + $tm[5]*2;
        $hours['06'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*3 + $tm[3]*3 + $tm[4]*3 + $tm[5]*3;
        $hours['08'] = $tm[0]*2 + $tm[1]*2 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1 + $tm[5]*1;
        $hours['11'] = $tm[0]*3 + $tm[1]*3 + $tm[2]*3 + $tm[3]*3 + $tm[4]*2 + $tm[5]*2;
        $hours['79'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1 + $tm[5]*1;
        $hours['91'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1;
        $hours['86'] = $tm[0]*1 + $tm[1]*1 + $tm[2]*1 + $tm[3]*1 + $tm[4]*1 + $tm[5]*1;
        $hours['70'] = $tm[0]*20 + $tm[1]*20 + $tm[2]*20 + $tm[3]*20 + $tm[4]*20 + $tm[5]*20;
        // oloimero
        $hours['O'] = $tm[6]>0 ? $tm[6]*10 + $tm[7]*5 + $tm[9]*10 : 0;
        // PZ
        $hours['P'] = $tm[8]*5;
        return $hours;
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
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
    // get tmimata
    $query = "SELECT students,tmimata,entaksis,leitoyrg,vivliothiki,type2 from school WHERE id='$sch'";
    $result = mysqli_query($mysqlconnection, $query);
    $tmimata_exp = strlen(mysqli_result($result, 0, "tmimata")) ? explode(",", mysqli_result($result, 0, "tmimata")) : '';
    $vivliothiki = mysqli_result($result, 0, "vivliothiki");
    $eidiko = mysqli_result($result, 0, "type2") == 2 ? true : false;
    $leit = is_array($tmimata_exp) && count($tmimata_exp) > 0 ? 
        $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5] :
        0;
    $oligothesio = $leit < 4 ? true : false;
    // entaksis
    $entaksis = explode(',', mysqli_result($result, 0, "entaksis"));
    $has_entaxi = strlen($entaksis[0])>1 ? 1 : 0;
    // synolo mathitwn (gia yp/ntes)
    if (strlen(mysqli_result($result, 0, "tmimata"))){
        $classes = explode(",", mysqli_result($result, 0, "students"));
        $synolo_pr = $classes[0]+$classes[1]+$classes[2]+$classes[3]+$classes[4]+$classes[5];
    } else {
        $synolo_pr = 0;
    }
        
    // for PZ: at least 7 stud for leit < 9, at least 10 for leit >= 9
    if ( ($leit < 9 && $classes[7] < 7 || $leit >= 9 && $classes[7] < 10) && is_array($tmimata_exp) > 0) {
        $tmimata_exp[8] = 0;
    }
    // Απαιτούμενες ώρες
    $reqhrs = anagkes_wrwn($tmimata_exp);
    // ώρες Δ/ντή
    $query = "SELECT *,e.id emp_id from employee e JOIN klados k ON e.klados = k.id WHERE sx_yphrethshs='$sch' AND status=1 AND thesi = 2";
    $result = mysqli_query($mysqlconnection, $query);
    if (mysqli_num_rows($result)) {
        $dnthrs = wres_dnth($leit);
        $klados = mysqli_result($result, 0, "klados");
        $klper = mysqli_result($result, 0, "perigrafh");
        $emp_id = mysqli_result($result, 0, "emp_id");
        $extra = '';
        // check if ypeythinos vivliothikis
        if ($emp_id == $vivliothiki) {
            $dnthrs -= MEIWSH_VIVLIOTHIKIS;
            $extra = ' <i><small>(Υπεύθυνος/-η Βιβλιοθήκης)<small></i>';
        }
        $avhrs[$klados] = $dnthrs;
        // ώρες Δ/ντή στην ανάλυση
        $ar = Array(
            'fullname' => mysqli_result($result, 0, 2).' '.mysqli_result($result, 0, 1)." <small>(Δ/ντής/-ντρια)</small> ".$extra,
                'klados' =>  mysqli_result($result, 0, "perigrafh"), 
                'hours' => $dnthrs
            );
            $all[] = $ar;
            $allcnt[$klper]++;
    }
    // ώρες Υπ/ντή
    $meiwsh_ypnth = 0;
    $query_yp = "SELECT e.id,e.name, e.surname,e.klados,k.perigrafh,e.wres from employee e JOIN klados k ON e.klados = k.id WHERE sx_yphrethshs='$sch' AND status=1 AND thesi = 1";
    $result_yp = mysqli_query($mysqlconnection, $query_yp);
    while ($row = mysqli_fetch_assoc($result_yp)){
        $extra = '';
        // reduce hours if students > 120 or eidiko with >30 students
        if ($synolo_pr > 120 || ($eidiko && $synolo_pr > 30)) {
            $meiwsh_ypnth = MEIWSH_YPNTH;
        }
        
        $klados = $row['klados'];
        $meiwsh_ypnth_klados = $row['perigrafh'];
        $avhrs[$klados] -= $meiwsh_ypnth;
        $hours = $row['wres'];
        // check if ypeythinos vivliothikis
        if ($row['id'] == $vivliothiki) {
            $avhrs[$row['klados']] -= MEIWSH_VIVLIOTHIKIS;
            $extra = ' <i><small>(Υπεύθυνος/-η Βιβλιοθήκης)<small></i>';
            $hours -= MEIWSH_VIVLIOTHIKIS;
        }

        // ώρες Υπ/ντή στην ανάλυση
        $ar = Array(
            'fullname' => $row['surname'].' '.substr($row['name'], 0, 6).'<small> (Υπ/ντής/-ντρια)</small>'.$extra,
            'klados' =>  $meiwsh_ypnth_klados, 
            'hours' => $hours - $meiwsh_ypnth
        );
        $all[] = $ar;
        $allcnt[$meiwsh_ypnth_klados]++;
    }
    
    // ώρες υπηρετούντων (Μόνιμοι εκπ/κοί - υπ/ντές, εκτός Τ.Ε., T.Y.)
    //$query = "SELECT klados, sum(wres) as wres from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,1) GROUP BY klados";
    if ($oligothesio) {
        $query = "SELECT e.klados, count(*) as plithos FROM employee e join yphrethsh y on e.id = y.emp_id WHERE y.yphrethsh='$sch' AND y.sxol_etos = $sxoletos AND e.status=1 AND e.thesi in (0,1) AND e.ent_ty NOT IN (1,2) GROUP BY klados";
        $result = mysqli_query($mysqlconnection, $query);
        while ($row = mysqli_fetch_array($result)){
            $plithos = strval($row['plithos']);
            $kl = strval($row['klados']);
            $avhrs[$kl] += $plithos * 30;
        }
    } else {
        $query = "SELECT e.klados, sum(y.hours) as wres FROM employee e join yphrethsh y on e.id = y.emp_id WHERE y.yphrethsh='$sch' AND y.sxol_etos = $sxoletos AND e.status=1 AND e.thesi in (0,1) AND e.ent_ty NOT IN (1,2) GROUP BY klados";
        $result = mysqli_query($mysqlconnection, $query);
        while ($row = mysqli_fetch_array($result)){
            $kl = strval($row['klados']);
            $avhrs[$kl] += $row['wres'];
        }
    }
    if ($print) {
        // αναλυτικά...
        $query = "SELECT e.id,e.name, e.surname,e.klados,k.perigrafh, y.hours FROM employee e join yphrethsh y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh='$sch' AND y.sxol_etos = $sxoletos AND e.status=1 AND e.thesi in (0) AND e.ent_ty NOT IN (1,2) ORDER BY e.klados";
        $result = mysqli_query($mysqlconnection, $query);
        while ($row = mysqli_fetch_array($result)){
            $extra = '';
            $hours = $row['hours'];
            // check if ypeythinos vivliothikis
            if ($row['id'] == $vivliothiki) {
            $avhrs[$row['klados']] -= MEIWSH_VIVLIOTHIKIS;
            $extra = ' <i><small>(Υπεύθυνος/-η Βιβλιοθήκης)<small></i>';
            $hours -= MEIWSH_VIVLIOTHIKIS;
            }
            $ar = Array(
            'fullname' => $row['surname'].' '.substr($row['name'], 0, 6).$extra,
            'klados' => $row['perigrafh'], 
            'hours' => $hours
            );
            $all[] = $ar;
            $allcnt[$row['perigrafh']]++;
            
        }
    }
    // αναπληρωτές (εκτός ΖΕΠ / ΕΚΟ (type=6) & ent_ty 1,2,3 (ένταξης/παράλληλη/ΤΥ) & type 4,5,6 (ΕΕΠ,ΕΒΠ,ΖΕΠ/ΕΚΟ))
    $query = "SELECT klados,sum(y.hours) as wres FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where y.yphrethsh=$sch AND y.sxol_etos = $sxoletos AND e.status = 1 AND e.type NOT IN (4,5,6) AND e.ent_ty NOT IN (1,2,3) GROUP BY klados";
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
            $fname = $row['surname'] . ' '. substr($row['name'], 0, 6).' *';
            $fname .= $row['thesi'] == 2 ? '<small> (Τμ.Ένταξης)</small>' : '';
            $fname .= $row['thesi'] == 3 ? '<small> (Παράλληλη)</small>' : '';
            $fname .= $row['thesi'] == 4 ? '<small> (Τάξη Υποδοχής)</small>' : '';
            $ar = Array('fullname' => $fname, 'klados' => $row['perigrafh'], 'hours' => $row['hours']);
            $all[] = $ar;
            $allcnt[$row['perigrafh']]++;
        }
    }
    // PE70 entaksis
    if ($has_entaxi > 0) {
        $qry = "SELECT count(*) as pe70 FROM employee WHERE sx_yphrethshs = $sch AND klados in (2,18,19) AND status=1 and ent_ty = 1";
        $res = mysqli_query($mysqlconnection, $qry);
        $top_ent = mysqli_result($res, 0, 'pe70');
        $avhrs['TE'] = $top_ent;
        $ret['TE'] = $top_ent - $has_entaxi;
        if ($print) {
            // αναλυτικά...
            $query = "SELECT e.name,e.surname,k.perigrafh, y.hours FROM employee e join yphrethsh y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh='$sch' AND y.sxol_etos = $sxoletos AND e.status=1 AND e.ent_ty = 1 ORDER BY e.klados";
            $result = mysqli_query($mysqlconnection, $query);
            while ($row = mysqli_fetch_array($result)){
                $ar = Array('fullname' => $row['surname'].' '.substr($row['name'], 0, 6). ' (Τ.Ε.)', 'klados' => $row['perigrafh'], 'hours' => $row['hours']);
                $all[] = $ar;
                //$allcnt[$row['perigrafh']]++;
            }
        }
    }
    
    // PE70 @ T.Y.
    if ($print) {
        // αναλυτικά...
        $query = "SELECT e.name,e.surname,k.perigrafh, y.hours FROM employee e join yphrethsh y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh='$sch' AND y.sxol_etos = $sxoletos AND e.status=1 AND e.ent_ty = 2 ORDER BY e.klados";
        $result = mysqli_query($mysqlconnection, $query);
        while ($row = mysqli_fetch_array($result)){
            $ar = Array('fullname' => $row['surname'].' '.substr($row['name'], 0, 6). ' (Τ.Y.)', 'klados' => $row['perigrafh'], 'hours' => $row['hours']);
            $all[] = $ar;
            //$allcnt[$row['perigrafh']]++;
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
            echo $vivliothiki > 0 ? 'Υπευθύνου Βιβλιοθήκης: '.MEIWSH_VIVLIOTHIKIS.' ώρες<br>' : '';
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
            echo "<tr><td>".$row['fullname']."</td><td>".$row['klados']."</td><td>".$row['hours']."</td></tr>";
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


function get_leitoyrgikothta($id, $mysqlconnection)
{
    $query = "SELECT tmimata,type,klasiko from school WHERE id='$id'";
    $result = mysqli_query($mysqlconnection, $query);
    $type = mysqli_result($result, 0, 'type');
    
    // return 0 if no tmimata are saved
    if (mysqli_result($result, 0, "tmimata") === NULL || strlen(mysqli_result($result, 0, "tmimata")) == 0 ){
        return 0;
    }
    // if nip
    if ($type == 2) {
        
        $klasiko_exp = explode(",", mysqli_result($result, 0, "klasiko"));
        // fill array blanks with zeroes
        foreach($klasiko_exp as &$val) {
            if(empty($val)) { $val = 0; }
        }
        $klasiko_tm = 0;
        $klasiko_tm += $klasiko_exp[0]+$klasiko_exp[1]>0 ? 1:0;
        $klasiko_tm += $klasiko_exp[2]+$klasiko_exp[3] >0 ? 1:0;
        $klasiko_tm += $klasiko_exp[4]+$klasiko_exp[5]>0 ? 1:0;
        $klasiko_tm += $klasiko_exp[7]+$klasiko_exp[8]>0 ? 1:0;
        $klasiko_tm += $klasiko_exp[9]+$klasiko_exp[10]>0 ? 1:0;
        $klasiko_tm += $klasiko_exp[11]+$klasiko_exp[12]>0 ? 1:0;
        return $klasiko_tm;
    } else if ($type == 1) {
        $tmimata_exp = explode(",", mysqli_result($result, 0, "tmimata"));
        $leit = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
        return $leit;
    }   
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
    
    
    
function organikes_per_klados($mysqlconnection)
{
    $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ',$mysqlconnection);
    $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ',$mysqlconnection);
    $query = "SELECT COUNT( * ) as total, k.perigrafh, k.onoma FROM employee e 
        JOIN klados k 
        ON k.id = e.klados 
        WHERE status!=2 AND sx_organikhs NOT IN ($allo_pyspe, $allo_pysde) AND thesi NOT IN (4,5)
        GROUP BY klados";
    $result_mon = mysqli_query($mysqlconnection, $query);
    $ret = [];
    while($row = mysqli_fetch_array($result_mon, MYSQLI_BOTH)){
        $ret[$row['perigrafh']] = $row['total'];
    }
    return $ret;
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
    $ekswteriko = getSchoolID('Απόσπαση στο εξωτερικό',$mysqlconnection);
    $query = "SELECT k.perigrafh as eidikothta, count(*) as total
        FROM `employee` e 
        JOIN klados k ON e.klados = k.id 
        WHERE e.sx_yphrethshs=$ekswteriko group by klados";
        
    $res = mysqli_query($mysqlconnection, $query);
    $ret = [];
    while($row = mysqli_fetch_array($res, MYSQLI_BOTH)){
        $ret[$row['eidikothta']] = $row['total'];
    }
    return $ret;
}



//////////////////////
// employee functions
//////////////////////

// 23-12-2013: compute ypoloipo adeiwn - if yphrethsh = foreas adeies = 25 days
function ypoloipo_adeiwn($id, $sql)
{
    $qry2 = "SELECT sx_yphrethshs,thesi FROM employee WHERE id=$id";
    $res2 = mysqli_query($sql, $qry2);
    $sx_yphr = mysqli_result($res2, 0, "sx_yphrethshs");
    $thesi = mysqli_result($res2, 0, "thesi");
    // if apospasmenoi / dioikhtikoi
    $se_forea = getSchoolID('Απόσπαση σε φορέα',$sql);
    $dnsi = getParam('foreas', $sql);
    $se_dnsi = getSchoolID($dnsi,$sql);
    if ($sx_yphr == $se_forea || $sx_yphr == $se_dnsi || $thesi == 4) {
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


/*
* check for expired adeies idiwtikoy ergoy
*/
function check_idiwtiko($conn)
{
    $ret = "";
    $query = "SELECT id,surname,name,idiwtiko_liksi from employee WHERE idiwtiko=1 AND idiwtiko_liksi <= curdate()";
    $result = mysqli_query($conn, $query);
    if (!$result) { 
        die('Could not query:' . mysqli_error($conn));
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

// compute_meiwmeno
// for teachers with reduced teaching hours
function compute_meiwmeno($days, $hours_per_week = 14, $ypoxr = 24)
{   
    return round($days * ($hours_per_week/$ypoxr));
}

 
// display idiwtiko ergo
function idiwtika_table($emp_type, $emp_id, $mysqlconnection) {
    ?>
    <script type="text/javascript" src="../js/common.js"></script>
    <?php
    global $sxol_etos;
    $query = "SELECT * from idiwtiko where emp_type = '$emp_type' AND emp_id = $emp_id and sxol_etos = $sxol_etos";
    $result = mysqli_query($mysqlconnection, $query);
    if (!$result) {
        echo "<p>Σφάλμα αναζήτησης ιδιωτικών έργων στη Βάση Δεδομένων.<br>Παρακαλώ ελέγξτε αν υπάρχει ο πίνακας 'idiwtiko'.</p>";
        return;
    }
    if (!mysqli_num_rows($result)){
        echo "<p>Δε βρέθηκαν ιδιωτικά έργα.</p>";
        echo "<a href='idiwtiko_ergo.php?id=$emp_id&type=$emp_type&op=add'><img style='border: 0pt none;' src='../images/user_add.png'>&nbsp;Προσθήκη ιδιωτικού έργου</a>";
        return;
    }
    echo "<h4>Ιδιωτικά έργα</h4>";
    echo "<table id=\"mytbl4\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>Ενέργεια</th>";
        echo "<th>Τύπος</th>";
        echo "<th>Αρ.Πρωτ.</th>";
        echo "<th>Ημ/νία Πρωτ.</th>";
        echo "<th>Πράξη</th>";
        echo "<th>ΑΔΑ</th>";
        echo "<th>Τελ.Ενημέρωση</th>";
        echo "</tr></thead>\n<tbody>";
    while ( $row = mysqli_fetch_assoc($result) ) {
        echo "<tr>";
        echo "<td>";
        echo "<span title=\"Επεξεργασία\"><a href=\"idiwtiko_ergo.php?id=".$row['id']."&op=edit&emp_id=".$emp_id."&type=$emp_type\"><img style=\"border: 0pt none;\" src=\"../images/edit_action.png\"/></a></span>";
        echo "<span title=\"Διαγραφή\"><a href=\"javascript:confirmDelete('idiwtiko_ergo.php?id=".$row['id']."&emp_id=$emp_id&type=$emp_type&op=delete')\"><img style=\"border: 0pt none;\" src=\"../images/delete_action.png\"/></a></span>";
        echo "</td>";
        echo "<td>" . $row['type'] . "</td>";
        echo "<td>" . $row['prot_no'] . "</td>";
        echo "<td>" . date('d-m-Y', strtotime($row['prot_date'])) . "</td>";
        echo "<td>" . $row['praxi'] . "</td>";
        echo "<td>" . $row['ada'] . "</td>";
        echo "<td>" . date('d-m-Y, H:i', strtotime($row['updated'])) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    echo "<a href='idiwtiko_ergo.php?id=$emp_id&type=$emp_type&op=add'><img style='border: 0pt none;' src='../images/user_add.png'>&nbsp;Προσθήκη ιδιωτικού έργου</a>";
    return;

}

// compute the sum of PE06 hours @ nip
function top_pe06_nip($sch, $conn){
    global $sxol_etos;
    // τοποθετημένοι εκπ/κοί ΠΕ06
    // mon
    $qry = "SELECT sum(y.hours) as pe06 FROM employee e JOIN yphrethsh y ON e.id = y.emp_id WHERE y.yphrethsh = $sch AND e.klados=3 AND e.status=1 AND y.sxol_etos = $sxol_etos";
    $res = mysqli_query($conn, $qry);
    $top06m = mysqli_result($res, 0, 'pe06');
    // anapl
    $qry = "SELECT sum(y.hours) as pe06 FROM ektaktoi e JOIN yphrethsh_ekt y ON e.id = y.emp_id WHERE y.yphrethsh = $sch AND e.klados=3 AND e.status=1 AND y.sxol_etos = $sxol_etos";
    $res = mysqli_query($conn, $qry);
    $top06ana = mysqli_result($res, 0, 'pe06');
    $top06 = $top06m + $top06ana;
    return $top06;
}

?>
