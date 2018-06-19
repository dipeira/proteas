<?php
session_start();

require_once"../config.php";
require_once '../tools/num2wordgen.php';
require_once '../tools/PHPWord.php';

$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
mysql_select_db($db_name, $mysqlconnection);
mysql_query("SET NAMES 'greek'", $mysqlconnection);
mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);

// Get employee data
$emp_id = $_POST['arr'][0];
$query1 = "select * from employee where id=$emp_id";
$result1 = mysql_query($query1, $mysqlconnection);
$name = mysql_result($result1, 0, "name");
$surname = mysql_result($result1, 0, "surname");

$kl1 = mysql_result($result1, 0, "klados");
$q2 = "select * from klados where id=$kl1";
$res2 = mysql_query($q2, $mysqlconnection);
$klados = mysql_result($res2, 0, "perigrafh");

$yphr = mysql_result($result1, 0, "sx_yphrethshs");
$q3 = "select * from school where id=$yphr";
$res3 = mysql_query($q3, $mysqlconnection);
$school = mysql_result($res3, 0, "name");

//$word = new COM("word.application") or die("Unable to instantiate Word");
$PHPWord = new PHPWord();

$type = $_POST['arr'][1];
    switch ($type)
        {
            case 1:
                //$word->Documents->Open(realpath("word/anar.doc"));
                $document = $PHPWord->loadTemplate('../word/docx/tmpl_anar.docx');
                break;
            case 2:
                $document = $PHPWord->loadTemplate('../word/docx/tmpl_kan.docx');
                break;
            case 3:
                $document = $PHPWord->loadTemplate('../word/docx/tmpl_gnwm.docx');
                break;
            case 4:
                $document = $PHPWord->loadTemplate('../word/docx/tmpl_eidikh.docx');
                break;
            case 5:
                $document = $PHPWord->loadTemplate('../word/docx/tmpl_loxeias.docx');
                break;
            case 6:
                $document = $PHPWord->loadTemplate('../word/docx/tmpl_kyhshs.docx');
                break;
            case 7:
                $document = $PHPWord->loadTemplate('../word/docx/tmpl_anatr.docx');
                break;
            case 8:
                $document = $PHPWord->loadTemplate('../word/docx/tmpl_gonikh.docx');
                break;
            case 9:
                $document = $PHPWord->loadTemplate('../word/docx/tmpl_kan_kyof.docx');
                break;
            case 10:
                $document = $PHPWord->loadTemplate('../word/docx/tmpl_aney_1m.docx');
                break;
            case 12:
                $document = $PHPWord->loadTemplate('../word/docx/tmpl_aney_1y.docx');
                break;
            // loipes
        }

$data = $klados;
$data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
$document->setValue('klados', $data);
        
$data = $surname." ".$name;
$data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
$document->setValue('fullname', $data);

//if ($type!=3)
//{
//      $document->setValue('fullname1', $data);
//}

$data = $school;
$data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
$document->setValue('school', $data);

        
$data = $_POST['arr']['2'];
$document->setValue('prot', $data);

$data = $_POST['arr']['3'];
$document->setValue('hmprot', $data);

if ($type!=3)
{
    $data = $_POST['arr']['4'];
    $document->setValue('date', $data);
}

// if aney apodoxwn skip days & daysfull
if (($type != 10) && ($type != 12))
{
    $data = $days = $_POST['arr']['6'];
    //$document->setValue('days', $data);

    if ($days==1)
          $data = "���� (1) ������";
//        $data = "(����) ������";
    else
        $data = convertNumber($data)." (".$days.") ������";
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    $document->setValue('daysfull', $data);
}

$start = $_POST['arr']['7'];
$finish = $_POST['arr']['8'];
if ($days==1)
    $duration = "���� $start";
else
    $duration = "��� $start ��� $finish";
$data = $duration;
$data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
$document->setValue('duration', $data);

if ($type==1)
{
    $vevdil = $_POST['arr']['5'];
    if ($vevdil==1)
        $data = "������� ��������";
    else
        $data = "�������� ������";
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    $document->setValue('vevdil', $data);

}

$output1 = "../word/adeia_".$_SESSION['userid'].".docx";
$document->save($output1);

header('Content-type: text/html; charset=iso8859-7'); 

echo "<html>";
echo "<p><a href=$output1>������� ��������</a></p>";
?>
</html>