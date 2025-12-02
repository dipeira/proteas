<?php
    header('Content-type: text/html; charset=utf-8'); 
    require_once"../config.php";
    require_once"../include/functions.php";

    session_start();
    
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

    // init variables
    $allo_pyspe = getSchoolID('Άλλο ΠΥΣΠΕ',$mysqlconnection);
    $allo_pysde = getSchoolID('Άλλο ΠΥΣΔΕ',$mysqlconnection);
    $se_forea = getSchoolID('Απόσπαση σε φορέα',$mysqlconnection);
    $sxol_symv = getSchoolID('Σύμβουλος Εκπαίδευσης',$mysqlconnection);
    $ekswteriko = getSchoolID('Απόσπαση στο εξωτερικό',$mysqlconnection);
?>    
<html>
    <head>
        <?php 
        $root_path = '../';
        $page_title = 'Τέλος Διδακτικού/Σχολικού έτους - Ενέργειες';
        require '../etc/head.php'; 
        ?>
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
        <style>
            .form-section {
                background: white;
                border-radius: 8px;
                padding: 12px;
                margin-bottom: 12px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            }
            .form-section h3 {
                margin-top: 0;
                margin-bottom: 10px;
                padding-bottom: 8px;
                border-bottom: 1px solid #e5e7eb;
                color: #1f2937;
                font-size: 1rem;
                font-weight: 600;
            }
            .warning-box {
                background: #fef3c7;
                border-left: 4px solid #f59e0b;
                padding: 12px 16px;
                margin-bottom: 16px;
                border-radius: 4px;
            }
            .warning-box strong {
                color: #92400e;
                display: block;
                margin-bottom: 4px;
            }
            .warning-box p {
                margin: 4px 0;
                color: #78350f;
                font-size: 0.875rem;
            }
            .info-box {
                background: #eff6ff;
                border-left: 4px solid #3b82f6;
                padding: 12px 16px;
                margin-bottom: 12px;
                border-radius: 4px;
                font-size: 0.875rem;
                color: #1e40af;
            }
            .radio-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
                padding: 8px 0;
            }
            .radio-item {
                display: flex;
                align-items: flex-start;
                gap: 8px;
                padding: 8px;
                border-radius: 4px;
                transition: background-color 0.2s;
            }
            .radio-item:hover {
                background-color: #f9fafb;
            }
            .radio-item input[type="radio"] {
                width: 18px;
                height: 18px;
                cursor: pointer;
                margin-top: 2px;
                flex-shrink: 0;
            }
            .radio-item label {
                font-size: 0.8125rem;
                color: #374151;
                cursor: pointer;
                line-height: 1.5;
                flex: 1;
            }
            .radio-number {
                font-weight: 600;
                color: #3b82f6;
                margin-right: 4px;
            }
            .btn-primary {
                background: #3b82f6;
                color: white;
                padding: 8px 20px;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                font-size: 0.875rem;
                cursor: pointer;
                transition: background 0.2s, transform 0.1s;
            }
            .btn-primary:hover:not(:disabled) {
                background: #2563eb;
                transform: translateY(-1px);
            }
            .btn-primary:disabled {
                background: #9ca3af;
                cursor: not-allowed;
                opacity: 0.6;
            }
            .btn-red {
                background: #ef4444;
                color: white;
                padding: 8px 20px;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                font-size: 0.875rem;
                cursor: pointer;
                transition: background 0.2s;
            }
            .btn-red:hover {
                background: #dc2626;
            }
            .button-group {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                margin-top: 12px;
            }
            .alert-message {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #991b1b;
                padding: 12px 16px;
                border-radius: 6px;
                margin-bottom: 16px;
            }
            .success-message {
                background: #d1fae5;
                border: 1px solid #86efac;
                color: #065f46;
                padding: 12px 16px;
                border-radius: 6px;
                margin-bottom: 16px;
            }
            .form-input {
                width: 100%;
                max-width: 300px;
                padding: 6px 10px;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                font-size: 0.8125rem;
                margin-top: 8px;
            }
            .form-input:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
            }
            @media (max-width: 768px) {
                .form-section {
                    padding: 10px;
                }
            }
        </style>
    </head>
    <body class="p-4 md:p-6 lg:p-8">
    <?php require '../etc/menu.php'; ?>
    <div class="max-w-4xl mx-auto">
