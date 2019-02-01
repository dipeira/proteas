<?php

session_start();
require_once "../config.php";
require_once '../vendor/phpoffice/phpword/Classes/PHPWord.php';
require_once '../tools/functions.php';

$PHPWord = new PHPWord();
$anadr = false;
if (isset($_POST['yphr'])) {
  $document = $PHPWord->loadTemplate('../word/tmpl/tmpl_vev.docx');
} else {
  $document = $PHPWord->loadTemplate('../word/tmpl/tmpl_anadr.docx');
  $anadr = true;
}

$data_array = $_POST;
//$current_date = date("d/m/Y");
$document->setValue('date', date("d/m/Y"));
$document->setValue("date2", date("d/m/Y"));

$document->setValue('surname', $data_array['surname']);

$document->setValue('name', $data_array['name']);

$document->setValue('father', $data_array['patrwnymo']);

$document->setValue('klados', $data_array['klados']);

$am = $data_array['am'];
$document->setValue('am', $data_array['am']);

$document->setValue('vath', $data_array['vathm']);

$document->setValue('mk', $data_array['mk']);

$document->setValue('organ', $data_array['sx_organikhs']);

$document->setValue('dior', $data_array['fek_dior']);

$data = date("d-m-Y", strtotime($data_array['hm_dior_org']));
$document->setValue('hmdior', $data);

$data = date("d/m/Y", strtotime($data_array['hm_anal']));
$document->setValue('hmanal', $data);

$document->setValue('yphr', $data_array['ymd']);

// head title & name
$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($mysqlconnection, "SET NAMES 'greek'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");
$data = mb_convert_encoding(getParam('head_title', $mysqlconnection), "utf-8", "iso-8859-7");
$document->setValue('headtitle', $data);
$data = mb_convert_encoding(getParam('head_name', $mysqlconnection), "utf-8", "iso-8859-7");
$document->setValue('headname', $data);

$output1 = $anadr ? 
  "../word/vev_anadr_$am.docx" :
  "../word/vev_yphr_$am.docx";
  
$document->save($output1);

header('Content-type: text/html; charset=iso8859-7'); 
echo "<html>";
echo "<p><a href=$output1>Ανοιγμα εγγράφου</a></p>";
echo "</html>";
?>
