<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once"../config.php";
	require_once"../tools/functions.php";
        $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
        mysql_select_db($db_name, $mysqlconnection);
?>	
  <html>
      <head><title>����� ����������/�������� ����� - ���������</title></head>
  <body>
        
<?php        
	// end_of_year: Includes end-of-year actions: Deletes ektakto personnel, returns personnel from other pispe etc...

                include("../tools/class.login.php");
                $log = new logmein();
                if($log->logincheck($_SESSION['loggedin']) == false){
                    header("Location: ../tools/login_check.php");
                }
                $usrlvl = $_SESSION['userlevel'];
                
		$sxol_etos = getParam('sxol_etos', $mysqlconnection);
                $tbl_bkp_mon = "employee_bkp_$sxol_etos";
                echo "<html><head><h2>����� ����������/�������� ����� - ���������</h2></head><body>";
                echo "<h3><blink>�������: �� ������������ ���������</blink></h3>";
                echo "<tr><td>����������� � �������� �� �� ����� ��� ������� ��� �����������.<br><br>";
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
                if ($usrlvl > 0)
                    echo "<tr><td colspan=2><input type='submit' value='��������������' disabled></td></tr>";
                else
                    echo "<tr><td colspan=2><input type='submit' value='��������������'></td></tr>";
		echo "</form></table>";
                if ($usrlvl > 0)
                {
                    echo "<br><br><h3>��� ����� �������� ��� ��� �������������� ����� ��� ���������. ������������� �� �� ����������� ���.</h3>";
                    echo "<br><a href=\"../index.php\">���������</a>";
                    mysql_close();
                    exit;
                }
                else
                    echo "<br><a href=\"../index.php\">���������</a>";
                // Allagh sxolikoy etoys
                if (isset($_POST['sxoletos']))
                {
                    //do2yphr($mysqlconnection);
                    $curSxoletos = getParam('sxol_etos', $mysqlconnection);
                    if ($curSxoletos == $_POST['sxoletos']){
                        echo "<br><br>";
                        die('������: �� ���� ���� ��� �������...');
                    }
                    setParam('sxol_etos', $_POST['sxoletos'], $mysqlconnection);
                    // more...
                    echo "<h3>��������� ������������ ������������� ��� ����� ��������� ���� �������� ����</h3>";
                    $query = "CREATE TABLE $tbl_bkp_mon SELECT * FROM employee";
                    $result = mysql_query($query, $mysqlconnection);
                    $query = "DROP TABLE employee_moved";
                    $result = mysql_query($query, $mysqlconnection);
                    // 388: allo pyspe, 389: apospasi se forea, 397: sxol. symvoulos, 399: Apospash ekswteriko
                    // thesi 2: d/nths, 4: dioikhtikos
                    $query = "CREATE TABLE employee_moved SELECT * FROM employee WHERE sx_yphrethshs NOT IN (388,389,397,399) AND thesi NOT IN (2,4)";
                    $result = mysql_query($query, $mysqlconnection);
                    $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs NOT IN (388,389,397,399) AND thesi NOT IN (2,4)";
                    $result = mysql_query($query, $mysqlconnection);
                    $num = mysql_affected_rows($mysqlconnection);
                    //echo $query;
                    if ($result)
                        echo "�������� �������� $num ��������.";
                    else 
                        echo "�������� ��� ��������...";
                    
                    echo "<h3>��������� ������������ ������������� ��� ���� �����</h3>";
                    $query = "INSERT INTO employee_moved SELECT * FROM employee WHERE sx_yphrethshs = 388";
                    $result = mysql_query($query, $mysqlconnection);
                    $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs = 388";
                    $result = mysql_query($query, $mysqlconnection);
                    $num = mysql_affected_rows($mysqlconnection);
                    //echo $query;
                    if ($result)
                        echo "�������� �������� $num ��������.";
                    else 
                        echo "�������� ��� ��������...";
                    
//                    echo "<h3>�������� ������������ ��� ���� ����� / ����� ��� ���� ���������</h3>";
//                    $query = "DROP TABLE employee_deleted";
//                    $result = mysql_query($query, $mysqlconnection);
//                    // 388: ���� �����, 394: ���� �����
//                    $query = "CREATE TABLE employee_deleted SELECT * FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397,389)";
//                    $result = mysql_query($query, $mysqlconnection);
//                    $query = "DELETE FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397,389)";
//                    $result = mysql_query($query, $mysqlconnection);
//                    $num = mysql_affected_rows();
//                    //echo $query;
//                    if ($result)
//                        echo "�������� �������� $num ��������.";
//                    else 
//                        echo "�������� ��� ��������...";
                    //
                    //do2yphr($mysqlconnection);
                    echo "<br><br>H ���������� ������������...";
                }
                // vevaiwseis proyphresias anaplhrwtwn
                if($_POST['type'] == 1 || $_POST['type'] == 7)
                {
                        if ($_POST['type'] == 1)
                            $kratikoy = 1;
                        $sxol_etos = getParam('sxol_etos', $mysqlconnection);
                        mysql_query("SET NAMES 'greek'", $mysqlconnection);
                        mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
                        // kratikoy or ESPA 
                        if ($kratikoy)
                            $query = "SELECT e.id,e.name,e.surname,e.patrwnymo,e.klados,p.name as praksi,p.ya,p.ada,p.apofasi,e.hm_anal,e.metakinhsh,e.afm from ektaktoi e JOIN praxi p ON e.praxi = p.id WHERE type IN (2)";
                        else
                            $query = "SELECT e.id,e.name,e.surname,e.patrwnymo,e.klados,p.name as praksi,p.ya,p.ada,p.apofasi,e.hm_anal,e.metakinhsh,e.afm from ektaktoi e JOIN praxi p ON e.praxi = p.id WHERE type IN (3,4,5,6)";

                        $result = mysql_query($query, $mysqlconnection);
                        $num=mysql_num_rows($result);

                        echo "<h3>�������� ���������� ����������� ";
                        if ($kratikoy)
                            echo "�������� ��������������";
                        else
                            echo "����";
                        echo "</h3>";
                        echo "<form name='anaplfrm' action=\"../employee/vev_yphr_anapl.php\" method='POST'>";

                        $i=0;
                        // ***********************
                        while ($i < $num)
                        //while ($i < 10) // for testing
                        // ***********************
                        {
                            $id = mysql_result($result, $i, "id");
                            $name = mysql_result($result, $i, "name");
                            $surname = mysql_result($result, $i, "surname");
                            $patrwnymo = mysql_result($result, $i, "patrwnymo");
                            $klados = mysql_result($result, $i, "klados");
                            $ya = mysql_result($result, $i, "ya") . ' (' . mysql_result($result, $i, "ada") . ')';
                            $apof = mysql_result($result, $i, "apofasi");
                            $hmpros = mysql_result($result, $i, "hm_anal");
                            $metakinhsh = mysql_result($result, $i, "metakinhsh");
                            $last_afm = substr (mysql_result($result, $i, "afm"), -3);
                            
                            // get yphrethseis
                            unset($sx_yphrethshs);
                            $sx_yphrethshs[] = array();
                            $qry = "select yphrethsh, hours from yphrethsh_ekt where emp_id = $id AND sxol_etos = $sxol_etos";
                            $res1 = mysql_query($qry, $mysqlconnection);
                            while ($arr = mysql_fetch_array($res1))
                            {
                                $sx_yphrethshs[] = array('sch' => $arr[0],'hours' => $arr[1]);
                            }
                            // Proteas has now praxi to distinguish workers.
                            $prefix = '';
                            $ebp = 0;
                            $praksi = mysql_result($result, $i, "praksi");
                            if (!$kratikoy)
                            {
                                $praksi = mysql_result($result, $i, "praksi");
                                if (strpos($praksi,'�������') !== false || strpos($praksi,'�������') !== false)
                                        $prefix = "ENIAIOY_";
                                elseif (strpos($praksi,'��������') !== false || strpos($praksi,'�����') !== false)
                                        $prefix = "ENISX_";
                                elseif (strpos($praksi,'�����') !== false)
                                        $prefix = "PEP_";
                                elseif (strpos($praksi,'���������') !== false || strpos($praksi,'���������') !== false || strpos($praksi,'���������') !== false
                                            || strpos($praksi,'�������������') !== false || strpos($praksi,'�������������') !== false || strpos($praksi,'�������������') !== false )
                                        $prefix = "PARAL_";
                                elseif (strpos($praksi,'������.') !== false || strpos($praksi,'��������������') !== false || strpos($praksi,'��������������') !== false 
                                            || strpos($praksi,'��������������') !== false || strpos($praksi,'������ �����') !== false || strpos($praksi,'������ �����') !== false)
                                        $prefix = "EKSATOM_";
                                elseif (strpos($praksi,'��������') !== false || strpos($praksi,'��������') !== false || strpos($praksi,'�����') !== false 
                                            || strpos($praksi,'�����') !== false)
                                        $prefix = "ANAPT_";
                                elseif (strpos($praksi,'��������') !== false || strpos($praksi,'��������') !== false || strpos($praksi,'��������') !== false)
                                        $prefix = "OLOHM_";
                                elseif (strpos($praksi,'��Ϡ�������') !== false || strpos($praksi,'��� �������') !== false || strpos($praksi,'��� �������') !== false || strpos($praksi,'���') !== false)
                                        $prefix = "NEO_";
                                elseif (strpos($praksi,'EKO') !== false || strpos($praksi,'���') !== false)
                                        $prefix = "EKO_";
                                else
                                        $prefix = '';
                                // check if ���
                                if (strpos($praksi,'���') !== false || strpos($praksi,'���') !== false)
                                {
                                    $ebp = 1;
                                    $prefix = 'EBP_' . $prefix;
                                }
                            }
                            else {
                                if (strpos($praksi,'���') !== false || strpos($praksi,'�.�.�.') !== false)
                                    $prefix = "PDE_";
                                else
                                    $prefix = '';
                            }
                            $metakinhsh = str_replace("'", "", $metakinhsh);
                            $emp_arr = array('name'=>$name,'surname'=>$surname,'patrwnymo'=>$patrwnymo,'klados'=>$klados,'sx_yphrethshs'=>$sx_yphrethshs,
                                'ya'=>$ya,'apof'=>$apof,'hmpros'=>$hmpros,'metakinhsh'=>$metakinhsh,'last_afm'=>$last_afm,'prefix'=>$prefix,'ebp'=>$ebp);

                            $submit_array[] = $emp_arr;
                            $i++;
                        }
                        echo "<input type='hidden' name='emp_arr' value='". serialize($submit_array) ."'>";
                        echo "<input type='hidden' name='kratikoy' value=$kratikoy>";
                        echo "<input type='hidden' name='plithos' value=$num>";
                        echo "<input type='submit' VALUE='������� ���������'>"; 
                        echo "</form>";
                }
                elseif ($_POST['type'] == 8)
                {
                        echo "<h3>������ �������� �����</h3>";
                        echo "������ ������� ����: $sxol_etos<br>";
                        echo "����� ��� ������� ���� (�.�. ��� �� ����.���� 2014-15 �������� <strong>201415</strong><br>";
                        echo "<form action='' method='POST'>";
                        echo "<input type='text' name='sxoletos'>";
                        echo "<input type='submit' value='�������'>";
                        echo "</form>";
                        echo "<small>���: � ���������� ������� ������ ���. �������...</small>";
                }
                elseif ($_POST['type'] == 2)
                {
                        echo "<h3>�������� ����������� / ���������� ��� ���� ���������</h3>";
                        // check if not empty
                        $query = "select id from ektaktoi";
                        $result = mysql_query($query, $mysqlconnection);
                        if (!mysql_num_rows($result))
                            exit('O ������� �������� ���������� ����� �����...');
                        //
                        //$query = "DROP TABLE ektaktoi_bkp";
                        //$result = mysql_query($query, $mysqlconnection);
                        $tbl_ekt = "ektaktoi_$sxol_etos";
                        $query = "CREATE TABLE $tbl_ekt SELECT * FROM ektaktoi";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "TRUNCATE table ektaktoi";
                        $result = mysql_query($query, $mysqlconnection);
                        
                        $tbl_prx = "praxi_$sxol_etos";
                        $query = "CREATE TABLE $tbl_prx SELECT * FROM praxi";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "TRUNCATE table praxi";
                        $result = mysql_query($query, $mysqlconnection);
                        if ($result)
                            echo "�������� ��������. <br><small>�� ���/��� ������������ ���� ������ $tbl_ekt</small>";
                        else 
                            echo "�������� ��� ��������...";
                }
                // epistrofh ekp/kwn Hrakleioy sthn organikh toys
                /*
                elseif ($_POST['type'] == 6)
                {
                        echo "<h3>��������� ������������ ������������� ��� ����� ��������� ���� �������� ����</h3>";
                        // check...
                        $query = "SELECT * FROM employee WHERE sx_yphrethshs NOT IN (389,397,399) AND sx_yphrethshs != sx_organikhs AND thesi NOT IN (2,4)";
                        $result = mysql_query($query, $mysqlconnection);
                        if (!mysql_num_rows($result))
                            exit('��� �������� ������������� ��\'���� ��� ��������...');
                        //
                        //$query = "DROP TABLE employee_bkp";
                        //$result = mysql_query($query, $mysqlconnection);
                        $query = "CREATE TABLE $tbl_bkp_mon SELECT * FROM employee";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "DROP TABLE employee_moved";
                        $result = mysql_query($query, $mysqlconnection);
                        // 389: apospasi se forea, 397: sxol. symvoulos, 399: Apospash ekswteriko
                        // thesi 2: d/nths, 4: dioikhtikos
                        $query = "CREATE TABLE employee_moved SELECT * FROM employee WHERE sx_yphrethshs NOT IN (389,397,399) AND thesi NOT IN (2,4)";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs NOT IN (389,397,399) AND thesi NOT IN (2,4)";
                        $result = mysql_query($query, $mysqlconnection);
                        $num = mysql_affected_rows();
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
                        $result = mysql_query($query, $mysqlconnection);
                        if (!mysql_num_rows($result))
                            exit('��� �������� ������������� ��\'���� ��� ��������...');
                        //
                        //$query = "DROP TABLE employee_bkp";
                        //$result = mysql_query($query, $mysqlconnection);
                        //$query = "CREATE TABLE employee_bkp SELECT * FROM employee";
                        //$result = mysql_query($query, $mysqlconnection);
                        $query = "INSERT INTO employee_moved SELECT * FROM employee WHERE sx_yphrethshs = 388";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs = 388";
                        $result = mysql_query($query, $mysqlconnection);
                        $num = mysql_affected_rows();
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
                        $result = mysql_query($query, $mysqlconnection);
                        if (!mysql_num_rows($result))
                            exit('��� �������� ������������� ��\' ���� ��� ��������...');
                        //
                        //$query = "DROP TABLE employee_bkp";
                        //$result = mysql_query($query, $mysqlconnection);
                        //$query = "CREATE TABLE employee_bkp SELECT * FROM employee";
                        //$result = mysql_query($query, $mysqlconnection);
                        $query = "DROP TABLE employee_deleted";
                        $result = mysql_query($query, $mysqlconnection);
                        // 388: ���� �����, 394: ���� �����
                        $query = "CREATE TABLE employee_deleted SELECT * FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397)";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "DELETE FROM employee WHERE sx_organikhs IN (388,394) AND thesi NOT IN (2,4) AND sx_yphrethshs NOT IN (397)";
                        $result = mysql_query($query, $mysqlconnection);
                        $num = mysql_affected_rows();
                        //echo $query;
                        if ($result)
                            echo "�������� �������� $num ��������.";
                        else 
                            echo "�������� ��� ��������...";
                }
                */
                elseif ($_POST['type'] == 4)
                {
                        echo "<h3>��������� ������������ ������������� ��� ������ (��� 31-08)</h3>";
                        // check...
                        $query = "SELECT * FROM employee WHERE sx_yphrethshs = 389";
                        $result = mysql_query($query, $mysqlconnection);
                        if (!mysql_num_rows($result))
                            exit('��� �������� ������������� ��\'���� ��� ��������...');
                        //
                        //$query = "DROP TABLE employee_bkp";
                        //$result = mysql_query($query, $mysqlconnection);
                        //$query = "CREATE TABLE employee_bkp SELECT * FROM employee";
                        //$result = mysql_query($query, $mysqlconnection);
                        $query = "INSERT INTO employee_moved SELECT * FROM employee WHERE sx_yphrethshs = 389";
                        $result = mysql_query($query, $mysqlconnection);
                        $query = "UPDATE employee SET sx_yphrethshs = sx_organikhs WHERE sx_yphrethshs = 389";
                        $result = mysql_query($query, $mysqlconnection);
                        $num = mysql_affected_rows();
                        //echo $query;
                        if ($result)
                            echo "�������� �������� $num ��������.";
                        else 
                            echo "�������� ��� ��������...";
                }
                // ������ ������� ���/���
                elseif ($_POST['type'] == 10)
                {
                    echo "<br><br><a href='update_wres.php'>������ ������� ���/���</a><br>";
                    echo "(�������� ����������� ��� ��������� 31/12 ��� ��������� ����� ��� ����� �������� ����������� ���� ��� ��)";
                }
                elseif ($_POST['type'] == 11)
                {
                    echo "<br><br><small>���: � ���������� ������� ������ ���. �������...</small>";
                    do2yphr($mysqlconnection);
                }
                
		mysql_close();

?>
<br><br>
  </body>
</html>