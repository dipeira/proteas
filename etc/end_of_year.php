<?php
    header('Content-type: text/html; charset=iso8859-7'); 
    require_once"../config.php";
    require_once"../tools/functions.php";
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'greek'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
?>    
<html>
    <head>
        <title>����� ����������/�������� ����� - ���������</title>
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
    <?php require '../etc/menu.php'; ?>
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
    echo "<html><head><h2>����� ����������/�������� ����� - ���������</h2></head><body>";
    echo "<h3><blink>�������: �� ������������ ���������</blink></h3>";
    echo "<tr><td><p>����������� � �������� �� �� ����� ��� ������� ��� �����������.</p>";
    echo "<table class=\"imagetable\" border='1'>";
    echo "<form action='' method='POST' autocomplete='off'>";
    echo "<tr><td>��������</td><td>";
    echo "<input type='radio' name='type' value='2'>1. �������� ����������� / ���������� ��� ���� ��������� (�� ����� ���� ��� ������ ��.�����)<br>";
    echo "<input type='radio' name='type' value='8'>2. ������ �������� ����� (������ ������� ����: $sxol_etos)<br>";
    echo "<input type='radio' name='type' value='10'>3. ������ ������� ���/���<br>";
    echo "<input type='radio' name='type' value='11'>4. ������� ������ �����������<br>";
    //echo "<input type='radio' name='type' value='6' disabled>3. ��������� ������������ ������������� ��� ����� ��������� ���� �������� ����<br>";
    //echo "<input type='radio' name='type' value='3' disabled>4. ��������� ������������ ������������� ��� ���� �����<br>";
    //echo "<input type='radio' name='type' value='5' disabled>5. �������� ������������ ��� ���� ����� / ����� ��� ���� ���������<br>";
    echo "<br>";
    echo "<input type='radio' name='type' value='4'>��������� ������������ ������������� ��� ������ (��� 31/08)<br>";
    echo "<br>";
    echo "<input type='radio' name='type' value='1'>�������� ���������� ����������� �������� ��������������<br>";
    echo "<input type='radio' name='type' value='7'>�������� ���������� ����������� ����<br>";
    echo "</td></tr>";
if ($usrlvl > 0) {
    echo "<tr><td colspan=2><input type='submit' value='��������������' disabled>";
} else {
    echo "<tr><td colspan=2><input type='submit' value='��������������'>";
}
    echo "<input type='button' class='btn-red' VALUE='���������' onClick=\"parent.location='../index.php'\">";
    echo "</td></tr>";
    echo "</form></table>";
if ($usrlvl > 0) {
    echo "<br><br><h3>��� ����� �������� ��� ��� �������������� ����� ��� ���������. ������������� �� �� ����������� ���.</h3>";
    mysqli_close($mysqlconnection);
    exit;
}
    // Allagh sxolikoy etoys
if (isset($_POST['sxoletos'])) {
    //do2yphr($mysqlconnection);
    $curSxoletos = getParam('sxol_etos', $mysqlconnection);
    if ($curSxoletos == $_POST['sxoletos']) {
        echo "<br><br>";
        die('������: �� ���� ���� ��� �������...');
    }
    setParam('sxol_etos', $_POST['sxoletos'], $mysqlconnection);
    // more...
    echo "<h3>��������� ������������ ������������� ��� ����� ��������� ���� �������� ����</h3>";
    $query = "CREATE TABLE $tbl_bkp_mon SELECT * FROM employee";
    $result = mysqli_query($mysqlconnection, $query);
    $query = "DROP TABLE employee_moved";
    $result = mysqli_query($mysqlconnection, $query);
    // 388: allo pyspe, 389: apospasi se forea, 397: sxol. symvoulos, 399: Apospash ekswteriko
    // thesi 2: d/nths, 4: dioikhtikos
    $query = "CREATE TABLE employee_moved SELECT * FROM employee WHERE sx_yphrethshs NOT IN (388,389,397,399) AND thesi NOT IN (2,4)";
    $result = mysqli_query($mysqlconnection, $query);
    $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs NOT IN (388,389,397,399) AND thesi NOT IN (2,4)";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_affected_rows($mysqlconnection);
    //echo $query;
    if ($result) {
        echo "�������� �������� $num ��������.";
    } else { 
        echo "�������� ��� ��������...";
    }
        
    echo "<h3>��������� ������������ ������������� ��� ���� �����</h3>";
    $query = "INSERT INTO employee_moved SELECT * FROM employee WHERE sx_yphrethshs = 388";
    $result = mysqli_query($mysqlconnection, $query);
    $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs = 388";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_affected_rows($mysqlconnection);
    //echo $query;
    if ($result) {
        echo "�������� �������� $num ��������.";
    } else { 
        echo "�������� ��� ��������...";
    }
        
    // echo "<h3>�������� ������������ ��� ���� ����� / ����� ��� ���� ���������</h3>";
    // $query = "DROP TABLE employee_deleted";
    // $result = mysqli_query($mysqlconnection, $query);
    // // 388: ���� �����, 394: ���� �����
    // $query = "CREATE TABLE employee_deleted SELECT * FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397,389)";
    // $result = mysqli_query($mysqlconnection, $query);
    // $query = "DELETE FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397,389)";
    // $result = mysqli_query($mysqlconnection, $query);
    // $num = mysqli_affected_rows();
    // //echo $query;
    // if ($result)
    //     echo "�������� �������� $num ��������.";
    // else 
    //     echo "�������� ��� ��������...";
    //
    //do2yphr($mysqlconnection);
    echo "<br><br>H ���������� ������������...";
}
    ////////////////////////////////////////
    // vevaiwseis proyphresias anaplhrwtwn
    ////////////////////////////////////////
