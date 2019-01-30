<?php
	header('Content-type: text/html; charset=iso8859-7'); 
    session_start();
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <script type="text/javascript" src="../js/jquery_notification_v.1.js"></script>
    <link href="../css/jquery_notification.css" type="text/css" rel="stylesheet"/> 
    <title>Update</title>
  </head>
  <body> 
<?php
require_once "../config.php";
require_once "../tools/functions.php";

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
mysqli_query($mysqlconnection, "SET NAMES 'greek'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'greek'");

$ip = $_SERVER['REMOTE_ADDR'];

$id = $_POST['id'];
$name = $_POST["name"];  
$surname = $_POST['surname']; 
$klados =$_POST['klados']; 

if ($_POST['org'] == "")
    $org = 387;
else
{
$organ = mb_convert_encoding($_POST['org'], "iso-8859-7", "utf-8");
$org = getSchoolID($organ,$mysqlconnection);  
}
// yphr array
$count = count($_POST['yphr']);
for ($i=0; $i<$count; $i++)
{
    if ($_POST['yphr'][$i] == "")
        $yp_tmp = 387;
    else
        $yp_tmp = $_POST['yphr'][$i];
    $yphret[$i] = mb_convert_encoding($yp_tmp, "iso-8859-7", "utf-8");
    $yphr_arr[$i] = getSchoolID($yphret[$i],$mysqlconnection);
    $hours_arr[$i] = $_POST['hours'][$i];
}
$yphret = $_POST['yphr'][0];
$yphret = mb_convert_encoding($yphret, "iso-8859-7", "utf-8");
$yphr = getSchoolID($yphret,$mysqlconnection);  

$patrwnymo = $_POST['patrwnymo'];
$mhtrwnymo = $_POST['mhtrwnymo'];
$afm = $_POST['afm'];
$tel = $_POST['tel'];
$address = $_POST['address'];
$idnum = $_POST['idnum'];
$amka = $_POST['amka'];
$am = $_POST['am'];
$wres = $_POST['wres'];
$vathm = $_POST['vathm'];
$mk = $_POST['mk'];
$hm_mk = date('Y-m-d',strtotime($_POST['hm_mk']));
$fek_dior = $_POST['fek_dior'];
$hm_dior = date('Y-m-d',strtotime($_POST['hm_dior']));
$analipsi = $_POST['analipsi'];
//date('d-m-Y',strtotime($hm_dior))
$hm_anal = date('Y-m-d',strtotime($_POST['hm_anal']));
$met_did = $_POST['met_did'];
$proyp = $_POST['pyears']*360 + $_POST['pmonths']*30 + $_POST['pdays'];
$proyp_not = $_POST['peyears']*360 + $_POST['pemonths']*30 + $_POST['pedays'];
//$anatr = $_POST['anatr'];
//$anatr = $_POST['ayears']*360 + $_POST['amonths']*30 + $_POST['adays'];
$comments = addslashes($_POST['comments']);
$katast = $_POST['status'];
$thesi = $_POST['thesi'];
// aney 27-02-2014
if ($_POST['aney'])
    $aney = 1;
$aney_apo = date('Y-m-d',strtotime($_POST['aney_apo']));
$aney_ews = date('Y-m-d',strtotime($_POST['aney_ews']));
$aney_xr = $_POST['aney_y']*360 + $_POST['aney_m']*30 + $_POST['aney_d'];
// idiwtiko 07-11-2014
if ($_POST['idiwtiko']){
    $idiwtiko=1;
    $idiwtiko_enarxi = date('Y-m-d',strtotime($_POST['idiwtiko_enarxi']));
    $idiwtiko_liksi = date('Y-m-d',strtotime($_POST['idiwtiko_liksi']));
}
if ($_POST['idiwtiko_id']){
    $idiwtiko_id=1;
    $idiwtiko_id_enarxi = date('Y-m-d',strtotime($_POST['idiwtiko_id_enarxi']));
    $idiwtiko_id_liksi = date('Y-m-d',strtotime($_POST['idiwtiko_id_liksi']));
}
if ($_POST['katoikon']){
    $katoikon=1;
    $katoikon_apo = date('Y-m-d',strtotime($_POST['katoikon_apo']));
    $katoikon_ews = date('Y-m-d',strtotime($_POST['katoikon_ews']));
    $katoikon_comm = addslashes($_POST['katoikon_comm']);
}

