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
      
?>
<html>
  <head>
	
    <LINK href="../css/style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="content-type" content="text/html; charset=iso8859-7">
    <title>������� ���������</title>
	
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery.validate.js"></script>
	<script type='text/javascript' src='../js/jquery.autocomplete.js'></script>
	<script type="text/javascript" src="../js/jquery.tablesorter.js"></script> 
	<link rel="stylesheet" type="text/css" href="../js/jquery.autocomplete.css" />
	<script type="text/javascript">		
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
			$("#surname").autocomplete("get_name_ekt.php", {
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
  <?php include('../etc/menu.php'); ?>
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

                $query = "SELECT * FROM ektaktoi ";
	  	
		$klpost = $yppost = $praxipost = 0;
		if (($_POST['klados']>0) || (strlen($_POST['yphr'])>0) || (strlen($_POST['surname'])>0) || (strlen($_POST['type'])>0))
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
                        if ($whflag)
                            $query .= "AND klados = $klpost ";
			else
                            $query .= "WHERE klados = $klpost ";
			$whflag=1;
		}
                if (($_POST['type']>0) || ($_GET['type']>0))
		{
			if ($_POST['type']>0)
				$typepost = $_POST['type'];
			else
				$typepost = $_GET['type'];
                        if ($whflag)
                            $query .= "AND type = $typepost ";
			else
                            $query .= "WHERE type = $typepost ";
			$whflag=1;
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
                        else{
                            $surpost = urldecode($_GET['surname']);
                            //$surpost = mb_convert_encoding($surpost, "iso-8859-7", "utf-8");
                        }
                            
			if ($whflag)
				$query .= "AND surname LIKE '$surpost' ";
			else
			{
				$query .= "WHERE surname LIKE '$surpost' ";
				$whflag=1;
			}
		}
                if ((strlen($_POST['praxi'])>0) || ($_GET['praxi']>0))
		{
			if ($_GET['praxi']>0)
				$praxipost = $_GET['praxi'];
                        else
                            $praxipost = $_POST['praxi'];
			if ($whflag)
				$query .= "AND praxi = $praxipost ";
			else
				$query .= "WHERE praxi = $praxipost ";
		}
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
                
                // added 07-02-2013 - when 1 result, redirect to that employee page
                if ($num_record == 1)
                {
                    $id = mysql_result($result, 0, "id");
                    $url = "ektaktoi.php?id=$id&op=view";
                    echo "<script>window.location = '$url'</script>";
                }
		
        echo "<center>";        
	echo "<table id=\"mytbl\" class=\"imagetable tablesorter\" border=\"2\">\n";
        echo "<thead><tr><form id='src' name='src' action='ektaktoi_list.php' method='POST'>\n";
	//if ($posted || ($_GET['klados']>0) || ($_GET['org']>0) || ($_GET['yphr']>0) || ($_GET['type']>0))
	//	echo "<td><INPUT TYPE='submit' VALUE='���������'></td><td>\n";
	//else	
        ?>
    <script type="text/javascript">
        $().ready(function(){
            $('#resetBtn').click(function() {
                $('#surname').val("");
                $('#klados').val("");
                $('#yphr').val("");
                $('#type').val("");
                $('#praxi').val("");
                $('#src').submit();
            });
        });
    </script>
        <?php
        echo "<td><INPUT TYPE='submit' VALUE='���������' />"
            . "<br><center><button type='button' id='resetBtn' style='margin: 3px'><small>���������</small></button></center>"
            . "</td><td>\n";
                
	echo "<input type='text' name='surname' id='surname' value='$surpost' />\n";
	echo "<td></td><td>\n";
        echo $klpost ? kladosCombo($klpost,$mysqlconnection) : kladosCmb($mysqlconnection);
	echo "</td>\n";
		echo "<div id=\"content\">";
		echo "<form autocomplete=\"off\">";
		echo "<td><input type=\"text\" name=\"yphr\" id=\"yphr\" value='".getSchool($yppost, $mysqlconnection)."'/></td>";
		echo "</div>";
	echo "<td>";
        //echo "</td>";
        echo $typepost ? typeCmb1($typepost,$mysqlconnection) : typeCmb($mysqlconnection);
        echo "</td>";
        echo "<td>";
        tblCmb($mysqlconnection, 'praxi', $praxipost,'praxi');
        echo "</td>";
	echo "</form></tr>\n";
	
        echo "<tr><th>��������</th>\n";
	echo "<th>�������</th>\n";
	echo "<th>�����</th>\n";
	echo "<th>����������</th>\n";
	echo "<th>��.����������<br><small>(* ����������� ��� 1 ����.)</small></th>\n";
        echo "<th>����� �����������</th>\n";
        echo "<th>�����</th>\n";
	echo "</tr>\n</thead>\n";
	
	echo "<tbody>\n";
	
	while ($i < $num)
	{
	
		$id = mysql_result($result, $i, "id");
		$name = mysql_result($result, $i, "name");
		$surname = mysql_result($result, $i, "surname");
		$klados_id = mysql_result($result, $i, "klados");
		$klados = getKlados($klados_id,$mysqlconnection);
		// an parapanw apo 1 sxoleia, deixnei mono to 1o (kyriws) kai vazei * dipla toy.
                $sx_yphrethshs_id_str = mysql_result($result, $i, "sx_yphrethshs");
                $sx_yphrethshs_id_arr = explode(",", $sx_yphrethshs_id_str);
                $sx_yphrethshs_id = trim($sx_yphrethshs_id_arr[0]);
                //$sx_yphrethshs_id = mysql_result($result, $i, "sx_yphrethshs");
		$sx_yphrethshs = getSchool ($sx_yphrethshs_id, $mysqlconnection);
                $sx_yphrethshs_url = "<a href=\"../school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a>";
//                if (count($sx_yphrethshs_id_arr)>2)
//                {
//                    $sx_yphrethshs_url = "<a href=\"../school/school_status.php?org=$sx_yphrethshs_id\">$sx_yphrethshs</a> *";
//                    $sx_yphrethshs.=" *";
//                }
                $type = mysql_result($result, $i, "type");
                $praxi = mysql_result($result, $i, "praxi");
                $praxi = getNamefromTbl($mysqlconnection, "praxi", $praxi);
								
		echo "<tr><td>";
		echo "<span title=\"�������\"><a href=\"ektaktoi.php?id=$id&op=view\"><img style=\"border: 0pt none;\" src=\"../images/view_action.png\"/></a></span>";
		if ($usrlvl < 3)
                    echo "<span title=\"�����������\"><a href=\"ektaktoi.php?id=$id&op=edit\"><img style=\"border: 0pt none;\" src=\"../images/edit_action.png\"/></a></span>";
		if ($usrlvl < 2)
                    echo "<span title=\"��������\"><a href=\"javascript:confirmDelete('ektaktoi.php?id=$id&op=delete')\"><img style=\"border: 0pt none;\" src=\"../images/delete_action.png\"/></a></span>";
		echo "</td>";
                $typos = get_type($type, $mysqlconnection);
		echo "<td><a href=\"ektaktoi.php?id=$id&op=view\">".$surname."</a></td><td>".$name."</td><td>".$klados."</td><td>".$sx_yphrethshs_url."</td><td>$typos</td><td>$praxi</td>\n";
		echo "</tr>";

		$i++;
    }   
		echo "</tbody>\n";
                if ($usrlvl < 2)
                    echo "<tr><td colspan=7><span title=\"��������\"><a href=\"ektaktoi.php?id=0&op=add\"><img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/>�������� �������� �������������</a></span>";		
                if ($usrlvl == 0)
                    echo "<tr><td colspan=7><span title=\"��������\"><a href=\"../tools/ektaktoi_import.php\"><img style=\"border: 0pt none;\" src=\"../images/user_add.png\"/>������ �������� �������� ���/��� ��� ������ excel (.xls)</a></span>";
		echo "<tr><td colspan=7 align=center>";
		$prevpg = $curpg-1;
		if ($lastpg == 0)
			$curpg = 0;
		echo "������ $curpg ��� $lastpg ($num_record1 ��������)<br>";
		if ($curpg!=1)
		{
				echo "  <a href=ektaktoi_list.php?page=1&rpp=$rpp&klados=$klpost&praxi=$praxipost&yphr=$yppost&klados=$klpost&type=$typepost>�����</a>";
				echo "&nbsp;&nbsp;  <a href=ektaktoi_list.php?page=$prevpg&rpp=$rpp&klados=$klpost&praxi=$praxipost&yphr=$yppost&klados=$klpost&type=$typepost>�����/��</a>";
		}
		else
			echo "  ����� &nbsp;&nbsp; �����/��";
		if ($curpg != $lastpg)
		{
				$nextpg = $curpg+1;
				echo "&nbsp;&nbsp;  <a href=ektaktoi_list.php?page=$nextpg&rpp=$rpp&klados=$klpost&praxi=$praxipost&yphr=$yppost&klados=$klpost&type=$typepost>�������</a>";
				echo "&nbsp;&nbsp;  <a href=ektaktoi_list.php?page=$lastpg&rpp=$rpp&klados=$klpost&praxi=$praxipost&yphr=$yppost&klados=$klpost&type=$typepost>���������</a>";
		}
		else 
			echo "  ������� &nbsp;&nbsp; ���������";
		echo "<FORM METHOD=\"POST\" ACTION=\"ektaktoi_list.php\">";
		echo " �������� ��� ���.  <input type=\"text\" name=\"page\" size=1 />";
		echo "<input type=\"submit\" value=\"��������\">";
                echo "<br>";
                echo "   ����./���.    <input type=\"text\" name=\"rpp\" value=\"$rpp\" size=1 />";
		echo "<input type=\"submit\" value=\"�������\">";
		echo "</FORM>";
		echo "</td></tr>";
                echo "<tr><td colspan=7><INPUT TYPE='button' VALUE='����������� �������' onClick=\"parent.location='praxi.php'\">";
                echo "&nbsp;&nbsp;&nbsp;";
                echo "<INPUT TYPE='button' VALUE='������������� & ������� ��� �����' onClick=\"parent.location='praxi_sch.php'\"></td></tr>";
                echo "<tr><td colspan=7><INPUT TYPE='button' VALUE='������� ��������� ������������ �����' onClick=\"parent.location='ektaktoi_prev.php'\"></td></tr>";
                echo "<tr><td colspan=7><INPUT TYPE='button' class='btn-red' VALUE='������ ������' onClick=\"parent.location='../index.php'\"></td></tr>";
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