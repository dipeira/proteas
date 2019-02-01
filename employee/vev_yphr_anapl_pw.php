<?php

session_start();
require_once "../config.php";
require_once '../vendor/phpoffice/phpword/Classes/PHPWord.php';
require_once '../tools/functions.php';

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  

$PHPWord = new PHPWord();
$anadr = false;

$document = $PHPWord->loadTemplate('../word/tmpl/tmpl_vev_anapl.docx');

$data_array = $_POST;

$document->setValue('date', date("d/m/Y"));

$document->setValue('surname', $data_array['surname']);

$document->setValue('name', $data_array['name']);

$document->setValue('patrwnymo', $data_array['patrwnymo']);

$document->setValue('apofasi', $data_array['apofasi']);

$document->setValue('ada', $data_array['ada']);

$document->setValue('ya', $data_array['ya']);

$document->setValue('klados', $data_array['klados']);

$data = date("d/m/Y", strtotime($data_array['date_anal']));
$document->setValue('date_anal', $data);

$sxol_etos = substr($data_array['sxoletos'], 0, 4).'-'.substr($data_array['sxoletos'], 4, 2);
$document->setValue('sxoletos', $sxol_etos);

$document->setValue('endofyear', substr($data_array['sxoletos'], 0, 2).substr($data_array['sxoletos'], 4, 2));

$document->setValue('schools', $data_array['schools']);

// compute yphresia
// convert date_anal
$dan = $data_array['date_anal'];
$date_anal = substr($dan, 6, 4) . '-'. substr($dan, 3, 2) .'-'. substr($dan, 0, 2);
//yphresia_anaplhrwth($hmapox, $hmpros, $meiwmeno = false, $subtracted = 0, $yp_wrario = 24, $hour_sum = 0) {
$yphr = yphresia_anaplhrwth($data_array['sel_date'], $date_anal, $data_array['meiwmeno'],0,24,$data_array['hoursum']);
$yphr = mb_convert_encoding($yphr, "utf-8", "iso-8859-7");
$document->setValue('yphr', $yphr);

// head title & name
mysqli_query($mysqlconnection, "SET NAMES 'greek'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
$data = mb_convert_encoding(getParam('head_title', $mysqlconnection), "utf-8", "iso-8859-7");
$document->setValue('headtitle', $data);
$data = mb_convert_encoding(getParam('head_name', $mysqlconnection), "utf-8", "iso-8859-7");
$document->setValue('headname', $data);

$rnd = rand(0,100);
$output1 = "../word/vev_yphr_$rnd.docx";
  
$document->save($output1);

header('Content-type: text/html; charset=iso8859-7'); 
echo "<html>";
echo "<p><a href=$output1>Ανοιγμα εγγράφου</a></p>";
echo "</html>";
?>
