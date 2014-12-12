<?php
	header('Content-type: text/html; charset=iso8859-7'); 
	require_once"config.php";
	require_once"functions.php";
	
	$met_did = $_POST['met_did'];
	
                 
        $anatr= $_POST['anatr'];
	//if (isset($_POST['yphr']))
	$d1 = strtotime($_POST['yphr']);
        //$vathm = $_POST['vathm'];
        $vathm = mb_convert_encoding($_POST['vathm'], "iso-8859-7", "utf-8");
	$result = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $anatr;
	if ($result<=0)
		echo "Λάθος ημερομηνία";
	else
	{
		$ymd=days2ymd($result);	
		echo "Έτη: $ymd[0] &nbsp; Μήνες: $ymd[1] &nbsp; Ημέρες: $ymd[2]";
		//$vath = vathmos($result);
		//$mk = mk($result,$vathm);
                
                if ($met_did==1)
			$anatr = $_POST['anatr'] - 720;
		else if ($met_did==2)
			$anatr = $_POST['anatr'] - 2160;
		else if ($met_did==3)
			$anatr = $_POST['anatr'] - 2520;
		else
                        $anatr = $_POST['anatr'];
                $result = (date('d',$d1) + date('m',$d1)*30 + date('Y',$d1)*360) - $anatr;
                                
                $tmp = mk_plus($result, $vathm);
                $mk = $tmp[0];
                
		echo "<br>Βαθμός: $vathm";
                if ($mk <> $_POST['mk'])
                {
                    $ymd = days2ymd($tmp[1]);
                    $v99 = "-$tmp[1] day"; //may need fixing...
                    $vdate = strtotime ( $v99 , $d1 );
                    $vdate = date ( 'd-m-Y' , $vdate );
                    echo "<br>MK: $mk <small>(από $vdate)</small>";
                }
                
                else
                    echo "<br>MK: $mk";
	}
	
		
?>