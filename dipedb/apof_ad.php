<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once"config.php";
	require_once"functions.php";
?>	
  <html>
  <head>      
        
<?php        
	// apofaseis_adeiwn
        // Displays & exports apofaseis adeiwn to doc files. It also emails schools (using swift mailer).

        include("tools/class.login.php");
        $log = new logmein();
        if($log->logincheck($_SESSION['loggedin']) == false)
            header("Location: tools/login_check.php");
        $usrlvl = $_SESSION['userlevel'];
        
        echo "<html><head><h2>Αποφάσεις αδειών</h2></head><body>";
        echo "<table class='imagetable' border='1'>";
        echo "<form action='' method='POST'>";
        echo "<tr><td>Αριθμός Πρωτοκόλου:</td><td><input type='text' name='prot'></td></tr>";
        echo "</td></tr>";
        echo "<tr>";
        echo "<td colspan=2><input type='radio' name='type' value='1' checked >Μόνιμοι";
        echo "<input type='radio' name='type' value='2' >Αναπληρωτές<br></td>";
        echo "</tr>";
        echo "<tr><td colspan=2><input type='submit' value='Υποβολή'></td></tr>";
        echo "</form></table>";
        
        $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
        mysql_select_db($db_name, $mysqlconnection);
        mysql_query("SET NAMES 'greek'", $mysqlconnection);
        mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
        
        function getEmail($id,$conn)
        {
            $query = "SELECT email FROM school WHERE id=$id";
            $result = mysql_query($query, $conn);
            return mysql_result($result, 0);
        }

        // if user has submitted data
        if(isset($_POST['prot']))
        {   
            $has_errors = 0;
            // if monimoi
            if ($_POST['type'] == 1)
                $query = "SELECT a.id,emp_id,surname,e.name,start,days,prot,vev_dil,hm_apof,a.type,sx_yphrethshs,a.logos FROM adeia a
                    JOIN employee e ON a.emp_id = e.id WHERE a.prot_apof = ".$_POST['prot']." ORDER BY surname,name ASC";
            else
            {
                $is_anapl = 1;
                $query = "SELECT a.id,emp_id,surname,e.name,start,days,prot,vev_dil,hm_apof,a.type,sx_yphrethshs,a.logos FROM adeia_ekt a
                    JOIN ektaktoi e ON a.emp_id = e.id WHERE a.prot_apof = ".$_POST['prot']." ORDER BY surname,name ASC";
            }
            
            //echo $query;
            $result = mysql_query($query, $mysqlconnection);
            $num=mysql_numrows($result);
            if (!$num)
            {
                echo "Δεν υπάρχουν εγγραφές για τον αρ.πρωτ. ".$_POST['prot'];
                echo "<br><a href=\"index.php\">Επιστροφή</a>";
                exit;
            }

            $type = mysql_result($result, 0, "type");
            if ($type == 1)
                $typewrd = "αναρρωτικών";
            elseif ($type == 2)
                $typewrd = "κανονικών";
            elseif ($type == 3)
                $typewrd = "αναρρωτικών με γνωμάτευση Α/θμιας Υγ/κής Επιτροπής";
            elseif ($type == 4)
                $typewrd = "ειδικών";
            $hm_apof_org = $hm_apof = mysql_result($result, 0, "hm_apof");
            $hm_apof = date('d-m-Y',strtotime($hm_apof));
            $prot_apof = $_POST['prot'];
            echo "<h3>Απόφαση $typewrd αδειών με αρ.πρωτ. $prot_apof/$hm_apof</h3>";
            echo "<table class='imagetable' border='1'>";
            if ($type == 1)
                echo "<tr><td>Επώνυμο</td><td>Όνομα</td><td>Ημέρες</td><td>Έναρξη</td><td>Αρ.Πρωτ.</td><td>Δικ/κό</td>";
            elseif ($type == 2)
                echo "<tr><td>Επώνυμο</td><td>Όνομα</td><td>Ημέρες</td><td>Έναρξη</td><td>Αρ.Πρωτ.</td><td>Υπολ.</td>";
            else
                echo "<tr><td>Επώνυμο</td><td>Όνομα</td><td>Ημέρες</td><td>Έναρξη</td><td>Αρ.Πρωτ.</td><td>Λόγος</td>";
            $i=0;
            while ($i < $num)
            {
                $name = mysql_result($result, $i, "name");
                $surname = mysql_result($result, $i, "surname");
                $days = mysql_result($result, $i, "days");
                $start = mysql_result($result, $i, "start");
                $start = date("d-m-Y", strtotime($start));
                $prot = mysql_result($result, $i, "prot");
                $vev_dil = mysql_result($result, $i, "vev_dil");
                if ($is_anapl)
                {
                    $sx_yphrethshs_id_str = mysql_result($result, $i, "sx_yphrethshs");
                    $sx_yphrethshs_id_arr = explode(",", $sx_yphrethshs_id_str);
                    $sch_code = $sx_yphrethshs_id_arr[0];
                }
                else
                    $sch_code = mysql_result($result, $i, "sx_yphrethshs");
                $emp_id = mysql_result($result, $i, "emp_id");
                $ad_id = mysql_result($result, $i, "id");
                $typei = mysql_result($result, $i, "type");
                $hm_apof_1 = mysql_result($result, $i, "hm_apof");
                $logos = mysql_result($result, $i, "logos");
                // if different date of apofasi
                if ($hm_apof_org <> $hm_apof_1)
                {
                    if ($is_anapl)
                        echo "<strong>ΠΡΟΣΟΧΗ:</strong> Πρόβλημα στην ημ/νία ΑΠΟΦΑΣΗΣ άδειας. Εκπ/κός: $surname $name, ?δεια: <a href='ekt_adeia.php?adeia=$ad_id&op=view' target='_blank'>$ad_id</a>.<br><br>";
                    else
                        echo "<strong>ΠΡΟΣΟΧΗ:</strong> Πρόβλημα στην ημ/νία ΑΠΟΦΑΣΗΣ άδειας. Εκπ/κός: $surname $name, ?δεια: <a href='adeia.php?adeia=$ad_id&op=view' target='_blank'>$ad_id</a>.<br><br>";
                    $error_found = 1;
                }
                //if different type of adeia
                if ($typei <> $type)
                {
                    if ($is_anapl)
                        echo "<strong>ΠΡΟΣΟΧΗ:</strong> Πρόβλημα στον τύπο άδειας. Εκπ/κός: $surname $name, ?δεια: <a href='ekt_adeia.php?adeia=$ad_id&op=view' target='_blank'>$ad_id</a>.<br><br>";
                    else
                        echo "<strong>ΠΡΟΣΟΧΗ:</strong> Πρόβλημα στον τύπο άδειας. Εκπ/κός: $surname $name, ?δεια: <a href='adeia.php?adeia=$ad_id&op=view' target='_blank'>$ad_id</a>.<br><br>";
                    $error_found = 1;
                }
                
                // if anarrwtikh, show vevaiwsh klp.
                if ($type == 1)
                {
                    switch ($vev_dil)
                    {
                        case 0:
                            $dik = "Όχι";
                            break;
                        case 1:
                            $dik = "Βεβαίωση";
                            break;
                        case 2:
                            $dik = "Υπεύθ.Δήλωση";
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
                echo "Βρέθηκαν ΛΑΘΗ. Παρακαλώ διορθώστε.<br>";
                echo "<a href=\"index.php\">Επιστροφή στην αρχική σελίδα</a>";
                exit;
            }
            echo "<form id='wordfrm' name='wordfrm' action='' method='POST'>";
            echo "<input type='hidden' name=arr[] value=$type>";
            echo "<input type='hidden' name=arr[] value=$prot_apof>";
            echo "<input type='hidden' name=arr[] value=$hm_apof>";
            echo "<input type='hidden' name=arr[] value='$emp_ser'>";
            echo "<input type='hidden' name=arr[] value='$is_anapl'>";
            echo "<INPUT name='btnSubmit' TYPE='submit' VALUE='Εκτύπωση απόφασης αδειών'>";
            echo "&nbsp;&nbsp;&nbsp;";
            
            // check if already sent
            $qry = "SELECT * FROM apofaseis WHERE prwt = ".$_POST['prot'];
            $res = mysql_query($qry, $mysqlconnection);
            if (mysql_num_rows($res) > 0)
                echo "<br>Τα email γι' αυτήν την απόφαση έχουν ήδη σταλεί.</h3>";
            else
            {
                $email_msg = "Είστε σίγουροι ότι θέλετε να αποστείλετε $num email σε ισάριθμα σχολεία;";
                echo "<INPUT name='btnEmail' TYPE='submit' VALUE='Αποστολή email στα σχολεία' onclick=\"javascript:return confirm('$email_msg');\">";
            }
            echo "</form>";
            echo "<small>ΣΗΜ.: Οι παραπάνω ενέργειες ίσως χρειαστούν αρκετό χρόνο (ειδικά τα email).<br>Μην επιχειρείτε να τις επαναλάβετε αν δεν εκτελεστούν αμέσως και δε λάβετε σχετικό μήνυμα.</small>";
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
            
            require_once 'tools/PHPWord.php';
            $PHPWord = new PHPWord();
            if ($is_anapl)
            {
                if ($type == 1)
                    $document = $PHPWord->loadTemplate('word/apof/an_tmpl_apof_anar.docx');
                elseif ($type == 2)
                    $document = $PHPWord->loadTemplate('word/apof/an_tmpl_apof_kan.docx');
                elseif ($type == 3)
                    $document = $PHPWord->loadTemplate('word/apof/an_tmpl_apof_anar_gn.docx');
                elseif ($type == 4)
                    $document = $PHPWord->loadTemplate('word/apof/an_tmpl_apof_eid.docx');
                elseif ($type == 13)
                    $document = $PHPWord->loadTemplate('word/apof/an_tmpl_apof_ekl.docx');
            }
            else
            {
                if ($type == 1)
                    $document = $PHPWord->loadTemplate('word/apof/tmpl_apof_anar.docx');
                elseif ($type == 2)
                    $document = $PHPWord->loadTemplate('word/apof/tmpl_apof_kan.docx');
                elseif ($type == 3)
                    $document = $PHPWord->loadTemplate('word/apof/tmpl_apof_anar_gn.docx');
                elseif ($type == 4)
                    $document = $PHPWord->loadTemplate('word/apof/tmpl_apof_eid.docx');
                elseif ($type == 13)
                    $document = $PHPWord->loadTemplate('word/apof/an_tmpl_apof_ekl.docx');
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
            $output1 = "word/apof/adeia_apof_".$_SESSION['userid'].".docx";
            $document->save($output1);
            echo "<html>";
            echo "<p><a href=$output1>Ανοιγμα εγγράφου</a></p>";
            //echo "<br><a href=\"apof_ad.php\">Επιστροφή</a>"; 
            //echo "</html>";
            
        }
        // email sending
        if (isset($_POST['btnEmail']))
        {
            // check if already sent
            $qry = "SELECT * FROM apofaseis WHERE prwt = ".$_POST['arr'][1];
            //echo $query;
            $res = mysql_query($qry, $mysqlconnection);
            if (mysql_num_rows($res) > 0)
            {
                echo "<h3>Τα email γι' αυτήν την απόφαση έχουν ήδη σταλεί.</h3>";
                echo "<br><a href=\"index.php\">Επιστροφή</a>";
                exit;
            }
            // set max execution time 
            set_time_limit (360);
            
            // SMTP password
            $pass = file_get_contents('../conf.txt');
            
            require_once 'tools/lib/swift_required.php';
            $transport = Swift_SmtpTransport::newInstance('mail.sch.gr', 25)
            ->setUsername('dipeira')
            ->setPassword($pass)
            ;
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
            $fname = 'tools/mail.log';
            if (file_exists($fname))
            {
                $temp = file_get_contents($fname);
                file_put_contents('tools/mail.log.old', $temp, FILE_APPEND);
            }
            $type1 = $_POST['arr'][0];
            if ($type1 == 1)
                $type = "αναρρωτική άδεια";
            elseif ($type1 == 2)
                $type = "κανονική άδεια";
            elseif ($type1 == 3)
                $type = "αναρρωτική άδεια με γνωμ. Α/θμιας Υγ/κής Επιτροπής";
            elseif ($type1 == 4)
                $type = "ειδική άδεια";
            elseif ($type1 == 13)
                $type = "εκλογική άδεια";
                
            $tmpl = "Σας ενημερώνουμε ότι με την υπ.αριθμ. PROT/HMPRT απόφαση έχει χορηγηθεί TYPE DAYS ημερών από START στον/στην εκπ/κό με ον/μο SURNME NAME.\n\nΔ/νση Π.Ε. Ηρακλείου";
            
            $data = unserialize($_POST['arr'][3]);
            echo "<br><h3>Αποστολή email για την απόφαση: ".$_POST['arr'][1]."/".$_POST['arr'][2]."</h3><br>";
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
                $from = "Δ/νση ΠΕ Ηρακλείου";
                //$headers = "From:".$from;
                
                //echo "<br>$subject<br>".$mail_body."<br>".$email;
                $mymail = "mail@dipe.ira.sch.gr";
                $testmail = "it@dipe.ira.sch.gr";
                                
                //utf8 encode
                $subject = mb_convert_encoding($subject, "utf-8", "iso-8859-7");
                $mail_body = mb_convert_encoding($mail_body, "utf-8", "iso-8859-7");
                
                $message = Swift_Message::newInstance($subject)
                ->setFrom($mymail)
                // *** SOS *** uncomment '$testemail', comment '$email' to test
                //->setTo($testmail)
                ->setTo($email)
                ->setBody($mail_body);
                $result = $mailer->send($message);
                
                $summary[] = array('name' => $dat[0], 'email' => $email, 'res' => $result);
                // Log email activity
                $log = $logger->dump();
                $logger->clear();
                file_put_contents('tools/mail.log', $log, FILE_APPEND);
            }                     
            // insert 2 db
            $qry = "INSERT INTO apofaseis (prwt, sent, result) VALUES (".$_POST['arr'][1].",1,'".serialize($summary)."')";
            $res = mysql_query($qry, $mysqlconnection);
            
            // print results
            $oks = $errs = 0;
            echo "<br>";
            echo "<h3>Αποτελέσματα</h3>";
            echo "<table border='1'>";
            foreach ($summary as $sum) {
                if ($sum['res'] == 1)
                {
                    echo "<tr><td>".$sum['name']."</td><td>".$sum['email']."</td><td>OK</td></tr>";
                    $oks++;
                }
                else
                {
                    echo "<tr><td>".$sum['name']."</td><td>".$sum['email']."</td><td>Πρόβλημα (err.:".$sum['res'].")</td></tr>";
                    $errs++;
                }
            }
            echo "</table>";
            echo "$oks επιτυχημένες αποστολές.<br>$errs λάθη.";
        }
        mysql_close();
?>
<br><br>
<a href="index.php">Επιστροφή</a>
</html>