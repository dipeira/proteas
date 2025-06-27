<html>
  <head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Πίνακας λειτουργικών κενών</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <?php
    // include all datatables related files
    require_once('../js/datatables/includes.html');
    ?>
    <script type="text/javascript">    
        $(document).ready(function() { 
            /* Init DataTables */
            $('#mytbl').DataTable({
                paging: false,
                fixedHeader: true,
                language: {
                    url: '../js/datatables/greek.json'
                },
                ordering: false,
                pageLength: 20,
                lengthMenu: [[10, 20, 50, -1], [10, 20, 50, "Όλες"]],
                dom: 'Bfrt',
                buttons: [
                    {
                        extend: 'copy',
                        text: 'Αντιγραφή',
                    },
                    {
                        extend: 'excel',
                        text: 'Εξαγωγή σε excel',
                        filename: 'export'
                    },
                    {
                        extend: 'print',
                        text: 'Εκτύπωση',
                    }
                ]
            });
        });
    </script>
        <style>
            table.imagetable th {
                padding: 0px;
                font-weight: normal;
                font-size: 12px;
            }
        </style>
  </head>

<?php    
    require_once"../config.php";
    require_once"../include/functions.php";
    session_start();

    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
    
    // only dhmosia (type2 = 0)
    $query = "SELECT * from school WHERE type2 = 0 AND type = 1 AND anenergo=0 ORDER BY name";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);

    $oligothesia = isset($_GET['oligothesia']) ? true : false;
    $show_required = isset($_GET['required']) ? true : false;

    echo "<body>";
    require '../etc/menu.php';
    echo "<h3>Πίνακας λειτουργικών κενών</h3>";
    if ($_SESSION['userlevel'] == 3){
        echo "Σφάλμα: Δεν επιτρέπεται η πρόσβαση...";
        echo "<br><br><INPUT TYPE='button' class='btn-red' VALUE='Αρχική σελίδα' onClick=\"parent.location='../index.php'\">";
        die();
      }
    echo $oligothesia ? 
        "<p><a href='report_leit.php'>4/θέσια & άνω</a>&nbsp;&nbsp;Ολιγοθέσια</p>" :
        "<p>4/θέσια & άνω&nbsp;&nbsp;<a href='report_leit.php?oligothesia=1'>Ολιγοθέσια</a></p>";
    echo "<center>";
    $i=0;
    ob_start();
    echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"1\">\n";
    echo "<thead>";
    
if ($oligothesia) {
    echo "<tr><th>Κωδ.</th>";
    echo "<th>Ονομασία</th>";
    echo "<th>Οργ.</th>";
    echo "<th>Λειτ.</th>";
    echo "<th>Ολ.</th>";
    echo "<th>Τοπ.<br>ΠΕ70</th>";
    echo "<th>Ωρ. Πρ.</th>";
    echo "<th>Ωρ. Ολ.</th>";
    echo "<th>Συν. Ωρ.</th>";
}
if (!$oligothesia) {
    echo "<tr><th rowspan=2>Κωδ.</th>";
    echo "<th rowspan=2>Ονομασία</th>";
    echo "<th rowspan=2>Οργ.</th>";
    echo "<th rowspan=2>Λειτ.</th>";
    echo "<th rowspan=2>Ολ.</th>";
    echo "<th rowspan=2>Τοπ.<br>ΠΕ70</th>";
    // new
    echo "<th rowspan=2>Ωρ. Πρ.</th>";
    echo "<th rowspan=2>Ωρ. Ολ.</th>";
    echo "<th rowspan=2>Συν. Ωρ.</th>";
    echo "<th rowspan=2>Συν. Παρ.</th>";
    if ($show_required){
        echo "<th rowspan=2>Απ.05-07</th>";
        echo "<th rowspan=2>Απ.06</th>";
        echo "<th rowspan=2>Απ.08</th>";
        echo "<th rowspan=2>Απ.11</th>";
        echo "<th rowspan=2>Απ.79</th>";
        echo "<th rowspan=2>Απ.86</th>";
        echo "<th rowspan=2>Απ.91</th>";
    }
    echo "<th colspan=8>Υπάρχουν +/- <small>(με Δ/ντή, σε ώρες)</small></th>";
    echo "<th colspan=13>Λειτουργικά Κενά +/- <small>(σε ώρες)</small></th>";
    echo "</tr>";
    echo "<th>05</th><th>07</th><th>06</th><th>08</th><th>11</th><th>79</th><th>91</th><th>86</th>";
    echo "<th>70</th>";
} else {
    echo "<th>Υπ.70</th>";
}
    
