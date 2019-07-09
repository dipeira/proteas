<?php
  header('Content-type: text/html; charset=iso8859-7'); 
?>
<html>
  <head>
	  <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>�������� ��������� ��� ������</title>
    <script type="text/javascript" src="../js/jquery.js"></script>
  </head>
  <body>

<?php
  session_start();

  require_once "../config.php";
  require_once 'functions.php';
  
  include_once("class.login.php");
  $log = new logmein();
  // if not logged in or not admin
  //if($log->logincheck($_SESSION['loggedin']) == false || $_SESSION['user'] != $av_admin)
  if($_SESSION['loggedin'] == false)
  {   
      header("Location: login.php");
  }
  else
      $loggedin = 1;

  // check if admin
  if ($_SESSION['userlevel'] > 0)
  {
    echo "<br><br><h3>��� ����� �������� ��� ��� �������������� ����� ��� ���������. ������������� �� �� ����������� ���.</h3>";
    die();
  }

  if (!isset($_POST['submit']))
  {
    echo "<IMG src='../images/logo.png' class='applogo'></a>";
    echo "<h2> �������� ��������� ��� ���� ��������� </h2>";
    echo "<form enctype='multipart/form-data' action='import.php' method='post'>";
    echo "<b>���� 1.</b> ������� ������� ���� ����������:<br>";
    echo "<ul><li><a href='employees.csv'>�������</a></li>";
    echo "<li><a href='schools.csv'>�������</a></li>";
    echo "<li><a href='students_ds.csv'>������� / ������� �.�.</a></li>";
    echo "<li><a href='students_nip.csv'>������� / ������� ���.</a></li></ul>";
    echo "<b>���� 2.</b> ������� ����� ���������:<br>";
    echo "<input type='radio' name='type' value='1'>�) �������<br>";
    echo "<input type='radio' name='type' value='2'>�) �������<br>";
    echo "<input type='radio' name='type' value='3'>�) ������� / ������� �.�.<br>";
    echo "<input type='radio' name='type' value='4'>�) ������� / ������� ���.<br>";
    echo "<br><b>�������: </b> �� �, � �� ���������� ���� ������� �� ��. ����.<br />\n";
    echo "<br><b>���� 3.</b> ������� ������������� ������� ���� ��������:<br />\n";
    echo "<input size='50' type='file' name='filename'><br />\n";
    print "<input type='submit' name='submit' value='�����������'></form>";
    echo "<small>���.: � �������� ��������� �� ��������� ������ �����, ������ ��� ������ ������.<br>�� ������ ��� �� ������ �� ��� ������ ������ ������.</small>";
    echo "</form>";
    echo "<br><a href='ektaktoi_import.php'>�������� �����������</a>";
    echo "<br><br>";
    echo "<a href='../index.php'>���������</a>";
    exit;
  }
		
  $mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
  mysqli_query($mysqlconnection, "SET NAMES 'greek'");
  mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
  
  if (!isset($_POST['type'])){
    echo "<h3>������: ��� ��������� ���� ���������.</h3>";
    echo "<br><a href='import.php'>���������</a>";
    die();
  }
  //Upload File
  if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
      echo "<h3>" . "To ������ ". $_FILES['filename']['name'] ." ������� �� ��������." . "</h3>";

      //Import uploaded file to Database
      $handle = fopen($_FILES['filename']['tmp_name'], "r");
      switch ($_POST['type'])
      {
          case 1:
            $tbl = 'employee';
            break;
          case 2:
            $tbl = 'school';
            break;
          case 3:
          case 4:
            $tbl = 'school';
            break;
      }
      $num = 0;
      $checked = 0;
      $headers = 1;
      $error = false;
      $er_msg = '';
      
      // set max execution time (for large files)
      set_time_limit (480);

      while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        // skip header line
        if ($headers){
            $headers = 0;
            continue;
        }
        // check if csv & table columns are equal
        if (!$checked)
        {
          $csvcols = count($data);
          if ($_POST['type'] == 1){
            $tblcols = 25;
          }
          else if ($_POST['type'] == 2){
            $tblcols = 12;
          }
          else if ($_POST['type'] == 3){
            $tblcols = 18;
          }
          else if ($_POST['type'] == 4){
            $tblcols = 13;
          }

          if ($csvcols <> $tblcols)
          {
            echo "<h3>������: ����� ������ (������ �������: $csvcols <> ������ ������: $tblcols)</h3>";
            echo "<a href='import.php'>���������</a>";
            die();
          }
          else
            $checked = 1;
        }  

        switch ($_POST['type']){
          // employees
          case 1:
            // check school codes
            $mysqlconn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
            $sx_organ = getSchoolFromCode($data[23],$mysqlconn);
            $sx_yphr = getSchoolFromCode($data[24],$mysqlconn);
            if (!$sx_organ || !$sx_yphr){
              $error = true;
              $er_msg = '������: �� ������� � 7������ ������� ��������: ';
              $er_msg .= !$sx_organ ? $data[24] : $data[23];
              $er_msg .= " (������ ".($num+1).")";
              break;
            }
            // check if am exists
            $qry = "SELECT * FROM employee WHERE am = $data[5]";
            if (mysqli_num_rows(mysqli_query($mysqlconnection, $qry)) ){
              $error = true;
              $er_msg ="������: � ��������� �� �� ".$data[5]." ������� ���...";
              $er_msg .= " (������ ".($num+1).")";
              break;
            }
            // fix dates
            $data[7] = date ("Y-m-d", strtotime($data[7]));
            $data[9] = date ("Y-m-d", strtotime($data[9]));
            $data[11] = date ("Y-m-d", strtotime($data[11]));
            $data[12] = date ("Y-m-d", strtotime($data[12]));
            // proceed to import
            $import="INSERT into employee(name,surname,patrwnymo,mhtrwnymo,klados,am,thesi,fek_dior,hm_dior,
            vathm, hm_vathm, mk, hm_mk, hm_anal, met_did, proyp, proyp_not, status,
            afm, tel, address, idnum, amka, wres, sx_organikhs, sx_yphrethshs)
            values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]',0,'$data[6]','$data[7]',
            '$data[8]','$data[9]','$data[10]','$data[11]','$data[12]','$data[13]','$data[14]','$data[15]','$data[16]',
            '$data[17]','$data[18]','$data[19]','$data[20]','$data[21]','$data[22]', $sx_organ, $sx_yphr)";
            $ret = mysqli_query($mysqlconnection, $import);
            if (!$ret) {
              $error = true;
            } else {
              // insert yphrethsh as well
              $id = mysqli_insert_id($mysqlconnection);
              $query = "insert into yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) 
              values ($id, '$sx_yphr', '$data[22]', '$sx_organ', '$sxol_etos')";
              mysqli_query($mysqlconnection,$query);
            }
            break;
          // schools
          case 2:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            if (mysqli_num_rows(mysqli_query($mysqlconnection, $qry)) ){
              $error = true;
              $er_msg ="������: �� ������� �� ������ ".$data[0]." ������� ���...";
              $er_msg .= " (������ ".($num+1).")";
              break;
            }
            $import="INSERT into school(code,category,type,name,address,tk,tel,fax,email,organikothta,leitoyrg,type2) 
            values('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]','$data[10]','$data[11]')";
            $ret = mysqli_query($mysqlconnection, $import);
            if (!$ret) {
              $error = true;
            }
            break;
          // students ds
          case 3:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            $res = mysqli_query($mysqlconnection, $qry);
            if (!mysqli_num_rows($res) ){
              $error = true;
              $er_msg ="������: �� ������� �� ������ ".$data[0]." ��� �������...";
              $er_msg .= " (������ ".($num+1).")";
              break;
            }
            
            // update school set students='�,�,�,�,�,��,��,��-�',tmimata='�,�,�,�,�,��,��,��16,��-�' WHERE code='9170117';
            $students = implode(',',Array($data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7],$data[8]));
            $tm_prz = ceil($data[8]/25);
            $tmimata = implode(',',Array($data[10],$data[11],$data[12],$data[13],$data[14],$data[15],$data[16],$data[17],$tm_prz));
            $entaksis = $data[9] > 0 ? 'on,'.$data[9] : ',';
            // archive 
            $archive = mysqli_result($res, 0, "archive");
            if (strlen($archive) > 0) {
              $archive_arr = unserialize($archive);
            } else $archive_arr = Array();
            $students_old = mysqli_result($res, 0, "students");
            $tmimata_old = mysqli_result($res, 0, "tmimata");
            $entaksis_old = mysqli_result($res, 0, "entaksis");
            $archive_data = $students_old . ',' . $tmimata_old . ',' . $entaksis_old;
            $sxoletos = find_prev_year($sxol_etos);
            $archive_arr[$sxoletos] = $archive_data;
            $sql="UPDATE school SET archive = '". serialize($archive_arr) . "' WHERE code=".$data[0];
            $ret = mysqli_query($mysqlconnection, $sql);
            // update school table
            $sql="UPDATE school SET students='$students', tmimata='$tmimata', entaksis='$entaksis' WHERE code=".$data[0];
            $ret = mysqli_query($mysqlconnection, $sql);
            if (!$ret) {
              $error = true;
            }
            break;
          // students nip
          case 4:
            // check if school code exists
            $qry = "SELECT * FROM school WHERE code = $data[0]";
            $res = mysqli_query($mysqlconnection, $qry);
            if (!mysqli_num_rows($res) ){
              $error = true;
              $er_msg ="������: �� ������� �� ������ ".$data[0]." ��� �������...";
              $er_msg .= " (������ ".($num+1).")";
              break;
            }

            // update school set klasiko='1�,1�,2�,2�,3�,3�,��', oloimero_nip='��1�,��1�,��2�,��2�',entaksis='0,0' where code=9170040;
            $klasiko = implode(',',Array($data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7]));
            $oloimero_nip = implode(',',Array($data[8],$data[9],$data[10],$data[11]));
            $entaksis = $data[12] > 0 ? 'on,'.$data[12] : '0,0';
            // archive 
            $archive = mysqli_result($res, 0, "archive");
            if (strlen($archive) > 0) {
              $archive_arr = unserialize($archive);
            } else $archive_arr = Array();
            $klasiko_old = mysqli_result($res, 0, "klasiko");
            $oloimero_nip_old = mysqli_result($res, 0, "oloimero_nip");
            $entaksis_old = mysqli_result($res, 0, "entaksis");
            $archive_data = $klasiko_old . ',' . $oloimero_nip_old . ',' . $entaksis_old;
            $sxoletos = find_prev_year($sxol_etos);
            $archive_arr[$sxoletos] = $archive_data;
            $sql="UPDATE school SET archive = '". serialize($archive_arr) . "' WHERE code=".$data[0];
            $ret = mysqli_query($mysqlconnection, $sql);
            // update school table
            $sql="UPDATE school SET klasiko='$klasiko', oloimero_nip='$oloimero_nip', entaksis='$entaksis' WHERE code=".$data[0];
            $ret = mysqli_query($mysqlconnection, $sql);
            if (!$ret) {
              $error = true;
            }
            break;
        }
        if ($error){
          break;
        }
        
        $num++;
      }

      fclose($handle);
      if (!$error){
          print "<h3>� �������� ���������������� �� ��������!</h3>";
          echo "����� �������� $num �������� ���� ������ $tbl.<br>";
      }
      else
      {
          echo "<h3>������������� ������ ���� ��� ��������</h3>";
          
          echo mysqli_error($mysqlconnection) ? "������ ������:".mysqli_error($mysqlconnection) : '';
          echo $er_msg ? "<h3>$er_msg</h3>" : '';
          echo "<h4>������� �� ������ � ������������� �� �� �����������.</h4>";
      }
    }
    else {
        echo "<h3>������: ��� ��������� ������</h3><br><br>";
    }
                
    echo "<a href='import.php'>���������</a>";
?>

</body>
</html>
	