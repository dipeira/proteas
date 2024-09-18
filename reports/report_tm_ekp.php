<html>
  <head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Μαθητές & Εκπαιδευτικοί</title>
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
        } );
    </script>

  </head>
  <body>
<?php
  require_once"../config.php";
  require_once"../include/functions.php";
  session_start();
  
  $subtitle_array = Array(
    '1' => 'Δημόσια Δημοτικά (όχι ειδικά)',
    '8' => 'Δημόσια Δημοτικά (προηγούμενου σχολικού έτους)',
    '2' => 'Ιδιωτικά Δημοτικά',
    '3' => 'Ειδικά Δημοτικά',
    '7' => 'Ολιγοθέσια Δημοτικά',
    '4' => 'Δημόσια Νηπιαγωγεία (όχι ειδικά) (και λειτουργικά κενά)',
    '5' => 'Ιδιωτικά Νηπιαγωγεία',
    '6' => 'Ειδικά Νηπιαγωγεία'
  );
  require '../etc/menu.php';
  echo "<h3>Μαθητές & Εκπαιδευτικοί</h3>";
  echo "<h4>".$subtitle_array[$_REQUEST['type']]."</h4>";
  echo "<table class=\"imagetable\" border='1'>";

  $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

  echo "<form action='' method='POST' autocomplete='off'>";
  // echo "<table>";
  echo "<tr><td colspan='2'>";

  $options = [
      1 => "Δημόσια Δημοτικά (όχι ειδικά)",
      8 => "Δημόσια Δημοτικά (προηγούμενου σχολικού έτους)",
      2 => "Ιδιωτικά Δημοτικά",
      3 => "Ειδικά Δημοτικά",
      7 => "Ολιγοθέσια Δημοτικά",
      4 => "Δημόσια Νηπιαγωγεία (όχι ειδικά) (και λειτουργικά κενά)",
      5 => "Ιδιωτικά Νηπιαγωγεία",
      6 => "Ειδικά Νηπιαγωγεία"
  ];

  foreach ($options as $value => $label) {
      $checked = ($value == $type) ? 'checked' : '';
      echo "<input type='radio' name='type' value='$value' $checked >$label<br>";
  }

  echo "</td></tr>";
  echo "<tr><td colspan='2'><input type='submit' value='Προβολή'>";
  echo "<input type='button' class='btn-red' value='Επιστροφή' onClick=\"parent.location='../index.php'\"></td></tr>";
  echo "</table></form>";

  echo "<br>";
                
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
    
