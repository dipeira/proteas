<?php
  header('Content-type: text/html; charset=iso8859-7'); 
  require_once"config.php";
  require_once"functions.php";
    
  $mysqlconnection = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $mysqlconnection);
  mysql_query("SET NAMES 'greek'", $mysqlconnection);
  mysql_query("SET CHARACTER SET 'greek'", $mysqlconnection);
  
    include("tools/class.login.php");
    $log = new logmein();
    if($log->logincheck($_SESSION['loggedin']) == false)
    {   
        header("Location: tools/login_check.php");
    }
    else
        $logged = 1;
      
?>
<html>
  <head>
	<center><IMG src="images/header.jpg"></center>
	<LINK href="style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>������� - ��������� �������������</title>
	
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>
	<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
	<script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
	<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
	<script type="text/javascript">		
		$().ready(function() {
			$("#org").autocomplete("get_school.php", {
				width: 260,
				matchContains: true,
				//mustMatch: true,
				//minChars: 0,
				//multiple: true,
				//highlight: false,
				//multipleSeparator: ",",
				selectFirst: false
			});
		});
		$().ready(function() {
			$("#yphr").autocomplete("get_school.php", {
				width: 260,
				matchContains: true,
				//mustMatch: true,
				//minChars: 0,
				//multiple: true,
				//highlight: false,
				//multipleSeparator: ",",
				selectFirst: false
			});
		});
		$().ready(function() {
			$("#surname").autocomplete("get_name.php", {
				width: 260,
				matchContains: true,
				//mustMatch: true,
				//minChars: 0,
				//multiple: true,
				//highlight: false,
				//multipleSeparator: ",",
				selectFirst: false
			});
		});
		
		$(document).ready(function() { 
			$("#mytbl").tablesorter({widgets: ['zebra']}); 
		}); 
	</script>
	
  </head>
  <body> 
  <!-- <h1> DIPE D.B.</h1> -->
	
