<html>
    <head>
        <title>Βεβαιώσεις υπηρεσίας αναπληρωτών</title>
        <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
<?php

session_start();
header('Content-type: text/html; charset=iso8859-7'); 
require_once '../tools/PHPWord.php';
require_once '../config.php';
require_once '../tools/functions.php';

$mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
mysql_select_db($db_name, $mysqlconnection);
mysql_query("SET NAMES 'greek'", $mysqlconnection);
mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);

set_time_limit (180);

$arr = unserialize(html_entity_decode($_POST['emp_arr']));

$i = 1;

// create directory if not present
if (!file_exists('../word/anapl')) {
    mkdir('../word/anapl', 0777, true);
}

foreach($arr as $myarr)
{
    $hour_sum = 0;
    $PHPWord = new PHPWord();

    // choose word template depending on employee type etc.
    // kratikoy
    if ($_POST['kratikoy'])
        $document = $PHPWord->loadTemplate('../word/tmpl_anapl/tmpl_vev_anapl.docx');
    // espa
    else {
        if ($myarr['eepebp'] > 0){
            // if PEP
            if ($myarr['eepebp'] == 2){
                $document = $PHPWord->loadTemplate('../word/tmpl_anapl/tmpl_pep.docx');
            } else {
                $document = $PHPWord->loadTemplate('../word/tmpl_anapl/tmpl_eepebp.docx');
            }
            $data = $myarr['eepebp'] == 1 ?
                'Ειδικού Εκπαιδευτικού Προσωπικού ΕΕΠ' :
                'Ειδικού Βοηθητικού Προσωπικού (ΕΒΠ)';
            $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
            $document->setValue('eepebp', $data);
        }
        else
            $document = $PHPWord->loadTemplate('../word/tmpl_anapl/tmpl_vev_anapl_espa.docx');
    }

    $data = $endofyear;
    $document->setValue('endofyear', $data);
    $data = $hmapox = $myarr['hmapox'];
    $data = date("d-m-Y", strtotime($data));
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
    
    $data = $myarr['ada'];
    $data = str_replace(array( '(', ')' ), "", $data);
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    $document->setValue('ada', $data);
    
    $didetos = substr($sxol_etos,0,4).'-'.substr($sxol_etos,4,2);
    $document->setValue('didetos', $didetos);
    
    $data = $myarr['apof'];
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    $document->setValue('apof', $data);
    
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
            // if meiwmeno, compute total hours
            if ($k === 'hours' && $myarr['meiwmeno']){
                $hour_sum += $v;
            }
        }
    }
    $sxoleia = substr($sxoleia, 0, -2);

    $data = mb_convert_encoding($sxoleia, "utf-8", "iso-8859-7");
    $document->setValue('schools', $data);

    // metakinhsh
    $metakinhsh = $myarr['metakinhsh'];
    if ($myarr['ebp'])
    {
        if (strlen($metakinhsh)<2)
            $top_metak = "και τοποθετήθηκε με την ταυτάριθμη απόφαση στο (-α) $sxoleia";
        else
            $top_metak = $metakinhsh . " " . $sxoleia;
    }
    else
    {
        if (strlen($metakinhsh)<2)
            $top_metak = "Τοποθετήθηκε με την αριθμ. ".$myarr['apof']." Απόφαση του Δ/ντή Π.Ε. Ηρακλείου στο (-α) $sxoleia";
        else
            
            $top_metak = $metakinhsh . " " . $sxoleia;
    }
    $data = mb_convert_encoding($top_metak, "utf-8", "iso-8859-7");
    $document->setValue('top_metak', $data);
    
    $data = $hmpros = $myarr['hmpros'];
    $data = date("d-m-Y", strtotime($data));
    $document->setValue('hmpros', $data);

    // meiwmeno
    $ypoxr = get_ypoxrewtiko_wrario($myarr['id'], $mysqlconnection);
    // debug
    // if ($ypoxr>24){
    //     echo "<br>".$myarr['surname']." ".$myarr['prefix']." $ypoxr";
    // }
    //
    $data = $myarr['meiwmeno'] ? 
        "μειωμένο ωράριο $hour_sum ώρες/εβδομάδα (πλήρες υποχρ.ωράριο $ypoxr ώρες/εβδ.)" :
        "πλήρες ωράριο ($ypoxr ώρες/εβδομάδα)";
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    $document->setValue('wrario', $data);

    //////////////////////////////
    // Adeies, aney, apergies poy afairoyntai
    //////////////////////////////
    $adeies = $myarr['adeies'];
    
    // debug
    // if ($adeies['subtracted'] >0){
    //     echo $myarr['surname']." ".$myarr['prefix']." ";
    //     print_r($adeies);
    //     echo "<br>";
    // }
    $adeies_txt = '';
    if ($adeies['anar_sub'] > 0) {
        $adeies_txt .= "Έλαβε αναρρωτικές άδειες σύνολο: ".$adeies['anar']." ημέρες, από τις οποίες μόνο 15 ημέρες υπολογίζονται για προϋπηρεσία σύμφωνα με το άρθρο 657 και 658 του αστικού κώδικα, το άρθρο 11 του Ν. 2874/2000, την εγκύκλιο αριθμ. 79/14-07-1999 ΙΚΑ, έγγραφο αρ. πρωτ. Π06/40/29-04-2013 ΙΚΑ. ";
    }
    if ($adeies['aney'] > 0){
        $adeies_aney = $adeies['aney'] > 1 ?
            $adeies['aney'] . " ημέρες, που αφαιρούνται από τη συνολική του/-ης προϋπηρεσία." :
            $adeies['aney'] . " ημέρα, που αφαιρείται από τη συνολική του/-ης προϋπηρεσία.";

        $adeies_txt .= "Έλαβε άδεια άνευ αποδοχών σε εφαρμογή του Ν.4075/2012 άρθρο 50 για ".$adeies_aney;
    }
    // if ($adeies['apergies'] > 0){
    //     $adeies_txt .= "Απέργησε ".$adeies['apergies']." ημέρα/-ες.";
    // }

    $data = mb_convert_encoding($adeies_txt, "utf-8", "iso-8859-7");
    $document->setValue('adeies', $data);
    //////////////////
    

    // ypologismos yphresias
    $apol = substr($hmapox,8,2) + substr($hmapox,5,2)*30 + substr($hmapox,0,4)*360;
    // hm/nia ya or apofasi perif/khs
    $tempya = strlen($myarr['ya']) > 0 ? $myarr['ya'] : $myarr['apof'];
    $temp = explode('/',$tempya);
    $temp = explode('-', $temp[2]);
    $hm_ya = $temp[0] + $temp[1]*30 + $temp[2]*360;
    // hm proslhpshs
    $pros = substr($hmpros,0,4)*360 + substr($hmpros,5,2)*30 + substr($hmpros,8,2);
    // +1 για να περιληφθεί και η τελευταία μέρα
    $days = $apol - $pros + 1;
    $days_ya = $apol - $hm_ya + 1;
    // subtract subtracted
    $days -= $adeies['subtracted'];
    $days_ya -= $adeies['subtracted'];
    // if meiwmeno, compute yphresia
    if ($myarr['meiwmeno']){
        $days = compute_meiwmeno($days, $hour_sum, $mysqlconnection);
        $days_ya = compute_meiwmeno($days_ya, $hour_sum, $mysqlconnection);
    }
    $ymd = days2ymd($days);
    $ymd_ya = days2ymd($days_ya);
    $data = $ymd[1]." μήνες, ".$ymd[2]." ημέρες";
    $data_ya = $ymd_ya[1]." μήνες, ".$ymd_ya[2]." ημέρες";
    // debug
    // echo "hm_ya: $tempya -> p: $data /// p_ya: $data_ya<br>";
    //
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    $document->setValue('yphr', $data);
    $data = mb_convert_encoding($data_ya, "utf-8", "iso-8859-7");
    $document->setValue('yphr_ya', $data);

    $data = getParam('head_title', $mysqlconnection);
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    $document->setValue('head_title', $data);
    $data = getParam('head_name', $mysqlconnection);
    $data = mb_convert_encoding($data, "utf-8", "iso-8859-7");
    $document->setValue('head_name', $data);
    
    // write to file
    $fname = greek_to_greeklish($myarr['surname']);
    $fname = $myarr['prefix'].$fname;
    $last_afm = $myarr['last_afm'];
    $output1 = "../word/anapl/".$fname.".docx";
    // if same surname, use last digits of afm
    if (file_exists($output1))
        $output1 = "../word/anapl/".$fname."_".$last_afm.".docx";
    $document->save($output1);
    $filenames[] = $output1;
    $i++;
} 

// create zip file
if ($_POST['kratikoy'])
    $zipname = '../word/anapl/vev.zip';
else
    $zipname = '../word/anapl/vev_espa.zip';
if (file_exists($zipname))
    unlink($zipname);
$zip = new ZipArchive;
$zip->open($zipname, ZipArchive::CREATE);
foreach ($filenames as $file) {
    $zip->addFile($file);
}
$zip->close();
// end of zip


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
echo "<br><p>Εκπ/κοί που βρέθηκαν: ".$_POST['plithos'];
echo "<br>Βεβαιώσεις που εξήχθησαν: ".$vev;
echo "</p><br><br><a href=$zipname>Ανοιγμα zip εγγράφου</a><br>";
echo "<input type='button' class='btn-red' value='Επιστροφή' onclick=\"parent.location='../etc/end_of_year.php'\">";
echo "</p>";

?>
</body>
</html>