<?php       
    // end_of_year: Includes end-of-year actions: Deletes ektakto personnel, returns personnel from other pispe etc...
    require "../tools/class.login.php";
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false) {
        header("Location: ../tools/login.php");
    }
    $usrlvl = $_SESSION['userlevel'];
            
    $sxol_etos = getParam('sxol_etos', $mysqlconnection);
    $tbl_bkp_mon = "employee_bkp_$sxol_etos";
    
    echo "<h2 class='text-3xl font-bold text-gray-800 mb-4'>Τέλος Διδακτικού/Σχολικού έτους - Ενέργειες</h2>";
    echo "<div class='warning-box'>";
    echo "<strong>⚠️ Προσοχή: Μη αναστρέψιμες ενέργειες</strong>";
    echo "<p>Επιβάλλεται η εκτέλεση με τη σειρά του αριθμού που αναγράφεται.</p>";
    echo "</div>";
    
    echo "<div class='form-section'>";
    echo "<h3>Επιλογή Ενέργειας</h3>";
    echo "<form action='' method='POST' autocomplete='off'>";
    echo "<div class='radio-group'>";
    
    echo "<div class='radio-item'>";
    echo "<input type='radio' name='type' value='2' id='type_2' />";
    echo "<label for='type_2'><span class='radio-number'>1.</span> Διαγραφή Αναπληρωτών / Ωρομισθίων από βάση δεδομένων (να γίνει πριν την αλλαγή Σχ.έτους)</label>";
    echo "</div>";
    
    echo "<div class='radio-item'>";
    echo "<input type='radio' name='type' value='8' id='type_8' />";
    echo "<label for='type_8'><span class='radio-number'>2.</span> Αλλαγή σχολικού έτους (τρέχον σχολικό έτος: <strong>$sxol_etos</strong>)</label>";
    echo "</div>";
    
    echo "<div class='radio-item'>";
    echo "<input type='radio' name='type' value='10' id='type_10' />";
    echo "<label for='type_10'><span class='radio-number'>3.</span> Αλλαγή ωραριου εκπ/κων</label>";
    echo "</div>";
    
    echo "<div class='radio-item'>";
    echo "<input type='radio' name='type' value='11' id='type_11' />";
    echo "<label for='type_11'><span class='radio-number'>4.</span> Πλήρωση πίνακα υπηρετήσεων</label>";
    echo "</div>";
    
    echo "<div class='radio-item'>";
    echo "<input type='radio' name='type' value='12' id='type_12' />";
    echo "<label for='type_12'><span class='radio-number'>5.</span> Εισαγωγή μαθητών / τμημάτων</label>";
    echo "</div>";
    
    echo "<div class='radio-item' style='margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;'>";
    echo "<input type='radio' name='type' value='4' id='type_4' />";
    echo "<label for='type_4'>Επιστροφή αποσπασμένων εκπαιδευτικών από φορείς (για 31/08)</label>";
    echo "</div>";
    
    echo "<div class='radio-item' style='margin-top: 8px; padding-top: 12px; border-top: 1px solid #e5e7eb;'>";
    echo "<input type='radio' name='type' value='1' id='type_1' />";
    echo "<label for='type_1'>Εκτύπωση βεβαιώσεων Αναπληρωτών Κρατικού Προυπολογισμού</label>";
    echo "</div>";
    
    echo "<div class='radio-item'>";
    echo "<input type='radio' name='type' value='7' id='type_7' />";
    echo "<label for='type_7'>Εκτύπωση βεβαιώσεων Αναπληρωτών ΕΣΠΑ</label>";
    echo "</div>";
    
    echo "</div>"; // radio-group
    
    echo "<div class='button-group'>";
    if ($usrlvl > 0) {
        echo "<button type='submit' class='btn-primary' disabled>Πραγματοποίηση</button>";
    } else {
        echo "<button type='submit' class='btn-primary'>⚙️ Πραγματοποίηση</button>";
    }
    echo "<button type='button' class='btn-red' onClick=\"parent.location='../index.php'\">← Επιστροφή</button>";
    echo "</div>";
    
    echo "</form>";
    echo "</div>"; // form-section
    
    if ($usrlvl > 0) {
        echo "<div class='alert-message'>";
        echo "<strong>Δεν έχετε δικαίωμα για την πραγματοποίηση αυτών των ενεργειών.</strong><br>";
        echo "Επικοινωνήστε με το διαχειριστή σας.";
        echo "</div>";
        mysqli_close($mysqlconnection);
        exit;
    }
    // Allagh sxolikoy etoys