<div>
      <?php
                $usrlvl = $_SESSION['userlevel'];
                //$query = "SELECT * FROM employee";
		//rpp = results per page
		if (isset ($_POST['rpp']))
			$rpp = $_POST['rpp'];
		elseif (isset ($_GET['rpp']))
			$rpp = $_GET['rpp'];
		else
			$rpp= 20;
			
		if ($_POST['page']!=0)
			$curpg = $_POST['page'];
                elseif (isset($_GET['page'])) 
			$curpg = $_GET['page'];
		else
			$curpg = 1;
		                
		//limit in the query thing
		$limitQ = ' LIMIT ' .($curpg - 1) * $rpp .',' .$rpp;

		//$query = "SELECT * FROM employee ORDER BY surname ";
                $query = "SELECT * FROM employee ";
	  	
		$klpost = 0;
		$orgpost = 0;
		$yppost = 0;
		if (($_POST['klados']>0) || (strlen($_POST['org'])>0) || (strlen($_POST['yphr'])>0) || (strlen($_POST['surname'])>0))
		{
			$posted=1;
			$curpg=1;
		}
		if (($_POST['klados']>0) || ($_GET['klados']>0))
		{
			if ($_POST['klados']>0)
				$klpost = $_POST['klados'];
			else
				$klpost = $_GET['klados'];
			$query .= "WHERE klados = $klpost ";
			$whflag=1;
		}
		if ((strlen($_POST['org'])>0) || ($_GET['org']>0))
		{
			if (strlen($_POST['org'])>0)
				$orgpost = getSchoolID ($_POST['org'],$mysqlconnection);
			if ($_GET['org']>0)
				$orgpost = $_GET['org'];
			if ($whflag)
				$query .= "AND sx_organikhs = $orgpost ";
			else
			{
				$query .= "WHERE sx_organikhs = $orgpost ";
				$whflag=1;
			}	
		}
		if ((strlen($_POST['yphr'])>0) || ($_GET['yphr']>0))
		{
			if (strlen($_POST['yphr'])>0)
				$yppost = getSchoolID ($_POST['yphr'], $mysqlconnection);
			if ($_GET['yphr']>0)
				$yppost = $_GET['yphr'];
			if ($whflag)
				$query .= "AND sx_yphrethshs = $yppost ";
			else
				$query .= "WHERE sx_yphrethshs = $yppost ";
		}
		if (strlen($_POST['surname'])>0 || strlen($_GET['surname'])>0)
		{
			if (strlen($_POST['surname'])>0)
                            $surpost = $_POST['surname'];
                        else
                            $surpost = $_GET['surname'];
			if ($whflag)
				$query .= "AND surname LIKE '$surpost' ";
			else
			{
				$query .= "WHERE surname LIKE '$surpost' ";
				$whflag=1;
			}
		}
		                
                // Mono idiwtikoi
                if ($whflag)
                    $query .= " AND thesi=5 ";
                else
                    $query .= " WHERE thesi=5 ";
                
                $query_all = $query;
                
		$query .= " ORDER BY surname ";
                $query .= $limitQ;
                
		// Debugging...
		//echo $query;
		
		$result = mysql_query($query, $mysqlconnection);
		$result1 = mysql_query($query_all, $mysqlconnection);
		// Number of records found
		
		if ($result)
			$num_record = mysql_num_rows($result);
		if ($result1)
			$num_record1 = mysql_num_rows($result1);
		$lastpg = ceil($num_record1 / $rpp);
		
				
		if ($result)
			$num=mysql_num_rows($result);
	
        echo "<center>";        
	echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr><form id='src' name='src' action='index.php' method='POST'>\n";
	if ($posted || ($_GET['klados']>0) || ($_GET['org']>0) || ($_GET['yphr']>0))
		echo "<td><INPUT TYPE='submit' VALUE='���������'></td><td>\n";
	else	
		echo "<td><INPUT TYPE='submit' VALUE='���������'></td><td>\n";
	echo "<input type='text' name='surname' id='surname''/>\n";
	echo "<td></td></td><td>\n";
	kladosCmb($mysqlconnection);
	echo "</td>\n";
		echo "<div id=\"content\">";
		echo "<form autocomplete=\"off\">";
		echo "<td><input type=\"text\" name=\"org\" id=\"org\" /></td>";
		echo "<td><input type=\"text\" name=\"yphr\" id=\"yphr\" /></td>";
		echo "</div>";
	echo "</td>";
	echo "</form></tr>\n";
        
	//echo "<thead>\n<tr><th>��������</th>\n";
        echo "<tr><th>��������</th>\n";
	//echo "<th>ID</th>\n";
	echo "<th>�������</th>\n";
	echo "<th>�����</th>\n";
	echo "<th>����������</th>\n";
	echo "<th>��.���������</th>\n";
	echo "<th>��.����������</th>\n";
	echo "</tr>\n</thead>\n";
	
	echo "<tbody>\n";
	
	while ($i < $num)
	{
	
		$id = mysql_result($result, $i, "id");
		$name = mysql_result($result, $i, "name");
		$surname = mysql_result($result, $i, "surname");
		$klados_id = mysql_result($result, $i, "klados");
		$klados = getKlados($klados_id,$mysqlconnection);
		$sx_organ_id = mysql_result($result, $i, "sx_organikhs");
		$sx_organikhs = getSchool ($sx_organ_id, $mysqlconnection);
		$sx_yphrethshs_id = mysql_result($result, $i, "sx_yphrethshs");
		$sx_yphrethshs = getSchool ($sx_yphrethshs_id, $mysqlconnection);
                $sx_organikhs_url = "<a href=\"school_status.php?org=$sx_organ_id\">$sx_organikhs</a>";
                $sx_yphrethshs_url = "<a href=\"school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a>";
                // check if multiple schools
                $qry = "select * from yphrethsh where emp_id=$id and sxol_etos=$sxol_etos";
                $res = mysql_query($qry,$mysqlconnection);
                if (mysql_num_rows($res) > 0)
                    $sx_yphrethshs .= "*";
								
		echo "<tr><td>";
		echo "<span title=\"�������\"><a href=\"employee.php?id=$id&op=view\"><img style=\"border: 0pt none;\" src=\"images/view_action.png\"/></a></span>";
		if ($usrlvl < 3)
                    echo "<span title=\"�����������\"><a href=\"employee.php?id=$id&op=edit\"><img style=\"border: 0pt none;\" src=\"images/edit_action.png\"/></a></span>";
		if ($usrlvl < 2)
                    echo "<span title=\"��������\"><a href=\"javascript:confirmDelete('employee.php?id=$id&op=delete')\"><img style=\"border: 0pt none;\" src=\"images/delete_action.png\"/></a></span>";
		echo "</td>";
		echo "<td><a href=\"employee.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>".$sx_organikhs_url."</td><td>".$sx_yphrethshs_url."</td>\n";
		echo "</tr>";

		$i++;
    }   
		echo "</tbody>\n";
                if ($usrlvl < 2)
                    echo "<tr><td colspan=7><span title=\"��������\"><a href=\"employee.php?id=$id&op=add\"><img style=\"border: 0pt none;\" src=\"images/user_add.png\"/>�������� �������������</a></span>";		
		echo "<tr><td colspan=7 align=center>";
		$prevpg = $curpg-1;
		if ($lastpg == 0)
			$curpg = 0;
		echo "������ $curpg ��� $lastpg ($num_record1 ��������)<br>";
		if ($curpg!=1)
		{
				echo "  <a href=idiwtikoi.php?page=1&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost>�����</a>";
				echo "&nbsp;&nbsp;  <a href=idiwtikoi.php?page=$prevpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost>�����/��</a>";
		}
		else
			echo "  ����� &nbsp;&nbsp; �����/��";
		if ($curpg != $lastpg)
		{
				$nextpg = $curpg+1;
				echo "&nbsp;&nbsp;  <a href=idiwtikoi.php?page=$nextpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost>�������</a>";
				echo "&nbsp;&nbsp;  <a href=idiwtikoi.php?page=$lastpg&rpp=$rpp&klados=$klpost&org=$orgpost&yphr=$yppost&surname=$surpost>���������</a>";
		}
		else 
			echo "  ������� &nbsp;&nbsp; ���������";
		echo "<FORM METHOD=\"POST\" ACTION=\"idiwtikoi.php\">";
		echo " �������� ��� ���.  <input type=\"text\" name=\"page\" size=1 />";
		echo "<input type=\"submit\" value=\"��������\">";
                echo "<br>";
                echo "   ����./���.    <input type=\"text\" name=\"rpp\" value=\"$rpp\" size=1 />";
		echo "<input type=\"submit\" value=\"�������\">";
		echo "</FORM>";
		echo "</td></tr>";
                echo "<tr><td colspan=6><INPUT TYPE='button' VALUE='������ ������' onClick=\"parent.location='index.php'\"></td></tr>";
		echo "</table>\n";
      ?>
      
      <br><br>
     
    </center>
</div>
  </body>
</html>
<?php
	mysql_close();
?>