if($_POST['type'] == 1 || $_POST['type'] == 7) {
    if ($_POST['type'] == 1) {
        $kratikoy = 1;
    }
    $sxol_etos = getParam('sxol_etos', $mysqlconnection);
    mysqli_query($mysqlconnection, "SET NAMES 'greek'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
    // kratikoy or ESPA 
    if ($kratikoy) {
        $query = "SELECT e.id,e.name,e.surname,e.patrwnymo,e.klados,p.name as praksi,p.ya,p.ada,p.apofasi,p.type,e.hm_anal,e.hm_apox,e.metakinhsh,e.afm,e.type as typos from ektaktoi e JOIN praxi p ON e.praxi = p.id WHERE e.type IN (1,2) AND p.type ='����'";
    } else {
        $query = "SELECT e.id,e.name,e.surname,e.patrwnymo,e.klados,p.name as praksi,p.ya,p.ada,p.apofasi,p.type,e.hm_anal,e.hm_apox,e.metakinhsh,e.afm,e.type as typos from ektaktoi e JOIN praxi p ON e.praxi = p.id WHERE e.type IN (1,3,4,5,6) AND p.type !='����'";
    }

    $result = mysqli_query($mysqlconnection, $query);
    $num=mysqli_num_rows($result);

    echo "<h3>�������� ���������� ����������� ";
    if ($kratikoy) {
        echo "�������� ��������������";
    } else {
        echo "����";
    }
    echo "</h3>";
    if ($num == 0) {
        echo "<p>�� �������� ��������!</p>";
        die();
    }
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
        } elseif (strpos($prefix, 'YPOS') !== false || strpos($praksi, '���') !== false || strpos($praksi, '���') !== false) { 
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
    echo "<input type='submit' VALUE='������� ���������'>"; 
    echo "</form>";
}
elseif ($_POST['type'] == 8) {
    echo "<h3>������ �������� �����</h3>";
    echo "������ ������� ����: $sxol_etos<br>";
    echo "����� ��� ������� ���� (�.�. ��� �� ����.���� 2014-15 �������� <strong>201415</strong><br>";
    echo "<form action='' method='POST'>";
    echo "<input type='text' name='sxoletos'>";
    echo "<input type='submit' value='�������'>";
    echo "</form>";
    echo "<small>���: � ���������� ������� ������ ���. �������...</small>";
}
elseif ($_POST['type'] == 2) {
    echo "<h3>�������� ����������� / ���������� ��� ���� ���������</h3>";
    // check if not empty
    $query = "select id from ektaktoi";
    $result = mysqli_query($mysqlconnection, $query);
    if (!mysqli_num_rows($result)) {
        exit('������: O ������� �������� ���������� ����� �����...');
    }
    // check if already inserted        
    $query = "select id from ektaktoi_old where sxoletos = $sxol_etos";
    $result = mysqli_query($mysqlconnection, $query);
    if (!mysqli_num_rows($result)) {
        exit('������: H �������� ���� ��� �����...');
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
        echo "�������� ��������. <br><small>�� ���/��� ������������ ���� ������ 'ektaktoi_old'</small>";
    } else { 
        echo "�������� ��� ��������...";
    }
}
elseif ($_POST['type'] == 4) {
    echo "<h3>��������� ������������ ������������� ��� ������ (��� 31-08)</h3>";
    // check...
    $query = "SELECT * FROM employee WHERE sx_yphrethshs = 389";
    $result = mysqli_query($mysqlconnection, $query);
    if (!mysqli_num_rows($result)) {
        exit('��� �������� ������������� ��\'���� ��� ��������...');
    }
    //
    //$query = "DROP TABLE employee_bkp";
    //$result = mysqli_query($mysqlconnection, $query);
    //$query = "CREATE TABLE employee_bkp SELECT * FROM employee";
    //$result = mysqli_query($mysqlconnection, $query);
    $query = "INSERT INTO employee_moved SELECT * FROM employee WHERE sx_yphrethshs = 389";
    $result = mysqli_query($mysqlconnection, $query);
    $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs = 389";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_affected_rows();
    //echo $query;
    if ($result) {
        echo "�������� �������� $num ��������.";
    } else { 
        echo "�������� ��� ��������...";
    }
}
    // ������ ������� ���/���
elseif ($_POST['type'] == 10) {
    echo "<br><br><a href='../employee/update_wres.php'>������ ������� ���/���</a><br>";
    echo "(�������� ����������� ��� ��������� 31/12 ��� ��������� ����� ��� ����� �������� ����������� ���� ��� ��)";
}
elseif ($_POST['type'] == 11) {
    echo "<br><br><small>���: � ���������� ������� ������ ���. �������...</small>";
    do2yphr($mysqlconnection);
}
    mysqli_close($mysqlconnection);

    // epistrofh ekp/kwn Hrakleioy sthn organikh toys
    /*
    elseif ($_POST['type'] == 6)
    {
        echo "<h3>��������� ������������ ������������� ��� ����� ��������� ���� �������� ����</h3>";
        // check...
        $query = "SELECT * FROM employee WHERE sx_yphrethshs NOT IN (389,397,399) AND sx_yphrethshs != sx_organikhs AND thesi NOT IN (2,4)";
        $result = mysqli_query($mysqlconnection, $query);
        if (!mysqli_num_rows($result))
            exit('��� �������� ������������� ��\'���� ��� ��������...');
        //
        //$query = "DROP TABLE employee_bkp";
        //$result = mysqli_query($mysqlconnection, $query);
        $query = "CREATE TABLE $tbl_bkp_mon SELECT * FROM employee";
        $result = mysqli_query($mysqlconnection, $query);
        $query = "DROP TABLE employee_moved";
        $result = mysqli_query($mysqlconnection, $query);
        // 389: apospasi se forea, 397: sxol. symvoulos, 399: Apospash ekswteriko
        // thesi 2: d/nths, 4: dioikhtikos
        $query = "CREATE TABLE employee_moved SELECT * FROM employee WHERE sx_yphrethshs NOT IN (389,397,399) AND thesi NOT IN (2,4)";
        $result = mysqli_query($mysqlconnection, $query);
        $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs NOT IN (389,397,399) AND thesi NOT IN (2,4)";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_affected_rows();
        //echo $query;
        if ($result)
            echo "�������� �������� $num ��������.";
        else 
            echo "�������� ��� ��������...";
    }
    elseif ($_POST['type'] == 3)
    {
        echo "<h3>��������� ������������ ������������� ��� ���� �����</h3>";
        // check...
        $query = "SELECT * FROM employee WHERE sx_yphrethshs = 388 AND sx_organikhs != 388";
        $result = mysqli_query($mysqlconnection, $query);
        if (!mysqli_num_rows($result))
            exit('��� �������� ������������� ��\'���� ��� ��������...');
        //
        //$query = "DROP TABLE employee_bkp";
        //$result = mysqli_query($mysqlconnection, $query);
        //$query = "CREATE TABLE employee_bkp SELECT * FROM employee";
        //$result = mysqli_query($mysqlconnection, $query);
        $query = "INSERT INTO employee_moved SELECT * FROM employee WHERE sx_yphrethshs = 388";
        $result = mysqli_query($mysqlconnection, $query);
        $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs = 388";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_affected_rows();
        //echo $query;
        if ($result)
            echo "�������� �������� $num ��������.";
        else 
            echo "�������� ��� ��������...";
    }
    // diagrafh ekpkwn apo alla pyspe/pysde
    elseif ($_POST['type'] == 5)
    {
        echo "<h3>�������� ������������ ��� ���� ����� / ����� ��� ���� ���������</h3>";
        // check...
        $query = "SELECT * FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397)";
        $result = mysqli_query($mysqlconnection, $query);
        if (!mysqli_num_rows($result))
            exit('��� �������� ������������� ��\' ���� ��� ��������...');
        //
        //$query = "DROP TABLE employee_bkp";
        //$result = mysqli_query($mysqlconnection, $query);
        //$query = "CREATE TABLE employee_bkp SELECT * FROM employee";
        //$result = mysqli_query($mysqlconnection, $query);
        $query = "DROP TABLE employee_deleted";
        $result = mysqli_query($mysqlconnection, $query);
        // 388: ���� �����, 394: ���� �����
        $query = "CREATE TABLE employee_deleted SELECT * FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397)";
        $result = mysqli_query($mysqlconnection, $query);
        $query = "DELETE FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397)";
        $result = mysqli_query($mysqlconnection, $query);
        $num = mysqli_affected_rows();
        //echo $query;
        if ($result)
            echo "�������� �������� $num ��������.";
        else 
            echo "�������� ��� ��������...";
    }
    */
?>
</body>
</html>