if (isset($_POST['sxoletos'])) {
    $curSxoletos = getParam('sxol_etos', $mysqlconnection);
    if ($curSxoletos == $_POST['sxoletos']) {
        echo "<div class='alert-message'>Σφάλμα: Το έτος έχει ήδη αλλάξει...</div>";
        mysqli_close($mysqlconnection);
        echo "</div></body></html>";
        exit;
    }
    $debug = '';
    setParam('sxol_etos', $_POST['sxoletos'], $mysqlconnection);
    // more...
    echo "<div class='form-section'>";
    echo "<h3>Επιστροφή αποσπασμένων εκπαιδευτικών του ΠΥΣΠΕ Ηρακλείου στην οργανική τους</h3>";
    $query = "CREATE TABLE $tbl_bkp_mon SELECT * FROM employee";
    $debug .= $query . ' / ';
    $result = mysqli_query($mysqlconnection, $query);
    $query = "DROP TABLE employee_moved";
    $debug .= $query . ' / ';
    $result = mysqli_query($mysqlconnection, $query);
    // allo pyspe, apospasi se forea, sxol. symvoulos, Apospash ekswteriko
    // thesi 2: d/nths, 4: dioikhtikos
    $query = "CREATE TABLE employee_moved SELECT * FROM employee WHERE sx_yphrethshs NOT IN ($allo_pyspe,$se_forea,$sxol_symv,$ekswteriko) AND thesi NOT IN (2,4)";
    $debug .= $query . ' / ';
    $result = mysqli_query($mysqlconnection, $query);
    $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs NOT IN ($allo_pyspe,$se_forea,$sxol_symv,$ekswteriko) AND thesi NOT IN (2,4)";
    $debug .= $query . ' / ';
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_affected_rows($mysqlconnection);
    if ($result) {
        echo "<div class='success-message'>✓ Επιτυχής μεταβολή $num εγγραφών.</div>";
    } else { 
        echo "<div class='alert-message'>✗ Πρόβλημα στη διαγραφή...</div>";
    }
    echo "</div>";
    
    echo "<div class='form-section'>";
    echo "<h3>Επιστροφή αποσπασμένων εκπαιδευτικών από άλλα ΠΥΣΠΕ</h3>";
    $query = "INSERT INTO employee_moved SELECT * FROM employee WHERE sx_yphrethshs = $allo_pyspe";
    $debug .= $query . ' / ';
    $result = mysqli_query($mysqlconnection, $query);
    $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs = $allo_pyspe";
    $debug .= $query . ' / ';
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_affected_rows($mysqlconnection);
    if ($result) {
        echo "<div class='success-message'>✓ Επιτυχής μεταβολή $num εγγραφών.</div>";
    } else { 
        echo "<div class='alert-message'>✗ Πρόβλημα στη διαγραφή...</div>";
    }
    echo "<div class='info-box' style='margin-top: 8px;'><small title='$debug'>ℹ️ Λεπτομέρειες διαθέσιμες</small></div>";
    echo "</div>";
    
    echo "<div class='success-message'>Η διαδικασία ολοκληρώθηκε επιτυχώς!</div>";
}
    ////////////////////////////////////////
    // vevaiwseis proyphresias anaplhrwtwn
    ////////////////////////////////////////