if (!$oligothesia) {
    echo "<th>05</th><th>07</th><th>06</th><th>08</th><th>11</th><th>79</th><th>91</th><th>86</th>";
    echo "<th>70</th><th>70+Ολ+ΠΖ <strong>(A)</strong></th><th>70+Ολ+ΠΖ</strong></th>";
} else {
    echo "<th>Απαιτ.70</th><th>Απαιτ.70<br>+(Ολ+ΠΖ) <strong>(A)</strong></th>";
}
    
    echo "<th>+/- 08,11,<br>79,91 <strong>(B)</strong></th><th>A+B</th><th>+/- T.E.</th>";
    echo "</tr>";
    echo "</thead>\n<tbody>\n";
while ($i < $num)
    // while ($i < 4) // for testing
    // (problem @ 87)
{        
    $sch = mysqli_result($result, $i, "id");
    $name = getSchool($sch, $mysqlconnection);
    $code = mysqli_result($result, $i, "code");
    $organikothta = mysqli_result($result, $i, "organikothta");
    $pe0507 = explode('|', mysqli_result($result, $i, "pe0507"));
        
    //// Oloimera
    $classes = explode(",", mysqli_result($result, $i, "students"));
    $tmimata_exp = explode(",", mysqli_result($result, $i, "tmimata"));

    $oloimero_stud = $classes[6];
    $oloimero_tea = $tmimata_exp[6];
    $oloimero = "$oloimero_stud ($oloimero_tea)";
    ////

    // compute leitoyrgikothta
    $tmimata_exp = explode(",", mysqli_result($result, $i, "tmimata"));
    $leit = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
    // skip depending on requested schools
    if (($oligothesia & $leit >=4) || (!$oligothesia && $leit < 4)) {
        $i++;
        continue;
    }
                
    // call ektimhseis_wrwn function
    $results = ektimhseis_wrwn($sch, $mysqlconnection, $sxol_etos);
    // required
    $req = $results['required'];
    // available hours
    $av = $results['available'];
    // hour diffs (+/-)
    $df = $results['diff'];
    if (!$df) {
        $i++;
        continue;
    }
        
    // τοποθετηθέντες ΠΕ70
    $qry = "SELECT count(*) as cnt FROM employee WHERE sx_yphrethshs = $sch AND klados=2 AND status=1 AND thesi IN (0,1,2)";
    $rs = mysqli_query($mysqlconnection, $qry);
    $top70 = mysqli_result($rs, 0, "cnt");
    $syntop70 += $top70;
    //
    echo "<tr>";
    echo "<td>$code</td>";
    echo "<td><a href=\"../school/school_status.php?org=$sch\" target=\"_blank\">$name</a></td>";
    echo "<td>$organikothta</td>";
    echo "<td>".$results['leit']."</td>";
    echo "<td>$oloimero</td>";
    echo "<td>$top70</td>";
    // new
    echo $oligothesia ? 
        "<td>".($results['leit']*30)."</td>" :
        "<td>".($results['leit']*30)."</td>";
    $OP = $req['O'] + $req['P'];
    echo "<td>".$OP."</td>"; // olohm + PZ
    //$diffOP = $df['70']-$df['OP'];
    //echo "<td>".$diffOP."</td>"; // olohm
    // echo $oligothesia ?
    //     "<td>".($results['leit']*30+$OP)."</td>" : 
    //     "<td>".($results['leit']*30+$OP)."</td>" ;
    echo "<td>".($results['leit']*30+$OP)."</td>";

    if (!$oligothesia) {
        $all_av = array_sum($av);
        echo "<td>$all_av</td>";
        if ($show_required) {
            echo "<td>".($req['05']+$req['07'])."</td>"; 
            echo "<td>".$req['06']."</td>";
            echo "<td>".$req['08']."</td>";
            echo "<td>".$req['11']."</td>";
            echo "<td>".$req['79']."</td>";
            echo "<td>".$req['86']."</td>";
            echo "<td>".$req['91']."</td>";
        }
        echo "<td>".(int)$av['05']."</td><td>".(int)$av['07']."</td><td>".(int)$av['06']."</td><td>".(int)$av['08']."</td><td>".(int)$av['11']."</td><td>".(int)$av['79']."</td><td>".(int)$av['91']."</td><td>".(int)$av['86']."</td>";
    }
    $telPE70 = $df['70']-$OP;
    echo "<td>".(int)$av['70']."</td>";
    if (!$oligothesia) {
        echo tdc2($df['05'],$pe0507[1]).tdc2($df['07'],$pe0507[3]).tdc($df['06']).tdc($df['08']).tdc($df['11']).tdc($df['79']).tdc($df['91']).tdc($df['86']);
    }
    echo tdc($df['70']).tdc($telPE70);
    echo '<td>'.hours_to_teachers_adaptive($telPE70).'</td>';
    
    $koines = $df['08']+$df['11']+$df['79']+$df['91'];
    echo tdc((int)$koines); // apait. 08,11,79,91
    echo tdc($telPE70+$koines);
    echo isset($df['TE']) ? tdc($df['TE'], null, false) : "<td></td>";
    echo "</tr>\n";

    $par_sum['05'] += $av['05'];
    $par_sum['07'] += $av['07'];
    $par_sum['06'] += $av['06'];
    $par_sum['08'] += $av['08'];
    $par_sum['11'] += $av['11'];
    $par_sum['79'] += $av['79'];
    $par_sum['91'] += $av['91'];
    $par_sum['86'] += $av['86'];
    $par_sum['70'] += $av['70'];
        
    $df_sum['05'] += $df['05'];
    $kena_sum['05'] += $df['05'] < 0 ? $df['05'] : 0;
    $df_sum['07'] += $df['07'];
    $kena_sum['07'] += $df['07'] < 0 ? $df['07'] : 0;
        
    $df_sum['06'] += $df['06'];
    $kena_sum['06'] += $df['06'] < 0 ? $df['06'] : 0;
        
    $df_sum['08'] += $df['08'];
    $kena_sum['08'] += $df['08'] < 0 ? $df['08'] : 0;
        
    $df_sum['11'] += $df['11'];
    $kena_sum['11'] += $df['11'] < 0 ? $df['11'] : 0;
        
    $df_sum['79'] += $df['79'];
    $kena_sum['79'] += $df['79'] < 0 ? $df['79'] : 0;
        
    $df_sum['91'] += $df['91'];
    $kena_sum['91'] += $df['91'] < 0 ? $df['91'] : 0;
        
    $df_sum['86'] += $df['86'];
    $kena_sum['86'] += $df['86'] < 0 ? $df['86'] : 0;
        
    $df_sum['70'] += $df['70'];
    $kena_sum['70'] += $df['70'] < 0 ? $df['70'] : 0;
        
    $df_sum['OP'] += $df['OP'];

    $df_sum['TE'] += $df['TE'];

    $i++;                        
} // of while

    // convert hour sums to teachers (23 hours each)
    $par_sum_t = array_map(hours_to_teachers, $par_sum);
    $df_sum_t = array_map(hours_to_teachers, $df_sum);
    $kena_sum_t = array_map(hours_to_teachers, $kena_sum);
    
    echo "<tr><td>ΣΥΝΟΛΑ</td><td></td><td></td><td></td><td></td><td></td><td>";
    if ($show_required){
        echo "<td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
    }
