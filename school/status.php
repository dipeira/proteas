<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once "../config.php";
  require_once "../tools/functions.php";
  
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($mysqlconnection, "SET NAMES 'greek'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
  
  // If in production, login using sch.gr's CAS server
  // (To be able to login via sch.gr's CAS, the app must be whitelisted from their admins)
  $prDebug = 0;
  if (!$prDebug)
  {
    // phpCAS simple client, import phpCAS lib (downloaded with composer)
    require_once('../vendor/jasig/phpcas/CAS.php');
    //initialize phpCAS using SAML
    phpCAS::client(SAML_VERSION_1_1,'sso-test.sch.gr',443,'');
    // if logout
    if (isset($_POST['logout']))
    {
      session_unset();
      session_destroy(); 
      phpCAS::logout();
      die('���������������� ������...<br>������������ ��� �� ����� ���!');
    }
    
    // no SSL validation for the CAS server, only for testing environments
    phpCAS::setNoCasServerValidation();
    // handle backend logout requests from CAS server
    phpCAS::handleLogoutRequests(array('sso-test.sch.gr'));
    // force CAS authentication
    if (!phpCAS::checkAuthentication())
      phpCAS::forceAuthentication();
    // at this step, the user has been authenticated by the CAS server and the user's login name can be read with phpCAS::getUser().
    $_SESSION['loggedin'] = 1;
    $sch_code = phpCAS::getAttribute('edupersonorgunitdn:gsnunitcode');
    $sch = getSchoolFromCode($sch_code, $mysqlconnection);
  }
  else {
    if (!$_GET['code']){
      die('������: ��� ���� �������� �������...');
    }
    $sch = getSchoolFromCode($_GET['code'], $mysqlconnection);
    //$_SESSION['loggedin'] = 1;
  }
  if (isset($_POST['type'])){
    if ($_POST['type'] == 'insert'){
      //$text = mb_convert_encoding($_POST['request'], "iso-8859-7", "utf-8");
      // remove single quotes
      $text = str_replace('\'', '', $_POST['request']);
      $sname = getSchool($_POST['school'], $mysqlconnection);
      $query = "INSERT INTO school_requests (request, school, school_name, submitted, sxol_etos) VALUES ('$text', '". $_POST['school']."','".$sname."', NOW(), $sxol_etos)";
      $result = mysqli_query($mysqlconnection, $query);
      $ret = $result ? '�������� ���������� ���������!' : '������������� ������ ���� ��� ����������';
      echo "<h1>$ret</h1>";//mb_convert_encoding($ret, "utf-8", "iso-8859-7");  
    } 
    // else {
    //   $text = mb_convert_encoding($_POST['comment'], "iso-8859-7", "utf-8");
    //   $query = "UPDATE school_requests SET comment = '$text',done = ".$_POST['done']." WHERE id=".$_POST['id'];
    //   $result = mysqli_query($mysqlconnection, $query);
    //   $ret = $result ? '�������� ��������� ���������!' : '������������� ������ ���� ��� ���������';
    //   echo mb_convert_encoding($ret, "utf-8", "iso-8859-7");
    // }
    ?>
    <meta http-equiv="refresh" content="2; URL=status.php">
    <?php
    die();
  }
?>
<html>
  <head>
    <style>
      @import url('https://fonts.googleapis.com/css?family=Open+Sans&subset=greek');

      body * {
        font-family : "Open Sans",Verdana,Helvetica,Arial,sans-serif;
      }
      .imagetable {
        /* font-size : 90%; */
        /* font-family : Verdana,Helvetica,Arial,sans-serif; */
        font-size:15px;
        color:#333333;
        border-width: 1px;
        border-color: #999999;
        border-collapse: collapse;
        width: 90%;
      }
      .imagetable th {
        /* font-family : Verdana,Helvetica,Arial,sans-serif; */
        background:#b5cfd2;
        border-width: 1px;
        padding: 8px;
        border-style: solid;
        border-color: #999999;
      }
      .imagetable td {
        /* font-size : 80%; */
        /* font-family : Verdana,Helvetica,Arial,sans-serif; */
        background:#dddebc;
        border-width: 1px;
        padding: 6px;
        border-style: solid;
        border-color: #999999;
      }

      .imagetable tr:nth-child(even){background-color: #f2f2f2;}

      .imagetable tr:hover {background-color: #ddd;}

      .imagetable th {
        padding-top: 10px;
        padding-bottom: 10px;
        text-align: left;
        background-color: #2a672ddb;
        color: white;
      }
      .stable {
        width: auto;
      }
    </style>
    <LINK href="./style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>������� ��������</title>
    
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.0.6/js/jquery.tablesorter.js"></script> 
    <script type="text/javascript">
      $().ready(function() {
          $("#slidingDiv").hide();
          $("#slidingDiv2").hide();
          $('#show_hide').click(function(){
              $("#slidingDiv").slideToggle();
          });
          $('#show_hide2').click(function(){
              $("#slidingDiv2").slideToggle();
          });
      });
      $(document).ready(function() { 
          $(".tablesorter").tablesorter({widgets: ['zebra']}); 
          $('#toggleBtn').click(function(){
              event.preventDefault();
              $("#analysis").slideToggle();
          });
          $('#toggleSystegBtn').click(function(){
              event.preventDefault();
              $("#systeg").slideToggle();
          });
          // alert user on empty input
          $('#submit').click(function(){
            if($.trim($('#request').val()) == ''){
              event.preventDefault();
              alert('������: �� ������ ��� ��� ������ �� ����� ����.\n�� ��������� ��� ��� ���������� ������ �����, �� ���������� ������ ��������.');
              return false;
            }
          });
      });    
    </script>
  </head>
  <body> 
    <center>
        <h1><?= getParam('foreas',$mysqlconnection);?> <br> ������������ ������� "�������"</h1>
    <?php
    function disp_school($sch,$sxol_etos,$conn)
    {
        $query = "SELECT * from school where id=$sch";
        $result = mysqli_query($conn, $query);
                
        $titlos = mysqli_result($result, 0, "titlos");
        $address = mysqli_result($result, 0, "address");
        $tk = mysqli_result($result, 0, "tk");
        $dimos = mysqli_result($result, 0, "dimos");
        $dimos = getDimos($dimos, $conn);
        $cat = getCategory(mysqli_result($result, 0, "category"));
        $tel = mysqli_result($result, 0, "tel");
        $fax = mysqli_result($result, 0, "fax");
        $email = mysqli_result($result, 0, "email");
        $type = mysqli_result($result, 0, "type");
        $type2 = mysqli_result($result, 0, "type2");
        $organikothta = mysqli_result($result, 0, "organikothta");
        $leitoyrg = get_leitoyrgikothta($sch, $conn);
        // organikes - added 05-10-2012
        $organikes = unserialize(mysqli_result($result, 0, "organikes"));
        // kena_org, kena_leit - added 19-06-2013
        $kena_org = unserialize(mysqli_result($result, 0, "kena_org"));
        $code = mysqli_result($result, 0, "code");
        $updated = mysqli_result($result, 0, "updated");
        $perif = mysqli_result($result, 0, "perif");
        $systeg = mysqli_result($result, 0, "systeg");
        $anenergo = mysqli_result($result, 0, "anenergo");
        if ($systeg) {
            $systegName = getSchool($systeg, $conn);
        }
        $archive = mysqli_result($result, 0, "archive");
                        
        // if dimotiko
        if ($type == 1) {
            $students = mysqli_result($result, 0, "students");
            $classes = explode(",", $students);
            $frontistiriako = mysqli_result($result, 0, "frontistiriako");
            $ted = mysqli_result($result, 0, "ted");
            //$oloimero_stud = mysqli_result($result, 0, "oloimero_stud");
            $tmimata = mysqli_result($result, 0, "tmimata");
            $tmimata_exp = explode(",", $tmimata);
            //$oloimero_tea = mysqli_result($result, 0, "oloimero_tea");
            $ekp_ee = mysqli_result($result, 0, "ekp_ee");
            $ekp_ee_exp = explode(",", $ekp_ee);
            
            $synolo = array_sum($classes);
            //$synolo_tmim = array_sum($tmimata_exp);
            $vivliothiki = mysqli_result($result, 0, "vivliothiki");
        }
        //if nipiagwgeio
        if ($type == 2) {
            $klasiko = mysqli_result($result, 0, "klasiko");
            $klasiko_exp = explode(",", $klasiko);
            $oloimero_nip = mysqli_result($result, 0, "oloimero_nip");
            $oloimero_nip_exp = explode(",", $oloimero_nip);
            $nip = mysqli_result($result, 0, "nip");
            $nip_exp = explode(",", $nip);
        }
        // entaksis (varchar): on/off, no. of students
        $entaksis = explode(",", mysqli_result($result, 0, "entaksis"));
        $org_ent = $entaksis[0] ? 1 : 0;
        $ypodoxis = mysqli_result($result, 0, "ypodoxis");
        //$frontistiriako = mysqli_result($result, 0, "frontistiriako");
        $oloimero = mysqli_result($result, 0, "oloimero");
        $comments = mysqli_result($result, 0, "comments");
        
        echo "<h2 style='text-align:left;'>�. �������� �������� �������</h2>";
        echo "<table class=\"imagetable\" border='1'>";
        echo "<tr><td colspan=3>������ (���������): $titlos</td></tr>";
        echo "<tr><td>�/���: $address - �.�. $tk - �����: $dimos</td><td>���.: $tel</td></tr>";
        echo "<tr><td>email: <a href=\"mailto:$email\">$email</a></td><td>Fax: $fax</td></tr>";
        echo "<tr><td>������������: $organikothta</td><td>���������������: $leitoyrg</td></tr>";
        
        // �������� ��������������
        $klados_qry = ($type == 1) ? 2 : 1;
        $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados= $klados_qry AND status IN (1,3) AND thesi IN (0,1,2)";
        $rs = mysqli_query($conn, $qry);
        $orgtop = mysqli_result($rs, 0, "cnt");
        echo "<tr><td>�������� �������������� (���� �.�.): $orgtop</td><td colspan=3>���������: $cat</td></tr>";
        
        // 05-10-2012 - organikes
        for ($i=0; $i<count($organikes); $i++) {
            if (!$organikes[$i]) {
                $organikes[$i]=0;
            }
        }
        
        // if ds
        if ($type == 1) {
            echo "<tr><td colspan=2><a href='#' id='show_hide'>���������</a><br>";
            echo "<div id='slidingDiv'>";
            echo "<table>";
            echo "<thead><tr>";
            echo "<th>������</th>";
            echo "<th><span title='��������'>70</th>";
            echo "<th><span title='������� ������'>11</th>";
            echo "<th><span title='��������'>06</th>";
            echo "<th><span title='��������'>79</th>";
            echo "<th><span title='��������'>05</th>";
            echo "<th><span title='����������'>07</th>";
            echo "<th><span title='�������������'>08</th>";
            echo "<th><span title='������������'>86</th>";
            echo "<th><span title='��������� �������'>91</th>";
            echo $org_ent ? "<th>�������</th>" : '';
            if ($type2 == 2) {
              echo "<th><span title='��������������'>21</th>";
              echo "<th><span title='���������'>23</th>";
              echo "<th><span title='��.����������'>25</th>";
              echo "<th><span title='��������������'>26</th>";
              echo "<th><span title='����������������'>28</th>";
              echo "<th><span title='��������������'>29</th>";
              echo "<th><span title='����.����������'>30</th>";
              echo "<th><span title='����.����.���.��.'>��1���</th>";
            }
            echo "</tr></thead>";
            echo "<tbody><tr>";
            echo "<td>���������</td>";
            echo "<td>$organikes[0]</td>";
            echo "<td>$organikes[1]</td>";
            echo "<td>$organikes[2]</td>";
            echo "<td>$organikes[3]</td>";
            echo "<td>$organikes[4]</td>";
            echo "<td>$organikes[5]</td>";
            echo "<td>$organikes[6]</td>";
            echo "<td>$organikes[7]</td>";
            echo "<td>$organikes[8]</td>";
            echo $org_ent ? "<td>$org_ent</td>" : '';
            if ($type2 == 2) {
              echo "<td>$organikes[9]</td>";
              echo "<td>$organikes[10]</td>";
              echo "<td>$organikes[11]</td>";
              echo "<td>$organikes[12]</td>";
              echo "<td>$organikes[13]</td>";
              echo "<td>$organikes[14]</td>";
              echo "<td>$organikes[15]</td>";
              echo "<td>$organikes[16]</td>";
            }
            echo "</tr>";
        }
        // if nip
        else {
            echo "<tr><td colspan=2>���������: ��60: $organikes[0]";
            if ($type2 == 2) {
              echo "<table>";
              echo "<thead><tr>";
                echo "<th>������</th>";
                echo "<th><span title='��������������'>21</th>";
                echo "<th><span title='���������'>23</th>";
                echo "<th><span title='��.����������'>25</th>";
                echo "<th><span title='��������������'>26</th>";
                echo "<th><span title='����������������'>28</th>";
                echo "<th><span title='��������������'>29</th>";
                echo "<th><span title='����.����������'>30</th>";
                echo "<th><span title='����.����.���.��.'>��1���</th>";
              echo "</tr></thead><tbody>";
              echo "<tr>";
                echo "<td>���������</td>";
                echo "<td>$organikes[1]</td>";
                echo "<td>$organikes[2]</td>";
                echo "<td>$organikes[3]</td>";
                echo "<td>$organikes[4]</td>";
                echo "<td>$organikes[5]</td>";
                echo "<td>$organikes[6]</td>";
                echo "<td>$organikes[7]</td>";
                echo "<td>$organikes[8]</td>";
              echo "</tr>";
              echo "<tr>";
                $orgs = get_orgs($sch,$conn);
                echo "<td>���.���������</td>";
                echo "<td>".$orgs['��21']."</td>";
                echo "<td>".$orgs['��23']."</td>";
                echo "<td>".$orgs['��25']."</td>";
                echo "<td>".$orgs['��26']."</td>";
                echo "<td>".$orgs['��28']."</td>";
                echo "<td>".$orgs['��29']."</td>";
                echo "<td>".$orgs['��30']."</td>";
                echo "<td>".$orgs['��1���']."</td>";
              echo "</tr>";
              echo "<tr>";
                $orgs = get_orgs($sch,$conn);
                echo "<td>���.����</td>";
                echo "<td>".($organikes[1] - $orgs['��21'])."</td>";
                echo "<td>".($organikes[2] - $orgs['��23'])."</td>";
                echo "<td>".($organikes[3] - $orgs['��25'])."</td>";
                echo "<td>".($organikes[4] - $orgs['��26'])."</td>";
                echo "<td>".($organikes[5] - $orgs['��28'])."</td>";
                echo "<td>".($organikes[6] - $orgs['��29'])."</td>";
                echo "<td>".($organikes[7] - $orgs['��30'])."</td>";
                echo "<td>".($organikes[8] - $orgs['��1���'])."</td>";
              echo "</tr>";
              echo "</tbody></table>";
            }
        }
        
        echo "</td></tr>";
        // 05-10-2012 - kena_leit, kena_org
        for ($i=0; $i<count($kena_org); $i++) {
            if (!$kena_org[$i]) {
                $kena_org[$i]=0;
            }
        }
        if ($type == 1) {
            // echo "<tr>";
            // echo "<td>�������� ����</td>";
            // echo "<td>$kena_org[0]</td>";
            // echo "<td>$kena_org[1]</td>";
            // echo "<td>$kena_org[2]</td>";
            // echo "<td>$kena_org[3]</td>";
            // echo "<td>$kena_org[4]</td>";
            // echo "<td>$kena_org[5]</td>";
            // echo "<td>$kena_org[6]</td>";
            // echo "<td>$kena_org[7]</td>";
            // echo "<td>$kena_org[8]</td>";
            // echo "</tr>";
            ///////
            echo "<tr>";
            $orgs = get_orgs($sch,$conn);
            echo "<td>�������� ���������</td>";
            echo "<td>".$orgs['��70']."</td>";
            echo "<td>".$orgs['��11']."</td>";
            echo "<td>".$orgs['��06']."</td>";
            echo "<td>".$orgs['��79']."</td>";
            echo "<td>".$orgs['��05']."</td>";
            echo "<td>".$orgs['��07']."</td>";
            echo "<td>".$orgs['��08']."</td>";
            echo "<td>".$orgs['��86']."</td>";
            echo "<td>".$orgs['��91']."</td>";
            echo $org_ent ? "<td>".$orgs['ent']."</td>" : '';
            if ($type2 == 2) {
              echo "<td>".$orgs['��21']."</td>";
              echo "<td>".$orgs['��23']."</td>";
              echo "<td>".$orgs['��25']."</td>";
              echo "<td>".$orgs['��26']."</td>";
              echo "<td>".$orgs['��28']."</td>";
              echo "<td>".$orgs['��29']."</td>";
              echo "<td>".$orgs['��30']."</td>";
              echo "<td>".$orgs['��1���']."</td>";
            }
            echo "</tr>";
            ///////
            echo "</tr>";
            echo "<tr>";
            $orgs = get_orgs($sch,$conn);
            echo "<td>�������� ����</td>";
            echo "<td>".($organikes[0] - $orgs['��70'])."</td>";
            echo "<td>".($organikes[1] - $orgs['��11'])."</td>";
            echo "<td>".($organikes[2] - $orgs['��06'])."</td>";
            echo "<td>".($organikes[3] - $orgs['��79'])."</td>";
            echo "<td>".($organikes[4] - $orgs['��05'])."</td>";
            echo "<td>".($organikes[5] - $orgs['��07'])."</td>";
            echo "<td>".($organikes[6] - $orgs['��08'])."</td>";
            echo "<td>".($organikes[7] - $orgs['��86'])."</td>";
            echo "<td>".($organikes[8] - $orgs['��91'])."</td>";
            echo $org_ent ? "<td>".($org_ent - $orgs['ent'])."</td>" : '';
            if ($type2 == 2) {
              echo "<td>".($organikes[9] - $orgs['��21'])."</td>";
              echo "<td>".($organikes[10] - $orgs['��23'])."</td>";
              echo "<td>".($organikes[11] - $orgs['��25'])."</td>";
              echo "<td>".($organikes[12] - $orgs['��26'])."</td>";
              echo "<td>".($organikes[13] - $orgs['��28'])."</td>";
              echo "<td>".($organikes[14] - $orgs['��29'])."</td>";
              echo "<td>".($organikes[15] - $orgs['��30'])."</td>";
              echo "<td>".($organikes[16] - $orgs['��1���'])."</td>";
            }
            echo "</tr>";
            echo "</table>";
            echo "</div>";
            // echo "&nbsp;&nbsp;&nbsp;��11: ".($organikes[1] - $orgs['��11']);
            // echo "&nbsp;&nbsp;��06: ".($organikes[2] - $orgs['��06']);
            // echo "&nbsp;&nbsp;��79: ".($organikes[3] - $orgs['��79']);
            // echo "&nbsp;&nbsp;��05: ".($organikes[4] - $orgs['��05']);
            // echo "&nbsp;&nbsp;��07: ".($organikes[5] - $orgs['��07']);
            // echo "&nbsp;&nbsp;��08: ".($organikes[6] - $orgs['��08']);
            // echo "&nbsp;&nbsp;��86: ".($organikes[7] - $orgs['��86']);
            // echo "&nbsp;&nbsp;��91: ".($organikes[8] - $orgs['��91']);
            // echo "</td></tr>";

        }
        else {
            echo "<tr><td colspan=2>���. ����: ��60: $kena_org[0]";
        }
        echo "</td></tr>";

        if ($entaksis[0]) {
            echo "<td><input type=\"checkbox\" checked disabled>����� ������� / �������: $entaksis[1]</td>";
        } else {
            echo "<td><input type=\"checkbox\" disabled>����� �������</td>";
        }
        if ($ypodoxis) {
            echo "<td><input type=\"checkbox\" checked disabled>����� ��������</td>";
        } else {
            echo "<td><input type=\"checkbox\" disabled>����� ��������</td>";
        }
        echo "</tr>";
        if ($entaksis[0] || $ypodoxis) {
            echo "<tr><td>���/��� ��.�������: $ekp_ee_exp[0]</td><td>���/��� ��.��������: $ekp_ee_exp[1]</td></tr>";
        }

        echo "<tr>";
        if ($type == 1) {
            if ($frontistiriako) {
                echo "<td><input type=\"checkbox\" checked disabled>�������������� �����</td>";
            } else {
                echo "<td><input type=\"checkbox\" disabled>�������������� �����</td>";
            }
        }
        // if nip print Proini Zoni (klasiko[6])
        else {
            if ($klasiko_exp[6]) {
                echo "<td><input type=\"checkbox\" checked disabled>������ ���� / �������: $klasiko_exp[6]</td>";
            } else {
                echo "<td><input type=\"checkbox\" disabled>������ ����</td>";
            }
        }
                                            
        if ($oloimero) {
            if ($type == 1) {
                echo "<td><input type=\"checkbox\" checked disabled>��������</td></tr>";
                //echo "<tr><td>������� ���������: $oloimero_stud</td>";
                //echo "<td>���/��� ���������: $oloimero_tea</td></tr>";
            }
            else {
                echo "<td><input type=\"checkbox\" checked disabled>��������</td></tr>";
            }
        }
        else {
            echo "<td><input type=\"checkbox\" disabled>��������</td></tr>";
        }
        
        if ($type == 1) {
            echo "<tr>";
            if ($ted) {
                echo "<td><input type=\"checkbox\" checked disabled>��.�����.����������� (�.�.�.)</td>";
            } else {
                echo "<td><input type=\"checkbox\" disabled>��.�����.����������� (�.�.�.)</td>";
            }
            if ($vivliothiki) {
                echo "<td><input type=\"checkbox\" checked disabled>������� ����������</td>";
            } else {
                echo "<td><input type=\"checkbox\" disabled>������� ����������</td>";
            }
            echo "</tr>";
            echo "<tr><td>���������� �������� ���������: ".$perif."�</td>";
            echo $anenergo ? "<td>���������: �� ��������</td>" : "<td>���������: ������</td>";
            echo "</tr>";
        }
        echo $anenergo && $type == 2 ? "<tr><td>���������: �� ��������</td><td></td>" : "<td>���������: ������</td><td></td></tr>";
        echo "<tr><td>������: $comments</td><td>������� �����: $code</td></tr>";
        // if ($systeg) {
        //     echo "<tr><td colspan=2>������������� ������� ������: <a href='school_status.php?org=$systeg' target='_blank'>$systegName</td></tr>";    
        // }
        if ($updated>0) {
            echo "<tr><td colspan=2 align=right><small>���.���������: ".date("d-m-Y H:i", strtotime($updated))."<small></td></tr>";
        }
        echo "</table>";
        echo "<br>";
        
        
        if ($type == 1) {
            if ($synolo>0) {
                echo "<h2 style='text-align:left;'>�. ������� - ������� / ����������� ���� - �����������</h2>";
                echo "<table class=\"imagetable\" border='1'>";
                echo "<tr><td></td><td>�'</td><td>�'</td><td>�'</td><td>�'</td><td>�'</td><td>��'</td><td class='tdnone'><i>��</i></td><td class='tdnone'><i>��</i></td></tr>";
                $synolo_pr = $classes[0]+$classes[1]+$classes[2]+$classes[3]+$classes[4]+$classes[5];
                echo "<tr><td>���.�������<br>������: $synolo_pr</td><td>$classes[0]</td><td>$classes[1]</td><td>$classes[2]</td><td>$classes[3]</td><td>$classes[4]</td><td>$classes[5]</td><td class='tdnone'><i>$classes[6]</i></td><td class='tdnone'><i>$classes[7]</i></td></tr>";
                $synolo_pr = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
                echo "<tr><td>��./���� �������<br>������: $synolo_pr</td><td>$tmimata_exp[0]</td><td>$tmimata_exp[1]</td><td>$tmimata_exp[2]</td><td>$tmimata_exp[3]</td><td>$tmimata_exp[4]</td><td>$tmimata_exp[5]</td><td class='tdnone'><i>$tmimata_exp[6]<small> (14-15)</small><br>$tmimata_exp[7]<small> (15-16)</small></i></td><td class='tdnone'><i>$tmimata_exp[8]</i></td></tr>";
                if (strlen($archive) > 0){
                  // update school set students='�,�,�,�,�,��,��,��-�',
                  // tmimata='�,�,�,�,�,��,��,��16,��-�' WHERE code='9170117';
                  echo "<tr><td colspan=9><a href='#' id='show_hide2'>��������</a><br>";
                  echo "<div id='slidingDiv2'>";
                  echo "<table>";
                  echo "<thead><tr>";
                  echo "<th>������� ����</th>";
                  echo "<th>�</th>";
                  echo "<th>�</th>";
                  echo "<th>�</th>";
                  echo "<th>�</th>";
                  echo "<th>�</th>";
                  echo "<th>��</th>";
                  echo "<th>��������</th>";
                  echo "<th>������ ����</th>";
                  echo "<th>�������</th>";
                  echo "</tr>";
                  echo "</thead>";
                  echo "<tbody>";
                  $archive_arr = unserialize($archive);
                  foreach ($archive_arr as $key => $value) {
                    echo '<tr>';
                    echo "<td>". substr($key, 0, 4).'-'.substr($key, 4, 2) ."</td>";
                    $data = explode(',',$value);
                    echo "<td>$data[0] ($data[8])</td>";
                    echo "<td>$data[1] ($data[9])</td>";
                    echo "<td>$data[2] ($data[10])</td>";
                    echo "<td>$data[3] ($data[11])</td>";
                    echo "<td>$data[4] ($data[12])</td>";
                    echo "<td>$data[5] ($data[13])</td>";
                    echo "<td>$data[6] (14-15: $data[14], 15-16: $data[15])</td>";
                    echo "<td>$data[7] ($data[16])</td>";
                    echo $data[17] == 'on' ? "<td>��� ($data[18] ���.)</td>" : "<td></td>";
                  }
                  echo "</tbody>";
                  echo "</table>";
                  echo "</div>";
                }
                echo "</table>";
            }
            else 
            {
                echo "��� ���� ����������� ������ �������";
                echo "<br><br>";
            }
        }
        else if ($type == 2) {
            // klasiko_nip/pro: klasiko
            // klasiko pos 0-5: 0,1 t1n,p / 2,3 t2n,p / 4,5 t3n,p
            // prwinh zvnh @ pos 7 -> klasiko[6]
            // oloimero_syn_nip/pro: oloimero
            $klasiko_nip = $klasiko_exp[0] + $klasiko_exp[2] + $klasiko_exp[4];
            $klasiko_pro = $klasiko_exp[1] + $klasiko_exp[3] + $klasiko_exp[5];
            $oloimero_syn_nip = $oloimero_nip_exp[0] + $oloimero_nip_exp[2] + $oloimero_nip_exp[4] + $oloimero_nip_exp[6];
            $oloimero_syn_pro = $oloimero_nip_exp[1] + $oloimero_nip_exp[3] + $oloimero_nip_exp[5] + $oloimero_nip_exp[7];
            $meikto_nip = $klasiko_exp[6] + $klasiko_exp[8];
            $meikto_pro = $klasiko_exp[7] + $klasiko_exp[9];
            // �������
            echo "<h2 style='text-align:left;'>�. ������� - ������� / ����������� ���� - �����������</h2>";
            echo "<h3>�������</h3>";
            echo "<table class=\"imagetable\" border='1'>";
            $ola = $klasiko_nip + $klasiko_pro;
            $olola = $oloimero_syn_nip + $oloimero_syn_pro;
            echo "<tr><td rowspan=2>�����</td><td colspan=3>�������</small></td><td colspan=3>��������</td></tr>";
            echo "<tr><td>�����</td><td>��������</td><td>������</td><td>�����</td><td>��������</td><td>������</td></tr>";
            // t1
            $syn = $klasiko_exp[0]+$klasiko_exp[1];
            $tmimata_nip = 1;
            $tmimata_nip_ol = 0;
            echo "<tr><td>��.1</td><td>$klasiko_exp[0]</td><td>$klasiko_exp[1]</td><td>$syn</td>";
            $syn_ol = $oloimero_nip_exp[0]+$oloimero_nip_exp[1];
            if ($syn_ol > 0) {
                $tmimata_nip_ol += 1;
            }
            echo "<td>$oloimero_nip_exp[0]</td><td>$oloimero_nip_exp[1]</td><td>$syn_ol</td></tr>";
            // print t2 + t3 only if they have students
            // t2
            $syn2 = $klasiko_exp[2]+$klasiko_exp[3];
            $syn_ol2 = $oloimero_nip_exp[2]+$oloimero_nip_exp[3];
            if (($syn2+$syn_ol2) > 0) {
                $tmimata_nip += 1;
                if ($syn_ol2 > 0) {
                    $tmimata_nip_ol += 1;
                }
                echo "<tr><td>��.2</td><td>$klasiko_exp[2]</td><td>$klasiko_exp[3]</td><td>$syn2</td>";
                echo "<td>$oloimero_nip_exp[2]</td><td>$oloimero_nip_exp[3]</td><td>$syn_ol2</td></tr>";
            }
            // t3
            $syn3 = $klasiko_exp[4]+$klasiko_exp[5];
            $syn_ol3 = $oloimero_nip_exp[4]+$oloimero_nip_exp[5];
            if (($syn3+$syn_ol3) > 0) {
                $tmimata_nip += 1;
                if ($syn_ol3 > 0) {
                    $tmimata_nip_ol += 1;
                }
                echo "<tr><td>��.3</td><td>$klasiko_exp[4]</td><td>$klasiko_exp[5]</td><td>$syn3</td>";
                echo "<td>$oloimero_nip_exp[4]</td><td>$oloimero_nip_exp[5]</td><td>$syn_ol3</td></tr>";
            }
            // totals (if more than one tmima)
            if (($syn2 + $syn_ol2 + $syn3 + $syn_ol3) > 0) {
                echo "<tr><td><strong>������</strong></td><td>$klasiko_nip<td>$klasiko_pro</td><td>$ola</td>";
                echo "<td>$oloimero_syn_nip<td>$oloimero_syn_pro</td><td>$olola</td>";
                echo "</tr>";
            }
            if (strlen($archive) > 0){
              // update school set klasiko='1�,1�,2�,2�,3�,3�,��', oloimero_nip='��1�,��1�,��2�,��2�',entaksis='0,0' where code=XXX;              
              echo "<tr><td colspan=9><a href='#' id='show_hide2'>��������</a><br>";
              echo "<div id='slidingDiv2'>";
              echo "<table>";
              echo "<thead><tr>";
              echo "<th>������� ����</th>";
              echo "<th>��.1 ��������</th>";
              echo "<th>��.1 �����</th>";
              echo "<th>��.2 ��������</th>";
              echo "<th>��.2 �����</th>";
              echo "<th>��.3 ��������</th>";
              echo "<th>��.3 �����</th>";
              echo "<th>������ ����</th>";
              echo "<th>�����.1 ��������</th>";
              echo "<th>�����.1 �����</th>";
              echo "<th>�����.2 ��������</th>";
              echo "<th>�����.2 �����</th>";
              echo "<th>�������</th>";
              echo "</tr>";
              echo "</thead>";
              echo "<tbody>";
              $archive_arr = unserialize($archive);
              foreach ($archive_arr as $key => $value) {
                echo '<tr>';
                echo "<td>". substr($key, 0, 4).'-'.substr($key, 4, 2) ."</td>";
                $data = explode(',',$value);
                echo "<td>$data[0]</td>";
                echo "<td>$data[1]</td>";
                echo "<td>$data[2]</td>";
                echo "<td>$data[3]</td>";
                echo "<td>$data[4]</td>";
                echo "<td>$data[5]</td>";
                echo "<td>$data[6]</td>";
                echo "<td>$data[7]</td>";
                echo "<td>$data[8]</td>";
                echo "<td>$data[9]</td>";
                echo "<td>$data[10]</td>";
                echo $data[11] == 'on' ? "<td>��� ($data[12] ���.)</td>" : "<td></td>";
              }
              echo "</tbody>";
              echo "</table>";
              echo "</div>";
            }
            echo "</table>";
            echo "<br>";
            
            $has_entaxi = strlen($entaksis[0])>1 ? 1 : 0; 
            // ������������� ���/���
            $top60 = $top60m = $top60ana = 0;
            $qry = "SELECT count(*) as pe60 FROM employee WHERE sx_yphrethshs = $sch AND klados=1 AND status=1";
            $res = mysqli_query($conn, $qry);
            $top60m = mysqli_result($res, 0, 'pe60');
            $qry = "SELECT count(*) as pe60 FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados=1 AND status=1";
            $res = mysqli_query($conn, $qry);
            $top60ana = mysqli_result($res, 0, 'pe60');
            $top60 = $top60m+$top60ana;
            
            $syn_apait = $tmimata_nip+$tmimata_nip_ol+$has_entaxi;

            echo "<h3>����������� ����</h3>";
            echo "<table class=\"imagetable stable\" border='1'>";
            echo "<thead><th></th><th>�������</th><th>�������</th><th>��������</th><th>��.�������</th></thead><tbody>";

            echo "<tr><td>������������ ����������</td>";
            echo "<td>$syn_apait</td>";
            echo "<td>$tmimata_nip</td><td>$tmimata_nip_ol</td><td>$has_entaxi</td></tr>";

            echo "<tr><td>���������� ����������</td><td>$top60</td><td></td><td></td><td></td></tr>";
            $k_pl = $top60-$syn_apait;
            $k_pl_class = $k_pl >= 0 ? 
                "'background:none;background-color:rgba(0, 255, 0, 0.37)'" : 
                "'background:none;background-color:rgba(255, 0, 0, 0.45)'";
            echo "<tr><td>+ / -</td><td style=$k_pl_class>$k_pl</td><td></td><td></td><td></td></tr>";

            echo "</tbody></table>";
            echo "<br>";
        }
        //echo "<INPUT TYPE='button' VALUE='�����������' onClick=\"parent.location='school_edit.php?org=$sch'\">";
        //echo "&nbsp;&nbsp;&nbsp;<INPUT TYPE='button' VALUE='��������' onClick=\"parent.location='ekdromi.php?sch=$sch&op=list'\">";
        echo "<br>";
        // if dimotiko & leitoyrg >= 4
        if ($type == 1 ) {//&& array_sum($tmimata_exp)>3){
            ektimhseis_wrwn($sch, $conn, $sxol_etos, true);
        }
        // if systegazomeno
        if ($systeg) {
            echo "<a id='toggleSystegBtn' href='#'>�������������: $systegName</a>";
            echo "<div id='systeg' style='display: none;'>";
            ektimhseis_wrwn($systeg, $conn, $sxol_etos, true);
            echo "</div>";
            echo "<br><br>";
        }
    } // of disp_school
    
    $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
    mysqli_query($mysqlconnection, "SET NAMES 'greek'");
    mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
    
    $str1 = getSchool($sch, $mysqlconnection);
    if (!$str1) {
        die('�� ������� �� �������...');
    }
    
    echo "<h2>������� ��������: $str1</h2>";
    if (!$sch && !$str) {
        die('�� ������� �� �������...');
    }
    if (isset($_GET['sxoletos'])) {
        $sxol_etos = $_GET['sxoletos'];
    }
    disp_school($sch, $sxol_etos, $mysqlconnection);
    
    //��������� �� ������
    echo "<h2 style='text-align:left;'>�. ���������</h2>";
    $query = "SELECT * from employee WHERE sx_yphrethshs='$sch' AND status=1 AND thesi in (1,2,6) ORDER BY thesi DESC";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
    if ($num) {
        echo "<h3>��������� �� ������</h3>";
        
        $i=0;
        echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>�������</th>";
        echo "<th>�����</th>";
        echo "<th>������</th>";
        echo "<th>����</th>";
        //echo "<th>������</th>";
        echo "</tr></thead>\n<tbody>";
        while ($i < $num)
        {            
            $id = mysqli_result($result, $i, "id");
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            $thesi = mysqli_result($result, $i, "thesi");
            $th = thesicmb($thesi);
            //$comments = mysqli_result($result, $i, "comments");

            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>$surname</td><td>".$name."</td><td>".$klados."</td><td>$th</td>";//<td>$comments</td>\n";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    }                   
    //������� �������� ��� ��������� (��60-70)
    //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0";
    //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0 ORDER BY klados";
    $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,5) AND (klados=2 OR klados=1)";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
    if ($num) {
        echo "<h3>������� �������� ��� ��������� (��60/��70)</h3>";
        $i=0;
        echo "<table id=\"mytbl2\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>�������</th>";
        echo "<th>�����</th>";
        echo "<th>������</th>";
        //echo "<th>������</th>";
        echo "</tr></thead>\n<tbody>";
        while ($i < $num)
        {            
            $id = mysqli_result($result, $i, "id");
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            //$comments = mysqli_result($result, $i, "comments");
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>$surname</td><td>".$name."</td><td>".$klados."</td>";
            //<td>$comments</td>\n";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    }
    //������� �������� ��� ��������� (�����������)
    //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0";
    //$query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi=0 ORDER BY klados";
    $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,5) AND klados!=2 AND klados!=1";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
    if ($num) {
        echo "<h3>������� �������� ��� ��������� (�����������)</h3>";
        $i=0;
        echo "<table id=\"mytbl2\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>�������</th>";
        echo "<th>�����</th>";
        echo "<th>������</th>";
        //echo "<th>������</th>";
        echo "</tr></thead>\n<tbody>";
        while ($i < $num)
        {            
            $id = mysqli_result($result, $i, "id");
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            //$comments = mysqli_result($result, $i, "comments");

            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>$surname</td><td>".$name."</td><td>".$klados."</td>";//<td>$comments</td>\n";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    }
    
    
    // �������� ����� ��� ���������
    $query = "SELECT * from employee WHERE sx_organikhs!='$sch' AND sx_yphrethshs='$sch' AND thesi in (0,5) AND status=1 ORDER BY klados";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
    if ($num) {
        echo "<h3>�� �������� �� ���� ������� ��� ���������</h3>";
        $i=0;
        echo "<table id=\"mytbl3\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>�������</th>";
        echo "<th>�����</th>";
        echo "<th>������</th>";
        echo "<th>������� ���������</th>";
        //echo "<th>������</th>";
        echo "</tr></thead>\n<tbody>";
        while ($i < $num)
        {
            $id = mysqli_result($result, $i, "id");
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            $sx_organ_id = mysqli_result($result, $i, "sx_organikhs");
            $sx_organikhs = getSchool($sx_organ_id, $mysqlconnection);
            //$comments = mysqli_result($result, $i, "comments");
            
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>$surname</td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td>";
            //<td>$comments</td>\n";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    }
    
    // �������� ����� ��� ������������ ���������
    //$query = "SELECT * from employee WHERE sx_organikhs!='$sch' AND (sx_yphrethshs='$sch' AND thesi=0";
    $query = "SELECT * FROM employee e join yphrethsh y on e.id = y.emp_id where y.yphrethsh=$sch and e.sx_yphrethshs!=$sch AND y.sxol_etos = $sxol_etos";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
    if ($num) {
        echo "<h3>�� �������� ��� ����� ��������� �� ���� �������, ��� ��������� �� �������</h3>";
        $i=0;
        echo "<table id=\"mytbl3\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>�������</th>";
        echo "<th>�����</th>";
        echo "<th>������</th>";
        echo "<th>������� ���������</th>";
        echo "<th>����</th>";
        //echo "<th>������</th>";
        echo "</tr></thead>\n<tbody>";
        while ($i < $num)
        {
            $id = mysqli_result($result, $i, 0);
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            $sx_organ_id = mysqli_result($result, $i, "sx_organikhs");
            $sx_organikhs = getSchool($sx_organ_id, $mysqlconnection);
            //$comments = mysqli_result($result, $i, "comments");
            $hours = mysqli_result($result, $i, "hours");
            
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>$surname</td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td><td>$hours</td>";
            //<td>$comments</td>\n";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    }
    //��������� �� ����� �������
    $query = "SELECT * from employee WHERE sx_yphrethshs='$sch' AND status=1 AND thesi=3";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
    if ($num) {
        echo "<h3>��������� �� ����� �������</h3>";
        $i=0;
        echo "<table id=\"mytbl2\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>�������</th>";
        echo "<th>�����</th>";
        echo "<th>������</th>";
        echo "<th>������� ���������</th>";
        //echo "<th>������</th>";
        echo "</tr></thead>\n<tbody>";
        while ($i < $num)
        {            
            $id = mysqli_result($result, $i, "id");
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            $sx_organ_id = mysqli_result($result, $i, "sx_organikhs");
            $sx_organikhs = getSchool($sx_organ_id, $mysqlconnection);
            //$comments = mysqli_result($result, $i, "comments");

            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>$surname</td><td>".$name."</td><td>".$klados."</td><td>$sx_organikhs</td>";//<td>$comments</td>\n";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    }
    
    //�����������
    //$query = "SELECT * FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos)";
    $query = "SELECT * FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 1)";
    //echo $query;
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
    $sx_yphrethshs = mysqli_result($result, 0, "sx_yphrethshs");
    if ($num) {
        echo "<h3>�����������</h3>";
        $i=0;
        echo "<table id=\"mytbl4\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>�������</th>";
        echo "<th>�����</th>";
        echo "<th>������</th>";
        echo "<th>����� �����������</th>";
        echo "<th>����</th>";
        //echo "<th>������</th>";
        echo "</tr></thead>\n<tbody>";
        while ($i < $num)
        {
            $id = mysqli_result($result, $i, 0);
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            $typos = mysqli_result($result, $i, "type");
            $type = get_type($typos, $mysqlconnection);
            $thesi = mysqli_result($result, $i, "thesi");
            $type .= $thesi == 2 ? '<small> (��.�������)</small>' : '';
            $type .= $thesi == 3 ? '<small> (��������� �������)</small>' : '';

            //$comments = mysqli_result($result, $i, "comments");
            $wres = mysqli_result($result, $i, "hours");
            
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>$surname</td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$wres</td>";//<td>$comments</td>\n";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    }
    
    //�����������: ������� �������� ��� ��������� �����
    $query = "SELECT * from employee WHERE sx_organikhs='$sch' AND sx_yphrethshs!='$sch' order by klados";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
    if ($num) {
        echo "<h2>�����������</h2>";
        echo "<h3>������� �������� ��� ��������� �����</h3>";
        $i=0;
        echo "<table id=\"mytbl5\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>�������</th>";
        echo "<th>�����</th>";
        echo "<th>������</th>";
        echo "<th>�������/������ ����������</th>";
        echo "<th>������</th>";
        echo "</tr></thead>\n<tbody>";
        while ($i < $num)
        {       
            $id = mysqli_result($result, $i, "id");
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            $sx_yphrethshs_id = mysqli_result($result, $i, "sx_yphrethshs");
            $sx_yphrethshs = getSchool($sx_yphrethshs_id, $mysqlconnection);
            $comments = mysqli_result($result, $i, "comments");
            
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>$surname</td><td>".$name."</td><td>".$klados."</td><td>$sx_yphrethshs</td><td>$comments</td>\n";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    }
    
    //�� �����
    $today = date("Y-m-d");
    $query = "SELECT * FROM adeia ad RIGHT JOIN employee emp ON ad.emp_id = emp.id WHERE (sx_organikhs='$sch' OR sx_yphrethshs='$sch') AND ((start<'$today' AND finish>'$today') OR status=3) ORDER BY finish DESC";
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
    if ($num) {
        echo "<h2>�� �����</h2>";
        echo "<h3>�������</h3>";
        $i=0;
        echo "<table id=\"mytbl6\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>�������</th>";
        echo "<th>�����</th>";
        echo "<th>������</th>";
        echo "<th>�����</th>";
        echo "<th>��/��� ����������</th>";
        echo "<th>������</th>";
        echo "</tr></thead>\n<tbody>";
        $apontes = array();
        while ($i < $num)
        {
            $flag = $absent = 0;        
            $id = mysqli_result($result, $i, "emp_id");
            $adeia_id = mysqli_result($result, $i, "id");
            $type = mysqli_result($result, $i, "type");
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            $comments = mysqli_result($result, $i, "comments");
            $comm = $comments;
            $today = date("Y-m-d");
            $return = mysqli_result($result, $i, "finish");
            $start = mysqli_result($result, $i, "start");
            $status = mysqli_result($result, $i, "status");
            // if return date exists, check if absent and print - else continue.
            if ($return) {
                if ($start<$today && $return>$today) {
                    $flag = $absent = 1;
                    $apontes[] = $id;
                }
                else
                {
                        //$flag=1;
                    if (!in_array($id, $apontes)) {
                            $flag = 1;
                    }
                        $apontes[] = $id;
                        $comments = "��� ����������.<br>���� ������� ��������� \"�� �����\"<br>";
                }
                $ret = date("d-m-Y", strtotime($return));
            }
            else
            {
                $ret="";
                $id = mysqli_result($result, $i, "emp.id");
                $flag=1;
                $comments = "��� ����������.<br>���� ������� ��������� \"�� �����\"<br>";
            }
            if ($flag) {
                $query1 = "select type from adeia_type where id=$type";
                $result1 = mysqli_query($mysqlconnection, $query1);
                $typewrd = mysqli_result($result1, 0, "type");
                if ($absent && $status<>3) {
                    $comments = "<blink>�������� ������� ��� ��������� ��� <br>���/��� �� \"�� �����\"</blink><br>$comm";
                }

                echo "<tr>";
                echo "<td>".($i+1)."</td>";
                echo "<td>$surname</td><td>".$name."</td><td>".$klados."</td><td>$typewrd</td><td>$ret</td><td>$comments</td>\n";
                echo "</tr>";
            }
            $i++;
        }
        echo "</tbody></table>";
    }
    //����������� �� �����
    $query = "SELECT * FROM ektaktoi e join yphrethsh_ekt y on e.id = y.emp_id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 3)";
    //echo $query;
    $result = mysqli_query($mysqlconnection, $query);
    $num = mysqli_num_rows($result);
    $sx_yphrethshs = mysqli_result($result, 0, "sx_yphrethshs");
    if ($num) {
        echo "<h3>�����������</h3>";
        $i=0;
        echo "<table id=\"mytbl4\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr>";
        echo "<th>A/A</th>";
        echo "<th>�������</th>";
        echo "<th>�����</th>";
        echo "<th>������</th>";
        echo "<th>����� �����������</th>";
        echo "<th>����</th>";
        echo "<th>������</th>";
        echo "</tr></thead>\n<tbody>";
        while ($i < $num)
        {
            $id = mysqli_result($result, $i, 0);
            $name = mysqli_result($result, $i, "name");
            $surname = mysqli_result($result, $i, "surname");
            $klados_id = mysqli_result($result, $i, "klados");
            $klados = getKlados($klados_id, $mysqlconnection);
            $typos = mysqli_result($result, $i, "type");
            $type = get_type($typos, $mysqlconnection);
            $comments = mysqli_result($result, $i, "comments");
            $wres = mysqli_result($result, $i, "hours");
            
            echo "<tr>";
            echo "<td>".($i+1)."</td>";
            echo "<td>$surname</td><td>".$name."</td><td>".$klados."</td><td>$type</td><td>$wres</td><td>$comments</td>\n";
            echo "</tr>";
            $i++;
        }
        echo "</tbody></table>";
        echo "<br>";
    }
    // requests
    display_school_requests($sch, $sxol_etos, $mysqlconnection);
    
    echo "<h4>������� ���������</h4>";
    echo "<p><i>���: �� ��������� ��� ��� ���������� ������ �����, �� ���������� ������ ��������.</i></p>";
    echo "<form id='requestfrm' action='' method='POST' autocomplete='off'>";
    echo "<table class=\"imagetable stable\" border='1'>";
    echo "<td>������</td><td></td>";
    echo "<td><textarea id='request' name='request' rows='10' cols='80'></textarea></td></tr>";
    echo "</table>";
    echo "<input type='hidden' name = 'school' value='$sch'>";
    echo "<input type='hidden' name = 'type' value='insert'>";
    echo "<br>";
    echo "<input id='submit' type='submit' value='�������'>";
    echo "</form>";

    //logout button
    echo "<form action='' method='POST'>";
    echo "<input type='submit' name='logout' value='������'>";
    echo "</form>";

    ?>
</center>
</body>
</html>