if($_POST['type'] == 1 || $_POST['type'] == 7) {
    if ($_POST['type'] == 1) {
        $kratikoy = 1;
    }
    $sxol_etos = getParam('sxol_etos', $mysqlconnection);
    mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");
    // kratikoy or ESPA 
    if ($kratikoy) {
        $query = "SELECT e.id,e.name,e.surname,e.patrwnymo,e.klados,p.name as praksi,p.ya,p.ada,p.apofasi,p.type,e.hm_anal,e.hm_apox,e.metakinhsh,e.afm,e.type as typos from ektaktoi e JOIN praxi p ON e.praxi = p.id WHERE e.type IN (1,2) AND p.type ='ΚΡΑΤ'";
    } else {
        $query = "SELECT e.id,e.name,e.surname,e.patrwnymo,e.klados,p.name as praksi,p.ya,p.ada,p.apofasi,p.type,e.hm_anal,e.hm_apox,e.metakinhsh,e.afm,e.type as typos from ektaktoi e JOIN praxi p ON e.praxi = p.id WHERE e.type IN (1,2,3,4,5,6) AND p.type !='ΚΡΑΤ'";
    }

    $result = mysqli_query($mysqlconnection, $query);
    $num=mysqli_num_rows($result);

    echo "<div class='form-section'>";
    echo "<h3>Εκτύπωση βεβαιώσεων Αναπληρωτών ";
    if ($kratikoy) {
        echo "κρατικού προύπολογισμού";
    } else {
        echo "ΕΣΠΑ";
    }
    echo "</h3>";
    if ($num == 0) {
        echo "<div class='alert-message'>Δε βρέθηκαν εγγραφές!</div>";
        echo "</div>";
        mysqli_close($mysqlconnection);
        echo "</div></body></html>";
        exit;
    }
    echo "<div class='info-box'>Βρέθηκαν <strong>$num</strong> αναπληρωτές.</div>";
    echo "<form name='anaplfrm' action=\"../employee/vev_yphr_anapl.php\" method='POST'>";
        

    $i=$cnt=0;
    // ***********************
    while ($i < $num)
    //while ($i < 20) // for testing
    // ***********************
    {
        $id = mysqli_result($result, $i, "id");
        $name = mysqli_result($result, $i, "name");
        $surname = mysqli_result($result, $i, "surname");
        $patrwnymo = mysqli_result($result, $i, "patrwnymo");
        $klados = mysqli_result($result, $i, "klados");
        $ya = mysqli_result($result, $i, "ya");
        $ada = mysqli_result($result, $i, "ada");
        $apof = mysqli_result($result, $i, "apofasi");
        $hmpros = mysqli_result($result, $i, "hm_anal");
        $hmapox = mysqli_result($result, $i, "hm_apox");
        $metakinhsh = mysqli_result($result, $i, "metakinhsh");
        $last_afm = substr(mysqli_result($result, $i, "afm"), -3);
        $ptype = mysqli_result($result, $i, "type");
        $praksi = mysqli_result($result, $i, "praksi");
        $typos = mysqli_result($result, $i, "typos");

        if ($typos == 1) {
            $meiwmeno = true;
        } else { 
            $meiwmeno = false;
        }

        // get yphrethseis
        unset($sx_yphrethshs);
        $sx_yphrethshs[] = array();
        $qry = "select yphrethsh, hours from yphrethsh_ekt where emp_id = $id AND sxol_etos = $sxol_etos";
        $res1 = mysqli_query($mysqlconnection, $qry);
        while ($arr = mysqli_fetch_array($res1))
        {
            $sx_yphrethshs[] = array('sch' => $arr[0],'hours' => $arr[1]);
        }
        // Proteas has now prefix to distinguish workers.
        $prefix = '';
        $eepebp = 0;

        if (!$kratikoy) {
            if (strlen($ptype) > 0) {
                $prefix = greek_to_greeklish($ptype).'_';
            }
        }
                        
        if (strpos($prefix, 'PEP') !== false) {
            $eepebp = 2;
        } elseif (strpos($prefix, 'YPOS') !== false || strpos($praksi, 'ΕΕΠ') !== false || strpos($praksi, 'ΕΒΠ') !== false) { 
            $eepebp = 1;
        }

        $cnt++;
            
        // get (and subtract) Adeies
        $adeies = get_adeies($id, $mysqlconnection);

        $metakinhsh = str_replace("'", "", $metakinhsh);
        $emp_arr = array(
            'id'=>$id,'name'=>$name,'surname'=>$surname,'patrwnymo'=>$patrwnymo,
            'klados'=>$klados,'sx_yphrethshs'=>$sx_yphrethshs,
            'ya'=>$ya,'ada'=>$ada,'apof'=>$apof,'hmpros'=>$hmpros,'hmapox'=>$hmapox,'metakinhsh'=>$metakinhsh,
            'last_afm'=>$last_afm,'prefix'=>$prefix,'eepebp'=>$eepebp,
            'adeies'=>$adeies, 'meiwmeno'=>$meiwmeno
        );

        $submit_array[] = $emp_arr;
        $i++;
    }
    echo "<input type='hidden' name='emp_arr' value='". serialize($submit_array) ."'>";
    echo "<input type='hidden' name='kratikoy' value=$kratikoy>";
    echo "<input type='hidden' name='plithos' value=$num>";
    echo "<button type='submit' class='btn-primary'>📄 Υποβολή αιτήματος</button>"; 
    echo "</form>";
    echo "</div>";
}
elseif ($_POST['type'] == 8) {
    echo "<div class='form-section'>";
    echo "<h3>Αλλαγή Σχολικού έτους</h3>";
    echo "<div class='info-box'>Τρέχον σχολικό έτος: <strong>$sxol_etos</strong></div>";
    echo "<p style='margin-bottom: 8px; font-size: 0.875rem;'>Δώστε νέο σχολικό έτος (π.χ. για το σχολ.έτος 2014-15 εισάγετε <strong>201415</strong>)</p>";
    echo "<form action='' method='POST'>";
    echo "<input type='text' name='sxoletos' class='form-input' placeholder='π.χ. 201415'>";
    echo "<div class='button-group'>";
    echo "<button type='submit' class='btn-primary'>⚙️ Υποβολή</button>";
    echo "</div>";
    echo "</form>";
    echo "<div class='info-box' style='margin-top: 8px;'>ΣΗΜ: Η διαδικασία διαρκεί αρκετή ώρα. Υπομονή...</div>";
    echo "</div>";
}
elseif ($_POST['type'] == 2) {
    echo "<div class='form-section'>";
    echo "<h3>Διαγραφή Αναπληρωτών / Ωρομισθίων από βάση δεδομένων</h3>";
    // check if not empty
    $query = "select id from ektaktoi";
    $result = mysqli_query($mysqlconnection, $query);
    if (!mysqli_num_rows($result)) {
        echo "<div class='alert-message'>Σφάλμα: O πίνακας αναπληρωτών είναι κενός...</div>";
        echo "</div>";
        mysqli_close($mysqlconnection);
        echo "</div></body></html>";
        exit;
    }
    // check if already inserted        
    $query = "select id from ektaktoi_old where sxoletos = $sxol_etos";
    $result = mysqli_query($mysqlconnection, $query);
    if (mysqli_num_rows($result) > 0) {
        echo "<div class='alert-message'>Σφάλμα: H διαγραφή έχει ήδη γίνει...</div>";
        echo "</div>";
        mysqli_close($mysqlconnection);
        echo "</div></body></html>";
        exit;
    }
    // archive into ektaktoi_old
    $query = "insert into ektaktoi_old select *, '$sxol_etos' as sxoletos from ektaktoi where 1";
    $result = mysqli_query($mysqlconnection, $query);
    // empty table
    $query = "TRUNCATE table ektaktoi";
    $result = mysqli_query($mysqlconnection, $query);
        
    // archive praxi
    $query = "insert into praxi_old select *, '$sxol_etos' as sxoletos from praxi where 1";
    $result = mysqli_query($mysqlconnection, $query);
    $query = "TRUNCATE table praxi";
    $result = mysqli_query($mysqlconnection, $query);
    if ($result) {
        echo "<div class='success-message'>✓ Επιτυχής Διαγραφή.<br><small>Οι εκπ/κοί μεταφέρθηκαν στον πίνακα 'ektaktoi_old'</small></div>";
    } else { 
        echo "<div class='alert-message'>✗ Πρόβλημα στη διαγραφή...</div>";
    }
    echo "</div>";
}
elseif ($_POST['type'] == 4) {
    echo "<div class='form-section'>";
    echo "<h3>Επιστροφή αποσπασμένων εκπαιδευτικών από φορείς (για 31-08)</h3>";
    // check...
    $query = "SELECT * FROM employee WHERE sx_yphrethshs = $se_forea";
    $result = mysqli_query($mysqlconnection, $query);
    if (!mysqli_num_rows($result)) {
        echo "<div class='alert-message'>Δεν υπάρχουν εκπαιδευτικοί γι'αυτή την ενέργεια...</div>";
        echo "</div>";
        mysqli_close($mysqlconnection);
        echo "</div></body></html>";
        exit;
    }
    $query = "INSERT INTO employee_moved SELECT * FROM employee WHERE sx_yphrethshs = $se_forea";
    $result = mysqli_query($mysqlconnection, $query);
    $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs = $se_forea";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_affected_rows();
    if ($result) {
        echo "<div class='success-message'>✓ Επιτυχής μεταβολή $num εγγραφών.</div>";
    } else { 
        echo "<div class='alert-message'>✗ Πρόβλημα στη διαγραφή...</div>";
    }
    echo "</div>";
}
    // Αλλαγή ωραριου εκπ/κων