if (!$oligothesia) {
    echo "</td><td></td><td></td><td></td>";
    echo "<td>".$par_sum['05']."</td><td>".$par_sum['07']."</td><td>".$par_sum['06']."</td><td>".$par_sum['08']."</td><td>".$par_sum['11']."</td><td>".$par_sum['79']."</td><td>".$par_sum['91']."</td><td>".$par_sum['86']."</td>";
} else {
    echo "<td></td><td></td>";
}
    echo "<td>".$par_sum['70']."</td>\n";
if (!$oligothesia) {
    echo "<td>".$df_sum['05']."</td><td>".$df_sum['07']."</td><td>".$df_sum['06']."</td><td>".$df_sum['08']."</td><td>".$df_sum['11']."</td><td>".$df_sum['79']."</td><td>".$df_sum['91']."</td><td>".$df_sum['86']."</td>";
} 
    echo "<td>".$df_sum['70']."</td><td>".$df_sum['OP']."</td><td></td><td></td><td></td><td></td>\n";
    echo "</tr>";
    
    echo "<tr><td>ΣΥΝΟΛΑ (εκπ)</td><td></td><td></td><td></td><td></td><td></td><td>";
if (!$oligothesia) {
    if ($show_required){
        echo "<td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
    }
    echo "</td><td></td><td></td><td></td>";
    echo "<td>".$par_sum_t['05']."</td><td>".$par_sum_t['07']."</td><td>".$par_sum_t['06']."</td><td>".$par_sum_t['08']."</td><td>".$par_sum_t['11']."</td><td>".$par_sum_t['79']."</td><td>".$par_sum_t['91']."</td><td>".$par_sum_t['86']."</td>";
} else {
    echo "<td></td><td></td>";
}
    echo "<td>".$par_sum_t['70']."</td>\n";
