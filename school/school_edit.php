<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"../config.php";
  require_once "../tools/functions.php";
  //define("L_LANG", "el_GR"); Needs fixing
  require '../tools/calendar/tc_calendar.php';
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'greek'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
  
  // Demand authorization                
  require "../tools/class.login.php";
  $log = new logmein();
if($log->logincheck($_SESSION['loggedin']) == false) {
    header("Location: ../tools/login.php");
}
?>
<html>
  <head>
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>����������� ��������</title>
    
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.js"></script>
    <script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
    <script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
    <link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
    <script type="text/javascript" src='../tools/calendar/calendar.js'></script>
    <script type="text/javascript">    
        $().ready(function() {
            $("#org").autocomplete("../employee/get_school.php", {
                width: 260,
                matchContains: true,
                selectFirst: false
            });
        });
        $(document).ready(function() { 
            $("#mytbl").tablesorter({widgets: ['zebra']}); 
            $("#mytbl2").tablesorter({widgets: ['zebra']});
            $("#mytbl3").tablesorter({widgets: ['zebra']});
            $("#mytbl4").tablesorter({widgets: ['zebra']});
            $("#mytbl5").tablesorter({widgets: ['zebra']});
            $("#mytbl6").tablesorter({widgets: ['zebra']});
        });
    </script>
  </head>
  <body>
    <?php require '../etc/menu.php'; ?>
    <center>
        <h2>����������� ��������</h2>
        <?php     
      
        echo "<div id=\"content\">";
        echo "<form id='searchfrm' name='searchfrm' action='' method='POST' autocomplete='off'>";
        echo "<table class=\"imagetable stable\" border='1'>";
        echo "<td>�������</td><td></td><td><input type=\"text\" name=\"org\" id=\"org\" /></td></tr>";                
        echo "	</table>";
        echo "	<input type='submit' value='���������'>";
        //echo "  &nbsp;&nbsp;&nbsp;&nbsp;<input type='reset' value=\"���������\" onClick=\"window.location.reload()\">";
        echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' class='btn-red' VALUE='���������' onClick=\"parent.location='../index.php'\">";
        echo "	</form>";
        echo "</div>";
        
        if (isset($_POST['org']) || isset($_GET['org'])) {
            $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
            mysqli_query($mysqlconnection, "SET NAMES 'greek'");
            mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
                    
            if (isset($_POST['org'])) {
                    $str1 = $_POST['org'];
                    //$str1 = mb_convert_encoding($_POST['org'], "iso-8859-7", "utf-8");
                    $sch = getSchoolID($str1, $mysqlconnection);
            }
            else
            {
                $sch = $_GET['org'];
                $str1 = getSchool($sch, $mysqlconnection);
            }
                    
            echo "<h1>$str1</h1>";
            //disp_school($sch, $mysqlconnection);
            $query = "SELECT * from school where id=$sch";
            $result = mysqli_query($mysqlconnection, $query);

            $titlos = mysqli_result($result, 0, "titlos");
            $address = mysqli_result($result, 0, "address");
            $tk = mysqli_result($result, 0, "tk");
            $tel = mysqli_result($result, 0, "tel");
            $fax = mysqli_result($result, 0, "fax");
            $email = mysqli_result($result, 0, "email");
            $type = mysqli_result($result, 0, "type");
            $organikothta = mysqli_result($result, 0, "organikothta");
            $leitoyrg = get_leitoyrgikothta($sch, $mysqlconnection);
            $anenergo = mysqli_result($result, 0, "anenergo");
                    
            // if dimotiko
            if ($type == 1) {
                $students = mysqli_result($result, 0, "students");
                $classes = explode(",", $students);
                $frontistiriako = mysqli_result($result, 0, "frontistiriako");
                $ted = mysqli_result($result, 0, "ted");
                $oloimero_stud = mysqli_result($result, 0, "oloimero_stud");
                $tmimata = mysqli_result($result, 0, "tmimata");
                $tmimata_exp = explode(",", $tmimata);
                $oloimero_tea = mysqli_result($result, 0, "oloimero_tea");
                $ekp_ee = mysqli_result($result, 0, "ekp_ee");
                $ekp_ee_exp = explode(",", $ekp_ee);
                $vivliothiki = mysqli_result($result, 0, "vivliothiki");
                        
                $synolo = $classes[0]+$classes[1]+$classes[2]+$classes[3]+$classes[4]+$classes[5];
                $synolo_tmim = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
            }
            // if nipiagwgeio
            else if ($type == 2) {
                $klasiko = mysqli_result($result, 0, "klasiko");
                $klasiko_exp = explode(",", $klasiko);
                $oloimero_nip = mysqli_result($result, 0, "oloimero_nip");
                $oloimero_nip_exp = explode(",", $oloimero_nip);
                $nip = mysqli_result($result, 0, "nip");
                $nip_exp = explode(",", $nip);
                        
                $klasiko_synolo = array_sum($klasiko_exp);
                $oloimero_synolo = array_sum($oloimero_nip_exp);
            }
            $oloimero = mysqli_result($result, 0, "oloimero");
            $entaksis = explode(",", mysqli_result($result, 0, "entaksis"));
            $ypodoxis = mysqli_result($result, 0, "ypodoxis");                    
            $comments = mysqli_result($result, 0, "comments");
            // organikes - added 05-10-2012
            $organikes = unserialize(mysqli_result($result, 0, "organikes"));
            // kena_leit, kena_org - added 19-06-2013
            $kena_org = unserialize(mysqli_result($result, 0, "kena_org"));
            $kena_leit = unserialize(mysqli_result($result, 0, "kena_leit"));

            echo "<table class=\"imagetable\" border='1'>";
            echo "<form id='updatefrm' name='update' action='school_update.php' method='POST'>";
            echo "<tr><td colspan=3>������ (���������): <input type='text' name='titlos' value='$titlos' size='80'/></td></tr>";
            echo "<tr><td>�/���: <input type='text' name='address' value='$address' /> T.K.: <input size='5' type='text' name='tk' value='$tk' /></td><td>���.: <input type='text' name='tel' value='$tel' /></td></tr>";
            echo "<tr><td>email: <input type='text' name='email' value='$email' size='30'/></a></td><td>Fax: <input type='text' name='fax' value='$fax' /></td></tr>";
            echo "<tr><td>������������: <input type='text' name='organ' value='$organikothta' size='2'/><td></td></td></tr>";
            // 05-10-2012 - organikes
            if ($type == 1) {
                echo "<tr><td colspan=2>���������: ��70: <input type='text' name='organikes[]' value='$organikes[0]' size='2'/>";
                echo "&nbsp;&nbsp;��11: <input type='text' name='organikes[]' value='$organikes[1]' size='2'/>";
                echo "&nbsp;&nbsp;��06: <input type='text' name='organikes[]' value='$organikes[2]' size='2'/>";
                echo "&nbsp;&nbsp;��79: <input type='text' name='organikes[]' value='$organikes[3]' size='2'/>";
                echo "&nbsp;&nbsp;��05: <input type='text' name='organikes[]' value='$organikes[4]' size='2'/>";
                echo "&nbsp;&nbsp;��07: <input type='text' name='organikes[]' value='$organikes[5]' size='2'/>";
                echo "&nbsp;&nbsp;��08: <input type='text' name='organikes[]' value='$organikes[6]' size='2'/>";
                echo "&nbsp;&nbsp;��86: <input type='text' name='organikes[]' value='$organikes[7]' size='2'/>";
                echo "&nbsp;&nbsp;��91: <input type='text' name='organikes[]' value='$organikes[8]' size='2'/>";
            }  
            else {
                echo "<tr><td colspan=2>���������: ��60: <input type='text' name='organikes[]' value='$organikes[0]' size='2'/>";
            }
            echo "</td></tr>";
            // 19-06-2013 - kena_org, kena_leit
            if ($type == 1) {
                echo "<tr><td colspan=2>���. ����: &nbsp;��70: <input type='text' name='kena_org[]' value='$kena_org[0]' size='2'/>";
                echo "&nbsp;&nbsp;��11: <input type='text' name='kena_org[]' value='$kena_org[1]' size='2'/>";
                echo "&nbsp;&nbsp;��06: <input type='text' name='kena_org[]' value='$kena_org[2]' size='2'/>";
                echo "&nbsp;&nbsp;��79: <input type='text' name='kena_org[]' value='$kena_org[3]' size='2'/>";
                echo "&nbsp;&nbsp;��05: <input type='text' name='kena_org[]' value='$kena_org[4]' size='2'/>";
                echo "&nbsp;&nbsp;��07: <input type='text' name='kena_org[]' value='$kena_org[5]' size='2'/>";
                echo "&nbsp;&nbsp;��08: <input type='text' name='kena_org[]' value='$kena_org[6]' size='2'/>";
                echo "&nbsp;&nbsp;��86: <input type='text' name='kena_org[]' value='$kena_org[7]' size='2'/>";
                echo "&nbsp;&nbsp;��91: <input type='text' name='kena_org[]' value='$kena_org[8]' size='2'/>";
            }
            else {
                echo "<tr><td colspan=2>���. ����: ��60: <input type='text' name='kena_org[]' value='$kena_org[0]' size='2'/>";
            }
            echo "</td></tr>";
            // if ($type == 1)
            //     echo "<tr><td colspan=2>����. ����: ��70: <input type='text' name='kena_leit[]' value='$kena_leit[0]' size='2'/>";
            // else
            //     echo "<tr><td colspan=2>����. ����: ��60: <input type='text' name='kena_leit[]' value='$kena_leit[0]' size='2'/>";
            // echo "&nbsp;&nbsp;���. ������: <input type='text' name='kena_leit[]' value='$kena_leit[1]' size='2'/>";
            // echo "&nbsp;&nbsp;��������: <input type='text' name='kena_leit[]' value='$kena_leit[2]' size='2'/>";
            // echo "&nbsp;&nbsp;��������: <input type='text' name='kena_leit[]' value='$kena_leit[3]' size='2'/>";
            // echo "</td></tr>";
            //
            echo "<tr>";
            if ($entaksis[0]) {
                echo "<td><input type=\"checkbox\" name='entaksis' checked >����� ������� / �������: <input type='text' name='entaksis_math' value='$entaksis[1]' size='2'/></td>";
            } else {
                echo "<td><input type=\"checkbox\" name='entaksis'>����� ������� / �������: <input type='text' name='entaksis_math' value='$entaksis[1]' size='2'/></td>";
            }
            if ($ypodoxis) {
                echo "<td><input type=\"checkbox\" name='ypodoxis' checked >����� ��������</td>";
            } else {
                echo "<td><input type=\"checkbox\" name='ypodoxis' >����� ��������</td>";
            }
            echo "</tr>";
            echo "<tr><td>���/��� ��.�������: <input type='text' name='ekp_te' size='1' value='$ekp_ee_exp[0]' /></td><td colspan=3>���/��� ��.��������: <input type='text' name='ekp_ty' size='1' value='$ekp_ee_exp[1]' /></td></tr>";
            echo "<tr>";
                    
                    
            if ($type == 1) {
                echo $frontistiriako ? 
                    "<td><input type=\"checkbox\" name='frontistiriako' checked >�������������� �����</td>" :
                    "<td><input type=\"checkbox\" name='frontistiriako' >�������������� �����</td>";
                echo $oloimero ? 
                    "<td><input type=\"checkbox\" name='oloimero' checked >��������</td>" : 
                    "<td><input type=\"checkbox\" name='oloimero' >��������</td>";
                echo "</tr>";
                        
                echo "<tr>";
                echo $ted ? 
                    "<td><input type=\"checkbox\" name='ted' checked >��.�����.����������� (�.�.�.)</td>" :
                    "<td><input type=\"checkbox\" name='ted' >��.�����.����������� (�.�.�.)</td>";
                //echo "<td></td>";
                echo $anenergo ? 
                    "<td><input type=\"checkbox\" name='anenergo' checked >��������</td>" :
                    "<td><input type=\"checkbox\" name='anenergo' >��������</td>";
                echo "</tr>";
                echo $vivliothiki ? 
                    "<td><input type=\"checkbox\" name='vivliothiki' checked >������� ����������</td><td></td>" :
                    "<td><input type=\"checkbox\" name='vivliothiki' >������� ����������</td><td></td>";
                        
                echo "<tr><td colspan=2>������: <textarea rows='4' cols='80' name='comments'>$comments</textarea></td></tr>";
                echo "</table>";
                echo "<br>";
                        
                //if ($oloimero) - Afairethike gia taxythterh kataxwrhsh...
                //{
                /*
                    echo "<table class=\"imagetable\" border='1'>";
                    echo "<tr><td>������� ���������: <input type='text' name='oloimero_stud' value='$oloimero_stud' size='2'/></td>";
                    echo "<td>���/��� ���������: <input type='text' name='oloimero_tea' value='$oloimero_tea' size='2'/><td></tr>";
                    echo "</table>";
                */
                //}
                echo "<br>";

                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td colspan=8>������ ������� ��.: $synolo</td></tr>";
                echo "<tr><td>�'</td><td>�'</td><td>�'</td><td>�'</td><td>�'</td><td>��'</td><td>��.<small>(15.00/16.00)</small></td><td>��</td></tr>";
                if ($synolo>0) {
                    echo "<tr><td><input type='text' name='a' size='1' value=$classes[0] /></td><td><input type='text' name='b' size='1' value=$classes[1] /></td><td><input type='text' name='c' size='1' value=$classes[2] /></td><td><input type='text' name='d' size='1' value=$classes[3] /></td><td><input type='text' name='e' size='1' value=$classes[4] /></td><td><input type='text' name='f' size='1' value=$classes[5] /></td><td><input type='text' name='g' size='1' value=$classes[6] /></td><td><input type='text' name='h' size='1' value=$classes[7] /></td></tr>";
                } else {
                    echo "<tr><td><input type='text' name='a' size='1' value='0' /></td><td><input type='text' name='b' size='1' value='0' /></td><td><input type='text' name='c' size='1' value='0' /></td><td><input type='text' name='d' size='1' value='0' /></td><td><input type='text' name='e' size='1' value='0' /></td><td><input type='text' name='f' size='1' value='0' /></td><td><input type='text' name='g' size='1' value='0' /></td><td><input type='text' name='h' size='1' value='0' /></td></tr>";
                }
                echo "<tr><td colspan=8>������� (�������������) ��� ����<br>������ ��: $synolo_tmim</td></tr>";
                if ($synolo>0) {
                    echo "<tr><td><input type='text' name='ta' size='1' value=$tmimata_exp[0] /></td><td><input type='text' name='tb' size='1' value=$tmimata_exp[1] /></td><td><input type='text' name='tc' size='1' value=$tmimata_exp[2] /></td><td><input type='text' name='td' size='1' value=$tmimata_exp[3] /></td><td><input type='text' name='te' size='1' value=$tmimata_exp[4] /></td><td><input type='text' name='tf' size='1' value=$tmimata_exp[5] /></td><td><input type='text' name='tg' size='1' value=$tmimata_exp[6] /><input type='text' name='th' size='1' value=$tmimata_exp[7] /></td><td><input type='text' name='ti' size='1' value=$tmimata_exp[8] /></td></tr>";
                } else {
                    echo "<tr><td><input type='text' name='ta' size='1' value='0' /></td><td><input type='text' name='tb' size='1' value='0' /></td><td><input type='text' name='tc' size='1' value='0' /></td><td><input type='text' name='td' size='1' value='0' /></td><td><input type='text' name='te' size='1' value='0' /></td><td><input type='text' name='tf' size='1' value='0' /></td><td><input type='text' name='tg' size='1' value='0' /></td><td><input type='text' name='th' size='1' value='0' /></td></tr>";
                }
            }
            // if nip
            else if ($type == 2) {
                if ($oloimero) {
                    echo "<td><input type=\"checkbox\" name='oloimero' checked >��������</td>";
                } else {
                    echo "<td><input type=\"checkbox\" name='oloimero'>��������</td>";
                }
                echo "<td>������� ������� �����&nbsp;&nbsp;<input type='text' name='pz' size='1' value=$klasiko_exp[6]></td></tr>";
                echo "<tr><td>������: <textarea rows='4' cols='80' name='comments'>$comments</textarea></td>";
                echo $anenergo ? 
                    "<td><input type=\"checkbox\" name='anenergo' checked >��������</td>" :
                    "<td><input type=\"checkbox\" name='anenergo' >��������</td>";
                echo "</tr>";
                echo "</table>";
                echo "<br>";
                /////////    
                echo "<h3>�������</h3><br>";
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td rowspan=2>�����</td><td colspan=2>�������</td><td colspan=2>��������</td></tr>";
                echo "<tr><td>�����</td><td>��������</td><td>�����</td><td>��������</td></tr>";
                // t1
                echo "<tr><td>��.1</td><td><input type='text' name='k1a' size='1' value=$klasiko_exp[0]></td><td><input type='text' name='k1b' size='1' value=$klasiko_exp[1]>";
                echo "<td><input type='text' name='o1a' size='1' value=$oloimero_nip_exp[0]></td><td><input type='text' name='o1b' size='1' value=$oloimero_nip_exp[1]></td></tr>";
                // t2
                echo "<tr><td>��.2</td><td><input type='text' name='k2a' size='1' value=$klasiko_exp[2]></td><td><input type='text' name='k2b' size='1' value=$klasiko_exp[3]>";
                echo "<td><input type='text' name='o2a' size='1' value=$oloimero_nip_exp[2]></td><td><input type='text' name='o2b' size='1' value=$oloimero_nip_exp[3]></td></tr>";
                // t3
                echo "<tr><td>��.3</td><td><input type='text' name='k3a' size='1' value=$klasiko_exp[4]></td><td><input type='text' name='k3b' size='1' value=$klasiko_exp[5]>";
                echo "<td><input type='text' name='o3a' size='1' value=$oloimero_nip_exp[4]></td><td><input type='text' name='o3b' size='1' value=$oloimero_nip_exp[5]></td></tr>";
                echo "</table>";
                echo "<br>";
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td colspan=3>����������</td></tr>";
                echo "<tr><td>�������</td><td>��������</td><td>��.�������</td></tr>";
                echo "<tr><td><input type='text' name='ekp_kl' size='1' value=$nip_exp[0]></td><td><input type='text' name='ekp_ol' size='1' value=$nip_exp[1]></td><td><input type='text' name='ekp_te' size='1' value=$nip_exp[2]></td></tr>";
                echo "</table>";
            }
                    
            echo "</table>";
            echo "<br>";
                    
            echo "	<input type='hidden' name = 'sch' value='$sch'>";
            echo "	<input type='hidden' name = 'name' value='$str1'>";
                    
            echo "<input type='submit' value='����������'>";
            echo "</form>";
            $schLink = "school_status.php?org=$sch";
            echo "	&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' class='btn-red' VALUE='���������' onClick=\"parent.location='$schLink'\">";
                
        }
        ?>
        </center>
        <div id='results'></div>
        </body>
        </html>