elseif ($_POST['type'] == 10) {
    echo "<div class='form-section'>";
    echo "<h3>Αλλαγή ωραριου εκπ/κων</h3>";
    echo "<p style='margin-bottom: 12px;'>Παρακαλώ βεβαιωθείτε ότι επιλέγετε 31/12 του τρέχοντος έτους και έχετε επιλέξει Τροποποίηση ωρών στη ΒΔ</p>";
    echo "<a href='../employee/update_wres.php' class='btn-primary' style='text-decoration: none; display: inline-block;'>🔧 Αλλαγή ωραριου</a>";
    echo "</div>";
}
elseif ($_POST['type'] == 11) {
    echo "<div class='form-section'>";
    echo "<h3>Πλήρωση πίνακα υπηρετήσεων</h3>";
    echo "<div class='info-box'>ΣΗΜ: Η διαδικασία διαρκεί αρκετή ώρα. Υπομονή...</div>";
    do2yphr($mysqlconnection);
    echo "</div>";
}
elseif ($_POST['type'] == 12) {
    echo "<div class='form-section'>";
    echo "<h3>Εισαγωγή μαθητών / τμημάτων</h3>";
    echo "<p style='margin-bottom: 12px;'>(Χρησιμοποιήστε τα πρότυπα αρχεία <strong>Μαθητές / Τμήματα Δ.Σ./Νηπ.</strong> & τις αντίστοιχες επιλογές)</p>";
    echo "<a href='../tools/import.php' class='btn-primary' style='text-decoration: none; display: inline-block;'>📥 Εισαγωγή</a>";
    echo "</div>";
}
    mysqli_close($mysqlconnection);
?>
    </div>
</body>
</html>
