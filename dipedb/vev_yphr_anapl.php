<html>
    <head><title>Βεβαιώσεις υπηρεσίας αναπληρωτών</title></head>
    <body>
<?php

session_start();

require_once 'tools/PHPWord.php';
require_once 'config.php';
require_once 'functions.php';

$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
mysql_select_db($db_name, $mysqlconnection);
mysql_query("SET NAMES 'greek'", $mysqlconnection);
mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);

set_time_limit (180);

$arr = unserialize(html_entity_decode($_POST['emp_arr']));

$i = 1;

foreach( $arr as $myarr)
{
    $PHPWord = new PHPWord();
    // kratikoy
    if ($_POST['kratikoy'])
        $document = $PHPWord->loadTemplate('word/tmpl_vev_anapl.docx');
    // espa
    else
    {
        if (strpos($myarr['prefix'],'PARAL_') !== false || strpos($myarr['prefix'],'EKSATOM_') !== false || strpos($myarr['prefix'],'ANAPT_') !== false || strpos($myarr['prefix'],'NEO_') !== false)
                $document = $PHPWord->loadTemplate('word/tmpl_vev_anapl_eks_ekseid_etc.docx');
        elseif (strpos($myarr['prefix'],'EAEP_') !== false || strpos($myarr['prefix'],'OLOHM_') !== false )
                $document = $PHPWord->loadTemplate('word/tmpl_vev_anapl_eaep_oloim.docx');
        elseif (strpos($myarr['prefix'],'PEP_') !== false)
                $document = $PHPWord->loadTemplate('word/tmpl_vev_anapl_pep.docx');
        /*
        if ($myarr['ebp'])
            $document = $PHPWord->loadTemplate('word/tmpl_vev_anapl_ebp.docx');
        else
            $document = $PHPWord->loadTemplate('word/tmpl_vev_anapl_espa.docx');
        */
    }
        
    $data = $endofyear;
    $document->setValue('endofyear', $data);
	$data = $endofyear2;
    $document->setValue('endofyear2', $data);
    
    $data = $protapol; 
    $document->setValue('protapol', $data);
    
    $data = $myarr['surname']." ".$myarr['name'];
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    //$fullname = $data;
    $document->setValue('fullname', $data);
    
    $data = $myarr['patrwnymo'];
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7"); 
    $document->setValue('patrwnymo', $data);
    
    $klados = $myarr['klados'];
    $qry1 = "select perigrafh,onoma from klados where id=$klados";
    $res1 = mysql_query($qry1, $mysqlconnection);
    $kl1 = mysql_result($res1, 0, "perigrafh");
    $kl2 = mysql_result($res1, 0, "onoma");
    $data = $kl2." (".$kl1.")";
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    $document->setValue('kladosfull', $data);
    
    $data = $myarr['ya'];
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    $document->setValue('ya', $data);
    
    $didetos = substr($sxol_etos,0,4).'-'.substr($sxol_etos,4,2);
    $document->setValue('didetos', $didetos);
    
    $data = $myarr['apof'];
    $apof = $data;
    
    //sxoleio/-a
    $sxoleia = '';
    foreach ($myarr['sx_yphrethshs'] as $sx_arr){
        foreach($sx_arr as $k => $v) {
            if ($k === 'sch' && $v>0)
            {
                $sxoleia .= getSchool($v, $mysqlconnection);
            }
            else {
                $sxoleia .= '';
            }
            if ($k === 'hours' && $v>0)
                $sxoleia .= " ($v ώρες), ";
            else
                $sxoleia .= '';
        }
    }
    $sxoleia = substr($sxoleia, 0, -2);

    // metakinhsh
    $metakinhsh = $myarr['metakinhsh'];
    if ($myarr['ebp'])
    {
        if (strlen($metakinhsh)<2)
            $top_metak = "και τοποθετήθηκε με την ταυτάριθμη απόφαση στο (-α) $sxoleia.";
        else
            $top_metak = ". Τοποθετήθηκε με την ταυτάριθμη απόφαση στο ".$metakinhsh . $sxoleia;
    }
    else
    {
        if (strlen($metakinhsh)<2)
            $top_metak = "και τοποθετήθηκε με την αριθμ. $apof Απόφαση του Δ/ντή Π.Ε. Ηρακλείου στο (-α) $sxoleia.";
        else
            $top_metak = ". Με την αριθμ. $apof τοποθετήθηκε στο ".$metakinhsh . $sxoleia;
    }
    $data = mb_convert_encoding($top_metak, "utf-8", "iso-8859-7");
    $document->setValue('top_metak', $data);
    
    $data = $hmpros = $myarr['hmpros'];
    $data = date("d-m-Y", strtotime($data));
    $document->setValue('hmpros', $data);
    
    // ypologismos yphresias
    $apol = substr($endofyear2,0,2) + substr($endofyear2,3,2)*30 + substr($endofyear2,6,4)*360;
    $pros = substr($hmpros,0,4)*360 + substr($hmpros,5,2)*30 + substr($hmpros,8,2);
    // +1 για να περιληφθεί και η τελευταία μέρα
    $days = $apol - $pros + 1;
    $ymd = days2ymd($days);
    $data = $ymd[1]." μήνες, ".$ymd[2]." ημέρες";
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    $document->setValue('yphr', $data);
    
    // write to file
    $fname = greek_to_greeklish($myarr['surname']);
    $fname = $myarr['prefix'].$fname;
    $last_afm = $myarr['last_afm'];
    $output1 = "word/anapl/".$fname.".docx";
    // if same surname, use last digits of afm
    if (file_exists($output1))
        $output1 = "word/anapl/".$fname."_".$last_afm.".docx";
    $document->save($output1);
    $filenames[] = $output1;
    $i++;
} 

// create zip file
if ($_POST['kratikoy'])
    $zipname = 'word/anapl/file.zip';
else
    $zipname = 'word/anapl/file_espa.zip';
if (file_exists($zipname))
    unlink($zipname);
$zip = new ZipArchive;
$zip->open($zipname, ZipArchive::CREATE);
foreach ($filenames as $file) {
    $zip->addFile($file);
}
$zip->close();
// end of zip

header('Content-type: text/html; charset=iso8859-7'); 
echo "<html>";
echo "<p>";
// Delete docx files after creating zip file
foreach ($filenames as $file)
    unlink($file);
$vev = $i-1;
echo "<h3>Επιτυχής εξαγωγή βεβαιώσεων αναπληρωτών ";
if ($_POST['kratikoy'])
    echo "κρατικού προϋπολογισμού";
else
    echo "ΕΣΠΑ";
echo "</h3>";
echo "<br>Εκπ/κοί που βρέθηκαν: ".$_POST['plithos'];
echo "<br>Βεβαιώσεις που εξήχθησαν: ".$vev;
echo "<br><br><a href=$zipname>Ανοιγμα zip εγγράφου</a>";
echo "<br><br><a href=\"index.php\">Επιστροφή</a>";
echo "</p>";

?>
    </body>
</html>