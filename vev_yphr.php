<?php

session_start();
require_once 'config.php';

//1. Instanciate Word
$word = new COM("word.application") or die("Unable to instantiate Word");
//2. specify the MS Word template document (with Bookmark TODAYDATE inside)
//$template_file = "c:/template.doc";
//3. open the template document
//$word->Documents->Open($template_file);
$word->Documents->Open(realpath("word/vev.doc"));

$current_date = date("d/m/Y");
$bookmarkname = "DATE";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $current_date;

$bookmarkname = "DATE1";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $current_date;

//$data = $_POST['arr']['0'];
$data = mb_convert_encoding($_POST['arr'][0], "iso-8859-7", "utf-8");
//echo $data;
$bookmarkname = "SURNAME";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

//$data = $_POST['arr']['1'];
$data = mb_convert_encoding($_POST['arr'][1], "iso-8859-7", "utf-8");
//echo " ".$data;
$bookmarkname = "NAME";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

//$data = $_POST['arr']['2'];
$data = mb_convert_encoding($_POST['arr'][2], "iso-8859-7", "utf-8");
$bookmarkname = "PATRWNYMO";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

//$data = $_POST['arr']['3'];
$data = mb_convert_encoding($_POST['arr'][3], "iso-8859-7", "utf-8");
$bookmarkname = "KLADOS";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

$data = $_POST['arr']['4'];
$bookmarkname = "AM";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

//$data = $_POST['arr']['5'];
$data = mb_convert_encoding($_POST['arr'][5], "iso-8859-7", "utf-8");
$bookmarkname = "VATHMOS";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

$data = $_POST['arr']['6'];
$bookmarkname = "MK";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

//$data = $_POST['arr']['7'];
$data = mb_convert_encoding($_POST['arr'][7], "iso-8859-7", "utf-8");
$bookmarkname = "ORGAN";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

//$data = $_POST['arr']['8'];
$data = mb_convert_encoding($_POST['arr'][8], "iso-8859-7", "utf-8");
$bookmarkname = "DIOR";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

$data = date("d-m-Y", strtotime($_POST['arr']['9']));
$bookmarkname = "HM_DIOR";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

$data = date("d/m/Y", strtotime($_POST['arr']['10']));
$bookmarkname = "HM_ANAL";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

//$data = $_POST['arr']['11'];
$data = mb_convert_encoding($_POST['arr'][11], "iso-8859-7", "utf-8");
$bookmarkname = "YPHR";
$objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
$range = $objBookmark->Range;
$range->Text = $data;

//7. save the template as a new document (c:/reminder_new.doc)
//$new_file = "c:/new.doc";
//$word->Documents[1]->SaveAs($new_file);
//$word->Documents[1]->SaveAs(realpath("word/new_vev.doc"));
$output1 = "word/new_vev_".$_SESSION['userid'].".doc";
$word->Documents[1]->SaveAs(realpath($output1));
//8. free the object
$word->Quit();
//$word->Release();
$word = null;
header('Content-type: text/html; charset=iso8859-7'); 
echo "<html>";
echo "<p><a href=$output1>Ανοιγμα εγγράφου</a></p>";
echo "</html>";
?>



