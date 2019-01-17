<?php

session_start();
require_once "../config.php";
require_once '../vendor/phpoffice/phpword/Classes/PHPWord.php';
require_once '../tools/functions.php';

$PHPWord = new PHPWord();

$document = $PHPWord->loadTemplate('../word/tmpl/tmpl_vev.docx');

//$current_date = date("d/m/Y");
//$document->setValue('date', $current_date);
$document->setValue('date', date("d/m/Y"));
$document->setValue("date2", date("d/m/Y"));

//$data = mb_convert_encoding($_POST['arr'][0], "iso-8859-7", "utf-8");
$data = $_POST['arr'][0];
$document->setValue('surname', $data);

//$data = mb_convert_encoding($_POST['arr'][1], "iso-8859-7", "utf-8");
$data = $_POST['arr']['1'];
$document->setValue('name', $data);

//$data = mb_convert_encoding($_POST['arr'][2], "iso-8859-7", "utf-8");
$data = $_POST['arr']['2'];
$document->setValue('father', $data);

//$data = mb_convert_encoding($_POST['arr'][3], "iso-8859-7", "utf-8");
$data = $_POST['arr']['3'];
$document->setValue('klados', $data);

$data = $_POST['arr']['4'];
$document->setValue('am', $data);

//$data = mb_convert_encoding($_POST['arr'][5], "iso-8859-7", "utf-8");
$data = $_POST['arr']['5'];
$document->setValue('vath', $data);

$data = $_POST['arr']['6'];
$document->setValue('mk', $data);

//$data = mb_convert_encoding($_POST['arr'][7], "iso-8859-7", "utf-8");
$data = $_POST['arr']['7'];
$document->setValue('organ', $data);

//$data = mb_convert_encoding($_POST['arr'][8], "iso-8859-7", "utf-8");
$data = $_POST['arr']['8'];
$document->setValue('dior', $data);

$data = date("d-m-Y", strtotime($_POST['arr']['9']));
$document->setValue('hmdior', $data);

$data = date("d/m/Y", strtotime($_POST['arr']['10']));
$document->setValue('hmanal', $data);

//$data = mb_convert_encoding($_POST['arr'][11], "iso-8859-7", "utf-8");
$data = $_POST['arr']['11'];
$document->setValue('yphr', $data);

// head title & name
$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
$data = mb_convert_encoding(getParam('head_title', $mysqlconnection), "utf-8", "iso-8859-7");
$document->setValue('headtitle', $data);
$data = mb_convert_encoding(getParam('head_name', $mysqlconnection), "utf-8", "iso-8859-7");
$document->setValue('headname', $data);

$output1 = "../word/new_vev_".$_SESSION['userid'].".docx";
$document->save($output1);

header('Content-type: text/html; charset=iso8859-7'); 
echo "<html>";
echo "<p><a href=$output1>Ανοιγμα εγγράφου</a></p>";
echo "</html>";
?>
