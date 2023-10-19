<?php
  header("Content-Type:application/json");
  require_once "../config.php";
  require_once "../include/functions.php";

  $headers = getallheaders();
  // Check if the Bearer Token is present in the request headers
  if (!array_key_exists('Authorization', $headers)) {
    // Bearer Token is missing or empty
    http_response_code(401); // Unauthorized
    echo json_encode(array('message' => 'Authentication Error: Bearer Token is missing.'));
    exit;
  }

  // Extract the Bearer Token
  $authorizationHeader = $headers['Authorization'];
  $token = null;

  // Check if the Authorization header starts with "Bearer "
  if (strpos($authorizationHeader, 'Bearer ') === 0) {
    // Extract the token (remove "Bearer " prefix)
    $token = substr($authorizationHeader, 7);
  } else {
    // Invalid Authorization header format
    http_response_code(401); // Unauthorized
    echo json_encode(array('message' => 'Authentication Error: Invalid Authorization header format.'));
    exit;
  }

  // Now, you have the Bearer Token in the $token variable
  // You can use it for authentication or authorization as needed

  // Check if the token is valid
  if ($token !== $api_token) {
    http_response_code(401); // Unauthorized
    echo json_encode(array('message' => 'Authentication Error: Invalid Bearer Token.'));
    exit;
  }
 
  $conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);  
  mysqli_query($conn, "SET NAMES 'utf8'");
  mysqli_query($conn, "SET CHARACTER SET 'utf8'");
  
  // If in production, login using sch.gr's CAS server
  // (To be able to login via sch.gr's CAS, the app must be whitelisted from their admins)
  
    if (!$_GET['code']){
      die('Σφάλμα: Δεν έχει επιλεγεί σχολείο...');
    }
  $sch = getSchoolFromCode($_GET['code'], $conn);

  // collecting data...  
  $str1 = getSchool($sch, $conn);
  if (!$str1) {
      die('Το σχολείο δε βρέθηκε...');
  }
  
  
  if (isset($_GET['sxoletos'])) {
      $sxol_etos = $_GET['sxoletos'];
  }
  $response = [];


  // school data  
  $query = "SELECT * from school where id=$sch";
  $result = mysqli_query($conn, $query);
                
  $titlos = mysqli_result($result, 0, "titlos");
  $address = mysqli_result($result, 0, "address");
  $tk = mysqli_result($result, 0, "tk");
  $dimos = mysqli_result($result, 0, "dimos");
  $dimos = getDimos($dimos, $conn);
  $cat = getCategory(mysqli_result($result, 0, "category"));
  $tel = mysqli_result($result, 0, "tel");
  $fax = mysqli_result($result, 0, "fax");
  $email = mysqli_result($result, 0, "email");
  $type = mysqli_result($result, 0, "type");
  $type2 = mysqli_result($result, 0, "type2");
  $organikothta = mysqli_result($result, 0, "organikothta");
  $leitoyrg = get_leitoyrgikothta($sch, $conn);
  // organikes - added 05-10-2012
  $organikes = unserialize(mysqli_result($result, 0, "organikes"));
  // kena_org, kena_leit - added 19-06-2013
  $kena_org = unserialize(mysqli_result($result, 0, "kena_org"));
  $code = mysqli_result($result, 0, "code");
  $updated = mysqli_result($result, 0, "updated");
  $perif = mysqli_result($result, 0, "perif");
  $systeg = mysqli_result($result, 0, "systeg");
  $anenergo = mysqli_result($result, 0, "anenergo");
  if ($systeg) {
      $systegCode = getSchoolCode($systeg, $conn);
  }
  $archive = mysqli_result($result, 0, "archive");
                        
  // if dimotiko
  if ($type == 1) {
      $students = mysqli_result($result, 0, "students");
      $classes = explode(",", $students);
      $frontistiriako = mysqli_result($result, 0, "frontistiriako");
      $ted = mysqli_result($result, 0, "ted");
      //$oloimero_stud = mysqli_result($result, 0, "oloimero_stud");
      $tmimata = mysqli_result($result, 0, "tmimata");
      $tmimata_exp = explode(",", $tmimata);
      //$oloimero_tea = mysqli_result($result, 0, "oloimero_tea");
      $ekp_ee = mysqli_result($result, 0, "ekp_ee");
      $ekp_ee_exp = explode(",", $ekp_ee);
      
      $synolo = array_sum($classes);
      //$synolo_tmim = array_sum($tmimata_exp);
      $vivliothiki = mysqli_result($result, 0, "vivliothiki");
  }
  //if nipiagwgeio
  else if ($type == 2) {
      $klasiko = mysqli_result($result, 0, "klasiko");
      $klasiko_exp = explode(",", $klasiko);
      $oloimero_nip = mysqli_result($result, 0, "oloimero_nip");
      $oloimero_nip_exp = explode(",", $oloimero_nip);
      $nip = mysqli_result($result, 0, "nip");
      $nip_exp = explode(",", $nip);
  }
  // entaksis (varchar): on/off, no. of students
  $entaksis = explode(",", mysqli_result($result, 0, "entaksis"));
  $org_ent = $entaksis[0] ? 1 : 0;
  $ypodoxis = mysqli_result($result, 0, "ypodoxis");
  //$frontistiriako = mysqli_result($result, 0, "frontistiriako");
  $oloimero = mysqli_result($result, 0, "oloimero");
  $comments = mysqli_result($result, 0, "comments");
  $schtype = $type == 1 ? 'dim' : 'nip';

  $school_arr = array(
    'type' => $schtype,
    'type2' => $type2,
    'title' => $titlos,
    'address' => $address.' - '.$tk,
    'dimos' => $dimos,
    'tel' => $tel,
    'email' => $email,
    'organikothta' => $organikothta,
    'leitoyrg' => $leitoyrg,
    'comments' => $comments,
    'te' => $org_ent,
    'te_stud' => $entaksis[1],
    'ypodoxis' => $ypodoxis,
    'has_oloimero' => $oloimero,
    'systeg' => $systegCode
  );
        
  // οργανικά τοποθετηθέντες
  $klados_qry = ($type == 1) ? 2 : 1;
  $qry = "SELECT count(*) as cnt FROM employee WHERE sx_organikhs = $sch AND klados= $klados_qry AND status IN (1,3,5) AND thesi IN (0,1,2)";
  $rs = mysqli_query($conn, $qry);
  $orgtop = mysqli_result($rs, 0, "cnt");
  $school_arr['orgtop'] = $orgtop;
  $school_arr['cat'] = $cat;

        
  // 05-10-2012 - organikes
  for ($i=0; $i<count($organikes); $i++) {
      if (!$organikes[$i]) {
          $organikes[$i]=0;
      }
  }
  $school_arr['organikes'] = $organikes;
        
  $orgs = get_orgs($sch,$conn);
  $school_arr['org_top'] = $orgs;
  
        
  // 05-10-2012 - kena_leit, kena_org
  for ($i=0; $i<count($kena_org); $i++) {
      if (!$kena_org[$i]) {
          $kena_org[$i]=0;
      }
  }
  $school_arr['kena_org'] = $kena_org;
        
  if ($type == 1) {
      if ($synolo>0) {
          $synolo_pr_math = $classes[0]+$classes[1]+$classes[2]+$classes[3]+$classes[4]+$classes[5];
          $synolo_pr_tm = $tmimata_exp[0]+$tmimata_exp[1]+$tmimata_exp[2]+$tmimata_exp[3]+$tmimata_exp[4]+$tmimata_exp[5];
          $school_arr['classes'] = $classes;
          $school_arr['tmimata'] = $tmimata_exp;
          $school_arr['synolo_mathiton'] = $synolo_pr_math;
          $school_arr['sylono_tmimaton'] = $synolo_pr_tm;
      }
  }
  else if ($type == 2) {
      // klasiko_nip/pro: klasiko
      // klasiko pos 0-5: 0,1 t1n,p / 2,3 t2n,p / 4,5 t3n,p
      // prwinh zvnh @ pos 7 -> klasiko[6]
      // oloimero_syn_nip/pro: oloimero
      $school_arr['classes'] = array_map(function($value) { return $value === '' ? 0 : $value; }, $klasiko_exp);
      $school_arr['oloimero'] = array_map(function($value) { return $value === '' ? 0 : $value; }, $oloimero_nip_exp);
      
      $klasiko_nip = $klasiko_exp[0] + $klasiko_exp[2] + $klasiko_exp[4] + $klasiko_exp[7] + $klasiko_exp[9] + $klasiko_exp[11];
      $klasiko_pro = $klasiko_exp[1] + $klasiko_exp[3] + $klasiko_exp[5] + $klasiko_exp[8] + $klasiko_exp[10] + $klasiko_exp[12];
      $oloimero_syn_nip = $oloimero_nip_exp[0] + $oloimero_nip_exp[2] + $oloimero_nip_exp[4] + $oloimero_nip_exp[6] + $oloimero_nip_exp[8] + $oloimero_nip_exp[10];
      $oloimero_syn_pro = $oloimero_nip_exp[1] + $oloimero_nip_exp[3] + $oloimero_nip_exp[5] + $oloimero_nip_exp[7] + $oloimero_nip_exp[9] + $oloimero_nip_exp[11];
      
      // Μαθητές
      $school_arr['synolo_mathiton'] = $klasiko_nip + $klasiko_pro;
      $school_arr['synolo_oloimero'] = $oloimero_syn_nip + $oloimero_syn_pro;


      $temp_arr = tmimata_nipiagwgeiwn($conn, $sch);
      $tmimata_nip = $temp_arr['klasiko'];
      $tmimata_nip_ol = $temp_arr['oloimero'];

      $has_entaxi = strlen($entaksis[0])>1 ? 1 : 0; 

      
      // τοποθετημένοι εκπ/κοί
      $top60 = $top60m = $top60ana = 0;
      $qry = "SELECT count(*) as pe60 FROM employee WHERE sx_yphrethshs = $sch AND klados=1 AND status=1";
      $res = mysqli_query($conn, $qry);
      $top60m = mysqli_result($res, 0, 'pe60');
      $qry = "SELECT count(*) as pe60 FROM ektaktoi WHERE sx_yphrethshs = $sch AND klados=1 AND status=1";
      $res = mysqli_query($conn, $qry);
      $top60ana = mysqli_result($res, 0, 'pe60');
      $top60 = $top60m+$top60ana;
      
      $syn_apait = $tmimata_nip+$tmimata_nip_ol+$has_entaxi;

      $k_pl = $top60-$syn_apait;
      $school_arr['kena_pleonasmata'] = array(
        'apaitoymenoi' => array('synolo'=>$syn_apait, 'klasiko'=>$tmimata_nip, 'oloimero'=>$tmimata_nip_ol, 'entaksis'=>$has_entaxi),
        'yparxontes' => $top60,
        'kena_pleon' => $k_pl
      );

  }
  // if dimotiko & leitoyrg >= 4
  if ($type == 1 ) { //&& array_sum($tmimata_exp)>3){
      $school_arr['kena_pleonasmata'] = ektimhseis_wrwn($sch, $conn, $sxol_etos, false, true);
  }

  $response['school_data'] = $school_arr;
  // end of school data
    

  //Υπηρετούν με θητεία
  $query = "SELECT e.surname,e.name,k.perigrafh as klados,e.thesi from employee e JOIN klados k ON e.klados = k.id WHERE sx_yphrethshs='$sch' AND status=1 AND thesi in (1,2,6) ORDER BY thesi DESC";
  $result = mysqli_query($conn, $query);
  $thiteia = array();
  while ($r = mysqli_fetch_assoc($result)){
    $thiteia[] = $r;
  }
  $response['thiteia'] = $thiteia;
    
  //Ανήκουν οργανικά και υπηρετούν (ΠΕ60-70)
  $query = "SELECT e.surname,e.name,k.perigrafh as klados from employee e JOIN klados k ON e.klados = k.id ";
  $query .= "WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,5) AND (klados=2 OR klados=1)";
  $result = mysqli_query($conn, $query);
  $organika = array();
  while ($r = mysqli_fetch_assoc($result)){
    $organika[] = $r;
  }
  $response['organika'] = $organika;

  //Ανήκουν οργανικά και υπηρετούν (Ειδικότητες)
  $query = "SELECT e.surname,e.name,k.perigrafh as klados from employee e JOIN klados k ON e.klados = k.id ";
  $query .= "WHERE sx_organikhs='$sch' AND sx_yphrethshs='$sch' AND status=1 AND thesi in (0,5) AND klados!=2 AND klados!=1";
  $result = mysqli_query($conn, $query);
  $organika_eid = array();
  while ($r = mysqli_fetch_assoc($result)){
    $organika_eid[] = $r;
  }
  $response['organika_eid'] = $organika_eid;

  // Οργανική αλλού και υπηρετούν
  $query = "SELECT e.surname,e.name,k.perigrafh as klados,s.name as sx_organikhs from employee e JOIN klados k ON e.klados = k.id ";
  $query .= "JOIN school s ON e.sx_organikhs = s.id WHERE e.sx_organikhs!='$sch' AND sx_yphrethshs='$sch' AND thesi in (0,5) AND status=1 ORDER BY klados";
  $result = mysqli_query($conn, $query);
  $organikh_allou = array();
  while ($r = mysqli_fetch_assoc($result)){
    $organikh_allou[] = $r;
  }
  $response['organikh_allou'] = $organikh_allou;

  // Οργανική αλλού και δευτερεύουσα υπηρέτηση
  $query = "SELECT e.surname,e.name,k.perigrafh as klados,s.name as organikh,y.wres FROM employee e JOIN klados k ON k.id = e.klados ";
  $query .= "JOIN yphrethsh y on e.id = y.emp_id JOIN school s on e.sx_organikhs = s.id where y.yphrethsh=$sch and e.sx_yphrethshs!=$sch AND y.sxol_etos = $sxol_etos";

  $result = mysqli_query($conn, $query);
  $organikh_allou_deyt = array();
  while ($r = mysqli_fetch_assoc($result)){
    $organikh_allou_deyt[] = $r;
  }
  $response['organikh_allou_deyt'] = $organikh_allou_deyt;


  //Υπηρετούν σε τμήμα ένταξης
  $query = "SELECT e.name,e.surname,k.perigrafh as klados,s.name as organikh from employee e JOIN klados k ON e.klados = k.id ";
  $query .= "JOIN school s ON e.sx_organikhs = s.id WHERE sx_yphrethshs='$sch' AND e.status=1 AND ent_ty=1";
  $result = mysqli_query($conn, $query);
  $entaksis = array();
  while ($r = mysqli_fetch_assoc($result)){
    $entaksis[] = $r;
  }
  $response['entaksis'] = $entaksis;


  //Υπηρετούν σε τάξη υποδοχής
  $query = "SELECT e.name,e.surname,k.perigrafh as klados, s.name as organikh from employee e JOIN klados k ON e.klados = k.id ";
  $query .= "JOIN school s ON e.sx_organikhs = s.id WHERE sx_yphrethshs='$sch' AND status=1 AND ent_ty=2";
  $result = mysqli_query($conn, $query);
  $ty = array();
  while ($r = mysqli_fetch_assoc($result)){
    $ty[] = $r;
  }
  $response['ty'] = $ty;


  //Αναπληρωτές
  $query = "SELECT e.surname,e.name,k.perigrafh as klados,e.thesi,tp.type as etype, e.name as ename, p.name as praxi, thesi as praxiname, hours FROM ektaktoi e ";
  $query .= "join yphrethsh_ekt y on e.id = y.emp_id JOIN ektaktoi_types tp ON e.type = tp.id ";
  $query .= "JOIN klados k ON e.klados = k.id join praxi p on e.praxi = p.id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 1)";
  $result = mysqli_query($conn, $query);
  $anapl = array();
  while ($r = mysqli_fetch_assoc($result)){
    $anapl[] = $r;
  }
  $response['anapl'] = $anapl;


  //Απουσιάζουν: Ανήκουν οργανικά και υπηρετούν αλλού
  $query = "SELECT e.surname,e.name,k.perigrafh as klados,s.name as yphrethsh,e.comments from employee e ";
  $query .= "JOIN klados k ON e.klados = k.id JOIN school s ON e.sx_yphrethshs = s.id ";
  $query .= "WHERE sx_organikhs='$sch' AND sx_yphrethshs!='$sch' AND e.status IN (1,3) order by klados";
  $result = mysqli_query($conn, $query);
  $apontes = array();
  while ($r = mysqli_fetch_assoc($result)){
    $apontes[] = $r;
  }
  $response['apontes'] = $apontes;


  //Σε άδεια
  $today = date("Y-m-d");
  $query = "SELECT e.surname,e.name,k.perigrafh as klados,tp.type,ad.finish,e.comments FROM adeia ad RIGHT JOIN employee e ON ad.emp_id = e.id ";
  $query .= "JOIN adeia_type tp ON ad.type = tp.id JOIN klados k ON e.klados = k.id WHERE (sx_organikhs='$sch' OR sx_yphrethshs='$sch') ";
  $query .= "AND ((start<'$today' AND finish>'$today') OR status=3) ORDER BY finish DESC";
  $result = mysqli_query($conn, $query);
  $adeia = array();
  while ($r = mysqli_fetch_assoc($result)){
    $adeia[] = $r;
  }
  $response['adeia'] = $adeia;
  

  //Αναπληρωτές σε άδεια
  $query = "SELECT e.surname,e.name,k.perigrafh as klados,e.thesi,tp.type as etype, e.name as ename, p.name as praxi, thesi as praxiname, hours FROM ektaktoi e ";
  $query .= "join yphrethsh_ekt y on e.id = y.emp_id JOIN ektaktoi_types tp ON e.type = tp.id ";
  $query .= "JOIN klados k ON e.klados = k.id join praxi p on e.praxi = p.id where (y.yphrethsh=$sch AND y.sxol_etos = $sxol_etos AND e.status = 3)";
  $result = mysqli_query($conn, $query);
  $anapladeia = array();
  while ($r = mysqli_fetch_assoc($result)){
    $anapladeia[] = $r;
  }
  $response['anapladeia'] = $anapladeia;


  $json_response = json_encode($response);
  echo $json_response;

?>
