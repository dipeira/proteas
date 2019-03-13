<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once"../config.php";
	require_once"../tools/functions.php";
?>	
        
<?php        
	// apofaseis_adeiwn
        // Displays & exports apofaseis adeiwn to doc files. It also emails schools (using swift mailer).

        include("../tools/class.login.php");
        $log = new logmein();
        if($log->logincheck($_SESSION['loggedin']) == false)
            header("Location: ../tools/login.php");
        $usrlvl = $_SESSION['userlevel'];
        
        echo "<html><head>";
        echo "<link rel='stylesheet' type='text/css' href='../css/style.css' />";
        echo "</head><body>";
        include('../etc/menu.php');
        echo "<h2>��������� ������</h2>";
        echo "<table class='imagetable stable' border='1'>";
        echo "<form action='' method='POST'>";
        echo "<tr><td>������� ����������:</td><td><input type='text' name='prot'></td></tr>";
        echo "<tr><td>������������ ����</td><td><input type='text' name='year' value='".date('Y')."'></td></tr>";
        echo "<tr>";
        echo "<td colspan=2><input type='radio' name='type' value='1' checked >�������";
        echo "<input type='radio' name='type' value='2' >�����������<br></td>";
        echo "</tr>";
        echo "<tr><td colspan=2><input type='submit' value='�������'>";
        echo "<input type='button' class='btn-red' VALUE='���������' onClick=\"parent.location='../index.php'\">";
        echo "</td></tr>";
        echo "</form></table>";
        
        $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
        mysqli_query($mysqlconnection, "SET NAMES 'greek'");
        mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
        
        function getEmail($id,$conn)
        {
            $query = "SELECT email FROM school WHERE id=$id";
            $result = mysqli_query($conn, $query);
            return mysqli_result($result, 0);
        }

        // if user has submitted data
        if(isset($_POST['prot']) && isset($_POST['year']))
        {   
            if (!$_POST['year'] && !$_POST['prot'])
                exit('�������� ����� ��.����������� & ����.');
            if (!$_POST['year'])
                exit('�������� ����� ����.');
            if (!$_POST['prot'])
                exit('�������� ����� ��.�����������.');

            $has_errors = 0;
            // if monimoi
            if ($_POST['type'] == 1)
                $query = "SELECT a.id,emp_id,surname,e.name,start,days,prot,vev_dil,hm_apof,a.type,sx_yphrethshs,a.logos FROM adeia a
                    JOIN employee e ON a.emp_id = e.id WHERE a.prot_apof = ".$_POST['prot']." AND YEAR(hm_apof) = ".$_POST['year']." ORDER BY surname,name ASC";
            else
            {
                $is_anapl = 1;
                $query = "SELECT a.id,emp_id,surname,e.name,start,days,prot,vev_dil,hm_apof,a.type,sx_yphrethshs,a.logos FROM adeia_ekt a
                    JOIN ektaktoi e ON a.emp_id = e.id WHERE a.prot_apof = ".$_POST['prot']." AND YEAR(hm_apof) = ".$_POST['year']." ORDER BY surname,name ASC";
            }
            
            //echo $query;
            $result = mysqli_query($mysqlconnection, $query);
            $num=mysqli_num_rows($result);
            if (!$num)
            {
                echo "��� �������� �������� ��� ��� ��.����. ".$_POST['prot'];
                echo "<br><a href=\"../index.php\">���������</a>";
                exit;
            }

            $type = mysqli_result($result, 0, "type");
            if ($type == 1)
                $typewrd = "�����������";
            elseif ($type == 2)
                $typewrd = "���������";
            elseif ($type == 3)
                $typewrd = "����������� �� ���������� �/����� ��/��� ���������";
            elseif ($type == 4)
                $typewrd = "�������";
            $hm_apof_org = $hm_apof = mysqli_result($result, 0, "hm_apof");
            $hm_apof = date('d-m-Y',strtotime($hm_apof));
            $prot_apof = $_POST['prot'];
            echo "<h3>������� $typewrd ������ �� ��.����. $prot_apof/$hm_apof</h3>";
            echo "<table class='imagetable' border='1'>";
            if ($type == 1)
                echo "<tr><td>�������</td><td>�����</td><td>������</td><td>������</td><td>��.����.</td><td>���/��</td>";
            elseif ($type == 2)
                echo "<tr><td>�������</td><td>�����</td><td>������</td><td>������</td><td>��.����.</td><td>����.</td>";
            else
                echo "<tr><td>�������</td><td>�����</td><td>������</td><td>������</td><td>��.����.</td><td>�����</td>";
            $i=0;
            while ($i < $num)
            {
                $name = mysqli_result($result, $i, "name");
                $surname = mysqli_result($result, $i, "surname");
                $days = mysqli_result($result, $i, "days");
                $start = mysqli_result($result, $i, "start");
                $start = date("d-m-Y", strtotime($start));
                $prot = mysqli_result($result, $i, "prot");
                $vev_dil = mysqli_result($result, $i, "vev_dil");
                if ($is_anapl)
                {
                    $sx_yphrethshs_id_str = mysqli_result($result, $i, "sx_yphrethshs");
                    $sx_yphrethshs_id_arr = explode(",", $sx_yphrethshs_id_str);
                    $sch_code = $sx_yphrethshs_id_arr[0];
                }
                else
                    $sch_code = mysqli_result($result, $i, "sx_yphrethshs");
                $emp_id = mysqli_result($result, $i, "emp_id");
                $ad_id = mysqli_result($result, $i, "id");
                $typei = mysqli_result($result, $i, "type");
                $hm_apof_1 = mysqli_result($result, $i, "hm_apof");
                $logos = mysqli_result($result, $i, "logos");
                // if different date of apofasi
                if ($hm_apof_org <> $hm_apof_1)
                {
                    if ($is_anapl)
                        echo "<strong>�������:</strong> �������� ���� ��/��� �������� ������. ���/���: $surname $name, ?����: <a href='ekt_adeia.php?adeia=$ad_id&op=view' target='_blank'>$ad_id</a>.<br><br>";
                    else
                        echo "<strong>�������:</strong> �������� ���� ��/��� �������� ������. ���/���: $surname $name, ?����: <a href='adeia.php?adeia=$ad_id&op=view' target='_blank'>$ad_id</a>.<br><br>";
                    $error_found = 1;
                }
                //if different type of adeia
                if ($typei <> $type)
                {
                    if ($is_anapl)
                        echo "<strong>�������:</strong> �������� ���� ���� ������. ���/���: $surname $name, ?����: <a href='ekt_adeia.php?adeia=$ad_id&op=view' target='_blank'>$ad_id</a>.<br><br>";
                    else
                        echo "<strong>�������:</strong> �������� ���� ���� ������. ���/���: $surname $name, ?����: <a href='adeia.php?adeia=$ad_id&op=view' target='_blank'>$ad_id</a>.<br><br>";
                    $error_found = 1;
                }
                
                // if anarrwtikh, show vevaiwsh klp.
                if ($type == 1)
                {
                    switch ($vev_dil)
                    {
                        case 0:
                            $dik = "���";
                            break;
                        case 1:
                            $dik = "��������";
                            break;
                        case 2:
                            $dik = "�����.������";
                            break;
                    }
                }
                // if kanonikh, compute remaining days
                elseif ($type == 2)
                {
                    $ypol = ypoloipo_adeiwn($emp_id, $mysqlconnection,$is_anapl);
                    $dik = $ypol[1];
                }
                // if anarrwtikh me gnwmateysh, get vevaiwsh a/thmias yg.ep.
                // if ($type == 3 || $type == 4)
                else
                    $dik = $logos;

                if ($is_anapl){
                    if ($error_found)
                    echo "<tr><td><a href='ektaktoi.php?id=$emp_id&op=view'>$surname</a></td><td>$name</td><td>$days</td><td bgcolor='#FF0000'><a href='ekt_adeia.php?adeia=$ad_id&op=view' target='_blank'>$start</a></td><td>$prot</td><td>$dik</td><tr>";
                else
                    echo "<tr><td><a href='ektaktoi.php?id=$emp_id&op=view'>$surname</a></td><td>$name</td><td>$days</td><td><a href='ekt_adeia.php?adeia=$ad_id&op=view' target='_blank'>$start</a></td><td>$prot</td><td>$dik</td><tr>";
                }
                else{
                    if ($error_found)
                        echo "<tr><td><a href='employee.php?id=$emp_id&op=view'>$surname</a></td><td>$name</td><td>$days</td><td bgcolor='#FF0000'><a href='adeia.php?adeia=$ad_id&op=view' target='_blank'>$start</a></td><td>$prot</td><td>$dik</td><tr>";
                    else
                        echo "<tr><td><a href='employee.php?id=$emp_id&op=view'>$surname</a></td><td>$name</td><td>$days</td><td><a href='adeia.php?adeia=$ad_id&op=view' target='_blank'>$start</a></td><td>$prot</td><td>$dik</td><tr>";
                }
                $row = array($surname,$name,$days,$start,$prot,$dik,$sch_code);
                $emp[] = $row;
                $i++;
                if ($error_found)
                    $has_errors = 1;
                $error_found = 0;
            }
            echo "</table>";
            echo "<br>";
            $emp_ser = serialize($emp);
            if ($has_errors)
            {
                echo "�������� ����. �������� ���������.<br>";
                echo "<a href=\"../index.php\">��������� ���� ������ ������</a>";
                exit;
            }
            echo "<form id='wordfrm' name='wordfrm' action='' method='POST'>";
            echo "<input type='hidden' name=arr[] value=$type>";
            echo "<input type='hidden' name=arr[] value=$prot_apof>";
            echo "<input type='hidden' name=arr[] value=$hm_apof>";
            echo "<input type='hidden' name=arr[] value='$emp_ser'>";
            echo "<input type='hidden' name=arr[] value='$is_anapl'>";
            echo "<input type='hidden' name=arr[] value='".$_POST['year']."'>";
            echo "<INPUT name='btnSubmit' TYPE='submit' VALUE='�������� �������� ������'>";
            echo "&nbsp;&nbsp;&nbsp;";
            
            // check if already sent
            $qry = "SELECT * FROM apofaseis WHERE prwt = ".$_POST['prot']." AND year = ". $_POST['year'];
            $res = mysqli_query($mysqlconnection, $qry);
            if (mysqli_num_rows($res) > 0)
                echo "<br>�� email ��' ����� ��� ������� ����� ��� ������.</h3>";
            else {
                $email_msg = "����� �������� ��� ������ �� ����������� $num email �� �������� �������;";
                echo "<INPUT name='btnEmail' TYPE='submit' VALUE='�������� email ��� �������' onclick=\"javascript:return confirm('$email_msg');\">";
            }
            echo "</form>";
            echo "<small>���.: �� �������� ��������� ���� ���������� ������ ����� (������ �� email).<br>��� ����������� �� ��� ����������� �� ��� ����������� ������ ��� �� ������ ������� ������.</small>";
        }
        // if 'export to doc button' is pushed...
        if (isset($_POST['btnSubmit']))
        {
            // set max execution time 
            set_time_limit (180);
            
            $arr = $_POST['arr'];
            $type = $arr[0];
            $prot_apof = $arr[1];
            $hm_apof = $arr[2];
            $emp = unserialize($arr[3]);
            $emp_num = count($emp);
            $is_anapl = $arr[4];
            
            // include MyPHPWord which features cloneRow function
            require_once('../tools/phpword.php');
            $PHPWord = new MyPHPWord();
            if ($is_anapl)
            {
                if ($type == 1)
                    $document = $PHPWord->loadTemplate('../word/tmpl_apof/an_tmpl_apof_anar.docx');
                elseif ($type == 2)
                    $document = $PHPWord->loadTemplate('../word/tmpl_apof/an_tmpl_apof_kan.docx');
                elseif ($type == 3)
                    $document = $PHPWord->loadTemplate('../word/tmpl_apof/an_tmpl_apof_anar_gn.docx');
                elseif ($type == 4)
                    $document = $PHPWord->loadTemplate('../word/tmpl_apof/an_tmpl_apof_eid.docx');
                elseif ($type == 13)
                    $document = $PHPWord->loadTemplate('../word/tmpl_apof/an_tmpl_apof_ekl.docx');
            }
            else
            {
                if ($type == 1)
                    $document = $PHPWord->loadTemplate('../word/tmpl_apof/tmpl_apof_anar.docx');
                elseif ($type == 2)
                    $document = $PHPWord->loadTemplate('../word/tmpl_apof/tmpl_apof_kan.docx');
                elseif ($type == 3)
                    $document = $PHPWord->loadTemplate('../word/tmpl_apof/tmpl_apof_anar_gn.docx');
                elseif ($type == 4)
                    $document = $PHPWord->loadTemplate('../word/tmpl_apof/tmpl_apof_eid.docx');
                elseif ($type == 13)
                    $document = $PHPWord->loadTemplate('../word/tmpl_apof/an_tmpl_apof_ekl.docx');
            }
            $document->setValue('prot', $prot_apof);
            $document->setValue('hmprot', $hm_apof);
            
            $document->cloneRow('onoma', $emp_num);
            $i = 1;
            
            foreach ($emp as $ar)
            {
                $data = mb_convert_encoding($ar[0], "utf-8", "iso-8859-7");
                $document->setValue("epwnymo#$i", $data);
                $data = mb_convert_encoding($ar[1], "utf-8", "iso-8859-7");
                $document->setValue("onoma#$i", $data);
                $data = mb_convert_encoding($ar[2], "utf-8", "iso-8859-7");
                $document->setValue("days#$i", $data);
                $data = mb_convert_encoding($ar[3], "utf-8", "iso-8859-7");
                $document->setValue("start#$i", $data);
                $data = mb_convert_encoding($ar[4], "utf-8", "iso-8859-7");
                $document->setValue("protait#$i", $data);
                $data = mb_convert_encoding($ar[5], "utf-8", "iso-8859-7");
                $document->setValue("ypol#$i", $data);
                
                $schwrd = getSchool($ar[6], $mysqlconnection);
                $data = mb_convert_encoding($schwrd, "utf-8", "iso-8859-7");
                $document->setValue("sch#$i", $data);
                
                $i++;
            }
            // head_title & head_name
            $data = getParam('head_title', $mysqlconnection);
            $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
            $document->setValue('head_title', $data);
            $data = getParam('head_name', $mysqlconnection);
            $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
            $document->setValue('head_name', $data);
            
            $output1 = "../word/adeia_apof_".$_SESSION['userid'].".docx";
            $document->save($output1);
            echo "<html>";
            echo "<p><a href=$output1>������� ��������</a></p>";
            //echo "<br><a href=\"apof_ad.php\">���������</a>"; 
            //echo "</html>";
            
        }
        // email sending
        if (isset($_POST['btnEmail']))
        {
            // check if already sent
            $qry = "SELECT * FROM apofaseis WHERE prwt = ".$_POST['arr'][1]." AND year = ". $_POST['arr'][5];
            $res = mysqli_query($mysqlconnection, $qry);
            if (mysqli_num_rows($res) > 0)
            {
                echo "<h3>�� email ��' ����� ��� ������� ����� ��� ������.</h3>";
                echo "<br><a href=\"../index.php\">���������</a>";
                exit;
            }
            // set max execution time 
            set_time_limit (1000);
            
            // SMTP username & password
            global $smtp_password;
            global $smtp_username;
                        
            require_once '../vendor/autoload.php';
            // set up gmail transport
            $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
            ->setUsername($smtp_username)
            ->setPassword($smtp_password);

            $mailer = Swift_Mailer::newInstance($transport);
            
            // swiftmailer antiflood plugin (every 25 emails)
            $mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(15));
            // swiftmailer logger
            $logger = new Swift_Plugins_Loggers_ArrayLogger();
            $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
            
            // swiftmailer echo logger
            //$logger = new Swift_Plugins_Loggers_EchoLogger();
            //$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
            
            // Log setup
            $fname = '../tools/mail.log';
            if (file_exists($fname))
            {
                $temp = file_get_contents($fname);
                file_put_contents('../tools/mail.log.old', $temp, FILE_APPEND);
            }
            $type1 = $_POST['arr'][0];
            if ($type1 == 1)
                $type = "���������� �����";
            elseif ($type1 == 2)
                $type = "�������� �����";
            elseif ($type1 == 3)
                $type = "���������� ����� �� ����. �/����� ��/��� ���������";
            elseif ($type1 == 4)
                $type = "������ �����";
            elseif ($type1 == 13)
                $type = "�������� �����";
                
            $tmpl = "��� ������������ ��� �� ��� ��.�����. PROT/HMPRT ������� ���� ��������� TYPE DAYS ������ ��� START ����/���� ���/�� �� ��/�� SURNME NAME.\n\n�/��� �.�. ���������";
            
            $data = unserialize($_POST['arr'][3]);
            echo "<br><h3>�������� email ��� ��� �������: ".$_POST['arr'][1]."/".$_POST['arr'][2]."</h3><br>";
            foreach ($data as $dat)
            {
                $mail_body = str_replace('PROT', $_POST['arr'][1], $tmpl);
                $mail_body = str_replace('HMPRT', $_POST['arr'][2], $mail_body);
                $mail_body = str_replace('TYPE', $type, $mail_body);
                $mail_body = str_replace('DAYS', $dat[2], $mail_body);
                $mydate = date('d-m-Y',  strtotime($dat[3]));
                $mail_body = str_replace('START', $mydate, $mail_body);
                $mail_body = str_replace('SURNME', $dat[0], $mail_body);
                $mail_body = str_replace('NAME', $dat[1], $mail_body);
                
                // get & validate email address
                $email = getEmail($dat[6], $mysqlconnection);
                $email = filter_var( $email, FILTER_VALIDATE_EMAIL );
                if (!$email)
                {
                    $summary[] = array('name' => $dat[0], 'res' => -1);
                    continue;
                }
                $subject = $type;
                $from = "�/��� �� ���������";
                //$headers = "From:".$from;
                
                //echo "<br>$subject<br>".$mail_body."<br>".$email;
                $mymail = "mail@dipe.ira.sch.gr";
                                
                //utf8 encode
                $subject = mb_convert_encoding($subject, "utf-8", "iso-8859-7");
                $mail_body = mb_convert_encoding($mail_body, "utf-8", "iso-8859-7");
                
                $message = Swift_Message::newInstance($subject)
                ->setFrom($mymail)
                // *** SOS *** uncomment '$testemail', comment '$email' to test
                //->setTo("it@dipe.ira.sch.gr")
                ->setTo($email)
                ->setBody($mail_body);
                $result = $mailer->send($message);
                
                $summary[] = array('name' => $dat[0], 'email' => $email, 'res' => $result);
                // Log email activity
                $log = $logger->dump();
                $logger->clear();
                file_put_contents('../tools/mail.log', $log, FILE_APPEND);
            }                     
            // insert 2 db
            $qry = "INSERT INTO apofaseis (prwt, sent, result, year) VALUES 
            (".$_POST['arr'][1].",1,'".serialize($summary)."',".$_POST['arr'][5].")";
            $res = mysqli_query($mysqlconnection, $qry);
            
            // print results
            $oks = $errs = 0;
            echo "<br>";
            echo "<h3>������������</h3>";
            echo "<table border='1'>";
            foreach ($summary as $sum) {
                if ($sum['res'] == 1)
                {
                    echo "<tr><td>".$sum['name']."</td><td>".$sum['email']."</td><td>OK</td></tr>";
                    $oks++;
                }
                else
                {
                    echo "<tr><td>".$sum['name']."</td><td>".$sum['email']."</td><td>�������� (err.:".$sum['res'].")</td></tr>";
                    $errs++;
                }
            }
            echo "</table>";
            echo "$oks ������������ ���������.<br>$errs ����.";
        }
        mysqli_close($mysqlconnection);
?>
<br><br>

</html>