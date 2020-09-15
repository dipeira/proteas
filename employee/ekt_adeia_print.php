<?php
require_once"../config.php";
require_once"../include/functions.php";
require_once '../tools/num2wordgen.php';

session_start();
$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");


// Get employee data
$emp_id = $_POST['arr'][0];
$query1 = "select * from ektaktoi where id=$emp_id";
$result1 = mysqli_query($mysqlconnection, $query1);
$name = mysqli_result($result1, 0, "name");
$surname = mysqli_result($result1, 0, "surname");

$kl1 = mysqli_result($result1, 0, "klados");
$q2 = "select * from klados where id=$kl1";
$res2 = mysqli_query($mysqlconnection, $q2);
$klados = mysqli_result($res2, 0, "perigrafh");

//$yphr = mysqli_result($result1, 0, "sx_yphrethshs");
//$q3 = "select * from school where id=$yphr";
//$res3 = mysqli_query($mysqlconnection, $q3)
//$school = mysqli_result($res3, 0, "name");

$sx_yphrethshs_id_str = mysqli_result($result1, 0, "sx_yphrethshs");
$sx_yphrethshs_id_arr = explode(",", $sx_yphrethshs_id_str);
$sx_yphrethshs_id = trim($sx_yphrethshs_id_arr[0]);
$school = getSchool($sx_yphrethshs_id, $mysqlconnection);

$word = new COM("word.application") or die("Unable to instantiate Word");

$type = $_POST['arr'][1];
switch ($type)
{
  case 1:
      $word->Documents->Open(realpath("word/adanapl/anar.doc"));
      break;
  case 2:
      $word->Documents->Open(realpath("word/adanapl/kan.doc"));
      break;
  case 3:
      $word->Documents->Open(realpath("word/adanapl/anar_gnwm.doc"));
      break;
  case 4:
      $word->Documents->Open(realpath("word/adanapl/eidikh.doc"));
      break;
  case 5:
      $word->Documents->Open(realpath("word/adanapl/loxeias.doc"));
      break;
  case 6:
      $word->Documents->Open(realpath("word/adanapl/kyhshs.doc"));
      break;
  case 7:
      $word->Documents->Open(realpath("word/adanapl/anatr.doc"));
      break;
  case 8:
      $word->Documents->Open(realpath("word/adanapl/gonikh.doc"));
      break;
  case 9:
      $word->Documents->Open(realpath("word/adanapl/kan_kyof.doc"));
      break;
  case 10:
      $word->Documents->Open(realpath("word/adanapl/aney_1m.doc"));
      break;
  case 12:
      $word->Documents->Open(realpath("word/adanapl/aney_1y.doc"));
      break;
              // loipes
}

    $data = $klados;
    $bookmarkname = "KLADOS";
    $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
    $range = $objBookmark->Range;
    $range->Text = $data;
        
    $data = "$surname $name";
    $bookmarkname = "FULLNAME";
    $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
    $range = $objBookmark->Range;
    $range->Text = $data;

    if ($type!=3) {
        $bookmarkname = "FULLNAME1";
        $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
        $range = $objBookmark->Range;
        $range->Text = $data;
    }

    $data = $school;
    $bookmarkname = "SCHOOL";
    $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
    $range = $objBookmark->Range;
    $range->Text = $data;
        
    $data = $_POST['arr']['2'];
    $bookmarkname = "PROT";
    $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
    $range = $objBookmark->Range;
    $range->Text = $data;

    $data = $_POST['arr']['3'];
    $bookmarkname = "HMPROT";
    $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
    $range = $objBookmark->Range;
    $range->Text = $data;

    if ($type!=3) {
        $data = $_POST['arr']['4'];
        $bookmarkname = "DATE";
        $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
        $range = $objBookmark->Range;
        $range->Text = $data;
    }

    // if aney apodoxwn skip days & daysfull
    if (($type != 10) && ($type != 12)) {
        $data = $days = $_POST['arr']['6'];
        $bookmarkname = "DAYS";
        $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
        $range = $objBookmark->Range;
        $range->Text = $data;


        if ($days==1) {
            $data = "(μίας) ημέρας";
        } else {
            $data = "(".convertNumber($data).") ημερών";
        }
        $bookmarkname = "DAYSFULL";
        $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
        $range = $objBookmark->Range;
        $range->Text = $data;   
    }

    $start = $_POST['arr']['7'];
    $finish = $_POST['arr']['8'];
    if ($days==1) {
        $duration = "στις $start";
    } else {
        $duration = "από $start έως $finish";
    }
    $data = $duration;
    $bookmarkname = "DURATION";
    $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
    $range = $objBookmark->Range;
    $range->Text = $data;


    if ($type==1) {
        $vevdil = $_POST['arr']['5'];
        if ($vevdil==1) {
            $data = "Ιατρική Βεβαίωση";
        } else {
            $data = "Υπεύθυνη Δήλωση";
        }
        $bookmarkname = "VEVDIL";
        $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
        $range = $objBookmark->Range;
        $range->Text = $data;
    }
    /*
    logos

    $data = $_POST['arr']['6'];
    $bookmarkname = "DAYS";
    $objBookmark = $word->ActiveDocument->Bookmarks($bookmarkname);
    $range = $objBookmark->Range;
    $range->Text = $data;
    */

    //7. save the template as a new document (c:/reminder_new.doc)
    //$new_file = "c:/new.doc";
    //$word->Documents[1]->SaveAs($new_file);
    $output1 = "word/new_".$_SESSION['userid'].".doc";
    $word->Documents[1]->SaveAs(realpath($output1));
    //8. free the object
    $word->Quit();
    //$word->Release();
    $word = null;
    header('Content-type: text/html; charset=utf-8'); 

    echo "<html>";
    echo "<p><a href=$output1>Ανοιγμα εγγράφου</a></p>";
    ?>
</html>