if ($_REQUEST['type']) {
  $oligothesia = 0;
    $dim_ar = array('1', '2', '3', '7', '8');
    $nip_ar = array('4', '5', '6');       
    //if ($_GET['type'] == 1)
    if (in_array($_REQUEST['type'], $dim_ar)) {
        $type = 1;
        //type2: 0 δημόσιο, 1 ιδιωτικό, 2 ειδικό
        if ($_REQUEST['type'] == 7) {
          $oligothesia = 1;
          $type2 = 0;
        } else if ($_REQUEST['type'] == 8) {
            $type2 = 0;
        } else {
          $type2 = $_REQUEST['type'] - 1;
        }
        $query = "SELECT * from school WHERE type = $type AND type2=$type2 AND anenergo=0";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
        $previous_year = find_prev_year($sxol_etos);

        echo "<center>";
        $i = $sumschools = 0;
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr><th>Ονομασία</th>";
        echo "<th>Κωδικός</th>";
        echo "<th>Δήμος</th>";
        echo "<th>Οργ.</th>";
        echo "<th>Λειτ.</th>";
        echo "<th>Α'</th>";
        echo "<th>Β'</th>";
        echo "<th>Γ'</th>";
        echo "<th>Δ'</th>";
        echo "<th>Ε'</th>";
        echo "<th>ΣΤ'</th>";
        echo "<th>Σύν.</th>";
        echo "<th>Τμ. Α'</th>";
        echo "<th>Τμ. Β'</th>";
        echo "<th>Τμ. Γ'</th>";
        echo "<th>Τμ. Δ'</th>";
        echo "<th>Τμ. Ε'</th>";
        echo "<th>Τμ. ΣΤ'</th>";
        echo "<th>Σύν. Τμ.</th>";
        echo "<th>Τ.Ε.</th>";
        echo "<th>Μαθ Τ.Ε.</th>";
        echo "<th>ΠΕ70</th>";
        echo "<th>ΠΕ06</th>";
        echo "<th>ΠΕ11</th>";
        echo "<th>ΠΕ79</th>";
        echo "<th>Συν.προσ.</th>";
        echo "<th>Τμ. Ολ.</th>";
        echo "<th>Ολ.<br>15-16</th>";
        echo "<th>Ολ.<br>16-17</th>";
        echo "<th>Μαθ. Ολ.</th>";
        echo "<th>Τμ.<br>Π.Ζ.</th>";
        echo "<th>Μαθ.<br>Π.Ζ.</th>";
        //echo "<th>Εκπ. T.E.</th>";
        //echo "<th>Εκπ. T.Y.</th>";
        echo "</tr></thead>\n<tbody>\n";

        while ($i < $num)
        {        
            $organikothta = mysqli_result($result, $i, "organikothta");
            $sch = mysqli_result($result, $i, "id");
            $code = mysqli_result($result, $i, "code");
            $name = getSchool($sch, $mysqlconnection);
            // if user requests archives
            if ($_REQUEST['type'] == 8){
                $archive = mysqli_result($result, $i, "archive");
                $archive_unser = $archive ? unserialize($archive) : null;
                $archive_arr = explode(',',$archive_unser[$previous_year]);
                if ($archive_arr) {
                    $classes = array_slice($archive_arr, 0, 8);
                    $tmimata_exp = array_slice($archive_arr, 8, 9);
                    $has_entaxi = strlen($archive_arr[17])>1 ? 1 : 0;
                    $entaksis = Array(0,$archive_arr[18]);
                }
            } else {
                $students = mysqli_result($result, $i, "students");
                $tmimata = mysqli_result($result, $i, "tmimata");
                $entaksis = explode(',', mysqli_result($result, $i, "entaksis"));
                $has_entaxi = strlen($entaksis[0])>1 ? 1 : 0;

                $classes = explode(",", $students);
                $tmimata_exp = explode(",", $tmimata);
            }
            //$frontistiriako = mysqli_result($result, $i, "frontistiriako");
            
            $oloimero_stud = $classes[6];
            $oloimero_tea = $tmimata_exp[6];
            $ol1516 = $tmimata_exp[7] > 0 ? $tmimata_exp[7] : 0;
            $pz = $tmimata_exp[8] > 0 ? $tmimata_exp[8] : 0;
            $mpz = $classes[7];
            $ol1617 = $tmimata_exp[9] > 0 ? $tmimata_exp[9] : 0;
            //$ekp_ee = mysqli_result($result, $i, "ekp_ee");
            //$ekp_ee_exp = explode(",",$ekp_ee);
            
            $synolo = $classes[0] + $classes[1] + $classes[2] + $classes[3] + $classes[4] + $classes[5];
            $leitoyrg = $synolo_tmim = $tmimata_exp[0] + $tmimata_exp[1] + $tmimata_exp[2] + $tmimata_exp[3] + $tmimata_exp[4] + $tmimata_exp[5];
            if (($oligothesia & $leitoyrg >=4) || (!$oligothesia && $leitoyrg < 4)) {
              $i++;
              continue;
            }
            // count employees per specialty
            $ekp_ar = [];
            $qry = "SELECT k.perigrafh as klados, count(k.perigrafh) as count FROM employee e join yphrethsh y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status=1 AND e.thesi in (0,1) GROUP BY e.klados";
            $res = mysqli_query($mysqlconnection, $qry);
            while ($row = mysqli_fetch_array($res)){
                $ekp_ar[$row['klados']] = $row['count'];
            }
            $qry = "SELECT k.perigrafh as klados, count(k.perigrafh) as count FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id JOIN klados k on k.id=e.klados WHERE y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status=1 GROUP BY e.klados";
            $res = mysqli_query($mysqlconnection, $qry);
            while ($row = mysqli_fetch_array($res)){
                $ekp_ar[$row['klados']] += $row['count'];
            }
            // count all employees
            $ekp_count = 0;
            $qry = "SELECT count(*) as count FROM employee e join yphrethsh y on e.id = y.emp_id WHERE y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status=1";
            $res = mysqli_query($mysqlconnection, $qry);
            $row = mysqli_fetch_array($res);
            $ekp_count = $row['count'];
            $qry = "SELECT count(*) as count FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id WHERE y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status=1";
            $res = mysqli_query($mysqlconnection, $qry);
            $row = mysqli_fetch_array($res);
            $ekp_count += $row['count'];

            echo "<tr>";
            echo "<td><a href='../school/school_status.php?org=$sch' target='_blank'>$name</a></td>";
            echo "<td><small>$code</small></td>";
            echo "<td>".substr(getSchDimos($sch, $mysqlconnection),0,10).".</td>";
            echo "<td>$organikothta</td><td>$leitoyrg</td><td>$classes[0]</td><td>$classes[1]</td><td>$classes[2]</td><td>$classes[3]</td><td>$classes[4]</td><td>$classes[5]</td><td>$synolo</td>\n";
            echo "<td>$tmimata_exp[0]</td><td>$tmimata_exp[1]</td><td>$tmimata_exp[2]</td><td>$tmimata_exp[3]</td><td>$tmimata_exp[4]</td><td>$tmimata_exp[5]</td><td>$synolo_tmim</td>\n";
            echo $has_entaxi ? "<td>Ναι</td>" : "<td>Όχι</td>";
            echo $has_entaxi ? "<td>$entaksis[1]</td>" : "<td>0</td>";
            if ($_REQUEST['type'] == 8){
                echo "<td> - </td><td> - </td><td> - </td><td> - </td>";
            } else {
                echo "<td>".$ekp_ar['ΠΕ70']."</td><td>".$ekp_ar['ΠΕ06']."</td><td>".$ekp_ar['ΠΕ11']."</td><td>".$ekp_ar['ΠΕ79']."</td>";
            }
            echo "<td>$ekp_count</td>";
            echo "<td>$oloimero_tea</td><td>$ol1516</td><td>$ol1617</td><td>$oloimero_stud</td><td>$pz</td><td>$mpz</td>";//<td>$ekp_ee_exp[0]</td><td>$ekp_ee_exp[1]</td>";
            echo "</tr>\n";

            $sums[0] += $classes[0];
            $sums[1] += $classes[1];
            $sums[2] += $classes[2];
            $sums[3] += $classes[3];
            $sums[4] += $classes[4];
            $sums[5] += $classes[5];
            $sumt[0] += $tmimata_exp[0];
            $sumt[1] += $tmimata_exp[1];
            $sumt[2] += $tmimata_exp[2];
            $sumt[3] += $tmimata_exp[3];
            $sumt[4] += $tmimata_exp[4];
            $sumt[5] += $tmimata_exp[5];
            $sumte += $has_entaxi ? $entaksis[1] : 0;
            $sumol += $oloimero_tea;
            $sumol15 += $ol1516;
            $sumol16 += $ol1617;
            $sumpz += $pz;
            $summpz += $mpz;
            $sumolstud += $oloimero_stud;
            //$sumee[0] += $ekp_ee_exp[0];
            //$sumee[1] += $ekp_ee_exp[1];
            $sum70 += $ekp_ar['ΠΕ70'];
            $sum06 += $ekp_ar['ΠΕ06'];
            $sum11 += $ekp_ar['ΠΕ11'];
            $sum16 += $ekp_ar['ΠΕ79'];
            $ekp_count_sum += $ekp_count;
            $sumschools += 1;
        
            $i++;                        
        }

        $synolo_stud = is_array($sums) ? array_sum($sums) : 0;
        $synolo_teach =  is_array($sumt) ? array_sum($sumt) : 0;
        echo "<tr><td>Πλήθος: $sumschools</td><td></td><td></td><td></td><td>ΣΥΝΟΛΑ:</td><td>$sums[0]</td><td>$sums[1]</td><td>$sums[2]</td><td>$sums[3]</td><td>$sums[4]</td><td>$sums[5]</td><td>$synolo_stud</td>";
        echo "<td>$sumt[0]</td><td>$sumt[1]</td><td>$sumt[2]</td><td>$sumt[3]</td><td>$sumt[4]</td><td>$sumt[5]</td><td>$synolo_teach</td><td></td><td>$sumte</td><td>$sum70</td><td>$sum06</td><td>$sum11</td><td>$sum16</td>";
        echo "<td>$ekp_count_sum</td><td>$sumol</td><td>$sumol15</td><td>$sumol16</td><td>$sumolstud</td><td>$sumpz</td><td>$summpz</td></tr>";
        //<td>$sumee[0]</td><td>$sumee[1]</td></tr>";
        // echo "<tr><td></td><td></td><td></td><td></td><td></td><td>Α'</td><td>Β'</td><td>Γ'</td><td>Δ'</td><td>Ε'</td><td>ΣΤ'</td><td>Σύν.</td>";
        // echo "<td>Τμ.Α'</td><td>Τμ.Β'</td><td>Τμ.Γ'</td><td>Τμ.Δ'</td><td>Τμ.Ε'</td><td>Τμ.ΣΤ'</td><td>Σύν.Τμ.</td><td></td><td>Μαθ.Τ.Ε.</td><td>ΠΕ70</td><td>ΠΕ06</td><td>ΠΕ11</td><td>ΠΕ79</td><td>Συν.προσ.</td><td>Τμ. Ολ.</td><td>Μαθ. Ολ.</td>";//<td>Εκπ. T.E.</td><td>Εκπ. T.Y.</td>";
        // echo "</tr>";
        echo "</tbody></table>";
        
        echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    }
    //else if ($_GET['type'] == 2)
    else if (in_array($_REQUEST['type'], $nip_ar)) {
        //nipiagogeia
        $type = 2;
        //type2: 0 δημόσιο, 1 ιδιωτικό, 2 ειδικό
        $type2 = $_REQUEST['type'] - 4;
        $kenapl = isset($_GET['kenapl']) ? true : false;
        echo $kenapl ? 
        "<p><a href='report_tm_ekp.php?type=4'>Όλα</a>&nbsp;&nbsp;Μόνο Κενά/Πλεονάσματα</p>" :
        "<p>Όλα&nbsp;&nbsp;<a href='report_tm_ekp.php?type=4&kenapl=1'>Μόνο Κενά/Πλεονάσματα</a></p>";
        
        $query = "SELECT * from school WHERE type = $type AND type2=$type2 AND anenergo=0 ORDER BY name";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_num_rows($result);
    
        echo "<center>";
        $i=0;
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
            echo "<thead><tr><th>Ονομασία</th>";
            echo "<th>Δήμος</th>";
            echo "<th>Οργ.</th>";
            echo "<th>Λειτ.</th>";
            
            //echo "<th>Τμήματα<br>Πρωινού</th>";
            echo "<th>Νήπια<br>Πρωινού</th>";
            echo "<th>Προνήπια<br>Πρωινού</th>";
            
            echo "<th>Τμήματα<br>Ολοήμ.</th>";
            echo "<th>Νήπια<br>Ολοήμ.</th>";
            echo "<th>Προνήπια<br>Ολοήμ.</th>";

            echo "<th>Τ.Ε.</th>";
            echo "<th>Μαθ Τ.Ε.</th>";
            
            echo "<th>Απαιτ.Εκπ<br><small>(όχι Τ.Ε.)</small></th>";
            echo "<th>Τοπ/νοι Εκπ<br><small>(όχι Τ.Ε. & Παράλ.)</small></th>";
            echo $kenapl ?
                "<th>Κενά</th><th>Πλεονάσματα</th>" :
                "<th>+ / -</th>";
            echo "<th>+ / - Τ.Ε.</th>";
            echo "<th>+ / - ΠΕ06<small><br>(ώρες)</small></th>";
            echo "</tr></thead>\n<tbody>\n";

        while ($i < $num)
        {         
            $sch = mysqli_result($result, $i, "id");
            $organikothta = mysqli_result($result, $i, "organikothta");
            $name = getSchool($sch, $mysqlconnection);
            $entaksis = explode(',', mysqli_result($result, $i, "entaksis"));
            $klasiko = mysqli_result($result, $i, "klasiko");
            $klasiko_exp = explode(",", $klasiko);
            // fill array blanks with zeroes
            foreach($klasiko_exp as &$val) {
                if(empty($val)) { $val = 0; }
            }
            $oloimero_nip = mysqli_result($result, $i, "oloimero_nip");
            $oloimero_nip_exp = explode(",", $oloimero_nip);
            // fill array blanks with zeroes
            foreach($oloimero_nip_exp as &$val) {
                if(empty($val)) { $val = 0; }
            }

            $klasiko_tm = $oloimero_tm = 0;
            $klasiko_tm += $klasiko_exp[0]+$klasiko_exp[1] >0 ? 1:0;
            $klasiko_tm += $klasiko_exp[2]+$klasiko_exp[3] >0 ? 1:0;
            $klasiko_tm += $klasiko_exp[4]+$klasiko_exp[5] >0 ? 1:0;
            $klasiko_tm += $klasiko_exp[7]+$klasiko_exp[8] >0 ? 1:0;
            $klasiko_tm += $klasiko_exp[9]+$klasiko_exp[10] >0 ? 1:0;
            $klasiko_tm += $klasiko_exp[11]+$klasiko_exp[12] >0 ? 1:0;
            $klasiko_tm += $klasiko_exp[13]+$klasiko_exp[14] >0 ? 1:0;
            $klasiko_tm += $klasiko_exp[15]+$klasiko_exp[16] >0 ? 1:0;
            $oloimero_tm += $oloimero_nip_exp[0]+$oloimero_nip_exp[1]>0 ? 1:0;
            $oloimero_tm += $oloimero_nip_exp[2]+$oloimero_nip_exp[3]>0 ? 1:0;
            $oloimero_tm += $oloimero_nip_exp[4]+$oloimero_nip_exp[5]>0 ? 1:0;
            $oloimero_tm += $oloimero_nip_exp[6]+$oloimero_nip_exp[7]>0 ? 1:0;
            $oloimero_tm += $oloimero_nip_exp[8]+$oloimero_nip_exp[9]>0 ? 1:0;
            $oloimero_tm += $oloimero_nip_exp[10]+$oloimero_nip_exp[11]>0 ? 1:0;
            $oloimero_tm += $oloimero_nip_exp[12]+$oloimero_nip_exp[13]>0 ? 1:0;
            $oloimero_tm += $oloimero_nip_exp[14]+$oloimero_nip_exp[15]>0 ? 1:0;
            // add tmimata of dieyrymeno
            $oloimero_tm += $oloimero_nip_exp[16]>0 ? 1:0;

            // τοποθετημένοι εκπ/κοί
            $top60 = $top60m = $top60ana = $top60ent = 0;
            // get monimoi - exclude ekp/koys@tmima entaksis
            $qry = "SELECT count(*) as pe60 FROM employee WHERE sx_yphrethshs = $sch AND klados=1 AND status=1 and ent_ty != 1";
            $res = mysqli_query($mysqlconnection, $qry);
            $top60m = mysqli_result($res, 0, 'pe60');
            // get anapl - except ent/paral
            $qry = "SELECT count(*) as pe60 FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados=1 AND status=1 and ent_ty not in (1,2,3)";
            $res = mysqli_query($mysqlconnection, $qry);
            $top60ana = mysqli_result($res, 0, 'pe60');
            // only T.E. - klados is PE60,60.50,61
            $qry = "SELECT count(*) as pe60 FROM employee WHERE sx_yphrethshs = $sch AND klados in (1,16,17) AND status=1 and ent_ty = 1";
            $res = mysqli_query($mysqlconnection, $qry);
            $top60ent_mon = mysqli_result($res, 0, 'pe60');
            $qry = "SELECT count(*) as pe60 FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados in (1,16,17) AND status=1 and ent_ty = 1";
            $res = mysqli_query($mysqlconnection, $qry);
            $top60ent_ana = mysqli_result($res, 0, 'pe60');
            $top60ent = $top60ent_mon + $top60ent_ana;
            
            $top60 = $top60m+$top60ana;
            // apaitoymenoi
            $apait = $klasiko_tm + $oloimero_tm;
            // add tmimata of dieyrymeno
            $apait += $oloimero_nip_exp[16] > 0 ? 1 : 0;
            // apaitoymenoi @ entaksi
            $has_entaxi = strlen($entaksis[0])>1 ? 1 : 0; 
            $k_pl_ent = $has_entaxi > 0 ?
                $top60ent - $has_entaxi :
                0;
            // compute kena / pleonasmata
            $k_pl = $top60-$apait;
            // ΠΕ06
            // τοποθετημένοι εκπ/κοί ΠΕ06
            $top06 = top_pe06_nip($sch, $mysqlconnection);
            $apait06 = $klasiko_tm * WRES_PE06_NIP;
            $k_pl_06 = $top06 - $apait06;

            if ($kenapl && $k_pl == 0 && $k_pl_ent == 0) {
                $i++;
                continue;
            }
            echo "<tr>";
            echo "<td><a href='../school/school_status.php?org=$sch'>$name</a></td>";
            echo "<td>".substr(getSchDimos($sch, $mysqlconnection),0,10).".</td>";
            echo "<td>$organikothta</td>";
            echo "<td><strong>$klasiko_tm</strong></td>";

            // add students of tmima 1-8
            $klasiko_nip = $klasiko_exp[0] + $klasiko_exp[2] + $klasiko_exp[4] + $klasiko_exp[7] + $klasiko_exp[9] + $klasiko_exp[11] + $klasiko_exp[13] + $klasiko_exp[15];
            $klasiko_pro = $klasiko_exp[1] + $klasiko_exp[3] + $klasiko_exp[5] + $klasiko_exp[8] + $klasiko_exp[10] + $klasiko_exp[12] + $klasiko_exp[14] + $klasiko_exp[16];
            echo "<td>$klasiko_nip</td><td>$klasiko_pro</td>";
            // add students of oloimero tmima 1-8
            $oloimero_syn_nip = $oloimero_nip_exp[0] + $oloimero_nip_exp[2] + $oloimero_nip_exp[4] + $oloimero_nip_exp[6] + $oloimero_nip_exp[8] + $oloimero_nip_exp[10] + $oloimero_nip_exp[12] + $oloimero_nip_exp[14];
            $oloimero_syn_pro = $oloimero_nip_exp[1] + $oloimero_nip_exp[3] + $oloimero_nip_exp[5] + $oloimero_nip_exp[7] + $oloimero_nip_exp[9] + $oloimero_nip_exp[11] + $oloimero_nip_exp[13] + $oloimero_nip_exp[15];
            echo "<td><strong>$oloimero_tm</strong></td>";
            echo "<td>$oloimero_syn_nip</td><td>$oloimero_syn_pro</td>";
            
            echo $has_entaxi ? "<td>Ναι</td>" : "<td>Όχι</td>";
            echo $has_entaxi ? "<td>$entaksis[1]</td>" : "<td>0</td>";
            echo "<td>$apait</td>";
            echo "<td>$top60</td>";
            
            if ($kenapl) {
                if ($k_pl > 0) {
                    echo "<td style='background:none;background-color:rgba(0, 255, 0, 0.37)'>0</td>".tdc($k_pl, null, false);
                    $synolo_pleon += $k_pl;
                } else {
                    echo tdc($k_pl, null, false)."<td style='background:none;background-color:rgba(0, 255, 0, 0.37)'>0</td>";
                    $synolo_kena += $k_pl;
                }
                   
                   
            } else {
                echo tdc($k_pl, null, false);
            }
            echo tdc($k_pl_ent, null, false);
            echo tdc($k_pl_06, null, false);
            echo "</tr>\n";

            $synolo_tm_klas += $klasiko_tm;
            $synolo_tm_olo += $oloimero_tm;
            $synolo_nip += $klasiko_nip;
            $synolo_pro += $klasiko_pro;
            $synolo_ol_nip += $oloimero_syn_nip;
            $synolo_ol_pro += $oloimero_syn_pro;
            $synolo_apait += $apait;
            $synolo_k_pl += $k_pl;
            $synolo_k_pl_06 += $k_pl_06;
            $synolo_k_pl_ent += $k_pl_ent;
            $synolo_nipiag_top += $top60;
            
            $i++;
        }
        
        echo "<tr>";
        echo "<td>Σύνολα</td><td></td><td></td>";
        echo "<td>$synolo_tm_klas</td>";
        echo "<td>$synolo_nip</td>";
        echo "<td>$synolo_tm_olo</td>";
        echo "<td>$synolo_pro</td>";
        echo "<td>$synolo_ol_nip</td>";
        echo "<td>$synolo_ol_pro</td><td></td><td></td>";
        echo "<td>$synolo_apait</td>";
        echo "<td>$synolo_nipiag_top</td>";
        if ($kenapl) {
            echo "<td>$synolo_kena</td>";
            echo "<td>$synolo_pleon</td>";
        } else {
            echo "<td>$synolo_k_pl</td>";
        }
        echo "<td>$synolo_k_pl_ent</td>";
        echo "<td>$synolo_k_pl_06</td>";
        
        echo "</tr>";
        echo "</tbody></table>";

        echo "<input type='button' class='btn-red' VALUE='Επιστροφή' onClick=\"parent.location='../index.php'\">";
    }
}
?>  
</body>
</html>