// $_POST['action']=1 for adding records  
if (isset($_POST['action']))
{
    // check if record exists by checking am AND surname
    $surn = mb_convert_encoding($surname, "iso-8859-7", "utf-8");
    $query = "select am,surname from employee WHERE am='$am' AND surname = '$surn'";
    $result = mysqli_query($mysqlconnection, $query);
    if (!mysqli_num_rows($result))
    {
        $query0 = "INSERT INTO employee (name, surname, patrwnymo, mhtrwnymo, klados, am, sx_organikhs, sx_yphrethshs, fek_dior, hm_dior, vathm, mk, hm_anal, met_did, proyp, comments, afm, thesi, status, wres, proyp_not) ";
        $query1 = "VALUES ('$name','$surname','$patrwnymo','$mhtrwnymo','$klados','$am','$org','$yphr_arr[0]','$fek_dior','$hm_dior','$vathm','$mk','$hm_anal','$met_did','$proyp','$comments', '$afm', '$thesi', '$katast', '$wres', '$proyp_not')";
        $query = $query0.$query1;
        $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
        mysqli_query($mysqlconnection, $query);
        // insert into yphrethsh
        $id = mysqli_insert_id();
        for ($i=0; $i<count($yphr_arr); $i++) 
        {
            $query = "insert into yphrethsh (emp_id, yphrethsh, hours, sxol_etos) values ($id, '$yphr_arr[$i]', '$hours_arr[$i]', $sxol_etos)";
            mysqli_query($mysqlconnection, $query);
        } 
        // insert 2 log
        $query1 = "INSERT INTO employee_log (emp_id, userid, action, ip) VALUES ('$id',".$_SESSION['userid'].", 0,'$ip')";
        mysqli_query($mysqlconnection, $query1);
    }
    // if already inserted
    else 
    {
        notify('Η εγγραφή έχει ήδη καταχωρηθεί...',1);
        $dupe = 1;
    }
} //of if action

// if update
else {
    // get current row from db
    $qry = "SELECT * from employee WHERE id=$id";
    $res = mysqli_query($mysqlconnection, $qry);
    $before = mysqli_fetch_row($res);
        
    $query1 = "UPDATE employee SET name='".$name."', surname='".$surname."', klados='".$klados."', sx_organikhs='".$org."', sx_yphrethshs='$yphr_arr[0]',";
    $query2 = " patrwnymo='$patrwnymo', mhtrwnymo='$mhtrwnymo', am='$am', tel='$tel', address='$address', idnum='$idnum', amka='$amka', vathm='$vathm', mk='$mk', hm_mk='$hm_mk', fek_dior='$fek_dior', hm_dior='$hm_dior', analipsi='$analipsi',";
    $query3 = " aney='$aney', aney_xr='$aney_xr', aney_apo='$aney_apo', aney_ews='$aney_ews',idiwtiko='$idiwtiko',idiwtiko_liksi='$idiwtiko_liksi',idiwtiko_enarxi='$idiwtiko_enarxi',idiwtiko_id='$idiwtiko_id',idiwtiko_id_liksi='$idiwtiko_id_liksi',idiwtiko_id_enarxi='$idiwtiko_id_enarxi',katoikon='$katoikon',katoikon_apo='$katoikon_apo',katoikon_ews='$katoikon_ews',katoikon_comm='$katoikon_comm',";
    $query4 = " hm_anal='$hm_anal', met_did='$met_did', proyp='$proyp', proyp_not='$proyp_not',anatr='$anatr', comments='$comments',afm='$afm', status='$katast', thesi='$thesi', wres='$wres' WHERE id='$id'";
    $query = $query1.$query2.$query3.$query4;
    $query = mb_convert_encoding($query, "iso-8859-7", "utf-8");
    //echo $query;
    mysqli_query($mysqlconnection, $query);
    // insert 2 log
    if (mysqli_affected_rows()>0)
    {
        // find changes and write them to query field
        $qry = "SELECT * from employee WHERE id=$id";
        $res = mysqli_query($mysqlconnection, $qry);
        $after = mysqli_fetch_row($res);

        $diff = array_diff($after, $before);
        $temp = Array();
        foreach ($diff as $key => $value) {
            $field = mysqli_field_name($res, $key);
            if ($field === 'updated'){
                continue;
            }
            array_push($temp, $field .': '. $before[$key] .' -> '.$value);
        }
        $change = implode(", ", $temp);
        //
        $query1 = "INSERT INTO employee_log (emp_id, userid, action, ip, query) VALUES ('$id',".$_SESSION['userid'].", 1, '$ip', '$change')";
        mysqli_query($mysqlconnection, $query1);
    }
    // AND sxol_etos -> future use...
    $query = "DELETE FROM yphrethsh WHERE emp_id = $id AND sxol_etos=$sxol_etos";
    mysqli_query($mysqlconnection, $query);
    for ($i=0; $i<count($yphr_arr); $i++) 
    {
        $query = "insert into yphrethsh (emp_id, yphrethsh, hours, organikh, sxol_etos) values ($id, '$yphr_arr[$i]', '$hours_arr[$i]', '$org',$sxol_etos)";
        mysqli_query($mysqlconnection, $query);
    }
}
mysqli_close($mysqlconnection);

echo "<br>";
if (!$dupe)
    notify('Επιτυχής καταχώρηση!',0);
echo "</body>";
echo "</html>";
?>