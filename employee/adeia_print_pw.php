<?php
session_start();

require_once "../config.php";
require_once "../include/functions.php";
require_once '../tools/num2wordgen.php';

require_once '../vendor/phpoffice/phpword/Classes/PHPWord.php';

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

// Get employee data
$emp_id = $_POST['arr'][0];
$query1 = "select * from employee where id=$emp_id";
$result1 = mysqli_query($mysqlconnection, $query1);
$name = mysqli_result($result1, 0, "name");
$surname = mysqli_result($result1, 0, "surname");

$kl1 = mysqli_result($result1, 0, "klados");
$q2 = "select * from klados where id=$kl1";
$res2 = mysqli_query($mysqlconnection, $q2);
$klados = mysqli_result($res2, 0, "perigrafh");

$yphr = mysqli_result($result1, 0, "sx_yphrethshs");
$q3 = "select * from school where id=$yphr";
$res3 = mysqli_query($mysqlconnection, $q3);
$school = mysqli_result($res3, 0, "name");

//$word = new COM("word.application") or die("Unable to instantiate Word");
$PHPWord = new PHPWord();

$type = $_POST['arr'][1];
switch ($type)
{
  case 1:
      $document = $PHPWord->loadTemplate('../word/tmpl_adeia/tmpl_anar.docx');
      break;
  case 2:
      $document = $PHPWord->loadTemplate('../word/tmpl_adeia/tmpl_kan.docx');
      break;
  case 3:
      $document = $PHPWord->loadTemplate('../word/tmpl_adeia/tmpl_gnwm.docx');
      break;
  case 4:
      $document = $PHPWord->loadTemplate('../word/tmpl_adeia/tmpl_eidikh.docx');
      break;
  case 5:
      $document = $PHPWord->loadTemplate('../word/tmpl_adeia/tmpl_loxeias.docx');
      break;
  case 6:
      $document = $PHPWord->loadTemplate('../word/tmpl_adeia/tmpl_kyhshs.docx');
      break;
  case 7:
      $document = $PHPWord->loadTemplate('../word/tmpl_adeia/tmpl_anatr.docx');
      break;
  case 8:
      $document = $PHPWord->loadTemplate('../word/tmpl_adeia/tmpl_gonikh.docx');
      break;
  case 9:
      $document = $PHPWord->loadTemplate('../word/tmpl_adeia/tmpl_kan_kyof.docx');
      break;
  case 10:
      $document = $PHPWord->loadTemplate('../word/tmpl_adeia/tmpl_aney_1m.docx');
      break;
  case 12:
      $document = $PHPWord->loadTemplate('../word/tmpl_adeia/tmpl_aney_1y.docx');
      break;
              // loipes
}

    $data = $klados;
    $document->setValue('klados', $data);
        
    $data = $surname." ".$name;
    $document->setValue('fullname', $data);

    //if ($type!=3)
    //{
    //      $document->setValue('fullname1', $data);
    //}

    $data = $school;
    $document->setValue('school', $data);

    $data = $_POST['arr']['2'];
    $document->setValue('prot', $data);

    $data = $_POST['arr']['3'];
    $document->setValue('hmprot', $data);

    if ($type!=3) {
        $data = $_POST['arr']['4'];
        $document->setValue('date', $data);
    }

    // if aney apodoxwn skip days & daysfull
    if (($type != 10) && ($type != 12)) {
        $data = $days = $_POST['arr']['6'];
        //$document->setValue('days', $data);

        if ($days==1) {
            $data = "μίας (1) ημέρας";
        }
        //        $data = "(μίας) ημέρας";
        else {
            $data = convertNumber($data)." (".$days.") ημερών";
        }
        $document->setValue('daysfull', $data);
    }

    $start = $_POST['arr']['7'];
    $finish = $_POST['arr']['8'];
    if ($days==1) {
        $duration = "στις $start";
    } else {
        $duration = "από $start έως $finish";
    }
    $data = $duration;
    $document->setValue('duration', $data);

    if ($type==1) {
        $vevdil = $_POST['arr']['5'];
        if ($vevdil==1) {
            $data = "Ιατρική Βεβαίωση";
        } else {
            $data = "Υπεύθυνη Δήλωση";
        }
        $document->setValue('vevdil', $data);

    }

    $data = getParam('head_title', $mysqlconnection);
    $document->setValue('head_title', $data);
    $data = getParam('head_name', $mysqlconnection);
    $document->setValue('head_name', $data);

    $output1 = "../word/adeia_".$_SESSION['userid'].".docx";
    $document->save($output1);

    echo "<html>";
    echo "<p><a href=$output1>Ανοιγμα εγγράφου</a></p>";
    ?>
</html>
