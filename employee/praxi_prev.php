<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"../config.php";
  require_once"../tools/functions.php";
    
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
    include("../tools/class.login.php");
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false)
    {   
        header("Location: ../tools/login.php");
    }
    else
        $logged = 1;
    
    // Get previous sxol_etos or redirect
    if (((strlen($_REQUEST['sxoletos'])>0) || isset($_SESSION['sxoletos'])) && strcmp($_REQUEST['sxoletos'], $sxol_etos) != 0)
    {
        $sxoletos = $_REQUEST['sxoletos'];
    }
    else
    {
        $sxoletos = find_prev_year($sxol_etos);
    }
//     if (!isset($_SESSION['sxoletos']))
// 	{
// 		$_SESSION['sxoletos'] = $sxoletos;
// 	}
// 	else {
// 		$sxoletos = $_SESSION['sxoletos'];
// 	}
      
?>
<html>
<head>
	<LINK href="../css/style.css" rel="stylesheet" type="text/css">
    	<meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    	<title>������� ������������ �����</title>
</head>
  <body> 
  <?php include('../etc/menu.php'); ?>
  	
<div>
<?php
	echo "<h3>������� �������� �����: " . substr($sxoletos,0,4) . '-' . substr($sxoletos,4,2) ."</h3>";
	
	$query = "SELECT * from praxi_old where sxoletos=".$sxoletos;
	//echo $query;
	
	$result = mysql_query($query, $mysqlconnection);
		
        //echo "<center>";        
	echo "<table id=\"mytbl\" class=\"imagetable \" border=\"2\">\n";
        echo "<thead>";
        echo "<tr>";
	echo "<th>�����</th>\n";
	echo "<th>Y.A.</th>\n";
	echo "<th>�.�.�.</th>\n";
        echo "<th>�������</th>\n";
	echo "<th>������</th>\n";
	echo "<th>�����</th>\n";
	echo "</tr>\n</thead>\n";
	
	echo "<tbody>\n";
	
	while ($row = mysql_fetch_array($result))
	{
		echo "<tr>";
		echo "<td>".$row['name']."</td>";
		echo "<td>".$row['ya']."</td>";
		echo "<td>".$row['ada']."</td>";
		echo "<td>".$row['apofasi']."</td>";
		echo "<td>".$row['sxolio']."</td>";
		echo "<td>".$row['type']."</td>";
		echo "</tr>";
	}   
	echo "</tbody>\n";
	echo "</table>\n";
      ?>
      
      <br>
      <INPUT TYPE='button' class='btn-red' VALUE='���������' onClick='parent.location="ektaktoi_prev.php?sxoletos=<?=$sxoletos?>"'>
    </center>
</div>
  </body>
</html>
<?php
	mysql_close();
?>