if (!$oligothesia) {
    echo "<td>".$df_sum_t['05']."</td><td>".$df_sum_t['07']."</td><td>".$df_sum_t['06']."</td><td>".$df_sum_t['08']."</td><td>".$df_sum_t['11']."</td><td>".$df_sum_t['79']."</td><td>".$df_sum_t['91']."</td><td>".$df_sum_t['86']."</td>";
}
    echo "<td>".$df_sum_t['70']."</td><td>".$df_sum_t['OP']."</td><td></td><td></td>\n";
    echo "<td></td><td>".$df_sum['TE']."</td>";
    
    //echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
    //echo "<td></td><td colspan=3>MONO KENA</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
    //echo "<td>".$kena_sum['05-07']."</td><td>".$kena_sum['06']."</td><td>".$kena_sum['08']."</td><td>".$kena_sum['11']."</td><td>".$kena_sum['79']."</td><td>".$kena_sum['91']."</td><td>".$kena_sum['86']."</td><td>".$kena_sum['70']."</td><td></td><td></td><td></td>\n";

    //echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td>";
    //echo "<td></td><td></td><td colspan=3>MONO KENA (εκπ)</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
    //echo "<td>".$kena_sum_t['05-07']."</td><td>".$kena_sum_t['06']."</td><td>".$kena_sum_t['08']."</td><td>".$kena_sum_t['11']."</td><td>".$kena_sum_t['79']."</td><td>".$kena_sum_t['91']."</td><td>".$kena_sum_t['86']."</td><td>".$kena_sum_t['70']."</td><td></td><td></td><td></td>\n";
    
// if (!$oligothesia) {
//     echo "<tr><td></td><td></td><td></td><td></td><td></td>";//<td></td>";
//     echo "<td></td><td></td><td></td><td></td><td></td>";
//     echo "<td><i>05-07</i></td><td><i>06</i></td><td><i>08</i></td><td><i>11</i></td><td><i>79</i></td><td><i>91</i></td><td><i>86</i></td><td><i>70</i></td>";
//     echo "<td><i>05-07</i></td><td><i>06</i></td><td><i>08</i></td><td><i>11</i></td><td><i>79</i></td><td><i>91</i></td><td><i>86</i></td><td><i>70</i></td><td><i>70-(Ολ+ΠΖ)</i></td><td></td><td></td></i>";
//     echo "<td></td>";
// }
    echo "</tr>";
    echo "</tbody></table>";
    echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
?>
        </body>
        </html>
                
